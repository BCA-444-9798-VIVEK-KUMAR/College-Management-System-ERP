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

$pageTitle = "Take Exam";
$basePath = "..";
// Don't include header for full-screen exam mode
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $pageTitle; ?> - College ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        * {
            box-sizing: border-box;
        }
        
        body {
            overflow-x: hidden;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            font-size: 14px;
        }

        .exam-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            padding: 10px 0;
        }

        .exam-content {
            margin-top: 70px;
            margin-bottom: 70px;
            padding: 15px;
        }

        .exam-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            padding: 10px 0;
        }

        .timer-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 8px 15px;
            font-size: 1.2rem;
            font-weight: bold;
            color: #856404;
            display: inline-block;
        }

        .timer-box.critical {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
            animation: blink 1s infinite;
        }
        
        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .exam-header {
                padding: 8px 0;
            }
            
            .exam-header .row {
                row-gap: 8px;
            }
            
            .exam-header h5 {
                font-size: 1rem;
                margin-bottom: 0;
            }
            
            .exam-header small {
                font-size: 0.75rem;
            }
            
            .timer-box {
                padding: 6px 10px;
                font-size: 1rem;
            }
            
            .exam-content {
                margin-top: 100px;
                margin-bottom: 140px;
                padding: 10px;
            }
            
            .exam-footer {
                padding: 8px 0;
            }
            
            .exam-footer .btn {
                padding: 6px 10px;
                font-size: 0.85rem;
                margin-bottom: 5px;
            }
            
            .exam-footer .col-md-4,
            .exam-footer .col-md-8 {
                text-align: center !important;
            }
            
            .question-card {
                padding: 15px;
                min-height: auto;
            }
            
            .question-card h5 {
                font-size: 1.1rem;
            }
            
            .question-card .lead {
                font-size: 1rem;
            }
            
            .option-card {
                padding: 10px;
                font-size: 0.9rem;
            }
            
            body {
                font-size: 13px;
            }
        }

        @keyframes blink {

            0%,
            50%,
            100% {
                opacity: 1;
            }

            25%,
            75% {
                opacity: 0.5;
            }
        }

        .question-navigator {
            max-height: 400px;
            overflow-y: auto;
        }

        .question-nav-btn {
            width: 45px;
            height: 45px;
            margin: 5px;
            border-radius: 5px;
            border: 2px solid #dee2e6;
            background: #fff;
            color: #000;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .question-nav-btn.answered {
            background: #28a745;
            color: #fff;
            border-color: #28a745;
        }

        .question-nav-btn.marked {
            background: #ffc107;
            color: #000;
            border-color: #ffc107;
        }

        .question-nav-btn.current {
            background: #007bff;
            color: #fff;
            border-color: #007bff;
            transform: scale(1.1);
        }

        .question-nav-btn:hover {
            transform: scale(1.05);
        }

        .question-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            min-height: 400px;
        }

        .option-card {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .option-card:hover {
            border-color: #007bff;
            background: #e7f3ff;
        }

        .option-card.selected {
            border-color: #007bff;
            background: #cfe2ff;
        }

        .option-card input[type="radio"],
        .option-card input[type="checkbox"] {
            transform: scale(1.3);
            margin-right: 10px;
        }

        .warning-banner {
            position: fixed;
            top: 80px;
            left: 0;
            right: 0;
            background: #dc3545;
            color: #fff;
            padding: 10px;
            text-align: center;
            z-index: 999;
            display: none;
        }

        /* Disable right-click context menu */
        body {
            -webkit-touch-callout: none;
        }
    </style>
</head>

<body>

    <!-- Warning Banner -->
    <div class="warning-banner" id="warningBanner">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <span id="warningText"></span>
    </div>

    <!-- Exam Header -->
    <div class="exam-header">
        <div class="container-fluid">
            <div class="row align-items-center g-2">
                <div class="col-12 col-md-4">
                    <h5 class="mb-0" id="examTitle">Loading...</h5>
                    <small class="text-muted" id="examSubject"></small>
                </div>
                <div class="col-6 col-md-4 text-center">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <button class="btn btn-primary btn-sm" id="fullscreenBtn" title="Toggle Fullscreen">
                            <i class="fas fa-expand"></i>
                        </button>
                        <div class="timer-box" id="timerBox">
                            <i class="fas fa-clock me-1"></i>
                            <span id="timer">00:00:00</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 text-end">
                    <span class="me-2 d-none d-sm-inline">
                        <i class="fas fa-user"></i>
                        <strong><?php echo $_SESSION['name']; ?></strong>
                    </span>
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#navigatorModal">
                        <i class="fas fa-th"></i><span class="d-none d-sm-inline ms-1"> Navigator</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Exam Content -->
    <div class="exam-content container-fluid">
        <div class="row">
            <div class="col-lg-9">
                <!-- Question Card -->
                <div class="card">
                    <div class="card-body">
                        <div id="questionContainer">
                            <div class="text-center py-5">
                                <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                                <p class="mt-3">Loading exam...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <!-- Question Navigator (Desktop) -->
                <div class="card d-none d-lg-block">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-th me-2"></i> Question Navigator</h6>
                    </div>
                    <div class="card-body">
                        <div class="question-navigator" id="questionNav">
                            <!-- Navigator buttons will be loaded here -->
                        </div>

                        <hr>

                        <div class="legend">
                            <small class="d-block mb-2">
                                <span class="badge bg-success">Answered</span>
                            </small>
                            <small class="d-block mb-2">
                                <span class="badge bg-warning text-dark">Marked</span>
                            </small>
                            <small class="d-block mb-2">
                                <span class="badge bg-secondary">Not Visited</span>
                            </small>
                            <small class="d-block mb-2">
                                <span class="badge bg-light text-dark">Not Answered</span>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Stats Card -->
                <div class="card mt-3 d-none d-lg-block">
                    <div class="card-body">
                        <h6 class="card-title">Progress</h6>
                        <p class="mb-1"><strong id="answeredCount">0</strong> Answered</p>
                        <p class="mb-1"><strong id="markedCount">0</strong> Marked for Review</p>
                        <p class="mb-1"><strong id="notAnsweredCount">0</strong> Not Answered</p>
                        <p class="mb-0"><strong id="notVisitedCount">0</strong> Not Visited</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Exam Footer -->
    <div class="exam-footer">
        <div class="container-fluid">
            <div class="row align-items-center g-2">
                <div class="col-12 col-md-4 text-center text-md-start">
                    <span class="text-muted">
                        Question <strong id="currentQuestionNum">1</strong> of <strong id="totalQuestions">0</strong>
                    </span>
                </div>
                <div class="col-12 col-md-8 text-center text-md-end">
                    <div class="d-flex flex-wrap justify-content-center justify-content-md-end gap-1">
                        <button class="btn btn-outline-secondary btn-sm" id="prevBtn" disabled>
                            <i class="fas fa-chevron-left"></i><span class="d-none d-sm-inline"> Previous</span>
                        </button>
                        <button class="btn btn-warning btn-sm" id="markReviewBtn">
                            <i class="fas fa-flag"></i><span class="d-none d-sm-inline"> Mark</span>
                        </button>
                        <button class="btn btn-success btn-sm" id="saveNextBtn">
                            <span class="d-none d-sm-inline">Save & </span>Next <i class="fas fa-chevron-right"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" id="submitExamBtn">
                            <i class="fas fa-check-circle"></i> Submit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Question Navigator Modal (Mobile) -->
    <div class="modal fade" id="navigatorModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Question Navigator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="questionNavMobile"></div>
                    <hr>
                    <div class="legend">
                        <small class="d-block mb-2">
                            <span class="badge bg-success">Answered</span>
                        </small>
                        <small class="d-block mb-2">
                            <span class="badge bg-warning text-dark">Marked for Review</span>
                        </small>
                        <small class="d-block mb-2">
                            <span class="badge bg-primary">Current</span>
                        </small>
                        <small class="d-block mb-2">
                            <span class="badge bg-light text-dark">Not Answered</span>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Exam state variables
        let examData = null;
        let questions = [];
        let currentQuestionIndex = 0;
        let answers = {};
        let markedQuestions = {};
        let visitedQuestions = {};
        let timeRemaining = 0;
        let timerInterval = null;
        let autoSaveInterval = null;
        let studentExamId = null;
        let tabSwitchCount = 0;
        let violations = [];
        const examId = <?php echo $exam_id; ?>;
        let beforeUnloadHandler = null;

        $(document).ready(function() {
            initializeExam();
            setupEventListeners();
            setupAntiCheating();
        });

        function initializeExam() {
            // Load exam details
            $.ajax({
                url: '../api/student/start_exam.php',
                method: 'POST',
                data: JSON.stringify({
                    exam_id: examId
                }),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        examData = response.data.exam;
                        questions = response.data.questions;
                        studentExamId = response.data.student_exam_id;

                        // Restore previous answers if resuming
                        if (response.data.saved_answers) {
                            answers = response.data.saved_answers;
                        }

                        // Initialize UI
                        $('#examTitle').text(examData.title);
                        $('#examSubject').text(examData.subject_name);
                        $('#totalQuestions').text(questions.length);

                        // Set timer
                        timeRemaining = response.data.time_remaining * 60; // Convert to seconds
                        startTimer();

                        // Load first question
                        loadQuestion(0);
                        generateNavigator();

                        // Start auto-save
                        startAutoSave();
                        
                        // Auto-enable fullscreen if required
                        if (examData.full_screen_required) {
                            setTimeout(function() {
                                enterFullscreen();
                            }, 500);
                        }

                        Swal.fire({
                            title: 'Exam Started!',
                            text: 'Your exam has begun. Good luck!',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error').then(() => {
                            window.location.href = 'exams.php';
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Start exam error:', xhr.responseText);
                    let errorMessage = 'Failed to start exam';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                        if (response.debug) {
                            console.error('Debug info:', response.debug);
                        }
                    } catch (e) {
                        console.error('Raw error:', xhr.responseText);
                    }
                    Swal.fire('Error', errorMessage, 'error').then(() => {
                        window.location.href = 'exams.php';
                    });
                }
            });
        }

        function loadQuestion(index) {
            if (index < 0 || index >= questions.length) return;

            currentQuestionIndex = index;
            const question = questions[index];
            visitedQuestions[index] = true;

            let html = `
                <div class="question-card">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5>Question ${index + 1}</h5>
                        <span class="badge bg-primary">${question.marks} ${question.marks > 1 ? 'Marks' : 'Mark'}</span>
                    </div>
                    
                    <div class="mb-4">
                        <p class="lead">${question.question_text}</p>
                        ${question.image_url ? `<img src="${question.image_url}" class="img-fluid mb-3" alt="Question Image">` : ''}
                    </div>
            `;

            if (question.question_type === 'mcq_single') {
                html += '<div class="options">';
                question.options.forEach((option, optIndex) => {
                    const isSelected = answers[question.id] === option.id;
                    html += `
                        <div class="option-card ${isSelected ? 'selected' : ''}" onclick="selectOption(${question.id}, ${option.id}, false)">
                            <input type="radio" name="question_${question.id}" value="${option.id}" ${isSelected ? 'checked' : ''}>
                            <label>${option.option_text}</label>
                        </div>
                    `;
                });
                html += '</div>';
            } else if (question.question_type === 'mcq_multiple') {
                html += '<div class="options">';
                const selectedOptions = answers[question.id] || [];
                question.options.forEach((option, optIndex) => {
                    const isSelected = selectedOptions.includes(option.id);
                    html += `
                        <div class="option-card ${isSelected ? 'selected' : ''}" onclick="selectOption(${question.id}, ${option.id}, true)">
                            <input type="checkbox" name="question_${question.id}" value="${option.id}" ${isSelected ? 'checked' : ''}>
                            <label>${option.option_text}</label>
                        </div>
                    `;
                });
                html += '</div>';
            } else if (question.question_type === 'true_false') {
                html += '<div class="options">';
                const tfOptions = [{
                        id: 'true',
                        text: 'True'
                    },
                    {
                        id: 'false',
                        text: 'False'
                    }
                ];
                tfOptions.forEach(option => {
                    const isSelected = answers[question.id] === option.id;
                    html += `
                        <div class="option-card ${isSelected ? 'selected' : ''}" onclick="selectTrueFalse(${question.id}, '${option.id}')">
                            <input type="radio" name="question_${question.id}" value="${option.id}" ${isSelected ? 'checked' : ''}>
                            <label>${option.text}</label>
                        </div>
                    `;
                });
                html += '</div>';
            } else if (question.question_type === 'short_answer') {
                const savedAnswer = answers[question.id] || '';
                html += `
                    <textarea class="form-control" id="answer_${question.id}" rows="3" 
                              placeholder="Type your answer here..." 
                              onchange="saveTextAnswer(${question.id})">${savedAnswer}</textarea>
                `;
            } else if (question.question_type === 'long_answer') {
                const savedAnswer = answers[question.id] || '';
                html += `
                    <textarea class="form-control" id="answer_${question.id}" rows="10" 
                              placeholder="Type your answer here..." 
                              onchange="saveTextAnswer(${question.id})">${savedAnswer}</textarea>
                `;
            }

            html += '</div>';
            $('#questionContainer').html(html);

            // Update UI
            $('#currentQuestionNum').text(index + 1);
            $('#prevBtn').prop('disabled', index === 0);

            // Update mark for review button
            if (markedQuestions[index]) {
                $('#markReviewBtn').html('<i class="fas fa-flag"></i><span class="d-none d-sm-inline"> Unmark</span>');
            } else {
                $('#markReviewBtn').html('<i class="fas fa-flag"></i><span class="d-none d-sm-inline"> Mark</span>');
            }

            updateNavigator();
            updateStats();
        }

        function selectOption(questionId, optionId, isMultiple) {
            if (isMultiple) {
                if (!Array.isArray(answers[questionId])) {
                    answers[questionId] = [];
                }
                const index = answers[questionId].indexOf(optionId);
                if (index > -1) {
                    answers[questionId].splice(index, 1);
                } else {
                    answers[questionId].push(optionId);
                }
            } else {
                answers[questionId] = optionId;
            }
            loadQuestion(currentQuestionIndex); // Reload to show selection
        }

        function selectTrueFalse(questionId, value) {
            answers[questionId] = value;
            loadQuestion(currentQuestionIndex);
        }

        function saveTextAnswer(questionId) {
            answers[questionId] = $('#answer_' + questionId).val();
        }

        function generateNavigator() {
            let html = '';
            questions.forEach((q, index) => {
                html += `<button class="question-nav-btn" onclick="navigateToQuestion(${index})" data-question-index="${index}">${index + 1}</button>`;
            });
            $('#questionNav').html(html);
            $('#questionNavMobile').html(html);
        }
        
        function navigateToQuestion(index) {
            loadQuestion(index);
            // Close mobile modal if open
            const modal = bootstrap.Modal.getInstance(document.getElementById('navigatorModal'));
            if (modal) {
                modal.hide();
            }
        }

        function updateNavigator() {
            questions.forEach((q, index) => {
                // Select both desktop and mobile nav buttons
                const desktopBtn = $(`#questionNav button[data-question-index="${index}"]`);
                const mobileBtn = $(`#questionNavMobile button[data-question-index="${index}"]`);
                const btns = desktopBtn.add(mobileBtn);
                
                btns.removeClass('answered marked current');

                if (index === currentQuestionIndex) {
                    btns.addClass('current');
                } else if (markedQuestions[index]) {
                    btns.addClass('marked');
                } else if (answers[q.id] !== undefined && answers[q.id] !== '' &&
                    (Array.isArray(answers[q.id]) ? answers[q.id].length > 0 : true)) {
                    btns.addClass('answered');
                }
            });
        }

        function updateStats() {
            let answered = 0;
            let marked = Object.keys(markedQuestions).length;
            let visited = Object.keys(visitedQuestions).length;

            questions.forEach(q => {
                if (answers[q.id] !== undefined && answers[q.id] !== '' &&
                    (Array.isArray(answers[q.id]) ? answers[q.id].length > 0 : true)) {
                    answered++;
                }
            });

            $('#answeredCount').text(answered);
            $('#markedCount').text(marked);
            $('#notAnsweredCount').text(visited - answered);
            $('#notVisitedCount').text(questions.length - visited);
        }

        function startTimer() {
            timerInterval = setInterval(function() {
                timeRemaining--;

                const hours = Math.floor(timeRemaining / 3600);
                const minutes = Math.floor((timeRemaining % 3600) / 60);
                const seconds = timeRemaining % 60;

                const display =
                    String(hours).padStart(2, '0') + ':' +
                    String(minutes).padStart(2, '0') + ':' +
                    String(seconds).padStart(2, '0');

                $('#timer').text(display);

                // Warning at 5 minutes
                if (timeRemaining === 300) {
                    $('#timerBox').addClass('critical');
                    Swal.fire({
                        title: 'Time Warning!',
                        text: '5 minutes remaining',
                        icon: 'warning',
                        timer: 3000
                    });
                }

                // Auto-submit when time ends
                if (timeRemaining <= 0) {
                    clearInterval(timerInterval);
                    autoSubmitExam();
                }
            }, 1000);
        }

        function startAutoSave() {
            autoSaveInterval = setInterval(function() {
                saveAnswersToServer(false);
            }, 30000); // Every 30 seconds
        }

        function saveAnswersToServer(showMessage = false) {
            $.ajax({
                url: '../api/student/save_answers.php',
                method: 'POST',
                data: JSON.stringify({
                    student_exam_id: studentExamId,
                    answers: answers,
                    marked_questions: Object.keys(markedQuestions)
                }),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    if (showMessage && response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Saved!',
                            text: 'Your answers have been saved',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                }
            });
        }

        function setupEventListeners() {
            $('#prevBtn').click(function() {
                if (currentQuestionIndex > 0) {
                    loadQuestion(currentQuestionIndex - 1);
                }
            });

            $('#saveNextBtn').click(function() {
                saveAnswersToServer(false);
                if (currentQuestionIndex < questions.length - 1) {
                    loadQuestion(currentQuestionIndex + 1);
                } else {
                    Swal.fire('Info', 'This is the last question', 'info');
                }
            });

            $('#markReviewBtn').click(function() {
                if (markedQuestions[currentQuestionIndex]) {
                    delete markedQuestions[currentQuestionIndex];
                } else {
                    markedQuestions[currentQuestionIndex] = true;
                }
                loadQuestion(currentQuestionIndex);
            });

            $('#submitExamBtn').click(function() {
                submitExam();
            });

            $('#fullscreenBtn').click(function() {
                toggleFullscreen();
            });

            // Keyboard shortcuts
            $(document).keydown(function(e) {
                if (e.key === 'ArrowLeft' && currentQuestionIndex > 0) {
                    e.preventDefault();
                    loadQuestion(currentQuestionIndex - 1);
                } else if (e.key === 'ArrowRight' && currentQuestionIndex < questions.length - 1) {
                    e.preventDefault();
                    loadQuestion(currentQuestionIndex + 1);
                } else if (e.key === 'm' || e.key === 'M') {
                    e.preventDefault();
                    $('#markReviewBtn').click();
                }
            });

            // Prevent page unload during exam
            beforeUnloadHandler = function(e) {
                e.preventDefault();
                e.returnValue = 'You have an ongoing exam. Are you sure you want to leave?';
                return e.returnValue;
            };
            window.addEventListener('beforeunload', beforeUnloadHandler);
        }

        function setupAntiCheating() {
            // Disable right-click
            if (examData && examData.disable_copy_paste) {
                document.addEventListener('contextmenu', function(e) {
                    e.preventDefault();
                    logViolation('right_click');
                    showWarning('Right-click is disabled during exam');
                });

                // Disable copy
                document.addEventListener('copy', function(e) {
                    e.preventDefault();
                    logViolation('copy_attempt');
                    showWarning('Copy is disabled during exam');
                });

                // Disable paste
                document.addEventListener('paste', function(e) {
                    e.preventDefault();
                    logViolation('paste_attempt');
                    showWarning('Paste is disabled during exam');
                });

                // Disable cut
                document.addEventListener('cut', function(e) {
                    e.preventDefault();
                    logViolation('cut_attempt');
                    showWarning('Cut is disabled during exam');
                });

                // Disable keyboard shortcuts
                document.addEventListener('keydown', function(e) {
                    // Ctrl+C, Ctrl+V, Ctrl+X, Ctrl+A, Ctrl+P
                    if (e.ctrlKey && (e.key === 'c' || e.key === 'v' || e.key === 'x' || e.key === 'a' || e.key === 'p')) {
                        e.preventDefault();
                        logViolation('keyboard_shortcut');
                        showWarning('Keyboard shortcuts are disabled during exam');
                    }
                });
            }

            // Detect tab/window switch
            if (examData && examData.detect_tab_switch) {
                document.addEventListener('visibilitychange', function() {
                    if (document.hidden) {
                        tabSwitchCount++;
                        logViolation('tab_switch');
                        showWarning('Tab switching detected! Count: ' + tabSwitchCount);

                        // Save violation to server
                        $.ajax({
                            url: '../api/student/log_violation.php',
                            method: 'POST',
                            data: JSON.stringify({
                                student_exam_id: studentExamId,
                                violation_type: 'tab_switch',
                                count: tabSwitchCount
                            }),
                            contentType: 'application/json'
                        });
                    }
                });

                window.addEventListener('blur', function() {
                    logViolation('window_blur');
                });
            }

            // Detect fullscreen exit
            document.addEventListener('fullscreenchange', function() {
                if (!document.fullscreenElement && examData && examData.full_screen_required) {
                    logViolation('fullscreen_exit');
                    showWarning('Please stay in fullscreen mode');
                }
            });
        }

        function logViolation(type) {
            violations.push({
                type: type,
                timestamp: new Date().toISOString()
            });
        }

        function showWarning(message) {
            $('#warningText').text(message);
            $('#warningBanner').fadeIn().delay(3000).fadeOut();
        }

        function enterFullscreen() {
            if (!document.fullscreenElement) {
                const elem = document.documentElement;
                if (elem.requestFullscreen) {
                    elem.requestFullscreen();
                } else if (elem.webkitRequestFullscreen) { /* Safari */
                    elem.webkitRequestFullscreen();
                } else if (elem.msRequestFullscreen) { /* IE11 */
                    elem.msRequestFullscreen();
                }
                $('#fullscreenBtn i').removeClass('fa-expand').addClass('fa-compress');
            }
        }
        
        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                enterFullscreen();
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) { /* Safari */
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) { /* IE11 */
                    document.msExitFullscreen();
                }
                $('#fullscreenBtn i').removeClass('fa-compress').addClass('fa-expand');
            }
        }

        function submitExam() {
            // Save current question's text answer if any
            const currentQuestion = questions[currentQuestionIndex];
            if (currentQuestion && (currentQuestion.question_type === 'short_answer' || currentQuestion.question_type === 'long_answer')) {
                const answerElement = document.getElementById('answer_' + currentQuestion.id);
                if (answerElement) {
                    answers[currentQuestion.id] = answerElement.value;
                }
            }
            
            // Count unanswered questions
            let unanswered = 0;
            questions.forEach(q => {
                if (!answers[q.id] || answers[q.id] === '' ||
                    (Array.isArray(answers[q.id]) && answers[q.id].length === 0)) {
                    unanswered++;
                }
            });

            let confirmText = 'Are you sure you want to submit your exam?';
            if (unanswered > 0) {
                confirmText += `\n\n⚠️ You have ${unanswered} unanswered questions.`;
            }

            Swal.fire({
                title: 'Submit Exam?',
                text: confirmText,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Submit!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Stop timer and auto-save
                    clearInterval(timerInterval);
                    clearInterval(autoSaveInterval);

                    // Submit to server
                    $.ajax({
                        url: '../api/student/submit_exam.php',
                        method: 'POST',
                        data: JSON.stringify({
                            student_exam_id: studentExamId,
                            answers: answers,
                            marked_questions: Object.keys(markedQuestions),
                            tab_switch_count: tabSwitchCount,
                            violations: violations
                        }),
                        contentType: 'application/json',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                // Remove beforeunload warning
                                window.removeEventListener('beforeunload', beforeUnloadHandler);
                                
                                Swal.fire({
                                    title: 'Exam Submitted!',
                                    text: 'Your exam has been submitted successfully',
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.location.href = 'exam_result.php?id=' + studentExamId;
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Failed to submit exam', 'error');
                        }
                    });
                }
            });
        }

        function autoSubmitExam() {
            // Remove beforeunload warning
            window.removeEventListener('beforeunload', beforeUnloadHandler);
            
            Swal.fire({
                title: 'Time Up!',
                text: 'Your exam is being auto-submitted...',
                icon: 'warning',
                showConfirmButton: false,
                allowOutsideClick: false
            });

            clearInterval(autoSaveInterval);

            $.ajax({
                url: '../api/student/submit_exam.php',
                method: 'POST',
                data: JSON.stringify({
                    student_exam_id: studentExamId,
                    answers: answers,
                    marked_questions: Object.keys(markedQuestions),
                    tab_switch_count: tabSwitchCount,
                    violations: violations,
                    auto_submit: true
                }),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    Swal.fire({
                        title: 'Exam Auto-Submitted',
                        text: 'Time ended. Your exam has been submitted.',
                        icon: 'info',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = 'exam_result.php?id=' + studentExamId;
                    });
                }
            });
        }
    </script>
</body>

</html>