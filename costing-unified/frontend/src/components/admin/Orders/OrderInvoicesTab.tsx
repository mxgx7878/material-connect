// FILE PATH: src/components/admin/Orders/OrderInvoicesTab.tsx

/**
 * Order Invoices Tab Component
 * Allows admin to:
 * 1. Select deliveries across items to invoice
 * 2. Preview calculated totals
 * 3. Generate invoices
 * 4. View existing invoices with status management
 * 5. Mark invoices Completed (locks + pushes to Xero)   ← Phase 6
 *
 * PHASE 6 ADDITIONS:
 *   - "Mark Completed" button on the invoice detail view, visible only
 *     when status ∈ COMPLETABLE_INVOICE_STATUSES, disabled when there's
 *     an open dispute (with tooltip).
 *   - Locked banner + completion meta grid when status === 'Completed'.
 *   - Status select fully disabled when status === 'Completed'.
 *   - getInvoiceStatusBadge map now has a Completed entry.
 *   - INVOICE_STATUSES dropdown options stay unchanged — Completed is
 *     never set manually.
 *
 * PHASE 7 ADDITIONS:
 *   - Mark Completed button is now role-gated. Visible only for admin +
 *     support; hidden for accountant (who can land on this page but
 *     shouldn't be able to mark invoices completed).
 *
 * FIX (delivery status badge):
 *   - The DeliveryRow status badge previously compared delivery.status
 *     against title-case strings ('Delivered', 'Scheduled', 'Cancelled'),
 *     but the DB column stores lowercase enum values ('delivered',
 *     'scheduled', 'paid', 'invoiced', …). None of those comparisons ever
 *     matched, so every badge fell through to the gray "unknown" style and
 *     showed the raw lowercase value. Now uses the canonical helpers from
 *     utils/deliveryStatus so labels + colours match the rest of the app.
 */

import React, { useState, useMemo, useCallback, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  Receipt,
  Package,
  Calendar,
  Clock,
  CheckCircle,
  AlertCircle,
  ChevronDown,
  ChevronUp,
  Truck,
  DollarSign,
  FileText,
  Plus,
  Eye,
  Loader2,
  Lock,
  ShieldAlert,
  ExternalLink,
  X,
} from 'lucide-react';
import {
  useInvoiceableDeliveries,
  useOrderInvoices,
  useInvoicePreview,
  useCreateInvoice,
  useUpdateInvoiceStatus,
  useInvoiceDetail,
  useMarkInvoiceCompleted,   // ← Phase 6
} from '../../../features/invoices/hooks';
import { formatCurrency, formatDate } from '../../../features/adminOrders/utils';
import type {
  InvoiceableItem,
  InvoiceableDelivery,
  InvoicePreviewData,
  InvoiceSummary,
  InvoiceStatus,
} from '../../../types/invoice.types';
import MarkCompletedConfirmModal from './MarkCompletedConfirmModal';   // ← Phase 6
import { usePermissions } from '../../../hooks/usePermissions';         // ← Phase 7
import { getOrderStatusLabel } from '../../../utils/orderStatus';
import { getDeliveryStatusBadgeClass, getDeliveryStatusLabel } from '../../../utils/deliveryStatus';

interface OrderInvoicesTabProps {
  orderId: number;
  orderStatus: string; // ← Phase 7
}

// ==================== STATUS BADGE HELPER ====================
const getInvoiceStatusBadge = (status: InvoiceStatus): string => {
  const map: Record<InvoiceStatus, string> = {
    Draft: 'bg-gray-100 text-gray-700 border-gray-300',
    Sent: 'bg-blue-100 text-blue-700 border-blue-300',
    Paid: 'bg-green-100 text-green-700 border-green-300',
    Overdue: 'bg-red-100 text-red-700 border-red-300',
    Completed: 'bg-emerald-100 text-emerald-800 border-emerald-300',   // ← Phase 6
    Cancelled: 'bg-gray-100 text-gray-500 border-gray-300',
    Void: 'bg-red-50 text-red-500 border-red-200',
  };
  return map[status] || 'bg-gray-100 text-gray-700 border-gray-300';
};

const formatTime = (timeStr: string | null): string => {
  if (!timeStr) return '—';
  const [h, m] = timeStr.split(':');
  const hour = parseInt(h, 10);
  const ampm = hour >= 12 ? 'PM' : 'AM';
  const hour12 = hour % 12 || 12;
  return `${hour12}:${m} ${ampm}`;
};

// ==================== INVOICE STATUS OPTIONS ====================
// Note: 'Completed' is intentionally NOT in this list — it's only set
// via the Mark Completed button, never via the dropdown.
const INVOICE_STATUSES: InvoiceStatus[] = [
  'Draft',
  'Sent',
  'Paid',
  'Overdue',
  'Cancelled',
  'Void',
];

// ==================== COMPLETABLE STATUSES ====================
// Phase 6: which statuses allow Mark Completed.
// Drafts must be Sent first; Cancelled / Void are dead ends; Completed
// is itself terminal.
const COMPLETABLE_INVOICE_STATUSES: InvoiceStatus[] = [
  'Sent',
  'Paid',
  'Overdue',
];



// ==================== OPEN DISPUTE BANNER ====================
const OpenDisputeBanner: React.FC<{
  dispute: { id: number; dispute_number: string; status: string };
}> = ({ dispute }) => {
  const navigate = useNavigate();

  return (
    <div className="bg-amber-50 border-2 border-amber-300 rounded-xl p-4 flex items-start gap-3">
      <ShieldAlert className="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" />
      <div className="flex-1 min-w-0">
        <p className="text-sm font-bold text-amber-900">
          Open dispute on this invoice
        </p>
        <p className="text-xs text-amber-700 mt-0.5">
          Dispute{' '}
          <span className="font-mono font-bold">{dispute.dispute_number}</span>{' '}
          is currently <span className="font-medium">{dispute.status.replace('_', ' ')}</span>.
          Void / Cancelled / Mark Completed actions are blocked until it's resolved.
        </p>
      </div>
      <button
        onClick={() => navigate(`/admin/disputes/${dispute.id}`)}
        className="flex items-center gap-1.5 px-3 py-1.5 bg-amber-600 text-white text-xs font-bold rounded-lg hover:bg-amber-700 transition-colors flex-shrink-0"
      >
        <ExternalLink className="w-3.5 h-3.5" />
        View Dispute
      </button>
    </div>
  );
};

// ==================== DISPUTE BLOCKED MODAL ====================
interface DisputeBlockedModalProps {
  isOpen: boolean;
  onClose: () => void;
  dispute: { id: number; dispute_number: string; status: string } | null;
  attemptedStatus: InvoiceStatus | null;
}

