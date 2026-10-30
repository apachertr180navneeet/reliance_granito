<?php
// Reliance Granito Export Documentation Management System (EDMS)
// Consigners (Consignor / Exporter) Master Management (CRUD)
$pageTitle = 'Reliance Granito | Consigners (Consignor) Master CRUD';
$activeNav = 'consigners';
include_once __DIR__.'/includes/header.php';
?>

<!-- Consigners Master Management Area -->
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
              4 Consigners Registered
            </span>
          </div>
          <h3 class="fw-bold text-dark mb-1">Consigners Master (Consignors / Exporters)</h3>
          <p class="text-muted small mb-0">Create, update, view and manage verified export entities, IEC registrations, GSTIN, factory works in Gujarat & Jodhpur, and authorized AD Bank codes.</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
          <!-- Reset to defaults -->
          <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2" onclick="resetDefaultConsigners()" title="Restore default master seed records">
            <i class="bi bi-arrow-counterclockwise"></i>
            <span>Reset Defaults</span>
          </button>
          
          <!-- Export Data -->
          <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
              <i class="bi bi-download"></i>
              <span>Export</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size:0.85rem;">
              <li><a class="dropdown-item" href="#" onclick="exportConsignersJSON(); return false;"><i class="bi bi-filetype-json text-warning me-2"></i>Export as JSON</a></li>
              <li><a class="dropdown-item" href="#" onclick="exportConsignersCSV(); return false;"><i class="bi bi-filetype-csv text-success me-2"></i>Export as CSV</a></li>
            </ul>
          </div>

          <!-- Add Consigner Button -->
          <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-2 shadow-sm px-3 py-2 fw-semibold" onclick="openAddConsignerModal()">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Add New Consigner</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Key Operational Metric Cards -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm" style="border-radius:10px;">
        <div class="card-body p-3">
          <div class="d-flex align-items-center justify-content-between mb-1">
            <span class="text-muted small fw-semibold">TOTAL CONSIGNERS</span>
            <span class="badge bg-primary-subtle text-primary"><i class="bi bi-buildings"></i></span>
          </div>
          <h3 class="fw-bold mb-0 text-dark" id="statTotalConsigners">4</h3>
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
          <h3 class="fw-bold mb-0 text-success" id="statActiveConsigners">4</h3>
          <div class="text-muted small" style="font-size:0.75rem;">Ready for Shipping Bills</div>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm" style="border-radius:10px;">
        <div class="card-body p-3">
          <div class="d-flex align-items-center justify-content-between mb-1">
            <span class="text-muted small fw-semibold">GUJARAT</span>
            <span class="badge bg-warning-subtle text-warning"><i class="bi bi-geo-alt"></i></span>
          </div>
          <h3 class="fw-bold mb-0 text-dark" id="statMorbiPlants">3</h3>
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
          <h3 class="fw-bold mb-0 text-dark" id="statJodhpurUnits">1</h3>
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
            <option value="Gujarat">Gujarat Facility</option>
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
          <!-- Populated dynamically via JS -->
        </tbody>
      </table>
    </div>
  </div>

  <!-- Main Consigners Grid View (Cards) -->
  <div class="row g-3 mb-4 d-none" id="consignersGridWrapper">
    <!-- Populated dynamically via JS -->
  </div>

  <!-- Empty State Message (hidden by default) -->
  <div class="card border-0 shadow-sm py-5 text-center d-none" id="emptyStateCard" style="border-radius:10px;">
    <div class="card-body py-4">
      <div class="d-inline-flex align-items-center justify-content-center bg-light text-muted rounded-circle mb-3" style="width:64px; height:64px;">
        <i class="bi bi-building-slash fs-2"></i>
      </div>
      <h5 class="fw-bold text-dark">No Consigners Found</h5>
      <p class="text-muted small mb-3">No export consigner entities match your search or filter criteria.</p>
      <button class="btn btn-primary btn-sm px-3" onclick="openAddConsignerModal()">
        <i class="bi bi-plus-lg me-1"></i> Add New Consigner
      </button>
    </div>
  </div>

</main>

<!-- =========================================================================
     MODAL 1: ADD / EDIT CONSIGNER FORM MODAL
     ========================================================================= -->
