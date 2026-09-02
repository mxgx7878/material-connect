// FILE PATH: src/components/admin/Orders/OrderCostingTab.tsx

import React, { useState, useMemo } from 'react';
import {
  Calculator, Truck,
  Lock, Calendar, AlertCircle, CheckCircle, FlaskConical,
  Search, X, Loader2, Clock, Save,
} from 'lucide-react';
import toast from 'react-hot-toast';
import { useAdminCalculateCosting, useAdminAssignTestingFees } from '../../../features/adminOrders/hooks';
import { useGeneralTestingFees } from '../../../features/surcharges/hooks';
import type { AdminOrderDetail, AdminOrderItem } from '../../../types/adminOrder.types';
import { formatCurrency } from '../../../features/adminOrders/utils';
import { CostPriceGate, ProfitMarginGate } from '../../common/PermissionGate';
import PermissionGate from '../../common/PermissionGate';

// ==================== CONSTANTS ====================
// NOTE: All margin math now comes from the backend's unified PricingService
// via `item.pricing` and `delivery.customer_delivery_cost`. The frontend must
// NOT recompute margins. GST_RATE below is only a display fallback for the
// selection preview; the authoritative value arrives in order.pricing_constants.
const GST_RATE_FALLBACK = 0.10;

const TRUCK_LABELS: Record<string, string> = {
  tipper_light: 'Tipper Light', tipper_medium: 'Tipper Medium', tipper_heavy: 'Tipper Heavy',
  light_rigid: 'Light Rigid', medium_rigid: 'Medium Rigid', heavy_rigid: 'Heavy Rigid',
  mini_body: 'Mini Body', body_truck: 'Body Truck', eight_wheeler: '8-Wheeler',
  semi: 'Semi', truck_dog: 'Truck & Dog', mini_truck: 'Mini Truck', truck_and_dog: 'Truck & Dog',
  '10_wheeler': '10-Wheeler',
};

// ==================== TYPES ====================

interface AvailableTestingFee {
  id: number;
  billing_code: string | null;
  name: string;
  amount: number;
  conditions?: string | null;
}

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
  product_name: string;
  unit_of_measure: string;
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
  item_surcharges_total: number;
  item_testing_total: number;
}

// Flat per-delivery view model
interface DeliveryVM {
  deliveryId: number;
  itemId: number;
  deliveryDate: string;
  deliveryTime: string;
  productName: string;
  unitOfMeasure: string;
  quantity: number;
  truckType: string | null;
  loadSize: number;
  supplierItemCostShare: number;
  customerItemCostShare: number;
  materialDiscountShare: number;
  supplierDeliveryCost: number;
  customerDeliveryCost: number;
  supplierRowTotal: number;
  customerRowTotal: number;
  supplierName: string | null;
  isQuoted: boolean;
  supplierConfirms: boolean;
}

