<?php
require_once __DIR__ . '/../datafile/config.php';

class Database {
    private $connection;
    private $host = 'localhost';
    private $username = 'root';
    private $password = '';
    private $database = 'new';

    public function __construct() {
        try {
            $this->connection = new mysqli($this->host, $this->username, $this->password, $this->database);
            
            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }
            
            $this->connection->set_charset("utf8mb4");
        } catch (Exception $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw $e;
        }
    }

    public function createUser($data) {
        try {
            $query = "INSERT INTO users (username, email, password, name, phone, country, referral_code, referred_by) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->connection->prepare($query);
            
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $referralCode = substr(md5(uniqid()), 0, 8);
            $referredBy = !empty($data['ref']) ? $data['ref'] : null;
            
            $stmt->bind_param("ssssssss", 
                $data['username'],
                $data['email'],
                $hashedPassword,
                $data['name'],
                $data['phone'],
                $data['country'],
                $referralCode,
                $referredBy
            );
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'id' => $this->connection->insert_id
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to create user'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getUserById($id) {
        $query = "SELECT * FROM users WHERE id = ? AND deleted = 0";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getUserByEmail($email) {
        try {
            // Debug log
            error_log("Getting user by email: $email");
            
            $query = "SELECT * FROM users WHERE email = ?";
            $stmt = $this->connection->prepare($query);
            
            if (!$stmt) {
                error_log("Prepare failed: " . $this->connection->error);
                return null;
            }
            
            $stmt->bind_param('s', $email);
            
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                return null;
            }
            
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            // Debug log
            error_log("User data retrieved: " . print_r($user, true));
            
            return $user;
        } catch (Exception $e) {
            error_log("Database error in getUserByEmail: " . $e->getMessage());
            return null;
        }
    }

    public function getUserStats($userId) {
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    public function createTrade($data) {
        try {
            // Get current user data
            $user = $this->getUserById($data['user_id']);
            
            // Get trading fee from settings
            $trading_fee_percentage = $this->getSetting('trading_fee', 1); // Default 1% if not set
            $fee_amount = $data['amount'] * ($trading_fee_percentage / 100);
            $total_cost = $data['amount'] + $fee_amount;
            
            // Check user's balance against total cost including fee
            if ($user['balance'] < $total_cost) {
                return [
                    'success' => false,
                    'message' => sprintf(
                        'Insufficient balance. Amount: $%s, Fee: $%s, Total Required: $%s',
                        number_format($data['amount'], 2),
                        number_format($fee_amount, 2),
                        number_format($total_cost, 2)
                    )
                ];
            }

            // Start transaction
            $this->connection->begin_transaction();
            
            try {
                // Insert trade record with fee information
                $query = "INSERT INTO trading_history (
                    user_id, asset, amount, type, leverage, expiration,
                    fee_percentage, fee_amount, total_cost, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
                
                $stmt = $this->connection->prepare($query);
                if (!$stmt) {
                    throw new Exception("Failed to prepare trade query: " . $this->connection->error);
                }
                
                $stmt->bind_param(
                    "isdsisddd",
                    $data['user_id'],
                    $data['asset'],
                    $data['amount'],
                    $data['type'],
                    $data['leverage'],
                    $data['expiration'],
                    $trading_fee_percentage,
                    $fee_amount,
                    $total_cost
                );
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to create trade: " . $stmt->error);
                }

                // Update user balance (deduct total cost including fee)
                $query = "UPDATE users SET balance = balance - ? WHERE id = ?";
                $stmt = $this->connection->prepare($query);
                
                if (!$stmt) {
                    throw new Exception("Failed to prepare balance update: " . $this->connection->error);
                }
                
                $stmt->bind_param("di", $total_cost, $data['user_id']);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update balance: " . $stmt->error);
                }
                
                // Commit transaction
                $this->connection->commit();
                
                return [
                    'success' => true,
                    'message' => sprintf(
                        'Trade created successfully! Amount: $%s, Fee: $%s, Total Cost: $%s',
                        number_format($data['amount'], 2),
                        number_format($fee_amount, 2),
                        number_format($total_cost, 2)
                    )
                ];
                
            } catch (Exception $e) {
                $this->connection->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("Database error in createTrade: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to process trade. Please try again.'
            ];
        }
    }
    public function getUserTrades($userId, $limit = 10) {
        $query = "SELECT * FROM trading_history 
                  WHERE user_id = ? 
                  ORDER BY created_at DESC 
                  LIMIT ?";
        
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param("ii", $userId, $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Failed to get user trades: " . $e->getMessage());
            return [];
        }
    }

    public function createDeposit($data) {
        try {
            $query = "INSERT INTO deposits (user_id, amount, payment_method, status) 
                     VALUES (?, ?, ?, 'pending')";
            
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param("ids", 
                $data['user_id'],
                $data['amount'],
                $data['payment_method']
            );
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Deposit request created successfully',
                    'deposit_id' => $this->connection->insert_id
                ];
            }
            
            throw new Exception("Failed to create deposit");
            
        } catch (Exception $e) {
            error_log("Deposit error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getUserDeposits($userId) {
        $query = "SELECT * FROM deposits 
                  WHERE user_id = ? 
                  ORDER BY created_at DESC";
        
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Failed to get user deposits: " . $e->getMessage());
            return [];
        }
    }

    public function getWalletAddress($method) {
        try {
            // Debug log
            error_log("Fetching wallet address for method: " . $method);
            
            // Convert payment method to column name
            $column_map = [
                'Bitcoin' => 'btc_address',
                'Ethereum' => 'eth_address',
                'Litecoin' => 'ltc_address',
                'USDT' => 'usdt_address',
                'BNB' => 'bnb_address',
                'XRP' => 'xrp_address',
                'DOGE' => 'doge_address'
            ];
            
            $column = $column_map[$method] ?? '';
            if (empty($column)) {
                error_log("Invalid payment method: " . $method);
                return '';
            }

            // Get the first wallet address record (usually admin wallet)
            $query = "SELECT $column FROM wallet_addresses ORDER BY id ASC LIMIT 1";
            error_log("SQL Query: " . $query);
            
            $result = $this->connection->query($query);
            if (!$result) {
                error_log("SQL Error: " . $this->connection->error);
                return '';
            }
            
            $row = $result->fetch_assoc();
            if (!$row || empty($row[$column])) {
                error_log("No wallet address found for column: " . $column);
                return '';
            }
            
            error_log("Found wallet address: " . $row[$column]);
            return $row[$column];
            
        } catch (Exception $e) {
            error_log("Failed to get wallet address: " . $e->getMessage());
            return '';
        }
    }

    public function updateDeposit($id, $userId, $data) {
        try {
            $query = "UPDATE deposits SET 
                      transaction_id = ?, 
                      proof_image = ?,
                      status = ? 
                      WHERE id = ? AND user_id = ?";
                      
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param('sssii', 
                $data['transaction_id'],
                $data['proof_image'],
                $data['status'],
                $id,
                $userId
            );
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Deposit updated successfully'
                ];
            }
            
            throw new Exception("Failed to update deposit");
            
        } catch (Exception $e) {
            error_log("Deposit update error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function beginTransaction() {
        return $this->connection->begin_transaction();
    }

    public function commit() {
        return $this->connection->commit();
    }

    public function rollback() {
        return $this->connection->rollback();
    }

    public function prepare($query) {
        $stmt = $this->connection->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->connection->error);
        }
        return $stmt;
    }

    public function getLastInsertId() {
        return $this->connection->insert_id;
    }

    public function getDeposits($userId) {
        try {
            $query = "SELECT d.*, 
                             DATE_FORMAT(d.created_at, '%d %b %Y %h:%i %p') as formatted_date 
                      FROM deposits d 
                      WHERE d.user_id = ? 
                      ORDER BY d.created_at DESC";
                      
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param('i', $userId);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to fetch deposits");
            }
            
            $result = $stmt->get_result();
            $deposits = [];
            
            while ($row = $result->fetch_assoc()) {
                // Format amount with 2 decimal places
                $row['amount'] = number_format($row['amount'], 2);
                
                // Add status badge class
                $row['status_badge'] = $this->getStatusBadgeClass($row['status']);
                
                $deposits[] = $row;
            }
            
            return $deposits;
            
        } catch (Exception $e) {
            error_log("Error fetching deposits: " . $e->getMessage());
            return [];
        }
    }

    public function getWithdrawals($userId) {
        try {
            $query = "SELECT w.*, 
                             DATE_FORMAT(w.created_at, '%d %b %Y %h:%i %p') as formatted_date 
                      FROM withdrawals w 
                      WHERE w.user_id = ? 
                      ORDER BY w.created_at DESC";
                      
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param('i', $userId);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to fetch withdrawals");
            }
            
            $result = $stmt->get_result();
            $withdrawals = [];
            
            while ($row = $result->fetch_assoc()) {
                // Format amount with 2 decimal places
                $row['amount'] = number_format($row['amount'], 2);
                
                // Add status badge class
                $row['status_badge'] = $this->getStatusBadgeClass($row['status']);
                
                $withdrawals[] = $row;
            }
            
            return $withdrawals;
            
        } catch (Exception $e) {
            error_log("Error fetching withdrawals: " . $e->getMessage());
            return [];
        }
    }

    private function getStatusBadgeClass($status) {
        $badges = [
            'pending' => 'badge-warning',
            'approved' => 'badge-success',
            'rejected' => 'badge-danger',
            'cancelled' => 'badge-secondary'
        ];
        
        return $badges[$status] ?? 'badge-secondary';
    }

    public function getUserBalance($userId) {
        try {
            $query = "SELECT balance FROM users WHERE id = ?";
            $stmt = $this->connection->prepare($query);
            
            if (!$stmt) {
                error_log("Prepare failed in getUserBalance: " . $this->connection->error);
                return 0;
            }
            
            $stmt->bind_param('i', $userId);
            
            if (!$stmt->execute()) {
                error_log("Execute failed in getUserBalance: " . $stmt->error);
                return 0;
            }
            
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            return $user ? (float)$user['balance'] : 0;
        } catch (Exception $e) {
            error_log("Error in getUserBalance: " . $e->getMessage());
            return 0;
        }
    }

    public function subscribeToSignal($signal_name, $amount, $userId) {
        try {
            $this->connection->begin_transaction();

            // Get current balance
            $balance_query = "SELECT balance FROM users WHERE id = ?";
            $stmt = $this->connection->prepare($balance_query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if (!$user) {
                throw new Exception("User not found");
            }
            
            $current_balance = $user['balance'];

            // Check balance
            if ($current_balance < $amount) {
                throw new Exception("Insufficient balance. You need $" . number_format($amount, 2));
            }

            // Get signal ID first
            $signal_query = "SELECT id FROM signals WHERE name = ?";
            $stmt = $this->connection->prepare($signal_query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->connection->error);
            }
            
            $stmt->bind_param("s", $signal_name);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                throw new Exception("Signal not found");
            }
            
            $signal = $result->fetch_assoc();
            error_log("Found signal ID: " . $signal['id']);

            // Update balance
            $new_balance = $current_balance - $amount;
            $update_balance = "UPDATE users SET balance = ? WHERE id = ?";
            $stmt = $this->connection->prepare($update_balance);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->connection->error);
            }
            
            $stmt->bind_param("di", $new_balance, $userId);
            if (!$stmt->execute()) {
                throw new Exception("Failed to update balance: " . $stmt->error);
            }

            // Insert subscription
            $insert_signal = "INSERT INTO user_signals (user_id, signal_id, status, created_at) VALUES (?, ?, 'active', NOW())";
            $stmt = $this->connection->prepare($insert_signal);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->connection->error);
            }
            
            $stmt->bind_param("ii", $userId, $signal['id']);
            if (!$stmt->execute()) {
                throw new Exception("Failed to create subscription: " . $stmt->error);
            }

            // Commit transaction
            $this->connection->commit();
            error_log("Transaction committed successfully");

            return [
                'success' => true,
                'message' => 'Successfully subscribed to ' . $signal_name,
                'new_balance' => $new_balance
            ];

        } catch (Exception $e) {
            $this->connection->rollback();
            error_log("Signal subscription error: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAllSignals() {
        try {
            // Get all signals
            $query = "SELECT * FROM signals ORDER BY price ASC";
            $result = $this->connection->query($query);
            
            $signals = [];
            while ($row = $result->fetch_assoc()) {
                $row['features'] = json_decode($row['features'], true) ?? [];
                $signals[] = $row;
            }
            
            return $signals;
        } catch (Exception $e) {
            error_log("Error fetching signals: " . $e->getMessage());
            return [];
        }
    }
    private function insertDefaultSignals() {
        $signals = [
            [
                'name' => 'Basic Signals',
                'price' => 650.00,
                'success_rate' => 25,
                'tier' => 'BASIC',
                'features' => json_encode(['Basic Signals', 'Email Support'])
            ],
            [
                'name' => 'Omentum Signals',
                'price' => 900.00,
                'success_rate' => 25,
                'tier' => 'STANDARD',
                'features' => json_encode(['Standard Signals', 'Chat Support'])
            ],
            [
                'name' => 'Breakout Signals',
                'price' => 1300.00,
                'success_rate' => 32,
                'tier' => 'PRO',
                'features' => json_encode(['Advanced Signals', 'Priority Support'])
            ],
            [
                'name' => 'Omentum+² Signals',
                'price' => 1080.00,
                'success_rate' => 35,
                'tier' => 'EXPERT',
                'features' => json_encode(['Premium Signals', '24/7 Support'])
            ],
            [
                'name' => 'Breakout+² Signals[PrO]',
                'price' => 1650.00,
                'success_rate' => 55,
                'tier' => 'ELITE',
                'features' => json_encode(['Pro Signals', 'VIP Support', 'Priority Access'])
            ],
            [
                'name' => 'Buying Oversold',
                'price' => 2000.00,
                'success_rate' => 68,
                'tier' => 'EXPERT',
                'features' => json_encode(['Expert Signals', 'Premium Support', 'Market Analysis'])
            ],
            [
                'name' => 'Trend Signal',
                'price' => 2400.00,
                'success_rate' => 75,
                'tier' => 'ELITE',
                'features' => json_encode(['Elite Signals', 'Dedicated Support', 'Advanced Analysis'])
            ],
            [
                'name' => 'AntMiner-S7-4.8THs-1250w',
                'price' => 3000.00,
                'success_rate' => 85,
                'tier' => 'ELITE',
                'features' => json_encode(['Mining Signals', '24/7 Support', 'Hardware Support'])
            ]
        ];

        foreach ($signals as $signal) {
            $query = "INSERT INTO signals (name, price, success_rate, tier, features) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param("sdiss", 
                $signal['name'], 
                $signal['price'], 
                $signal['success_rate'], 
                $signal['tier'], 
                $signal['features']
            );
            $stmt->execute();
        }
    }

    public function isSubscribedToSignal($userId, $signalId) {
        try {
            $query = "SELECT id FROM user_signals WHERE user_id = ? AND signal_id = ? AND status = 'active'";
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param('ii', $userId, $signalId);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->num_rows > 0;
        } catch (Exception $e) {
            error_log("Error checking subscription: " . $e->getMessage());
            return false;
        }
    }

    public function getAllPlans() {
        try {
            $query = "SELECT * FROM investment_plans ORDER BY min_deposit ASC";
            $result = $this->connection->query($query);
            
            $plans = [];
            while ($row = $result->fetch_assoc()) {
                $row['features'] = json_decode($row['features'], true) ?? [];
                $plans[] = $row;
            }
            
            return $plans;
        } catch (Exception $e) {
            error_log("Error fetching plans: " . $e->getMessage());
            return [];
        }
    }

    public function hasActivePlan($userId, $planId) {
        try {
            $query = "SELECT id FROM user_plans WHERE user_id = ? AND plan_id = ? AND status = 'active'";
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param('ii', $userId, $planId);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->num_rows > 0;
        } catch (Exception $e) {
            error_log("Error checking plan: " . $e->getMessage());
            return false;
        }
    }

    public function purchasePlan($plan_name, $amount, $userId) {
        try {
            $this->connection->begin_transaction();

            // Get current balance
            $balance_query = "SELECT balance FROM users WHERE id = ?";
            $stmt = $this->connection->prepare($balance_query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if (!$user) {
                throw new Exception("User not found");
            }
            
            $current_balance = $user['balance'];

            // Check balance
            if ($current_balance < $amount) {
                throw new Exception("Insufficient balance. You need $" . number_format($amount, 2));
            }

            // Get plan details
            $plan_query = "SELECT id, duration FROM investment_plans WHERE name = ?";
            $stmt = $this->connection->prepare($plan_query);
            $stmt->bind_param("s", $plan_name);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Plan not found");
            }
            
            $plan = $result->fetch_assoc();

            // Update balance
            $new_balance = $current_balance - $amount;
            $update_balance = "UPDATE users SET balance = ? WHERE id = ?";
            $stmt = $this->connection->prepare($update_balance);
            $stmt->bind_param("di", $new_balance, $userId);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update balance");
            }

            // Create plan subscription
            $created_at = date('Y-m-d H:i:s');
            $expires_at = date('Y-m-d H:i:s', strtotime("+{$plan['duration']} days"));

            $insert_plan = "INSERT INTO user_plans (user_id, plan_id, amount, status, created_at, expires_at) 
                            VALUES (?, ?, ?, 'active', ?, ?)";
            $stmt = $this->connection->prepare($insert_plan);
            $stmt->bind_param("iidss", $userId, $plan['id'], $amount, $created_at, $expires_at);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create plan subscription");
            }

            $this->connection->commit();
            
            return [
                'success' => true,
                'message' => 'Successfully purchased ' . $plan_name,
                'new_balance' => $new_balance
            ];

        } catch (Exception $e) {
            $this->connection->rollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getUserActivePlans($userId) {
        try {
            $query = "SELECT p.*, ip.name, ip.roi, ip.tier, ip.duration 
                      FROM user_plans p 
                      JOIN investment_plans ip ON p.plan_id = ip.id 
                      WHERE p.user_id = ? AND p.status = 'active'
                      ORDER BY p.created_at DESC";
                      
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $plans = [];
            while ($row = $result->fetch_assoc()) {
                $plans[] = $row;
            }
            
            return $plans;
        } catch (Exception $e) {
            error_log("Error fetching user plans: " . $e->getMessage());
            return [];
        }
    }

    public function updatePlanExpiryDate($planId, $expiryDate) {
        try {
            $query = "UPDATE user_plans SET expires_at = ? WHERE id = ?";
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param('si', $expiryDate, $planId);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating plan expiry: " . $e->getMessage());
            return false;
        }
    }
    public function getUserReferralStats($userId) {
        try {
            // Get referrer's username
            $user_query = "SELECT username FROM users WHERE id = ?";
            $stmt = $this->connection->prepare($user_query);
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
    
            // Get referral stats
            $stats_query = "SELECT 
                COUNT(id) as total_referrals,
                COALESCE(SUM(referral_earnings), 0) as total_earnings
                FROM users 
                WHERE referred_by = ?";
            $stmt = $this->connection->prepare($stats_query);
            $stmt->bind_param('s', $user['username']);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats = $result->fetch_assoc();
    
            // Get referral details
            $referrals_query = "SELECT 
                u.username,
                u.created_at,
                COALESCE(SUM(d.amount), 0) as total_deposits,
                COALESCE(SUM(d.amount * 0.1), 0) as earnings,
                CASE WHEN COUNT(d.id) > 0 THEN 1 ELSE 0 END as has_deposited
                FROM users u
                LEFT JOIN deposits d ON u.id = d.user_id AND d.status = 'completed'
                WHERE u.referred_by = ?
                GROUP BY u.id
                ORDER BY u.created_at DESC";
            $stmt = $this->connection->prepare($referrals_query);
            $stmt->bind_param('s', $user['username']);
            $stmt->execute();
            $referrals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
            return [
                'total_referrals' => $stats['total_referrals'],
                'total_earnings' => $stats['total_earnings'],
                'referrals' => $referrals
            ];
        } catch (Exception $e) {
            error_log("Error fetching referral stats: " . $e->getMessage());
            return [
                'total_referrals' => 0,
                'total_earnings' => 0,
                'referrals' => []
            ];
        }
    }

    public function getVerificationStatus($userId) {
        try {
            $query = "SELECT 
                verification_status as status,
                reject_reason,
                verified_at
                FROM users 
                WHERE id = ?";
                
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if (!$row) {
                return [
                    'status' => 'unverified',
                    'reject_reason' => null,
                    'verified_at' => null
                ];
            }
            
            return [
                'status' => $row['status'] ?? 'unverified',
                'reject_reason' => $row['reject_reason'],
                'verified_at' => $row['verified_at']
            ];
        } catch (Exception $e) {
            error_log("Error getting verification status: " . $e->getMessage());
            return [
                'status' => 'unverified',
                'reject_reason' => null,
                'verified_at' => null
            ];
        }
    }

    /**
     * Get database connection
     * 
     * @return mysqli Database connection
     */
    public function getConnection() {
        return $this->connection;
    }

    public function getTotalUsers() {
        try {
            $query = "SELECT COUNT(*) as total FROM users WHERE deleted = 0";
            $stmt = $this->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();
            return intval($result->fetch_assoc()['total']);
        } catch (Exception $e) {
            error_log("Error getting total users: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalDeposits() {
        try {
            $query = "SELECT COALESCE(SUM(amount), 0) as total FROM deposits WHERE status = 'completed'";
            $stmt = $this->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();
            return floatval($result->fetch_assoc()['total']);
        } catch (Exception $e) {
            error_log("Error getting total deposits: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalWithdrawals() {
        try {
            $query = "SELECT COALESCE(SUM(amount), 0) as total FROM withdrawals WHERE status = 'completed'";
            $stmt = $this->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();
            return floatval($result->fetch_assoc()['total']);
        } catch (Exception $e) {
            error_log("Error getting total withdrawals: " . $e->getMessage());
            return 0;
        }
    }

    public function getPendingVerifications() {
        try {
            $query = "SELECT COUNT(*) as total FROM users WHERE verification_status = 'pending' AND deleted = 0";
            $stmt = $this->prepare($query);
        $stmt->execute();
            $result = $stmt->get_result();
            return intval($result->fetch_assoc()['total']);
        } catch (Exception $e) {
            error_log("Error getting pending verifications: " . $e->getMessage());
            return 0;
        }
    }

    public function getRecentUsers($limit = 10) {
        $query = "SELECT * FROM users ORDER BY created_at DESC LIMIT ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getPendingUsers() {
        $query = "SELECT COUNT(*) as total FROM users WHERE status = 'pending'";
        $result = $this->connection->query($query);
        return $result->fetch_assoc()['total'] ?? 0;
    }

    public function getPendingDeposits() {
        $query = "SELECT COUNT(*) as total FROM deposits WHERE status = 'pending'";
        $result = $this->connection->query($query);
        return $result->fetch_assoc()['total'] ?? 0;
    }

    public function getPendingWithdrawals() {
        $query = "SELECT COUNT(*) as total FROM withdrawals WHERE status = 'pending'";
        $result = $this->connection->query($query);
        return $result->fetch_assoc()['total'] ?? 0;
    }

    public function getUsers($status = 'all', $verified = 'all', $search = '', $page = 1, $per_page = 20) {
        try {
            $where = ["deleted = 0"];
            $params = [];
            $types = '';
            
            if ($status !== 'all') {
                $where[] = "status = ?";
                $params[] = $status;
                $types .= 's';
            }
            
            if ($verified !== 'all') {
                $where[] = "verification_status = ?";
                $params[] = $verified;
                $types .= 's';
            }
            
            if ($search) {
                $where[] = "(name LIKE ? OR email LIKE ? OR username LIKE ? OR phone LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $types .= 'ssss';
            }
            
            $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            
            // Convert page and per_page to integers
            $page = (int)$page;
            $per_page = (int)$per_page;
            $offset = ($page - 1) * $per_page;
            
            // Add limit parameters
            $types .= 'ii';
            $params[] = $offset;
            $params[] = $per_page;
            
            $sql = "SELECT * FROM users $whereClause ORDER BY id DESC LIMIT ?, ?";
            
            // Prepare and execute
            $stmt = $this->connection->prepare($sql);
            if ($params) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error fetching users: " . $e->getMessage());
            return [];
        }
    }

    public function getTotalUsersCount($status = 'all', $verified = 'all', $search = '') {
        try {
            $where = [];
            $params = [];
            $types = '';
            
            if ($status !== 'all') {
                $where[] = "status = ?";
                $params[] = $status;
                $types .= 's';
            }
            
            if ($verified !== 'all') {
                $where[] = "verification_status = ?";
                $params[] = $verified;
                $types .= 's';
            }
            
            if ($search) {
                $where[] = "(name LIKE ? OR email LIKE ? OR username LIKE ? OR phone LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $types .= 'ssss';
            }
            
            $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $sql = "SELECT COUNT(*) as total FROM users $whereClause";
            
            // Prepare and execute
            $stmt = $this->connection->prepare($sql);
            if ($params) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            
            return $stmt->get_result()->fetch_assoc()['total'];
            
        } catch (Exception $e) {
            error_log("Error getting total users count: " . $e->getMessage());
            return 0;
        }
    }

    public function getAdminUserWithdrawals($userId, $limit = null) {
        try {
            $sql = "SELECT w.*, 
                    u.name as user_name, 
                    u.email as user_email 
                    FROM withdrawals w 
                    JOIN users u ON w.user_id = u.id 
                    WHERE w.user_id = ? 
                    ORDER BY w.created_at DESC";

            if ($limit) {
                $sql .= " LIMIT ?";
            }

            $stmt = $this->connection->prepare($sql);
            
            if ($limit) {
                $stmt->bind_param('ii', $userId, $limit);
            } else {
                $stmt->bind_param('i', $userId);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_all(MYSQLI_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error getting user withdrawals: " . $e->getMessage());
            return [];
        }
    }

    public function getAdminUserDeposits($userId, $limit = null) {
        try {
            $sql = "SELECT d.*, 
                    u.name as user_name, 
                    u.email as user_email 
                    FROM deposits d 
                    JOIN users u ON d.user_id = u.id 
                    WHERE d.user_id = ? 
                    ORDER BY d.created_at DESC";

            if ($limit) {
                $sql .= " LIMIT ?";
            }

            $stmt = $this->connection->prepare($sql);
            
            if ($limit) {
                $stmt->bind_param('ii', $userId, $limit);
            } else {
                $stmt->bind_param('i', $userId);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_all(MYSQLI_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error getting user deposits: " . $e->getMessage());
            return [];
        }
    }

    public function getAdminUserTrades($userId, $limit = null) {
        try {
            $sql = "SELECT t.*, 
                    u.name as user_name, 
                    u.email as user_email 
                    FROM trading_history t 
                    JOIN users u ON t.user_id = u.id 
                    WHERE t.user_id = ? 
                    ORDER BY t.created_at DESC";

            if ($limit) {
                $sql .= " LIMIT ?";
            }

            $stmt = $this->connection->prepare($sql);
            
            if ($limit) {
                $stmt->bind_param('ii', $userId, $limit);
            } else {
                $stmt->bind_param('i', $userId);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_all(MYSQLI_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error getting user trades: " . $e->getMessage());
            return [];
        }
    }
    public function getAdminTotalWithdrawalsCount($status = 'all', $user_id = null, $search = '') {
        try {
            $conditions = [];
            $params = [];
            $types = "";
            
            if ($status !== 'all') {
                $conditions[] = "w.status = ?";
                $params[] = $status;
                $types .= "s";
            }
            
            if ($user_id) {
                $conditions[] = "w.user_id = ?";
                $params[] = $user_id;
                $types .= "i";
            }
            
            if ($search) {
                $search = "%$search%";
                $conditions[] = "(u.name LIKE ? OR u.email LIKE ? OR w.payment_method LIKE ?)";
                $params = array_merge($params, [$search, $search, $search]);
                $types .= "sss";
            }
            
            $where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";
            
            $sql = "SELECT COUNT(*) as total 
                    FROM withdrawals w 
                    JOIN users u ON w.user_id = u.id 
                    $where";
                    
            $stmt = $this->connection->prepare($sql);
            if ($params) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            
            return $stmt->get_result()->fetch_assoc()['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Error in getAdminTotalWithdrawalsCount: " . $e->getMessage());
            return 0;
        }
    }
    
    public function getWithdrawalStats() {
        try {
            $stats = [
                'total' => 0,
                'pending' => 0,
                'approved' => 0,
                'rejected' => 0
            ];
            
            $query = "SELECT 
                      COUNT(*) as total,
                      SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                      SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                      SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                      SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END) as total_amount
                      FROM withdrawals";
                      
            $result = $this->connection->query($query);
            if ($row = $result->fetch_assoc()) {
                $stats = [
                    'total' => (int)$row['total'],
                    'pending' => (int)$row['pending'],
                    'approved' => (int)$row['approved'],
                    'rejected' => (int)$row['rejected'],
                    'total_amount' => (float)$row['total_amount']
                ];
            }
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error getting withdrawal stats: " . $e->getMessage());
            return [
                'total' => 0,
                'pending' => 0,
                'approved' => 0,
                'rejected' => 0,
                'total_amount' => 0
            ];
        }
    }
    public function getAdminUserTransactions($userId, $limit = null) {
        try {
            $sql = "SELECT 
                    'withdrawal' as type,
                    id,
                    amount,
                    status,
                    created_at
                    FROM withdrawals 
                    WHERE user_id = ?
                    UNION ALL
                    SELECT 
                    'deposit' as type,
                    id,
                    amount,
                    status,
                    created_at
                    FROM deposits 
                    WHERE user_id = ?
                    ORDER BY created_at DESC";

            if ($limit) {
                $sql .= " LIMIT ?";
            }

            $stmt = $this->connection->prepare($sql);
            
            if ($limit) {
                $stmt->bind_param('iii', $userId, $userId, $limit);
            } else {
                $stmt->bind_param('ii', $userId, $userId);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_all(MYSQLI_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error getting user transactions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get trades with pagination for admin panel
     */
    public function getTrades($status = 'all', $type = 'all', $user_id = null, $search = '', $page = 1, $per_page = 20) {
        try {
            $conditions = [];
            $params = [];
            $types = "";
            
            // Add status condition
            if ($status !== 'all') {
                $conditions[] = "t.status = ?";
                $params[] = $status;
                $types .= "s";
            }
            
            // Add type condition
            if ($type !== 'all') {
                $conditions[] = "t.type = ?";
                $params[] = $type;
                $types .= "s";
            }
            
            // Add user condition
            if ($user_id) {
                $conditions[] = "t.user_id = ?";
                $params[] = $user_id;
                $types .= "i";
            }
            
            // Add search condition
            if ($search) {
                $search_term = "%$search%";
                $conditions[] = "(t.id LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR t.asset LIKE ?)";
                $params[] = $search_term;
                $params[] = $search_term;
                $params[] = $search_term;
                $params[] = $search_term;
                $types .= "ssss";
            }
            
            // Build WHERE clause
            $where = "";
            if (!empty($conditions)) {
                $where = "WHERE " . implode(" AND ", $conditions);
            }
            
            // Calculate offset
            $offset = ($page - 1) * $per_page;
            
            // Add pagination parameters
            $params[] = $offset;
            $params[] = $per_page;
            $types .= "ii";
            
            // Build query
            $sql = "SELECT t.*, u.name as user_name
                    FROM trading_history t
                    LEFT JOIN users u ON t.user_id = u.id
                    $where
                    ORDER BY t.created_at DESC
                    LIMIT ?, ?";
            
            $stmt = $this->connection->prepare($sql);
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getTrades: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total number of trades for admin panel
     */
    public function getTotalTradesCount($status = 'all', $type = 'all', $user_id = null, $search = '') {
        try {
            $conditions = [];
            $params = [];
            $types = "";
            
            // Add status condition
            if ($status !== 'all') {
                $conditions[] = "t.status = ?";
                $params[] = $status;
                $types .= "s";
            }
            
            // Add type condition
            if ($type !== 'all') {
                $conditions[] = "t.type = ?";
                $params[] = $type;
                $types .= "s";
            }
            
            // Add user condition
            if ($user_id) {
                $conditions[] = "t.user_id = ?";
                $params[] = $user_id;
                $types .= "i";
            }
            
            // Add search condition
            if ($search) {
                $search_term = "%$search%";
                $conditions[] = "(t.id LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR t.asset LIKE ?)";
                $params[] = $search_term;
                $params[] = $search_term;
                $params[] = $search_term;
                $params[] = $search_term;
                $types .= "ssss";
            }
            
            // Build WHERE clause
            $where = "";
            if (!empty($conditions)) {
                $where = "WHERE " . implode(" AND ", $conditions);
            }
            
            // Build query
            $sql = "SELECT COUNT(*) as total
                    FROM trading_history t
                    LEFT JOIN users u ON t.user_id = u.id
                    $where";
            
            // Prepare and execute
            $stmt = $this->connection->prepare($sql);
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc()['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Error in getTotalTradesCount: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get count of pending trades
     */
    public function getPendingTrades() {
        try {
            $sql = "SELECT COUNT(*) as total FROM trading_history WHERE status = 'pending'";
            $result = $this->connection->query($sql);
            return $result->fetch_assoc()['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Error in getPendingTrades: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalTrades() {
        try {
            $query = "SELECT COUNT(*) as total FROM trading_history";
            $result = $this->connection->query($query);
            return $result->fetch_assoc()['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Error getting total trades: " . $e->getMessage());
            return 0;
        }
    }

    public function getTradeStats() {
        try {
            $stats = [
                'total' => 0,
                'pending' => 0,
                'completed' => 0,
                'cancelled' => 0
            ];
            
            $query = "SELECT 
                      COUNT(*) as total,
                      SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                      SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                      SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
                      FROM trading_history";
                      
            $result = $this->connection->query($query);
            if ($row = $result->fetch_assoc()) {
                $stats = [
                    'total' => (int)$row['total'],
                    'pending' => (int)$row['pending'],
                    'completed' => (int)$row['completed'],
                    'cancelled' => (int)$row['cancelled']
                ];
            }
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error getting trade stats: " . $e->getMessage());
            return [
                'total' => 0,
                'pending' => 0,
                'completed' => 0,
                'cancelled' => 0
            ];
        }
    }

    public function getTotalDepositsCount($status = 'all', $user_id = null, $search = '') {
        try {
            // Build query
            $where = [];
            $params = [];
            $types = '';
            
            if ($status !== 'all') {
                $where[] = "status = ?";
                $params[] = $status;
                $types .= 's';
            }
            
            if ($user_id) {
                $where[] = "user_id = ?";
                $params[] = $user_id;
                $types .= 'i';
            }
            
            if ($search) {
                $where[] = "(transaction_id LIKE ? OR payment_method LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $types .= 'ss';
            }
            
            $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
            
            $query = "SELECT COUNT(*) as total FROM deposits $whereClause";
            
            $stmt = $this->connection->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return (int)$row['total'];
            
        } catch (Exception $e) {
            error_log("Error getting deposits count: " . $e->getMessage());
            return 0;
        }
    }
// Admin deposit methods
public function getAdminDepositById($id) {
    $sql = "SELECT d.*, u.name as user_name 
            FROM deposits d 
            LEFT JOIN users u ON d.user_id = u.id 
            WHERE d.id = ?";
            
    $stmt = $this->getConnection()->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

public function getAdminDeposits($status = 'all', $user_id = null, $search = '', $page = 1, $per_page = 20) {
    $sql = "SELECT d.*, u.name as user_name, u.email as user_email 
            FROM deposits d 
            LEFT JOIN users u ON d.user_id = u.id 
            WHERE 1=1";
    
    $params = [];
    $types = "";
    
    if ($status !== 'all') {
        $sql .= " AND d.status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    if ($user_id) {
        $sql .= " AND d.user_id = ?";
        $params[] = $user_id;
        $types .= "i";
    }
    
    if ($search) {
        $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR d.transaction_id LIKE ?)";
        $search = "%$search%";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $types .= "sss";
    }
    
    $sql .= " ORDER BY d.created_at DESC";
    
    // Add pagination
    $offset = ($page - 1) * $per_page;
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = $offset;
    $types .= "ii";
    
    $stmt = $this->getConnection()->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

public function getAdminTotalDepositsCount($status = 'all', $user_id = null, $search = '') {
    $sql = "SELECT COUNT(*) as total 
            FROM deposits d 
            LEFT JOIN users u ON d.user_id = u.id 
            WHERE 1=1";
    
    $params = [];
    $types = "";
    
    if ($status !== 'all') {
        $sql .= " AND d.status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    if ($user_id) {
        $sql .= " AND d.user_id = ?";
        $params[] = $user_id;
        $types .= "i";
    }
    
    if ($search) {
        $sql .= " AND (u.name LIKE ? OR u.email LIKE ? OR d.transaction_id LIKE ?)";
        $search = "%$search%";
        $params = array_merge($params, [$search, $search, $search]);
        $types .= "sss";
    }
    
    $stmt = $this->getConnection()->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total'] ?? 0;
}

public function getAdminPendingDeposits() {
    $sql = "SELECT COUNT(*) as count FROM deposits WHERE status = 'pending'";
    $result = $this->getConnection()->query($sql);
    $row = $result->fetch_assoc();
    return $row['count'] ?? 0;
}

public function updateAdminDeposit($id, $data) {
    // Validate status value
    $allowed_statuses = ['pending', 'completed', 'failed'];
    
    // Map the admin action to the correct database status
    $status = $data['status'] === 'approved' ? 'completed' : 
             ($data['status'] === 'rejected' ? 'failed' : $data['status']);
    
    if (!in_array($status, $allowed_statuses)) {
        error_log("Invalid status value: " . $status);
        return false;
    }

    $sql = "UPDATE deposits SET status = ? WHERE id = ?";
    $stmt = $this->getConnection()->prepare($sql);
    
    if (!$stmt) {
        error_log("Prepare failed: " . $this->getConnection()->error);
        return false;
    }
    
    $stmt->bind_param("si", $status, $id);
    return $stmt->execute();
}

public function getAdminUserTotalDeposits($user_id) {
    $sql = "SELECT SUM(amount) as total 
            FROM deposits 
            WHERE user_id = ? AND status = 'approved'";
            
    $stmt = $this->getConnection()->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total'] ?? 0;
}
    public function updateUser($user_id, $data) {
        try {
            // Start building query
            $updates = [];
            $params = [];
            $types = '';
            
            // Build update statements and params array
            foreach ($data as $key => $value) {
                $updates[] = "$key = ?";
                $params[] = $value;
                
                // Determine parameter type
                if (is_int($value)) {
                    $types .= 'i';
                } elseif (is_float($value)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }
            
            // Add user_id to params
            $params[] = $user_id;
            $types .= 'i';
            
            // Construct final query
            $query = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
            
            // Debug log
            error_log("Update Query: " . $query);
            error_log("Params: " . print_r($params, true));
            error_log("Types: " . $types);
            
            // Prepare and execute statement
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param($types, ...$params);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update user: " . $stmt->error);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error updating user: " . $e->getMessage());
            throw $e;
        }
    }


    public function deleteUser($user_id) {
        try {
            $query = "UPDATE users 
                      SET deleted = 1, 
                          deleted_at = CURRENT_TIMESTAMP 
                      WHERE id = ?";
                      
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param('i', $user_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to delete user: " . $stmt->error);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error deleting user: " . $e->getMessage());
            throw $e;
        }
    }

    public function getVerifications($status = 'all', $type = 'all', $search = '', $page = 1, $per_page = 20) {
        $offset = ($page - 1) * $per_page;
        $where = ["deleted = 0"]; // Only show non-deleted users
        $params = [];
        $types = "";

        // Add status filter
        if ($status !== 'all') {
            $where[] = "verification_status = ?";
            $params[] = $status;
            $types .= "s";
        }

        // Add search filter
        if (!empty($search)) {
            $where[] = "(name LIKE ? OR email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $types .= "ss";
        }

        // Build WHERE clause
        $whereClause = "WHERE " . implode(" AND ", $where);

        $query = "SELECT * FROM users 
                 $whereClause 
                 ORDER BY created_at DESC 
                 LIMIT ?, ?";

        // Add pagination parameters
        $params[] = $offset;
        $params[] = $per_page;
        $types .= "ii";

        $stmt = $this->connection->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getTotalVerificationsCount($status = 'all', $type = 'all', $search = '') {
        $where = ["deleted = 0"];
        $params = [];
        $types = "";

        if ($status !== 'all') {
            $where[] = "verification_status = ?";
            $params[] = $status;
            $types .= "s";
        }

        if ($type !== 'all') {
            $where[] = "type = ?";
            $params[] = $type;
            $types .= "s";
        }

        if (!empty($search)) {
            $where[] = "(name LIKE ? OR email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $types .= "ss";
        }

        $whereClause = "WHERE " . implode(" AND ", $where);

        $query = "SELECT COUNT(*) as total FROM users $whereClause";

        $stmt = $this->connection->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['total'];
    }

    public function getVerificationById($id) {
        $stmt = $this->connection->prepare(
            "SELECT * FROM users 
             WHERE id = ? AND deleted = 0"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateVerification($user_id, $data) {
        try {
            // Debug log
            error_log("updateVerification called with user_id: $user_id");
            error_log("Data: " . print_r($data, true));
            
            $updates = [];
            $params = [];
            $types = '';
            
            foreach ($data as $key => $value) {
                $updates[] = "$key = ?";
                $params[] = $value;
                $types .= is_null($value) ? 's' : (is_int($value) ? 'i' : 's');
            }
            
            $params[] = $user_id;
            $types .= 'i';
            
            $query = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
            
            // Debug query
            error_log("SQL Query: $query");
            error_log("Params: " . print_r($params, true));
            error_log("Types: $types");
            
            $stmt = $this->connection->prepare($query);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->connection->error);
                return false;
            }
            
            $stmt->bind_param($types, ...$params);
            $result = $stmt->execute();
            
            // Debug result
            error_log("Execute result: " . ($result ? "true" : "false"));
            if (!$result) {
                error_log("Execute failed: " . $stmt->error);
            }
            error_log("Affected rows: " . $stmt->affected_rows);
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Error in updateVerification: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateVerificationStatus($user_id, $status) {
        try {
            // Debug log
            error_log("updateVerificationStatus called with user_id: $user_id, status: $status");
            
            $query = "UPDATE users SET verification_status = ? WHERE id = ?";
            
            // Debug query
            error_log("SQL Query: $query");
            
            $stmt = $this->connection->prepare($query);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->connection->error);
                return false;
            }
            
            $stmt->bind_param("si", $status, $user_id);
            $result = $stmt->execute();
            
            // Debug result
            error_log("Execute result: " . ($result ? "true" : "false"));
            if (!$result) {
                error_log("Execute failed: " . $stmt->error);
            }
            error_log("Affected rows: " . $stmt->affected_rows);
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Error in updateVerificationStatus: " . $e->getMessage());
            throw $e;
        }
    }

    public function checkVerificationColumn() {
        try {
            $result = $this->connection->query("SHOW COLUMNS FROM users LIKE 'verification_status'");
            $column = $result->fetch_assoc();
            error_log("Verification status column info: " . print_r($column, true));
            return $column;
        } catch (Exception $e) {
            error_log("Error checking verification column: " . $e->getMessage());
            return null;
        }
    }

    public function checkVerificationStatus($user_id) {
        try {
            $query = "SELECT id, verification_status FROM users WHERE id = ?";
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            error_log("Current verification status for user $user_id: " . print_r($result, true));
            return $result;
        } catch (Exception $e) {
            error_log("Error checking verification status: " . $e->getMessage());
            return null;
        }
    }

    public function updateUserBalance($user_id, $amount, $operation = 'add') {
        try {
            // Get current balance
            $sql = "SELECT balance FROM users WHERE id = ?";
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if (!$result) {
                throw new Exception("User not found");
            }
            
            $current_balance = $result['balance'];
            $new_balance = $operation === 'add' ? $current_balance + $amount : $current_balance - $amount;
            
            // Update balance
            $update_sql = "UPDATE users SET balance = ? WHERE id = ?";
            $update_stmt = $this->getConnection()->prepare($update_sql);
            $update_stmt->bind_param("di", $new_balance, $user_id);
            
            return $update_stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating user balance: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateAdminWithdrawal($id, $data) {
        // Validate status value
        $allowed_statuses = ['pending', 'completed', 'failed'];
        
        // Map the admin action to the correct database status
        $status = $data['status'] === 'approved' ? 'completed' : 
                 ($data['status'] === 'rejected' ? 'failed' : $data['status']);
        
        if (!in_array($status, $allowed_statuses)) {
            error_log("Invalid status value: " . $status);
            return false;
        }

        $sql = "UPDATE withdrawals SET status = ? WHERE id = ?";
        $stmt = $this->getConnection()->prepare($sql);
        
        if (!$stmt) {
            error_log("Prepare failed: " . $this->getConnection()->error);
            return false;
        }
        
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    public function getAdminWithdrawalById($id) {
        try {
            $sql = "SELECT w.*, u.name as user_name 
                    FROM withdrawals w 
                    JOIN users u ON w.user_id = u.id 
                    WHERE w.id = ?";
                    
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            return $stmt->get_result()->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error in getAdminWithdrawalById: " . $e->getMessage());
            return null;
        }
    }

    public function getAdminWithdrawals($status = 'all', $user_id = null, $search = '', $page = 1, $per_page = 20) {
        try {
            $conditions = [];
            $params = [];
            $types = "";
            
            if ($status !== 'all') {
                $conditions[] = "w.status = ?";
                $params[] = $status;
                $types .= "s";
            }
            
            if ($user_id) {
                $conditions[] = "w.user_id = ?";
                $params[] = $user_id;
                $types .= "i";
            }
            
            if ($search) {
                $search = "%$search%";
                $conditions[] = "(u.name LIKE ? OR u.email LIKE ? OR w.payment_method LIKE ?)";
                $params = array_merge($params, [$search, $search, $search]);
                $types .= "sss";
            }
            
            $where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";
            
            $offset = ($page - 1) * $per_page;
            $params[] = $offset;
            $params[] = $per_page;
            $types .= "ii";
            
            $sql = "SELECT w.*, u.name as user_name 
                    FROM withdrawals w 
                    JOIN users u ON w.user_id = u.id 
                    $where 
                    ORDER BY w.created_at DESC 
                    LIMIT ?, ?";
                    
            $stmt = $this->getConnection()->prepare($sql);
            if ($params) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getAdminWithdrawals: " . $e->getMessage());
            return [];
        }
    }

    public function getUserTotalWithdrawals($user_id) {
        try {
            $sql = "SELECT COALESCE(SUM(amount), 0) as total 
                    FROM withdrawals 
                    WHERE user_id = ? AND status = 'completed'";
                    
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            return $stmt->get_result()->fetch_assoc()['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Error in getUserTotalWithdrawals: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get trade by ID for admin panel
     */
    public function getTradeById($id) {
        try {
            // Debug
            error_log("Getting trade by ID: $id");
            
            $sql = "SELECT t.*, 
                    u.name as user_name,
                    a.name as processed_by_name
                    FROM trading_history t
                    LEFT JOIN users u ON t.user_id = u.id
                    LEFT JOIN users a ON t.processed_by = a.id
                    WHERE t.id = ?";
                    
            $stmt = $this->connection->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->connection->error);
                return null;
            }
            
            $stmt->bind_param("i", $id);
            
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                return null;
            }
            
            $result = $stmt->get_result();
            $trade = $result->fetch_assoc();
            
            // Debug
            error_log("Trade data: " . ($trade ? json_encode($trade) : "null"));
            
            return $trade;
        } catch (Exception $e) {
            error_log("Error in getTradeById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get total number of user trades
     */
    public function getUserTotalTrades($user_id) {
        try {
            $sql = "SELECT COUNT(*) as total FROM trading_history WHERE user_id = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            $result = $stmt->get_result();
            return $result->fetch_assoc()['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Error in getUserTotalTrades: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Update trade status and details
     * 
     * @param int $trade_id Trade ID
     * @param array $data Data to update
     * @return bool Success status
     */
    public function updateTrade($trade_id, $data) {
        try {
            $sets = [];
            $params = [];
            $types = '';
            
            foreach ($data as $key => $value) {
                $sets[] = "$key = ?";
                $params[] = $value;
                
                if (is_int($value)) {
                    $types .= 'i';
                } elseif (is_float($value)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }
            
            $params[] = $trade_id;
            $types .= 'i';
            
            $sql = "UPDATE trading_history SET " . implode(', ', $sets) . " WHERE id = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param($types, ...$params);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating trade: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get trade by ID directly (for debugging)
     * 
     * @param int $id Trade ID
     * @return array|null Trade data or null if not found
     */
    public function getTradeByIdDirect($id) {
        try {
            $sql = "SELECT * FROM trading_history WHERE id = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error in getTradeByIdDirect: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update trade details with enhanced error checking
     * 
     * @param int $id Trade ID
     * @param array $data Data to update
     * @return bool Success status
     */
    public function updateTradeAdmin($id, $data) {
        try {
            $fields = [];
            $values = [];
            $types = '';
            
            // Debug information
            error_log("Updating trade #$id with data: " . json_encode($data));
            
            foreach ($data as $key => $value) {
                $fields[] = "$key = ?";
                $values[] = $value;
                $types .= is_int($value) ? 'i' : (is_float($value) ? 'd' : 's');
            }
            
            $values[] = $id;
            $types .= 'i';
            
            $sql = "UPDATE trading_history SET " . implode(', ', $fields) . " WHERE id = ?";
            error_log("SQL Query: $sql");
            error_log("Parameter types: $types");
            error_log("Parameter values: " . json_encode($values));
            
            // Force direct execution for debugging
            $stmt = $this->connection->prepare($sql);
            if (!$stmt) {
                error_log("Prepare error: " . $this->connection->error);
                return false;
            }
            
            // Check if binding parameters works
            if (!$stmt->bind_param($types, ...$values)) {
                error_log("Bind param error: " . $stmt->error);
                return false;
            }
            
            // Execute and check result
            $result = $stmt->execute();
            error_log("Execute result: " . ($result ? "true" : "false"));
            
            if (!$result) {
                error_log("Execute error: " . $stmt->error);
                return false;
            }
            
            error_log("Trade #$id updated successfully. Affected rows: " . $stmt->affected_rows);
            return true;
        } catch (Exception $e) {
            error_log("Error in updateTradeAdmin: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update multiple settings at once
     * 
     * @param array $settings Key-value pairs of settings
     * @return bool Success status
     */
    public function updateSettings($settings) {
        try {
            $this->connection->begin_transaction();
            $success = true;
            
            foreach ($settings as $key => $value) {
                if (!$this->updateSetting($key, $value)) {
                    $success = false;
                    break;
                }
            }
            
            if ($success) {
                $this->connection->commit();
                return true;
            } else {
                $this->connection->rollback();
                return false;
            }
        } catch (Exception $e) {
            $this->connection->rollback();
            error_log("Error updating multiple settings: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the MySQL server version
     * 
     * @return string Server version
     */
    public function getServerVersion() {
        try {
            return $this->connection->server_info ?? 'Unknown';
        } catch (Exception $e) {
            error_log("Error getting server version: " . $e->getMessage());
            return 'Unknown';
        }
    }

    public function createWithdrawal($data) {
        try {
            // Get current user data
            $user = $this->getUserById($data['user_id']);
            
            // Calculate withdrawal fee
            $withdrawal_fee_percentage = (float)$this->getSetting('withdrawal_fee', 5);
            $fee_amount = $data['amount'] * ($withdrawal_fee_percentage / 100);
            $net_amount = $data['amount'] - $fee_amount;
            
            // Validate balance
            if ($user['balance'] < $data['amount']) {
                return ['success' => false, 'message' => 'Insufficient balance'];
            }

            // Get withdrawal limits
            $min_withdrawal = (float)$this->getSetting('min_withdrawal', 100);
            $max_withdrawal = (float)$this->getSetting('max_withdrawal', 10000);
            
            // Validate against limits
            if ($data['amount'] < $min_withdrawal) {
                return ['success' => false, 'message' => "Minimum withdrawal amount is $" . number_format($min_withdrawal, 2)];
            }
            
            if ($data['amount'] > $max_withdrawal) {
                return ['success' => false, 'message' => "Maximum withdrawal amount is $" . number_format($max_withdrawal, 2)];
            }

            // Start transaction
            $this->connection->begin_transaction();
            
            try {
                // 1. Insert into withdrawals with fee information
                $query = "INSERT INTO withdrawals (
                    user_id, amount, fee_percentage, fee_amount, net_amount, 
                    payment_method, wallet_address, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
                
                $stmt = $this->connection->prepare($query);
                $stmt->bind_param(
                    "idddss", 
                    $data['user_id'],
                    $data['amount'],
                    $withdrawal_fee_percentage,
                    $fee_amount,
                    $net_amount,
                    $data['payment_method'],
                    $data['wallet_address']
                );
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to create withdrawal: " . $stmt->error);
                }
                
                $withdrawal_id = $this->connection->insert_id;

                // 2. Update user's balance
                $updateBalanceQuery = "UPDATE users SET balance = balance - ? WHERE id = ?";
                
                $stmt = $this->connection->prepare($updateBalanceQuery);
                $stmt->bind_param("di", $data['amount'], $data['user_id']);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update balance: " . $stmt->error);
                }
                
                // If everything is successful, commit the transaction
                $this->connection->commit();
                
                // Get the new balance
                $updatedUser = $this->getUserById($data['user_id']);
                
                return [
                    'success' => true,
                    'message' => 'Withdrawal request created successfully',
                    'withdrawal_id' => $withdrawal_id,
                    'new_balance' => $updatedUser['balance'],
                    'fee_amount' => $fee_amount,
                    'net_amount' => $net_amount
                ];
                
            } catch (Exception $e) {
                // If there's an error, rollback the transaction
                $this->connection->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("Withdrawal error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getUserWithdrawals($userId) {
        try {
            $query = "SELECT * FROM withdrawals WHERE user_id = ? ORDER BY created_at DESC";
            $stmt = $this->prepare($query);
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $withdrawals = [];
            while ($row = $result->fetch_assoc()) {
                $withdrawals[] = $row;
            }
            
            return $withdrawals;
        } catch (Exception $e) {
            error_log("Database error in getUserWithdrawals: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a specific setting value
     * 
     * @param string $key Setting key
     * @param mixed $default Default value if setting not found
     * @return mixed Setting value
     */
    public function getSetting($key, $default = null) {
        try {
            $query = "SELECT setting_value FROM settings WHERE setting_key = ?";
            $stmt = $this->connection->prepare($query);
            
            if (!$stmt) {
                error_log("Failed to prepare getSetting query: " . $this->connection->error);
                return $default;
            }
            
            $stmt->bind_param("s", $key);
            
            if (!$stmt->execute()) {
                error_log("Failed to execute getSetting query: " . $stmt->error);
                return $default;
            }
            
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row ? $row['setting_value'] : $default;
            
        } catch (Exception $e) {
            error_log("Error in getSetting: " . $e->getMessage());
            return $default;
        }
    }

    /**
     * Get all settings, optionally filtered by category
     * 
     * @param string $category Optional category filter
     * @return array Settings
     */
    public function getAllSettings($category = null) {
        try {
            $query = "SELECT id, setting_key, setting_value, setting_type, is_public, category, description 
                      FROM settings";
            $params = [];
            
            if ($category) {
                $query .= " WHERE category = ?";
                $params[] = $category;
            }
            
            $query .= " ORDER BY category, setting_key";
            
            $stmt = $this->connection->prepare($query);
            
            if (!$stmt) {
                error_log("Prepare failed in getAllSettings: " . $this->connection->error);
                return [];
            }
            
            if ($category) {
                $stmt->bind_param('s', $category);
            }
            
            if (!$stmt->execute()) {
                error_log("Execute failed in getAllSettings: " . $stmt->error);
                return [];
            }
            
            $result = $stmt->get_result();
            $settings = [];
            
            while ($row = $result->fetch_assoc()) {
                // Convert value based on type
                switch ($row['setting_type']) {
                    case 'boolean':
                        $row['setting_value'] = (bool)$row['setting_value'];
                        break;
                    case 'number':
                        $row['setting_value'] = floatval($row['setting_value']);
                        break;
                    case 'json':
                        $row['setting_value'] = json_decode($row['setting_value'], true);
                        break;
                }
                
                $settings[] = $row;
            }
            
            return $settings;
            
        } catch (Exception $e) {
            error_log("Error in getAllSettings: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update a setting value
     * 
     * @param string $key Setting key
     * @param mixed $value New value
     * @return bool Success
     */
    public function updateSetting($key, $value) {
        try {
            $query = "UPDATE settings 
                      SET setting_value = ?, 
                          updated_at = CURRENT_TIMESTAMP 
                      WHERE setting_key = ?";
                      
            $stmt = $this->connection->prepare($query);
            
            if (!$stmt) {
                error_log("Prepare failed in updateSetting: " . $this->connection->error);
                return false;
            }
            
            $stmt->bind_param('ss', $value, $key);
            
            if (!$stmt->execute()) {
                error_log("Execute failed in updateSetting: " . $stmt->error);
                return false;
            }
            
            return $stmt->affected_rows > 0;
            
        } catch (Exception $e) {
            error_log("Error in updateSetting: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a new setting
     * 
     * @param array $data Setting data
     * @return bool Success
     */
    public function createSetting($data) {
        try {
            $query = "INSERT INTO settings (setting_key, setting_value, setting_type, is_public, category, description) 
                      VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->connection->prepare($query);
            
            if (!$stmt) {
                error_log("Prepare failed in createSetting: " . $this->connection->error);
                return false;
            }
            
            $isPublic = isset($data['is_public']) ? (int)$data['is_public'] : 1;
            
            $stmt->bind_param(
                'sssiss',
                $data['setting_key'],
                $data['setting_value'],
                $data['setting_type'],
                $isPublic,
                $data['category'],
                $data['description']
            );
            
            if (!$stmt->execute()) {
                error_log("Execute failed in createSetting: " . $stmt->error);
                return false;
            }
            
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Error in createSetting: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a setting
     * 
     * @param string $key Setting key
     * @return bool Success
     */
    public function deleteSetting($key) {
        try {
            $query = "DELETE FROM settings WHERE setting_key = ?";
            $stmt = $this->connection->prepare($query);
            
            if (!$stmt) {
                error_log("Prepare failed in deleteSetting: " . $this->connection->error);
                return false;
            }
            
            $stmt->bind_param('s', $key);
            
            if (!$stmt->execute()) {
                error_log("Execute failed in deleteSetting: " . $stmt->error);
                return false;
            }
            
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("Error in deleteSetting: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get distinct setting categories
     * 
     * @return array Categories
     */
    public function getSettingCategories() {
        try {
            $query = "SELECT DISTINCT category FROM settings ORDER BY category";
            $stmt = $this->connection->prepare($query);
            
            if (!$stmt) {
                error_log("Prepare failed in getSettingCategories: " . $this->connection->error);
                return ['general'];
            }
            
            if (!$stmt->execute()) {
                error_log("Execute failed in getSettingCategories: " . $stmt->error);
                return ['general'];
            }
            
            $result = $stmt->get_result();
            $categories = [];
            
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row['category'];
            }
            
            return $categories;
        } catch (Exception $e) {
            error_log("Error in getSettingCategories: " . $e->getMessage());
            return ['general'];
        }
    }

    public function validateTradeAmount($amount) {
        $min_trade = floatval($this->getSetting('min_trade', 10));
        $max_trade = floatval($this->getSetting('max_trade', 1000));
        
        if ($amount < $min_trade) {
            return ['success' => false, 'message' => "Minimum trade amount is $" . number_format($min_trade, 2)];
        }
        
        if ($amount > $max_trade) {
            return ['success' => false, 'message' => "Maximum trade amount is $" . number_format($max_trade, 2)];
        }
        
        return ['success' => true];
    }

    public function validateWithdrawal($amount, $user_id) {
        $min_withdrawal = floatval($this->getSetting('min_withdrawal', 100));
        $max_withdrawal = floatval($this->getSetting('max_withdrawal', 5000));
        
        // Get user's balance
        $user = $this->getUserById($user_id);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }
        
        if ($amount < $min_withdrawal) {
            return ['success' => false, 'message' => "Minimum withdrawal amount is $" . number_format($min_withdrawal, 2)];
        }
        
        if ($amount > $max_withdrawal) {
            return ['success' => false, 'message' => "Maximum withdrawal amount is $" . number_format($max_withdrawal, 2)];
        }
        
        if ($amount > $user['balance']) {
            return ['success' => false, 'message' => 'Insufficient balance'];
        }
        
        return ['success' => true];
    }

    /**
 /**
 * Get admin wallet addresses for all cryptocurrencies
 * @return array Array of admin wallet addresses by crypto type
 */
public function getAdminWalletAddresses() {
    try {
        $query = "SELECT * FROM wallet_addresses LIMIT 1";
        $stmt = $this->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    } catch (Exception $e) {
        error_log("Error fetching admin wallet addresses: " . $e->getMessage());
        return [];
    }
}

public function updateAdminWalletAddress($addresses) {
    try {
        $query = "UPDATE wallet_addresses SET 
        btc_address = ?,
        eth_address = ?,
        ltc_address = ?,
        usdt_address = ?,
        bnb_address = ?,
        xrp_address = ?,
        doge_address = ?";
      
        $stmt = $this->connection->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->connection->error);
        }

        $stmt->bind_param("sssssss", 
            $addresses['btc_address'],
            $addresses['eth_address'],
            $addresses['ltc_address'],
            $addresses['usdt_address'],
            $addresses['bnb_address'],
            $addresses['xrp_address'],
            $addresses['doge_address']
        );

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        return true;
    } catch (Exception $e) {
        error_log("Error updating admin wallet addresses: " . $e->getMessage());
        return false;
    }
}

public function getUserByReferralCode($code) {
    try {
        $stmt = $this->prepare("SELECT * FROM users WHERE referral_code = ?");
        $stmt->bind_param('s', $code);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    } catch (Exception $e) {
        error_log("Error getting user by referral code: " . $e->getMessage());
        return null;
    }
}

public function storeVerificationToken($user_id, $token) {
    try {
        $query = "UPDATE users SET verification_token = ?, verification_sent_at = NOW() WHERE id = ?";
        $stmt = $this->prepare($query);
        $stmt->bind_param('si', $token, $user_id);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Error storing verification token: " . $e->getMessage());
        return false;
    }
}

/**
 * Get admin logs with filtering
 * 
 * @param int|null $admin_id Admin ID filter
 * @param string $action Action filter
 * @param string $search Search term
 * @param int $page Page number
 * @param int $per_page Items per page
 * @return array Admin logs
 */
public function getAdminLogs($admin_id = null, $action = '', $search = '', $page = 1, $per_page = 50) {
    try {
        $offset = ($page - 1) * $per_page;
        $where_conditions = [];
        $params = [];
        $types = '';
        
        if ($admin_id) {
            $where_conditions[] = "al.admin_id = ?";
            $params[] = $admin_id;
            $types .= 'i';
        }
        
        if ($action) {
            $where_conditions[] = "al.action = ?";
            $params[] = $action;
            $types .= 's';
        }
        
        if ($search) {
            $where_conditions[] = "(al.details LIKE ? OR al.action LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $types .= 'ss';
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        $query = "SELECT al.*, u.name as admin_name, u.email as admin_email 
                  FROM admin_logs al 
                  LEFT JOIN users u ON al.admin_id = u.id 
                  $where_clause 
                  ORDER BY al.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $per_page;
        $params[] = $offset;
        $types .= 'ii';
        
        $stmt = $this->connection->prepare($query);
        if (!$stmt) {
            error_log("Prepare failed in getAdminLogs: " . $this->connection->error);
            return [];
        }
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            error_log("Execute failed in getAdminLogs: " . $stmt->error);
            return [];
        }
        
        $result = $stmt->get_result();
        $logs = [];
        
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
        
        return $logs;
    } catch (Exception $e) {
        error_log("Error in getAdminLogs: " . $e->getMessage());
        return [];
    }
}

/**
 * Get total count of admin logs
 * 
 * @param int|null $admin_id Admin ID filter
 * @param string $action Action filter
 * @param string $search Search term
 * @return int Total count
 */
public function getAdminLogsCount($admin_id = null, $action = '', $search = '') {
    try {
        $where_conditions = [];
        $params = [];
        $types = '';
        
        if ($admin_id) {
            $where_conditions[] = "al.admin_id = ?";
            $params[] = $admin_id;
            $types .= 'i';
        }
        
        if ($action) {
            $where_conditions[] = "al.action = ?";
            $params[] = $action;
            $types .= 's';
        }
        
        if ($search) {
            $where_conditions[] = "(al.details LIKE ? OR al.action LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $types .= 'ss';
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        $query = "SELECT COUNT(*) as total FROM admin_logs $where_clause";
        
        $stmt = $this->connection->prepare($query);
        if (!$stmt) {
            error_log("Prepare failed in getAdminLogsCount: " . $this->connection->error);
            return 0;
        }
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            error_log("Execute failed in getAdminLogsCount: " . $stmt->error);
            return 0;
        }
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['total'] ?? 0;
    } catch (Exception $e) {
        error_log("Error in getAdminLogsCount: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get unique admin log actions
 * 
 * @return array Unique actions
 */
public function getAdminLogActions() {
    try {
        $query = "SELECT DISTINCT action FROM admin_logs ORDER BY action";
        $stmt = $this->connection->prepare($query);
        
        if (!$stmt) {
            error_log("Prepare failed in getAdminLogActions: " . $this->connection->error);
            return [];
        }
        
        if (!$stmt->execute()) {
            error_log("Execute failed in getAdminLogActions: " . $stmt->error);
            return [];
        }
        
        $result = $stmt->get_result();
        $actions = [];
        
        while ($row = $result->fetch_assoc()) {
            $actions[] = $row['action'];
        }
        
        return $actions;
    } catch (Exception $e) {
        error_log("Error in getAdminLogActions: " . $e->getMessage());
        return [];
    }
}

/**
 * Log admin activity
 * 
 * @param int $admin_id Admin ID
 * @param string $action Action performed
 * @param string $details Action details
 * @return bool Success
 */
public function logAdminActivity($admin_id, $action, $details = '') {
    try {
        $query = "INSERT INTO admin_logs (admin_id, action, details, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->connection->prepare($query);
        
        if (!$stmt) {
            error_log("Prepare failed in logAdminActivity: " . $this->connection->error);
            return false;
        }
        
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $stmt->bind_param('isss', $admin_id, $action, $details, $ip_address);
        
        if (!$stmt->execute()) {
            error_log("Execute failed in logAdminActivity: " . $stmt->error);
            return false;
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Error in logAdminActivity: " . $e->getMessage());
        return false;
    }
}

    // Stock management methods
    public function getStocks($search = '', $exchange = '', $page = 1, $per_page = 20) {
        try {
            $conditions = [];
            $params = [];
            $types = "";
            
            if ($search) {
                $conditions[] = "(name LIKE ? OR symbol LIKE ?)";
                $search = "%$search%";
                $params[] = $search;
                $params[] = $search;
                $types .= "ss";
            }
            
            if ($exchange) {
                $conditions[] = "exchange = ?";
                $params[] = $exchange;
                $types .= "s";
            }
            
            $where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";
            
            $offset = ($page - 1) * $per_page;
            $params[] = $offset;
            $params[] = $per_page;
            $types .= "ii";
            
            $sql = "SELECT * FROM stocks $where ORDER BY created_at DESC LIMIT ?, ?";
            
            $stmt = $this->connection->prepare($sql);
            if ($params) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getStocks: " . $e->getMessage());
            return [];
        }
    }
    
    public function getTotalStocksCount($search = '', $exchange = '') {
        try {
            $conditions = [];
            $params = [];
            $types = "";
            
            if ($search) {
                $conditions[] = "(name LIKE ? OR symbol LIKE ?)";
                $search = "%$search%";
                $params[] = $search;
                $params[] = $search;
                $types .= "ss";
            }
            
            if ($exchange) {
                $conditions[] = "exchange = ?";
                $params[] = $exchange;
                $types .= "s";
            }
            
            $where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";
            
            $sql = "SELECT COUNT(*) as total FROM stocks $where";
            
            $stmt = $this->connection->prepare($sql);
            if ($params) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            
            $result = $stmt->get_result()->fetch_assoc();
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Error in getTotalStocksCount: " . $e->getMessage());
            return 0;
        }
    }
    
    public function addStock($data) {
        try {
            $sql = "INSERT INTO stocks (name, symbol, exchange, min_amount, max_amount, commission, leverage, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->connection->prepare($sql);
            
            $stmt->bind_param("sssddddi", 
                $data['name'],
                $data['symbol'],
                $data['exchange'],
                $data['min_amount'],
                $data['max_amount'],
                $data['commission'],
                $data['leverage'],
                $data['featured']
            );
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error in addStock: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateStock($id, $data) {
        try {
            $sql = "UPDATE stocks SET name = ?, symbol = ?, exchange = ?, min_amount = ?, max_amount = ?, commission = ?, leverage = ?, featured = ? WHERE id = ?";
            $stmt = $this->connection->prepare($sql);
            
            $stmt->bind_param("sssddddii", 
                $data['name'],
                $data['symbol'],
                $data['exchange'],
                $data['min_amount'],
                $data['max_amount'],
                $data['commission'],
                $data['leverage'],
                $data['featured'],
                $id
            );
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error in updateStock: " . $e->getMessage());
            return false;
        }
    }
    
    public function deleteStock($id) {
        try {
            $sql = "DELETE FROM stocks WHERE id = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("i", $id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error in deleteStock: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAllStocks() {
        try {
            $sql = "SELECT * FROM stocks ORDER BY symbol";
            $result = $this->connection->query($sql);
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getAllStocks: " . $e->getMessage());
            return [];
        }
    }
    
    public function getStockById($id) {
        try {
            $sql = "SELECT * FROM stocks WHERE id = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error in getStockById: " . $e->getMessage());
            return null;
        }
    }
    
    // Stock investment methods
    public function getStockInvestments($search = '', $status = '', $stock_id = 0, $page = 1, $per_page = 20) {
        try {
            $conditions = [];
            $params = [];
            $types = "";
            
            if ($search) {
                $conditions[] = "(u.name LIKE ? OR u.email LIKE ?)";
                $search = "%$search%";
                $params[] = $search;
                $params[] = $search;
                $types .= "ss";
            }
            
            if ($status) {
                $conditions[] = "si.status = ?";
                $params[] = $status;
                $types .= "s";
            }
            
            if ($stock_id) {
                $conditions[] = "si.stock_id = ?";
                $params[] = $stock_id;
                $types .= "i";
            }
            
            $where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";
            
            $offset = ($page - 1) * $per_page;
            $params[] = $offset;
            $params[] = $per_page;
            $types .= "ii";
            
            $sql = "SELECT si.*, u.name as user_name, u.email as user_email, s.name as stock_name, s.symbol as stock_symbol 
                    FROM stock_investments si 
                    LEFT JOIN users u ON si.user_id = u.id 
                    LEFT JOIN stocks s ON si.stock_id = s.id 
                    $where 
                    ORDER BY si.created_at DESC 
                    LIMIT ?, ?";
            
            $stmt = $this->connection->prepare($sql);
            if ($params) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getStockInvestments: " . $e->getMessage());
            return [];
        }
    }
    
    public function getTotalStockInvestmentsCount($search = '', $status = '', $stock_id = 0) {
        try {
            $conditions = [];
            $params = [];
            $types = "";
            
            if ($search) {
                $conditions[] = "(u.name LIKE ? OR u.email LIKE ?)";
                $search = "%$search%";
                $params[] = $search;
                $params[] = $search;
                $types .= "ss";
            }
            
            if ($status) {
                $conditions[] = "si.status = ?";
                $params[] = $status;
                $types .= "s";
            }
            
            if ($stock_id) {
                $conditions[] = "si.stock_id = ?";
                $params[] = $stock_id;
                $types .= "i";
            }
            
            $where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";
            
            $sql = "SELECT COUNT(*) as total 
                    FROM stock_investments si 
                    LEFT JOIN users u ON si.user_id = u.id 
                    LEFT JOIN stocks s ON si.stock_id = s.id 
                    $where";
            
            $stmt = $this->connection->prepare($sql);
            if ($params) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            
            $result = $stmt->get_result()->fetch_assoc();
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Error in getTotalStockInvestmentsCount: " . $e->getMessage());
            return 0;
        }
    }
    
    public function updateStockInvestmentStatus($id, $status) {
        try {
            $sql = "UPDATE stock_investments SET status = ? WHERE id = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("si", $status, $id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error in updateStockInvestmentStatus: " . $e->getMessage());
            return false;
        }
    }
    
    // Trading history methods
    public function getTradingHistory($search = '', $status = '', $user_id = 0, $page = 1, $per_page = 20) {
        try {
            $conditions = [];
            $params = [];
            $types = "";
            
            if ($search) {
                $conditions[] = "(u.name LIKE ? OR u.email LIKE ? OR th.asset LIKE ?)";
                $search = "%$search%";
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
                $types .= "sss";
            }
            
            if ($status) {
                $conditions[] = "th.status = ?";
                $params[] = $status;
                $types .= "s";
            }
            
            if ($user_id) {
                $conditions[] = "th.user_id = ?";
                $params[] = $user_id;
                $types .= "i";
            }
            
            $where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";
            
            $offset = ($page - 1) * $per_page;
            $params[] = $offset;
            $params[] = $per_page;
            $types .= "ii";
            
            $sql = "SELECT th.*, u.name as user_name, u.email as user_email 
                    FROM trading_history th 
                    LEFT JOIN users u ON th.user_id = u.id 
                    $where 
                    ORDER BY th.created_at DESC 
                    LIMIT ?, ?";
            
            $stmt = $this->connection->prepare($sql);
            if ($params) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getTradingHistory: " . $e->getMessage());
            return [];
        }
    }
    
    public function getTotalTradingHistoryCount($search = '', $status = '', $user_id = 0) {
        try {
            $conditions = [];
            $params = [];
            $types = "";
            
            if ($search) {
                $conditions[] = "(u.name LIKE ? OR u.email LIKE ? OR th.asset LIKE ?)";
                $search = "%$search%";
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
                $types .= "sss";
            }
            
            if ($status) {
                $conditions[] = "th.status = ?";
                $params[] = $status;
                $types .= "s";
            }
            
            if ($user_id) {
                $conditions[] = "th.user_id = ?";
                $params[] = $user_id;
                $types .= "i";
            }
            
            $where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";
            
            $sql = "SELECT COUNT(*) as total 
                    FROM trading_history th 
                    LEFT JOIN users u ON th.user_id = u.id 
                    $where";
            
            $stmt = $this->connection->prepare($sql);
            if ($params) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            
            $result = $stmt->get_result()->fetch_assoc();
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Error in getTotalTradingHistoryCount: " . $e->getMessage());
            return 0;
        }
    }
    
    public function updateTradeStatus($id, $status) {
        try {
            $sql = "UPDATE trading_history SET status = ? WHERE id = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("si", $status, $id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error in updateTradeStatus: " . $e->getMessage());
        return false;
    }
}

    // Fetch staking investments for admin
    public function getStakingInvestments($search = '', $status = '', $user_id = 0, $page = 1, $per_page = 100) {
        $params = [];
        $where = [];
        if ($search !== '') {
            $where[] = "(id LIKE ? OR amount LIKE ? OR status LIKE ?)";
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        if ($status !== '' && $status !== 'all') {
            $where[] = "status = ?";
            $params[] = $status;
        }
        if ($user_id > 0) {
            $where[] = "user_id = ?";
            $params[] = $user_id;
        }
        $sql = "SELECT * FROM staking_investments";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " ORDER BY id DESC LIMIT ? OFFSET ?";
        $params[] = $per_page;
        $params[] = ($page - 1) * $per_page;
        $stmt = $this->connection->prepare($sql);
        $types = str_repeat('s', count($params) - 2) . 'ii';
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $investments = [];
        while ($row = $result->fetch_assoc()) {
            $investments[] = $row;
        }
        $stmt->close();
        return $investments;
    }
}