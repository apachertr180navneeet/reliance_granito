<?php
// Reliance Granito EDMS - Common Header Component (Full Width Top Navigation - No Sidebar)
if (!isset($pageTitle)) {
    $pageTitle = "Reliance Granito | Export Documentation Management System (EDMS)";
}
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
<body>
  <div class="app-container" style="display:flex; flex-direction:column; min-height:100vh;">
    <!-- Main Top Navbar (Full Width Navigation & Brand Header - No Sidebar) -->
    <header class="top-navbar">
      <!-- Left: Brand Logo & Title -->
      <div class="d-flex align-items-center gap-3">
        <div class="brand-logo-icon">RG</div>
        <div class="brand-info me-3">
          <h1 style="font-size:1.15rem; font-weight:800; margin-bottom:0; color:#0F172A;">Reliance Granito</h1>
          <span style="font-size:0.7rem; color:#D97706; font-weight:700; text-transform:uppercase; letter-spacing:0.06em;">Export Documentation Management System (EDMS)</span>
        </div>

        <!-- Desktop Quick Nav Pills -->
        <nav class="d-none d-lg-flex align-items-center gap-2">
          <a href="index.php#overviewSection" class="btn btn-sm" style="background:#EFF6FF; color:#1D4ED8; font-weight:700; border:1px solid #BFDBFE;">
            <i class="bi bi-speedometer2"></i> Dashboard
          </a>
          <button type="button" class="btn btn-sm btn-secondary" onclick="openShipmentDetailModal('SHP-1469')">
            <i class="bi bi-star-fill text-warning"></i> Primary SHP-1469 Hub
          </button>
          <button type="button" class="btn btn-sm btn-secondary" onclick="openDiscrepancyModal('SHP-1469')">
            <i class="bi bi-shield-check text-danger"></i> 3-Way Validation
            <span class="badge badge-discrepancy ms-1" id="topNavMismatchBadge">1 Mismatch</span>
          </button>
          <div class="dropdown">
            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-folder2-open text-primary"></i> Export Documents
            </button>
            <ul class="dropdown-menu shadow-sm" style="font-size:0.84rem;">
              <li><a class="dropdown-item" href="javascript:void(0)" onclick="openDocViewer('invoice')"><i class="bi bi-receipt me-2 text-primary"></i> Commercial Invoice (INV-1469)</a></li>
              <li><a class="dropdown-item" href="javascript:void(0)" onclick="openDocViewer('shipping-bill')"><i class="bi bi-file-earmark-ruled me-2 text-success"></i> Shipping Bill (ICEGATE 6-Page)</a></li>
              <li><a class="dropdown-item" href="javascript:void(0)" onclick="openDocViewer('bill-of-lading')"><i class="bi bi-compass me-2 text-warning"></i> Multimodal Bill of Lading (HIDE Masking)</a></li>
            </ul>
          </div>
        </nav>
      </div>

      <!-- Right: Search, Facility, Currency, Actions & User Profile -->
      <div class="top-actions">
        <div class="top-search-box d-none d-md-flex">
          <i class="bi bi-search" style="opacity:0.6;"></i>
          <input type="text" id="globalSearch" placeholder="Search Shipment #, Buyer, Container, Seal..." oninput="handleGlobalSearch(this.value)">
        </div>

        <div class="currency-badge d-none d-xl-flex">
          <i class="bi bi-currency-dollar"></i>
          <span>USD / INR 86.85</span>
        </div>

        <div class="morbi-facility-status d-none d-xl-flex py-1 px-2" style="font-size:0.75rem;">
          <div class="status-dot-pulse me-2"></div>
          <div>
            <div style="font-size:0.74rem; font-weight:700; color:#0F172A;">Morbi Ceramic Facility</div>
            <div style="font-size:0.65rem; color:#64748B;">IEC: ABEFR3842G Verified</div>
          </div>
        </div>

        <button type="button" class="btn btn-primary btn-sm" onclick="openCreateShipmentModal()">
          <i class="bi bi-plus-lg"></i>
          <span>+ Create Shipment</span>
        </button>

        <!-- User Profile Display & Sign Out -->
        <div class="user-profile-badge" onclick="window.location.href='login.php'" title="Sign Out / Switch User">
          <div class="user-avatar" id="navUserAvatar">RP</div>
          <div class="user-details d-none d-md-block">
            <div class="user-name" id="navUserName">Rajesh B. Patel</div>
            <div class="user-role" id="navUserRole">Export Manager</div>
          </div>
          <i class="bi bi-box-arrow-right ms-1 text-danger" style="font-size:0.85rem;" title="Sign Out"></i>
        </div>
      </div>
    </header>

    <!-- Main Workspace (Full Width) -->
    <div class="main-wrapper" style="margin-left:0 !important; width:100%;">
