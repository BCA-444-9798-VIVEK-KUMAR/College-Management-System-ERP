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

// --- ANTI-PIRACY DOMAIN CHECK ---
$allowed_hosts = ['college-erp.free.nf', 'localhost', '127.0.0.1'];
$current_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';

// Remove 'www.' if present for clean checking
$current_host = str_replace('www.', '', $current_host);

if ($current_host && !in_array($current_host, $allowed_hosts)) {
    http_response_code(403);
    die("<html><head><title>Access Denied</title></head><body style='font-family:sans-serif;text-align:center;padding:50px;background:#f8f9fa;color:#333;'><h1>403 Forbidden</h1><p>Software license restricted to authorized domain.</p><p>This application is the intellectual property of <strong>Vivek Kumar</strong>.</p></body></html>");
}
// --------------------------------

// Database Configuration for mysqli (InfinityFree hosting)



// Database Configuration for mysqli (Local running)

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "college_erp";


// Function to create mysqli connection with retry logic
function getMysqliConnection($retries = 5, $delay = 3) {
    global $servername, $username, $password, $dbname;
    
    $lastError = null;
    for ($i = 0; $i < $retries; $i++) {
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            
            $conn = new mysqli($servername, $username, $password, $dbname);
            $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
            $conn->set_charset("utf8mb4");
            
            // Set timezone
            $conn->query("SET time_zone = '+05:30'");
            
            return $conn;
        } catch (mysqli_sql_exception $e) {
            $lastError = $e;
            if ($i < $retries - 1) {
                sleep($delay);
            }
        }
    }
    
    // If all retries failed, return JSON error and exit
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed after ' . $retries . ' attempts',
        'message' => 'Unable to connect to database. Please try again later.',
        'details' => $lastError ? $lastError->getMessage() : 'Unknown error'
    ]);
    exit;
}

// Database Configuration class for PDO
class Database
{
    //Online Hosting Configuration

    

    // Local Running Configuration

    private $host = "localhost";
    private $db_name = "college_erp";
    private $username = "root";
    private $password = "";
    private $conn;

    // Get database connection with retry logic
    public function getConnection($retries = 5, $delay = 3)
    {
        $this->conn = null;
        $lastError = null;

        for ($i = 0; $i < $retries; $i++) {
            try {
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_TIMEOUT => 10,
                    PDO::ATTR_PERSISTENT => false
                ];
                
                $this->conn = new PDO(
                    "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4", 
                    $this->username, 
                    $this->password,
                    $options
                );

                // Set timezone to Asia/Kolkata (Indian Standard Time)
                date_default_timezone_set('Asia/Kolkata');
                $this->conn->exec("SET time_zone = '+05:30'");
                
                return $this->conn;
            } catch (PDOException $exception) {
                $lastError = $exception;
                if ($i < $retries - 1) {
                    sleep($delay);
                }
            }
        }
        
        // If all retries failed, return JSON error and exit
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Database connection failed after ' . $retries . ' attempts',
            'message' => 'Unable to connect to database. Please try again later.',
            'details' => $lastError ? $lastError->getMessage() : 'Unknown error'
        ]);
        exit;
    }
}
