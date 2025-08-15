<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . '/../datafile/config.php');
require_once(__DIR__ . '/../includes/Database.php');
require_once(__DIR__ . '/../includes/Auth.php');
require_once(__DIR__ . '/../includes/ReCaptcha.php');

use Includes\ReCaptcha;

$auth = new Auth();
$error = '';
$success = '';
$response = [];

// If already logged in, redirect based on role
if ($auth->isLoggedIn()) {
    if ($auth->isAdmin()) {
        header('Location: /lldash/xxadmin/index/index.php');
    } else {
        header('Location: /users/dashboard/index.php');
    }
    exit;
}

// Handle AJAX login request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
    
    // Debug log
    error_log("Login attempt - Email: $email");
    
    // Verify reCAPTCHA
    $recaptcha = new ReCaptcha("6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe");
    
    if (!$recaptcha_response) {
        $response = ['success' => false, 'message' => 'Please complete the captcha'];
    } else {
        $verify = $recaptcha->verify($recaptcha_response, $_SERVER['REMOTE_ADDR']);
        
        if (!$verify->success) {
            $response = ['success' => false, 'message' => 'Invalid captcha response'];
        } else {
            $result = $auth->login($email, $password);
            error_log("Login result: " . print_r($result, true));
            
            if ($result['success']) {
                $response = [
                    'success' => true,
                    'redirect' => $result['role'] === 'admin' || $result['role'] === 'super_admin' 
                        ? '/lldash/xxadmin/index/index.php' 
                        : 'localhost/equitymarketholdings.ltd/users/dashboard/index.php'
                ];
            } else {
                $response = ['success' => false, 'message' => 'Invalid email or password'];
            }
        }
    }
    
    echo json_encode($response);
    exit;
}

// Keep your existing HTML, but add error display
?>
<!DOCTYPE html>
<html lang="en" class="dark-mode">


<!-- Mirrored from digixtradesecurities.com/Account/Login by HTTrack Website Copier/3.x [XR&CO'2014], Fri, 23 May 2025 13:05:32 GMT -->
<!-- Added by HTTrack --><meta http-equiv="content-type" content="text/html;charset=utf-8"><!-- /Added by HTTrack -->
<head>
    <meta charset="utf-8">
    <title><?php echo APP_TITLE ?></title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport">
    <meta content="" name="description">
    <meta content="" name="author">

    <link href="../css-1?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <link href="../assets/css/vendor.min.css" rel="stylesheet">
    <link href="../assets/css/default/app.min.css" rel="stylesheet">

</head>
<body class='pace-top'>

    <div id="loader" class="app-loader">
        <span class="spinner"></span>
    </div>


    <div id="app" class="app">

        <div class="login login-v2 fw-bold">

            <div class="login-cover">
                <div class="login-cover-img" style="background-image: url(../1.jpg)" data-id="login-cover-image"></div>
                <div class="login-cover-bg"></div>
            </div>


            <div class="login-container">

                <div class="login-header">
                    <div class="brand">
                        <div class="d-flex align-items-center">
                           <img src="<?php echo APP_LOGO ?>" width="100%" height="100%">
                        </div>
                       
                    </div>
                    <div class="icon">
                        <i class="fa fa-lock"></i>
                    </div>
                </div>


                <div class="login-content">
                    <form method="post" action="#">
                        <div class="text-danger validation-summary-valid" data-valmsg-summary="true"><ul><li style="display:none"></li>
</ul></div>
                        <div class="form-floating mb-20px">
                            <input type="text" class="form-control fs-13px h-45px border-0" placeholder="Email Address" data-val="true" data-val-email="The Email field is not a valid e-mail address." data-val-required="The Email field is required." id="email" name="email" value="">
                            <label for="Email" class="d-flex align-items-center text-gray-600 fs-13px">Email Address</label>

                        </div>
                        <span class="text-danger field-validation-valid" data-valmsg-for="Email" data-valmsg-replace="true"></span>

                        <div class="form-floating mb-20px">
                            <input type="password" class="form-control fs-13px h-45px border-0" placeholder="Password" data-val="true" data-val-required="The Password field is required." id="password" name="password">
                            <label for="Password" class="d-flex align-items-center text-gray-600 fs-13px">Password</label>
                        </div>
                        <span class="text-danger field-validation-valid" data-valmsg-for="Email" data-valmsg-replace="true"></span>
  <div class="form-group">
                <div class="g-recaptcha" 
                     data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"
                     data-theme="light">
                </div>
            </div>
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                        <div class="form-check mb-20px">
                            <input class="form-check-input border-0" type="checkbox" data-val="true" data-val-required="The Remember me? field is required." id="RememberMe" name="RememberMe" value="true">
                            <label class="form-check-label fs-13px text-gray-500" for="RememberMe">
                                Remember Me
                            </label>
                        </div>
                        <div class="mb-20px">
                            <button type="submit" class="btn btn-primary d-block w-100 h-45px btn-lg">Sign me in</button>
                        </div>
                        <div class="text-gray-500">
                            Not a member yet? Click <a class="text-white" href="Register.php">here</a> to register.
                        </div>
                        <div class="text-gray-500">
                            Forgot Password? Click <a class="text-white" href="SendLink.php">here</a> to Reset.
                        </div>
                    <input name="__RequestVerificationToken" type="hidden" value="CfDJ8I8DDa5JhGZOqiGXG7WfnKmmtH7P-JfmtSrbaJh6GTK_sBr5y3dYs5Ez5Lxr_LFRm1aS8YaRm_KqfrfJbp5o0mOQx2K3R5i8M3aCDcDzanMtObDFk8ebC2jY6ClY5Yv-hbvh-MawDMprroWAO1oJe8s"><input name="RememberMe" type="hidden" value="false"></form>
                </div>

            </div>

        </div>


        


        


        <a href="javascript:;" class="btn btn-icon btn-circle btn-success btn-scroll-to-top" data-toggle="scroll-to-top"><i class="fa fa-angle-up"></i></a>

    </div>
    <script src="../assets/js/vendor.min.js" type="b1f316c0a61621cd7fba9118-text/javascript"></script>
    <script src="../assets/js/app.min.js" type="b1f316c0a61621cd7fba9118-text/javascript"></script>


    <script src="../assets/js/demo/login-v2.demo.js" type="b1f316c0a61621cd7fba9118-text/javascript"></script>

    <script type="b1f316c0a61621cd7fba9118-text/javascript">
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','www.google-analytics.com/analytics.html','ga');

        ga('create', 'UA-53034621-1', 'auto');
        ga('send', 'pageview');

    </script>
    <script src="../cdn-cgi/scripts/7d0fa10a/cloudflare-static/rocket-loader.min.js" data-cf-settings="b1f316c0a61621cd7fba9118-|49" defer=""></script>
    <script defer="" src="../beacon.min.js/v652eace1692a40cfa3763df669d7439c1639079717194" integrity="sha512-Gi7xpJR8tSkrpF7aordPZQlW2DLtzUlZcumS8dMQjwDHEnw9I7ZLyiOj/6tZStRBGtGgN6ceN6cMH8z7etPGlw==" data-cf-beacon='{"rayId":"7166678baf774c4f","version":"2021.12.0","r":1,"token":"4db8c6ef997743fda032d4f73cfeff63","si":100}' crossorigin="anonymous"></script>

    
</body>


<!-- Mirrored from digixtradesecurities.com/Account/Login by HTTrack Website Copier/3.x [XR&CO'2014], Fri, 23 May 2025 13:05:40 GMT -->
</html>