const DisputeBlockedModal: React.FC<DisputeBlockedModalProps> = ({
  isOpen,
  onClose,
  dispute,
  attemptedStatus,
}) => {
  const navigate = useNavigate();
  if (!isOpen || !dispute) return null;

  const actionVerb =
    attemptedStatus === 'Void'
      ? 'void'
      : attemptedStatus === 'Cancelled'
      ? 'cancel'
      : 'update';

  return (
    <>
      <div
        className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50"
        onClick={onClose}
      />
      <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div
          className="bg-white rounded-xl shadow-2xl w-full max-w-md border-2 border-amber-200"
          onClick={(e) => e.stopPropagation()}
        >
          <div className="flex items-center justify-between p-6 border-b border-gray-200">
            <div className="flex items-center gap-4">
              <div className="p-3 rounded-full bg-amber-100">
                <ShieldAlert className="w-6 h-6 text-amber-600" />
              </div>
              <h3 className="text-xl font-bold text-gray-900">
                Cannot {actionVerb} invoice
              </h3>
            </div>
            <button
              onClick={onClose}
              className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
            >
              <X className="w-5 h-5 text-gray-500" />
            </button>
          </div>

          <div className="p-6 space-y-3">
            <p className="text-gray-700 leading-relaxed">
              This invoice has an open dispute. The action is blocked until the
              dispute is resolved.
            </p>
            <div className="bg-amber-50 border border-amber-200 rounded-lg p-3">
              <p className="text-[10px] font-bold text-amber-700 uppercase tracking-wide">
                Active Dispute
              </p>
              <p className="text-sm font-mono font-bold text-amber-900 mt-1">
                {dispute.dispute_number}
              </p>
              <p className="text-xs text-amber-700 mt-0.5">
                Status:{' '}
                <span className="font-medium">
                  {dispute.status.replace('_', ' ')}
                </span>
              </p>
            </div>
          </div>

          <div className="flex gap-3 p-6 bg-gray-50 border-t border-gray-200 rounded-b-xl">
            <button
              onClick={onClose}
              className="flex-1 px-4 py-3 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-white transition-colors font-medium"
            >
              Close
            </button>
            <button
              onClick={() => {
                navigate(`/admin/disputes/${dispute.id}`);
                onClose();
              }}
              className="flex-1 px-4 py-3 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors font-medium flex items-center justify-center gap-2"
            >
              <ExternalLink className="w-4 h-4" />
              Resolve Dispute
            </button>
          </div>
        </div>
      </div>
    </>
  );
};


