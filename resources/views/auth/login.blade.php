<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reliance Granito | Export Documentation Management System - Sign In</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3.3 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body style="display:flex; align-items:center; justify-content:center; min-height:100vh; background: radial-gradient(circle at top right, #EEF2F6 0%, #F8FAFC 50%, #E2E8F0 100%); padding:20px;">

    <div style="width:100%; max-width:460px;">
        <!-- Brand Header -->
        <div style="text-align:center; margin-bottom:24px;">
            <div style="width:68px; height:68px; border-radius:16px; background:linear-gradient(135deg, #1D4ED8 0%, #2563EB 100%); display:inline-flex; align-items:center; justify-content:center; font-size:1.85rem; font-weight:800; color:#FFFFFF; box-shadow:0 10px 25px rgba(37,99,235,0.28); margin-bottom:14px;">
                RG
            </div>
            <h2 style="font-size:1.7rem; font-weight:800; color:#0F172A; margin-bottom:4px; letter-spacing:-0.02em;">Reliance Granito</h2>
            <div style="font-size:0.82rem; color:#D97706; font-weight:700; text-transform:uppercase; letter-spacing:0.08em;">
                Export Documentation Management System (EDMS)
            </div>
            <div style="font-size:0.78rem; color:#64748B; margin-top:4px;">
                Jodhpur Export Head Office & Morbi Ceramic Facility
            </div>
        </div>

        <!-- Login Card -->
        <div class="card" style="box-shadow: 0 12px 30px -4px rgba(0, 0, 0, 0.08), 0 4px 6px -4px rgba(0, 0, 0, 0.04) !important; border: 1px solid #E2E8F0 !important; background-color: #FFFFFF !important; border-radius: 16px;">
            <div class="card-body" style="padding:32px;">
                <div class="mb-4">
                    <h4 style="font-size:1.25rem; font-weight:800; color:#0F172A; margin-bottom:3px;">Account Sign In</h4>
                    <p style="font-size:0.82rem; color:#64748B; margin-bottom:0;">Sign in to access export dockets and the visual PDF editor</p>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger py-2 px-3 small d-flex align-items-center gap-2 mb-3 rounded-3">
                        <i class="bi bi-exclamation-triangle-fill fs-6"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" id="loginForm">
                    @csrf
                    <!-- Email / Username Input -->
                    <div class="mb-3">
                        <label class="form-label" style="font-size:0.84rem; font-weight:600; color:#1E293B !important;">Export Officer Email</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background:#F8FAFC; border-color:#CBD5E1; color:#2563EB;">
                                <i class="bi bi-person-badge"></i>
                            </span>
                            <input type="email" name="email" class="form-control" id="loginUsername" value="{{ old('email', 'admin@reliancegranito.com') }}" required placeholder="name@reliancegranito.com" style="background:#FFFFFF !important; color:#0F172A !important; border-color:#CBD5E1 !important; font-size:0.9rem;">
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label mb-0" style="font-size:0.84rem; font-weight:600; color:#1E293B !important;">Password</label>
                            <span class="text-muted" style="font-size:0.76rem;">Default: <code class="text-primary fw-bold">Reliance@2026</code></span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text" style="background:#F8FAFC; border-color:#CBD5E1; color:#2563EB;">
                                <i class="bi bi-shield-lock"></i>
                            </span>
                            <input type="password" name="password" class="form-control" id="loginPassword" value="Reliance@2026" required style="background:#FFFFFF !important; color:#0F172A !important; border-color:#CBD5E1 !important; font-size:0.9rem;">
                            <button class="btn btn-secondary btn-sm" type="button" onclick="togglePasswordVisibility()" style="border-color:#CBD5E1; background:#F8FAFC; color:#64748B;">
                                <i class="bi bi-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember me -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pt-1" style="font-size:0.82rem;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="rememberMe" checked style="background-color:#FFFFFF; border-color:#CBD5E1;">
                            <label class="form-check-label" for="rememberMe" style="color:#334155 !important; font-weight:500; cursor:pointer;">Keep me signed in</label>
                        </div>
                        <span class="text-muted small">
                            <i class="bi bi-shield-check text-success"></i> ICEGATE Secured
                        </span>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2 fw-bold shadow-sm mb-2" style="font-size:0.95rem;">
                        <span>Sign In to Control Tower</span>
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </form>



            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const pwd = document.getElementById('loginPassword');
            const icon = document.getElementById('togglePasswordIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                pwd.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }
    </script>
</body>
</html>
