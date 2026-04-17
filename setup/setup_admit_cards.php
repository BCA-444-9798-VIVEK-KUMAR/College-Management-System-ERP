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
 * Admit Card System Setup Script
 * Run this file once to create the required database tables
 */

require_once '../config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Admit Card System Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0; }
        .info { color: blue; padding: 10px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 5px; margin: 10px 0; }
        h1 { color: #333; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Admit Card System Setup</h1>
";

try {
    // Read the SQL file
    $sql_file = __DIR__ . '/../database/admit_cards_schema.sql';

    if (!file_exists($sql_file)) {
        throw new Exception("SQL file not found: " . $sql_file);
    }

    $sql = file_get_contents($sql_file);

    // Split by semicolon to get individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    echo "<div class='info'>Found " . count($statements) . " SQL statements to execute.</div>";

    $success_count = 0;
    $error_count = 0;

    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }

        try {
            $pdo->exec($statement);
            $success_count++;

            // Extract table name for display
            if (preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches)) {
                echo "<div class='success'>✓ Created table: " . $matches[1] . "</div>";
            }
        } catch (PDOException $e) {
            $error_count++;

            // Check if error is "table already exists"
            if (strpos($e->getMessage(), 'already exists') !== false) {
                if (preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches)) {
                    echo "<div class='info'>ℹ Table already exists: " . $matches[1] . "</div>";
                }
                $success_count++; // Count as success since table exists
                $error_count--;
            } else {
                echo "<div class='error'>✗ Error: " . $e->getMessage() . "</div>";
            }
        }
    }

    echo "<h2>Setup Summary</h2>";
    echo "<div class='success'>Successfully executed: $success_count statements</div>";

    if ($error_count > 0) {
        echo "<div class='error'>Failed: $error_count statements</div>";
    }

    // Verify tables
    echo "<h2>Verification</h2>";
    $tables_to_check = ['exam_admit_cards', 'exam_admit_card_subjects', 'student_admit_cards'];

    foreach ($tables_to_check as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            $exists = $stmt->fetch();

            if ($exists) {
                // Get row count
                $count_stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
                $count = $count_stmt->fetch()['count'];

                echo "<div class='success'>✓ Table '$table' exists (Current records: $count)</div>";
            } else {
                echo "<div class='error'>✗ Table '$table' does not exist</div>";
            }
        } catch (PDOException $e) {
            echo "<div class='error'>✗ Error checking table '$table': " . $e->getMessage() . "</div>";
        }
    }

    echo "<h2>Next Steps</h2>";
    echo "<div class='info'>";
    echo "<ol>";
    echo "<li>Go to <a href='../admin/manage_exams.php'>Admin > Manage Exams</a></li>";
    echo "<li>Click 'Create Admit Card' button</li>";
    echo "<li>Fill in the examination details and schedule</li>";
    echo "<li>Publish the admit card</li>";
    echo "<li>Students will see it on their dashboard</li>";
    echo "</ol>";
    echo "</div>";

    echo "<div class='success'><strong>Setup completed successfully!</strong></div>";
} catch (Exception $e) {
    echo "<div class='error'><strong>Setup failed:</strong> " . $e->getMessage() . "</div>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "
    <hr>
    <p><a href='../admin/manage_exams.php'>Go to Manage Exams</a> | <a href='../admin/dashboard.php'>Go to Admin Dashboard</a></p>
</body>
</html>";
