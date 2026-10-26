@extends('layouts.app')

@section('content')
<main class="content-area p-3 p-lg-4" id="invoiceSettingsApp">

  <!-- Breadcrumb & Header -->
  <div class="mb-4">
    <nav aria-label="breadcrumb" class="mb-2">
      <ol class="breadcrumb mb-0 small">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bi bi-house-door me-1"></i>Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('invoices.create') }}">Commercial Invoices</a></li>
        <li class="breadcrumb-item active" aria-current="page">Invoice Settings</li>
      </ol>
    </nav>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
      <div>
        <div class="d-flex align-items-center gap-2 mb-1">
          <h2 class="fw-bold text-dark mb-0" style="font-size: 1.6rem; letter-spacing: -0.02em;">Commercial Invoice Settings</h2>
          <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1" style="font-size: 0.78rem;">
            <i class="bi bi-sliders me-1"></i> Global Configuration
          </span>
        </div>
        <p class="text-muted small mb-0">
          Configure default statutory exporter particulars, bank accounts, LUT rules, shipping logistics, badges, and authorised signatory. Changes apply automatically to all Commercial Invoices.
        </p>
      </div>

      <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="{{ route('invoices.create') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3" style="font-size: 0.88rem; font-weight: 500;">
          <i class="bi bi-arrow-left"></i>
          <span>Back to Invoice Creator</span>
        </a>
        <button type="submit" form="invoiceSettingsForm" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3.5 py-2 rounded-3 shadow-sm" style="font-size: 0.88rem; font-weight: 600;">
          <i class="bi bi-check2-circle"></i>
          <span>Save All Settings</span>
        </button>
      </div>
    </div>
  </div>

  @if(session('success'))
  <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 10px;">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-check-circle-fill fs-5 text-success"></i>
      <div class="fw-semibold">{{ session('success') }}</div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  @if($errors->any())
  <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 10px;">
    <div class="d-flex align-items-start gap-2.5">
      <i class="bi bi-exclamation-octagon-fill fs-5 text-danger mt-0.5"></i>
      <div>
        <div class="fw-bold">Please correct the following input errors:</div>
        <ul class="mb-0 ps-3 small mt-1">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  <!-- Form Start -->
  <form action="{{ route('invoices.settings.update') }}" method="POST" id="invoiceSettingsForm">
    @csrf

    <div class="row g-4">
      
      <!-- Left Column: Settings Form Cards -->
      <div class="col-xl-8">

        <!-- Section 1: Exporter & Manufacturer Particulars -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; border: 1px solid #E2E8F0;">
          <div class="card-header bg-white py-3 px-4 border-bottom">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-building text-primary fs-5"></i>
              <h5 class="fw-bold text-dark mb-0">1. Exporter & Manufacturer Identity</h5>
            </div>
            <div class="small text-muted mt-1">Top-left header of the Commercial Export Invoice.</div>
          </div>
          <div class="card-body p-4">
            
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted">Company / Exporter Name <span class="text-danger">*</span></label>
                <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $setting->company_name) }}" required>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted">Tagline / Business Nature</label>
                <input type="text" name="tagline" class="form-control" value="{{ old('tagline', $setting->tagline) }}">
              </div>

              <div class="col-12">
                <label class="form-label small fw-semibold text-muted">Official Factory / Export Address <span class="text-danger">*</span></label>
                <textarea name="address" class="form-control" rows="2" required>{{ old('address', $setting->address) }}</textarea>
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Telephone / Mobile Contacts</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $setting->phone) }}">
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Official Export Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $setting->email) }}">
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Website URL</label>
                <input type="text" name="website" class="form-control" value="{{ old('website', $setting->website) }}">
              </div>
            </div>

          </div>
        </div>

        <!-- Section 2: Statutory Tax, IEC, GSTIN & LUT Rule -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; border: 1px solid #E2E8F0;">
          <div class="card-header bg-white py-3 px-4 border-bottom">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-shield-check text-primary fs-5"></i>
              <h5 class="fw-bold text-dark mb-0">2. Statutory Tax, IEC, GSTIN & LUT Regulations</h5>
            </div>
            <div class="small text-muted mt-1">Export customs identification, Letter of Undertaking (LUT) rule, and ARN tracking.</div>
          </div>
          <div class="card-body p-4">
            
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">IEC Number <span class="text-danger">*</span></label>
                <input type="text" name="iec" class="form-control font-monospace text-uppercase" value="{{ old('iec', $setting->iec) }}" required>
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">GSTIN <span class="text-danger">*</span></label>
                <input type="text" name="gstin" class="form-control font-monospace text-uppercase" value="{{ old('gstin', $setting->gstin) }}" required>
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">PAN Number <span class="text-danger">*</span></label>
                <input type="text" name="pan" class="form-control font-monospace text-uppercase" value="{{ old('pan', $setting->pan) }}" required>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted">LUT ARN Number</label>
                <input type="text" name="lut_arn" class="form-control font-monospace" value="{{ old('lut_arn', $setting->lut_arn) }}">
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted">Default Invoice Prefix / Format</label>
                <input type="text" name="invoice_prefix" class="form-control font-monospace" value="{{ old('invoice_prefix', $setting->invoice_prefix) }}">
              </div>

              <div class="col-12">
                <label class="form-label small fw-semibold text-muted">LUT Statutory Rule Header (Bond / Undertaking Clause)</label>
                <textarea name="lut_rule" class="form-control font-monospace small" rows="2">{{ old('lut_rule', $setting->lut_rule) }}</textarea>
                <div class="form-text small">Appears across the top subtitle and right-hand terms cell in the invoice.</div>
              </div>
            </div>

          </div>
        </div>

        <!-- Section 3: Banking & Forex Exchange -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; border: 1px solid #E2E8F0;">
          <div class="card-header bg-white py-3 px-4 border-bottom">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-bank text-primary fs-5"></i>
              <h5 class="fw-bold text-dark mb-0">3. Authorised Forex Bank & Remittance Particulars</h5>
            </div>
            <div class="small text-muted mt-1">Bank credentials printed in the lower bank details box for foreign buyer remittances.</div>
          </div>
          <div class="card-body p-4">
            
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted">Bank Name <span class="text-danger">*</span></label>
                <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $setting->bank_name) }}" required>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted">Branch Name</label>
                <input type="text" name="bank_branch" class="form-control" value="{{ old('bank_branch', $setting->bank_branch) }}">
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Forex Account Number <span class="text-danger">*</span></label>
                <input type="text" name="bank_account_no" class="form-control font-monospace fw-bold" value="{{ old('bank_account_no', $setting->bank_account_no) }}" required>
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">SWIFT Code <span class="text-danger">*</span></label>
                <input type="text" name="bank_swift_code" class="form-control font-monospace text-uppercase" value="{{ old('bank_swift_code', $setting->bank_swift_code) }}" required>
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Authorised Dealer (AD) Code</label>
                <input type="text" name="bank_ad_code" class="form-control font-monospace" value="{{ old('bank_ad_code', $setting->bank_ad_code) }}">
              </div>

              <div class="col-12">
                <label class="form-label small fw-semibold text-muted">Bank Full Address</label>
                <input type="text" name="bank_address" class="form-control" value="{{ old('bank_address', $setting->bank_address) }}">
              </div>
            </div>

          </div>
        </div>

        <!-- Section 4: Default Shipping Logistics & Terms -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; border: 1px solid #E2E8F0;">
          <div class="card-header bg-white py-3 px-4 border-bottom">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-truck text-primary fs-5"></i>
              <h5 class="fw-bold text-dark mb-0">4. Shipping Logistics & Terms Defaults</h5>
            </div>
            <div class="small text-muted mt-1">Default pre-carriage, loading/discharge ports, payment terms, and origin clause.</div>
          </div>
          <div class="card-body p-4">
            
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Pre-Carriage By</label>
                <input type="text" name="pre_carriage_by" class="form-control" value="{{ old('pre_carriage_by', $setting->pre_carriage_by) }}">
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Place of Receipt</label>
                <input type="text" name="place_of_receipt" class="form-control" value="{{ old('place_of_receipt', $setting->place_of_receipt) }}">
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Country of Origin of Goods</label>
                <input type="text" name="country_of_origin" class="form-control" value="{{ old('country_of_origin', $setting->country_of_origin) }}">
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Default Port of Loading</label>
                <input type="text" name="default_port_of_loading" class="form-control" value="{{ old('default_port_of_loading', $setting->default_port_of_loading) }}">
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Default Port of Discharge</label>
                <input type="text" name="default_port_of_discharge" class="form-control" value="{{ old('default_port_of_discharge', $setting->default_port_of_discharge) }}">
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Default Final Destination</label>
                <input type="text" name="default_final_destination" class="form-control" value="{{ old('default_final_destination', $setting->default_final_destination) }}">
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted">Default Terms of Delivery & Payment</label>
                <input type="text" name="default_payment_terms" class="form-control" value="{{ old('default_payment_terms', $setting->default_payment_terms) }}">
              </div>

              <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Origin Clause Text</label>
                <input type="text" name="origin_clause" class="form-control" value="{{ old('origin_clause', $setting->origin_clause) }}">
              </div>

              <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Billing Currency <span class="text-danger">*</span></label>
                <input type="text" name="currency" class="form-control font-monospace" value="{{ old('currency', $setting->currency) }}" required>
              </div>

              <div class="col-12">
                <label class="form-label small fw-semibold text-muted">Legal Export Declaration Text</label>
                <textarea name="declaration" class="form-control small" rows="2">{{ old('declaration', $setting->declaration) }}</textarea>
              </div>
            </div>

          </div>
        </div>

        <!-- Section 5: Badges, Certifications & Authorised Signatory -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; border: 1px solid #E2E8F0;">
          <div class="card-header bg-white py-3 px-4 border-bottom">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-patch-check text-primary fs-5"></i>
              <h5 class="fw-bold text-dark mb-0">5. Badges, Certifications & Authorised Signatory</h5>
            </div>
            <div class="small text-muted mt-1">Export council affiliations, ISO registration numbers, and signatory authorization.</div>
          </div>
          <div class="card-body p-4">
            
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted">FIEO RCMC Registration Number</label>
                <input type="text" name="rcmc_fieo_no" class="form-control" value="{{ old('rcmc_fieo_no', $setting->rcmc_fieo_no) }}">
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted">REPC Registration Number</label>
                <input type="text" name="repc_no" class="form-control" value="{{ old('repc_no', $setting->repc_no) }}">
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted">ISO Certification Particulars</label>
                <input type="text" name="iso_certification" class="form-control" value="{{ old('iso_certification', $setting->iso_certification) }}">
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted">Export House Recognition Level</label>
                <input type="text" name="export_house_star" class="form-control" value="{{ old('export_house_star', $setting->export_house_star) }}">
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Signatory Company Line</label>
                <input type="text" name="signatory_company" class="form-control" value="{{ old('signatory_company', $setting->signatory_company) }}">
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Signatory Authorized Name</label>
                <input type="text" name="signatory_name" class="form-control" value="{{ old('signatory_name', $setting->signatory_name) }}">
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Signatory Designation / Title</label>
                <input type="text" name="signatory_title" class="form-control" value="{{ old('signatory_title', $setting->signatory_title) }}">
              </div>
            </div>

          </div>
        </div>

      </div>

      <!-- Right Column: Live Summary / Sticky Action Panel -->
      <div class="col-xl-4">
        <div class="sticky-top" style="top: 20px;">

          <!-- Quick Action Card -->
          <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; border: 1px solid #E2E8F0;">
            <div class="card-body p-4 text-center">
              <div class="p-3 bg-primary-subtle text-primary rounded-circle d-inline-flex mb-3">
                <i class="bi bi-file-earmark-spreadsheet fs-3"></i>
              </div>
              <h5 class="fw-bold text-dark mb-1">Apply Invoice Settings</h5>
              <p class="small text-muted mb-4">
                Saving will instantly refresh the statutory table values across both the Invoice Creator and Consigners Edit screens.
              </p>
              
              <button type="submit" form="invoiceSettingsForm" class="btn btn-primary w-100 py-2.5 rounded-3 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2 mb-2">
                <i class="bi bi-save2"></i> Save All Settings
              </button>

              <a href="{{ route('invoices.create') }}" class="btn btn-outline-secondary w-100 py-2 rounded-3 text-decoration-none small">
                <i class="bi bi-eye me-1"></i> Preview in Invoice Creator
              </a>
            </div>
          </div>

          <!-- Configuration Overview Card -->
          <div class="card border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #E2E8F0;">
            <div class="card-header bg-white py-3 px-3.5 border-bottom">
              <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.05em;">Current Settings Summary</span>
            </div>
            <div class="card-body p-3.5 small">
              <div class="d-flex justify-content-between py-1.5 border-bottom">
                <span class="text-muted">Exporter:</span>
                <span class="fw-bold text-dark">{{ $setting->company_name }}</span>
              </div>
              <div class="d-flex justify-content-between py-1.5 border-bottom">
                <span class="text-muted">IEC No:</span>
                <span class="font-monospace fw-bold text-primary">{{ $setting->iec }}</span>
              </div>
              <div class="d-flex justify-content-between py-1.5 border-bottom">
                <span class="text-muted">GSTIN:</span>
                <span class="font-monospace fw-bold">{{ $setting->gstin }}</span>
              </div>
              <div class="d-flex justify-content-between py-1.5 border-bottom">
                <span class="text-muted">Forex Bank:</span>
                <span class="fw-bold text-truncate" style="max-width: 170px;">{{ $setting->bank_name }}</span>
              </div>
              <div class="d-flex justify-content-between py-1.5 border-bottom">
                <span class="text-muted">SWIFT:</span>
                <span class="font-monospace fw-bold">{{ $setting->bank_swift_code }}</span>
              </div>
              <div class="d-flex justify-content-between py-1.5 border-bottom">
                <span class="text-muted">Signatory:</span>
                <span class="fw-bold text-dark">{{ $setting->signatory_name }} ({{ $setting->signatory_title }})</span>
              </div>
              <div class="d-flex justify-content-between py-1.5">
                <span class="text-muted">Badges:</span>
                <span class="badge bg-success-subtle text-success">5 Recognized</span>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>

  </form>

</main>
@endsection
