<?php

abstract class AdminController {
    protected $admin;
    protected $currentUser;
    protected $success = null;
    protected $error = null;

    public function __construct() {
        $this->admin = new Admin();
        $this->currentUser = $this->admin->getCurrentUser();
        $this->requireAdmin();
        $this->handlePostRequests();
    }

    /**
     * Require admin access
     */
    protected function requireAdmin() {
        $this->admin->requireAdmin();
    }

    /**
     * Handle POST requests - override in child classes
     */
    protected function handlePostRequests() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processPostRequest();
        }
    }

    /**
     * Process POST request - override in child classes
     */
    protected function processPostRequest() {
        // Override in child classes
    }

    /**
     * Get current user
     */
    protected function getCurrentUser() {
        return $this->currentUser;
    }

    /**
     * Get admin instance
     */
    protected function getAdmin() {
        return $this->admin;
    }

    /**
     * Set success message
     */
    protected function setSuccess($message) {
        $this->success = $message;
    }

    /**
     * Set error message
     */
    protected function setError($message) {
        $this->error = $message;
    }

    /**
     * Get success message
     */
    public function getSuccess() {
        return $this->success;
    }

    /**
     * Get error message
     */
    public function getError() {
        return $this->error;
    }

    /**
     * Render the page
     */
    public function render() {
        $this->renderHeader();
        $this->renderContent();
        $this->renderFooter();
    }

    /**
     * Render header - override in child classes
     */
    protected function renderHeader() {
        echo '<!DOCTYPE html>';
        echo '<html lang="en" dir="ltl" data-sidebar="open" color-scheme="light">';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<title>Admin Dashboard</title>';
        $this->renderCommonAssets();
        $this->renderCommonStyles();
        
        // Theme initialization script
        echo '<script>
        // Apply theme before page renders
        (function() {
            try {
                var theme = localStorage.getItem("theme") || "light";
                var html = document.documentElement;
                var body = document.body;
                
                html.classList.remove("light-theme", "dark-theme");
                body.classList.remove("light-theme", "dark-theme");
                
                if (theme === "dark") {
                    html.classList.add("dark-theme");
                    body.classList.add("dark-theme");
                } else {
                    html.classList.add("light-theme");
                    body.classList.add("light-theme");
                }
            } catch (e) {}
        })();
        </script>';
        
        echo '</head>';
        echo '<body>';
        echo '<div class="overlay-bg" id="overlay"></div>';
        
        // Mobile Sidebar Toggle Button
        echo '<button class="mobile-sidebar-toggle" id="mobileSidebarToggle" title="Toggle Sidebar">';
        echo '<i class="bi bi-list"></i>';
        echo '</button>';
        
        $this->renderSidebar('dashboard');
        echo '<div class="main-content">';
        $this->renderNavbar('Dashboard');
        $this->renderAlerts();
    }

    /**
     * Render content - override in child classes
     */
    protected function renderContent() {
        // Override in child classes
    }

    /**
     * Render footer - override in child classes
     */
    protected function renderFooter() {
        // Override in child classes
    }

    /**
     * Get filter parameters from GET request
     */
    protected function getFilters() {
        return [
            'status' => $_GET['status'] ?? 'all',
            'search' => $_GET['search'] ?? '',
            'page' => max(1, intval($_GET['page'] ?? 1)),
            'user_id' => $_GET['user_id'] ?? null,
            'action' => $_GET['action'] ?? '',
            'admin_id' => $_GET['admin_id'] ?? null
        ];
    }

    /**
     * Build pagination links
     */
    protected function buildPaginationLinks($currentPage, $totalPages, $filters = []) {
        $links = [];
        
        if ($currentPage > 1) {
            $links[] = [
                'url' => '?' . http_build_query(array_merge($filters, ['page' => $currentPage - 1])),
                'text' => 'Previous',
                'class' => 'page-link'
            ];
        }
        
        for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++) {
            $links[] = [
                'url' => '?' . http_build_query(array_merge($filters, ['page' => $i])),
                'text' => $i,
                'class' => $i === $currentPage ? 'page-link active' : 'page-link'
            ];
        }
        
        if ($currentPage < $totalPages) {
            $links[] = [
                'url' => '?' . http_build_query(array_merge($filters, ['page' => $currentPage + 1])),
                'text' => 'Next',
                'class' => 'page-link'
            ];
        }
        
        return $links;
    }

    /**
     * Render alerts
     */
    protected function renderAlerts() {
        if ($this->success) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
            echo htmlspecialchars($this->success);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            echo '</div>';
        }

        if ($this->error) {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
            echo htmlspecialchars($this->error);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            echo '</div>';
        }
    }

    /**
     * Render sidebar
     */
    protected function renderSidebar($activePage = 'dashboard') {
        echo '<div class="d-sidebar" id="admin-sidebar">';
        echo '<div class="sidebar-logo">';
        echo '<a href="index.php">';
        echo '<i class="bi bi-shield-check"></i> Admin Panel';
        echo '</a>';
        echo '</div>';
        echo '<div class="sidebar-menu-container" data-simplebar="" style="max-height: calc(100vh - 70px); overflow: auto;">';
        echo '<ul class="sidebar-menu">';

        // Dashboard
        $activeClass = $activePage === 'dashboard' ? 'active' : '';
        echo '<li class="sidebar-menu-item">';
        echo '<a class="sidebar-menu-link ' . $activeClass . '" href="index.php">';
        echo '<span><i class="bi bi-speedometer2"></i></span>';
        echo '<p>Dashboard</p>';
        echo '</a>';
        echo '</li>';

        // Users
        $activeClass = $activePage === 'users' ? 'active' : '';
        echo '<li class="sidebar-menu-item">';
        echo '<a class="sidebar-menu-link ' . $activeClass . '" href="users.php">';
        echo '<span><i class="bi bi-people"></i></span>';
        echo '<p>Users</p>';
        echo '</a>';
        echo '</li>';

        // Deposits
        $activeClass = $activePage === 'deposits' ? 'active' : '';
        echo '<li class="sidebar-menu-item">';
        echo '<a class="sidebar-menu-link ' . $activeClass . '" href="deposits.php">';
        echo '<span><i class="bi bi-cash-coin"></i></span>';
        echo '<p>Deposits</p>';
        echo '</a>';
        echo '</li>';

        // Withdrawals
        $activeClass = $activePage === 'withdrawals' ? 'active' : '';
        echo '<li class="sidebar-menu-item">';
        echo '<a class="sidebar-menu-link ' . $activeClass . '" href="withdrawals.php">';
        echo '<span><i class="bi bi-bank"></i></span>';
        echo '<p>Withdrawals</p>';
        echo '</a>';
        echo '</li>';

        // Stocks
        $activeClass = $activePage === 'stocks' ? 'active' : '';
        echo '<li class="sidebar-menu-item">';
        echo '<a class="sidebar-menu-link ' . $activeClass . '" href="stocks.php">';
        echo '<span><i class="bi bi-graph-up"></i></span>';
        echo '<p>Stocks</p>';
        echo '</a>';
        echo '</li>';

        // Stock Investments
        $activeClass = $activePage === 'stock_investments' ? 'active' : '';
        echo '<li class="sidebar-menu-item">';
        echo '<a class="sidebar-menu-link ' . $activeClass . '" href="stock_investments.php">';
        echo '<span><i class="bi bi-pie-chart"></i></span>';
        echo '<p>Stock Investments</p>';
        echo '</a>';
        echo '</li>';

        // Trading History
        $activeClass = $activePage === 'trading_history' ? 'active' : '';
        echo '<li class="sidebar-menu-item">';
        echo '<a class="sidebar-menu-link ' . $activeClass . '" href="trading_history.php">';
        echo '<span><i class="bi bi-clock-history"></i></span>';
        echo '<p>Trading History</p>';
        echo '</a>';
        echo '</li>';

        // Settings
        $activeClass = $activePage === 'settings' ? 'active' : '';
        echo '<li class="sidebar-menu-item">';
        echo '<a class="sidebar-menu-link ' . $activeClass . '" href="settings.php">';
        echo '<span><i class="bi bi-gear"></i></span>';
        echo '<p>Settings</p>';
        echo '</a>';
        echo '</li>';

        // Activity Logs
        $activeClass = $activePage === 'logs' ? 'active' : '';
        echo '<li class="sidebar-menu-item">';
        echo '<a class="sidebar-menu-link ' . $activeClass . '" href="logs.php">';
        echo '<span><i class="bi bi-journal-text"></i></span>';
        echo '<p>Activity Logs</p>';
        echo '</a>';
        echo '</li>';

        // Theme Toggle
        echo '<li class="sidebar-menu-item">';
        echo '<button onclick="toggleTheme()" class="sidebar-menu-link">';
        echo '<span><i class="bi bi-moon"></i></span>';
        echo '<p>Toggle Theme</p>';
        echo '</button>';
        echo '</li>';

        echo '</ul>';
        echo '</div>';
        echo '</div>';

        // Theme toggle script
        echo '<script>
        function toggleTheme() {
            var html = document.documentElement;
            if (html.classList.contains("dark-theme")) {
                html.classList.remove("dark-theme");
                html.classList.add("light-theme");
                localStorage.setItem("theme", "light");
            } else {
                html.classList.remove("light-theme");
                html.classList.add("dark-theme");
                localStorage.setItem("theme", "dark");
            }
        }
        (function() {
            try {
                var theme = localStorage.getItem("theme");
                var html = document.documentElement;
                html.classList.remove("light-theme", "dark-theme");
                if (theme === "dark") {
                    html.classList.add("dark-theme");
                } else {
                    html.classList.add("light-theme");
                }
            } catch (e) {}
        })();
        </script>';
        
        // Mobile Sidebar Toggle Script
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            const mobileToggle = document.getElementById("mobileSidebarToggle");
            const sidebar = document.getElementById("admin-sidebar");
            const overlay = document.getElementById("overlay");
            
            if (mobileToggle && sidebar) {
                mobileToggle.addEventListener("click", function() {
                    sidebar.classList.toggle("show");
                    if (overlay) {
                        overlay.style.display = sidebar.classList.contains("show") ? "block" : "none";
                    }
                });
                
                // Close sidebar when clicking overlay
                if (overlay) {
                    overlay.addEventListener("click", function() {
                        sidebar.classList.remove("show");
                        overlay.style.display = "none";
                    });
                }
                
                // Close sidebar when clicking on a menu item (mobile)
                const menuLinks = sidebar.querySelectorAll(".sidebar-menu-link");
                menuLinks.forEach(function(link) {
                    link.addEventListener("click", function() {
                        if (window.innerWidth <= 992) {
                            sidebar.classList.remove("show");
                            if (overlay) {
                                overlay.style.display = "none";
                            }
                        }
                    });
                });
            }
        });
        </script>';
    }

    /**
     * Render navbar
     */
    protected function renderNavbar($title) {
        echo '<header class="d-header">';
        echo '<div class="container-fluid px-0">';
        echo '<div class="row align-items-center">';
        echo '<div class="col-lg-5 col-6 d-flex align-items-center">';
        echo '<div class="d-header-left">';
        echo '<div class="sidebar-button" id="dash-sidebar-btn">';
        echo '<span></span>';
        echo '<span></span>';
        echo '<span></span>';
        echo '</div>';
        echo '<h4 class="mb-0 ms-3">' . htmlspecialchars($title) . '</h4>';
        echo '</div>';
        echo '<div class="col-lg-7 col-6">';
        echo '<div class="d-header-right">';

        // Theme Toggle Button
        echo '<div class="header-theme-toggle me-3">';
        echo '<button class="theme-toggle-btn" id="headerThemeToggle" title="Toggle Theme">';
        echo '<i class="bi bi-sun-fill theme-icon theme-icon--light"></i>';
        echo '<i class="bi bi-moon-fill theme-icon theme-icon--dark"></i>';
        echo '</button>';
        echo '</div>';

        // User Dropdown
        echo '<div class="i-dropdown user-dropdown dropdown">';
        echo '<div class="user-dropdown-meta dropdown-toggle hide-arrow" data-bs-toggle="dropdown">';
        echo '<div class="user-img rounded-circle overflow-hidden">';
        echo '<img src="../account/default/images/user.png" alt="Profile image">';
        echo '</div>';
        echo '<div class="user-dropdown-info">';
        echo '<p>' . htmlspecialchars($this->currentUser['name']) . '</p>';
        echo '</div>';
        echo '</div>';

        echo '<ul class="dropdown-menu dropdown-menu-end">';
        echo '<li><span>Welcome, ' . htmlspecialchars($this->currentUser['name']) . '!</span></li>';
        echo '<li><a class="dropdown-item" href="profile.php">Profile</a></li>';
        echo '<li><a class="dropdown-item" href="settings.php">Settings</a></li>';
        echo '<li><hr class="dropdown-divider"></li>';
        echo '<li><a class="dropdown-item" href="../auth/logout.php">Log Out</a></li>';
        echo '</ul>';
        echo '</div>';

        // Notifications (if needed)
        echo '<li class="nav-item dropdown">';
        echo '<a class="nav-link" href="#" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">';
        echo '<i class="bi bi-bell"></i>';
        echo '</a>';
        echo '<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notifDropdown" style="min-width: 320px;">';
        echo '<li class="dropdown-header">Notifications</li>';
        echo '<li><span class="dropdown-item text-muted">No notifications</span></li>';
        echo '</ul>';
        echo '</li>';

        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</header>';

        // Theme toggle JS
        echo '<script>
        // Header theme toggle functionality
        document.addEventListener("DOMContentLoaded", function() {
            const headerThemeToggle = document.getElementById("headerThemeToggle");
            if (headerThemeToggle) {
                headerThemeToggle.addEventListener("click", function() {
                    const currentTheme = localStorage.getItem("theme") || "light";
                    const newTheme = currentTheme === "light" ? "dark" : "light";
                    
                    // Update localStorage
                    localStorage.setItem("theme", newTheme);
                    
                    // Apply theme
                    const html = document.documentElement;
                    const body = document.body;
                    
                    html.classList.remove("light-theme", "dark-theme");
                    body.classList.remove("light-theme", "dark-theme");
                    
                    html.classList.add(newTheme + "-theme");
                    body.classList.add(newTheme + "-theme");
                    
                    // Show notification
                    if (typeof toastr !== "undefined") {
                        const message = newTheme === "dark" ? "🌙 Dark mode activated" : "☀️ Light mode activated";
                        toastr.success(message, "Theme Changed", {
                            timeOut: 2000,
                            progressBar: true,
                            closeButton: true
                        });
                    }
                });
            }
        });
        </script>';
    }

    /**
     * Render common CSS and JS
     */
    protected function renderCommonAssets() {
        echo '<link rel="shortcut icon" href="../account/assets/files/uvaXLDn8KI8Mupab-3.png" type="image/x-icon">';
        echo '<link rel="stylesheet" href="../account/assets/theme/global/css/bootstrap.min.css">';
        echo '<link rel="stylesheet" href="../account/assets/theme/global/css/line-awesome.min.css">';
        echo '<link rel="stylesheet" href="../account/assets/theme/global/css/bootstrap-icons.min.css">';
        echo '<link rel="stylesheet" href="../account/assets/theme/global/css/select2.min.css">';
        echo '<link rel="stylesheet" href="../account/assets/theme/global/css/toaster.css">';
        echo '<link rel="stylesheet" href="../account/assets/theme/global/css/swiper-bundle.min.css">';
        echo '<link rel="stylesheet" href="../account/assets/theme/global/css/apexcharts.css">';
        echo '<link rel="stylesheet" href="../account/assets/theme/global/css/datepicker.min.css">';
        echo '<link rel="stylesheet" href="../account/assets/theme/user/css/main-2.css">';
        echo '<script src="../account/assets/theme/global/js/jquery-3.7.1.min.js"></script>';
        echo '<script src="../account/assets/theme/global/js/bootstrap.bundle.min.js"></script>';
        echo '<script src="../account/assets/theme/global/js/select2.min.js"></script>';
        echo '<script src="../account/assets/theme/global/js/toaster.js"></script>';
        echo '<script src="../account/assets/theme/global/js/swiper-bundle.min.js"></script>';
        echo '<script src="../account/assets/theme/global/js/apexcharts.js"></script>';
        echo '<script src="../account/assets/theme/global/js/datepicker.min.js"></script>';
        echo '<script src="../account/assets/theme/user/js/script.js"></script>';
    }

    /**
     * Render common CSS styles
     */
    protected function renderCommonStyles() {
        echo '<style>';
        echo ':root {';
        echo '--font-primary: "Kanit", sans-serif;';
        echo '--font-secondary: "Kanit", sans-serif;';
        echo '--color-primary: #50cd89;';
        echo '--color-primary-light: rgba(80, 205, 137, 0.15);';
        echo '--color-primary-light-2: rgba(80, 205, 137, 0.08);';
        echo '--color-primary-text: #222;';
        echo '--text-primary: #222;';
        echo '--text-secondary: #6a6a6a;';
        echo '--text-light: #b7b7b7;';
        echo '--color-border: #d2f5e3;';
        echo '--border-primary: rgba(80, 205, 137, 0.4);';
        echo '--border-light: rgba(80, 205, 137, 0.12);';
        echo '--color-white: #fff;';
        echo '--color-gray-1: #f7f8fa;';
        echo '--color-dark: #101010;';
        echo '--color-dark-2: #2d3134;';
        echo '--bg-light: #f3fcf7;';
        echo '--site-bg: #ffffff;';
        echo '--card-bg: #f3fcf7;';
        echo '--topbar-bg: #fff;';
        echo '--sidebar-bg: #fff;';
        echo '--color-success: #50cd89;';
        echo '--color-success-light: rgba(80, 205, 137, 0.12);';
        echo '--success-border: rgba(80, 205, 137, 0.4);';
        echo '--color-danger: rgb(255, 20, 35);';
        echo '--color-danger-light: rgba(240, 101, 72, 0.12);';
        echo '--danger-border: rgba(240, 101, 72, 0.4);';
        echo '--color-warning: #ffc059;';
        echo '--color-warning-light: rgba(255, 192, 89, 0.15);';
        echo '--color-info: #50cd89;';
        echo '--color-info-light: rgba(80, 205, 137, 0.08);';
        echo '--color-green: #3daf70;';
        echo '--color-green-light: rgba(80, 205, 137, 0.1);';
        echo '--shadow-light: 0 2px 8px rgba(80, 205, 137, 0.03);';
        echo '--shadow-medium: 0 4px 15px rgba(80, 205, 137, 0.1);';
        echo '--shadow-heavy: 0 8px 25px rgba(80, 205, 137, 0.15);';
        echo '--gradient-primary: linear-gradient(145deg, #50cd89, #3daf70);';
        echo '--gradient-card: linear-gradient(145deg, #f3fcf7, #e8f5f0);';
        echo '--transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);';
        echo '}';
        
        echo '.dark-theme, .dark-theme html, .dark-theme body {';
        echo 'background: #181a20 !important;';
        echo 'color: #f1f1f1 !important;';
        echo '}';
        
        echo '.dark-theme .main-content,';
        echo '.dark-theme .i-card-sm,';
        echo '.dark-theme .container,';
        echo '.dark-theme .form-group label,';
        echo '.dark-theme input,';
        echo '.dark-theme select,';
        echo '.dark-theme textarea,';
        echo '.dark-theme .dashboard-wrapper,';
        echo '.dark-theme .d-sidebar,';
        echo '.dark-theme .d-header,';
        echo '.dark-theme .sidebar-menu-container,';
        echo '.dark-theme .i-dropdown,';
        echo '.dark-theme .dropdown-menu,';
        echo '.dark-theme .modal-content {';
        echo 'background: #23263a !important;';
        echo 'color: #f1f1f1 !important;';
        echo 'border-color: #35384a !important;';
        echo '}';
        
        echo '.dark-theme .form-group label,';
        echo '.dark-theme label,';
        echo '.dark-theme .sidebar-menu-link,';
        echo '.dark-theme .sidebar-menu-link p,';
        echo '.dark-theme .sidebar-menu-link span,';
        echo '.dark-theme .user-dropdown-info p {';
        echo 'color: #f1f1f1 !important;';
        echo '}';
        
        echo '.light-theme, .light-theme html, .light-theme body {';
        echo 'background: #fff !important;';
        echo 'color: #222 !important;';
        echo '}';
        
        echo '.light-theme .main-content,';
        echo '.light-theme .i-card-sm,';
        echo '.light-theme .container,';
        echo '.light-theme .dashboard-wrapper,';
        echo '.light-theme .d-sidebar,';
        echo '.light-theme .d-header,';
        echo '.light-theme .sidebar-menu-container,';
        echo '.light-theme .i-dropdown,';
        echo '.light-theme .dropdown-menu,';
        echo '.light-theme .modal-content,';
        echo '.light-theme .card,';
        echo '.light-theme .card-body,';
        echo '.light-theme .i-btn,';
        echo '.light-theme .section-title,';
        echo '.light-theme .form-section,';
        echo '.light-theme .form-wrapper,';
        echo '.light-theme .user-dropdown-meta,';
        echo '.light-theme .sidebar-menu-link,';
        echo '.light-theme .sidebar-menu-link.active,';
        echo '.light-theme .sidebar-menu-link:hover,';
        echo '.light-theme .alert,';
        echo '.light-theme .badge,';
        echo '.light-theme .nav,';
        echo '.light-theme .nav-tabs,';
        echo '.light-theme .tab-content {';
        echo 'background: #f3fcf7 !important;';
        echo 'color: #222 !important;';
        echo 'border-color: #d2f5e3 !important;';
        echo 'box-shadow: 0 2px 8px rgba(80,205,137,0.03) !important;';
        echo '}';
        
        echo '.light-theme .form-group label,';
        echo '.light-theme label,';
        echo '.light-theme .sidebar-menu-link,';
        echo '.light-theme .sidebar-menu-link p,';
        echo '.light-theme .sidebar-menu-link span,';
        echo '.light-theme .user-dropdown-info p,';
        echo '.light-theme h1,';
        echo '.light-theme h2,';
        echo '.light-theme h3,';
        echo '.light-theme h4,';
        echo '.light-theme h5,';
        echo '.light-theme h6,';
        echo '.light-theme p,';
        echo '.light-theme a,';
        echo '.light-theme span,';
        echo '.light-theme li,';
        echo '.light-theme .card--title,';
        echo '.light-theme .section-title,';
        echo '.light-theme .dropdown-item,';
        echo '.light-theme .sidebar-menu-link.active,';
        echo '.light-theme .sidebar-menu-link:hover,';
        echo '.light-theme .btn,';
        echo '.light-theme .i-btn,';
        echo '.light-theme .badge,';
        echo '.light-theme .alert,';
        echo '.light-theme .nav-link,';
        echo '.light-theme .tab-pane {';
        echo 'color: #222 !important;';
        echo '}';
        
        echo '.light-theme input, .light-theme select, .light-theme textarea {';
        echo 'background: #fff !important;';
        echo 'color: #222 !important;';
        echo 'border-color: #d2f5e3 !important;';
        echo '}';
        
        echo '.light-theme .i-btn.btn--primary,';
        echo '.light-theme .btn-primary,';
        echo '.light-theme .i-btn.btn--success,';
        echo '.light-theme .btn-success,';
        echo '.light-theme .i-btn.btn--danger,';
        echo '.light-theme .btn-danger {';
        echo 'background: linear-gradient(145deg, #50cd89, #3daf70) !important;';
        echo 'color: #fff !important;';
        echo 'border-color: #50cd89 !important;';
        echo '}';
        
        echo '.light-theme .i-btn.btn--primary:hover,';
        echo '.light-theme .btn-primary:hover,';
        echo '.light-theme .i-btn.btn--success:hover,';
        echo '.light-theme .btn-success:hover,';
        echo '.light-theme .i-btn.btn--danger:hover,';
        echo '.light-theme .btn-danger:hover {';
        echo 'background: #3daf70 !important;';
        echo 'color: #fff !important;';
        echo 'border-color: #3daf70 !important;';
        echo '}';
        
        echo '.light-theme .badge,';
        echo '.light-theme .alert {';
        echo 'border-radius: 6px !important;';
        echo 'box-shadow: 0 1px 4px rgba(80,205,137,0.04) !important;';
        echo '}';
        
        echo '.light-theme .nav-tabs .nav-link.active,';
        echo '.light-theme .nav-tabs .nav-link:hover {';
        echo 'background: #f3fcf7 !important;';
        echo 'color: #50cd89 !important;';
        echo 'border-color: #d2f5e3 #d2f5e3 #fff !important;';
        echo '}';
        
        echo '.light-theme svg,';
        echo '.light-theme i,';
        echo '.light-theme .bi,';
        echo '.light-theme .la,';
        echo '.light-theme .icon {';
        echo 'color: #222 !important;';
        echo 'fill: #222 !important;';
        echo '}';
        
        echo '.light-theme [style*="background: #23263a"],';
        echo '.light-theme [style*="background:#23263a"],';
        echo '.light-theme [style*="background: #181a20"],';
        echo '.light-theme [style*="background:#181a20"],';
        echo '.light-theme [style*="color: #fff"],';
        echo '.light-theme [style*="color:#fff"] {';
        echo 'background: #fff !important;';
        echo 'color: #222 !important;';
        echo '}';
        
        echo '.light-theme {';
        echo '--color-primary-text: #222;';
        echo '--text-primary: #222;';
        echo '--text-secondary: #6a6a6a;';
        echo '--text-light: #b7b7b7;';
        echo '--color-border: #d2f5e3;';
        echo '--color-white: #fff;';
        echo '--color-gray-1: #f7f8fa;';
        echo '--color-dark: #101010;';
        echo '--color-dark-2: #2d3134;';
        echo '--bg-light: #f3fcf7;';
        echo '--site-bg: #ffffff;';
        echo '--card-bg: #f3fcf7;';
        echo '--topbar-bg: #fff;';
        echo '--sidebar-bg: #fff;';
        echo '}';
        
        echo '.stats-card {';
        echo 'background: var(--card-bg);';
        echo 'color: var(--text-primary);';
        echo '}';
        
        echo '.dark-theme .stats-card {';
        echo 'background: #23263a;';
        echo 'color: #f1f1f1;';
        echo '}';
        
        echo '.dark-theme {';
        echo '--card-bg: #23263a;';
        echo '--color-primary-text: #f1f1f1;';
        echo '--text-primary: #f1f1f1;';
        echo '--text-secondary: #b7b7b7;';
        echo '--color-white: #23263a;';
        echo '--color-gray-1: #23263a;';
        echo '--bg-light: #181a20;';
        echo '--site-bg: #181a20;';
        echo '--topbar-bg: #23263a;';
        echo '--sidebar-bg: #23263a;';
        echo '--shadow-light: 0 2px 8px rgba(0, 0, 0, 0.2);';
        echo '--shadow-medium: 0 4px 15px rgba(0, 0, 0, 0.3);';
        echo '--shadow-heavy: 0 8px 25px rgba(0, 0, 0, 0.4);';
        echo '--gradient-primary: linear-gradient(145deg, #50cd89, #3daf70);';
        echo '--gradient-card: linear-gradient(145deg, #23263a, #1e2130);';
        echo '--transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);';
        echo '}';
        
        echo '.header-theme-toggle {';
        echo 'display: flex;';
        echo 'align-items: center;';
        echo '}';
        
        echo '.theme-toggle-btn {';
        echo 'width: 40px;';
        echo 'height: 40px;';
        echo 'border-radius: 50%;';
        echo 'border: none;';
        echo 'background: var(--gradient-card);';
        echo 'color: var(--text-primary);';
        echo 'cursor: pointer;';
        echo 'display: flex;';
        echo 'align-items: center;';
        echo 'justify-content: center;';
        echo 'transition: var(--transition-smooth);';
        echo 'box-shadow: var(--shadow-light);';
        echo 'position: relative;';
        echo 'overflow: hidden;';
        echo '}';
        
        echo '.theme-toggle-btn:hover {';
        echo 'transform: translateY(-2px);';
        echo 'box-shadow: var(--shadow-medium);';
        echo '}';
        
        echo '.theme-toggle-btn:active {';
        echo 'transform: translateY(0);';
        echo '}';
        
        echo '.theme-icon {';
        echo 'font-size: 18px;';
        echo 'transition: var(--transition-smooth);';
        echo 'position: absolute;';
        echo '}';
        
        echo '.theme-icon--light {';
        echo 'color: #ffd700;';
        echo 'opacity: 1;';
        echo 'transform: scale(1);';
        echo '}';
        
        echo '.theme-icon--dark {';
        echo 'color: #e2e8f0;';
        echo 'opacity: 0;';
        echo 'transform: scale(0.8);';
        echo '}';
        
        echo '.dark-theme .theme-icon--light {';
        echo 'opacity: 0;';
        echo 'transform: scale(0.8);';
        echo '}';
        
        echo '.dark-theme .theme-icon--dark {';
        echo 'opacity: 1;';
        echo 'transform: scale(1);';
        echo '}';
        
        echo '* {';
        echo 'transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;';
        echo '}';
        
        echo '.light-theme .theme-toggle-btn {';
        echo 'background: var(--gradient-card);';
        echo 'border: 1px solid var(--color-border);';
        echo '}';
        
        echo '.dark-theme .theme-toggle-btn {';
        echo 'background: var(--gradient-card);';
        echo 'border: 1px solid var(--color-border);';
        echo '}';
        
        echo 'body { background-color: var(--light-color); color: var(--text-color); font-family: "Inter", sans-serif; }';
        echo '.main-content { margin-left: 250px; padding: 20px; }';
        echo '.card { border: none; border-radius: 15px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); background: var(--card-bg); color: var(--text-color); }';
        echo '.table th { background-color: var(--table-header-bg); border-top: none; }';
        echo '.badge { font-size: 0.75em; background: var(--badge-bg); color: var(--text-color); }';
        echo '.table-responsive { width: 100%; }';
        echo '.table { min-width: 1100px; }';
        
        // Mobile Responsive Styles
        echo '@media (max-width: 1200px) {';
        echo '.main-content { margin-left: 200px; padding: 15px; }';
        echo '.card-body { padding: 1rem; }';
        echo '.table { min-width: 800px; }';
        echo '}';
        
        echo '@media (max-width: 992px) {';
        echo '.main-content { margin-left: 0; padding: 10px; }';
        echo '.d-sidebar { transform: translateX(-100%); position: fixed; z-index: 1050; }';
        echo '.d-sidebar.show { transform: translateX(0); }';
        echo '.overlay-bg { display: block; background: rgba(0,0,0,0.3); transition: background 0.3s; }';
        echo '.card-body { padding: 0.75rem; }';
        echo '.table { min-width: 600px; }';
        echo '.btn-group { flex-direction: column; }';
        echo '.btn-group .btn { margin-bottom: 0.25rem; }';
        echo '.pagination { flex-wrap: wrap; justify-content: center; }';
        echo '.pagination .page-item { margin: 0.125rem; }';
        echo '}';
        
        echo '@media (max-width: 768px) {';
        echo '.main-content { padding: 8px; }';
        echo '.card-header { padding: 0.75rem; }';
        echo '.card-body { padding: 0.5rem; }';
        echo '.table { min-width: 400px; font-size: 0.875rem; }';
        echo '.table th, .table td { padding: 0.5rem 0.25rem; }';
        echo '.btn { padding: 0.375rem 0.75rem; font-size: 0.875rem; }';
        echo '.btn-sm { padding: 0.25rem 0.5rem; font-size: 0.75rem; }';
        echo '.form-control, .form-select { font-size: 0.875rem; padding: 0.375rem 0.75rem; }';
        echo '.badge { font-size: 0.7em; }';
        echo '.row.g-3 > .col-md-3, .row.g-3 > .col-md-4, .row.g-3 > .col-md-6 { margin-bottom: 0.5rem; }';
        echo '.d-flex.justify-content-between { flex-direction: column; align-items: flex-start; }';
        echo '.d-flex.justify-content-between > * { margin-bottom: 0.5rem; }';
        echo '.navbar { padding: 0.5rem 1rem; }';
        echo '.navbar-brand { font-size: 1.1rem; }';
        echo '.user-dropdown { margin-left: auto; }';
        // Modern card look for mobile tables
        echo '.table-responsive { border: none; }';
        echo '.table { border: none; background: none; }';
        echo '.table thead { display: none; }';
        echo '.table tbody tr { display: block; margin-bottom: 1.2rem; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(80,205,137,0.08); padding: 0.75rem 0.5rem; border: 1px solid #e6f4ee; }';
        echo '.dark-theme .table tbody tr { background: #23263a; border: 1px solid #35384a; }';
        echo '.table tbody td { display: flex; align-items: center; border: none; padding: 0.35rem 0; font-size: 1em; }';
        echo '.table tbody td:before { content: attr(data-label) ": "; font-weight: 600; color: var(--text-secondary); min-width: 110px; display: inline-block; }';
        echo '.table tbody td.actions { text-align: center; justify-content: flex-start; }';
        echo '.table tbody td.actions:before { content: ""; }';
        echo '.btn-group { width: 100%; }';
        echo '.btn-group .btn { width: 100%; margin-bottom: 0.3rem; border-radius: 8px; }';
        echo '.btn-group .btn:last-child { margin-bottom: 0; }';
        echo '.badge { margin-right: 0.5rem; }';
        echo '}';
        
        echo '@media (max-width: 576px) {';
        echo '.main-content { padding: 5px; }';
        echo '.card { margin-bottom: 0.5rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(80,205,137,0.10); }';
        echo '.card-header { padding: 0.5rem; border-radius: 12px 12px 0 0; }';
        echo '.card-body { padding: 0.25rem; }';
        echo '.table { min-width: 300px; font-size: 0.8rem; }';
        echo '.table th, .table td { padding: 0.25rem 0.125rem; }';
        echo '.btn { padding: 0.25rem 0.5rem; font-size: 0.8rem; border-radius: 8px; }';
        echo '.btn-sm { padding: 0.125rem 0.25rem; font-size: 0.7rem; border-radius: 8px; }';
        echo '.form-control, .form-select { font-size: 0.8rem; padding: 0.25rem 0.5rem; border-radius: 8px; }';
        echo '.badge { font-size: 0.65em; border-radius: 6px; }';
        echo '.row.g-3 { margin: 0; }';
        echo '.row.g-3 > * { padding: 0.25rem; }';
        echo '.col-md-3, .col-md-4, .col-md-6 { width: 100%; }';
        echo '.d-flex { flex-direction: column; }';
        echo '.d-flex > * { margin-bottom: 0.25rem; }';
        echo '.navbar { padding: 0.25rem 0.5rem; border-radius: 8px; }';
        echo '.navbar-brand { font-size: 1rem; }';
        echo '.user-dropdown-info { display: none; }';
        echo '.user-dropdown img { width: 32px; height: 32px; }';
        echo '.theme-toggle-btn { width: 32px; height: 32px; }';
        echo '.theme-icon { font-size: 14px; }';
        // Filter and form improvements
        echo '.row.g-3, .form-row { gap: 0.5rem; }';
        echo '.form-control, .form-select { box-shadow: 0 1px 4px rgba(80,205,137,0.04); border: 1px solid #e6f4ee; }';
        echo '.btn-outline-primary, .btn-outline-secondary, .btn-outline-success, .btn-outline-danger, .btn-outline-warning { border-width: 2px; }';
        echo '}';
        
        echo '@media (max-width: 480px) {';
        echo '.table { min-width: 250px; font-size: 0.75rem; }';
        echo '.table th, .table td { padding: 0.125rem; }';
        echo '.btn { padding: 0.125rem 0.25rem; font-size: 0.75rem; border-radius: 8px; }';
        echo '.btn-sm { padding: 0.1rem 0.2rem; font-size: 0.65rem; border-radius: 8px; }';
        echo '.form-control, .form-select { font-size: 0.75rem; padding: 0.125rem 0.25rem; border-radius: 8px; }';
        echo '.badge { font-size: 0.6em; border-radius: 6px; }';
        echo '.card-header h5 { font-size: 1rem; }';
        echo '.navbar-brand { font-size: 0.9rem; }';
        echo '.user-dropdown img { width: 28px; height: 28px; }';
        echo '.theme-toggle-btn { width: 28px; height: 28px; }';
        echo '.theme-icon { font-size: 12px; }';
        echo '}';
        
        // Mobile Sidebar Toggle
        echo '.mobile-sidebar-toggle {';
        echo 'display: none;';
        echo 'position: fixed;';
        echo 'top: 10px;';
        echo 'left: 10px;';
        echo 'z-index: 1060;';
        echo 'background: var(--color-primary);';
        echo 'color: white;';
        echo 'border: none;';
        echo 'border-radius: 50%;';
        echo 'width: 40px;';
        echo 'height: 40px;';
        echo 'font-size: 18px;';
        echo 'cursor: pointer;';
        echo 'box-shadow: 0 2px 8px rgba(0,0,0,0.2);';
        echo 'transition: background 0.2s, box-shadow 0.2s;';
        echo '}';
        echo '.mobile-sidebar-toggle:active { background: #3daf70; }';
        
        echo '@media (max-width: 992px) {';
        echo '.mobile-sidebar-toggle { display: flex; align-items: center; justify-content: center; }';
        echo '.main-content { padding-top: 60px; }';
        echo '}';
        
        // Mobile Modal Improvements
        echo '@media (max-width: 576px) {';
        echo '.modal-dialog { margin: 0.5rem; }';
        echo '.modal-content { border-radius: 8px; }';
        echo '.modal-header { padding: 0.75rem; }';
        echo '.modal-body { padding: 0.75rem; }';
        echo '.modal-footer { padding: 0.75rem; }';
        echo '.modal-footer .btn { margin: 0.25rem; }';
        echo '}';
        
        echo '</style>';
    }
} 