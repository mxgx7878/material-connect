<?php

namespace App\Services;

use App\Models\OrderItemDelivery;
use App\Models\ActionLog;
use Illuminate\Support\Facades\Auth;

class DeliveryStatusService
{
    /** Delivery lifecycle, in order (lowercase — matches the DB column). */
    public const LIFECYCLE = [
        'scheduled',
        'invoiced',
        'paid',
        'ordered_with_supplier',
        'out_for_delivery',
        'delivered',
        'client_confirmed',
    ];

    /** Non-linear edge statuses. */
    public const EDGE = [
        'delivery_issue',
        'cancelled',
    ];

    /** Finished — cannot progress further. */
    public const TERMINAL = ['client_confirmed', 'cancelled'];

    /** Allowed transitions: from => [to, ...]. */
    public const TRANSITIONS = [
        'scheduled'             => ['invoiced', 'cancelled'],
        'invoiced'              => ['paid', 'scheduled', 'cancelled'],          // back to scheduled if invoice voided
        'paid'                  => ['ordered_with_supplier', 'cancelled'],
        'ordered_with_supplier' => ['out_for_delivery', 'delivery_issue', 'cancelled'],
        'out_for_delivery'      => ['delivered', 'delivery_issue'],
        'delivered'             => ['client_confirmed', 'delivery_issue'],
        'client_confirmed'      => [],
        // edge
        'delivery_issue'        => ['out_for_delivery', 'delivered', 'cancelled'],
        'cancelled'             => [],
    ];

    /** Human-readable labels for UI. */
    public const LABELS = [
        'scheduled'             => 'Scheduled',
        'invoiced'              => 'Invoiced',
        'paid'                  => 'Paid',
        'ordered_with_supplier' => 'Ordered with Supplier',
        'out_for_delivery'      => 'Out for Delivery',
        'delivered'             => 'Delivered',
        'client_confirmed'      => 'Confirmed by Customer',
        'delivery_issue'        => 'Delivery Issue',
        'cancelled'             => 'Cancelled',
    ];

    public static function all(): array
    {
        return array_merge(self::LIFECYCLE, self::EDGE);
    }

    public static function isValid(string $status): bool
    {
        return in_array($status, self::all(), true);
    }

    public static function label(string $status): string
    {
        return self::LABELS[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }

    public static function canTransition(string $from, string $to): bool
    {
        if ($from === $to) return true;
        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    /**
     * Apply a delivery transition with guard + audit log. Throws on an illegal move.
     * After reaching 'client_confirmed', triggers the order-level rollup to Completed.
     */
    public static function apply(OrderItemDelivery $delivery, string $to, ?string $reason = null, ?int $userId = null): void
    {
        $from = (string) $delivery->status;

        if (!self::isValid($to)) {
            throw new \InvalidArgumentException("Unknown delivery status: {$to}");
        }
        if ($from === $to) {
            return;
        }
        if (!self::canTransition($from, $to)) {
            throw new \RuntimeException("Illegal delivery transition: {$from} → {$to}");
        }

        $delivery->status = $to;
        $delivery->save();

        ActionLog::create([
            'action'   => 'Delivery Status Changed',
            'details'  => "Delivery #{$delivery->id} (order #{$delivery->order_id}): {$from} → {$to}" . ($reason ? " ({$reason})" : ''),
            'order_id' => $delivery->order_id,
            'user_id'  => $userId ?? Auth::id(),
        ]);

        // When a delivery is confirmed by the customer, check if the whole order is done.
        if ($to === 'client_confirmed') {
            $order = $delivery->order()->first();
            if ($order) {
                OrderStatusService::syncOrderFromDeliveries($order, $userId);
            }
        }
    }
}