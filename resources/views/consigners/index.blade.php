@extends('layouts.app')

@section('content')
<main class="content-area p-3 p-lg-4" id="consignersApp">
  
  <!-- Page Header & Action Bar -->
  <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
    <div class="card-body p-4">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase px-2 py-1" style="font-size:0.75rem;">
              <i class="bi bi-shield-check me-1"></i>ICEGATE Compliant Directory
            </span>
            <span class="badge bg-success-subtle text-success fw-bold px-2 py-1" id="consignerTotalBadge" style="font-size:0.75rem;">
              {{ $stats['total'] ?? $consigners->count() }} Consigners in MySQL
            </span>
          </div>
          <h3 class="fw-bold text-dark mb-1">Consigners Master (Consignors / Exporters)</h3>
          <p class="text-muted small mb-0">Create, update, view and manage verified export entities, IEC registrations, GSTIN, factory works in Morbi & Jodhpur, and authorized AD Bank codes stored in MySQL.</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
          <!-- Add Consigner Page Link -->
          <a href="{{ route('consigners.create') }}" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm px-3">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Add Consigner</span>
          </a>

          <!-- Export Dropdown -->
          <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
              <i class="bi bi-download"></i>
              <span>Export</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
              <li><h6 class="dropdown-header">Export Master Directory</h6></li>
              <li>
                <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('consigners.export', ['format' => 'csv']) }}">
                  <i class="bi bi-file-earmark-spreadsheet text-success"></i> Export as CSV
                </a>
              </li>
              <li>
                <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('consigners.export', ['format' => 'json']) }}">
                  <i class="bi bi-filetype-json text-primary"></i> Export as JSON
                </a>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item d-flex align-items-center gap-2 text-danger" href="javascript:void(0)" onclick="resetDemoData()">
                  <i class="bi bi-arrow-counterclockwise"></i> Restore Default Seed Data
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Operational Statistics Ribbon (Real-Time from MySQL) -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm" style="border-radius:10px;">
        <div class="card-body p-3">
          <div class="d-flex align-items-center justify-content-between mb-1">
            <span class="text-muted small fw-semibold">TOTAL CONSIGNERS</span>
            <span class="badge bg-primary-subtle text-primary"><i class="bi bi-buildings"></i></span>
          </div>
          <h3 class="fw-bold mb-0 text-dark" id="statTotalConsigners">{{ $stats['total'] ?? 4 }}</h3>
          <div class="text-muted small" style="font-size:0.75rem;">Verified Export Entities</div>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm" style="border-radius:10px;">
        <div class="card-body p-3">
          <div class="d-flex align-items-center justify-content-between mb-1">
            <span class="text-muted small fw-semibold">ACTIVE STATUS</span>
            <span class="badge bg-success-subtle text-success"><i class="bi bi-check2-circle"></i></span>
          </div>
          <h3 class="fw-bold mb-0 text-success" id="statActiveConsigners">{{ $stats['active'] ?? 4 }}</h3>
          <div class="text-muted small" style="font-size:0.75rem;">Ready for Shipping Bills</div>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm" style="border-radius:10px;">
        <div class="card-body p-3">
          <div class="d-flex align-items-center justify-content-between mb-1">
            <span class="text-muted small fw-semibold">MORBI / GUJARAT</span>
            <span class="badge bg-warning-subtle text-warning"><i class="bi bi-geo-alt"></i></span>
          </div>
          <h3 class="fw-bold mb-0 text-dark" id="statMorbiPlants">{{ $stats['morbi'] ?? 3 }}</h3>
          <div class="text-muted small" style="font-size:0.75rem;">Ceramic Cluster Plants</div>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm" style="border-radius:10px;">
        <div class="card-body p-3">
          <div class="d-flex align-items-center justify-content-between mb-1">
            <span class="text-muted small fw-semibold">JODHPUR / RAJASTHAN</span>
            <span class="badge bg-info-subtle text-info"><i class="bi bi-building"></i></span>
          </div>
          <h3 class="fw-bold mb-0 text-dark" id="statJodhpurUnits">{{ $stats['jodhpur'] ?? 1 }}</h3>
          <div class="text-muted small" style="font-size:0.75rem;">Head Office & Granite Hub</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Search, Filter & View Controls -->
  <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;">
    <div class="card-body p-3">
      <div class="row g-2 align-items-center">
        <!-- Search Input -->
        <div class="col-md-5">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-white border-end-0 text-muted">
              <i class="bi bi-search"></i>
            </span>
            <input type="text" class="form-control border-start-0 ps-0" id="searchConsignerInput" placeholder="Search by name, IEC, GSTIN, city or AD code..." oninput="handleConsignerSearch()">
            <button class="btn btn-outline-secondary" type="button" onclick="clearConsignerSearch()" title="Clear Search">
              <i class="bi bi-x"></i>
            </button>
          </div>
        </div>

        <!-- Filter Status -->
        <div class="col-6 col-md-2">
          <select class="form-select form-select-sm" id="filterStatusSelect" onchange="handleConsignerSearch()">
            <option value="ALL">All Statuses</option>
            <option value="Active">Active Only</option>
            <option value="Inactive">Inactive Only</option>
          </select>
        </div>

        <!-- Filter Region -->
        <div class="col-6 col-md-3">
          <select class="form-select form-select-sm" id="filterRegionSelect" onchange="handleConsignerSearch()">
            <option value="ALL">All Manufacturing Locations</option>
            <option value="Morbi">Morbi Facility (Gujarat)</option>
            <option value="Jodhpur">Jodhpur Facility (Rajasthan)</option>
          </select>
        </div>

        <!-- View Mode Toggle (Table / Card Grid) -->
        <div class="col-md-2 text-md-end">
          <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-primary active" id="viewModeTableBtn" onclick="setViewMode('table')" title="Table View">
              <i class="bi bi-table"></i> Table
            </button>
            <button type="button" class="btn btn-outline-primary" id="viewModeGridBtn" onclick="setViewMode('grid')" title="Card Grid View">
              <i class="bi bi-grid-fill"></i> Cards
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Consigners Table View -->
  <div class="card border-0 shadow-sm mb-4" id="consignersTableWrapper" style="border-radius:10px; overflow:hidden;">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="consignersTable">
        <thead class="table-light text-uppercase" style="font-size:0.75rem; letter-spacing: 0.04em;">
          <tr>
            <th class="ps-3 py-3" style="width: 28%;">Consigner / Firm Details</th>
            <th class="py-3" style="width: 18%;">Compliance (IEC & GSTIN)</th>
            <th class="py-3" style="width: 22%;">Factory / Works Location</th>
            <th class="py-3" style="width: 14%;">AD Code & Bank</th>
            <th class="py-3 text-center" style="width: 8%;">Status</th>
            <th class="pe-3 py-3 text-end" style="width: 10%;">Actions</th>
          </tr>
        </thead>
        <tbody id="consignersTableBody">
          <!-- Populated dynamically via JS from MySQL -->
        </tbody>
      </table>
    </div>
  </div>

  <!-- Main Consigners Grid View (Cards) -->
  <div class="row g-3 mb-4 d-none" id="consignersGridWrapper">
    <!-- Populated dynamically via JS from MySQL -->
  </div>

  <!-- Empty State Message (hidden by default) -->
  <div class="card border-0 shadow-sm py-5 text-center d-none" id="emptyStateCard" style="border-radius:10px;">
    <div class="card-body py-4">
      <div class="d-inline-flex align-items-center justify-content-center bg-light text-muted rounded-circle mb-3" style="width:64px; height:64px;">
        <i class="bi bi-building-slash fs-2"></i>
      </div>
      <h5 class="fw-bold text-dark">No Consigners Found</h5>
      <p class="text-muted small mb-3">No export consigner entities match your search or filter criteria.</p>
      <a href="{{ route('consigners.create') }}" class="btn btn-primary btn-sm px-3">
        <i class="bi bi-plus-lg me-1"></i> Add New Consigner
      </a>
    </div>
  </div>

