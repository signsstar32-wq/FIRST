<?php

class Admin {
    private $db;
    private $auth;
    private $currentUser;

    public function __construct() {
        $this->db = new Database();
        $this->auth = new Auth();
        $this->currentUser = $this->auth->getCurrentUser();
    }

    /**
     * Check if current user is admin
     */
    public function isAdmin() {
        return $this->auth->isLoggedIn() && $this->auth->isAdmin();
    }

    /**
     * Get current admin user
     */
    public function getCurrentUser() {
        return $this->currentUser;
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats() {
        return [
            'total_users' => $this->db->getTotalUsers(),
            'total_deposits' => $this->db->getTotalDeposits(),
            'total_withdrawals' => $this->db->getTotalWithdrawals(),
            'pending_verifications' => $this->db->getPendingVerifications(),
            'pending_deposits' => $this->db->getPendingDeposits(),
            'pending_withdrawals' => $this->db->getPendingWithdrawals(),
            'recent_users' => $this->db->getRecentUsers(5),
            'trade_stats' => $this->db->getTradeStats(),
            'withdrawal_stats' => $this->db->getWithdrawalStats()
        ];
    }

    /**
     * Get users with filtering and pagination
     */
    public function getUsers($filters = []) {
        $status = $filters['status'] ?? 'all';
        $verified = $filters['verified'] ?? 'all';
        $search = $filters['search'] ?? '';
        $page = max(1, intval($filters['page'] ?? 1));
        $per_page = 20;

        $users = $this->db->getUsers($status, $verified, $search, $page, $per_page);
        $total_users = $this->db->getTotalUsersCount($status, $verified, $search);
        $total_pages = ceil($total_users / $per_page);

        return [
            'users' => $users,
            'total_users' => $total_users,
            'total_pages' => $total_pages,
            'current_page' => $page
        ];
    }

    /**
     * Update user status
     */
    public function updateUserStatus($userId, $status) {
        if ($this->db->updateUser($userId, ['status' => $status])) {
            $this->logActivity('user_status_updated', "Updated user #$userId status to $status");
            return ['success' => true, 'message' => 'User status updated successfully'];
        }
        return ['success' => false, 'message' => 'Failed to update user status'];
    }

    /**
     * Update user balance
     */
    public function updateUserBalance($userId, $amount, $operation) {
        if ($this->db->updateUserBalance($userId, $amount, $operation)) {
            $this->logActivity('user_balance_updated', "Updated user #$userId balance: $operation $amount");
            return ['success' => true, 'message' => 'User balance updated successfully'];
        }
        return ['success' => false, 'message' => 'Failed to update user balance'];
    }

    /**
     * Delete user
     */
    public function deleteUser($userId) {
        $user = $this->db->getUserById($userId);
        if ($user && $user['role'] !== 'super_admin') {
            if ($this->db->deleteUser($userId)) {
                $this->logActivity('user_deleted', "Deleted user #$userId (" . $user['email'] . ")");
                return ['success' => true, 'message' => 'User deleted successfully'];
            }
        }
        return ['success' => false, 'message' => 'Failed to delete user'];
    }

    /**
     * Get deposits with filtering
     */
    public function getDeposits($filters = []) {
        $status = $filters['status'] ?? 'all';
        $user_id = $filters['user_id'] ?? null;
        $search = $filters['search'] ?? '';
        $page = max(1, intval($filters['page'] ?? 1));
        $per_page = 20;

        $deposits = $this->db->getAdminDeposits($status, $user_id, $search, $page, $per_page);
        $total_deposits = $this->db->getAdminTotalDepositsCount($status, $user_id, $search);
        $total_pages = ceil($total_deposits / $per_page);

        return [
            'deposits' => $deposits,
            'total_deposits' => $total_deposits,
            'total_pages' => $total_pages,
            'current_page' => $page
        ];
    }

    /**
     * Approve deposit
     */
    public function approveDeposit($depositId) {
        $deposit = $this->db->getAdminDepositById($depositId);
        if ($deposit) {
            if ($this->db->updateAdminDeposit($depositId, ['status' => 'approved'])) {
                // Update user balance
                $this->db->updateUserBalance($deposit['user_id'], $deposit['amount'], 'add');
                $this->logActivity('deposit_approved', "Approved deposit #$depositId for $" . number_format($deposit['amount'], 2));
                return ['success' => true, 'message' => 'Deposit approved successfully'];
            }
        }
        return ['success' => false, 'message' => 'Failed to approve deposit'];
    }

    /**
     * Reject deposit
     */
    public function rejectDeposit($depositId) {
        $deposit = $this->db->getAdminDepositById($depositId);
        if ($deposit) {
            if ($this->db->updateAdminDeposit($depositId, ['status' => 'rejected'])) {
                $this->logActivity('deposit_rejected', "Rejected deposit #$depositId");
                return ['success' => true, 'message' => 'Deposit rejected successfully'];
            }
        }
        return ['success' => false, 'message' => 'Failed to reject deposit'];
    }

    /**
     * Get withdrawals with filtering
     */
    public function getWithdrawals($filters = []) {
        $status = $filters['status'] ?? 'all';
        $user_id = $filters['user_id'] ?? null;
        $search = $filters['search'] ?? '';
        $page = max(1, intval($filters['page'] ?? 1));
        $per_page = 20;

        $withdrawals = $this->db->getAdminWithdrawals($status, $user_id, $search, $page, $per_page);
        $total_withdrawals = $this->db->getAdminTotalWithdrawalsCount($status, $user_id, $search);
        $total_pages = ceil($total_withdrawals / $per_page);

        return [
            'withdrawals' => $withdrawals,
            'total_withdrawals' => $total_withdrawals,
            'total_pages' => $total_pages,
            'current_page' => $page
        ];
    }

    /**
     * Approve withdrawal
     */
    public function approveWithdrawal($withdrawalId) {
        $withdrawal = $this->db->getAdminWithdrawalById($withdrawalId);
        if ($withdrawal) {
            if ($this->db->updateAdminWithdrawal($withdrawalId, ['status' => 'completed'])) {
                $this->logActivity('withdrawal_approved', "Approved withdrawal #$withdrawalId for $" . number_format($withdrawal['amount'], 2));
                return ['success' => true, 'message' => 'Withdrawal approved successfully'];
            }
        }
        return ['success' => false, 'message' => 'Failed to approve withdrawal'];
    }

    /**
     * Reject withdrawal
     */
    public function rejectWithdrawal($withdrawalId) {
        $withdrawal = $this->db->getAdminWithdrawalById($withdrawalId);
        if ($withdrawal) {
            // Refund the user's balance if withdrawal was pending
            if ($withdrawal['status'] === 'pending') {
                $this->db->updateUserBalance($withdrawal['user_id'], $withdrawal['amount'], 'add');
            }
            
            if ($this->db->updateAdminWithdrawal($withdrawalId, ['status' => 'failed'])) {
                $this->logActivity('withdrawal_rejected', "Rejected withdrawal #$withdrawalId");
                return ['success' => true, 'message' => 'Withdrawal rejected successfully'];
            }
        }
        return ['success' => false, 'message' => 'Failed to reject withdrawal'];
    }

    /**
     * Get all settings
     */
    public function getSettings() {
        return $this->db->getAllSettings();
    }

    /**
     * Update settings
     */
    public function updateSettings($settings) {
        $success_count = 0;
        foreach ($settings as $key => $value) {
            if ($this->db->updateSetting($key, $value)) {
                $success_count++;
            }
        }
        
        if ($success_count > 0) {
            $this->logActivity('settings_updated', "Updated $success_count settings");
            return ['success' => true, 'message' => 'Settings updated successfully'];
        }
        return ['success' => false, 'message' => 'Failed to update settings'];
    }

    /**
     * Update wallet addresses
     */
    public function updateWalletAddresses($addresses) {
        if ($this->db->updateAdminWalletAddress($addresses)) {
            $this->logActivity('wallet_addresses_updated', 'Updated admin wallet addresses');
            return ['success' => true, 'message' => 'Wallet addresses updated successfully'];
        }
        return ['success' => false, 'message' => 'Failed to update wallet addresses'];
    }

    /**
     * Get wallet addresses
     */
    public function getWalletAddresses() {
        return $this->db->getAdminWalletAddresses();
    }

    /**
     * Get admin logs
     */
    public function getLogs($filters = []) {
        $admin_id = $filters['admin_id'] ?? null;
        $action = $filters['action'] ?? '';
        $search = $filters['search'] ?? '';
        $page = max(1, intval($filters['page'] ?? 1));
        $per_page = 50;

        $logs = $this->db->getAdminLogs($admin_id, $action, $search, $page, $per_page);
        $total_logs = $this->db->getAdminLogsCount($admin_id, $action, $search);
        $total_pages = ceil($total_logs / $per_page);

        return [
            'logs' => $logs,
            'total_logs' => $total_logs,
            'total_pages' => $total_pages,
            'current_page' => $page
        ];
    }

    /**
     * Get unique log actions
     */
    public function getLogActions() {
        return $this->db->getAdminLogActions();
    }

    /**
     * Log admin activity
     */
    private function logActivity($action, $details = '') {
        if ($this->currentUser) {
            $this->db->logAdminActivity($this->currentUser['id'], $action, $details);
        }
    }

    /**
     * Get user by ID
     */
    public function getUserById($userId) {
        $stmt = $this->db->prepare("SELECT id, name, email, password, role, created_at, last_login, phone, username, status, balance, country, total_profit, active_investment, total_invested, total_withdrawn, roi, profit_rate, last_profit, referral_code, referred_by, referral_earnings, verification_status, reject_reason, verified_at, id_front, id_back, address_proof, selfie, verification_submitted_at, deleted, deleted_at, practice_balance, currency FROM users WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }

    /**
     * Get deposit by ID
     */
    public function getDepositById($depositId) {
        return $this->db->getAdminDepositById($depositId);
    }

    /**
     * Get withdrawal by ID
     */
    public function getWithdrawalById($withdrawalId) {
        return $this->db->getAdminWithdrawalById($withdrawalId);
    }

    // Stock management methods
    public function getStocks($search = '', $exchange = '', $page = 1, $per_page = 20) {
        return $this->db->getStocks($search, $exchange, $page, $per_page);
    }
    
    public function getStocksOOP($filters = []) {
        $search = $filters['search'] ?? '';
        $exchange = $filters['exchange'] ?? '';
        $page = max(1, intval($filters['page'] ?? 1));
        $per_page = 20;
        
        $stocks = $this->db->getStocks($search, $exchange, $page, $per_page);
        $total_stocks = $this->db->getTotalStocksCount($search, $exchange);
        $total_pages = ceil($total_stocks / $per_page);
        
        return [
            'stocks' => $stocks,
            'total_stocks' => $total_stocks,
            'total_pages' => $total_pages,
            'current_page' => $page
        ];
    }
    
    public function getTotalStocksCount($search = '', $exchange = '') {
        return $this->db->getTotalStocksCount($search, $exchange);
    }
    
    public function addStock($data) {
        return $this->db->addStock($data);
    }
    
    public function updateStock($id, $data) {
        return $this->db->updateStock($id, $data);
    }
    
    public function deleteStock($id) {
        return $this->db->deleteStock($id);
    }
    
    public function getAllStocks() {
        return $this->db->getAllStocks();
    }
    
    public function getStockById($id) {
        return $this->db->getStockById($id);
    }
    
    // Stock investment methods
    public function getStockInvestments($search = '', $status = '', $stock_id = 0, $page = 1, $per_page = 20) {
        return $this->db->getStockInvestments($search, $status, $stock_id, $page, $per_page);
    }
    
    public function getStockInvestmentsOOP($filters = []) {
        $search = $filters['search'] ?? '';
        $status = $filters['status'] ?? '';
        $stock_id = isset($filters['stock_id']) ? (int)$filters['stock_id'] : 0;
        $page = max(1, intval($filters['page'] ?? 1));
        $per_page = 20;
        
        $investments = $this->db->getStockInvestments($search, $status, $stock_id, $page, $per_page);
        $total_investments = $this->db->getTotalStockInvestmentsCount($search, $status, $stock_id);
        $total_pages = ceil($total_investments / $per_page);
        
        return [
            'investments' => $investments,
            'total_investments' => $total_investments,
            'total_pages' => $total_pages,
            'current_page' => $page
        ];
    }
    
    public function getTotalStockInvestmentsCount($search = '', $status = '', $stock_id = 0) {
        return $this->db->getTotalStockInvestmentsCount($search, $status, $stock_id);
    }
    
    public function updateStockInvestmentStatus($id, $status) {
        return $this->db->updateStockInvestmentStatus($id, $status);
    }
    
    // Trading history methods
    public function getTradingHistory($search = '', $status = '', $user_id = 0, $page = 1, $per_page = 20) {
        return $this->db->getTradingHistory($search, $status, $user_id, $page, $per_page);
    }
    
    public function getTradingHistoryOOP($filters = []) {
        $search = $filters['search'] ?? '';
        $status = $filters['status'] ?? '';
        $user_id = isset($filters['user_id']) ? (int)$filters['user_id'] : 0;
        $page = max(1, intval($filters['page'] ?? 1));
        $per_page = 20;
        
        $trades = $this->db->getTradingHistory($search, $status, $user_id, $page, $per_page);
        $total_trades = $this->db->getTotalTradingHistoryCount($search, $status, $user_id);
        $total_pages = ceil($total_trades / $per_page);
        
        return [
            'trades' => $trades,
            'total_trades' => $total_trades,
            'total_pages' => $total_pages,
            'current_page' => $page
        ];
    }
    
    public function getTotalTradingHistoryCount($search = '', $status = '', $user_id = 0) {
        return $this->db->getTotalTradingHistoryCount($search, $status, $user_id);
    }
    
    public function updateTradeStatus($id, $status) {
        return $this->db->updateTradeStatus($id, $status);
    }
    
    // Investment methods
    public function getInvestments($search = '', $status = '', $user_id = 0, $page = 1, $per_page = 20) {
        return $this->db->getInvestments($search, $status, $user_id, $page, $per_page);
    }
    
    public function getTotalInvestmentsCount($search = '', $status = '', $user_id = 0) {
        return $this->db->getTotalInvestmentsCount($search, $status, $user_id);
    }
    
    public function updateInvestmentStatus($id, $status) {
        return $this->db->updateInvestmentStatus($id, $status);
    }
    
    // Staking methods
    public function getStakingInvestments($search = '', $status = '', $user_id = 0, $page = 1, $per_page = 20) {
        return $this->db->getStakingInvestments($search, $status, $user_id, $page, $per_page);
    }
    
    public function getTotalStakingInvestmentsCount($search = '', $status = '', $user_id = 0) {
        return $this->db->getTotalStakingInvestmentsCount($search, $status, $user_id);
    }
    
    public function updateStakingInvestmentStatus($id, $status) {
        return $this->db->updateStakingInvestmentStatus($id, $status);
    }
    
    // Matrix methods
    public function getMatrixInvestments($search = '', $status = '', $user_id = 0, $page = 1, $per_page = 20) {
        return $this->db->getMatrixInvestments($search, $status, $user_id, $page, $per_page);
    }
    
    public function getTotalMatrixInvestmentsCount($search = '', $status = '', $user_id = 0) {
        return $this->db->getTotalMatrixInvestmentsCount($search, $status, $user_id);
    }
    
    public function updateMatrixInvestmentStatus($id, $status) {
        return $this->db->updateMatrixInvestmentStatus($id, $status);
    }
    
    // Copy trades methods
    public function getCopyTrades($search = '', $status = '', $user_id = 0, $page = 1, $per_page = 20) {
        return $this->db->getCopyTrades($search, $status, $user_id, $page, $per_page);
    }
    
    public function getTotalCopyTradesCount($search = '', $status = '', $user_id = 0) {
        return $this->db->getTotalCopyTradesCount($search, $status, $user_id);
    }
    
    public function updateCopyTradeStatus($id, $status) {
        return $this->db->updateCopyTradeStatus($id, $status);
    }
    
    // Traders methods
    public function getTraders($search = '', $status = '', $page = 1, $per_page = 20) {
        return $this->db->getTraders($search, $status, $page, $per_page);
    }
    
    public function getTotalTradersCount($search = '', $status = '') {
        return $this->db->getTotalTradersCount($search, $status);
    }
    
    public function addTrader($data) {
        return $this->db->addTrader($data);
    }
    
    public function updateTrader($id, $data) {
        return $this->db->updateTrader($id, $data);
    }
    
    public function deleteTrader($id) {
        return $this->db->deleteTrader($id);
    }
    
    // Signals methods
    public function getSignals($search = '', $status = '', $page = 1, $per_page = 20) {
        return $this->db->getSignals($search, $status, $page, $per_page);
    }
    
    public function getTotalSignalsCount($search = '', $status = '') {
        return $this->db->getTotalSignalsCount($search, $status);
    }
    
    public function addSignal($data) {
        return $this->db->addSignal($data);
    }
    
    public function updateSignal($id, $data) {
        return $this->db->updateSignal($id, $data);
    }
    
    public function deleteSignal($id) {
        return $this->db->deleteSignal($id);
    }
    
    // User signals methods
    public function getUserSignals($search = '', $status = '', $user_id = 0, $page = 1, $per_page = 20) {
        return $this->db->getUserSignals($search, $status, $user_id, $page, $per_page);
    }
    
    public function getTotalUserSignalsCount($search = '', $status = '', $user_id = 0) {
        return $this->db->getTotalUserSignalsCount($search, $status, $user_id);
    }
    
    public function updateUserSignalStatus($id, $status) {
        return $this->db->updateUserSignalStatus($id, $status);
    }
    
    // KYC methods
    public function getKYCVerifications($search = '', $status = '', $page = 1, $per_page = 20) {
        return $this->db->getKYCVerifications($search, $status, $page, $per_page);
    }
    
    public function getTotalKYCVerificationsCount($search = '', $status = '') {
        return $this->db->getTotalKYCVerificationsCount($search, $status);
    }
    
    public function updateKYCStatus($id, $status, $reason = '') {
        return $this->db->updateKYCStatus($id, $status, $reason);
    }
    
    // Recharge pins methods
    public function getRechargePins($search = '', $status = '', $page = 1, $per_page = 20) {
        return $this->db->getRechargePins($search, $status, $page, $per_page);
    }
    
    public function getTotalRechargePinsCount($search = '', $status = '') {
        return $this->db->getTotalRechargePinsCount($search, $status);
    }
    
    public function generateRechargePin($amount, $generated_by) {
        return $this->db->generateRechargePin($amount, $generated_by);
    }
    
    public function deleteRechargePin($id) {
        return $this->db->deleteRechargePin($id);
    }
    
    // Support tickets methods
    public function getSupportTickets($search = '', $status = '', $page = 1, $per_page = 20) {
        return $this->db->getSupportTickets($search, $status, $page, $per_page);
    }
    
    public function getTotalSupportTicketsCount($search = '', $status = '') {
        return $this->db->getTotalSupportTicketsCount($search, $status);
    }
    
    public function updateSupportTicketStatus($id, $status) {
        return $this->db->updateSupportTicketStatus($id, $status);
    }
    
    public function replyToTicket($id, $reply) {
        return $this->db->replyToTicket($id, $reply);
    }

    /**
     * Redirect to login if not admin
     */
    public function requireAdmin() {
        if (!$this->isAdmin()) {
            header('Location: ../auth/login.php');
            exit;
        }
    }

    /**
     * Admin Dashboard Methods
     */
    public function getTotalUsers() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM users");
        $stmt->execute();
        $result = $stmt->get_result();
        $total = $result ? ($result->fetch_assoc()['total'] ?? 0) : 0;
        $stmt->close();
        return $total;
    }

    public function getPendingKycCount() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM users WHERE verification_status = 'pending'");
        $stmt->execute();
        $result = $stmt->get_result();
        $total = $result ? ($result->fetch_assoc()['total'] ?? 0) : 0;
        $stmt->close();
        return $total;
    }

    public function getTotalDeposits() {
        $stmt = $this->db->prepare("SELECT SUM(amount) as total FROM transactions WHERE type = 'deposit' AND status = 'completed'");
        $stmt->execute();
        $result = $stmt->get_result();
        $total = $result ? ($result->fetch_assoc()['total'] ?? 0) : 0;
        $stmt->close();
        return $total;
    }

    public function getTotalWithdrawals() {
        $stmt = $this->db->prepare("SELECT SUM(amount) as total FROM transactions WHERE type = 'withdrawal' AND status = 'completed'");
        $stmt->execute();
        $result = $stmt->get_result();
        $total = $result ? ($result->fetch_assoc()['total'] ?? 0) : 0;
        $stmt->close();
        return $total;
    }

    public function getRecentUsers($limit = 5) {
        $stmt = $this->db->prepare("SELECT id, name, email, status, created_at FROM users ORDER BY created_at DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $stmt->close();
        return $users;
    }

    public function getRecentTransactions($limit = 10) {
        $stmt = $this->db->prepare("SELECT * FROM transactions ORDER BY created_at DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $transactions = [];
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }
        $stmt->close();
        return $transactions;
    }

    public function getNewUsersToday() {
        $today = date('Y-m-d');
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM users WHERE DATE(created_at) = ?");
        $stmt->bind_param("s", $today);
        $stmt->execute();
        $result = $stmt->get_result();
        $total = $result ? ($result->fetch_assoc()['total'] ?? 0) : 0;
        $stmt->close();
        return $total;
    }

    public function getActiveUsersCount() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
        $stmt->execute();
        $result = $stmt->get_result();
        $total = $result ? ($result->fetch_assoc()['total'] ?? 0) : 0;
        $stmt->close();
        return $total;
    }

    public function getVerifiedUsersCount() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM users WHERE verification_status = 'verified'");
        $stmt->execute();
        $result = $stmt->get_result();
        $total = $result ? ($result->fetch_assoc()['total'] ?? 0) : 0;
        $stmt->close();
        return $total;
    }

    public function getAllUsers() {
        $stmt = $this->db->prepare("SELECT id, name, email, status, created_at FROM users ORDER BY created_at DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $stmt->close();
        return $users;
    }

    public function updateUser($userId, $data) {
        return $this->db->updateUser($userId, $data);
    }
} 