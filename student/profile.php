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

// Check if user is logged in and is student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

// Set page title and include header
$pageTitle = "My Profile";
$basePath = "..";
include_once "../includes/header.php";
?>

<main class="main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-user me-2"></i> My Profile</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="refresh-btn">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    <div id="alert-container"></div>

    <!-- Student Profile Information -->
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-circle"></i> Profile Picture
                </div>
                <div class="card-body text-center">
                    <i class="fas fa-user-circle fa-10x text-secondary mb-3"></i>
                    <h5 id="profile-name">Loading...</h5>
                    <p class="text-muted" id="profile-email">Loading...</p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i> Personal Information
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td width="30%"><strong>College ID:</strong></td>
                                <td><span id="info-college-id" class="badge bg-primary fs-6">Loading...</span></td>
                            </tr>
                            <tr>
                                <td><strong>Full Name:</strong></td>
                                <td id="info-name">Loading...</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td id="info-email">Loading...</td>
                            </tr>
                            <tr>
                                <td><strong>Roll Number:</strong></td>
                                <td id="info-roll">Loading...</td>
                            </tr>
                            <tr>
                                <td><strong>Course:</strong></td>
                                <td id="info-course">Loading...</td>
                            </tr>
                            <tr>
                                <td><strong>Batch:</strong></td>
                                <td id="info-batch">Loading...</td>
                            </tr>
                            <tr>
                                <td><strong>Session:</strong></td>
                                <td id="info-session">Loading...</td>
                            </tr>
                            <tr>
                                <td><strong>Year of Joining:</strong></td>
                                <td id="info-year">Loading...</td>
                            </tr>
                            <tr>
                                <td><strong>Account Created:</strong></td>
                                <td id="info-created">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-key"></i> Change Password
                </div>
                <div class="card-body">
                    <form id="change-password-form">
                        <div class="mb-3">
                            <label for="current-password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current-password" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new-password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new-password" name="new_password" required minlength="6">
                            <small class="text-muted">Minimum 6 characters</small>
                        </div>
                        <div class="mb-3">
                            <label for="confirm-password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm-password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
</main>

<script>
    $(document).ready(function() {
        // Load profile data
        loadProfile();

        // Refresh button
        $("#refresh-btn").click(function() {
            loadProfile();
        });

        // Function to load profile
        function loadProfile() {
            $.ajax({
                type: "GET",
                url: "../api/student/student_info.php",
                dataType: "json",
                success: function(response) {
                    console.log("Profile response:", response);
                    if (response.success) {
                        var data = response.data;

                        // Update profile card
                        $("#profile-name").text(data.name);
                        $("#profile-email").text(data.email || 'N/A');

                        // Update information table
                        $("#info-college-id").text(data.college_id || 'N/A');
                        $("#info-name").text(data.name);
                        $("#info-email").text(data.email || 'N/A');
                        $("#info-roll").text(data.roll_no);
                        $("#info-course").text(data.course);
                        $("#info-batch").text(data.batch);
                        $("#info-session").text(data.session || 'N/A');
                        $("#info-year").text(data.year_of_joining || 'N/A');
                        $("#info-created").text(data.created_at || 'N/A');
                    } else {
                        showAlert('danger', 'Error loading profile: ' + (response.error || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    console.error("Response Text:", xhr.responseText);
                    showAlert('danger', 'Failed to load profile data. Please try again.');
                }
            });
        }

        // Handle password change form
        $("#change-password-form").submit(function(e) {
            e.preventDefault();

            var currentPassword = $("#current-password").val();
            var newPassword = $("#new-password").val();
            var confirmPassword = $("#confirm-password").val();

            // Validate passwords match
            if (newPassword !== confirmPassword) {
                showAlert('danger', 'New passwords do not match!');
                return;
            }

            $.ajax({
                type: "POST",
                url: "../api/common/change_password.php",
                data: {
                    current_password: currentPassword,
                    new_password: newPassword
                },
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        showAlert('success', 'Password changed successfully!');
                        $("#change-password-form")[0].reset();
                    } else {
                        showAlert('danger', 'Error: ' + (response.error || 'Failed to change password'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    showAlert('danger', 'Failed to change password. Please try again.');
                }
            });
        });

        // Function to show alerts
        function showAlert(type, message) {
            var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                message +
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                '</div>';
            $("#alert-container").html(alertHtml);

            // Auto-hide after 5 seconds
            setTimeout(function() {
                $("#alert-container").find('.alert').alert('close');
            }, 5000);
        }
    });
</script>

<?php
include_once "../includes/footer.php";
?>