interface OrderCostingTabProps {
  order: AdminOrderDetail;
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

// Build flat delivery VMs from order items — sorted by date then time
function buildDeliveryVMs(items: AdminOrderItem[]): DeliveryVM[] {
  const vms: DeliveryVM[] = [];

  for (const item of items) {
    const qty         = toNum(item.quantity, 0);
    const unitCost    = toNum(item.supplier_unit_cost, 0);
    const discountPU  = toNum(item.supplier_discount, 0); // PER UNIT
    const isQuoted    = item.is_quoted === 1 && item.quoted_price != null;
    const quotedPrice = toNum(item.quoted_price, 0);

    // ── Backend unified pricing (source of truth). Fallbacks mirror
    //    PricingService only for payloads that predate the pricing block. ──
    const p = item.pricing;
    const supplierItemTotal = p ? p.supplier_net
      : Math.max(unitCost * qty - discountPU * qty, 0);
    const customerItemTotal = p ? p.customer_item_total
      : (isQuoted ? quotedPrice : Math.max(unitCost * qty * 1.5 - discountPU * qty, 0));
    const materialDiscountTotal = p ? p.material_discount
      : (isQuoted ? 0 : discountPU * qty);

    for (const d of (item.deliveries || [])) {
      const dQty  = toNum(d.quantity, 0);
      const ratio = qty > 0 ? dQty / qty : 0;
      const dCost = toNum(d.delivery_cost, 0);

      const supplierItemShare  = supplierItemTotal * ratio;
      const customerItemShare  = customerItemTotal * ratio;
      const supplierDelCost    = dCost;
      // Backend sends customer_delivery_cost per delivery (flat 10% margin)
      const customerDelCost    = toNum((d as any).customer_delivery_cost, dCost * 1.1);

      vms.push({
        deliveryId: d.id,
        itemId: item.id,
        deliveryDate: (d.delivery_date ?? '').substring(0, 10),
        deliveryTime: d.delivery_time ?? '',
        productName: item.product_name ?? 'Product',
        unitOfMeasure: item.unit_of_measure ?? '',
        quantity: dQty,
        truckType: d.truck_type ?? null,
        loadSize: toNum(d.load_size, 0),
        supplierItemCostShare: supplierItemShare,
        customerItemCostShare: customerItemShare,
        materialDiscountShare: materialDiscountTotal * ratio,
        supplierDeliveryCost: supplierDelCost,
        customerDeliveryCost: customerDelCost,
        supplierRowTotal: supplierItemShare + supplierDelCost,
        customerRowTotal: customerItemShare + customerDelCost,
        supplierName: item.supplier?.name ?? null,
        isQuoted,
        supplierConfirms: !!d.supplier_confirms,
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

// ==================== TESTING SERVICES MODAL ====================

const TestingServicesModal: React.FC<{
  deliveryId: number;
  deliveryLabel: string;
  currentFees: SavedTestingFee[];
  onSave: (deliveryId: number, selectedFeeIds: number[]) => void;
  isSaving: boolean;
  onClose: () => void;
}> = ({ deliveryId, deliveryLabel, currentFees, onSave, isSaving, onClose }) => {
  // Always fetch independently — not tied to calculate response
  const { data, isLoading } = useGeneralTestingFees();
  const availableFees: AvailableTestingFee[] = (data?.data ?? []).filter((f: any) => f.is_active !== false);

  const [selected, setSelected] = useState<number[]>(currentFees.map((f) => f.testing_fee_id));
  const [search, setSearch]     = useState('');

  const filteredFees = availableFees.filter((f) =>
    !search.trim() ||
    f.name.toLowerCase().includes(search.toLowerCase()) ||
    (f.billing_code ?? '').toLowerCase().includes(search.toLowerCase())
  );

  const selectedTotal = availableFees
    .filter((f) => selected.includes(f.id))
    .reduce((s, f) => s + f.amount, 0);

  const toggle = (id: number) =>
    setSelected((prev) => prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id]);

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div className="bg-white rounded-2xl w-full max-w-lg mx-4 shadow-2xl overflow-hidden">
        <div className="bg-gradient-to-r from-teal-600 to-cyan-600 px-5 py-4 flex items-center justify-between">
          <div className="flex items-center gap-3 text-white">
            <FlaskConical size={18} />
            <div>
              <p className="font-bold text-sm">Assign Testing Services</p>
              <p className="text-teal-100 text-xs">{deliveryLabel}</p>
            </div>
          </div>
          <button onClick={onClose} className="text-white/70 hover:text-white"><X size={18} /></button>
        </div>

        <div className="px-4 py-3 border-b border-gray-100 bg-gray-50">
          <div className="relative">
            <Search size={14} className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
            <input
              type="text"
              placeholder="Search testing services..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="w-full pl-8 pr-3 py-2 text-sm border border-gray-200 rounded-lg outline-none bg-white focus:ring-2 focus:ring-teal-500/20 focus:border-teal-400"
              autoFocus
            />
          </div>
          {selected.length > 0 && (
            <div className="flex items-center justify-between mt-2">
              <p className="text-xs text-teal-600 font-medium">{selected.length} selected</p>
              <p className="text-xs font-bold text-teal-700">{fmt(selectedTotal)}</p>
            </div>
          )}
        </div>

        <div className="p-4 max-h-[380px] overflow-y-auto space-y-2">
          {isLoading ? (
            <div className="flex justify-center py-8"><Loader2 className="animate-spin text-teal-600" size={24} /></div>
          ) : filteredFees.length === 0 ? (
            <p className="text-center text-gray-400 text-sm py-8">
              {availableFees.length === 0 ? 'No testing services configured' : `No results for "${search}"`}
            </p>
          ) : (
            filteredFees.map((fee) => {
              const checked = selected.includes(fee.id);
              return (
                <label
                  key={fee.id}
                  className={`flex items-start gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all ${
                    checked ? 'border-teal-400 bg-teal-50' : 'border-gray-200 hover:border-gray-300'
                  }`}
                >
                  <input type="checkbox" checked={checked} onChange={() => toggle(fee.id)} className="mt-0.5 accent-teal-600" />
                  <div className="flex-1 min-w-0">
                    <div className="flex items-start justify-between gap-2">
                      <span className={`text-sm font-semibold ${checked ? 'text-teal-800' : 'text-gray-800'}`}>{fee.name}</span>
                      <span className="text-sm font-bold text-gray-700 shrink-0">{fee.amount === 0 ? 'POA' : fmt(fee.amount)}</span>
                    </div>
                    {fee.billing_code && (
                      <span className="inline-block mt-1 text-[10px] font-mono bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded">{fee.billing_code}</span>
                    )}
                    {fee.conditions && <p className="text-xs text-gray-400 mt-1">{fee.conditions}</p>}
                  </div>
                </label>
              );
            })
          )}
        </div>

        <div className="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
          <p className="text-xs text-gray-400">Saves to database</p>
          <div className="flex gap-2">
            <button onClick={onClose} className="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">
              Cancel
            </button>
            <button
              onClick={() => onSave(deliveryId, selected)}
              disabled={isSaving}
              className="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold bg-teal-600 hover:bg-teal-700 disabled:opacity-50 text-white rounded-lg transition-colors"
            >
              {isSaving ? <Loader2 size={13} className="animate-spin" /> : <Save size={13} />}
              {isSaving ? 'Saving...' : `Save (${selected.length})`}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

// ==================== MAIN COMPONENT ====================

const OrderCostingTab: React.FC<OrderCostingTabProps> = ({ order }) => {
  // const { canViewCostPrice } = usePermissions();

  const [selectedIds, setSelectedIds]             = useState<Set<number>>(new Set());
  const [testingModalDeliveryId, setTestingModal] = useState<number | null>(null);
  const initialTestingFeeMap = useMemo(() => {
  const map = new Map<number, SavedTestingFee[]>();
    for (const item of order.items) {
      for (const d of (item.deliveries || [])) {
        const fees = (d as any).saved_testing_fees as SavedTestingFee[] | undefined;
        if (fees && fees.length > 0) map.set(d.id, fees);
      }
    }
    return map;
  }, [order.items]);

  const [testingFeeMap, setTestingFeeMap] = useState<Map<number, SavedTestingFee[]>>(initialTestingFeeMap);
  const [surchargeMap, setSurchargeMap]           = useState<Map<number, { surcharges: SurchargeResult[]; tripCount: number }>>(new Map());
  const [hasCalculated, setHasCalculated]         = useState(false);

  const { mutate: calculate, isPending: isCalculating } = useAdminCalculateCosting(order.id);
  const { mutate: assignFees, isPending: isSaving }     = useAdminAssignTestingFees();

  const deliveryVMs = useMemo(() => buildDeliveryVMs(order.items), [order.items]);
  const grouped     = useMemo(() => groupByDate(deliveryVMs), [deliveryVMs]);

  // Order-level totals — sourced from the backend's per-item pricing blocks
  // (unified PricingService). No margin math happens here.
  const gstRate = toNum(order.pricing_constants?.gst_rate, GST_RATE_FALLBACK);

  const totals = useMemo(() => {
    let supplierItemGross = 0, materialDiscount = 0, supplierNet = 0, supplierDeliveryCost = 0;
    let customerItemGross = 0, customerItemCost = 0, customerDeliveryCost = 0;

    for (const item of order.items) {
      const qty        = toNum(item.quantity, 0);
      const uc         = toNum(item.supplier_unit_cost, 0);
      const discPU     = toNum(item.supplier_discount, 0); // PER UNIT
      const isQ        = item.is_quoted === 1 && item.quoted_price != null;
      const qp         = toNum(item.quoted_price, 0);
      const p          = item.pricing;

      supplierItemGross += p ? p.supplier_gross     : uc * qty;
      materialDiscount  += p ? p.material_discount  : (isQ ? 0 : discPU * qty);
      supplierNet       += p ? p.supplier_net       : Math.max(uc * qty - (isQ ? 0 : discPU * qty), 0);
      customerItemGross += p ? p.customer_item_gross : (isQ ? qp : uc * qty * 1.5);
      customerItemCost  += p ? p.customer_item_total : (isQ ? qp : Math.max(uc * qty * 1.5 - discPU * qty, 0));

      for (const d of (item.deliveries || [])) {
        const dc = toNum(d.delivery_cost, 0);
        supplierDeliveryCost += dc;
        customerDeliveryCost += toNum((d as any).customer_delivery_cost, dc * 1.1);
      }
    }

    const supplierTotal    = supplierNet + supplierDeliveryCost;
    const customerSubtotal = customerItemCost + customerDeliveryCost;
    const gst              = customerSubtotal * gstRate;
    const discount         = toNum(order.discount, 0);
    const otherCharges     = toNum(order.other_charges, 0);
    const grandTotal       = customerSubtotal + gst - discount + otherCharges;
    const profitAmount     = customerSubtotal - supplierTotal;
    const profitPct        = supplierTotal > 0 ? (profitAmount / supplierTotal) * 100 : 0;

    return {
      supplierItemGross, materialDiscount, supplierNet, supplierDeliveryCost, supplierTotal,
      customerItemGross, customerItemCost, customerDeliveryCost, customerSubtotal,
      gst, discount, otherCharges, grandTotal, profitAmount, profitPct,
    };
  }, [order.items, order.discount, order.other_charges, gstRate]);

  // Grand total for selected deliveries including all services
  const selectionTotal = useMemo(() => {
    let customerBase = 0, surcharges = 0, testing = 0;
    for (const vm of deliveryVMs) {
      if (!selectedIds.has(vm.deliveryId)) continue;
      customerBase += vm.customerItemCostShare + vm.customerDeliveryCost;
      const s = surchargeMap.get(vm.deliveryId);
      if (s) surcharges += s.surcharges.reduce((acc, x) => acc + x.calculated_amount, 0);
      const t = testingFeeMap.get(vm.deliveryId);
      if (t) testing += t.reduce((acc, x) => acc + x.amount_snapshot, 0);
    }
    const subtotal   = customerBase + surcharges + testing;
    const gst        = subtotal * gstRate;
    return { customerBase, surcharges, testing, subtotal, gst, grandTotal: subtotal + gst };
  }, [deliveryVMs, selectedIds, surchargeMap, testingFeeMap, gstRate]);

  // Selection helpers
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

  // Calculate
  const handleCalculate = () => {
    if (!selectedIds.size) return;
    calculate(Array.from(selectedIds), {
      onSuccess: (res) => {
        const newSurcharges = new Map(surchargeMap);
        const newTesting    = new Map(testingFeeMap);
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
      onError: () => toast.error('Failed to calculate services'),
    });
  };

  // Assign testing fees
  const handleSaveTestingFees = (deliveryId: number, feeIds: number[]) => {
    assignFees(
      { orderId: order.id, assignments: [{ delivery_id: deliveryId, testing_fee_ids: feeIds }] },
      {
        onSuccess: (res) => {
          const assignment = res.data?.[0];
          if (assignment) {
            const saved: SavedTestingFee[] = (assignment.assigned_fees ?? []).map((f: any) => ({
              id: f.id, testing_fee_id: f.testing_fee_id, billing_code: f.billing_code,
              name: f.name, amount_snapshot: f.amount_snapshot,
            }));
            setTestingFeeMap((prev) => { const n = new Map(prev); n.set(deliveryId, saved); return n; });
          }
          toast.success('Testing services saved');
          setTestingModal(null);
        },
        onError: () => toast.error('Failed to save testing services'),
      }
    );
  };

  const modalVM = testingModalDeliveryId !== null
    ? deliveryVMs.find((d) => d.deliveryId === testingModalDeliveryId)
    : null;

  return (
    <div className="space-y-5">

      {/* ── SECTION 1: COST SUMMARY CARDS ── */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-3">
        <CostPriceGate fallback={
          <div className="bg-gray-50 border border-gray-200 rounded-xl p-4 flex items-center gap-2 text-gray-400">
            <Lock size={16} /><span className="text-sm font-medium">Supplier cost hidden</span>
          </div>
        }>
          <div className="bg-blue-50 border border-blue-200 rounded-xl p-4">
            <p className="text-xs font-bold text-blue-600 uppercase tracking-wide mb-1">Supplier Cost (Net)</p>
            <p className="text-xl font-bold text-blue-800">{formatCurrency(totals.supplierTotal)}</p>
            <p className="text-xs text-blue-500 mt-1">
              Items {formatCurrency(totals.supplierItemGross)}
              {totals.materialDiscount > 0 && (
                <span className="text-emerald-600"> · Material Disc −{formatCurrency(totals.materialDiscount)}</span>
              )}
              {' '}· Delivery {formatCurrency(totals.supplierDeliveryCost)}
            </p>
          </div>
        </CostPriceGate>

        <div className="bg-green-50 border border-green-200 rounded-xl p-4">
          <p className="text-xs font-bold text-green-600 uppercase tracking-wide mb-1">Customer Total</p>
          <p className="text-xl font-bold text-green-800">{formatCurrency(totals.grandTotal)}</p>
          <p className="text-xs text-green-500 mt-1">
            Items {formatCurrency(totals.customerItemGross)}
            {totals.materialDiscount > 0 && (
              <span className="text-emerald-600"> · Material Disc −{formatCurrency(totals.materialDiscount)}</span>
            )}
            {' '}· Delivery {formatCurrency(totals.customerDeliveryCost)} · GST {formatCurrency(totals.gst)}
            {totals.discount > 0 && ` · Discount -${formatCurrency(totals.discount)}`}
          </p>
        </div>

        <ProfitMarginGate fallback={
          <div className="bg-gray-50 border border-gray-200 rounded-xl p-4 flex items-center gap-2 text-gray-400">
            <Lock size={16} /><span className="text-sm font-medium">Margin hidden</span>
          </div>
        }>
          <div className={`border rounded-xl p-4 ${totals.profitAmount >= 0 ? 'bg-emerald-50 border-emerald-200' : 'bg-red-50 border-red-200'}`}>
            <p className={`text-xs font-bold uppercase tracking-wide mb-1 ${totals.profitAmount >= 0 ? 'text-emerald-600' : 'text-red-600'}`}>
              Profit & Margin
            </p>
            <p className={`text-xl font-bold ${totals.profitAmount >= 0 ? 'text-emerald-800' : 'text-red-800'}`}>
              {formatCurrency(totals.profitAmount)}
            </p>
            <p className={`text-xs mt-1 ${totals.profitAmount >= 0 ? 'text-emerald-500' : 'text-red-500'}`}>
              {totals.profitPct.toFixed(1)}% margin on supplier cost
            </p>
          </div>
        </ProfitMarginGate>
      </div>

      <PermissionGate permission="pricing.view_cost_price">
        <div className="flex items-center gap-2 flex-wrap">
          <span className="px-2.5 py-1 bg-blue-600 text-white rounded-lg text-[11px] font-bold">
            Item Margin: {toNum(order.pricing_constants?.item_margin, 0.5) * 100}% (on full supplier price)
          </span>
          <span className="px-2.5 py-1 bg-indigo-600 text-white rounded-lg text-[11px] font-bold">
            Delivery Margin: {toNum(order.pricing_constants?.delivery_margin, 0.1) * 100}%
          </span>
          <span className="px-2.5 py-1 bg-green-600 text-white rounded-lg text-[11px] font-bold">GST: {gstRate * 100}%</span>
          {totals.materialDiscount > 0 && (
            <span className="px-2.5 py-1 bg-emerald-600 text-white rounded-lg text-[11px] font-bold">
              Material Discount: −{formatCurrency(totals.materialDiscount)}
            </span>
          )}
        </div>
      </PermissionGate>

      {/* ── DIVIDER ── */}
      <div className="relative">
        <div className="absolute inset-0 flex items-center"><div className="w-full border-t border-gray-200" /></div>
        <div className="relative flex justify-center">
          <span className="bg-white px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">
            Deliveries · Services & Surcharges
          </span>
        </div>
      </div>

      {/* ── SECTION 2: TOOLBAR ── */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-3">
          <button onClick={toggleAll} className="text-xs text-blue-600 font-semibold hover:underline">
            {selectedIds.size === deliveryVMs.length ? 'Deselect All' : 'Select All'}
          </button>
          <span className="text-xs text-gray-400">
            {selectedIds.size} of {deliveryVMs.length} selected
          </span>
        </div>
        <button
          onClick={handleCalculate}
          disabled={selectedIds.size === 0 || isCalculating}
          className="flex items-center gap-2 px-5 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-lg transition-colors"
        >
          {isCalculating
            ? <><Loader2 className="w-4 h-4 animate-spin" /> Calculating...</>
            : <><Calculator className="w-4 h-4" /> Calculate Services</>}
        </button>
      </div>

      {/* ── SECTION 3: GROUPED DELIVERIES ── */}
      <div className="space-y-3">
        {Array.from(grouped.entries()).map(([date, vms]) => {
          const dayIds      = vms.map((v) => v.deliveryId);
          const allSelected = dayIds.every((id) => selectedIds.has(id));
          const someSelected = dayIds.some((id) => selectedIds.has(id));

          const dayCustomerBase = vms.reduce((s, v) => s + v.customerRowTotal, 0);
          const daySupplierBase = vms.reduce((s, v) => s + v.supplierRowTotal, 0);
          const daySurcharges   = vms.reduce((s, v) => {
            const sr = surchargeMap.get(v.deliveryId);
            return s + (sr?.surcharges.reduce((ss, x) => ss + x.calculated_amount, 0) ?? 0);
          }, 0);
          const dayTesting = vms.reduce((s, v) => {
            const tf = testingFeeMap.get(v.deliveryId);
            return s + (tf?.reduce((ss, f) => ss + f.amount_snapshot, 0) ?? 0);
          }, 0);

          return (
            <div key={date} className="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
              {/* Day header */}
              <div
                className={`px-4 py-3 flex items-center justify-between border-b cursor-pointer select-none ${
                  allSelected ? 'bg-blue-50 border-blue-200' : someSelected ? 'bg-blue-50/40 border-blue-100' : 'bg-gray-50 border-gray-200'
                }`}
                onClick={() => toggleDay(date)}
              >
                <div className="flex items-center gap-3">
                  <input
                    type="checkbox"
                    checked={allSelected}
                    ref={(el) => { if (el) el.indeterminate = someSelected && !allSelected; }}
                    onChange={() => toggleDay(date)}
                    className="accent-blue-600"
                    onClick={(e) => e.stopPropagation()}
                  />
                  <Calendar size={14} className="text-blue-500" />
                  <span className="font-bold text-gray-900 text-sm">{formatShortDate(date)}</span>
                  <span className="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">
                    {vms.length} deliver{vms.length !== 1 ? 'ies' : 'y'}
                  </span>
                </div>
                <div className="flex items-center gap-4 text-xs">
                  {(daySurcharges > 0 || dayTesting > 0) && (
                    <span className="text-amber-700 font-semibold">
                      Services: {fmt(daySurcharges + dayTesting)}
                    </span>
                  )}
                  <CostPriceGate fallback={null}>
                    <span className="text-blue-600 font-medium">Supplier: {fmt(daySupplierBase)}</span>
                  </CostPriceGate>
                  <span className="text-green-700 font-bold">Customer: {fmt(dayCustomerBase + daySurcharges + dayTesting)}</span>
                </div>
              </div>

              {/* Delivery rows */}
              <div className="divide-y divide-gray-100">
                {vms.map((vm) => {
                  const surchargeData   = surchargeMap.get(vm.deliveryId);
                  const testingFees     = testingFeeMap.get(vm.deliveryId) ?? [];
                  const surchargesTotal = surchargeData?.surcharges.reduce((s, x) => s + x.calculated_amount, 0) ?? 0;
                  const testingTotal    = testingFees.reduce((s, f) => s + f.amount_snapshot, 0);
                  const rowGrandTotal   = vm.customerRowTotal + surchargesTotal + testingTotal;
                  const isSelected      = selectedIds.has(vm.deliveryId);

                  return (
                    <div key={vm.deliveryId} className={`transition-colors ${isSelected ? 'bg-blue-50/20' : ''}`}>
                      <div className="px-4 py-3 flex items-start gap-3">
                        {/* Checkbox */}
                        <input
                          type="checkbox"
                          checked={isSelected}
                          onChange={() => toggleId(vm.deliveryId)}
                          className="accent-blue-600 mt-1 flex-shrink-0"
                        />

                        {/* Content */}
                        <div className="flex-1 min-w-0">
                          {/* Meta */}
                          <div className="flex items-center gap-2 flex-wrap mb-1.5">
                            <span className="font-semibold text-gray-900 text-sm">{vm.productName}</span>
                            <span className="text-gray-300 text-xs">·</span>
                            <span className="flex items-center gap-1 text-xs text-gray-600">
                              <Clock size={11} className="text-gray-400" />{to12h(vm.deliveryTime)}
                            </span>
                            <span className="text-gray-300 text-xs">·</span>
                            <span className="flex items-center gap-1 text-xs text-gray-600">
                              <Truck size={11} className="text-gray-400" />
                              {vm.quantity} {vm.unitOfMeasure}
                              {vm.truckType && ` · ${TRUCK_LABELS[vm.truckType] ?? vm.truckType.replace(/_/g, ' ')}`}
                            </span>
                            {surchargeData?.tripCount && surchargeData.tripCount > 1 && (
                              <span className="px-1.5 py-0.5 bg-blue-100 text-blue-700 text-[10px] font-bold rounded">
                                {surchargeData.tripCount} trips
                              </span>
                            )}
                            {vm.isQuoted && (
                              <span className="px-1.5 py-0.5 bg-purple-100 text-purple-700 text-[10px] font-bold rounded">QUOTED</span>
                            )}
                            {vm.supplierConfirms
                              ? <CheckCircle size={12} className="text-green-500" />
                              : <AlertCircle size={12} className="text-amber-400" />}
                          </div>

                          {/* Cost breakdown */}
                          <div className="flex items-center flex-wrap gap-x-4 gap-y-0.5 text-xs mb-2">
                            <CostPriceGate fallback={null}>
                              <span className="text-blue-600">
                                Supplier: <span className="font-semibold">{fmt(vm.supplierItemCostShare)}</span>
                                {vm.supplierDeliveryCost > 0 && <span className="text-blue-400"> + {fmt(vm.supplierDeliveryCost)}</span>}
                              </span>
                            </CostPriceGate>
                            <span className="text-green-600">
                              Customer item: <span className="font-semibold">{fmt(vm.customerItemCostShare)}</span>
                            </span>
                            {vm.materialDiscountShare > 0 && (
                              <span className="text-emerald-600 text-[11px]">
                                (incl. Material Disc −{fmt(vm.materialDiscountShare)})
                              </span>
                            )}
                            {vm.customerDeliveryCost > 0 && (
                              <span className="text-green-500">
                                + delivery: <span className="font-semibold">{fmt(vm.customerDeliveryCost)}</span>
                              </span>
                            )}
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

                          {/* Testing fee rows */}
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

                        {/* Right: total + testing button */}
                        <div className="flex flex-col items-end gap-1.5 flex-shrink-0 min-w-[110px]">
                          <div className="text-right">
                            <CostPriceGate fallback={null}>
                              <p className="text-[11px] text-blue-500 font-semibold">{fmt(vm.supplierRowTotal)}</p>
                            </CostPriceGate>
                            <p className="text-sm font-bold text-gray-900">{fmt(rowGrandTotal)}</p>
                            {(surchargesTotal > 0 || testingTotal > 0) && (
                              <p className="text-[10px] text-gray-400 leading-tight">
                                Base {fmt(vm.customerRowTotal)}<br />
                                + Svc {fmt(surchargesTotal + testingTotal)}
                              </p>
                            )}
                          </div>
                          <button
                            onClick={() => setTestingModal(vm.deliveryId)}
                            className={`flex items-center gap-1 px-2 py-1 border text-[11px] font-semibold rounded-lg transition-colors ${
                              testingFees.length > 0
                                ? 'bg-teal-100 border-teal-300 text-teal-700 hover:bg-teal-200'
                                : 'bg-gray-50 border-gray-200 text-gray-500 hover:border-teal-200 hover:text-teal-600 hover:bg-teal-50'
                            }`}
                          >
                            <FlaskConical size={11} />
                            {testingFees.length > 0 ? `Testing (${testingFees.length})` : '+ Testing'}
                          </button>
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

      {/* ── GRAND TOTAL (visible after calculate with selection) ── */}
      {hasCalculated && selectedIds.size > 0 && (
        <div className="bg-gray-900 rounded-xl p-5 space-y-2">
          <p className="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">
            Selected {selectedIds.size} Slot{selectedIds.size !== 1 ? 's' : ''} — Full Cost Summary
          </p>
          <div className="space-y-2 text-sm">
            <div className="flex justify-between text-gray-300">
              <span>Customer Items + Delivery</span>
              <span>{fmt(selectionTotal.customerBase)}</span>
            </div>
            {selectionTotal.surcharges > 0 && (
              <div className="flex justify-between text-amber-300">
                <span>Surcharges</span><span>{fmt(selectionTotal.surcharges)}</span>
              </div>
            )}
            {selectionTotal.testing > 0 && (
              <div className="flex justify-between text-teal-300">
                <span>Testing Services</span><span>{fmt(selectionTotal.testing)}</span>
              </div>
            )}
            <div className="flex justify-between text-gray-500 border-t border-gray-700 pt-2">
              <span>Subtotal</span><span className="text-gray-300">{fmt(selectionTotal.subtotal)}</span>
            </div>
            <div className="flex justify-between text-gray-500">
              <span>GST (10%)</span><span className="text-gray-300">{fmt(selectionTotal.gst)}</span>
            </div>
            <div className="flex justify-between pt-1 border-t border-gray-600 text-white font-bold text-base">
              <span>Grand Total</span><span>{fmt(selectionTotal.grandTotal)}</span>
            </div>
          </div>
        </div>
      )}

      {/* Testing Modal */}
      {testingModalDeliveryId !== null && modalVM && (
        <TestingServicesModal
          deliveryId={testingModalDeliveryId}
          deliveryLabel={`${formatShortDate(modalVM.deliveryDate)} · ${to12h(modalVM.deliveryTime)} · ${modalVM.productName}`}
          currentFees={testingFeeMap.get(testingModalDeliveryId) ?? []}
          onSave={handleSaveTestingFees}
          isSaving={isSaving}
          onClose={() => setTestingModal(null)}
        />
      )}
    </div>
  );
};

export default OrderCostingTab;