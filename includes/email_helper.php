<?php
/**
 * ============================================================================
 *  Project     : College ERP System
 *  Author      : Vivek Kumar
 *  LinkedIn    : https://www.linkedin.com/in/vivek-info
 *  Instagram   : https://www.instagram.com/its.vivek.raj/
 * ============================================================================
 *  Copyright (c) 2026 Vivek Kumar. All Rights Reserved.
 *  This code is the intellectual property of Vivek Kumar.
 *  Unauthorized copying, distribution, or use of this project is strictly prohibited.
 * ============================================================================
 */


/**
 * Email Helper Functions
 * Centralized email sending functionality
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Send OTP email to user
 * @param string $to_email Recipient email address
 * @param string $to_name Recipient name
 * @param string $otp OTP code
 * @return array Array with 'success' (bool) and 'message' (string)
 */
function sendOTPEmail($to_email, $to_name, $otp)
{
    try {
        // Load email configuration from database first, fallback to config file
        require_once __DIR__ . '/../config/database.php';
        $database = new Database();
        $db = $database->getConnection();

        // Try to get settings from database
        $query = "SELECT * FROM system_email_settings WHERE id = 1";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $db_settings = $stmt->fetch(PDO::FETCH_ASSOC);

        // If database settings exist and email is enabled, use them
        if ($db_settings && $db_settings['email_enabled']) {
            $smtp_host = $db_settings['smtp_host'];
            $smtp_port = $db_settings['smtp_port'];
            $smtp_secure = $db_settings['smtp_secure'];
            $smtp_username = $db_settings['smtp_username'];
            $smtp_password = base64_decode($db_settings['smtp_password']);
            $from_email = $db_settings['from_email'];
            $from_name = $db_settings['from_name'];
        } else {
            // Fallback to config file
            $email_config = require __DIR__ . '/../config/email_config.php';

            if (!$email_config['use_smtp']) {
                return [
                    'success' => false,
                    'message' => 'Email system is not configured. Please contact administrator.'
                ];
            }

            $smtp_host = $email_config['smtp_host'];
            $smtp_port = $email_config['smtp_port'];
            $smtp_secure = $email_config['smtp_secure'];
            $smtp_username = $email_config['smtp_username'];
            $smtp_password = $email_config['smtp_password'];
            $from_email = $email_config['from_email'];
            $from_name = $email_config['from_name'];
        }

        // Create PHPMailer instance
        $mail = new PHPMailer(true);

        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_username;
        $mail->Password = $smtp_password;
        $mail->SMTPSecure = $smtp_secure;
        $mail->Port = $smtp_port;
        $mail->SMTPDebug = 0;
        $mail->Timeout = 30;
        $mail->SMTPKeepAlive = true;

        // Sender and recipient
        $mail->setFrom($from_email, $from_name);
        $mail->addAddress($to_email, $to_name);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset OTP - College Management System';

        // Email body
        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .otp-box { background: white; border: 2px dashed #667eea; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; }
                .otp-code { font-size: 32px; font-weight: bold; color: #667eea; letter-spacing: 5px; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
                .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🔐 Password Reset Request</h1>
                    <p>CIMAGE College - College Management System</p>
                </div>
                <div class="content">
                    <p>Hello <strong>' . htmlspecialchars($to_name) . '</strong>,</p>
                    
                    <p>We received a request to reset your password. Use the OTP code below to complete the password reset process:</p>
                    
                    <div class="otp-box">
                        <div style="font-size: 14px; color: #666; margin-bottom: 10px;">Your OTP Code:</div>
                        <div class="otp-code">' . htmlspecialchars($otp) . '</div>
                        <div style="font-size: 12px; color: #999; margin-top: 10px;">Valid for 15 minutes</div>
                    </div>
                    
                    <div class="warning">
                        <strong>⚠️ Security Notice:</strong>
                        <ul style="margin: 10px 0; padding-left: 20px;">
                            <li>This OTP is valid for 15 minutes only</li>
                            <li>Never share this code with anyone</li>
                            <li>If you didn\'t request this, please ignore this email</li>
                        </ul>
                    </div>
                    
                    <p>If you did not request a password reset, please ignore this email or contact support if you have concerns.</p>
                    
                    <p>Best regards,<br>
                    <strong>College Management System - Team</strong></p>
                </div>
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' College Management System. All rights reserved.</p>
                    <p>This is an automated email. Please do not reply.</p>
                </div>
            </div>
        </body>
        </html>
        ';

        // Plain text version
        $mail->AltBody = "Hello $to_name,\n\n" .
            "We received a request to reset your password.\n\n" .
            "Your OTP Code: $otp\n\n" .
            "This code is valid for 15 minutes.\n\n" .
            "If you did not request this, please ignore this email.\n\n" .
            "Best regards,\n" .
            "College Management System - Team";

        // Send email
        $mail->send();

        return [
            'success' => true,
            'message' => 'OTP has been sent to your email address. Please check your inbox.'
        ];
    } catch (Exception $e) {
        error_log("Failed to send OTP email: " . $e->getMessage());
        
        // Provide specific error messages based on error type
        $errorMsg = 'Failed to send email. ';
        if (strpos($e->getMessage(), 'authenticate') !== false) {
            $errorMsg .= 'SMTP authentication failed. Please check your email credentials.';
        } elseif (strpos($e->getMessage(), 'connect') !== false) {
            $errorMsg .= 'Could not connect to SMTP server. Please check host and port settings.';
        } elseif (strpos($e->getMessage(), 'timed out') !== false) {
            $errorMsg .= 'Connection timed out. Please check your network and firewall settings.';
        } else {
            $errorMsg .= 'Please contact administrator.';
        }
        
        return [
            'success' => false,
            'message' => $errorMsg,
            'debug' => $e->getMessage()
        ];
    }
}

/**
 * Send welcome email to new user
 * @param string $to_email Recipient email address
 * @param string $to_name Recipient name
 * @param string $role User role
 * @param string $temp_password Temporary password
 * @return array Array with 'success' (bool) and 'message' (string)
 */
function sendWelcomeEmail($to_email, $to_name, $role, $temp_password = null)
{
    try {
        // Load email configuration
        require_once __DIR__ . '/../config/database.php';
        $database = new Database();
        $db = $database->getConnection();

        // Try to get settings from database
        $query = "SELECT * FROM system_email_settings WHERE id = 1";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $db_settings = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($db_settings && $db_settings['email_enabled']) {
            $smtp_host = $db_settings['smtp_host'];
            $smtp_port = $db_settings['smtp_port'];
            $smtp_secure = $db_settings['smtp_secure'];
            $smtp_username = $db_settings['smtp_username'];
            $smtp_password = base64_decode($db_settings['smtp_password']);
            $from_email = $db_settings['from_email'];
            $from_name = $db_settings['from_name'];
        } else {
            $email_config = require __DIR__ . '/../config/email_config.php';

            if (!$email_config['use_smtp']) {
                return ['success' => false, 'message' => 'Email system not configured'];
            }

            $smtp_host = $email_config['smtp_host'];
            $smtp_port = $email_config['smtp_port'];
            $smtp_secure = $email_config['smtp_secure'];
            $smtp_username = $email_config['smtp_username'];
            $smtp_password = $email_config['smtp_password'];
            $from_email = $email_config['from_email'];
            $from_name = $email_config['from_name'];
        }

        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_username;
        $mail->Password = $smtp_password;
        $mail->SMTPSecure = $smtp_secure;
        $mail->Port = $smtp_port;
        $mail->SMTPDebug = 0;
        $mail->Timeout = 30;
        $mail->SMTPKeepAlive = true;

        $mail->setFrom($from_email, $from_name);
        $mail->addAddress($to_email, $to_name);

        $mail->isHTML(true);
        $mail->Subject = 'Welcome to College Management System';

        // Get base URL
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $base_url = $protocol . '://' . $host;
        
        // Construct URLs
        $login_url = $base_url . '/auth/login.php';
        $forgot_password_url = $base_url . '/auth/forgot_password.php';

        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: "Helvetica Neue", Arial, sans-serif; line-height: 1.6; color: #2c3e50; background: #ecf0f1; }
                .email-container { max-width: 600px; margin: 40px auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 20px rgba(0,0,0,0.1); }
                
                .email-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 50px 40px; text-align: center; color: white; }
                .email-header h1 { font-size: 32px; font-weight: 300; margin: 0; letter-spacing: 1px; }
                .email-header .tagline { font-size: 14px; margin-top: 10px; opacity: 0.9; letter-spacing: 2px; text-transform: uppercase; }
                
                .email-body { padding: 50px 40px; background: white; }
                .welcome-section { text-align: center; margin-bottom: 40px; }
                .welcome-section h2 { font-size: 26px; color: #2c3e50; font-weight: 400; margin-bottom: 10px; }
                .welcome-section p { font-size: 15px; color: #7f8c8d; }
                
                .credentials-box { background: #f8f9fa; border: 1px solid #e1e8ed; border-radius: 6px; padding: 35px; margin: 30px 0; }
                .credentials-box h3 { font-size: 18px; color: #667eea; font-weight: 600; margin-bottom: 25px; text-align: center; text-transform: uppercase; letter-spacing: 1px; }
                
                .form-field { margin-bottom: 20px; }
                .form-field label { display: block; font-size: 12px; color: #95a5a6; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; font-weight: 600; }
                .form-field .value { background: white; border: 2px solid #e1e8ed; padding: 12px 15px; border-radius: 4px; font-size: 15px; color: #2c3e50; font-weight: 500; word-break: break-all; }
                .form-field .password-value { background: #667eea; color: white; text-align: center; padding: 18px; font-size: 24px; font-weight: 700; letter-spacing: 4px; border: none; font-family: "Courier New", monospace; }
                
                .button-group { text-align: center; margin: 35px 0 25px; }
                .btn { display: inline-block; padding: 14px 40px; margin: 8px 5px; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.3s; }
                .btn-primary { background: #667eea; color: white !important; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3); }
                .btn-secondary { background: #27ae60; color: white !important; box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3); }
                .btn-tertiary { background: #95a5a6; color: white !important; box-shadow: 0 4px 15px rgba(149, 165, 166, 0.3); }
                
                .info-box { background: #fff8e1; border-left: 4px solid #ffc107; padding: 20px; margin: 30px 0; border-radius: 4px; }
                .info-box h4 { font-size: 14px; color: #f39c12; margin-bottom: 10px; font-weight: 700; text-transform: uppercase; }
                .info-box p { font-size: 14px; color: #7d6608; margin: 0; line-height: 1.6; }
                
                .divider { height: 1px; background: #e1e8ed; margin: 35px 0; }
                
                .footer-section { text-align: center; padding: 20px; }
                .footer-section p { font-size: 14px; color: #95a5a6; margin: 5px 0; }
                
                .email-footer { background: #34495e; color: #bdc3c7; padding: 30px 40px; text-align: center; }
                .email-footer p { margin: 5px 0; font-size: 13px; }
                .email-footer .brand { color: white; font-weight: 600; font-size: 14px; }
                
                @media only screen and (max-width: 600px) {
                    .email-container { margin: 20px; }
                    .email-header { padding: 40px 25px; }
                    .email-body { padding: 35px 25px; }
                    .credentials-box { padding: 25px 20px; }
                    .btn { display: block; margin: 10px 0; }
                }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="email-header">
                    <h1>Welcome</h1>
                    <div class="tagline">College ERP System</div>
                </div>
                
                <div class="email-body">
                    <div class="welcome-section">
                        <h2>Hello, ' . htmlspecialchars($to_name) . '</h2>
                        <p>Your account has been successfully created and is ready to use</p>
                    </div>
                    
                    <div class="credentials-box">
                        <h3>Login Credentials</h3>
                        
                        <div class="form-field">
                            <label>Email Address</label>
                            <div class="value">' . htmlspecialchars($to_email) . '</div>
                        </div>';

        if ($temp_password) {
            $mail->Body .= '
                        <div class="form-field">
                            <label>Temporary Password</label>
                            <div class="value password-value">' . htmlspecialchars($temp_password) . '</div>
                        </div>';
        }

        $mail->Body .= '
                    </div>
                    
                    <div class="button-group">
                        <a href="' . $login_url . '" class="btn btn-primary">Login to Account</a>';

        if ($temp_password) {
            $mail->Body .= '
                        <br>
                        <a href="' . $forgot_password_url . '" class="btn btn-secondary">Reset Password</a>';
        }

        $mail->Body .= '
                        <br>
                        <a href="' . $base_url . '" class="btn btn-tertiary">Visit Homepage</a>
                    </div>';

        if ($temp_password) {
            $mail->Body .= '
                    <div class="divider"></div>
                    
                    <div class="info-box">
                        <h4>Security Notice</h4>
                        <p>This is a temporary password. For your security, please change it immediately after your first login. Never share your password with anyone, and use a strong, unique password for your account.</p>
                    </div>';
        }

        $mail->Body .= '
                    <div class="footer-section">
                        <p>If you need any assistance, please contact our support team</p>
                        <p style="font-weight: 600; color: #2c3e50; margin-top: 15px;">College Administration</p>
                    </div>
                </div>
                
                <div class="email-footer">
                    <p class="brand">College ERP System</p>
                    <p>&copy; ' . date('Y') . ' All Rights Reserved</p>
                    <p style="margin-top: 15px; opacity: 0.7;">This is an automated message. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ';

        $mail->send();
        return ['success' => true, 'message' => 'Welcome email sent'];
    } catch (Exception $e) {
        error_log("Failed to send welcome email: " . $e->getMessage());
        
        $errorMsg = 'Failed to send email. ';
        if (strpos($e->getMessage(), 'authenticate') !== false) {
            $errorMsg .= 'SMTP authentication failed.';
        }
        
        return [
            'success' => false, 
            'message' => $errorMsg,
            'debug' => $e->getMessage()
        ];
    }
}

/**
 * Send admit card notification email to student
 * @param string $to_email Student email address
 * @param string $to_name Student name
 * @param array $admit_card_info Admit card information
 * @return array Array with 'success' (bool) and 'message' (string)
 */
function sendAdmitCardEmail($to_email, $to_name, $admit_card_info)
{
    try {
        require_once __DIR__ . '/../config/database.php';
        $database = new Database();
        $db = $database->getConnection();

        // Get email settings from database
        $query = "SELECT * FROM system_email_settings WHERE id = 1";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $db_settings = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($db_settings && $db_settings['email_enabled']) {
            $smtp_host = $db_settings['smtp_host'];
            $smtp_port = $db_settings['smtp_port'];
            $smtp_secure = $db_settings['smtp_secure'];
            $smtp_username = $db_settings['smtp_username'];
            $smtp_password = base64_decode($db_settings['smtp_password']);
            $from_email = $db_settings['from_email'];
            $from_name = $db_settings['from_name'];
            
            // Check test mode settings
            $use_test_email = isset($db_settings['use_test_email']) && $db_settings['use_test_email'] == 1;
            $test_email = $db_settings['test_email'] ?? null;
        } else {
            $email_config = require __DIR__ . '/../config/email_config.php';
            if (!$email_config['use_smtp']) {
                return ['success' => false, 'message' => 'Email system not configured'];
            }
            $smtp_host = $email_config['smtp_host'];
            $smtp_port = $email_config['smtp_port'];
            $smtp_secure = $email_config['smtp_secure'];
            $smtp_username = $email_config['smtp_username'];
            $smtp_password = $email_config['smtp_password'];
            $from_email = $email_config['from_email'];
            $from_name = $email_config['from_name'];
            $use_test_email = false;
            $test_email = null;
        }

        // Store original recipient for logging
        $original_email = $to_email;
        $original_name = $to_name;
        
        // Override recipient if test mode is enabled
        if ($use_test_email && !empty($test_email)) {
            $to_email = $test_email;
            $to_name = "TEST MODE - " . $original_name;
        }

        $mail = new PHPMailer(true);

        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_username;
        $mail->Password = $smtp_password;
        $mail->SMTPSecure = $smtp_secure;
        $mail->Port = $smtp_port;
        $mail->CharSet = 'UTF-8';

        // Sender and recipient
        $mail->setFrom($from_email, $from_name);
        $mail->addAddress($to_email, $to_name);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = '🎓 Admit Card Available - ' . $admit_card_info['exam_title'];

        $exam_date = date('d M Y', strtotime($admit_card_info['start_date']));
        // Build proper base URL
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $base_url = $protocol . "://" . $host;
        // Remove any trailing slashes and build the admit card link
        $base_url = rtrim($base_url, '/');
        $download_link = $base_url . '/student/admit_card.php?id=' . $admit_card_info['id'];

        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 20px; text-align: center; border-radius: 5px 5px 0 0; }
                .header h1 { margin: 0; font-size: 28px; }
                .content { padding: 30px 20px; }
                .admit-card-box { background: #f8f9fa; border: 2px solid #667eea; border-radius: 8px; padding: 20px; margin: 20px 0; }
                .admit-card-box h2 { color: #667eea; margin-top: 0; }
                .info-row { margin: 10px 0; }
                .info-label { font-weight: 600; color: #555; }
                .info-value { color: #000; }
                .download-btn { display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white !important; padding: 15px 40px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
                .download-btn:hover { opacity: 0.9; }
                .important-notice { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; border-top: 1px solid #ddd; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🎓 Admit Card Available</h1>
                    <p>CIMAGE College - Examination Department</p>
                </div>
                <div class="content">
                    <p>Dear <strong>' . htmlspecialchars($to_name) . '</strong>,</p>
                    
                    <p>Your admit card for the upcoming examination is now available for download.</p>
                    
                    <div class="admit-card-box">
                        <h2>' . htmlspecialchars($admit_card_info['exam_title']) . '</h2>
                        <div class="info-row">
                            <span class="info-label">Examination Type:</span>
                            <span class="info-value">' . htmlspecialchars($admit_card_info['exam_type']) . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Course:</span>
                            <span class="info-value">' . htmlspecialchars($admit_card_info['course_name']) . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Session:</span>
                            <span class="info-value">' . htmlspecialchars($admit_card_info['session_name']) . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Exam Starts From:</span>
                            <span class="info-value">' . $exam_date . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Centre:</span>
                            <span class="info-value">' . htmlspecialchars($admit_card_info['centre_name']) . '</span>
                        </div>
                    </div>
                    
                    <div style="text-align: center;">
                        <a href="' . htmlspecialchars($download_link) . '" class="download-btn">
                            📄 Download Admit Card
                        </a>
                    </div>
                    
                    <div class="important-notice">
                        <strong>⚠️ Important Instructions:</strong>
                        <ul style="margin: 10px 0; padding-left: 20px;">
                            <li>Download and print your admit card</li>
                            <li>Bring admit card and valid College ID to examination centre</li>
                            <li>Report ' . htmlspecialchars($admit_card_info['reporting_instructions'] ?? '30 minutes before exam') . '</li>
                            <li>Mobile phones and electronic devices are not allowed</li>
                        </ul>
                    </div>
                    
                    <p>You can also access your admit card by logging into the student portal and visiting your dashboard.</p>
                    
                    <p>Best wishes for your examination!</p>
                    
                    <p>Regards,<br>
                    <strong>CIMAGE College - Examination Department</strong></p>
                </div>
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' CIMAGE College. All rights reserved.</p>
                    <p>For queries, contact: exam@cimagecollege.edu.in</p>
                </div>
            </div>
        </body>
        </html>
        ';

        $mail->AltBody = "Dear $to_name,\n\n" .
            "Your admit card for {$admit_card_info['exam_title']} is now available.\n\n" .
            "Examination Type: {$admit_card_info['exam_type']}\n" .
            "Exam Date: $exam_date\n" .
            "Centre: {$admit_card_info['centre_name']}\n\n" .
            "Please login to the student portal to download your admit card.\n\n" .
            "Important: Bring admit card and College ID to the examination centre.\n\n" .
            "Best wishes!\n" .
            "CIMAGE College - Examination Department";

        $mail->send();
        
        // Log email activity
        try {
            require_once __DIR__ . '/activity_logger.php';
            $log_description = $use_test_email 
                ? "Admit card email sent in TEST MODE to {$to_email} (Original: {$original_email}) - {$admit_card_info['exam_title']}"
                : "Admit card email sent to {$original_email} - {$admit_card_info['exam_title']}";
            
            $log_stmt = $db->prepare("INSERT INTO activity_log (user_id, activity_type, activity_description, ip_address, created_at) 
                                       VALUES (:user_id, 'email_sent', :description, :ip, NOW())");
            $log_stmt->execute([
                ':user_id' => $_SESSION['user_id'] ?? 0,
                ':description' => $log_description,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
            ]);
        } catch (Exception $log_e) {
            error_log("Failed to log email activity: " . $log_e->getMessage());
        }
        
        return ['success' => true, 'message' => 'Admit Card notification sent'];
    } catch (Exception $e) {
        error_log("Failed to send admit card email: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to send email: ' . $e->getMessage()];
    }
}
