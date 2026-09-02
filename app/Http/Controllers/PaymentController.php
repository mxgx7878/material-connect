<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Customer;
use App\Models\Orders;
use App\Models\User;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\OrderStatusService;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    public function processPayment(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required|string',
            'order_id' => 'required|integer',
        ]);

        $order = Orders::findOrFail($request->order_id);
        $user = auth()->user();

        if ($order->client_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($order->order_status !== 'Awaiting Payment') {
            return response()->json(['error' => 'Order not ready for payment'], 400);
        }

        try {
            // Get or create Stripe customer
            $customerId = $this->getOrCreateCustomer($user);

            // Create and confirm payment in one step
            $paymentIntent = PaymentIntent::create([
                'amount' => round($order->total_price * 100),
                'currency' => 'aud',
                'customer' => $customerId,
                'payment_method' => $request->payment_method_id,
                'confirm' => true,
                'automatic_payment_methods' => [
                   'enabled' => true,
                   'allow_redirects' => 'never', // <-- key part
                ],
                'metadata' => [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                ],
            ]);

            if ($paymentIntent->status === 'succeeded') {
                $order->payment_status = 'Paid';
                $order->save();

                // Advance the lifecycle: Awaiting Payment → Paid.
                // Delivery is handled as a separate, later stage.
                try {
                    OrderStatusService::apply($order, 'Paid', 'Payment received via Stripe', $user->id);
                } catch (\RuntimeException $e) {
                    Log::warning('Order already past Awaiting Payment at payment time', [
                        'order_id' => $order->id,
                        'status'   => $order->order_status,
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Payment successful',
                    'order' => [
                        'id' => $order->id,
                        'payment_status' => $order->payment_status,
                        'order_status'   => $order->order_status,
                    ]
                ]);
            } else {
                $order->payment_status = 'Failed';
                $order->save();

                return response()->json([
                    'success' => false,
                    'message' => 'Payment failed',
                    'status' => $paymentIntent->status
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getOrCreateCustomer(User $user)
    {
        if ($user->stripe_customer_id) {
            try {
                Customer::retrieve($user->stripe_customer_id);
                return $user->stripe_customer_id;
            } catch (\Exception $e) {
                // Customer doesn't exist, create new one
            }
        }

        $customer = Customer::create([
            'email' => $user->email,
            'name' => $user->name,
            'metadata' => [
                'user_id' => $user->id,
            ],
        ]);

        $user->stripe_customer_id = $customer->id;
        $user->save();

        return $customer->id;
    }


    /**
     * POST /api/client/invoices/{invoice_id}/pay
     *
     * Creates a Stripe invoice for the full total_amount, pays it immediately with
     * the supplied payment method, then — only if Stripe confirms it's paid — saves
     * the Stripe invoice id + payment intent id and marks the local invoice Paid.
     *
     * NOTE: amount_paid / balance_due are intentionally NOT touched here. Those are
     * reconciled on the Xero side, per the agreed split.
     *
     * Body:
     *   payment_method_id : string (required)  Stripe PaymentMethod id (pm_...)
     *   idempotency_key   : string (optional)  client-generated; stops a retry from
     *                                          creating a second Stripe invoice / charge
     */
    public function payInvoice(Request $request, $invoice_id)
    {
        $request->validate([
            'payment_method_id' => 'required|string',
            'idempotency_key'   => 'nullable|string|max:255',
        ]);

        $user    = auth()->user();
        $invoice = Invoice::with('order')->findOrFail($invoice_id);

        // ── Authorization: only the invoice's client can pay it ──
        if ($invoice->client_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only pay your own invoices.',
            ], 403);
        }

        // ── Status guards ──
        if ($invoice->status === 'Paid') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice is already marked as paid.',
            ], 422);
        }

        if (in_array($invoice->status, ['Cancelled', 'Void'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot pay a cancelled or void invoice.',
            ], 422);
        }

        $total = round((float) $invoice->total_amount, 2);
        if ($total <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'This invoice has no amount to charge.',
            ], 422);
        }

        $pmId = $request->payment_method_id;

        // ── 1. Charge via a Stripe invoice ──
        try {
            $customerId = $this->getOrCreateCustomer($user);

            // The payment method must be attached to the customer before it can pay an invoice.
            try {
                \Stripe\PaymentMethod::retrieve($pmId)->attach(['customer' => $customerId]);
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                // Ignore "already attached to this customer"; rethrow anything else.
                if (!str_contains($e->getMessage(), 'already been attached')) {
                    throw $e;
                }
            }

            $idemRoot      = $request->filled('idempotency_key') ? $request->idempotency_key : null;
            $stripeOptions = fn (string $suffix) => $idemRoot ? ['idempotency_key' => "{$idemRoot}:{$suffix}"] : [];

            // One line item for the full invoice total.
            \Stripe\InvoiceItem::create([
                'customer'    => $customerId,
                'amount'      => (int) round($total * 100),
                'currency'    => 'aud',
                'description' => "Payment for invoice {$invoice->invoice_number}",
            ], $stripeOptions('item'));

            // Create the Stripe invoice (pulls in the pending item above).
            $stripeInvoice = \Stripe\Invoice::create([
                'customer'                       => $customerId,
                'collection_method'              => 'charge_automatically',
                'auto_advance'                   => false, // finalize/pay manually = synchronous result
                'pending_invoice_items_behavior' => 'include',
                'default_payment_method'         => $pmId,
                'metadata' => [
                    'invoice_id'     => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'order_id'       => $invoice->order_id,
                    'user_id'        => $user->id,
                ],
            ], $stripeOptions('invoice'));

            // Finalize, then pay on-session so SCA can resolve inline when needed.
            $stripeInvoice = $stripeInvoice->finalizeInvoice();
            $stripeInvoice = $stripeInvoice->pay([
                'payment_method' => $pmId,
                'off_session'    => false,
            ]);
        } catch (\Stripe\Exception\CardException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getError()->message ?? 'Your card was declined.',
            ], 402);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

        // ── 2. Bail unless Stripe actually marked the invoice paid ──
        if ($stripeInvoice->status !== 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Payment was not completed.',
                'status'  => $stripeInvoice->status, // e.g. open (needs action), uncollectible
            ], 402);
        }

        $stripeInvoiceId = $stripeInvoice->id;
        $paymentIntentId = $stripeInvoice->payment_intent ?? null;

        // Fallback: newer API versions expose the PI via invoice.payments rather than payment_intent.
        if (empty($paymentIntentId)) {
            try {
                $refetched = \Stripe\Invoice::retrieve([
                    'id'     => $stripeInvoiceId,
                    'expand' => ['payments.data.payment'],
                ]);
                foreach (($refetched->payments->data ?? []) as $p) {
                    $pi = $p->payment->payment_intent ?? null;
                    if ($pi) {
                        $paymentIntentId = is_string($pi) ? $pi : ($pi->id ?? null);
                        break;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Could not resolve Stripe PaymentIntent id from invoice payments', [
                    'stripe_invoice_id' => $stripeInvoiceId,
                    'error'             => $e->getMessage(),
                ]);
            }
        }

        // ── 3. Save ids + flip status to Paid (locked so concurrent requests can't double-apply) ──
        $order = DB::transaction(function () use ($invoice, $stripeInvoiceId, $paymentIntentId, $total, $user) {
            $locked = Invoice::whereKey($invoice->id)->lockForUpdate()->first();

            // A concurrent request already settled it — leave it alone (idempotency key guards the charge).
            if ($locked->status === 'Paid') {
                $invoice->refresh();
                return $locked->order;
            }

            $locked->update([
                'status'                   => 'Paid',
                'paid_at'                  => now(),
                'stripe_payment_intent_id' => $paymentIntentId,
                'stripe_invoice_id'        => $stripeInvoiceId,
                // amount_paid / balance_due left to Xero reconciliation.
            ]);

         
            $linkedDeliveries = \App\Models\OrderItemDelivery::where('invoice_id', $locked->id)
                ->whereIn('status', ['scheduled', 'invoiced'])
                ->get();

            foreach ($linkedDeliveries as $delivery) {
                if ($delivery->status === 'scheduled') {
                    \App\Services\DeliveryStatusService::apply(
                        $delivery,
                        'scheduled',
                        "Invoice {$locked->invoice_number} paid via Stripe (auto-invoiced)",
                        $user->id
                    );
                }

                \App\Services\DeliveryStatusService::apply(
                    $delivery,
                    'scheduled',
                    "Invoice {$locked->invoice_number} paid via Stripe",
                    $user->id
                );
            }

            // Keep the parent order's payment_status in sync (drop this block if Xero owns it too).
            $order       = $locked->order;
            $allInvoices = $order->invoices()->get();
            $paid        = $allInvoices->where('status', 'Paid')->count();
            $order->update([
                'payment_status' => $paid === 0
                    ? 'Pending'
                    : ($paid === $allInvoices->count() ? 'Paid' : 'Partially Paid'),
            ]);

            if (class_exists(\App\Models\ActionLog::class)) {
                \App\Models\ActionLog::create([
                    'order_id' => $order->id,
                    'user_id'  => $user->id,
                    'action'   => 'Invoice Paid',
                    'details'  => "Invoice {$locked->invoice_number} paid via Stripe. "
                                . "Stripe invoice: {$stripeInvoiceId}, payment: " . ($paymentIntentId ?? 'n/a')
                                . ". Amount: \$" . number_format($total, 2),
                ]);
            }

            $invoice->refresh();
            return $order;
        });

        // ── 4. Response ──
        return response()->json([
            'success' => true,
            'message' => 'Invoice paid successfully.',
            'data' => [
                'invoice' => [
                    'id'             => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'status'         => $invoice->status,
                    'paid_at'        => $invoice->paid_at?->toISOString(),
                ],
                'order' => [
                    'id'             => $order->id,
                    'payment_status' => $order->payment_status,
                ],
                'payment' => [
                    'stripe_invoice_id'        => $stripeInvoiceId,
                    'stripe_payment_intent_id' => $paymentIntentId,
                    'amount'                   => $total,
                    'currency'                 => 'aud',
                    'status'                   => $stripeInvoice->status,
                ],
            ],
        ]);
    }
}