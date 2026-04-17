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

$exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;
if (!$exam_id) {
    header("Location: exams.php");
    exit;
}

$pageTitle = "Exam Details";
$basePath = "..";
include_once "../includes/header.php";
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<main class="main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-list-alt me-2"></i> Exam Details & Answers</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="exams.php" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i> Back to Exams
            </a>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print me-2"></i> Print
            </button>
        </div>
    </div>

    <!-- Exam Info Card -->
    <div class="card mb-4" id="examInfoCard">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h4 id="examTitle" class="mb-3"></h4>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Subject:</strong> <span id="examSubject"></span></p>
                            <p class="mb-2"><strong>Date:</strong> <span id="examDate"></span></p>
                            <p class="mb-2"><strong>Duration:</strong> <span id="examDuration"></span> minutes</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Total Questions:</strong> <span id="totalQuestions"></span></p>
                            <p class="mb-2"><strong>Total Marks:</strong> <span id="totalMarks"></span></p>
                            <p class="mb-2"><strong>Passing Marks:</strong> <span id="passingMarks"></span></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">Your Score</h6>
                            <h2 class="mb-2"><span id="obtainedMarks"></span>/<span id="totalMarksScore"></span></h2>
                            <h3 class="text-primary mb-2"><span id="percentage"></span>%</h3>
                            <span id="resultBadge"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="badge bg-success mb-2" style="font-size: 14px; padding: 10px;">
                        <i class="fas fa-check-circle"></i> Correct Answer
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="badge bg-danger mb-2" style="font-size: 14px; padding: 10px;">
                        <i class="fas fa-times-circle"></i> Wrong Answer
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="badge bg-warning mb-2" style="font-size: 14px; padding: 10px;">
                        <i class="fas fa-question-circle"></i> Not Answered
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="badge bg-info mb-2" style="font-size: 14px; padding: 10px;">
                        <i class="fas fa-bookmark"></i> Marked for Review
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Questions & Answers -->
    <div id="questionsContainer">
        <div class="text-center py-5">
            <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
            <p class="mt-3">Loading exam details...</p>
        </div>
    </div>
</main>

<script>
    const examId = <?php echo $exam_id; ?>;

    $(document).ready(function() {
        loadExamDetails();
    });

    function loadExamDetails() {
        $.ajax({
            url: '../api/student/exam_details.php',
            method: 'GET',
            data: {
                exam_id: examId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayExamInfo(response.data);
                    displayQuestions(response.data.questions, response.data.answers);
                } else {
                    Swal.fire('Error', response.message, 'error').then(() => {
                        window.location.href = 'exams.php';
                    });
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to load exam details', 'error').then(() => {
                    window.location.href = 'exams.php';
                });
            }
        });
    }

    function displayExamInfo(data) {
        $('#examTitle').text(data.exam.title);
        $('#examSubject').text(data.exam.subject_name);
        $('#examDate').text(new Date(data.exam.submit_time).toLocaleString());
        $('#examDuration').text(data.exam.duration_minutes);
        $('#totalQuestions').text(data.questions.length);
        $('#totalMarks').text(data.exam.total_marks);
        $('#totalMarksScore').text(data.exam.total_marks);
        $('#passingMarks').text(data.exam.passing_marks);
        $('#obtainedMarks').text(data.exam.obtained_marks || 0);
        $('#percentage').text(data.exam.percentage ? parseFloat(data.exam.percentage).toFixed(2) : '0.00');

        const isPassed = data.exam.is_passed;
        const badge = isPassed ?
            '<span class="badge bg-success fs-5"><i class="fas fa-check-circle me-1"></i> PASSED</span>' :
            '<span class="badge bg-danger fs-5"><i class="fas fa-times-circle me-1"></i> FAILED</span>';
        $('#resultBadge').html(badge);
    }

    function displayQuestions(questions, answers) {
        let html = '';

        questions.forEach((question, index) => {
            const answer = answers[question.id] || {};
            const isCorrect = answer.is_correct == 1;
            const isAnswered = answer.selected_option_ids || answer.answer_text;
            const marksObtained = answer.marks_obtained || 0;

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
                        <h5 class="mb-0">
                            ${statusIcon}
                            Question ${index + 1}
                            ${answer.is_marked_for_review ? '<span class="badge bg-info ms-2"><i class="fas fa-bookmark"></i> Marked</span>' : ''}
                        </h5>
                        <span class="badge bg-primary">
                            ${marksObtained}/${question.marks} marks
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <p class="fw-bold mb-3">${question.question_text}</p>
                    
                    ${question.image_url ? `<img src="${question.image_url}" class="img-fluid mb-3" alt="Question Image">` : ''}
                    
                    ${displayAnswerSection(question, answer)}
                </div>
            </div>
        `;
        });

        $('#questionsContainer').html(html);
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
            html += '<div class="mb-2"><strong>Your Answer:</strong></div>';
            html += `<div class="p-3 border rounded bg-light">${answer.answer_text || '<em class="text-muted">Not answered</em>'}</div>`;

            if (answer.teacher_feedback) {
                html += '<div class="mt-3"><strong>Teacher Feedback:</strong></div>';
                html += `<div class="alert alert-info">${answer.teacher_feedback}</div>`;
            }
        }

        return html;
    }
</script>

<style>
    @media print {

        .btn-toolbar,
        .border-bottom,
        .no-print {
            display: none !important;
        }

        .card {
            break-inside: avoid;
            page-break-inside: avoid;
        }
    }
</style>

<?php include_once "../includes/footer.php"; ?>