-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 29, 2025 at 06:05 PM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `new`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `AddUserColumns` ()   BEGIN
    DECLARE column_exists INT;

    -- Add total_profit if not exists
    SELECT COUNT(*) INTO column_exists
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'total_profit';
    IF column_exists = 0 THEN
        ALTER TABLE users ADD COLUMN total_profit DECIMAL(15,2) DEFAULT 0.00;
    END IF;

    -- Add active_investment if not exists
    SELECT COUNT(*) INTO column_exists
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'active_investment';
    IF column_exists = 0 THEN
        ALTER TABLE users ADD COLUMN active_investment DECIMAL(15,2) DEFAULT 0.00;
    END IF;

    -- Add total_invested if not exists
    SELECT COUNT(*) INTO column_exists
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'total_invested';
    IF column_exists = 0 THEN
        ALTER TABLE users ADD COLUMN total_invested DECIMAL(15,2) DEFAULT 0.00;
    END IF;

    -- Add total_withdrawn if not exists
    SELECT COUNT(*) INTO column_exists
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'total_withdrawn';
    IF column_exists = 0 THEN
        ALTER TABLE users ADD COLUMN total_withdrawn DECIMAL(15,2) DEFAULT 0.00;
    END IF;

    -- Add roi if not exists
    SELECT COUNT(*) INTO column_exists
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'roi';
    IF column_exists = 0 THEN
        ALTER TABLE users ADD COLUMN roi DECIMAL(5,2) DEFAULT 0.00;
    END IF;

    -- Add profit_rate if not exists
    SELECT COUNT(*) INTO column_exists
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'profit_rate';
    IF column_exists = 0 THEN
        ALTER TABLE users ADD COLUMN profit_rate DECIMAL(5,2) DEFAULT 0.00;
    END IF;

    -- Add last_profit if not exists
    SELECT COUNT(*) INTO column_exists
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'last_profit';
    IF column_exists = 0 THEN
        ALTER TABLE users ADD COLUMN last_profit TIMESTAMP NULL;
    END IF;

    -- Add referral_code if not exists
    SELECT COUNT(*) INTO column_exists
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'referral_code';
    IF column_exists = 0 THEN
        ALTER TABLE users ADD COLUMN referral_code VARCHAR(10) UNIQUE;
    END IF;

    -- Add referred_by if not exists
    SELECT COUNT(*) INTO column_exists
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'referred_by';
    IF column_exists = 0 THEN
        ALTER TABLE users ADD COLUMN referred_by INT;
    END IF;

    -- Add referral_earnings if not exists
    SELECT COUNT(*) INTO column_exists
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'referral_earnings';
    IF column_exists = 0 THEN
        ALTER TABLE users ADD COLUMN referral_earnings DECIMAL(15,2) DEFAULT 0.00;
    END IF;

END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT 0.00,
  `account_type` varchar(50) NOT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `admin_logs`
--

