<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use XeroAPI\XeroPHP\Api\AccountingApi;
use XeroAPI\XeroPHP\Api\IdentityApi;  // Changed from ConnectionsApi
use XeroAPI\XeroPHP\Configuration;
use XeroAPI\XeroPHP\Models\Accounting\Invoice;
use XeroAPI\XeroPHP\Models\Accounting\Invoices;  // Added - wrapper object
use XeroAPI\XeroPHP\Models\Accounting\LineItem;  // Changed from InvoiceLine
use XeroAPI\XeroPHP\Models\Accounting\Contact;

class XeroInvoiceController extends Controller
{
    // Step 1: Retrieve the Tenant ID
    public function getTenantId(Request $request)
    {
        $accessToken = $request->bearerToken();
        if (!$accessToken) {
            return response()->json(['error' => 'Access token missing or expired'], 400);
        }

        try {
            $config = Configuration::getDefaultConfiguration()->setAccessToken($accessToken);

            // Use IdentityApi instead of ConnectionsApi
            $identityApi = new IdentityApi(
                new Client(),
                $config
            );

            // Get tenant connections
            $tenantConnections = $identityApi->getConnections();

            if (isset($tenantConnections[0])) {
                $tenantId = $tenantConnections[0]->getTenantId();
                return response()->json(['tenantId' => $tenantId], 200);
            } else {
                return response()->json(['error' => 'No Xero organizations found for this account.'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Step 2: Create an Invoice in Xero using Tenant ID
    public function createInvoice(Request $request)
    {
        $request->validate([
            'client_name' => 'required|string',
            'description' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'unit_amount' => 'required|numeric|min:0',
        ]);

        $accessToken = $request->bearerToken();
        if (!$accessToken) {
            return response()->json(['error' => 'Access token missing or expired'], 400);
        }

        try {
            $config = Configuration::getDefaultConfiguration()->setAccessToken($accessToken);

            // Use IdentityApi instead of ConnectionsApi
            $identityApi = new IdentityApi(
                new Client(),
                $config
            );

            $tenantConnections = $identityApi->getConnections();

            if (isset($tenantConnections[0])) {
                $tenantId = $tenantConnections[0]->getTenantId();
            } else {
                return response()->json(['error' => 'No Xero organizations found for this account.'], 404);
            }

            // Set the contact (client) details
            $contact = new Contact();
            $contact->setName($request->input('client_name'));

            // Use LineItem instead of InvoiceLine
            $lineItem = new LineItem();
            $lineItem->setDescription($request->input('description', 'Sample Product'));
            $lineItem->setQuantity($request->input('quantity', 1));
            $lineItem->setUnitAmount($request->input('unit_amount', 100));
            $lineItem->setAccountCode('200');  // String, not integer

            $lineItems = [];
            array_push($lineItems, $lineItem);

            // Create the invoice
            $invoice = new Invoice();
            $invoice->setType(Invoice::TYPE_ACCREC);  // Use constant
            $invoice->setDueDate(new \DateTime('+30 days'));
            $invoice->setContact($contact);
            $invoice->setLineItems($lineItems);  // Pass as array
            $invoice->setStatus(Invoice::STATUS_DRAFT);  // Optional: set status

            // Wrap invoice in Invoices object
            $invoices = new Invoices();
            $invoices->setInvoices([$invoice]);

            // Create AccountingApi with GuzzleHttp\Client
            $accountingApi = new AccountingApi(
                new Client(),
                $config
            );

            // Use createInvoices (plural) with Invoices wrapper
            $result = $accountingApi->createInvoices($tenantId, $invoices);

            return response()->json([
                'message' => 'Invoice created successfully',
                'invoice' => $result->getInvoices()[0]
            ], 201);

        } catch (\XeroAPI\XeroPHP\ApiException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'response_body' => $e->getResponseBody()
            ], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}