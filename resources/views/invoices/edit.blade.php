@extends('layouts.app')

@section('content')
<main class="content-area p-3 p-lg-4" id="invoiceEditApp">

  <!-- Breadcrumb & Page Header -->
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 d-print-none">
    <div>
      <nav aria-label="breadcrumb" class="mb-1">
        <ol class="breadcrumb mb-0 small">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bi bi-house-door me-1"></i>Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Commercial Invoices</a></li>
          <li class="breadcrumb-item"><a href="{{ route('invoices.show', $invoiceModel) }}">#{{ $invoiceModel->invoice_number }}</a></li>
          <li class="breadcrumb-item active" aria-current="page">Edit Docket</li>
        </ol>
      </nav>
      <div class="d-flex align-items-center gap-2">
        <h2 class="fw-bold text-dark mb-0" style="font-size: 1.6rem; letter-spacing: -0.02em;">Edit Invoice #{{ $invoiceModel->invoice_number }}</h2>
        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1" style="font-size: 0.78rem;">
          <i class="bi bi-database-check me-1"></i> Loaded from Database
        </span>
      </div>
      <p class="text-muted small mb-0 mt-1">
        Modify invoice particulars, secondary buyer details, shipping logistics, or line items. Everything updates the export document sheet live.
      </p>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap">
      <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 px-3 py-2">
        <i class="bi bi-arrow-left"></i>
        <span>All Invoices</span>
      </a>

      <a href="{{ route('invoices.show', $invoiceModel) }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-2 px-3 py-2">
        <i class="bi bi-eye"></i>
        <span>View Document</span>
      </a>

      <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 px-3 py-2" onclick="window.print()">
        <i class="bi bi-printer"></i>
        <span>Print</span>
      </button>

      <button type="submit" form="invoiceEditForm" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3.5 py-2 shadow-sm fw-semibold">
        <i class="bi bi-check2-circle"></i>
        <span>Update Invoice</span>
      </button>
    </div>
  </div>

  @if($errors->any())
  <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4 d-print-none" role="alert" style="border-radius: 10px;">
    <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Please resolve the following errors:</div>
    <ul class="mb-0 small ps-3">
      @foreach($errors->all() as $err)
        <li>{{ $err }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  <div class="row g-4">
    <!-- Left Column: Variable Controls Form (d-print-none) -->
    <div class="col-xl-4 d-print-none">
      
      <form id="invoiceEditForm" action="{{ route('invoices.update', $invoiceModel) }}" method="POST" onsubmit="return prepareInvoiceSubmission()">
        @csrf
        @method('PUT')

        <!-- Hidden Serialized Inputs for Controller Processing -->
        <input type="hidden" name="items_json" id="hiddenItemsJson">
        <input type="hidden" name="total_quantity" id="hiddenTotalQuantity">
        <input type="hidden" name="total_amount" id="hiddenTotalAmount">
        <input type="hidden" name="rounded_amount" id="hiddenRoundedAmount">
        <input type="hidden" name="amount_in_words" id="hiddenAmountInWords">

        <!-- 0. Docket Status Control -->
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px; border: 1px solid #E2E8F0;">
          <div class="card-body p-3">
            <label class="form-label small fw-semibold text-muted mb-1">Invoice Docket Status</label>
            <select class="form-select form-select-sm" name="status">
              <option value="Active" {{ $invoiceModel->status === 'Active' ? 'selected' : '' }}>Active / Approved</option>
              <option value="Draft" {{ $invoiceModel->status === 'Draft' ? 'selected' : '' }}>Draft</option>
              <option value="Dispatched" {{ $invoiceModel->status === 'Dispatched' ? 'selected' : '' }}>Dispatched</option>
            </select>
          </div>
        </div>

        <!-- 1. Consigner Selection Card -->
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px; border: 1px solid #E2E8F0;">
          <div class="card-header bg-white py-3 px-3 d-flex align-items-center justify-content-between border-bottom">
            <div class="fw-bold text-dark d-flex align-items-center gap-2">
              <i class="bi bi-building-up text-primary"></i>
              <span>1. Consignee / Buyer</span>
            </div>
            <span class="badge bg-primary-subtle text-primary">{{ $consigners->count() }} Available</span>
          </div>
          <div class="card-body p-3">
            <label class="form-label small fw-semibold text-muted mb-1">Assigned Consigner Master Record</label>
            <select class="form-select form-select-sm mb-2" id="consignerSelect" name="consigner_id" onchange="onConsignerChange(this.value)">
              <option value="">-- No Consignee Assigned --</option>
              @foreach($consigners as $c)
                <option value="{{ $c->id }}" {{ $selectedConsigner && $selectedConsigner->id === $c->id ? 'selected' : '' }}
                        data-name="{{ $c->name }}"
                        data-address="{{ $c->registered_address ?? $c->factory_address }}"
                        data-tax="{{ $c->gstin ?? $c->pan }}"
                        data-phone="{{ $c->contact_phone }}"
                        data-email="{{ $c->contact_email }}"
                        data-iec="{{ $c->iec }}"
                        data-region="{{ $c->region }}"
                        data-adcode="{{ $c->ad_code }}">
                  {{ $c->name }} ({{ $c->iec }})
                </option>
              @endforeach
            </select>
            <div class="d-flex align-items-center justify-content-between small text-muted">
              <span>IEC: <strong id="sidebarIec">{{ $selectedConsigner->iec ?? '-' }}</strong></span>
              <span>Region: <strong id="sidebarRegion">{{ $selectedConsigner->region ?? '-' }}</strong></span>
            </div>
          </div>
        </div>

        <!-- 2. Buyer (If other than Consignee) - Dynamic Adjusters -->
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px; border: 1px solid #E2E8F0;">
          <div class="card-header bg-white py-3 px-3 d-flex align-items-center justify-content-between border-bottom">
            <div class="fw-bold text-dark d-flex align-items-center gap-2">
              <i class="bi bi-person-badge text-primary"></i>
              <span>2. Buyer (If other than Consignee)</span>
            </div>
            <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 small" style="font-size: 0.72rem;" onclick="copyConsigneeToBuyerOther()">
              Copy Consignee
            </button>
          </div>
          <div class="card-body p-3">
            
            <div class="mb-2">
              <label class="form-label small fw-semibold text-muted mb-0.5">Buyer Company Name</label>
              <input type="text" name="buyer_other_name" class="form-control form-control-sm font-monospace fw-bold" id="inputBuyerOtherName" value="{{ $buyerOther['name'] ?? '' }}" placeholder="Leave blank if same as Consignee" oninput="syncVariable('buyerOtherNameDisplay', this.value)">
            </div>

            <div class="mb-2">
              <label class="form-label small fw-semibold text-muted mb-0.5">Address (ADD)</label>
              <textarea name="buyer_other_address" class="form-control form-control-sm" id="inputBuyerOtherAddress" rows="2" placeholder="Full address" oninput="syncVariable('buyerOtherAddressDisplay', this.value ? 'ADD: ' + this.value : '')">{{ $buyerOther['address'] ?? '' }}</textarea>
            </div>

            <div class="row g-2 mb-2">
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Tax Code</label>
                <input type="text" name="buyer_other_tax" class="form-control form-control-sm font-monospace" id="inputBuyerOtherTax" value="{{ $buyerOther['tax_code'] ?? '' }}" placeholder="Tax Code / GSTIN" oninput="syncVariable('buyerOtherTaxDisplay', this.value)">
              </div>
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Telephone (TEL)</label>
                <input type="text" name="buyer_other_phone" class="form-control form-control-sm font-monospace" id="inputBuyerOtherPhone" value="{{ $buyerOther['phone'] ?? '' }}" placeholder="Phone / Mobile" oninput="syncVariable('buyerOtherPhoneDisplay', this.value)">
              </div>
            </div>

            <div class="mb-1">
              <label class="form-label small fw-semibold text-muted mb-0.5">Email Address</label>
              <input type="email" name="buyer_other_email" class="form-control form-control-sm" id="inputBuyerOtherEmail" value="{{ $buyerOther['email'] ?? '' }}" placeholder="company@domain.com" oninput="syncVariable('buyerOtherEmailDisplay', this.value)">
            </div>

          </div>
        </div>

        <!-- 3. Shipping Logistics & Ports (Bill Matching) -->
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px; border: 1px solid #E2E8F0;">
          <div class="card-header bg-white py-3 px-3 border-bottom">
            <div class="fw-bold text-dark d-flex align-items-center gap-2">
              <i class="bi bi-geo-alt-fill text-primary"></i>
              <span>3. Shipping Logistics & Ports</span>
            </div>
          </div>
          <div class="card-body p-3">
            
            <div class="row g-2 mb-2">
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Pre-Carriage by</label>
                <input type="text" name="pre_carriage_by" class="form-control form-control-sm font-monospace" id="inputPreCarriage" value="{{ $invoice['pre_carriage_by'] ?? '' }}" placeholder="e.g. TRUCK" oninput="syncVariable('preCarriageDisplay', this.value)">
              </div>
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Place of Receipt</label>
                <input type="text" name="place_of_receipt" class="form-control form-control-sm font-monospace" id="inputPlaceReceipt" value="{{ $invoice['place_of_receipt'] ?? '' }}" placeholder="e.g. CHENNAI PORT" oninput="syncVariable('receiptPlaceDisplay', this.value)">
              </div>
            </div>

            <div class="row g-2 mb-2">
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Country of Origin</label>
                <input type="text" name="country_of_origin" class="form-control form-control-sm font-monospace" id="inputCountryOrigin" value="{{ $invoice['country_of_origin'] ?? 'INDIA' }}" oninput="syncVariable('countryOriginDisplay', this.value)">
              </div>
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Country Final Dest.</label>
                <input type="text" name="country_of_destination" class="form-control form-control-sm font-monospace" id="inputCountryDest" value="{{ $invoice['country_of_destination'] ?? '' }}" placeholder="e.g. VIETNAM" oninput="syncVariable('countryDestDisplay', this.value)">
              </div>
            </div>

            <div class="row g-2 mb-2">
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Vessel / Flight No.</label>
                <input type="text" name="vessel_flight_no" class="form-control form-control-sm font-monospace" id="inputVesselFlight" value="{{ $invoice['vessel_flight_no'] ?? '' }}" placeholder="e.g. MSC RAVEL V.1469W" oninput="syncVariable('vesselFlightDisplay', this.value)">
              </div>
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Port of Loading</label>
                <input type="text" name="port_of_loading" class="form-control form-control-sm font-monospace" id="inputPortLoading" value="{{ $invoice['port_of_loading'] ?? '' }}" placeholder="e.g. CHENNAI PORT" oninput="syncVariable('portLoadingDisplay', this.value)">
              </div>
            </div>

            <div class="row g-2 mb-2">
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Port of Discharge</label>
                <input type="text" name="port_of_discharge" class="form-control form-control-sm font-monospace" id="inputPortDischarge" value="{{ $invoice['port_of_discharge'] ?? '' }}" placeholder="e.g. CATLAI PORT" oninput="syncVariable('portDischargeDisplay', this.value)">
              </div>
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Final Destination</label>
                <input type="text" name="final_destination" class="form-control form-control-sm font-monospace" id="inputFinalDest" value="{{ $invoice['final_destination'] ?? '' }}" placeholder="e.g. CATLAI PORT" oninput="syncVariable('finalDestinationDisplay', this.value)">
              </div>
            </div>

            <div class="mb-1">
              <label class="form-label small fw-semibold text-muted mb-0.5">Terms of Delivery & Payment</label>
              <input type="text" name="payment_terms" class="form-control form-control-sm fw-bold" id="inputPaymentTerms" value="{{ $invoice['payment_terms'] ?? '' }}" placeholder="e.g. 100% AGAINST SCAN B/L COPY" oninput="syncVariable('paymentTermsDisplay', this.value)">
            </div>

          </div>
        </div>

        <!-- 4. Invoice & Container Identifiers -->
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px; border: 1px solid #E2E8F0;">
          <div class="card-header bg-white py-3 px-3 border-bottom">
            <div class="fw-bold text-dark d-flex align-items-center gap-2">
              <i class="bi bi-hash text-primary"></i>
              <span>4. Invoice & Container Identifiers</span>
            </div>
          </div>
          <div class="card-body p-3">
            
            <div class="row g-2 mb-2">
              <div class="col-7">
                <label class="form-label small fw-semibold text-muted mb-0.5">Invoice Number</label>
                <input type="text" name="invoice_number" required class="form-control form-control-sm font-monospace fw-bold" id="inputInvNo" value="{{ $invoice['invoice_number'] }}" oninput="updateInvoiceNoHeader()">
              </div>
              <div class="col-5">
                <label class="form-label small fw-semibold text-muted mb-0.5">Invoice Date</label>
                <input type="text" name="invoice_date" required class="form-control form-control-sm font-monospace" id="inputInvDate" value="{{ $invoice['invoice_date'] }}" oninput="updateInvoiceNoHeader()">
              </div>
            </div>

            <div class="mb-2">
              <label class="form-label small fw-semibold text-muted mb-0.5">Buyer's Order / P.O. No.</label>
              <input type="text" name="buyer_po" class="form-control form-control-sm font-monospace" id="inputBuyerPo" value="{{ $invoice['buyer_po'] ?? '' }}" placeholder="e.g. PO-84920" oninput="syncVariable('buyerPoDisplay', this.value)">
            </div>

            <div class="row g-2 mb-1">
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Container Number</label>
                <input type="text" name="container_number" class="form-control form-control-sm font-monospace text-uppercase" id="inputContainer" value="{{ $invoice['container_number'] ?? '' }}" placeholder="e.g. DFSU3044515" oninput="onContainerChange(this.value)">
              </div>
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Trailer / Vehicle No</label>
                <input type="text" name="trailer_number" class="form-control form-control-sm font-monospace text-uppercase" id="inputTrailer" value="{{ $invoice['trailer_number'] ?? '' }}" placeholder="e.g. TN02BB9434" oninput="onTrailerChange(this.value)">
              </div>
            </div>

          </div>
        </div>

        <!-- 5. Multiple Line Items Repeater (Goods Table) -->
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px; border: 1px solid #E2E8F0;">
          <div class="card-header bg-white py-3 px-3 d-flex align-items-center justify-content-between border-bottom">
            <div class="fw-bold text-dark d-flex align-items-center gap-2">
              <i class="bi bi-box-seam text-primary"></i>
              <span>5. Line Items (<span id="itemsCountBadge">{{ count($items) }}</span>)</span>
            </div>
            <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1.5 py-1 px-2.5 shadow-sm fw-semibold" onclick="addNewItem()">
              <i class="bi bi-plus-lg"></i>
              <span>Add Item</span>
            </button>
          </div>
          <div class="card-body p-3">
            
            <div id="itemsContainer">
              <!-- Dynamic items rendered by JavaScript -->
            </div>

            <!-- Totals Calculation Summary Box -->
            <div class="p-3 bg-light rounded-3 mt-3 border">
              <div class="d-flex align-items-center justify-content-between mb-1.5">
                <span class="small fw-semibold text-muted">Total Quantity (TOTAL SQ MT.):</span>
                <span class="fw-bold font-monospace text-dark" id="summaryTotalQty">{{ number_format($totalQuantity, 3) }}</span>
              </div>
              <div class="d-flex align-items-center justify-content-between mb-1.5">
                <span class="small fw-semibold text-muted">Total CIF Amount (₹):</span>
                <span class="fw-bold font-monospace fs-6 text-primary" id="summaryTotalAmount">₹{{ number_format($totalAmount, 2) }}</span>
              </div>
              <div class="d-flex align-items-center justify-content-between">
                <span class="small fw-semibold text-muted">Rounded to (₹):</span>
                <span class="fw-black font-monospace text-success fs-6" id="summaryRoundedAmount">₹{{ number_format($roundedAmount, 2) }}</span>
              </div>
            </div>

          </div>
        </div>

        <!-- Submit Button in Left Sidebar -->
        <div class="d-grid gap-2 mb-4">
          <button type="submit" class="btn btn-primary py-2.5 fw-semibold shadow-sm d-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-check2-circle"></i>
            <span>Update Commercial Invoice</span>
          </button>
        </div>

      </form>

    </div>

    <!-- Right Column: The Full Commercial Invoice Table Sheet -->
    <div class="col-xl-8">
      <div class="card border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #E2E8F0; overflow: hidden;">
        
        <div class="card-header bg-white py-3 px-3 d-flex align-items-center justify-content-between border-bottom d-print-none">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-table text-primary"></i>
            <span class="fw-bold text-dark">Commercial Invoice Preview (Docket #{{ $invoiceModel->invoice_number }})</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <a href="{{ route('invoices.show', $invoiceModel) }}" class="btn btn-sm btn-outline-primary py-1 px-2.5">
              <i class="bi bi-eye me-1"></i> View Mode
            </a>
            <button type="button" class="btn btn-sm btn-light border py-1 px-2.5" onclick="window.print()">
              <i class="bi bi-printer me-1"></i> Print / Save PDF
            </button>
          </div>
        </div>

        <div class="card-body p-2 p-md-3 bg-light overflow-auto">
          <!-- Include Reusable Commercial Invoice Table Component Passing Variables -->
          <x-commercial-invoice-table 
            :exporter="$exporter"
            :invoice="$invoice"
            :consigner="$selectedConsigner"
            :buyerOther="$buyerOther"
            :items="$items"
            :totalQuantity="$totalQuantity"
            :totalAmount="$totalAmount"
            :roundedAmount="$roundedAmount"
            :amountInWords="$amountInWords"
            :bank="$bank"
            :certifications="$certifications"
            :settings="$settings"
          />
        </div>

      </div>
    </div>
  </div>

</main>

<style>
@media print {
  /* Hide extraneous elements */
  .app-sidebar, 
  .app-header, 
  .app-footer, 
  .d-print-none, 
  .content-area > nav, 
  .top-navbar, 
  .sidebar-backdrop {
    display: none !important;
  }
  
  body, .content-area, .main-wrapper, .app-container {
    background: #fff !important;
    padding: 0 !important;
    margin: 0 !important;
  }

  .commercial-invoice-sheet {
    box-shadow: none !important;
    border: none !important;
    padding: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
  }

  .table-invoice-main {
    width: 100% !important;
  }

  .invoice-badges-row {
    display: flex !important;
    flex-direction: row !important;
    flex-wrap: nowrap !important;
    justify-content: space-between !important;
    width: 100% !important;
    gap: 3px !important;
  }

  .invoice-badges-row .badge-item {
    flex: 1 1 0 !important;
    min-width: 0 !important;
    padding: 2px 1px !important;
  }
}

.item-control-card {
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  background: #ffffff;
  padding: 12px;
  margin-bottom: 12px;
  transition: all 0.2s ease;
}
.item-control-card:hover {
  border-color: #93C5FD;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}
</style>

<script>
// Initial Items Array loaded directly from DB
let invoiceItems = @json($items ?? []);
if (!invoiceItems || invoiceItems.length === 0) {
  invoiceItems = [
    {
      marks_numbers: "20 FCL ( 1 )",
      container_no: "{{ $invoice['container_number'] ?? '' }}",
      trailer_no: "{{ $invoice['trailer_number'] ?? '' }}",
      description: "",
      hsn_code: "68022390",
      quantity: "",
      rate: "",
      amount: 0
    }
  ];
}

function syncVariable(targetId, value) {
  const el = document.getElementById(targetId);
  if (el) el.textContent = value;
}

function updateInvoiceNoHeader() {
  const invNo = document.getElementById("inputInvNo") ? document.getElementById("inputInvNo").value : "";
  const invDate = document.getElementById("inputInvDate") ? document.getElementById("inputInvDate").value : "";
  const display = document.getElementById("invNoDisplay");
  if (display) {
    if (invNo && invDate) {
      display.textContent = invNo + " • " + invDate;
    } else if (invNo) {
      display.textContent = invNo;
    } else {
      display.textContent = invDate;
    }
  }
}

function onContainerChange(val) {
  invoiceItems.forEach(it => { it.container_no = val; });
  syncVariable("containerNoDisplay", val);
  renderItemsTable();
}

function onTrailerChange(val) {
  invoiceItems.forEach(it => { it.trailer_no = val; });
  renderItemsTable();
}

function copyConsigneeToBuyerOther() {
  const select = document.getElementById("consignerSelect");
  if (!select.value) {
    alert("Please select a Consignee / Buyer from Section 1 first.");
    return;
  }
  const opt = select.options[select.selectedIndex];
  if (!opt) return;

  const name = opt.getAttribute("data-name") || "";
  const address = opt.getAttribute("data-address") || "";
  const tax = opt.getAttribute("data-tax") || "";
  const phone = opt.getAttribute("data-phone") || "";
  const email = opt.getAttribute("data-email") || "";

  document.getElementById("inputBuyerOtherName").value = name;
  document.getElementById("inputBuyerOtherAddress").value = address;
  document.getElementById("inputBuyerOtherTax").value = tax;
  document.getElementById("inputBuyerOtherPhone").value = phone;
  document.getElementById("inputBuyerOtherEmail").value = email;

  syncVariable("buyerOtherNameDisplay", name);
  syncVariable("buyerOtherAddressDisplay", address ? "ADD: " + address : "");
  syncVariable("buyerOtherTaxDisplay", tax);
  syncVariable("buyerOtherPhoneDisplay", phone);
  syncVariable("buyerOtherEmailDisplay", email);
}

function onConsignerChange(consignerId) {
  const select = document.getElementById("consignerSelect");
  if (!consignerId) {
    syncVariable("consigneeNameDisplay", "");
    syncVariable("consigneeAddressDisplay", "");
    syncVariable("consigneeTaxDisplay", "");
    syncVariable("consigneePhoneDisplay", "");
    syncVariable("consigneeEmailDisplay", "");
    const elIec = document.getElementById("sidebarIec");
    const elReg = document.getElementById("sidebarRegion");
    if (elIec) elIec.textContent = "-";
    if (elReg) elReg.textContent = "-";
    return;
  }

  const opt = select.options[select.selectedIndex];
  if (!opt) return;

  const name = opt.getAttribute("data-name") || "";
  const address = opt.getAttribute("data-address") || "";
  const tax = opt.getAttribute("data-tax") || "";
  const phone = opt.getAttribute("data-phone") || "";
  const email = opt.getAttribute("data-email") || "";
  const iec = opt.getAttribute("data-iec") || "-";
  const region = opt.getAttribute("data-region") || "-";

  syncVariable("consigneeNameDisplay", name);
  syncVariable("consigneeAddressDisplay", address ? "ADD: " + address : "");
  syncVariable("consigneeTaxDisplay", tax);
  syncVariable("consigneePhoneDisplay", phone);
  syncVariable("consigneeEmailDisplay", email);

  const elIec = document.getElementById("sidebarIec");
  const elReg = document.getElementById("sidebarRegion");
  if (elIec) elIec.textContent = iec;
  if (elReg) elReg.textContent = region;
}

// Convert numbers like 5702 -> "FIVE THOUSAND SEVEN HUNDRED & TWO RUPEES ONLY"
function convertNumberToWords(num) {
  num = Math.round(Number(num));
  if (isNaN(num) || num <= 0) return "";
  
  const a = ["", "ONE", "TWO", "THREE", "FOUR", "FIVE", "SIX", "SEVEN", "EIGHT", "NINE", "TEN", 
    "ELEVEN", "TWELVE", "THIRTEEN", "FOURTEEN", "FIFTEEN", "SIXTEEN", "SEVENTEEN", "EIGHTEEN", "NINETEEN"];
  const b = ["", "", "TWENTY", "THIRTY", "FORTY", "FIFTY", "SIXTY", "SEVENTY", "EIGHTY", "NINETY"];
  
  function inWords(n) {
    if (n < 20) return a[n];
    if (n < 100) return b[Math.floor(n / 10)] + (n % 10 !== 0 ? " " + a[n % 10] : "");
    if (n < 1000) return a[Math.floor(n / 100)] + " HUNDRED" + (n % 100 !== 0 ? " & " + inWords(n % 100) : "");
    if (n < 100000) return inWords(Math.floor(n / 1000)) + " THOUSAND" + (n % 1000 !== 0 ? " " + inWords(n % 1000) : "");
    if (n < 10000000) return inWords(Math.floor(n / 100000)) + " LAKH" + (n % 100000 !== 0 ? " " + inWords(n % 100000) : "");
    return inWords(Math.floor(n / 10000000)) + " CRORE" + (n % 10000000 !== 0 ? " " + inWords(n % 10000000) : "");
  }

  return inWords(num).trim() + " RUPEES ONLY";
}

function renderItemsForm() {
  const container = document.getElementById("itemsContainer");
  if (!container) return;

  const countBadge = document.getElementById("itemsCountBadge");
  if (countBadge) countBadge.textContent = invoiceItems.length;

  container.innerHTML = invoiceItems.map((item, idx) => `
    <div class="item-control-card" data-idx="${idx}">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="badge bg-secondary-subtle text-secondary fw-semibold">Item #${idx + 1}</span>
        ${invoiceItems.length > 1 ? `
          <button type="button" class="btn btn-xs btn-outline-danger py-0 px-2 small" onclick="removeItem(${idx})">
            <i class="bi bi-trash me-1"></i>Remove
          </button>
        ` : ''}
      </div>

      <div class="row g-2 mb-2">
        <div class="col-7">
          <label class="form-label small fw-semibold text-muted mb-0.5">Marks & Pkgs</label>
          <input type="text" class="form-control form-control-sm font-monospace" placeholder="e.g. 20 FCL ( 1 )" value="${escapeHtml(item.marks_numbers || '')}" oninput="updateItemField(${idx}, 'marks_numbers', this.value)">
        </div>
        <div class="col-5">
          <label class="form-label small fw-semibold text-muted mb-0.5">HSN Code</label>
          <input type="text" class="form-control form-control-sm font-monospace" value="${escapeHtml(item.hsn_code || '68022390')}" oninput="updateItemField(${idx}, 'hsn_code', this.value)">
        </div>
      </div>

      <div class="mb-2">
        <label class="form-label small fw-semibold text-muted mb-0.5">Description of Goods</label>
        <textarea class="form-control form-control-sm" rows="2" placeholder="e.g. NATURAL POLISHED GRANITE SLABS..." oninput="updateItemField(${idx}, 'description', this.value)">${escapeHtml(item.description || '')}</textarea>
      </div>

      <div class="row g-2">
        <div class="col-4">
          <label class="form-label small fw-semibold text-muted mb-0.5">Quantity (SQM)</label>
          <input type="number" step="0.001" class="form-control form-control-sm font-monospace fw-bold" placeholder="0.000" value="${item.quantity !== '' && item.quantity !== undefined ? item.quantity : ''}" oninput="updateItemField(${idx}, 'quantity', this.value)">
        </div>
        <div class="col-4">
          <label class="form-label small fw-semibold text-muted mb-0.5">Rate (₹ / SQM)</label>
          <input type="number" step="0.001" class="form-control form-control-sm font-monospace fw-bold" placeholder="0.000" value="${item.rate !== '' && item.rate !== undefined ? item.rate : ''}" oninput="updateItemField(${idx}, 'rate', this.value)">
        </div>
        <div class="col-4">
          <label class="form-label small fw-semibold text-muted mb-0.5">Amount (₹)</label>
          <input type="text" readonly class="form-control form-control-sm font-monospace bg-light fw-bold text-primary" value="${item.amount > 0 ? '₹' + Number(item.amount).toFixed(2) : '₹0.00'}">
        </div>
      </div>
    </div>
  `).join("");
}

function escapeHtml(str) {
  return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function renderItemsTable() {
  const tbody = document.getElementById("invoiceItemsTbody");
  if (!tbody) return;

  let totalQty = 0;
  let totalAmt = 0;

  tbody.innerHTML = invoiceItems.map((item, idx) => {
    const qty = parseFloat(item.quantity) || 0;
    const rate = parseFloat(item.rate) || 0;
    const amount = parseFloat(item.amount) || (qty * rate);
    
    totalQty += qty;
    totalAmt += amount;

    const formattedDesc = escapeHtml(item.description || '').replace(/\n/g, '<br>');

    // If item is completely blank, render clean empty row
    if (!item.description && !item.marks_numbers && qty === 0 && rate === 0) {
      return '';
    }

    return `
      <tr class="align-top" style="border-bottom: 1px solid #ddd;">
        <td class="p-1 text-center font-monospace" style="border-right: 1.5px solid #000; font-size: 10px;">
          <div class="fw-bold">${escapeHtml(item.marks_numbers || '')}</div>
          <div style="font-size: 9px; color: #444;">${escapeHtml(item.container_no || '')}</div>
          <div style="font-size: 9px; color: #666;">${escapeHtml(item.trailer_no || '')}</div>
        </td>
        <td class="p-1 font-monospace" style="border-right: 1.5px solid #000; font-size: 10px;">
          <div class="fw-bold">${formattedDesc}</div>
          <div class="mt-1" style="font-size: 9px; color: #444;">HSN: ${escapeHtml(item.hsn_code || '68022390')}</div>
        </td>
        <td class="p-1 text-end font-monospace fw-bold" style="border-right: 1.5px solid #000; font-size: 10px;">
          ${qty > 0 ? qty.toFixed(3) : ''}
        </td>
        <td class="p-1 text-end font-monospace" style="border-right: 1.5px solid #000; font-size: 10px;">
          ${rate > 0 ? '₹' + rate.toFixed(3) : ''}
        </td>
        <td class="p-1 text-end font-monospace fw-bold" style="font-size: 10px;">
          ${amount > 0 ? '₹' + amount.toFixed(2) : ''}
        </td>
      </tr>
    `;
  }).join("");

  const rounded = Math.round(totalAmt);
  const words = totalAmt > 0 ? convertNumberToWords(rounded) : '';

  // Update Table Display Elements
  syncVariable("totalQuantityDisplay", totalQty > 0 ? totalQty.toFixed(3) : "0.000");
  syncVariable("totalAmountDisplay", totalAmt > 0 ? "₹ " + totalAmt.toFixed(2) : "₹ 0.00");
  syncVariable("roundedAmountDisplay", rounded > 0 ? "₹ " + rounded.toFixed(2) : "₹ 0.00");
  syncVariable("amountInWordsDisplay", words);

  // Update Summary Card
  const sQty = document.getElementById("summaryTotalQty");
  const sAmt = document.getElementById("summaryTotalAmount");
  const sRnd = document.getElementById("summaryRoundedAmount");
  if (sQty) sQty.textContent = totalQty > 0 ? totalQty.toFixed(3) : "0.000";
  if (sAmt) sAmt.textContent = "₹" + totalAmt.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  if (sRnd) sRnd.textContent = "₹" + rounded.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function updateItemField(idx, field, value) {
  if (field === 'quantity' || field === 'rate') {
    invoiceItems[idx][field] = value;
    const q = parseFloat(invoiceItems[idx].quantity) || 0;
    const r = parseFloat(invoiceItems[idx].rate) || 0;
    invoiceItems[idx].amount = Number((q * r).toFixed(2));
    renderItemsForm();
  } else {
    invoiceItems[idx][field] = value;
  }
  renderItemsTable();
}

function addNewItem() {
  const currentContainer = document.getElementById("inputContainer") ? document.getElementById("inputContainer").value : "";
  const currentTrailer = document.getElementById("inputTrailer") ? document.getElementById("inputTrailer").value : "";
  
  invoiceItems.push({
    marks_numbers: "20 FCL ( " + (invoiceItems.length + 1) + " )",
    container_no: currentContainer,
    trailer_no: currentTrailer,
    description: "",
    hsn_code: "68022390",
    quantity: "",
    rate: "",
    amount: 0
  });

  renderItemsForm();
  renderItemsTable();
}

function removeItem(idx) {
  if (invoiceItems.length <= 1) {
    alert("At least one line item is required in the commercial invoice.");
    return;
  }
  invoiceItems.splice(idx, 1);
  renderItemsForm();
  renderItemsTable();
}

function prepareInvoiceSubmission() {
  let totalQty = 0;
  let totalAmt = 0;

  invoiceItems.forEach(item => {
    const qty = parseFloat(item.quantity) || 0;
    const rate = parseFloat(item.rate) || 0;
    const amt = parseFloat(item.amount) || (qty * rate);
    totalQty += qty;
    totalAmt += amt;
  });

  const rounded = Math.round(totalAmt);
  const words = totalAmt > 0 ? convertNumberToWords(rounded) : '';

  document.getElementById("hiddenItemsJson").value = JSON.stringify(invoiceItems);
  document.getElementById("hiddenTotalQuantity").value = totalQty;
  document.getElementById("hiddenTotalAmount").value = totalAmt;
  document.getElementById("hiddenRoundedAmount").value = rounded;
  document.getElementById("hiddenAmountInWords").value = words;

  return true;
}

// Initial Render
document.addEventListener("DOMContentLoaded", function() {
  renderItemsForm();
  renderItemsTable();
});
</script>
@endsection
