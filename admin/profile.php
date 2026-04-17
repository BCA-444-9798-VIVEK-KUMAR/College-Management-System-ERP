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

// Start session
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect to login page
    header("Location: ../auth/login.php");
    exit;
}

// Set page title and include header
$pageTitle = "My Profile";
$basePath = "..";
include_once "../includes/header.php";
?>

<!-- Main content -->`n<main class="main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="bi bi-person-circle"></i> My Profile</h1>
            </div>

            <!-- Profile Information -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-user-circle"></i> Profile Picture
                        </div>
                        <div class="card-body text-center">
                            <i class="fas fa-user-circle fa-10x text-secondary mb-3"></i>
                            <h5 id="profileNameDisplay">Loading...</h5>
                            <p class="text-muted" id="profileEmailDisplay">Loading...</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-info-circle"></i> Personal Information
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <td width="30%"><strong><i class="fas fa-user text-primary"></i> Full Name:</strong></td>
                                        <td id="profileName">Loading...</td>
                                    </tr>
                                    <tr>
                                        <td><strong><i class="fas fa-envelope text-primary"></i> Email:</strong></td>
                                        <td id="profileEmail">Loading...</td>
                                    </tr>
                                    <tr>
                                        <td><strong><i class="fas fa-shield-alt text-primary"></i> Role:</strong></td>
                                        <td id="profileRole">Administrator</td>
                                    </tr>
                                    <tr>
                                        <td><strong><i class="fas fa-calendar-plus text-primary"></i> Account Created:</strong></td>
                                        <td id="profileCreated">Loading...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Change Password -->
            <div class="row mt-4 mb-5">
                <div class="col-md-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-warning text-dark">
                            <i class="fas fa-key"></i> Change Password
                        </div>
                        <div class="card-body">
                            <div id="passwordMessage"></div>
                            <form id="changePasswordForm">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="currentPassword" class="form-label">
                                                <i class="fas fa-lock"></i> Current Password
                                            </label>
                                            <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="newPassword" class="form-label">
                                                <i class="fas fa-key"></i> New Password
                                            </label>
                                            <input type="password" class="form-control" id="newPassword" name="new_password" required minlength="6">
                                            <div class="form-text">Password must be at least 6 characters long.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="confirmPassword" class="form-label">
                                                <i class="fas fa-check-circle"></i> Confirm New Password
                                            </label>
                                            <input type="password" class="form-control" id="confirmPassword" required minlength="6">
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary" id="changePasswordBtn">
                                    <i class="fas fa-save"></i> Change Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        // Load admin profile information
        loadAdminInfo();

        // Handle password change form
        $('#changePasswordForm').on('submit', function(e) {
            e.preventDefault();
            changePassword();
        });
    });

    function loadAdminInfo() {
        $.ajax({
            url: '../api/admin/admin_info.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    // Update profile picture card
                    $('#profileNameDisplay').text(data.name);
                    $('#profileEmailDisplay').text(data.email);

                    // Update information table
                    $('#profileName').text(data.name);
                    $('#profileEmail').text(data.email);
                    $('#profileCreated').text(data.created_at);
                } else {
                    $('#profileNameDisplay').html('<span class="text-danger">Error</span>');
                    $('#profileEmailDisplay').html('<span class="text-danger">Error loading data</span>');
                    $('#profileName').html('<span class="text-danger">Error: ' + response.error + '</span>');
                    $('#profileEmail').html('<span class="text-danger">Error loading data</span>');
                    $('#profileCreated').html('<span class="text-danger">Error loading data</span>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading admin info:', error);
                $('#profileNameDisplay').html('<span class="text-danger">Error</span>');
                $('#profileEmailDisplay').html('<span class="text-danger">Error loading data</span>');
                $('#profileName').html('<span class="text-danger">Error loading data</span>');
                $('#profileEmail').html('<span class="text-danger">Error loading data</span>');
                $('#profileCreated').html('<span class="text-danger">Error loading data</span>');
            }
        });
    }

    function changePassword() {
        const currentPassword = $('#currentPassword').val();
        const newPassword = $('#newPassword').val();
        const confirmPassword = $('#confirmPassword').val();

        // Client-side validation
        if (newPassword !== confirmPassword) {
            showMessage('Passwords do not match', 'danger');
            return;
        }

        if (newPassword.length < 6) {
            showMessage('New password must be at least 6 characters long', 'danger');
            return;
        }

        // Disable button and show loading
        $('#changePasswordBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Changing...');

        $.ajax({
            url: '../api/common/change_password.php',
            method: 'POST',
            data: {
                current_password: currentPassword,
                new_password: newPassword
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showMessage(response.message, 'success');
                    $('#changePasswordForm')[0].reset();
                } else {
                    showMessage(response.error, 'danger');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error changing password:', error);
                showMessage('An error occurred while changing password', 'danger');
            },
            complete: function() {
                // Re-enable button
                $('#changePasswordBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Change Password');
            }
        });
    }

    function showMessage(message, type) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const html = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
        $('#passwordMessage').html(html);

        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow', function() {
                $(this).remove();
            });
        }, 5000);
    }
</script>

<?php
// Include footer
include_once "../includes/footer.php";
?>