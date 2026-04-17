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

// Check if user is logged in and is teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.php");
    exit;
}

// Set page title and include header
$pageTitle = "Email Alert Settings";
$basePath = "..";
include_once "../includes/header.php";
?>

<!-- SweetAlert2 CSS & JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<main class="main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-envelope"></i> Email Alert Settings</h1>
    </div>

    <div id="alert-container"></div>

    <!-- Alert Mode Selection -->
    <div class="card mb-4">
        <div class="card-header bg-primary text">
            <i class="fas fa-cog"></i> Alert Mode Configuration
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="enableAlerts" checked>
                        <label class="form-check-label" for="enableAlerts">
                            <strong>Enable Email Alerts</strong>
                        </label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Alert Mode:</strong></label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="alertMode" id="manualMode" value="manual" checked>
                            <label class="form-check-label" for="manualMode">
                                <i class="fas fa-hand-pointer text-primary"></i> <strong>Manual Mode</strong>
                                <br><small class="text-muted">Send reports manually from Student Reports page</small>
                            </label>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="radio" name="alertMode" id="automaticMode" value="automatic">
                            <label class="form-check-label" for="automaticMode">
                                <i class="fas fa-robot text-success"></i> <strong>Automatic Mode</strong>
                                <br><small class="text-muted">Auto-send alerts based on attendance criteria</small>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>How it works:</strong>
                        <ul class="mb-0 mt-2">
                            <li><strong>Manual:</strong> You choose when to send reports from Student Reports page</li>
                            <li><strong>Automatic:</strong> System sends alerts based on attendance percentage criteria below</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Manual Mode - Send Bulk Emails -->
    <div class="card mb-4" id="manualSendCard">
        <div class="card-header bg-success text">
            <i class="fas fa-paper-plane"></i> Send Bulk Email Reports (Manual Mode)
        </div>
        <div class="card-body">
            <p class="text-muted">Send attendance reports to multiple students based on attendance criteria:</p>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="bulkCourse" class="form-label">Course <span class="text-danger">*</span></label>
                    <select class="form-select" id="bulkCourse" required>
                        <option value="">Select Course</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="bulkBatch" class="form-label">Batch <span class="text-danger">*</span></label>
                    <select class="form-select" id="bulkBatch" required>
                        <option value="">Select Batch</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="bulkMonth" class="form-label">Month</label>
                    <select class="form-select" id="bulkMonth">
                        <option value="">All Months</option>
                        <option value="01">January</option>
                        <option value="02">February</option>
                        <option value="03">March</option>
                        <option value="04">April</option>
                        <option value="05">May</option>
                        <option value="06">June</option>
                        <option value="07">July</option>
                        <option value="08">August</option>
                        <option value="09">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="bulkYear" class="form-label">Year</label>
                    <select class="form-select" id="bulkYear">
                        <?php
                        $currentYear = date('Y');
                        for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                            echo "<option value='$y'" . ($y == $currentYear ? " selected" : "") . ">$y</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="bulkThreshold" class="form-label">Attendance Threshold (%)</label>
                    <div class="input-group">
                        <span class="input-group-text">Below</span>
                        <input type="number" class="form-control" id="bulkThreshold" value="75" min="0" max="100">
                        <span class="input-group-text">%</span>
                    </div>
                    <small class="text-muted">Send emails to students below this percentage</small>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success btn-lg" id="sendBulkEmailBtn">
                            <i class="fas fa-paper-plane"></i> Send Emails to All Matching Students
                        </button>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle"></i> This will send attendance reports with PDF attachments to all students
                        in the selected batch whose attendance is below the threshold.
                    </small>
                </div>
            </div>

            <!-- Email Destination Control -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="alert alert-warning" id="testModeWarning" style="display: none;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Test Mode Active:</strong> All emails will be sent to the test email address.
                        Check the box below to send to real student emails.
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="sendToRealStudents" checked>
                        <label class="form-check-label" for="sendToRealStudents">
                            <strong><i class="fas fa-user-check"></i> Send to Real Student Email Addresses</strong>
                        </label>
                        <small class="d-block text-muted">
                            When unchecked, all emails will go to the configured test email address for safety.
                        </small>
                    </div>
                </div>
            </div>

            <!-- Preview Section -->
            <div id="bulkPreview" class="mt-3" style="display: none;">
                <hr>
                <h5><i class="fas fa-users"></i> Students to Receive Email:</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Roll No</th>
                                <th>Name</th>
                                <th>Attendance %</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="bulkPreviewBody">
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-primary" id="confirmSendBulk">
                    <i class="fas fa-check"></i> Confirm & Send All
                </button>
                <button type="button" class="btn btn-secondary" id="cancelBulk">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- Automatic Alert Criteria -->
    <div class="card mb-4" id="criteriaCard">
        <div class="card-header bg-warning text-dark">
            <i class="fas fa-exclamation-triangle"></i> Automatic Alert Criteria
        </div>
        <div class="card-body">
            <p class="text-muted">Configure attendance percentage thresholds for automatic email alerts:</p>

            <div class="row">
                <!-- Critical Alert -->
                <div class="col-md-4 mb-3">
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-red">
                            <i class="fas fa-exclamation-circle"></i> Critical Alert
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="criticalThreshold" class="form-label">Threshold (%)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Below</span>
                                    <input type="number" class="form-control" id="criticalThreshold" value="50" min="0" max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="criticalEnabled" checked>
                                <label class="form-check-label" for="criticalEnabled">
                                    Enable critical alerts
                                </label>
                            </div>
                            <small class="text-muted">Urgent attention required</small>
                        </div>
                    </div>
                </div>

                <!-- Warning Alert -->
                <div class="col-md-4 mb-3">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-yellow">
                            <i class="fas fa-exclamation-triangle"></i> Warning Alert
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="warningThreshold" class="form-label">Threshold (%)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Below</span>
                                    <input type="number" class="form-control" id="warningThreshold" value="75" min="0" max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="warningEnabled" checked>
                                <label class="form-check-label" for="warningEnabled">
                                    Enable warning alerts
                                </label>
                            </div>
                            <small class="text-muted">Needs improvement</small>
                        </div>
                    </div>
                </div>

                <!-- Info Alert -->
                <div class="col-md-4 mb-3">
                    <div class="card border-info">
                        <div class="card-header bg-info text-blue">
                            <i class="fas fa-info-circle"></i> Info Alert
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="infoThreshold" class="form-label">Threshold (%)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Below</span>
                                    <input type="number" class="form-control" id="infoThreshold" value="85" min="0" max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="infoEnabled">
                                <label class="form-check-label" for="infoEnabled">
                                    Enable info alerts
                                </label>
                            </div>
                            <small class="text-muted">Informational notice</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label for="alertFrequency" class="form-label"><strong>Alert Frequency</strong></label>
                        <select class="form-select" id="alertFrequency">
                            <option value="daily">Daily</option>
                            <option value="weekly" selected>Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Template Preview -->
    <div class="card mb-4">
        <div class="card-header bg-secondary text">
            <i class="fas fa-envelope-open-text"></i> Email Template Preview
        </div>
        <div class="card-body">
            <div class="border p-4 bg-light">
                <h4 class="text-center">Cimage College - Attendance Report</h4>
                <hr>
                <p><strong>Dear Student Name,</strong></p>
                <p>This is your attendance report for the period: <strong>[Date Range]</strong></p>

                <table class="table table-bordered bg-white">
                    <tr>
                        <th>Course:</th>
                        <td>[Course Name]</td>
                        <th>Batch:</th>
                        <td>[Batch Name]</td>
                    </tr>
                    <tr>
                        <th>Roll No:</th>
                        <td>[Roll Number]</td>
                        <th>Attendance:</th>
                        <td><span class="badge bg-warning">[XX]%</span></td>
                    </tr>
                </table>

                <div class="alert alert-warning">
                    <strong><i class="fas fa-exclamation-triangle"></i> Alert:</strong> Your attendance is below the required threshold.
                </div>

                <p><strong>Attendance Summary:</strong></p>
                <ul>
                    <li>Present: <strong>[X]</strong> days</li>
                    <li>Absent: <strong>[X]</strong> days</li>
                    <li>Total: <strong>[X]</strong> days</li>
                </ul>

                <p class="mt-3"><small class="text-muted">Detailed PDF report is attached with this email.</small></p>

                <hr>
                <p class="text-center text-muted">
                    <small>This is an automated email from Cimage College Attendance System.<br>
                        Please contact your teacher if you have any questions.</small>
                </p>
            </div>
        </div>
    </div>

    <!-- Save Settings Button -->
    <div class="card mb-4">
        <div class="card-body text-center">
            <button type="button" class="btn btn-primary btn-lg" id="saveSettingsBtn">
                <i class="fas fa-save"></i> Save Settings
            </button>
            <button type="button" class="btn btn-secondary btn-lg" id="resetSettingsBtn">
                <i class="fas fa-undo"></i> Reset to Defaults
            </button>
        </div>
    </div>
