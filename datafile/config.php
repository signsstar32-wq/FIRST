<?php
// Load Database class if not already loaded
if (!class_exists('Database')) {
    require_once __DIR__ . '/../includes/Database.php';
}

// Create a database instance
$db = isset($db) ? $db : new Database();

// Define APP_URL first
define('APP_URL', $db->getSetting('site_url', 'http://localhost/lldash/account/users/index/index.php'));

define('APP_NAME', $db->getSetting('site_name', 'Nadex Market'));
define('APP_TITLE', $db->getSetting('site_title', APP_NAME . ' - Powerful Trading Platform: Forex, Crypto, and Stocks - Empowering Your Financial Success'));
define('APP_LOGO', $db->getSetting('site_logo', APP_URL.'xxedash/img/logo.png'));
define('APP_LOGO2', $db->getSetting('site_logo_dark', APP_URL.'xxedash/img/logodark.png'));
define('APP_FAVICON', $db->getSetting('site_favicon', APP_URL.'main/assets/img/brand/favicon.ico'));
define('APP_METAIMAGE', $db->getSetting('site_metaimage', APP_URL.'xxedash/img/og.png'));
define('APP_METADESCRIPTION', $db->getSetting('site_metadescription', APP_NAME . ' | Discover the world of financial trading with our comprehensive platform for forex, crypto, and stocks. Stay ahead of the markets, access real-time data, make informed investment decisions, and maximize your profits. Start your trading journey today with our trusted platform'));
define('APP_METAKEY', $db->getSetting('site_metakey', 'crypto, stock, trade, broker, liscense, '));
define('APP_COPYRIGHT', $db->getSetting('site_copyright', APP_NAME . ' LTD &copy; 2023. All Rights Reserved'));
define('APP_DOMAINNAME', $_SERVER['HTTP_HOST']);
define('APP_ABBR', $db->getSetting('site_abbr', 'NM'));

// pages 
define('APP_REG', APP_URL.'/register.php');
define('APP_LOGIN', APP_URL.'/login.php');
define('APP_FUNDING', APP_URL.'xxebrifo_admin/index.php'); // fund
define('APP_DASHBOARD', APP_URL.'xxedash/index.php'); // dash/main/nova, access/dashboard.php
define('APP_DASHBOARD2', APP_URL.'xxedash/index.php');

// contact
define('APP_LOCATION', $db->getSetting('site_location', 'United Kingdom'));
define('APP_PHONE', $db->getSetting('site_phone', '+44 7572 929453'));
define('APP_PHONE2', $db->getSetting('site_phone2', '+44 7572 929453'));
define('APP_ADDRESS', $db->getSetting('site_address', '9969 London Road MOTHERWELL ML58 8OF.'));
define('APP_ADDRESS2', $db->getSetting('site_address2', 'ueuiiugieuvguei'));
define('APP_MAIL', $db->getSetting('site_mail', 'support@'.APP_DOMAINNAME));
define('APP_MAIL2', $db->getSetting('site_mail2', 'ueuiiugieuvguei'));
define('APP_PASSWORD', $db->getSetting('site_password', 'ueuiiugieuvguei'));
define('APP_LIVECHAT', $db->getSetting('site_livechat', '<script src=\"//code.tidio.co/h8z0oihjqewrve0bm1pnma9o8fs23k0g.js\" async></script>'));
// MAIL PASSWORD - Nadex456Market#
// database
define('MYSQL_HOST', $db->getSetting('db_host', 'localhost'));
define('MYSQL_USER', $db->getSetting('db_user', 'root'));
define('MYSQL_PASSWORD', $db->getSetting('db_password', ''));
define('MYSQL_DATABASE', $db->getSetting('db_name', 'amiens'));
define('CRYPTED_KEY', $db->getSetting('crypted_key', '9UgqxEkyiIObaGmilZsNE2SRueDebr0HUQlHiHOqOqSiCJCZ6WW93PftnD4M'));
// $db = mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);

