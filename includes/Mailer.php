<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/phpmailer/phpmailer/src/Exception.php';
require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';

class Mailer {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        
        // Server settings
        $this->mail->isSMTP();
        $this->mail->Host = SMTP_HOST;
        $this->mail->SMTPAuth = true;
        $this->mail->Username = SMTP_USERNAME;
        $this->mail->Password = SMTP_PASSWORD;
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = SMTP_PORT;
        
        // Default settings
        $this->mail->isHTML(true);
        $this->mail->setFrom(APP_EMAIL, APP_NAME);
    }

    public function sendPasswordReset($email, $name, $token) {
        try {
            $resetLink = "https://" . $_SERVER['HTTP_HOST'] . "/auth/reset-password.php?token=" . $token;
            
            $this->mail->addAddress($email, $name);
            $this->mail->Subject = "Password Reset Request - " . APP_NAME;
            
            // HTML Email Body
            $this->mail->Body = "
            <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <div style='text-align: center; margin-bottom: 20px;'>
                        <img src='" . APP_FAVICON . "' style='height: 60px;' alt='" . APP_NAME . " Logo'>
                    </div>
                    
                    <h2 style='color: #2c3e50; margin-bottom: 20px;'>Password Reset Request</h2>
                    
                    <p>Hello " . htmlspecialchars($name) . ",</p>
                    
                    <p>You have requested to reset your password. Click the button below to reset it:</p>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='" . $resetLink . "' 
                           style='background-color: #3498db; 
                                  color: white; 
                                  padding: 12px 25px; 
                                  text-decoration: none; 
                                  border-radius: 5px; 
                                  display: inline-block;'>
                            Reset Password
                        </a>
                    </div>
                    
                    <p>This link will expire in 1 hour.</p>
                    
                    <p>If you did not request this reset, please ignore this email.</p>
                    
                    <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;'>
                        <p style='color: #666; font-size: 14px;'>
                            Best regards,<br>
                            " . APP_NAME . "
                        </p>
                    </div>
                </div>
            </body>
            </html>";
            
            // Plain text version
            $this->mail->AltBody = "
            Hello " . $name . ",

            You have requested to reset your password. Click the link below to reset it:

            " . $resetLink . "

            This link will expire in 1 hour.

            If you did not request this reset, please ignore this email.

            Best regards,
            " . APP_NAME;
            
            return $this->mail->send();
            
        } catch (Exception $e) {
            error_log("Failed to send password reset email: " . $e->getMessage());
            return false;
        }
    }

    public function sendCustomMail($email, $subject, $message) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($email);
            $this->mail->Subject = $subject;
            $this->mail->Body = $message;
            $this->mail->AltBody = strip_tags($message);
            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Failed to send custom email: " . $e->getMessage());
            return false;
        }
    }
}