// ==================== MAIN COMPONENT ====================
const OrderInvoicesTab: React.FC<OrderInvoicesTabProps> = ({ orderId, orderStatus }) => {
  const canInvoice = (orderStatus ?? '').toLowerCase() === 'processing';
  const [activeView, setActiveView] = useState<'list' | 'create' | 'detail'>('list');
  const [selectedDeliveries, setSelectedDeliveries] = useState<Set<number>>(new Set());
  const [expandedItems, setExpandedItems] = useState<Set<number>>(new Set());
  const [previewData, setPreviewData] = useState<InvoicePreviewData | null>(null);
  const [invoiceNotes, setInvoiceNotes] = useState('');
  const [invoiceDueDate, setInvoiceDueDate] = useState('');
  const [invoiceDiscount, setInvoiceDiscount] = useState('');
  const [viewingInvoiceId, setViewingInvoiceId] = useState<number | null>(null);
  const [blockedDispute, setBlockedDispute] = useState<{
    id: number;
    dispute_number: string;
    status: string;
  } | null>(null);
  const [attemptedStatus, setAttemptedStatus] = useState<InvoiceStatus | null>(null);

  const { data: deliveriesData, isLoading: loadingDeliveries } = useInvoiceableDeliveries(orderId);
  const { data: invoicesData, isLoading: loadingInvoices } = useOrderInvoices(orderId);
  

  const previewMutation = useInvoicePreview(orderId);
  const createMutation = useCreateInvoice();
  const statusMutation = useUpdateInvoiceStatus();

  const items = deliveriesData?.data?.items || [];
  const invoices = invoicesData?.data || [];

  const availableDeliveryCount = useMemo(() => {
    return items.reduce((count, item) => {
      return count + item.deliveries.filter((d) => !d.is_invoiced).length;
    }, 0);
  }, [items]);

  const toggleDelivery = useCallback((deliveryId: number) => {
    setSelectedDeliveries((prev) => {
      const next = new Set(prev);
      if (next.has(deliveryId)) next.delete(deliveryId);
      else next.add(deliveryId);
      return next;
    });
    setPreviewData(null);
  }, []);

  const toggleAllForItem = useCallback(
    (item: InvoiceableItem) => {
      const available = item.deliveries.filter((d) => !d.is_invoiced);
      const allSelected = available.every((d) => selectedDeliveries.has(d.id));

      setSelectedDeliveries((prev) => {
        const next = new Set(prev);
        available.forEach((d) => {
          if (allSelected) next.delete(d.id);
          else next.add(d.id);
        });
        return next;
      });
      setPreviewData(null);
    },
    [selectedDeliveries]
  );

  const selectAllAvailable = useCallback(() => {
    setSelectedDeliveries((prev) => {
      const next = new Set(prev);
      items.forEach((item) => {
        item.deliveries.forEach((d) => {
          if (!d.is_invoiced) next.add(d.id);
        });
      });
      return next;
    });
    setPreviewData(null);
  }, [items]);

  const clearSelection = useCallback(() => {
    setSelectedDeliveries(new Set());
    setPreviewData(null);
  }, []);

  const toggleItemExpand = useCallback((itemId: number) => {
    setExpandedItems((prev) => {
      const next = new Set(prev);
      if (next.has(itemId)) next.delete(itemId);
      else next.add(itemId);
      return next;
    });
  }, []);

  const runPreview = useCallback(() => {
    if (selectedDeliveries.size === 0) return;
    previewMutation.mutate(
      {
        delivery_ids: Array.from(selectedDeliveries),
        discount: invoiceDiscount ? parseFloat(invoiceDiscount) : undefined,
      },
      { onSuccess: (res) => setPreviewData(res.data) }
    );
  }, [selectedDeliveries, invoiceDiscount, previewMutation]);

  const handlePreview = runPreview;

  // Re-run the preview when the discount changes (debounced), but only once an
  // initial preview exists — the discount input only renders after preview.
  useEffect(() => {
    if (!previewData) return;
    const t = setTimeout(runPreview, 400);
    return () => clearTimeout(t);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [invoiceDiscount]);
  

  const handleCreateInvoice = useCallback(() => {
    if (selectedDeliveries.size === 0) return;
    createMutation.mutate(
      {
        orderId,
        payload: {
          delivery_ids: Array.from(selectedDeliveries),
          notes: invoiceNotes || undefined,
          due_date: invoiceDueDate || undefined,
          discount: invoiceDiscount ? parseFloat(invoiceDiscount) : undefined,
        },
      },
      {
        onSuccess: () => {
          setSelectedDeliveries(new Set());
          setPreviewData(null);
          setInvoiceNotes('');
          setInvoiceDueDate('');
          setInvoiceDiscount('');
          setActiveView('list');
        },
      }
    );
  }, [selectedDeliveries, orderId, invoiceNotes, invoiceDueDate, invoiceDiscount, createMutation]);

  const handleStatusChange = useCallback(
    (invoiceId: number, newStatus: InvoiceStatus) => {
      setAttemptedStatus(newStatus);
      statusMutation.mutate(
        { invoiceId, orderId, payload: { status: newStatus } },
        {
          onError: (error: any) => {
            if (error?.status === 409 && error?.open_dispute) {
              setBlockedDispute(error.open_dispute);
            }
          },
        }
      );
    },
    [orderId, statusMutation]
  );

  const handleViewInvoice = useCallback((invoiceId: number) => {
    setViewingInvoiceId(invoiceId);
    setActiveView('detail');
  }, []);

  const handleBackToList = useCallback(() => {
    setViewingInvoiceId(null);
    setActiveView('list');
    setPreviewData(null);
  }, []);

  const handleStartCreate = useCallback(() => {
    if (!canInvoice) return;
    setActiveView('create');
    setExpandedItems(new Set(items.map((i) => i.id)));
  }, [items, canInvoice]);

  if (loadingDeliveries || loadingInvoices) {
    return (
      <div className="flex items-center justify-center py-20">
        <Loader2 className="animate-spin text-blue-600 mr-3" size={32} />
        <span className="text-gray-600 font-medium">Loading invoice data...</span>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="bg-gradient-to-r from-emerald-600 to-teal-600 rounded-xl p-6 text-white shadow-lg">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="p-2 bg-white/10 rounded-lg">
              <Receipt size={28} />
            </div>
            <div>
              <h2 className="text-2xl font-bold">Invoices</h2>
              <p className="text-emerald-100 text-sm mt-1">
                {invoices.length} invoice{invoices.length !== 1 ? 's' : ''} created ·{' '}
                {availableDeliveryCount} deliveries available to invoice
              </p>
            </div>
          </div>

          {activeView === 'list' && availableDeliveryCount > 0 && canInvoice && (
            <button
              onClick={handleStartCreate}
              className="flex items-center gap-2 px-5 py-2.5 bg-white text-emerald-700 rounded-lg font-bold hover:bg-emerald-50 transition-colors shadow-sm"
            >
              <Plus size={18} />
              Create Invoice
            </button>
          )}

          {(activeView === 'create' || activeView === 'detail') && (
            <button
              onClick={handleBackToList}
              className="flex items-center gap-2 px-5 py-2.5 bg-white/10 text-white rounded-lg font-medium hover:bg-white/20 transition-colors border border-white/20"
            >
              ← Back to Invoices
            </button>
          )}
        </div>
      </div>

      {activeView === 'list' && !canInvoice && (
        <div className="bg-amber-50 border-2 border-amber-200 rounded-xl p-4 flex items-start gap-3">
          <AlertCircle className="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" />
          <p className="text-sm text-amber-800">
            Invoices can only be created while the order is in{' '}
            <span className="font-bold">Processing</span>. This order is currently{' '}
            <span className="font-bold">{getOrderStatusLabel(orderStatus)}</span>.
          </p>
        </div>
      )}

      {activeView === 'list' &&  (
        <InvoiceListView
          invoices={invoices}
          availableDeliveryCount={availableDeliveryCount}
          canInvoice={canInvoice}
          onViewInvoice={handleViewInvoice}
          onStatusChange={handleStatusChange}
          statusMutation={statusMutation}
          onStartCreate={handleStartCreate}
        />
      )}

      {activeView === 'create' && (
        <InvoiceCreateView
          items={items}
          selectedDeliveries={selectedDeliveries}
          expandedItems={expandedItems}
          previewData={previewData}
          invoiceNotes={invoiceNotes}
          invoiceDueDate={invoiceDueDate}
          invoiceDiscount={invoiceDiscount}
          previewLoading={previewMutation.isPending}
          createLoading={createMutation.isPending}
          onToggleDelivery={toggleDelivery}
          onToggleAllForItem={toggleAllForItem}
          onSelectAll={selectAllAvailable}
          onClearSelection={clearSelection}
          onToggleItemExpand={toggleItemExpand}
          onPreview={handlePreview}
          onCreate={handleCreateInvoice}
          onNotesChange={setInvoiceNotes}
          onDueDateChange={setInvoiceDueDate}
          onDiscountChange={setInvoiceDiscount}
        />
      )}

      {activeView === 'detail' && viewingInvoiceId && (
        <InvoiceDetailView
          invoiceId={viewingInvoiceId}
          orderId={orderId}
          onStatusChange={handleStatusChange}
          statusPending={statusMutation.isPending}
        />
      )}

      <DisputeBlockedModal
        isOpen={!!blockedDispute}
        onClose={() => {
          setBlockedDispute(null);
          setAttemptedStatus(null);
        }}
        dispute={blockedDispute}
        attemptedStatus={attemptedStatus}
      />
    </div>
  );
};

// ==================== INVOICE LIST VIEW ====================
interface InvoiceListViewProps {
  invoices: InvoiceSummary[];
  availableDeliveryCount: number;
  canInvoice: boolean;
  onViewInvoice: (id: number) => void;
  onStatusChange: (id: number, status: InvoiceStatus) => void;
  statusMutation: any;
  onStartCreate: () => void;
}

const InvoiceListView: React.FC<InvoiceListViewProps> = ({
  invoices,
  availableDeliveryCount,
  canInvoice,
  onViewInvoice,
  onStatusChange,
  statusMutation,
  onStartCreate,
}) => {
  if (invoices.length === 0) {
    return (
      <div className="bg-white border-2 border-gray-200 rounded-xl p-12 text-center">
        <div className="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-5">
          <Receipt className="w-10 h-10 text-gray-300" />
        </div>
        <h3 className="text-xl font-bold text-gray-900 mb-2">No Invoices Yet</h3>
        <p className="text-gray-500 mb-6 max-w-md mx-auto">
          Create your first invoice by selecting deliveries from order items. You can create
          multiple partial invoices for different delivery batches.
        </p>
        {availableDeliveryCount > 0 && canInvoice && (
          <button
            onClick={onStartCreate}
            className="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 text-white rounded-lg font-bold hover:bg-emerald-700 transition-colors shadow-sm"
          >
            <Plus size={18} />
            Create First Invoice
          </button>
        )}
      </div>
    );
  }

  return (
    <div className="space-y-4">
      {invoices.map((invoice) => {
        const isCompleted = invoice.status === 'Completed';
        return (
          <div key={invoice.id} className="space-y-2">
            {invoice.has_open_dispute && invoice.open_dispute && (
              <OpenDisputeBanner dispute={invoice.open_dispute} />
            )}

            <div className="bg-white border-2 border-gray-200 rounded-xl p-5 hover:border-gray-300 transition-colors shadow-sm">
              <div className="flex items-start justify-between md:flex-row flex-col  align-start gap-4">
                <div className="flex-1">
                  <div className="flex items-center gap-3 mb-2">
                    <h4 className="text-lg font-bold text-gray-900">{invoice.invoice_number}</h4>
                    <span
                      className={`px-3 py-1 text-xs font-bold rounded-full border-2 ${getInvoiceStatusBadge(
                        invoice.status
                      )}`}
                    >
                      {invoice.status}
                    </span>
                    {isCompleted && (
                      <span className="inline-flex items-center gap-1 text-xs font-bold text-emerald-700">
                        <Lock className="w-3 h-3" />
                        Locked
                      </span>
                    )}
                  </div>

                  <div className="flex flex-wrap gap-x-6 gap-y-1 text-sm text-gray-600">
                    <span className="flex items-center gap-1.5">
                      <Calendar className="w-3.5 h-3.5" />
                      Issued: {invoice.issued_date ? formatDate(invoice.issued_date) : '—'}
                    </span>
                    <span className="flex items-center gap-1.5">
                      <Clock className="w-3.5 h-3.5" />
                      Due: {invoice.due_date ? formatDate(invoice.due_date) : '—'}
                    </span>
                    <span className="flex items-center gap-1.5">
                      <Package className="w-3.5 h-3.5" />
                      {invoice.items_count} line item{invoice.items_count !== 1 ? 's' : ''}
                    </span>
                    <span className="flex items-center gap-1.5">
                      <FileText className="w-3.5 h-3.5" />
                      By {invoice.created_by}
                    </span>
                  </div>
                </div>

                <div className="text-right flex flex-col items-start gap-3">
                  <div>
                    <p className="text-xs text-gray-500 font-medium text-start">Total Amount</p>
                    <p className="text-2xl font-bold text-gray-900">
                      {formatCurrency(invoice.total_amount)}
                    </p>
                  </div>

                  <div className="flex items-center gap-2">
                    <select
                      value={invoice.status}
                      onChange={(e) => onStatusChange(invoice.id, e.target.value as InvoiceStatus)}
                      disabled={statusMutation.isPending || isCompleted}
                      className="text-xs border-2 border-gray-200 rounded-lg px-2 py-1.5 font-medium focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                      {isCompleted ? (
                        <option value="Completed">Completed</option>
                      ) : (
                        INVOICE_STATUSES.map((s) => (
                          <option key={s} value={s}>
                            {s}
                          </option>
                        ))
                      )}
                    </select>

                    <button
                      onClick={() => onViewInvoice(invoice.id)}
                      className="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 border-2 border-emerald-200 rounded-lg hover:bg-emerald-100 transition-colors"
                    >
                      <Eye size={14} />
                      View
                    </button>
                  </div>
                </div>
              </div>

              <div className="mt-4 pt-3 border-t border-gray-100 flex flex-wrap gap-x-6 gap-y-1 text-xs text-gray-500">
                <span>Material: {formatCurrency(invoice.material_total)}</span>
                <span>Delivery: {formatCurrency(invoice.delivery_total)}</span>
                {invoice.surcharges_total > 0 && (
                  <span className="text-amber-700">Surcharges: {formatCurrency(invoice.surcharges_total)}</span>
                )}
                {invoice.testing_total > 0 && (
                  <span className="text-teal-700">Testing: {formatCurrency(invoice.testing_total)}</span>
                )}
                <span>GST: {formatCurrency(invoice.gst_tax)}</span>
                {(invoice.material_discount_total ?? 0) > 0 && (
                  <span className="text-emerald-600">Material Disc: -{formatCurrency(invoice.material_discount_total!)}</span>
                )}
                {invoice.discount > 0 && (
                  <span className="text-red-600">Discount: -{formatCurrency(invoice.discount)}</span>
                )}
                {invoice.balance_due > 0 && invoice.balance_due < invoice.total_amount && (
                  <span className="text-orange-600 font-semibold">Balance Due: {formatCurrency(invoice.balance_due)}</span>
                )}
              </div>
            </div>
          </div>
        );
      })}
    </div>
  );
};

