@extends('layouts.app')

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
        <ul class="dropdown-menu dropdown-menu-end shadow-sm border" style="font-size: 0.82rem; border-radius: 8px;">
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
  <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden; border: 1px solid #E2E8F0 !important;">
    
    <!-- Integrated Search & Filter Toolbar (Full Width) -->
    <div class="p-3 px-4 border-bottom bg-white" style="background-color: #FFFFFF !important;">
      <form action="{{ route('consigners.index') }}" method="GET" id="consignersFilterForm" class="row g-2 align-items-center mb-0 w-100" style="width: 100% !important;">
        <input type="hidden" name="per_page" id="perPageInput" value="{{ $filters['per_page'] ?? 10 }}">

        <!-- Search Input -->
        <div class="col-12 col-md-5 col-lg-5">
          <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0 ps-2.5" style="border-color: #E2E8F0;">
              <i class="bi bi-search text-secondary"></i>
            </span>
            <input type="text" class="form-control bg-light border-start-0 ps-1" name="search" id="searchConsignerInput" value="{{ request('search') }}" placeholder="Search company, IEC, GSTIN, location, AD code..." style="font-size: 0.84rem; height: 38px; border-color: #E2E8F0;">
            @if(request('search'))
              <a href="{{ route('consigners.index', array_merge(request()->except('search', 'page'), ['page' => 1])) }}" class="input-group-text bg-light border-start-0 text-muted" style="border-color: #E2E8F0;" title="Clear Search">
                <i class="bi bi-x-circle-fill"></i>
              </a>
            @endif
          </div>
        </div>

        <!-- Manufacturing Hubs Filter -->
        <div class="col-6 col-md-3 col-lg-3">
          <select class="form-select bg-light" name="region" id="filterRegionSelect" onchange="this.form.submit()" style="font-size: 0.84rem; height: 38px; border-color: #E2E8F0;">
            <option value="ALL" {{ request('region') === 'ALL' || !request('region') ? 'selected' : '' }}>All Manufacturing Hubs</option>
            <option value="Morbi" {{ request('region') === 'Morbi' ? 'selected' : '' }}>Morbi Facility (Gujarat)</option>
            <option value="Jodhpur" {{ request('region') === 'Jodhpur' ? 'selected' : '' }}>Jodhpur Facility (Rajasthan)</option>
          </select>
        </div>

        <!-- Status Filter -->
        <div class="col-6 col-md-2 col-lg-2">
          <select class="form-select bg-light" name="status" id="filterStatusSelect" onchange="this.form.submit()" style="font-size: 0.84rem; height: 38px; border-color: #E2E8F0;">
            <option value="ALL" {{ request('status') === 'ALL' || !request('status') ? 'selected' : '' }}>All Statuses</option>
            <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active Only</option>
            <option value="Inactive" {{ request('status') === 'Inactive' ? 'selected' : '' }}>Inactive Only</option>
          </select>
        </div>

        <!-- Reset Button or Records Badge -->
        <div class="col-12 col-md-2 col-lg-2 text-md-end">
          @if(request('search') || (request('region') && request('region') !== 'ALL') || (request('status') && request('status') !== 'ALL'))
            <a href="{{ route('consigners.index') }}" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center justify-content-center gap-1.5 w-100" title="Reset all search filters" style="height: 38px; font-size: 0.82rem; font-weight: 500;">
              <i class="bi bi-arrow-counterclockwise"></i>
              <span>Reset Filters</span>
            </a>
          @else
            <div class="d-none d-md-flex align-items-center justify-content-end text-muted small pe-1" style="height: 38px; font-size: 0.78rem;">
              <span class="badge bg-light text-secondary border px-2.5 py-1.5 rounded-2">
                <strong class="text-dark">{{ $consigners->total() }}</strong> records found
              </span>
            </div>
          @endif
        </div>
      </form>
    </div>

    <!-- Clean Enterprise Data Table -->
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="consignersTable">
        <thead class="table-light text-uppercase small" style="font-size:0.75rem; letter-spacing:0.04em;">
          <tr>
            <th class="ps-4 py-3" style="width: 32%;">Exporter / Legal Entity</th>
            <th class="py-3" style="width: 18%;">Compliance (IEC & GSTIN)</th>
            <th class="py-3" style="width: 22%;">Factory / Works Location</th>
            <th class="py-3" style="width: 14%;">AD Code & Bank</th>
            <th class="py-3 text-center" style="width: 6%;">Status</th>
            <th class="pe-4 py-3 text-end" style="width: 8%;">Actions</th>
          </tr>
        </thead>
        <tbody id="consignersTableBody">
          @forelse($consigners as $c)
            @php
              $isMorbi = ($c->region === 'Morbi' || (str_contains($c->factory_address ?? '', 'Morbi')));
              $words = preg_split('/\s+/', trim($c->name));
              $initials = count($words) === 1 ? strtoupper(substr($words[0], 0, 2)) : strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
              $avatarBgClass = $isMorbi ? 'bg-amber-soft' : 'bg-sky-soft';
            @endphp
            <tr id="consigner-row-{{ $c->id }}" class="consigner-data-row">
              <!-- Exporter Name & Details -->
              <td class="ps-4 py-3">
                <div class="d-flex align-items-center gap-3">
                  <div class="company-avatar-box {{ $isMorbi ? 'morbi' : 'jodhpur' }} flex-shrink-0">
                    {{ $initials }}
                  </div>
                  <div class="overflow-hidden">
                    <div class="fw-bold text-dark text-truncate d-flex align-items-center gap-1" style="font-size: 0.89rem;">
                      <span role="button" onclick="viewConsignerDetails({{ $c->id }})" class="text-dark text-decoration-none" style="cursor: pointer;" title="{{ $c->name }}">{{ $c->name }}</span>
                      <i class="bi bi-patch-check-fill text-primary" style="font-size: 0.8rem;" title="ICEGATE Certified Exporter"></i>
                    </div>
                    <div class="text-muted small d-flex align-items-center gap-2 mt-0.5" style="font-size: 0.74rem;">
                      <span class="badge bg-light text-secondary border font-monospace py-0.5 px-1.5" style="font-size: 0.68rem;">{{ $c->consigner_code ?? ('EXP-' . $c->id) }}</span>
                      <span>{{ $c->entity_type ?? 'Manufacturer Exporter' }}</span>
                    </div>
                  </div>
                </div>
              </td>

              <!-- IEC & GSTIN -->
              <td class="py-3">
                <div class="d-flex flex-column gap-1">
                  <div class="d-flex align-items-center gap-1.5">
                    <span class="text-muted small" style="font-size: 0.72rem;">IEC:</span>
                    <span class="font-monospace fw-bold text-dark small">{{ $c->iec }}</span>
                    <button type="button" class="btn btn-link p-0 text-muted" onclick="copyToClipboard('{{ $c->iec }}', this)" title="Copy IEC Code" style="font-size: 0.7rem; line-height: 1;">
                      <i class="bi bi-copy"></i>
                    </button>
                  </div>
                  <div>
                    <span class="text-muted small" style="font-size: 0.72rem;">GST:</span>
                    <span class="font-monospace text-primary small" style="font-size: 0.76rem;">{{ $c->gstin }}</span>
                  </div>
                </div>
              </td>

              <!-- Works & Location -->
              <td class="py-3">
                <div class="mb-1">
                  @if($isMorbi)
                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle py-0.5 px-2" style="font-size: 0.7rem;"><i class="bi bi-geo-alt-fill text-warning me-1"></i>Morbi Facility (GJ)</span>
                  @else
                    <span class="badge bg-info-subtle text-dark border border-info-subtle py-0.5 px-2" style="font-size: 0.7rem;"><i class="bi bi-building text-info me-1"></i>Jodhpur Hub (RJ)</span>
                  @endif
                </div>
                <div class="text-muted small text-truncate" style="max-width: 260px; font-size: 0.75rem;" title="{{ $c->registered_address }}">
                  <i class="bi bi-geo-alt text-muted me-1"></i>{{ $c->registered_address }}
                </div>
              </td>

              <!-- Bank & AD Code -->
              <td class="py-3">
                <div class="small">
                  <span class="text-muted" style="font-size: 0.72rem;">AD Code:</span>
                  <span class="font-monospace fw-bold text-dark">{{ $c->ad_code ?? 'Pending' }}</span>
                </div>
                <div class="text-muted small text-truncate mt-0.5" style="max-width: 190px; font-size: 0.74rem;" title="{{ $c->forex_bank ?? '' }}">
                  <i class="bi bi-bank text-secondary me-1" style="font-size: 0.7rem;"></i>{{ $c->forex_bank ?? 'Authorized Dealer' }}
                </div>
              </td>

              <!-- Status (Interactive AJAX Toggle) -->
              <td class="py-3 text-center" id="status-cell-{{ $c->id }}">
                <button type="button" 
                        class="btn p-0 border-0 bg-transparent text-decoration-none shadow-none" 
                        id="status-btn-{{ $c->id }}" 
                        onclick="toggleConsignerStatus({{ $c->id }}, '{{ $c->status }}')" 
                        title="Click to toggle status (Active / Inactive)">
                  @if($c->status === 'Active')
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-semibold status-pill-badge" id="status-badge-{{ $c->id }}" style="font-size: 0.72rem; cursor: pointer;">
                      <i class="bi bi-check-circle-fill me-1"></i>Active
                    </span>
                  @else
                    <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2.5 py-1 status-pill-badge" id="status-badge-{{ $c->id }}" style="font-size: 0.72rem; cursor: pointer;">
                      <i class="bi bi-dash-circle me-1"></i>Inactive
                    </span>
                  @endif
                </button>
              </td>

              <!-- Action Buttons -->
              <td class="pe-4 py-3 text-end">
                <div class="btn-group btn-group-sm" role="group">
                  <a href="{{ route('consigners.edit', $c->id) }}" class="btn btn-outline-primary" title="Edit Consigner">
                    <i class="bi bi-pencil me-1"></i> Edit
                  </a>
                  <button type="button" class="btn btn-outline-secondary" onclick="stampInPdfEditor({{ $c->id }})" title="Stamp on PDF">
                    <i class="bi bi-file-earmark-diff"></i>
                  </button>
                  <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="visually-hidden">More Actions</span>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow-sm border" style="border-radius: 8px; font-size: 0.82rem;">
                    <li>
                      <a class="dropdown-item py-1.5 d-flex align-items-center gap-2" href="javascript:void(0)" onclick="viewConsignerDetails({{ $c->id }})">
                        <i class="bi bi-eye text-primary"></i>
                        <span>View Full Profile</span>
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
              </td>
            </tr>
          @empty
            <tr id="emptyConsignersRow">
              <td colspan="6" class="text-center py-4">
                <div class="text-muted mb-2">
                  <i class="bi bi-search fs-3 text-secondary"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">No Consigners Found</h6>
                <p class="text-muted small mb-2">No records match your search or filter criteria.</p>
                <div class="d-flex justify-content-center gap-2">
                  <a href="{{ route('consigners.index') }}" class="btn btn-outline-secondary btn-sm px-2.5 py-1">
                    Reset Filters
                  </a>
                  <a href="{{ route('consigners.create') }}" class="btn btn-primary btn-sm px-2.5 py-1">
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
    <div class="card-footer bg-white border-top py-2.5 px-4 d-flex flex-wrap align-items-center justify-content-between gap-2" id="consignersTableFooter">
      <div class="text-muted small">
        Showing <span class="fw-bold text-dark" id="paginationFirstItem">{{ $consigners->firstItem() ?? 0 }}</span> &ndash; <span class="fw-bold text-dark" id="paginationLastItem">{{ $consigners->lastItem() ?? 0 }}</span> of <span class="fw-bold text-dark" id="paginationTotalCount">{{ $consigners->total() }}</span> consigners
      </div>
      
      <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center gap-1.5">
          <label for="perPageSelector" class="text-muted small mb-0" style="font-size: 0.78rem;">Rows:</label>
          <select id="perPageSelector" class="form-select form-select-sm py-1 px-2" style="width: 70px; font-size: 0.78rem;" onchange="changePerPage(this.value)">
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
            const response = await fetch(`/consigners/${id}/status`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: nextStatus })
            });

            const result = await response.json();

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
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-semibold status-pill-badge" id="status-badge-${id}" style="font-size: 0.72rem; cursor: pointer;">
                                <i class="bi bi-check-circle-fill me-1"></i>Active
                            </span>
                        `;
                    } else {
                        btn.innerHTML = `
                            <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2.5 py-1 status-pill-badge" id="status-badge-${id}" style="font-size: 0.72rem; cursor: pointer;">
                                <i class="bi bi-dash-circle me-1"></i>Inactive
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
                throw new Error(result.message || 'Failed to update status');
            }
        } catch (err) {
            console.error('Status toggle error:', err);
            // Revert badge to original state
            if (btn) {
                if (currentStatus === 'Active') {
                    btn.innerHTML = `
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-semibold status-pill-badge" id="status-badge-${id}" style="font-size: 0.72rem; cursor: pointer;">
                            <i class="bi bi-check-circle-fill me-1"></i>Active
                        </span>
                    `;
                } else {
                    btn.innerHTML = `
                        <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2.5 py-1 status-pill-badge" id="status-badge-${id}" style="font-size: 0.72rem; cursor: pointer;">
                            <i class="bi bi-dash-circle me-1"></i>Inactive
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
            const response = await fetch(`/consigners/${pendingDeleteId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (response.ok && result.success) {
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
