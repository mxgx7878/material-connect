<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Customer;
use App\Models\Orders;
use App\Models\User;

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

        if ($order->workflow !== 'Payment Requested') {
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
                $order->workflow = 'Delivered';
                $order->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Payment successful',
                    'order' => [
                        'id' => $order->id,
                        'payment_status' => $order->payment_status,
                        'workflow' => $order->workflow,
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
}