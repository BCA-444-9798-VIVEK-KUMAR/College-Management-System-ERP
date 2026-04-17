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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . " - " : ""; ?>College Management System</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 640 512'><path fill='%2338bdf8' d='M320 32c-8.1 0-16.1 1.4-23.7 4.1L15.8 137.4C6.3 140.9 0 149.9 0 160s6.3 19.1 15.8 22.6l57.9 20.9C57.3 229.3 48 259.8 48 291.9v28.1c0 28.4-10.8 57.7-22.3 80.8c-6.5 13-13.9 25.8-22.5 37.6C0 442.7-.9 448.3 .9 453.4s6 8.9 11.2 10.2l64 16c4.2 1.1 8.7 .3 12.4-2s6.3-6.1 7.1-10.4c8.6-42.8 4.3-81.2-2.1-108.7C90.3 344.3 86 329.8 80 316.5V291.9c0-30.2 10.2-58.7 27.9-81.5c12.9-15.5 29.6-28 49.2-35.7l157-61.7c8.2-3.2 17.5 .8 20.7 9s-.8 17.5-9 20.7l-157 61.7c-12.4 4.9-23.3 12.4-32.2 21.6l159.6 57.6c7.6 2.7 15.6 4.1 23.7 4.1s16.1-1.4 23.7-4.1L624.2 182.6c9.5-3.4 15.8-12.5 15.8-22.6s-6.3-19.1-15.8-22.6L343.7 36.1C336.1 33.4 328.1 32 320 32zM128 408c0 35.3 86 72 192 72s192-36.7 192-72L496.7 262.6 354.5 314c-11.1 4-22.8 6-34.5 6s-23.5-2-34.5-6L143.3 262.6 128 408z'/></svg>">
    <link rel="apple-touch-icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 640 512'><path fill='%2338bdf8' d='M320 32c-8.1 0-16.1 1.4-23.7 4.1L15.8 137.4C6.3 140.9 0 149.9 0 160s6.3 19.1 15.8 22.6l57.9 20.9C57.3 229.3 48 259.8 48 291.9v28.1c0 28.4-10.8 57.7-22.3 80.8c-6.5 13-13.9 25.8-22.5 37.6C0 442.7-.9 448.3 .9 453.4s6 8.9 11.2 10.2l64 16c4.2 1.1 8.7 .3 12.4-2s6.3-6.1 7.1-10.4c8.6-42.8 4.3-81.2-2.1-108.7C90.3 344.3 86 329.8 80 316.5V291.9c0-30.2 10.2-58.7 27.9-81.5c12.9-15.5 29.6-28 49.2-35.7l157-61.7c8.2-3.2 17.5 .8 20.7 9s-.8 17.5-9 20.7l-157 61.7c-12.4 4.9-23.3 12.4-32.2 21.6l159.6 57.6c7.6 2.7 15.6 4.1 23.7 4.1s16.1-1.4 23.7-4.1L624.2 182.6c9.5-3.4 15.8-12.5 15.8-22.6s-6.3-19.1-15.8-22.6L343.7 36.1C336.1 33.4 328.1 32 320 32zM128 408c0 35.3 86 72 192 72s192-36.7 192-72L496.7 262.6 354.5 314c-11.1 4-22.8 6-34.5 6s-23.5-2-34.5-6L143.3 262.6 128 408z'/></svg>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo isset($basePath) ? $basePath : ""; ?>/assets/css/style.css">
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'teacher'): ?>
        <!-- Teacher Theme CSS -->
        <link rel="stylesheet" href="<?php echo isset($basePath) ? $basePath : ""; ?>/assets/css/teacher-style.css">
    <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>
        <!-- Student Theme CSS -->
        <link rel="stylesheet" href="<?php echo isset($basePath) ? $basePath : ""; ?>/assets/css/student-style.css">
    <?php endif; ?>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Top Navigation Bar -->
        <nav class="navbar navbar-expand-lg navbar-dark top-navbar">
            <div class="container-fluid px-4">
                <!-- Mobile Menu Toggle -->
                <button class="sidebar-toggle d-lg-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>

                <!-- Brand Logo -->
                <a class="navbar-brand d-flex align-items-center" href="<?php echo isset($basePath) ? $basePath : ""; ?>/<?php echo $_SESSION['role']; ?>/dashboard.php">
                    <i class="fas fa-graduation-cap me-2"></i>
                    <span class="brand-text">College ERP</span>
                </a>

                <!-- Right Side Menu -->
                <div class="ms-auto d-flex align-items-center">
                    <!-- User Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-link nav-link dropdown-toggle text-white text-decoration-none d-flex align-items-center"
                            type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar me-2">
                                <i class="fas fa-user-circle fa-2x"></i>
                            </div>
                            <div class="d-none d-md-block text-start">
                                <div class="user-name"><?php echo isset($_SESSION['name']) ? $_SESSION['name'] : 'User'; ?></div>
                                <div class="user-role"><?php echo isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'Guest'; ?></div>
                            </div>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item" href="<?php echo isset($basePath) ? $basePath : ""; ?>/<?php echo $_SESSION['role']; ?>/profile.php">
                                    <i class="fas fa-user me-2"></i> My Profile
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?php echo isset($basePath) ? $basePath : ""; ?>/auth/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Sidebar Navigation -->
        <aside id="sidebar" class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    <div class="brand-icon">
                        <i class="fas fa-<?php
                                            if (isset($_SESSION['role'])) {
                                                if ($_SESSION['role'] === 'admin') echo 'user-shield';
                                                elseif ($_SESSION['role'] === 'teacher') echo 'chalkboard-teacher';
                                                else echo 'user-graduate';
                                            } else {
                                                echo 'user';
                                            }
                                            ?>"></i>
                    </div>
                    <div class="brand-text">
                        <h5 class="mb-0">
                            <?php
                            if (isset($_SESSION['role'])) {
                                echo ucfirst($_SESSION['role']) . ' Panel';
                            } else {
                                echo 'Panel';
                            }
                            ?>
                        </h5>
                        <small class="text-muted">Cimage College</small>
                    </div>
                </div>
            </div>
            <div class="sidebar-menu">
                <ul class="nav flex-column">
                    <?php if (isset($_SESSION['role'])): ?>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $basePath ?>/admin/dashboard.php">
                                    <i class="fas fa-tachometer-alt"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $basePath ?>/admin/manage_courses.php">
                                    <i class="fas fa-book"></i>
                                    <span>Manage Courses</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $basePath ?>/admin/manage_sessions.php">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>Manage Sessions</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $basePath ?>/admin/manage_batches.php">
                                    <i class="fas fa-users"></i>
                                    <span>Manage Batches</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $basePath ?>/admin/manage_subjects.php">
                                    <i class="fas fa-book-open"></i>
                                    <span>Manage Subjects</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $basePath ?>/admin/manage_teachers.php">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                    <span>Manage Teachers</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $basePath ?>/admin/manage_students.php">
                                    <i class="fas fa-user-graduate"></i>
                                    <span>Manage Students</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $basePath ?>/admin/assign_subjects.php">
                                    <i class="fas fa-tasks"></i>
                                    <span>Assign Subjects</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $basePath ?>/admin/attendance_reports.php">
                                    <i class="fas fa-chart-bar"></i>
                                    <span>Attendance Reports</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $basePath ?>/admin/email_settings.php">
                                    <i class="fas fa-envelope"></i>
                                    <span>Email Settings</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $basePath ?>/admin/manage_exams.php">
                                    <i class="fas fa-file-alt"></i>
                                    <span>Manage Exams</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $basePath ?>/admin/announcements.php">
                                    <i class="fas fa-bullhorn"></i>
                                    <span>Manage Announcements</span>
                                </a>
                            </li>
                        <?php elseif ($_SESSION['role'] === 'teacher'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $basePath ?>/teacher/dashboard.php">
                                    <i class="fas fa-tachometer-alt"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $basePath ?>/teacher/edit_attendance.php">
                                    <i class="fas fa-edit"></i>
                                    <span>Edit Attendance</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $basePath ?>/teacher/student_reports.php">
                                    <i class="fas fa-user-graduate"></i>
                                    <span>Student Reports</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $basePath ?>/teacher/batch_reports.php">
                                    <i class="fas fa-users"></i>
                                    <span>Batch Reports</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $basePath ?>/teacher/email_alerts.php">
                                    <i class="fas fa-envelope"></i>
                                    <span>Email Alerts</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $basePath ?>/teacher/announcements.php">
                                    <i class="fas fa-bullhorn"></i>
                                    <span>Announcements</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $basePath ?>/teacher/problem_reports.php">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span>Problem Reports</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $basePath ?>/teacher/manage_exams.php">
                                    <i class="fas fa-file-alt"></i>
                                    <span>Manage Exams</span>
                                </a>
                            </li>
                        <?php elseif ($_SESSION['role'] === 'student'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $basePath ?>/student/dashboard.php">
                                    <i class="fas fa-tachometer-alt"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $basePath ?>/student/attendance_report.php">
                                    <i class="fas fa-chart-line"></i>
                                    <span>My Attendance</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $basePath ?>/student/announcements.php">
                                    <i class="fas fa-bullhorn"></i>
                                    <span>Announcements</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $basePath ?>/student/report_problem.php">
                                    <i class="fas fa-bug"></i>
                                    <span>Report Problem</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= $basePath ?>/student/exams.php">
                                    <i class="fas fa-clipboard-list"></i>
                                    <span>Online Exams</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </aside>

        <!-- Overlay for mobile sidebar -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <?php endif; ?>