// ==================== INVOICE CREATE VIEW ====================
interface InvoiceCreateViewProps {
  items: InvoiceableItem[];
  selectedDeliveries: Set<number>;
  expandedItems: Set<number>;
  previewData: InvoicePreviewData | null;
  invoiceNotes: string;
  invoiceDueDate: string;
  invoiceDiscount: string;
  previewLoading: boolean;
  createLoading: boolean;
  onToggleDelivery: (id: number) => void;
  onToggleAllForItem: (item: InvoiceableItem) => void;
  onSelectAll: () => void;
  onClearSelection: () => void;
  onToggleItemExpand: (id: number) => void;
  onPreview: () => void;
  onCreate: () => void;
  onNotesChange: (v: string) => void;
  onDueDateChange: (v: string) => void;
  onDiscountChange: (v: string) => void;
}

const InvoiceCreateView: React.FC<InvoiceCreateViewProps> = ({
  items,
  selectedDeliveries,
  expandedItems,
  previewData,
  invoiceNotes,
  invoiceDueDate,
  invoiceDiscount,
  previewLoading,
  createLoading,
  onToggleDelivery,
  onToggleAllForItem,
  onSelectAll,
  onClearSelection,
  onToggleItemExpand,
  onPreview,
  onCreate,
  onNotesChange,
  onDueDateChange,
  onDiscountChange,
}) => {
  return (
    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div className="lg:col-span-2 space-y-4">
        <div className="bg-white border-2 border-gray-200 rounded-xl p-4 shadow-sm">
          <div className="flex items-center justify-between">
            <div>
              <h3 className="font-bold text-gray-900">Select Deliveries to Invoice</h3>
              <p className="text-sm text-gray-500 mt-0.5">
                {selectedDeliveries.size} delivery{selectedDeliveries.size !== 1 ? 'es' : ''}{' '}
                selected
              </p>
            </div>
            <div className="flex items-center gap-2">
              <button
                onClick={onSelectAll}
                className="px-3 py-1.5 text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors"
              >
                Select All Available
              </button>
              {selectedDeliveries.size > 0 && (
                <button
                  onClick={onClearSelection}
                  className="px-3 py-1.5 text-xs font-bold text-gray-600 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors"
                >
                  Clear
                </button>
              )}
            </div>
          </div>
        </div>

        {items.map((item) => {
          const isExpanded = expandedItems.has(item.id);
          const availableDeliveries = item.deliveries.filter((d) => !d.is_invoiced);
          const invoicedDeliveries = item.deliveries.filter((d) => d.is_invoiced);
          const allAvailableSelected =
            availableDeliveries.length > 0 &&
            availableDeliveries.every((d) => selectedDeliveries.has(d.id));
          const someSelected = availableDeliveries.some((d) => selectedDeliveries.has(d.id));

          const selectedForItem = item.deliveries.filter((d) => selectedDeliveries.has(d.id));
          const selectedQty = selectedForItem.reduce((sum, d) => sum + d.quantity, 0);
          const selectedItemCost = selectedForItem.reduce(
            (sum, d) => sum + d.quantity * d.unit_cost,
            0
          );
          const selectedDeliveryCost = selectedForItem.reduce(
            (sum, d) => sum + d.delivery_cost,
            0
          );
          const selectedTotal = selectedItemCost + selectedDeliveryCost;

          return (
            <div
              key={item.id}
              className="bg-white border-2 border-gray-200 rounded-xl shadow-sm overflow-hidden"
            >
              <div
                className="flex items-center justify-between p-4 cursor-pointer hover:bg-gray-50 transition-colors"
                onClick={() => onToggleItemExpand(item.id)}
              >
                <div className="flex items-center gap-3 flex-1">
                  <div
                    onClick={(e) => {
                      e.stopPropagation();
                      onToggleAllForItem(item);
                    }}
                    className={`w-5 h-5 rounded border-2 flex items-center justify-center cursor-pointer transition-colors flex-shrink-0 ${
                      allAvailableSelected
                        ? 'bg-emerald-600 border-emerald-600'
                        : someSelected
                        ? 'bg-emerald-100 border-emerald-400'
                        : 'border-gray-300 hover:border-emerald-400'
                    }`}
                  >
                    {allAvailableSelected && <CheckCircle size={14} className="text-white" />}
                    {someSelected && !allAvailableSelected && (
                      <div className="w-2 h-2 rounded-sm bg-emerald-600" />
                    )}
                  </div>

                  <div className="flex-1 min-w-0">
                    <div className="flex items-center gap-2">
                      <Package size={16} className="text-gray-500 flex-shrink-0" />
                      <h4 className="font-bold text-gray-900 truncate">{item.product_name}</h4>
                      {item.is_quoted === 1 && (
                        <span className="px-2 py-0.5 text-[10px] font-bold bg-purple-100 text-purple-700 rounded-full border border-purple-200 flex-shrink-0">
                          QUOTED
                        </span>
                      )}
                    </div>
                    <div className="flex flex-wrap items-center gap-x-3 gap-y-0.5 mt-0.5">
                      <p className="text-xs text-gray-500">
                        {item.supplier_name} · {item.quantity} {item.unit_of_measure} total ·{' '}
                        {availableDeliveries.length} available, {invoicedDeliveries.length} invoiced
                      </p>
                      <span className="text-xs font-semibold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">
                        Unit: {formatCurrency(item.unit_cost)}/{item.unit_of_measure}
                      </span>
                    </div>
                  </div>
                </div>

                <div className="flex items-center gap-2 flex-shrink-0 ml-3">
                  {someSelected && (
                    <span className="px-2 py-1 text-xs font-bold bg-emerald-100 text-emerald-700 rounded-full">
                      {selectedForItem.length} selected
                    </span>
                  )}
                  {isExpanded ? (
                    <ChevronUp size={18} className="text-gray-400" />
                  ) : (
                    <ChevronDown size={18} className="text-gray-400" />
                  )}
                </div>
              </div>

              {selectedForItem.length > 0 && (
                <div className="mx-4 mb-2 p-3 bg-emerald-50 border border-emerald-200 rounded-lg">
                  <div className="flex items-center justify-between">
                    <p className="text-xs font-bold text-emerald-800">
                      Selected Summary ({selectedForItem.length} delivery{selectedForItem.length !== 1 ? 'es' : ''}, {selectedQty} {item.unit_of_measure})
                    </p>
                    <p className="text-sm font-bold text-emerald-800">
                      {formatCurrency(selectedTotal)}
                    </p>
                  </div>
                  <div className="flex gap-4 mt-1 text-[11px] text-emerald-700">
                    <span>
                      Items: {formatCurrency(selectedItemCost)}
                      <span className="text-emerald-500 ml-1">
                        ({selectedQty} × {formatCurrency(item.unit_cost)})
                      </span>
                    </span>
                    <span>Delivery: {formatCurrency(selectedDeliveryCost)}</span>
                  </div>
                </div>
              )}

              {isExpanded && (
                <div className="border-t-2 border-gray-100 p-4 bg-gray-50">
                  <div className="space-y-2">
                    {item.deliveries.map((delivery, idx) => (
                      <DeliveryRow
                        key={delivery.id}
                        delivery={delivery}
                        index={idx}
                        isSelected={selectedDeliveries.has(delivery.id)}
                        onToggle={() => onToggleDelivery(delivery.id)}
                        unitOfMeasure={item.unit_of_measure}
                      />
                    ))}
                  </div>
                </div>
              )}
            </div>
          );
        })}

        {items.length === 0 && (
          <div className="bg-white border-2 border-gray-200 rounded-xl p-8 text-center">
            <AlertCircle className="mx-auto text-gray-400 mb-3" size={40} />
            <p className="text-gray-600 font-medium">No items found for this order.</p>
          </div>
        )}
      </div>

      <div className="lg:col-span-1">
        <div className="sticky top-6 space-y-4">
          <button
            onClick={onPreview}
            disabled={selectedDeliveries.size === 0 || previewLoading}
            className="w-full flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
          >
            {previewLoading ? (
              <>
                <Loader2 size={18} className="animate-spin" />
                Calculating...
              </>
            ) : (
              <>
                <DollarSign size={18} />
                Preview Invoice ({selectedDeliveries.size} deliveries)
              </>
            )}
          </button>

          {previewData && (
            <div className="bg-white border-2 border-emerald-200 rounded-xl p-5 shadow-sm">
              <h4 className="font-bold text-gray-900 mb-4 flex items-center gap-2">
                <Receipt size={18} className="text-emerald-600" />
                Invoice Preview
              </h4>

              <div className="space-y-3 mb-4 max-h-96 overflow-y-auto">
                {previewData.line_items.map((line, i) => {
                  const surcharges = line.surcharges ?? [];
                  const testingFees = line.testing_fees ?? [];
                  const materialTotal = line.material_total ?? (line.quantity * line.unit_price);
                  return (
                    <div key={i} className="pb-3 border-b border-gray-100 last:border-0">
                      <div className="flex items-start justify-between text-sm">
                        <div className="flex-1 min-w-0">
                          <p className="font-medium text-gray-900 truncate">{line.product_name}</p>
                          <p className="text-xs text-gray-500">
                            {line.quantity} units · {line.delivery_date || '—'}
                            {line.delivery_time ? ` · ${formatTime(line.delivery_time)}` : ''}
                          </p>
                          <p className="text-[10px] text-gray-400 mt-0.5">
                            Material: {formatCurrency(materialTotal)}
                            {(line.material_discount ?? 0) > 0 && (
                              <span className="text-emerald-600"> · Material Disc: −{formatCurrency(line.material_discount!)}</span>
                            )}
                            {line.delivery_cost > 0 && <> · Delivery: {formatCurrency(line.delivery_cost)}</>}
                          </p>
                        </div>
                        <span className="font-bold text-gray-900 ml-3 flex-shrink-0">
                          {formatCurrency(line.line_total)}
                        </span>
                      </div>

                      {surcharges.length > 0 && (
                        <div className="mt-1.5 ml-2 space-y-0.5">
                          {surcharges.map((s, idx) => (
                            <div key={idx} className="flex items-center justify-between text-[11px]">
                              <span className="flex items-center gap-1.5 text-amber-700">
                                <span className="font-mono bg-amber-100 px-1 rounded">
                                  {s.billing_code ?? 'SURCH'}
                                </span>
                                <span className="text-gray-600 truncate">{s.name}</span>
                              </span>
                              <span className="font-semibold text-amber-700 ml-2">
                                {formatCurrency(s.calculated_amount)}
                              </span>
                            </div>
                          ))}
                        </div>
                      )}

                      {testingFees.length > 0 && (
                        <div className="mt-1.5 ml-2 space-y-0.5">
                          {testingFees.map((t, idx) => (
                            <div key={idx} className="flex items-center justify-between text-[11px]">
                              <span className="flex items-center gap-1.5 text-teal-700">
                                <span className="font-mono bg-teal-100 px-1 rounded">
                                  {t.billing_code ?? 'TEST'}
                                </span>
                                <span className="text-gray-600 truncate">{t.name}</span>
                                {!t.included && (
                                  <span className="text-[9px] text-gray-400 italic">(excluded)</span>
                                )}
                              </span>
                              <span className={`font-semibold ml-2 ${t.included ? 'text-teal-700' : 'text-gray-400 line-through'}`}>
                                {t.amount_snapshot === 0 ? 'POA' : formatCurrency(t.amount_snapshot)}
                              </span>
                            </div>
                          ))}
                        </div>
                      )}
                    </div>
                  );
                })}
              </div>

              <div className="border-t-2 border-gray-100 pt-3 space-y-1.5 text-sm">
                <div className="flex justify-between text-gray-600">
                  <span>Material Total</span>
                  <span>{formatCurrency(previewData.material_total)}</span>
                </div>
                {(previewData.material_discount_total ?? 0) > 0 && (
                  <div className="flex justify-between text-emerald-600">
                    <span>Material Discount</span>
                    <span>−{formatCurrency(previewData.material_discount_total!)}</span>
                  </div>
                )}
                <div className="flex justify-between text-gray-600">
                  <span>Delivery Total</span>
                  <span>{formatCurrency(previewData.delivery_total)}</span>
                </div>
                {previewData.surcharges_total > 0 && (
                  <div className="flex justify-between text-amber-700">
                    <span>Surcharges</span>
                    <span>{formatCurrency(previewData.surcharges_total)}</span>
                  </div>
                )}
                {previewData.testing_total > 0 && (
                  <div className="flex justify-between text-teal-700">
                    <span>Testing Fees</span>
                    <span>{formatCurrency(previewData.testing_total)}</span>
                  </div>
                )}
                {previewData.discount > 0 && (
                  <div className="flex justify-between text-red-600">
                    <span>Discount</span>
                    <span>−{formatCurrency(previewData.discount)}</span>
                  </div>
                )}
                <div className="flex justify-between text-gray-600 pt-1.5 border-t border-gray-100">
                  <span>GST (10%)</span>
                  <span>{formatCurrency(previewData.gst_tax)}</span>
                </div>
                <div className="flex justify-between font-bold text-base text-gray-900 pt-2 border-t-2 border-gray-200">
                  <span>Total</span>
                  <span>{formatCurrency(previewData.total_amount)}</span>
                </div>
              </div>
            </div>
          )}

          {previewData && (
            <div className="bg-white border-2 border-gray-200 rounded-xl p-5 shadow-sm space-y-4">
              <h4 className="font-bold text-gray-900 text-sm">Invoice Options</h4>

              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">Due Date</label>
                <input
                  type="date"
                  value={invoiceDueDate}
                  onChange={(e) => onDueDateChange(e.target.value)}
                  min={new Date().toISOString().split('T')[0]}
                  className="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">
                  Discount ($)
                </label>
                <input
                  type="number"
                  value={invoiceDiscount}
                  onChange={(e) => onDiscountChange(e.target.value)}
                  placeholder="0.00"
                  min="0"
                  step="0.01"
                  className="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">Notes</label>
                <textarea
                  value={invoiceNotes}
                  onChange={(e) => onNotesChange(e.target.value)}
                  placeholder="Optional notes for this invoice..."
                  rows={3}
                  className="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 resize-none"
                />
              </div>
            </div>
          )}

          {previewData && (
            <button
              onClick={onCreate}
              disabled={createLoading}
              className="w-full flex items-center justify-center gap-2 px-4 py-3.5 bg-emerald-600 text-white rounded-xl font-bold hover:bg-emerald-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-md"
            >
              {createLoading ? (
                <>
                  <Loader2 size={18} className="animate-spin" />
                  Creating Invoice...
                </>
              ) : (
                <>
                  <CheckCircle size={18} />
                  Create Invoice
                </>
              )}
            </button>
          )}
        </div>
      </div>
    </div>
  );
};

