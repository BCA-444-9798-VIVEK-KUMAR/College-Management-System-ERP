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
$pageTitle = "Today's Attendance";
$basePath = "..";
include_once "../includes/header.php";
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="today_attendance.php">
                            <i class="fas fa-clipboard-check"></i> Today's Attendance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="edit_attendance.php">
                            <i class="fas fa-edit"></i> Edit Attendance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="batch_reports.php">
                            <i class="fas fa-users"></i> Batch Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="student_reports.php">
                            <i class="fas fa-user-graduate"></i> Student Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="problem_reports.php">
                            <i class="fas fa-exclamation-triangle"></i> Problem Reports
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-calendar-day me-2"></i> Today's Attendance</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="date-picker-btn">
                            <i class="fas fa-calendar"></i> Change Date
                        </button>
                        <input type="date" id="attendance-date" class="form-control form-control-sm d-none" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
            </div>

            <div id="alert-container"></div>

            <!-- Classes for Today -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-calendar-day"></i> Classes for <span id="display-date"><?php echo date('d-m-Y'); ?></span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="classes-table" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Batch</th>
                                    <th>Course</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="classes-body">
                                <!-- Classes data will be loaded here via AJAX -->
                                <tr>
                                    <td colspan="5" class="text-center">Loading classes...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Load today's classes
        loadClasses();

        // Handle date picker button click
        $("#date-picker-btn").click(function() {
            $("#attendance-date").toggleClass("d-none");
            if (!$("#attendance-date").hasClass("d-none")) {
                $("#attendance-date").focus();
            }
        });

        // Handle date change
        $("#attendance-date").change(function() {
            var selectedDate = $(this).val();
            $("#display-date").text(formatDate(selectedDate));
            loadClasses(selectedDate);
            $(this).addClass("d-none");
        });

        // Function to load classes
        function loadClasses(date = null) {
            if (!date) {
                date = $("#attendance-date").val();
            }

            $.ajax({
                type: "GET",
                url: "../api/teacher/today_classes.php",
                data: {
                    date: date
                },
                dataType: "json",
                success: function(response) {
                    console.log("Today's classes response:", response);
                    if (response.success) {
                        // Update classes table
                        var classesHtml = "";
                        if (response.data.length > 0) {
                            $.each(response.data, function(index, classItem) {
                                classesHtml += "<tr>";
                                classesHtml += "<td>" + classItem.subject + "</td>";
                                classesHtml += "<td>" + classItem.batch + "</td>";
                                classesHtml += "<td>" + classItem.course + "</td>";

                                // Status badge
                                var statusBadge = "";
                                if (classItem.attendance_status === "completed") {
                                    statusBadge = "<span class='badge bg-success'>Completed</span>";
                                } else if (classItem.attendance_status === "pending") {
                                    statusBadge = "<span class='badge bg-warning text-dark'>Pending</span>";
                                } else {
                                    statusBadge = "<span class='badge bg-secondary'>Not Started</span>";
                                }
                                classesHtml += "<td>" + statusBadge + "</td>";

                                // Action button
                                var actionBtn = "";
                                if (classItem.attendance_status === "completed") {
                                    actionBtn = "<a href='view_attendance.php?subject_id=" + classItem.subject_id + "&batch_id=" + classItem.batch_id + "&date=" + date + "' class='btn btn-sm btn-info'><i class='fas fa-eye'></i> View</a>";
                                } else {
                                    actionBtn = "<a href='take_attendance.php?subject_id=" + classItem.subject_id + "&batch_id=" + classItem.batch_id + "&date=" + date + "' class='btn btn-sm btn-primary'><i class='fas fa-clipboard-check'></i> Take Attendance</a>";
                                }
                                classesHtml += "<td>" + actionBtn + "</td>";

                                classesHtml += "</tr>";
                            });
                        } else {
                            classesHtml = "<tr><td colspan='5' class='text-center'>No classes scheduled for this date</td></tr>";
                        }
                        $("#classes-body").html(classesHtml);
                    } else {
                        console.error("Error loading classes:", response.error);
                        // Show error message
                        $("#alert-container").html(
                            "<div class='alert alert-danger alert-dismissible fade show' role='alert'>" +
                            "Error: " + (response.error || "Failed to load classes") +
                            "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>" +
                            "</div>"
                        );
                        $("#classes-body").html("<tr><td colspan='5' class='text-center'>Failed to load classes</td></tr>");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    console.error("Response Text:", xhr.responseText);
                    // Show error message
                    $("#alert-container").html(
                        "<div class='alert alert-danger alert-dismissible fade show' role='alert'>" +
                        "An error occurred while loading classes. Please check console for details." +
                        "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>" +
                        "</div>"
                    );
                    $("#classes-body").html("<tr><td colspan='5' class='text-center'>Failed to load classes</td></tr>");
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