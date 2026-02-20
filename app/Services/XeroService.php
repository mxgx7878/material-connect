<?php
// FILE PATH: app/Services/XeroService.php

namespace App\Services;

use App\Models\Invoice as LocalInvoice;   // Our Eloquent Invoice model — aliased to prevent collision with Xero SDK
use App\Models\XeroToken;
use GuzzleHttp\Client;
use XeroAPI\XeroPHP\Configuration;
use XeroAPI\XeroPHP\Api\AccountingApi;
use XeroAPI\XeroPHP\Api\IdentityApi;
use XeroAPI\XeroPHP\Models\Accounting\Invoice as XeroInvoice;   // Xero SDK Invoice object
use XeroAPI\XeroPHP\Models\Accounting\Invoices;
use XeroAPI\XeroPHP\Models\Accounting\LineAmountTypes;
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
     * Push a locally created Invoice into Xero.
     *
     * This method accepts our own Invoice model (with items already loaded)
     * and maps it to Xero's data structure.
     *
     * Line item mapping:
     *   - One Xero line per InvoiceItem  → product material cost
     *   - One extra Xero line per item   → delivery fee (if delivery_cost > 0)
     *   - One discount line at the end   → negative amount if invoice->discount > 0
     *
     * Tax: OUTPUT (Australia GST 10%) set on every line item so Xero calculates
     *      tax correctly. LineAmountTypes set to EXCLUSIVE so unit_amount is
     *      the ex-GST price and Xero adds GST on top — matching your portal.
     *
     * Status: AUTHORISED (Xero's API does not allow STATUS_PAID on creation;
     *         PAID is a computed state requiring a separate Payment object.
     *         AUTHORISED = approved, finalised, ready for payment.)
     *
     * @param  Invoice $invoice  Must have ->items and ->order->client loaded
     * @return array             Xero invoice UUID + invoice number
     * @throws \Exception        If not connected or Xero API call fails
     */
    public function pushInvoice(LocalInvoice $invoice): array
    {
        $token    = $this->getValidToken();
        $tenantId = $token->tenant_id;

        $config = Configuration::getDefaultConfiguration()
            ->setAccessToken($token->access_token);

        $api = new AccountingApi(new Client(), $config);

        // ── 1. Build Contact from order's client ──
        $clientName  = $invoice->order?->client?->name  ?? 'Unknown Client';
        $clientEmail = $invoice->order?->client?->email ?? null;

        $contact = new Contact();
        $contact->setName($clientName);
        if ($clientEmail) {
            $contact->setEmailAddress($clientEmail);
        }

        // ── 2. Build Line Items ──
        //      setDescription() populates the "Description" column.
        //      Previously only setDescription() was used, leaving "Item" blank.
        //
        // FIX: setTaxType('OUTPUT') applies Australia's 10% GST on sales.
        //      Without this, Xero defaults to NONE (BAS Excluded) → 0 tax.
        //
        // All amounts are ex-GST. LineAmountTypes::EXCLUSIVE tells Xero
        // to treat unit_amount as the pre-tax price and calculate GST on top.

        $xeroLineItems = [];

        foreach ($invoice->items as $item) {
            // (a) Material cost line
            $materialLine = new LineItem();
            $materialLine->setDescription($item->product_name);         // Also shown in Description for clarity
            $materialLine->setQuantity((float) $item->quantity);
            $materialLine->setUnitAmount((float) $item->unit_price);    // ex-GST price
            $materialLine->setAccountCode('200');
            $materialLine->setTaxType('OUTPUT');                         // FIX: Australia GST 10% on sales
            $xeroLineItems[] = $materialLine;

            // (b) Delivery fee line — only when there is a delivery cost
            if ((float) $item->delivery_cost > 0) {
                $deliveryLine = new LineItem();
                $deliveryLine->setDescription("Delivery Fee - {$item->product_name}");
                $deliveryLine->setQuantity(1);
                $deliveryLine->setUnitAmount((float) $item->delivery_cost);
                $deliveryLine->setAccountCode('200');
                $deliveryLine->setTaxType('OUTPUT');                     // FIX: GST on delivery too
                $xeroLineItems[] = $deliveryLine;
            }
        }

        // (c) FIX: Discount line — Xero's standard way to represent an invoice-level
        //     discount is a negative-amount line item. We send the discount as a
        //     pre-tax negative value so Xero recalculates GST correctly on the net total.
        $discount = (float) ($invoice->discount ?? 0);
        if ($discount > 0) {
            $discountLine = new LineItem();
            $discountLine->setDescription('Discount');
            $discountLine->setQuantity(1);
            $discountLine->setUnitAmount(-$discount);                    // Negative amount = discount
            $discountLine->setAccountCode('200');
            $discountLine->setTaxType('OUTPUT');                         // GST adjusted on discounted amount too
            $xeroLineItems[] = $discountLine;
        }

        // ── 3. Build the Xero Invoice ──
        $xeroInvoice = new XeroInvoice();
        $xeroInvoice->setType(XeroInvoice::TYPE_ACCREC);
        $xeroInvoice->setContact($contact);
        $xeroInvoice->setLineItems($xeroLineItems);
        $xeroInvoice->setLineAmountTypes(LineAmountTypes::EXCLUSIVE); // ex-GST amounts
        $xeroInvoice->setDate(new \DateTime($invoice->issued_date->format('Y-m-d')));
        $xeroInvoice->setDueDate(new \DateTime($invoice->due_date->format('Y-m-d')));
        $xeroInvoice->setReference($invoice->invoice_number);
        $xeroInvoice->setStatus(XeroInvoice::STATUS_AUTHORISED);

        // ── 4. Wrap and send ──
        $invoicesWrapper = new Invoices();
        $invoicesWrapper->setInvoices([$xeroInvoice]);

        $result         = $api->createInvoices($tenantId, $invoicesWrapper);
        $createdInvoice = $result->getInvoices()[0];

        // ── 5. Detect Xero silent validation failures ──
        // Xero does NOT always throw an ApiException for bad data.
        // It can return HTTP 200 with the invoice object but InvoiceId = null UUID,
        // HasErrors = true, and validation messages in getValidationErrors().
        // We must detect this and throw explicitly so the catch in
        // InvoicePricingService logs it as a xero_warning instead of silently
        // saving 00000000-0000-0000-0000-000000000000 as the xero_invoice_id.
        $invoiceId = $createdInvoice->getInvoiceId();

        if (!$invoiceId || $invoiceId === '00000000-0000-0000-0000-000000000000') {
            $validationErrors = $createdInvoice->getValidationErrors() ?? [];
            $messages = [];
            foreach ($validationErrors as $err) {
                $messages[] = method_exists($err, 'getMessage') ? $err->getMessage() : (string) $err;
            }

            $detail = !empty($messages)
                ? implode(' | ', $messages)
                : 'Xero returned a null invoice ID. Check: account code 200 exists, tax type OUTPUT is enabled, contact name is valid.';

            \Illuminate\Support\Facades\Log::error('Xero invoice null UUID', [
                'invoice_number'    => $invoice->invoice_number,
                'has_errors'        => $createdInvoice->getHasErrors(),
                'validation_errors' => $messages,
                'xero_status'       => $createdInvoice->getStatus(),
            ]);

            throw new \Exception("Xero rejected the invoice: {$detail}");
        }

        // ── 6. Record Payment against the invoice ──
        // This marks the invoice as Paid in Xero — matching the portal's status.
        // We call POST /Payments with:
        //   - The Xero invoice UUID (just created above)
        //   - The bank account UUID for "Material Connect" NAB
        //   - The full invoice amount (what Xero calculated as the total)
        //   - Payment date = invoice issued date
        //
        // Xero will then move the invoice from "Awaiting Payment" → "Paid".
        try {
            $payment = new \XeroAPI\XeroPHP\Models\Accounting\Payment();

            // Reference the invoice by its Xero UUID
            $paymentInvoice = new XeroInvoice();
            $paymentInvoice->setInvoiceId($invoiceId);
            $payment->setInvoice($paymentInvoice);

            // Reference the bank account by its UUID
            $bankAccount = new \XeroAPI\XeroPHP\Models\Accounting\Account();
            $bankAccount->setAccountId('65934a72-06b2-468a-a3ca-e663260a6545'); // Material Connect NAB
            $payment->setAccount($bankAccount);

            // Payment amount = the total Xero calculated (after GST, after discount)
            $payment->setAmount($createdInvoice->getTotal());

            // Payment date = invoice issued date
            $payment->setDate(new \DateTime($invoice->issued_date->format('Y-m-d')));

            // Reference for traceability
            $payment->setReference($invoice->invoice_number);

            $api->createPayment($tenantId, $payment);

        } catch (\Exception $paymentException) {
            // Payment recording failed — invoice is in Xero as AUTHORISED but not Paid.
            // We don't fail the whole push for this — invoice was created successfully.
            // Log and surface in the return so the caller can warn the admin.
            \Illuminate\Support\Facades\Log::warning('Xero payment recording failed', [
                'invoice_number'   => $invoice->invoice_number,
                'xero_invoice_id'  => $invoiceId,
                'error'            => $paymentException->getMessage(),
            ]);
        }

        return [
            'xero_invoice_id'     => $invoiceId,
            'xero_invoice_number' => $createdInvoice->getInvoiceNumber(),
            'xero_status'         => $createdInvoice->getStatus(),
        ];
    }

    /**
     * Create Invoice via raw data (used by XeroController standalone endpoint).
     * Kept as-is for backward compatibility.
     */
    public function createInvoice(array $data): array
    {
        $token = $this->getValidToken();

        $config = Configuration::getDefaultConfiguration()
            ->setAccessToken($token->access_token);

        $api      = new AccountingApi(new Client(), $config);
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
        $invoice = new XeroInvoice();
        $invoice->setType(XeroInvoice::TYPE_ACCREC);
        $invoice->setContact($contact);
        $invoice->setLineItems($lineItems);
        $invoice->setDate(new \DateTime());
        $invoice->setDueDate(new \DateTime($data['due_date'] ?? '+30 days'));
        $invoice->setStatus(XeroInvoice::STATUS_DRAFT);

        if (!empty($data['reference'])) {
            $invoice->setReference($data['reference']);
        }

        // Send to Xero
        $invoices = new Invoices();
        $invoices->setInvoices([$invoice]);

        $result         = $api->createInvoices($tenantId, $invoices);
        $createdInvoice = $result->getInvoices()[0];

        return [
            'invoice_id'     => $createdInvoice->getInvoiceId(),
            'invoice_number' => $createdInvoice->getInvoiceNumber(),
            'status'         => $createdInvoice->getStatus(),
            'total'          => $createdInvoice->getTotal(),
            'contact'        => $createdInvoice->getContact()->getName(),
        ];
    }

    /**
     * TEMPORARY: Fetch all bank accounts from Xero to get their Account IDs.
     * Used once to find the correct account UUID for recording payments.
     * Remove after obtaining the bank account ID.
     */
    public function getBankAccounts(): array
    {
        $token  = $this->getValidToken();
        $config = Configuration::getDefaultConfiguration()
            ->setAccessToken($token->access_token);

        $api      = new AccountingApi(new Client(), $config);
        $accounts = $api->getAccounts($token->tenant_id, null, 'Type=="BANK"');

        $result = [];
        foreach ($accounts->getAccounts() as $account) {
            $result[] = [
                'account_id'   => $account->getAccountId(),   // UUID — this is what we need for payments
                'code'         => $account->getCode(),
                'name'         => $account->getName(),
                'type'         => $account->getType(),
                'status'       => $account->getStatus(),
            ];
        }

        return $result;
    }

}