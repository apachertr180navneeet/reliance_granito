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

      <a href="{{ route('invoices.packing-list', $invoiceModel) }}" class="btn btn-outline-info d-inline-flex align-items-center gap-2 px-3 py-2">
        <i class="bi bi-box2"></i>
        <span>Packing List</span>
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
        <input type="hidden" name="notify_parties_json" id="hiddenNotifyPartiesJson">
        <input type="hidden" name="total_quantity" id="hiddenTotalQuantity" value="{{ $totalQuantity }}">
        <input type="hidden" name="total_amount" id="hiddenTotalAmount" value="{{ $totalAmount }}">
        <input type="hidden" name="rounded_amount" id="hiddenRoundedAmount" value="{{ $roundedAmount }}">
        <input type="hidden" name="amount_in_words" id="hiddenAmountInWords" value="{{ $amountInWords }}">
        <input type="hidden" name="taxable_value_inr" id="hiddenTaxableValueInr" value="{{ $invoice['taxable_value_inr'] ?? 0 }}">
        <input type="hidden" name="igst_amount_inr" id="hiddenIgstAmountInr" value="{{ $invoice['igst_amount_inr'] ?? 0 }}">

        <!-- 0. Format, Delivery Terms & Currency Scheme -->
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px; border: 1px solid #E2E8F0;">
          <div class="card-header bg-white py-3 px-3 d-flex align-items-center justify-content-between border-bottom">
            <div class="fw-bold text-dark d-flex align-items-center gap-2">
              <i class="bi bi-file-earmark-check text-primary"></i>
              <span>Invoice Format &amp; Scheme</span>
            </div>
            <span class="badge bg-primary text-white" id="formatStatusBadge">{{ strtoupper($invoice['invoice_format'] ?? 'LUT') }} FORMAT</span>
          </div>
          <div class="card-body p-3">
            
            <!-- Format Radio -->
            <label class="form-label small fw-semibold text-muted mb-1">Invoice Format</label>
            <div class="btn-group w-100 mb-2" role="group">
              <input type="radio" class="btn-check" name="invoice_format" id="formatLut" value="LUT" {{ ($invoice['invoice_format'] ?? 'LUT') === 'LUT' ? 'checked' : '' }} onchange="onFormatChange('LUT')">
              <label class="btn btn-outline-primary btn-sm py-1.5 fw-semibold" for="formatLut">LUT (Under Bond)</label>

              <input type="radio" class="btn-check" name="invoice_format" id="formatIgst" value="IGST" {{ ($invoice['invoice_format'] ?? '') === 'IGST' ? 'checked' : '' }} onchange="onFormatChange('IGST')">
              <label class="btn btn-outline-primary btn-sm py-1.5 fw-semibold" for="formatIgst">IGST (With Tax)</label>
            </div>

            <!-- Delivery Terms & Currency -->
            <div class="row g-2 mb-2">
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Delivery Term</label>
                <select class="form-select form-select-sm fw-bold" name="delivery_term" id="inputDeliveryTerm" onchange="onDeliveryTermChange(this.value)">
                  <option value="CIF" {{ ($invoice['delivery_term'] ?? 'CIF') === 'CIF' ? 'selected' : '' }}>CIF</option>
                  <option value="CNF" {{ in_array($invoice['delivery_term'] ?? '', ['CNF', 'CNF/CFR', 'CFR']) ? 'selected' : '' }}>CNF / CFR</option>
                  <option value="FOB" {{ ($invoice['delivery_term'] ?? '') === 'FOB' ? 'selected' : '' }}>FOB</option>
                </select>
              </div>

              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Currency</label>
                <select class="form-select form-select-sm fw-bold" name="currency" id="inputCurrency" onchange="onCurrencyChange(this.value)">
                  <option value="USD" {{ ($invoice['currency'] ?? 'USD') === 'USD' ? 'selected' : '' }}>USD ($)</option>
                  <option value="INR" {{ ($invoice['currency'] ?? '') === 'INR' ? 'selected' : '' }}>INR (₹)</option>
                </select>
              </div>
            </div>

            <!-- IGST & Exchange Rate Box (Visible when IGST or Currency USD) -->
            <div id="igstFxSettingsBox" class="p-2.5 bg-light rounded border mb-1" style="{{ ($invoice['invoice_format'] ?? '') === 'IGST' ? '' : 'display: none;' }}">
              <div class="small fw-bold text-dark mb-1.5 d-flex align-items-center justify-content-between">
                <span>IGST &amp; Exchange Rate Calculation</span>
                <span class="badge bg-warning text-dark">Tax Applicable</span>
              </div>
              <div class="row g-2">
                <div class="col-6" id="fxRateGroup">
                  <label class="form-label small fw-semibold text-muted mb-0.5" title="Direction: 1 USD = INR">Exchange Rate (1 USD = ₹)</label>
                  <input type="number" step="0.0001" name="exchange_rate" id="inputExchangeRate" class="form-control form-control-sm font-monospace fw-bold" value="{{ $invoice['exchange_rate'] ?? 94.20 }}" oninput="recalculateTotals()">
                </div>
                <div class="col-6">
                  <label class="form-label small fw-semibold text-muted mb-0.5">IGST Rate (%)</label>
                  <input type="number" step="0.01" name="igst_rate" id="inputIgstRate" class="form-control form-control-sm font-monospace fw-bold" value="{{ $invoice['igst_rate'] ?? 18.00 }}" oninput="recalculateTotals()">
                </div>
              </div>
              <div class="d-flex justify-content-between small mt-2 pt-1 border-top">
                <span class="text-muted">Taxable Value (₹):</span>
                <span class="fw-bold font-monospace" id="fxSummaryTaxableVal">₹ {{ number_format($invoice['taxable_value_inr'] ?? 0, 2) }}</span>
              </div>
              <div class="d-flex justify-content-between small">
                <span class="text-muted">IGST Payable (₹):</span>
                <span class="fw-bold font-monospace text-primary" id="fxSummaryIgstVal">₹ {{ number_format($invoice['igst_amount_inr'] ?? 0, 2) }}</span>
              </div>
            </div>

          </div>
        </div>

        <!-- 1. Consignee / Buyer Selection Card -->
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px; border: 1px solid #E2E8F0;">
          <div class="card-header bg-white py-3 px-3 d-flex align-items-center justify-content-between border-bottom">
            <div class="fw-bold text-dark d-flex align-items-center gap-2">
              <i class="bi bi-building-up text-primary"></i>
              <span>1. Consignee / Buyer</span>
            </div>
            <div class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" id="toTheOrderToggle" {{ ($invoice['consignee_mode'] ?? '') === 'to_the_order' ? 'checked' : '' }} onchange="toggleToTheOrder(this.checked)">
              <label class="form-check-label small fw-semibold text-dark" for="toTheOrderToggle">TO THE ORDER</label>
            </div>
          </div>
          <div class="card-body p-3">
            <input type="hidden" name="consignee_mode" id="inputConsigneeMode" value="{{ $invoice['consignee_mode'] ?? 'consigner' }}">

            <div id="consignerMasterGroup" style="{{ ($invoice['consignee_mode'] ?? '') === 'to_the_order' ? 'display: none;' : '' }}">
              <label class="form-label small fw-semibold text-muted mb-1">Select Consigner / Buyer Master</label>
              <select class="form-select form-select-sm mb-2" id="consignerSelect" name="consigner_id" onchange="onConsignerChange(this.value)">
                <option value="" {{ empty($selectedConsigner) ? 'selected' : '' }}>-- Select Consignee / Buyer --</option>
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

              <div class="mb-2">
                <label class="form-label small fw-semibold text-muted mb-0.5">Consignee Name</label>
                <input type="text" name="consignee_name" id="inputConsigneeName" class="form-control form-control-sm font-monospace fw-bold" value="{{ $invoice['consignee_name'] ?? '' }}" oninput="syncVariable('consigneeNameDisplay', this.value)">
              </div>

              <div class="mb-2">
                <label class="form-label small fw-semibold text-muted mb-0.5">Consignee Address</label>
                <textarea name="consignee_address" id="inputConsigneeAddress" class="form-control form-control-sm" rows="2" oninput="syncVariable('consigneeAddressDisplay', this.value ? 'ADD: ' + this.value : '')">{{ $invoice['consignee_address'] ?? '' }}</textarea>
              </div>

              <div class="row g-2 mb-2">
                <div class="col-6">
                  <label class="form-label small fw-semibold text-muted mb-0.5">Tax Code / GSTIN</label>
                  <input type="text" name="consignee_tax" id="inputConsigneeTax" class="form-control form-control-sm font-monospace" value="{{ $invoice['consignee_tax'] ?? '' }}" oninput="syncVariable('consigneeTaxDisplay', this.value)">
                </div>
                <div class="col-6">
                  <label class="form-label small fw-semibold text-muted mb-0.5">Phone</label>
                  <input type="text" name="consignee_phone" id="inputConsigneePhone" class="form-control form-control-sm font-monospace" value="{{ $invoice['consignee_phone'] ?? '' }}" oninput="syncVariable('consigneePhoneDisplay', this.value)">
                </div>
              </div>

              <div class="mb-1">
                <label class="form-label small fw-semibold text-muted mb-0.5">Email</label>
                <input type="email" name="consignee_email" id="inputConsigneeEmail" class="form-control form-control-sm" value="{{ $invoice['consignee_email'] ?? '' }}" oninput="syncVariable('consigneeEmailDisplay', this.value)">
              </div>
            </div>

            <div id="toTheOrderAlert" class="alert alert-info py-2 px-3 small mb-0" style="{{ ($invoice['consignee_mode'] ?? '') === 'to_the_order' ? '' : 'display: none;' }}">
              <i class="bi bi-info-circle-fill me-1"></i> Consignee is set to <strong>TO THE ORDER</strong>. Overseas buyer details can be adjusted separately in Section 2.
            </div>

            <div class="d-flex align-items-center justify-content-between small text-muted mt-2 pt-2 border-top">
              <span>IEC: <strong id="sidebarIec">{{ $selectedConsigner->iec ?? '-' }}</strong></span>
              <span>Region: 
                <select name="region" id="inputRegion" class="form-select form-select-sm d-inline-block w-auto py-0 px-2 font-monospace" onchange="syncVariable('sidebarRegion', this.value)">
                  <option value="Gujarat" {{ ($invoice['region'] ?? 'Gujarat') === 'Gujarat' ? 'selected' : '' }}>Gujarat</option>
                  <option value="Jodhpur" {{ ($invoice['region'] ?? '') === 'Jodhpur' ? 'selected' : '' }}>Jodhpur</option>
                </select>
              </span>
            </div>
          </div>
        </div>

        <!-- 2. Buyer (If other than Consignee) - Dynamic Adjusters & Edit Address -->
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px; border: 1px solid #E2E8F0;">
          <div class="card-header bg-white py-3 px-3 d-flex align-items-center justify-content-between border-bottom">
            <div class="fw-bold text-dark d-flex align-items-center gap-2">
              <i class="bi bi-person-badge text-primary"></i>
              <span>2. Buyer (If other than Consignee)</span>
            </div>
            <div class="d-flex gap-1.5">
              <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 small" style="font-size: 0.72rem;" onclick="toggleEditAddress()">
                <i class="bi bi-pencil me-1"></i>Edit Address
              </button>
              <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 small" style="font-size: 0.72rem;" onclick="copyConsigneeToBuyerOther()">
                Copy Consignee
              </button>
            </div>
          </div>
          <div class="card-body p-3">
            
            <div class="mb-2">
              <label class="form-label small fw-semibold text-muted mb-0.5">Buyer Company Name</label>
              <input type="text" name="buyer_name" class="form-control form-control-sm font-monospace fw-bold" id="inputBuyerOtherName" value="{{ $invoice['buyer_name'] ?? ($buyerOther['name'] ?? '') }}" placeholder="Leave blank if same as Consignee" oninput="syncVariable('buyerOtherNameDisplay', this.value)">
            </div>

            <div class="mb-2">
              <div class="d-flex align-items-center justify-content-between">
                <label class="form-label small fw-semibold text-muted mb-0.5">Address (ADD)</label>
                <span class="badge bg-light text-muted border" id="addressEditStatusBadge" style="font-size: 0.68rem;">Invoice Snapshot</span>
              </div>
              <textarea name="buyer_address" class="form-control form-control-sm" id="inputBuyerOtherAddress" rows="2" placeholder="Full address" oninput="syncVariable('buyerOtherAddressDisplay', this.value ? 'ADD: ' + this.value : '')">{{ $invoice['buyer_address'] ?? ($buyerOther['address'] ?? '') }}</textarea>
            </div>

            <div class="row g-2 mb-2">
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Tax Code</label>
                <input type="text" name="buyer_tax" class="form-control form-control-sm font-monospace" id="inputBuyerOtherTax" value="{{ $invoice['buyer_tax'] ?? ($buyerOther['tax_code'] ?? '') }}" placeholder="Tax Code / GSTIN" oninput="syncVariable('buyerOtherTaxDisplay', this.value)">
              </div>
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Telephone (TEL)</label>
                <input type="text" name="buyer_phone" class="form-control form-control-sm font-monospace" id="inputBuyerOtherPhone" value="{{ $invoice['buyer_phone'] ?? ($buyerOther['phone'] ?? '') }}" placeholder="Phone / Mobile" oninput="syncVariable('buyerOtherPhoneDisplay', this.value)">
              </div>
            </div>

            <div class="mb-1">
              <label class="form-label small fw-semibold text-muted mb-0.5">Email Address</label>
              <input type="email" name="buyer_email" class="form-control form-control-sm" id="inputBuyerOtherEmail" value="{{ $invoice['buyer_email'] ?? ($buyerOther['email'] ?? '') }}" placeholder="company@domain.com" oninput="syncVariable('buyerOtherEmailDisplay', this.value)">
            </div>

          </div>
        </div>

        <!-- 3. Notify Parties (Independent Selection 1-4) -->
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px; border: 1px solid #E2E8F0;">
          <div class="card-header bg-white py-3 px-3 d-flex align-items-center justify-content-between border-bottom">
            <div class="fw-bold text-dark d-flex align-items-center gap-2">
              <i class="bi bi-bell-fill text-primary"></i>
              <span>3. Notify Parties (1 to 4)</span>
            </div>
            <span class="badge bg-secondary-subtle text-secondary">Optional</span>
          </div>
          <div class="card-body p-3">
            <ul class="nav nav-pills nav-fill mb-2 notify-party-tabs" id="notifyTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active py-1.5 px-2 small fw-semibold" id="np1-tab" data-bs-toggle="pill" data-bs-target="#np1" type="button" role="tab">Party 1</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link py-1.5 px-2 small fw-semibold" id="np2-tab" data-bs-toggle="pill" data-bs-target="#np2" type="button" role="tab">Party 2</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link py-1.5 px-2 small fw-semibold" id="np3-tab" data-bs-toggle="pill" data-bs-target="#np3" type="button" role="tab">Party 3</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link py-1.5 px-2 small fw-semibold" id="np4-tab" data-bs-toggle="pill" data-bs-target="#np4" type="button" role="tab">Party 4</button>
              </li>
            </ul>

            <div class="tab-content" id="notifyTabsContent">
              @for($i = 0; $i < 4; $i++)
              @php $npVal = $notifyParties[$i] ?? ['name' => '', 'address' => '', 'tax' => '', 'phone' => '', 'email' => '']; @endphp
              <div class="tab-pane fade {{ $i === 0 ? 'show active' : '' }}" id="np{{ $i + 1 }}" role="tabpanel">
                <div class="mb-2">
                  <label class="form-label small fw-semibold text-muted mb-0.5">Notify Party {{ $i + 1 }} Name</label>
                  <input type="text" class="form-control form-control-sm font-monospace" placeholder="Company Name" id="np_name_{{ $i }}" value="{{ $npVal['name'] ?? '' }}" oninput="updateNotifyParty({{ $i }}, 'name', this.value)">
                </div>
                <div class="mb-2">
                  <label class="form-label small fw-semibold text-muted mb-0.5">Address</label>
                  <textarea class="form-control form-control-sm" rows="2" placeholder="Full Address" id="np_address_{{ $i }}" oninput="updateNotifyParty({{ $i }}, 'address', this.value)">{{ $npVal['address'] ?? '' }}</textarea>
                </div>
                <div class="row g-2 mb-1">
                  <div class="col-6">
                    <label class="form-label small fw-semibold text-muted mb-0.5">Tax Code</label>
                    <input type="text" class="form-control form-control-sm font-monospace" placeholder="Tax / Reg" id="np_tax_{{ $i }}" value="{{ $npVal['tax'] ?? '' }}" oninput="updateNotifyParty({{ $i }}, 'tax', this.value)">
                  </div>
                  <div class="col-6">
                    <label class="form-label small fw-semibold text-muted mb-0.5">Phone</label>
                    <input type="text" class="form-control form-control-sm font-monospace" placeholder="Phone" id="np_phone_{{ $i }}" value="{{ $npVal['phone'] ?? '' }}" oninput="updateNotifyParty({{ $i }}, 'phone', this.value)">
                  </div>
                </div>
              </div>
              @endfor
            </div>

          </div>
        </div>

        <!-- 4. Shipping Logistics & Ports (Bill Matching) -->
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px; border: 1px solid #E2E8F0;">
          <div class="card-header bg-white py-3 px-3 border-bottom">
            <div class="fw-bold text-dark d-flex align-items-center gap-2">
              <i class="bi bi-geo-alt-fill text-primary"></i>
              <span>4. Shipping Logistics &amp; Ports</span>
            </div>
          </div>
          <div class="card-body p-3">
            
            <div class="row g-2 mb-2">
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Pre-Carriage by</label>
                <input type="text" name="pre_carriage_by" class="form-control form-control-sm font-monospace" id="inputPreCarriage" value="{{ $invoice['pre_carriage_by'] ?? '' }}" oninput="syncVariable('preCarriageDisplay', this.value)">
              </div>
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Place of Receipt</label>
                <input type="text" name="place_of_receipt" class="form-control form-control-sm font-monospace" id="inputPlaceReceipt" value="{{ $invoice['place_of_receipt'] ?? '' }}" oninput="syncVariable('receiptPlaceDisplay', this.value)">
              </div>
            </div>

            <div class="row g-2 mb-2">
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Country of Origin</label>
                <input type="text" name="country_of_origin" class="form-control form-control-sm font-monospace" id="inputCountryOrigin" value="{{ $invoice['country_of_origin'] ?? '' }}" oninput="syncVariable('countryOriginDisplay', this.value)">
              </div>
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Country Final Dest.</label>
                <input type="text" name="country_of_destination" class="form-control form-control-sm font-monospace" id="inputCountryDest" value="{{ $invoice['country_of_destination'] ?? '' }}" oninput="syncVariable('countryDestDisplay', this.value)">
              </div>
            </div>

            <div class="row g-2 mb-2">
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Vessel / Flight No.</label>
                <input type="text" name="vessel_flight_no" class="form-control form-control-sm font-monospace" id="inputVesselFlight" value="{{ $invoice['vessel_flight_no'] ?? '' }}" oninput="syncVariable('vesselFlightDisplay', this.value)">
              </div>
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Port of Loading</label>
                <input type="text" name="port_of_loading" class="form-control form-control-sm font-monospace" id="inputPortLoading" value="{{ $invoice['port_of_loading'] ?? '' }}" oninput="syncVariable('portLoadingDisplay', this.value)">
              </div>
            </div>

            <div class="row g-2 mb-2">
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Port of Discharge</label>
                <input type="text" name="port_of_discharge" class="form-control form-control-sm font-monospace" id="inputPortDischarge" value="{{ $invoice['port_of_discharge'] ?? '' }}" oninput="syncVariable('portDischargeDisplay', this.value)">
              </div>
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Final Destination</label>
                <input type="text" name="final_destination" class="form-control form-control-sm font-monospace" id="inputFinalDest" value="{{ $invoice['final_destination'] ?? '' }}" oninput="syncVariable('finalDestinationDisplay', this.value)">
              </div>
            </div>

            <div class="mb-1">
              <label class="form-label small fw-semibold text-muted mb-0.5">Terms of Delivery &amp; Payment</label>
              @php
                $editIsIgst = (strtoupper($invoice['invoice_format'] ?? '') === 'IGST');
                $defaultEditTerms = $editIsIgst ? '80% AT PORT & 20% AFTER CHECKING' : '100% AGAINST SCAN B/L COPY';
                $valEditPaymentTerms = (!empty($invoice['payment_terms']) && ($invoice['payment_terms'] !== '100% AGAINST SCAN B/L COPY' || !$editIsIgst)) ? $invoice['payment_terms'] : $defaultEditTerms;
              @endphp
              <input type="text" name="payment_terms" class="form-control form-control-sm fw-bold" id="inputPaymentTerms" value="{{ $valEditPaymentTerms }}" oninput="syncVariable('paymentTermsDisplay', this.value)">
            </div>

          </div>
        </div>

        <!-- 5. Invoice & Container Identifiers & Weights -->
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px; border: 1px solid #E2E8F0;">
          <div class="card-header bg-white py-3 px-3 border-bottom">
            <div class="fw-bold text-dark d-flex align-items-center gap-2">
              <i class="bi bi-truck text-primary"></i>
              <span>5. Identifiers, Containers &amp; Weights</span>
            </div>
          </div>
          <div class="card-body p-3">
            
            <div class="row g-2 mb-2">
              <div class="col-7">
                <label class="form-label small fw-semibold text-muted mb-0.5">Invoice Number</label>
                <input type="text" name="invoice_number" required class="form-control form-control-sm font-monospace fw-bold" id="inputInvNo" value="{{ $invoice['invoice_number'] ?? '' }}" oninput="updateInvoiceNoHeader()">
              </div>
              <div class="col-5">
                <label class="form-label small fw-semibold text-muted mb-0.5">Invoice Date</label>
                <input type="text" name="invoice_date" required class="form-control form-control-sm font-monospace" id="inputInvDate" value="{{ $invoice['invoice_date'] ?? '' }}" oninput="updateInvoiceNoHeader()">
              </div>
            </div>

            <div class="mb-2">
              <label class="form-label small fw-semibold text-muted mb-0.5">Buyer's Order / P.O. No.</label>
              <input type="text" name="buyer_po" class="form-control form-control-sm font-monospace" id="inputBuyerPo" value="{{ $invoice['buyer_po'] ?? '-' }}" oninput="syncVariable('buyerPoDisplay', this.value)">
            </div>

            <input type="hidden" name="container_number" id="inputContainer" value="{{ $invoice['container_number'] ?? '' }}">
            <input type="hidden" name="trailer_number" id="inputTrailer" value="{{ $invoice['trailer_number'] ?? '' }}">

            <div class="row g-2 mb-2">
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Container Type</label>
                <select name="container_type" id="inputContainerType" class="form-select form-select-sm font-monospace fw-semibold" onchange="syncVariable('containerTypeDisplay', this.value)">
                  <option value="" {{ empty($invoice['container_type']) ? 'selected' : '' }}></option>
                  <option value="20 FCL" {{ ($invoice['container_type'] ?? '') === '20 FCL' ? 'selected' : '' }}>20 FCL</option>
                  <option value="40 FCL" {{ ($invoice['container_type'] ?? '') === '40 FCL' ? 'selected' : '' }}>40 FCL</option>
                  <option value="20' Heavy Duty" {{ ($invoice['container_type'] ?? '') === "20' Heavy Duty" ? 'selected' : '' }}>20' Heavy Duty</option>
                  <option value="40' High Cube" {{ ($invoice['container_type'] ?? '') === "40' High Cube" ? 'selected' : '' }}>40' High Cube</option>
                </select>
              </div>
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">No. of Containers</label>
                <input type="number" min="1" name="no_of_containers" id="inputNoOfContainers" class="form-control form-control-sm font-monospace fw-bold" value="{{ $invoice['no_of_containers'] ?? '' }}" oninput="onNoOfContainersChange(this.value)">
              </div>
            </div>

            <!-- Weights (G.W. & N.W.) -->
            <div class="row g-2 mb-2">
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Gross Weight (G.WT KG)</label>
                <input type="number" step="0.01" name="gross_weight" id="inputGrossWeight" class="form-control form-control-sm font-monospace fw-bold" value="{{ $invoice['gross_weight'] ?? '' }}" oninput="syncVariable('grossWeightDisplay', this.value ? this.value + ' KGS' : '')">
              </div>
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Net Weight (N.WT KG)</label>
                <input type="number" step="0.01" name="net_weight" id="inputNetWeight" class="form-control form-control-sm font-monospace fw-bold" value="{{ $invoice['net_weight'] ?? '' }}" oninput="syncVariable('netWeightDisplay', this.value ? this.value + ' KGS' : '')">
              </div>
            </div>

            <!-- Statutory / ISO Override -->
            <div class="row g-2 mb-1">
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5" title="ISO Certification Details">ISO Certification</label>
                <input type="text" name="iso_certification" id="inputIsoCert" class="form-control form-control-sm font-monospace" value="{{ $invoice['iso_certification'] ?? '' }}" oninput="syncVariable('isoCertDisplay', this.value)">
              </div>
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">LUT / Export Rule</label>
                <input type="text" name="lut_no" id="inputLutRule" class="form-control form-control-sm font-monospace" value="{{ $invoice['lut_no'] ?? '' }}" oninput="syncVariable('lutRuleDisplay', this.value)">
              </div>
            </div>

          </div>
        </div>

        <!-- 6. Multiple Line Items Repeater (Goods Table) -->
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 12px; border: 1px solid #E2E8F0;">
          <div class="card-header bg-white py-3 px-3 d-flex align-items-center justify-content-between border-bottom">
            <div class="fw-bold text-dark d-flex align-items-center gap-2">
              <i class="bi bi-box-seam text-primary"></i>
              <span>6. Line Items (<span id="itemsCountBadge">{{ count($items) }}</span>)</span>
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
                <span class="small fw-semibold text-muted">Total Amount (<span class="currency-code">{{ $invoice['currency'] ?? 'USD' }}</span>):</span>
                <span class="fw-bold font-monospace fs-6 text-primary" id="summaryTotalAmount">{{ ($invoice['currency'] ?? 'USD') === 'USD' ? '$' : '₹' }}{{ number_format($totalAmount, 2) }}</span>
              </div>
              <div class="d-flex align-items-center justify-content-between">
                <span class="small fw-semibold text-muted">Rounded to (<span class="currency-code">{{ $invoice['currency'] ?? 'USD' }}</span>):</span>
                <span class="fw-black font-monospace text-success fs-6" id="summaryRoundedAmount">{{ ($invoice['currency'] ?? 'USD') === 'USD' ? '$' : '₹' }}{{ number_format($roundedAmount, 2) }}</span>
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
      <div class="invoice-preview-sticky">
        <div class="card border-0 shadow-sm invoice-preview-card" style="border-radius: 12px; border: 1px solid #E2E8F0; overflow: hidden;">
          
          <div class="card-header bg-white py-3 px-3 d-flex align-items-center justify-content-between border-bottom flex-shrink-0 d-print-none">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-table text-primary"></i>
              <span class="fw-bold text-dark">Commercial Invoice (Live Synchronized Preview)</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <a href="{{ route('invoices.packing-list', $invoiceModel) }}" class="btn btn-sm btn-outline-info py-1 px-2.5">
                <i class="bi bi-box2 me-1"></i> Packing List
              </a>
              <button type="button" class="btn btn-sm btn-light border py-1 px-2.5" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print / Save PDF
              </button>
            </div>
          </div>

          <div class="card-body p-2 p-md-3 bg-light overflow-auto invoice-preview-body">
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
  </div>

