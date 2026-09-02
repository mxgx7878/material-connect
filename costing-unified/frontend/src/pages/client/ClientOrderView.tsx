// src/pages/client/ClientOrderView.tsx
// Updated: Proper invoice view with From/To, no delivery costs, Mark as Paid

import { useState } from "react";
import React from "react";
import { useParams, useNavigate } from "react-router-dom";
import {
  ArrowLeft,
  MapPin,
  Calendar,
  Truck,
  Package,
  RefreshCw,
  Building2,
  Info,
  DollarSign,
  User,
  Phone,
  ClipboardList,
  XCircle,
  ChevronDown,
  ChevronUp,
  Clock,
  CheckCircle,
  Edit,
  FileText,
  Calculator,
  Receipt,
  BookOpen,
  X,
  Plus,
  Minus,
  Lock,
  Pencil,
  CalendarClock,
  Trash2,
  SplitSquareHorizontal,
  Loader2,
  CreditCard,
  Eye,
  ShieldAlert,    
  Shield,         
  ExternalLink,   
  Scale,
} from "lucide-react";
import { format } from "date-fns";
import toast from "react-hot-toast";
import {
  useClientOrderDetail,
  useCancelOrder,
  usePayInvoice,
  canCancelOrder,
  useConfirmOrder
} from "../../features/clientOrders/hooks";
import DashboardLayout from "../../components/layout/DashboardLayout";
import ConfirmationModal from "../../components/common/ConfirmationModal";
import RaiseDisputeModal from "../../components/client/Disputes/RaiseDisputeModal";
import StripePayInvoiceModal from "../../components/client/StripePayInvoiceModal";
import { clientMenuItems } from "../../utils/menuItems";
import type {
  ClientInvoice,
  InvoiceStatus,
  ClientOrder,
} from "../../types/clientOrder.types";
import ClientOrderCostingTab from '../../components/order/ClientOrderCostingTab';
import { getOrderStatusBadgeClass, getOrderStatusLabel } from '../../utils/orderStatus';
import { useConfirmDelivery } from "../../features/clientOrders/hooks";
import { getDeliveryStatusBadgeClass, getDeliveryStatusLabel } from "../../utils/deliveryStatus";


// ==================== MATERIAL CONNECT COMPANY INFO ====================
// Update these when company details change
const COMPANY_INFO = {
  name: "Material Connect",
  legalName: "Material Connect Pty Ltd",
  abn: "30 683 624 106",
  address: "Sydney, NSW, Australia",
  phone: "0485 985 477",
  email: "support@materialconnect.com.au",
  remittanceEmail: "support@materialconnect.com.au",
  website: "www.materialconnect.com.au",
  logo: "https://demowebportals.com/material_connect/public/assets/img/logo-text.png",
  bank: {
    bsb: "084034",
    accountNumber: "889916362",
  },
};

const getInvoiceDisplayStatus = (
  status: InvoiceStatus,
): { label: string; className: string } => {
  switch (status) {
    case "Sent":
    return {
      label: "Awaiting Payment – Delivery Pending",
      className: "text-amber-...",
    };
    case "Paid":
      return {
        label: "Paid",
        className: "text-green-700 bg-green-50 border-green-200",
      };
    case "Overdue":
      return {
        label: "Overdue – Delivery On Hold",
        className: "text-red-700 bg-red-50 border-red-200",
      };
    case "Draft":
      return {
        label: "Draft",
        className: "text-gray-600 bg-gray-50 border-gray-200",
      };
    case "Cancelled":
    case "Void":
      return {
        label: status,
        className: "text-gray-600 bg-gray-50 border-gray-200",
      };
    default:
      return {
        label: status,
        className: "text-gray-600 bg-gray-50 border-gray-200",
      };
  }
};

// ==================== TYPES ====================
type TabType = "overview" | "items" | "invoices" | "costing";

// ==================== HELPERS ====================
const formatDate = (dateString: string) => {
  if (!dateString) return "-";
  try {
    return format(new Date(dateString), "MMM dd, yyyy");
  } catch {
    return "-";
  }
};

const formatDateTime = (dateString: string, timeString?: string) => {
  if (!dateString) return "-";
  try {
    const date = format(new Date(dateString), "MMM dd, yyyy");
    return timeString ? `${date} at ${timeString}` : date;
  } catch {
    return "-";
  }
};

const formatTime = (timeString: string) => {
  if (!timeString) return "-";
  try {
    return timeString.includes("T")
      ? format(new Date(timeString), "hh:mm a")
      : timeString;
  } catch {
    return timeString;
  }
};

const formatCurrency = (amount: number | string) => {
  const num = typeof amount === "string" ? parseFloat(amount) : amount;
  if (isNaN(num)) return "$0.00";
  return `$${num.toFixed(2)}`;
};



const getPaymentStatusColor = (status: string) => {
  const map: Record<string, string> = {
    Pending: "bg-yellow-100 text-yellow-700 border-yellow-300",
    "Partially Paid": "bg-orange-100 text-orange-700 border-orange-300",
    Paid: "bg-green-100 text-green-700 border-green-300",
    Requested: "bg-purple-100 text-purple-700 border-purple-300",
    Refunded: "bg-red-100 text-red-700 border-red-300",
    "Partial Refunded": "bg-orange-100 text-orange-700 border-orange-300",
  };
  return map[status] || "bg-gray-100 text-gray-700 border-gray-300";
};

const getInvoiceStatusColor = (status: InvoiceStatus) => {
  const map: Record<string, string> = {
    Draft: "bg-gray-100 text-gray-700 border-gray-300",
    Issued: "bg-blue-100 text-blue-700 border-blue-300",
    Sent: "bg-blue-100 text-blue-700 border-blue-300",
    Unpaid: "bg-yellow-100 text-yellow-700 border-yellow-300",
    Paid: "bg-green-100 text-green-700 border-green-300",
    Overdue: "bg-red-100 text-red-700 border-red-300",
    Cancelled: "bg-gray-100 text-gray-500 border-gray-300",
    Void: "bg-gray-100 text-gray-500 border-gray-300",
  };
  return map[status] || "bg-gray-100 text-gray-700 border-gray-300";
};
// Days remaining in the dispute window (7 days from invoice issue date, end of day).
// Returns null if no issued date, 0 if window has closed.
const getDisputeDaysRemaining = (issuedDate: string | null): number | null => {
  if (!issuedDate) return null;
  const deadline = new Date(issuedDate);
  deadline.setDate(deadline.getDate() + 7);
  deadline.setHours(23, 59, 59, 999);
  const diffMs = deadline.getTime() - Date.now();
  return Math.max(0, Math.ceil(diffMs / (1000 * 60 * 60 * 24)));
};

const isInvoiceDisputable = (invoice: ClientInvoice): boolean => {
  if (invoice.has_open_dispute) return false;
  if (invoice.status === "Void" || invoice.status === "Cancelled") return false;
  const days = getDisputeDaysRemaining(invoice.issued_date);
  return days !== null && days > 0;
};



// ==================== DETAIL ROW COMPONENT ====================
const DetailRow = ({
  label,
  value,
  icon,
}: {
  label: string;
  value: React.ReactNode;
  icon?: React.ReactNode;
}) => (
  <tr className="border-b border-gray-100 last:border-0">
    <td className="py-2.5 px-4 text-sm text-gray-500 font-medium w-[180px] whitespace-nowrap">
      <div className="flex items-center gap-2 ">
        {icon}
        {label}
      </div>
    </td>
    <td className="py-2.5 px-4 text-sm text-gray-900 font-medium">
      {value || "-"}
    </td>
  </tr>
);

