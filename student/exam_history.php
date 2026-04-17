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

$pageTitle = "Exam History";
$basePath = "..";
require_once '../includes/header.php';
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();
$student_id = $_SESSION['student_id'];
?>

<main class="main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-history me-2"></i> My Exam History</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="exams.php" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i> Back to Exams
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <select class="form-select" id="subjectFilter">
                        <option value="">All Subjects</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="graded">Graded</option>
                        <option value="submitted">Pending Grading</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="resultFilter">
                        <option value="">All Results</option>
                        <option value="passed">Passed</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100" onclick="loadHistory()">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Exam History Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="historyTable">
                    <thead class="table-light">
                        <tr>
                            <th>Exam</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Attempt</th>
                            <th>Score</th>
                            <th>Percentage</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="historyBody">
                        <!-- Data will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-center border-primary">
                <div class="card-body">
                    <h6 class="text-muted">Total Exams</h6>
                    <h3 class="text-primary" id="totalExams">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h6 class="text-muted">Passed</h6>
                    <h3 class="text-success" id="passedExams">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-danger">
                <div class="card-body">
                    <h6 class="text-muted">Failed</h6>
                    <h3 class="text-danger" id="failedExams">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-info">
                <div class="card-body">
                    <h6 class="text-muted">Average %</h6>
                    <h3 class="text-info" id="avgPercentage">0%</h3>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    $(document).ready(function() {
        loadSubjects();
        loadHistory();
    });

    function loadSubjects() {
        $.get('../api/student/my_subjects.php', function(response) {
            if (response.success) {
                let options = '<option value="">All Subjects</option>';
                response.data.forEach(subject => {
                    options += `<option value="${subject.id}">${subject.title}</option>`;
                });
                $('#subjectFilter').html(options);
            } else {
                console.error('Error loading subjects:', response);
            }
        }).fail(function(xhr, status, error) {
            console.error('AJAX Error loading subjects:', error, xhr.responseText);
        });
    }

    function loadHistory() {
        const params = {
            subject_id: $('#subjectFilter').val(),
            status: $('#statusFilter').val(),
            result: $('#resultFilter').val()
        };

        $.get('../api/student/exam_history.php', params, function(response) {
            console.log('Exam history response:', response);
            if (response.success) {
                displayHistory(response.data);
                updateStatistics(response.stats);
            } else {
                console.error('Error loading history:', response);
                $('#historyBody').html(`<tr><td colspan="9" class="text-center text-danger">Error: ${response.message || 'Failed to load exam history'}</td></tr>`);
                updateStatistics({
                    total: 0,
                    passed: 0,
                    failed: 0,
                    average: 0
                });
            }
        }).fail(function(xhr, status, error) {
            console.error('AJAX Error loading history:', error, xhr.responseText);
            $('#historyBody').html(`<tr><td colspan="9" class="text-center text-danger">Error loading exam history. Please check console.</td></tr>`);
            updateStatistics({
                total: 0,
                passed: 0,
                failed: 0,
                average: 0
            });
        });
    }

    function displayHistory(history) {
        let html = '';

        if (history.length === 0) {
            html = '<tr><td colspan="9" class="text-center">No exam history found</td></tr>';
        } else {
            history.forEach(exam => {
                console.log('Exam data:', exam); // Debug log

                const statusBadge = exam.status === 'graded' ?
                    '<span class="badge bg-success">Graded</span>' :
                    '<span class="badge bg-warning">Pending</span>';

                const resultBadge = exam.is_passed ?
                    '<span class="badge bg-success"><i class="fas fa-check"></i> Pass</span>' :
                    '<span class="badge bg-danger"><i class="fas fa-times"></i> Fail</span>';

                // Convert percentage to number if it's a string
                const percentageValue = exam.percentage ? parseFloat(exam.percentage) : 0;
                const percentage = exam.percentage ? percentageValue.toFixed(2) + '%' : 'N/A';

                // Check for different possible field names
                const obtainedMarks = exam.obtained_marks || exam.score || exam.marks_obtained || 0;
                const totalMarks = exam.total_marks || exam.max_marks || exam.total_score || 0;
                const score = (obtainedMarks && totalMarks) ?
                    obtainedMarks + '/' + totalMarks :
                    'N/A';

                const progressBarClass = exam.is_passed ? 'bg-success' : 'bg-danger';

                let actionButton = '';
                if (exam.status === 'graded') {
                    actionButton = '<a href="exam_result.php?id=' + exam.id + '" class="btn btn-sm btn-primary" title="View Result">' +
                        '<i class="fas fa-eye"></i></a>';
                } else {
                    actionButton = '<button class="btn btn-sm btn-secondary" disabled title="Result pending">' +
                        '<i class="fas fa-clock"></i></button>';
                }

                html += '<tr>' +
                    '<td><strong>' + exam.exam_title + '</strong></td>' +
                    '<td>' + exam.subject_name + '</td>' +
                    '<td>' + new Date(exam.submit_time).toLocaleDateString() + '</td>' +
                    '<td>' + exam.attempt_number + '</td>' +
                    '<td>' + score + '</td>' +
                    '<td>' +
                    '<div class="progress" style="height: 20px;">' +
                    '<div class="progress-bar ' + progressBarClass + '" style="width: ' + percentageValue + '%">' +
                    percentage +
                    '</div>' +
                    '</div>' +
                    '</td>' +
                    '<td>' + exam.time_taken_minutes + ' min</td>' +
                    '<td>' + statusBadge + ' ' + (exam.status === 'graded' ? resultBadge : '') + '</td>' +
                    '<td>' + actionButton + '</td>' +
                    '</tr>';
            });
        }

        $('#historyBody').html(html);
    }

    function updateStatistics(stats) {
        $('#totalExams').text(stats.total || 0);
        $('#passedExams').text(stats.passed || 0);
        $('#failedExams').text(stats.failed || 0);

        // Convert average to number if it's a string
        const avgValue = stats.average ? parseFloat(stats.average) : 0;
        $('#avgPercentage').text(avgValue ? avgValue.toFixed(2) + '%' : '0%');
    }
</script>

<?php require_once '../includes/footer.php'; ?>