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

// Helper function to get student ID from user session
// Uses college_id as the linking field between users and students

function getStudentIdFromSession($conn, $user_id)
{
    // First get college_id from users table
    $userQuery = "SELECT college_id FROM users WHERE id = :user_id";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $userStmt->execute();

    if ($userStmt->rowCount() === 0) {
        return null;
    }

    $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
    $college_id = $userData['college_id'];

    // Get student ID using college_id
    $studentQuery = "SELECT id FROM students WHERE college_id = :college_id";
    $studentStmt = $conn->prepare($studentQuery);
    $studentStmt->bindParam(':college_id', $college_id, PDO::PARAM_STR);
    $studentStmt->execute();

    if ($studentStmt->rowCount() > 0) {
        $student = $studentStmt->fetch(PDO::FETCH_ASSOC);
        return $student['id'];
    }

    return null;
}

function getStudentDataFromSession($conn, $user_id)
{
    // First get college_id from users table
    $userQuery = "SELECT college_id FROM users WHERE id = :user_id";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $userStmt->execute();

    if ($userStmt->rowCount() === 0) {
        return null;
    }

    $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
    $college_id = $userData['college_id'];

    // Get student data using college_id
    $studentQuery = "SELECT * FROM students WHERE college_id = :college_id";
    $studentStmt = $conn->prepare($studentQuery);
    $studentStmt->bindParam(':college_id', $college_id, PDO::PARAM_STR);
    $studentStmt->execute();

    if ($studentStmt->rowCount() > 0) {
        return $studentStmt->fetch(PDO::FETCH_ASSOC);
    }

    return null;
}
