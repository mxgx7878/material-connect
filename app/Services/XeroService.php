<?php
// FILE PATH: app/Services/XeroService.php

namespace App\Services;

use App\Models\Invoice as LocalInvoice;
use App\Models\CreditNote as LocalCreditNote;
use App\Models\XeroToken;
use GuzzleHttp\Client;
use XeroAPI\XeroPHP\Configuration;
use XeroAPI\XeroPHP\Api\AccountingApi;
use XeroAPI\XeroPHP\Api\IdentityApi;
use XeroAPI\XeroPHP\Models\Accounting\Invoice as XeroInvoice;
use XeroAPI\XeroPHP\Models\Accounting\Invoices;
use XeroAPI\XeroPHP\Models\Accounting\LineAmountTypes;
use XeroAPI\XeroPHP\Models\Accounting\LineItem;
use XeroAPI\XeroPHP\Models\Accounting\Contact;
use XeroAPI\XeroPHP\Models\Accounting\Contacts;
use XeroAPI\XeroPHP\Models\Accounting\CreditNote as XeroCreditNote;
use XeroAPI\XeroPHP\Models\Accounting\CreditNotes;
use XeroAPI\XeroPHP\Models\Accounting\Payment;
use XeroAPI\XeroPHP\Models\Accounting\Account;
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

        XeroToken::truncate();

        return XeroToken::create([
            'access_token'  => $accessToken->getToken(),
            'refresh_token' => $accessToken->getRefreshToken(),
            'tenant_id'     => $tenant->getTenantId(),
            'tenant_name'   => $tenant->getTenantName(),
            'expires_at'    => now()->addSeconds($accessToken->getExpires() - time()),
        ]);
    }

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

    public function isConnected(): bool
    {
        return XeroToken::exists();
    }

    /**
     * Push a locally created Invoice into Xero. (Unchanged from your version.)
     */
    public function pushInvoice(LocalInvoice $invoice): array
    {
        $token    = $this->getValidToken();
        $tenantId = $token->tenant_id;

        $config = Configuration::getDefaultConfiguration()
            ->setAccessToken($token->access_token);

        $api = new AccountingApi(new Client(), $config);

        $invoice->loadMissing([
            'items.surcharges',
            'items.testingFees',
            'order.client',
        ]);

        $clientName  = $invoice->order?->client?->name  ?? 'Unknown Client';
        $clientEmail = $invoice->order?->client?->email ?? null;

        $contact = new Contact();
        $contact->setName($clientName);
        if ($clientEmail) {
            $contact->setEmailAddress($clientEmail);
        }

        $xeroLineItems = [];

        foreach ($invoice->items as $item) {
            $materialLine = new LineItem();
            $materialLine->setDescription($item->product_name);
            $materialLine->setQuantity((float) $item->quantity);
            $materialLine->setUnitAmount((float) $item->unit_price);
            $materialLine->setAccountCode('200');
            $materialLine->setTaxType('OUTPUT');
            $xeroLineItems[] = $materialLine;

            if ((float) $item->delivery_cost > 0) {
                $deliveryLine = new LineItem();
                $deliveryLine->setDescription("Delivery Fee - {$item->product_name}");
                $deliveryLine->setQuantity(1);
                $deliveryLine->setUnitAmount((float) $item->delivery_cost);
                $deliveryLine->setAccountCode('200');
                $deliveryLine->setTaxType('OUTPUT');
                $xeroLineItems[] = $deliveryLine;
            }

            foreach ($item->surcharges as $surcharge) {
                $amount = (float) $surcharge->calculated_amount;
                if ($amount == 0.0) continue;

                $label = $surcharge->billing_code
                    ? "{$surcharge->name} ({$surcharge->billing_code}) - {$item->product_name}"
                    : "{$surcharge->name} - {$item->product_name}";

                $surchargeLine = new LineItem();
                $surchargeLine->setDescription($label);
                $surchargeLine->setQuantity(1);
                $surchargeLine->setUnitAmount($amount);
                $surchargeLine->setAccountCode('200');
                $surchargeLine->setTaxType('OUTPUT');
                $xeroLineItems[] = $surchargeLine;
            }

            foreach ($item->testingFees as $testingFee) {
                if (!$testingFee->included) continue;

                $amount = (float) $testingFee->amount_snapshot;
                if ($amount == 0.0) continue;

                $label = $testingFee->billing_code
                    ? "{$testingFee->name} ({$testingFee->billing_code}) - {$item->product_name}"
                    : "{$testingFee->name} - {$item->product_name}";

                $testingLine = new LineItem();
                $testingLine->setDescription($label);
                $testingLine->setQuantity(1);
                $testingLine->setUnitAmount($amount);
                $testingLine->setAccountCode('200');
                $testingLine->setTaxType('OUTPUT');
                $xeroLineItems[] = $testingLine;
            }
        }

        $discount = (float) ($invoice->discount ?? 0);
        if ($discount > 0) {
            $discountLine = new LineItem();
            $discountLine->setDescription('Discount');
            $discountLine->setQuantity(1);
            $discountLine->setUnitAmount(-$discount);
            $discountLine->setAccountCode('200');
            $discountLine->setTaxType('OUTPUT');
            $xeroLineItems[] = $discountLine;
        }

        $xeroInvoice = new XeroInvoice();
        $xeroInvoice->setType(XeroInvoice::TYPE_ACCREC);
        $xeroInvoice->setContact($contact);
        $xeroInvoice->setLineItems($xeroLineItems);
        $xeroInvoice->setLineAmountTypes(LineAmountTypes::EXCLUSIVE);
        $xeroInvoice->setDate(new \DateTime($invoice->issued_date->format('Y-m-d')));
        $xeroInvoice->setDueDate(new \DateTime($invoice->due_date->format('Y-m-d')));
        $xeroInvoice->setReference($invoice->invoice_number);
        $xeroInvoice->setStatus(XeroInvoice::STATUS_AUTHORISED);

        $invoicesWrapper = new Invoices();
        $invoicesWrapper->setInvoices([$xeroInvoice]);

        $result         = $api->createInvoices($tenantId, $invoicesWrapper);
        $createdInvoice = $result->getInvoices()[0];

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

        return [
            'xero_invoice_id'     => $invoiceId,
            'xero_invoice_number' => $createdInvoice->getInvoiceNumber(),
            'xero_status'         => $createdInvoice->getStatus(),
        ];
    }

    // ═════════════════════════════════════════════════════════════════════
    // STATUS SYNC: push local status changes to Xero
    // ═════════════════════════════════════════════════════════════════════

    /**
     * Push a status change to Xero for an existing invoice.
     *
     * Mapping table:
     *   Local 'Draft'           → Xero DRAFT       (invoice update)
     *   Local 'Sent'            → Xero AUTHORISED  (invoice update)
     *   Local 'Void'            → Xero VOIDED      (invoice update)
     *   Local 'Cancelled'       → Xero DELETED     (or VOIDED if not draft)
     *   Local 'Paid'            → POST Payment for full balance, Xero auto-PAIDs
     *   Local 'Partially Paid'  → no-op with warning (record manually in Xero)
     *   Local 'Overdue'         → no-op with warning (Xero derives from due date)
     *
     * @return array ['pushed' => bool, 'xero_status' => ?string, 'warning' => ?string]
     */
    public function updateInvoiceStatus(LocalInvoice $invoice, string $localStatus): array
    {
        if (!$invoice->xero_invoice_id) {
            return [
                'pushed'      => false,
                'xero_status' => null,
                'warning'     => 'Invoice has no Xero ID — never synced to Xero.',
            ];
        }

        // ── Payment-driven status: Paid → records a Payment in Xero ──
        if ($localStatus === 'Paid') {
            return $this->recordPaymentForInvoice($invoice, null, true);
        }

        // ── Partially Paid: not supported for Xero sync yet ──
        // Local status updates fine, but we don't push a Payment to Xero because
        // we'd need an explicit amount from the admin. Returns a clear warning so
        // the frontend knows the local change succeeded but Xero is out of sync.
        if ($localStatus === 'Partially Paid') {
            return [
                'pushed'      => false,
                'xero_status' => null,
                'warning'     => "Partially Paid is not synced to Xero. Record the partial payment directly in Xero to keep the systems aligned.",
            ];
        }

        if ($localStatus === 'Overdue') {
            return [
                'pushed'      => false,
                'xero_status' => null,
                'warning'     => "Overdue is derived from due_date in Xero, not settable. Local status updated only.",
            ];
        }

        // ── Status-update flow for Draft / Sent / Void / Cancelled ──
        $xeroStatus = match ($localStatus) {
            'Draft'     => XeroInvoice::STATUS_DRAFT,
            'Sent'      => XeroInvoice::STATUS_AUTHORISED,
            'Void'      => XeroInvoice::STATUS_VOIDED,
            'Cancelled' => XeroInvoice::STATUS_DELETED,
            default     => null,
        };

        if ($xeroStatus === null) {
            return [
                'pushed'      => false,
                'xero_status' => null,
                'warning'     => "Local status '{$localStatus}' has no Xero mapping.",
            ];
        }

        $token    = $this->getValidToken();
        $tenantId = $token->tenant_id;
        $config   = Configuration::getDefaultConfiguration()->setAccessToken($token->access_token);
        $api      = new AccountingApi(new Client(), $config);

        try {
            $xeroInvoice = new XeroInvoice();
            $xeroInvoice->setInvoiceId($invoice->xero_invoice_id);
            $xeroInvoice->setStatus($xeroStatus);

            $wrapper = new Invoices();
            $wrapper->setInvoices([$xeroInvoice]);

            $result  = $api->updateInvoice($tenantId, $invoice->xero_invoice_id, $wrapper);
            $updated = $result->getInvoices()[0] ?? null;

            if (!$updated || $updated->getHasErrors()) {
                $errors = $updated ? ($updated->getValidationErrors() ?? []) : [];
                $messages = [];
                foreach ($errors as $err) {
                    $messages[] = method_exists($err, 'getMessage') ? $err->getMessage() : (string) $err;
                }
                $detail = !empty($messages) ? implode(' | ', $messages) : 'Unknown Xero error';

                // Cancelled → DELETED only works on DRAFT invoices.
                // For ANY other state, Xero rejects it. Fall back to VOIDED so
                // admin still gets the cancellation outcome.
                if ($xeroStatus === XeroInvoice::STATUS_DELETED) {
                    \Illuminate\Support\Facades\Log::info(
                        "Xero rejected DELETED for invoice {$invoice->invoice_number}; falling back to VOIDED. Original error: {$detail}"
                    );
                    return $this->updateInvoiceStatusToXeroValue(
                        $invoice,
                        XeroInvoice::STATUS_VOIDED,
                        $api,
                        $tenantId
                    );
                }

                return [
                    'pushed'      => false,
                    'xero_status' => null,
                    'warning'     => "Xero rejected status change: {$detail}",
                ];
            }

            return [
                'pushed'      => true,
                'xero_status' => $updated->getStatus(),
                'warning'     => null,
            ];
        } catch (\Exception $e) {
            // Xero's SDK throws ApiException for HTTP 400s instead of returning
            // a structured response with HasErrors=true. Cancelled→DELETED is the
            // most common cause: Xero only allows DELETED on DRAFT invoices.
            // For any DELETED-related exception, retry with VOIDED.
            if ($xeroStatus === XeroInvoice::STATUS_DELETED) {
                \Illuminate\Support\Facades\Log::info(
                    "Xero threw on DELETED for invoice {$invoice->invoice_number}; falling back to VOIDED. Original: " . $e->getMessage()
                );
                return $this->updateInvoiceStatusToXeroValue(
                    $invoice,
                    XeroInvoice::STATUS_VOIDED,
                    $api,
                    $tenantId
                );
            }

            // Extract the actual Xero validation error from the API exception body.
            // The Guzzle exception's truncated Message hides the useful detail; the real
            // error sits in $e->getResponseBody() if it's a Xero ApiException.
            $detail = $this->extractXeroErrorDetail($e);

            \Illuminate\Support\Facades\Log::error('Xero status update failed', [
                'invoice_number' => $invoice->invoice_number,
                'local_status'   => $localStatus,
                'xero_status'    => $xeroStatus,
                'error'          => $e->getMessage(),
                'xero_detail'    => $detail,
            ]);
            return [
                'pushed'      => false,
                'xero_status' => null,
                'warning'     => 'Xero rejected status change: ' . $detail,
            ];
        }
    }

    /**
     * Pull the actual ValidationError messages out of a Xero ApiException.
     * Without this, callers see Guzzle's generic "400 Bad Request" wrapping
     * with the useful Xero detail truncated.
     */
    private function extractXeroErrorDetail(\Throwable $e): string
    {
        // Xero's SDK exception exposes getResponseBody() with the raw JSON
        if (method_exists($e, 'getResponseBody')) {
            $body = $e->getResponseBody();
            if (is_string($body)) {
                $decoded = json_decode($body, true);
                if (is_array($decoded)) {
                    // Walk the standard Xero error structure:
                    // { Elements: [ { ValidationErrors: [ { Message: "..." } ] } ] }
                    $messages = [];
                    foreach ($decoded['Elements'] ?? [] as $el) {
                        foreach ($el['ValidationErrors'] ?? [] as $ve) {
                            if (!empty($ve['Message'])) {
                                $messages[] = $ve['Message'];
                            }
                        }
                    }
                    if (!empty($messages)) {
                        return implode(' | ', $messages);
                    }
                    if (!empty($decoded['Message'])) {
                        return $decoded['Message'];
                    }
                }
            }
        }
        // Fallback: just return the exception message (still better than nothing)
        return $e->getMessage();
    }

    private function updateInvoiceStatusToXeroValue(
        LocalInvoice $invoice,
        string $xeroStatus,
        AccountingApi $api,
        string $tenantId
    ): array {
        try {
            $xeroInvoice = new XeroInvoice();
            $xeroInvoice->setInvoiceId($invoice->xero_invoice_id);
            $xeroInvoice->setStatus($xeroStatus);

            $wrapper = new Invoices();
            $wrapper->setInvoices([$xeroInvoice]);

            $result  = $api->updateInvoice($tenantId, $invoice->xero_invoice_id, $wrapper);
            $updated = $result->getInvoices()[0] ?? null;

            if (!$updated || $updated->getHasErrors()) {
                return [
                    'pushed'      => false,
                    'xero_status' => null,
                    'warning'     => 'Fallback Xero status update failed.',
                ];
            }

            return [
                'pushed'      => true,
                'xero_status' => $updated->getStatus(),
                'warning'     => null,
            ];
        } catch (\Exception $e) {
            return [
                'pushed'      => false,
                'xero_status' => null,
                'warning'     => 'Fallback status update failed: ' . $e->getMessage(),
            ];
        }
    }

    // ═════════════════════════════════════════════════════════════════════
    // PAYMENT RECORDING (drives Paid / Partially Paid)
    // ═════════════════════════════════════════════════════════════════════

    /**
     * Record a Payment in Xero against an invoice. This is what actually transitions
     * a Xero invoice from AUTHORISED → PAID (or to a partially-paid state).
     *
     * Xero's API does NOT let you set status='PAID' directly — you must POST a Payment
     * object referencing the invoice and a bank account. Xero then derives the status.
     *
     * @param  LocalInvoice  $invoice
     * @param  ?float        $amount    Payment amount; if null, uses invoice's outstanding balance
     * @param  bool          $isFullPayment  True for 'Paid', false for 'Partially Paid'
     * @return array         ['pushed', 'xero_status', 'warning']
     */
    /**
     * Hardcoded bank account UUID — Material Connect NAB (production tenant).
     * Same UUID that was used in the previously removed payment block.
     * If you need to swap this for staging or a different bank, change it here.
     */
    private const XERO_BANK_ACCOUNT_ID = '65934a72-06b2-468a-a3ca-e663260a6545';

    public function recordPaymentForInvoice(LocalInvoice $invoice, ?float $amount, bool $isFullPayment): array
    {
        $bankAccountId = self::XERO_BANK_ACCOUNT_ID;

        if (!$invoice->xero_invoice_id) {
            return [
                'pushed'      => false,
                'xero_status' => null,
                'warning'     => 'Invoice has no Xero ID — cannot record payment.',
            ];
        }

        $token    = $this->getValidToken();
        $tenantId = $token->tenant_id;
        $config   = Configuration::getDefaultConfiguration()->setAccessToken($token->access_token);
        $api      = new AccountingApi(new Client(), $config);

        try {
            // ── 1. Fetch the current Xero invoice to learn its outstanding balance ──
            // We use AmountDue from Xero rather than computing locally, because Xero
            // may already have payments applied that we don't know about.
            $fetched = $api->getInvoice($tenantId, $invoice->xero_invoice_id);
            $xeroInvoice = $fetched->getInvoices()[0] ?? null;

            if (!$xeroInvoice) {
                return [
                    'pushed'      => false,
                    'xero_status' => null,
                    'warning'     => 'Could not fetch invoice from Xero to validate balance.',
                ];
            }

            $amountDue = (float) $xeroInvoice->getAmountDue();

            if ($amountDue <= 0) {
                return [
                    'pushed'      => false,
                    'xero_status' => $xeroInvoice->getStatus(),
                    'warning'     => 'Xero reports invoice already fully paid (AmountDue = 0). No payment recorded.',
                ];
            }

            // ── 2. Decide the payment amount ──
            $paymentAmount = $isFullPayment
                ? $amountDue                                  // Pay off the full outstanding balance
                : min((float) $amount, $amountDue);           // Cap partial at outstanding (defensive)

            if ($paymentAmount <= 0) {
                return [
                    'pushed'      => false,
                    'xero_status' => null,
                    'warning'     => 'Calculated payment amount is zero — nothing to record.',
                ];
            }

            // ── 3. Build the Payment object ──
            $payment = new Payment();

            $invoiceRef = new XeroInvoice();
            $invoiceRef->setInvoiceId($invoice->xero_invoice_id);
            $payment->setInvoice($invoiceRef);

            $bankAccount = new Account();
            $bankAccount->setAccountId($bankAccountId);
            $payment->setAccount($bankAccount);

            $payment->setAmount($paymentAmount);

            // Use paid_at if set, else today
            $paymentDate = $invoice->paid_at
                ? new \DateTime($invoice->paid_at->format('Y-m-d'))
                : new \DateTime();
            $payment->setDate($paymentDate);

            $payment->setReference("Portal payment for {$invoice->invoice_number}");

            // ── 4. Send to Xero ──
            $result   = $api->createPayment($tenantId, $payment);
            $created  = $result->getPayments()[0] ?? null;

            // ── Validation: Payment object doesn't expose getHasErrors() like Invoice does.
            // We check whether the Payment came back with a valid PaymentID instead.
            if (!$created) {
                return [
                    'pushed'      => false,
                    'xero_status' => null,
                    'warning'     => 'Xero did not return a payment object.',
                ];
            }

            $paymentId = $created->getPaymentId();
            if (!$paymentId || $paymentId === '00000000-0000-0000-0000-000000000000') {
                // Fall back to checking validation errors if the SDK exposed them
                $errors = method_exists($created, 'getValidationErrors')
                    ? ($created->getValidationErrors() ?? [])
                    : [];
                $messages = [];
                foreach ($errors as $err) {
                    $messages[] = method_exists($err, 'getMessage') ? $err->getMessage() : (string) $err;
                }
                $detail = !empty($messages) ? implode(' | ', $messages) : 'Xero returned a null PaymentID.';

                \Illuminate\Support\Facades\Log::error('Xero payment recording failed', [
                    'invoice_number'    => $invoice->invoice_number,
                    'amount'            => $paymentAmount,
                    'validation_errors' => $messages,
                ]);

                return [
                    'pushed'      => false,
                    'xero_status' => null,
                    'warning'     => "Xero rejected payment: {$detail}",
                ];
            }

            // ── 5. Re-fetch the invoice to get the new derived status (AUTHORISED or PAID) ──
            $refetched = $api->getInvoice($tenantId, $invoice->xero_invoice_id);
            $newStatus = $refetched->getInvoices()[0]?->getStatus() ?? 'PAID';

            return [
                'pushed'      => true,
                'xero_status' => $newStatus,
                'warning'     => null,
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Xero payment recording threw', [
                'invoice_number' => $invoice->invoice_number,
                'error'          => $e->getMessage(),
            ]);
            return [
                'pushed'      => false,
                'xero_status' => null,
                'warning'     => 'Xero payment recording failed: ' . $e->getMessage(),
            ];
        }
    }

    // ═════════════════════════════════════════════════════════════════════
    // CREDIT NOTES (used by dispute resolution)
    // ═════════════════════════════════════════════════════════════════════

    public function pushCreditNote(LocalCreditNote $cn, array $lines): array
    {
        $token    = $this->getValidToken();
        $tenantId = $token->tenant_id;
        $config   = Configuration::getDefaultConfiguration()->setAccessToken($token->access_token);
        $api      = new AccountingApi(new Client(), $config);

        $cn->loadMissing(['invoice.order.client']);

        $client = $cn->invoice?->order?->client;
        $contact = new Contact();
        $contact->setName($client?->name ?? 'Unknown Client');
        if ($client?->email) {
            $contact->setEmailAddress($client->email);
        }

        $xeroLines = [];
        foreach ($lines as $line) {
            $li = new LineItem();
            $li->setDescription($line['description']);
            $li->setQuantity($line['quantity']);
            $li->setUnitAmount((float) $line['amount']);
            $li->setAccountCode('200');
            $li->setTaxType('OUTPUT');
            $xeroLines[] = $li;
        }

        $xeroCn = new XeroCreditNote();
        $xeroCn->setType(XeroCreditNote::TYPE_ACCRECCREDIT);
        $xeroCn->setContact($contact);
        $xeroCn->setLineItems($xeroLines);
        $xeroCn->setLineAmountTypes(LineAmountTypes::EXCLUSIVE);
        $xeroCn->setDate(new \DateTime($cn->issued_date->format('Y-m-d')));
        $xeroCn->setReference($cn->credit_note_number);
        $xeroCn->setStatus(XeroCreditNote::STATUS_AUTHORISED);

        $wrapper = new CreditNotes();
        $wrapper->setCreditNotes([$xeroCn]);

        $result  = $api->createCreditNotes($tenantId, $wrapper);
        $created = $result->getCreditNotes()[0];

        $cnId = $created->getCreditNoteId();
        if (!$cnId || $cnId === '00000000-0000-0000-0000-000000000000') {
            $errors = $created->getValidationErrors() ?? [];
            $messages = [];
            foreach ($errors as $err) {
                $messages[] = method_exists($err, 'getMessage') ? $err->getMessage() : (string) $err;
            }
            $detail = !empty($messages) ? implode(' | ', $messages) : 'Xero returned a null credit note ID.';

            \Illuminate\Support\Facades\Log::error('Xero credit note null UUID', [
                'credit_note_number' => $cn->credit_note_number,
                'validation_errors'  => $messages,
            ]);

            throw new \Exception("Xero rejected the credit note: {$detail}");
        }

        return [
            'xero_credit_note_id'     => $cnId,
            'xero_credit_note_number' => $created->getCreditNoteNumber(),
            'xero_status'             => $created->getStatus(),
        ];
    }

    // ═════════════════════════════════════════════════════════════════════
    // LEGACY / UTILITY
    // ═════════════════════════════════════════════════════════════════════

    public function createInvoice(array $data): array
    {
        $token = $this->getValidToken();

        $config = Configuration::getDefaultConfiguration()
            ->setAccessToken($token->access_token);

        $api      = new AccountingApi(new Client(), $config);
        $tenantId = $token->tenant_id;

        $contact = new Contact();
        $contact->setName($data['customer_name']);
        if (!empty($data['customer_email'])) {
            $contact->setEmailAddress($data['customer_email']);
        }

        $lineItems = [];
        foreach ($data['items'] as $item) {
            $lineItem = new LineItem();
            $lineItem->setDescription($item['description']);
            $lineItem->setQuantity($item['quantity']);
            $lineItem->setUnitAmount($item['unit_price']);
            $lineItem->setAccountCode($item['account_code'] ?? '200');
            $lineItems[] = $lineItem;
        }

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
     * Fetch all bank accounts from Xero. Used during setup to find the
     * AccountId UUID for XERO_DEFAULT_BANK_ACCOUNT_ID in .env.
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
                'account_id'   => $account->getAccountId(),
                'code'         => $account->getCode(),
                'name'         => $account->getName(),
                'type'         => $account->getType(),
                'status'       => $account->getStatus(),
            ];
        }

        return $result;
    }



    /**
     * Push an invoice marked "Completed" to Xero in a single bundled call.
     *
     * Lines built:
     *   1. All original invoice lines (material, delivery, surcharges, testing fees, discount)
     *      — same logic as pushInvoice()
     *   2. Adjustment lines from each RESOLVED dispute:
     *        - refund        → negative line ("Refund: DSP-... — <description>")
     *        - partial_credit → negative line ("Credit: DSP-... — <description>")
     *        - replacement   → zero-amount note line ("Replacement issued for DSP-...")
     *        - rejection     → no line
     *
     * Net invoice total in Xero = original total − refunds − partial credits.
     */
    public function pushCompletedInvoice(LocalInvoice $invoice): array
    {
        $token    = $this->getValidToken();
        $tenantId = $token->tenant_id;

        $config = Configuration::getDefaultConfiguration()
            ->setAccessToken($token->access_token);

        $api = new AccountingApi(new Client(), $config);

        $invoice->loadMissing([
            'items.surcharges',
            'items.testingFees',
            'disputes' => fn($q) => $q->where('status', 'resolved')->with('resolutionLines'),
            'order.client',
        ]);

        $clientName  = $invoice->order?->client?->name  ?? 'Unknown Client';
        $clientEmail = $invoice->order?->client?->email ?? null;

        $contact = new Contact();
        $contact->setName($clientName);
        if ($clientEmail) {
            $contact->setEmailAddress($clientEmail);
        }

        $xeroLineItems = [];

        // ── 1. Original invoice lines (mirror pushInvoice logic) ──
        foreach ($invoice->items as $item) {
            $materialLine = new LineItem();
            $materialLine->setDescription($item->product_name);
            $materialLine->setQuantity((float) $item->quantity);
            $materialLine->setUnitAmount((float) $item->unit_price);
            $materialLine->setAccountCode('200');
            $materialLine->setTaxType('OUTPUT');
            $xeroLineItems[] = $materialLine;

            if ((float) $item->delivery_cost > 0) {
                $deliveryLine = new LineItem();
                $deliveryLine->setDescription("Delivery Fee - {$item->product_name}");
                $deliveryLine->setQuantity(1);
                $deliveryLine->setUnitAmount((float) $item->delivery_cost);
                $deliveryLine->setAccountCode('200');
                $deliveryLine->setTaxType('OUTPUT');
                $xeroLineItems[] = $deliveryLine;
            }

            foreach ($item->surcharges as $surcharge) {
                $amount = (float) $surcharge->calculated_amount;
                if ($amount == 0.0) continue;

                $label = $surcharge->billing_code
                    ? "{$surcharge->name} ({$surcharge->billing_code}) - {$item->product_name}"
                    : "{$surcharge->name} - {$item->product_name}";

                $line = new LineItem();
                $line->setDescription($label);
                $line->setQuantity(1);
                $line->setUnitAmount($amount);
                $line->setAccountCode('200');
                $line->setTaxType('OUTPUT');
                $xeroLineItems[] = $line;
            }

            foreach ($item->testingFees as $testingFee) {
                if (!$testingFee->included) continue;
                $amount = (float) $testingFee->amount_snapshot;
                if ($amount == 0.0) continue;

                $label = $testingFee->billing_code
                    ? "{$testingFee->name} ({$testingFee->billing_code}) - {$item->product_name}"
                    : "{$testingFee->name} - {$item->product_name}";

                $line = new LineItem();
                $line->setDescription($label);
                $line->setQuantity(1);
                $line->setUnitAmount($amount);
                $line->setAccountCode('200');
                $line->setTaxType('OUTPUT');
                $xeroLineItems[] = $line;
            }
        }

        $discount = (float) ($invoice->discount ?? 0);
        if ($discount > 0) {
            $discountLine = new LineItem();
            $discountLine->setDescription('Discount');
            $discountLine->setQuantity(1);
            $discountLine->setUnitAmount(-$discount);
            $discountLine->setAccountCode('200');
            $discountLine->setTaxType('OUTPUT');
            $xeroLineItems[] = $discountLine;
        }

        // ── 2. Dispute adjustment lines ──
        foreach ($invoice->disputes as $dispute) {
            $prefix = match ($dispute->resolution_outcome) {
                'refund'         => 'Refund',
                'partial_credit' => 'Credit',
                'replacement'    => 'Replacement',
                default          => null,
            };

            if (!$prefix) continue; // rejected disputes contribute nothing

            if ($dispute->resolution_outcome === 'replacement') {
                // Informational only — zero amount keeps Xero math intact
                $note = new LineItem();
                $note->setDescription("{$prefix} issued for {$dispute->dispute_number} — {$dispute->category}");
                $note->setQuantity(1);
                $note->setUnitAmount(0);
                $note->setAccountCode('200');
                $note->setTaxType('OUTPUT');
                $xeroLineItems[] = $note;
                continue;
            }

            // refund / partial_credit — sum resolutionLines as negative entries
            foreach ($dispute->resolutionLines as $rl) {
                $amount = (float) $rl->amount;
                if ($amount <= 0) continue;

                $line = new LineItem();
                $line->setDescription("{$prefix} ({$dispute->dispute_number}) — {$rl->description}");
                $line->setQuantity((float) ($rl->quantity ?? 1));
                $line->setUnitAmount(-$amount);          // negative
                $line->setAccountCode('200');
                $line->setTaxType('OUTPUT');
                $xeroLineItems[] = $line;
            }
        }

        // ── 3. Build the Xero invoice ──
        $xeroInvoice = new XeroInvoice();
        $xeroInvoice->setType(XeroInvoice::TYPE_ACCREC);
        $xeroInvoice->setContact($contact);
        $xeroInvoice->setLineItems($xeroLineItems);
        $xeroInvoice->setLineAmountTypes(LineAmountTypes::EXCLUSIVE);
        $xeroInvoice->setDate(new \DateTime($invoice->issued_date->format('Y-m-d')));
        $xeroInvoice->setDueDate(new \DateTime($invoice->due_date->format('Y-m-d')));
        $xeroInvoice->setReference($invoice->invoice_number);
        $xeroInvoice->setStatus(XeroInvoice::STATUS_AUTHORISED);

        $invoicesWrapper = new Invoices();
        $invoicesWrapper->setInvoices([$xeroInvoice]);

        $result         = $api->createInvoices($tenantId, $invoicesWrapper);
        $createdInvoice = $result->getInvoices()[0];
        $invoiceId      = $createdInvoice->getInvoiceId();

        if (!$invoiceId || $invoiceId === '00000000-0000-0000-0000-000000000000') {
            $validationErrors = $createdInvoice->getValidationErrors() ?? [];
            $messages = [];
            foreach ($validationErrors as $err) {
                $messages[] = method_exists($err, 'getMessage') ? $err->getMessage() : (string) $err;
            }
            $detail = !empty($messages)
                ? implode(' | ', $messages)
                : 'Xero returned a null invoice ID.';

            \Illuminate\Support\Facades\Log::error('Xero completed-invoice null UUID', [
                'invoice_number'    => $invoice->invoice_number,
                'validation_errors' => $messages,
            ]);

            throw new \Exception("Xero rejected the completed invoice: {$detail}");
        }

        return [
            'xero_invoice_id'     => $invoiceId,
            'xero_invoice_number' => $createdInvoice->getInvoiceNumber(),
            'xero_status'         => $createdInvoice->getStatus(),
        ];
    }
}