// ==================== GUIDELINES PANEL ====================
const GuidelinesPanel = ({
  isOpen,
  onClose,
}: {
  isOpen: boolean;
  onClose: () => void;
}) => {
  if (!isOpen) return null;

  const sections = [
    {
      title: "Editing Your Order",
      color: "blue",
      items: [
        {
          icon: <Pencil className="w-3.5 h-3.5" />,
          text: "You can edit your order while it's under review, before supplier confirmation, via the Edit button in the header.",
        },
        {
          icon: <Plus className="w-3.5 h-3.5" />,
          text: 'Use "Add New Item" in the edit page to search products and add them with delivery schedules.',
        },
        {
          icon: <Minus className="w-3.5 h-3.5" />,
          text: "You can reduce item quantity but not below the already-delivered amount.",
        },
        {
          icon: <Trash2 className="w-3.5 h-3.5" />,
          text: "Items can only be removed if none of their deliveries have been completed yet.",
        },
      ],
    },
    {
      title: "Delivery Schedules",
      color: "indigo",
      items: [
        {
          icon: <SplitSquareHorizontal className="w-3.5 h-3.5" />,
          text: "Split deliveries allow you to receive parts of an item on different dates and times.",
        },
        {
          icon: <CalendarClock className="w-3.5 h-3.5" />,
          text: "Delivery slots with a date before today are locked — they cannot be edited or removed.",
        },
        {
          icon: <Lock className="w-3.5 h-3.5" />,
          text: "Delivered or completed deliveries are read-only. Only scheduled slots can be changed.",
        },
        {
          icon: <Calculator className="w-3.5 h-3.5" />,
          text: "Total quantity across all delivery slots must equal the item quantity exactly.",
        },
      ],
    },
    {
      title: "Order Rules",
      color: "amber",
      items: [
        {
          icon: <XCircle className="w-3.5 h-3.5" />,
          text: "Orders can be cancelled any time before they're placed with the supplier.",
        },
        {
          icon: <CheckCircle className="w-3.5 h-3.5" />,
          text: "Each item requires supplier confirmation. Pending items are awaiting supplier acceptance.",
        },
        {
          icon: <DollarSign className="w-3.5 h-3.5" />,
          text: "Payment is requested after admin confirms your order. A payment section will appear when due.",
        },
        {
          icon: <Clock className="w-3.5 h-3.5" />,
          text: "Once an order is Completed or Cancelled, no further edits are possible.",
        },
      ],
    },
  ];

  const colorMap: Record<
    string,
    { bg: string; border: string; text: string; icon: string }
  > = {
    blue: {
      bg: "bg-blue-50",
      border: "border-blue-200",
      text: "text-blue-900",
      icon: "text-blue-600",
    },
    indigo: {
      bg: "bg-indigo-50",
      border: "border-indigo-200",
      text: "text-indigo-900",
      icon: "text-indigo-600",
    },
    amber: {
      bg: "bg-amber-50",
      border: "border-amber-200",
      text: "text-amber-900",
      icon: "text-amber-600",
    },
  };

  return (
    <div className="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
      <div className="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
        <h3 className="font-semibold text-gray-900 text-sm flex items-center gap-2">
          <BookOpen className="w-4 h-4 text-blue-600" />
          How to Manage Your Order
        </h3>
        <button
          onClick={onClose}
          className="p-1 hover:bg-gray-200 rounded-lg transition-colors"
        >
          <X className="w-4 h-4 text-gray-500" />
        </button>
      </div>
      <div className="p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
        {sections.map((section) => {
          const colors = colorMap[section.color];
          return (
            <div
              key={section.title}
              className={`${colors.bg} ${colors.border} border rounded-lg p-3`}
            >
              <h4 className={`font-semibold ${colors.text} text-xs mb-2`}>
                {section.title}
              </h4>
              <div className="space-y-2">
                {section.items.map((item, idx) => (
                  <div key={idx} className="flex items-start gap-2">
                    <span className={`${colors.icon} mt-0.5 flex-shrink-0`}>
                      {item.icon}
                    </span>
                    <p className="text-xs text-gray-700 leading-relaxed">
                      {item.text}
                    </p>
                  </div>
                ))}
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
};

// ==================== OVERVIEW TAB ====================
const OverviewTab = ({ order }: { order: any }) => (
  <div className="space-y-4">
    <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <div className="px-4 py-2.5 bg-gray-50 border-b border-gray-200">
        <h3 className="font-semibold text-gray-900 text-sm flex items-center gap-2">
          <ClipboardList className="w-4 h-4 text-blue-600" />
          Order Details
        </h3>
      </div>
      <table className="w-full">
        <tbody>
          <DetailRow
            label="PO Number"
            value={
              <span className="font-mono text-blue-600">{order.po_number}</span>
            }
            icon={<FileText className="w-3.5 h-3.5 text-gray-400" />}
          />
          <DetailRow
            label="Delivery Address"
            value={order.delivery_address}
            icon={<MapPin className="w-3.5 h-3.5 text-gray-400" />}
          />
          <DetailRow
            label="Delivery Date"
            value={formatDate(order.delivery_date)}
            icon={<Calendar className="w-3.5 h-3.5 text-gray-400" />}
          />
          <DetailRow
            label="Delivery Time"
            value={formatTime(order.delivery_time)}
            icon={<Clock className="w-3.5 h-3.5 text-gray-400" />}
          />
          {order.delivery_method && (
            <DetailRow
              label="Delivery Method"
              value={order.delivery_method}
              icon={<Truck className="w-3.5 h-3.5 text-gray-400" />}
            />
          )}
          <DetailRow
            label="Order Status"
            value={
              <span
                className={`px-2 py-0.5 text-xs font-semibold rounded-full border ${getOrderStatusBadgeClass(order.order_status)}`}
              >
                {getOrderStatusLabel(order.order_status)}
              </span>
            }
          />
          <DetailRow
            label="Payment Status"
            value={
              <span
                className={`px-2 py-0.5 text-xs font-semibold rounded-full border ${getPaymentStatusColor(order.payment_status)}`}
              >
                {order.payment_status}
              </span>
            }
          />
          <DetailRow
            label="Order Date"
            value={formatDate(order.created_at)}
            icon={<Calendar className="w-3.5 h-3.5 text-gray-400" />}
          />
          {order.updated_at && order.updated_at !== order.created_at && (
            <DetailRow
              label="Last Updated"
              value={formatDate(order.updated_at)}
              icon={<Clock className="w-3.5 h-3.5 text-gray-400" />}
            />
          )}
        </tbody>
      </table>
    </div>

    <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <div className="px-4 py-2.5 bg-gray-50 border-b border-gray-200">
        <h3 className="font-semibold text-gray-900 text-sm flex items-center gap-2">
          <Building2 className="w-4 h-4 text-blue-600" />
          Project & Contact
        </h3>
      </div>
      <table className="w-full">
        <tbody>
          <DetailRow
            label="Project"
            value={order.project?.name}
            icon={<Building2 className="w-3.5 h-3.5 text-gray-400" />}
          />
          {order.project?.site_contact_name && (
            <DetailRow
              label="Site Contact"
              value={order.project.site_contact_name}
              icon={<User className="w-3.5 h-3.5 text-gray-400" />}
            />
          )}
          {order.project?.site_contact_phone && (
            <DetailRow
              label="Site Phone"
              value={
                <a
                  href={`tel:${order.project.site_contact_phone}`}
                  className="text-blue-600 hover:underline"
                >
                  {order.project.site_contact_phone}
                </a>
              }
              icon={<Phone className="w-3.5 h-3.5 text-gray-400" />}
            />
          )}
          {order.contact_person_name && (
            <DetailRow
              label="Contact Person"
              value={order.contact_person_name}
              icon={<User className="w-3.5 h-3.5 text-gray-400" />}
            />
          )}
          {order.contact_person_number && (
            <DetailRow
              label="Contact Number"
              value={
                <a
                  href={`tel:${order.contact_person_number}`}
                  className="text-blue-600 hover:underline"
                >
                  {order.contact_person_number}
                </a>
              }
              icon={<Phone className="w-3.5 h-3.5 text-gray-400" />}
            />
          )}
          {order.project?.site_instructions && (
            <DetailRow
              label="Site Instructions"
              value={order.project.site_instructions}
              icon={<Info className="w-3.5 h-3.5 text-gray-400" />}
            />
          )}
        </tbody>
      </table>
    </div>
  </div>
);

// ==================== ITEMS TAB ====================
const ItemsTab = ({ items }: { items: any[] }) => {
  const confirmDelivery = useConfirmDelivery();
  const [expandedItems, setExpandedItems] = useState<Set<number>>(new Set());
  const toggleItem = (id: number) => {
    setExpandedItems((prev) => {
      const next = new Set(prev);
      next.has(id) ? next.delete(id) : next.add(id);
      return next;
    });
  };

  if (!items || items.length === 0) {
    return (
      <div className="bg-white rounded-xl border border-gray-200 p-8 text-center">
        <Package className="w-12 h-12 text-gray-300 mx-auto mb-3" />
        <h3 className="text-lg font-bold text-gray-900 mb-1">No items yet</h3>
        <p className="text-sm text-gray-500">This order doesn't have any items yet.</p>
      </div>
    );
  }

  const trips = (del: any): number | null => {
    if (!del.load_size) return null;
    const q = parseFloat(String(del.quantity));
    const l = parseFloat(String(del.load_size));
    if (!l) return null;
    return Math.ceil(q / l);
  };
  const intervalLabel = (v?: string) =>
    v === "60" ? "1 hr" : v === "120" ? "2 hrs" : v ? `${v} min` : null;

  return (
    <div className="space-y-3">
      {items.map((item: any) => {
        const isExpanded = expandedItems.has(item.id);
        const deliveries = item.deliveries || [];
        const uom = item.product?.unit_of_measure || "";
        const confirmedCount = deliveries.filter(
          (d: any) => d.status === "client_confirmed",
        ).length;

        return (
          <div
            key={item.id}
            className="bg-white rounded-xl border border-gray-200 overflow-hidden"
          >
            {/* Item header */}
            <button
              onClick={() => toggleItem(item.id)}
              className="w-full px-4 py-3 flex items-center justify-between hover:bg-gray-50 transition-colors"
            >
              <div className="flex items-center gap-3 min-w-0">
                <div className="w-9 h-9 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                  <Package className="w-4 h-4 text-purple-600" />
                </div>
                <div className="text-left min-w-0">
                  <p className="font-semibold text-gray-900 text-sm truncate">
                    {item.product?.product_name || `Product #${item.product_id}`}
                  </p>
                  <p className="text-xs text-gray-500">
                    Qty: {item.quantity} {uom} · {deliveries.length} delivery
                    {deliveries.length !== 1 ? "s" : ""}
                    {deliveries.length > 0 && (
                      <> · {confirmedCount}/{deliveries.length} confirmed</>
                    )}
                  </p>
                </div>
              </div>
              <div className="flex items-center gap-3">
                <span
                  className={`text-xs font-semibold px-2 py-0.5 rounded-full ${item.supplier_confirms ? "bg-green-100 text-green-700" : "bg-yellow-100 text-yellow-700"}`}
                >
                  {item.supplier_confirms ? "Confirmed" : "Pending"}
                </span>
                {isExpanded ? (
                  <ChevronUp className="w-4 h-4 text-gray-400" />
                ) : (
                  <ChevronDown className="w-4 h-4 text-gray-400" />
                )}
              </div>
            </button>

            {/* Delivery schedule — read-only table */}
            {isExpanded && deliveries.length > 0 && (
              <div className="border-t border-gray-100 bg-gray-50 px-3 py-3">
                <p className="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-2 px-1">
                  Delivery schedule
                </p>
                <div className="overflow-x-auto rounded-lg border border-gray-200 bg-white">
                  <table className="w-full text-sm">
                    <thead>
                      <tr className="bg-gray-50 border-b border-gray-200 text-[11px] uppercase tracking-wide text-gray-500">
                        <th className="text-left font-semibold px-3 py-2">Date &amp; time</th>
                        <th className="text-right font-semibold px-3 py-2">Qty</th>
                        <th className="text-left font-semibold px-3 py-2">Load / Trips</th>
                        <th className="text-left font-semibold px-3 py-2">Status</th>
                        <th className="text-right font-semibold px-3 py-2">Action</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                      {deliveries.map((del: any, idx: number) => {
                        const t = trips(del);
                        const il = intervalLabel(del.time_interval);
                        return (
                          <tr key={del.id || idx} className="hover:bg-gray-50/70">
                            <td className="px-3 py-2">
                              <span className="inline-flex items-center gap-1.5 text-gray-900">
                                <Calendar className="w-3.5 h-3.5 text-gray-400 flex-shrink-0" />
                                {formatDateTime(del.delivery_date, del.delivery_time)}
                              </span>
                            </td>
                            <td className="px-3 py-2 text-right font-semibold text-gray-900 whitespace-nowrap">
                              {del.quantity} {uom}
                            </td>
                            <td className="px-3 py-2 text-gray-600">
                              {del.load_size ? (
                                <span className="text-xs">
                                  {del.load_size} / load
                                  {il && <span className="text-gray-400"> · every {il}</span>}
                                  {t != null && (
                                    <span className="text-blue-600 font-medium"> · {t} trips</span>
                                  )}
                                </span>
                              ) : (
                                <span className="text-xs text-gray-400">—</span>
                              )}
                            </td>
                            <td className="px-3 py-2">
                              {del.status && (
                                <span
                                  className={`inline-block text-[11px] font-bold px-2 py-0.5 rounded-full border ${getDeliveryStatusBadgeClass(del.status)}`}
                                >
                                  {getDeliveryStatusLabel(del.status)}
                                </span>
                              )}
                            </td>
                            <td className="px-3 py-2 text-right">
                              {del.status === "delivered" && (
                                <button
                                  onClick={() => confirmDelivery.mutate(del.id)}
                                  disabled={confirmDelivery.isPending}
                                  className="px-3 py-1.5 text-xs font-bold text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50 transition-colors whitespace-nowrap"
                                >
                                  {confirmDelivery.isPending ? "Confirming…" : "Confirm received"}
                                </button>
                              )}
                              {del.status === "client_confirmed" && (
                                <span className="inline-flex items-center gap-1 text-xs font-bold text-green-700 whitespace-nowrap">
                                  ✓ Confirmed
                                </span>
                              )}
                            </td>
                          </tr>
                        );
                      })}
                    </tbody>
                  </table>
                </div>
              </div>
            )}
          </div>
        );
      })}

      {/* Footer summary */}
      <div className="bg-purple-50 border border-purple-200 rounded-lg px-4 py-2.5 flex items-center justify-between">
        <span className="text-xs text-purple-700 font-medium">
          {items.length} item{items.length !== 1 ? "s" : ""} · Total:{" "}
          <span className="font-bold text-gray-900">
            {items.reduce((s: number, i: any) => s + parseFloat(i.quantity || 0), 0)} units
          </span>
        </span>
        <span className="text-green-700 font-semibold text-xs">
          {items.every((i: any) => i.supplier_confirms) ? "All confirmed" : "Not all confirmed"}
        </span>
      </div>
    </div>
  );
};

// ==================== INVOICE VIEW MODAL (Tax Invoice Document) ====================
const InvoiceViewModal = ({
  invoice,
  order,
  isOpen,
  onClose,
}: {
  invoice: ClientInvoice | null;
  order: ClientOrder;
  isOpen: boolean;
  onClose: () => void;
}) => {
  // ==================== ALL HOOKS FIRST ====================
  const navigate = useNavigate();
  const [raiseDisputeOpen, setRaiseDisputeOpen] = useState(false);

  const chargesSummary = React.useMemo(() => {
    const surchargeMap = new Map<string, {
      billing_code: string | null;
      name: string;
      total: number;
      count: number;
    }>();

    const testingMap = new Map<string, {
      billing_code: string | null;
      name: string;
      total: number;
      count: number;
      hasPOA: boolean;
    }>();

    if (!invoice) {
      return { surcharges: [], testingFees: [] };
    }

    invoice.items.forEach((item) => {
      item.surcharges.forEach((s) => {
        const key = s.billing_code ?? `name-${s.name}`;
        const existing = surchargeMap.get(key);
        if (existing) {
          existing.total += s.calculated_amount;
          existing.count += 1;
        } else {
          surchargeMap.set(key, {
            billing_code: s.billing_code,
            name: s.name,
            total: s.calculated_amount,
            count: 1,
          });
        }
      });

      item.testing_fees
        .filter((t) => t.included)
        .forEach((t) => {
          const key = t.billing_code ?? `name-${t.name}`;
          const existing = testingMap.get(key);
          const isPOA = t.amount_snapshot === 0;
          if (existing) {
            existing.total += t.amount_snapshot;
            existing.count += 1;
            if (isPOA) existing.hasPOA = true;
          } else {
            testingMap.set(key, {
              billing_code: t.billing_code,
              name: t.name,
              total: t.amount_snapshot,
              count: 1,
              hasPOA: isPOA,
            });
          }
        });
    });

    return {
      surcharges: Array.from(surchargeMap.values()),
      testingFees: Array.from(testingMap.values()),
    };
  }, [invoice]);

  // ==================== EARLY RETURN AFTER HOOKS ====================
  if (!isOpen || !invoice) return null;

  const client = order.client;
  const clientCompany = client?.company;
  const displayStatus = getInvoiceDisplayStatus(invoice.status);

  return (
    <>
      {/* Backdrop */}
      <div
        className="fixed inset-0 bg-black/50 backdrop-blur-sm z-50"
        onClick={onClose}
      />

      {/* Modal */}
      <div className="fixed inset-0 z-50 flex items-start justify-center p-4 overflow-y-auto">
        <div
          className="bg-white rounded-2xl shadow-2xl w-full max-w-3xl my-8 border border-gray-200"
          onClick={(e) => e.stopPropagation()}
        >
          {/* Modal Header */}
          <div className="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-2xl">
            <h3 className="font-bold text-gray-900 flex items-center gap-2">
              <Receipt className="w-5 h-5 text-blue-600" />
              Invoice {invoice.invoice_number}
            </h3>
            <div className="flex items-center gap-2">
              <span
                className={`px-2.5 py-1 text-xs font-semibold rounded-full border ${displayStatus.className}`}
              >
                {displayStatus.label}
              </span>
              <button
                onClick={onClose}
                className="p-1.5 hover:bg-gray-200 rounded-lg transition-colors"
              >
                <X className="w-4 h-4 text-gray-500" />
              </button>
            </div>
          </div>

          {/* Open dispute banner */}
          {invoice.has_open_dispute && invoice.open_dispute && (
            <div className="bg-amber-50 border-b-2 border-amber-300 p-4 flex items-start gap-3">
              <ShieldAlert className="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" />
              <div className="flex-1 min-w-0">
                <p className="text-sm font-bold text-amber-900">
                  Dispute open on this invoice
                </p>
                <p className="text-xs text-amber-700 mt-0.5">
                  <span className="font-mono font-bold">
                    {invoice.open_dispute.dispute_number}
                  </span>{" "}
                  — currently {invoice.open_dispute.status.replace("_", " ")}
                </p>
              </div>
              <button
                onClick={() => {
                  onClose();
                  navigate(`/client/disputes/${invoice.open_dispute!.id}`);
                }}
                className="flex items-center gap-1.5 px-3 py-1.5 bg-amber-600 text-white text-xs font-bold rounded-lg hover:bg-amber-700 flex-shrink-0"
              >
                <ExternalLink className="w-3.5 h-3.5" />
                View
              </button>
            </div>
          )}

          {/* Raise Dispute CTA — eligible window */}
          {isInvoiceDisputable(invoice) && (
            <div className="bg-blue-50 border-b border-blue-200 px-4 py-3 flex items-center justify-between gap-3 flex-wrap">
              <div className="flex items-center gap-2 text-sm text-blue-900">
                <Shield className="w-4 h-4 text-blue-600 flex-shrink-0" />
                <span>
                  Something wrong? You have{" "}
                  <span className="font-bold">
                    {getDisputeDaysRemaining(invoice.issued_date)}{" "}
                    {getDisputeDaysRemaining(invoice.issued_date) === 1
                      ? "day"
                      : "days"}
                  </span>{" "}
                  left to raise a dispute.
                </span>
              </div>
              <button
                onClick={() => setRaiseDisputeOpen(true)}
                className="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700"
              >
                <Scale className="w-3.5 h-3.5" />
                Raise Dispute
              </button>
            </div>
          )}

          {/* Invoice Document */}
          <div className="p-8">
            {/* ── Company Header ── */}
            <div className="flex items-start justify-between mb-8">
              <div>
                <img
                  src={COMPANY_INFO.logo}
                  alt={COMPANY_INFO.name}
                  className="h-8 mb-3"
                  onError={(e) => {
                    (e.target as HTMLImageElement).style.display = "none";
                  }}
                />
                <h2 className="text-xl font-bold text-gray-900">
                  {COMPANY_INFO.name}
                </h2>
                <p className="text-sm font-medium text-gray-700">
                  {COMPANY_INFO.legalName}
                </p>
                {COMPANY_INFO.abn && (
                  <p className="text-sm text-gray-500 mt-0.5">
                    ABN: {COMPANY_INFO.abn}
                  </p>
                )}
                <p className="text-sm text-gray-500">{COMPANY_INFO.address}</p>
                <p className="text-sm text-gray-500">{COMPANY_INFO.phone}</p>
                <p className="text-sm text-gray-500">{COMPANY_INFO.email}</p>
                <p className="text-sm text-gray-500">{COMPANY_INFO.website}</p>
              </div>
              <div className="text-right">
                <h1 className="text-3xl font-bold text-gray-900 tracking-tight">
                  TAX INVOICE
                </h1>
                <p className="text-sm font-semibold text-blue-600 mt-1">
                  {invoice.invoice_number}
                </p>
              </div>
            </div>

            {/* ── Bill To + Invoice Details ── */}
            <div className="flex justify-between mb-8 gap-8">
              {/* Bill To */}
              <div className="flex-1">
                <p className="text-[11px] uppercase font-bold text-gray-400 tracking-wider mb-2">
                  Bill To
                </p>
                <div className="bg-gray-50 rounded-lg p-4 border border-gray-100">
                  {clientCompany?.name && (
                    <p className="font-bold text-gray-900 text-sm">
                      {clientCompany.name}
                    </p>
                  )}
                  {client?.name && (
                    <p className="text-sm text-gray-700">{client.name}</p>
                  )}
                  {client?.email && (
                    <p className="text-sm text-gray-500">{client.email}</p>
                  )}
                  {(client?.phone || clientCompany?.phone) && (
                    <p className="text-sm text-gray-500">
                      {client?.phone || clientCompany?.phone}
                    </p>
                  )}
                  {clientCompany?.abn && (
                    <p className="text-sm text-gray-500 mt-1">
                      ABN: {clientCompany.abn}
                    </p>
                  )}
                  {clientCompany?.address && (
                    <p className="text-sm text-gray-500">
                      {clientCompany.address}
                    </p>
                  )}
                </div>

                {/* Deliver To */}
                <p className="text-[11px] uppercase font-bold text-gray-400 tracking-wider mb-2 mt-4">
                  Deliver To
                </p>
                <div className="bg-gray-50 rounded-lg p-4 border border-gray-100">
                  <div className="flex items-start gap-2">
                    <MapPin className="w-3.5 h-3.5 text-gray-400 mt-0.5 flex-shrink-0" />
                    <div>
                      <p className="text-sm text-gray-700">
                        {order.delivery_address || "-"}
                      </p>
                      {order.project?.name && (
                        <p className="text-xs text-gray-500 mt-1">
                          Site / Job Ref: {order.project.name}
                        </p>
                      )}
                    </div>
                  </div>
                </div>
              </div>

              {/* Invoice Details */}
              <div className="w-[220px] flex-shrink-0">
                <p className="text-[11px] uppercase font-bold text-gray-400 tracking-wider mb-2">
                  Invoice Details
                </p>
                <div className="space-y-2">
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-500">Invoice Date:</span>
                    <span className="font-medium text-gray-900">
                      {invoice.issued_date
                        ? formatDate(invoice.issued_date)
                        : "-"}
                    </span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-500">Due Date:</span>
                    <span className="font-medium text-gray-900">
                      {invoice.due_date ? formatDate(invoice.due_date) : "-"}
                    </span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-500">PO Number:</span>
                    <span className="font-medium text-gray-900">
                      {order.po_number}
                    </span>
                  </div>
                  <div className="flex justify-between text-sm items-center">
                    <span className="text-gray-500">Status:</span>
                    <span
                      className={`text-[11px] font-semibold px-2 py-0.5 rounded-full border ${displayStatus.className}`}
                    >
                      {displayStatus.label}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            {/* ── Line Items Table ── */}
            <div className="mb-6">
              <table className="w-full">
                <thead>
                  <tr className="border-b-2 border-gray-900">
                    <th className="text-left py-3 text-xs font-bold text-gray-900 uppercase tracking-wider">
                      Item
                    </th>
                    <th className="text-left py-3 text-xs font-bold text-gray-900 uppercase tracking-wider w-[100px]">
                      Delivery Date
                    </th>
                    <th className="text-center py-3 text-xs font-bold text-gray-900 uppercase tracking-wider w-[80px]">
                      Qty
                    </th>
                    <th className="text-right py-3 text-xs font-bold text-gray-900 uppercase tracking-wider w-[100px]">
                      Unit Price
                    </th>
                    <th className="text-right py-3 text-xs font-bold text-gray-900 uppercase tracking-wider w-[100px]">
                      Amount
                    </th>
                  </tr>
                </thead>
                <tbody>
                  {invoice.items.map((item) => (
                    <React.Fragment key={item.id}>
                      {/* Main row: material */}
                      <tr className="border-b border-gray-100">
                        <td className="px-4 py-3">
                          <p className="font-medium text-gray-900">{item.product_name}</p>
                          <p className="text-xs text-gray-500">{item.unit_of_measure}</p>
                        </td>
                        <td className="px-4 py-3 text-gray-600 text-xs">
                          {item.delivery_date ? formatDate(item.delivery_date) : "—"}
                          {item.delivery_time && (
                            <p className="text-gray-400 mt-0.5">{item.delivery_time.substring(0, 5)}</p>
                          )}
                        </td>
                        <td className="px-4 py-3 text-right text-gray-900">{item.quantity}</td>
                        <td className="px-4 py-3 text-right text-gray-600">
                          {formatCurrency(item.unit_price)}
                        </td>
                        <td className="px-4 py-3 text-right font-semibold text-gray-900">
                          {formatCurrency(item.material_total)}
                        </td>
                      </tr>

                      {/* Material Discount sub-row */}
                      {(item.material_discount ?? 0) > 0 && (
                        <tr className="border-b border-gray-100 bg-emerald-50/40">
                          <td className="px-4 py-1.5 pl-8 text-xs text-emerald-700" colSpan={4}>
                            Material Discount
                          </td>
                          <td className="px-4 py-1.5 text-right text-xs font-semibold text-emerald-700">
                            −{formatCurrency(item.material_discount!)}
                          </td>
                        </tr>
                      )}

                      {/* Delivery sub-row */}
                      {item.delivery_cost > 0 && (
                        <tr className="border-b border-gray-100 bg-gray-50/50">
                          <td className="px-4 py-1.5 pl-8 text-xs text-gray-600" colSpan={4}>
                            Delivery charge
                          </td>
                          <td className="px-4 py-1.5 text-right text-xs text-gray-700">
                            {formatCurrency(item.delivery_cost)}
                          </td>
                        </tr>
                      )}

                      {/* Surcharge sub-rows */}
                      {item.surcharges.map((s) => (
                        <tr key={`s-${s.id}`} className="border-b border-gray-100 bg-amber-50/30">
                          <td className="px-4 py-1.5 pl-8" colSpan={4}>
                            <div className="flex items-center gap-2 text-xs">
                              <span className="font-mono text-[10px] bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded">
                                {s.billing_code ?? "SURCH"}
                              </span>
                              <span className="text-gray-700">{s.name}</span>
                            </div>
                          </td>
                          <td className="px-4 py-1.5 text-right text-xs font-semibold text-amber-700">
                            {formatCurrency(s.calculated_amount)}
                          </td>
                        </tr>
                      ))}

                      {/* Testing fee sub-rows */}
                      {item.testing_fees
                        .filter((t) => t.included)
                        .map((t) => (
                          <tr key={`t-${t.id}`} className="border-b border-gray-100 bg-teal-50/30">
                            <td className="px-4 py-1.5 pl-8" colSpan={4}>
                              <div className="flex items-center gap-2 text-xs">
                                <span className="font-mono text-[10px] bg-teal-100 text-teal-700 px-1.5 py-0.5 rounded">
                                  {t.billing_code ?? "TEST"}
                                </span>
                                <span className="text-gray-700">{t.name}</span>
                              </div>
                            </td>
                            <td className="px-4 py-1.5 text-right text-xs font-semibold text-teal-700">
                              {t.amount_snapshot === 0 ? "POA" : formatCurrency(t.amount_snapshot)}
                            </td>
                          </tr>
                        ))}
                    </React.Fragment>
                  ))}
                </tbody>
              </table>
            </div>

            {/* ── Surcharges & Additional Charges Summary ── */}
            {(chargesSummary.surcharges.length > 0 || chargesSummary.testingFees.length > 0) && (
              <div className="mb-6 border border-gray-200 rounded-lg overflow-hidden">
                <div className="px-4 py-2.5 bg-gradient-to-r from-amber-50 to-teal-50 border-b border-gray-200">
                  <p className="text-sm font-semibold text-gray-800">
                    Surcharges & Additional Charges
                  </p>
                  <p className="text-[11px] text-gray-500 mt-0.5">
                    Applied across delivery slots based on load size, timing, and service requirements
                  </p>
                </div>

                <div className="divide-y divide-gray-100">
                  {chargesSummary.surcharges.length > 0 && (
                    <div className="px-4 py-3">
                      <p className="text-[11px] font-bold text-amber-700 uppercase tracking-wide mb-2">
                        Surcharges
                      </p>
                      <div className="space-y-1.5">
                        {chargesSummary.surcharges.map((s, idx) => (
                          <div key={idx} className="flex items-center justify-between text-sm">
                            <div className="flex items-center gap-2 min-w-0 flex-1">
                              <span className="font-mono text-[10px] bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded flex-shrink-0">
                                {s.billing_code ?? "SURCH"}
                              </span>
                              <span className="text-gray-700 truncate">{s.name}</span>
                              {s.count > 1 && (
                                <span className="text-[10px] text-gray-500 flex-shrink-0">
                                  × {s.count} deliveries
                                </span>
                              )}
                            </div>
                            <span className="font-semibold text-amber-700 ml-3 flex-shrink-0">
                              {formatCurrency(s.total)}
                            </span>
                          </div>
                        ))}
                      </div>
                    </div>
                  )}

                  {chargesSummary.testingFees.length > 0 && (
                    <div className="px-4 py-3">
                      <p className="text-[11px] font-bold text-teal-700 uppercase tracking-wide mb-2">
                        Testing Services
                      </p>
                      <div className="space-y-1.5">
                        {chargesSummary.testingFees.map((t, idx) => (
                          <div key={idx} className="flex items-center justify-between text-sm">
                            <div className="flex items-center gap-2 min-w-0 flex-1">
                              <span className="font-mono text-[10px] bg-teal-100 text-teal-700 px-1.5 py-0.5 rounded flex-shrink-0">
                                {t.billing_code ?? "TEST"}
                              </span>
                              <span className="text-gray-700 truncate">{t.name}</span>
                              {t.count > 1 && (
                                <span className="text-[10px] text-gray-500 flex-shrink-0">
                                  × {t.count} deliveries
                                </span>
                              )}
                            </div>
                            <span className="font-semibold text-teal-700 ml-3 flex-shrink-0">
                              {t.hasPOA && t.total === 0 ? "POA" : formatCurrency(t.total)}
                            </span>
                          </div>
                        ))}
                      </div>
                    </div>
                  )}
                </div>
              </div>
            )}

            {/* ── Totals ── */}
            <div className="flex justify-end mb-6">
              <div className="w-full max-w-sm space-y-1.5">
                <div className="flex justify-between text-sm">
                  <span className="text-gray-600">Material Total</span>
                  <span className="text-gray-900 font-medium">{formatCurrency(invoice.material_total)}</span>
                </div>
                {((invoice as any).material_discount_total ?? 0) > 0 && (
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-600">Material Discount</span>
                    <span className="text-emerald-700 font-medium">−{formatCurrency((invoice as any).material_discount_total)}</span>
                  </div>
                )}
                <div className="flex justify-between text-sm">
                  <span className="text-gray-600">Delivery Total</span>
                  <span className="text-gray-900 font-medium">{formatCurrency(invoice.delivery_total)}</span>
                </div>
                {invoice.surcharges_total > 0 && (
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-600">Surcharges</span>
                    <span className="text-amber-700 font-medium">{formatCurrency(invoice.surcharges_total)}</span>
                  </div>
                )}
                {invoice.testing_total > 0 && (
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-600">Testing Fees</span>
                    <span className="text-teal-700 font-medium">{formatCurrency(invoice.testing_total)}</span>
                  </div>
                )}
                {invoice.back_charges > 0 && (
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-600">Back Charges</span>
                    <span className="text-gray-900 font-medium">{formatCurrency(invoice.back_charges)}</span>
                  </div>
                )}
                {invoice.credits > 0 && (
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-600">Credits</span>
                    <span className="text-gray-900 font-medium">−{formatCurrency(invoice.credits)}</span>
                  </div>
                )}
                {invoice.refunds > 0 && (
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-600">Refunds</span>
                    <span className="text-gray-900 font-medium">−{formatCurrency(invoice.refunds)}</span>
                  </div>
                )}
                {invoice.discount > 0 && (
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-600">Discount</span>
                    <span className="text-red-600 font-medium">−{formatCurrency(invoice.discount)}</span>
                  </div>
                )}
                <div className="flex justify-between text-sm pt-2 border-t border-gray-200">
                  <span className="text-gray-600">GST (10%)</span>
                  <span className="text-gray-900 font-medium">{formatCurrency(invoice.gst_tax)}</span>
                </div>
                <div className="flex justify-between text-base font-bold pt-2 border-t-2 border-gray-300">
                  <span className="text-gray-900">Total Amount</span>
                  <span className="text-blue-700">{formatCurrency(invoice.total_amount)}</span>
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

            {/* ── Payment Details Section ── */}
            <div className="bg-gray-50 border border-gray-200 rounded-lg p-5 mb-6">
              <p className="text-xs font-bold text-gray-900 uppercase tracking-wider mb-3">
                Payment Details
              </p>
              <div className="grid grid-cols-2 gap-6">
                <div>
                  <p className="text-xs font-semibold text-gray-600 mb-1.5">
                    Bank Details
                  </p>
                  <div className="space-y-1">
                    <div className="flex justify-between text-sm">
                      <span className="text-gray-500">BSB:</span>
                      <span className="font-medium text-gray-900">
                        {COMPANY_INFO.bank.bsb}
                      </span>
                    </div>
                    <div className="flex justify-between text-sm">
                      <span className="text-gray-500">Account Number:</span>
                      <span className="font-medium text-gray-900">
                        {COMPANY_INFO.bank.accountNumber}
                      </span>
                    </div>
                  </div>
                </div>
                <div>
                  <p className="text-xs font-semibold text-gray-600 mb-1.5">
                    Payment Reference
                  </p>
                  <p className="text-sm text-gray-700">
                    Please use{" "}
                    <span className="font-bold text-gray-900">
                      {invoice.invoice_number}
                    </span>{" "}
                    as your payment reference.
                  </p>
                </div>
              </div>
              <div className="mt-3 pt-3 border-t border-gray-200">
                <p className="text-xs font-semibold text-gray-600 mb-1">
                  Payment Terms
                </p>
                <p className="text-sm text-gray-700">
                  Payment must occur in full prior to delivery unless otherwise
                  agreed in writing.
                </p>
              </div>
              <div className="mt-3 pt-3 border-t border-gray-200 flex flex-wrap gap-x-6 gap-y-1 text-xs text-gray-500">
                <span>
                  Remittance:{" "}
                  <a
                    href={`mailto:${COMPANY_INFO.remittanceEmail}`}
                    className="text-blue-600 hover:underline"
                  >
                    {COMPANY_INFO.remittanceEmail}
                  </a>
                </span>
                <span>
                  Web:{" "}
                  <a
                    href={`https://${COMPANY_INFO.website}`}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="text-blue-600 hover:underline"
                  >
                    {COMPANY_INFO.website}
                  </a>
                </span>
              </div>
            </div>

            {/* ── Notes ── */}
            {invoice.notes && (
              <div className="border-t border-gray-200 pt-4 mb-4">
                <p className="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">
                  Notes
                </p>
                <p className="text-sm text-gray-600">{invoice.notes}</p>
              </div>
            )}

            {/* ── Terms & Conditions Footer ── */}
            <div className="border-t border-gray-200 pt-4">
              <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">
                Terms &amp; Conditions
              </p>
              <div className="space-y-1.5 text-[11px] text-gray-500 leading-relaxed">
                <p>
                  1. Payment must be received in full prior to dispatch or
                  delivery of goods unless otherwise agreed in writing.
                </p>
                <p>
                  2. Any bank charges, merchant fees, processing fees or
                  surcharges are the responsibility of the client.
                </p>
                <p>
                  3. All prices are inclusive of GST unless otherwise stated.
                </p>
                <p>
                  4. Goods remain the property of the supplier until paid in
                  full.
                </p>
                <p>
                  5. Any variations to quantity after invoicing may result in
                  adjustment invoices.
                </p>
                <p>
                  6. The client is responsible for ensuring suitable site access
                  for delivery.
                </p>
              </div>
              <div className="mt-4 pt-3 border-t border-gray-100 text-center">
                <p className="text-xs text-gray-400">
                  Thank you for your business &mdash; {COMPANY_INFO.legalName}
                </p>
                <p className="text-[10px] text-gray-400 mt-0.5">
                  {COMPANY_INFO.website} &middot; {COMPANY_INFO.email}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Raise Dispute Modal */}
      <RaiseDisputeModal
        isOpen={raiseDisputeOpen}
        onClose={() => setRaiseDisputeOpen(false)}
        invoice={invoice}
        onSuccess={(disputeId) => {
          onClose();
          navigate(`/client/disputes/${disputeId}`);
        }}
      />
    </>
  );
};

// ==================== INVOICE CARD ====================
const InvoiceCard = ({
  invoice,
  onView,
  onPay,
  onPayOnline,
  isPaying,
  payingInvoiceId,
}: {
  invoice: ClientInvoice;
  onView: (invoice: ClientInvoice) => void;
  onPay: (invoiceId: number) => void;
  onPayOnline: (invoice: ClientInvoice) => void;
  isPaying: boolean;
  payingInvoiceId: number | null;
}) => {
  const canPay =
    invoice.status !== "Paid" &&
    invoice.status !== "Cancelled" &&
    invoice.status !== "Void";
  const isThisOnePaying = isPaying && payingInvoiceId === invoice.id;

  return (
    <div className="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-sm transition-shadow">
      <div className="px-4 py-3 flex items-center justify-between">
        {/* Left: Invoice info */}
        <div className="flex md:flex-row flex-col items-center gap-3 min-w-0">
          <div
            className={`w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 ${
              invoice.status === "Paid" ? "bg-green-100" : "bg-amber-100"
            }`}
          >
            <Receipt
              className={`md:w-5 md:h-5 h-10 w-10 ${invoice.status === "Paid" ? "text-green-600" : "text-amber-600"}`}
            />
          </div>
          <div className="min-w-0">
            <div className="flex items-center gap-2">
              <p className="font-semibold text-gray-900 text-sm">
                {invoice.invoice_number}
              </p>
              <span
                className={`px-2 py-0.5 text-[11px] font-semibold rounded-full border ${getInvoiceStatusColor(invoice.status)}`}
              >
                {invoice.status}
              </span>
            </div>
            <p className="text-xs text-gray-500 mt-0.5">
              {invoice.issued_date
                ? `Issued ${formatDate(invoice.issued_date)}`
                : "No issue date"}
              {invoice.due_date && ` · Due ${formatDate(invoice.due_date)}`}
              {" · "}
              {invoice.items?.length || 0} item
              {(invoice.items?.length || 0) !== 1 ? "s" : ""}
            </p>
          </div>
        </div>

        {/* Right: Amount + Actions */}
        <div className="flex md:flex-row flex-col items-center gap-3">
          <p className="text-lg font-bold text-gray-900">
            {formatCurrency(invoice.total_amount)}
          </p>

          {/* View Invoice Button */}
          <button
            onClick={() => onView(invoice)}
            className="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50 transition-colors"
          >
            <Eye className="w-3.5 h-3.5" />
            View
          </button>

          {/* Pay Online (Stripe) */}
          {canPay && (
            <button
              onClick={() => onPayOnline(invoice)}
              className="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition-all"
            >
              <CreditCard className="w-3.5 h-3.5" />
              Pay Online
            </button>
          )}

          {/* Mark as Paid / Paid Badge */}
          {canPay ? (
            <button
              onClick={() => onPay(invoice.id)}
              disabled={isPaying}
              className="flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white text-xs font-semibold rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
            >
              {isThisOnePaying ? (
                <>
                  <Loader2 className="w-3.5 h-3.5 animate-spin" />
                  Processing...
                </>
              ) : (
                <>
                  <CreditCard className="w-3.5 h-3.5" />
                  Mark as Paid
                </>
              )}
            </button>
          ) : invoice.status === "Paid" ? (
            <span className="flex items-center gap-1 text-xs text-green-600 font-semibold px-3 py-1.5 bg-green-50 rounded-lg border border-green-200">
              <CheckCircle className="w-3.5 h-3.5" />
              Paid
            </span>
          ) : null}
        </div>
      </div>
    </div>
  );
};

// ==================== INVOICES TAB ====================
const InvoicesTab = ({
  invoices,
  order,
  orderId,
}: {
  invoices: ClientInvoice[];
  order: ClientOrder;
  orderId: number;
}) => {
  const payInvoiceMutation = usePayInvoice(orderId);
  const [payingInvoiceId, setPayingInvoiceId] = useState<number | null>(null);
  const [confirmModalOpen, setConfirmModalOpen] = useState(false);
  const [selectedInvoiceId, setSelectedInvoiceId] = useState<number | null>(
    null,
  );
  const [viewingInvoice, setViewingInvoice] = useState<ClientInvoice | null>(
    null,
  );
  const [payingOnlineInvoice, setPayingOnlineInvoice] = useState<ClientInvoice | null>(null);

  const handlePayOnlineClick = (invoice: ClientInvoice) => setPayingOnlineInvoice(invoice);

  const handlePayClick = (invoiceId: number) => {
    setSelectedInvoiceId(invoiceId);
    setConfirmModalOpen(true);
  };

  const handleConfirmPay = async () => {
    if (!selectedInvoiceId) return;
    setPayingInvoiceId(selectedInvoiceId);
    setConfirmModalOpen(false);
    try {
      await payInvoiceMutation.mutateAsync(selectedInvoiceId);
    } finally {
      setPayingInvoiceId(null);
      setSelectedInvoiceId(null);
    }
  };

  const selectedInvoice = invoices.find((inv) => inv.id === selectedInvoiceId);

  // Empty state
  if (!invoices || invoices.length === 0) {
    return (
      <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div className="px-4 py-2.5 bg-gray-50 border-b border-gray-200">
          <h3 className="font-semibold text-gray-900 text-sm flex items-center gap-2">
            <Receipt className="w-4 h-4 text-blue-600" />
            Invoices
          </h3>
        </div>
        <div className="flex flex-col items-center justify-center py-14 px-6">
          <div className="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mb-4">
            <Receipt className="w-8 h-8 text-gray-300" />
          </div>
          <h3 className="text-lg font-bold text-gray-900 mb-1">
            No Invoices Yet
          </h3>
          <p className="text-sm text-gray-500 text-center max-w-sm">
            Invoices will appear here once they are generated by the admin for
            your order.
          </p>
          <div className="mt-5 flex items-center gap-2 px-3 py-2 bg-blue-50 rounded-lg border border-blue-200">
            <Info className="w-3.5 h-3.5 text-blue-600" />
            <span className="text-[11px] text-blue-700 font-medium">
              Invoices are created per delivery and will show up after supplier
              confirmation
            </span>
          </div>
        </div>
      </div>
    );
  }

  // Summary stats
  const totalInvoices = invoices.length;
  const paidCount = invoices.filter((i) => i.status === "Paid").length;
  const unpaidCount = totalInvoices - paidCount;
  const totalAmount = invoices.reduce(
    (sum, i) => sum + (i.total_amount || 0),
    0,
  );
  const paidAmount = invoices
    .filter((i) => i.status === "Paid")
    .reduce((sum, i) => sum + (i.total_amount || 0), 0);
  const outstandingAmount = totalAmount - paidAmount;

  return (
    <div className="space-y-4">
      {/* Summary Cards */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div className="bg-white rounded-lg border border-gray-200 p-3 text-center">
          <p className="text-[11px] text-gray-500 uppercase font-semibold">
            Total Invoices
          </p>
          <p className="text-xl font-bold text-gray-900">{totalInvoices}</p>
        </div>
        <div className="bg-white rounded-lg border border-green-200 p-3 text-center">
          <p className="text-[11px] text-green-600 uppercase font-semibold">
            Paid
          </p>
          <p className="text-xl font-bold text-green-700">{paidCount}</p>
        </div>
        <div className="bg-white rounded-lg border border-amber-200 p-3 text-center">
          <p className="text-[11px] text-amber-600 uppercase font-semibold">
            Unpaid
          </p>
          <p className="text-xl font-bold text-amber-700">{unpaidCount}</p>
        </div>
        <div className="bg-white rounded-lg border border-blue-200 p-3 text-center">
          <p className="text-[11px] text-blue-600 uppercase font-semibold">
            Outstanding
          </p>
          <p className="text-xl font-bold text-blue-700">
            {formatCurrency(outstandingAmount)}
          </p>
        </div>
      </div>

      {/* Invoice Cards */}
      {invoices.map((invoice) => (
        <InvoiceCard
          key={invoice.id}
          invoice={invoice}
          onView={(inv) => setViewingInvoice(inv)}
          onPay={handlePayClick}
          onPayOnline={handlePayOnlineClick}
          isPaying={payInvoiceMutation.isPending}
          payingInvoiceId={payingInvoiceId}
        />
      ))}

      {/* Invoice View Modal */}
      <InvoiceViewModal
        invoice={viewingInvoice}
        order={order}
        isOpen={!!viewingInvoice}
        onClose={() => setViewingInvoice(null)}
      />

      {/* Stripe Pay Online Modal */}
      <StripePayInvoiceModal
        invoice={payingOnlineInvoice}
        orderId={orderId}
        isOpen={!!payingOnlineInvoice}
        onClose={() => setPayingOnlineInvoice(null)}
      />

      {/* Pay Confirmation Modal */}
      <ConfirmationModal
        isOpen={confirmModalOpen}
        onClose={() => {
          if (!payInvoiceMutation.isPending) {
            setConfirmModalOpen(false);
            setSelectedInvoiceId(null);
          }
        }}
        onConfirm={handleConfirmPay}
        title="Confirm Payment"
        message={
          selectedInvoice
            ? `Are you sure you want to mark invoice "${selectedInvoice.invoice_number}" (${formatCurrency(selectedInvoice.total_amount)}) as paid?`
            : "Are you sure you want to mark this invoice as paid?"
        }
        confirmText="Mark as Paid"
        variant="info"
        isLoading={payInvoiceMutation.isPending}
      />
    </div>
  );
};

// ==================== COSTING SIDEBAR ====================
const CostingSidebar = ({ order }: { order: any }) => (
  <div className="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div className="px-4 py-3 bg-gradient-to-r from-blue-600 to-indigo-600">
      <h3 className="font-semibold text-white flex items-center gap-2 text-sm">
        <Calculator className="w-4 h-4" />
        Items Total
      </h3>
    </div>
    <div className="p-4 space-y-2.5">
      <div className="flex justify-between items-center text-sm">
        <span className="text-gray-500">Items Cost</span>
        <span className="font-semibold text-gray-900">
          {formatCurrency(order.customer_item_cost || 0)}
        </span>
      </div>
      {/* <div className="flex justify-between items-center text-sm">
        <span className="text-gray-500">Delivery</span>
        <span className="text-xs font-medium text-green-600 bg-green-50 px-2 py-0.5 rounded-full">
          Included
        </span>
      </div> */}
      {/* <div className="flex justify-between items-center text-sm">
        <span className="text-gray-500">GST (10%)</span>
        <span className="font-semibold text-gray-900">
          {formatCurrency(order.gst_tax || 0)}
        </span>
      </div> */}
      {/* {order.discount && parseFloat(order.discount.toString()) > 0 && (
        <div className="flex justify-between items-center text-sm">
          <span className="text-green-600">Discount</span>
          <span className="font-semibold text-green-600">
            -{formatCurrency(order.discount)}
          </span>
        </div>
      )} */}
      {/* {order.other_charges &&
        parseFloat(order.other_charges.toString()) > 0 && (
          <div className="flex justify-between items-center text-sm">
            <span className="text-gray-500">Other Charges</span>
            <span className="font-semibold text-gray-900">
              {formatCurrency(order.other_charges)}
            </span>
          </div>
        )} */}

      {/* <div className="border-t-2 border-blue-200 pt-3 mt-1">
        <div className="flex justify-between items-center">
          <span className="text-sm font-bold text-gray-900">Total</span>
          <span className="text-xl font-bold text-blue-600">
            {formatCurrency(order.total_price - order.gst_tax || 0)}
          </span>
        </div>
      </div> */}
    </div>
  </div>
);



// ==================== MAIN COMPONENT ====================
const ClientOrderView = () => {
  const { orderId } = useParams();
  const navigate = useNavigate();
  const { data, isLoading, refetch } = useClientOrderDetail(Number(orderId));
  const cancelOrderMutation = useCancelOrder();
  const confirmOrderMutation = useConfirmOrder();
  const [activeTab, setActiveTab] = useState<TabType>("overview");
  const [cancelModalOpen, setCancelModalOpen] = useState(false);
  const [showGuidelines, setShowGuidelines] = useState(false);

  const handleRefresh = () => {
    refetch();
    toast.success("Order refreshed");
  };

  const handleCancelClick = () => setCancelModalOpen(true);

  const handleCancelConfirm = async () => {
    if (!orderId) return;
    try {
      await cancelOrderMutation.mutateAsync(Number(orderId));
      setCancelModalOpen(false);
    } catch {
      // handled by mutation
    }
  };

  const handleCancelModalClose = () => {
    if (!cancelOrderMutation.isPending) setCancelModalOpen(false);
  };
  
  // Loading
  if (isLoading) {
    return (
      <DashboardLayout menuItems={clientMenuItems}>
        <div className="flex items-center justify-center min-h-[60vh]">
          <div className="flex flex-col items-center gap-3">
            <div className="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin" />
            <p className="text-gray-400 text-sm">Loading order...</p>
          </div>
        </div>
      </DashboardLayout>
    );
  }

  // Not found
  if (!data?.data) {
    return (
      <DashboardLayout menuItems={clientMenuItems}>
        <div className="flex items-center justify-center min-h-[60vh]">
          <div className="text-center">
            <h2 className="text-lg font-bold text-gray-900 mb-1">
              Order not found
            </h2>
            <p className="text-gray-500 text-sm mb-4">
              This order doesn't exist or has been removed.
            </p>
            <button
              onClick={() => navigate("/client/orders")}
              className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium"
            >
              Back to Orders
            </button>
          </div>
        </div>
      </DashboardLayout>
    );
  }

  const { order, items, invoices } = data.data;
  const showCancelButton = canCancelOrder(order.order_status);

  const tabs: Array<{
    id: TabType;
    label: string;
    icon: React.ElementType;
    badge?: number;
    activeClass: string;
    inactiveClass: string;
  }> = [
    {
      id: "overview",
      label: "Overview",
      icon: FileText,
      activeClass: "bg-blue-600 text-white border-blue-700 shadow-sm",
      inactiveClass: "text-gray-600 hover:bg-gray-100 border-gray-200",
    },
    {
      id: "items",
      label: "Items & Delivery",
      icon: Package,
      badge: items?.length || 0,
      activeClass: "bg-purple-600 text-white border-purple-700 shadow-sm",
      inactiveClass: "text-gray-600 hover:bg-gray-100 border-gray-200",
    },
    {
      id: "invoices",
      label: "Invoices",
      icon: Receipt,
      badge: invoices?.length || 0,
      activeClass: "bg-amber-600 text-white border-amber-700 shadow-sm",
      inactiveClass: "text-gray-600 hover:bg-gray-100 border-gray-200",
    },
    {
      id: "costing" as TabType,
      label: "Costing",
      icon: Calculator,
      activeClass: "bg-green-600 text-white border-green-700 shadow-sm",
      inactiveClass: "text-gray-600 hover:bg-gray-100 border-gray-200",
    },
  ];

  return (
    <DashboardLayout menuItems={clientMenuItems}>
      <div className="space-y-4">
        {/* Header */}
        <div className="flex items-center gap-3">
          <button
            onClick={() => navigate("/client/orders")}
            className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
          >
            <ArrowLeft className="w-5 h-5 text-gray-600" />
          </button>
          <div className="flex-1 min-w-0">
            <h1 className="text-lg font-bold text-gray-900 truncate">
              Order {order.po_number}
            </h1>
            <p className="text-xs text-gray-500">
              Created {formatDate(order.created_at)}
            </p>
          </div>
          <div className="flex items-center gap-2">
            <span
              className={`px-2.5 py-1 text-xs font-semibold rounded-full border ${getOrderStatusBadgeClass(order.order_status)}`}
            >
              {getOrderStatusLabel(order.order_status)}
            </span>
            <span
              className={`px-2.5 py-1 text-xs font-semibold rounded-full border ${getPaymentStatusColor(order.payment_status)}`}
            >
              {order.payment_status}
            </span>
          </div>
        </div>

        {/* Action Buttons */}
        <div className="flex items-center gap-2 flex-wrap">
          <button
            onClick={handleRefresh}
            className="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <RefreshCw className="w-3.5 h-3.5" /> Refresh
          </button>
          {["Received", "Under Review", "Confirming Supply", "Awaiting Customer Confirmation"].includes(order.order_status) && (
            <button
              onClick={() => navigate(`/client/orders/${orderId}/edit`)}
              className="flex items-center gap-1.5 px-3 py-1.5 text-sm text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50 transition-colors"
            >
              <Edit className="w-3.5 h-3.5" /> Edit Order
            </button>
          )}
          {showCancelButton && (
            <button
              onClick={handleCancelClick}
              className="flex items-center gap-1.5 px-3 py-1.5 text-sm text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition-colors"
            >
              <XCircle className="w-3.5 h-3.5" /> Cancel Order
            </button>
          )}
          {order.order_status === 'Awaiting Customer Confirmation' && (
            <button
              onClick={() => {
                if (window.confirm('Confirm this order? Once confirmed, it locks and moves to payment — changes will need support.')) {
                  confirmOrderMutation.mutate(Number(orderId));
                }
              }}
              disabled={confirmOrderMutation.isPending}
              className="flex items-center gap-1.5 px-3 py-1.5 text-sm text-white bg-green-600 border border-green-700 rounded-lg hover:bg-green-700 disabled:opacity-50 transition-colors"
            >
              <CheckCircle className="w-3.5 h-3.5" />
              {confirmOrderMutation.isPending ? 'Confirming…' : 'Confirm Order'}
            </button>
          )}
          <button
            onClick={() => setShowGuidelines(!showGuidelines)}
            className={`flex items-center gap-1.5 px-3 py-1.5 text-sm border rounded-lg transition-colors ${
              showGuidelines
                ? "bg-blue-50 text-blue-700 border-blue-200"
                : "text-gray-500 border-gray-200 hover:bg-gray-50"
            }`}
          >
            <BookOpen className="w-3.5 h-3.5" /> How to Manage Order
          </button>
          <button
            onClick={() => navigate("/client/surcharge-guide")}
            className="flex items-center gap-1.5 px-3 py-1.5 text-sm text-amber-600 border border-amber-200 rounded-lg hover:bg-amber-50 transition-colors"
          >
            <DollarSign className="w-3.5 h-3.5" /> Surcharge Guide
          </button>
        </div>

        {/* Guidelines */}
        <GuidelinesPanel
          isOpen={showGuidelines}
          onClose={() => setShowGuidelines(false)}
        />

        {order.order_status === 'Awaiting Customer Confirmation' && (
          <div className="bg-amber-50 border border-amber-200 rounded-lg p-4 flex items-start gap-3">
            <Info className="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
            <div className="text-sm text-amber-800">
              <p className="font-semibold">Review your final pricing</p>
              <p className="text-xs mt-0.5">
                We've confirmed availability and pricing with the supplier. Please review the itemised costs, then confirm to proceed to payment. Once confirmed, the order is locked.
              </p>
            </div>
          </div>
        )}

        {/* Main Layout: Content + Costing Sidebar */}
        <div className="flex gap-5 items-start">
          <div className="flex-1 min-w-0 space-y-4">
            <div className="flex items-center gap-2 flex-wrap">
              {tabs.map((tab) => {
                const Icon = tab.icon;
                const isActive = activeTab === tab.id;
                return (
                  <button
                    key={tab.id}
                    onClick={() => setActiveTab(tab.id)}
                    className={`flex items-center sm:gap-1.5 sm:px-3.5 py-2 rounded-lg border text-xs md:text-sm font-semibold transition-all px-2 gap-1  whitespace-nowrap ${
                      isActive ? tab.activeClass : tab.inactiveClass
                    }`}
                  >
                    <Icon className="w-3.5 h-3.5" />
                    {tab.label}
                    {tab.badge !== undefined && (
                      <span
                        className={`px-1.5 py-0.5 text-[10px] rounded-full ${isActive ? "bg-white/20 text-white" : "bg-gray-100 text-gray-500"}`}
                      >
                        {tab.badge}
                      </span>
                    )}
                  </button>
                );
              })}
            </div>

            {activeTab === "overview" && <OverviewTab order={order} />}
            {activeTab === "items" && <ItemsTab items={items || []} />}
            {activeTab === "invoices" && (
              <InvoicesTab
                invoices={invoices || []}
                order={order}
                orderId={Number(orderId)}
              />
            )}
            {activeTab === "costing" && (
              <ClientOrderCostingTab orderId={Number(orderId)} order={order} items={items || []} />
            )}
          </div>

          {/* Costing Sidebar */}
          <div className="hidden lg:block w-[280px] flex-shrink-0 sticky top-6">
            <CostingSidebar order={order} />
          </div>
        </div>

        {/* Mobile Costing */}
        <div className="lg:hidden">
          <CostingSidebar order={order} />
        </div>

        {/* Cancel Modal */}
        <ConfirmationModal
          isOpen={cancelModalOpen}
          onClose={handleCancelModalClose}
          onConfirm={handleCancelConfirm}
          title="Cancel Order"
          message={`Are you sure you want to cancel order "${order.po_number}"? This action cannot be undone.`}
          confirmText="Cancel Order"
          variant="danger"
          isLoading={cancelOrderMutation.isPending}
        />
      </div>
    </DashboardLayout>
  );
};

export default ClientOrderView;