</main>



<!-- =========================================================================
     MODAL 2: VIEW CONSIGNER FULL DETAILS MODAL
     ========================================================================= -->
<div class="modal fade" id="consignerViewModal" tabindex="-1" aria-labelledby="consignerViewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow">
      
      <div class="modal-header bg-primary text-white py-3">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-building-check fs-5"></i>
          <div>
            <h5 class="modal-title fw-bold fs-6 mb-0" id="viewModalCompanyName">Consigner Details</h5>
            <div class="text-white-50 small" id="viewModalSubTitle">IEC & Customs Master Record (MySQL)</div>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4" id="viewModalContent">
        <!-- Rendered dynamically -->
      </div>

      <div class="modal-footer py-2 bg-light border-top d-flex justify-content-between">
        <div>
          <button type="button" class="btn btn-outline-primary btn-sm" id="viewModalUseInPdfBtn" onclick="openConsignerInPdfEditor()">
            <i class="bi bi-file-earmark-diff me-1"></i> Stamp on PDF in Editor
          </button>
        </div>
        <div>
          <a href="javascript:void(0)" class="btn btn-outline-secondary btn-sm me-1" id="viewModalEditBtn">
            <i class="bi bi-pencil me-1"></i> Edit
          </a>
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- =========================================================================
     MODAL 3: DELETE CONFIRMATION MODAL
     ========================================================================= -->