<div class="modal fade" id="consignerFormModal" tabindex="-1" aria-labelledby="consignerFormModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow">
      
      <!-- Modal Header -->
      <div class="modal-header bg-light py-3 border-bottom">
        <div class="d-flex align-items-center gap-2">
          <div class="bg-primary text-white rounded p-1 d-flex align-items-center justify-content-center" style="width:32px; height:32px;">
            <i class="bi bi-building" id="formModalIcon"></i>
          </div>
          <div>
            <h5 class="modal-title fw-bold fs-6 mb-0 text-dark" id="consignerFormModalLabel">Add New Consigner</h5>
            <div class="small text-muted" style="font-size:0.75rem;">Enter legally registered exporter particulars for Customs & Shipping Bills</div>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Modal Form Body -->
      <form id="consignerForm" onsubmit="handleConsignerFormSubmit(event)">
        <div class="modal-body p-4">
          <input type="hidden" id="formConsignerId">

          <!-- Section 1: Firm & Exporter Information -->
          <div class="mb-3">
            <h6 class="fw-bold text-primary mb-3 text-uppercase" style="font-size:0.75rem; letter-spacing:0.05em;">
              <i class="bi bi-building-gear me-1"></i> 1. Firm & Exporter Legal Particulars
            </h6>
            <div class="row g-3">
              <div class="col-md-8">
                <label class="form-label small fw-semibold">Company / Firm Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm" id="formName" required placeholder="e.g. Reliance Granito Private Limited">
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-semibold">Entity Type</label>
                <select class="form-select form-select-sm" id="formEntityType">
                  <option value="Manufacturer Exporter">Manufacturer Exporter</option>
                  <option value="Merchant Exporter">Merchant Exporter</option>
                  <option value="Manufacturing Facility / Plant">Manufacturing Facility / Plant</option>
                  <option value="Sister Firm / Associate">Sister Firm / Associate</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Section 2: Statutory EDI & Tax Codes -->
          <div class="mb-3 pt-2 border-top">
            <h6 class="fw-bold text-primary mb-3 text-uppercase" style="font-size:0.75rem; letter-spacing:0.05em;">
              <i class="bi bi-patch-check me-1"></i> 2. Statutory Customs EDI & Tax Registration
            </h6>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label small fw-semibold">IEC Code (10 Chars) <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm text-uppercase font-monospace" id="formIec" required maxlength="10" placeholder="e.g. ABEFR3842G">
              </div>
              <div class="col-md-2">
                <label class="form-label small fw-semibold">Branch No</label>
                <input type="text" class="form-control form-control-sm font-monospace" id="formIecBranch" value="0" maxlength="3" placeholder="0">
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold">GSTIN (15 Chars) <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm text-uppercase font-monospace" id="formGstin" required maxlength="15" placeholder="e.g. 08ABEFR3842G1Z5">
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-semibold">PAN / Tax ID</label>
                <input type="text" class="form-control form-control-sm text-uppercase font-monospace" id="formPan" maxlength="10" placeholder="e.g. ABEFR3842G">
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-semibold">AD Code (7 Digits)</label>
                <input type="text" class="form-control form-control-sm font-monospace" id="formAdCode" maxlength="10" placeholder="e.g. 6390013">
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-semibold">Operational Status</label>
                <select class="form-select form-select-sm" id="formStatus">
                  <option value="Active">Active (Ready for Docs)</option>
                  <option value="Inactive">Inactive / Suspended</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Section 3: Authorized Bank & AD Details -->
          <div class="mb-3 pt-2 border-top">
            <h6 class="fw-bold text-primary mb-3 text-uppercase" style="font-size:0.75rem; letter-spacing:0.05em;">
              <i class="bi bi-bank me-1"></i> 3. Authorized Dealer Forex Bank Details
            </h6>
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label small fw-semibold">Authorized Bank Name, Branch & Account No</label>
                <input type="text" class="form-control form-control-sm" id="formForexBank" placeholder="e.g. ICICI Bank Limited, Jodhpur Branch (A/C: ICIC0006827)">
              </div>
            </div>
          </div>

          <!-- Section 4: Operational Addresses -->
          <div class="mb-3 pt-2 border-top">
            <h6 class="fw-bold text-primary mb-3 text-uppercase" style="font-size:0.75rem; letter-spacing:0.05em;">
              <i class="bi bi-geo-alt me-1"></i> 4. Factory & Registered Addresses
            </h6>
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label small fw-semibold">Registered Office Address <span class="text-danger">*</span></label>
                <textarea class="form-control form-control-sm" id="formRegisteredAddress" rows="2" required placeholder="Street address, City, District, State, PIN Code"></textarea>
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Factory / Manufacturing Works Address</label>
                <textarea class="form-control form-control-sm" id="formFactoryAddress" rows="2" placeholder="Factory premises, Highway / GIDC, Gujarat - 363642"></textarea>
              </div>
            </div>
          </div>

          <!-- Section 5: Authorized Contact Details -->
          <div class="pt-2 border-top">
            <h6 class="fw-bold text-primary mb-3 text-uppercase" style="font-size:0.75rem; letter-spacing:0.05em;">
              <i class="bi bi-person-lines-fill me-1"></i> 5. Authorized Contact Person
            </h6>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label small fw-semibold">Contact Person</label>
                <input type="text" class="form-control form-control-sm" id="formContactPerson" placeholder="e.g. Rajesh B. Patel">
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-semibold">Direct Phone / Mobile</label>
                <input type="text" class="form-control form-control-sm" id="formContactPhone" placeholder="e.g. +91 2822 240890">
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-semibold">Official Email</label>
                <input type="email" class="form-control form-control-sm" id="formContactEmail" placeholder="e.g. export@reliancegranito.com">
              </div>
            </div>
          </div>

        </div>

        <!-- Modal Footer -->
        <div class="modal-footer py-2 bg-light border-top">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold" id="btnSaveConsigner">
            <i class="bi bi-check-lg me-1"></i> Save Consigner
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

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
            <div class="text-white-50 small" id="viewModalSubTitle">IEC & Customs Master Record</div>
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
          <button type="button" class="btn btn-outline-secondary btn-sm me-1" id="viewModalEditBtn" onclick="openEditFromViewModal()">
            <i class="bi bi-pencil me-1"></i> Edit
          </button>
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
        <h6 class="fw-bold mb-1 text-dark">Delete Consigner?</h6>
        <p class="text-muted small mb-3">Are you sure you want to delete <strong id="deleteConsignerName">this firm</strong>? This will remove its profile from the EDMS master directory.</p>
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

<!-- Toast Container for Notifications -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11000;">
  <div id="consignerToast" class="toast align-items-center text-white bg-dark border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body d-flex align-items-center gap-2">
        <i class="bi bi-info-circle-fill text-warning fs-5" id="toastIcon"></i>
        <span id="toastMessage">Consigner updated successfully</span>
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

<?php
include_once __DIR__.'/includes/footer.php';
?>

<!-- Consigners Master Management Script -->
<script src="js/consigners.js"></script>
