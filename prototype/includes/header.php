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
  <link rel="stylesheet" href="css/style.css?v=<?= time() ?>">
</head>
<body>
  <div class="app-container">
    <!-- Common Sidebar Component -->
    <?php include_once __DIR__ . '/sidebar.php'; ?>

    <!-- Main Workspace Area -->
    <div class="main-wrapper">
      <!-- Top Navigation Bar -->
      <header class="top-navbar">
        <div class="d-flex align-items-center gap-3">
          <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle Navigation">
            <i class="bi bi-list fs-4"></i>
          </button>
          <div class="d-none d-md-flex align-items-center gap-2" style="font-size:0.84rem; color:#64748B;">
            <i class="bi bi-building-gear text-primary"></i>
            <span style="font-weight:600; color:#0F172A;">Jodhpur Export Office & Morbi Ceramic Facility</span>
          </div>
        </div>

        <!-- Right Side: User Profile & Sign Out -->
        <div class="top-actions ms-auto">
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
