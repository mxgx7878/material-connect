// src/components/client/orders/ClientOrderCostingTab.tsx

import React, { useState, useMemo, useEffect  } from 'react';
import {
  Calculator, Truck, Calendar, CheckCircle,
  Loader2, Clock, Package, DollarSign,
} from 'lucide-react';
import toast from 'react-hot-toast';
import { useCalculateCosting } from '../../features/clientOrders/hooks';
import type { ClientOrderItem, ClientOrder } from '../../types/clientOrder.types';
// ==================== TYPES ====================
interface SavedTestingFee {
  id: number;
  testing_fee_id: number;
  billing_code: string | null;
  name: string;
  amount_snapshot: number;
}

interface SurchargeResult {
  surcharge_id: number;
  billing_code: string;
  name: string;
  calculated_amount: number;
}

interface ItemServiceResult {
  order_item_id: number;
  deliveries: Array<{
    delivery_id: number;
    delivery_date: string;
    delivery_time: string;
    truck_type: string | null;
    load_size: number;
    trip_count: number;
    surcharges: SurchargeResult[];
    surcharges_total: number;
    saved_testing_fees: SavedTestingFee[];
    testing_fees_total: number;
  }>;
}

interface DeliveryVM {
  deliveryId: number;
  itemId: number;
  deliveryDate: string;
  deliveryTime: string;
  productName: string;
  unitOfMeasure: string;
  quantity: number;
  totalItemQty: number;    // total qty for this order item (for ratio)
  truckType: string | null;
  loadSize: number;
  customerItemCostShare: number;  // proportional share of customer_item_cost
  customerDeliveryCostShare: number;
}

interface ClientOrderCostingTabProps {
  orderId: number;
  order: ClientOrder;
  items: ClientOrderItem[];
}

// ==================== HELPERS ====================
const toNum = (val: unknown, fallback = 0): number => {
  if (val === null || val === undefined) return fallback;
  const n = typeof val === 'number' ? val : parseFloat(String(val));
  return Number.isFinite(n) ? n : fallback;
};

