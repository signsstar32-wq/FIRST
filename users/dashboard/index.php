
<?php include("../include/header.php") ?>
<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("../include/header.php");

// Get user data from database
$db = new Database();
$stats = $db->getUserStats($currentUser['id']);

// Initialize variables with default values if not set
$balance = $stats['balance'] ?? 0;
$total_profit = $stats['total_profit'] ?? 0;
$active_investment = $stats['active_investment'] ?? 0;
$total_invested = $stats['total_invested'] ?? 0;
$total_withdrawn = $stats['total_withdrawn'] ?? 0;
$roi = $stats['roi'] ?? 0;
$profit_rate = $stats['profit_rate'] ?? 0;
$last_profit = $stats['last_profit'] ?? 'Never';

// Get referral link with APP_URL
$referralLink = APP_URL . "/auth/register.php?ref=" . $stats['referral_code'];

// Get recent trades
$recentTrades = $db->getUserTrades($currentUser['id'], 5);

// Get trading limits for display
$min_trade = floatval($db->getSetting('min_trade', 10));
$max_trade = floatval($db->getSetting('max_trade', 1000));
$trade_profit = floatval($db->getSetting('trade_profit', 85));

// Get withdrawal fee from settings
$withdrawalFee = floatval($db->getSetting('withdrawal_fee', 0));

// Handle trade form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Form submitted: " . print_r($_POST, true));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_type'])) {
    // Get form data
    $amount = floatval($_POST['amount']);
    $asset = $_POST['asset'] ?? 'EURUSD'; // Default to EURUSD if not set
    $type = $_POST['order_type'];
    
    error_log("Processing trade - Amount: $amount, Asset: $asset, Type: $type");
    
    // Create trade data
    $tradeData = [
        'user_id' => $currentUser['id'],
        'asset' => $asset,
        'amount' => $amount,
        'type' => $type
    ];
    
    // Create the trade
    $result = $db->createTrade($tradeData);
    
    if ($result['success']) {
        $success = $result['message'];
        // Refresh user stats
        $stats = $db->getUserStats($currentUser['id']);
        $balance = $stats['balance'];
        
        // Update UI
        echo "<script>
            $(document).ready(function() {
                $('.stats h3').first().text('$ " . number_format($balance, 2) . "');
                $('.alert-info').text('Available Balance: $" . number_format($balance, 2) . "');
                $('#tradeModal').modal('hide');
                location.reload(); // Refresh to show updated balance
            });
        </script>";
    } else {
        $error = $result['message'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_proof'])) {
    $user_id = $_SESSION['user_id'];
    $amount = $_POST['amount'];
    $currency = $_POST['currency'];
    $network = $_POST['network'] ?? null;

    // Handle file upload
    $proof_image = null;
    if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['proof_image']['name'], PATHINFO_EXTENSION);
        $proof_image = 'proof_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['proof_image']['tmp_name'], '../../../uploads/' . $proof_image);
    }

    $status = 'pending';
    $notes = '';
    $processed_by = null;
    $processed_at = null;
    $transaction_id = '';

    $stmt = $db->prepare("INSERT INTO deposits (
        user_id, amount, payment_method, status, notes, processed_by, processed_at, created_at, transaction_id, proof_image
    ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)");
    $stmt->bind_param(
        "idsssssss",
        $user_id,
        $amount,
        $currency,
        $status,
        $notes,
        $processed_by,
        $processed_at,
        $transaction_id,
        $proof_image
    );
    $stmt->execute();
    $stmt->close();

    echo "<div class='alert alert-success' style='max-width:450px;margin:40px auto;text-align:center;'>Deposit submitted! Awaiting admin approval.</div>";
    exit;
}

// Fetch the user's total deposits
$userId = $currentUser['id'];
$totalDeposits = 0;
$query = "SELECT SUM(amount) as total FROM transactions WHERE user_id = ? AND type = 'deposit' AND status = 'completed'";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($totalDeposits);
$stmt->fetch();
$stmt->close();
$totalDeposits = $totalDeposits ?? 0;

