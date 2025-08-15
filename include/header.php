<!-- Warning Modal -->
<!--<div class="modal fade" id="warningModal" tabindex="-1" aria-labelledby="warningModalLabel" aria-hidden="true">-->
<!--    <div class="modal-dialog modal-dialog-centered">-->
<!--        <div class="modal-content" style="background: linear-gradient(145deg, rgba(43, 49, 78, 0.98), rgba(31, 35, 58, 0.98)); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.3);">-->
<!--            <div class="modal-header border-0">-->
<!--                <h5 class="modal-title" style="color: #fff; font-weight: 600; font-size: 1.25rem;">-->
<!--                    <i class="bi bi-exclamation-triangle-fill me-2" style="color: #ffae17;"></i>-->
<!--                    Important Notice-->
<!--                </h5>-->
<!--                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>-->
<!--            </div>-->
<!--            <div class="modal-body" style="padding: 1.5rem;">-->
<!--                <div style="background: rgba(255, 174, 23, 0.1); border-left: 4px solid #ffae17; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">-->
<!--                    <p style="color: #fff; margin: 0; font-size: 1.1rem; font-weight: 500;">Please Contact Support Before Making Any Deposits</p>-->
<!--                </div>-->
<!--                <p style="color: rgba(255, 255, 255, 0.8); margin-bottom: 1rem; line-height: 1.6;">-->
<!--                    To ensure a smooth deposit process and prevent any potential loss of funds, we strongly recommend contacting our support team before proceeding with any deposit transactions.-->
<!--                </p>-->
<!--                <div style="background: rgba(99, 92, 255, 0.1); border: 1px solid rgba(99, 92, 255, 0.2); padding: 1rem; border-radius: 8px;">-->
<!--                    <p style="color: rgba(255, 255, 255, 0.9); margin: 0; font-size: 0.95rem;">-->
<!--                        <i class="bi bi-info-circle-fill me-2" style="color: #635cff;"></i>-->
<!--                        Our support team is available 24/7 to assist you with your deposit process and answer any questions you may have.-->
<!--                    </p>-->
<!--                </div>-->
<!--            </div>-->
<!--            <div class="modal-footer" style="border-top: 1px solid rgba(255, 255, 255, 0.1); padding: 1rem 1.5rem;">-->
<!--                <button type="button" class="btn" style="background: rgba(255, 255, 255, 0.1); color: #fff; padding: 0.5rem 1.25rem; border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.1);" data-bs-dismiss="modal">Close</button>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--</div>-->


<?php
require_once '../../../includes/Auth.php';
require_once '../../../includes/Database.php';
require_once '../../../includes/currency_helpers.php';

if (session_status() === PHP_SESSION_NONE) {
session_start();
}

$auth = new Auth();
$currentUser = $auth->getCurrentUser();

$publicPages = ['login.php', 'register.php', 'forgot-password.php'];
$currentPage = basename($_SERVER['PHP_SELF']);

function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

if (!$auth->isLoggedIn() && !in_array($currentPage, $publicPages)) {
    if (isAjaxRequest()) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Session expired. Please refresh the page.'
        ]);
        exit();
    } else {
        header('Location: ../../../auth/login.php');
        exit;
    }
}

// Now fetch user data as needed
$db = new Database();
$user = null;
if ($currentUser) {
    $user = $db->getUserById($currentUser['id']);
}

$notifications = [];
$unreadCount = 0;

