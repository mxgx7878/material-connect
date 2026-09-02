// src/types/clientOrder.types.ts

import type { OrderStatus } from "../utils/orderStatus";
export type { OrderStatus } from "../utils/orderStatus";

export type PaymentStatus = 'Pending' | 'Partially Paid' | 'Paid' | 'Partial Refunded' | 'Refunded' | 'Requested';

// ==================== INVOICE TYPES ====================

export type InvoiceStatus =
  | 'Draft'
  | 'Sent'
  | 'Paid'
  | 'Partially Paid'
  | 'Overdue'
  | 'Cancelled'
  | 'Void'
  | 'Completed';



export interface ClientInvoiceSurchargeLine {
  id: number;
  surcharge_id: number | null;
  billing_code: string | null;
  name: string;
  amount_snapshot: number;
  calculated_amount: number;
}

export interface ClientInvoiceTestingFeeLine {
  id: number;
  testing_fee_id: number | null;
  billing_code: string | null;
  name: string;
  amount_snapshot: number;
  included: boolean;
}

export interface ClientInvoiceLineItem {
  id: number;
  product_name: string;
  quantity: number;
  unit_price: number;
  material_total: number;
  material_discount?: number;   // per-unit supplier discount × delivery qty
  delivery_cost: number;

  surcharges: ClientInvoiceSurchargeLine[];
  surcharges_total: number;

  testing_fees: ClientInvoiceTestingFeeLine[];
  testing_total: number;

  line_total: number;
  unit_of_measure: string;
  order_item_id: number;
  order_item_delivery_id: number | null;
  delivery_date: string | null;
  delivery_time: string | null;
  delivery_status: string | null;
}


export interface ClientInvoice {
  id: number;
  invoice_number: string;
  status: InvoiceStatus;
  issued_date: string | null;
  due_date: string | null;
  notes: string | null;

  // Financial Summary
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

  // Metadata
  created_by: string;
  created_at: string;
  completed_at?: string | null;
  completed_by?:| { id: number; name: string; email?: string }
    | number
    | null;
  has_open_dispute?: boolean;
  open_dispute?: {
    id: number;
    dispute_number: string;
    status: string;
  } | null;
  xero_invoice_id?: string | null;
  // Line Items
  items: ClientInvoiceLineItem[];
}

// ==================== PAY INVOICE RESPONSE ====================

export interface PayInvoiceResponse {
  success: boolean;
  message: string;
  data?: {
    invoice: {
      id: number;
      invoice_number: string;
      status: string;
      paid_at: string;
    };
    order: {
      id: number;
      payment_status: string;
    };
  };
}

// ==================== CLIENT INFO (from order detail) ====================

export interface ClientCompany {
  id: number;
  name: string;
  abn: string | null;
  address: string | null;
  phone: string | null;
  email: string | null;
}

export interface ClientInfo {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  profile_image: string | null;
  company: ClientCompany;
}

// ==================== CLIENT ORDER ====================

export interface ClientOrder {
  id: number;
  po_number: string;

  project: {
    id: number;
    name: string;
    added_by: number;
    site_contact_name?: string | null;
    site_contact_phone?: string | null;
    site_instructions?: string | null;
    delivery_address?: string | null;
    delivery_lat?: number | null;
    delivery_long?: number | null;
    created_at: string;
    updated_at: string;
  };

  // Client info (returned in detail API)
  client?: ClientInfo;

  // Delivery
  delivery_address: string;
  delivery_date: string;
  delivery_time: string;
  contact_person_name?: string | null;
  contact_person_number?: string | null;

    // Status
  order_status: OrderStatus;
  payment_status: PaymentStatus;

  // Counts / totals
  items_count: number;
  total_price: number;
  gst_tax: number;
  discount: number;

  repeat_order: boolean;
  order_info?: string | null;

  created_at: string;
  updated_at: string;
  reason?: string;

  // Pricing breakdown (unified PricingService)
  customer_item_gross?: number;       // before material discount
  material_discount_total?: number;   // Σ per-unit discount × qty
  customer_item_cost?: number;        // NET of material discount
  customer_delivery_cost?: number;
  supplier_item_cost?: number;
  supplier_delivery_cost?: number;
  other_charges?: number;
}

export interface ClientOrderMetrics {
  total_orders_count: number;
  active_count: number;
  processing_count: number;
  completed_count: number;
  cancelled_count: number;
}

export interface ProjectFilter {
  id: number;
  name: string;
}

export interface ClientOrderFilters {
  projects: ProjectFilter[];
  order_statuses: OrderStatus[];
  payment_statuses: PaymentStatus[];
  delivery_methods: string[];
}

