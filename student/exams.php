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

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$pageTitle = "Online Exams";
$basePath = "..";
include_once "../includes/header.php";
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<main class="main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-clipboard-list me-2"></i> Online Exams</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="exam_history.php" class="btn btn-outline-primary">
                <i class="fas fa-history me-2"></i> View History
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                    <h3 class="mb-0" id="pendingExams">0</h3>
                    <p class="text-muted mb-0">Pending Exams</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="fas fa-play-circle fa-2x text-info mb-2"></i>
                    <h3 class="mb-0" id="ongoingExams">0</h3>
                    <p class="text-muted mb-0">Ongoing Now</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <h3 class="mb-0" id="completedExams">0</h3>
                    <p class="text-muted mb-0">Completed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <i class="fas fa-percentage fa-2x text-primary mb-2"></i>
                    <h3 class="mb-0" id="avgPercentage">0%</h3>
                    <p class="text-muted mb-0">Average Score</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search exams...">
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="upcoming">Upcoming</option>
                        <option value="ongoing">Available Now</option>
                        <option value="completed">Completed</option>
                        <option value="missed">Missed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="subjectFilter">
                        <option value="">All Subjects</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-secondary w-100" id="refreshBtn">
                        <i class="fas fa-sync-alt me-2"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Exams Grid -->
    <div id="examsContainer">
        <div class="text-center py-5">
            <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
            <p class="mt-3">Loading exams...</p>
        </div>
    </div>
</main>

