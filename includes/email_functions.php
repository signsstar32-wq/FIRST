<?php
/**
 * Send verification email to new user
 * 
 * @param int $user_id User ID
 * @param string $email User's email address
 * @return bool Whether email was sent successfully
 */
function sendVerificationEmail($user_id, $email) {
    global $db;
    
    try {
        // Generate verification token
        $token = bin2hex(random_bytes(32));
        
        // Store token in database
        $db->storeVerificationToken($user_id, $token);
        
        // Create verification link
        $verify_link = APP_URL . "auth/verify.php?token=" . $token;
        
        // Email content
        $subject = APP_NAME . " - Verify Your Email";
        $message = "
            <h2>Welcome to " . APP_NAME . "!</h2>
            <p>Please click the link below to verify your email address:</p>
            <p><a href='$verify_link'>Verify Email Address</a></p>
            <p>If you did not create an account, no further action is required.</p>
            <p>Regards,<br>" . APP_NAME . " Team</p>
        ";
        
        // Send email using Mailer class
        require_once __DIR__ . '/Mailer.php';
        $mailer = new Mailer();
        return $mailer->sendCustomMail($email, $subject, $message);
        
    } catch (Exception $e) {
        error_log("Error sending verification email: " . $e->getMessage());
        return false;
    }
} 