// ==================== DELIVERY ROW ====================
interface DeliveryRowProps {
  delivery: InvoiceableDelivery;
  index: number;
  isSelected: boolean;
  onToggle: () => void;
  unitOfMeasure: string;
}

const DeliveryRow: React.FC<DeliveryRowProps> = ({ delivery, index, isSelected, onToggle, unitOfMeasure }) => {
  const isInvoiced = delivery.is_invoiced;
  const itemSubtotal = delivery.quantity * delivery.unit_cost;
  const rowTotal = itemSubtotal + delivery.delivery_cost;

  return (
    <div
      onClick={!isInvoiced ? onToggle : undefined}
      className={`flex flex-col gap-2 p-3 rounded-lg transition-all ${
        isInvoiced
          ? 'bg-gray-100 opacity-60 cursor-not-allowed'
          : isSelected
          ? 'bg-emerald-50 border-2 border-emerald-300 cursor-pointer'
          : 'bg-white border-2 border-gray-200 cursor-pointer hover:border-emerald-200'
      }`}
    >
      <div className="flex items-center gap-3">
        <div
          className={`w-5 h-5 rounded border-2 flex items-center justify-center flex-shrink-0 ${
            isInvoiced
              ? 'border-gray-300 bg-gray-200'
              : isSelected
              ? 'bg-emerald-600 border-emerald-600'
              : 'border-gray-300'
          }`}
        >
          {isInvoiced ? (
            <Lock size={12} className="text-gray-400" />
          ) : isSelected ? (
            <CheckCircle size={14} className="text-white" />
          ) : null}
        </div>

        <div className="flex-1 flex items-center gap-4 flex-wrap">
          <span className="px-2 py-0.5 bg-blue-100 text-blue-700 text-[10px] font-bold rounded border border-blue-200">
            #{index + 1}
          </span>

          <div className="flex items-center gap-1.5 text-sm">
            <Calendar size={13} className="text-gray-400" />
            <span className="font-medium text-gray-900">
              {delivery.delivery_date ? formatDate(delivery.delivery_date) : '—'}
            </span>
          </div>

          <div className="flex items-center gap-1.5 text-sm">
            <Clock size={13} className="text-gray-400" />
            <span className="text-gray-600">{formatTime(delivery.delivery_time)}</span>
          </div>

          <div className="flex items-center gap-1.5 text-sm">
            <Truck size={13} className="text-gray-400" />
            <span className="font-medium text-gray-900">
              {delivery.quantity} {unitOfMeasure}
            </span>
          </div>

          {/*
            FIX: was comparing against title-case ('Delivered' / 'Scheduled' /
            'Cancelled'), which never matched the lowercase DB enum. Now uses the
            shared deliveryStatus helpers so every status (scheduled, invoiced,
            paid, ordered_with_supplier, out_for_delivery, delivered,
            client_confirmed, delivery_issue, cancelled) renders correctly.
          */}
          <span
            className={`px-2 py-0.5 text-[10px] font-bold rounded-full border ${getDeliveryStatusBadgeClass(delivery.status)}`}
          >
            {getDeliveryStatusLabel(delivery.status)}
          </span>

          {delivery.supplier_confirms && (
            <CheckCircle size={14} className="text-green-500 flex-shrink-0" />
          )}
        </div>

        {isInvoiced && (
          <span className="px-2 py-1 text-[10px] font-bold bg-amber-100 text-amber-700 rounded border border-amber-200 flex-shrink-0">
            INVOICED
          </span>
        )}
      </div>

      {!isInvoiced && (
        <div className="flex items-center gap-4 ml-8 text-xs">
          <span className="text-gray-500">
            <DollarSign size={11} className="inline -mt-0.5" />
            Unit: {formatCurrency(delivery.unit_cost)}/{unitOfMeasure}
          </span>
          <span className="text-gray-500">
            Items: {formatCurrency(itemSubtotal)}
            <span className="text-gray-400 ml-1">
              ({delivery.quantity} × {formatCurrency(delivery.unit_cost)})
            </span>
          </span>
          {delivery.delivery_cost > 0 && (
            <span className="text-gray-500">
              <Truck size={11} className="inline -mt-0.5 mr-0.5" />
              Delivery: {formatCurrency(delivery.delivery_cost)}
            </span>
          )}
          <span className="font-bold text-gray-700 ml-auto">
            Total: {formatCurrency(rowTotal)}
          </span>
        </div>
      )}
    </div>
  );
};

