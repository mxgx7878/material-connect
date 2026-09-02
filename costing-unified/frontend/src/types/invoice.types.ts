// FILE PATH: src/types/invoice.types.ts

/**
 * Invoice Type Definitions
 * TypeScript interfaces for invoice generation module
 *
 * UPDATED FOR INVOICE COMPLETION FLOW:
 * - Added `Completed` to InvoiceStatus
 * - Added `completed_at` and `completed_by` fields on summary + detail
 * - Removed `xero` block from InvoiceStatusUpdateResponse (no Xero push on
 *   status changes anymore)
 * - Added MarkInvoiceCompletedResponse (Xero push happens HERE, and is mandatory)
 */

// ==================== INVOICE STATUS ====================
/**
 * Canonical invoice status union (matches Invoice::STATUSES on backend).
 * `Issued` / `Unpaid` from older code is gone — never persisted by the API.
 */
export type InvoiceStatus =
  | 'Draft'
  | 'Sent'
  | 'Paid'
  | 'Overdue'
  | 'Cancelled'
  | 'Void'
  | 'Completed';

/**
 * Subset where Mark Completed is permitted server-side.
 */
export const COMPLETABLE_INVOICE_STATUSES: InvoiceStatus[] = [
  'Sent',
  'Paid',
  'Overdue',
];

// ==================== INVOICEABLE DELIVERIES ====================

export interface InvoiceableDelivery {
  id: number;
  quantity: number;
  delivery_date: string | null;
  delivery_time: string | null;
  status: string;
  supplier_confirms: boolean;
  is_invoiced: boolean;
  invoice_id: number | null;
  unit_cost: number;
  delivery_cost: number;
}

export interface InvoiceableItem {
  id: number;
  product_name: string;
  unit_of_measure: string;
  quantity: number;
  supplier_name: string;
  supplier_id: number | null;
  is_quoted: number;
  quoted_price: number | null;
  is_paid: number;
  unit_cost: number;
  deliveries: InvoiceableDelivery[];
}

export interface InvoiceableDeliveriesResponse {
  success: boolean;
  data: {
    order_id: number;
    po_number: string;
    client: string;
    items: InvoiceableItem[];
  };
}

// ==================== SHARED: SURCHARGE / TESTING FEE ====================

export interface InvoiceSurchargeLine {
  id?: number;                // present on persisted records
  surcharge_id: number | null;
  billing_code: string | null;
  name: string;
  amount_snapshot: number;
  calculated_amount: number;
}

export interface InvoiceTestingFeeLine {
  id?: number;                // present on persisted records
  testing_fee_id: number | null;
  billing_code: string | null;
  name: string;
  amount_snapshot: number;
  included: boolean;
}

// ==================== INVOICE PREVIEW ====================

export interface InvoicePreviewLineItem {
  order_item_id: number;
  order_item_delivery_id: number;
  product_name: string;
  quantity: number;
  unit_price: number;
  material_total: number;
  material_discount?: number;   // per-unit supplier discount × delivery qty
  delivery_cost: number;
  surcharges: InvoiceSurchargeLine[];
  surcharges_total: number;
  testing_fees: InvoiceTestingFeeLine[];
  testing_total: number;
  line_total: number;
  delivery_date: string | null;
  delivery_time: string | null;
  delivery_status: string;
  supplier_confirms: boolean;
}

export interface InvoicePreviewData {
  line_items: InvoicePreviewLineItem[];
  material_total: number;
  material_discount_total?: number;
  delivery_total: number;
  surcharges_total: number;
  testing_total: number;
  back_charges: number;
  credits: number;
  refunds: number;
  discount: number;
  gst_tax: number;
  total_amount: number;
}

export interface InvoicePreviewResponse {
  success: boolean;
  data: InvoicePreviewData;
}


export interface InvoiceOpenDispute {
  id: number;
  dispute_number: string;
  status: string;
}

// ==================== COMPLETED-BY USER ====================
/**
 * Minimal user ref returned in `completed_by` when expanded on detail.
 */
export interface InvoiceCompletedByUser {
  id: number;
  name: string;
  email?: string;
}

// ==================== INVOICE SUMMARY (list) ====================

export interface InvoiceSummary {
  id: number;
  invoice_number: string;
  order_id: number;
  client_id: number;

  material_total: number;
  material_discount_total?: number;
  delivery_total: number;
  surcharges_total: number;
  testing_total: number;
  back_charges: number;
  credits: number;
  refunds: number;
  gst_tax: number;
  discount: number;
  total_amount: number;
  amount_paid: number;
  balance_due: number;

  status: InvoiceStatus;
  issued_date: string | null;
  due_date: string | null;
  notes: string | null;
  xero_invoice_id: string | null;
  items_count: number;
  created_by: string;
  created_at: string;

  // Completion fields (set when status becomes 'Completed')
  completed_at?: string | null;
  /** On list responses this is an integer FK or null. */
  completed_by?: number | null;

