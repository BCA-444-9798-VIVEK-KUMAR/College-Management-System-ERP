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

class User {
    // Database connection and table name
    private $conn;
    private $table_name = "users";

    // Object properties
    public $id;
    public $name;
    public $email;
    public $password_hash;
    public $role;
    public $created_at;
    public $updated_at;

    // Constructor with DB
    public function __construct($db) {
        $this->conn = $db;
    }

    // Login user
    public function login($password) {
        // Query to check if email exists
        $query = "SELECT id, name, email, password_hash, role FROM " . $this->table_name . " WHERE email = :email LIMIT 1";

        // Prepare the query
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->email = htmlspecialchars(strip_tags($this->email));

        // Bind values
        $stmt->bindParam(":email", $this->email);

        // Execute query
        $stmt->execute();

        // Check if email exists
        if ($stmt->rowCount() > 0) {
            // Get record details
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify password
            if (password_verify($password, $row['password_hash'])) {
                return $row;
            }
        }

        return false;
    }

    // Create user
    public function create() {
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . " 
                  SET name=:name, email=:email, password_hash=:password_hash, role=:role";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password_hash = htmlspecialchars(strip_tags($this->password_hash));
        $this->role = htmlspecialchars(strip_tags($this->role));

        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password_hash", $this->password_hash);
        $stmt->bindParam(":role", $this->role);

        // Execute query
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    // Check if email exists
    public function emailExists() {
        // Query to check if email exists
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email LIMIT 1";

        // Prepare the query
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->email = htmlspecialchars(strip_tags($this->email));

        // Bind values
        $stmt->bindParam(":email", $this->email);

        // Execute query
        $stmt->execute();

        // Return true if email exists
        return $stmt->rowCount() > 0;
    }

    // Generate OTP for password reset
    public function generateOTP() {
        // Generate 6-digit OTP
        $otp = sprintf("%06d", mt_rand(1, 999999));
        
        // Set expiry time (15 minutes from now)
        $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        // Query to insert OTP
        $query = "INSERT INTO otp_tokens 
                  SET user_id=:user_id, token=:token, expires_at=:expires_at, purpose='pwd_reset', used=0";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(":user_id", $this->id);
        $stmt->bindParam(":token", $otp);
        $stmt->bindParam(":expires_at", $expires_at);
        
        // Execute query
        if ($stmt->execute()) {
            return $otp;
        }
        
        return false;
    }

    // Verify OTP
    public function verifyOTP($otp) {
        // Query to check if OTP exists and is valid
        $query = "SELECT id FROM otp_tokens 
                  WHERE user_id = :user_id AND token = :token AND purpose = 'pwd_reset' 
                  AND expires_at > NOW() AND used = 0 LIMIT 1";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(":user_id", $this->id);
        $stmt->bindParam(":token", $otp);
        
        // Execute query
        $stmt->execute();
        
        // Return true if OTP is valid
        return $stmt->rowCount() > 0;
    }

    // Mark OTP as used
    public function markOTPAsUsed($otp) {
        // Query to update OTP status
        $query = "UPDATE otp_tokens 
                  SET used = 1 
                  WHERE user_id = :user_id AND token = :token AND purpose = 'pwd_reset'";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(":user_id", $this->id);
        $stmt->bindParam(":token", $otp);
        
        // Execute query
        return $stmt->execute();
    }

    // Update password
    public function updatePassword($new_password) {
        // Hash the password
        $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
        
        // Query to update password
        $query = "UPDATE " . $this->table_name . " 
                  SET password_hash = :password_hash 
                  WHERE id = :id";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(":password_hash", $password_hash);
        $stmt->bindParam(":id", $this->id);
        
        // Execute query
        return $stmt->execute();
    }

    // Get user by ID
    public function readOne() {
        // Query to read single record
        $query = "SELECT id, name, email, role, created_at, updated_at 
                  FROM " . $this->table_name . " 
                  WHERE id = :id LIMIT 1";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind ID
        $stmt->bindParam(":id", $this->id);
        
        // Execute query
        $stmt->execute();
        
        // Get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            // Set values to object properties
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->role = $row['role'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }

    // Get user by email
    public function readByEmail() {
        // Query to read single record
        $query = "SELECT id, name, email, role 
                  FROM " . $this->table_name . " 
                  WHERE email = :email LIMIT 1";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->email = htmlspecialchars(strip_tags($this->email));
        
        // Bind email
        $stmt->bindParam(":email", $this->email);
        
        // Execute query
        $stmt->execute();
        
        // Get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            // Set values to object properties
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->role = $row['role'];
            
            return true;
        }
        
        return false;
    }
}
?>