if ($currentUser) {
    $stmt = $db->prepare("SELECT id, message, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY is_read ASC, created_at DESC LIMIT 10");
    $stmt->bind_param("i", $currentUser['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
        if (!$row['is_read']) $unreadCount++;
    }
    $stmt->close();
}

$user_currency = 'USD';
if ($user && isset($user['currency']) && $user['currency']) {
    $user_currency = preg_replace('/[^A-Z]/', '', strtoupper($user['currency']));
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltl" data-sidebar="open" color-scheme="light">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_TITLE; ?></title>
    <link rel="shortcut icon" href="../../assets/files/uvaXLDn8KI8Mupab-3.png" type="image/x-icon">
            <link rel="stylesheet" href="../../assets/theme/global/css/bootstrap.min.css">
            <link rel="stylesheet" href="../../assets/theme/global/css/line-awesome.min.css">
            <link rel="stylesheet" href="../../assets/theme/global/css/bootstrap-icons.min.css">
            <link rel="stylesheet" href="../../assets/theme/global/css/select2.min.css">
            <link rel="stylesheet" href="../../assets/theme/global/css/toaster.css">
            <link rel="stylesheet" href="../../assets/theme/global/css/swiper-bundle.min.css">
            <link rel="stylesheet" href="../../assets/theme/global/css/apexcharts.css">
            <link rel="stylesheet" href="../../assets/theme/global/css/datepicker.min.css">
                <link rel="stylesheet" href="../../assets/theme/user/css/main-2.css">
                <style>
    :root {
        color-scheme: light;
        --font-primary: "Kanit", sans-serif;
        --font-secondary: "Kanit", sans-serif;
        --color-primary: #50cd89;
        --color-primary-light: rgba(80, 205, 137, 0.15);
        --color-primary-light-2: rgba(80, 205, 137, 0.08);
        --color-primary-text: #222;
        --text-primary: #222;
        --text-secondary: #6a6a6a;
        --text-light: #b7b7b7;
        --color-border: #d2f5e3;
        --border-primary: rgba(80, 205, 137, 0.4);
        --border-light: rgba(80, 205, 137, 0.12);
        --color-white: #fff;
        --color-gray-1: #f7f8fa;
        --color-dark: #101010;
        --color-dark-2: #2d3134;
        --bg-light: #f3fcf7;
        --site-bg: #ffffff;
        --card-bg: #f3fcf7;
        --topbar-bg: #fff;
        --sidebar-bg: #fff;
        --color-success: #50cd89;
        --color-success-light: rgba(80, 205, 137, 0.12);
        --success-border: rgba(80, 205, 137, 0.4);
        --color-danger: rgb(255, 20, 35);
        --color-danger-light: rgba(240, 101, 72, 0.12);
        --danger-border: rgba(240, 101, 72, 0.4);
        --color-warning: #ffc059;
        --color-warning-light: rgba(255, 192, 89, 0.15);
        --color-info: #50cd89;
        --color-info-light: rgba(80, 205, 137, 0.08);
        --color-green: #3daf70;
        --color-green-light: rgba(80, 205, 137, 0.1);
        
        /* Enhanced theme variables */
        --shadow-light: 0 2px 8px rgba(80, 205, 137, 0.03);
        --shadow-medium: 0 4px 15px rgba(80, 205, 137, 0.1);
        --shadow-heavy: 0 8px 25px rgba(80, 205, 137, 0.15);
        --gradient-primary: linear-gradient(145deg, #50cd89, #3daf70);
        --gradient-card: linear-gradient(145deg, #f3fcf7, #e8f5f0);
        --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .dark-theme, .dark-theme html, .dark-theme body {
        background: #181a20 !important;
        color: #f1f1f1 !important;
    }
    .dark-theme .main-content,
    .dark-theme .i-card-sm,
    .dark-theme .container,
    .dark-theme .form-group label,
    .dark-theme input,
    .dark-theme select,
    .dark-theme textarea,
    .dark-theme .dashboard-wrapper,
    .dark-theme .d-sidebar,
    .dark-theme .d-header,
    .dark-theme .sidebar-menu-container,
    .dark-theme .i-dropdown,
    .dark-theme .dropdown-menu,
    .dark-theme .modal-content {
        background: #23263a !important;
        color: #f1f1f1 !important;
        border-color: #35384a !important;
    }
    .dark-theme .form-group label,
    .dark-theme label,
    .dark-theme .sidebar-menu-link,
    .dark-theme .sidebar-menu-link p,
    .dark-theme .sidebar-menu-link span,
    .dark-theme .user-dropdown-info p {
        color: #f1f1f1 !important;
    }
    .light-theme, .light-theme html, .light-theme body {
        background: #fff !important;
        color: #222 !important;
    }
    .light-theme .main-content,
    .light-theme .i-card-sm,
    .light-theme .container,
    .light-theme .dashboard-wrapper,
    .light-theme .d-sidebar,
    .light-theme .d-header,
    .light-theme .sidebar-menu-container,
    .light-theme .i-dropdown,
    .light-theme .dropdown-menu,
    .light-theme .modal-content,
    .light-theme .card,
    .light-theme .card-body,
    .light-theme .i-btn,
    .light-theme .section-title,
    .light-theme .form-section,
    .light-theme .form-wrapper,
    .light-theme .user-dropdown-meta,
    .light-theme .sidebar-menu-link,
    .light-theme .sidebar-menu-link.active,
    .light-theme .sidebar-menu-link:hover,
    .light-theme .alert,
    .light-theme .badge,
    .light-theme .nav,
    .light-theme .nav-tabs,
    .light-theme .tab-content {
        background: #f3fcf7 !important;
        color: #222 !important;
        border-color: #d2f5e3 !important;
        box-shadow: 0 2px 8px rgba(80,205,137,0.03) !important;
    }
    .light-theme .form-group label,
    .light-theme label,
    .light-theme .sidebar-menu-link,
    .light-theme .sidebar-menu-link p,
    .light-theme .sidebar-menu-link span,
    .light-theme .user-dropdown-info p,
    .light-theme h1,
    .light-theme h2,
    .light-theme h3,
    .light-theme h4,
    .light-theme h5,
    .light-theme h6,
    .light-theme p,
    .light-theme a,
    .light-theme span,
    .light-theme li,
    .light-theme .card--title,
    .light-theme .section-title,
    .light-theme .dropdown-item,
    .light-theme .sidebar-menu-link.active,
    .light-theme .sidebar-menu-link:hover,
    .light-theme .btn,
    .light-theme .i-btn,
    .light-theme .badge,
    .light-theme .alert,
    .light-theme .nav-link,
    .light-theme .tab-pane {
        color: #222 !important;
    }
    .light-theme input, .light-theme select, .light-theme textarea {
        background: #fff !important;
        color: #222 !important;
        border-color: #d2f5e3 !important;
    }
    .light-theme .i-btn.btn--primary,
    .light-theme .btn-primary,
    .light-theme .i-btn.btn--success,
    .light-theme .btn-success,
    .light-theme .i-btn.btn--danger,
    .light-theme .btn-danger {
        background: linear-gradient(145deg, #50cd89, #3daf70) !important;
        color: #fff !important;
        border-color: #50cd89 !important;
    }
    .light-theme .i-btn.btn--primary:hover,
    .light-theme .btn-primary:hover,
    .light-theme .i-btn.btn--success:hover,
    .light-theme .btn-success:hover,
    .light-theme .i-btn.btn--danger:hover,
    .light-theme .btn-danger:hover {
        background: #3daf70 !important;
        color: #fff !important;
        border-color: #3daf70 !important;
    }
    .light-theme .badge,
    .light-theme .alert {
        border-radius: 6px !important;
        box-shadow: 0 1px 4px rgba(80,205,137,0.04) !important;
    }
    .light-theme .nav-tabs .nav-link.active,
    .light-theme .nav-tabs .nav-link:hover {
        background: #f3fcf7 !important;
        color: #50cd89 !important;
        border-color: #d2f5e3 #d2f5e3 #fff !important;
    }
    .light-theme svg,
    .light-theme i,
    .light-theme .bi,
    .light-theme .la,
    .light-theme .icon {
        color: #222 !important;
        fill: #222 !important;
    }
    /* Aggressively override common dark backgrounds and white text */
    .light-theme [style*="background: #23263a"],
    .light-theme [style*="background:#23263a"],
    .light-theme [style*="background: #181a20"],
    .light-theme [style*="background:#181a20"],
    .light-theme [style*="color: #fff"],
    .light-theme [style*="color:#fff"] {
        background: #fff !important;
        color: #222 !important;
    }
    /* Reset CSS variables for light mode */
    .light-theme {
        --color-primary-text: #222;
        --text-primary: #222;
        --text-secondary: #6a6a6a;
        --text-light: #b7b7b7;
        --color-border: #d2f5e3;
        --color-white: #fff;
        --color-gray-1: #f7f8fa;
        --color-dark: #101010;
        --color-dark-2: #2d3134;
        --bg-light: #f3fcf7;
        --site-bg: #ffffff;
        --card-bg: #f3fcf7;
        --topbar-bg: #fff;
        --sidebar-bg: #fff;
    }
    .stats-card {
        background: var(--card-bg);
        color: var(--text-primary);
    }
    .dark-theme .stats-card {
        background: #23263a;
        color: #f1f1f1;
    }
    .dark-theme {
        --card-bg: #23263a;
        --color-primary-text: #f1f1f1;
        --text-primary: #f1f1f1;
        --text-secondary: #b7b7b7;
        --color-white: #23263a;
        --color-gray-1: #23263a;
        --bg-light: #181a20;
        --site-bg: #181a20;
        --topbar-bg: #23263a;
        --sidebar-bg: #23263a;
        
        /* Enhanced dark theme variables */
        --shadow-light: 0 2px 8px rgba(0, 0, 0, 0.2);
        --shadow-medium: 0 4px 15px rgba(0, 0, 0, 0.3);
        --shadow-heavy: 0 8px 25px rgba(0, 0, 0, 0.4);
        --gradient-primary: linear-gradient(145deg, #50cd89, #3daf70);
        --gradient-card: linear-gradient(145deg, #23263a, #1e2130);
        --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    /* Header Theme Toggle Styles */
    .header-theme-toggle {
        display: flex;
        align-items: center;
    }
    
    .theme-toggle-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: none;
        background: var(--gradient-card);
        color: var(--text-primary);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition-smooth);
        box-shadow: var(--shadow-light);
        position: relative;
        overflow: hidden;
    }
    
    .theme-toggle-btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-medium);
    }
    
    .theme-toggle-btn:active {
        transform: translateY(0);
    }
    
    .theme-icon {
        font-size: 18px;
        transition: var(--transition-smooth);
        position: absolute;
    }
    
    .theme-icon--light {
        color: #ffd700;
        opacity: 1;
        transform: scale(1);
    }
    
    .theme-icon--dark {
        color: #e2e8f0;
        opacity: 0;
        transform: scale(0.8);
    }
    
    .dark-theme .theme-icon--light {
        opacity: 0;
        transform: scale(0.8);
    }
    
    .dark-theme .theme-icon--dark {
        opacity: 1;
        transform: scale(1);
    }
    
    /* Smooth transitions for all elements */
    * {
        transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
    }
    
    /* Enhanced theme consistency */
    .light-theme .theme-toggle-btn {
        background: var(--gradient-card);
        border: 1px solid var(--color-border);
    }
    
    .dark-theme .theme-toggle-btn {
        background: var(--gradient-card);
        border: 1px solid var(--color-border);
    }
</style>
        <script>
    // Apply theme before page renders
    (function() {
        try {
            var theme = localStorage.getItem('theme') || 'light';
            var html = document.documentElement;
            var body = document.body;
            
            html.classList.remove('light-theme', 'dark-theme');
            body.classList.remove('light-theme', 'dark-theme');
            
            if (theme === 'dark') {
                html.classList.add('dark-theme');
                body.classList.add('dark-theme');
            } else {
                html.classList.add('light-theme');
                body.classList.add('light-theme');
            }
        } catch (e) {}
    })();
    
    // Header theme toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
        const headerThemeToggle = document.getElementById('headerThemeToggle');
        if (headerThemeToggle) {
            headerThemeToggle.addEventListener('click', function() {
                const currentTheme = localStorage.getItem('theme') || 'light';
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                
                // Update localStorage
                localStorage.setItem('theme', newTheme);
                
                // Apply theme
                const html = document.documentElement;
                const body = document.body;
                
                html.classList.remove('light-theme', 'dark-theme');
                body.classList.remove('light-theme', 'dark-theme');
                
                html.classList.add(newTheme + '-theme');
                body.classList.add(newTheme + '-theme');
                
                // Show notification
                if (typeof toastr !== 'undefined') {
                    const message = newTheme === 'dark' ? '🌙 Dark mode activated' : '☀️ Light mode activated';
                    toastr.success(message, 'Theme Changed', {
                        timeOut: 2000,
                        progressBar: true,
                        closeButton: true
                    });
                }
            });
        }
    });
    </script>
        </head>

<body>
<div class="overlay-bg" id="overlay"></div>

<!-- Activity notification system -->
<!--<div class="activity-notification-container" id="activityNotificationContainer">-->
<!--    <div class="activity-notification" id="activityNotification">-->
<!--        <div class="activity-icon">-->
<!--            <i class="bi bi-graph-up-arrow"></i>-->
<!--        </div>-->
<!--        <div class="activity-content">-->
<!--            <p class="activity-message" id="activityMessage"></p>-->
<!--            <span class="activity-time">Just now</span>-->
<!--        </div>-->
<!--    </div>-->
<!--</div>-->

    <header class="d-header">
    <div class="container-fluid px-0">
        <div class="row align-items-cener">
            <div class="col-lg-5 col-6 d-flex align-items-center">
                <div class="d-header-left">
                    <div class="sidebar-button" id="dash-sidebar-btn">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
            <div class="col-lg-7 col-6">
                <div class="d-header-right">

                    <!-- Theme Toggle Button -->
                    <div class="header-theme-toggle me-3">
                        <button class="theme-toggle-btn" id="headerThemeToggle" title="Toggle Theme">
                            <i class="bi bi-sun-fill theme-icon theme-icon--light"></i>
                            <i class="bi bi-moon-fill theme-icon theme-icon--dark"></i>
                        </button>
                    </div>

                    <div class="i-dropdown user-dropdown dropdown">
                        <div class="user-dropdown-meta dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                            <div class="user-img rounded-circle overflow-hidden">
                                <img src="../../default/images/user.png" alt="Profile image">
                            </div>
                            <div class="user-dropdown-info">
                                <p></p>
                            </div>
                        </div>

                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <span>Welcome !</span>
                            </li>
                            <li>
                                <a class="dropdown-item" href="../profile/index.php" style="color: white !important;">
                                   Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="../settings/index.php" style="color: white !important;">
                                    Settings
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="../wallet/index.php" style="color: white !important;">
                                    Wallet Top-Up
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/lldash/auth/logout.php" style="color: white !important;">
                                    Log Out
                                </a>
                            </li>
                        </ul>
                    </div>

                    <li class="nav-item dropdown">
                        <a class="nav-link" href="#" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell"></i>
                            <?php if ($unreadCount > 0): ?>
                                <span class="badge bg-danger"><?php echo $unreadCount; ?></span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notifDropdown" style="min-width: 320px;">
                            <li class="dropdown-header">Notifications</li>
                            <?php if (count($notifications) === 0): ?>
                                <li><span class="dropdown-item text-muted">No notifications</span></li>
                            <?php else: ?>
                                <?php foreach ($notifications as $notif): ?>
                                    <li>
                                        <a href="#" class="dropdown-item<?php echo !$notif['is_read'] ? ' fw-bold' : ''; ?> mark-as-read" data-id="<?php echo $notif['id']; ?>">
                                            <?php echo htmlspecialchars($notif['message']); ?>
                                            <br>
                                            <small class="text-muted"><?php echo date('M d, H:i', strtotime($notif['created_at'])); ?></small>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </li>
                </div>
            </div>
        </div>
    </div>
</header>
    <div class="dashboard-wrapper">
        <div class="d-sidebar" id="user-sidebar">
    <div class="sidebar-logo">
        <a href="../index/index.php">
            <img src="<?php echo APP_LOGO; ?>" alt="logo">
        </a>
    </div>
    <div class="sidebar-menu-container" data-simplebar="" style="max-height: calc(100vh - 70px); overflow: auto;">
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a class="sidebar-menu-link active" href="../index/index.php" aria-expanded="false">
                    <span><i class="bi bi-speedometer2"></i></span>
                    <p>Dashboard</p>
                </a>
            </li>

            <li class="sidebar-menu-item">
                <a class="sidebar-menu-link " href="../transactions/index.php" aria-expanded="false">
                    <span><i class="bi bi-credit-card-fill"></i></span>
                    <p>Transaction</p>
                </a>
            </li>
            
            <li class="sidebar-menu-item">
                <a class="sidebar-menu-link collapsed " data-bs-toggle="collapse" href="#collapseDeposit" role="button" aria-expanded="false" aria-controls="collapseTrade">
                    <span><i class="bi bi-wallet2"></i></span>
                    <p>Deposit  <small><i class="las la-angle-down"></i></small></p>
                </a>
                <div class="side-menu-dropdown collapse " id="collapseDeposit">
                    <ul class="sub-menu  ">
                                                    <li class="sub-menu-item">
                                <a class="sidebar-menu-link " href="../payment/deposits.php" aria-expanded="false">
                                    <p>Instant</p>
                                </a>
                            </li>
                                                    <li class="sub-menu-item">
                                <a class="sidebar-menu-link " href="../payment/deposits-commissions.php" aria-expanded="false">
                                    <p>Commissions</p>
                                </a>
                            </li>
                                            </ul>
                </div>
            </li> 
            
            <li class="sidebar-menu-item">
                <a class="sidebar-menu-link " href="../wallet/index.php" aria-expanded="false">
                    <span><i class="bi bi-arrow-clockwise"></i></span>
                    <p>Transfer Funds</p>
                </a>
            </li>

                            <li class="sidebar-menu-item">
                    <a class="sidebar-menu-link " href="../rewards/index.php" aria-expanded="false">
                        <span><i class="bi bi-award-fill"></i></span>
                        <p>Reward Badges</p>
                    </a>
                </li>
            
                            <li class="sidebar-menu-item">
                    <a class="sidebar-menu-link collapsed " data-bs-toggle="collapse" href="#collapseWithdraw" role="button" aria-expanded="false" aria-controls="collapseWithdraw">
                        <span><i class="bi la-money-bill-wave"></i></span>
                        <p>Matrix<small><i class="las la-angle-down"></i></small></p>
                    </a>
                    <div class="side-menu-dropdown collapse " id="collapseWithdraw">
                        <ul class="sub-menu ">
                                                            <li class="sub-menu-item">
                                    <a class="sidebar-menu-link " href="../matrix/index.php" aria-expanded="false">
                                        <p>Scheme</p>
                                    </a>
                                </li>
                                                            <li class="sub-menu-item">
                                    <a class="sidebar-menu-link " href="../commissions/rewards.php" aria-expanded="false">
                                        <p>Referral Rewards</p>
                                    </a>
                                </li>
                                                            <li class="sub-menu-item">
                                    <a class="sidebar-menu-link " href="../commission/index.php" aria-expanded="false">
                                        <p>Commissions</p>
                                    </a>
                                </li>
                                                    </ul>
                    </div>
                </li>
            
                            <li class="sidebar-menu-item">
                    <a class="sidebar-menu-link collapsed " data-bs-toggle="collapse" href="#collapsePaymentProcessor" role="button" aria-expanded="false" aria-controls="collapsePaymentProcessor">
                        <span><i class="bi bi-wallet-fill"></i></span>
                        <p>Investments  <small><i class="las la-angle-down"></i></small></p>
                    </a>
                    <div class="side-menu-dropdown collapse " id="collapsePaymentProcessor">
                        <ul class="sub-menu  ">
                                                    <li class="sub-menu-item">
                                <a class="sidebar-menu-link " href="../investment/index.php" aria-expanded="false">
                                    <p>Scheme</p>
                                </a>
                            </li>
                                                    <li class="sub-menu-item">
                                <a class="sidebar-menu-link " href="../investments/funds.php" aria-expanded="false">
                                    <p>Funds</p>
                                </a>
                            </li>
                                                    <li class="sub-menu-item">
                                <a class="sidebar-menu-link " href="../investments/profit-statistics.php" aria-expanded="false">
                                    <p>Profit Statistics</p>
                                </a>
                            </li>
                                                </ul>
                    </div>
                </li>
            

                            <li class="sidebar-menu-item">
                    <a class="sidebar-menu-link " href="../staking/index.php" aria-expanded="false">
                        <span><i class="bi bi-currency-euro"></i></span>
                        <p>Staking Investment</p>
                    </a>
                </li>

            <li class="sidebar-menu-item">
                <a class="sidebar-menu-link collapsed " data-bs-toggle="collapse" href="#collapseSignal" role="button" aria-expanded="false" aria-controls="collapseSignal">
                    <span><i class="bi bi-graph-up-arrow"></i></span>
                    <p>Trading Signals <small><i class="las la-angle-down"></i></small></p>
                </a>
                <div class="side-menu-dropdown collapse " id="collapseSignal">
                    <ul class="sub-menu ">
                        <li class="sub-menu-item">
                            <a class="sidebar-menu-link " href="../signal/buy.php" aria-expanded="false">
                                <p>Buy Signals</p>
                            </a>
                        </li>
                        <li class="sub-menu-item">
                            <a class="sidebar-menu-link " href="../signal/index.php" aria-expanded="false">
                                <p>My Signals</p>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="sidebar-menu-item">
                <a class="sidebar-menu-link collapsed " data-bs-toggle="collapse" href="#collapseCopyTrading" role="button" aria-expanded="false" aria-controls="collapseCopyTrading">
                    <span><i class="bi bi-people-fill"></i></span>
                    <p>Copy Trading <small><i class="las la-angle-down"></i></small></p>
                </a>
                <div class="side-menu-dropdown collapse " id="collapseCopyTrading">
                    <ul class="sub-menu ">
                        <li class="sub-menu-item">
                            <a class="sidebar-menu-link " href="../copy-trader/list.php" aria-expanded="false">
                                <p>Copy Traders</p>
                            </a>
                        </li>
                        <li class="sub-menu-item">
                            <a class="sidebar-menu-link " href="../copy-trader/my-trades.php" aria-expanded="false">
                                <p>My Trades</p>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="sidebar-menu-item">
                <a class="sidebar-menu-link collapsed " data-bs-toggle="collapse" href="#collapseStocks" role="button" aria-expanded="false" aria-controls="collapseStocks">
                    <span><i class="bi bi-graph-up"></i></span>
                    <p>Stocks <small><i class="las la-angle-down"></i></small></p>
                </a>
                <div class="side-menu-dropdown collapse " id="collapseStocks">
                    <ul class="sub-menu ">
                        <li class="sub-menu-item">
                            <a class="sidebar-menu-link " href="../stock/index.php" aria-expanded="false">
                                <p>All Stocks</p>
                            </a>
                        </li>
                        <li class="sub-menu-item">
                            <a class="sidebar-menu-link " href="../stocks/my-trades.php" aria-expanded="false">
                                <p>My Stock Trades</p>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

                            <li class="sidebar-menu-item">
                    <a class="sidebar-menu-link collapsed " data-bs-toggle="collapse" href="#collapseTrade" role="button" aria-expanded="false" aria-controls="collapseTrade">
                        <span><i class="bi bi-bar-chart"></i></span>
                        <p>Binary Trading  <small><i class="las la-angle-down"></i></small></p>
                    </a>
                    <div class="side-menu-dropdown collapse " id="collapseTrade">
                        <ul class="sub-menu  ">
                                                            <li class="sub-menu-item">
                                    <a class="sidebar-menu-link " href="../trade/index.php" aria-expanded="false">
                                        <p>Trade Now</p>
                                    </a>
                                </li>
                                                            <li class="sub-menu-item">
                                    <a class="sidebar-menu-link " href="../practices/logs.php" aria-expanded="false">
                                        <p>History</p>
                                    </a>
                                </li>
                                                            <li class="sub-menu-item">
                                    <a class="sidebar-menu-link " href="../trade/practice_hist.php" aria-expanded="false">
                                        <p>Practices</p>
                                    </a>
                                </li>
                                                    </ul>
                    </div>
                </li>
            

            <li class="sidebar-menu-item">
                <a class="sidebar-menu-link " href="../referrals/index.php" aria-expanded="false">
                    <span><i class="bi bi-command"></i></span>
                    <p>Referrals</p>
                </a>
            </li>

            <li class="sidebar-menu-item">
                <a class="sidebar-menu-link " href="../cash-out/index.php" aria-expanded="false">
                    <span><i class="bi bi-wallet"></i></span>
                    <p>Cash out</p>
                </a>
            </li>

            <li class="sidebar-menu-item">
                <a class="sidebar-menu-link " href="../recharge/index.php" aria-expanded="false">
                    <span><i class="bi bi-cash"></i></span>
                    <p>InstaPIN Recharge</p>
                </a>
            </li>

            <li class="sidebar-menu-item">
                <a class="sidebar-menu-link " href="../settings/index.php" aria-expanded="false">
                    <span><i class="bi bi-gear"></i></span>
                    <p>Settings</p>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a class="sidebar-menu-link " href="../kyc/index.php" aria-expanded="false">
                    <span><i class="bi bi-shield-check"></i></span>
                    <p>KYC Verification</p>
                </a>
            </li>
            <li class="sidebar-menu-item">
            <button onclick="toggleTheme()" class="sidebar-menu-link">
                <span><i class="bi bi-moon"></i></span>
                <p>Toggle Theme</p>
            </button>
</li>
        </ul>
    </div>
</div>
<script>
        // In a script tag or JS file
        function toggleTheme() {
      var html = document.documentElement;
      if (html.classList.contains('dark-theme')) {
        html.classList.remove('dark-theme');
        html.classList.add('light-theme');
        localStorage.setItem('theme', 'light');
      } else {
        html.classList.remove('light-theme');
        html.classList.add('dark-theme');
        localStorage.setItem('theme', 'dark');
      }
    }
    (function() {
    try {
        var theme = localStorage.getItem('theme');
        var html = document.documentElement;
        html.classList.remove('light-theme', 'dark-theme');
        if (theme === 'dark') {
            html.classList.add('dark-theme');
        } else {
            html.classList.add('light-theme');
        }
    } catch (e) {}
})();
</script>