</main>
</div>
</div>

<script>
    $(document).ready(function() {
        let allBatchesData = [];
        let bulkEmailStudents = [];

        // Rate limiting configuration (loaded from Admin Email Settings)
        let EMAIL_BATCH_SIZE = 10; // default: 10 emails per batch
        let BATCH_DELAY_MS = 5000; // default: 5 seconds between batches
        let EMAIL_DELAY_MS = 500; // default: 500ms between emails
        let SYSTEM_TEST_MODE = 0; // default: off
        let SYSTEM_TEST_EMAIL = '';

        // Load system email settings (rate limits, test mode) then load teacher settings and batches
        loadSystemEmailSettings();

        // Load saved teacher alert settings
        loadSettings();

        // Load batches for bulk email
        loadBatchesForBulk();
        // Fetch Admin SMTP/rate limit settings for use in bulk send UI
        function loadSystemEmailSettings() {
            $.ajax({
                url: '../api/teacher/get_smtp_status.php',
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res && res.success && res.data) {
                        const s = res.data;
                        EMAIL_BATCH_SIZE = parseInt(s.batch_size) || 10;
                        BATCH_DELAY_MS = (parseInt(s.batch_delay) || 5) * 1000; // seconds -> ms
                        EMAIL_DELAY_MS = parseInt(s.email_delay) || 500;
                        SYSTEM_TEST_MODE = parseInt(s.use_test_email) || 0;
                        SYSTEM_TEST_EMAIL = s.test_email || '';

                        // Toggle Test Mode warning and default override checkbox
                        if (SYSTEM_TEST_MODE === 1) {
                            $('#testModeWarning').show().find('strong').append(SYSTEM_TEST_EMAIL ? ` (${SYSTEM_TEST_EMAIL})` : '');
                            $('#sendToRealStudents').prop('checked', false);
                        } else {
                            $('#testModeWarning').hide();
                            $('#sendToRealStudents').prop('checked', true);
                        }
                    }
                }
            });
        }


        // Toggle criteria card based on mode
        $('input[name="alertMode"]').change(function() {
            if ($(this).val() === 'automatic') {
                $('#criteriaCard').slideDown();
                $('#manualSendCard').slideUp();
            } else {
                $('#criteriaCard').slideUp();
                $('#manualSendCard').slideDown();
            }
        });

        // Enable/disable alerts
        $('#enableAlerts').change(function() {
            if ($(this).is(':checked')) {
                $('input[name="alertMode"]').prop('disabled', false);
                $('#criteriaCard input, #criteriaCard select').prop('disabled', false);
                $('#manualSendCard input, #manualSendCard select, #manualSendCard button').prop('disabled', false);
            } else {
                $('input[name="alertMode"]').prop('disabled', true);
                $('#criteriaCard input, #criteriaCard select').prop('disabled', true);
                $('#manualSendCard input, #manualSendCard select, #manualSendCard button').prop('disabled', true);
            }
        });
        // Handle bulk course change
        $('#bulkCourse').change(function() {
            filterBulkBatchesByCourse($(this).val());
        });

        // Send bulk email button
        $('#sendBulkEmailBtn').click(function() {
            var course = $('#bulkCourse').val();
            var batch = $('#bulkBatch').val();
            var threshold = $('#bulkThreshold').val();
            var month = $('#bulkMonth').val();
            var year = $('#bulkYear').val();

            if (!course || !batch) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Information',
                    text: 'Please select course and batch',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            // Calculate date range
            var fromDate, toDate;
            if (month && year) {
                fromDate = year + "-" + month + "-01";
                var lastDay = new Date(year, parseInt(month), 0).getDate();
                toDate = year + "-" + month + "-" + ("0" + lastDay).slice(-2);
            } else if (year && !month) {
                fromDate = year + "-01-01";
                toDate = year + "-12-31";
            } else {
                toDate = new Date().toISOString().split('T')[0];
                var thirtyDaysAgo = new Date();
                thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
                fromDate = thirtyDaysAgo.toISOString().split('T')[0];
            }

            // Show loading
            Swal.fire({
                title: 'Loading Students...',
                text: 'Please wait while we fetch matching students',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Get students matching criteria
            $.ajax({
                url: '../api/teacher/get_bulk_email_students.php',
                method: 'POST',
                dataType: 'json',
                data: JSON.stringify({
                    batch_id: batch,
                    threshold: threshold,
                    from_date: fromDate,
                    to_date: toDate
                }),
                contentType: 'application/json',
                success: function(response) {
                    Swal.close();

                    if (response.success && response.data && response.data.length > 0) {
                        bulkEmailStudents = response.data;
                        showBulkPreview(response.data, fromDate, toDate);
                    } else {
                        Swal.fire({
                            icon: 'info',
                            title: 'No Students Found',
                            text: response.message || 'No students found matching the criteria',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load students. Please try again.',
                        confirmButtonColor: '#dc3545'
                    });
                }
            });
        });

        // Confirm send bulk
        $('#confirmSendBulk').click(function() {
            if (bulkEmailStudents.length === 0) return;

            var sendToReal = $('#sendToRealStudents').is(':checked');
            var destination = sendToReal ? 'real student email addresses' : 'test email address';

            Swal.fire({
                title: 'Confirm Send',
                html: `Send attendance reports to <strong>${bulkEmailStudents.length}</strong> student(s)?<br>` +
                    `<small class="text-muted">Destination: ${destination}</small><br>` +
                    `<small class="text-muted">This may take a few minutes</small>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Send All',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    sendBulkEmails();
                }
            });
        });

        // Cancel bulk
        $('#cancelBulk').click(function() {
            $('#bulkPreview').slideUp();
            bulkEmailStudents = [];
        });

        // Save settings
        $('#saveSettingsBtn').click(function() {
            var settings = {
                enabled: $('#enableAlerts').is(':checked'),
                mode: $('input[name="alertMode"]:checked').val(),
                critical: {
                    threshold: $('#criticalThreshold').val(),
                    enabled: $('#criticalEnabled').is(':checked')
                },
                warning: {
                    threshold: $('#warningThreshold').val(),
                    enabled: $('#warningEnabled').is(':checked')
                },
                info: {
                    threshold: $('#infoThreshold').val(),
                    enabled: $('#infoEnabled').is(':checked')
                },
                frequency: $('#alertFrequency').val()
            };

            $.ajax({
                type: "POST",
                url: "../api/teacher/save_email_settings.php",
                data: JSON.stringify(settings),
                contentType: "application/json",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        showAlert('success', 'Settings saved successfully!');
                    } else {
                        showAlert('danger', 'Error: ' + response.error);
                    }
                },
                error: function() {
                    showAlert('danger', 'Failed to save settings. Please try again.');
                }
            });
        });

        // Reset settings
        $('#resetSettingsBtn').click(function() {
            if (confirm('Are you sure you want to reset to default settings?')) {
                $('#enableAlerts').prop('checked', true);
                $('#manualMode').prop('checked', true);
                $('#criticalThreshold').val(50);
                $('#criticalEnabled').prop('checked', true);
                $('#warningThreshold').val(75);
                $('#warningEnabled').prop('checked', true);
                $('#infoThreshold').val(85);
                $('#infoEnabled').prop('checked', false);
                $('#alertFrequency').val('weekly');
                $('#criteriaCard').slideUp();
                $('#manualSendCard').slideDown();
                showAlert('info', 'Settings reset to defaults. Click Save to apply.');
            }
        });

        // Function to load batches for bulk
        function loadBatchesForBulk() {
            $.ajax({
                type: "GET",
                url: "../api/teacher/teacher_batches.php",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        allBatchesData = response.data;
                        loadBulkCourses();
                    }
                }
            });
        }

        // Function to load courses for bulk
        function loadBulkCourses() {
            const uniqueCourses = {};
            allBatchesData.forEach(function(batch) {
                if (batch.course && !uniqueCourses[batch.course]) {
                    uniqueCourses[batch.course] = true;
                }
            });

            let coursesHtml = "<option value=''>Select Course</option>";
            Object.keys(uniqueCourses).sort().forEach(function(course) {
                coursesHtml += "<option value='" + course + "'>" + course + "</option>";
            });

            $("#bulkCourse").html(coursesHtml);
        }

        // Function to filter batches by course for bulk
        function filterBulkBatchesByCourse(courseName) {
            if (!courseName) {
                $("#bulkBatch").html("<option value=''>Select Batch</option>");
                return;
            }

            const filteredBatches = allBatchesData.filter(function(batch) {
                return batch.course === courseName;
            });

            let batchesHtml = "<option value=''>Select Batch</option>";
            filteredBatches.forEach(function(batch) {
                batchesHtml += "<option value='" + batch.batch_id + "'>" + batch.batch_name + "</option>";
            });

            $("#bulkBatch").html(batchesHtml);
        }

        // Function to show bulk preview
        function showBulkPreview(students, fromDate, toDate) {
            let html = '';
            students.forEach(function(student) {
                let statusBadge = '';
                let percentage = parseFloat(student.attendance_percentage);

                if (percentage < 50) {
                    statusBadge = '<span class="badge bg-danger">Critical</span>';
                } else if (percentage < 75) {
                    statusBadge = '<span class="badge bg-warning">Warning</span>';
                } else {
                    statusBadge = '<span class="badge bg-info">Info</span>';
                }

                html += `
                <tr>
                    <td>${student.roll_no}</td>
                    <td>${student.name}</td>
                    <td><strong>${student.attendance_percentage}%</strong></td>
                    <td>${statusBadge}</td>
                </tr>
            `;
            });

            $('#bulkPreviewBody').html(html);
            $('#bulkPreview').slideDown();
        }

        // Function to send bulk emails
        function sendBulkEmails() {
            let totalStudents = bulkEmailStudents.length;
            let completed = 0;
            let failed = 0;
            let month = $('#bulkMonth').val();
            let year = $('#bulkYear').val();

            // Calculate date range
            var fromDate, toDate;
            if (month && year) {
                fromDate = year + "-" + month + "-01";
                var lastDay = new Date(year, parseInt(month), 0).getDate();
                toDate = year + "-" + month + "-" + ("0" + lastDay).slice(-2);
            } else if (year && !month) {
                fromDate = year + "-01-01";
                toDate = year + "-12-31";
            } else {
                toDate = new Date().toISOString().split('T')[0];
                var thirtyDaysAgo = new Date();
                thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
                fromDate = thirtyDaysAgo.toISOString().split('T')[0];
            }

            // Show progress with enhanced UI
            Swal.fire({
                title: 'Sending Emails...',
                html: `<div class="progress mb-3">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" 
                         style="width: 0%" id="emailProgress">0%</div>
                   </div>
                   <div class="row text-center">
                       <div class="col-4">
                           <h5 id="sentCount" class="text-success">0</h5>
                           <small>Sent</small>
                       </div>
                       <div class="col-4">
                           <h5 id="failedCount" class="text-danger">0</h5>
                           <small>Failed</small>
                       </div>
                       <div class="col-4">
                           <h5>${totalStudents}</h5>
                           <small>Total</small>
                       </div>
                   </div>
                   <p class="mt-3 text-muted small">
                       <i class="fas fa-info-circle"></i> Sending with rate limiting (${EMAIL_BATCH_SIZE} emails per batch, ${BATCH_DELAY_MS/1000}s pause between batches, ${EMAIL_DELAY_MS}ms between emails)
                   </p>`,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false
            });

            // Send emails sequentially with rate limiting
            sendEmailSequentially(0);

            function sendEmailSequentially(index) {
                if (index >= bulkEmailStudents.length) {
                    // All emails sent
                    Swal.fire({
                        icon: completed > 0 ? 'success' : 'error',
                        title: completed > 0 ? 'Emails Sent!' : 'Send Failed',
                        html: `<div class="row text-center mb-3">
                               <div class="col-6">
                                   <h3 class="text-success">${completed}</h3>
                                   <p>Successful</p>
                               </div>
                               <div class="col-6">
                                   <h3 class="text-danger">${failed}</h3>
                                   <p>Failed</p>
                               </div>
                           </div>
                           <p class="text-muted small">Total processed: ${totalStudents} student(s)</p>`,
                        confirmButtonColor: '#28a745'
                    });
                    $('#bulkPreview').slideUp();
                    bulkEmailStudents = [];
                    return;
                }

                let student = bulkEmailStudents[index];

                // Check if we need to pause for batch rate limiting
                let shouldPause = (index > 0) && (index % EMAIL_BATCH_SIZE === 0);
                let delay = shouldPause ? BATCH_DELAY_MS : EMAIL_DELAY_MS;

                if (shouldPause) {
                    // Update UI to show pausing
                    Swal.update({
                        html: Swal.getHtmlContainer().innerHTML.replace(
                            'Sending with rate limiting',
                            `<span class="text-warning">⏸ Pausing for ${BATCH_DELAY_MS/1000} seconds (batch rate limit)...</span>`
                        )
                    });
                }

                setTimeout(function() {
                    $.ajax({
                        url: '../api/teacher/send_attendance_email.php',
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            student_id: student.student_id,
                            from_date: fromDate,
                            to_date: toDate,
                            override_test: $('#sendToRealStudents').is(':checked') ? 1 : 0
                        },
                        success: function(response) {
                            if (response.success) {
                                completed++;
                                $('#sentCount').text(completed);
                            } else {
                                failed++;
                                $('#failedCount').text(failed);
                                console.error('Email failed for student:', student.name, response.error);
                            }
                        },
                        error: function(xhr, status, error) {
                            failed++;
                            $('#failedCount').text(failed);
                            console.error('AJAX error for student:', student.name, error);
                        },
                        complete: function() {
                            // Update progress bar
                            let progress = ((index + 1) / totalStudents * 100).toFixed(0);
                            $('#emailProgress').css('width', progress + '%').text(progress + '%');

                            // Continue to next email
                            sendEmailSequentially(index + 1);
                        }
                    });
                }, delay);
            }
        }

        // Load settings
        function loadSettings() {
            $.ajax({
                type: "GET",
                url: "../api/teacher/get_email_settings.php",
                dataType: "json",
                success: function(response) {
                    if (response.success && response.data) {
                        var settings = response.data;

                        // Handle both flat and nested structure
                        var mode = settings.mode || settings.alert_mode || 'manual';
                        var criticalThreshold = settings.critical?.threshold || settings.critical_threshold || 50;
                        var criticalEnabled = settings.critical?.enabled !== undefined ? settings.critical.enabled :
                            (settings.critical_enabled !== undefined ? settings.critical_enabled : true);
                        var warningThreshold = settings.warning?.threshold || settings.warning_threshold || 75;
                        var warningEnabled = settings.warning?.enabled !== undefined ? settings.warning.enabled :
                            (settings.warning_enabled !== undefined ? settings.warning_enabled : true);
                        var infoThreshold = settings.info?.threshold || settings.info_threshold || 85;
                        var infoEnabled = settings.info?.enabled !== undefined ? settings.info.enabled :
                            (settings.info_enabled !== undefined ? settings.info_enabled : false);

                        $('#enableAlerts').prop('checked', settings.enabled);
                        $('input[name="alertMode"][value="' + mode + '"]').prop('checked', true);
                        $('#criticalThreshold').val(criticalThreshold);
                        $('#criticalEnabled').prop('checked', criticalEnabled);
                        $('#warningThreshold').val(warningThreshold);
                        $('#warningEnabled').prop('checked', warningEnabled);
                        $('#infoThreshold').val(infoThreshold);
                        $('#infoEnabled').prop('checked', infoEnabled);
                        $('#alertFrequency').val(settings.frequency || 'daily');

                        // Show/hide cards based on mode
                        if (mode === 'automatic') {
                            $('#criteriaCard').show();
                            $('#manualSendCard').hide();
                        } else {
                            $('#criteriaCard').hide();
                            $('#manualSendCard').show();
                        }

                        // Trigger the mode change to update UI state
                        $('input[name="alertMode"]:checked').trigger('change');
                    }
                }
            });
        }

        // Show alert
        function showAlert(type, message) {
            var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                message +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                '</div>';
            $('#alert-container').html(alertHtml);

            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);
        }
    });
</script>

<?php
include_once "../includes/footer.php";
?>