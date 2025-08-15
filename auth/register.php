<?php
require_once("../datafile/config.php");
require_once("../includes/Database.php");
require_once("../includes/Auth.php");
require_once("../includes/ReCaptcha.php");
require_once("../includes/Settings.php");
require_once("../includes/email_functions.php");

use Includes\ReCaptcha;

session_start();

// Initialize Database and Settings
$db = new Database();
Settings::init($db);

$auth = new Auth();
$error = '';
$success = '';

// Get referral code from URL if exists
$ref = $_GET['ref'] ?? '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username            = trim($_POST['username'] ?? '');
    $name                = trim($_POST['name'] ?? '');
    $email               = trim($_POST['email'] ?? '');
    $password            = $_POST['password'] ?? '';
    $confirm_password    = $_POST['password_confirmation'] ?? '';
    $phone               = trim($_POST['phone'] ?? '');
    $country             = trim($_POST['country'] ?? '');
    $currency            = trim($_POST['currency'] ?? '');
    $ref                 = trim($_POST['ref'] ?? '');
    $recaptcha_response  = $_POST['g-recaptcha-response'] ?? '';

    // Verify reCAPTCHA
    $recaptcha = new ReCaptcha("6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe"); // Google test secret
    if (!$recaptcha_response) {
        $error = 'Please complete the captcha';
    } else {
        $verify = $recaptcha->verify($recaptcha_response, $_SERVER['REMOTE_ADDR']);
        if (!$verify->success) {
            $error = 'Invalid captcha response';
        } else {
            // Basic validation
            if (empty($username) || empty($name) || empty($email) || empty($password) || empty($phone) || empty($country) || empty($currency)) {
                $error = 'All fields are required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Invalid email format';
            } elseif ($password !== $confirm_password) {
                $error = 'Passwords do not match';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters long';
            } else {
                // Check referral code if provided
                $referred_by = null;
                if (!empty($ref)) {
                    $referrer = $db->getUserByReferralCode($ref);
                    if ($referrer) {
                        $referred_by = $referrer['id'];
                    } else {
                        $error = 'Invalid referral code';
                    }
                }

                if (!$error) {
                    // Attempt to register
                    $userData = [
                        'username' => $username,
                        'name' => $name,
                        'email' => $email,
                        'password' => $password,
                        'phone' => $phone,
                        'country' => $country,
                        'currency' => $currency,
                        'referred_by' => $referred_by
                    ];

                    $result = $auth->register($userData);

                    if ($result['success']) {
                        if (Settings::get('email_verification', true)) {
                            sendVerificationEmail($result['user_id'], $email);
                            $_SESSION['message'] = "Please check your email to verify your account.";
                        } else {
                            $db->updateVerificationStatus($result['user_id'], 'verified');
                            $_SESSION['message'] = "Your account has been created successfully.";
                        }
                        header('Location: users/dashboard.php');
                        exit;
                    } else {
                        $error = $result['message'];
                    }
                }
            }
        }
    }
}
?>
<!doctype html>

<html lang="en">


