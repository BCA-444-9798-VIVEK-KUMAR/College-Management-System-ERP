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
    // Redirect to login page
    header("Location: ../auth/login.php");
    exit;
}

// Set page title and include header
$pageTitle = "Attendance Report";
$basePath = "..";
include_once "../includes/header.php";
?>

<main class="main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-calendar-check me-2"></i> Attendance Report</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="print-btn">
                <i class="fas fa-print"></i> Print Report
            </button>
        </div>
    </div>

    <!-- Filter Options -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter"></i> Filter Options
        </div>
        <div class="card-body">
            <form id="filter-form" class="row g-3">
                <div class="col-md-4">
                    <label for="subject-filter" class="form-label">Subject</label>
                    <select class="form-select" id="subject-filter">
                        <option value="">All Subjects</option>
                        <!-- Subjects will be loaded here via AJAX -->
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="from-date" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="from-date" value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>">
                </div>
                <div class="col-md-4">
                    <label for="to-date" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="to-date" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Attendance Summary -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-chart-pie"></i> Attendance Summary
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="card bg-primary text-white mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Present</h5>
                            <h2 class="display-4" id="present-count">0</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-danger text-white mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Absent</h5>
                            <h2 class="display-4" id="absent-count">0</h2>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12">
                    <h5>Attendance Percentage: <span id="attendance-percentage">0%</span></h5>
                    <div class="progress">
                        <div id="attendance-progress" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Attendance Records -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table"></i> Detailed Attendance Records
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="attendance-table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Subject</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="attendance-body">
                        <!-- Attendance data will be loaded here via AJAX -->
                        <tr>
                            <td colspan="3" class="text-center">Loading attendance data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
    </div>
</main>

<script>
    $(document).ready(function() {
        // Load subjects for filter
        loadSubjects();

        // Load initial attendance data
        loadAttendanceData();

        // Handle filter form submission
        $("#filter-form").submit(function(e) {
            e.preventDefault();
            loadAttendanceData();
        });

        // Handle print button click
        $("#print-btn").click(function() {
            window.print();
        });

        // Function to load subjects
        function loadSubjects() {
            $.ajax({
                type: "GET",
                url: "../api/student/subjects.php",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        // Update subject filter dropdown
                        var subjectsHtml = "<option value=''>All Subjects</option>";
                        $.each(response.data, function(index, subject) {
                            subjectsHtml += "<option value='" + subject.id + "'>" + subject.name + "</option>";
                        });
                        $("#subject-filter").html(subjectsHtml);
                    } else {
                        console.error("Error loading subjects:", response.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                }
            });
        }

        // Function to load attendance data
        function loadAttendanceData() {
            var subjectId = $("#subject-filter").val();
            var fromDate = $("#from-date").val();
            var toDate = $("#to-date").val();

            $.ajax({
                type: "GET",
                url: "../api/student/detailed_attendance.php",
                data: {
                    subject_id: subjectId,
                    from_date: fromDate,
                    to_date: toDate
                },
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        // Update attendance summary
                        $("#present-count").text(response.data.summary.present_count);
                        $("#absent-count").text(response.data.summary.absent_count);

                        var percentage = response.data.summary.percentage;
                        $("#attendance-percentage").text(percentage + "%");
                        $("#attendance-progress").css("width", percentage + "%").attr("aria-valuenow", percentage).text(percentage + "%");

                        // Update progress bar color based on percentage
                        if (percentage >= 75) {
                            $("#attendance-progress").removeClass("bg-warning bg-danger").addClass("bg-success");
                        } else if (percentage >= 50) {
                            $("#attendance-progress").removeClass("bg-success bg-danger").addClass("bg-warning");
                        } else {
                            $("#attendance-progress").removeClass("bg-success bg-warning").addClass("bg-danger");
                        }

                        // Update attendance table
                        var attendanceHtml = "";
                        if (response.data.records.length > 0) {
                            $.each(response.data.records, function(index, attendance) {
                                attendanceHtml += "<tr>";
                                attendanceHtml += "<td>" + formatDate(attendance.date) + "</td>";
                                attendanceHtml += "<td>" + attendance.subject + "</td>";

                                // Status badge
                                var statusBadge = "";
                                if (attendance.status === "present") {
                                    statusBadge = "<span class='badge bg-success'>Present</span>";
                                } else if (attendance.status === "absent") {
                                    statusBadge = "<span class='badge bg-danger'>Absent</span>";
                                }
                                attendanceHtml += "<td>" + statusBadge + "</td>";

                                attendanceHtml += "</tr>";
                            });
                        } else {
                            attendanceHtml = "<tr><td colspan='3' class='text-center'>No attendance records found</td></tr>";
                        }
                        $("#attendance-body").html(attendanceHtml);
                    } else {
                        console.error("Error loading attendance data:", response.error);
                        $("#attendance-body").html("<tr><td colspan='3' class='text-center'>Failed to load attendance data</td></tr>");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    $("#attendance-body").html("<tr><td colspan='3' class='text-center'>Failed to load attendance data</td></tr>");
                }
            });
        }

        // Function to format date
        function formatDate(dateString) {
            var date = new Date(dateString);
            return ("0" + date.getDate()).slice(-2) + "-" + ("0" + (date.getMonth() + 1)).slice(-2) + "-" + date.getFullYear();
        }
    });
</script>

<?php
// Include footer
include_once "../includes/footer.php";
?>