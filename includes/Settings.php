<?php
/**
 * Settings utility class to access global site settings
 */
class Settings {
    private static $settings = null;
    private static $db = null;
    
    /**
     * Initialize the settings utility
     * @param Database $db Database connection
     */
    public static function init($db) {
        self::$db = $db;
    }
    
    /**
     * Get a specific setting value
     * @param string $key Setting key
     * @param mixed $default Default value if setting not found
     * @return mixed Setting value
     */
    public static function get($key, $default = null) {
        return $default; // For now, just return the default value
    }
    
    /**
     * Check if a feature is enabled
     * @param string $feature Feature name
     * @return bool Whether feature is enabled
     */
    public static function isEnabled($feature) {
        return true; // For now, enable all features
    }
    
    /**
     * Check if site is in maintenance mode
     * @return bool
     */
    public static function isMaintenanceMode() {
        return false;
    }
    
    /**
     * Get minimum withdrawal amount
     * @return float
     */
    public static function getMinWithdrawal() {
        return 100;
    }
    
    /**
     * Get maximum withdrawal amount
     * @return float
     */
    public static function getMaxWithdrawal() {
        return 10000;
    }
    
    /**
     * Get withdrawal fee percentage
     * @return float
     */
    public static function getWithdrawalFee() {
        return 5;
    }
    
    /**
     * Get trading fee percentage
     * @return float
     */
    public static function getTradingFee() {
        return 1;
    }
} 