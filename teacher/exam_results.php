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
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['teacher', 'admin'])) {
    header("Location: ../auth/login.php");
    exit;
}

$exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;
if (!$exam_id) {
    header("Location: manage_exams.php");
    exit;
}

$pageTitle = "Exam Results";
$basePath = "..";
include_once "../includes/header.php";
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<main class="main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-chart-bar me-2"></i> Exam Results</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="manage_exams.php" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i> Back to Exams
            </a>
            <button class="btn btn-success" id="exportExcelBtn">
                <i class="fas fa-file-excel me-2"></i> Export Excel
            </button>
        </div>
    </div>

    <!-- Exam Info Card -->
    <div class="card mb-4" id="examInfoCard">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h4 id="examTitle"></h4>
                    <p class="mb-1"><strong>Subject:</strong> <span id="examSubject"></span></p>
                    <p class="mb-1"><strong>Course:</strong> <span id="examCourse"></span></p>
                    <p class="mb-1"><strong>Duration:</strong> <span id="examDuration"></span> minutes</p>
                    <p class="mb-1"><strong>Total Marks:</strong> <span id="totalMarks"></span></p>
                    <p class="mb-1"><strong>Passing Marks:</strong> <span id="passingMarks"></span></p>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">Statistics</h6>
                            <div class="row">
                                <div class="col-6 border-end">
                                    <h4 class="mb-0" id="totalAttempts">0</h4>
                                    <small class="text-muted">Total Attempts</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="mb-0" id="passPercentage">0%</h4>
                                    <small class="text-muted">Pass Rate</small>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-4">
                                    <h5 class="mb-0" id="avgScore">0</h5>
                                    <small class="text-muted">Avg Score</small>
                                </div>
                                <div class="col-4">
                                    <h5 class="mb-0" id="highestScore">0</h5>
                                    <small class="text-muted">Highest</small>
                                </div>
                                <div class="col-4">
                                    <h5 class="mb-0" id="lowestScore">0</h5>
                                    <small class="text-muted">Lowest</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search by student name or roll number...">
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="passed">Passed</option>
                        <option value="failed">Failed</option>
                        <option value="pending">Pending Grading</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="sortBy">
                        <option value="marks_desc">Highest Marks First</option>
                        <option value="marks_asc">Lowest Marks First</option>
                        <option value="name_asc">Name (A-Z)</option>
                        <option value="roll_asc">Roll Number</option>
                        <option value="date_desc">Latest First</option>
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

    <!-- Results Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="resultsTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Roll No</th>
                            <th>Student Name</th>
                            <th>Attempt</th>
                            <th>Submit Time</th>
                            <th>Marks Obtained</th>
                            <th>Percentage</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="resultsTableBody">
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                                <p class="mt-3">Loading results...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Student Response Modal -->
<div class="modal fade" id="responseModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user-graduate me-2"></i> Student Response Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div id="responseContent">
                    <div class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                        <p class="mt-3">Loading response...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    const examId = <?php echo $exam_id; ?>;

    $(document).ready(function() {
        loadExamInfo();
        loadResults();

        $('#searchInput').on('keyup', debounce(loadResults, 500));
        $('#statusFilter, #sortBy').on('change', loadResults);
        $('#refreshBtn').on('click', function() {
            loadExamInfo();
            loadResults();
        });

        $('#exportExcelBtn').on('click', exportToExcel);
    });

    function loadExamInfo() {
        $.ajax({
            url: '../api/teacher/exams.php?id=' + examId,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const exam = response.data;
                    $('#examTitle').text(exam.title);
                    $('#examSubject').text(exam.subject_name);
                    $('#examCourse').text(exam.course_name || 'N/A');
                    $('#examDuration').text(exam.duration_minutes);
                    $('#totalMarks').text(exam.total_marks);
                    $('#passingMarks').text(exam.passing_marks);
                }
            }
        });

        // Load statistics
        $.ajax({
            url: '../api/teacher/exam_statistics.php?exam_id=' + examId,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const stats = response.data;
                    $('#totalAttempts').text(stats.total_attempts || 0);
                    $('#passPercentage').text(parseFloat(stats.pass_percentage || 0).toFixed(1) + '%');
                    $('#avgScore').text(parseFloat(stats.average_score || 0).toFixed(1));
                    $('#highestScore').text(parseFloat(stats.highest_score || 0).toFixed(1));
                    $('#lowestScore').text(parseFloat(stats.lowest_score || 0).toFixed(1));
                }
            }
        });
    }

    function loadResults() {
        const params = {
            exam_id: examId,
            search: $('#searchInput').val(),
            status: $('#statusFilter').val(),
            sort: $('#sortBy').val()
        };

        $.ajax({
            url: '../api/teacher/exam_results.php',
            method: 'GET',
            data: params,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayResults(response.data);
                } else {
                    $('#resultsTableBody').html(`
                    <tr><td colspan="9" class="text-center text-danger">${response.message}</td></tr>
                `);
                }
            },
            error: function() {
                $('#resultsTableBody').html(`
                <tr><td colspan="9" class="text-center text-danger">Failed to load results</td></tr>
            `);
            }
        });
    }

    function displayResults(results) {
        if (results.length === 0) {
            $('#resultsTableBody').html(`
            <tr><td colspan="9" class="text-center text-muted py-4">No submissions found</td></tr>
        `);
            return;
        }

        let html = '';
        results.forEach((result, index) => {
            const statusBadge = result.is_passed == 1 ?
                '<span class="badge bg-success">Passed</span>' :
                (result.status === 'graded' ? '<span class="badge bg-danger">Failed</span>' : '<span class="badge bg-warning">Pending</span>');

            const percentage = result.percentage ? parseFloat(result.percentage).toFixed(2) : '0.00';
            const submitTime = result.submit_time ? new Date(result.submit_time).toLocaleString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }) : 'N/A';

            html += `
            <tr>
                <td>${index + 1}</td>
                <td><strong>${result.roll_no}</strong></td>
                <td>${result.student_name}</td>
                <td>${result.attempt_number}</td>
                <td>${submitTime}</td>
                <td><strong>${result.obtained_marks || 0}</strong> / ${result.total_marks}</td>
                <td><strong>${percentage}%</strong></td>
                <td>${statusBadge}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="viewResponse(${result.student_exam_id}, '${result.student_name}')">
                        <i class="fas fa-eye me-1"></i> View Answers
                    </button>
                </td>
            </tr>
        `;
        });

        $('#resultsTableBody').html(html);
    }

    function viewResponse(studentExamId, studentName) {
        $('#responseModal').modal('show');
        $('#responseContent').html(`
        <div class="text-center py-5">
            <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
            <p class="mt-3">Loading response...</p>
        </div>
    `);

        $.ajax({
            url: '../api/teacher/student_response.php',
            method: 'GET',
            data: {
                student_exam_id: studentExamId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayResponse(response.data, studentName);
                } else {
                    $('#responseContent').html(`
                    <div class="alert alert-danger">${response.message}</div>
                `);
                }
            },
            error: function() {
                $('#responseContent').html(`
                <div class="alert alert-danger">Failed to load response</div>
            `);
            }
        });
    }

    function displayResponse(data, studentName) {
        let html = `
        <div class="mb-4">
            <h5>${studentName}</h5>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>College ID:</strong> ${data.exam.college_id || 'N/A'}</p>
                    <p class="mb-1"><strong>Roll No:</strong> ${data.exam.roll_no}</p>
                    <p class="mb-1"><strong>Batch:</strong> ${data.exam.batch_name || 'N/A'}</p>
                    <p class="mb-1"><strong>Session:</strong> ${data.exam.session_name || 'N/A'}</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Exam:</strong> ${data.exam.title}</p>
                    <p class="mb-1"><strong>Subject:</strong> ${data.exam.subject_name}</p>
                    <p class="mb-1"><strong>Marks:</strong> ${data.exam.obtained_marks} / ${data.exam.total_marks} (${parseFloat(data.exam.percentage).toFixed(2)}%)</p>
                    <p class="mb-1"><strong>Status:</strong> ${data.exam.is_passed == 1 ? '<span class="badge bg-success">PASSED</span>' : '<span class="badge bg-danger">FAILED</span>'}</p>
                </div>
            </div>
            <p class="mb-0 mt-2 text-muted"><small><i class="fas fa-clock"></i> Submitted: ${new Date(data.exam.submit_time).toLocaleString()}</small></p>
        </div>
        <hr>
    `;

        data.questions.forEach((question, index) => {
            const answer = data.answers[question.id] || {};
            const isCorrect = answer.is_correct == 1;
            const isAnswered = answer.selected_option_ids || answer.answer_text;

            let cardClass = 'border-secondary';
            let headerClass = 'bg-light';
            let statusIcon = '<i class="fas fa-question-circle text-warning"></i>';

            if (isAnswered) {
                if (isCorrect) {
                    cardClass = 'border-success';
                    headerClass = 'bg-success-subtle';
                    statusIcon = '<i class="fas fa-check-circle text-success"></i>';
                } else {
                    cardClass = 'border-danger';
                    headerClass = 'bg-danger-subtle';
                    statusIcon = '<i class="fas fa-times-circle text-danger"></i>';
                }
            }

            html += `
            <div class="card mb-3 ${cardClass}">
                <div class="card-header ${headerClass}">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            ${statusIcon}
                            Question ${index + 1}
                        </h6>
                        <span class="badge bg-primary">
                            ${answer.marks_obtained || 0}/${question.marks} marks
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <p class="fw-bold mb-3">${question.question_text}</p>
                    ${displayAnswerSection(question, answer)}
                </div>
            </div>
        `;
        });

        $('#responseContent').html(html);
    }

    function displayAnswerSection(question, answer) {
        let html = '';

        if (question.question_type === 'mcq_single' || question.question_type === 'mcq_multiple' || question.question_type === 'true_false') {
            const selectedIds = answer.selected_option_ids ? JSON.parse(answer.selected_option_ids) : [];

            html += '<div class="mb-3"><strong>Options:</strong></div>';

            question.options.forEach(option => {
                const isSelected = selectedIds.includes(option.id);
                const isCorrect = option.is_correct == 1;

                let optionClass = '';
                let icon = '';

                if (isCorrect) {
                    optionClass = 'border-success bg-success-subtle';
                    icon = '<i class="fas fa-check text-success me-2"></i>';
                } else if (isSelected && !isCorrect) {
                    optionClass = 'border-danger bg-danger-subtle';
                    icon = '<i class="fas fa-times text-danger me-2"></i>';
                }

                html += `
                <div class="p-3 mb-2 border rounded ${optionClass}">
                    ${icon}
                    ${isSelected ? '<strong>' : ''}${option.option_text}${isSelected ? '</strong>' : ''}
                </div>
            `;
            });

        } else if (question.question_type === 'short_answer' || question.question_type === 'long_answer') {
            html += '<div class="mb-2"><strong>Student Answer:</strong></div>';
            html += `<div class="p-3 border rounded bg-light">${answer.answer_text || '<em class="text-muted">Not answered</em>'}</div>`;

            if (answer.teacher_feedback) {
                html += '<div class="mt-3"><strong>Your Feedback:</strong></div>';
                html += `<div class="alert alert-info">${answer.teacher_feedback}</div>`;
            }
        }

        return html;
    }

    function exportToExcel() {
        window.location.href = `../api/teacher/export_exam_results.php?exam_id=${examId}`;
    }

    function debounce(func, wait) {
        let timeout;
        return function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, arguments), wait);
        };
    }
</script>

<style>
    @media print {

        .btn-toolbar,
        .border-bottom,
        .no-print,
        .modal-footer {
            display: none !important;
        }

        .card {
            break-inside: avoid;
            page-break-inside: avoid;
        }
    }
</style>

<?php include_once "../includes/footer.php"; ?>