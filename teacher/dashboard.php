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
$pageTitle = "Teacher Dashboard";
$basePath = "..";
include_once "../includes/header.php";
?>

<!-- Main Content -->
<main class="main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-tachometer-alt me-2"></i> Teacher Dashboard</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Today's Classes -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-calendar-day"></i> Today's Classes
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="today-classes-table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Batch</th>
                            <th>Course</th>
                            <th>Session</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="today-classes-body">
                        <!-- Classes data will be loaded here via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Attendance Summary -->
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar"></i> Attendance Summary (Last 7 Days)
                </div>
                <div class="card-body">
                    <canvas id="attendance-chart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-exclamation-circle"></i> Recent Problem Reports
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="problem-reports-table" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Student</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="problem-reports-body">
                                <!-- Problem reports data will be loaded here via AJAX -->
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
        // Load dashboard data
        loadDashboardData();

        // Refresh button click event
        $("#refresh-btn").click(function() {
            loadDashboardData();
        });

        // Function to load dashboard data
        function loadDashboardData() {
            // Load today's classes
            $.ajax({
                type: "GET",
                url: "../api/teacher/today_classes.php",
                dataType: "json",
                success: function(response) {
                    console.log("Today's classes response:", response);
                    if (response.success) {
                        // Update today's classes table
                        var classesHtml = "";
                        if (response.data.length > 0) {
                            $.each(response.data, function(index, classData) {
                                classesHtml += "<tr>";
                                classesHtml += "<td>" + classData.subject + "</td>";
                                classesHtml += "<td>" + classData.batch + "</td>";
                                classesHtml += "<td>" + classData.course + "</td>";
                                classesHtml += "<td>" + classData.session + "</td>";

                                // Status badge based on attendance_status from API
                                var statusBadge = "";
                                if (classData.attendance_status === "completed") {
                                    statusBadge = "<span class='badge bg-success'>Completed</span>";
                                } else if (classData.attendance_status === "pending") {
                                    statusBadge = "<span class='badge bg-warning text-dark'>Pending</span>";
                                } else {
                                    statusBadge = "<span class='badge bg-secondary'>Not Started</span>";
                                }
                                classesHtml += "<td>" + statusBadge + "</td>";

                                // Action button
                                if (classData.attendance_status === "completed") {
                                    classesHtml += "<td><a href='view_attendance.php?subject_id=" + classData.subject_id + "&batch_id=" + classData.batch_id + "&date=" + classData.date + "' class='btn btn-sm btn-info'>View</a></td>";
                                } else {
                                    classesHtml += "<td><a href='take_attendance.php?subject_id=" + classData.subject_id + "&batch_id=" + classData.batch_id + "&date=" + classData.date + "' class='btn btn-sm btn-primary'>Take Attendance</a></td>";
                                }

                                classesHtml += "</tr>";
                            });
                        } else {
                            classesHtml = "<tr><td colspan='6' class='text-center'>No classes scheduled for today</td></tr>";
                        }
                        $("#today-classes-body").html(classesHtml);

                        // Load attendance chart data
                        loadAttendanceChart();

                        // Load problem reports
                        loadProblemReports();
                    } else {
                        console.error("Error loading today's classes:", response.error);
                        $("#today-classes-body").html("<tr><td colspan='6' class='text-center text-danger'>Error: " + (response.error || response.message) + "</td></tr>");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    console.error("Response Text:", xhr.responseText);
                    $("#today-classes-body").html("<tr><td colspan='6' class='text-center text-danger'>Error loading classes. Check console for details.</td></tr>");
                }
            });
        }

        // Function to load attendance chart
        function loadAttendanceChart() {
            $.ajax({
                type: "GET",
                url: "../api/teacher/attendance_summary.php",
                dataType: "json",
                success: function(response) {
                    console.log("Attendance summary response:", response);
                    if (response.success) {
                        // Create chart
                        var ctx = document.getElementById("attendance-chart");
                        var myChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: response.data.dates,
                                datasets: [{
                                        label: "Present",
                                        backgroundColor: "rgba(40, 167, 69, 0.7)",
                                        data: response.data.present
                                    },
                                    {
                                        label: "Absent",
                                        backgroundColor: "rgba(220, 53, 69, 0.7)",
                                        data: response.data.absent
                                    }
                                ]
                            },
                            options: {
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        title: {
                                            display: true,
                                            text: 'Number of Students'
                                        }
                                    },
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Date'
                                        }
                                    }
                                }
                            }
                        });
                    } else {
                        console.error("Error loading attendance summary:", response.error);
                        $("#attendance-chart").parent().html("<p class='text-danger'>Error loading chart: " + (response.error || response.message) + "</p>");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error loading chart:", error);
                    console.error("Response Text:", xhr.responseText);
                    $("#attendance-chart").parent().html("<p class='text-danger'>Error loading chart. Check console.</p>");
                }
            });
        }

        // Function to load problem reports
        function loadProblemReports() {
            $.ajax({
                type: "GET",
                url: "../api/teacher/problem_reports.php",
                dataType: "json",
                success: function(response) {
                    console.log("Problem reports response:", response);
                    if (response.success) {
                        // Update problem reports table
                        var reportsHtml = "";
                        if (response.data.length > 0) {
                            $.each(response.data, function(index, report) {
                                reportsHtml += "<tr>";
                                reportsHtml += "<td>" + report.created_at + "</td>";
                                reportsHtml += "<td>" + report.student_name + "</td>";
                                reportsHtml += "<td>" + report.subject + "</td>";

                                // Status badge
                                if (report.status === 'open') {
                                    reportsHtml += "<td><span class='badge bg-danger'>Open</span></td>";
                                } else {
                                    reportsHtml += "<td><span class='badge bg-success'>Resolved</span></td>";
                                }

                                // Action button
                                reportsHtml += "<td><a href='problem_reports.php' class='btn btn-sm btn-info'>View</a></td>";

                                reportsHtml += "</tr>";
                            });
                        } else {
                            reportsHtml = "<tr><td colspan='5' class='text-center'>No problem reports</td></tr>";
                        }
                        $("#problem-reports-body").html(reportsHtml);
                    } else {
                        console.error("Error loading problem reports:", response.error);
                        $("#problem-reports-body").html("<tr><td colspan='5' class='text-center text-danger'>Error: " + (response.error || response.message) + "</td></tr>");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error loading reports:", error);
                    console.error("Response Text:", xhr.responseText);
                    $("#problem-reports-body").html("<tr><td colspan='5' class='text-center text-danger'>Error loading reports. Check console.</td></tr>");
                }
            });
        }
    });
</script>

<?php
// Include footer
include_once "../includes/footer.php";
?>