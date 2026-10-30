@extends('layouts.app')

@push('styles')
<style>
/* ==========================================================================
   CONSIGNERS MASTER - PREMIUM MODERN SAAS TABLE & TOOLBAR STYLES
   ========================================================================== */
.consigners-card {
  border-radius: 12px !important;
  border: 1px solid #E2E8F0 !important;
  background: #FFFFFF;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.02) !important;
  overflow: hidden;
}

/* Toolbar */
.consigners-toolbar-wrapper {
  padding: 14px 20px;
  background: #FFFFFF;
  border-bottom: 1px solid #F1F5F9;
}

.search-input-box {
  position: relative;
  width: 100%;
}

.search-input-box .search-icon {
  position: absolute;
  left: 13px;
  top: 50%;
  transform: translateY(-50%);
  color: #94A3B8;
  font-size: 0.88rem;
  pointer-events: none;
}

.search-input-box .search-input {
  width: 100%;
  height: 38px;
  padding-left: 36px;
  padding-right: 32px;
  border-radius: 8px;
  border: 1px solid #E2E8F0;
  background-color: #F8FAFC;
  font-size: 0.83rem;
  color: #0F172A;
  transition: all 0.15s ease;
}

.search-input-box .search-input:focus {
  background-color: #FFFFFF;
  border-color: #3B82F6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
  outline: none;
}

.search-clear-btn {
  position: absolute;
  right: 11px;
  top: 50%;
  transform: translateY(-50%);
  color: #94A3B8;
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
  display: flex;
  align-items: center;
  transition: color 0.15s ease;
}

.search-clear-btn:hover {
  color: #475569;
}

.toolbar-select {
  height: 38px;
  border-radius: 8px;
  border: 1px solid #E2E8F0;
  background-color: #F8FAFC;
  font-size: 0.82rem;
  color: #1E293B;
  font-weight: 500;
  transition: all 0.15s ease;
}

.toolbar-select:focus {
  background-color: #FFFFFF;
  border-color: #3B82F6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
  outline: none;
}

.records-badge-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background-color: #F8FAFC;
  border: 1px solid #E2E8F0;
  padding: 6px 12px;
  border-radius: 7px;
  font-size: 0.77rem;
  color: #64748B;
  font-weight: 500;
  white-space: nowrap;
}

.records-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background-color: #3B82F6;
}

/* Modern Enterprise Table */
#consignersTable {
  margin-bottom: 0 !important;
  border-collapse: separate;
  border-spacing: 0;
  width: 100%;
}

#consignersTable thead th {
  background-color: #F8FAFC !important;
  color: #64748B !important;
  font-size: 0.71rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  border-bottom: 1px solid #E2E8F0 !important;
  border-top: none !important;
  padding: 12px 18px;
  white-space: nowrap;
}

#consignersTable tbody td {
  padding: 13px 18px;
  border-bottom: 1px solid #F1F5F9 !important;
  border-top: none !important;
  background-color: #FFFFFF !important;
  vertical-align: middle;
}

#consignersTable tbody tr {
  transition: background-color 0.12s ease-in-out;
}

#consignersTable tbody tr:hover td {
  background-color: #F8FAFC !important;
}

/* Avatar Box */
.company-avatar-box {
  width: 36px;
  height: 36px;
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.8rem;
  letter-spacing: -0.01em;
  flex-shrink: 0;
}

.company-avatar-box.morbi {
  background: #FEF3C7;
  color: #92400E;
  border: 1px solid #FDE68A;
}

.company-avatar-box.jodhpur {
  background: #EFF6FF;
  color: #1D4ED8;
  border: 1px solid #BFDBFE;
}

/* Title & Subline */
.consigner-company-title {
  font-size: 0.88rem;
  font-weight: 600;
  color: #0F172A;
  text-decoration: none;
  transition: color 0.15s ease;
}

.consigner-company-title:hover {
  color: #2563EB;
}

.consigner-code-pill {
  font-family: 'SFMono-Regular', Menlo, Monaco, Consolas, monospace;
  font-size: 0.67rem;
  font-weight: 600;
  background-color: #F1F5F9;
  color: #475569;
  padding: 1px 6px;
  border-radius: 4px;
  border: 1px solid #E2E8F0;
}

.consigner-entity-type {
  font-size: 0.74rem;
  color: #64748B;
}

/* Compliance Badges */
.compliance-row {
  display: flex;
  align-items: center;
  gap: 6px;
}

.badge-compliance-label {
  font-size: 0.64rem;
  font-weight: 700;
  font-family: 'SFMono-Regular', Menlo, Monaco, Consolas, monospace;
  color: #64748B;
  background-color: #F1F5F9;
  border: 1px solid #E2E8F0;
  padding: 1px 5px;
  border-radius: 4px;
  line-height: 1.2;
}

.compliance-value {
  font-size: 0.78rem;
  color: #1E293B;
  letter-spacing: 0.02em;
}

.btn-copy-code {
  background: none;
  border: none;
  padding: 0;
  margin-left: 2px;
  color: #94A3B8;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  font-size: 0.72rem;
  transition: color 0.15s ease;
}

