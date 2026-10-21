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
