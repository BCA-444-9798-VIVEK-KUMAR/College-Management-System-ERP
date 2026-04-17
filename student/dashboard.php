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
$pageTitle = "Student Dashboard";
$basePath = "..";
include_once "../includes/header.php";
?>

<main class="main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-tachometer-alt me-2"></i> Student Dashboard</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="refresh-btn">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Student Information -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user"></i> Student Information
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>College ID:</strong><br>
                            <span id="student-college-id" class="badge bg-primary fs-6 mt-1">Loading...</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Name:</strong><br>
                            <span id="student-name" class="text-primary fs-5">Loading...</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Roll No:</strong><br>
                            <span id="student-roll" class="fs-6">Loading...</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Course:</strong><br>
                            <span id="student-course" class="fs-6">Loading...</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Batch:</strong><br>
                            <span id="student-batch" class="fs-6">Loading...</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Session:</strong><br>
                            <span id="student-session" class="fs-6">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Summary -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie"></i> Attendance Summary
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Overall Attendance</h5>
                                    <h2 class="display-4" id="overall-attendance">0%</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <canvas id="attendance-chart" width="100%" height="50"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Latest Announcements -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-bullhorn"></i> Latest Announcements</span>
            <a href="announcements.php" class="btn btn-sm btn-primary">
                View All <span class="badge bg-light text-dark ms-1" id="announcementBadge" style="display: none;">0</span>
            </a>
        </div>
        <div class="card-body" id="announcements-widget">
            <div class="text-center text-muted py-3">
                <i class="fas fa-spinner fa-spin"></i> Loading announcements...
            </div>
        </div>
    </div>

    <!-- Admit Cards -->
    <div class="card mb-4" id="admit-cards-section" style="display: none;">
        <div class="card-header d-flex justify-content-between align-items-center bg-success text-blue">
            <span><i class="fas fa-id-card me-2"></i> Available Admit Cards</span>
            <span class="badge bg-light text-success" id="admitCardCount">0</span>
        </div>
        <div class="card-body" id="admit-cards-widget">
            <div class="text-center text-muted py-3">
                <i class="fas fa-spinner fa-spin"></i> Loading admit cards...
            </div>
        </div>
    </div>

    <!-- Recent Attendance -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-calendar-day"></i> Recent Attendance
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="recent-attendance-table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Subject</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="recent-attendance-body">
                        <!-- Recent attendance data will be loaded here via AJAX -->
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
        // Load student information and attendance data
        loadStudentInfo();
        loadAttendanceSummary();
        loadRecentAttendance();
        loadLatestAnnouncements();
        loadAdmitCards();

        // Handle refresh button click
        $("#refresh-btn").click(function() {
            loadStudentInfo();
            loadAttendanceSummary();
            loadRecentAttendance();
            loadLatestAnnouncements();
        });

        // Function to load student information
        function loadStudentInfo() {
            $.ajax({
                type: "GET",
                url: "../api/student/student_info.php",
                dataType: "json",
                success: function(response) {
                    console.log("Student info response:", response);
                    if (response.success) {
                        // Update student information
                        $("#student-college-id").text(response.data.college_id || 'N/A');
                        $("#student-name").text(response.data.name);
                        $("#student-roll").text(response.data.roll_no);
                        $("#student-batch").text(response.data.batch);
                        $("#student-session").text(response.data.session);
                        $("#student-course").text(response.data.course);
                    } else {
                        console.error("Error loading student information:", response.error);
                        $("#student-college-id").text("N/A");
                        $("#student-name").html('<span class="text-danger">Error: ' + (response.error || response.message) + '</span>');
                        $("#student-roll").text("N/A");
                        $("#student-batch").text("N/A");
                        $("#student-session").text("N/A");
                        $("#student-course").text("N/A");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    console.error("Response Text:", xhr.responseText);
                    console.error("Status:", xhr.status);
                    $("#student-name").html('<span class="text-danger">Error loading data (HTTP ' + xhr.status + ')</span>');
                    $("#student-roll").text("N/A");
                    $("#student-batch").text("N/A");
                    $("#student-session").text("N/A");
                    $("#student-course").text("N/A");
                }
            });
        }

        // Function to load attendance summary
        function loadAttendanceSummary() {
            $.ajax({
                type: "GET",
                url: "../api/student/attendance_summary.php",
                dataType: "json",
                success: function(response) {
                    console.log("Attendance summary response:", response);
                    if (response.success) {
                        // Update overall attendance percentage
                        $("#overall-attendance").text(response.data.overall_percentage + "%");

                        // Update attendance chart
                        var ctx = document.getElementById("attendance-chart").getContext("2d");
                        var myChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: response.data.subjects.map(function(subject) {
                                    return subject.name;
                                }),
                                datasets: [{
                                    label: 'Attendance Percentage',
                                    data: response.data.subjects.map(function(subject) {
                                        return subject.percentage;
                                    }),
                                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                    borderColor: 'rgba(54, 162, 235, 1)',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        max: 100
                                    }
                                }
                            }
                        });
                    } else {
                        console.error("Error loading attendance summary:", response.error);
                        $("#overall-attendance").text("Error");
                        $("#attendance-chart").parent().html("<p class='text-danger'>Error: " + (response.error || response.message) + "</p>");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    console.error("Response Text:", xhr.responseText);
                    $("#overall-attendance").text("Error");
                    $("#attendance-chart").parent().html("<p class='text-danger'>Error loading chart. Check console.</p>");
                }
            });
        }

        // Function to load recent attendance
        function loadRecentAttendance() {
            $.ajax({
                type: "GET",
                url: "../api/student/recent_attendance.php",
                dataType: "json",
                success: function(response) {
                    console.log("Recent attendance response:", response);
                    if (response.success) {
                        // Update recent attendance table
                        var attendanceHtml = "";
                        if (response.data.length > 0) {
                            $.each(response.data, function(index, attendance) {
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
                        $("#recent-attendance-body").html(attendanceHtml);
                    } else {
                        console.error("Error loading recent attendance:", response.error);
                        $("#recent-attendance-body").html("<tr><td colspan='3' class='text-center'>Failed to load attendance data</td></tr>");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    $("#recent-attendance-body").html("<tr><td colspan='3' class='text-center'>Failed to load attendance data</td></tr>");
                }
            });
        }

        // Function to load latest announcements
        function loadLatestAnnouncements() {
            $.ajax({
                type: "GET",
                url: "../api/student/announcements.php",
                data: {
                    limit: 5
                },
                dataType: "json",
                success: function(response) {
                    console.log("Announcements response:", response);
                    if (response.success) {
                        // Update badge count
                        if (response.unread_count > 0) {
                            $("#announcementBadge").text(response.unread_count).show();
                        } else {
                            $("#announcementBadge").hide();
                        }

                        // Update announcements widget
                        var announcementsHtml = "";
                        if (response.data.length > 0) {
                            $.each(response.data, function(index, announcement) {
                                // Priority icon and badge
                                var priorityIcon = "";
                                var priorityBadge = "";
                                if (announcement.priority === "urgent") {
                                    priorityIcon = "<i class='fas fa-exclamation-circle text-danger me-2'></i>";
                                    priorityBadge = "<span class='badge bg-danger'>Urgent</span>";
                                } else if (announcement.priority === "normal") {
                                    priorityIcon = "<i class='fas fa-info-circle text-primary me-2'></i>";
                                    priorityBadge = "<span class='badge bg-primary'>Normal</span>";
                                } else if (announcement.priority === "info") {
                                    priorityIcon = "<i class='fas fa-bell text-info me-2'></i>";
                                    priorityBadge = "<span class='badge bg-info'>Info</span>";
                                }

                                // Unread badge
                                var unreadBadge = announcement.is_read == 0 ? "<span class='badge bg-success ms-2'>NEW</span>" : "";

                                announcementsHtml += "<div class='d-flex align-items-start mb-3 pb-3 border-bottom'>";
                                announcementsHtml += "<div class='flex-shrink-0'>" + priorityIcon + "</div>";
                                announcementsHtml += "<div class='flex-grow-1'>";
                                announcementsHtml += "<h6 class='mb-1'>" + announcement.title + unreadBadge + "</h6>";
                                announcementsHtml += "<p class='mb-1 text-muted small'>" + announcement.message.substring(0, 100);
                                if (announcement.message.length > 100) {
                                    announcementsHtml += "...";
                                }
                                announcementsHtml += "</p>";
                                announcementsHtml += "<div class='d-flex justify-content-between align-items-center'>";
                                announcementsHtml += "<small class='text-muted'><i class='far fa-calendar-alt me-1'></i>" + formatDate(announcement.created_at) + "</small>";
                                announcementsHtml += priorityBadge;
                                announcementsHtml += "</div>";
                                announcementsHtml += "</div>";
                                announcementsHtml += "</div>";
                            });
                        } else {
                            announcementsHtml = "<div class='text-center text-muted py-3'>";
                            announcementsHtml += "<i class='fas fa-bullhorn fa-3x mb-2 opacity-25'></i>";
                            announcementsHtml += "<p class='mb-0'>No announcements available</p>";
                            announcementsHtml += "</div>";
                        }
                        $("#announcements-widget").html(announcementsHtml);
                    } else {
                        console.error("Error loading announcements:", response.error);
                        $("#announcements-widget").html("<div class='text-center text-muted py-3'>Failed to load announcements</div>");
                        $("#announcementBadge").hide();
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    $("#announcements-widget").html("<div class='text-center text-muted py-3'>Failed to load announcements</div>");
                    $("#announcementBadge").hide();
                }
            });
        }

        // Function to format date
        function formatDate(dateString) {
            var date = new Date(dateString);
            return ("0" + date.getDate()).slice(-2) + "-" + ("0" + (date.getMonth() + 1)).slice(-2) + "-" + date.getFullYear();
        }

        // Function to load admit cards
        function loadAdmitCards() {
            $.ajax({
                url: "../api/student/get_admit_cards.php",
                method: "GET",
                dataType: "json",
                success: function(response) {
                    console.log("Admit Cards response:", response);
                    if (response.success) {
                        var admitCards = response.admit_cards || [];

                        if (admitCards.length > 0) {
                            $("#admit-cards-section").show();
                            $("#admitCardCount").text(admitCards.length);

                            var admitCardsHtml = "";
                            admitCards.forEach(function(card) {
                                var examDate = new Date(card.start_date);
                                var formattedDate = examDate.toLocaleDateString('en-IN', {
                                    day: 'numeric',
                                    month: 'short',
                                    year: 'numeric'
                                });

                                var downloadText = card.download_count > 0 ?
                                    'Downloaded ' + card.download_count + ' time(s)' :
                                    'Not downloaded yet';

                                admitCardsHtml += "<div class='alert alert-success d-flex align-items-center justify-content-between mb-3'>";
                                admitCardsHtml += "<div class='flex-grow-1'>";
                                admitCardsHtml += "<h6 class='mb-1'><i class='fas fa-id-card me-2'></i>" + card.exam_title + "</h6>";
                                admitCardsHtml += "<p class='mb-1 small'><i class='fas fa-calendar me-2'></i>Exam Starts: " + formattedDate + "</p>";
                                admitCardsHtml += "<p class='mb-0 text-muted small'><i class='fas fa-download me-2'></i>" + downloadText + "</p>";
                                admitCardsHtml += "</div>";
                                admitCardsHtml += "<div>";
                                admitCardsHtml += "<a href='admit_card.php?id=" + card.id + "' class='btn btn-success btn-sm' target='_blank'>";
                                admitCardsHtml += "<i class='fas fa-download me-1'></i> Download";
                                admitCardsHtml += "</a>";
                                admitCardsHtml += "</div>";
                                admitCardsHtml += "</div>";
                            });

                            $("#admit-cards-widget").html(admitCardsHtml);
                        } else {
                            $("#admit-cards-section").hide();
                        }
                    } else {
                        console.error("Error loading admit cards:", response.message);
                        $("#admit-cards-section").hide();
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error loading admit cards:", error);
                    $("#admit-cards-section").hide();
                }
            });
        }
    });
</script>

<?php
// Include footer
include_once "../includes/footer.php";
?>