<!-- Exam Instructions Modal -->
<div class="modal fade" id="instructionsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i> Exam Instructions</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div id="examInstructions"></div>

                <div class="alert alert-warning mt-3 mb-3 alert-permanent" style="opacity: 1 !important; display: block !important; visibility: visible !important;">
                    <h6 class="alert-heading mb-2"><i class="fas fa-exclamation-triangle me-2"></i> Important Guidelines:</h6>
                    <ul class="mb-0" style="padding-left: 20px;">
                        <li class="mb-1">Do not refresh the page during the exam</li>
                        <li class="mb-1">Do not close the browser tab</li>
                        <li class="mb-1">Your answers will be auto-saved every 30 seconds</li>
                        <li class="mb-1">Exam will auto-submit when time ends</li>
                        <li class="mb-1">Tab switching may be monitored and counted</li>
                    </ul>
                </div>

                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="agreeTerms">
                    <label class="form-check-label" for="agreeTerms">
                        I have read and understood the instructions. I agree to follow all exam rules.
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="startExamBtn" disabled>
                    <i class="fas fa-play me-2"></i> Start Exam
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        let currentExamId = null;

        loadExams();
        loadStats();
        loadSubjects();

        // Auto-refresh every 60 seconds
        setInterval(function() {
            loadExams();
            loadStats();
        }, 60000);

        $('#searchInput').on('keyup', debounce(loadExams, 500));
        $('#statusFilter, #subjectFilter').on('change', loadExams);
        $('#refreshBtn').on('click', function() {
            loadExams();
            loadStats();
        });

        $('#agreeTerms').on('change', function() {
            $('#startExamBtn').prop('disabled', !$(this).is(':checked'));
        });

        $('#startExamBtn').on('click', function() {
            if (currentExamId) {
                window.location.href = 'take_exam.php?exam_id=' + currentExamId;
            }
        });

        function loadStats() {
            $.ajax({
                url: '../api/student/exam_stats.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#pendingExams').text(response.data.pending || 0);
                        $('#ongoingExams').text(response.data.ongoing || 0);
                        $('#completedExams').text(response.data.completed || 0);
                        $('#avgPercentage').text((response.data.average_percentage || 0) + '%');
                    }
                }
            });
        }

        function loadSubjects() {
            $.ajax({
                url: '../api/student/my_subjects.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let html = '<option value="">All Subjects</option>';
                        response.data.forEach(function(subject) {
                            html += `<option value="${subject.id}">${subject.title}</option>`;
                        });
                        $('#subjectFilter').html(html);
                    }
                }
            });
        }

        function loadExams() {
            const params = {
                search: $('#searchInput').val(),
                status: $('#statusFilter').val(),
                subject_id: $('#subjectFilter').val()
            };

            $.ajax({
                url: '../api/student/exams.php',
                method: 'GET',
                data: params,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        displayExams(response.data);
                    } else {
                        $('#examsContainer').html('<div class="alert alert-danger">Error: ' + response.message + '</div>');
                    }
                },
                error: function() {
                    $('#examsContainer').html('<div class="alert alert-danger">Failed to load exams</div>');
                }
            });
        }

        function displayExams(exams) {
            if (exams.length === 0) {
                $('#examsContainer').html(`
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                    <h4>No exams found</h4>
                    <p class="text-muted">Check back later for upcoming exams</p>
                </div>
            `);
                return;
            }

            let html = '<div class="row">';

            exams.forEach(function(exam) {
                const now = new Date();
                const startTime = new Date(exam.start_time);
                const endTime = new Date(exam.end_time);

                let statusBadge = '';
                let cardClass = '';
                let actionButton = '';
                let timeInfo = '';

                const attemptsUsed = exam.attempts_used || 0;
                const attemptsLeft = exam.max_attempts - attemptsUsed;

                if (now < startTime) {
                    // Upcoming
                    statusBadge = '<span class="badge bg-info">Upcoming</span>';
                    cardClass = 'border-info';
                    timeInfo = `<p class="text-muted mb-2"><i class="far fa-clock"></i> Starts: ${formatDateTime(startTime)}</p>`;
                    actionButton = '<button class="btn btn-secondary btn-sm w-100" disabled><i class="fas fa-lock me-2"></i> Not Started</button>';
                } else if (now >= startTime && now <= endTime) {
                    // Ongoing
                    if (attemptsLeft > 0) {
                        if (exam.last_attempt_status === 'in_progress') {
                            statusBadge = '<span class="badge bg-warning">In Progress</span>';
                            cardClass = 'border-warning';
                            actionButton = `<a href="take_exam.php?exam_id=${exam.id}" class="btn btn-warning btn-sm w-100"><i class="fas fa-arrow-right me-2"></i> Resume Exam</a>`;
                        } else {
                            statusBadge = '<span class="badge bg-success">Available</span>';
                            cardClass = 'border-success';
                            actionButton = `<button class="btn btn-success btn-sm w-100" onclick="showInstructions(${exam.id})"><i class="fas fa-play me-2"></i> Start Exam</button>`;
                        }
                        timeInfo = `<p class="text-danger mb-2"><i class="far fa-clock"></i> Ends: ${formatDateTime(endTime)}</p>`;
                    } else {
                        // No attempts left but exam still ongoing - show view details if already attempted
                        statusBadge = '<span class="badge bg-secondary">No Attempts Left</span>';
                        cardClass = 'border-secondary';
                        if (attemptsUsed > 0 && exam.show_results) {
                            actionButton = `
                            <a href="exam_details.php?exam_id=${exam.id}" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-list-alt me-2"></i> View Details
                            </a>
                        `;
                        } else {
                            actionButton = '<button class="btn btn-secondary btn-sm w-100" disabled><i class="fas fa-ban me-2"></i> No Attempts</button>';
                        }
                        timeInfo = `<p class="text-muted mb-2"><i class="far fa-clock"></i> Ends: ${formatDateTime(endTime)}</p>`;
                    }
                } else {
                    // Completed or Missed
                    if (attemptsUsed > 0) {
                        // Has completed attempts - show results regardless of attempts left
                        statusBadge = '<span class="badge bg-primary">Completed</span>';
                        cardClass = 'border-primary';
                        if (exam.show_results) {
                            actionButton = `
                            <a href="exam_details.php?exam_id=${exam.id}" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-list-alt me-2"></i> View Details
                            </a>
                        `;
                        } else {
                            actionButton = '<button class="btn btn-secondary btn-sm w-100" disabled><i class="fas fa-hourglass-half me-2"></i> Results Pending</button>';
                        }
                    } else {
                        // No attempts made - exam was missed
                        statusBadge = '<span class="badge bg-danger">Missed</span>';
                        cardClass = 'border-danger';
                        actionButton = '<button class="btn btn-danger btn-sm w-100" disabled><i class="fas fa-times me-2"></i> Missed</button>';
                    }
                    timeInfo = `<p class="text-muted mb-2"><i class="far fa-clock"></i> Ended: ${formatDateTime(endTime)}</p>`;
                }

                html += `
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 ${cardClass}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">${exam.title}</h5>
                                ${statusBadge}
                            </div>
                            
                            <p class="text-muted mb-2">
                                <i class="fas fa-book"></i> ${exam.subject_name}
                            </p>
                            
                            ${timeInfo}
                            
                            <div class="row text-center mb-3">
                                <div class="col-4">
                                    <small class="text-muted d-block">Duration</small>
                                    <strong>${exam.duration_minutes} min</strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Marks</small>
                                    <strong>${exam.total_marks}</strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Attempts</small>
                                    <strong>${attemptsUsed}/${exam.max_attempts}</strong>
                                </div>
                            </div>
                            
                            ${exam.best_score !== null ? `
                                <div class="alert alert-info py-2 mb-2">
                                    <small><i class="fas fa-trophy"></i> Best Score: <strong>${exam.best_score}%</strong></small>
                                </div>
                            ` : ''}
                            
                            ${actionButton}
                        </div>
                    </div>
                </div>
            `;
            });

            html += '</div>';
            $('#examsContainer').html(html);
        }

        window.showInstructions = function(examId) {
            currentExamId = examId;

            $.ajax({
                url: '../api/student/exams.php?id=' + examId,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const exam = response.data;
                        let instructionsHtml = `
                        <h5>${exam.title}</h5>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-6">
                                <p class="mb-1"><strong>Subject:</strong> ${exam.subject_name}</p>
                                <p class="mb-1"><strong>Duration:</strong> ${exam.duration_minutes} minutes</p>
                                <p class="mb-1"><strong>Total Marks:</strong> ${exam.total_marks}</p>
                            </div>
                            <div class="col-6">
                                <p class="mb-1"><strong>Total Questions:</strong> ${exam.question_count}</p>
                                <p class="mb-1"><strong>Passing Marks:</strong> ${exam.passing_marks}</p>
                                <p class="mb-1"><strong>Attempts Allowed:</strong> ${exam.max_attempts}</p>
                            </div>
                        </div>
                    `;

                        if (exam.instructions) {
                            instructionsHtml += `
                            <div class="alert alert-primary">
                                <h6>Exam Instructions:</h6>
                                ${exam.instructions}
                            </div>
                        `;
                        }

                        $('#examInstructions').html(instructionsHtml);
                        $('#agreeTerms').prop('checked', false);
                        $('#startExamBtn').prop('disabled', true);
                        $('#instructionsModal').modal('show');
                    }
                }
            });
        };

        function formatDateTime(date) {
            return date.toLocaleString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function debounce(func, wait) {
            let timeout;
            return function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, arguments), wait);
            };
        }
    });
</script>

<style>
    .alert-permanent {
        animation: none !important;
        transition: none !important;
    }

    .alert-permanent.fade {
        opacity: 1 !important;
    }
</style>

<?php include_once "../includes/footer.php"; ?>