const fmt = (n: number) =>
  `$${n.toLocaleString('en-AU', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const to12h = (t: string | null | undefined): string => {
  if (!t) return '—';
  const [hStr, mStr] = t.substring(0, 5).split(':');
  const h = parseInt(hStr, 10), m = parseInt(mStr, 10);
  if (isNaN(h) || isNaN(m)) return t;
  return `${h % 12 || 12}:${String(m).padStart(2, '0')} ${h >= 12 ? 'PM' : 'AM'}`;
};

const formatShortDate = (dateStr: string) => {
  if (!dateStr || dateStr.length < 10) return '—';
  try {
    return new Date(dateStr.substring(0, 10) + 'T00:00:00').toLocaleDateString('en-AU', {
      weekday: 'short', day: 'numeric', month: 'short', year: 'numeric',
    });
  } catch { return dateStr; }
};

function buildDeliveryVMs(items: ClientOrderItem[], orderCustomerItemCost: number, orderCustomerDeliveryCost: number): DeliveryVM[] {
  const vms: DeliveryVM[] = [];

  // Total qty across all items for proportional distribution
  const totalOrderQty = items.reduce((sum, item) => sum + toNum(item.quantity, 0), 0);

  for (const item of items) {
    const itemQty = toNum(item.quantity, 0);
    const deliveries = item.deliveries || [];
    const deliveryCount = deliveries.length || 1;

    // This item's proportional share of order customer_item_cost
    const itemRatio = totalOrderQty > 0 ? itemQty / totalOrderQty : 0;
    const itemCustomerCost = orderCustomerItemCost * itemRatio;
    const itemDeliveryCost = orderCustomerDeliveryCost * itemRatio;

    for (const d of deliveries) {
      const dQty = toNum(d.quantity, 0);
      const qtyRatio = itemQty > 0 ? dQty / itemQty : 1 / deliveryCount;

      vms.push({
        deliveryId: d.id,
        itemId: item.id,
        deliveryDate: (d.delivery_date ?? '').substring(0, 10),
        deliveryTime: d.delivery_time ?? '',
        productName: item.product?.product_name ?? 'Product',
        unitOfMeasure: item.product?.unit_of_measure ?? '',
        quantity: dQty,
        totalItemQty: itemQty,
        truckType: d.truck_type ?? null,
        loadSize: toNum(d.load_size, 0),
        customerItemCostShare: itemCustomerCost * qtyRatio,
        customerDeliveryCostShare: itemDeliveryCost * qtyRatio,
      });
    }
  }

  return vms.sort((a, b) =>
    (a.deliveryDate + a.deliveryTime).localeCompare(b.deliveryDate + b.deliveryTime)
  );
}

function groupByDate(vms: DeliveryVM[]): Map<string, DeliveryVM[]> {
  const map = new Map<string, DeliveryVM[]>();
  for (const vm of vms) {
    if (!map.has(vm.deliveryDate)) map.set(vm.deliveryDate, []);
    map.get(vm.deliveryDate)!.push(vm);
  }
  return map;
}

// ==================== COMPONENT ====================
const ClientOrderCostingTab: React.FC<ClientOrderCostingTabProps> = ({ orderId, order, items }) => {
  const [selectedIds, setSelectedIds] = useState<Set<number>>(new Set());
  const [surchargeMap, setSurchargeMap] = useState<Map<number, { surcharges: SurchargeResult[]; tripCount: number }>>(new Map());
  const initialTestingFeeMap = useMemo(() => {
  const map = new Map<number, SavedTestingFee[]>();
    for (const item of items) {
      for (const d of (item.deliveries || [])) {
        const fees = d.saved_testing_fees;
        if (fees && fees.length > 0) map.set(d.id, fees);
      }
    }
    return map;
  }, [items]);

  const [testingFeeMap, setTestingFeeMap] = useState<Map<number, SavedTestingFee[]>>(initialTestingFeeMap);

  // Sync when items prop updates (e.g. after refetch)
  useEffect(() => {
    setTestingFeeMap(initialTestingFeeMap);
  }, [initialTestingFeeMap]);
  const [hasCalculated, setHasCalculated] = useState(false);

  const { mutate: calculate, isPending: isCalculating } = useCalculateCosting(orderId);

  const customerItemCost = toNum(order.customer_item_cost, 0);              // net of material discount
  const customerItemGross = toNum((order as any).customer_item_gross, customerItemCost);
  const materialDiscountTotal = toNum((order as any).material_discount_total, 0);
  const customerDeliveryCost = toNum(order.customer_delivery_cost, 0);
  const gst = toNum(order.gst_tax, 0);
  const discount = toNum(order.discount, 0);
  const otherCharges = toNum((order as any).other_charges, 0);
  const totalPrice = toNum(order.total_price, 0);

  const deliveryVMs = useMemo(
    () => buildDeliveryVMs(items, customerItemCost, customerDeliveryCost),
    [items, customerItemCost, customerDeliveryCost]
  );
  const grouped = useMemo(() => groupByDate(deliveryVMs), [deliveryVMs]);

  // Surcharges + testing totals across selected slots
  const surchargesGrandTotal = useMemo(() => {
    let total = 0;
    for (const vm of deliveryVMs) {
      const s = surchargeMap.get(vm.deliveryId);
      if (s) total += s.surcharges.reduce((acc, x) => acc + x.calculated_amount, 0);
    }
    return total;
  }, [deliveryVMs, surchargeMap]);

  const testingGrandTotal = useMemo(() => {
    let total = 0;
    for (const [, fees] of testingFeeMap) {
      total += fees.reduce((acc, f) => acc + f.amount_snapshot, 0);
    }
    return total;
  }, [testingFeeMap]);

  const selectionSurchargeTotal = useMemo(() => {
    let surcharges = 0, testing = 0;
    for (const vm of deliveryVMs) {
      if (!selectedIds.has(vm.deliveryId)) continue;
      const s = surchargeMap.get(vm.deliveryId);
      if (s) surcharges += s.surcharges.reduce((acc, x) => acc + x.calculated_amount, 0);
      const t = testingFeeMap.get(vm.deliveryId);
      if (t) testing += t.reduce((acc, x) => acc + x.amount_snapshot, 0);
    }
    return { surcharges, testing };
  }, [deliveryVMs, selectedIds, surchargeMap, testingFeeMap]);

  const toggleId = (id: number) =>
    setSelectedIds((prev) => { const n = new Set(prev); n.has(id) ? n.delete(id) : n.add(id); return n; });

  const toggleAll = () =>
    setSelectedIds(selectedIds.size === deliveryVMs.length ? new Set() : new Set(deliveryVMs.map((d) => d.deliveryId)));

  const toggleDay = (date: string) => {
    const ids = (grouped.get(date) ?? []).map((d) => d.deliveryId);
    const allOn = ids.every((id) => selectedIds.has(id));
    setSelectedIds((prev) => {
      const n = new Set(prev);
      ids.forEach((id) => allOn ? n.delete(id) : n.add(id));
      return n;
    });
  };

  const handleCalculate = () => {
    if (!selectedIds.size) return;
    calculate(Array.from(selectedIds), {
      onSuccess: (res) => {
        const newSurcharges = new Map(surchargeMap);
        const newTesting = new Map(testingFeeMap);
        (res.data.items as unknown as ItemServiceResult[]).forEach((item) =>
          item.deliveries.forEach((d) => {
            newSurcharges.set(d.delivery_id, { surcharges: d.surcharges, tripCount: d.trip_count });
            if (!newTesting.has(d.delivery_id) && d.saved_testing_fees?.length) {
              newTesting.set(d.delivery_id, d.saved_testing_fees);
            }
          })
        );
        setSurchargeMap(newSurcharges);
        setTestingFeeMap(newTesting);
        setHasCalculated(true);
      },
      onError: () => toast.error('Failed to calculate surcharges'),
    });
  };

  return (
    <div className="space-y-5">

      {/* ── SECTION 1: ORDER COST SUMMARY CARD ── */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
        {/* Items Cost */}
        <div className="bg-blue-50 border border-blue-200 rounded-xl p-4">
          <p className="text-xs font-bold text-blue-600 uppercase tracking-wide mb-1">Items Cost</p>
          <p className="text-xl font-bold text-blue-800">{fmt(customerItemCost)}</p>
          <p className="text-xs text-blue-500 mt-1">
            {items.length} product{items.length !== 1 ? 's' : ''}
            {materialDiscountTotal > 0 && (
              <span className="text-emerald-600"> · Material Discount −{fmt(materialDiscountTotal)} applied</span>
            )}
          </p>
        </div>

        {/* Delivery */}
        <div className="bg-green-50 border border-green-200 rounded-xl p-4">
          <p className="text-xs font-bold text-green-600 uppercase tracking-wide mb-1">Delivery</p>
          <p className="text-xl font-bold text-green-800">
            {customerDeliveryCost > 0 ? fmt(customerDeliveryCost) : 'Included'}
          </p>
          <p className="text-xs text-green-500 mt-1">{deliveryVMs.length} slot{deliveryVMs.length !== 1 ? 's' : ''}</p>
        </div>

        {/* Order Total */}
        <div className="bg-gray-900 rounded-xl p-4">
          <p className="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Order Total</p>
          <p className="text-xl font-bold text-white">{fmt(totalPrice)}</p>
          <div className="text-xs text-gray-400 mt-1 space-y-0.5">
            <div className="flex justify-between">
              <span>Items</span>
              <span>{fmt(customerItemGross)}</span>
            </div>
            {materialDiscountTotal > 0 && (
              <div className="flex justify-between text-emerald-400">
                <span>Material Discount</span>
                <span>-{fmt(materialDiscountTotal)}</span>
              </div>
            )}
            <div className="flex justify-between">
              <span>Delivery</span>
              <span>{fmt(customerDeliveryCost)}</span>
            </div>
            {surchargesGrandTotal > 0 && (
              <div className="flex justify-between text-amber-300">
                <span>Surcharges</span>
                <span>{fmt(surchargesGrandTotal)}</span>
              </div>
            )}
            {testingGrandTotal > 0 && (
              <div className="flex justify-between text-teal-300">
                <span>Testing</span>
                <span>{fmt(testingGrandTotal)}</span>
              </div>
            )}
            <div className="flex justify-between">
              <span>GST (10%)</span>
              <span>{fmt(gst)}</span>
            </div>
            {discount > 0 && (
              <div className="flex justify-between text-red-400">
                <span>Discount</span>
                <span>-{fmt(discount)}</span>
              </div>
            )}
            {otherCharges > 0 && (
              <div className="flex justify-between">
                <span>Other</span>
                <span>{fmt(otherCharges)}</span>
              </div>
            )}
          </div>
        </div>
      </div>

      {/* ── SECTION 2: ITEMS BREAKDOWN ── */}
      <div className="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div className="px-4 py-3 bg-gray-50 border-b border-gray-200">
          <p className="font-semibold text-gray-900 text-sm flex items-center gap-2">
            <Package size={15} className="text-purple-600" />
            Items Breakdown
          </p>
        </div>
        <div className="divide-y divide-gray-100">
          {items.map((item) => {
            const itemQty = toNum(item.quantity, 0);
            // Prefer backend per-item pricing (unified PricingService); fall back
            // to proportional distribution for older payloads.
            const totalOrderQty = items.reduce((sum, i) => sum + toNum(i.quantity, 0), 0);
            const itemRatio = totalOrderQty > 0 ? itemQty / totalOrderQty : 0;
            const itemCost = toNum((item as any).customer_item_total, customerItemCost * itemRatio);
            const itemMatDiscount = toNum((item as any).material_discount, 0);
            const itemUnitPrice = toNum((item as any).customer_unit_price, 0);
            const itemDelivery = (item.deliveries || []).reduce(
              (s, d: any) => s + toNum(d.customer_delivery_cost, toNum(d.delivery_cost, 0) * 1.1), 0
            ) || customerDeliveryCost * itemRatio;

            return (
              <div key={item.id} className="px-4 py-3 flex items-center justify-between">
                <div className="flex items-center gap-3">
                  <div className="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <Package size={14} className="text-purple-600" />
                  </div>
                  <div>
                    <p className="text-sm font-semibold text-gray-900">{item.product?.product_name ?? 'Product'}</p>
                    <p className="text-xs text-gray-500">
                      {itemQty} {item.product?.unit_of_measure ?? ''}
                      {itemUnitPrice > 0 && ` @ ${fmt(itemUnitPrice)}`}
                      {' '}· {(item.deliveries || []).length} delivery slot{(item.deliveries || []).length !== 1 ? 's' : ''}
                    </p>
                    {itemMatDiscount > 0 && (
                      <p className="text-xs text-emerald-600 font-medium">
                        Material Discount −{fmt(itemMatDiscount)}
                      </p>
                    )}
                  </div>
                </div>
                <div className="text-right">
                  <p className="text-sm font-bold text-gray-900">{fmt(itemCost + itemDelivery)}</p>
                  <p className="text-xs text-gray-400">
                    Items {fmt(itemCost)}
                    {itemDelivery > 0 && ` + Del ${fmt(itemDelivery)}`}
                  </p>
                </div>
              </div>
            );
          })}
        </div>
      </div>

      {/* ── SECTION 3: SURCHARGE CALCULATOR ── */}
      <div className="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div className="px-4 py-3 bg-gray-50 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
          <div>
            <p className="font-semibold text-gray-900 text-sm flex items-center gap-2">
              <Calculator size={15} className="text-green-600" />
              Surcharge Calculator
            </p>
            <p className="text-xs text-gray-500 mt-0.5">Select delivery slots to calculate applicable surcharges.</p>
          </div>
          <div className="flex items-center gap-3">
            <button onClick={toggleAll} className="text-xs text-blue-600 font-semibold hover:underline">
              {selectedIds.size === deliveryVMs.length ? 'Deselect All' : 'Select All'}
            </button>
            <span className="text-xs text-gray-400">{selectedIds.size} selected</span>
            <button
              onClick={handleCalculate}
              disabled={selectedIds.size === 0 || isCalculating}
              className="flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-lg transition-colors"
            >
              {isCalculating
                ? <><Loader2 className="w-4 h-4 animate-spin" /> Calculating...</>
                : <><Calculator className="w-4 h-4" /> Calculate</>
              }
            </button>
          </div>
        </div>
      </div>

      {/* ── DELIVERY SLOTS GROUPED BY DATE ── */}
      <div className="space-y-4">
        {Array.from(grouped.entries()).map(([date, vms]) => {
          const dayIds = vms.map((v) => v.deliveryId);
          const allDaySelected = dayIds.every((id) => selectedIds.has(id));

          return (
            <div key={date} className="bg-white border border-gray-200 rounded-xl overflow-hidden">
              {/* Day header */}
              <div
                className="px-4 py-2.5 bg-gray-50 border-b border-gray-200 flex items-center gap-3 cursor-pointer hover:bg-gray-100 transition-colors"
                onClick={() => toggleDay(date)}
              >
                <div className={`w-4 h-4 rounded border-2 flex items-center justify-center flex-shrink-0 transition-colors ${
                  allDaySelected ? 'bg-green-600 border-green-600' : 'border-gray-300 bg-white'
                }`}>
                  {allDaySelected && <CheckCircle size={10} className="text-white" />}
                </div>
                <Calendar size={14} className="text-gray-500" />
                <span className="text-sm font-bold text-gray-800">{formatShortDate(date)}</span>
                <span className="text-xs text-gray-500 ml-auto">{vms.length} slot{vms.length !== 1 ? 's' : ''}</span>
              </div>

              {/* Delivery rows */}
              <div className="divide-y divide-gray-100">
                {vms.map((vm) => {
                  const isSelected = selectedIds.has(vm.deliveryId);
                  const surchargeData = surchargeMap.get(vm.deliveryId);
                  const testingFees = testingFeeMap.get(vm.deliveryId) ?? [];
                  const surchargesTotal = surchargeData?.surcharges.reduce((s, x) => s + x.calculated_amount, 0) ?? 0;
                  const testingTotal = testingFees.reduce((s, f) => s + f.amount_snapshot, 0);
                  const slotBase = vm.customerItemCostShare + vm.customerDeliveryCostShare;
                  const slotGrandTotal = slotBase + surchargesTotal + testingTotal;

                  return (
                    <div
                      key={vm.deliveryId}
                      className={`px-4 py-3 transition-colors cursor-pointer ${isSelected ? 'bg-green-50' : 'hover:bg-gray-50'}`}
                      onClick={() => toggleId(vm.deliveryId)}
                    >
                      <div className="flex gap-3">
                        {/* Checkbox */}
                        <div className={`w-4 h-4 mt-0.5 rounded border-2 flex items-center justify-center flex-shrink-0 transition-colors ${
                          isSelected ? 'bg-green-600 border-green-600' : 'border-gray-300 bg-white'
                        }`}>
                          {isSelected && <CheckCircle size={10} className="text-white" />}
                        </div>

                        {/* Content */}
                        <div className="flex-1 min-w-0">
                          {/* Meta */}
                          <div className="flex flex-wrap items-center gap-x-3 gap-y-1 mb-2">
                            <span className="text-xs font-bold text-gray-800">{vm.productName}</span>
                            <span className="text-xs text-gray-500">{vm.quantity} {vm.unitOfMeasure}</span>
                            <span className="flex items-center gap-1 text-xs text-gray-500">
                              <Clock size={11} /> {to12h(vm.deliveryTime)}
                            </span>
                            {vm.truckType && (
                              <span className="flex items-center gap-1 text-xs text-gray-500">
                                <Truck size={11} /> {vm.truckType.replace(/_/g, ' ')}
                              </span>
                            )}
                            {vm.loadSize > 0 && (
                              <span className="text-xs text-gray-400">Load: {vm.loadSize} {vm.unitOfMeasure}</span>
                            )}
                            {surchargeData && (
                              <span className="text-[10px] bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded font-bold">
                                {surchargeData.tripCount} trip{surchargeData.tripCount !== 1 ? 's' : ''}
                              </span>
                            )}
                          </div>

                          {/* Cost rows: item + delivery */}
                          <div className="flex gap-4 mb-2">
                            <div className="flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 border border-blue-100 rounded-lg">
                              <DollarSign size={10} className="text-blue-600" />
                              <span className="text-xs text-gray-700">Items</span>
                              <span className="text-xs font-semibold text-blue-800">{fmt(vm.customerItemCostShare)}</span>
                            </div>
                            <div className="flex items-center gap-1.5 px-2.5 py-1 bg-green-50 border border-green-100 rounded-lg">
                              <Truck size={10} className="text-green-600" />
                              <span className="text-xs text-gray-700">Delivery</span>
                              <span className="text-xs font-semibold text-green-800">
                                {vm.customerDeliveryCostShare > 0 ? fmt(vm.customerDeliveryCostShare) : 'Included'}
                              </span>
                            </div>
                          </div>

                          {/* Surcharge rows */}
                          {surchargeData && surchargeData.surcharges.length > 0 && (
                            <div className="space-y-1 mb-1">
                              {surchargeData.surcharges.map((s, i) => (
                                <div key={i} className="flex items-center justify-between px-2.5 py-1 bg-amber-50 border border-amber-100 rounded-lg">
                                  <div className="flex items-center gap-2">
                                    <span className="text-[10px] font-mono text-amber-700 bg-amber-100 px-1.5 py-0.5 rounded">{s.billing_code}</span>
                                    <span className="text-xs text-gray-700">{s.name}</span>
                                  </div>
                                  <span className="text-xs font-semibold">{fmt(s.calculated_amount)}</span>
                                </div>
                              ))}
                            </div>
                          )}

                          {surchargeData && surchargeData.surcharges.length === 0 && (
                            <p className="text-xs text-gray-400 italic mb-1">No surcharges apply to this slot.</p>
                          )}

                          {/* Testing fee rows (read-only) */}
                          {testingFees.length > 0 && (
                            <div className="space-y-1">
                              {testingFees.map((f) => (
                                <div key={f.id} className="flex items-center justify-between px-2.5 py-1 bg-teal-50 border border-teal-100 rounded-lg">
                                  <div className="flex items-center gap-2">
                                    <span className="text-[10px] font-mono text-teal-700 bg-teal-100 px-1.5 py-0.5 rounded">{f.billing_code ?? 'TEST'}</span>
                                    <span className="text-xs text-gray-700">{f.name}</span>
                                    <span className="text-[10px] bg-teal-100 text-teal-700 px-1 py-0.5 rounded font-bold">Testing</span>
                                  </div>
                                  <span className="text-xs font-semibold">{f.amount_snapshot === 0 ? 'POA' : fmt(f.amount_snapshot)}</span>
                                </div>
                              ))}
                            </div>
                          )}
                        </div>

                        {/* Right: slot total */}
                        <div className="flex flex-col items-end gap-1 flex-shrink-0 min-w-[100px]">
                          <p className="text-sm font-bold text-gray-900">{fmt(slotGrandTotal)}</p>
                          {(surchargesTotal > 0 || testingTotal > 0) && (
                            <p className="text-[10px] text-gray-400 leading-tight text-right">
                              Base {fmt(slotBase)}<br />
                              + Svc {fmt(surchargesTotal + testingTotal)}
                            </p>
                          )}
                        </div>
                      </div>
                    </div>
                  );
                })}
              </div>
            </div>
          );
        })}
      </div>

      {/* ── GRAND TOTAL after calculate ── */}
      {hasCalculated && selectedIds.size > 0 && (
        <div className="bg-gray-900 rounded-xl p-5 space-y-2">
          <p className="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">
            Selected {selectedIds.size} Slot{selectedIds.size !== 1 ? 's' : ''} — Surcharge Summary
          </p>
          <div className="space-y-2 text-sm">
            {selectionSurchargeTotal.surcharges > 0 && (
              <div className="flex justify-between text-amber-300">
                <span>Surcharges</span><span>{fmt(selectionSurchargeTotal.surcharges)}</span>
              </div>
            )}
            {selectionSurchargeTotal.testing > 0 && (
              <div className="flex justify-between text-teal-300">
                <span>Testing Services</span><span>{fmt(selectionSurchargeTotal.testing)}</span>
              </div>
            )}
            <div className="flex justify-between pt-2 border-t border-gray-600 text-white font-bold text-base">
              <span>Surcharges Total</span>
              <span>{fmt(selectionSurchargeTotal.surcharges + selectionSurchargeTotal.testing)}</span>
            </div>
            <p className="text-[11px] text-gray-500 pt-1 italic">
              GST is calculated at the invoice level.
            </p>
          </div>
        </div>
      )}
    </div>
  );
};

export default ClientOrderCostingTab;