.btn-copy-code:hover {
  color: #2563EB;
}

/* Hub Badges */
.hub-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 9px;
  border-radius: 999px;
  font-size: 0.7rem;
  font-weight: 600;
  white-space: nowrap;
}

.hub-badge.hub-morbi {
  background-color: #FFFBEB;
  color: #92400E;
  border: 1px solid #FDE68A;
}

.hub-badge.hub-jodhpur {
  background-color: #F0F9FF;
  color: #0369A1;
  border: 1px solid #BAE6FD;
}

.consigner-address-line {
  color: #64748B;
  font-size: 0.74rem;
  max-width: 250px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  margin-top: 3px;
}

.consigner-bank-line {
  color: #64748B;
  font-size: 0.74rem;
  max-width: 190px;
}

/* Status Pill (Interactive AJAX) */
.btn-status-toggle {
  background: transparent;
  border: none;
  padding: 0;
  cursor: pointer;
  outline: none;
  transition: transform 0.15s ease;
}

.btn-status-toggle:hover {
  transform: scale(1.03);
}

.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 600;
  letter-spacing: 0.01em;
  transition: all 0.2s ease;
}

.status-pill-active {
  background-color: #ECFDF5;
  color: #065F46;
  border: 1px solid #A7F3D0;
}

.status-pill-inactive {
  background-color: #F8FAFC;
  color: #64748B;
  border: 1px solid #E2E8F0;
}

.status-indicator-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  display: inline-block;
}

