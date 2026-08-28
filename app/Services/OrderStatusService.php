<?php

namespace App\Services;

use App\Models\Orders;
use App\Models\ActionLog;
use Illuminate\Support\Facades\Auth;

class OrderStatusService
{
    /** Status a freshly submitted order starts at. */
    public const INITIAL = 'Received';

    /**
     * Order-level lifecycle, in order.
     * Delivery now lives entirely at the DELIVERY level (see DeliveryStatusService)
     * and is fully independent of both invoicing and order status. The order sits
     * in 'Processing' while deliveries play out; an admin marks it 'Completed'
     * explicitly (no automatic rollup from deliveries anymore).
     */
    public const LIFECYCLE = [
        'Received',
        'Under Review',
        'Confirming Supply',
        'Awaiting Customer Confirmation',
        'Processing',
        'Completed',
    ];

    /** Non-linear branch/edge statuses. */
    public const EDGE = [
        'Cancelled',
        'Supplier Unavailable',
        'Customer Action Required',
    ];

    /** Finished — cannot move on. */
    public const TERMINAL = ['Completed', 'Cancelled'];

    /** Order contents may still be edited at these stages (before customer confirms). */
    public const EDITABLE_STATUSES = [
        'Received', 'Under Review', 'Confirming Supply', 'Awaiting Customer Confirmation',
    ];

    /** At/after these the customer can no longer cancel the whole order. */
    public const NON_CANCELLABLE = [
        'Processing', 'Completed', 'Cancelled',
    ];

    /** Allowed transitions: from => [to, ...]. */
    public const TRANSITIONS = [
        'Received'                        => ['Under Review', 'Cancelled'],
        'Under Review'                    => ['Confirming Supply', 'Customer Action Required', 'Supplier Unavailable', 'Cancelled'],
        'Confirming Supply'               => ['Awaiting Customer Confirmation', 'Supplier Unavailable', 'Customer Action Required', 'Cancelled'],
        'Awaiting Customer Confirmation'  => ['Processing', 'Customer Action Required', 'Cancelled'],
        'Processing'                      => ['Completed', 'Customer Action Required', 'Cancelled'],
        'Completed'                       => [],
        // edge
        'Cancelled'                       => [],
        'Supplier Unavailable'            => ['Under Review', 'Cancelled'],
        'Customer Action Required'        => ['Under Review', 'Confirming Supply', 'Awaiting Customer Confirmation', 'Processing', 'Cancelled'],
    ];

    public static function all(): array
    {
        return array_merge(self::LIFECYCLE, self::EDGE);
    }

    public static function isValid(string $status): bool
    {
        return in_array($status, self::all(), true);
    }

    public static function canTransition(string $from, string $to): bool
    {
        if ($from === $to) return true;
        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    public static function isEditable(Orders $order): bool
    {
        return !$order->customer_confirmed
            && in_array($order->order_status, self::EDITABLE_STATUSES, true);
    }

    /**
     * Apply a transition with guard + audit log. Throws on an illegal move.
     */
    public static function apply(Orders $order, string $to, ?string $reason = null, ?int $userId = null): void
    {
        $from = (string) $order->order_status;

        if (!self::isValid($to)) {
            throw new \InvalidArgumentException("Unknown order status: {$to}");
        }
        if ($from === $to) {
            return;
        }
        if (!self::canTransition($from, $to)) {
            throw new \RuntimeException("Illegal status transition: {$from} → {$to}");
        }

        $order->order_status = $to;
        if ($reason !== null) {
            $order->reason = $reason;
        }
        $order->save();

        ActionLog::create([
            'action'   => 'Order Status Changed',
            'details'  => "Order #{$order->id} status: {$from} → {$to}" . ($reason ? " ({$reason})" : ''),
            'order_id' => $order->id,
            'user_id'  => $userId ?? Auth::id(),
        ]);
    }

    /**
     * Walk the linear lifecycle forward to $target, applying each legal step.
     * No-op if already at/past target, or on an edge state.
     */
    public static function fastForward(Orders $order, string $target, ?int $userId = null): void
    {
        $curIdx = array_search($order->order_status, self::LIFECYCLE, true);
        $tgtIdx = array_search($target, self::LIFECYCLE, true);
        if ($curIdx === false || $tgtIdx === false || $tgtIdx <= $curIdx) {
            return;
        }
        for ($i = $curIdx + 1; $i <= $tgtIdx; $i++) {
            self::apply($order, self::LIFECYCLE[$i], null, $userId);
        }
    }

    // NOTE: syncOrderFromDeliveries() has been removed.
    // Order completion is no longer derived from delivery statuses — an admin
    // marks the order 'Completed' explicitly via updateOrderStatus(). Delivery
    // confirmation (client_confirmed) is now purely a delivery-level concern.
}