// Fetch the user's total withdrawals
$totalWithdrawals = 0;
$query = "SELECT SUM(amount) as total FROM withdrawals WHERE user_id = ? AND status = 'completed'";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($totalWithdrawals);
$stmt->fetch();
$stmt->close();
$totalWithdrawals = $totalWithdrawals ?? 0;

// Fetch transactions with improved error handling
$where = ["user_id = ?"];
$params = [$userId];
$types = "i";

// Transaction ID search
if (!empty($_GET['search'])) {
    $where[] = "transaction_id LIKE ?";
    $params[] = "%" . $_GET['search'] . "%";
    $types .= "s";
}

// Wallet type filter
if (!empty($_GET['wallet_type'])) {
    $where[] = "wallet_type = ?";
    $params[] = $_GET['wallet_type'];
    $types .= "s";
}

// Source filter
if (!empty($_GET['source'])) {
    $where[] = "source = ?";
    $params[] = $_GET['source'];
    $types .= "s";
}

// Date range filter (assuming 'date' is in format "YYYY-MM-DD - YYYY-MM-DD")
if (!empty($_GET['date'])) {
    $dates = explode(' - ', $_GET['date']);
    if (count($dates) == 2) {
        $where[] = "DATE(created_at) BETWEEN ? AND ?";
        $params[] = $dates[0];
        $params[] = $dates[1];
        $types .= "ss";
    }
}

$whereSql = implode(" AND ", $where);
$query = "SELECT * FROM transactions WHERE $whereSql ORDER BY created_at DESC LIMIT 100";

// Debug: Log the query and parameters
error_log("Transaction query: " . $query);
error_log("Transaction params: " . print_r($params, true));

$stmt = $db->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$transactions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Debug: Log the results
error_log("Transactions found: " . count($transactions));
if (!empty($transactions)) {
    error_log("First transaction: " . print_r($transactions[0], true));
}

$totalRejectedWithdrawalsCount = 0;
$query = "SELECT COUNT(*) as total FROM transactions WHERE user_id = ? AND type = 'withdrawal' AND status = 'rejected'";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($totalRejectedWithdrawalsCount);
$stmt->fetch();
$stmt->close();
$totalRejectedWithdrawalsCount = $totalRejectedWithdrawalsCount ?? 0;

$totalPendingWithdrawalsCount = 0;
$query = "SELECT COUNT(*) as total FROM transactions WHERE user_id = ? AND type = 'withdrawal' AND status = 'pending'";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($totalPendingWithdrawalsCount);
$stmt->fetch();
$stmt->close();
$totalPendingWithdrawalsCount = $totalPendingWithdrawalsCount ?? 0;

