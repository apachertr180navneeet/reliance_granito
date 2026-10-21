<?php
// Reliance Granito EDMS - Common Header Component
if (!isset($pageTitle)) {
    $pageTitle = "Reliance Granito | Export Documentation Management System (EDMS)";
}
$hideSidebar = $hideSidebar ?? false;
$activeNav = $activeNav ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="<?= $hideSidebar ? 'no-sidebar' : '' ?>">
  <div class="app-container <?= $hideSidebar ? 'no-sidebar' : '' ?>">
    <?php if (!$hideSidebar): ?>
      <?php include_once __DIR__ . '/sidebar.php'; ?>
    <?php endif; ?>

    <!-- Main Workspace Area -->
    <div class="main-wrapper <?= $hideSidebar ? 'full-width' : '' ?>">
      <!-- Top Navigation Bar -->
      <header class="top-navbar">
        <div class="d-flex align-items-center gap-2">
          <?php if (!$hideSidebar): ?>
            <button class="btn btn-sm btn-light border" id="sidebarToggleBtn" type="button" title="Toggle Sidebar">
              <i class="bi bi-layout-sidebar-inset" style="font-size:1.1rem; color:#2563EB;"></i>
            </button>
          <?php endif; ?>

          <div class="d-flex align-items-center gap-2">
            <div class="brand-logo-icon" style="width:36px; height:36px; font-size:1rem; border-radius:8px;">RG</div>
            <div>
              <div style="font-size:1.05rem; font-weight:800; line-height:1.1; color:#0F172A;">Reliance Granito</div>
              <div style="font-size:0.68rem; color:#D97706; font-weight:700; text-transform:uppercase; letter-spacing:0.04em;">Export EDMS Enterprise</div>
            </div>
          </div>
        </div>

        <!-- Center Search Box -->
        <div class="top-search-box d-none d-md-flex">
          <i class="bi bi-search" style="opacity:0.6;"></i>
          <input type="text" id="globalSearch" placeholder="Search Shipment #, Buyer, Container, Seal..." oninput="handleGlobalSearch(this.value)">
        </div>

        <!-- Right Side Quick Actions & User Profile -->
        <div class="top-actions">
          <div class="currency-badge d-none d-lg-flex">
            <i class="bi bi-currency-dollar"></i>
            <span>USD / INR 86.85</span>
          </div>

          <div class="morbi-facility-status d-none d-xl-flex py-1 px-2" style="font-size:0.75rem;">
            <div class="status-dot-pulse me-2"></div>
            <div>
              <div style="font-size:0.72rem; font-weight:700; color:#0F172A;">Morbi Facility</div>
              <div style="font-size:0.62rem; color:#64748B;">IEC Verified</div>
            </div>
          </div>

          <button type="button" class="btn btn-primary btn-sm" onclick="openCreateShipmentModal()">
            <i class="bi bi-plus-lg"></i>
            <span>+ Create Shipment</span>
          </button>

          <!-- User Profile Display & Sign Out -->
          <div class="user-profile-badge" onclick="window.location.href='login.php'" title="Click to Switch User / Sign Out">
            <div class="user-avatar" id="navUserAvatar">RP</div>
            <div class="user-details d-none d-sm-block">
              <div class="user-name" id="navUserName">Rajesh B. Patel</div>
              <div class="user-role" id="navUserRole">Export Manager</div>
            </div>
            <i class="bi bi-box-arrow-right ms-1 text-danger" style="font-size:0.85rem;" title="Sign Out"></i>
          </div>
        </div>
      </header>