  has_open_dispute?: boolean;
  open_dispute?: InvoiceOpenDispute | null;
}

// ==================== INVOICE DETAIL ====================

export interface InvoiceDetailItem {
  id: number;
  product_name: string;
  unit_of_measure: string;
  quantity: number;
  unit_price: number;
  material_total: number;
  material_discount?: number;   // per-unit supplier discount × delivery qty
  delivery_cost: number;

  surcharges: InvoiceSurchargeLine[];
  surcharges_total: number;

  testing_fees: InvoiceTestingFeeLine[];
  testing_total: number;

  line_total: number;
  delivery_date: string | null;
  delivery_time: string | null;
  delivery_status: string;
}

export interface InvoiceDetail {
  id: number;
  invoice_number: string;
  status: InvoiceStatus;
  issued_date: string | null;
  due_date: string | null;
  notes: string | null;
  xero_invoice_id: string | null;
  created_by: string;
  created_at: string;

  order: {
    id: number;
    po_number: string;
    delivery_address: string;
    client_name: string;
    client_email: string;
  };

  material_total: number;
  material_discount_total?: number;
  delivery_total: number;
  surcharges_total: number;
  testing_total: number;
  back_charges: number;
  credits: number;
  refunds: number;
  gst_tax: number;
  discount: number;
  total_amount: number;
  amount_paid: number;
  balance_due: number;

  // Completion fields. On the detail endpoint `completed_by` is expanded to a
  // user object (or null). On older payloads it may still be an integer.
  completed_at?: string | null;
  completed_by?: InvoiceCompletedByUser | number | null;

  has_open_dispute?: boolean;
  open_dispute?: InvoiceOpenDispute | null;
  items: InvoiceDetailItem[];
}

// ==================== API RESPONSES ====================

export interface InvoiceListResponse {
  success: boolean;
  data: InvoiceSummary[];
}

export interface InvoiceDetailResponse {
  success: boolean;
  data: InvoiceDetail;
}

/**
 * Invoice creation response.
 * NOTE: Xero is no longer pushed at creation — `xero_invoice_id` stays `null`
 * until the invoice is marked Completed. `xero_warning` may still appear but
 * will always be `null` for backward compatibility.
 */
export interface InvoiceCreateResponse {
  success: boolean;
  message: string;
  data: InvoiceSummary & {
    xero_synced?: boolean;
    xero_invoice_id?: string | null;
  };
  xero_warning?: string | null;
}

/**
 * Invoice status update response.
 * BREAKING: the old `xero` block (InvoiceXeroResult) is removed — status
 * changes no longer push to Xero.
 */
export interface InvoiceStatusUpdateResponse {
  success: boolean;
  message: string;
  data: {
    id: number;
    status: InvoiceStatus;
  };
  /** Omitted on older responses; defaults to false in UI. */
  has_open_dispute?: boolean;
}


export interface InvoiceStatusConflictError {
  status: 409;
  message: string;
  open_dispute: {
    id: number;
    dispute_number: string;
    status: string;
  };
}

// ==================== MARK INVOICE COMPLETED ====================

/**
 * Xero meta returned by the Mark Completed endpoint.
 * Xero is now MANDATORY for completion — on success the invoice will always
 * have a `xero_invoice_id`, and any Xero failure is surfaced as an error
 * (not a warning). So `pushed` is effectively always `true` on a successful
 * response; we keep the shape verbatim for transparency.
 */
export interface MarkInvoiceCompletedXeroMeta {
  pushed: boolean;
  xero_invoice_id: string;
  xero_invoice_number: string;
  xero_status: string;
}

/**
 * Summary of disputes attached to the invoice at completion time.
 * Embedded inside the Mark Completed response so admin sees the resolution
 * adjustments that just got bundled into Xero.
 */
export interface MarkInvoiceCompletedDisputeSummary {
  id: number;
  dispute_number: string;
  status: string;
  resolution_outcome: string | null;
  resolution_amount: string | null;
  resolution_lines?: Array<{
    id: number;
    description: string;
    quantity: string;
    amount: string;
  }>;
}

export interface MarkInvoiceCompletedData
  extends Omit<InvoiceSummary, 'completed_by'> {
  /** Always expanded to a user object on this response. */
  completed_by: InvoiceCompletedByUser;
  /** Always non-null on success (Xero push is mandatory). */
  xero_invoice_id: string;
  items?: InvoiceDetailItem[];
  disputes?: MarkInvoiceCompletedDisputeSummary[];
}

export interface MarkInvoiceCompletedResponse {
  success: boolean;
  message: string;
  data: MarkInvoiceCompletedData;
  xero: MarkInvoiceCompletedXeroMeta;
}

// ==================== PAYLOADS ====================

export interface InvoicePreviewPayload {
  delivery_ids: number[];
  discount?: number;
}

export interface InvoiceCreatePayload {
  delivery_ids: number[];
  notes?: string;
  due_date?: string;
  discount?: number;
}

export interface InvoiceStatusPayload {
  status: InvoiceStatus;
}