</main>

<style>
/* Notify Party & Segmented Tabs */
.notify-party-tabs {
  background-color: #F8FAFC !important;
  border: 1px solid #E2E8F0 !important;
  border-radius: 8px !important;
  padding: 4px !important;
  display: flex !important;
  gap: 4px !important;
}

.notify-party-tabs .nav-item {
  flex: 1 1 0 !important;
  margin: 0 !important;
}

.notify-party-tabs .nav-link {
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  width: 100% !important;
  padding: 6px 10px !important;
  border-radius: 6px !important;
  border: 1px solid transparent !important;
  border-left: none !important;
  color: #475569 !important;
  background: transparent !important;
  background-image: none !important;
  font-size: 0.82rem !important;
  font-weight: 600 !important;
  line-height: 1.2 !important;
  text-align: center !important;
  gap: 0 !important;
  box-shadow: none !important;
  transition: all 0.15s ease-in-out !important;
}

.notify-party-tabs .nav-link:hover {
  color: #0F172A !important;
  background-color: #E2E8F0 !important;
  border-left: none !important;
}

.notify-party-tabs .nav-link.active {
  background: #2563EB !important;
  background-color: #2563EB !important;
  background-image: none !important;
  color: #FFFFFF !important;
  font-weight: 600 !important;
  border-left: none !important;
  box-shadow: 0 1px 3px rgba(37, 99, 235, 0.3) !important;
}