<!-- Mirrored from digixtradesecurities.com/Account/Register by HTTrack Website Copier/3.x [XR&CO'2014], Fri, 23 May 2025 13:05:20 GMT -->
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

        <div class="register register-with-news-feed">

            <div class="news-feed">
                <div class="news-image" style="background-image: url(../3.jpg)"></div>
                <div class="news-caption">
                    <img src="<?php echo APP_LOGO ?>">
                    <p>
                       <?php echo APP_NAME ?> Your Online Broker, world largest cryptocurrency online trading and investment platform. Trade cryptocurrency/CFDs all on our advanced, web-based trading platform.We apply a 100% DMA STP order execution model for all our clients.
                    </p>
                </div>
            </div>


            <div class="register-container">

                <div class="register-header mb-25px h1">
                    <div class="mb-1">Sign Up</div>
                    <small class="d-block fs-15px lh-16">Create your <?php echo APP_NAME ?> Account.</small>
                </div>

                <div class="register-content">
                    <div class="register-content">

    <?php if (!empty($error)): ?>
        <div style="color: red; padding: 10px; background: #ffd6d6; border: 1px solid red; margin-bottom: 10px;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div style="color: green; padding: 10px; background: #d6ffd6; border: 1px solid green; margin-bottom: 10px;">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <form method="post" class="fs-13px" action="">

                    <form method="post" class="fs-13px" action="#">
                        <div class="text-danger validation-summary-valid" data-valmsg-summary="true"><ul><li style="display:none"></li>
</ul></div>
                        <div class="mb-3">
                            <label class="mb-2">Name <span class="text-danger">*</span></label>
                            <div class="row gx-3">
                                <div class="col-md-6 mb-2 mb-md-0">
                                    <input type="text" class="form-control fs-13px" placeholder="Full name" data-val="true" data-val-required="The Full Name field is required." id="name" name="name" value="">
                                    <span class="text-danger field-validation-valid" data-valmsg-for="name" data-valmsg-replace="true"></span>
                                </div>
                                
                                <div class="col-md-6">
                                    <input type="text" class="form-control fs-13px" placeholder="User name" data-val="true" data-val-required="The Username field is required." id="username" name="username" value="">
                                    <span class="text-danger field-validation-valid" data-valmsg-for="username" data-valmsg-replace="true"></span>
                                </div>
                               
                            </div>
                        </div>
                        <div class="row gx-3 mb-3">
                            <div class="mb-12">
                                <label class="mb-2">Email <span class="text-danger">*</span></label>
                                <input type="text" class="form-control fs-13px" placeholder="xxx@xxx.xx" data-val="true" data-val-email="The Email field is not a valid e-mail address." data-val-required="The Email field is required." id="email" name="email" value="">
                            </div>
                            <span class="text-danger field-validation-valid" data-valmsg-for="email" data-valmsg-replace="true"></span>
                        </div>
                        <div class="row gx-3 mb-3">
                            <div class="mb-12">
                                <label class="mb-2">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control fs-13px" placeholder="+xxxxxxxx" data-val="true" data-val-phone="The Phone Number field is not a valid phone number." data-val-required="The Phone Number field is required." id="phone" name="phone" value="">
                            </div>
                            <span class="text-danger field-validation-valid" data-valmsg-for="phone" data-valmsg-replace="true"></span>
                        </div>
                        <div class="row gx-3 mb-3">
                            <div class="mb-6">
                                <label class="mb-2">Country<span class="text-danger">*</span></label>
                                <select type="text" class="form-control fs-13px" data-val="true" data-val-required="The Country field is required." id="country" name="country">
                                <option value="Afghanistan">Afghanistan</option>
<option value="Albania">Albania</option>
<option value="Algeria">Algeria</option>
<option value="American Samoa">American Samoa</option>
<option value="Andorra">Andorra</option>
<option value="Angola">Angola</option>
<option value="Anguilla">Anguilla</option>
<option value="Antarctica">Antarctica</option>
<option value="Antigua and Barbuda">Antigua and Barbuda</option>
<option value="Argentina">Argentina</option>
<option value="Armenia">Armenia</option>
<option value="Aruba">Aruba</option>
<option value="Australia">Australia</option>
<option value="Austria">Austria</option>
<option value="Azerbaijan">Azerbaijan</option>
<option value="Bahamas">Bahamas</option>
<option value="Bahrain">Bahrain</option>
<option value="Bangladesh">Bangladesh</option>
<option value="Barbados">Barbados</option>
<option value="Belarus">Belarus</option>
<option value="Belgium">Belgium</option>
<option value="Belize">Belize</option>
<option value="Benin">Benin</option>
<option value="Bermuda">Bermuda</option>
<option value="Bhutan">Bhutan</option>
<option value="Bolivia">Bolivia</option>
<option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
<option value="Botswana">Botswana</option>
<option value="Bouvet Island">Bouvet Island</option>
<option value="Brazil">Brazil</option>
<option value="British Indian Ocean Territory">British Indian Ocean Territory</option>
<option value="Brunei Darussalam">Brunei Darussalam</option>
<option value="Bulgaria">Bulgaria</option>
<option value="Burkina Faso">Burkina Faso</option>
<option value="Burundi">Burundi</option>
<option value="Cambodia">Cambodia</option>
<option value="Cameroon">Cameroon</option>
<option value="Canada">Canada</option>
<option value="Cape Verde">Cape Verde</option>
<option value="Cayman Islands">Cayman Islands</option>
<option value="Central African Republic">Central African Republic</option>
<option value="Chad">Chad</option>
<option value="Chile">Chile</option>
<option value="China">China</option>
<option value="Christmas Island">Christmas Island</option>
<option value="Cocos (Keeling) Islands">Cocos (Keeling) Islands</option>
<option value="Colombia">Colombia</option>
<option value="Comoros">Comoros</option>
<option value="Congo">Congo</option>
<option value="Cook Islands">Cook Islands</option>
<option value="Costa Rica">Costa Rica</option>
<option value="Croatia (Hrvatska)">Croatia (Hrvatska)</option>
<option value="Cuba">Cuba</option>
<option value="Cyprus">Cyprus</option>
<option value="Czech Republic">Czech Republic</option>
<option value="Denmark">Denmark</option>
<option value="Djibouti">Djibouti</option>
<option value="Dominica">Dominica</option>
<option value="Dominican Republic">Dominican Republic</option>
<option value="East Timor">East Timor</option>
<option value="Ecuador">Ecuador</option>
<option value="Egypt">Egypt</option>
<option value="El Salvador">El Salvador</option>
<option value="Equatorial Guinea">Equatorial Guinea</option>
<option value="Eritrea">Eritrea</option>
<option value="Estonia">Estonia</option>
<option value="Ethiopia">Ethiopia</option>
<option value="Falkland Islands (Malvinas)">Falkland Islands (Malvinas)</option>
<option value="Faroe Islands">Faroe Islands</option>
<option value="Fiji">Fiji</option>
<option value="Finland">Finland</option>
<option value="France">France</option>
<option value="France, Metropolitan">France, Metropolitan</option>
<option value="French Guiana">French Guiana</option>
<option value="French Polynesia">French Polynesia</option>
<option value="French Southern Territories">French Southern Territories</option>
<option value="Gabon">Gabon</option>
<option value="Gambia">Gambia</option>
<option value="Georgia">Georgia</option>
<option value="Germany">Germany</option>
<option value="Ghana">Ghana</option>
<option value="Gibraltar">Gibraltar</option>
<option value="Greece">Greece</option>
<option value="Greenland">Greenland</option>
<option value="Grenada">Grenada</option>
<option value="Guadeloupe">Guadeloupe</option>
<option value="Guam">Guam</option>
<option value="Guatemala">Guatemala</option>
<option value="Guernsey">Guernsey</option>
<option value="Guinea">Guinea</option>
<option value="Guinea-Bissau">Guinea-Bissau</option>
<option value="Guyana">Guyana</option>
<option value="Haiti">Haiti</option>
<option value="Heard and Mc Donald Islands">Heard and Mc Donald Islands</option>
<option value="Honduras">Honduras</option>
<option value="Hong Kong">Hong Kong</option>
<option value="Hungary">Hungary</option>
<option value="Iceland">Iceland</option>
<option value="India">India</option>
<option value="Indonesia">Indonesia</option>
<option value="Iran (Islamic Republic of)">Iran (Islamic Republic of)</option>
<option value="Iraq">Iraq</option>
<option value="Ireland">Ireland</option>
<option value="Isle of Man">Isle of Man</option>
<option value="Israel">Israel</option>
<option value="Italy">Italy</option>
<option value="Ivory Coast">Ivory Coast</option>
<option value="Jamaica">Jamaica</option>
<option value="Japan">Japan</option>
<option value="Jersey">Jersey</option>
<option value="Jordan">Jordan</option>
<option value="Kazakhstan">Kazakhstan</option>
<option value="Kenya">Kenya</option>
<option value="Kiribati">Kiribati</option>
<option value="Korea, Democratic People&#x27;s Republic of">Korea, Democratic People&#x27;s Republic of</option>
<option value="Korea, Republic of">Korea, Republic of</option>
<option value="Kosovo">Kosovo</option>
<option value="Kuwait">Kuwait</option>
<option value="Kyrgyzstan">Kyrgyzstan</option>
<option value="Lao People&#x27;s Democratic Republic">Lao People&#x27;s Democratic Republic</option>
<option value="Latvia">Latvia</option>
<option value="Lebanon">Lebanon</option>
<option value="Lesotho">Lesotho</option>
<option value="Liberia">Liberia</option>
<option value="Libyan Arab Jamahiriya">Libyan Arab Jamahiriya</option>
<option value="Liechtenstein">Liechtenstein</option>
<option value="Lithuania">Lithuania</option>
<option value="Luxembourg">Luxembourg</option>
<option value="Macau">Macau</option>
<option value="Macedonia">Macedonia</option>
<option value="Madagascar">Madagascar</option>
<option value="Malawi">Malawi</option>
<option value="Malaysia">Malaysia</option>
<option value="Maldives">Maldives</option>
<option value="Mali">Mali</option>
<option value="Malta">Malta</option>
<option value="Marshall Islands">Marshall Islands</option>
<option value="Martinique">Martinique</option>
<option value="Mauritania">Mauritania</option>
<option value="Mauritius">Mauritius</option>
<option value="Mayotte">Mayotte</option>
<option value="Mexico">Mexico</option>
<option value="Micronesia, Federated States of">Micronesia, Federated States of</option>
<option value="Moldova, Republic of">Moldova, Republic of</option>
<option value="Monaco">Monaco</option>
<option value="Mongolia">Mongolia</option>
<option value="Montenegro">Montenegro</option>
<option value="Montserrat">Montserrat</option>
<option value="Morocco">Morocco</option>
<option value="Mozambique">Mozambique</option>
<option value="Myanmar">Myanmar</option>
<option value="Namibia">Namibia</option>
<option value="Nauru">Nauru</option>
<option value="Nepal">Nepal</option>
<option value="Netherlands">Netherlands</option>
<option value="Netherlands Antilles">Netherlands Antilles</option>
<option value="New Caledonia">New Caledonia</option>
<option value="New Zealand">New Zealand</option>
<option value="Nicaragua">Nicaragua</option>
<option value="Niger">Niger</option>
<option value="Nigeria">Nigeria</option>
<option value="Niue">Niue</option>
<option value="Norfolk Island">Norfolk Island</option>
<option value="Northern Mariana Islands">Northern Mariana Islands</option>
<option value="Norway">Norway</option>
<option value="Oman">Oman</option>
<option value="Pakistan">Pakistan</option>
<option value="Palau">Palau</option>
<option value="Palestine">Palestine</option>
<option value="Panama">Panama</option>
<option value="Papua New Guinea">Papua New Guinea</option>
<option value="Paraguay">Paraguay</option>
<option value="Peru">Peru</option>
<option value="Philippines">Philippines</option>
<option value="Pitcairn">Pitcairn</option>
<option value="Poland">Poland</option>
<option value="Portugal">Portugal</option>
<option value="Puerto Rico">Puerto Rico</option>
<option value="Qatar">Qatar</option>
<option value="Reunion">Reunion</option>
<option value="Romania">Romania</option>
<option value="Russian Federation">Russian Federation</option>
<option value="Rwanda">Rwanda</option>
<option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
<option value="Saint Lucia">Saint Lucia</option>
<option value="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines</option>
<option value="Samoa">Samoa</option>
<option value="San Marino">San Marino</option>
<option value="Sao Tome and Principe">Sao Tome and Principe</option>
<option value="Saudi Arabia">Saudi Arabia</option>
<option value="Senegal">Senegal</option>
<option value="Serbia">Serbia</option>
<option value="Seychelles">Seychelles</option>
<option value="Sierra Leone">Sierra Leone</option>
<option value="Singapore">Singapore</option>
<option value="Slovakia">Slovakia</option>
<option value="Slovenia">Slovenia</option>
<option value="Solomon Islands">Solomon Islands</option>
<option value="Somalia">Somalia</option>
<option value="South Africa">South Africa</option>
<option value="South Georgia South Sandwich Islands">South Georgia South Sandwich Islands</option>
<option value="Spain">Spain</option>
<option value="Sri Lanka">Sri Lanka</option>
<option value="St. Helena">St. Helena</option>
<option value="St. Pierre and Miquelon">St. Pierre and Miquelon</option>
<option value="Sudan">Sudan</option>
<option value="Suriname">Suriname</option>
<option value="Svalbard and Jan Mayen Islands">Svalbard and Jan Mayen Islands</option>
<option value="Swaziland">Swaziland</option>
<option value="Sweden">Sweden</option>
<option value="Switzerland">Switzerland</option>
<option value="Syrian Arab Republic">Syrian Arab Republic</option>
<option value="Taiwan">Taiwan</option>
<option value="Tajikistan">Tajikistan</option>
<option value="Tanzania, United Republic of">Tanzania, United Republic of</option>
<option value="Thailand">Thailand</option>
<option value="Togo">Togo</option>
<option value="Tokelau">Tokelau</option>
<option value="Tonga">Tonga</option>
<option value="Trinidad and Tobago">Trinidad and Tobago</option>
<option value="Tunisia">Tunisia</option>
<option value="Turkey">Turkey</option>
<option value="Turkmenistan">Turkmenistan</option>
<option value="Turks and Caicos Islands">Turks and Caicos Islands</option>
<option value="Tuvalu">Tuvalu</option>
<option value="Uganda">Uganda</option>
<option value="Ukraine">Ukraine</option>
<option value="United Arab Emirates">United Arab Emirates</option>
<option value="United Kingdom">United Kingdom</option>
<option value="United States">United States</option>
<option value="United States minor outlying islands">United States minor outlying islands</option>
<option value="Uruguay">Uruguay</option>
<option value="Uzbekistan">Uzbekistan</option>
<option value="Vanuatu">Vanuatu</option>
<option value="Vatican City State">Vatican City State</option>
<option value="Venezuela">Venezuela</option>
<option value="Vietnam">Vietnam</option>
<option value="Virgin Islands (British)">Virgin Islands (British)</option>
<option value="Virgin Islands (U.S.)">Virgin Islands (U.S.)</option>
<option value="Wallis and Futuna Islands">Wallis and Futuna Islands</option>
<option value="Western Sahara">Western Sahara</option>
<option value="Yemen">Yemen</option>
<option value="Zaire">Zaire</option>
<option value="Zambia">Zambia</option>
<option value="Zimbabwe">Zimbabwe</option>
</select>

                            </div>
                            <span class="text-danger field-validation-valid" data-valmsg-for="Country" data-valmsg-replace="true"></span>
                            <div class="mb-6">
                                <label class="mb-12">Acount Currency<span class="text-danger">*</span></label>
                                <select type="text" class="form-control fs-13px" data-val="true" data-val-required="The Account Currency field is required." id="currency" name="currency">
                                <option value="EUR">EUR</option>
<option value="GBP">GBP</option>
<option value="USD">USD</option>
<option value="ZAR">ZAR</option>
</select>
                            </div>
                            <span class="text-danger field-validation-valid" data-valmsg-for="currency" data-valmsg-replace="true"></span>
                        </div>
                        <div class="row gx-3 mb-3">
                            <div class="mb-6">
                                <label class="mb-2">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control fs-13px" placeholder="Password" data-val="true" data-val-length="The Password must be at least 6 characters long." data-val-length-max="100" data-val-length-min="6" data-val-required="The Password field is required." id="password" maxlength="100" name="password">
                            </div>
                            <span class="text-danger field-validation-valid" data-valmsg-for="password" data-valmsg-replace="true"></span>
                            <div class="mb-6">
                                <label class="mb-2">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control fs-13px" placeholder="Password" data-val="true" data-val-equalto="The password and confirmation password do not match." data-val-equalto-other="*.Password" id="password_confirmation" name="password_confirmation">
                            </div>
                            <span class="text-danger field-validation-valid" data-valmsg-for="password_confirmation" data-valmsg-replace="true"></span>
                        </div>
                         <div class="form-group">
                  <!-- your input fields here -->

    <div class="g-recaptcha" data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"></div>
</form>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" value="" id="agreementCheckbox">
                                <label class="form-check-label" for="agreementCheckbox">
                                    By clicking Sign Up, you agree to our <a href="../Terms.php">Terms</a>.
                                </label>
                            </div>
                            <div class="mb-4">
                                <button type="submit" class="btn btn-primary d-block w-100 btn-lg h-45px fs-13px">Sign Up</button>
                            </div>
                            <div class="mb-4 pb-5">
                                Already a member? Click <a href="login.php">here</a> to login.
                            </div>
                            <hr class="bg-gray-600 opacity-2">
                            <p class="text-center text-gray-600">
                                <?php echo APP_COPYRIGHT ?>
                            </p>
                    <input name="__RequestVerificationToken" type="hidden" value="CfDJ8I8DDa5JhGZOqiGXG7WfnKmc6uxJlrG3AvQwz4PPFHKeElFcKqIJKpecLFa2RmlZhS8Nlv04WjXOCTLEM97dVPusvVk593KlvwBLih-aubEJHlA11zS5_liH-lS65KJ-zd_ZgTh1p9lJBUiXLXCYJuM"></form>
                </div>

            </div>

        </div>


        


        <a href="javascript:;" class="btn btn-icon btn-circle btn-success btn-scroll-to-top" data-toggle="scroll-to-top"><i class="fa fa-angle-up"></i></a>

    </div>
    <script src="../assets/js/vendor.min.js" type="97bf303615d2f4b8e041de4e-text/javascript"></script>
    <script src="../assets/js/app.min.js" type="97bf303615d2f4b8e041de4e-text/javascript"></script>

    <script type="97bf303615d2f4b8e041de4e-text/javascript">
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','../www.google-analytics.com/analytics.html','ga');

        ga('create', 'UA-53034621-1', 'auto');
        ga('send', 'pageview');

    </script>
    <script src="../cdn-cgi/scripts/7d0fa10a/cloudflare-static/rocket-loader.min.js" data-cf-settings="97bf303615d2f4b8e041de4e-|49" defer=""></script>
    <script defer="" src="../beacon.min.js/v652eace1692a40cfa3763df669d7439c1639079717194" integrity="sha512-Gi7xpJR8tSkrpF7aordPZQlW2DLtzUlZcumS8dMQjwDHEnw9I7ZLyiOj/6tZStRBGtGgN6ceN6cMH8z7etPGlw==" data-cf-beacon='{"rayId":"7166678f69fa4c4f","version":"2021.12.0","r":1,"token":"4db8c6ef997743fda032d4f73cfeff63","si":100}' crossorigin="anonymous"></script>
    

</body>

</html>
