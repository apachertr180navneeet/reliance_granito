@extends('layouts.app')

@section('content')
<main class="content-area p-3 p-lg-4" id="invoiceCreateApp">

  <!-- Breadcrumb & Page Header -->
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 d-print-none">
    <div>
      <nav aria-label="breadcrumb" class="mb-1">
        <ol class="breadcrumb mb-0 small">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bi bi-house-door me-1"></i>Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Commercial Invoices</a></li>
          <li class="breadcrumb-item active" aria-current="page">Create Invoice</li>
        </ol>
      </nav>
      <div class="d-flex align-items-center gap-2">
        <h2 class="fw-bold text-dark mb-0" style="font-size: 1.5rem; letter-spacing: -0.02em;">Create Commercial Export Invoice</h2>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1" style="font-size: 0.78rem;">
          <i class="bi bi-file-earmark-plus me-1"></i> New Docket
        </span>
      </div>
      <p class="text-muted small mb-0 mt-1">
        Fill export docket details. All changes synchronize to the commercial invoice preview in real-time.
      </p>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap">
      <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1.5 px-3 py-2">
        <i class="bi bi-arrow-left"></i>
        <span>All Invoices</span>
      </a>

      <a href="{{ route('invoices.settings') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1.5 px-3 py-2">
        <i class="bi bi-sliders"></i>
        <span>Settings</span>
      </a>

      <button type="button" class="btn btn-outline-primary d-inline-flex align-items-center gap-1.5 px-3 py-2" onclick="window.print()">
        <i class="bi bi-printer"></i>
        <span>Print Preview</span>
      </button>

      <button type="submit" form="invoiceForm" class="btn btn-primary d-inline-flex align-items-center gap-1.5 px-3.5 py-2 shadow-sm fw-semibold">
        <i class="bi bi-floppy2-fill"></i>
        <span>Save &amp; Create Invoice</span>
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
    <div class="col-lg-5 col-xl-5 d-print-none">
      
      <!-- Quick Section Jump Navigation Bar -->
      <div class="section-nav-bar d-flex align-items-center gap-1 p-1.5 mb-3 bg-white rounded-3 border shadow-sm sticky-top" style="top: 10px; z-index: 15;">
        <a href="#sec-scheme" class="btn btn-sm btn-light border-0 py-1 px-2.5 small fw-semibold text-muted text-nowrap sec-nav-link active">1. Details &amp; Scheme</a>
        <a href="#sec-parties" class="btn btn-sm btn-light border-0 py-1 px-2.5 small fw-semibold text-muted text-nowrap sec-nav-link">2. Parties</a>
        <a href="#sec-shipping" class="btn btn-sm btn-light border-0 py-1 px-2.5 small fw-semibold text-muted text-nowrap sec-nav-link">3. Shipping</a>
        <a href="#sec-containers" class="btn btn-sm btn-light border-0 py-1 px-2.5 small fw-semibold text-muted text-nowrap sec-nav-link">4. Containers</a>
        <a href="#sec-items" class="btn btn-sm btn-light border-0 py-1 px-2.5 small fw-semibold text-muted text-nowrap sec-nav-link">5. Line Items</a>
      </div>

      <form id="invoiceForm" action="{{ route('invoices.store') }}" method="POST" onsubmit="return prepareInvoiceSubmission()">
        @csrf
        <!-- Hidden Serialized Inputs for Controller Processing -->
        <input type="hidden" name="items_json" id="hiddenItemsJson">
        <input type="hidden" name="notify_parties_json" id="hiddenNotifyPartiesJson">
        <input type="hidden" name="total_quantity" id="hiddenTotalQuantity">
        <input type="hidden" name="total_amount" id="hiddenTotalAmount">
        <input type="hidden" name="rounded_amount" id="hiddenRoundedAmount">
        <input type="hidden" name="amount_in_words" id="hiddenAmountInWords">
        <input type="hidden" name="taxable_value_inr" id="hiddenTaxableValueInr">
        <input type="hidden" name="igst_amount_inr" id="hiddenIgstAmountInr">

        <!-- 1. Invoice Identifiers, Format, Delivery Terms & Currency Scheme -->
        <div class="card border-0 shadow-sm mb-3 form-section-card" id="sec-scheme" style="border-radius: 12px; border: 1px solid #E2E8F0;">
          <div class="card-header bg-white py-2.5 px-3 d-flex align-items-center justify-content-between border-bottom">
            <div class="fw-bold text-dark d-flex align-items-center gap-2">
              <span class="badge bg-primary text-white rounded-pill px-2 py-1" style="font-size: 0.72rem;">1</span>
              <i class="bi bi-file-earmark-check text-primary"></i>
              <span>Invoice Details &amp; Export Scheme</span>
            </div>
            <span class="badge bg-primary text-white" id="formatStatusBadge">LUT FORMAT</span>
          </div>
          <div class="card-body p-3">
            
            <!-- Invoice Number, Date, PO -->
            <div class="row g-2 mb-2.5">
              <div class="col-7">
                <label class="form-label small fw-semibold text-muted mb-0.5">Invoice Number <span class="text-danger">*</span></label>
                <input type="text" name="invoice_number" required class="form-control form-control-sm font-monospace fw-bold" id="inputInvNo" value="{{ $invoice['invoice_number'] ?? '' }}" placeholder="e.g. 681 / 26-27" oninput="updateInvoiceNoHeader()">
              </div>
              <div class="col-5">
                <label class="form-label small fw-semibold text-muted mb-0.5">Invoice Date <span class="text-danger">*</span></label>
                <input type="text" name="invoice_date" required class="form-control form-control-sm font-monospace" id="inputInvDate" value="{{ $invoice['invoice_date'] ?? date('d.m.Y') }}" oninput="updateInvoiceNoHeader()">
              </div>
            </div>

            <div class="mb-2.5">
              <label class="form-label small fw-semibold text-muted mb-0.5">Buyer's Order / P.O. No.</label>
              <input type="text" name="buyer_po" class="form-control form-control-sm font-monospace" id="inputBuyerPo" value="{{ $invoice['buyer_po'] ?? '-' }}" oninput="syncVariable('buyerPoDisplay', this.value)">
            </div>

            <hr class="my-2.5 text-muted opacity-25">

            <!-- Format Radio -->
            <label class="form-label small fw-semibold text-muted mb-1">Invoice Format</label>
            <div class="btn-group w-100 mb-2.5" role="group">
              <input type="radio" class="btn-check" name="invoice_format" id="formatLut" value="LUT" {{ ($invoice['invoice_format'] ?? 'LUT') === 'LUT' ? 'checked' : '' }} onchange="onFormatChange('LUT')">
              <label class="btn btn-outline-primary btn-sm py-1.5 fw-semibold" for="formatLut">
                <i class="bi bi-shield-check me-1"></i> LUT (Under Bond)
              </label>

              <input type="radio" class="btn-check" name="invoice_format" id="formatIgst" value="IGST" {{ ($invoice['invoice_format'] ?? '') === 'IGST' ? 'checked' : '' }} onchange="onFormatChange('IGST')">
              <label class="btn btn-outline-primary btn-sm py-1.5 fw-semibold" for="formatIgst">
                <i class="bi bi-receipt-cutoff me-1"></i> IGST (With Tax)
              </label>
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
                <span><i class="bi bi-calculator me-1 text-warning"></i>IGST &amp; Exchange Rate Calculation</span>
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
                <span class="fw-bold font-monospace" id="fxSummaryTaxableVal">₹ 0.00</span>
              </div>
              <div class="d-flex justify-content-between small">
                <span class="text-muted">IGST Payable (₹):</span>
                <span class="fw-bold font-monospace text-primary" id="fxSummaryIgstVal">₹ 0.00</span>
              </div>
            </div>

          </div>
        </div>

        <!-- 2. Parties: Consignee & Buyer Selection Card -->
        <div class="card border-0 shadow-sm mb-3 form-section-card" id="sec-parties" style="border-radius: 12px; border: 1px solid #E2E8F0;">
          <div class="card-header bg-white py-2.5 px-3 d-flex align-items-center justify-content-between border-bottom">
            <div class="fw-bold text-dark d-flex align-items-center gap-2">
              <span class="badge bg-primary text-white rounded-pill px-2 py-1" style="font-size: 0.72rem;">2</span>
              <i class="bi bi-people-fill text-primary"></i>
              <span>Consignee, Buyer &amp; Notify Parties</span>
            </div>
            <div class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" id="toTheOrderToggle" onchange="toggleToTheOrder(this.checked)">
              <label class="form-check-label small fw-semibold text-dark" for="toTheOrderToggle">TO THE ORDER</label>
            </div>
          </div>
          <div class="card-body p-3">
            <input type="hidden" name="consignee_mode" id="inputConsigneeMode" value="{{ $invoice['consignee_mode'] ?? 'consigner' }}">

            <div id="consignerMasterGroup">
              <div class="mb-2">
                <label class="form-label small fw-semibold text-muted mb-1 d-flex align-items-center justify-content-between">
                  <span>Select Consignee / Buyer Master</span>
                  <span class="badge bg-light text-primary border" style="font-size: 0.68rem;">Auto-fills details</span>
                </label>
                <select class="form-select form-select-sm mb-2" id="consignerSelect" name="consigner_id" onchange="onConsignerChange(this.value)">
                  <option value="" {{ empty($selectedConsigner) ? 'selected' : '' }}>-- Select Consignee / Buyer Master --</option>
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
              </div>

              <div class="mb-2">
                <label class="form-label small fw-semibold text-muted mb-0.5">Consignee Name</label>
                <input type="text" name="consignee_name" id="inputConsigneeName" class="form-control form-control-sm font-monospace fw-bold" value="{{ $invoice['consignee_name'] ?? '' }}" oninput="syncVariable('consigneeNameDisplay', this.value)" placeholder="Consignee Business Name">
              </div>

              <div class="mb-2">
                <label class="form-label small fw-semibold text-muted mb-0.5">Consignee Address</label>
                <textarea name="consignee_address" id="inputConsigneeAddress" class="form-control form-control-sm" rows="2" placeholder="Full address" oninput="syncVariable('consigneeAddressDisplay', this.value ? 'ADD: ' + this.value : '')">{{ $invoice['consignee_address'] ?? '' }}</textarea>
              </div>

              <div class="row g-2 mb-2">
                <div class="col-6">
                  <label class="form-label small fw-semibold text-muted mb-0.5">Tax Code / GSTIN</label>
                  <input type="text" name="consignee_tax" id="inputConsigneeTax" class="form-control form-control-sm font-monospace" value="{{ $invoice['consignee_tax'] ?? '' }}" placeholder="Tax Registration" oninput="syncVariable('consigneeTaxDisplay', this.value)">
                </div>
                <div class="col-6">
                  <label class="form-label small fw-semibold text-muted mb-0.5">Phone</label>
                  <input type="text" name="consignee_phone" id="inputConsigneePhone" class="form-control form-control-sm font-monospace" value="{{ $invoice['consignee_phone'] ?? '' }}" placeholder="Phone / Mobile" oninput="syncVariable('consigneePhoneDisplay', this.value)">
                </div>
              </div>

              <div class="mb-2">
                <label class="form-label small fw-semibold text-muted mb-0.5">Email</label>
                <input type="email" name="consignee_email" id="inputConsigneeEmail" class="form-control form-control-sm" value="{{ $invoice['consignee_email'] ?? '' }}" placeholder="contact@example.com" oninput="syncVariable('consigneeEmailDisplay', this.value)">
              </div>
            </div>

            <div id="toTheOrderAlert" class="alert alert-info py-2 px-3 small mb-2" style="display: none;">
              <i class="bi bi-info-circle-fill me-1"></i> Consignee will display as <strong>TO THE ORDER</strong>. Overseas buyer details can be specified separately below.
            </div>

            <div class="d-flex align-items-center justify-content-between small text-muted pt-2 border-top mb-3">
              <span>IEC: <strong id="sidebarIec">{{ $selectedConsigner->iec ?? '-' }}</strong></span>
              <span>Region: 
                <select name="region" id="inputRegion" class="form-select form-select-sm d-inline-block w-auto py-0 px-2 font-monospace" onchange="syncVariable('sidebarRegion', this.value)">
                  <option value="Gujarat" {{ ($invoice['region'] ?? 'Gujarat') === 'Gujarat' ? 'selected' : '' }}>Gujarat</option>
                  <option value="Jodhpur" {{ ($invoice['region'] ?? '') === 'Jodhpur' ? 'selected' : '' }}>Jodhpur</option>
                </select>
              </span>
            </div>

            <!-- Buyer Other Accordion / Collapsible (Optional) -->
            @php
              $hasBuyerOther = !empty($buyerOther['name']) || !empty($buyerOther['address']) || !empty($buyerOther['tax_code']);
            @endphp
            <div class="border rounded-2 p-2.5 bg-light-subtle mb-3">
              <div class="d-flex align-items-center justify-content-between">
                <div class="form-check form-switch mb-0">
                  <input class="form-check-input" type="checkbox" id="toggleBuyerOtherCheck" {{ $hasBuyerOther ? 'checked' : '' }} onchange="toggleBuyerOtherSection(this.checked)">
                  <label class="form-check-label small fw-bold text-dark" for="toggleBuyerOtherCheck">
                    <i class="bi bi-person-badge me-1 text-primary"></i> Buyer is different from Consignee
                  </label>
                </div>
                <div class="d-flex gap-1" id="buyerOtherActionButtons" style="{{ $hasBuyerOther ? '' : 'display: none;' }}">
                  <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 small" style="font-size: 0.72rem;" onclick="toggleEditAddress()">
                    <i class="bi bi-pencil me-1"></i>Edit
                  </button>
                  <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 small" style="font-size: 0.72rem;" onclick="copyConsigneeToBuyerOther()">
                    Copy Consignee
                  </button>
                </div>
              </div>

              <div id="buyerOtherFieldsContainer" class="mt-2.5 pt-2 border-top" style="{{ $hasBuyerOther ? '' : 'display: none;' }}">
                <div class="mb-2">
                  <label class="form-label small fw-semibold text-muted mb-0.5">Buyer Company Name</label>
                  <input type="text" name="buyer_name" class="form-control form-control-sm font-monospace fw-bold" id="inputBuyerOtherName" value="{{ $buyerOther['name'] ?? '' }}" placeholder="Leave blank if same as Consignee" oninput="syncVariable('buyerOtherNameDisplay', this.value)">
                </div>

                <div class="mb-2">
                  <div class="d-flex align-items-center justify-content-between">
                    <label class="form-label small fw-semibold text-muted mb-0.5">Address (ADD)</label>
                    <span class="badge bg-light text-muted border" id="addressEditStatusBadge" style="font-size: 0.68rem;">Invoice Snapshot</span>
                  </div>
                  <textarea name="buyer_address" class="form-control form-control-sm" id="inputBuyerOtherAddress" rows="2" placeholder="Full address" oninput="syncVariable('buyerOtherAddressDisplay', this.value ? 'ADD: ' + this.value : '')">{{ $buyerOther['address'] ?? '' }}</textarea>
                </div>

                <div class="row g-2 mb-2">
                  <div class="col-6">
                    <label class="form-label small fw-semibold text-muted mb-0.5">Tax Code</label>
                    <input type="text" name="buyer_tax" class="form-control form-control-sm font-monospace" id="inputBuyerOtherTax" value="{{ $buyerOther['tax_code'] ?? '' }}" placeholder="Tax Code / GSTIN" oninput="syncVariable('buyerOtherTaxDisplay', this.value)">
                  </div>
                  <div class="col-6">
                    <label class="form-label small fw-semibold text-muted mb-0.5">Telephone (TEL)</label>
                    <input type="text" name="buyer_phone" class="form-control form-control-sm font-monospace" id="inputBuyerOtherPhone" value="{{ $buyerOther['phone'] ?? '' }}" placeholder="Phone / Mobile" oninput="syncVariable('buyerOtherPhoneDisplay', this.value)">
                  </div>
                </div>

                <div class="mb-1">
                  <label class="form-label small fw-semibold text-muted mb-0.5">Email Address</label>
                  <input type="email" name="buyer_email" class="form-control form-control-sm" id="inputBuyerOtherEmail" value="{{ $buyerOther['email'] ?? '' }}" placeholder="company@domain.com" oninput="syncVariable('buyerOtherEmailDisplay', this.value)">
                </div>
              </div>
            </div>

            <!-- Notify Parties Accordion (1-4) -->
            <div class="border rounded-2 p-2.5 bg-light-subtle">
              <a class="d-flex align-items-center justify-content-between text-decoration-none text-dark" data-bs-toggle="collapse" href="#notifyPartiesCollapse" role="button" aria-expanded="false" aria-controls="notifyPartiesCollapse">
                <span class="small fw-bold d-flex align-items-center gap-1.5">
                  <i class="bi bi-bell-fill text-muted"></i> Notify Parties (1 to 4)
                  <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.68rem;">Optional</span>
                </span>
                <i class="bi bi-chevron-down text-muted small"></i>
              </a>

              <div class="collapse mt-2.5 pt-2 border-top" id="notifyPartiesCollapse">
                <ul class="nav nav-pills nav-fill mb-2 notify-party-tabs" id="notifyTabs" role="tablist">
                  <li class="nav-item" role="presentation">
                    <button class="nav-link active py-1 px-2 small fw-semibold" id="np1-tab" data-bs-toggle="pill" data-bs-target="#np1" type="button" role="tab">Party 1</button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link py-1 px-2 small fw-semibold" id="np2-tab" data-bs-toggle="pill" data-bs-target="#np2" type="button" role="tab">Party 2</button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link py-1 px-2 small fw-semibold" id="np3-tab" data-bs-toggle="pill" data-bs-target="#np3" type="button" role="tab">Party 3</button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link py-1 px-2 small fw-semibold" id="np4-tab" data-bs-toggle="pill" data-bs-target="#np4" type="button" role="tab">Party 4</button>
                  </li>
                </ul>

                <div class="tab-content" id="notifyTabsContent">
                  @for($i = 0; $i < 4; $i++)
                  <div class="tab-pane fade {{ $i === 0 ? 'show active' : '' }}" id="np{{ $i + 1 }}" role="tabpanel">
                    <div class="mb-2">
                      <label class="form-label small fw-semibold text-muted mb-0.5">Notify Party {{ $i + 1 }} Name</label>
                      <input type="text" class="form-control form-control-sm font-monospace" placeholder="Company Name" id="np_name_{{ $i }}" oninput="updateNotifyParty({{ $i }}, 'name', this.value)">
                    </div>
                    <div class="mb-2">
                      <label class="form-label small fw-semibold text-muted mb-0.5">Address</label>
                      <textarea class="form-control form-control-sm" rows="2" placeholder="Full Address" id="np_address_{{ $i }}" oninput="updateNotifyParty({{ $i }}, 'address', this.value)"></textarea>
                    </div>
                    <div class="row g-2 mb-1">
                      <div class="col-6">
                        <label class="form-label small fw-semibold text-muted mb-0.5">Tax Code</label>
                        <input type="text" class="form-control form-control-sm font-monospace" placeholder="Tax / Reg" id="np_tax_{{ $i }}" oninput="updateNotifyParty({{ $i }}, 'tax', this.value)">
                      </div>
                      <div class="col-6">
                        <label class="form-label small fw-semibold text-muted mb-0.5">Phone</label>
                        <input type="text" class="form-control form-control-sm font-monospace" placeholder="Phone" id="np_phone_{{ $i }}" oninput="updateNotifyParty({{ $i }}, 'phone', this.value)">
                      </div>
                    </div>
                  </div>
                  @endfor
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- 3. Shipping Logistics & Ports -->
        <div class="card border-0 shadow-sm mb-3 form-section-card" id="sec-shipping" style="border-radius: 12px; border: 1px solid #E2E8F0;">
          <div class="card-header bg-white py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
            <div class="fw-bold text-dark d-flex align-items-center gap-2">
              <span class="badge bg-primary text-white rounded-pill px-2 py-1" style="font-size: 0.72rem;">3</span>
              <i class="bi bi-geo-alt-fill text-primary"></i>
              <span>Shipping Logistics &amp; Ports</span>
            </div>
            <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">Optional / Bill Matching</span>
          </div>
          <div class="card-body p-3">
            
            <div class="row g-2 mb-2">
              <div class="col-6">
                <div class="d-flex align-items-center justify-content-between mb-0.5">
                  <label class="form-label small fw-semibold text-muted mb-0">Pre-Carriage by</label>
                  <div class="quick-chip-group">
                    <span class="quick-chip" onclick="quickFill('inputPreCarriage', 'TRUCK', 'preCarriageDisplay')">TRUCK</span>
                    <span class="quick-chip" onclick="quickFill('inputPreCarriage', 'RAIL', 'preCarriageDisplay')">RAIL</span>
                  </div>
                </div>
                <input type="text" name="pre_carriage_by" class="form-control form-control-sm font-monospace" id="inputPreCarriage" value="{{ $invoice['pre_carriage_by'] ?? '' }}" oninput="syncVariable('preCarriageDisplay', this.value)" placeholder="e.g. TRUCK">
              </div>
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Place of Receipt</label>
                <input type="text" name="place_of_receipt" class="form-control form-control-sm font-monospace" id="inputPlaceReceipt" value="{{ $invoice['place_of_receipt'] ?? '' }}" oninput="syncVariable('receiptPlaceDisplay', this.value)" placeholder="e.g. CHENNAI PORT">
              </div>
            </div>

            <div class="row g-2 mb-2">
              <div class="col-6">
                <div class="d-flex align-items-center justify-content-between mb-0.5">
                  <label class="form-label small fw-semibold text-muted mb-0">Country of Origin</label>
                  <span class="quick-chip" onclick="quickFill('inputCountryOrigin', 'INDIA', 'countryOriginDisplay')">INDIA</span>
                </div>
                <input type="text" name="country_of_origin" class="form-control form-control-sm font-monospace" id="inputCountryOrigin" value="{{ $invoice['country_of_origin'] ?? '' }}" oninput="syncVariable('countryOriginDisplay', this.value)" placeholder="e.g. INDIA">
              </div>
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Country Final Dest.</label>
                <input type="text" name="country_of_destination" class="form-control form-control-sm font-monospace" id="inputCountryDest" value="{{ $invoice['country_of_destination'] ?? '' }}" oninput="syncVariable('countryDestDisplay', this.value)" placeholder="e.g. VIETNAM">
              </div>
            </div>

            <div class="row g-2 mb-2">
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Vessel / Flight No.</label>
                <input type="text" name="vessel_flight_no" class="form-control form-control-sm font-monospace" id="inputVesselFlight" value="{{ $invoice['vessel_flight_no'] ?? '' }}" oninput="syncVariable('vesselFlightDisplay', this.value)" placeholder="e.g. MSC RAVEL V.1469W">
              </div>
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Port of Loading</label>
                <input type="text" name="port_of_loading" class="form-control form-control-sm font-monospace" id="inputPortLoading" value="{{ $invoice['port_of_loading'] ?? '' }}" oninput="syncVariable('portLoadingDisplay', this.value)" placeholder="e.g. CHENNAI PORT">
              </div>
            </div>

            <div class="row g-2 mb-2">
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Port of Discharge</label>
                <input type="text" name="port_of_discharge" class="form-control form-control-sm font-monospace" id="inputPortDischarge" value="{{ $invoice['port_of_discharge'] ?? '' }}" oninput="syncVariable('portDischargeDisplay', this.value)" placeholder="e.g. CATLAI PORT">
              </div>
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Final Destination</label>
                <input type="text" name="final_destination" class="form-control form-control-sm font-monospace" id="inputFinalDest" value="{{ $invoice['final_destination'] ?? '' }}" oninput="syncVariable('finalDestinationDisplay', this.value)" placeholder="e.g. CATLAI PORT">
              </div>
            </div>

            <div class="mb-1">
              <div class="d-flex align-items-center justify-content-between mb-0.5">
                <label class="form-label small fw-semibold text-muted mb-0">Terms of Delivery &amp; Payment</label>
                <div class="quick-chip-group">
                  <span class="quick-chip" onclick="quickFill('inputPaymentTerms', '100% AGAINST SCAN B/L COPY', 'paymentTermsDisplay')">100% SCAN B/L</span>
                  <span class="quick-chip" onclick="quickFill('inputPaymentTerms', '80% AT PORT &amp; 20% AFTER CHECKING', 'paymentTermsDisplay')">80% / 20%</span>
                  <span class="quick-chip" onclick="quickFill('inputPaymentTerms', '100% LC AT SIGHT', 'paymentTermsDisplay')">100% LC</span>
                </div>
              </div>
              @php
                $createIsIgst = (strtoupper($invoice['invoice_format'] ?? '') === 'IGST');
                $defaultCreateTerms = $createIsIgst ? '80% AT PORT & 20% AFTER CHECKING' : '100% AGAINST SCAN B/L COPY';
                $valPaymentTerms = (!empty($invoice['payment_terms']) && ($invoice['payment_terms'] !== '100% AGAINST SCAN B/L COPY' || !$createIsIgst)) ? $invoice['payment_terms'] : $defaultCreateTerms;
              @endphp
              <input type="text" name="payment_terms" class="form-control form-control-sm fw-bold" id="inputPaymentTerms" value="{{ $valPaymentTerms }}" oninput="syncVariable('paymentTermsDisplay', this.value)">
            </div>

          </div>
        </div>

        <!-- 4. Containers, Weights & Statutory Rules -->
        <div class="card border-0 shadow-sm mb-3 form-section-card" id="sec-containers" style="border-radius: 12px; border: 1px solid #E2E8F0;">
          <div class="card-header bg-white py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
            <div class="fw-bold text-dark d-flex align-items-center gap-2">
              <span class="badge bg-primary text-white rounded-pill px-2 py-1" style="font-size: 0.72rem;">4</span>
              <i class="bi bi-truck text-primary"></i>
              <span>Containers, Weights &amp; Statutory Info</span>
            </div>
            <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">Optional / Real-time Sync</span>
          </div>
          <div class="card-body p-3">
            
            <input type="hidden" name="container_number" id="inputContainer" value="{{ $invoice['container_number'] ?? '' }}">
            <input type="hidden" name="trailer_number" id="inputTrailer" value="{{ $invoice['trailer_number'] ?? '' }}">

            <div class="row g-2 mb-2">
              <div class="col-7">
                <div class="d-flex align-items-center justify-content-between mb-0.5">
                  <label class="form-label small fw-semibold text-muted mb-0">Container Type</label>
                  <div class="quick-chip-group">
                    <span class="quick-chip" onclick="quickFillSelect('inputContainerType', '20 FCL', 'containerTypeDisplay')">20 FCL</span>
                    <span class="quick-chip" onclick="quickFillSelect('inputContainerType', '40 FCL', 'containerTypeDisplay')">40 FCL</span>
                  </div>
                </div>
                <select name="container_type" id="inputContainerType" class="form-select form-select-sm font-monospace fw-semibold" onchange="syncVariable('containerTypeDisplay', this.value)">
                  <option value="" {{ empty($invoice['container_type']) ? 'selected' : '' }}></option>
                  <option value="20 FCL" {{ ($invoice['container_type'] ?? '') === '20 FCL' ? 'selected' : '' }}>20 FCL</option>
                  <option value="40 FCL" {{ ($invoice['container_type'] ?? '') === '40 FCL' ? 'selected' : '' }}>40 FCL</option>
                  <option value="20' Heavy Duty" {{ ($invoice['container_type'] ?? '') === "20' Heavy Duty" ? 'selected' : '' }}>20' Heavy Duty</option>
                  <option value="40' High Cube" {{ ($invoice['container_type'] ?? '') === "40' High Cube" ? 'selected' : '' }}>40' High Cube</option>
                </select>
              </div>
              <div class="col-5">
                <label class="form-label small fw-semibold text-muted mb-0.5">No. of Containers</label>
                <input type="number" min="1" name="no_of_containers" id="inputNoOfContainers" class="form-control form-control-sm font-monospace fw-bold" value="{{ $invoice['no_of_containers'] ?? '' }}" oninput="onNoOfContainersChange(this.value)" placeholder="e.g. 1">
              </div>
            </div>

            <!-- Weights (G.W. & N.W.) -->
            <div class="row g-2 mb-2.5">
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Gross Weight (G.WT KG)</label>
                <div class="input-group input-group-sm">
                  <input type="number" step="0.01" name="gross_weight" id="inputGrossWeight" class="form-control form-control-sm font-monospace fw-bold" value="{{ $invoice['gross_weight'] ?? '' }}" oninput="syncVariable('grossWeightDisplay', this.value ? this.value + ' KGS' : '')" placeholder="e.g. 26500">
                  <span class="input-group-text small text-muted px-2">KG</span>
                </div>
              </div>
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">Net Weight (N.WT KG)</label>
                <div class="input-group input-group-sm">
                  <input type="number" step="0.01" name="net_weight" id="inputNetWeight" class="form-control form-control-sm font-monospace fw-bold" value="{{ $invoice['net_weight'] ?? '' }}" oninput="syncVariable('netWeightDisplay', this.value ? this.value + ' KGS' : '')" placeholder="e.g. 25500">
                  <span class="input-group-text small text-muted px-2">KG</span>
                </div>
              </div>
            </div>

            <!-- Statutory / ISO Override -->
            <div class="row g-2 mb-1">
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5" title="ISO Certification Details">ISO Certification</label>
                <input type="text" name="iso_certification" id="inputIsoCert" class="form-control form-control-sm font-monospace" value="{{ $invoice['iso_certification'] ?? '' }}" oninput="syncVariable('isoCertDisplay', this.value)" placeholder="e.g. ISO 9001:2015">
              </div>
              <div class="col-6">
                <label class="form-label small fw-semibold text-muted mb-0.5">LUT / Export Rule</label>
                <input type="text" name="lut_no" id="inputLutRule" class="form-control form-control-sm font-monospace" value="{{ $invoice['lut_no'] ?? '' }}" oninput="syncVariable('lutRuleDisplay', this.value)" placeholder="e.g. UNDER LUT NO - 06/LUT">
              </div>
            </div>

          </div>
        </div>

        <!-- 5. Multiple Line Items Repeater (Goods Table) -->
        <div class="card border-0 shadow-sm mb-3 form-section-card" id="sec-items" style="border-radius: 12px; border: 1px solid #E2E8F0;">
          <div class="card-header bg-white py-2.5 px-3 d-flex align-items-center justify-content-between border-bottom">
            <div class="fw-bold text-dark d-flex align-items-center gap-2">
              <span class="badge bg-primary text-white rounded-pill px-2 py-1" style="font-size: 0.72rem;">5</span>
              <i class="bi bi-box-seam text-primary"></i>
              <span>Export Product Line Items (<span id="itemsCountBadge">1</span>)</span>
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
                <span class="fw-bold font-monospace text-dark" id="summaryTotalQty">0.000</span>
              </div>
              <div class="d-flex align-items-center justify-content-between mb-1.5">
                <span class="small fw-semibold text-muted">Total Amount (<span class="currency-code">USD</span>):</span>
                <span class="fw-bold font-monospace fs-6 text-primary" id="summaryTotalAmount">$0.00</span>
              </div>
              <div class="d-flex align-items-center justify-content-between">
                <span class="small fw-semibold text-muted">Rounded to (<span class="currency-code">USD</span>):</span>
                <span class="fw-black font-monospace text-success fs-6" id="summaryRoundedAmount">$0.00</span>
              </div>
            </div>

          </div>
        </div>

        <!-- Floating / Sticky Action Footer in Left Sidebar -->
        <div class="sticky-action-bar p-3 bg-white border rounded-3 shadow-sm mb-4 d-flex align-items-center justify-content-between gap-2">
          <div>
            <div class="small text-muted fw-semibold" style="font-size: 0.75rem;">LIVE INVOICE TOTAL</div>
            <div class="fw-black font-monospace text-primary fs-5" id="stickyTotalAmount">$0.00</div>
          </div>
          <button type="submit" class="btn btn-primary py-2 px-3 fw-semibold shadow-sm d-flex align-items-center gap-2">
            <i class="bi bi-floppy2-fill"></i>
            <span>Save &amp; Create Invoice</span>
          </button>
        </div>

      </form>

    </div>

    <!-- Right Column: The Full Commercial Invoice Table Sheet -->
    <div class="col-lg-7 col-xl-7">
      <div class="invoice-preview-sticky">
        <div class="card border-0 shadow-sm invoice-preview-card" style="border-radius: 12px; border: 1px solid #E2E8F0; overflow: hidden;">
          
          <div class="card-header bg-white py-2.5 px-3 d-flex align-items-center justify-content-between border-bottom flex-shrink-0 d-print-none">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-table text-primary"></i>
              <span class="fw-bold text-dark">Commercial Invoice (Live Synchronized Preview)</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <a href="{{ route('invoices.settings') }}" class="btn btn-sm btn-outline-secondary py-1 px-2.5">
                <i class="bi bi-gear me-1"></i> Settings
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
/* Quick navigation pills */
.section-nav-bar {
  overflow-x: auto;
  white-space: nowrap;
  scrollbar-width: thin;
}
.section-nav-bar::-webkit-scrollbar {
  height: 4px;
}
.section-nav-bar::-webkit-scrollbar-thumb {
  background-color: #CBD5E1;
  border-radius: 4px;
}
.sec-nav-link {
  transition: all 0.15s ease-in-out;
  border-radius: 6px;
}
.sec-nav-link:hover {
  background-color: #EEF2F6 !important;
  color: #1E293B !important;
}