export interface ClientOrdersListResponse {
  data: ClientOrder[];
  pagination: {
    per_page: number;
    current_page: number;
    total_pages: number;
    total_items: number;
    has_more_pages: boolean;
  };
  metrics: ClientOrderMetrics;
  projects?: ProjectFilter[];
  order_statuses?: OrderStatus[];
  payment_statuses?: PaymentStatus[];
  delivery_methods?: string[];
}

export interface ClientOrdersQueryParams {
  per_page?: number;
  search?: string;
  project_id?: string;
  order_status?: string;
  payment_status?: string;
  delivery_date?: string;
  delivery_method?: string;
  repeat_order?: string;
  sort?: string;
  dir?: string;
  details?: boolean;
  page?: number;
}

export interface RepeatOrderPayload {
  items: Array<{
    product_id: number;
    quantity: number;
  }>;
}

export interface ClientOrdersResponse {
  data: ClientOrderListItem[];
  pagination: {
    per_page: number;
    current_page: number;
    total_pages: number;
    total_items: number;
    has_more_pages: boolean;
  };
  metrics: ClientOrderMetrics;
  projects?: Array<{
    id: number;
    name: string;
  }>;
  order_statuses?: OrderStatus[];
  payment_statuses?: PaymentStatus[];
  delivery_methods?: string[];
}

export interface ClientOrderListItem {
  id: number;
  po_number: string;
  project_id: number;
  client_id: number;
  

  // Status
  order_status: OrderStatus;
  payment_status: PaymentStatus;

  delivery_address: string;
  delivery_date: string;
  delivery_time: string;
  delivery_method?: string;

  repeat_order: boolean;
  order_info?: string | null;

  created_at: string;
  updated_at: string;

  // Counts / totals for list view
  items_count: number;
  discount: number;
  total_price?: number;
  gst_tax?: number;

  // Pricing breakdown (optional, unified PricingService)
  customer_item_cost?: number;        // NET of material discount
  material_discount_total?: number;
  customer_delivery_cost?: number;

  // Legacy
  subtotal?: number;
  fuel_levy?: number;
  other_charges?: number;

  // Relationships
  project?: {
    id: number;
    name: string;
  };
}

export interface ClientOrderDetailResponse {
  success: boolean;
  data: ClientOrderDetail;
}

export interface ClientOrderDetail {
  order: ClientOrder;
  items: ClientOrderItem[];
  invoices: ClientInvoice[];
}

export interface ClientOrderItemDelivery {
  id: number;
  order_item_id: number;
  quantity: number | string;
  delivery_date: string;
  delivery_time: string | null;
  truck_type?: string | null;
  load_size?: string | null;
  time_interval?: string | null;
  delivery_cost?: number | string | null;
  status: string;
  invoice_id?: number | null;
  supplier_confirms?: boolean;
  created_at: string;
  updated_at: string;
  // Surcharge fields
  accelerator_type?: string | null;
  retarder_type?: string | null;
  aggregate_size?: string | null;
  slump_value?: number | null;
  oxide_fibre?: boolean | null;
  paver_delivery?: boolean | null;
  omc_conditioning?: boolean | null;
  additional_stabiliser?: boolean | null;
  saved_testing_fees?: Array<{
    id: number;
    testing_fee_id: number;
    billing_code: string | null;
    name: string;
    amount_snapshot: number;
  }>;
}

export interface ClientOrderItem {
  id: number;
  order_id: number;
  product_id: number;
  quantity: number | string;
  supplier_id?: number | null;
  supplier_unit_cost?: string | null;
  quoted_price?: string | null;
  is_quoted?: number;
  delivery_cost?: string | null;
  delivery_type?: string | null;
  supplier_discount?: string | null;
  supplier_confirms?: number;
  custom_blend_mix?: string | null;
  // Client-facing pricing (from backend unified PricingService)
  customer_unit_price?: number;   // unit × 1.5 (pre-discount)
  material_discount?: number;     // per-unit discount × qty
  customer_item_total?: number;   // final material charge, ex GST
  created_at: string;
  updated_at: string;
  product: {
    id: number;
    product_name: string;
    photo: string | null;
    unit_of_measure: string;
    specifications: string;
    product_type: string;
  };
  supplier?: {
    id: number;
    name: string;
    company_name: string;
  } | null;
  deliveries: ClientOrderItemDelivery[];
}


// ==================== STRIPE PAY INVOICE ====================

export interface StripePayInvoicePayload {
  payment_method_id: string;
  idempotency_key?: string;
}

export interface StripePayInvoiceResponse {
  success: boolean;
  message: string;
  status?: string; // present on 402 "not completed"
  data?: {
    invoice: { id: number; invoice_number: string; status: string; paid_at: string | null };
    order: { id: number; payment_status: string };
    payment: {
      stripe_invoice_id: string;
      stripe_payment_intent_id: string | null;
      amount: number;
      currency: string;
      status: string;
    };
  };
}