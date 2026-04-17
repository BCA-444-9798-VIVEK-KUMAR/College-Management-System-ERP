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
$pageTitle = "Admin Dashboard";
$basePath = "..";
include_once "../includes/header.php";
?>

<!-- Main content -->
<main class="main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Admin Dashboard</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </div>
    </div>

    <!-- Dashboard Cards -->
    <div class="row dashboard-cards">
        <div class="col-12 col-sm-6 col-md-4 mb-4">
            <div class="card bg-primary text-white h-100 dashboard-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase fw-bold">Total Students</h6>
                            <h1 class="display-4" id="total-students">0</h1>
                        </div>
                        <i class="fas fa-user-graduate fa-3x opacity-75"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a href="manage_students.php" class="text-white text-decoration-none">View Details</a>
                    <i class="fas fa-angle-right text-white"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-4 mb-4">
            <div class="card bg-success text-white h-100 dashboard-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase fw-bold">Total Teachers</h6>
                            <h1 class="display-4" id="total-teachers">0</h1>
                        </div>
                        <i class="fas fa-chalkboard-teacher fa-3x opacity-75"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a href="manage_teachers.php" class="text-white text-decoration-none">View Details</a>
                    <i class="fas fa-angle-right text-white"></i>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-4 mb-4">
            <div class="card bg-warning text-white h-100 dashboard-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase fw-bold">Average Attendance</h6>
                            <h1 class="display-4" id="avg-attendance">0%</h1>
                        </div>
                        <i class="fas fa-chart-line fa-3x opacity-75"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a href="attendance_reports.php" class="text-white text-decoration-none">View Details</a>
                    <i class="fas fa-angle-right text-white"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Settings / Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i> Additional Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <div class="d-grid">
                                <a href="email_settings.php" class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-envelope-open-text fa-2x mb-2 d-block"></i>
                                    <strong>Email Settings</strong>
                                    <small class="d-block text-muted mt-1">Configure SMTP & Email Alerts</small>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <div class="d-grid">
                                <a href="announcements.php" class="btn btn-outline-danger btn-lg">
                                    <i class="fas fa-bullhorn fa-2x mb-2 d-block"></i>
                                    <strong>Announcements</strong>
                                    <small class="d-block text-muted mt-1">Manage System Announcements</small>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <div class="d-grid">
                                <a href="attendance_reports.php" class="btn btn-outline-success btn-lg">
                                    <i class="fas fa-chart-line fa-2x mb-2 d-block"></i>
                                    <strong>View Reports</strong>
                                    <small class="d-block text-muted mt-1">Attendance Analytics</small>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-grid">
                                <a href="profile.php" class="btn btn-outline-info btn-lg">
                                    <i class="fas fa-user-cog fa-2x mb-2 d-block"></i>
                                    <strong>Profile Settings</strong>
                                    <small class="d-block text-muted mt-1">Update Your Information</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i> Recent Activity</h5>
                <div class="d-flex align-items-center gap-3">
                    <small id="activityCount" class="text-muted">Loading...</small>
                    <button class="btn btn-sm btn-outline-secondary" id="refresh-activity">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="recent-activity-table" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 15%;">Date</th>
                            <th style="width: 15%;">Activity</th>
                            <th class="d-none d-md-table-cell" style="width: 15%;">User</th>
                            <th style="width: 55%;">Details</th>
                        </tr>
                    </thead>
                    <tbody id="recent-activity-body">
                        <!-- Activity data will be loaded here via AJAX -->
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
</main>

<style>
    .bg-purple {
        background-color: #6f42c1 !important;
    }

    #recent-activity-table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }

    .badge {
        font-weight: 500;
        padding: 0.35em 0.5em;
        font-size: 0.8rem;
    }

    /* Mobile optimizations for tables */
    @media (max-width: 768px) {
        #recent-activity-table {
            font-size: 0.85rem;
        }

        #recent-activity-table th,
        #recent-activity-table td {
            padding: 0.5rem 0.3rem;
            vertical-align: middle;
        }

        #recent-activity-table th:first-child,
        #recent-activity-table td:first-child {
            padding-left: 0.5rem;
        }

        #recent-activity-table th:last-child,
        #recent-activity-table td:last-child {
            padding-right: 0.5rem;
        }

        .badge {
            font-size: 0.7rem;
            padding: 0.25em 0.4em;
            white-space: nowrap;
        }

        .badge i {
            display: none;
        }

        .table-responsive {
            margin-bottom: 0;
        }
    }

    @media (max-width: 576px) {
        #recent-activity-table {
            font-size: 0.75rem;
        }

        #recent-activity-table th,
        #recent-activity-table td {
            padding: 0.4rem 0.25rem;
        }

        #recent-activity-table small {
            font-size: 0.7rem;
            display: block;
            line-height: 1.2;
        }

        .card-body {
            padding: 0.75rem;
        }

        .dashboard-cards .card-body {
            padding: 1rem;
        }
    }
</style>

