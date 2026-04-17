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
 * Security utility functions for the College Management System
 * Includes CSRF protection, input validation, and other security measures
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

// Sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Validate integer
function validate_int($value, $min = null, $max = null) {
    $options = array(
        'options' => array(
            'default' => false
        )
    );
    
    if ($min !== null) {
        $options['options']['min_range'] = $min;
    }
    
    if ($max !== null) {
        $options['options']['max_range'] = $max;
    }
    
    return filter_var($value, FILTER_VALIDATE_INT, $options);
}

// Validate date format (YYYY-MM-DD)
function validate_date($date) {
    $format = 'Y-m-d';
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Validate email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Prevent XSS in output
function safe_output($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Log security events
function log_security_event($event_type, $description, $user_id = null) {
    global $conn;
    
    try {
        $query = "INSERT INTO security_logs (event_type, description, user_id, ip_address, timestamp) 
                  VALUES (:event_type, :description, :user_id, :ip_address, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':event_type', $event_type);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':ip_address', $_SERVER['REMOTE_ADDR']);
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        // Silently fail but don't break the application
        error_log("Security log error: " . $e->getMessage());
        return false;
    }
}

// Rate limiting for login attempts
function check_login_attempts($username) {
    global $conn;
    
    try {
        // Clean up old attempts (older than 1 hour)
        $cleanup = "DELETE FROM login_attempts WHERE timestamp < DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        $conn->exec($cleanup);
        
        // Count recent attempts
        $query = "SELECT COUNT(*) FROM login_attempts 
                  WHERE username = :username AND timestamp > DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        // If too many attempts, block
        if ($count >= 5) {
            return false;
        }
        
        return true;
    } catch (PDOException $e) {
        // If we can't check, default to allowing (but log the error)
        error_log("Login attempt check error: " . $e->getMessage());
        return true;
    }
}

// Record login attempt
function record_login_attempt($username, $success) {
    global $conn;
    
    try {
        $query = "INSERT INTO login_attempts (username, ip_address, success, timestamp) 
                  VALUES (:username, :ip_address, :success, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':ip_address', $_SERVER['REMOTE_ADDR']);
        $stmt->bindParam(':success', $success, PDO::PARAM_BOOL);
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        // Silently fail but don't break the application
        error_log("Login attempt record error: " . $e->getMessage());
        return false;
    }
}

// Check if user session is valid and not expired
function validate_session() {
    // Check if session exists
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        return false;
    }
    
    // Check if session is expired (30 minutes of inactivity)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        // Session expired, destroy it
        session_unset();
        session_destroy();
        return false;
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
    
    return true;
}
?>