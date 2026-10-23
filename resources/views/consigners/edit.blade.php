@extends('layouts.app')

@section('content')
<main class="content-area p-3 p-lg-4" id="consignerEditApp">

  <!-- Top Navigation & Breadcrumbs -->
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1 small">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('consigners.index') }}" class="text-decoration-none">Consigners Master</a></li>
          <li class="breadcrumb-item active" aria-current="page">Edit Consigner</li>
        </ol>
      </nav>
      <div class="d-flex align-items-center gap-2">
        <h3 class="fw-bold text-dark mb-0">Edit Consigner Particulars</h3>
        <span class="badge bg-primary font-monospace">{{ $consigner->consigner_code ?? ('EXP-' . $consigner->id) }}</span>
      </div>
      <p class="text-muted small mb-0 mt-1">Updating records for <strong>{{ $consigner->name }}</strong> in MySQL database.</p>
    </div>

    <div class="d-flex align-items-center gap-2">
      <a href="{{ route('consigners.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2 shadow-sm px-3">
        <i class="bi bi-arrow-left"></i>
        <span>Back to List</span>
      </a>
      <a href="{{ route('pdf-editor.index') }}" class="btn btn-outline-primary d-flex align-items-center gap-2 shadow-sm px-3" title="Open PDF Visual Editor">
        <i class="bi bi-file-earmark-diff"></i>
        <span>PDF Editor</span>
      </a>
    </div>
  </div>

  @if($errors->any())
  <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
      <div>
        <div class="fw-bold">Please correct the following errors:</div>
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

  <!-- Main Edit Form Card -->
  <div class="card border-0 shadow-sm" style="border-radius:12px;">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-2">
        <div class="bg-primary text-white rounded p-2 d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
          <i class="bi bi-pencil-square fs-5"></i>
        </div>
        <div>
          <h5 class="fw-bold text-dark mb-0">Modify Exporter Particulars</h5>
          <div class="small text-muted">All updates immediately sync with the MySQL database and the visual PDF stamping suite</div>
        </div>
      </div>
      <div>
        <span class="badge {{ $consigner->status === 'Active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-muted' }} px-3 py-2 fw-bold">
          {{ $consigner->status }}
        </span>
      </div>
    </div>

    <form action="{{ route('consigners.update', $consigner->id) }}" method="POST">
      @csrf
      @method('PUT')
      <div class="card-body p-4">

        <!-- Section 1: Firm & Exporter Information -->
        <div class="mb-4">
          <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
            <span class="badge bg-primary-subtle text-primary fw-bold px-2 py-1" style="font-size:0.75rem;">Step 1</span>
            <h6 class="fw-bold text-primary mb-0 text-uppercase" style="font-size:0.8rem; letter-spacing:0.05em;">
              Firm & Exporter Legal Particulars
            </h6>
          </div>
          <div class="row g-3">
            <div class="col-md-8">
              <label for="name" class="form-label small fw-semibold">Company / Firm Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $consigner->name) }}" required placeholder="e.g. Reliance Granito Private Limited">
              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-4">
              <label for="entity_type" class="form-label small fw-semibold">Entity Type</label>
              <select class="form-select @error('entity_type') is-invalid @enderror" id="entity_type" name="entity_type">
                @php $currentType = old('entity_type', $consigner->entity_type); @endphp
                <option value="Manufacturer Exporter" {{ $currentType == 'Manufacturer Exporter' ? 'selected' : '' }}>Manufacturer Exporter</option>
                <option value="Merchant Exporter" {{ $currentType == 'Merchant Exporter' ? 'selected' : '' }}>Merchant Exporter</option>
                <option value="Manufacturing Facility / Plant" {{ $currentType == 'Manufacturing Facility / Plant' ? 'selected' : '' }}>Manufacturing Facility / Plant</option>
                <option value="Sister Firm / Associate" {{ $currentType == 'Sister Firm / Associate' ? 'selected' : '' }}>Sister Firm / Associate</option>
              </select>
              @error('entity_type')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>

        <!-- Section 2: Statutory EDI & Tax Codes -->
        <div class="mb-4">
          <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
            <span class="badge bg-primary-subtle text-primary fw-bold px-2 py-1" style="font-size:0.75rem;">Step 2</span>
            <h6 class="fw-bold text-primary mb-0 text-uppercase" style="font-size:0.8rem; letter-spacing:0.05em;">
              Statutory Customs EDI & Tax Registration
            </h6>
          </div>
          <div class="row g-3">
            <div class="col-md-4">
              <label for="iec" class="form-label small fw-semibold">IEC Code (10 Chars) <span class="text-danger">*</span></label>
              <input type="text" class="form-control text-uppercase font-monospace @error('iec') is-invalid @enderror" id="iec" name="iec" value="{{ old('iec', $consigner->iec) }}" required maxlength="10" placeholder="e.g. ABEFR3842G">
              @error('iec')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-2">
              <label for="iec_branch" class="form-label small fw-semibold">Branch No</label>
              <input type="text" class="form-control font-monospace @error('iec_branch') is-invalid @enderror" id="iec_branch" name="iec_branch" value="{{ old('iec_branch', $consigner->iec_branch ?? '0') }}" maxlength="5" placeholder="0">
              @error('iec_branch')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6">
              <label for="gstin" class="form-label small fw-semibold">GSTIN (15 Chars) <span class="text-danger">*</span></label>
              <input type="text" class="form-control text-uppercase font-monospace @error('gstin') is-invalid @enderror" id="gstin" name="gstin" value="{{ old('gstin', $consigner->gstin) }}" required maxlength="15" placeholder="e.g. 08ABEFR3842G1Z5">
              @error('gstin')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-4">
              <label for="pan" class="form-label small fw-semibold">PAN / Tax ID</label>
              <input type="text" class="form-control text-uppercase font-monospace @error('pan') is-invalid @enderror" id="pan" name="pan" value="{{ old('pan', $consigner->pan) }}" maxlength="10" placeholder="e.g. ABEFR3842G">
              @error('pan')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-4">
              <label for="ad_code" class="form-label small fw-semibold">AD Code (Authorized Dealer 7 Digits)</label>
              <input type="text" class="form-control font-monospace @error('ad_code') is-invalid @enderror" id="ad_code" name="ad_code" value="{{ old('ad_code', $consigner->ad_code) }}" maxlength="15" placeholder="e.g. 6390013">
              @error('ad_code')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-4">
              <label for="status" class="form-label small fw-semibold">Operational Status</label>
              <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                @php $currentStatus = old('status', $consigner->status); @endphp
                <option value="Active" {{ $currentStatus == 'Active' ? 'selected' : '' }}>Active (Ready for Docs)</option>
                <option value="Inactive" {{ $currentStatus == 'Inactive' ? 'selected' : '' }}>Inactive / Suspended</option>
              </select>
              @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>

        <!-- Section 3: Authorized Bank & AD Details -->
        <div class="mb-4">
          <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
            <span class="badge bg-primary-subtle text-primary fw-bold px-2 py-1" style="font-size:0.75rem;">Step 3</span>
            <h6 class="fw-bold text-primary mb-0 text-uppercase" style="font-size:0.8rem; letter-spacing:0.05em;">
              Authorized Dealer Forex Bank Details
            </h6>
          </div>
          <div class="row g-3">
            <div class="col-12">
              <label for="forex_bank" class="form-label small fw-semibold">Authorized Bank Name, Branch & Account No</label>
              <input type="text" class="form-control @error('forex_bank') is-invalid @enderror" id="forex_bank" name="forex_bank" value="{{ old('forex_bank', $consigner->forex_bank) }}" placeholder="e.g. ICICI Bank Limited, Jodhpur Branch (A/C: ICIC0006827)">
              @error('forex_bank')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>

        <!-- Section 4: Operational Addresses -->
        <div class="mb-4">
          <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
            <span class="badge bg-primary-subtle text-primary fw-bold px-2 py-1" style="font-size:0.75rem;">Step 4</span>
            <h6 class="fw-bold text-primary mb-0 text-uppercase" style="font-size:0.8rem; letter-spacing:0.05em;">
              Factory & Registered Addresses
            </h6>
          </div>
          <div class="row g-3">
            <div class="col-12">
              <label for="registered_address" class="form-label small fw-semibold">Registered Office Address <span class="text-danger">*</span></label>
              <textarea class="form-control @error('registered_address') is-invalid @enderror" id="registered_address" name="registered_address" rows="2" required placeholder="Street address, City, District, State, PIN Code">{{ old('registered_address', $consigner->registered_address) }}</textarea>
              @error('registered_address')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-8">
              <label for="factory_address" class="form-label small fw-semibold">Factory / Manufacturing Works Address (e.g. Morbi Plant)</label>
              <textarea class="form-control @error('factory_address') is-invalid @enderror" id="factory_address" name="factory_address" rows="2" placeholder="Factory premises, Highway / GIDC, Morbi - 363642, Gujarat">{{ old('factory_address', $consigner->factory_address) }}</textarea>
              @error('factory_address')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-4">
              <label for="region" class="form-label small fw-semibold">Primary Manufacturing Region</label>
              <select class="form-select @error('region') is-invalid @enderror" id="region" name="region">
                @php $currentRegion = old('region', $consigner->region); @endphp
                <option value="Morbi" {{ $currentRegion == 'Morbi' ? 'selected' : '' }}>Morbi Facility (Gujarat Ceramic Cluster)</option>
                <option value="Jodhpur" {{ $currentRegion == 'Jodhpur' ? 'selected' : '' }}>Jodhpur Facility (Rajasthan Granite Hub)</option>
              </select>
              @error('region')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>

        <!-- Section 5: Authorized Contact Details -->
        <div class="mb-4">
          <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
            <span class="badge bg-primary-subtle text-primary fw-bold px-2 py-1" style="font-size:0.75rem;">Step 5</span>
            <h6 class="fw-bold text-primary mb-0 text-uppercase" style="font-size:0.8rem; letter-spacing:0.05em;">
              Authorized Contact Person
            </h6>
          </div>
          <div class="row g-3">
            <div class="col-md-4">
              <label for="contact_person" class="form-label small fw-semibold">Contact Person</label>
              <input type="text" class="form-control @error('contact_person') is-invalid @enderror" id="contact_person" name="contact_person" value="{{ old('contact_person', $consigner->contact_person) }}" placeholder="e.g. Rajesh B. Patel">
              @error('contact_person')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-4">
              <label for="contact_phone" class="form-label small fw-semibold">Direct Phone / Mobile</label>
              <input type="text" class="form-control @error('contact_phone') is-invalid @enderror" id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $consigner->contact_phone) }}" placeholder="e.g. +91 2822 240890">
              @error('contact_phone')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-4">
              <label for="contact_email" class="form-label small fw-semibold">Official Email</label>
              <input type="email" class="form-control @error('contact_email') is-invalid @enderror" id="contact_email" name="contact_email" value="{{ old('contact_email', $consigner->contact_email) }}" placeholder="e.g. export@reliancegranito.com">
              @error('contact_email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>

      </div>

      <!-- Action Footer -->
      <div class="card-footer bg-light p-3 px-4 d-flex align-items-center justify-content-between border-top">
        <a href="{{ route('consigners.index') }}" class="btn btn-outline-secondary px-4">
          <i class="bi bi-x-lg me-1"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm d-flex align-items-center gap-2">
          <i class="bi bi-check-lg fs-5"></i>
          <span>Update Consigner in MySQL</span>
        </button>
      </div>
    </form>
  </div>

</main>
@endsection
