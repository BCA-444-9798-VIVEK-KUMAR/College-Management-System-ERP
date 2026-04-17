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
 * Email Configuration
 * Configure SMTP settings for sending emails
 */

// Email configuration array
$email_config = [
    // SMTP Settings
    'use_smtp' => true, // Set to true to use SMTP, false to use PHP mail()
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_secure' => 'tls', // 'tls' or 'ssl'
    'smtp_auth' => true,

    'smtp_username' => 'vk5045343@gmail.com', 
    'smtp_password' => 'kptvyefnvqoajkcd',

    // Sender Details
    'from_email' => 'vk5045343@gmail.com',
    'from_name' => 'CIMAGE College - College Management System',

    // Test Email (for testing purposes)
    'test_email' => 'vk5045343@gmail.com',
    'use_test_email' => true, // Set to true to send all emails to test_email
];

// Alternative SMTP services (uncomment to use)

// Mailtrap (for testing - catches all emails)
/*
$email_config = [
    'use_smtp' => true,
    'smtp_host' => 'smtp.mailtrap.io',
    'smtp_port' => 2525,
    'smtp_secure' => 'tls',
    'smtp_auth' => true,
    'smtp_username' => 'your-mailtrap-username',
    'smtp_password' => 'your-mailtrap-password',
    'from_email' => 'noreply@cimagecollege.edu',
    'from_name' => 'CIMAGE College - College Management System',
    'test_email' => 'test@example.com',
    'use_test_email' => false,
];
*/

// SendGrid
/*
$email_config = [
    'use_smtp' => true,
    'smtp_host' => 'smtp.sendgrid.net',
    'smtp_port' => 587,
    'smtp_secure' => 'tls',
    'smtp_auth' => true,
    'smtp_username' => 'apikey',
    'smtp_password' => 'your-sendgrid-api-key',
    'from_email' => 'noreply@cimagecollege.edu',
    'from_name' => 'CIMAGE College - College Management System',
    'test_email' => 'test@example.com',
    'use_test_email' => false,
];
*/

return $email_config;
