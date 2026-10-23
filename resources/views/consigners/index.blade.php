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
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold px-2 py-0.5 rounded-pill" style="font-size: 0.72rem;">
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

  <!-- Standalone Slim Search & Filter Card -->
  <form action="{{ route('consigners.index') }}" method="GET" id="consignersFilterForm" class="card border-0 shadow-sm p-2 mb-3" style="border: 1px solid #E2E8F0 !important; border-radius: 10px; background: #FFFFFF;">
    <input type="hidden" name="per_page" id="perPageInput" value="{{ $filters['per_page'] ?? 10 }}">

    <div class="row g-2 align-items-center">
      <!-- Search Input -->
      <div class="col-md-6 col-lg-6">
        <div class="input-group input-group-sm">
          <span class="input-group-text border-end-0 text-muted ps-2.5" style="background: #F8FAFC; border-color: #E2E8F0;">
            <i class="bi bi-search text-secondary"></i>
          </span>
          <input type="text" class="form-control border-start-0 ps-1" name="search" id="searchConsignerInput" value="{{ request('search') }}" placeholder="Search company name, IEC, GSTIN, location, AD code..." style="background: #F8FAFC; border-color: #E2E8F0; font-size: 0.83rem;">
          @if(request('search'))
            <a href="{{ route('consigners.index', array_merge(request()->except('search', 'page'), ['page' => 1])) }}" class="input-group-text border-start-0 text-muted" style="background: #F8FAFC; border-color: #E2E8F0;" title="Clear Search">
              <i class="bi bi-x-circle-fill"></i>
            </a>
          @endif
        </div>
      </div>

      <!-- Filter: Location -->
      <div class="col-sm-6 col-md-3 col-lg-3">
        <select class="form-select form-select-sm" name="region" id="filterRegionSelect" onchange="this.form.submit()" style="border-color: #E2E8F0; background-color: #F8FAFC; font-size: 0.83rem;">
          <option value="ALL" {{ request('region') === 'ALL' || !request('region') ? 'selected' : '' }}>All Locations</option>
          <option value="Morbi" {{ request('region') === 'Morbi' ? 'selected' : '' }}>Morbi Ceramic Cluster (GJ)</option>
          <option value="Jodhpur" {{ request('region') === 'Jodhpur' ? 'selected' : '' }}>Jodhpur Granite Hub (RJ)</option>
        </select>
      </div>

      <!-- Filter: Status -->
      <div class="col-sm-6 col-md-3 col-lg-3">
        <select class="form-select form-select-sm" name="status" id="filterStatusSelect" onchange="this.form.submit()" style="border-color: #E2E8F0; background-color: #F8FAFC; font-size: 0.83rem;">
          <option value="ALL" {{ request('status') === 'ALL' || !request('status') ? 'selected' : '' }}>All Statuses</option>
          <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active Only</option>
          <option value="Inactive" {{ request('status') === 'Inactive' ? 'selected' : '' }}>Inactive Only</option>
        </select>
      </div>
    </div>
  </form>

  <!-- Clean Enterprise Data Table Container -->
  <div class="card border-0 shadow-sm mb-4" style="border: 1px solid #E2E8F0 !important; border-radius: 10px; overflow: hidden; background: #FFFFFF;" id="consignersTableWrapper">
    <div class="table-responsive">
      <table class="table consigner-data-table align-middle mb-0" id="consignersTable">
        <thead>
          <tr>
            <th style="width: 32%;"><i class="bi bi-building me-1 opacity-75"></i> Exporter / Legal Entity</th>
            <th style="width: 20%;"><i class="bi bi-upc-scan me-1 opacity-75"></i> IEC & GSTIN</th>
            <th style="width: 22%;"><i class="bi bi-geo-alt me-1 opacity-75"></i> Works & Location</th>
            <th style="width: 16%;"><i class="bi bi-bank me-1 opacity-75"></i> Bank & AD Code</th>
            <th class="text-center" style="width: 5%;"><i class="bi bi-shield-check me-1 opacity-75"></i> Status</th>
            <th class="text-end pe-3" style="width: 5%;">Actions</th>
          </tr>
        </thead>
        <tbody id="consignersTableBody">
          @forelse($consigners as $c)
            @php
              $isMorbi = ($c->region === 'Morbi' || (str_contains($c->factory_address ?? '', 'Morbi')));
              $words = preg_split('/\s+/', trim($c->name));
              $initials = count($words) === 1 ? strtoupper(substr($words[0], 0, 2)) : strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
              $avatarClass = $isMorbi ? 'morbi' : 'jodhpur';
            @endphp
            <tr>
              <!-- Exporter Name & Details -->
              <td>
                <div class="d-flex align-items-center gap-2.5">
                  <div class="company-avatar-box {{ $avatarClass }}">
                    {{ $initials }}
                  </div>
                  <div>
                    <div class="fw-bold text-dark d-flex align-items-center gap-1" style="font-size: 0.88rem;">
                      <span role="button" onclick="viewConsignerDetails({{ $c->id }})" class="text-dark text-decoration-none" style="cursor: pointer;">{{ $c->name }}</span>
                      <i class="bi bi-patch-check-fill text-primary" style="font-size: 0.8rem;" title="ICEGATE Certified Exporter"></i>
                    </div>
                    <div class="text-muted d-flex align-items-center gap-1.5 mt-0.5" style="font-size: 0.74rem;">
                      <span class="consigner-code-badge">{{ $c->consigner_code ?? ('EXP-' . $c->id) }}</span>
                      <span class="text-muted">&bull;</span>
                      <span>{{ $c->entity_type ?? 'Manufacturer Exporter' }}</span>
                    </div>
                  </div>
                </div>
              </td>

              <!-- IEC & GSTIN -->
              <td>
                <div class="iec-badge-chip mb-1">
                  <span class="iec-label">IEC</span>
                  <span class="iec-val">{{ $c->iec }}</span>
                  <button type="button" class="iec-copy-btn ms-1" onclick="copyToClipboard('{{ $c->iec }}', this)" title="Copy IEC Code">
                    <i class="bi bi-copy"></i>
                  </button>
                </div>
                <div class="gstin-code-text">
                  <span class="text-muted" style="font-size: 0.68rem; font-weight: 600;">GST</span> {{ $c->gstin }}
                </div>
              </td>

              <!-- Works & Location -->
              <td>
                <div class="mb-1">
                  @if($isMorbi)
                    <span class="facility-pill-badge facility-pill-morbi"><i class="bi bi-geo-alt-fill"></i> Morbi, GJ</span>
                  @else
                    <span class="facility-pill-badge facility-pill-jodhpur"><i class="bi bi-building"></i> Jodhpur, RJ</span>
                  @endif
                </div>
                <div class="text-muted text-truncate" style="max-width: 230px; font-size: 0.74rem;" title="{{ $c->registered_address }}">
                  {{ $c->registered_address }}
                </div>
              </td>

              <!-- Bank & AD Code -->
              <td>
                <div class="font-monospace text-dark fw-bold mb-0.5" style="font-size: 0.78rem;">
                  <span class="text-muted fw-semibold" style="font-size: 0.7rem;">AD:</span> {{ $c->ad_code ?? 'N/A' }}
                </div>
                <div class="text-muted text-truncate" style="max-width: 190px; font-size: 0.74rem;" title="{{ $c->forex_bank ?? '' }}">
                  <i class="bi bi-bank text-secondary me-1" style="font-size: 0.7rem;"></i>{{ $c->forex_bank ?? 'Standard Export Bank' }}
                </div>
              </td>

              <!-- Status -->
              <td class="text-center">
                @if($c->status === 'Active')
                  <span class="status-pill-active">
                    <span class="status-live-dot"></span> Active
                  </span>
                @else
                  <span class="status-pill-inactive">Inactive</span>
                @endif
              </td>

              <!-- Clean Actions (Edit + Stamp + More Dropdown) -->
              <td class="text-end pe-3">
                <div class="d-inline-flex align-items-center gap-1.5">
                  <!-- Edit Button -->
                  <a href="{{ route('consigners.edit', $c->id) }}" class="action-btn-edit" title="Edit Consigner">
                    <i class="bi bi-pencil-square text-primary"></i>
                    <span>Edit</span>
                  </a>
                  
                  <!-- Stamp on PDF Button -->
                  <button type="button" class="action-btn-stamp" onclick="stampInPdfEditor({{ $c->id }})" title="Stamp on PDF">
                    <i class="bi bi-file-earmark-diff"></i>
                  </button>

                  <!-- More Options Dropdown -->
                  <div class="dropdown d-inline-block">
                    <button type="button" class="action-btn-more" data-bs-toggle="dropdown" aria-expanded="false" title="More Options">
                      <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border" style="border-radius: 8px; font-size: 0.82rem;">
                      <li>
                        <a class="dropdown-item py-1.5 d-flex align-items-center gap-2" href="javascript:void(0)" onclick="viewConsignerDetails({{ $c->id }})">
                          <i class="bi bi-eye text-primary"></i>
                          <span>View Full Profile</span>
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
            <tr>
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

    <!-- Clean Server-Side Pagination Footer -->
    @if($consigners->total() > 0)
    <div class="p-2.5 px-3 bg-white border-top d-flex flex-wrap align-items-center justify-content-between gap-2" style="border-color: #F1F5F9 !important;">
      <div class="text-muted small" style="font-size: 0.8rem;">
        Showing <span class="fw-semibold text-dark">{{ $consigners->firstItem() ?? 0 }}</span> &ndash; <span class="fw-semibold text-dark">{{ $consigners->lastItem() ?? 0 }}</span> of <span class="fw-semibold text-dark">{{ $consigners->total() }}</span> consigners
      </div>
      
      <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center gap-1.5">
          <label for="perPageSelector" class="text-muted small mb-0" style="font-size: 0.76rem;">Rows:</label>
          <select id="perPageSelector" class="form-select form-select-sm py-1 px-2" style="width: 70px; font-size: 0.78rem; border-color: #CBD5E1;" onchange="changePerPage(this.value)">
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
            <div class="text-white-50" id="viewModalSubTitle" style="font-size: 0.7rem;">ICEGATE & Customs Master Record (MySQL)</div>
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
        <p class="text-muted small mb-3">Permanently delete <strong id="deleteConsignerName">this firm</strong> from MySQL?</p>
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

    async function executeDeleteConsigner() {
        if (!pendingDeleteId) return;

        try {
            const response = await fetch(`/consigners/${pendingDeleteId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();
            if (response.ok) {
                bootstrap.Modal.getInstance(document.getElementById('consignerDeleteModal')).hide();
                showToast(result.message || 'Consigner removed from database.', 'success');
                setTimeout(() => window.location.reload(), 600);
            } else {
                alert(result.message || 'Error deleting consigner.');
            }
        } catch (err) {
            console.error(err);
            showToast('Error communicating with database', 'danger');
        }
    }

    // Reset Demo Data
    async function resetDemoData() {
        if (!confirm('Restore default verified consigners in MySQL?')) return;

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
                showToast(result.message || 'Demo dataset restored in MySQL.', 'success');
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
