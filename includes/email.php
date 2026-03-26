<?php
/**
 * Email Configuration and Sending Functions
 * Supports Gmail App Password and Zoho Mail
 * Uses SMTP authentication for reliable delivery
 * 
 * NOTE: Settings are now managed via Admin Panel > Settings
 * This file uses settings from data/settings.json
 */

require_once __DIR__ . '/config.php';

// Get settings from database
$settings = loadSettings();

// Define constants from settings (with fallbacks)
define('EMAIL_ENABLED', $settings['email_enabled'] ?? false);
define('SMTP_HOST', $settings['smtp_host'] ?? 'smtp.gmail.com');
define('SMTP_PORT', $settings['smtp_port'] ?? 587);
define('SMTP_USERNAME', $settings['smtp_username'] ?? '');
define('SMTP_PASSWORD', $settings['smtp_password'] ?? '');
define('SMTP_ENCRYPTION', $settings['smtp_encryption'] ?? 'tls');
define('FROM_EMAIL', $settings['from_email'] ?? ($settings['smtp_username'] ?? ''));
define('FROM_NAME', $settings['from_name'] ?? 'Family Tree');
// define('SMTP_ENCRYPTION', 'tls');

/**
 * Send email using SMTP with authentication
 * This works with Gmail App Password without needing PHPMailer
 */
