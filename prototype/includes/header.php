<?php
// Reliance Granito EDMS - Common Header Component
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
  <div class="app-container">
    <!-- Common Sidebar Component -->
    <?php include_once __DIR__ . '/sidebar.php'; ?>

    <!-- Main Workspace Area -->
    <div class="main-wrapper">
      <!-- Top Navigation Bar (High-End Enterprise Header) -->
      <header class="top-navbar">
        <!-- Left: Mobile Menu & Breadcrumb -->
        <div class="d-flex align-items-center gap-3">
          <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle Navigation">
            <i class="bi bi-list fs-4"></i>
          </button>
          
          <div class="d-flex align-items-center gap-2">
            <div class="d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-3" style="width:36px; height:36px;">
              <i class="bi bi-layers-half fs-5"></i>
            </div>
            <div>
              <div style="font-size:0.88rem; font-weight:700; color:#0F172A; line-height:1.2;">
                Export Documentation Control Tower
              </div>
              <div class="d-flex align-items-center gap-2" style="font-size:0.72rem; color:#64748B;">
                <span>Morbi Facility &bull; Jodhpur Export HQ</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle py-0 px-1" style="font-size:0.65rem;">
                  <i class="bi bi-circle-fill me-1" style="font-size:0.45rem;"></i>Port INMAA1 Active
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Center: Quick Global Search -->
        <div class="top-search-box d-none d-lg-flex">
          <i class="bi bi-search text-muted"></i>
          <input type="text" id="globalSearch" placeholder="Search Shipment #, Buyer, Container, Seal..." oninput="if(typeof handleGlobalSearch==='function') handleGlobalSearch(this.value)">
          <span class="badge bg-light text-muted border py-1 px-1.5" style="font-size:0.65rem;">Ctrl K</span>
        </div>

        <!-- Right: Actions, Benchmarks & User Profile -->
        <div class="top-actions ms-auto">
          <!-- USD/INR Benchmark Pill -->
          <div class="currency-badge d-none d-md-flex" title="Live RBI Reference Exchange Benchmark">
            <i class="bi bi-currency-dollar"></i>
            <span>USD / INR ₹86.85</span>
          </div>

          <!-- Notification Bell with Pending Discrepancy Alert -->
          <button type="button" class="btn btn-light btn-sm position-relative border" style="width:38px; height:38px; border-radius:50%;" onclick="if(typeof openDiscrepancyModal==='function') openDiscrepancyModal('SHP-1469')" title="Active Compliance Alert">
            <i class="bi bi-bell text-secondary fs-6"></i>
            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
              <span class="visually-hidden">Action Required</span>
            </span>
          </button>

          <!-- User Profile & Sign Out -->
          <div class="user-profile-badge" onclick="window.location.href='login.php'" title="Click to Sign Out">
            <div class="user-avatar" id="navUserAvatar">RP</div>
            <div class="user-details d-none d-sm-block">
              <div class="user-name" id="navUserName">Rajesh B. Patel</div>
              <div class="user-role" id="navUserRole">Export Documentation Manager</div>
            </div>
            <i class="bi bi-box-arrow-right text-danger ms-1" style="font-size:0.9rem;" title="Sign Out"></i>
          </div>
        </div>
      </header>
