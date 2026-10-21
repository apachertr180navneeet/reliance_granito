<?php
// Reliance Granito EDMS - Common Sidebar Navigation Component
$activeNav = $activeNav ?? 'dashboard';
?>
<!-- Common Sidebar Navigation Component -->
<aside class="app-sidebar" id="appSidebar">
  <div class="brand-section">
    <div class="brand-logo-icon">RG</div>
    <div class="brand-info">
      <h1>Reliance Granito</h1>
      <span>Export EDMS Enterprise</span>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-category">Main Operations</div>
    <a href="index.php#overviewSection" class="nav-link <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
      <i class="bi bi-speedometer2"></i>
      <span>Dashboard & Tower</span>
    </a>
    <a href="index.php#shipmentsSection" class="nav-link">
      <i class="bi bi-box-seam"></i>
      <span>Export Consignments</span>
      <span class="nav-badge blue">3 Live</span>
    </a>
    <a href="javascript:void(0)" onclick="openShipmentDetailModal('SHP-1469')" class="nav-link" style="background:#EFF6FF; border-left:3px solid #2563EB;">
      <i class="bi bi-star-fill" style="color:#D97706;"></i>
      <span style="font-weight:700; color:#1D4ED8;">Active SHP-1469 Hub</span>
      <span class="nav-badge gold">Primary</span>
    </a>

    <div class="nav-category">Validation & Documents</div>
    <a href="javascript:void(0)" onclick="openDiscrepancyModal('SHP-1469')" class="nav-link">
      <i class="bi bi-shield-check"></i>
      <span>3-Way Validation</span>
      <span class="nav-badge danger" id="sidebarMismatchBadge">1 Mismatch</span>
    </a>
    <a href="javascript:void(0)" onclick="openDocViewer('invoice')" class="nav-link">
      <i class="bi bi-receipt"></i>
      <span>Commercial Invoice</span>
      <span class="nav-badge">Original / Edit</span>
    </a>
    <a href="javascript:void(0)" onclick="openDocViewer('shipping-bill')" class="nav-link">
      <i class="bi bi-file-earmark-ruled"></i>
      <span>Shipping Bill (6-Page)</span>
      <span class="nav-badge">ICEGATE</span>
    </a>
    <a href="javascript:void(0)" onclick="openDocViewer('bill-of-lading')" class="nav-link">
      <i class="bi bi-compass"></i>
      <span>Bill of Lading & HIDE</span>
      <span class="nav-badge gold">Masking</span>
    </a>

    <div class="nav-category">System Session</div>
    <a href="index.php#auditSection" class="nav-link">
      <i class="bi bi-clock-history"></i>
      <span>Audit Trail & Logs</span>
    </a>
    <a href="login.php" class="nav-link text-danger" style="margin-top:12px;">
      <i class="bi bi-box-arrow-right text-danger"></i>
      <span>Sign Out / Lock</span>
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="morbi-facility-status">
      <div style="display:flex; align-items:center; gap:8px;">
        <div class="status-dot-pulse"></div>
        <div>
          <div style="font-size:0.75rem; font-weight:700; color:#0F172A;">Jodhpur / Morbi Facility</div>
          <div style="font-size:0.68rem; color:var(--text-dim);">IEC: ABEFR3842G Verified</div>
        </div>
      </div>
      <span style="font-size:0.7rem; font-weight:600; color:var(--accent-emerald);">Active</span>
    </div>
  </div>
</aside>
