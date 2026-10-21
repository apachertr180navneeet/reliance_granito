<?php
// Reliance Granito EDMS - Common Sidebar Navigation Component
$activeNav = $activeNav ?? 'dashboard';
?>
<!-- Common Sidebar Navigation Component -->
<aside class="app-sidebar" id="appSidebar">
  <!-- Brand Identity -->
  <div class="brand-section">
    <div class="brand-logo-icon">RG</div>
    <div class="brand-info">
      <div class="d-flex align-items-center justify-content-between">
        <h1>Reliance Granito</h1>
      </div>
      <span>Export EDMS Enterprise</span>
    </div>
  </div>

  <!-- Navigation Links -->
  <nav class="sidebar-nav">
    <div class="nav-category">Core Operations</div>
    <a href="index.php#overviewSection" class="nav-link <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
      <i class="bi bi-speedometer2"></i>
      <span>Dashboard & Tower</span>
      <span class="nav-badge blue">Live</span>
    </a>
    <a href="index.php#shipmentsSection" class="nav-link">
      <i class="bi bi-boxes"></i>
      <span>Export Consignments</span>
      <span class="nav-badge">3 Total</span>
    </a>
    <a href="javascript:void(0)" onclick="if(typeof openShipmentDetailModal==='function') openShipmentDetailModal('SHP-1469')" class="nav-link" style="background:#EFF6FF; border-left:3px solid #2563EB;">
      <i class="bi bi-star-fill text-warning"></i>
      <span style="font-weight:700; color:#1D4ED8;">Active SHP-1469 Hub</span>
    </a>

    <div class="nav-category">Document Engine</div>
    <a href="javascript:void(0)" onclick="if(typeof openDocViewer==='function') openDocViewer('invoice')" class="nav-link">
      <i class="bi bi-receipt"></i>
      <span>Commercial Invoice</span>
    </a>
    <a href="javascript:void(0)" onclick="if(typeof openDocViewer==='function') openDocViewer('shipping-bill')" class="nav-link">
      <i class="bi bi-file-earmark-ruled"></i>
      <span>Shipping Bill (6-Page)</span>
    </a>
    <a href="javascript:void(0)" onclick="if(typeof openDocViewer==='function') openDocViewer('bill-of-lading')" class="nav-link">
      <i class="bi bi-compass"></i>
      <span>Bill of Lading & HIDE</span>
    </a>

    <div class="nav-category">Audit & Compliance</div>
    <a href="javascript:void(0)" onclick="if(typeof openDiscrepancyModal==='function') openDiscrepancyModal('SHP-1469')" class="nav-link">
      <i class="bi bi-shield-check"></i>
      <span>3-Way Validation</span>
      <span class="nav-badge danger">1 Mismatch</span>
    </a>
    <a href="index.php#auditSection" class="nav-link">
      <i class="bi bi-clock-history"></i>
      <span>Audit Trail & History</span>
    </a>
  </nav>

  <!-- Facility Status & Session Exit -->
  <div class="sidebar-footer">
    <div class="morbi-facility-status mb-2">
      <div style="display:flex; align-items:center; gap:10px;">
        <div class="status-dot-pulse"></div>
        <div>
          <div style="font-size:0.76rem; font-weight:700; color:#0F172A;">Jodhpur & Morbi Facility</div>
          <div style="font-size:0.67rem; color:var(--text-dim);">IEC: ABEFR3842G Verified</div>
        </div>
      </div>
      <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:0.68rem; font-weight:700;">Active</span>
    </div>

    <a href="login.php" class="nav-link text-danger py-2 px-2" style="border-radius:6px; background:#FEF2F2;">
      <i class="bi bi-box-arrow-right text-danger"></i>
      <span style="font-weight:600;">Sign Out / Switch User</span>
    </a>
  </div>
</aside>