$matrixEarnings = 0;
$stmt = $db->prepare("SELECT SUM(amount) as total FROM matrix_commissions WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($matrixEarnings);
$stmt->fetch();
$stmt->close();

$activeMatrixInvestments = 0;
$stmt = $db->prepare("SELECT COUNT(*) as total FROM matrix_investments WHERE user_id = ? AND status = 'active'");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($activeMatrixInvestments);
$stmt->fetch();
$stmt->close();
?>
    <div class="bd-example bd-example-tabs">
      <ul class="nav nav-pills mb-3 nav-fill bar-bg" id="pills-tab" role="tablist">
        <li class="nav-item">
          <a class="nav-link" id="pills-trading-view -tab" data-toggle="pill" href="#pills-trading-view " role="tab" aria-controls="pills-trading-view " aria-selected="true">Live Markets</a>
        </li>
        <li class="nav-item">
          <a class="nav-link  active show" id="pills-portfolio-tab" data-toggle="pill" href="#pills-portfolio" role="tab" aria-controls="pills-portfolio" aria-selected="false">Portfolio</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" id="pills-transaction-tab" data-toggle="pill" href="#pills-transaction" role="tab" aria-controls="pills-transaction" aria-selected="false">Activities</a>
        </li>
      </ul>
      <div class="tab-content" id="pills-tabContent" style="margin-top: 23px;">
        <div class="tab-pane fade" id="pills-trading-view " role="tabpanel" aria-labelledby="pills-trading-view -tab">

          <div>
            <!-- put widget here -->
            <!-- TradingView Widget BEGIN -->
            <div class="tradingview-widget-container">
              <div class="tradingview-widget-container__widget"></div>
              <div class="tradingview-widget-copyright"><a href="https://www.tradingview.com/" rel="noopener nofollow" target="_blank"><span class="blue-text">Track all markets on TradingView</span></a></div>
              <script type="text/javascript" src="../../external-embedding/embed-widget-screener.js" async="">
                  {
                    "width": "100%",
                      "height": "800",
                        "defaultColumn": "overview",
                          "screener_type": "crypto_mkt",
                            "displayCurrency": "USD",
                              "colorTheme": "dark",
                                "locale": "en",
                                  "isTransparent": true
                  }
                </script>
            </div>
            <!-- TradingView Widget END -->
          </div>


          <!-- end-->
        </div>
        
        <div class="tab-pane fade  active show" id="pills-portfolio" role="tabpanel" aria-labelledby="pills-portfolio-tab">

          <div class="user-sum">

            <div class="d-flex justify-content-center">
              <div class="d-flex flex-column">
               <!-- Portfolio Balance -->
<div class="d-flex justify-content-center pb">Portfolio Balance</div>
<div class="d-flex justify-content-center amt">
    <div id="link11">
        <?php echo htmlspecialchars($cs) . number_format((float)$balance, 2); ?>
    </div>
</div>

<!-- Withdrawable Balance -->
<div class="d-flex justify-content-center pb">Withdrawable Balance</div>
<div class="d-flex justify-content-center amt">
    <div id="link11" style="color:green;">
        <?php echo htmlspecialchars($cs) . number_format((float)$profit, 2); ?>
    </div>
</div>

                <div class="d-flex justify-content-center pb">Withdrawable Balance</div>
                <div class="d-flex justify-content-center amt">
                  <div id="link11" style="color:green;"><br>
<b>Warning</b>:  Undefined variable $cs in <b>/home/tradeoak/equitymarketholdings.ltd/users/dashboard.php</b> on line <b>162</b><br>
<br>
<b>Warning</b>:  Undefined variable $profit in <b>/home/tradeoak/equitymarketholdings.ltd/users/dashboard.php</b> on line <b>162</b><br>
<br>
<b>Deprecated</b>:  number_format(): Passing null to parameter #1 ($num) of type float is deprecated in <b>/home/tradeoak/equitymarketholdings.ltd/users/dashboard.php</b> on line <b>162</b><br>
0.00</div>
                </div>
                

              </div>
            </div>

            <div style="margin-top: 10px;">
              <!-- put widget here -->
              <!-- TradingView Widget BEGIN -->
              <div class="tradingview-widget-container">
                <div class="tradingview-widget-container__widget"></div>
                <div class="tradingview-widget-copyright"><a href="https://www.tradingview.com/" rel="noopener nofollow" target="_blank"><span class="blue-text"></span></a></div>
                <script type="text/javascript" src="../external-embedding/embed-widget-tickers.js" async="">
                    {
                      "symbols": [
                        {
                          "proName": "BITSTAMP:BTCUSD",
                          "title": "Bitcoin"
                        },
                        {
                          "proName": "BITSTAMP:ETHUSD",
                          "title": "Ethereum"
                        }
                      ],
                        "colorTheme": "dark",
                          "isTransparent": true,
                            "showSymbolLogo": true,
                              "locale": "en"
                    }
                  </script>
              </div>
              <!-- TradingView Widget END -->
            </div>

<style>
/* Container */
.progress {
  position: relative;
  background-color: #f1f1f1;
  border-radius: 9px;
  overflow: hidden;
}

/* Common progress bar base */
.progress-bar {
  height: 18px;
  line-height: 18px;
  font-size: 12px;
  text-align: center;
  color: white;
  font-weight: bold;
  position: relative;
  transition: width 2s ease-in-out; /* animation from 0% to target */
  animation: glow 1.5s infinite alternate;
  background-size: 30px 30px;
  background-repeat: repeat;
  background-position: 0 0;
}

/* Red for 0-30% */
.progress-red {
  background-color: #e74c3c;
  background-image: repeating-linear-gradient(
    45deg,
    rgba(255, 255, 255, 0.2) 0px,
    rgba(255, 255, 255, 0.2) 15px,
    transparent 15px,
    transparent 30px
  );
  animation: glowRed 1.5s infinite alternate, moveStripes 1s linear infinite;
}

/* Faded green for 31-75% */
.progress-faded-green {
  background-color: #6fc3dc;
  background-image: repeating-linear-gradient(
    45deg,
    rgba(255, 255, 255, 0.3) 0px,
    rgba(255, 255, 255, 0.3) 15px,
    transparent 15px,
    transparent 30px
  );
  animation: glowGreen 1.5s infinite alternate, moveStripes 1s linear infinite;
}

/* Strong green for 76-100% */
.progress-strong-green {
  background-color: #2ecc71;
  background-image: repeating-linear-gradient(
    45deg,
    rgba(255, 255, 255, 0.3) 0px,
    rgba(255, 255, 255, 0.3) 15px,
    transparent 15px,
    transparent 30px
  );
  animation:
    pulseGlow 2s ease-in-out infinite,
    moveStripes 1s linear infinite;
}
@keyframes pulseGlow {
  0%   { box-shadow: 0 0 0px #2ecc71; }
  50%  { box-shadow: 0 0 15px #2ecc71; }
  100% { box-shadow: 0 0 0px #2ecc71; }
}


/* Glow animation */
@keyframes glowRed {
  0% { box-shadow: 0 0 5px #e74c3c; }
  100% { box-shadow: 0 0 15px #e74c3c; }
}

@keyframes glowGreen {
  0% { box-shadow: 0 0 5px #2ecc71; }
  100% { box-shadow: 0 0 15px #2ecc71; }
}

/* Stripes movement */
@keyframes moveStripes {
  0% { background-position: 0 0; }
  100% { background-position: 30px 0; }
}
</style>

<p style="text-align: center;">Trading progress</p>

<div class="progress mt-3" style="max-width: 400px; margin: auto;">
  <div class="progress-bar progress-red" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" id="tradingProgressBar">
    0%
  </div>
</div>

<script>
// Animate bar fill from 0 to target
document.addEventListener("DOMContentLoaded", function() {
  const bar = document.getElementById("tradingProgressBar");
  const percent = 0;
  setTimeout(() => {
    bar.style.width = percent + "%";
  }, 100); // delay for smoother load
});
</script>


            <br>
<b>Warning</b>:  Undefined variable $signal_strength in <b>/home/tradeoak/equitymarketholdings.ltd/users/dashboard.php</b> on line <b>354</b><br>

<p style="text-align: center; margin-top:30px;">Signal strength</p>
<div style="
    display: flex;
    gap: 4px;
    justify-content: center;
    align-items: center;
    background-color: #111;
    padding: 10px;
    border-radius: 8px;
    max-width: 400px;
    margin: 0 auto;
    margin-to:-10px;
">
            <div style="
            width: 36px;
            height: 10px;
            border-radius: 4px;
            background-color: #eb4651;
            box-shadow: 0 0 3px rgba(0,0,0,0.4);
            transition: background-color 0.4s ease;
        "></div>
            <div style="
            width: 36px;
            height: 10px;
            border-radius: 4px;
            background-color: #eb4651;
            box-shadow: 0 0 3px rgba(0,0,0,0.4);
            transition: background-color 0.4s ease;
        "></div>
            <div style="
            width: 36px;
            height: 10px;
            border-radius: 4px;
            background-color: #eb4651;
            box-shadow: 0 0 3px rgba(0,0,0,0.4);
            transition: background-color 0.4s ease;
        "></div>
            <div style="
            width: 36px;
            height: 10px;
            border-radius: 4px;
            background-color: #eb4651;
            box-shadow: 0 0 3px rgba(0,0,0,0.4);
            transition: background-color 0.4s ease;
        "></div>
            <div style="
            width: 36px;
            height: 10px;
            border-radius: 4px;
            background-color: #eb4651;
            box-shadow: 0 0 3px rgba(0,0,0,0.4);
            transition: background-color 0.4s ease;
        "></div>
            <div style="
            width: 36px;
            height: 10px;
            border-radius: 4px;
            background-color: #eb4651;
            box-shadow: 0 0 3px rgba(0,0,0,0.4);
            transition: background-color 0.4s ease;
        "></div>
            <div style="
            width: 36px;
            height: 10px;
            border-radius: 4px;
            background-color: #eb4651;
            box-shadow: 0 0 3px rgba(0,0,0,0.4);
            transition: background-color 0.4s ease;
        "></div>
            <div style="
            width: 36px;
            height: 10px;
            border-radius: 4px;
            background-color: #eb4651;
            box-shadow: 0 0 3px rgba(0,0,0,0.4);
            transition: background-color 0.4s ease;
        "></div>
            <div style="
            width: 36px;
            height: 10px;
            border-radius: 4px;
            background-color: #eb4651;
            box-shadow: 0 0 3px rgba(0,0,0,0.4);
            transition: background-color 0.4s ease;
        "></div>
            <div style="
            width: 36px;
            height: 10px;
            border-radius: 4px;
            background-color: #eb4651;
            box-shadow: 0 0 3px rgba(0,0,0,0.4);
            transition: background-color 0.4s ease;
        "></div>
    </div>

            <br>
<!-- Compact Dark Techy Profit Tab Styles -->
<style>
  .profit-tab-dark {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    background-color: #1c1f26;
    border-radius: 1rem;
    padding: 1.2rem;
    gap: 1rem;
    box-shadow: 0 0 12px rgba(0, 255, 255, 0.05);
  }

  .profit-card {
    flex: 1 1 220px;
    background: linear-gradient(145deg, #23272f, #1a1d23);
    border-radius: 0.75rem;
    padding: 1rem;
    transition: all 0.3s ease;
    box-shadow: 0 0 8px rgba(0, 255, 255, 0.08);
    position: relative;
  }

  .profit-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 0 16px rgba(0, 255, 255, 0.2);
  }

  .profit-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: #00fff2;
    margin-bottom: 0.3rem;
    animation: fadeInUp 0.6s ease;
  }

  .profit-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #e2e8f0;
    text-transform: uppercase;
  }

  .profit-desc {
    font-size: 0.75rem;
    color: #a0aec0;
    margin-top: 0.3rem;
    animation: fadeIn 0.9s ease;
  }

  @keyframes fadeInUp {
    from {
      transform: translateY(15px);
      opacity: 0;
    }

    to {
      transform: translateY(0);
      opacity: 1;
    }
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
    }

    to {
      opacity: 1;
    }
  }
</style>

<!-- Profit Tab Content -->
<div class="profit-tab-dark">
  <div class="profit-card">
    <div class="profit-value" id="link22">
      <br>
<b>Warning</b>:  Undefined variable $cs in <b>/home/tradeoak/equitymarketholdings.ltd/users/dashboard.php</b> on line <b>460</b><br>
<br>
<b>Warning</b>:  Undefined variable $profit in <b>/home/tradeoak/equitymarketholdings.ltd/users/dashboard.php</b> on line <b>460</b><br>
<br>
<b>Deprecated</b>:  number_format(): Passing null to parameter #1 ($num) of type float is deprecated in <b>/home/tradeoak/equitymarketholdings.ltd/users/dashboard.php</b> on line <b>460</b><br>
0.00    </div>
    <div class="profit-title">Active Earnings</div>
    <div class="profit-desc">Amount Earned From Live Trades</div>
  </div>

  <div class="profit-card">
    <div class="profit-value" id="link33">
      0%
    </div>
    <div class="profit-title">Trade Sessions Completed</div>
    <div class="profit-desc">Success Rate of Trading Activity</div>
  </div>
</div>


            
                    <div style="margin-top: 15px;">
            <!-- put widget here -->
            <div class="tradingview-widget-container">
              <div class="tradingview-widget-container__widget"></div>
              <script data-cfasync="false" src="../cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
              <script type="text/javascript" src="../../external-embedding/embed-widget-market-overview.js" async="">
                  {
                    "colorTheme": "dark"
                      , "dateRange": "12M"
                        , "showChart": true
                          , "locale": "en"
                            , "largeChartUrl": ""
                              , "isTransparent": true
                                , "showSymbolLogo": true
                                  , "width": "100%"
                                    , "height": "660"
                                      , "plotLineColorGrowing": "rgba(18,207,247,1)"
                                        , "plotLineColorFalling": "rgba(18,207,247,1)"
                                          , "gridLineColor": "rgba(240, 243, 250, 1)"
                                            , "scaleFontColor": "rgba(120, 123, 134, 1)"
                                              , "belowLineFillColorGrowing": "rgba(18,207,247,0.12)"
                                                , "belowLineFillColorFalling": "rgba(18,207,247,0.12)"
                                                  , "symbolActiveColor": "rgba(18,207,247,0.12)"
                                                    , "tabs": [{
                                                      "title": "Indices"
                                                      , "symbols": [{
                                                        "s": "FOREXCOM:SPXUSD"
                                                        , "d": "S&P 500"
                                                      }
                                                        , {
                                                        "s": "FOREXCOM:NSXUSD"
                                                        , "d": "Nasdaq 100"
                                                      }
                                                        , {
                                                        "s": "FOREXCOM:DJI"
                                                        , "d": "Dow 30"
                                                      }
                                                        , {
                                                        "s": "INDEX:NKY"
                                                        , "d": "Nikkei 225"
                                                      }
                                                        , {
                                                        "s": "INDEX:DEU30"
                                                        , "d": "DAX Index"
                                                      }
                                                        , {
                                                        "s": "FOREXCOM:UKXGBP"
                                                        , "d": "FTSE 100"
                                                      }
                                                      ]
                                                      , "originalTitle": "Indices"
                                                    }
                                                      , {
                                                      "title": "Commodities"
                                                      , "symbols": [{
                                                        "s": "CME_MINI:ES1!"
                                                        , "d": "S&P 500"
                                                      }
                                                        , {
                                                        "s": "CME:6E1!"
                                                        , "d": "Euro"
                                                      }
                                                        , {
                                                        "s": "COMEX:GC1!"
                                                        , "d": "Gold"
                                                      }
                                                        , {
                                                        "s": "NYMEX:CL1!"
                                                        , "d": "Crude Oil"
                                                      }
                                                        , {
                                                        "s": "NYMEX:NG1!"
                                                        , "d": "Natural Gas"
                                                      }
                                                        , {
                                                        "s": "CBOT:ZC1!"
                                                        , "d": "Corn"
                                                      }
                                                      ]
                                                      , "originalTitle": "Commodities"
                                                    }
                                                      , {
                                                      "title": "Bonds"
                                                      , "symbols": [{
                                                        "s": "CME:GE1!"
                                                        , "d": "Eurodollar"
                                                      }
                                                        , {
                                                        "s": "CBOT:ZB1!"
                                                        , "d": "T-Bond"
                                                      }
                                                        , {
                                                        "s": "CBOT:UB1!"
                                                        , "d": "Ultra T-Bond"
                                                      }
                                                        , {
                                                        "s": "EUREX:FGBL1!"
                                                        , "d": "Euro Bund"
                                                      }
                                                        , {
                                                        "s": "EUREX:FBTP1!"
                                                        , "d": "Euro BTP"
                                                      }
                                                        , {
                                                        "s": "EUREX:FGBM1!"
                                                        , "d": "Euro BOBL"
                                                      }
                                                      ]
                                                      , "originalTitle": "Bonds"
                                                    }
                                                      , {
                                                      "title": "Forex"
                                                      , "symbols": [{
                                                        "s": "FX:EURUSD"
                                                      }
                                                        , {
                                                        "s": "FX:GBPUSD"
                                                      }
                                                        , {
                                                        "s": "FX:USDJPY"
                                                      }
                                                        , {
                                                        "s": "FX:USDCHF"
                                                      }
                                                        , {
                                                        "s": "FX:AUDUSD"
                                                      }
                                                        , {
                                                        "s": "FX:USDCAD"
                                                      }
                                                      ]
                                                      , "originalTitle": "Forex"
                                                    }
                                                    ]
                  }

                </script>
            </div>
            <!-- TradingView Widget END -->
            <br><br>
            <!-- TradingView Widget BEGIN -->
<div class="tradingview-widget-container">
  <div class="tradingview-widget-container__widget"></div>
  <div class="tradingview-widget-copyright"><a href="https://www.tradingview.com/" rel="noopener nofollow" target="_blank"><span class="blue-text">Track all markets on TradingView</span></a></div>
  <script type="text/javascript" src="../../external-embedding/embed-widget-technical-analysis.js" async="">
  {
  "interval": "1m",
  "width": "100%",
  "isTransparent": false,
  "height": "450",
  "symbol": "NASDAQ:AAPL",
  "showIntervalTabs": true,
  "displayMode": "single",
  "locale": "en",
  "colorTheme": "dark"
}
  </script>
</div>
<!-- TradingView Widget END -->
          </div>


          <!-- end-->
        </div>
        <div class="tab-pane fade" id="pills-transaction" role="tabpanel" aria-labelledby="pills-transaction-tab">



          <div class="bottom-half">
            <div class="mid">
              

              <div style="margin-top:-15px;">

                <!-- TradingView Widget BEGIN -->
                <!-- TradingView Widget BEGIN -->
                <div class="tradingview-widget-container">
                  <div class="tradingview-widget-container__widget"></div>
                  <div class="tradingview-widget-copyright"><a href="https://www.tradingview.com/" rel="noopener nofollow" target="_blank"><span class="blue-text"></span></a></div>
                  <script type="text/javascript" src="../../external-embedding/embed-widget-single-quote.js" async="">
                      {
                        "symbol": "BINANCE:BTCUSDT",
                          "width": 350,
                            "colorTheme": "dark",
                              "isTransparent": true,
                                "locale": "en"
                      }
                    </script>
                </div>
                <!-- TradingView Widget END -->
                <!-- TradingView Widget END -->

              </div>




            </div>


          </div>


 
          <div class="row" style="margin-top: 38px;">

            <div class="col coll">
              <div class="d-flex flex-column onecard">
                <div class="onecard-amt"><br>
<b>Warning</b>:  Undefined variable $cs in <b>/home/tradeoak/equitymarketholdings.ltd/users/dashboard.php</b> on line <b>759</b><br>
<br>
<b>Warning</b>:  Undefined variable $active_depo in <b>/home/tradeoak/equitymarketholdings.ltd/users/dashboard.php</b> on line <b>759</b><br>
<br>
<b>Deprecated</b>:  number_format(): Passing null to parameter #1 ($num) of type float is deprecated in <b>/home/tradeoak/equitymarketholdings.ltd/users/dashboard.php</b> on line <b>759</b><br>
0.00 </div>
                <div class="onecard-title">Active Deposit
                </div>
                <div class="onecard-last oc-blue">
                  <span></span> &nbsp;Investment Amount
                </div>
              </div>
            </div>
            <div class="col">
              <div class="d-flex flex-column  onecard">
                <div class="onecard-amt"><br>
<b>Warning</b>:  Undefined variable $cs in <b>/home/tradeoak/equitymarketholdings.ltd/users/dashboard.php</b> on line <b>769</b><br>
<br>
<b>Warning</b>:  Undefined variable $pending_with in <b>/home/tradeoak/equitymarketholdings.ltd/users/dashboard.php</b> on line <b>769</b><br>
<br>
<b>Deprecated</b>:  number_format(): Passing null to parameter #1 ($num) of type float is deprecated in <b>/home/tradeoak/equitymarketholdings.ltd/users/dashboard.php</b> on line <b>769</b><br>
0.00</div>
                <div class="onecard-title">Pending Withdrawal
                </div>
                <div class="onecard-last oc-y">
                  <span></span> &nbsp;Amount Not Processed
                </div>
              </div>
            </div>
          </div>

          <div class="row" style="margin-top: 9px;">

            
            <div class="col">
              <div class="d-flex flex-column  onecard">
                <div class="onecard-amt"><br>
<b>Warning</b>:  Undefined variable $cs in <b>/home/tradeoak/equitymarketholdings.ltd/users/dashboard.php</b> on line <b>784</b><br>
<br>
<b>Warning</b>:  Undefined variable $comp_with in <b>/home/tradeoak/equitymarketholdings.ltd/users/dashboard.php</b> on line <b>784</b><br>
<br>
<b>Deprecated</b>:  number_format(): Passing null to parameter #1 ($num) of type float is deprecated in <b>/home/tradeoak/equitymarketholdings.ltd/users/dashboard.php</b> on line <b>784</b><br>
0.00 </div>
                <div class="onecard-title">Total Withdrawal
                </div>
                <div class="onecard-last oc-r">
                  <span></span> &nbsp;Total Amount Debited
                </div>
              </div>
            </div>
          </div>

          <div class="row" style="margin-top: 9px;">

            <div class="col coll">
              <div class="d-flex flex-column onecard">
                <div class="onecard-amt"><br>
<b>Warning</b>:  Undefined variable $cs in <b>/home/tradeoak/equitymarketholdings.ltd/users/dashboard.php</b> on line <b>798</b><br>
<br>
<b>Warning</b>:  Undefined variable $last_depo in <b>/home/tradeoak/equitymarketholdings.ltd/users/dashboard.php</b> on line <b>798</b><br>
<br>
<b>Deprecated</b>:  number_format(): Passing null to parameter #1 ($num) of type float is deprecated in <b>/home/tradeoak/equitymarketholdings.ltd/users/dashboard.php</b> on line <b>798</b><br>
0.00 </div>
                <div class="onecard-title">Last Deposit
                </div>
                <div class="onecard-last oc-g">
                  <span></span> &nbsp;Last Amount Credited
                </div>
              </div>
            </div>
            <div class="col">
              <div class="d-flex flex-column  onecard">
                <div class="onecard-amt"><br>
<b>Warning</b>:  Undefined variable $cs in <b>/home/tradeoak/equitymarketholdings.ltd/users/dashboard.php</b> on line <b>808</b><br>
<br>
<b>Warning</b>:  Undefined variable $last_with in <b>/home/tradeoak/equitymarketholdings.ltd/users/dashboard.php</b> on line <b>808</b><br>
<br>
<b>Deprecated</b>:  number_format(): Passing null to parameter #1 ($num) of type float is deprecated in <b>/home/tradeoak/equitymarketholdings.ltd/users/dashboard.php</b> on line <b>808</b><br>
0.00 </div>
                <div class="onecard-title">Last Withdrawal
                </div>
                <div class="onecard-last oc-c">
                  <span></span> &nbsp;Last Amount Debited
                </div>
              </div>
            </div>
          </div>
          <!--end-->
        </div>
      </div>
    </div>



<?php include("../include/footer.php") ?>