function sendEmail($to, $subject, $body, $altBody = '') {
    if (!EMAIL_ENABLED) {
        // Email disabled - log for debugging
        $logFile = DATA_DIR . 'email_log.txt';
        $logEntry = date('Y-m-d H:i:s') . " - EMAIL DISABLED (would send to: {$to})\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND);
        
        return [
            'success' => false,
            'message' => 'Email sending is disabled. Check data/email_log.txt'
        ];
    }
    
    // Try PHPMailer if available (best option)
    if (file_exists(__DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php')) {
        return sendEmailWithPHPMailer($to, $subject, $body, $altBody);
    }
    
    // Otherwise use native PHP SMTP
    return sendEmailWithNativeSMTP($to, $subject, $body, $altBody);
}

/**
 * Send email using PHPMailer (if installed via Composer)
 */
function sendEmailWithPHPMailer($to, $subject, $body, $altBody) {
    try {
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
        
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;  // Gmail App Password works here!
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $altBody ?: strip_tags($body);
        
        $mail->send();
        
        logActivity('email_sent', "Email sent to: {$to}");
        return ['success' => true, 'message' => 'Email sent successfully'];
        
    } catch (\Exception $e) {
        logActivity('email_failed', "Email error: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Send email using native PHP socket SMTP (no external libraries needed)
 * Works with Gmail App Password!
 */
function sendEmailWithNativeSMTP($to, $subject, $body, $altBody) {
    $smtpHost = SMTP_HOST;
    $smtpPort = SMTP_PORT;
    $username = SMTP_USERNAME;
    $password = SMTP_PASSWORD;
    
    // Create connection
    $errno = 0;
    $errstr = '';
    $timeout = 30;
    
    $socket = @fsockopen($smtpHost, $smtpPort, $errno, $errstr, $timeout);
    
    if (!$socket) {
        return ['success' => false, 'message' => "Cannot connect to SMTP: {$errstr}"];
    }
    
    // Read greeting
    fgets($socket, 515);
    
    // EHLO
    fputs($socket, "EHLO " . $_SERVER['HTTP_HOST'] . "\r\n");
    while ($line = fgets($socket, 515)) {
        if (substr($line, 3, 1) == ' ') break;
    }
    
    // STARTTLS for port 587
    if ($smtpPort == 587) {
        fputs($socket, "STARTTLS\r\n");
        fgets($socket, 515);
        
        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($socket);
            return ['success' => false, 'message' => 'TLS negotiation failed'];
        }
        
        // EHLO again after TLS
        fputs($socket, "EHLO " . $_SERVER['HTTP_HOST'] . "\r\n");
        while ($line = fgets($socket, 515)) {
            if (substr($line, 3, 1) == ' ') break;
        }
    }
    
    // AUTH LOGIN
    fputs($socket, "AUTH LOGIN\r\n");
    fgets($socket, 515);
    
    // Send username (base64 encoded)
    fputs($socket, base64_encode($username) . "\r\n");
    fgets($socket, 515);
    
    // Send password (base64 encoded) - Gmail App Password works here!
    fputs($socket, base64_encode($password) . "\r\n");
    $response = fgets($socket, 515);
    
    if (substr($response, 0, 3) != '235') {
        fclose($socket);
        return ['success' => false, 'message' => 'Authentication failed. Check App Password.'];
    }
    
    // MAIL FROM
    fputs($socket, "MAIL FROM:<{$username}>\r\n");
    fgets($socket, 515);
    
    // RCPT TO
    fputs($socket, "RCPT TO:<{$to}>\r\n");
    fgets($socket, 515);
    
    // DATA
    fputs($socket, "DATA\r\n");
    fgets($socket, 515);
    
    // Send email content
    $boundary = md5(time());
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
    $headers .= "From: " . FROM_NAME . " <{$username}>\r\n";
    $headers .= "To: {$to}\r\n";
    $headers .= "Subject: {$subject}\r\n";
    
    $message = $headers . "\r\n";
    $message .= "--{$boundary}\r\n";
    $message .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
    $message .= ($altBody ?: strip_tags($body)) . "\r\n\r\n";
    $message .= "--{$boundary}\r\n";
    $message .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
    $message .= $body . "\r\n\r\n";
    $message .= "--{$boundary}--\r\n";
    $message .= ".\r\n";
    
    fputs($socket, $message);
    $response = fgets($socket, 515);
    
    // QUIT
    fputs($socket, "QUIT\r\n");
    fclose($socket);
    
    if (substr($response, 0, 3) == '250') {
        logActivity('email_sent', "Email sent to: {$to}");
        return ['success' => true, 'message' => 'Email sent successfully'];
    } else {
        logActivity('email_failed', "Email failed: {$response}");
        return ['success' => false, 'message' => 'Failed to send email'];
    }
}

/**
 * Send email using basic mail() function
 * Note: This has limited functionality and may go to spam
 */
function sendEmailWithMail($to, $subject, $body, $altBody) {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . FROM_NAME . " <" . FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . FROM_EMAIL . "\r\n";
    
    $message = $body;
    
    $result = mail($to, $subject, $message, $headers);
    
    if ($result) {
        logActivity('email_sent', "Email sent to: {$to}, Subject: {$subject}");
        return ['success' => true, 'message' => 'Email sent successfully'];
    } else {
        logActivity('email_failed', "Failed to send email to: {$to}");
        return ['success' => false, 'message' => 'Failed to send email'];
    }
}

/**
 * Send password reset email
 */
function sendPasswordResetEmail($to, $resetLink) {
    $subject = 'Password Reset - Family Tree';
    
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #00b2ff; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
            .button { display: inline-block; background: #00b2ff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Family Tree - Password Reset</h2>
            </div>
            <div class='content'>
                <p>Hello,</p>
                <p>You requested a password reset for your Family Tree account.</p>
                <p>Click the button below to reset your password:</p>
                <center>
                    <a href='{$resetLink}' class='button'>Reset Password</a>
                </center>
                <p>Or copy and paste this link in your browser:</p>
                <p style='word-break: break-all; color: #666;'>{$resetLink}</p>
                <p><strong>This link will expire in 1 hour.</strong></p>
                <p>If you didn't request this, please ignore this email.</p>
            </div>
            <div class='footer'>
                <p>Best regards,<br>Family Tree Team</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $altBody = "Hello,\n\n";
    $altBody .= "You requested a password reset for your Family Tree account.\n\n";
    $altBody .= "Click the link below to reset your password:\n";
    $altBody .= $resetLink . "\n\n";
    $altBody .= "This link will expire in 1 hour.\n\n";
    $altBody .= "If you didn't request this, please ignore this email.\n\n";
    $altBody .= "Best regards,\nFamily Tree Team";
    
    return sendEmail($to, $subject, $body, $altBody);
}

/**
 * Send welcome email to new users
 */
function sendWelcomeEmail($to, $name, $password) {
    $subject = 'Welcome to Family Tree';
    
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #00b2ff; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
            .credentials { background: white; padding: 15px; border-left: 4px solid #00b2ff; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Welcome to Family Tree!</h2>
            </div>
            <div class='content'>
                <p>Hello {$name},</p>
                <p>Your account has been created. Here are your login credentials:</p>
                <div class='credentials'>
                    <p><strong>Email:</strong> {$to}</p>
                    <p><strong>Password:</strong> {$password}</p>
                </div>
                <p>Please change your password after your first login for security.</p>
                <p>You can access the family tree at: <a href='https://{$_SERVER['HTTP_HOST']}'>{$_SERVER['HTTP_HOST']}</a></p>
            </div>
            <div class='footer'>
                <p>Best regards,<br>Family Tree Team</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $altBody = "Hello {$name},\n\n";
    $altBody .= "Your account has been created. Here are your login credentials:\n\n";
    $altBody .= "Email: {$to}\n";
    $altBody .= "Password: {$password}\n\n";
    $altBody .= "Please change your password after your first login for security.\n\n";
    $altBody .= "Best regards,\nFamily Tree Team";
    
    return sendEmail($to, $subject, $body, $altBody);
}
