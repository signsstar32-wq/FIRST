<?php
require_once '../../includes/Auth.php';
require_once '../../includes/Database.php';
require_once '../../includes/currency_helpers.php';

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

// Redirect if not logged in and not on a public page
if (!$auth->isLoggedIn() && !in_array($currentPage, $publicPages)) {
    if (isAjaxRequest()) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Session expired. Please refresh the page.'
        ]);
        exit();
    } else {
        header('Location: ../../auth/login.php');
        exit;
    }
}

// Fetch user data from DB
$db = new Database();
$user = null;
if ($currentUser) {
    $user = $db->getUserById($currentUser['id']);
}

// Assign safe defaults to avoid undefined variable warnings
$name         = $user['name'] ?? '';
$email        = $user['email'] ?? '';
$photo        = $user['photo'] ?? 'default.png';
$date_created = $user['date_created'] ?? null;
$last_login   = $user['last_login'] ?? null;
$plan         = $user['plan'] ?? 'Free';
$data         = $user['kyc_status'] ?? null;

// Notifications
$notifications = [];
$unreadCount = 0;
if ($currentUser) {
    $stmt = $db->prepare("
        SELECT id, message, is_read, created_at 
        FROM notifications 
        WHERE user_id = ? 
        ORDER BY is_read ASC, created_at DESC 
        LIMIT 10
    ");
    $stmt->bind_param("i", $currentUser['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
        if (!$row['is_read']) $unreadCount++;
    }
    $stmt->close();
}

// Currency setup
$user_currency = 'USD';
if (!empty($user['currency'])) {
    $user_currency = preg_replace('/[^A-Z]/', '', strtoupper($user['currency']));
}
?>

<html>

<head>
  <title><?php echo APP_TITLE ?></title>

  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="../../npm/bootstrap%404.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

  <link href="https://www.dafontfree.net/embed/bmljb25uZS1yZWd1bGFyJmRhdGEvMTYvbi84NDM1Ny9OaWNvbm5lLVJlZ3VsYXIudHRm" rel="stylesheet" type="text/css">
  <link rel="stylesheet" href="../../ajax/libs/animate.css/4.1.1/animate.min.css">

  <link rel="stylesheet" href="../assets0/notify.css">
  <link rel="stylesheet" href="../assets0/custom.css">
  <link rel="stylesheet" href="../assets0/deposit.css">
  <link rel="stylesheet" href="../assets0/profile.css">

  <link rel="stylesheet" href="../css/bootstrap.min.css">

  <link rel="stylesheet" href="../assets0/transact.css">

    <link rel="stylesheet" href="../css/typography.css">
  <!-- Style CSS -->
  <link rel="stylesheet" href="../css/style.css">
  <!-- Responsive CSS -->
  <link rel="stylesheet" href="../css/responsive.css">

  <link rel="stylesheet" href="../assets0/tradinglive.css">

  <link rel="stylesheet" type="text/css" href="../assets0/index.css">

  <link rel="stylesheet" href="../assets0/t6.css">

<link rel="icon" href="<?php echo APP_FAVICON ?>" type="image/png">

  <script type="text/javascript" src="../script/jquery.min.js"></script>

  <script src="../assets0/promise-polyfill.js"></script>
  <!--// Chat Widgets -->
  <link href="../../css-4?family=Roboto:300,400,500,600,700" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@hotwired/turbo@7.3.0/dist/turbo.min.js"></script>
  
</head>

<body oncontextmenu="return false;" class="main">
      <center><div id="google_translate_element" style="margin-bottom:10px;"></div></center>
<script type="text/javascript">
function googleTranslateElementInit() {
  new google.translate.TranslateElement({pageLanguage: 'en'}, 'google_translate_element');}
</script>

<script type="text/javascript" src="../../translate_a/element-1.js?cb=googleTranslateElementInit"></script>
  
<!--<script>-->
<!--    function getMessages(letter) {-->
<!--      var div = $("#link11");-->
<!--      $.get('load.php', function (data) {-->
<!--        div.html(data);-->
<!--      });-->
<!--    }-->

<!--    setInterval(getMessages, 100);-->
<!--  </script>-->

<!--  <script>-->
<!--    function getMessages(letter) {-->
<!--      var div = $("#link22");-->
<!--      $.get('load2.php', function (data) {-->
<!--        div.html(data);-->
<!--      });-->
<!--    }-->

<!--    setInterval(getMessages, 100);-->
<!--  </script>-->



<!--  <script>-->
<!--    function getMessages(letter) {-->
<!--      var div = $("#link33");-->
<!--      $.get('load3.php', function (data) {-->
<!--        div.html(data);-->
<!--      });-->
<!--    }-->

<!--    setInterval(getMessages, 100);-->
<!--  </script>-->

<!--  <script>-->
<!--    function getMessages(letter) {-->
<!--      var div = $("#suspend");-->
<!--      $.get('suspend.php', function (data) {-->
<!--        div.html(data);-->
<!--      });-->
<!--    }-->

<!--    setInterval(getMessages, 1000);-->
<!--  </script>-->

  <!--<div id="suspend"></div>-->


  <div class="content">

    <!-- <div id="a-notification" class="notification  gone   d-flex justify-content-start ">
      <div class="d-flex flex-column">

        <div class="h-">
          Notification
        </div>
        <div class="s-">
        </div>
        <div class="b-"> </div>
        <div class="btns row">
          <div class="col-6">
            <div class="red-btn" id="close_n">
              <center>CLOSE NOTIFICATION</center>
            </div>
          </div>
          <div class="col-6">
            <div class="gren-btn" id="mark_red" data-id="<br />
<b>Notice</b>:  Undefined index: i- in <b>/home/u963230125/domains/adarfxlt.com/public_html/wp-user/dashboard.php</b> on line <b>163</b><br />
">
              <center>MARK AS READ</center>
            </div>
          </div>
        </div>

      </div>
    </div> -->
    <center><img src="../logo-1.png" style="width:7em;"></center>
    <div class="d-flex justify-content-between" style="margin-top: 1px;margin-bottom: 15px;">
     <div class="hamburger" id="openSidebar">
    <img src="assets0/m.svg">
  </div>
      <div class="d-flex justify-content-center align-items-center">
        <div class="uname" style="text-transform:capitalize;"><?php echo ucwords($name ?: 'Guest'); ?> </div>
        <img src="assets0/b.svg">
      </div>
      <a href="profile.php.html">
        <div class="ph">
          <img src="../uploads/<?php echo htmlspecialchars($photo); ?>" alt="profile-pic">
        </div>
      </a>
    </div>
    
    
   <script src="https://cdn.jsdelivr.net/npm/@hotwired/turbo@7.3.0/dist/turbo.min.js"></script>
<script>
  document.addEventListener("turbo:load", () => {
    document.querySelectorAll('a').forEach(link => {
      link.addEventListener('mouseover', () => {
        if (link.href) Turbo.preload(link.href);
      });
    });
  });
</script>

<!-- Sidebar Overlay -->
<div id="sidebarOverlay">
  <div class="sidebarContent">
    <button id="closeSidebar" class="close-btn">&times;</button>

    <div style="margin: 20px 10px;">
      <img src="../logo-1.png" style="width:7em;" loading="lazy">
    </div>

    <div class="d-flex align-items-center" style="margin: 25px 10px;">
      <div>
        <a href="profile.php.html">
          <div class="ph">
            <img src="../uploads/<?php echo htmlspecialchars($photo); ?>" loading="lazy">
          </div>
        </a> 
      </div>
      <div class="d-flex flex-column" style="padding-left: 5px;">
        <div class="username"><?php echo ucwords($name ?: 'Guest'); ?>
</div>
        <div class="email"><?php echo $email ?: 'N/A'; ?>
</div>
      </div>
    </div>
     <div style="margin-top: 30px;">
      <a href="logout.php" class="logout-btn">Log Out</a>
    </div> <br><br> 

    <div class="mycard" style="padding-top: 3%;">
      <table class="xtable">
        <tr class="cld">
          <td class="lsi">Join Date:</td>
          <td> <?php 
            if ($date_created) {
                echo date_format(date_create($date_created), "F d, Y h:i a");
            } else {
                echo "N/A";
            }
            ?></td>
        </tr>
        <tr class="cld">
          <td class="lsi">Last Login:</td>
          <td><td><?php echo $last_login ?: 'Never'; ?></td>
        </tr>
        <tr class="cld">
          <td class="lsi">Kyc Status:</td>
          <td class="desc text-light">   <?php echo ucfirst($data ?: 'Unverified'); ?>
</span>

          </td>
        </tr>
        <tr class="cld">
          <td class="lsi">Account Type:</td>
          <td><?php echo ucfirst($plan); ?></td>
        </tr>
      </table>
    </div>

    <div style="padding: 5px;"></div>

    <div class="box32" style="margin-bottom: 5px;">
      <a href="../dashboard.php" class="d-flex justify-content-center texta">Dashboard</a>
      <div class="d-flex justify-content-center textb">Click to Access Portfolio</div>
    </div>

    <div class="others-row d-flex">
  <a href="trader.php.html" class="box32n d-flex flex-column">
    <div class="d-flex justify-content-center texta">Copy Experts</div>
    <div class="d-flex justify-content-center textb">list of trusted expert we have partnered</div>
  </a>
  <a href="deposit.php.html" class="box32n-r d-flex flex-column">
    <div class="d-flex justify-content-center texta">Deposit</div>
    <div class="d-flex justify-content-center textb">Add Funds To Your Account</div>
  </a>
</div>
<div class="others-row d-flex">
  <a href="withdrawal.php.html" class="box32n d-flex flex-column">
    <div class="d-flex justify-content-center texta">Withdrawal</div>
    <div class="d-flex justify-content-center textb">Withdraw Funds With Various Methods</div>
  </a>
  <a href="accountUpgrade.php.html" class="box32n-r d-flex flex-column">
    <div class="d-flex justify-content-center texta">Account Upgrade</div>
    <div class="d-flex justify-content-center textb">Upgrade To Access More</div>
  </a>
</div>

<div class="others-row d-flex">
    <a href="idv.php.html" class="box32n d-flex flex-column">
    <div class="d-flex justify-content-center texta">KYC</div>
    <div class="d-flex justify-content-center textb">Verify Identity</div>
  </a>
    
  <a href="trading-live.php.html" class="box32n d-flex flex-column">
    <div class="d-flex justify-content-center texta">Live Trading</div>
    <div class="d-flex justify-content-center textb">Trade Crypto and Fx In Real Time</div>
  </a>
  <a href="trading-stock.php.html" class="box32n-r d-flex flex-column">
    <div class="d-flex justify-content-center texta">Stock Trading</div>
    <div class="d-flex justify-content-center textb">Invest In The Stock Markets</div>
  </a>
</div>

<div class="others-row d-flex">
  <a href="insurance.php.html" class="box32n d-flex flex-column">
    <div class="d-flex justify-content-center texta">Insurance Deposit</div>
    <div class="d-flex justify-content-center textb">Insure Your Funds</div>
  </a>
  <a href="signal-package.php.html" class="box32n-r d-flex flex-column">
    <div class="d-flex justify-content-center texta">Signal Subscription</div>
    <div class="d-flex justify-content-center textb">Subscribe To Our Signals</div>
  </a>
</div>

<div class="others-row d-flex">
  <a href="live-qoutes.php.html" class="box32n d-flex flex-column">
    <div class="d-flex justify-content-center texta">Live Quotes</div>
    <div class="d-flex justify-content-center textb">Provides a quick glance at the latest market</div>
  </a>
  <a href="Live-charts.php.html" class="box32n-r d-flex flex-column">
    <div class="d-flex justify-content-center texta">Live Charts</div>
    <div class="d-flex justify-content-center textb">Provide free real-time forex and bitcoin data</div>
  </a>
</div>

<div class="others-row d-flex">
  <a href="calender.php.html" class="box32n d-flex flex-column">
    <div class="d-flex justify-content-center texta">Calender</div>
    <div class="d-flex justify-content-center textb">Shows key upcoming economic events</div>
  </a>
  <a href="buy-crypto.php.html" class="box32n-r d-flex flex-column">
    <div class="d-flex justify-content-center texta">Buy Crypto</div>
    <div class="d-flex justify-content-center textb">list of trusted crypto merchants we have partnered</div>
  </a>
</div>

<div class="others-row d-flex">
  <a href="transactions.php.html" class="box32n d-flex flex-column">
    <div class="d-flex justify-content-center texta">Transactions</div>
    <div class="d-flex justify-content-center textb">View Your Transactions Made</div>
  </a>
  <a href="mailto:support@tradeoakcapital.app" class="box32n-r d-flex flex-column">
    <div class="d-flex justify-content-center texta">Email US</div>
    <div class="d-flex justify-content-center textb">Contact Us Via Email</div>
  </a>
</div>


    <div class="d-flex justify-content-center d34">
     <?php echo APP_COPYRIGHT ?>
    </div>
  </div>
</div>

<!-- Sidebar CSS -->
<style>
#sidebarOverlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.6);
  display: none;
  z-index: 9999;
}

.sidebarContent {
  width: 100%;
  height: 100%;
  background: #111;
  color: white;
  padding: 15px;
  overflow-y: auto;
  position: absolute;
  left: 0;
  top: 0;
  box-shadow: 2px 0 5px rgba(0,0,0,0.5);
  animation: slideIn 0.3s ease forwards;
}

@keyframes slideIn {
  from { transform: translateX(-100%); }
  to { transform: translateX(0); }
}

.close-btn {
  position: absolute;
  top: 10px;
  right: 15px;
  background: none;
  border: none;
  color: white;
  font-size: 28px;
  cursor: pointer;
}
</style>

<!-- Sidebar Script -->
<script>
document.addEventListener("DOMContentLoaded", function () {
  const openBtn = document.getElementById("openSidebar");
  const closeBtn = document.getElementById("closeSidebar");
  const overlay = document.getElementById("sidebarOverlay");

  if (openBtn) {
    openBtn.addEventListener("click", function () {
      overlay.style.display = "block";
    });
  }

  if (closeBtn) {
    closeBtn.addEventListener("click", function () {
      overlay.style.display = "none";
    });
  }
});
</script>