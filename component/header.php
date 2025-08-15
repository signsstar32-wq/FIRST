<?php include("datafile/config.php") ?>
<!DOCTYPE html>
<html>

<meta http-equiv="content-type" content="text/html;charset=utf-8">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="MobileOptimized" content="320">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <!-- Primary Meta Tags -->
    <title><?php echo APP_TITLE ?></title>
    <meta name="title" content="<?php echo APP_TITLE ?>">
    <meta name="description" content="<?php echo APP_METADESCRIPTION ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo APP_TITLE ?>">
    <meta property="og:description" content="<?php echo APP_METADESCRIPTION ?>">
    <meta property="og:image" content="<?php echo APP_LOGO ?>">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:title" content="<?php echo APP_TITLE ?>">
    <meta property="twitter:description" content="<?php echo APP_METADESCRIPTION ?>">
    <meta property="twitter:image" content="<?php echo APP_LOGO ?>">
    
    <!-- Favicon and apple icon -->
    <link rel="shortcut icon" href="<?php echo APP_FAVICON ?>" type="image/x-icon">
    <!-- Stylesheet -->

    <link href="css/libs.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">
    <style>
        .goog-te-banner-frame.skiptranslate {
            display: none !important;
        }
        body {
            top: 0px !important;
        }
    </style>
</head>
<body class="dark-load">
   

    <header id="top-nav" class="top-nav page-header">
        
        <div class="container">
             <center><div id="google_translate_element" style="margin-bottom:10px;"></div></center>
<script type="text/javascript">
function googleTranslateElementInit() {
  new google.translate.TranslateElement({pageLanguage: 'en'}, 'google_translate_element');}
</script>

<script type="text/javascript" src="translate_a/element.js?cb=googleTranslateElementInit"></script>
            <a href="#" class="logo smooth-scroll" style="margin-top:-20px;"><img src="<?php echo APP_LOGO ?>" alt="logo" class="logo-white" style="width:70px; height:70px;"><img src="<?php echo APP_LOGO2 ?>" alt="logo" class="logo-dark" style="width:70px; height:70px;"></a>
            <nav class="top-menu">
                <ul class="sf-menu">
                    <!--Menu default-->
                    <li><a href="index.php">Home</a></li>
                    <li><a href="Plan.php">Our Plan</a></li>

                    <li><a href="About.php" class="smooth-scroll">About Us</a></li>
                    <li> <a href="Contact.php" class="smooth-scroll">Contact</a></li>
                    <li> <a href="Help.php" class="smooth-scroll">FAQs-Help</a></li>
                    <li><a href="Terms.php">Terms and Condition</a></li>
                    <li><a href="auth/register.php">Register</a></li>
                </ul>
                <!-- Start toggle menu--><a href="#" class="toggle-mnu"><span></span></a>

            </nav>
            <!-- Start mobile menu-->
            <div id="mobile-menu">
                <div class="inner-wrap">
                    <nav>
                        <ul class="nav_menu">
                            <li>
                                <a href="index.php">Home</a>
                            </li>
                            <li>
                                <a href="Plan.php">Our Plan</a>
                            </li>

                            <li><a href="About.php">About Us</a></li>
                            <li><a href="Contact.php">Contact</a></li>
                            <li><a href="Help.php">FAQs-Help ?</a></li> 
                            <li><a href="Terms.php">Terms and Condition</a></li>
                            <li><a href="auth/register.php">Register</a></li>

                        </ul>
                    </nav>
                </div>
            </div>
            <!-- End mobile menu-->
        </div>
    </header>