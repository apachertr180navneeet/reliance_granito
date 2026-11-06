@extends('layouts.app')

@section('content')
<main class="content-area p-3 p-lg-4" id="consignerCreateApp">

  <!-- Breadcrumb & Header -->
  <div class="consigner-page-header">
    <nav aria-label="breadcrumb" class="consigner-breadcrumb mb-2">
      <ol class="breadcrumb mb-0 small">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bi bi-house-door me-1"></i>Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('consigners.index') }}">Consignee Master</a></li>
        <li class="breadcrumb-item active" aria-current="page">Add New Consignee</li>
      </ol>
    </nav>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
      <div>
        <div class="d-flex align-items-center gap-2 mb-1">
          <h2 class="fw-bold text-dark mb-0" style="font-size: 1.6rem; letter-spacing: -0.02em;">Add New Consignee Entity <span class="d-none">Add New Consigner Entity</span></h2>
          <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold px-2.5 py-1" style="font-size: 0.76rem;">
            New Consignee Entity
          </span>
        </div>
        <p class="text-muted small mb-0">
          Register a new corporate export consignee, statutory credentials, manufacturing works, and authorized bank details.
        </p>
      </div>

      <div>
        <a href="{{ route('consigners.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3" style="font-size: 0.88rem; font-weight: 500;">
          <i class="bi bi-arrow-left"></i>
          <span>Back to Consignees List</span>
        </a>
      </div>
    </div>
  </div>

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

  <!-- Form with 2-Column Responsive SaaS Layout -->
  <form action="{{ route('consigners.store') }}" method="POST" id="consignerForm">
    @csrf

    <div class="row g-4">
      
      <!-- Left Column: Form Cards -->
      <div class="col-lg-8">
        
        <!-- Section 1: Firm Legal Particulars -->
        <div class="consigner-form-card">
          <div class="consigner-form-header">
            <div class="form-header-icon primary">
              <i class="bi bi-buildings"></i>
            </div>
            <div>
              <div class="form-header-title">Firm & Exporter Legal Particulars</div>
              <div class="form-header-desc">Registered firm identity matching DGFT IEC certificate and Customs Shipping Bills</div>
            </div>
          </div>
          <div class="p-3 p-lg-4">
            <div class="row g-3">
              <div class="col-md-8">
                <label for="name" class="form-label-custom">
                  <span>Company / Firm Legal Name <span class="req-star">*</span></span>
                  <span class="text-muted fw-normal small">Full Corporate Name</span>
                </label>
                <input type="text" class="form-control form-control-custom @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g. Reliance Granito Private Limited" oninput="updateLivePreview()">
                @error('name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-4">
                <label for="entity_type" class="form-label-custom">
                  <span>Entity Classification</span>
                </label>
                <select class="form-select form-select-custom @error('entity_type') is-invalid @enderror" id="entity_type" name="entity_type" onchange="updateLivePreview()">
                  <option value="Manufacturer Exporter" {{ old('entity_type') == 'Manufacturer Exporter' ? 'selected' : '' }}>Manufacturer Exporter</option>
                  <option value="Merchant Exporter" {{ old('entity_type') == 'Merchant Exporter' ? 'selected' : '' }}>Merchant Exporter</option>
                  <option value="Manufacturing Facility / Plant" {{ old('entity_type') == 'Manufacturing Facility / Plant' ? 'selected' : '' }}>Manufacturing Facility / Plant</option>
                  <option value="Sister Firm / Associate" {{ old('entity_type') == 'Sister Firm / Associate' ? 'selected' : '' }}>Sister Firm / Associate</option>
                </select>
                @error('entity_type')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>
        </div>

        <!-- Hidden preserve fields for Customs / Status -->
        <input type="hidden" name="iec" id="iec" value="{{ old('iec', 'ABEFR3842G') }}">
        <input type="hidden" name="iec_branch" id="iec_branch" value="{{ old('iec_branch', '0') }}">
        <input type="hidden" name="gstin" id="gstin" value="{{ old('gstin') }}">
        <input type="hidden" name="pan" id="pan" value="{{ old('pan') }}">
        <input type="hidden" name="ad_code" id="ad_code" value="{{ old('ad_code') }}">
        <input type="hidden" name="status" id="status" value="{{ old('status', 'Active') }}">

        <!-- Section 2: Manufacturing Works & Physical Premises -->
        <div class="consigner-form-card">
          <div class="consigner-form-header">
            <div class="form-header-icon amber">
              <i class="bi bi-geo-alt"></i>
            </div>
            <div>
              <div class="form-header-title">Manufacturing Works & Physical Premises</div>
              <div class="form-header-desc">Registered premises, manufacturing cluster, and official tax & contact particulars</div>
            </div>
          </div>
          <div class="p-3 p-lg-4">
            <div class="row g-3">
              <div class="col-12">
                <label for="registered_address" class="form-label-custom">
                  <span>Registered Corporate Office Address <span class="req-star">*</span></span>
                  <span class="text-muted fw-normal small">Legal Entity Head Office</span>
                </label>
                <textarea class="form-control form-control-custom @error('registered_address') is-invalid @enderror" id="registered_address" name="registered_address" rows="2" required placeholder="Plot / Survey No, Industrial Area, City, District, State, PIN Code" oninput="updateLivePreview()">{{ old('registered_address') }}</textarea>
                @error('registered_address')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-7">
                <label for="factory_address" class="form-label-custom">
                  <span>Factory / Manufacturing Works Address</span>
                  <span class="text-muted fw-normal small">Plant Site</span>
                </label>
                <textarea class="form-control form-control-custom @error('factory_address') is-invalid @enderror" id="factory_address" name="factory_address" rows="2" placeholder="Premises Highway / GIDC, Gujarat - 363642">{{ old('factory_address') }}</textarea>
                @error('factory_address')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-5">
                <label for="region" class="form-label-custom">
                  <span>Primary Manufacturing Region</span>
                </label>
                <select class="form-select form-select-custom @error('region') is-invalid @enderror" id="region" name="region" onchange="updateLivePreview()">
                  <option value="Gujarat" {{ old('region') == 'Gujarat' ? 'selected' : '' }}>Gujarat Ceramic Cluster</option>
                  <option value="Jodhpur" {{ old('region') == 'Jodhpur' ? 'selected' : '' }}>Jodhpur Granite Hub (Rajasthan)</option>
                </select>
                @error('region')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-4">
                <label for="tax_code" class="form-label-custom">
                  <span>Tax Code / GSTIN</span>
                  <span class="text-muted fw-normal small">Tax Identifier</span>
                </label>
                <input type="text" class="form-control form-control-custom text-uppercase font-monospace @error('tax_code') is-invalid @enderror" id="tax_code" name="tax_code" value="{{ old('tax_code') }}" placeholder="e.g. 08ABEFR3842G1Z5" oninput="updateLivePreview()">
                @error('tax_code')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-4">
                <label for="phone" class="form-label-custom">
                  <span>Telephone Number</span>
                  <span class="text-muted fw-normal small">Direct Phone</span>
                </label>
                <input type="text" class="form-control form-control-custom font-monospace @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="e.g. +91 9785065066" oninput="updateLivePreview()">
                @error('phone')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-4">
                <label for="email" class="form-label-custom">
                  <span>Email Address</span>
                  <span class="text-muted fw-normal small">Official Email</span>
                </label>
                <input type="email" class="form-control form-control-custom @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="e.g. contact@domain.com" oninput="updateLivePreview()">
                @error('email')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>
        </div>

        <!-- Section 3: Authorized Dealer Forex Bank Details -->
        <div class="consigner-form-card">
          <div class="consigner-form-header">
            <div class="form-header-icon emerald">
              <i class="bi bi-bank"></i>
            </div>
            <div>
              <div class="form-header-title">Authorized Dealer Forex Bank Details</div>
              <div class="form-header-desc">Customs AD Bank where foreign inward export remittances and BRC are processed</div>
            </div>
          </div>
          <div class="p-3 p-lg-4">
            <div class="row g-3">
              <div class="col-md-6">
                <label for="bank_name" class="form-label-custom">
                  <span>Bank Name</span>
                </label>
                <input type="text" class="form-control form-control-custom @error('bank_name') is-invalid @enderror" id="bank_name" name="bank_name" value="{{ old('bank_name', 'ICICI BANK LIMITED') }}" placeholder="ICICI BANK LIMITED" oninput="updateLivePreview()">
                @error('bank_name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label for="bank_branch" class="form-label-custom">
                  <span>Branch Name</span>
                </label>
                <input type="text" class="form-control form-control-custom @error('bank_branch') is-invalid @enderror" id="bank_branch" name="bank_branch" value="{{ old('bank_branch', 'JODHPUR BRANCH') }}" placeholder="JODHPUR BRANCH" oninput="updateLivePreview()">
                @error('bank_branch')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label for="bank_account_no" class="form-label-custom">
                  <span>Account Number</span>
                  <span class="text-primary fw-semibold small">Must be 777705052052</span>
                </label>
                <input type="text" class="form-control form-control-custom font-monospace fw-bold @error('bank_account_no') is-invalid @enderror" id="bank_account_no" name="bank_account_no" value="{{ old('bank_account_no', '777705052052') }}" placeholder="777705052052" oninput="updateLivePreview()">
                @error('bank_account_no')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label for="bank_swift" class="form-label-custom">
                  <span>IFSC / SWIFT Code</span>
                </label>
                <input type="text" class="form-control form-control-custom text-uppercase font-monospace @error('bank_swift') is-invalid @enderror" id="bank_swift" name="bank_swift" value="{{ old('bank_swift', 'ICIC0006827') }}" placeholder="ICIC0006827" oninput="updateLivePreview()">
                @error('bank_swift')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-12">
                <label for="bank_address" class="form-label-custom">
                  <span>Bank Address</span>
                </label>
                <textarea class="form-control form-control-custom @error('bank_address') is-invalid @enderror" id="bank_address" name="bank_address" rows="2" placeholder="Jodhpur Branch, Rajasthan, India" oninput="updateLivePreview()">{{ old('bank_address', 'Jodhpur Branch, Rajasthan, India') }}</textarea>
                @error('bank_address')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>
        </div>

        <!-- Section 5: Authorized Key Contact -->
        <div class="consigner-form-card">
          <div class="consigner-form-header">
            <div class="form-header-icon primary">
              <i class="bi bi-person-badge"></i>
            </div>
            <div>
              <div class="form-header-title">Authorized Liaison & Key Contact</div>
              <div class="form-header-desc">Key official responsible for export documentation signing and compliance</div>
            </div>
          </div>
          <div class="p-3 p-lg-4">
            <div class="row g-3">
              <div class="col-md-4">
                <label for="contact_person" class="form-label-custom">
                  <span>Key Contact Person</span>
                </label>
                <input type="text" class="form-control form-control-custom @error('contact_person') is-invalid @enderror" id="contact_person" name="contact_person" value="{{ old('contact_person') }}" placeholder="e.g. Rajesh B. Patel">
                @error('contact_person')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-4">
                <label for="contact_phone" class="form-label-custom">
                  <span>Direct Mobile / Phone</span>
                </label>
                <input type="text" class="form-control form-control-custom @error('contact_phone') is-invalid @enderror" id="contact_phone" name="contact_phone" value="{{ old('contact_phone') }}" placeholder="e.g. +91 98252 14690">
                @error('contact_phone')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-4">
                <label for="contact_email" class="form-label-custom">
                  <span>Official Email Address</span>
                </label>
                <input type="email" class="form-control form-control-custom @error('contact_email') is-invalid @enderror" id="contact_email" name="contact_email" value="{{ old('contact_email') }}" placeholder="e.g. export@reliancegranito.com">
                @error('contact_email')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Right Column: Live Card Preview & Sticky Action Box -->
      <div class="col-lg-4">
        <div class="form-sticky-panel">

          <!-- Live Passport Preview Card -->
          <div class="consigner-live-preview-box">
            <div class="consigner-live-preview-header">
              <div class="small fw-bold text-uppercase text-muted" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                <i class="bi bi-eye text-primary me-1"></i> Live Profile Card Preview
              </div>
              <span class="badge bg-primary-subtle text-primary" style="font-size: 0.7rem;">Auto Updates</span>
            </div>
            <div class="p-3 p-xl-4">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2.5">
                  <div class="company-avatar-box morbi" id="previewAvatar" style="width: 40px; height: 40px; font-size: 0.9rem;">
                    RG
                  </div>
                  <div>
                    <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.68rem;">EXP-NEW</span>
                    <div class="small text-muted" id="previewRegionTag" style="font-size: 0.72rem;">Gujarat Works</div>
                  </div>
                </div>
                <div id="previewStatusBadge">
                  <span class="status-pill-active"><span class="status-live-dot"></span>Active</span>
                </div>
              </div>

              <h5 class="fw-bold text-dark mb-1 fs-6 d-flex align-items-center gap-1">
                <span id="previewName">Company Name</span>
                <i class="bi bi-patch-check-fill text-primary" style="font-size: 0.8rem;"></i>
              </h5>
              <span class="badge bg-primary-subtle text-primary border border-primary-subtle align-self-start mb-3" id="previewEntityType" style="font-size: 0.7rem;">
                Manufacturer Exporter
              </span>

              <div class="p-2.5 bg-light rounded-3 small mb-3 border">
                <div class="d-flex justify-content-between mb-1 pb-1 border-bottom">
                  <span class="text-muted fw-semibold" style="font-size: 0.76rem;">IEC Code:</span>
                  <span class="font-monospace fw-bold text-dark" id="previewIec" style="font-size: 0.78rem;">ABEFR3842G</span>
                </div>
                <div class="d-flex justify-content-between mb-1 pb-1 border-bottom">
                  <span class="text-muted fw-semibold" style="font-size: 0.76rem;">GSTIN:</span>
                  <span class="font-monospace text-primary fw-bold" id="previewGstin" style="font-size: 0.78rem;">08ABEFR3842G1Z5</span>
                </div>
                <div class="d-flex justify-content-between">
                  <span class="text-muted fw-semibold" style="font-size: 0.76rem;">AD Code:</span>
                  <span class="font-monospace fw-bold text-dark" id="previewAdCode" style="font-size: 0.78rem;">N/A</span>
                </div>
              </div>

              <div class="text-muted small" style="font-size: 0.76rem;">
                <div class="mb-1 text-truncate" id="previewAddress">
                  <i class="bi bi-geo-alt text-primary me-1"></i>Office address preview
                </div>
                <div class="text-truncate" id="previewBank">
                  <i class="bi bi-bank text-secondary me-1"></i>Bank details preview
                </div>
              </div>
            </div>
          </div>

          <!-- Sticky Submission Action Card -->
          <div class="consigner-form-card mb-3">
            <div class="p-3">
              <button type="submit" class="btn btn-primary w-100 fw-semibold py-2.5 mb-2 d-flex align-items-center justify-content-center gap-2 shadow-sm rounded-3">
                <i class="bi bi-check-lg fs-5"></i>
                <span>Save Consignee to MySQL</span>
                <span class="d-none">Save Consigner to MySQL</span>
              </button>
              <a href="{{ route('consigners.index') }}" class="btn btn-outline-secondary w-100 py-2 rounded-3" style="font-size: 0.88rem;">
                <i class="bi bi-x-lg me-1"></i> Cancel & Discard
              </a>
            </div>
          </div>

          <!-- Customs EDI Compliance Guidelines -->
          <div class="compliance-tip-card small text-muted">
            <div class="fw-bold text-dark mb-1.5 d-flex align-items-center gap-1.5">
              <i class="bi bi-shield-check text-primary"></i>
              <span>ICEGATE Regulatory Tip</span>
            </div>
            <p class="mb-0" style="font-size: 0.76rem; line-height: 1.5;">
              Ensure the IEC Code strictly matches DGFT records and the 7-digit AD Code is mapped to the Authorized Dealer Forex branch registered in the Customs EDI system.
            </p>
          </div>

        </div>
      </div>

    </div>
  </form>

</main>
@endsection

@push('scripts')
<script>
  function getInitials(name) {
    if (!name) return 'RG';
    const parts = name.trim().split(/\s+/);
    if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }

  function updateLivePreview() {
    const name = document.getElementById('name').value.trim() || 'Company / Firm Name';
    const entityType = document.getElementById('entity_type').value;
    const iec = document.getElementById('iec').value.trim().toUpperCase() || 'IEC-PENDING';
    const gstin = (document.getElementById('tax_code')?.value.trim() || document.getElementById('gstin')?.value.trim() || 'GSTIN-PENDING').toUpperCase();
    const adCode = document.getElementById('ad_code')?.value.trim() || 'N/A';
    const status = document.getElementById('status')?.value || 'Active';
    const region = document.getElementById('region')?.value;
    const address = document.getElementById('registered_address')?.value.trim() || 'Registered office location';
    const bankName = document.getElementById('bank_name')?.value.trim() || 'ICICI BANK';
    const bankAcc = document.getElementById('bank_account_no')?.value.trim() || '777705052052';
    const bank = `${bankName} (A/C: ${bankAcc})`;

    document.getElementById('previewName').textContent = name;
    document.getElementById('previewEntityType').textContent = entityType;
    document.getElementById('previewIec').textContent = iec;
    document.getElementById('previewGstin').textContent = gstin;
    document.getElementById('previewAdCode').textContent = adCode;
    document.getElementById('previewAddress').innerHTML = `<i class="bi bi-geo-alt text-primary me-1"></i>${address}`;
    document.getElementById('previewBank').innerHTML = `<i class="bi bi-bank text-secondary me-1"></i>${bank}`;

    // Avatar & region styling
    const avatar = document.getElementById('previewAvatar');
    avatar.textContent = getInitials(name);
    if (region === 'Gujarat' || region === 'Morbi') {
      avatar.className = 'company-avatar-box morbi';
      document.getElementById('previewRegionTag').textContent = 'Gujarat Works';
    } else {
      avatar.className = 'company-avatar-box jodhpur';
      document.getElementById('previewRegionTag').textContent = 'Jodhpur Works';
    }

    // Status Badge
    const statusBadge = document.getElementById('previewStatusBadge');
    if (status === 'Active') {
      statusBadge.innerHTML = `<span class="status-pill-active"><span class="status-live-dot"></span>Active</span>`;
    } else {
      statusBadge.innerHTML = `<span class="badge bg-secondary-subtle text-secondary border px-2 py-1">Inactive</span>`;
    }
  }

  document.addEventListener('DOMContentLoaded', updateLivePreview);
</script>
@endpush