<script>
    $(document).ready(function() {
        let activityOffset = 0;
        let activityTotal = 0;
        
        // Load dashboard data
        loadDashboardData();

        // Refresh button click handler
        $("#refresh-activity").on("click", function() {
            $(this).find("i").addClass("fa-spin");
            activityOffset = 0;
            loadDashboardData(false).then(() => {
                setTimeout(() => {
                    $(this).find("i").removeClass("fa-spin");
                }, 500);
            });
        });
        
        // Load more button click handler
        $('#loadMoreActivity').click(function() {
            activityOffset += 15;
            $(this).html('<i class="fas fa-spinner fa-spin me-2"></i>Loading...');
            loadDashboardData(true);
        });

        // Function to load dashboard data
        function loadDashboardData(append = false) {
            return $.ajax({
                type: "GET",
                url: "../api/admin/dashboard_data.php?offset=" + activityOffset + "&limit=15",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        // Update dashboard cards with animation (only on initial load)
                        if (!append) {
                            animateCounter("#total-students", response.data.total_students);
                            animateCounter("#total-teachers", response.data.total_teachers);
                            animateCounter("#avg-attendance", response.data.avg_attendance, "%");
                        }
                        
                        activityTotal = response.data.activity_total || 0;

                        // Update recent activity
                        var activityHtml = "";
                        if (response.data.recent_activity && response.data.recent_activity.length > 0) {
                            $.each(response.data.recent_activity, function(index, activity) {
                                // Determine badge color based on activity type
                                var badgeClass = 'bg-secondary';
                                var iconClass = 'fa-circle';

                                switch (activity.activity.toLowerCase()) {
                                    case 'login':
                                        badgeClass = 'bg-success';
                                        iconClass = 'fa-sign-in-alt';
                                        break;
                                    case 'logout':
                                        badgeClass = 'bg-warning';
                                        iconClass = 'fa-sign-out-alt';
                                        break;
                                    case 'create':
                                        badgeClass = 'bg-primary';
                                        iconClass = 'fa-plus-circle';
                                        break;
                                    case 'update':
                                        badgeClass = 'bg-info';
                                        iconClass = 'fa-edit';
                                        break;
                                    case 'delete':
                                        badgeClass = 'bg-danger';
                                        iconClass = 'fa-trash';
                                        break;
                                    case 'attendance':
                                        badgeClass = 'bg-purple';
                                        iconClass = 'fa-clipboard-check';
                                        break;
                                    case 'password_change':
                                    case 'password_reset':
                                    case 'password_update':
                                        badgeClass = 'bg-warning';
                                        iconClass = 'fa-key';
                                        break;
                                    case 'otp_generated':
                                    case 'otp_verified':
                                        badgeClass = 'bg-info';
                                        iconClass = 'fa-shield-alt';
                                        break;
                                }

                                activityHtml += "<tr>";
                                activityHtml += "<td><small class='text-muted'>" + activity.date + "</small></td>";
                                activityHtml += "<td><span class='badge " + badgeClass + "'><i class='fas " + iconClass + " me-1'></i>" + activity.activity + "</span></td>";
                                activityHtml += "<td class='d-none d-md-table-cell'><strong>" + activity.user + "</strong></td>";
                                activityHtml += "<td>" + activity.details + "</td>";
                                activityHtml += "</tr>";
                            });
                            
                            if (append) {
                                $("#recent-activity-body").append(activityHtml);
                            } else {
                                $("#recent-activity-body").html(activityHtml);
                            }
                            
                            // Update count display
                            const currentCount = activityOffset + response.data.recent_activity.length;
                            $('#activityCount').text(`Showing ${currentCount} of ${activityTotal}`);
                            
                            // Show/hide load more button
                            if (response.data.activity_has_more) {
                                $('#loadMoreContainer').show();
                                $('#loadMoreActivity').html('<i class="fas fa-angle-down me-2"></i>Load More');
                            } else {
                                $('#loadMoreContainer').hide();
                            }
                        } else {
                            activityHtml = "<tr><td colspan='4' class='text-center text-muted py-4'><i class='fas fa-inbox fa-2x mb-2 d-block'></i>No recent activity</td></tr>";
                            $("#recent-activity-body").html(activityHtml);
                            $('#activityCount').text('No activities');
                            $('#loadMoreContainer').hide();
                        }
                    } else {
                        console.error("Error loading dashboard data:", response.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                }
            });
        }

        // Function to animate counter
        function animateCounter(elementId, targetValue, suffix = "") {
            const element = $(elementId);
            const startValue = parseInt(element.text()) || 0;
            const duration = 1000;
            const frameRate = 30;
            const steps = duration / frameRate;
            const increment = (targetValue - startValue) / steps;
            let currentValue = startValue;
            let currentStep = 0;

            const animation = setInterval(function() {
                currentStep++;
                currentValue += increment;

                if (currentStep >= steps) {
                    clearInterval(animation);
                    currentValue = targetValue;
                }

                element.text(Math.round(currentValue) + suffix);
            }, frameRate);
        }

        // Handle responsive behavior
        $(window).on('resize', function() {
            if ($(window).width() < 768) {
                $('.table-responsive').addClass('table-responsive-sm');
            } else {
                $('.table-responsive').removeClass('table-responsive-sm');
            }
        }).trigger('resize');
    });
</script>

<?php
// Include footer
include_once "../includes/footer.php";
?>