// ==================== INVOICE DETAIL VIEW ====================
interface InvoiceDetailViewProps {
  invoiceId: number;
  orderId: number;
  onStatusChange: (id: number, status: InvoiceStatus) => void;
  statusPending: boolean;
}

const InvoiceDetailView: React.FC<InvoiceDetailViewProps> = ({ invoiceId, orderId, onStatusChange, statusPending }) => {
  const { data, isLoading } = useInvoiceDetail(invoiceId);

  // Phase 6 — Mark Completed mutation + confirmation modal state
  const markCompletedMutation = useMarkInvoiceCompleted();
  const [completeModalOpen, setCompleteModalOpen] = useState(false);

  // Phase 7 — role-gated visibility for the Mark Completed button.
  // Admin + support can mark invoices completed; accountant (who can
  // also reach this page in read-only mode) cannot see the action.
  const { role } = usePermissions();
  const canMarkCompletedByRole = role === 'admin' || role === 'support';

  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-16">
        <Loader2 className="animate-spin text-emerald-600 mr-3" size={32} />
        <span className="text-gray-600 font-medium">Loading invoice...</span>
      </div>
    );
  }

  const invoice = data?.data;
  if (!invoice) {
    return (
      <div className="bg-white border-2 border-red-200 rounded-xl p-8 text-center">
        <AlertCircle className="mx-auto text-red-500 mb-3" size={40} />
        <p className="text-red-600 font-medium">Invoice not found.</p>
      </div>
    );
  }

  // ── Phase 6 + 7: status- and role-derived flags ──
  const isCompleted = invoice.status === 'Completed';
  const hasOpenDispute = !!invoice.has_open_dispute;
  const canMarkCompleted =
    !isCompleted &&
    COMPLETABLE_INVOICE_STATUSES.includes(invoice.status) &&
    canMarkCompletedByRole;                                    // ← Phase 7
  const markCompletedDisabled = hasOpenDispute;
  const openDisputeNumber = invoice.open_dispute?.dispute_number;

  // Completion meta (only present when isCompleted)
  const completedAt = (invoice as any).completed_at as string | null | undefined;
  const completedBy = (invoice as any).completed_by as
    | { id: number; name: string; email: string }
    | string
    | null
    | undefined;
  const completedByName =
    typeof completedBy === 'string'
      ? completedBy
      : completedBy?.name ?? null;
  const xeroInvoiceId = invoice.xero_invoice_id;

  const handleConfirmMarkCompleted = () => {
    markCompletedMutation.mutate(
      { invoiceId: invoice.id, orderId },
      { onSuccess: () => setCompleteModalOpen(false) }
    );
  };

  return (
    <div className="space-y-5">
      {hasOpenDispute && invoice.open_dispute && (
        <OpenDisputeBanner dispute={invoice.open_dispute} />
      )}

      {isCompleted && (
        <div className="bg-emerald-50 border-2 border-emerald-300 rounded-xl p-4 flex items-start gap-3">
          <div className="p-2 bg-emerald-100 rounded-lg flex-shrink-0">
            <Lock className="w-5 h-5 text-emerald-700" />
          </div>
          <div className="flex-1 min-w-0">
            <p className="text-sm font-bold text-emerald-900">
              Invoice is locked
            </p>
            <p className="text-xs text-emerald-700 mt-0.5">
              This invoice is marked Completed
              {completedAt && (
                <>
                  {' '}on{' '}
                  <span className="font-semibold">{formatDate(completedAt)}</span>
                </>
              )}
              {completedByName && (
                <>
                  {' '}by{' '}
                  <span className="font-semibold">{completedByName}</span>
                </>
              )}
              . No further changes can be made.
            </p>
            {xeroInvoiceId && (
              <p className="text-xs text-emerald-700 mt-0.5">
                Synced to Xero ·{' '}
                <span className="font-mono font-bold">{xeroInvoiceId}</span>
              </p>
            )}
          </div>
        </div>
      )}

      <div className="bg-white border-2 border-gray-200 rounded-xl p-6 shadow-sm">
        <div className="flex items-start justify-between mb-6 flex-wrap gap-4">
          <div>
            <div className="flex items-center gap-3 mb-1 flex-wrap">
              <h3 className="text-2xl font-bold text-gray-900">{invoice.invoice_number}</h3>
              <span
                className={`px-3 py-1 text-xs font-bold rounded-full border-2 ${getInvoiceStatusBadge(
                  invoice.status
                )}`}
              >
                {invoice.status}
              </span>
              {isCompleted && (
                <span className="inline-flex items-center gap-1 text-xs font-bold text-emerald-700">
                  <Lock className="w-3 h-3" />
                  Locked
                </span>
              )}
            </div>
            <p className="text-sm text-gray-500">Created by {invoice.created_by}</p>
          </div>

          <div className="flex items-center gap-2 flex-wrap">
            {canMarkCompleted && (
              <div className="relative group">
                <button
                  onClick={() => setCompleteModalOpen(true)}
                  disabled={markCompletedDisabled || markCompletedMutation.isPending}
                  className="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-bold text-sm disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                >
                  {markCompletedMutation.isPending ? (
                    <Loader2 className="w-4 h-4 animate-spin" />
                  ) : (
                    <CheckCircle className="w-4 h-4" />
                  )}
                  Mark Completed
                </button>
                {markCompletedDisabled && (
                  <div className="absolute top-full right-0 mt-1 w-64 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity z-10 shadow-lg">
                    <div className="flex items-start gap-1.5">
                      <ShieldAlert className="w-3.5 h-3.5 flex-shrink-0 mt-0.5 text-amber-300" />
                      <div>
                        <p className="font-bold">Cannot complete</p>
                        <p className="text-gray-300 mt-0.5">
                          Open dispute{' '}
                          {openDisputeNumber && (
                            <span className="font-mono font-bold text-amber-300">
                              {openDisputeNumber}
                            </span>
                          )}{' '}
                          must be resolved first.
                        </p>
                      </div>
                    </div>
                  </div>
                )}
              </div>
            )}

            <div className="flex items-center gap-2">
              <label className="text-xs font-bold text-gray-600">
                {isCompleted ? 'Status:' : 'Change Status:'}
              </label>
              <select
                value={invoice.status}
                onChange={(e) =>
                  onStatusChange(invoice.id, e.target.value as InvoiceStatus)
                }
                disabled={statusPending || isCompleted}
                className="text-sm border-2 border-gray-200 rounded-lg px-3 py-1.5 font-medium focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 disabled:opacity-60 disabled:cursor-not-allowed"
              >
                {isCompleted ? (
                  <option value="Completed">Completed</option>
                ) : (
                  INVOICE_STATUSES.map((s) => {
                    const isBlocked =
                      hasOpenDispute && (s === 'Void' || s === 'Cancelled');
                    return (
                      <option key={s} value={s} disabled={isBlocked}>
                        {s}
                        {isBlocked ? ' — blocked (open dispute)' : ''}
                      </option>
                    );
                  })
                )}
              </select>
            </div>
          </div>
        </div>

        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div className="bg-gray-50 rounded-lg p-3">
            <p className="text-[10px] font-bold text-gray-500 uppercase tracking-wide">Order</p>
            <p className="text-sm font-bold text-gray-900 mt-0.5">{invoice.order.po_number}</p>
          </div>
          <div className="bg-gray-50 rounded-lg p-3">
            <p className="text-[10px] font-bold text-gray-500 uppercase tracking-wide">Client</p>
            <p className="text-sm font-bold text-gray-900 mt-0.5">{invoice.order.client_name}</p>
          </div>
          <div className="bg-gray-50 rounded-lg p-3">
            <p className="text-[10px] font-bold text-gray-500 uppercase tracking-wide">Issued</p>
            <p className="text-sm font-bold text-gray-900 mt-0.5">
              {invoice.issued_date ? formatDate(invoice.issued_date) : '—'}
            </p>
          </div>
          <div className="bg-gray-50 rounded-lg p-3">
            <p className="text-[10px] font-bold text-gray-500 uppercase tracking-wide">Due</p>
            <p className="text-sm font-bold text-gray-900 mt-0.5">
              {invoice.due_date ? formatDate(invoice.due_date) : '—'}
            </p>
          </div>
        </div>

        {isCompleted && (completedAt || completedByName || xeroInvoiceId) && (
          <div className="mt-4 pt-4 border-t border-gray-100 grid grid-cols-1 md:grid-cols-3 gap-4">
            {completedAt && (
              <div className="bg-emerald-50 rounded-lg p-3 border border-emerald-100">
                <p className="text-[10px] font-bold text-emerald-700 uppercase tracking-wide">
                  Completed At
                </p>
                <p className="text-sm font-bold text-gray-900 mt-0.5">
                  {formatDate(completedAt)}
                </p>
              </div>
            )}
            {completedByName && (
              <div className="bg-emerald-50 rounded-lg p-3 border border-emerald-100">
                <p className="text-[10px] font-bold text-emerald-700 uppercase tracking-wide">
                  Completed By
                </p>
                <p className="text-sm font-bold text-gray-900 mt-0.5">
                  {completedByName}
                </p>
              </div>
            )}
            {xeroInvoiceId && (
              <div className="bg-emerald-50 rounded-lg p-3 border border-emerald-100">
                <p className="text-[10px] font-bold text-emerald-700 uppercase tracking-wide">
                  Xero Invoice ID
                </p>
                <p className="text-sm font-mono font-bold text-gray-900 mt-0.5 truncate">
                  {xeroInvoiceId}
                </p>
              </div>
            )}
          </div>
        )}

        {invoice.notes && (
          <div className="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
            <p className="text-xs font-bold text-yellow-800 mb-0.5">Notes</p>
            <p className="text-sm text-yellow-900">{invoice.notes}</p>
          </div>
        )}
      </div>

      <div className="bg-white border-2 border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div className="px-5 py-3 bg-gray-50 border-b border-gray-200">
          <h4 className="font-bold text-gray-900 text-sm flex items-center gap-2">
            <Package size={16} className="text-emerald-600" />
            Line Items ({invoice.items.length})
          </h4>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b-2 border-gray-100">
                <th className="text-left px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wide">
                  Product
                </th>
                <th className="text-left px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wide">
                  Delivery Date
                </th>
                <th className="text-right px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wide">
                  Qty
                </th>
                <th className="text-right px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wide">
                  Unit Price
                </th>
                <th className="text-right px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wide">
                  Delivery
                </th>
                <th className="text-right px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wide">
                  Line Total
                </th>
              </tr>
            </thead>
            <tbody>
              {invoice.items.map((item) => (
                <React.Fragment key={item.id}>
                  <tr className="border-b border-gray-50 hover:bg-gray-50">
                    <td className="px-5 py-3">
                      <p className="font-medium text-gray-900">{item.product_name}</p>
                      <p className="text-xs text-gray-500">{item.unit_of_measure}</p>
                    </td>
                    <td className="px-5 py-3 text-gray-600">
                      <div className="flex items-center gap-1.5">
                        <Calendar size={13} className="text-gray-400" />
                        {item.delivery_date ? formatDate(item.delivery_date) : '—'}
                      </div>
                      {item.delivery_time && (
                        <p className="text-xs text-gray-400 mt-0.5">
                          {formatTime(item.delivery_time)}
                        </p>
                      )}
                    </td>
                    <td className="px-5 py-3 text-right font-medium text-gray-900">
                      {item.quantity}
                    </td>
                    <td className="px-5 py-3 text-right text-gray-600">
                      {formatCurrency(item.unit_price)}
                    </td>
                    <td className="px-5 py-3 text-right text-gray-600">
                      {formatCurrency(item.delivery_cost)}
                    </td>
                    <td className="px-5 py-3 text-right font-bold text-gray-900">
                      {formatCurrency(item.line_total)}
                    </td>
                  </tr>

                  {(item.material_discount ?? 0) > 0 && (
                    <tr className="border-b border-gray-50 bg-emerald-50/30">
                      <td className="px-5 py-1.5 pl-10" colSpan={5}>
                        <div className="flex items-center gap-2 text-xs text-gray-600">
                          <span className="font-mono text-[10px] bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded">
                            MAT-DISC
                          </span>
                          <span className="text-emerald-800">Material Discount</span>
                        </div>
                      </td>
                      <td className="px-5 py-1.5 text-right text-xs font-semibold text-emerald-700">
                        −{formatCurrency(item.material_discount!)}
                      </td>
                    </tr>
                  )}

                  {item.surcharges.map((s) => (
                    <tr key={`s-${s.id}`} className="border-b border-gray-50 bg-amber-50/30">
                      <td className="px-5 py-1.5 pl-10" colSpan={5}>
                        <div className="flex items-center gap-2 text-xs text-gray-600">
                          <span className="font-mono text-[10px] bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded">
                            {s.billing_code ?? 'SURCH'}
                          </span>
                          <span className="text-amber-800">{s.name}</span>
                          <span className="text-[10px] bg-amber-100 text-amber-700 px-1 py-0.5 rounded font-bold">
                            Surcharge
                          </span>
                        </div>
                      </td>
                      <td className="px-5 py-1.5 text-right text-xs font-semibold text-amber-700">
                        {formatCurrency(s.calculated_amount)}
                      </td>
                    </tr>
                  ))}

                  {item.testing_fees.map((t) => (
                    <tr key={`t-${t.id}`} className="border-b border-gray-50 bg-teal-50/30">
                      <td className="px-5 py-1.5 pl-10" colSpan={5}>
                        <div className="flex items-center gap-2 text-xs text-gray-600">
                          <span className="font-mono text-[10px] bg-teal-100 text-teal-700 px-1.5 py-0.5 rounded">
                            {t.billing_code ?? 'TEST'}
                          </span>
                          <span className="text-teal-800">{t.name}</span>
                          <span className="text-[10px] bg-teal-100 text-teal-700 px-1 py-0.5 rounded font-bold">
                            Testing
                          </span>
                          {!t.included && (
                            <span className="text-[10px] text-gray-400 italic">(excluded from billing)</span>
                          )}
                        </div>
                      </td>
                      <td className={`px-5 py-1.5 text-right text-xs font-semibold ${t.included ? 'text-teal-700' : 'text-gray-400 line-through'}`}>
                        {t.amount_snapshot === 0 ? 'POA' : formatCurrency(t.amount_snapshot)}
                      </td>
                    </tr>
                  ))}
                </React.Fragment>
              ))}
            </tbody>
          </table>
        </div>

        <div className="border-t-2 border-gray-200 bg-gray-50 px-5 py-4">
          <div className="max-w-sm ml-auto space-y-1.5">
            <div className="flex justify-between text-sm">
              <span className="text-gray-600">Material Total</span>
              <span className="font-medium">{formatCurrency(invoice.material_total)}</span>
            </div>
            {(invoice.material_discount_total ?? 0) > 0 && (
              <div className="flex justify-between text-sm text-emerald-700">
                <span>Material Discount</span>
                <span className="font-medium">−{formatCurrency(invoice.material_discount_total!)}</span>
              </div>
            )}
            <div className="flex justify-between text-sm">
              <span className="text-gray-600">Delivery Total</span>
              <span className="font-medium">{formatCurrency(invoice.delivery_total)}</span>
            </div>
            {invoice.surcharges_total > 0 && (
              <div className="flex justify-between text-sm text-amber-700">
                <span>Surcharges</span>
                <span className="font-medium">{formatCurrency(invoice.surcharges_total)}</span>
              </div>
            )}
            {invoice.testing_total > 0 && (
              <div className="flex justify-between text-sm text-teal-700">
                <span>Testing Fees</span>
                <span className="font-medium">{formatCurrency(invoice.testing_total)}</span>
              </div>
            )}
            {invoice.back_charges > 0 && (
              <div className="flex justify-between text-sm text-gray-600">
                <span>Back Charges</span>
                <span className="font-medium">{formatCurrency(invoice.back_charges)}</span>
              </div>
            )}
            {invoice.credits > 0 && (
              <div className="flex justify-between text-sm text-gray-600">
                <span>Credits</span>
                <span className="font-medium">−{formatCurrency(invoice.credits)}</span>
              </div>
            )}
            {invoice.refunds > 0 && (
              <div className="flex justify-between text-sm text-gray-600">
                <span>Refunds</span>
                <span className="font-medium">−{formatCurrency(invoice.refunds)}</span>
              </div>
            )}
            {invoice.discount > 0 && (
              <div className="flex justify-between text-sm text-red-600">
                <span>Discount</span>
                <span className="font-medium">−{formatCurrency(invoice.discount)}</span>
              </div>
            )}
            <div className="flex justify-between text-sm pt-2 border-t border-gray-200">
              <span className="text-gray-600">GST (10%)</span>
              <span className="font-medium">{formatCurrency(invoice.gst_tax)}</span>
            </div>
            <div className="flex justify-between text-lg font-bold pt-2 border-t-2 border-gray-300">
              <span className="text-gray-900">Total Amount</span>
              <span className="text-emerald-700">{formatCurrency(invoice.total_amount)}</span>
            </div>
            {invoice.amount_paid > 0 && (
              <>
                <div className="flex justify-between text-sm text-green-700 pt-1">
                  <span>Amount Paid</span>
                  <span className="font-medium">−{formatCurrency(invoice.amount_paid)}</span>
                </div>
                <div className="flex justify-between text-base font-bold pt-2 border-t border-gray-300">
                  <span className="text-gray-900">Balance Due</span>
                  <span className="text-orange-700">{formatCurrency(invoice.balance_due)}</span>
                </div>
              </>
            )}
          </div>
        </div>
      </div>

      <MarkCompletedConfirmModal
        isOpen={completeModalOpen}
        onClose={() => setCompleteModalOpen(false)}
        onConfirm={handleConfirmMarkCompleted}
        invoiceNumber={invoice.invoice_number}
        totalAmount={invoice.total_amount}
        isLoading={markCompletedMutation.isPending}
      />
    </div>
  );
};

export default OrderInvoicesTab;