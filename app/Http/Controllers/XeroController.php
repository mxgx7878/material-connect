<?php

namespace App\Http\Controllers;

use App\Services\XeroService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class XeroController extends Controller
{
    private XeroService $xeroService;

    public function __construct(XeroService $xeroService)
    {
        $this->xeroService = $xeroService;
    }

    /**
     * Step 1: Redirect to Xero for authorization
     * GET /api/xero/authorize (open in browser)
     */
    public function authorize()
    {
        $auth = $this->xeroService->getAuthorizationUrl();
        Cache::put('xero_oauth_state', $auth['state'], now()->addMinutes(10));
        return redirect($auth['url']);
    }

    /**
     * Step 2: Handle callback from Xero
     * GET /api/xero/callback
     */
    public function callback(Request $request)
    {
        $storedState = Cache::get('xero_oauth_state');

        if (!$storedState || $request->get('state') !== $storedState) {
            return response()->json(['error' => 'Invalid state'], 400);
        }

        Cache::forget('xero_oauth_state');

        if (!$request->has('code')) {
            return response()->json(['error' => 'No authorization code'], 400);
        }

        try {
            $token = $this->xeroService->handleCallback($request->get('code'));

            return response()->json([
                'message'     => '✅ Successfully connected to Xero!',
                'tenant_id'   => $token->tenant_id,
                'tenant_name' => $token->tenant_name,
                'expires_at'  => $token->expires_at->toDateTimeString(),
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Check connection status
     * GET /api/xero/status
     */
    public function status()
    {
        try {
            if (!$this->xeroService->isConnected()) {
                return response()->json([
                    'connected' => false,
                    'message'   => 'Not connected. Visit /api/xero/authorize in browser.',
                ]);
            }

            $token = $this->xeroService->getValidToken();

            return response()->json([
                'connected'   => true,
                'tenant_id'   => $token->tenant_id,
                'tenant_name' => $token->tenant_name,
                'expires_at'  => $token->expires_at->toDateTimeString(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'connected' => false,
                'error'     => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create Invoice - USE THIS FROM POSTMAN
     * POST /api/xero/invoice
     * 
     * Body:
     * {
     *   "customer_name": "John Doe",
     *   "customer_email": "john@example.com",
     *   "items": [
     *     {
     *       "description": "Product A",
     *       "quantity": 2,
     *       "unit_price": 50.00
     *     }
     *   ],
     *   "reference": "Order #123",
     *   "due_date": "+30 days"
     * }
     */
    public function createInvoice(Request $request)
    {
        $request->validate([
            'customer_name'           => 'required|string',
            'customer_email'          => 'nullable|email',
            'items'                   => 'required|array|min:1',
            'items.*.description'     => 'required|string',
            'items.*.quantity'        => 'required|numeric|min:1',
            'items.*.unit_price'      => 'required|numeric|min:0',
            'reference'               => 'nullable|string',
            'due_date'                => 'nullable|string',
        ]);

        try {
            $result = $this->xeroService->createInvoice($request->all());

            return response()->json([
                'message' => '✅ Invoice created successfully!',
                'invoice' => $result,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Failed to create invoice',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}