INSERT INTO `admin_logs` (`id`, `admin_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 11, 'user_updated', 'Updated user #11', '::1', '2025-03-14 11:30:34'),
(2, 11, 'user_updated', 'Updated user #11', '::1', '2025-03-14 11:33:16'),
(3, 11, 'user_updated', 'Updated user #11', '::1', '2025-03-14 11:33:22'),
(4, 11, 'user_updated', 'Updated user #11', '::1', '2025-03-14 11:33:26'),
(5, 11, 'user_updated', 'Updated user #11', '::1', '2025-03-14 11:33:45'),
(6, 11, 'user_updated', 'Updated user #11', '::1', '2025-03-14 11:36:12'),
(7, 11, 'user_updated', 'Updated user #1', '::1', '2025-03-14 11:58:24'),
(8, 11, 'user_updated', 'Updated user #10', '::1', '2025-03-14 14:30:26'),
(9, 11, 'user_deleted', 'Deleted user #7 (signsstar32@gmail.com)', '::1', '2025-03-15 09:43:15'),
(10, 11, 'deposit_approved', 'Approved deposit #14 for 3000.00', '::1', '2025-03-18 15:20:42'),
(11, 11, 'deposit_approved', 'Approved deposit #14 for 3000.00', '::1', '2025-03-18 15:22:13'),
(12, 11, 'deposit_rejected', 'Rejected deposit #14', '::1', '2025-03-18 16:46:46'),
(13, 11, 'deposit_rejected', 'Rejected deposit #14', '::1', '2025-03-18 17:41:22'),
(14, 11, 'deposit_approved', 'Approved deposit #13', '::1', '2025-03-18 17:47:38'),
(15, 11, 'deposit_rejected', 'Rejected deposit #14', '::1', '2025-03-18 17:47:58'),
(16, 11, 'deposit_rejected', 'Rejected deposit #14', '::1', '2025-03-18 17:52:00'),
(17, 11, 'deposit_approved', 'Approved deposit #13', '::1', '2025-03-18 18:00:25'),
(18, 11, 'deposit_rejected', 'Rejected deposit #14', '::1', '2025-03-18 18:07:47'),
(19, 11, 'deposit_approved', 'Approved deposit #13', '::1', '2025-03-18 18:08:12'),
(20, 11, 'deposit_rejected', 'Rejected deposit #15', '::1', '2025-03-18 18:18:04'),
(21, 11, 'withdrawal_approved', 'Approved withdrawal #12', '::1', '2025-03-19 08:32:17'),
(22, 11, 'withdrawal_rejected', 'Rejected withdrawal #11', '::1', '2025-03-19 08:32:47'),
(23, 11, 'withdrawal_approved', 'Approved withdrawal #12', '::1', '2025-03-19 09:01:55'),
(24, 11, 'trade_completed', 'Completed trade #9', '::1', '2025-03-19 15:05:34'),
(25, 11, 'trade_completed', 'Completed trade #10', '::1', '2025-03-19 15:06:04'),
(26, 11, 'trade_cancelled', 'Cancelled trade #8', '::1', '2025-03-19 15:06:44'),
(27, 11, 'trade_cancelled', 'Cancelled trade #7', '::1', '2025-03-19 15:11:58'),
(28, 11, 'trade_cancelled', 'Cancelled trade #7', '::1', '2025-03-19 15:15:49'),
(29, 11, 'settings_updated', 'Updated system settings', '::1', '2025-03-19 16:10:11'),
(30, 11, 'withdrawal_approved', 'Approved withdrawal #15', '::1', '2025-03-19 17:36:56'),
(31, 11, 'withdrawal_rejected', 'Rejected withdrawal #13', '::1', '2025-03-19 18:08:41'),
(32, 11, 'trade_completed', 'Completed trade #18', '::1', '2025-03-20 19:07:05'),
(33, 11, 'deposit_approved', 'Approved deposit #17', '::1', '2025-03-22 14:29:56');

-- --------------------------------------------------------

--
-- Table structure for table `deposits`
--

CREATE TABLE `deposits` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `transaction_id` varchar(100) DEFAULT NULL,
  `proof_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `deposits`
--

INSERT INTO `deposits` (`id`, `user_id`, `amount`, `payment_method`, `status`, `notes`, `processed_by`, `processed_at`, `created_at`, `transaction_id`, `proof_image`) VALUES
(13, 9, '100.00', 'Bitcoin', 'completed', 'payment recieved', 11, '2025-03-18 18:47:38', '2025-03-10 08:36:48', 'dfsdgdffghgfjghjh', 'proof_13_67cea4a0e24c9.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `investment_plans`
--

CREATE TABLE `investment_plans` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `min_deposit` decimal(15,2) NOT NULL,
  `max_deposit` decimal(15,2) DEFAULT NULL,
  `roi` decimal(5,2) NOT NULL,
  `duration` int(11) NOT NULL,
  `min_withdrawal` decimal(15,2) NOT NULL,
  `tier` varchar(50) NOT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `investment_plans`
--

INSERT INTO `investment_plans` (`id`, `name`, `min_deposit`, `max_deposit`, `roi`, `duration`, `min_withdrawal`, `tier`, `features`, `created_at`) VALUES
(1, 'Starter Plan', '500.00', '4999.00', '15.00', 30, '50.00', 'BASIC', '[\"Basic Trading Tools\", \"Email Support\", \"Market Analysis\", \"Daily Updates\"]', '2025-03-09 15:46:09'),
(2, 'Advanced Plan', '5000.00', '14999.00', '25.00', 30, '100.00', 'STANDARD', '[\"Advanced Trading Tools\", \"Priority Support\", \"Expert Market Analysis\", \"Real-time Updates\", \"Weekly Strategy Calls\"]', '2025-03-09 15:46:09'),
(3, 'Professional Plan', '15000.00', '49999.00', '35.00', 30, '250.00', 'PRO', '[\"Professional Trading Suite\", \"24/7 Dedicated Support\", \"Advanced Market Analysis\", \"Instant Updates\", \"Daily Strategy Calls\", \"Risk Management Tools\"]', '2025-03-09 15:46:09'),
(4, 'Expert Plan', '50000.00', '99999.00', '45.00', 30, '500.00', 'EXPERT', '[\"Complete Trading Suite\", \"VIP Support\", \"Premium Market Analysis\", \"Real-time Alerts\", \"Personal Account Manager\", \"Advanced Risk Management\", \"Portfolio Diversification\"]', '2025-03-09 15:46:09'),
(5, 'Elite Plan', '100000.00', NULL, '55.00', 30, '1000.00', 'ELITE', '[\"Elite Trading Platform\", \"Dedicated Team Support\", \"Institutional Grade Analysis\", \"Priority Execution\", \"Senior Account Manager\", \"Custom Risk Strategies\", \"Priority Withdrawals\", \"Exclusive Investment Opportunities\"]', '2025-03-09 15:46:09');

-- --------------------------------------------------------

--
-- Table structure for table `kyc_verifications`
--

CREATE TABLE `kyc_verifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `referred_by` varchar(255) NOT NULL,
  `referral_earnings` decimal(15,2) DEFAULT 0.00,
  `commission` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','number','boolean','json') DEFAULT 'string',
  `is_public` tinyint(1) DEFAULT 1,
  `category` varchar(50) DEFAULT 'general',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `is_public`, `category`, `description`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'Maxex Capital', 'string', 1, 'general', 'Site name used throughout the application', '2025-03-20 12:12:04', '2025-03-20 12:12:04'),
(2, 'site_tagline', 'Your Trusted Partner in Trading', 'string', 1, 'general', 'Site tagline or slogan', '2025-03-20 12:12:04', '2025-03-20 12:12:04'),
(3, 'trading_fee', '1', 'number', 1, 'fees', 'Trading fee percentage', '2025-03-20 12:12:04', '2025-03-20 12:12:04'),
(4, 'withdrawal_fee', '5', 'number', 1, 'fees', 'Withdrawal fee percentage', '2025-03-20 12:12:04', '2025-03-20 12:12:04'),
(5, 'min_withdrawal', '100', 'number', 1, 'limits', 'Minimum withdrawal amount', '2025-03-20 12:12:04', '2025-03-20 12:12:04'),
(6, 'max_withdrawal', '10000', 'number', 1, 'limits', 'Maximum withdrawal amount', '2025-03-20 12:12:04', '2025-03-20 12:12:04'),
(7, 'maintenance_mode', '0', 'boolean', 1, 'system', 'Enable/disable maintenance mode', '2025-03-20 12:12:04', '2025-03-20 12:12:04'),
(8, 'maintenance_message', 'We are currently performing scheduled maintenance. Please check back soon.', 'string', 1, 'system', 'Message to display during maintenance mode', '2025-03-20 12:12:04', '2025-03-20 12:12:04'),
(9, 'enable_registration', '1', 'boolean', 1, 'users', 'Allow new user registrations', '2025-03-20 12:12:04', '2025-03-20 12:12:04'),
(10, 'enable_withdrawals', '1', 'boolean', 1, 'transaction', 'Allow users to make withdrawals', '2025-03-20 12:12:04', '2025-03-20 12:12:04'),
(11, 'enable_trading', '1', 'boolean', 1, 'transaction', 'Allow users to make trades', '2025-03-20 12:12:04', '2025-03-20 12:12:04'),
(12, 'contact_email', 'support@me.com', 'string', 1, 'contact', 'Primary contact email address', '2025-03-20 12:12:04', '2025-03-20 18:52:17'),
(13, 'support_phone', '+1234567890', 'string', 1, 'contact', 'Support phone number', '2025-03-20 12:12:04', '2025-03-20 12:12:04'),
(14, 'app_name', 'Nadex Market', 'string', 1, 'general', 'Name of your application', '2025-03-20 18:58:38', '2025-03-20 18:58:38'),
(15, 'min_deposit', '100', 'number', 1, 'trading', 'Minimum amount that can be deposited', '2025-03-20 18:58:38', '2025-03-20 19:19:04'),
(16, 'support_email', 'support@example.com', 'string', 1, 'general', 'Email for customer support', '2025-03-20 18:58:38', '2025-03-20 18:58:38'),
(20, 'min_trade_amount', '300', 'number', 1, 'trading', 'Minimum amount users can trade', '2025-03-20 19:10:05', '2025-03-20 19:19:04'),
(21, 'max_trade_amount', '1000', 'number', 1, 'trading', 'Maximum amount users can trade', '2025-03-20 19:10:05', '2025-03-20 19:19:04'),
(22, 'profit_percentage', '10', 'number', 1, 'trading', 'Profit percentage for successful trades', '2025-03-20 19:10:05', '2025-03-20 19:19:04'),
(23, 'max_deposit', '10000', 'number', 1, 'deposit', 'Maximum deposit amount allowed', '2025-03-20 19:10:05', '2025-03-20 19:10:05'),
(24, 'deposit_bonus', '10', 'number', 1, 'deposit', 'Bonus percentage on deposits', '2025-03-20 19:10:05', '2025-03-20 19:10:05'),
(25, 'daily_withdrawal_limit', '10000', 'number', 1, 'withdrawal', 'Maximum total withdrawals per day', '2025-03-20 19:10:05', '2025-03-20 19:10:05'),
(31, 'min_profit', '10', 'number', 1, 'trading', 'Minimum profit amount', '2025-03-20 19:13:57', '2025-03-20 19:19:04'),
(32, 'max_profit', '3000', 'number', 1, 'trading', 'Maximum profit amount', '2025-03-20 19:13:57', '2025-03-20 19:19:04'),
(33, 'deposit_fee', '0', 'number', 1, 'deposit', 'Fee percentage on deposits', '2025-03-20 19:13:57', '2025-03-20 19:13:57'),
(34, 'min_deposit_bonus', '500', 'number', 1, 'deposit', 'Minimum amount for deposit bonus', '2025-03-20 19:13:57', '2025-03-20 19:13:57'),
(35, 'deposit_bonus_percentage', '10', 'number', 1, 'deposit', 'Bonus percentage on qualifying deposits', '2025-03-20 19:13:57', '2025-03-20 19:13:57'),
(36, 'min_withdrawal_balance', '150', 'number', 1, 'withdrawal', 'Minimum balance required to withdraw', '2025-03-20 19:13:57', '2025-03-20 19:13:57'),
(37, 'withdrawal_processing_time', '24', 'number', 1, 'withdrawal', 'Processing time in hours', '2025-03-20 19:13:57', '2025-03-20 19:13:57'),
(48, 'min_trade', '120', 'number', 1, 'trading', 'Minimum trade amount ($)', '2025-03-20 19:16:20', '2025-03-20 19:19:04'),
(49, 'max_trade', '2000', 'number', 1, 'trading', 'Maximum trade amount ($)', '2025-03-20 19:16:20', '2025-03-20 19:19:04'),
(50, 'trade_profit', '10', 'number', 1, 'trading', 'Trade profit percentage (%)', '2025-03-20 19:16:20', '2025-03-20 19:19:04'),
(51, 'trade_commission', '3', 'number', 1, 'trading', 'Commission per trade (%)', '2025-03-20 19:16:20', '2025-03-20 19:19:04');

-- --------------------------------------------------------

--
-- Table structure for table `signals`
--

CREATE TABLE `signals` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `percentage` decimal(5,2) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `success_rate` int(11) NOT NULL,
  `tier` enum('BASIC','STANDARD','PRO','EXPERT','ELITE') NOT NULL,
  `features` text NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `signals`
--

INSERT INTO `signals` (`id`, `name`, `price`, `percentage`, `status`, `created_at`, `success_rate`, `tier`, `features`, `description`) VALUES
(1, 'Basic Signals', '650.00', '0.00', 'active', '2025-03-08 10:37:54', 25, 'BASIC', '[\"Basic Signals\", \"Email Support\"]', NULL),
(2, 'Omentum Signals', '900.00', '0.00', 'active', '2025-03-08 10:37:54', 25, 'STANDARD', '[\"Standard Signals\", \"Chat Support\"]', NULL),
(3, 'Breakout Signals', '1300.00', '0.00', 'active', '2025-03-08 10:37:54', 32, 'PRO', '[\"Advanced Signals\", \"Priority Support\"]', NULL),
(4, 'Omentum+² Signals', '1080.00', '0.00', 'active', '2025-03-08 10:37:54', 35, 'EXPERT', '[\"Premium Signals\", \"24/7 Support\"]', NULL),
(5, 'Breakout+² Signals[PrO]', '1650.00', '0.00', 'active', '2025-03-08 10:37:54', 55, 'ELITE', '[\"Pro Signals\", \"VIP Support\", \"Priority Access\"]', NULL),
(6, 'Buying Oversold', '2000.00', '0.00', 'active', '2025-03-08 10:37:54', 68, 'EXPERT', '[\"Expert Signals\", \"Premium Support\", \"Market Analysis\"]', NULL),
(7, 'Trend Signal', '2400.00', '0.00', 'active', '2025-03-08 10:37:54', 75, 'ELITE', '[\"Elite Signals\", \"Dedicated Support\", \"Advanced Analysis\"]', NULL),
(8, 'AntMiner-S7-4.8THs-1250w', '3000.00', '0.00', 'active', '2025-03-08 10:37:54', 85, 'ELITE', '[\"Mining Signals\", \"24/7 Support\", \"Hardware Support\"]', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('open','closed') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `trading_history`
--

CREATE TABLE `trading_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `asset` varchar(50) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `type` enum('buy','sell') NOT NULL,
  `leverage` int(11) NOT NULL DEFAULT 1,
  `expiration` varchar(50) NOT NULL DEFAULT '1 Minutes',
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `fee_percentage` decimal(5,2) DEFAULT 0.00,
  `fee_amount` decimal(10,2) DEFAULT 0.00,
  `total_cost` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('deposit','withdrawal') NOT NULL,
  `status` enum('pending','completed','failed') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `phone` varchar(20) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `balance` decimal(15,2) DEFAULT 0.00,
  `country` varchar(100) DEFAULT NULL,
  `total_profit` decimal(15,2) DEFAULT 0.00,
  `active_investment` decimal(15,2) DEFAULT 0.00,
  `total_invested` decimal(15,2) DEFAULT 0.00,
  `total_withdrawn` decimal(15,2) DEFAULT 0.00,
  `roi` decimal(5,2) DEFAULT 0.00,
  `profit_rate` decimal(5,2) DEFAULT 0.00,
  `last_profit` timestamp NULL DEFAULT NULL,
  `referral_code` varchar(10) DEFAULT NULL,
  `referred_by` int(11) DEFAULT NULL,
  `referral_earnings` decimal(15,2) DEFAULT 0.00,
  `verification_status` enum('unverified','pending','verified','rejected') DEFAULT 'unverified',
  `reject_reason` text DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `id_front` varchar(255) DEFAULT NULL,
  `id_back` varchar(255) DEFAULT NULL,
  `address_proof` varchar(255) DEFAULT NULL,
  `selfie` varchar(255) DEFAULT NULL,
  `verification_submitted_at` timestamp NULL DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `phone`, `username`, `status`, `balance`, `country`, `total_profit`, `active_investment`, `total_invested`, `total_withdrawn`, `roi`, `profit_rate`, `last_profit`, `referral_code`, `referred_by`, `referral_earnings`, `verification_status`, `reject_reason`, `verified_at`, `id_front`, `id_back`, `address_proof`, `selfie`, `verification_submitted_at`, `deleted`, `deleted_at`) VALUES
(1, 'blue baby', 'lia@baxchain.com', '$2y$10$WcLdi8lGchCMnpqnDdPql.ESpRk7C9ZXmAqSLxRJ./CPr9LuGxBNe', 'user', '2025-03-04 17:34:59', '24656587', 'blue baby', 'active', '650.00', 'Bahrain', '20.00', '10.00', '10.00', '10.00', '60.00', '0.40', NULL, NULL, NULL, '0.00', 'verified', NULL, NULL, 'id_front_1742237785.jpg', 'id_back_1742237785.jpg', 'address_proof_1742237785.jpg', 'selfie_1742237785.PNG', '2025-03-17 18:56:25', 0, NULL),
(9, 'lia blue', 'lia@gmail.com', '$2y$10$Qwdew0DMTui91x20LTRAROgF3aowkY5cuRyDzZBdqKgho3KOIPm4G', 'user', '2025-03-10 07:58:03', '5645656765', 'lia', 'active', '1050.00', NULL, '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', NULL, '44a53c17', NULL, '0.00', 'verified', NULL, NULL, 'id_front_1741593512.PNG', 'id_back_1741593512.jpg', 'address_proof_1741593512.PNG', 'selfie_1741593512.PNG', '2025-03-10 07:58:32', 0, NULL),
(11, 'Administrator', 'admin@example.com', '$2y$10$WcLdi8lGchCMnpqnDdPql.ESpRk7C9ZXmAqSLxRJ./CPr9LuGxBNe', 'super_admin', '2025-03-10 12:37:38', NULL, 'admin', 'active', '100000.00', NULL, '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', NULL, NULL, NULL, '0.00', 'unverified', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL),
(16, 'work', 'test@gmail.com', '$2y$10$4F6mbAKVlZgg6EODwoCtD.UW0xu9dpVsyFAA9ck0sjZE0acs.MvNG', NULL, '2025-05-29 16:04:38', '3534667879', 'test', 'active', '0.00', '', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', NULL, '63a0adce', NULL, '0.00', 'unverified', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_plans`
--

CREATE TABLE `user_plans` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` enum('active','completed','cancelled') DEFAULT 'active',
  `profit_earned` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user_plans`
--

INSERT INTO `user_plans` (`id`, `user_id`, `plan_id`, `amount`, `status`, `profit_earned`, `created_at`, `expires_at`) VALUES
(2, 9, 1, '500.00', 'active', '0.00', '2025-03-10 08:35:28', '2025-04-09 08:35:28'),
(3, 1, 1, '500.00', 'active', '0.00', '2025-03-17 14:30:47', '2025-04-16 14:30:47');

-- --------------------------------------------------------

--
-- Table structure for table `user_signals`
--

CREATE TABLE `user_signals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `signal_id` int(11) DEFAULT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` enum('active','expired') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user_signals`
--

INSERT INTO `user_signals` (`id`, `user_id`, `signal_id`, `payment_method`, `status`, `created_at`) VALUES
(19, 9, 1, '', 'active', '2025-03-10 08:35:43'),
(20, 1, 1, '', 'active', '2025-03-17 14:30:57');

-- --------------------------------------------------------

--
-- Table structure for table `wallet_addresses`
--

CREATE TABLE `wallet_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `btc_address` varchar(255) DEFAULT NULL,
  `eth_address` varchar(255) DEFAULT NULL,
  `ltc_address` varchar(255) DEFAULT NULL,
  `usdt_address` varchar(255) DEFAULT NULL,
  `bnb_address` varchar(255) DEFAULT NULL,
  `xrp_address` varchar(255) DEFAULT NULL,
  `doge_address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `wallet_addresses`
--

INSERT INTO `wallet_addresses` (`id`, `user_id`, `btc_address`, `eth_address`, `ltc_address`, `usdt_address`, `bnb_address`, `xrp_address`, `doge_address`, `created_at`) VALUES
(2, 1, 'aaaaaaaaaaaaaaaaaaaa', 'bbbbbbbbbbbbbbbb', 'cccccccccccccccccccccc', 'dddddddddddddddd', 'eeeeeeeeeeeeeeee', 'ffffffffffffffffffffffffffff', 'ggggggggggggggggg', '2025-03-07 07:37:11');

-- --------------------------------------------------------

--
-- Table structure for table `withdrawals`
--

CREATE TABLE `withdrawals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `fee` decimal(10,2) DEFAULT 0.00,
  `payment_method` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `bank_name` varchar(100) DEFAULT NULL,
  `account_name` varchar(100) DEFAULT NULL,
  `account_number` varchar(50) DEFAULT NULL,
  `swift_code` varchar(50) DEFAULT NULL,
  `paypal_email` varchar(255) DEFAULT NULL,
  `skrill_email` varchar(255) DEFAULT NULL,
  `crypto_type` varchar(20) DEFAULT NULL,
  `wallet_address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fee_percentage` decimal(5,2) DEFAULT 0.00,
  `fee_amount` decimal(10,2) DEFAULT 0.00,
  `net_amount` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `deposits`
--
ALTER TABLE `deposits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user` (`user_id`);

--
-- Indexes for table `investment_plans`
--
ALTER TABLE `investment_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kyc_verifications`
--
ALTER TABLE `kyc_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_expires` (`expires_at`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_referred_by` (`referred_by`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `signals`
--
ALTER TABLE `signals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `trading_history`
--
ALTER TABLE `trading_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `referral_code` (`referral_code`),
  ADD KEY `fk_referred_by` (`referred_by`),
  ADD KEY `idx_role` (`role`);

--
-- Indexes for table `user_plans`
--
ALTER TABLE `user_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indexes for table `user_signals`
--
ALTER TABLE `user_signals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `signal_id` (`signal_id`);

--
-- Indexes for table `wallet_addresses`
--
ALTER TABLE `wallet_addresses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `btc_address` (`btc_address`),
  ADD UNIQUE KEY `eth_address` (`eth_address`),
  ADD UNIQUE KEY `ltc_address` (`ltc_address`),
  ADD UNIQUE KEY `usdt_address` (`usdt_address`),
  ADD UNIQUE KEY `bnb_address` (`bnb_address`),
  ADD UNIQUE KEY `xrp_address` (`xrp_address`),
  ADD UNIQUE KEY `doge_address` (`doge_address`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `deposits`
--
ALTER TABLE `deposits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `investment_plans`
--
ALTER TABLE `investment_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `kyc_verifications`
--
ALTER TABLE `kyc_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `signals`
--
ALTER TABLE `signals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trading_history`
--
ALTER TABLE `trading_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `user_plans`
--
ALTER TABLE `user_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_signals`
--
ALTER TABLE `user_signals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `wallet_addresses`
--
ALTER TABLE `wallet_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `withdrawals`
--
ALTER TABLE `withdrawals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `deposits`
--
ALTER TABLE `deposits`
  ADD CONSTRAINT `deposits_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `kyc_verifications`
--
ALTER TABLE `kyc_verifications`
  ADD CONSTRAINT `kyc_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `referrals`
--
ALTER TABLE `referrals`
  ADD CONSTRAINT `referrals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trading_history`
--
ALTER TABLE `trading_history`
  ADD CONSTRAINT `trading_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_referred_by` FOREIGN KEY (`referred_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_plans`
--
ALTER TABLE `user_plans`
  ADD CONSTRAINT `user_plans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_plans_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `investment_plans` (`id`);

--
-- Constraints for table `user_signals`
--
ALTER TABLE `user_signals`
  ADD CONSTRAINT `user_signals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_signals_ibfk_2` FOREIGN KEY (`signal_id`) REFERENCES `signals` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallet_addresses`
--
ALTER TABLE `wallet_addresses`
  ADD CONSTRAINT `wallet_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD CONSTRAINT `withdrawals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