<div class="modal fade" id="consignerDeleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-body p-4 text-center">
        <div class="d-inline-flex align-items-center justify-content-center bg-danger-subtle text-danger rounded-circle mb-3" style="width:54px; height:54px;">
          <i class="bi bi-exclamation-triangle fs-3"></i>
        </div>
        <h6 class="fw-bold mb-1 text-dark">Delete Consigner from MySQL?</h6>
        <p class="text-muted small mb-3">Are you sure you want to delete <strong id="deleteConsignerName">this firm</strong>? This will remove its profile from the EDMS MySQL database.</p>
        <div class="d-flex justify-content-center gap-2">
          <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger btn-sm px-3 fw-semibold" id="confirmDeleteBtn" onclick="executeDeleteConsigner()">
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
    // Initial dataset passed from MySQL via Blade
    let consignersList = @json($consigners);
    let currentViewMode = 'table';
    let activeViewingConsigner = null;
    let pendingDeleteId = null;

    document.addEventListener('DOMContentLoaded', function() {
        renderConsigners();
    });

    // Render Table and Grid Cards
    function renderConsigners(dataToRender = consignersList) {
        const tbody = document.getElementById('consignersTableBody');
        const gridWrapper = document.getElementById('consignersGridWrapper');
        const emptyState = document.getElementById('emptyStateCard');
        const tableWrapper = document.getElementById('consignersTableWrapper');

        if (!dataToRender || dataToRender.length === 0) {
            tbody.innerHTML = '';
            gridWrapper.innerHTML = '';
            emptyState.classList.remove('d-none');
            tableWrapper.classList.add('d-none');
            gridWrapper.classList.add('d-none');
            return;
        }

        emptyState.classList.add('d-none');
        if (currentViewMode === 'table') {
            tableWrapper.classList.remove('d-none');
            gridWrapper.classList.add('d-none');
        } else {
            tableWrapper.classList.add('d-none');
            gridWrapper.classList.remove('d-none');
        }

        // 1. Build Table Rows
        let tableHtml = '';
        dataToRender.forEach(c => {
            const isMorbi = (c.region === 'Morbi' || (c.factory_address && c.factory_address.includes('Morbi')));
            const regionBadge = isMorbi
                ? `<span class="badge bg-warning-subtle text-dark border border-warning-subtle" style="font-size:0.7rem;"><i class="bi bi-geo-alt-fill me-1 text-warning"></i>Morbi (Gujarat)</span>`
                : `<span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size:0.7rem;"><i class="bi bi-building me-1"></i>Jodhpur (Rajasthan)</span>`;

            const statusBadge = c.status === 'Active'
                ? `<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle me-1"></i>Active</span>`
                : `<span class="badge bg-secondary-subtle text-muted border border-secondary-subtle">Inactive</span>`;

            tableHtml += `
                <tr>
                    <td class="ps-3 py-3">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-light text-dark font-monospace border" style="font-size:0.7rem;">${c.consigner_code || ('EXP-' + c.id)}</span>
                            <span class="badge bg-primary-subtle text-primary" style="font-size:0.7rem;">${c.entity_type || 'Manufacturer Exporter'}</span>
                        </div>
                        <div class="fw-bold text-dark fs-6">${c.name}</div>
                        <div class="text-muted small text-truncate" style="max-width:320px;" title="${c.registered_address}">
                            <i class="bi bi-geo-alt me-1"></i>${c.registered_address}
                        </div>
                    </td>
                    <td class="py-3">
                        <div class="small mb-1">
                            <span class="text-muted fw-semibold">IEC:</span>
                            <span class="font-monospace fw-bold text-dark">${c.iec}</span>
                            <span class="text-muted font-monospace">(${c.iec_branch || '0'})</span>
                        </div>
                        <div class="small mb-1">
                            <span class="text-muted fw-semibold">GSTIN:</span>
                            <span class="font-monospace text-primary fw-semibold">${c.gstin}</span>
                        </div>
                        ${c.pan ? `<div class="text-muted small font-monospace">PAN: ${c.pan}</div>` : ''}
                    </td>
                    <td class="py-3">
                        <div class="mb-1">${regionBadge}</div>
                        <div class="text-muted small text-truncate" style="max-width: 280px;" title="${c.factory_address || c.registered_address}">
                            ${c.factory_address || c.registered_address}
                        </div>
                    </td>
                    <td class="py-3">
                        <div class="small fw-bold font-monospace text-dark mb-1">AD: ${c.ad_code || 'N/A'}</div>
                        <div class="text-muted small text-truncate" style="max-width:220px;" title="${c.forex_bank || ''}">
                            <i class="bi bi-bank me-1"></i>${c.forex_bank || 'Standard Export Bank'}
                        </div>
                    </td>
                    <td class="py-3 text-center">
                        ${statusBadge}
                    </td>
                    <td class="pe-3 py-3 text-end">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary" onclick="viewConsignerDetails(${c.id})" title="View Complete Details">
                                <i class="bi bi-eye"></i>
                            </button>
                            <a href="/consigners/${c.id}/edit" class="btn btn-outline-primary" title="Edit Consigner Particulars">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button class="btn btn-outline-success" onclick="stampInPdfEditor(${c.id})" title="Open & Stamp on PDF in Editor">
                                <i class="bi bi-file-earmark-diff"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="openDeleteConsignerModal(${c.id})" title="Delete Consigner">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = tableHtml;

        // 2. Build Cards View
        let gridHtml = '';
        dataToRender.forEach(c => {
            const isMorbi = (c.region === 'Morbi' || (c.factory_address && c.factory_address.includes('Morbi')));
            const statusBadge = c.status === 'Active'
                ? `<span class="badge bg-success-subtle text-success">Active</span>`
                : `<span class="badge bg-secondary-subtle text-muted">Inactive</span>`;

            gridHtml += `
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius:10px;">
                        <div class="card-body p-3 d-flex flex-column">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-light text-dark font-monospace border">${c.consigner_code || ('EXP-' + c.id)}</span>
                                ${statusBadge}
                            </div>
                            <h5 class="fw-bold text-dark mb-1 fs-6">${c.name}</h5>
                            <span class="badge bg-primary-subtle text-primary align-self-start mb-2" style="font-size:0.7rem;">${c.entity_type}</span>
                            
                            <div class="p-2 bg-light rounded-2 small mb-2">
                                <div class="d-flex justify-content-between"><span class="text-muted">IEC:</span> <span class="font-monospace fw-bold">${c.iec}</span></div>
                                <div class="d-flex justify-content-between"><span class="text-muted">GSTIN:</span> <span class="font-monospace text-primary fw-semibold">${c.gstin}</span></div>
                                <div class="d-flex justify-content-between"><span class="text-muted">AD Code:</span> <span class="font-monospace">${c.ad_code || 'N/A'}</span></div>
                            </div>

                            <div class="text-muted small mb-3 flex-grow-1">
                                <i class="bi bi-geo-alt me-1"></i>${c.factory_address || c.registered_address}
                            </div>

                            <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                                <button class="btn btn-outline-primary btn-sm" onclick="viewConsignerDetails(${c.id})"><i class="bi bi-eye me-1"></i>Details</button>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-success" onclick="stampInPdfEditor(${c.id})" title="Stamp in PDF"><i class="bi bi-file-earmark-diff"></i></button>
                                    <a href="/consigners/${c.id}/edit" class="btn btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <button class="btn btn-outline-danger" onclick="openDeleteConsignerModal(${c.id})" title="Delete"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        gridWrapper.innerHTML = gridHtml;

        // Update counts
        document.getElementById('statTotalConsigners').textContent = consignersList.length;
        document.getElementById('statActiveConsigners').textContent = consignersList.filter(c => c.status === 'Active').length;
        document.getElementById('statMorbiPlants').textContent = consignersList.filter(c => c.region === 'Morbi' || (c.factory_address && c.factory_address.includes('Morbi'))).length;
        document.getElementById('statJodhpurUnits').textContent = consignersList.filter(c => c.region === 'Jodhpur').length;
        document.getElementById('consignerTotalBadge').textContent = `${consignersList.length} Consigners in MySQL`;
    }

    // Search and filter handler
    function handleConsignerSearch() {
        const query = document.getElementById('searchConsignerInput').value.toLowerCase().trim();
        const statusFilter = document.getElementById('filterStatusSelect').value;
        const regionFilter = document.getElementById('filterRegionSelect').value;

        const filtered = consignersList.filter(c => {
            const matchesQuery = !query || 
                (c.name && c.name.toLowerCase().includes(query)) ||
                (c.iec && c.iec.toLowerCase().includes(query)) ||
                (c.gstin && c.gstin.toLowerCase().includes(query)) ||
                (c.ad_code && c.ad_code.toLowerCase().includes(query)) ||
                (c.registered_address && c.registered_address.toLowerCase().includes(query)) ||
                (c.factory_address && c.factory_address.toLowerCase().includes(query));

            const matchesStatus = (statusFilter === 'ALL' || c.status === statusFilter);

            const isMorbi = (c.region === 'Morbi' || (c.factory_address && c.factory_address.includes('Morbi')));
            const matchesRegion = (regionFilter === 'ALL') || 
                (regionFilter === 'Morbi' && isMorbi) || 
                (regionFilter === 'Jodhpur' && !isMorbi);

            return matchesQuery && matchesStatus && matchesRegion;
        });

        renderConsigners(filtered);
    }

    function clearConsignerSearch() {
        document.getElementById('searchConsignerInput').value = '';
        document.getElementById('filterStatusSelect').value = 'ALL';
        document.getElementById('filterRegionSelect').value = 'ALL';
        renderConsigners(consignersList);
    }

    function setViewMode(mode) {
        currentViewMode = mode;
        const tableBtn = document.getElementById('viewModeTableBtn');
        const gridBtn = document.getElementById('viewModeGridBtn');
        if (mode === 'table') {
            tableBtn.classList.add('active');
            gridBtn.classList.remove('active');
        } else {
            tableBtn.classList.remove('active');
            gridBtn.classList.add('active');
        }
        renderConsigners();
    }



    // View Details Modal
    function viewConsignerDetails(id) {
        const c = consignersList.find(item => item.id == id);
        if (!c) return;
        activeViewingConsigner = c;

        document.getElementById('viewModalCompanyName').textContent = c.name;
        document.getElementById('viewModalSubTitle').textContent = `${c.consigner_code || ('EXP-' + c.id)} • ${c.entity_type}`;

        const html = `
            <div class="row g-3">
                <div class="col-12">
                    <div class="p-3 bg-light rounded-3">
                        <div class="text-muted small fw-semibold">REGISTERED EXPORT ENTITY</div>
                        <h4 class="fw-bold text-dark mb-1">${c.name}</h4>
                        <div class="d-flex gap-2">
                            <span class="badge bg-primary text-white">${c.entity_type}</span>
                            <span class="badge bg-success-subtle text-success">${c.status}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100 border">
                        <div class="card-header bg-light py-2 fw-semibold small text-primary">Statutory EDI Particulars</div>
                        <div class="card-body p-3 small">
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">IEC Code:</span>
                                <span class="font-monospace fw-bold">${c.iec} (Branch: ${c.iec_branch || 0})</span>
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
                                <span class="font-monospace fw-bold">${c.ad_code || 'N/A'}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100 border">
                        <div class="card-header bg-light py-2 fw-semibold small text-primary">Forex Bank & Contact</div>
                        <div class="card-body p-3 small">
                            <div class="mb-2">
                                <span class="text-muted fw-semibold">Authorized Forex Bank:</span>
                                <div><i class="bi bi-bank text-secondary me-1"></i>${c.forex_bank || 'Not Specified'}</div>
                            </div>
                            <div class="mb-2">
                                <span class="text-muted fw-semibold">Contact Person:</span>
                                <div>${c.contact_person || 'N/A'} ${c.contact_phone ? ' • ' + c.contact_phone : ''}</div>
                            </div>
                            <div>
                                <span class="text-muted fw-semibold">Official Email:</span>
                                <div>${c.contact_email || 'N/A'}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border">
                        <div class="card-header bg-light py-2 fw-semibold small text-primary">Operational Facilities</div>
                        <div class="card-body p-3 small">
                            <div class="mb-2">
                                <div class="text-muted fw-semibold"><i class="bi bi-geo-alt text-primary me-1"></i>Registered Office:</div>
                                <div>${c.registered_address}</div>
                            </div>
                            <div>
                                <div class="text-muted fw-semibold"><i class="bi bi-building-gear text-warning me-1"></i>Factory / Works (Cluster):</div>
                                <div>${c.factory_address || c.registered_address}</div>
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

    function openEditFromViewModal() {
        if (!activeViewingConsigner) return;
        window.location.href = `/consigners/${activeViewingConsigner.id}/edit`;
    }

    function openConsignerInPdfEditor() {
        if (!activeViewingConsigner) return;
        sessionStorage.setItem('reliance_stamp_consigner', JSON.stringify(activeViewingConsigner));
        window.location.href = "{{ route('pdf-editor.index') }}";
    }

    function stampInPdfEditor(id) {
        const c = consignersList.find(item => item.id == id);
        if (!c) return;
        sessionStorage.setItem('reliance_stamp_consigner', JSON.stringify(c));
        window.location.href = "{{ route('pdf-editor.index') }}";
    }

    // Delete Modal & Action
    function openDeleteConsignerModal(id) {
        const c = consignersList.find(item => item.id == id);
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
                consignersList = consignersList.filter(c => c.id != pendingDeleteId);
                bootstrap.Modal.getInstance(document.getElementById('consignerDeleteModal')).hide();
                renderConsigners();
                showToast(result.message || 'Deleted from MySQL', 'success');
            } else {
                alert(result.message || 'Failed to delete consigner.');
            }
        } catch (err) {
            console.error(err);
            showToast('Error communicating with MySQL database', 'danger');
        }
    }

    // Reset Demo Data
    async function resetDemoData() {
        if (!confirm('Restore default Morbi & Jodhpur verified consigners in MySQL?')) return;

        try {
            const response = await fetch("{{ route('consigners.reset-demo') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();
            if (response.ok) {
                consignersList = result.data;
                renderConsigners();
                showToast(result.message, 'success');
            }
        } catch (err) {
            console.error(err);
            showToast('Error resetting demo seed data', 'danger');
        }
    }
</script>
@endpush