/* Quick Fill Chips */
.quick-chip-group {
  display: inline-flex;
  gap: 4px;
}
.quick-chip {
  font-size: 0.65rem;
  font-weight: 600;
  padding: 1px 6px;
  background-color: #F1F5F9;
  color: #475569;
  border: 1px solid #CBD5E1;
  border-radius: 4px;
  cursor: pointer;
  user-select: none;
  transition: all 0.12s ease-in-out;
}
.quick-chip:hover {
  background-color: #2563EB;
  border-color: #2563EB;
  color: #FFFFFF;
}

/* Form section cards */
.form-section-card {
  scroll-margin-top: 60px;
}

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
@media (min-width: 992px) {
  .invoice-preview-sticky {
    position: sticky;
    top: 1rem;
    z-index: 10;
  }
  .invoice-preview-card {
    max-height: calc(100vh - 2rem);
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
// Quick fill helper
function quickFill(inputId, value, syncId) {
  const el = document.getElementById(inputId);
  if (el) {
    el.value = value;
    el.dispatchEvent(new Event('input', { bubbles: true }));
  }
  if (syncId) {
    syncVariable(syncId, value);
  }
}

function quickFillSelect(selectId, value, syncId) {
  const el = document.getElementById(selectId);
  if (el) {
    el.value = value;
    el.dispatchEvent(new Event('change', { bubbles: true }));
  }
  if (syncId) {
    syncVariable(syncId, value);
  }
}

function toggleBuyerOtherSection(checked) {
  const container = document.getElementById('buyerOtherFieldsContainer');
  const btnGroup = document.getElementById('buyerOtherActionButtons');
  if (container) container.style.display = checked ? '' : 'none';
  if (btnGroup) btnGroup.style.display = checked ? '' : 'none';
  if (!checked) {
    // If unchecked, clear buyer other fields
    document.getElementById('inputBuyerOtherName').value = '';
    document.getElementById('inputBuyerOtherAddress').value = '';
    document.getElementById('inputBuyerOtherTax').value = '';
    document.getElementById('inputBuyerOtherPhone').value = '';
    document.getElementById('inputBuyerOtherEmail').value = '';
    syncVariable('buyerOtherNameDisplay', '');
    syncVariable('buyerOtherAddressDisplay', '');
    syncVariable('buyerOtherTaxDisplay', '');
    syncVariable('buyerOtherPhoneDisplay', '');
    syncVariable('buyerOtherEmailDisplay', '');
  }
}

// Initial Blank Items Array for New Invoice
let invoiceItems = [
  {
    marks_numbers: "",
    container_no: "",
    trailer_no: "",
    description: "",
    hsn_code: "",
    quantity: "",
    rate: "",
    amount: 0
  }
];

// Notify Parties Array (1 to 4)
let notifyParties = [
  { name: "", address: "", tax: "", phone: "", email: "" },
  { name: "", address: "", tax: "", phone: "", email: "" },
  { name: "", address: "", tax: "", phone: "", email: "" },
  { name: "", address: "", tax: "", phone: "", email: "" }
];

let currentFormat = "LUT";
let currentDeliveryTerm = "CIF";
let currentCurrency = "USD";

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
    alert("Please select a Consignee / Buyer Master first.");
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
    if (elIec) elIec.textContent = "-";
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

  // Update Summary Card & Sticky Total
  const sQty = document.getElementById("summaryTotalQty");
  const sAmt = document.getElementById("summaryTotalAmount");
  const sRnd = document.getElementById("summaryRoundedAmount");
  const stickyAmt = document.getElementById("stickyTotalAmount");
  if (sQty) sQty.textContent = totalQty > 0 ? totalQty.toFixed(3) : "0.000";
  if (sAmt) sAmt.textContent = sym + totalAmt.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  if (sRnd) sRnd.textContent = sym + rounded.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  if (stickyAmt) stickyAmt.textContent = sym + rounded.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

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
  recalculateTotals();
  onFormatChange("{{ $invoice['invoice_format'] ?? 'LUT' }}");
  onDeliveryTermChange("{{ $invoice['delivery_term'] ?? 'CIF' }}");
  onCurrencyChange("{{ $invoice['currency'] ?? 'USD' }}");
});
</script>
@endsection
