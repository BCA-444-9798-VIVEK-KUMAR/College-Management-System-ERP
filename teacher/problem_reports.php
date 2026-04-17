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
    // Redirect to login page
    header("Location: ../auth/login.php");
    exit;
}

// Set page title and include header
$pageTitle = "Problem Reports";
$basePath = "..";
include_once "../includes/header.php";
?>

<main class="main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-exclamation-triangle me-2"></i> Student Problem Reports</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="refresh-btn">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter"></i> Filter Reports
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label for="status-filter" class="form-label">Status</label>
                    <select class="form-select" id="status-filter">
                        <option value="">All Status</option>
                        <option value="open">Open</option>
                        <option value="resolved">Resolved</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="subject-filter" class="form-label">Subject</label>
                    <select class="form-select" id="subject-filter">
                        <option value="">All Subjects</option>
                        <!-- Will be populated via AJAX -->
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Problem Reports List -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-list"></i> All Problem Reports
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="reports-table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Reported Date</th>
                            <th>Problem Date</th>
                            <th>Roll No</th>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Batch</th>
                            <th>Subject</th>
                            <th>Problem Type</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="reports-body">
                        <!-- Reports data will be loaded here via AJAX -->
                        <tr>
                            <td colspan="12" class="text-center">Loading reports...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
</div>
</div>

