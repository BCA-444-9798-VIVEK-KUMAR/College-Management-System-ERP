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

session_start();

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../config/database.php';

$page_title = 'Email Settings';
$pageTitle = 'Email Settings';
$basePath = "..";
include '../includes/header.php';
?>

<!-- SweetAlert2 CSS & JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .bg-purple {
        background-color: #6f42c1 !important;
        color: white;
    }
</style>

<!-- Main Content -->
<main class="main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-envelope-open-text me-2"></i> Email Settings</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshBtn">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    <div id="alertContainer"></div>

    <!-- System Email Configuration -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-server me-2"></i> SMTP Server Configuration</h5>
                </div>
                <div class="card-body">
                    <form id="smtpConfigForm">
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="smtp_host" class="form-label">SMTP Host <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="smtp_host" name="smtp_host"
                                    placeholder="smtp.gmail.com" required>
                                <small class="text-muted">Gmail: smtp.gmail.com | Outlook: smtp-mail.outlook.com</small>
                            </div>
                            <div class="col-md-4">
                                <label for="smtp_port" class="form-label">SMTP Port <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="smtp_port" name="smtp_port"
                                    placeholder="587" required>
                                <small class="text-muted">TLS: 587 | SSL: 465</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="smtp_secure" class="form-label">Encryption Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="smtp_secure" name="smtp_secure" required>
                                <option value="">-- Select Encryption --</option>
                                <option value="tls">TLS (Port 587)</option>
                                <option value="ssl">SSL (Port 465)</option>
                            </select>
                        </div>

                        <hr class="my-4">

                        <div class="mb-3">
                            <label for="smtp_username" class="form-label">Email Username / Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="smtp_username" name="smtp_username"
                                placeholder="your-email@gmail.com" required>
                            <small class="text-muted">This will be used for SMTP authentication</small>
                        </div>

                        <div class="mb-3">
                            <label for="smtp_password" class="form-label">Email Password / App Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="smtp_password" name="smtp_password"
                                    placeholder="Enter password or App Password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted">
                                <strong>Gmail Users:</strong> You must use an
                                <a href="../gmail_smtp_setup.php" target="_blank">App Password</a>,
                                not your regular password.
                            </small>
                        </div>

                        <hr class="my-4">

                        <div class="mb-3">
                            <label for="from_email" class="form-label">Sender Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="from_email" name="from_email"
                                placeholder="noreply@cimagecollege.edu" required>
                            <small class="text-muted">Email address that will appear as sender in all outgoing emails</small>
                        </div>

                        <div class="mb-3">
                            <label for="from_name" class="form-label">Sender Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="from_name" name="from_name"
                                placeholder="CIMAGE College - Management System" required>
                            <small class="text-muted">Name that will appear as sender in all outgoing emails</small>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="email_enabled" name="email_enabled" checked>
                                <label class="form-check-label" for="email_enabled">
                                    <strong>Enable Email System</strong>
                                </label>
                                <small class="d-block text-muted">Disable to temporarily stop all outgoing emails</small>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3"><i class="fas fa-tachometer-alt me-2"></i> Rate Limiting & Safety</h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="batch_size" class="form-label">Emails per Batch</label>
                                <input type="number" class="form-control" id="batch_size" name="batch_size"
                                    value="10" min="1" max="50">
                                <small class="text-muted">Number of emails to send before pausing</small>
                            </div>
                            <div class="col-md-6">
                                <label for="batch_delay" class="form-label">Batch Delay (seconds)</label>
                                <input type="number" class="form-control" id="batch_delay" name="batch_delay"
                                    value="5" min="1" max="60">
                                <small class="text-muted">Pause duration between batches</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email_delay" class="form-label">Delay Between Emails (milliseconds)</label>
                            <input type="number" class="form-control" id="email_delay" name="email_delay"
                                value="500" min="100" max="5000" step="100">
                            <small class="text-muted">Time to wait between individual email sends</small>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="use_test_email" name="use_test_email">
                                <label class="form-check-label" for="use_test_email">
                                    <strong>Enable Test Mode (All Emails to Test Address)</strong>
                                </label>
                                <small class="d-block text-muted">
                                    When enabled, <strong>ALL emails including admit cards</strong> will be sent to the test address below instead of real recipients.
                                    This applies to all modules in the system for testing purposes.
                                </small>
                            </div>
                            <div class="mt-2 ms-4" id="testEmailInput" style="display: none;">
                                <label for="test_email_address" class="form-label small">Test Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control form-control-sm" id="test_email_address" name="test_email_address" 
                                    placeholder="test@example.com">
                                <small class="text-info d-block mt-1">
                                    <i class="fas fa-info-circle"></i> All system emails will be redirected to this address when test mode is active
                                </small>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i> Save SMTP Configuration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar with Info and Test -->
        <div class="col-lg-4">
            <!-- Current Status -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Current Status</h6>
                </div>
                <div class="card-body">
                    <div id="currentStatus">
                        <p class="text-center text-muted">
                            <i class="fas fa-spinner fa-spin"></i> Loading...
                        </p>
                    </div>
                </div>
            </div>

            <!-- Test Email -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-paper-plane me-2"></i> Test Email</h6>
                </div>
                <div class="card-body">
                    <form id="testEmailForm">
                        <div class="mb-3">
                            <label for="test_email" class="form-label">Test Email Address</label>
                            <input type="email" class="form-control" id="test_email" name="test_email"
                                placeholder="test@example.com" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-paper-plane me-2"></i> Send Test Email
                            </button>
                        </div>
                    </form>
                    <small class="text-muted d-block mt-2">
                        This will send a test email using the current SMTP settings.
                    </small>
                </div>
            </div>

            <!-- Help & Documentation -->
            <div class="card shadow-sm">
                <div class="card-header bg-warning">
                    <h6 class="mb-0"><i class="fas fa-question-circle me-2"></i> Help & Guides</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <a href="../gmail_smtp_setup.php" target="_blank" class="text-decoration-none">
                                <i class="fas fa-external-link-alt me-2"></i> Gmail SMTP Setup Guide
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="javascript:void(0)" onclick="showCommonIssues()" class="text-decoration-none">
                                <i class="fas fa-wrench me-2"></i> Common SMTP Issues
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)" onclick="showEmailProviders()" class="text-decoration-none">
                                <i class="fas fa-server me-2"></i> SMTP Provider Settings
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Log -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i> Recent Email Activity</h5>
                        <small id="activityCount" class="text-white">Loading...</small>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Activity</th>
                                    <th>User</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody id="activityLog">
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        <i class="fas fa-spinner fa-spin"></i> Loading activity...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="loadMoreContainer" class="text-center mt-3" style="display: none;">
                        <button class="btn btn-outline-secondary btn-sm" id="loadMoreActivity">
                            <i class="fas fa-angle-down me-2"></i>Load More
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    $(document).ready(function() {
        // Load current settings
        loadCurrentSettings();
        loadActivityLog();

        // Toggle password visibility
        $('#togglePassword').click(function() {
            const passwordField = $('#smtp_password');
            const icon = $(this).find('i');

            if (passwordField.attr('type') === 'password') {
                passwordField.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                passwordField.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // Toggle test email input visibility
        $('#use_test_email').change(function() {
            if ($(this).is(':checked')) {
                $('#testEmailInput').slideDown();
                $('#test_email_address').prop('required', true);
            } else {
                $('#testEmailInput').slideUp();
                $('#test_email_address').prop('required', false);
            }
        });

        // SMTP Configuration Form Submit
        $('#smtpConfigForm').submit(function(e) {
            e.preventDefault();

            const formData = {
                smtp_host: $('#smtp_host').val(),
                smtp_port: $('#smtp_port').val(),
                smtp_secure: $('#smtp_secure').val(),
                smtp_username: $('#smtp_username').val(),
                smtp_password: $('#smtp_password').val(),
                from_email: $('#from_email').val(),
                from_name: $('#from_name').val(),
                email_enabled: $('#email_enabled').is(':checked') ? 1 : 0,
                batch_size: parseInt($('#batch_size').val()) || 10,
                batch_delay: parseInt($('#batch_delay').val()) || 5,
                email_delay: parseInt($('#email_delay').val()) || 500,
                use_test_email: $('#use_test_email').is(':checked') ? 1 : 0,
                test_email: $('#test_email_address').val() || $('#from_email').val()
            };

            $.ajax({
                url: '../api/admin/save_smtp_settings.php',
                type: 'POST',
                data: JSON.stringify(formData),
                contentType: 'application/json',
                beforeSend: function() {
                    Swal.fire({
                        title: 'Saving...',
                        text: 'Please wait while we save your settings',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Settings Saved!',
                            text: 'SMTP configuration has been updated successfully.',
                            timer: 2000
                        });
                        loadCurrentSettings();
                        loadActivityLog();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error || 'Failed to save settings'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Connection Error',
                        text: 'Failed to connect to the server'
                    });
                }
            });
        });

        // Test Email Form Submit
        $('#testEmailForm').submit(function(e) {
            e.preventDefault();

            const testEmail = $('#test_email').val();

            $.ajax({
                url: '../api/admin/test_smtp.php',
                type: 'POST',
                data: JSON.stringify({
                    test_email: testEmail
                }),
                contentType: 'application/json',
                beforeSend: function() {
                    Swal.fire({
                        title: 'Sending Test Email...',
                        text: 'This may take a few seconds',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Test Email Sent!',
                            html: `Test email has been sent successfully to <strong>${testEmail}</strong>`,
                            timer: 3000
                        });
                        loadActivityLog();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Test Failed',
                            html: `<p>${response.error || 'Failed to send test email'}</p>
                               ${response.details ? `<small class="text-muted">${response.details}</small>` : ''}`
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Connection Error',
                        text: 'Failed to connect to the server'
                    });
                }
            });
        });

        // Refresh button
        $('#refreshBtn').click(function() {
            loadCurrentSettings();
            loadActivityLog();

            Swal.fire({
                icon: 'success',
                title: 'Refreshed',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1500
            });
        });
    });

    // Load current SMTP settings
    function loadCurrentSettings() {
        $.ajax({
            url: '../api/admin/get_smtp_settings.php',
            type: 'GET',
            success: function(response) {
                if (response.success && response.data) {
                    const settings = response.data;

                    // Populate form
                    $('#smtp_host').val(settings.smtp_host || '');
                    $('#smtp_port').val(settings.smtp_port || '');
                    $('#smtp_secure').val(settings.smtp_secure || '');
                    $('#smtp_username').val(settings.smtp_username || '');
                    $('#smtp_password').val(settings.smtp_password || '');
                    $('#from_email').val(settings.from_email || '');
                    $('#from_name').val(settings.from_name || '');
                    $('#email_enabled').prop('checked', settings.email_enabled == 1);

                    // Populate rate limiting settings
                    $('#batch_size').val(settings.batch_size || 10);
                    $('#batch_delay').val(settings.batch_delay || 5);
                    $('#email_delay').val(settings.email_delay || 500);
                    $('#use_test_email').prop('checked', settings.use_test_email == 1);
                    $('#test_email_address').val(settings.test_email || '');
                    
                    // Show/hide test email input based on checkbox state
                    if (settings.use_test_email == 1) {
                        $('#testEmailInput').show();
                        $('#test_email_address').prop('required', true);
                    } else {
                        $('#testEmailInput').hide();
                        $('#test_email_address').prop('required', false);
                    }

                    // Update status display
                    updateStatusDisplay(settings);
                }
            }
        });
    }

    // Update status display
    function updateStatusDisplay(settings) {
        const status = settings.email_enabled == 1 ?
            '<span class="badge bg-success"><i class="fas fa-check-circle"></i> Enabled</span>' :
            '<span class="badge bg-danger"><i class="fas fa-times-circle"></i> Disabled</span>';

        const testMode = settings.use_test_email == 1 ?
            '<span class="badge bg-warning"><i class="fas fa-exclamation-triangle"></i> Test Mode Active</span>' :
            '<span class="badge bg-success"><i class="fas fa-check"></i> Production Mode</span>';
        
        const testEmailInfo = settings.use_test_email == 1 && settings.test_email ?
            `<div class="mb-2">
                <strong>Test Email:</strong><br>
                <small class="text-danger">${settings.test_email}</small>
            </div>` : '';

        const html = `
        <div class="mb-2">
            <strong>Email System:</strong> ${status}
        </div>
        <div class="mb-2">
            <strong>Mode:</strong> ${testMode}
        </div>
        ${testEmailInfo}
        <div class="mb-2">
            <strong>SMTP Host:</strong><br>
            <code>${settings.smtp_host || 'Not configured'}</code>
        </div>
        <div class="mb-2">
            <strong>SMTP Port:</strong> <code>${settings.smtp_port || 'N/A'}</code>
        </div>
        <div class="mb-2">
            <strong>Encryption:</strong> <code>${settings.smtp_secure ? settings.smtp_secure.toUpperCase() : 'N/A'}</code>
        </div>
        <div class="mb-2">
            <strong>From Email:</strong><br>
            <small>${settings.from_email || 'Not set'}</small>
        </div>
        <div class="mb-2">
            <strong>Rate Limiting:</strong><br>
            <small>Batch: ${settings.batch_size || 10} emails, Delay: ${settings.batch_delay || 5}s, Email: ${settings.email_delay || 500}ms</small>
        </div>
        <div>
            <strong>Last Updated:</strong><br>
            <small class="text-muted">${settings.updated_at || 'Never'}</small>
        </div>
    `;

        $('#currentStatus').html(html);
    }

    // Load activity log
    let activityOffset = 0;
    let activityTotal = 0;
    
    function loadActivityLog(append = false) {
        if (!append) {
            activityOffset = 0;
        }
        
        $.ajax({
            url: '../api/admin/get_email_activity.php?offset=' + activityOffset + '&limit=15',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    activityTotal = response.total || 0;
                    
                    if (response.data && response.data.length > 0) {
                        let html = '';
                        response.data.forEach(function(activity) {
                            // Determine badge color and icon based on activity type
                            let badgeClass = 'bg-secondary';
                            let iconClass = 'fa-envelope';
                            
                            switch(activity.activity_type.toLowerCase()) {
                                case 'smtp_settings':
                                case 'email_settings':
                                    badgeClass = 'bg-info';
                                    iconClass = 'fa-cog';
                                    break;
                                case 'smtp_test':
                                    badgeClass = 'bg-success';
                                    iconClass = 'fa-paper-plane';
                                    break;
                                case 'email_sent':
                                    badgeClass = 'bg-primary';
                                    iconClass = 'fa-envelope-open';
                                    break;
                                default:
                                    if (activity.details && activity.details.toLowerCase().includes('test mode')) {
                                        badgeClass = 'bg-warning';
                                        iconClass = 'fa-flask';
                                    } else if (activity.details && activity.details.toLowerCase().includes('admit card')) {
                                        badgeClass = 'bg-purple';
                                        iconClass = 'fa-id-card';
                                    }
                                    break;
                            }
                            
                            html += `
                            <tr>
                                <td><small>${activity.created_at}</small></td>
                                <td><span class="badge ${badgeClass}"><i class="fas ${iconClass} me-1"></i>${activity.activity_type}</span></td>
                                <td>${activity.user_name || 'System'}</td>
                                <td><small>${activity.details || '-'}</small></td>
                            </tr>
                        `;
                        });
                        
                        if (append) {
                            $('#activityLog').append(html);
                        } else {
                            $('#activityLog').html(html);
                        }
                        
                        // Update count display
                        const currentCount = activityOffset + response.data.length;
                        $('#activityCount').text(`Showing ${currentCount} of ${activityTotal}`);
                        
                        // Show/hide load more button
                        if (response.has_more) {
                            $('#loadMoreContainer').show();
                        } else {
                            $('#loadMoreContainer').hide();
                        }
                    } else if (!append) {
                        $('#activityLog').html(`
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                No recent activity found
                            </td>
                        </tr>
                    `);
                        $('#activityCount').text('No activities');
                        $('#loadMoreContainer').hide();
                    }
                }
            }
        });
    }
    
    // Load more button click
    $('#loadMoreActivity').click(function() {
        activityOffset += 15;
        $(this).html('<i class="fas fa-spinner fa-spin me-2"></i>Loading...');
        loadActivityLog(true);
        $(this).html('<i class="fas fa-angle-down me-2"></i>Load More');
    });

    // Show common issues dialog
    function showCommonIssues() {
        Swal.fire({
            title: 'Common SMTP Issues',
            html: `
            <div class="text-start">
                <h6>1. Authentication Failed</h6>
                <p class="small">Make sure you're using an App Password for Gmail, not your regular password.</p>
                
                <h6>2. Connection Timeout</h6>
                <p class="small">Check your firewall settings. SMTP ports (587/465) must be open.</p>
                
                <h6>3. SSL/TLS Errors</h6>
                <p class="small">Verify the correct port: TLS uses 587, SSL uses 465.</p>
                
                <h6>4. "Could not instantiate mail function"</h6>
                <p class="small">Enable SMTP mode and configure proper SMTP settings.</p>
            </div>
        `,
            width: 600
        });
    }

    // Show email providers info
    function showEmailProviders() {
        Swal.fire({
            title: 'SMTP Provider Settings',
            html: `
            <div class="text-start">
                <h6>Gmail</h6>
                <p class="small">Host: smtp.gmail.com | Port: 587 (TLS) | Requires App Password</p>
                
                <h6>Outlook / Office 365</h6>
                <p class="small">Host: smtp-mail.outlook.com | Port: 587 (TLS)</p>
                
                <h6>Yahoo Mail</h6>
                <p class="small">Host: smtp.mail.yahoo.com | Port: 587 (TLS) | Requires App Password</p>
                
                <h6>SendGrid</h6>
                <p class="small">Host: smtp.sendgrid.net | Port: 587 (TLS) | Username: apikey</p>
            </div>
        `,
            width: 600
        });
    }
</script>

<?php include '../includes/footer.php'; ?>