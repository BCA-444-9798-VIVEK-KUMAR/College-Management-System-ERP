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
 * Activity Logger Helper
 * Functions to log user activities throughout the application
 */

/**
 * Log an activity to the activity_log table
 * 
 * @param PDO $db Database connection
 * @param int $user_id User ID performing the activity
 * @param string $activity_type Type of activity (Login, Create, Update, Delete, etc.)
 * @param string $description Detailed description of the activity
 * @return bool Success status
 */
function logActivity($db, $user_id, $activity_type, $description)
{
    try {
        $query = "INSERT INTO activity_log 
                  (user_id, activity_type, activity_description, ip_address, user_agent, created_at) 
                  VALUES 
                  (:user_id, :activity_type, :description, :ip_address, :user_agent, NOW())";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':activity_type', $activity_type);
        $stmt->bindParam(':description', $description);

        // Get client IP address
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $stmt->bindParam(':ip_address', $ip_address);

        // Get user agent
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $stmt->bindParam(':user_agent', $user_agent);

        return $stmt->execute();
    } catch (PDOException $e) {
        // Log error but don't break the application
        error_log("Activity logging failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Log a login activity
 */
function logLogin($db, $user_id, $user_name, $role)
{
    $description = ucfirst($role) . " '$user_name' logged into the system";
    return logActivity($db, $user_id, 'Login', $description);
}

/**
 * Log a logout activity
 */
function logLogout($db, $user_id, $user_name, $role)
{
    $description = ucfirst($role) . " '$user_name' logged out of the system";
    return logActivity($db, $user_id, 'Logout', $description);
}

/**
 * Log a create activity
 */
function logCreate($db, $user_id, $entity_type, $entity_name)
{
    $description = "Created new $entity_type: $entity_name";
    return logActivity($db, $user_id, 'Create', $description);
}

/**
 * Log an update activity
 */
function logUpdate($db, $user_id, $entity_type, $entity_name)
{
    $description = "Updated $entity_type: $entity_name";
    return logActivity($db, $user_id, 'Update', $description);
}

/**
 * Log a delete activity
 */
function logDelete($db, $user_id, $entity_type, $entity_name)
{
    $description = "Deleted $entity_type: $entity_name";
    return logActivity($db, $user_id, 'Delete', $description);
}

/**
 * Log an attendance activity
 */
function logAttendance($db, $user_id, $teacher_name, $student_count, $date)
{
    $description = "Teacher '$teacher_name' marked attendance for $student_count student(s) on $date";
    return logActivity($db, $user_id, 'Attendance', $description);
}

/**
 * Log a subject assignment activity
 */
function logAssignment($db, $user_id, $teacher_name, $subject_name, $batch_name)
{
    $description = "Assigned teacher '$teacher_name' to subject '$subject_name' for batch '$batch_name'";
    return logActivity($db, $user_id, 'Assignment', $description);
}

/**
 * Log a password change activity
 */
function logPasswordChange($db, $user_id, $user_name)
{
    $description = "User '$user_name' changed their password";
    return logActivity($db, $user_id, 'Security', $description);
}
