<?php

namespace App\Services;

use App\Models\XeroToken;
use GuzzleHttp\Client;
use XeroAPI\XeroPHP\Configuration;
use XeroAPI\XeroPHP\Api\AccountingApi;
use XeroAPI\XeroPHP\Api\IdentityApi;
use XeroAPI\XeroPHP\Models\Accounting\Invoice;
use XeroAPI\XeroPHP\Models\Accounting\Invoices;
use XeroAPI\XeroPHP\Models\Accounting\LineItem;
use XeroAPI\XeroPHP\Models\Accounting\Contact;
use XeroAPI\XeroPHP\Models\Accounting\Contacts;
use League\OAuth2\Client\Provider\GenericProvider;

class XeroService
{
    private GenericProvider $provider;
    private ?XeroToken $token = null;

    public function __construct()
    {
        $this->provider = new GenericProvider([
            'clientId'                => config('services.xero.client_id'),
            'clientSecret'            => config('services.xero.client_secret'),
            'redirectUri'             => config('services.xero.redirect_uri'),
            'urlAuthorize'            => 'https://login.xero.com/identity/connect/authorize',
            'urlAccessToken'          => 'https://identity.xero.com/connect/token',
            'urlResourceOwnerDetails' => 'https://identity.xero.com/resources'
        ]);
    }

    /**
     * Get authorization URL
     */
    public function getAuthorizationUrl(): array
    {
        $authUrl = $this->provider->getAuthorizationUrl([
            'scope' => 'openid profile email offline_access accounting.transactions accounting.contacts accounting.settings'
        ]);

        return [
            'url'   => $authUrl,
            'state' => $this->provider->getState()
        ];
    }

    /**
     * Handle OAuth callback
     */
    public function handleCallback(string $code): XeroToken
    {
        $accessToken = $this->provider->getAccessToken('authorization_code', [
            'code' => $code
        ]);

        $config = Configuration::getDefaultConfiguration()
            ->setAccessToken($accessToken->getToken());

        $identityApi = new IdentityApi(new Client(), $config);
        $connections = $identityApi->getConnections();

        if (empty($connections)) {
            throw new \Exception('No Xero organizations found');
        }

        $tenant = $connections[0];

        // Clear old tokens and save new one
        XeroToken::truncate();

        return XeroToken::create([
            'access_token'  => $accessToken->getToken(),
            'refresh_token' => $accessToken->getRefreshToken(),
            'tenant_id'     => $tenant->getTenantId(),
            'tenant_name'   => $tenant->getTenantName(),
            'expires_at'    => now()->addSeconds($accessToken->getExpires() - time()),
        ]);
    }

    /**
     * Get valid token (auto-refresh if needed)
     */
    public function getValidToken(): XeroToken
    {
        $this->token = XeroToken::first();

        if (!$this->token) {
            throw new \Exception('Xero not connected. Visit /api/xero/authorize in browser first.');
        }

        if ($this->token->hasExpired() || $this->token->expiresSoon()) {
            $this->refreshToken();
        }

        return $this->token;
    }

    /**
     * Refresh access token
     */
    private function refreshToken(): void
    {
        $newAccessToken = $this->provider->getAccessToken('refresh_token', [
            'refresh_token' => $this->token->refresh_token
        ]);

        $this->token->update([
            'access_token'  => $newAccessToken->getToken(),
            'refresh_token' => $newAccessToken->getRefreshToken(),
            'expires_at'    => now()->addSeconds($newAccessToken->getExpires() - time()),
        ]);
    }

    /**
     * Check if connected
     */
    public function isConnected(): bool
    {
        return XeroToken::exists();
    }

    /**
     * Create invoice in Xero
     */
    public function createInvoice(array $data): array
    {
        $token = $this->getValidToken();

        $config = Configuration::getDefaultConfiguration()
            ->setAccessToken($token->access_token);

        $api = new AccountingApi(new Client(), $config);
        $tenantId = $token->tenant_id;

        // Create Contact
        $contact = new Contact();
        $contact->setName($data['customer_name']);
        if (!empty($data['customer_email'])) {
            $contact->setEmailAddress($data['customer_email']);
        }

        // Create Line Items
        $lineItems = [];
        foreach ($data['items'] as $item) {
            $lineItem = new LineItem();
            $lineItem->setDescription($item['description']);
            $lineItem->setQuantity($item['quantity']);
            $lineItem->setUnitAmount($item['unit_price']);
            $lineItem->setAccountCode($item['account_code'] ?? '200');
            $lineItems[] = $lineItem;
        }

        // Create Invoice
        $invoice = new Invoice();
        $invoice->setType(Invoice::TYPE_ACCREC);
        $invoice->setContact($contact);
        $invoice->setLineItems($lineItems);
        $invoice->setDate(new \DateTime());
        $invoice->setDueDate(new \DateTime($data['due_date'] ?? '+30 days'));
        $invoice->setStatus(Invoice::STATUS_DRAFT);

        if (!empty($data['reference'])) {
            $invoice->setReference($data['reference']);
        }

        // Send to Xero
        $invoices = new Invoices();
        $invoices->setInvoices([$invoice]);

        $result = $api->createInvoices($tenantId, $invoices);
        $createdInvoice = $result->getInvoices()[0];

        return [
            'invoice_id'     => $createdInvoice->getInvoiceId(),
            'invoice_number' => $createdInvoice->getInvoiceNumber(),
            'status'         => $createdInvoice->getStatus(),
            'total'          => $createdInvoice->getTotal(),
            'contact'        => $createdInvoice->getContact()->getName(),
        ];
    }
}