<!-- View Report Modal -->
<div class="modal fade" id="viewReportModal" tabindex="-1" aria-labelledby="viewReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewReportModalLabel">Problem Report Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Roll No:</label>
                            <p id="modal-rollno"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Student:</label>
                            <p id="modal-student"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Course:</label>
                            <p id="modal-course"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Batch:</label>
                            <p id="modal-batch"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Subject:</label>
                            <p id="modal-subject"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Problem Type:</label>
                            <p id="modal-problem-type"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Problem Date:</label>
                            <p id="modal-problem-date"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Reported Date:</label>
                            <p id="modal-date"></p>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Message:</label>
                    <p id="modal-message" class="border p-2 bg-light"></p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Current Status:</label>
                    <p id="modal-status"></p>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> <strong>Note:</strong> As the assigned teacher for this subject, you have the authority to resolve or reopen this issue.
                </div>
                <div class="mb-3">
                    <label for="modal-status-select" class="form-label fw-bold">Change Status:</label>
                    <select class="form-select" id="modal-status-select">
                        <option value="open">Open</option>
                        <option value="resolved">Resolved</option>
                    </select>
                </div>
                <input type="hidden" id="modal-report-id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="update-status-btn">Update Status</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Load reports on page load
        loadReports();

        // Load subjects for filter
        loadSubjectsFilter();

        // Refresh button
        $("#refresh-btn").click(function() {
            loadReports();
        });

        // Status filter change
        $("#status-filter, #subject-filter").change(function() {
            loadReports();
        });

        // Function to load subjects for filter
        function loadSubjectsFilter() {
            // Load teacher's assigned subjects from problem reports
            $.ajax({
                type: "GET",
                url: "../api/teacher/problem_reports.php",
                data: {
                    limit: 'false'
                },
                dataType: "json",
                success: function(response) {
                    console.log("Loading subjects from reports:", response);
                    if (response.success) {
                        // Extract unique subjects from problem reports
                        var uniqueSubjects = {};
                        var subjectsArray = [];

                        $.each(response.data, function(i, report) {
                            if (report.subject_id && !uniqueSubjects[report.subject_id]) {
                                uniqueSubjects[report.subject_id] = true;
                                subjectsArray.push({
                                    id: report.subject_id,
                                    name: report.subject
                                });
                            }
                        });

                        // Sort by name
                        subjectsArray.sort(function(a, b) {
                            return a.name.localeCompare(b.name);
                        });

                        var options = '<option value="">All Subjects</option>';
                        $.each(subjectsArray, function(i, subject) {
                            options += '<option value="' + subject.id + '">' + subject.name + '</option>';
                        });
                        $("#subject-filter").html(options);

                        console.log("Subject filter loaded with", subjectsArray.length, "subjects");
                    } else {
                        console.error("Error loading subjects:", response.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error loading subjects:", error);
                    console.error("Response Text:", xhr.responseText);
                }
            });
        }

        // Function to load reports
        function loadReports() {
            var statusFilter = $("#status-filter").val();
            var subjectFilter = $("#subject-filter").val();

            console.log("Loading reports with filters - Status:", statusFilter, "Subject:", subjectFilter);

            $.ajax({
                type: "GET",
                url: "../api/teacher/problem_reports.php",
                data: {
                    status: statusFilter,
                    subject_id: subjectFilter,
                    limit: 'false'
                },
                dataType: "json",
                success: function(response) {
                    console.log("Problem reports response:", response);
                    if (response.success) {
                        displayReports(response.data);
                    } else {
                        $("#reports-body").html('<tr><td colspan="12" class="text-center text-danger">Error: ' + (response.error || 'Unknown error') + '</td></tr>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    console.error("Response:", xhr.responseText);
                    $("#reports-body").html('<tr><td colspan="12" class="text-center text-danger">Error loading reports. Check console.</td></tr>');
                }
            });
        }

        // Function to display reports
        function displayReports(reports) {
            var html = "";
            if (reports.length > 0) {
                $.each(reports, function(index, report) {
                    var statusBadge = "";
                    if (report.status === 'open') {
                        statusBadge = '<span class="badge bg-danger">Open</span>';
                    } else if (report.status === 'resolved') {
                        statusBadge = '<span class="badge bg-success">Resolved</span>';
                    } else {
                        statusBadge = '<span class="badge bg-secondary">' + report.status + '</span>';
                    }

                    // Format problem type for display
                    var problemTypeDisplay = formatProblemType(report.problem_type);

                    var truncatedMessage = report.message.length > 30 ?
                        report.message.substring(0, 30) + '...' :
                        report.message;

                    html += '<tr>';
                    html += '<td>' + report.id + '</td>';
                    html += '<td>' + report.created_at + '</td>';
                    html += '<td>' + (report.problem_date || 'N/A') + '</td>';
                    html += '<td>' + report.roll_no + '</td>';
                    html += '<td>' + report.student_name + '</td>';
                    html += '<td>' + report.course_name + '</td>';
                    html += '<td>' + report.batch_name + '</td>';
                    html += '<td>' + report.subject + '</td>';
                    html += '<td><small>' + problemTypeDisplay + '</small></td>';
                    html += '<td>' + truncatedMessage + '</td>';
                    html += '<td>' + statusBadge + '</td>';
                    html += '<td>';
                    html += '<button class="btn btn-sm btn-info view-report" data-id="' + report.id + '" data-student="' + report.student_name + '" data-rollno="' + report.roll_no + '" data-course="' + report.course_name + '" data-batch="' + report.batch_name + '" data-subject="' + report.subject + '" data-problemtype="' + report.problem_type + '" data-problemdate="' + (report.problem_date || 'N/A') + '" data-date="' + report.created_at + '" data-message="' + report.message + '" data-status="' + report.status + '"><i class="fas fa-eye"></i> View</button>';
                    html += '</td>';
                    html += '</tr>';
                });
            } else {
                html = '<tr><td colspan="12" class="text-center">No problem reports found</td></tr>';
            }
            $("#reports-body").html(html);
        }

        // Function to format problem type
        function formatProblemType(type) {
            if (!type) return 'N/A';

            const typeMap = {
                'attendance_not_marked': 'Attendance Not Marked',
                'marked_absent_by_mistake': 'Marked Absent by Mistake',
                'wrong_subject_attendance': 'Wrong Subject Attendance',
                'other': 'Other Issue'
            };

            return typeMap[type] || type;
        }

        // View report button click
        $(document).on("click", ".view-report", function() {
            var id = $(this).data("id");
            var rollno = $(this).data("rollno");
            var student = $(this).data("student");
            var course = $(this).data("course");
            var batch = $(this).data("batch");
            var subject = $(this).data("subject");
            var problemType = $(this).data("problemtype");
            var problemDate = $(this).data("problemdate");
            var date = $(this).data("date");
            var message = $(this).data("message");
            var status = $(this).data("status");

            $("#modal-report-id").val(id);
            $("#modal-rollno").text(rollno);
            $("#modal-student").text(student);
            $("#modal-course").text(course);
            $("#modal-batch").text(batch);
            $("#modal-subject").text(subject);
            $("#modal-problem-type").text(formatProblemType(problemType));
            $("#modal-problem-date").text(problemDate);
            $("#modal-date").text(date);
            $("#modal-message").text(message);

            var statusBadge = status === 'open' ?
                '<span class="badge bg-danger">Open</span>' :
                '<span class="badge bg-success">Resolved</span>';
            $("#modal-status").html(statusBadge);

            $("#modal-status-select").val(status);

            $("#viewReportModal").modal("show");
        });

        // Update status button
        $("#update-status-btn").click(function() {
            var reportId = $("#modal-report-id").val();
            var newStatus = $("#modal-status-select").val();

            $.ajax({
                type: "POST",
                url: "../api/teacher/update_problem_status.php",
                data: {
                    report_id: reportId,
                    status: newStatus
                },
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        alert("Status updated successfully!");
                        $("#viewReportModal").modal("hide");
                        loadReports();
                    } else {
                        alert("Error: " + (response.error || "Failed to update status"));
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    alert("Error updating status. Please try again.");
                }
            });
        });
    });
</script>

<?php
// Include footer
include_once "../includes/footer.php";
?>