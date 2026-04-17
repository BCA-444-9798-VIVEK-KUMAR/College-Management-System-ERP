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

// Get parameters
$subject_id = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : 0;
$batch_id = isset($_GET['batch_id']) ? intval($_GET['batch_id']) : 0;
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Set page title and include header
$pageTitle = "View Attendance";
$basePath = "..";
include_once "../includes/header.php";
?>

<main class="main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-eye me-2"></i> View Attendance</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="dashboard.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div id="alert-container"></div>

    <!-- Class Information -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-info-circle"></i> Class Information
        </div>
        <div class="card-body">
            <div class="row" id="class-info">
                <div class="col-md-12 text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Details -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list"></i> Attendance Details</span>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-success" id="export-excel-btn">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
                <button class="btn btn-danger" id="export-pdf-btn">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="attendance-table">
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Student Name</th>
                            <th>Status</th>
                            <th>Marked At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="attendance-body">
                        <tr>
                            <td colspan="5" class="text-center">Loading attendance...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Summary -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Present</h5>
                            <h2 id="present-count">0</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h5 class="card-title">Absent</h5>
                            <h2 id="absent-count">0</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    $(document).ready(function() {
        var subjectId = <?php echo $subject_id; ?>;
        var batchId = <?php echo $batch_id; ?>;
        var date = "<?php echo $date; ?>";

        // Load class information
        loadClassInfo();

        // Load attendance data
        loadAttendance();

        // Function to load class information
        function loadClassInfo() {
            $.ajax({
                type: "GET",
                url: "../api/teacher/class_info.php",
                data: {
                    subject_id: subjectId,
                    batch_id: batchId,
                    date: date
                },
                dataType: "json",
                success: function(response) {
                    console.log("Class info response:", response);
                    if (response.success) {
                        var info = response.data;
                        var html = "<div class='row'>";
                        html += "<div class='col-md-3'><strong>Subject:</strong> " + info.subject + "</div>";
                        html += "<div class='col-md-3'><strong>Batch:</strong> " + info.batch + "</div>";
                        html += "<div class='col-md-3'><strong>Course:</strong> " + info.course + "</div>";
                        html += "<div class='col-md-3'><strong>Date:</strong> " + formatDate(date) + "</div>";
                        html += "</div>";
                        $("#class-info").html(html);
                    } else {
                        console.error("Error loading class info:", response.error);
                        $("#class-info").html("<div class='alert alert-danger'>" + (response.error || "Failed to load class information") + "</div>");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    console.error("Response Text:", xhr.responseText);
                    $("#class-info").html("<div class='alert alert-danger'>Failed to load class information. Please try again.</div>");
                }
            });
        }

        // Function to load attendance data
        function loadAttendance() {
            $.ajax({
                type: "GET",
                url: "../api/teacher/batch_students.php",
                data: {
                    batch_id: batchId,
                    subject_id: subjectId,
                    date: date
                },
                dataType: "json",
                success: function(response) {
                    console.log("Attendance response:", response);
                    if (response.success) {
                        var html = "";
                        var presentCount = 0;
                        var absentCount = 0;

                        if (response.data.length > 0) {
                            $.each(response.data, function(index, student) {
                                html += "<tr data-student-id='" + student.id + "' data-attendance-id='" + (student.attendance_id || '') + "'>";
                                html += "<td>" + student.roll_no + "</td>";
                                html += "<td>" + student.name + "</td>";

                                // Status with badge
                                var statusBadge = "";
                                var currentStatus = student.status || '';
                                if (currentStatus === "present") {
                                    statusBadge = "<span class='badge bg-success'>Present</span>";
                                    presentCount++;
                                } else if (currentStatus === "absent") {
                                    statusBadge = "<span class='badge bg-danger'>Absent</span>";
                                    absentCount++;
                                } else {
                                    statusBadge = "<span class='badge bg-secondary'>Not Marked</span>";
                                }
                                html += "<td class='status-cell'>" + statusBadge + "</td>";

                                // Marked at - only use recorded_at field
                                var markedAt = "-";
                                if (student.recorded_at) {
                                    markedAt = formatDateTime(student.recorded_at);
                                }
                                html += "<td class='marked-at-cell'>" + markedAt + "</td>";

                                // Edit action - only if attendance exists
                                if (student.attendance_id || currentStatus) {
                                    html += "<td>";
                                    html += "<div class='btn-group btn-group-sm' role='group'>";
                                    html += "<button type='button' class='btn btn-outline-success status-btn' data-status='present' " + (currentStatus === 'present' ? 'disabled' : '') + ">P</button>";
                                    html += "<button type='button' class='btn btn-outline-danger status-btn' data-status='absent' " + (currentStatus === 'absent' ? 'disabled' : '') + ">A</button>";
                                    html += "</div>";
                                    html += "</td>";
                                } else {
                                    html += "<td class='text-muted'>-</td>";
                                }

                                html += "</tr>";
                            });

                            // Update summary counts
                            $("#present-count").text(presentCount);
                            $("#absent-count").text(absentCount);
                        } else {
                            html = "<tr><td colspan='5' class='text-center'>No students found for this batch</td></tr>";
                        }

                        $("#attendance-body").html(html);
                    } else {
                        console.error("Error loading attendance:", response.error);
                        $("#attendance-body").html("<tr><td colspan='4' class='text-center text-danger'>Error: " + (response.error || "Failed to load attendance") + "</td></tr>");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    console.error("Response Text:", xhr.responseText);
                    $("#attendance-body").html("<tr><td colspan='5' class='text-center text-danger'>Failed to load attendance. Please try again.</td></tr>");
                }
            });
        }

        // Handle status button click
        $(document).on('click', '.status-btn', function() {
            var btn = $(this);
            var row = btn.closest('tr');
            var studentId = row.data('student-id');
            var newStatus = btn.data('status');

            // Confirm change
            if (!confirm('Are you sure you want to change the attendance status?')) {
                return;
            }

            // Disable all buttons in this row
            row.find('.status-btn').prop('disabled', true);

            // Save attendance
            $.ajax({
                type: "POST",
                url: "../api/teacher/save_attendance.php",
                data: {
                    subject_id: subjectId,
                    batch_id: batchId,
                    date: date,
                    attendance: JSON.stringify([{
                        student_id: studentId,
                        status: newStatus
                    }])
                },
                dataType: "json",
                success: function(response) {
                    console.log("Update response:", response);
                    if (response.success) {
                        // Update the status badge
                        var statusBadge = "";
                        if (newStatus === "present") {
                            statusBadge = "<span class='badge bg-success'>Present</span>";
                        } else if (newStatus === "absent") {
                            statusBadge = "<span class='badge bg-danger'>Absent</span>";
                        }
                        row.find('.status-cell').html(statusBadge);

                        // Update marked at time
                        row.find('.marked-at-cell').html(formatDateTime(new Date()));

                        // Enable all buttons except the current one
                        row.find('.status-btn').prop('disabled', false);
                        btn.prop('disabled', true);

                        // Reload to update counts
                        loadAttendance();

                        // Show success message
                        $("#alert-container").html(
                            "<div class='alert alert-success alert-dismissible fade show' role='alert'>" +
                            "Attendance updated successfully!" +
                            "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>" +
                            "</div>"
                        );

                        // Auto-hide alert after 3 seconds
                        setTimeout(function() {
                            $("#alert-container").find('.alert').alert('close');
                        }, 3000);
                    } else {
                        alert("Error: " + (response.error || "Failed to update attendance"));
                        row.find('.status-btn').prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    console.error("Response Text:", xhr.responseText);
                    alert("Failed to update attendance. Please try again.");
                    row.find('.status-btn').prop('disabled', false);
                }
            });
        });

        // Export to Excel
        $("#export-excel-btn").click(function() {
            window.location.href = "../api/teacher/export_attendance.php?subject_id=" + subjectId + "&batch_id=" + batchId + "&date=" + date + "&format=excel";
        });

        // Export to PDF / Print
        $("#export-pdf-btn").click(function() {
            exportToPDF();
        });

        // Function to export as PDF (using print)
        function exportToPDF() {
            // Create a new window for printing
            var printWindow = window.open('', '_blank');

            // Get class info
            var classInfoHtml = $("#class-info").html();

            // Get table data
            var tableHtml = $("#attendance-table").clone();
            tableHtml.find('.status-btn').closest('td').remove(); // Remove action column
            tableHtml.find('th:last-child').remove(); // Remove action header

            // Get summary data
            var presentCount = $("#present-count").text();
            var absentCount = $("#absent-count").text();

            // Build print HTML
            var printHtml = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Attendance Report - ${formatDate(date)}</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 20px;
                }
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                }
                .header h1 {
                    margin: 0;
                    font-size: 24px;
                }
                .header h2 {
                    margin: 5px 0;
                    font-size: 18px;
                    color: #666;
                }
                .class-info {
                    margin: 20px 0;
                    padding: 15px;
                    background: #f8f9fa;
                    border: 1px solid #dee2e6;
                }
                .class-info strong {
                    font-weight: bold;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                }
                table th, table td {
                    border: 1px solid #dee2e6;
                    padding: 8px;
                    text-align: left;
                }
                table th {
                    background-color: #f8f9fa;
                    font-weight: bold;
                }
                .badge {
                    padding: 4px 8px;
                    border-radius: 4px;
                    font-size: 12px;
                    font-weight: bold;
                }
                .bg-success {
                    background-color: #28a745;
                    color: white;
                }
                .bg-danger {
                    background-color: #dc3545;
                    color: white;
                }
                .bg-warning {
                    background-color: #ffc107;
                    color: black;
                }
                .bg-secondary {
                    background-color: #6c757d;
                    color: white;
                }
                .summary {
                    margin-top: 30px;
                    display: flex;
                    justify-content: space-around;
                }
                .summary-card {
                    text-align: center;
                    padding: 15px;
                    border: 1px solid #dee2e6;
                    border-radius: 5px;
                    min-width: 150px;
                }
                .summary-card h3 {
                    margin: 0 0 10px 0;
                    font-size: 16px;
                }
                .summary-card .count {
                    font-size: 32px;
                    font-weight: bold;
                }
                .footer {
                    margin-top: 50px;
                    text-align: center;
                    font-size: 12px;
                    color: #666;
                }
                @media print {
                    body {
                        margin: 0;
                    }
                    .no-print {
                        display: none;
                    }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>College Attendance Report</h1>
                <h2>Cimage College</h2>
            </div>
            
            <div class="class-info">
                ${classInfoHtml}
            </div>
            
            <table>
                ${tableHtml.html()}
            </table>
            
            <div class="summary">
                <div class="summary-card">
                    <h3>Present</h3>
                    <div class="count" style="color: #28a745;">${presentCount}</div>
                </div>
                <div class="summary-card">
                    <h3>Absent</h3>
                    <div class="count" style="color: #dc3545;">${absentCount}</div>
                </div>
            </div>
            
            <div class="footer">
                <p>Generated on ${new Date().toLocaleString()}</p>
            </div>
            
            <script type="text/javascript">
                window.onload = function() {
                    window.print();
                    // Close window after printing
                    window.onafterprint = function() {
                        window.close();
                    };
                };
            <\/script>
        </body>
        </html>
        `;

            printWindow.document.write(printHtml);
            printWindow.document.close();
        }

        // Function to format date
        function formatDate(dateString) {
            var date = new Date(dateString);
            return ("0" + date.getDate()).slice(-2) + "-" + ("0" + (date.getMonth() + 1)).slice(-2) + "-" + date.getFullYear();
        }

        // Function to format date time
        function formatDateTime(dateTimeString) {
            var date = new Date(dateTimeString);
            return ("0" + date.getDate()).slice(-2) + "-" + ("0" + (date.getMonth() + 1)).slice(-2) + "-" + date.getFullYear() + " " +
                ("0" + date.getHours()).slice(-2) + ":" + ("0" + date.getMinutes()).slice(-2);
        }
    });
</script>

<?php
include_once "../includes/footer.php";
?>