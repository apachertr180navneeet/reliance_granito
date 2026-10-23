<?php
// Reliance Granito Export Documentation Management System (EDMS)
// Master Control Tower Dashboard
$pageTitle = 'Reliance Granito | Export Documentation Management System (EDMS) - Dashboard';
$activeNav = 'dashboard';
include_once __DIR__.'/includes/header.php';
?>

<!-- Main Content Area -->
<!-- Main Content Area -->
<main class="content-area p-3 p-lg-4" id="overviewSection">
  
  <!-- Welcome Banner -->
  <div class="card border-0 shadow-sm mb-4 dashboard-hero-card">
    <div class="card-body p-4">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
          <span class="badge bg-warning text-dark fw-bold mb-2 text-uppercase tracking-wider">Active Export Docket</span>
          <h3 class="fw-bold mb-1">Reliance Granito &bull; Export Control Tower</h3>
          <p class="text-white-50 mb-0">Consignment #1469 &bull; Jodhpur Export Office & Morbi Ceramic Facility &bull; Destination: Haiphong Port, Vietnam</p>
        </div>
        <div>
          <a href="pdf-editor.php" class="btn btn-warning fw-bold d-flex align-items-center gap-2 shadow-sm px-4 py-2">
            <i class="bi bi-file-earmark-diff fs-5"></i>
            <span>Launch PDF Visual Editor</span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Operational Action Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-6 col-lg-3">
      <div class="card border-0 shadow-sm h-100 dashboard-action-card">
        <div class="card-body p-3">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="text-muted small fw-semibold">PRIMARY WORKFLOW</span>
            <span class="badge bg-primary-subtle text-primary">PDF Tool</span>
          </div>
          <h5 class="fw-bold mb-1">PDF Corrections</h5>
          <p class="text-muted small mb-3">Upload export PDFs, draw whiteout masks to hide sensitive prices, replace text values, and download updated copies.</p>
          <a href="pdf-editor.php" class="btn btn-outline-primary btn-sm w-100">
            Open PDF Editor &rarr;
          </a>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-lg-3">
      <div class="card border-0 shadow-sm h-100 dashboard-action-card">
        <div class="card-body p-3">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="text-muted small fw-semibold">PARTY DIRECTORY</span>
            <span class="badge bg-primary-subtle text-primary">CRUD Master</span>
          </div>
          <h5 class="fw-bold mb-1">Consigners Master</h5>
          <p class="text-muted small mb-3">Manage export consignors, IEC licenses, GSTIN, factory works addresses in Morbi & Jodhpur, and authorized banks.</p>
          <a href="consigners.php" class="btn btn-primary btn-sm w-100">
            Manage Consigners &rarr;
          </a>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-lg-3">
      <div class="card border-0 shadow-sm h-100 dashboard-action-card">
        <div class="card-body p-3">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="text-muted small fw-semibold">ACTIVE DOCKET</span>
            <span class="badge bg-success-subtle text-success">Verified</span>
          </div>
          <h5 class="fw-bold mb-1">Consignment 1469</h5>
          <p class="text-muted small mb-3">Shipping Bill, Commercial Invoice & BL for CJ Stone Co. Ltd (Vietnam). Ready for review and redaction.</p>
          <a href="pdf-editor.php" class="btn btn-outline-secondary btn-sm w-100">
            Open PDF Editor &rarr;
          </a>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-lg-3">
      <div class="card border-0 shadow-sm h-100 dashboard-action-card">
        <div class="card-body p-3">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="text-muted small fw-semibold">COMPLIANCE</span>
            <span class="badge bg-info-subtle text-info">EDI Standard</span>
          </div>
          <h5 class="fw-bold mb-1">ICEGATE & Customs</h5>
          <p class="text-muted small mb-3">Export Inspection Agency, Mundra Port Gateway, and RFID electronic seal verification dockets.</p>
          <a href="pdf-editor.php" class="btn btn-outline-secondary btn-sm w-100">
            Open PDF Editor &rarr;
          </a>
        </div>
      </div>
    </div>
  </div>

</main>


<?php
include_once __DIR__.'/includes/footer.php';
?>
