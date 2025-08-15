<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/Settings.php';

$db = new Database();
// Initialize the settings utility
Settings::init($db); 

// Check for maintenance mode (put this after session start)
if (Settings::isMaintenanceMode() && !isset($_SESSION['is_admin'])) {
    // Display maintenance page
    include_once('maintenance.php');
    exit;
} 