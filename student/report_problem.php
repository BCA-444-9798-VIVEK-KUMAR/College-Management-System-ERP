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

// Include security functions
require_once '../includes/security.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit;
}

// Generate CSRF token
$csrf_token = generate_csrf_token();

// Include database connection
require_once '../config/database.php';

// Initialize database connection
$database = new Database();
$conn = $database->getConnection();

// Get student information
try {
    $user_id = $_SESSION['user_id'];

    // First get college_id from users table
    $user_query = "SELECT college_id FROM users WHERE id = :user_id";
    $user_stmt = $conn->prepare($user_query);
    $user_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $user_stmt->execute();

    if ($user_stmt->rowCount() > 0) {
        $user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);
        $college_id = $user_data['college_id'];

        // Get student details using college_id
        $student_query = "SELECT s.id, s.name, s.roll_no, b.name AS batch, c.title AS course
                          FROM students s
                          LEFT JOIN batches b ON s.batch_id = b.id
                          LEFT JOIN courses c ON s.course_id = c.id
                          WHERE s.college_id = :college_id";
        $stmt = $conn->prepare($student_query);
        $stmt->bindParam(':college_id', $college_id, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = "Student record not found";
        }
    } else {
        $error = "User not found";
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Include header
$pageTitle = "Report Problem";
$basePath = "..";
include_once '../includes/header.php';
?>

<main class="main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-exclamation-triangle me-2"></i> Report Problem</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="refresh-btn">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-paper-plane"></i> Submit Problem Report
                </div>
                <div class="card-body">
                    <div id="alert-container"></div>

                    <form id="problem-form">
                        <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo $csrf_token; ?>">

                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                            <select class="form-select" id="subject" name="subject_id" required>
                                <option value="">Select Subject</option>
                                <!-- Will be populated via AJAX -->
                            </select>
                            <small class="text-muted">Select the subject related to your problem</small>
                        </div>

                        <div class="mb-3">
                            <label for="teacher" class="form-label">Teacher <span class="text-danger">*</span></label>
                            <select class="form-select" id="teacher" name="teacher_id" required disabled>
                                <option value="">Select Subject First</option>
                                <!-- Will be populated when subject is selected -->
                            </select>
                            <small class="text-muted">Select the teacher for this subject</small>
                        </div>

                        <div class="mb-3">
                            <label for="problem-type" class="form-label">Problem Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="problem-type" name="problem_type" required>
                                <option value="">Select Problem Type</option>
                                <option value="attendance_not_marked">Attendance Not Marked</option>
                                <option value="marked_absent_by_mistake">Marked Absent by Mistake (I was Present)</option>
                                <option value="wrong_subject_attendance">Wrong Subject Marked</option>
                                <option value="other">Other Issue</option>
                            </select>
                            <small class="text-muted">Select the type of problem you're reporting</small>
                        </div>

                        <div class="mb-3">
                            <label for="problem-date" class="form-label">Date of Issue <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="problem-date" name="problem_date" required max="<?php echo date('Y-m-d'); ?>">
                            <small class="text-muted">Select the date when the problem occurred</small>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Problem Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="6" required placeholder="Describe your problem in detail..."></textarea>
                            <small class="text-muted">Please provide as much detail as possible</small>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Submit Report
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user"></i> Student Information
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> <?php echo isset($student) ? safe_output($student['name']) : 'N/A'; ?></p>
                    <p><strong>Roll No:</strong> <?php echo isset($student) ? safe_output($student['roll_no']) : 'N/A'; ?></p>
                    <p><strong>Batch:</strong> <?php echo isset($student) ? safe_output($student['batch']) : 'N/A'; ?></p>
                    <p><strong>Course:</strong> <?php echo isset($student) ? safe_output($student['course']) : 'N/A'; ?></p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-history"></i> Previous Reports
                </div>
                <div class="card-body">
                    <div id="previous-reports">
                        <p class="text-center">Loading previous reports...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
</main>

<!-- JavaScript for the page -->
<script>
    $(document).ready(function() {
        // Load subjects
        loadSubjects();

        // Load previous reports
        loadPreviousReports();

        // Handle subject change - load teachers for selected subject
        $('#subject').change(function() {
            var subjectId = $(this).val();
            if (subjectId) {
                loadTeachers(subjectId);
            } else {
                $('#teacher').html('<option value="">Select Subject First</option>').prop('disabled', true);
            }
        });

        // Handle refresh button click
        $("#refresh-btn").click(function() {
            loadSubjects();
            loadPreviousReports();
        });

        // Function to load subjects
        function loadSubjects() {
            $.ajax({
                url: '../api/student/subjects.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var subjects = response.data;
                        var options = '<option value="">Select Subject</option>';

                        $.each(subjects, function(i, subject) {
                            // Show only subject name (teacher will be in separate dropdown)
                            options += '<option value="' + subject.id + '">' + subject.name + '</option>';
                        });

                        $('#subject').html(options);
                    } else {
                        console.error('Error loading subjects:', response.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                }
            });
        }

        // Function to load teachers for selected subject
        function loadTeachers(subjectId) {
            $('#teacher').html('<option value="">Loading teachers...</option>').prop('disabled', true);

            $.ajax({
                url: '../api/student/subject_teachers.php',
                type: 'GET',
                data: {
                    subject_id: subjectId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var teachers = response.data;
                        var options = '<option value="">Select Teacher</option>';

                        if (teachers.length > 0) {
                            $.each(teachers, function(i, teacher) {
                                options += '<option value="' + teacher.teacher_id + '">' + teacher.teacher_name + '</option>';
                            });
                            $('#teacher').html(options).prop('disabled', false);
                        } else {
                            $('#teacher').html('<option value="">No teachers assigned</option>').prop('disabled', true);
                        }
                    } else {
                        console.error('Error loading teachers:', response.error);
                        $('#teacher').html('<option value="">Error loading teachers</option>').prop('disabled', true);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    $('#teacher').html('<option value="">Error loading teachers</option>').prop('disabled', true);
                }
            });
        }

        // Handle form submission
        $('#problem-form').on('submit', function(e) {
            e.preventDefault();

            var formData = $(this).serialize();

            // Debug: Log what teacher was selected
            var selectedTeacherId = $('#teacher').val();
            var selectedTeacherName = $('#teacher option:selected').text();
            console.log('Submitting report to Teacher ID:', selectedTeacherId, 'Name:', selectedTeacherName);
            console.log('Form data:', formData);

            $.ajax({
                url: '../api/student/submit_problem.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    console.log('Submit response:', response);
                    if (response.success) {
                        // Show success message
                        $('#alert-container').html(
                            '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                            'Your problem report has been submitted successfully.' +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                            '</div>'
                        );

                        // Reset form
                        $('#problem-form')[0].reset();

                        // Update CSRF token if provided
                        if (response.csrf_token) {
                            $('#csrf_token').val(response.csrf_token);
                        }

                        // Reload previous reports
                        loadPreviousReports();
                    } else {
                        // Show error message
                        $('#alert-container').html(
                            '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                            response.error +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                            '</div>'
                        );

                        // Update CSRF token if provided
                        if (response.csrf_token) {
                            $('#csrf_token').val(response.csrf_token);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    // Show error message
                    $('#alert-container').html(
                        '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                        'An error occurred while submitting your report. Please try again later.' +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>'
                    );
                    console.error('AJAX Error:', error);
                }
            });
        });

        // Function to load previous reports
        function loadPreviousReports() {
            $.ajax({
                url: '../api/student/previous_problems.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var reports = response.data;
                        var html = '';

                        if (reports.length === 0) {
                            html = '<p class="text-center">No previous reports found.</p>';
                        } else {
                            html = '<div class="list-group">';

                            $.each(reports, function(i, report) {
                                var statusBadge = '';

                                // Match actual schema: 'open' or 'resolved'
                                switch (report.status) {
                                    case 'open':
                                        statusBadge = '<span class="badge bg-warning text-dark">Open</span>';
                                        break;
                                    case 'resolved':
                                        statusBadge = '<span class="badge bg-success">Resolved</span>';
                                        break;
                                    default:
                                        statusBadge = '<span class="badge bg-primary">' + report.status + '</span>';
                                }

                                html += '<a href="#" class="list-group-item list-group-item-action view-report-detail" data-report=\'' + JSON.stringify(report) + '\'>' +
                                    '<div class="d-flex w-100 justify-content-between">' +
                                    '<h6 class="mb-1">' + report.title + '</h6>' +
                                    statusBadge +
                                    '</div>' +
                                    '<p class="mb-1 text-muted small">' + report.subject_name + '</p>' +
                                    '<small class="text-muted">' + report.created_at + '</small>' +
                                    '</a>';
                            });

                            html += '</div>';
                        }

                        $('#previous-reports').html(html);
                    } else {
                        $('#previous-reports').html(
                            '<div class="alert alert-danger" role="alert">' +
                            'Error loading previous reports: ' + response.error +
                            '</div>'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    $('#previous-reports').html(
                        '<div class="alert alert-danger" role="alert">' +
                        'An error occurred while loading previous reports. Please try again later.' +
                        '</div>'
                    );
                    console.error('AJAX Error:', error);
                }
            });
        }

        // Handle click on previous report to view details
        $(document).on('click', '.view-report-detail', function(e) {
            e.preventDefault();
            var report = JSON.parse($(this).attr('data-report'));
            showReportDetail(report);
        });

        // Handle reopen report button
        $(document).on('click', '#reopen-report-btn', function() {
            var reportId = $(this).data('report-id');

            if (!confirm('Are you sure you want to reopen this issue?')) {
                return;
            }

            $.ajax({
                url: '../api/student/reopen_problem.php',
                type: 'POST',
                data: {
                    report_id: reportId,
                    csrf_token: $('#csrf_token').val()
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Issue reopened successfully!');
                        $('#reportDetailModal').modal('hide');
                        loadPreviousReports();

                        // Update CSRF token if provided
                        if (response.csrf_token) {
                            $('#csrf_token').val(response.csrf_token);
                        }
                    } else {
                        alert('Error: ' + (response.error || 'Failed to reopen issue'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    alert('Error reopening issue. Please try again.');
                }
            });
        });

        // Function to show report detail modal
        function showReportDetail(report) {
            var statusBadge = '';
            var canReopen = false;
            switch (report.status) {
                case 'open':
                    statusBadge = '<span class="badge bg-warning text-dark">Open</span>';
                    canReopen = false;
                    break;
                case 'resolved':
                    statusBadge = '<span class="badge bg-success">Resolved</span>';
                    canReopen = true;
                    break;
                default:
                    statusBadge = '<span class="badge bg-primary">' + report.status + '</span>';
                    canReopen = false;
            }

            var problemTypeLabel = formatProblemType(report.problem_type);

            var reopenButton = canReopen ?
                '<button type="button" class="btn btn-warning" id="reopen-report-btn" data-report-id="' + report.id + '">Reopen Issue</button>' : '';

            var modalHtml = `
            <div class="modal fade" id="reportDetailModal" tabindex="-1" aria-labelledby="reportDetailModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="reportDetailModalLabel">Problem Report Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Subject:</strong> ${report.subject_name}
                                </div>
                                <div class="col-md-6">
                                    <strong>Reported To Teacher:</strong> <span class="text-primary">${report.teacher_name}</span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Problem Type:</strong> ${problemTypeLabel}
                                </div>
                                <div class="col-md-6">
                                    <strong>Date of Issue:</strong> ${report.problem_date || 'N/A'}
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Status:</strong> ${statusBadge}
                                </div>
                                <div class="col-md-6">
                                    <strong>Submitted:</strong> ${report.created_at}
                                </div>
                            </div>
                            ${report.updated_at ? `<div class="row mb-3"><div class="col-md-12"><strong>Last Updated:</strong> ${report.updated_at}</div></div>` : ''}
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <strong>Description:</strong>
                                    <p class="mt-2 p-3 bg-light rounded">${report.message}</p>
                                </div>
                            </div>
                            ${canReopen ? '<div class="alert alert-info"><i class="fas fa-info-circle"></i> This issue has been resolved by the teacher. You can reopen it if the problem still exists.</div>' : ''}
                        </div>
                        <div class="modal-footer">
                            ${reopenButton}
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

            // Remove existing modal if any
            $('#reportDetailModal').remove();

            // Append and show new modal
            $('body').append(modalHtml);
            var modal = new bootstrap.Modal(document.getElementById('reportDetailModal'));
            modal.show();

            // Clean up modal after it's hidden
            $('#reportDetailModal').on('hidden.bs.modal', function() {
                $(this).remove();
            });
        }

        // Function to format problem type for display
        function formatProblemType(type) {
            if (!type) return 'Other Issue';

            var types = {
                'attendance_not_marked': 'Attendance Not Marked',
                'marked_absent_by_mistake': 'Marked Absent by Mistake',
                'marked_present_by_mistake': 'Marked Present by Mistake',
                'wrong_subject_attendance': 'Wrong Subject Attendance',
                'attendance_discrepancy': 'Attendance Discrepancy',
                'other': 'Other Issue'
            };

            return types[type] || type;
        }
    });
</script>

<?php include_once '../includes/footer.php'; ?>