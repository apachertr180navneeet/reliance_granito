<?php
// Reliance Granito Export Documentation Management System (EDMS)
// Single Sign-In Authentication Page
$pageTitle = 'Reliance Granito | Export Documentation Management System - Sign In';
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
<body style="display:flex; align-items:center; justify-content:center; min-height:100vh; background: radial-gradient(circle at top right, #EEF2F6 0%, #F8FAFC 50%, #E2E8F0 100%); padding:20px;">

  <div style="width:100%; max-width:440px;">
    <!-- Brand Header -->
    <div style="text-align:center; margin-bottom:26px;">
      <div style="width:64px; height:64px; border-radius:14px; background:linear-gradient(135deg, #1D4ED8 0%, #2563EB 100%); display:inline-flex; align-items:center; justify-content:center; font-size:1.75rem; font-weight:800; color:#FFFFFF; box-shadow:0 8px 25px rgba(37,99,235,0.25); margin-bottom:12px;">
        RG
      </div>
      <h2 style="font-size:1.6rem; font-weight:800; color:#0F172A; margin-bottom:4px; letter-spacing:-0.02em;">Reliance Granito</h2>
      <div style="font-size:0.84rem; color:#D97706; font-weight:700; text-transform:uppercase; letter-spacing:0.08em;">
        Export Documentation Management System (EDMS)
      </div>
      <div style="font-size:0.8rem; color:#64748B; margin-top:4px;">
        Jodhpur Export Head Office & Facility
      </div>
    </div>

    <!-- Login Card -->
    <div class="card" style="box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -4px rgba(0, 0, 0, 0.04) !important; border: 1px solid #E2E8F0 !important; background-color: #FFFFFF !important; border-radius: 14px;">
      <div class="card-body" style="padding:32px;">
        <h4 style="font-size:1.25rem; font-weight:800; color:#0F172A; margin-bottom:6px;">Sign In to Your Account</h4>
        <p style="font-size:0.84rem; color:#64748B; margin-bottom:24px;">Enter your export credentials to access consignments and shipping dockets</p>

        <form onsubmit="handleSingleLogin(event)">
          <!-- Email / Username Input -->
          <div class="mb-3">
            <label class="form-label" style="font-size:0.84rem; font-weight:600; color:#1E293B !important;">Email / Username</label>
            <div class="input-group">
              <span class="input-group-text" style="background:#F8FAFC; border-color:#CBD5E1; color:#2563EB;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                  <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                </svg>
              </span>
              <input type="text" class="form-control" id="loginUsername" value="admin@reliancegranito.com" required placeholder="name@reliancegranito.com" style="background:#FFFFFF !important; color:#0F172A !important; border-color:#CBD5E1 !important; font-size:0.9rem;">
            </div>
          </div>

          <!-- Password Input -->
          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label class="form-label mb-0" style="font-size:0.84rem; font-weight:600; color:#1E293B !important;">Password</label>
              <a href="javascript:void(0)" onclick="showToast('Password reset link sent to registered email.', 'warning')" style="font-size:0.78rem; color:#2563EB; font-weight:600;">Forgot password?</a>
            </div>
            <div class="input-group">
              <span class="input-group-text" style="background:#F8FAFC; border-color:#CBD5E1; color:#2563EB;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
              </span>
              <input type="password" class="form-control" id="loginPassword" value="Reliance@2026" required style="background:#FFFFFF !important; color:#0F172A !important; border-color:#CBD5E1 !important; font-size:0.9rem;">
              <button class="btn btn-secondary btn-sm" type="button" onclick="togglePasswordVisibility()" style="border-color:#CBD5E1; background:#F8FAFC; color:#64748B;">
                <i class="bi bi-eye" id="togglePasswordIcon"></i>
              </button>
            </div>
          </div>

          <!-- Remember me & Verified System Badge -->
          <div class="d-flex justify-content-between align-items-center mb-4 pt-1" style="font-size:0.82rem;">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="rememberMe" checked style="background-color:#FFFFFF; border-color:#CBD5E1;">
              <label class="form-check-label" for="rememberMe" style="color:#334155 !important; font-weight:500; cursor:pointer;">Remember me</label>
            </div>
            <span class="badge" style="background:#DCFCE7; color:#15803D; border:1px solid #BBF7D0; font-size:0.74rem; padding:4px 9px;">
              <i class="bi bi-shield-check"></i> Verified System
            </span>
          </div>

          <!-- Submit Button -->
          <button type="submit" class="btn btn-primary w-100 py-2" style="font-size:0.92rem; font-weight:700;">
            <span>Sign In to Export System</span>
            <i class="bi bi-arrow-right"></i>
          </button>
        </form>

        <div style="text-align:center; margin-top:22px; padding-top:16px; border-top:1px solid #E2E8F0;">
          <button type="button" class="btn btn-secondary btn-sm" onclick="resetDemoData()" style="font-size:0.78rem;">
            <i class="bi bi-arrow-counterclockwise"></i>
            <span>Restore Demo Default Data</span>
          </button>
        </div>
      </div>
    </div>

    <div style="text-align:center; margin-top:20px; font-size:0.78rem; color:#64748B;">
      Reliance Granito LLP &bull; Export & International Trade Division &copy; 2026
    </div>
  </div>

  <script src="js/data.js"></script>
  <script src="js/app.js"></script>
  <script>
    function togglePasswordVisibility() {
      const pwd = document.getElementById("loginPassword");
      const icon = document.getElementById("togglePasswordIcon");
      if (pwd.type === "password") {
        pwd.type = "text";
        icon.className = "bi bi-eye-slash";
      } else {
        pwd.type = "password";
        icon.className = "bi bi-eye";
      }
    }

    function handleSingleLogin(e) {
      e.preventDefault();
      const email = document.getElementById("loginUsername").value;
      const data = getExportData();
      
      data.currentUser = {
        id: "USR-001",
        name: "Rajesh B. Patel",
        email: email,
        roleTitle: "Export Documentation Manager",
        avatar: "RG"
      };
      saveExportData(data);
      showToast("Signed in successfully as Rajesh B. Patel", "success");
      setTimeout(() => {
        window.location.href = "index.php";
      }, 350);
    }
  </script>
</body>
</html>
