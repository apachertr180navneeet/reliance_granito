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
    <button type="button" class="btn-close ms-auto d-lg-none sidebar-close-btn" id="sidebarCloseBtn" aria-label="Close Sidebar"></button>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-category">Main Operations</div>
    <a href="index.php" class="nav-link <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
      <i class="bi bi-speedometer2"></i>
      <span>Dashboard & Tower</span>
    </a>
    <a href="pdf-editor.php" class="nav-link <?= $activeNav === 'pdf-editor' ? 'active' : '' ?>">
      <i class="bi bi-file-earmark-diff text-primary"></i>
      <span>PDF Visual Editor</span>
    </a>
    <a href="consigners.php" class="nav-link <?= $activeNav === 'consigners' ? 'active' : '' ?>">
      <i class="bi bi-building-up text-primary"></i>
      <span>Consigners (Consignor)</span>
    </a>
    <a href="login.php" class="nav-link text-danger nav-link-signout">
      <i class="bi bi-box-arrow-right text-danger"></i>
      <span>Sign Out / Lock</span>
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="morbi-facility-status">
      <div class="d-flex align-items-center gap-2">
        <div class="status-dot-pulse"></div>
        <div>
          <div class="facility-status-title">Jodhpur / Morbi Facility</div>
          <div class="facility-status-sub">IEC: ABEFR3842G Verified</div>
        </div>
      </div>
      <span class="facility-status-badge">Active</span>
    </div>
  </div>
</aside>
