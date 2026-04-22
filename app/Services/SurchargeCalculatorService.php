<?php
// FILE PATH: app/Services/SurchargeCalculatorService.php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\OrderItemDelivery;
use App\Models\Surcharge;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SurchargeCalculatorService
{
    protected ?Collection $byBillingCode = null;
    protected ?Collection $byId = null;

    /**
     * Lazy-load and cache active surcharges.
     */
    protected function loadSurcharges(): void
    {
        if ($this->byBillingCode !== null) return;

        $surcharges = Surcharge::where('is_active', true)
            ->whereNull('deleted_at')
            ->get();

        $this->byBillingCode = $surcharges->keyBy('billing_code');
        $this->byId          = $surcharges->keyBy('id');
    }

    /**
     * Detect whether a product is concrete vs. aggregate based on unit_of_measure.
     */
    public function isConcreteProduct($product): bool
    {
        if (!$product) return false;
        $unit = strtolower($product->unit_of_measure ?? '');
        return str_contains($unit, 'm3') || str_contains($unit, 'm³') || str_contains($unit, 'cubic');
    }

    /**
     * Calculate accumulated surcharges for a single delivery.
     *
     * @return array ['surcharges' => [...], 'surcharges_total' => float, 'trip_count' => int]
     */
    public function calculateForDelivery(OrderItemDelivery $delivery, OrderItem $orderItem): array
    {
        $this->loadSurcharges();

        $product = $orderItem->product;
        if (!$product) {
            return ['surcharges' => [], 'surcharges_total' => 0.00, 'trip_count' => 0];
        }

        $isConcrete = $this->isConcreteProduct($product);
        $trips      = $this->expandTrips($delivery);

        // Accumulate per billing code across all trips
        $accumulated = [];

        foreach ($trips as $trip) {
            $tripSurcharges = $this->calculateTripSurcharges($trip, $delivery, $isConcrete);
            foreach ($tripSurcharges as $s) {
                $match = $this->byId->get($s['surcharge_id']);
                $code  = $match?->billing_code ?? $s['surcharge_id'];
                if (!isset($accumulated[$code])) {
                    $accumulated[$code] = [
                        'surcharge_id'      => $s['surcharge_id'],
                        'billing_code'      => $match?->billing_code,
                        'name'              => $match?->name,
                        'amount_snapshot'   => $s['amount_snapshot'],
                        'calculated_amount' => 0,
                    ];
                }
                $accumulated[$code]['calculated_amount'] += $s['calculated_amount'];
            }
        }

        $surcharges = array_map(function ($s) {
            $s['calculated_amount'] = round($s['calculated_amount'], 2);
            return $s;
        }, array_values($accumulated));

        $total = round(collect($surcharges)->sum('calculated_amount'), 2);

        return [
            'surcharges'       => $surcharges,
            'surcharges_total' => $total,
            'trip_count'       => count($trips),
        ];
    }

    /**
     * Expand a delivery into individual trips based on load_size, quantity, and time_interval.
     * Trips spanning midnight cross day boundaries.
     */
    public function expandTrips(OrderItemDelivery $delivery): array
    {
        $loadSize     = (float) ($delivery->load_size ?? 0);
        $quantity     = (float) ($delivery->quantity ?? 0);
        $timeInterval = (int)   ($delivery->time_interval ?? 0);
        $startTime    = $delivery->getRawOriginal('delivery_time') ?? '08:00:00';
        $deliveryDate = $delivery->delivery_date?->format('Y-m-d');

        if ($loadSize <= 0 || $timeInterval <= 0) {
            return [[
                'date'      => $deliveryDate,
                'time'      => substr($startTime, 0, 5),
                'load_size' => $quantity,
            ]];
        }

        $trips     = (int) ceil($quantity / $loadSize);
        $result    = [];
        $startMins = $this->timeToMinutes(substr($startTime, 0, 5));

        for ($i = 0; $i < $trips; $i++) {
            $tripMins  = $startMins + ($i * $timeInterval);
            $dayOffset = (int) floor($tripMins / 1440);
            $minsInDay = $tripMins % 1440;

            $tripDate = Carbon::parse($deliveryDate)->addDays($dayOffset)->format('Y-m-d');
            $tripTime = sprintf('%02d:%02d', intdiv($minsInDay, 60), $minsInDay % 60);

            $isLast   = ($i === $trips - 1);
            $tripLoad = $isLast
                ? round($quantity - ($loadSize * ($trips - 1)), 4)
                : $loadSize;

            $result[] = [
                'date'      => $tripDate,
                'time'      => $tripTime,
                'load_size' => max($tripLoad, 0),
            ];
        }

        return $result;
    }

    /**
     * Calculate surcharges for a single trip.
     */
    public function calculateTripSurcharges(array $trip, OrderItemDelivery $delivery, bool $isConcrete): array
    {
        $this->loadSurcharges();
        $byBillingCode = $this->byBillingCode;

        $results  = [];
        $loadSize = (float) $trip['load_size'];
        if ($loadSize <= 0) return [];

        $date        = Carbon::parse($trip['date']);
        $dayOfWeek   = (int) $date->dayOfWeek; // 0=Sun, 6=Sat
        $timeMinutes = $this->timeToMinutes($trip['time']);

        // ── CONCRETE ────────────────────────────────────────────────────────
        if ($isConcrete) {

            // 1. Environmental Levy — always per m³
            if ($s = $byBillingCode->get('EL-017'))
                $results[] = ['surcharge_id' => $s->id, 'amount_snapshot' => $s->amount,
                    'calculated_amount' => round($loadSize * $s->amount, 2)];

            // 2. Minimum Cartage — load < 4m³
            if ($loadSize < 4.0 && ($s = $byBillingCode->get('MCART')))
                $results[] = ['surcharge_id' => $s->id, 'amount_snapshot' => $s->amount,
                    'calculated_amount' => round((4.0 - $loadSize) * $s->amount, 2)];

            // 3. Sunday & Public Holiday — covers Sunday + Monday 00:00–04:00
            if ($dayOfWeek === 0 || ($dayOfWeek === 1 && $timeMinutes < 240)) {
                if ($s = $byBillingCode->get('PH-003'))
                    $results[] = ['surcharge_id' => $s->id, 'amount_snapshot' => $s->amount,
                        'calculated_amount' => round($loadSize * $s->amount, 2)];
            }
            // 4. Saturday time bands
            elseif ($dayOfWeek === 6) {
                $code = match(true) {
                    $timeMinutes < 360  => 'AH-007B',
                    $timeMinutes < 720  => 'SD-002A',
                    $timeMinutes < 960  => 'SD-002B',
                    default             => 'SD-002C',
                };
                if ($s = $byBillingCode->get($code))
                    $results[] = ['surcharge_id' => $s->id, 'amount_snapshot' => $s->amount,
                        'calculated_amount' => round($loadSize * $s->amount, 2)];
            }
            // 5. Weekday after hours
            elseif ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
                $ahCode = match(true) {
                    $timeMinutes >= 960 && $timeMinutes < 1080 => 'AH-007A',
                    $timeMinutes >= 1080 || $timeMinutes < 240 => 'AH-007B',
                    default => null,
                };
                if ($ahCode && ($s = $byBillingCode->get($ahCode)))
                    $results[] = ['surcharge_id' => $s->id, 'amount_snapshot' => $s->amount,
                        'calculated_amount' => round($loadSize * $s->amount, 2)];
            }

            // 6. Accelerator
            $accelCode = ['low' => 'ACCEL-LOW', 'medium' => 'ACCEL-MED', 'high' => 'ACCEL-HIGH'][$delivery->accelerator_type] ?? null;
            if ($accelCode && ($s = $byBillingCode->get($accelCode)))
                $results[] = ['surcharge_id' => $s->id, 'amount_snapshot' => $s->amount,
                    'calculated_amount' => round($loadSize * $s->amount, 2)];

            // 7. Retarder
            $retardCode = ['low' => 'RETARD-LOW', 'medium' => 'RETARD-MED', 'high' => 'RETARD-HIGH'][$delivery->retarder_type] ?? null;
            if ($retardCode && ($s = $byBillingCode->get($retardCode)))
                $results[] = ['surcharge_id' => $s->id, 'amount_snapshot' => $s->amount,
                    'calculated_amount' => round($loadSize * $s->amount, 2)];

            // 8. Small Aggregate Premium
            $sapCode = ['10mm' => 'SAP-006A', '7mm' => 'SAP-006B'][$delivery->aggregate_size] ?? null;
            if ($sapCode && ($s = $byBillingCode->get($sapCode)))
                $results[] = ['surcharge_id' => $s->id, 'amount_snapshot' => $s->amount,
                    'calculated_amount' => round($loadSize * $s->amount, 2)];

            // 9. Slump Modification
            if ($delivery->slump_value && (float) $delivery->slump_value > 80) {
                $increments = floor(((float) $delivery->slump_value - 80) / 20);
                if ($increments > 0 && ($s = $byBillingCode->get('SM-007')))
                    $results[] = ['surcharge_id' => $s->id, 'amount_snapshot' => $s->amount,
                        'calculated_amount' => round($increments * $loadSize * $s->amount, 2)];
            }

            // 10. Oxide / Fibre
            if (!empty($delivery->oxide_fibre) && ($s = $byBillingCode->get('HMW-008')))
                $results[] = ['surcharge_id' => $s->id, 'amount_snapshot' => $s->amount,
                    'calculated_amount' => round($loadSize * $s->amount, 2)];
        }

        // ── AGGREGATES ───────────────────────────────────────────────────────
        else {

            // 11. Environmental Levy — always per tonne
            if ($s = $byBillingCode->get('AG-EL-006'))
                $results[] = ['surcharge_id' => $s->id, 'amount_snapshot' => $s->amount,
                    'calculated_amount' => round($loadSize * $s->amount, 2)];

            // 12. Out of Hours
            $isOOH = $dayOfWeek === 0
                || ($dayOfWeek === 6 && ($timeMinutes >= 720 || $timeMinutes < 360))
                || ($dayOfWeek >= 1 && $dayOfWeek <= 5 && ($timeMinutes >= 1080 || $timeMinutes < 360));
            if ($isOOH && ($s = $byBillingCode->get('AG-OOH-003')))
                $results[] = ['surcharge_id' => $s->id, 'amount_snapshot' => $s->amount,
                    'calculated_amount' => round($loadSize * $s->amount, 2)];

            // 13. Minimum Cartage
            $truckType = strtolower($delivery->truck_type ?? '');
            $minLoad   = match(true) {
                in_array($truckType, ['mini_truck', 'small_truck', 'body_truck']) => 12.0,
                in_array($truckType, ['8_wheeler', 'truck_and_dog'])              => 25.0,
                default => null,
            };
            $mcart = $byBillingCode->get('AG-MC-004');
            if ($minLoad !== null && $loadSize < $minLoad && $mcart && $mcart->amount > 0)
                $results[] = ['surcharge_id' => $mcart->id, 'amount_snapshot' => $mcart->amount,
                    'calculated_amount' => round(($minLoad - $loadSize) * $mcart->amount, 2)];

            // 14. Paver Delivery
            if (!empty($delivery->paver_delivery) && ($s = $byBillingCode->get('AG-AGP-008')))
                $results[] = ['surcharge_id' => $s->id, 'amount_snapshot' => $s->amount,
                    'calculated_amount' => round($loadSize * $s->amount, 2)];

            // 15. OMC Conditioning
            if (!empty($delivery->omc_conditioning) && ($s = $byBillingCode->get('AG-OMC-009')))
                $results[] = ['surcharge_id' => $s->id, 'amount_snapshot' => $s->amount,
                    'calculated_amount' => round($loadSize * $s->amount, 2)];

            // 16. Additional Stabiliser
            if (!empty($delivery->additional_stabiliser) && ($s = $byBillingCode->get('AG-SCT-010')))
                $results[] = ['surcharge_id' => $s->id, 'amount_snapshot' => $s->amount,
                    'calculated_amount' => round($loadSize * $s->amount, 2)];
        }

        return $results;
    }

    /**
     * Convert HH:MM time string to total minutes from midnight.
     */
    protected function timeToMinutes(string $time): int
    {
        $parts = explode(':', $time);
        return ((int) ($parts[0] ?? 0)) * 60 + ((int) ($parts[1] ?? 0));
    }
}