/* Live Synchronized Preview Sticky & Scroll Behavior */
@media (min-width: 1200px) {
  .invoice-preview-sticky {
    position: sticky;
    top: 1.25rem;
    z-index: 10;
  }
  .invoice-preview-card {
    max-height: calc(100vh - 2.5rem);
    display: flex;
    flex-direction: column;
  }
  .invoice-preview-body {
    overflow-y: auto !important;
    flex-grow: 1;
  }
}

@media print {
  .invoice-preview-sticky {
    position: static !important;
  }
  .invoice-preview-card {
    max-height: none !important;
    overflow: visible !important;
    border: none !important;
    box-shadow: none !important;
  }
  .invoice-preview-body {
    max-height: none !important;
    overflow: visible !important;
    padding: 0 !important;
    background: transparent !important;
  }
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

  .invoice-badges-table {
    width: 100% !important;
    table-layout: fixed !important;
  }

  .invoice-badges-table .badge-item {
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
// Loaded Items from Database
let invoiceItems = {!! json_encode(!empty($items) ? $items : [
  [
    'marks_numbers' => '',
    'container_no' => $invoice['container_number'] ?? '',
    'trailer_no' => $invoice['trailer_number'] ?? '',
    'description' => '',
    'hsn_code' => '',
    'quantity' => '',
    'rate' => '',
    'amount' => 0
  ]
]) !!};

// Loaded Notify Parties from Database
let notifyParties = {!! json_encode(!empty($notifyParties) ? $notifyParties : [
  ['name' => '', 'address' => '', 'tax' => '', 'phone' => '', 'email' => ''],
  ['name' => '', 'address' => '', 'tax' => '', 'phone' => '', 'email' => ''],
  ['name' => '', 'address' => '', 'tax' => '', 'phone' => '', 'email' => ''],
  ['name' => '', 'address' => '', 'tax' => '', 'phone' => '', 'email' => ''],
]) !!};

while (notifyParties.length < 4) {
  notifyParties.push({ name: '', address: '', tax: '', phone: '', email: '' });
}

let currentFormat = "{{ $invoice['invoice_format'] ?? 'LUT' }}";
let currentDeliveryTerm = "{{ $invoice['delivery_term'] ?? 'CIF' }}";
let currentCurrency = "{{ $invoice['currency'] ?? 'USD' }}";

function syncVariable(targetId, value) {
  const el = document.getElementById(targetId);
  if (el) el.textContent = value;
}

function onFormatChange(fmt) {
  currentFormat = fmt;
  const banner = document.getElementById("invoiceFormatBanner");
  const lutRow = document.getElementById("termsOfExportRow");
  const igstRow = document.getElementById("igstBreakdownRow");
  const igstSettingsBox = document.getElementById("igstFxSettingsBox");
  const badge = document.getElementById("formatStatusBadge");

  if (fmt === "IGST") {
    if (banner) banner.textContent = "SUPPLY MEANT FOR EXPORT WITH PAYMENT OF IGST";
    if (lutRow) lutRow.style.display = "none";
    if (igstRow) igstRow.style.display = "";
    if (igstSettingsBox) igstSettingsBox.style.display = "";
    if (badge) {
      badge.textContent = "IGST FORMAT";
      badge.className = "badge bg-warning text-dark";
    }
    const paymentTermsInput = document.getElementById("inputPaymentTerms");
    if (paymentTermsInput && (!paymentTermsInput.value || paymentTermsInput.value === "100% AGAINST SCAN B/L COPY" || paymentTermsInput.value === "CIF - 100% AGAINST SCAN B/L COPY")) {
      paymentTermsInput.value = "80% AT PORT & 20% AFTER CHECKING";
      syncVariable("paymentTermsDisplay", "80% AT PORT & 20% AFTER CHECKING");
    }
  } else {
    if (banner) banner.textContent = "SUPPLY MEANT FOR EXPORT UNDER BOND OR LETTER OF UNDERTAKING WITHOUT PAYMENT OF IGST";
    if (lutRow) lutRow.style.display = "";
    if (igstRow) igstRow.style.display = "none";
    if (igstSettingsBox) igstSettingsBox.style.display = "none";
    if (badge) {
      badge.textContent = "LUT FORMAT";
      badge.className = "badge bg-primary text-white";
    }
    const paymentTermsInput = document.getElementById("inputPaymentTerms");
    if (paymentTermsInput && (!paymentTermsInput.value || paymentTermsInput.value === "80% AT PORT & 20% AFTER CHECKING")) {
      paymentTermsInput.value = "100% AGAINST SCAN B/L COPY";
      syncVariable("paymentTermsDisplay", "100% AGAINST SCAN B/L COPY");
    }
  }
  recalculateTotals();
}

function onDeliveryTermChange(term) {
  currentDeliveryTerm = term;
  syncVariable("rateTermSpan", term);
  const pTerms = document.getElementById("inputPaymentTerms");
  if (pTerms) {
    syncVariable("paymentTermsDisplay", pTerms.value || (term + " - 100% AGAINST SCAN B/L COPY"));
  }
}

function onCurrencyChange(curr) {
  currentCurrency = curr;
  const symbol = curr === "USD" ? "$" : "₹";
  
  document.querySelectorAll(".currency-symbol").forEach(el => el.textContent = symbol);
  document.querySelectorAll(".currency-code").forEach(el => el.textContent = curr);
  
  const fxGroup = document.getElementById("fxRateGroup");
  const fxContainer = document.getElementById("igstFxContainer");
  if (curr === "USD") {
    if (fxGroup) fxGroup.style.display = "";
    if (fxContainer) fxContainer.style.display = "";
  } else {
    if (fxGroup) fxGroup.style.display = "none";
    if (fxContainer) fxContainer.style.display = "none";
  }

  recalculateTotals();
}

function toggleToTheOrder(checked) {
  const modeInput = document.getElementById("inputConsigneeMode");
  const masterGroup = document.getElementById("consignerMasterGroup");
  const alertBox = document.getElementById("toTheOrderAlert");
  const nameDisp = document.getElementById("consigneeNameDisplay");
  const addrDisp = document.getElementById("consigneeAddressDisplay");
  const metaDisp = document.getElementById("consigneeMetaDisplay");

  if (checked) {
    modeInput.value = "to_the_order";
    masterGroup.style.display = "none";
    alertBox.style.display = "block";
    if (nameDisp) nameDisp.textContent = "TO THE ORDER";
    if (addrDisp) addrDisp.textContent = "";
    if (metaDisp) metaDisp.style.display = "none";
  } else {
    modeInput.value = "consigner";
    masterGroup.style.display = "block";
    alertBox.style.display = "none";
    if (metaDisp) metaDisp.style.display = "";
    const select = document.getElementById("consignerSelect");
    onConsignerChange(select ? select.value : "");
  }
}

function toggleEditAddress() {
  const addr = document.getElementById("inputBuyerOtherAddress");
  if (addr) {
    addr.focus();
    addr.style.borderColor = "#0284c7";
  }
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

function onNoOfContainersChange(val) {
  const n = parseInt(val) || "";
  syncVariable("noOfContainersDisplay", n);
}

function autoSyncContainersCount() {
  const containerNumbers = invoiceItems
    .map(it => (it.container_no || '').trim())
    .filter(Boolean);

  if (containerNumbers.length > 0) {
    const unique = new Set(containerNumbers);
    const count = unique.size;
    const input = document.getElementById("inputNoOfContainers");
    if (input && !input.value) {
      input.value = count;
      onNoOfContainersChange(count);
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
  const region = opt.getAttribute("data-region") || "Gujarat";

  document.getElementById("inputConsigneeName").value = name;
  document.getElementById("inputConsigneeAddress").value = address;
  document.getElementById("inputConsigneeTax").value = tax;
  document.getElementById("inputConsigneePhone").value = phone;
  document.getElementById("inputConsigneeEmail").value = email;

  syncVariable("consigneeNameDisplay", name);
  syncVariable("consigneeAddressDisplay", address ? "ADD: " + address : "");
  syncVariable("consigneeTaxDisplay", tax);
  syncVariable("consigneePhoneDisplay", phone);
  syncVariable("consigneeEmailDisplay", email);

  const elIec = document.getElementById("sidebarIec");
  if (elIec) elIec.textContent = iec;

  const regInput = document.getElementById("inputRegion");
  if (regInput) regInput.value = region;
}

function updateNotifyParty(idx, field, value) {
  if (!notifyParties[idx]) {
    notifyParties[idx] = { name: "", address: "", tax: "", phone: "", email: "" };
  }
  notifyParties[idx][field] = value;
  renderNotifyPartiesDisplay();
}

function renderNotifyPartiesDisplay() {
  const row = document.getElementById("notifyPartiesDisplayRow");
  const list = document.getElementById("notifyPartiesList");
  if (!row || !list) return;

  const active = notifyParties.filter(p => p.name || p.address);
  if (active.length === 0) {
    row.style.display = "none";
    return;
  }

  row.style.display = "";
  list.innerHTML = notifyParties.map((p, idx) => {
    if (!p.name && !p.address) return "";
    return `
      <div class="col-md-6 col-12 mb-0.5">
        <strong>Notify Party ${idx + 1}:</strong> <span class="fw-bold">${escapeHtml(p.name)}</span>
        ${p.address ? ' &bull; <span>ADD: ' + escapeHtml(p.address) + '</span>' : ''}
        ${p.tax ? ' &bull; <span>TAX: ' + escapeHtml(p.tax) + '</span>' : ''}
        ${p.phone ? ' &bull; <span>TEL: ' + escapeHtml(p.phone) + '</span>' : ''}
      </div>
    `;
  }).join("");
}

// Convert numbers like 16425.27 -> words in INR or USD
function convertNumberToWords(amount, curr) {
  if (curr === 'USD') {
    const dollars = Math.floor(amount);
    const cents = Math.round((amount - dollars) * 100);
    const dolStr = dollars > 0 ? helperWesternWords(dollars) + " US DOLLARS" : "";
    const centStr = cents > 0 ? helperWesternWords(cents) + " CENTS" : "";
    if (dolStr && centStr) return dolStr + " AND " + centStr + " ONLY";
    if (dolStr) return dolStr + " ONLY";
    return centStr + " ONLY";
  } else {
    const num = Math.round(Number(amount));
    if (isNaN(num) || num <= 0) return "";
    return helperInrWords(num) + " RUPEES ONLY";
  }
}

function helperInrWords(n) {
  const a = ["", "ONE", "TWO", "THREE", "FOUR", "FIVE", "SIX", "SEVEN", "EIGHT", "NINE", "TEN", 
    "ELEVEN", "TWELVE", "THIRTEEN", "FOURTEEN", "FIFTEEN", "SIXTEEN", "SEVENTEEN", "EIGHTEEN", "NINETEEN"];
  const b = ["", "", "TWENTY", "THIRTY", "FORTY", "FIFTY", "SIXTY", "SEVENTY", "EIGHTY", "NINETY"];
  
  if (n < 20) return a[n];
  if (n < 100) return b[Math.floor(n / 10)] + (n % 10 !== 0 ? " " + a[n % 10] : "");
  if (n < 1000) return a[Math.floor(n / 100)] + " HUNDRED" + (n % 100 !== 0 ? " & " + helperInrWords(n % 100) : "");
  if (n < 100000) return helperInrWords(Math.floor(n / 1000)) + " THOUSAND" + (n % 1000 !== 0 ? " " + helperInrWords(n % 1000) : "");
  if (n < 10000000) return helperInrWords(Math.floor(n / 100000)) + " LAKH" + (n % 100000 !== 0 ? " " + helperInrWords(n % 100000) : "");
  return helperInrWords(Math.floor(n / 10000000)) + " CRORE" + (n % 10000000 !== 0 ? " " + helperInrWords(n % 10000000) : "");
}

function helperWesternWords(n) {
  const a = ["", "ONE", "TWO", "THREE", "FOUR", "FIVE", "SIX", "SEVEN", "EIGHT", "NINE", "TEN", 
    "ELEVEN", "TWELVE", "THIRTEEN", "FOURTEEN", "FIFTEEN", "SIXTEEN", "SEVENTEEN", "EIGHTEEN", "NINETEEN"];
  const b = ["", "", "TWENTY", "THIRTY", "FORTY", "FIFTY", "SIXTY", "SEVENTY", "EIGHTY", "NINETY"];
  
  if (n < 20) return a[n];
  if (n < 100) return b[Math.floor(n / 10)] + (n % 10 !== 0 ? " " + a[n % 10] : "");
  if (n < 1000) return a[Math.floor(n / 100)] + " HUNDRED" + (n % 100 !== 0 ? " & " + helperWesternWords(n % 100) : "");
  if (n < 1000000) return helperWesternWords(Math.floor(n / 1000)) + " THOUSAND" + (n % 1000 !== 0 ? " " + helperWesternWords(n % 1000) : "");
  return helperWesternWords(Math.floor(n / 1000000)) + " MILLION" + (n % 1000000 !== 0 ? " " + helperWesternWords(n % 1000000) : "");
}

function escapeHtml(str) {
  return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function renderItemsForm() {
  const container = document.getElementById("itemsContainer");
  if (!container) return;

  const countBadge = document.getElementById("itemsCountBadge");
  if (countBadge) countBadge.textContent = invoiceItems.length;

  container.innerHTML = invoiceItems.map((item, idx) => `
    <div class="item-control-card" id="item-card-${idx}" data-idx="${idx}">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="badge bg-primary-subtle text-primary fw-bold">Item #${idx + 1}</span>
        ${invoiceItems.length > 1 ? `
          <button type="button" class="btn btn-xs btn-outline-danger py-0 px-2 small" onclick="removeItem(${idx})">
            <i class="bi bi-trash me-1"></i>Remove
          </button>
        ` : ''}
      </div>

      <!-- Container Number & Trailer / Vehicle No (Per Item) -->
      <div class="row g-2 mb-2">
        <div class="col-6">
          <label class="form-label small fw-semibold text-muted mb-0.5">Container Number</label>
          <input type="text" class="form-control form-control-sm font-monospace text-uppercase" placeholder="e.g. DFSU3044515" value="${escapeHtml(item.container_no || '')}" oninput="updateItemField(${idx}, 'container_no', this.value, false)">
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold text-muted mb-0.5">Trailer / Vehicle No</label>
          <input type="text" class="form-control form-control-sm font-monospace text-uppercase" placeholder="e.g. TN02BB9434" value="${escapeHtml(item.trailer_no || '')}" oninput="updateItemField(${idx}, 'trailer_no', this.value, false)">
        </div>
      </div>

      <div class="row g-2 mb-2">
        <div class="col-7">
          <label class="form-label small fw-semibold text-muted mb-0.5">Marks &amp; Pkgs</label>
          <input type="text" class="form-control form-control-sm font-monospace" placeholder="e.g. 20 FCL ( 1 )" value="${escapeHtml(item.marks_numbers || '')}" oninput="updateItemField(${idx}, 'marks_numbers', this.value, false)">
        </div>
        <div class="col-5">
          <label class="form-label small fw-semibold text-muted mb-0.5">HSN Code</label>
          <input type="text" class="form-control form-control-sm font-monospace" placeholder="e.g. 68022390" value="${escapeHtml(item.hsn_code || '')}" oninput="updateItemField(${idx}, 'hsn_code', this.value, false)">
        </div>
      </div>

      <div class="mb-2">
        <label class="form-label small fw-semibold text-muted mb-0.5">Description of Goods</label>
        <textarea class="form-control form-control-sm" rows="2" placeholder="e.g. NATURAL POLISHED GRANITE SLABS..." oninput="updateItemField(${idx}, 'description', this.value, false)">${escapeHtml(item.description || '')}</textarea>
      </div>

      <div class="row g-2">
        <div class="col-4">
          <label class="form-label small fw-semibold text-muted mb-0.5">Quantity (SQM)</label>
          <input type="number" step="0.001" class="form-control form-control-sm font-monospace fw-bold" placeholder="0.000" id="item-qty-${idx}" value="${item.quantity !== '' && item.quantity !== undefined ? item.quantity : ''}" oninput="updateItemField(${idx}, 'quantity', this.value, true)">
        </div>
        <div class="col-4">
          <label class="form-label small fw-semibold text-muted mb-0.5">Rate per SQM</label>
          <input type="number" step="0.001" class="form-control form-control-sm font-monospace fw-bold" placeholder="0.000" id="item-rate-${idx}" value="${item.rate !== '' && item.rate !== undefined ? item.rate : ''}" oninput="updateItemField(${idx}, 'rate', this.value, true)">
        </div>
        <div class="col-4">
          <label class="form-label small fw-semibold text-muted mb-0.5">Amount</label>
          <input type="text" readonly id="item-amt-${idx}" class="form-control form-control-sm font-monospace bg-light fw-bold text-primary" value="${item.amount > 0 ? (currentCurrency === 'USD' ? '$' : '₹') + Number(item.amount).toFixed(2) : (currentCurrency === 'USD' ? '$0.00' : '₹0.00')}">
        </div>
      </div>
    </div>
  `).join("");
}

function updateItemField(idx, field, value, isNumeric) {
  if (isNumeric) {
    invoiceItems[idx][field] = value;
    const q = parseFloat(invoiceItems[idx].quantity) || 0;
    const r = parseFloat(invoiceItems[idx].rate) || 0;
    const amt = Number((q * r).toFixed(2));
    invoiceItems[idx].amount = amt;

    const amtField = document.getElementById(`item-amt-${idx}`);
    if (amtField) {
      amtField.value = amt > 0 ? (currentCurrency === 'USD' ? '$' : '₹') + amt.toFixed(2) : (currentCurrency === 'USD' ? '$0.00' : '₹0.00');
    }
  } else {
    invoiceItems[idx][field] = value;
    if (idx === 0) {
      if (field === 'container_no' && document.getElementById('inputContainer')) {
        document.getElementById('inputContainer').value = value;
      }
      if (field === 'trailer_no' && document.getElementById('inputTrailer')) {
        document.getElementById('inputTrailer').value = value;
      }
    }
    if (field === 'container_no') {
      autoSyncContainersCount();
    }
  }
  recalculateTotals();
}

function addNewItem() {
  const nextIdx = invoiceItems.length + 1;
  const prevItem = invoiceItems[invoiceItems.length - 1] || {};
  
  invoiceItems.push({
    marks_numbers: "",
    container_no: "",
    trailer_no: "",
    description: "",
    hsn_code: prevItem.hsn_code || "",
    quantity: "",
    rate: prevItem.rate || "",
    amount: 0
  });

  renderItemsForm();
  autoSyncContainersCount();
  recalculateTotals();
}

function removeItem(idx) {
  if (invoiceItems.length <= 1) {
    alert("At least one line item is required in the commercial invoice.");
    return;
  }
  invoiceItems.splice(idx, 1);
  renderItemsForm();
  autoSyncContainersCount();
  recalculateTotals();
}

function recalculateTotals() {
  renderItemsTable();
}

function renderItemsTable() {
  const tbody = document.getElementById("invoiceItemsTbody");
  if (!tbody) return;

  let totalQty = 0;
  let totalAmt = 0;
  const sym = currentCurrency === 'USD' ? '$' : '₹';

  tbody.innerHTML = invoiceItems.map((item, idx) => {
    const qty = parseFloat(item.quantity) || 0;
    const rate = parseFloat(item.rate) || 0;
    const amount = parseFloat(item.amount) || (qty * rate);
    
    totalQty += qty;
    totalAmt += amount;

    const formattedDesc = escapeHtml(item.description || '').replace(/\n/g, '<br>');

    if (!item.description && !item.marks_numbers && qty === 0 && rate === 0) {
      return '';
    }

    return `
      <tr class="align-top invoice-item-row" style="border-bottom: 1px solid #000; font-size: 10.5px;">
        <td class="p-1.5 text-center align-top item-marks" style="border-right: 1px solid #000;">
          <div class="fw-bold">${escapeHtml(item.marks_numbers || '')}</div>
        </td>
        <td class="p-1.5 text-center align-top font-monospace item-container" style="border-right: 1px solid #000;">
          <div class="fw-bold">${escapeHtml(item.container_no || '')}</div>
          ${item.trailer_no ? `<div class="small text-muted">${escapeHtml(item.trailer_no)}</div>` : ''}
        </td>
        <td class="p-1.5 align-top item-desc" style="border-right: 1px solid #000;">
          <div class="fw-bold text-uppercase">${formattedDesc}</div>
        </td>
        <td class="p-1.5 text-center align-top font-monospace item-hsn" style="border-right: 1px solid #000;">
          ${escapeHtml(item.hsn_code || '')}
        </td>
        <td class="p-1.5 text-end align-top fw-bold font-monospace item-qty" style="border-right: 1px solid #000;">
          ${qty > 0 ? qty.toFixed(3) : ''}
        </td>
        <td class="p-1.5 text-end align-top font-monospace item-rate" style="border-right: 1px solid #000;">
          ${rate > 0 ? sym + ' ' + rate.toFixed(3) : ''}
        </td>
        <td class="p-1.5 text-end align-top fw-bold font-monospace item-amount">
          ${amount > 0 ? sym + ' ' + amount.toFixed(2) : ''}
        </td>
      </tr>
    `;
  }).join("");

  totalAmt = Number(totalAmt.toFixed(2));
  const rounded = Math.round(totalAmt);
  const words = totalAmt > 0 ? convertNumberToWords(currentCurrency === 'INR' ? rounded : totalAmt, currentCurrency) : '';

  // Update Table Display Elements
  syncVariable("totalQuantityDisplay", totalQty > 0 ? totalQty.toFixed(3) : "0.000");
  syncVariable("totalAmountDisplay", sym + " " + totalAmt.toFixed(2));
  syncVariable("roundedAmountDisplay", sym + " " + rounded.toFixed(2));
  syncVariable("amountInWordsDisplay", words);

  // Update Summary Card
  const sQty = document.getElementById("summaryTotalQty");
  const sAmt = document.getElementById("summaryTotalAmount");
  const sRnd = document.getElementById("summaryRoundedAmount");
  if (sQty) sQty.textContent = totalQty > 0 ? totalQty.toFixed(3) : "0.000";
  if (sAmt) sAmt.textContent = sym + totalAmt.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  if (sRnd) sRnd.textContent = sym + rounded.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

  // Calculate IGST if applicable
  const exRateInput = document.getElementById("inputExchangeRate");
  const igstRateInput = document.getElementById("inputIgstRate");
  const exRate = exRateInput ? (parseFloat(exRateInput.value) || 94.20) : 94.20;
  const igstRate = igstRateInput ? (parseFloat(igstRateInput.value) || 18.00) : 18.00;

  let taxableValInr = currentCurrency === 'USD' ? Number((totalAmt * exRate).toFixed(2)) : totalAmt;
  let igstAmtInr = currentFormat === 'IGST' ? Number((taxableValInr * (igstRate / 100)).toFixed(2)) : 0;

  syncVariable("displayExchangeRate", exRate.toFixed(2));
  syncVariable("displayTaxableValue", taxableValInr.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
  syncVariable("displayIgstRate", igstRate.toFixed(0));
  syncVariable("displayIgstAmount", "₹ " + igstAmtInr.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

  syncVariable("fxSummaryTaxableVal", "₹ " + taxableValInr.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
  syncVariable("fxSummaryIgstVal", "₹ " + igstAmtInr.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

  document.getElementById("hiddenTaxableValueInr").value = taxableValInr;
  document.getElementById("hiddenIgstAmountInr").value = igstAmtInr;
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
  const words = totalAmt > 0 ? convertNumberToWords(currentCurrency === 'INR' ? rounded : totalAmt, currentCurrency) : '';

  if (invoiceItems.length > 0) {
    const cEl = document.getElementById("inputContainer");
    const tEl = document.getElementById("inputTrailer");
    if (cEl) cEl.value = invoiceItems[0].container_no || "";
    if (tEl) tEl.value = invoiceItems[0].trailer_no || "";
  }

  document.getElementById("hiddenItemsJson").value = JSON.stringify(invoiceItems);
  document.getElementById("hiddenNotifyPartiesJson").value = JSON.stringify(notifyParties);
  document.getElementById("hiddenTotalQuantity").value = totalQty;
  document.getElementById("hiddenTotalAmount").value = totalAmt;
  document.getElementById("hiddenRoundedAmount").value = rounded;
  document.getElementById("hiddenAmountInWords").value = words;

  return true;
}

// Initial Render
document.addEventListener("DOMContentLoaded", function() {
  renderItemsForm();
  autoSyncContainersCount();
  renderNotifyPartiesDisplay();
  recalculateTotals();
  onFormatChange("{{ $invoice['invoice_format'] ?? 'LUT' }}");
  onDeliveryTermChange("{{ $invoice['delivery_term'] ?? 'CIF' }}");
  onCurrencyChange("{{ $invoice['currency'] ?? 'USD' }}");
});
</script>
@endsection
