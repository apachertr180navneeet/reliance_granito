<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Reliance Granito | Export Documentation Management System (EDMS)' }}</title>
    
    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3.3 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Centralized EDMS Application Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ time() }}">
    
    @stack('styles')
</head>
<body class="@yield('body-class')">
    <div class="app-container">
        <!-- Sidebar Navigation Drawer -->
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
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard & Tower</span>
                </a>
                <a href="{{ route('pdf-editor.index') }}" class="nav-link {{ request()->routeIs('pdf-editor.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-diff text-primary"></i>
                    <span>PDF Visual Editor</span>
                </a>
                <a href="{{ route('consigners.index') }}" class="nav-link {{ request()->routeIs('consigners.*') ? 'active' : '' }}">
                    <i class="bi bi-building-up text-primary"></i>
                    <span>Consigners (Consignor)</span>
                </a>
                <a href="{{ route('logout') }}" class="nav-link text-danger nav-link-signout">
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
        
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <!-- Main Workspace Area -->
        <div class="main-wrapper">
            <!-- Top Navigation Bar -->
            <header class="top-navbar">
                <div class="d-flex align-items-center gap-3">
                    <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle Navigation">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                    <div class="d-none d-md-flex align-items-center gap-2 top-facility-meta">
                        <i class="bi bi-building-gear text-primary"></i>
                        <span class="top-facility-title">Jodhpur Export Office & Morbi Ceramic Facility</span>
                    </div>
                </div>

                <!-- Right Side: User Profile & Actions -->
                <div class="top-actions ms-auto d-flex align-items-center gap-3">

                    <div class="user-profile-badge" onclick="window.location.href='{{ route('logout') }}'" title="Click to Sign Out">
                        <div class="user-avatar" id="navUserAvatar">{{ auth()->user()->avatar ?? 'RP' }}</div>
                        <div class="user-details d-none d-sm-block">
                            <div class="user-name" id="navUserName">{{ auth()->user()->name ?? 'Rajesh B. Patel' }}</div>
                            <div class="user-role" id="navUserRole">{{ auth()->user()->role_title ?? 'Export Documentation Manager' }}</div>
                        </div>
                        <i class="bi bi-box-arrow-right text-danger ms-1 top-signout-icon" title="Sign Out"></i>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            @yield('content')

            <!-- Professional Clean Footer -->
            <footer class="app-footer">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 w-100">
                    <div class="d-flex align-items-center gap-2">
                        <span>&copy; {{ date('Y') }} <strong>Reliance Granito Private Limited</strong></span>
                        <span class="d-none d-md-inline">&bull; Export Documentation Management System (EDMS)</span>
                    </div>
                    <div class="text-muted small">
                        <span>Powered by <strong>Laravel 12 & MySQL</strong></span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Global Toast Container for Notifications -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11000;">
        <div id="globalToast" class="toast align-items-center text-white bg-dark border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle-fill text-warning fs-5" id="globalToastIcon"></i>
                    <span id="globalToastMessage">System message</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JavaScript Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Setup CSRF header for all fetch requests
        window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Mobile Sidebar Toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('appSidebar');
        const sidebarBackdrop = document.getElementById('sidebarBackdrop');
        const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');

        function toggleSidebar() {
            sidebar?.classList.toggle('show');
            sidebarBackdrop?.classList.toggle('show');
        }

        mobileMenuBtn?.addEventListener('click', toggleSidebar);
        sidebarBackdrop?.addEventListener('click', toggleSidebar);
        sidebarCloseBtn?.addEventListener('click', toggleSidebar);

        // Global Toast Notification Helper
        window.showToast = function(message, type = 'info') {
            const toastEl = document.getElementById('globalToast');
            const msgEl = document.getElementById('globalToastMessage');
            const iconEl = document.getElementById('globalToastIcon');
            if (!toastEl || !msgEl) return;

            msgEl.textContent = message;

            if (type === 'success') {
                iconEl.className = 'bi bi-check-circle-fill text-success fs-5';
            } else if (type === 'danger' || type === 'error') {
                iconEl.className = 'bi bi-exclamation-octagon-fill text-danger fs-5';
            } else if (type === 'warning') {
                iconEl.className = 'bi bi-exclamation-triangle-fill text-warning fs-5';
            } else {
                iconEl.className = 'bi bi-info-circle-fill text-primary fs-5';
            }

            const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
            toast.show();
        };

        @if(session('success'))
            document.addEventListener('DOMContentLoaded', function() {
                showToast("{{ session('success') }}", 'success');
            });
        @endif
        @if(session('error'))
            document.addEventListener('DOMContentLoaded', function() {
                showToast("{{ session('error') }}", 'danger');
            });
        @endif
    </script>

    @stack('scripts')
</body>
</html>