.status-indicator-dot.active {
  background-color: #10B981;
  box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

.status-indicator-dot.inactive {
  background-color: #94A3B8;
}

/* Modern Action Buttons */
.table-actions-group {
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.btn-table-action {
  height: 31px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  padding: 0 10px;
  border-radius: 7px;
  font-size: 0.78rem;
  font-weight: 500;
  border: 1px solid #E2E8F0;
  background-color: #FFFFFF;
  color: #334155;
  text-decoration: none;
  transition: all 0.15s ease;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
}

.btn-table-action:hover {
  background-color: #F8FAFC;
  border-color: #CBD5E1;
  color: #0F172A;
}

.btn-action-primary {
  color: #1D4ED8;
  border-color: #DBEAFE;
  background-color: #EFF6FF;
}

.btn-action-primary:hover {
  background-color: #DBEAFE;
  border-color: #BFDBFE;
  color: #1E40AF;
}

.btn-table-action.btn-action-icon {
  width: 31px;
  padding: 0;
  color: #64748B;
}

.btn-table-action.btn-action-icon:hover {
  color: #0F172A;
}

.dropdown-menu-modern {
  border-radius: 10px !important;
  border: 1px solid #E2E8F0 !important;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04) !important;
  padding: 6px !important;
  font-size: 0.82rem !important;
}

.dropdown-menu-modern .dropdown-item {
  border-radius: 6px;
  padding: 6px 10px;
  color: #334155;
  font-weight: 500;
  transition: all 0.12s ease;
}

.dropdown-menu-modern .dropdown-item:hover {
  background-color: #F1F5F9;
  color: #0F172A;
}

.dropdown-menu-modern .dropdown-item.text-danger:hover {
  background-color: #FEF2F2;
  color: #DC2626 !important;
}

.empty-icon-box {
  width: 52px;
  height: 52px;
  border-radius: 12px;
  background-color: #F8FAFC;
  border: 1px solid #E2E8F0;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
@endpush

@section('content')
<main class="content-area p-3 p-lg-4" id="consignersApp">
  
  <!-- Breadcrumb & Sleek Header Bar -->
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
    <div>
      <nav aria-label="breadcrumb" class="mb-1">
        <ol class="breadcrumb mb-0 small">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted"><i class="bi bi-house-door me-1"></i>Dashboard</a></li>
          <li class="breadcrumb-item active fw-semibold text-dark" aria-current="page">Consigners Master</li>
        </ol>
      </nav>
      <div class="d-flex align-items-center gap-2">
        <h3 class="fw-bold text-dark mb-0 fs-5" style="letter-spacing: -0.01em;">Consigners Master</h3>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold px-2 py-0.5 rounded-pill" id="totalRegisteredBadge" style="font-size: 0.72rem;">
          {{ $consigners->total() }} Registered
        </span>
      </div>
    </div>

    <!-- Header Actions -->
    <div class="d-flex align-items-center gap-2">
      <a href="{{ route('consigners.create') }}" class="btn btn-primary btn-sm fw-semibold px-3 py-1.5 d-inline-flex align-items-center gap-1.5 shadow-sm rounded-2">
        <i class="bi bi-plus-lg"></i>
        <span>Add Consigner</span>
      </a>

      <!-- Export Dropdown -->
      <div class="dropdown">
        <button class="btn btn-sm border dropdown-toggle d-inline-flex align-items-center gap-1.5 px-2.5 py-1.5 rounded-2 shadow-sm text-dark fw-medium" type="button" data-bs-toggle="dropdown" style="background: #FFFFFF; font-size: 0.82rem; border-color: #E2E8F0 !important;">
          <i class="bi bi-download text-muted"></i>
          <span>Export</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm border dropdown-menu-modern" style="min-width: 160px;">
          <li>
            <a class="dropdown-item py-1.5 d-flex align-items-center gap-2" href="{{ route('consigners.export', ['format' => 'csv']) }}">
              <i class="bi bi-file-earmark-spreadsheet text-success"></i>
              <span>Export CSV</span>
            </a>
          </li>
          <li>
            <a class="dropdown-item py-1.5 d-flex align-items-center gap-2" href="{{ route('consigners.export', ['format' => 'json']) }}">
              <i class="bi bi-filetype-json text-primary"></i>
              <span>Export JSON</span>
            </a>
          </li>
          <li><hr class="dropdown-divider my-1"></li>
          <li>
            <a class="dropdown-item py-1.5 text-danger d-flex align-items-center gap-2" href="javascript:void(0)" onclick="resetDemoData()">
              <i class="bi bi-arrow-counterclockwise"></i>
              <span>Restore Default Data</span>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Main Unified Consigners Data Card -->
  <div class="card consigners-card mb-4">
    
    <!-- Integrated Search & Filter Toolbar -->
    <div class="consigners-toolbar-wrapper">
      <form action="{{ route('consigners.index') }}" method="GET" id="consignersFilterForm" class="row g-2 align-items-center mb-0 w-100">
        <input type="hidden" name="per_page" id="perPageInput" value="{{ $filters['per_page'] ?? 10 }}">

        <!-- Search Input with Integrated Icon -->
        <div class="col-12 col-md-5 col-lg-5">
          <div class="search-input-box">
            <i class="bi bi-search search-icon"></i>
            <input type="text" class="search-input" name="search" id="searchConsignerInput" value="{{ request('search') }}" placeholder="Search company, IEC, GSTIN, city, AD code...">
            @if(request('search'))
              <a href="{{ route('consigners.index', array_merge(request()->except('search', 'page'), ['page' => 1])) }}" class="search-clear-btn" title="Clear Search">
                <i class="bi bi-x-circle-fill"></i>
              </a>
            @endif
          </div>
        </div>

        <!-- Manufacturing Hubs Filter -->
        <div class="col-6 col-md-3 col-lg-3">
          <select class="form-select toolbar-select" name="region" id="filterRegionSelect" onchange="this.form.submit()">
            <option value="ALL" {{ request('region') === 'ALL' || !request('region') ? 'selected' : '' }}>All Manufacturing Hubs</option>
            <option value="Gujarat" {{ in_array(request('region'), ['Gujarat', 'Morbi']) ? 'selected' : '' }}>Gujarat Facility</option>
            <option value="Jodhpur" {{ request('region') === 'Jodhpur' ? 'selected' : '' }}>Jodhpur Facility (Rajasthan)</option>
          </select>
        </div>

        <!-- Status Filter -->
        <div class="col-6 col-md-2 col-lg-2">
          <select class="form-select toolbar-select" name="status" id="filterStatusSelect" onchange="this.form.submit()">
            <option value="ALL" {{ request('status') === 'ALL' || !request('status') ? 'selected' : '' }}>All Statuses</option>
            <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active Only</option>
            <option value="Inactive" {{ request('status') === 'Inactive' ? 'selected' : '' }}>Inactive Only</option>
          </select>
        </div>

        <!-- Reset Button or Records Badge -->
        <div class="col-12 col-md-2 col-lg-2 text-md-end">
          @if(request('search') || (request('region') && request('region') !== 'ALL') || (request('status') && request('status') !== 'ALL'))
            <a href="{{ route('consigners.index') }}" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center justify-content-center gap-1.5 w-100" title="Reset all search filters" style="height: 38px; font-size: 0.82rem; font-weight: 500; border-radius: 8px;">
              <i class="bi bi-arrow-counterclockwise"></i>
              <span>Reset</span>
            </a>
          @else
            <div class="d-none d-md-flex align-items-center justify-content-end text-muted pe-1" style="height: 38px;">
              <span class="records-badge-pill">
                <span class="records-dot"></span>
                <span><strong class="text-dark">{{ $consigners->total() }}</strong> records</span>
              </span>
            </div>
          @endif
        </div>
      </form>
    </div>

    <!-- Clean Enterprise Data Table -->
    <div class="table-responsive">
      <table class="table align-middle mb-0" id="consignersTable">
        <thead>
          <tr>
            <th class="ps-4" style="width: 30%;">Exporter / Legal Entity</th>
            <th style="width: 18%;">Compliance (IEC & GSTIN)</th>
            <th style="width: 24%;">Manufacturing Hub & Works</th>
            <th style="width: 14%;">AD Code & Bank</th>
            <th class="text-center" style="width: 6%;">Status</th>
            <th class="pe-4 text-end" style="width: 8%;">Actions</th>
          </tr>
        </thead>
        <tbody id="consignersTableBody">
          @forelse($consigners as $c)
            @php
              $isGujarat = (in_array($c->region, ['Gujarat', 'Morbi']) || (str_contains(strtolower($c->factory_address ?? ''), 'morbi') || str_contains(strtolower($c->factory_address ?? ''), 'gujarat')));
              $words = preg_split('/\s+/', trim($c->name));
              $initials = count($words) === 1 ? strtoupper(substr($words[0], 0, 2)) : strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
            @endphp
            <tr id="consigner-row-{{ $c->id }}" class="consigner-data-row">
              <!-- Exporter Name & Details -->
              <td class="ps-4">
                <div class="d-flex align-items-center gap-3">
                  <div class="company-avatar-box {{ $isGujarat ? 'morbi' : 'jodhpur' }}">
                    {{ $initials }}
                  </div>
                  <div class="overflow-hidden">
                    <div class="d-flex align-items-center gap-1">
                      <a href="javascript:void(0)" onclick="viewConsignerDetails({{ $c->id }})" class="consigner-company-title text-truncate" title="{{ $c->name }}">{{ $c->name }}</a>
                      <i class="bi bi-patch-check-fill text-primary" style="font-size: 0.78rem;" title="ICEGATE Certified Exporter"></i>
                    </div>
                    <div class="d-flex align-items-center gap-1.5 mt-0.5">
                      <span class="consigner-code-pill">{{ $c->consigner_code ?? ('EXP-' . str_pad($c->id, 3, '0', STR_PAD_LEFT)) }}</span>
                      <span class="text-muted opacity-50 small mx-0.5">&bull;</span>
                      <span class="consigner-entity-type text-truncate">{{ $c->entity_type ?? 'Manufacturer Exporter' }}</span>
                    </div>
                  </div>
                </div>
              </td>

              <!-- Compliance (IEC & GSTIN) -->
              <td>
                <div class="d-flex flex-column gap-1">
                  <div class="compliance-row">
                    <span class="badge-compliance-label">IEC</span>
                    <span class="compliance-value font-monospace fw-semibold">{{ $c->iec }}</span>
                    <button type="button" class="btn-copy-code" onclick="copyToClipboard('{{ $c->iec }}', this)" title="Copy IEC Code">
                      <i class="bi bi-copy"></i>
                    </button>
                  </div>
                  <div class="compliance-row">
                    <span class="badge-compliance-label">GST</span>
                    <span class="compliance-value font-monospace" style="font-size: 0.76rem;">{{ $c->gstin }}</span>
                  </div>
                </div>
              </td>

              <!-- Works & Location -->
              <td>
                <div class="mb-1">
                  @if($isGujarat)
                    <span class="hub-badge hub-morbi"><i class="bi bi-geo-alt-fill"></i>Gujarat Hub (GJ)</span>
                  @else
                    <span class="hub-badge hub-jodhpur"><i class="bi bi-building"></i>Jodhpur Hub (RJ)</span>
                  @endif
                </div>
                <div class="consigner-address-line" title="{{ $c->registered_address }}">
                  <i class="bi bi-geo-alt me-1 text-muted"></i>{{ $c->registered_address }}
                </div>
              </td>

              <!-- Bank & AD Code -->
              <td>
                <div class="d-flex align-items-center gap-1.5 mb-0.5">
                  <span class="text-muted" style="font-size: 0.72rem; font-weight: 500;">AD Code:</span>
                  <span class="font-monospace fw-semibold text-dark" style="font-size: 0.82rem;">{{ $c->ad_code ?? 'Pending' }}</span>
                </div>
                <div class="consigner-bank-line text-truncate d-flex align-items-center gap-1" title="{{ $c->forex_bank ?? 'Authorized Dealer' }}">
                  <i class="bi bi-bank text-muted" style="font-size: 0.72rem;"></i>
                  <span>{{ $c->forex_bank ?? 'Authorized Dealer' }}</span>
                </div>
              </td>

              <!-- Status (Interactive AJAX Toggle) -->
              <td class="text-center" id="status-cell-{{ $c->id }}">
                <button type="button" 
                        class="btn-status-toggle" 
                        id="status-btn-{{ $c->id }}" 
                        onclick="toggleConsignerStatus({{ $c->id }}, '{{ $c->status }}')" 
                        title="Click to toggle status (Active / Inactive)">
                  @if($c->status === 'Active')
                    <span class="status-pill status-pill-active" id="status-badge-{{ $c->id }}">
                      <span class="status-indicator-dot active"></span> Active
                    </span>
                  @else
                    <span class="status-pill status-pill-inactive" id="status-badge-{{ $c->id }}">
                      <span class="status-indicator-dot inactive"></span> Inactive
                    </span>
                  @endif
                </button>
              </td>

              <!-- Action Buttons -->
              <td class="pe-4 text-end">
                <div class="table-actions-group justify-content-end">
                  <a href="{{ route('consigners.edit', $c->id) }}" class="btn-table-action btn-action-primary" title="Edit Consigner">
                    <i class="bi bi-pencil"></i>
                    <span>Edit</span>
                  </a>
                  <button type="button" class="btn-table-action btn-action-icon" onclick="stampInPdfEditor({{ $c->id }})" title="Stamp on PDF">
                    <i class="bi bi-file-earmark-diff text-success"></i>
                  </button>
                  <div class="dropdown d-inline-block">
                    <button type="button" class="btn-table-action btn-action-icon" data-bs-toggle="dropdown" aria-expanded="false" title="More Actions">
                      <i class="bi bi-three-dots"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 dropdown-menu-modern" style="min-width: 175px;">
                      <li>
                        <a class="dropdown-item py-1.5 d-flex align-items-center gap-2" href="javascript:void(0)" onclick="viewConsignerDetails({{ $c->id }})">
                          <i class="bi bi-eye text-primary"></i>
                          <span>View Profile</span>
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item py-1.5 d-flex align-items-center gap-2" href="javascript:void(0)" id="status-dropdown-btn-{{ $c->id }}" onclick="toggleConsignerStatus({{ $c->id }}, '{{ $c->status }}')">
                          <i class="bi bi-arrow-repeat text-warning"></i>
                          <span>Toggle Status</span>
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item py-1.5 d-flex align-items-center gap-2" href="{{ route('consigners.edit', $c->id) }}">
                          <i class="bi bi-pencil-square text-dark"></i>
                          <span>Edit Particulars</span>
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item py-1.5 d-flex align-items-center gap-2" href="javascript:void(0)" onclick="stampInPdfEditor({{ $c->id }})">
                          <i class="bi bi-file-earmark-diff text-success"></i>
                          <span>Stamp on PDF</span>
                        </a>
                      </li>
                      <li><hr class="dropdown-divider my-1"></li>
                      <li>
                        <a class="dropdown-item py-1.5 text-danger d-flex align-items-center gap-2" href="javascript:void(0)" onclick="openDeleteConsignerModal({{ $c->id }})">
                          <i class="bi bi-trash3 text-danger"></i>
                          <span>Delete Consigner</span>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
              </td>
            </tr>
          @empty
            <tr id="emptyConsignersRow">
              <td colspan="6" class="text-center py-5">
                <div class="empty-icon-box mx-auto mb-3">
                  <i class="bi bi-building-slash fs-3 text-muted"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">No Consigners Found</h6>
                <p class="text-muted small mb-3">No master records match your search or filter criteria.</p>
                <div class="d-flex justify-content-center gap-2">
                  <a href="{{ route('consigners.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-1.5" style="border-radius: 7px;">
                    Reset Filters
                  </a>
                  <a href="{{ route('consigners.create') }}" class="btn btn-primary btn-sm px-3 py-1.5" style="border-radius: 7px;">
                    Add Consigner
                  </a>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Card Footer with Clean Server-Side Pagination -->
    @if($consigners->total() > 0)
    <div class="card-footer bg-white border-top py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-2" id="consignersTableFooter" style="border-top: 1px solid #F1F5F9 !important;">
      <div class="text-muted small" style="font-size: 0.82rem;">
        Showing <span class="fw-semibold text-dark" id="paginationFirstItem">{{ $consigners->firstItem() ?? 0 }}</span> &ndash; <span class="fw-semibold text-dark" id="paginationLastItem">{{ $consigners->lastItem() ?? 0 }}</span> of <span class="fw-semibold text-dark" id="paginationTotalCount">{{ $consigners->total() }}</span> consigners
      </div>
      
      <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center gap-1.5">
          <label for="perPageSelector" class="text-muted small mb-0" style="font-size: 0.78rem;">Rows:</label>
          <select id="perPageSelector" class="form-select form-select-sm py-1 px-2" style="width: 70px; font-size: 0.78rem; border-radius: 7px; border-color: #E2E8F0;" onchange="changePerPage(this.value)">
            <option value="5" {{ $consigners->perPage() == 5 ? 'selected' : '' }}>5</option>
            <option value="10" {{ $consigners->perPage() == 10 ? 'selected' : '' }}>10</option>
            <option value="25" {{ $consigners->perPage() == 25 ? 'selected' : '' }}>25</option>
            <option value="50" {{ $consigners->perPage() == 50 ? 'selected' : '' }}>50</option>
          </select>
        </div>

        <div>
          {{ $consigners->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
      </div>
    </div>
    @endif

  </div>

</main>

<!-- =========================================================================
     MODAL: VIEW CONSIGNER FULL DETAILS
     ========================================================================= -->
<div class="modal fade" id="consignerViewModal" tabindex="-1" aria-labelledby="consignerViewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
      
      <div class="modal-header py-2.5 px-3 text-white" style="background: linear-gradient(135deg, #0F172A 0%, #1E3A8A 100%);">
        <div class="d-flex align-items-center gap-2">
          <div class="rounded bg-white bg-opacity-20 p-1.5 text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
            <i class="bi bi-building-check fs-6"></i>
          </div>
          <div>
            <h6 class="modal-title fw-bold fs-6 mb-0 text-white" id="viewModalCompanyName">Consigner Details</h6>
            <div class="text-white-50" id="viewModalSubTitle" style="font-size: 0.7rem;">ICEGATE & Customs Master Record</div>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-3" id="viewModalContent">
        <!-- Rendered dynamically -->
      </div>

      <div class="modal-footer py-2 px-3 bg-light border-top d-flex justify-content-between">
        <div>
          <button type="button" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1.5" id="viewModalUseInPdfBtn" onclick="openConsignerInPdfEditor()">
            <i class="bi bi-file-earmark-diff"></i>
            <span>Stamp in PDF Editor</span>
          </button>
        </div>
        <div class="d-flex align-items-center gap-2">
          <a href="javascript:void(0)" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5" id="viewModalEditBtn">
            <i class="bi bi-pencil-square"></i>
            <span>Edit</span>
          </a>
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- =========================================================================
     MODAL: DELETE CONFIRMATION MODAL
     ========================================================================= -->
<div class="modal fade" id="consignerDeleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
      <div class="modal-body p-3 text-center">
        <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger rounded-circle mb-2" style="width:48px; height:48px;">
          <i class="bi bi-exclamation-triangle fs-4"></i>
        </div>
        <h6 class="fw-bold mb-1 text-dark fs-6">Delete Consigner?</h6>
        <p class="text-muted small mb-3">Permanently delete <strong id="deleteConsignerName">this firm</strong>?</p>
        <div class="d-flex justify-content-center gap-2">
          <button type="button" class="btn btn-light border btn-sm px-2.5" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger btn-sm px-2.5 fw-semibold shadow-sm" id="confirmDeleteBtn" onclick="executeDeleteConsigner()">
            <i class="bi bi-trash3 me-1"></i> Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
    // Server-side loaded current page records
    let consignersCurrentPage = @json($consigners->items());
    let activeViewingConsigner = null;
    let pendingDeleteId = null;

    // Helper: Copy Text to Clipboard with Visual Toast
    function copyToClipboard(text, element) {
        if (!navigator.clipboard) return;
        navigator.clipboard.writeText(text).then(() => {
            if (window.showToast) {
                window.showToast(`Copied ${text}`, 'success');
            }
        });
    }

    // Server-side Rows Per Page Changer
    function changePerPage(value) {
        document.getElementById('perPageInput').value = value;
        document.getElementById('consignersFilterForm').submit();
    }

    // View Details Modal with Clean SaaS Styling
    function viewConsignerDetails(id) {
        const c = consignersCurrentPage.find(item => item.id == id);
        if (!c) return;
        activeViewingConsigner = c;

        document.getElementById('viewModalCompanyName').textContent = c.name;
        document.getElementById('viewModalSubTitle').textContent = `${c.consigner_code || ('EXP-' + c.id)} • ${c.entity_type || 'Exporter'} • ${c.region || 'Standard'}`;

        const html = `
            <div class="row g-2.5">
                <div class="col-12">
                    <div class="p-2.5 bg-light rounded border">
                        <div class="text-muted small fw-semibold text-uppercase" style="font-size:0.68rem;">Registered Corporate Exporter</div>
                        <h5 class="fw-bold text-dark mb-1 fs-6">${c.name}</h5>
                        <div class="d-flex flex-wrap gap-1.5 mt-1">
                            <span class="badge bg-primary text-white" style="font-size:0.7rem;">${c.entity_type || 'Manufacturer Exporter'}</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:0.7rem;">${c.status}</span>
                            <span class="badge bg-secondary-subtle text-dark border font-monospace" style="font-size:0.7rem;">${c.consigner_code || ('EXP-' + c.id)}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100 border">
                        <div class="card-header bg-light py-1.5 px-2.5 fw-semibold small text-primary d-flex align-items-center gap-1.5" style="font-size:0.76rem;">
                            <i class="bi bi-shield-check"></i> Statutory Customs EDI Details
                        </div>
                        <div class="card-body p-2.5 small" style="font-size:0.78rem;">
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">IEC Code:</span>
                                <span class="font-monospace fw-bold text-dark">${c.iec} (Br: ${c.iec_branch || 0})</span>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">GSTIN:</span>
                                <span class="font-monospace fw-bold text-primary">${c.gstin}</span>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">PAN Number:</span>
                                <span class="font-monospace">${c.pan || 'N/A'}</span>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted">AD Code:</span>
                                <span class="font-monospace fw-bold text-dark">${c.ad_code || 'N/A'}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100 border">
                        <div class="card-header bg-light py-1.5 px-2.5 fw-semibold small text-primary d-flex align-items-center gap-1.5" style="font-size:0.76rem;">
                            <i class="bi bi-bank"></i> Forex Bank & Contact
                        </div>
                        <div class="card-body p-2.5 small" style="font-size:0.78rem;">
                            <div class="mb-1.5">
                                <span class="text-muted fw-semibold">Forex Bank:</span>
                                <div class="text-dark">${c.forex_bank || 'Standard Export Bank'}</div>
                            </div>
                            <div class="mb-1.5">
                                <span class="text-muted fw-semibold">Contact:</span>
                                <div class="text-dark">${c.contact_person || 'N/A'} ${c.contact_phone ? ' • ' + c.contact_phone : ''}</div>
                            </div>
                            <div>
                                <span class="text-muted fw-semibold">Email:</span>
                                <div class="text-dark">${c.contact_email || 'N/A'}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border">
                        <div class="card-header bg-light py-1.5 px-2.5 fw-semibold small text-primary d-flex align-items-center gap-1.5" style="font-size:0.76rem;">
                            <i class="bi bi-geo-alt"></i> Addresses
                        </div>
                        <div class="card-body p-2.5 small" style="font-size:0.78rem;">
                            <div class="mb-1.5">
                                <span class="text-muted fw-semibold">Office:</span>
                                <span class="text-dark ms-1">${c.registered_address}</span>
                            </div>
                            <div>
                                <span class="text-muted fw-semibold">Plant:</span>
                                <span class="text-dark ms-1">${c.factory_address || c.registered_address}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('viewModalContent').innerHTML = html;
        const editBtn = document.getElementById('viewModalEditBtn');
        if (editBtn) editBtn.href = `/consigners/${c.id}/edit`;
        const modal = new bootstrap.Modal(document.getElementById('consignerViewModal'));
        modal.show();
    }

    function openConsignerInPdfEditor() {
        if (!activeViewingConsigner) return;
        sessionStorage.setItem('reliance_stamp_consigner', JSON.stringify(activeViewingConsigner));
        window.location.href = "{{ route('pdf-editor.index') }}";
    }

    function stampInPdfEditor(id) {
        const c = consignersCurrentPage.find(item => item.id == id);
        if (!c) return;
        sessionStorage.setItem('reliance_stamp_consigner', JSON.stringify(c));
        window.location.href = "{{ route('pdf-editor.index') }}";
    }

    // Delete Modal & Action
    function openDeleteConsignerModal(id) {
        const c = consignersCurrentPage.find(item => item.id == id);
        if (!c) return;
        pendingDeleteId = id;
        document.getElementById('deleteConsignerName').textContent = c.name;
        const modal = new bootstrap.Modal(document.getElementById('consignerDeleteModal'));
        modal.show();
    }

    // AJAX: Toggle Consigner Status (Active / Inactive) In-Place
    async function toggleConsignerStatus(id, currentStatus) {
        const btn = document.getElementById(`status-btn-${id}`);
        const dropdownItem = document.getElementById(`status-dropdown-btn-${id}`);
        const badge = document.getElementById(`status-badge-${id}`);

        // If currentStatus not passed, resolve from dataset or badge
        if (!currentStatus) {
            const item = consignersCurrentPage.find(c => c.id == id);
            currentStatus = item ? item.status : (badge && badge.textContent.includes('Active') ? 'Active' : 'Inactive');
        }

        const nextStatus = currentStatus === 'Active' ? 'Inactive' : 'Active';

        // Temporary Loading Spinner on the Badge
        if (btn) btn.disabled = true;
        if (badge) {
            badge.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status" style="width: 0.65rem; height: 0.65rem; border-width: 1.5px;"></span>Updating...`;
        }

        try {
            const statusUrl = `{{ url('consigners') }}/${id}/status`;

            const response = await fetch(statusUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'X-HTTP-Method-Override': 'PATCH',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    status: nextStatus,
                    _method: 'PATCH'
                })
            });

            let result;
            const contentType = response.headers.get('content-type') || '';
            if (contentType.includes('application/json')) {
                result = await response.json();
            } else {
                throw new Error(`Server returned status ${response.status}`);
            }

            if (response.ok && result.success) {
                const updatedStatus = result.status;

                // Update in-memory page array
                const localItem = consignersCurrentPage.find(c => c.id == id);
                if (localItem) localItem.status = updatedStatus;

                // Update badge HTML
                if (btn) {
                    btn.setAttribute('onclick', `toggleConsignerStatus(${id}, '${updatedStatus}')`);
                    if (updatedStatus === 'Active') {
                        btn.innerHTML = `
                            <span class="status-pill status-pill-active" id="status-badge-${id}">
                                <span class="status-indicator-dot active"></span> Active
                            </span>
                        `;
                    } else {
                        btn.innerHTML = `
                            <span class="status-pill status-pill-inactive" id="status-badge-${id}">
                                <span class="status-indicator-dot inactive"></span> Inactive
                            </span>
                        `;
                    }
                }

                if (dropdownItem) {
                    dropdownItem.setAttribute('onclick', `toggleConsignerStatus(${id}, '${updatedStatus}')`);
                }

                if (window.showToast) {
                    showToast(result.message || `Status updated to ${updatedStatus}.`, 'success');
                }
            } else {
                throw new Error(result?.message || 'Failed to update status');
            }
        } catch (err) {
            console.error('Status toggle error:', err);
            // Revert badge to original state
            if (btn) {
                if (currentStatus === 'Active') {
                    btn.innerHTML = `
                        <span class="status-pill status-pill-active" id="status-badge-${id}">
                            <span class="status-indicator-dot active"></span> Active
                        </span>
                    `;
                } else {
                    btn.innerHTML = `
                        <span class="status-pill status-pill-inactive" id="status-badge-${id}">
                            <span class="status-indicator-dot inactive"></span> Inactive
                        </span>
                    `;
                }
            }
            if (window.showToast) {
                showToast(err.message || 'Error communicating with database', 'danger');
            } else {
                alert(err.message || 'Error communicating with database');
            }
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    // AJAX: Delete Consigner with In-Place Row Animation & Counter Updates
    async function executeDeleteConsigner() {
        if (!pendingDeleteId) return;

        const deleteBtn = document.getElementById('confirmDeleteBtn');
        const originalBtnHtml = deleteBtn ? deleteBtn.innerHTML : '';
        if (deleteBtn) {
            deleteBtn.disabled = true;
            deleteBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Deleting...`;
        }

        try {
            const formData = new FormData();
            formData.append('_method', 'DELETE');
            formData.append('_token', window.csrfToken);

            const deleteUrl = `{{ url('consigners') }}/${pendingDeleteId}/delete`;

            const response = await fetch(deleteUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'X-HTTP-Method-Override': 'DELETE',
                    'Accept': 'application/json'
                },
                body: formData
            });

            let result;
            const contentType = response.headers.get('content-type') || '';
            if (contentType.includes('application/json')) {
                result = await response.json();
            } else {
                throw new Error(`Server returned status ${response.status}`);
            }

            if (result && result.success) {
                // 1. Hide confirmation modal immediately
                const modalEl = document.getElementById('consignerDeleteModal');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();

                const deletedId = pendingDeleteId;
                pendingDeleteId = null;

                // 2. Animate and remove the row from the table DOM in-place
                const row = document.getElementById(`consigner-row-${deletedId}`);
                if (row) {
                    row.style.transition = 'all 0.35s ease';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(25px)';
                    row.style.backgroundColor = '#FEE2E2';

                    setTimeout(() => {
                        row.remove();

                        // 3. Update in-memory records array
                        consignersCurrentPage = consignersCurrentPage.filter(c => c.id != deletedId);

                        // 4. Update Header and Footer Counters
                        const remainingRows = document.querySelectorAll('.consigner-data-row');
                        const newTotal = typeof result.total !== 'undefined' 
                            ? result.total 
                            : Math.max(0, parseInt(document.getElementById('paginationTotalCount')?.textContent || '1') - 1);

                        // Update Registered badge in header
                        const regBadge = document.getElementById('totalRegisteredBadge');
                        if (regBadge) regBadge.textContent = `${newTotal} Registered`;

                        // Update total count in footer
                        const totalCountEl = document.getElementById('paginationTotalCount');
                        if (totalCountEl) totalCountEl.textContent = newTotal;

                        // Update pagination last item number
                        const lastItemEl = document.getElementById('paginationLastItem');
                        const firstItemEl = document.getElementById('paginationFirstItem');
                        if (lastItemEl && firstItemEl) {
                            const first = parseInt(firstItemEl.textContent) || 0;
                            const currentLast = parseInt(lastItemEl.textContent) || 0;
                            if (newTotal === 0) {
                                firstItemEl.textContent = '0';
                                lastItemEl.textContent = '0';
                            } else if (currentLast > newTotal) {
                                lastItemEl.textContent = newTotal;
                            }
                        }

                        // 5. If table is now empty, render empty state row
                        if (remainingRows.length === 0) {
                            const tbody = document.getElementById('consignersTableBody');
                            if (tbody && !document.getElementById('emptyConsignersRow')) {
                                tbody.innerHTML = `
                                    <tr id="emptyConsignersRow">
                                        <td colspan="6" class="text-center py-4">
                                            <div class="text-muted mb-2">
                                                <i class="bi bi-search fs-3 text-secondary"></i>
                                            </div>
                                            <h6 class="fw-bold text-dark mb-1">No Consigners Found</h6>
                                            <p class="text-muted small mb-2">No records found in this view.</p>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="{{ route('consigners.index') }}" class="btn btn-outline-secondary btn-sm px-2.5 py-1">Reset View</a>
                                                <a href="{{ route('consigners.create') }}" class="btn btn-primary btn-sm px-2.5 py-1">Add Consigner</a>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                            }
                            const footer = document.getElementById('consignersTableFooter');
                            if (footer && newTotal === 0) footer.style.display = 'none';
                        }
                    }, 350);
                }

                // 6. Show clean toast message
                if (window.showToast) {
                    showToast(result.message || 'Consigner removed from database.', 'success');
                }
            } else {
                if (window.showToast) {
                    showToast(result.message || 'Error deleting consigner.', 'danger');
                } else {
                    alert(result.message || 'Error deleting consigner.');
                }
            }
        } catch (err) {
            console.error('Delete error:', err);
            if (window.showToast) {
                showToast('Error communicating with database', 'danger');
            } else {
                alert('Error communicating with database');
            }
        } finally {
            if (deleteBtn) {
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = originalBtnHtml;
            }
        }
    }

    // Reset Demo Data
    async function resetDemoData() {
        if (!confirm('Restore default verified consigners?')) return;

        try {
            const response = await fetch('/consigners/reset-demo', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();
            if (response.ok) {
                showToast(result.message || 'Demo dataset restored successfully.', 'success');
                setTimeout(() => window.location.reload(), 600);
            } else {
                alert(result.message || 'Error restoring demo data');
            }
        } catch (err) {
            console.error(err);
            showToast('Error restoring demo data', 'danger');
        }
    }
</script>
@endpush
