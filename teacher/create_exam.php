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
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get teacher_id from session
if (!isset($_SESSION['faculty_id'])) {
    // Try to fetch faculty_id from database
    $query = "SELECT id FROM faculties WHERE user_id = :user_id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $faculty = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($faculty) {
        $_SESSION['faculty_id'] = $faculty['id'];
    } else {
        $_SESSION['error'] = "Faculty profile not found. Please contact administrator.";
        header("Location: ../auth/logout.php");
        exit;
    }
}

$teacher_id = $_SESSION['faculty_id'];
$exam_id = isset($_GET['id']) ? $_GET['id'] : null;

// If editing, load exam data
$exam = null;
if ($exam_id) {
    $query = "SELECT * FROM exams WHERE id = :id AND created_by_teacher_id = :teacher_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $exam_id, ':teacher_id' => $teacher_id]);
    $exam = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$exam) {
        $_SESSION['error'] = "Exam not found";
        header("Location: manage_exams.php");
        exit;
    }
}

// Now include header after all redirects are done
$pageTitle = isset($_GET['id']) ? "Edit Exam" : "Create Exam";
$basePath = ".."; // Set base path for includes
require_once '../includes/header.php';
?>

<main class="main-content">
    <div class="container-fluid py-4">
        <div class="row mb-3">
            <div class="col-12">
                <h2><i class="fas fa-file-alt"></i> <?= $pageTitle ?></h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="manage_exams.php">Manage Exams</a></li>
                        <li class="breadcrumb-item active"><?= $pageTitle ?></li>
                    </ol>
                </nav>
            </div>
        </div>

        <form id="examForm">
            <input type="hidden" name="exam_id" id="exam_id" value="<?= $exam['id'] ?? '' ?>">

            <!-- Step 1: Basic Details -->
            <div class="card mb-4 step-card" id="step1">
                <div class="card-header bg-primary text-black">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Step 1: Basic Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Exam Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" id="title"
                                value="<?= $exam['title'] ?? '' ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Exam Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="exam_type" id="exam_type" required>
                                <option value="quiz" <?= ($exam['exam_type'] ?? '') == 'quiz' ? 'selected' : '' ?>>Quiz</option>
                                <option value="test" <?= ($exam['exam_type'] ?? '') == 'test' ? 'selected' : '' ?>>Test</option>
                                <option value="midterm" <?= ($exam['exam_type'] ?? '') == 'midterm' ? 'selected' : '' ?>>Midterm</option>
                                <option value="final" <?= ($exam['exam_type'] ?? '') == 'final' ? 'selected' : '' ?>>Final Exam</option>
                                <option value="assignment" <?= ($exam['exam_type'] ?? '') == 'assignment' ? 'selected' : '' ?>>Assignment</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="description" rows="3"><?= $exam['description'] ?? '' ?></textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <select class="form-select" name="subject_id" id="subject_id" required>
                                <option value="">Select Subject</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Course <span class="text-danger">*</span></label>
                            <select class="form-select" name="course_id" id="course_id" required>
                                <option value="">Select Course</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Batch (Optional)</label>
                            <select class="form-select" name="batch_id" id="batch_id">
                                <option value="">All Batches</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Start Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="start_time" id="start_time"
                                value="<?= isset($exam['start_time']) ? date('Y-m-d\TH:i', strtotime($exam['start_time'])) : '' ?>" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">End Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="end_time" id="end_time"
                                value="<?= isset($exam['end_time']) ? date('Y-m-d\TH:i', strtotime($exam['end_time'])) : '' ?>" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Duration (Minutes) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="duration_minutes" id="duration_minutes"
                                value="<?= $exam['duration_minutes'] ?? 60 ?>" min="1" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Total Marks <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="total_marks" id="total_marks"
                                value="<?= $exam['total_marks'] ?? 100 ?>" min="1" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Passing Marks <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="passing_marks" id="passing_marks"
                                value="<?= $exam['passing_marks'] ?? 40 ?>" min="1" required>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-primary" onclick="saveBasicDetailsAndContinue()">
                            Next: Questions <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 2: Questions -->
            <div class="card mb-4 step-card d-none" id="step2">
                <div class="card-header bg-primary text-black d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-question-circle"></i> Step 2: Add Questions</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-light" onclick="openQuestionBankModal()">
                            <i class="fas fa-database"></i> From Question Bank
                        </button>
                        <button type="button" class="btn btn-sm btn-light" onclick="addNewQuestion()">
                            <i class="fas fa-plus"></i> Add New Question
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="examSavedNotice" class="alert alert-success mb-3" style="display:none;">
                        <i class="fas fa-check-circle"></i> Exam saved! You can now add questions.
                    </div>
                    <div id="questionsContainer">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No questions added yet. Click "Add New Question" or select from Question Bank.
                        </div>
                    </div>

                    <div class="mt-3 d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" onclick="goToStep(1)">
                            <i class="fas fa-arrow-left"></i> Previous
                        </button>
                        <button type="button" class="btn btn-primary" onclick="goToStep(3)">
                            Next: Settings <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 3: Exam Settings -->
            <div class="card mb-4 step-card d-none" id="step3">
                <div class="card-header bg-primary text-black">
                    <h5 class="mb-0"><i class="fas fa-cog"></i> Step 3: Exam Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2">Display Settings</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="allow_review" id="allow_review"
                                    <?= ($exam['allow_review'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="allow_review">
                                    Allow students to review answers after submission
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="show_correct_answers" id="show_correct_answers"
                                    <?= ($exam['show_correct_answers'] ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="show_correct_answers">
                                    Show correct answers in review
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="randomize_questions" id="randomize_questions"
                                    <?= ($exam['randomize_questions'] ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="randomize_questions">
                                    Randomize question order
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="randomize_options" id="randomize_options"
                                    <?= ($exam['randomize_options'] ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="randomize_options">
                                    Randomize answer options
                                </label>
                            </div>

                            <h6 class="border-bottom pb-2 mt-3">Attempt Settings</h6>
                            <div class="mb-3">
                                <label class="form-label">Maximum Attempts</label>
                                <input type="number" class="form-control" name="max_attempts" id="max_attempts"
                                    value="<?= $exam['max_attempts'] ?? 1 ?>" min="1" max="10">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Show Results</label>
                                <select class="form-select" name="show_results_after" id="show_results_after">
                                    <option value="immediate" <?= ($exam['show_results_after'] ?? 'submit') == 'immediate' ? 'selected' : '' ?>>Immediately after submission</option>
                                    <option value="submit" <?= ($exam['show_results_after'] ?? 'submit') == 'submit' ? 'selected' : '' ?>>After exam window closes</option>
                                    <option value="manual" <?= ($exam['show_results_after'] ?? 'submit') == 'manual' ? 'selected' : '' ?>>Manual release by teacher</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2">Anti-Cheating Settings</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="enable_proctoring" id="enable_proctoring"
                                    <?= ($exam['enable_proctoring'] ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="enable_proctoring">
                                    Enable proctoring (experimental)
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="detect_tab_switch" id="detect_tab_switch"
                                    <?= ($exam['detect_tab_switch'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="detect_tab_switch">
                                    Detect tab switching
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="disable_copy_paste" id="disable_copy_paste"
                                    <?= ($exam['disable_copy_paste'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="disable_copy_paste">
                                    Disable copy/paste
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="full_screen_required" id="full_screen_required"
                                    <?= ($exam['full_screen_required'] ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="full_screen_required">
                                    Require full-screen mode
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="auto_submit_on_time_end" id="auto_submit_on_time_end"
                                    <?= ($exam['auto_submit_on_time_end'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="auto_submit_on_time_end">
                                    Auto-submit when time expires
                                </label>
                            </div>

                            <h6 class="border-bottom pb-2 mt-3">Grading Settings</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="negative_marking" id="negative_marking"
                                    <?= ($exam['negative_marking'] ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="negative_marking">
                                    Enable negative marking
                                </label>
                            </div>
                            <div class="mb-3" id="negativeMarksDiv" style="display: <?= ($exam['negative_marking'] ?? 0) ? 'block' : 'none' ?>;">
                                <label class="form-label">Negative Marks per Wrong Answer</label>
                                <input type="number" class="form-control" name="negative_marks_per_question"
                                    id="negative_marks_per_question" value="<?= $exam['negative_marks_per_question'] ?? 0.25 ?>"
                                    step="0.25" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <label class="form-label">Instructions for Students</label>
                            <textarea class="form-control" name="instructions" id="instructions" rows="4"><?= $exam['instructions'] ?? '' ?></textarea>
                        </div>
                    </div>

                    <div class="mt-3 d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" onclick="goToStep(2)">
                            <i class="fas fa-arrow-left"></i> Previous
                        </button>
                        <button type="button" class="btn btn-primary" onclick="goToStep(4)">
                            Next: Preview <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 4: Preview & Submit -->
            <div class="card mb-4 step-card d-none" id="step4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-eye"></i> Step 4: Preview & Submit</h5>
                </div>
                <div class="card-body">
                    <div id="previewContent">
                        <!-- Preview will be generated dynamically -->
                    </div>

                    <div class="mt-4 d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" onclick="goToStep(3)">
                            <i class="fas fa-arrow-left"></i> Previous
                        </button>
                        <div>
                            <button type="button" class="btn btn-outline-primary me-2" onclick="saveAsDraft()">
                                <i class="fas fa-save"></i> Save as Draft
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check"></i> Publish Exam
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Question Bank Modal -->
    <div class="modal fade" id="questionBankModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Questions from Bank</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="bankSearch" placeholder="Search questions...">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="bankTypeFilter">
                                <option value="">All Types</option>
                                <option value="mcq_single">MCQ (Single)</option>
                                <option value="mcq_multiple">MCQ (Multiple)</option>
                                <option value="true_false">True/False</option>
                                <option value="short_answer">Short Answer</option>
                                <option value="long_answer">Long Answer</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="bankSubjectFilter">
                                <option value="">All Subjects</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100" onclick="searchQuestionBank()">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                    <div id="questionBankList" style="max-height: 400px; overflow-y: auto;">
                        <!-- Questions will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" onclick="deleteSelectedBankQuestions()">
                        <i class="fas fa-trash"></i> Delete Selected
                    </button>
                    <button type="button" class="btn btn-primary" onclick="addSelectedQuestions()">
                        <i class="fas fa-plus"></i> Add Selected Questions
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Question Modal -->
    <div class="modal fade" id="addQuestionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addQuestionModalTitle">Add New Question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addQuestionForm">
                        <input type="hidden" id="editingQuestionId" value="">
                        <div class="mb-3">
                            <label class="form-label">Question Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="newQuestionType" required onchange="updateQuestionForm()">
                                <option value="mcq_single">Multiple Choice (Single Answer)</option>
                                <option value="mcq_multiple">Multiple Choice (Multiple Answers)</option>
                                <option value="true_false">True/False</option>
                                <option value="short_answer">Short Answer</option>
                                <option value="long_answer">Long Answer</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Question Text <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="newQuestionText" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Marks <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="newQuestionMarks" value="1" min="0.25" step="0.25" required>
                        </div>
                        <div id="optionsContainer">
                            <!-- Options will be added dynamically based on question type -->
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="saveToBank">
                            <label class="form-check-label" for="saveToBank">
                                Save to Question Bank for future use
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveNewQuestion()">
                        <i class="fas fa-plus"></i> Add Question
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .question-item {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background: #f8f9fa;
        }

        .question-item:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .question-number {
            display: inline-block;
            width: 35px;
            height: 35px;
            line-height: 35px;
            text-align: center;
            background: #0d6efd;
            color: white;
            border-radius: 50%;
            font-weight: bold;
            margin-right: 10px;
        }

        .option-badge {
            display: inline-block;
            padding: 5px 10px;
            margin: 3px;
            border-radius: 5px;
            background: #e9ecef;
        }

        .option-badge.correct {
            background: #d1e7dd;
            border: 1px solid #0f5132;
        }
    </style>

    <script>
        let currentStep = 1;
        let questions = [];
        let selectedBankQuestions = [];

        $(document).ready(function() {
            loadSubjects();
            loadCourses();

            // Toggle negative marks input
            $('#negative_marking').change(function() {
                $('#negativeMarksDiv').toggle(this.checked);
            });

            // Update question form when type changes
            updateQuestionForm();

            // Load existing questions if editing
            <?php if ($exam_id): ?>
                $('#examSavedNotice').show();
                loadExistingQuestions(<?= $exam_id ?>);
            <?php endif; ?>

            // Trigger course change if editing
            <?php if ($exam_id && !empty($exam['course_id'])): ?>
                setTimeout(function() {
                    $('#course_id').trigger('change');
                }, 500);
            <?php endif; ?>
        });

        function goToStep(step) {
            $('.step-card').addClass('d-none');
            $('#step' + step).removeClass('d-none');
            currentStep = step;

            if (step === 4) {
                generatePreview();
            }

            $('html, body').animate({
                scrollTop: 0
            }, 300);
        }

        function saveBasicDetailsAndContinue() {
            console.log('Button clicked - starting validation');

            // Validate required fields
            if (!$('#title').val()) {
                Swal.fire('Error', 'Please enter exam title', 'error');
                return;
            }
            if (!$('#subject_id').val()) {
                Swal.fire('Error', 'Please select a subject', 'error');
                return;
            }
            if (!$('#course_id').val()) {
                Swal.fire('Error', 'Please select a course', 'error');
                return;
            }
            if (!$('#start_time').val() || !$('#end_time').val()) {
                Swal.fire('Error', 'Please select start and end date/time', 'error');
                return;
            }

            console.log('Validation passed');

            // Check if exam already exists
            if ($('#exam_id').val()) {
                console.log('Exam already exists, moving to step 2');
                goToStep(2);
                return;
            }

            console.log('Saving new exam...');

            // Save exam as draft first
            const formData = {
                title: $('#title').val(),
                description: $('#description').val(),
                exam_type: $('#exam_type').val(),
                subject_id: $('#subject_id').val(),
                course_id: $('#course_id').val(),
                batch_id: $('#batch_id').val() || null,
                start_time: $('#start_time').val(),
                end_time: $('#end_time').val(),
                duration_minutes: $('#duration_minutes').val(),
                total_marks: $('#total_marks').val(),
                passing_marks: $('#passing_marks').val(),
                max_attempts: 1,
                instructions: '',
                is_active: 0 // Save as draft
            };

            console.log('Form data:', formData);

            $.ajax({
                url: '../api/teacher/exams.php',
                method: 'POST',
                data: JSON.stringify(formData),
                contentType: 'application/json',
                beforeSend: function() {
                    console.log('Sending AJAX request...');
                    Swal.fire({
                        title: 'Saving...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response) {
                    console.log('Success response:', response);
                    if (response.success) {
                        // API returns exam_id directly, not nested in data object
                        const examId = response.exam_id || (response.data && response.data.id);
                        $('#exam_id').val(examId);
                        console.log('Exam ID set to:', examId);
                        Swal.fire({
                            title: 'Saved!',
                            text: 'Basic details saved as draft. You can now add questions.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        $('#examSavedNotice').show();
                        goToStep(2);
                    } else {
                        Swal.fire('Error', response.message || 'Failed to save exam', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', xhr, status, error);
                    console.error('Response text:', xhr.responseText);
                    let errorMsg = 'Failed to save exam';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMsg = response.message || errorMsg;
                    } catch (e) {
                        errorMsg = xhr.responseText || errorMsg;
                    }
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        }

        function loadSubjects() {
            $.get('../api/teacher/my_subjects.php', function(response) {
                if (response.success) {
                    let options = '<option value="">Select Subject</option>';
                    response.data.forEach(subject => {
                        let selected = <?= $exam['subject_id'] ?? 0 ?> == subject.id ? 'selected' : '';
                        options += `<option value="${subject.id}" ${selected}>${subject.title}</option>`;
                    });
                    $('#subject_id, #bankSubjectFilter').html(options);
                }
            });
        }

        function loadCourses() {
            $.get('../api/common/courses.php', function(response) {
                if (response.success) {
                    let options = '<option value="">Select Course</option>';
                    response.data.forEach(course => {
                        let selected = <?= $exam['course_id'] ?? 0 ?> == course.id ? 'selected' : '';
                        options += `<option value="${course.id}" ${selected}>${course.title}</option>`;
                    });
                    $('#course_id').html(options);
                }
            });
        }

        $('#course_id').change(function() {
            const courseId = $(this).val();
            if (courseId) {
                $.get('../api/common/batches.php?course_id=' + courseId, function(response) {
                    if (response.success) {
                        let options = '<option value="">All Batches</option>';
                        response.data.forEach(batch => {
                            let selected = <?= $exam['batch_id'] ?? 0 ?> == batch.id ? 'selected' : '';
                            options += `<option value="${batch.id}" ${selected}>${batch.name}</option>`;
                        });
                        $('#batch_id').html(options);
                    }
                });
            }
        });

        function openQuestionBankModal() {
            const examId = $('#exam_id').val();
            if (!examId) {
                Swal.fire({
                    title: 'Save Exam First',
                    text: 'Please save the exam basic details first before adding questions.',
                    icon: 'warning',
                    confirmButtonText: 'Go to Step 1'
                }).then(() => {
                    goToStep(1);
                });
                return;
            }
            $('#questionBankModal').modal('show');
            searchQuestionBank();
        }

        function searchQuestionBank() {
            const search = $('#bankSearch').val();
            const type = $('#bankTypeFilter').val();
            const subject = $('#bankSubjectFilter').val();

            $.get('../api/teacher/question_bank.php', {
                search,
                type,
                subject_id: subject
            }, function(response) {
                if (response.success) {
                    let html = '';
                    response.data.forEach(q => {
                        const checked = selectedBankQuestions.includes(q.id) ? 'checked' : '';
                        html += `
                    <div class="card mb-2">
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="${q.id}" 
                                       id="bank_q_${q.id}" ${checked}>
                                <label class="form-check-label" for="bank_q_${q.id}">
                                    <strong>${q.question_text}</strong>
                                    <br><small class="text-muted">Type: ${q.question_type} | Difficulty: ${q.difficulty || 'N/A'}</small>
                                </label>
                            </div>
                        </div>
                    </div>
                `;
                    });
                    $('#questionBankList').html(html || '<div class="alert alert-info">No questions found</div>');
                }
            });
        }

        function addSelectedQuestions() {
            selectedBankQuestions = [];
            $('#questionBankList input:checked').each(function() {
                selectedBankQuestions.push($(this).val());
            });

            if (selectedBankQuestions.length === 0) {
                Swal.fire('Warning', 'Please select at least one question', 'warning');
                return;
            }

            console.log('Adding questions:', selectedBankQuestions);

            // Add questions to exam
            $.ajax({
                url: '../api/teacher/add_bank_questions.php',
                method: 'POST',
                data: JSON.stringify({
                    exam_id: parseInt($('#exam_id').val()),
                    question_ids: selectedBankQuestions.map(id => parseInt(id))
                }),
                contentType: 'application/json',
                success: function(response) {
                    console.log('Add questions response:', response);
                    if (response.success) {
                        Swal.fire('Success', `${selectedBankQuestions.length} questions added`, 'success');
                        loadExamQuestions();
                        $('#questionBankModal').modal('hide');
                    } else {
                        Swal.fire('Error', response.message || 'Failed to add questions', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Add questions error:', xhr, status, error);
                    console.error('Response text:', xhr.responseText);
                    let errorMsg = 'Failed to add questions';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMsg = response.message || errorMsg;
                    } catch (e) {
                        errorMsg = xhr.responseText || errorMsg;
                    }
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        }

        function deleteSelectedBankQuestions() {
            const selectedQuestions = [];
            $('#questionBankList input:checked').each(function() {
                selectedQuestions.push($(this).val());
            });

            if (selectedQuestions.length === 0) {
                Swal.fire('Warning', 'Please select at least one question to delete', 'warning');
                return;
            }

            Swal.fire({
                title: 'Delete Questions?',
                text: `Are you sure you want to permanently delete ${selectedQuestions.length} question(s) from the question bank?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete them!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Delete questions from bank
                    const deletePromises = selectedQuestions.map(questionId => {
                        return $.ajax({
                            url: '../api/teacher/question_bank.php?id=' + questionId,
                            method: 'DELETE'
                        });
                    });

                    Promise.all(deletePromises)
                        .then(() => {
                            Swal.fire('Deleted!', `${selectedQuestions.length} question(s) deleted from question bank`, 'success');
                            searchQuestionBank(); // Refresh the list
                        })
                        .catch((error) => {
                            console.error('Delete error:', error);
                            Swal.fire('Error', 'Failed to delete some questions', 'error');
                        });
                }
            });
        }

        function addNewQuestion() {
            const examId = $('#exam_id').val();
            if (!examId) {
                Swal.fire({
                    title: 'Save Exam First',
                    text: 'Please save the exam basic details first before adding questions.',
                    icon: 'warning',
                    confirmButtonText: 'Go to Step 1'
                }).then(() => {
                    goToStep(1);
                });
                return;
            }
            // Reset form for new question
            $('#editingQuestionId').val('');
            $('#addQuestionModalTitle').text('Add New Question');
            $('#newQuestionText').val('');
            $('#newQuestionMarks').val(1);
            $('#newQuestionType').val('mcq_single');
            $('#saveToBank').prop('checked', false);
            $('#addQuestionModal').modal('show');
            updateQuestionForm();
        }

        function editQuestion(questionId) {
            // Find the question in the questions array
            const question = questions.find(q => q.id == questionId);
            if (!question) {
                Swal.fire('Error', 'Question not found', 'error');
                return;
            }

            // Set editing mode
            $('#editingQuestionId').val(questionId);
            $('#addQuestionModalTitle').text('Edit Question');
            
            // Populate form fields
            $('#newQuestionText').val(question.question_text);
            $('#newQuestionMarks').val(question.marks);
            $('#newQuestionType').val(question.question_type);
            
            // Update form based on question type
            updateQuestionForm();
            
            // Populate options for MCQ questions
            if (question.question_type === 'mcq_single' || question.question_type === 'mcq_multiple') {
                setTimeout(function() {
                    $('#mcqOptions').empty();
                    const inputType = question.question_type === 'mcq_multiple' ? 'checkbox' : 'radio';
                    
                    question.options.forEach((opt, index) => {
                        const optNum = index + 1;
                        const html = `
                            <div class="input-group mb-2 mcq-option">
                                <div class="input-group-text">
                                    <input class="form-check-input" type="${inputType}" 
                                           name="correctOption" value="${optNum}" ${opt.is_correct ? 'checked' : ''}>
                                </div>
                                <input type="text" class="form-control" placeholder="Option ${optNum}" 
                                       id="option_${optNum}" value="${opt.option_text}">
                                ${index > 1 ? '<button type="button" class="btn btn-outline-danger" onclick="$(this).parent().remove()"><i class="fas fa-times"></i></button>' : ''}
                            </div>
                        `;
                        $('#mcqOptions').append(html);
                    });
                    optionCounter = question.options.length + 1;
                }, 100);
            } else if (question.question_type === 'true_false') {
                setTimeout(function() {
                    const correctAnswer = question.options.find(opt => opt.is_correct);
                    if (correctAnswer) {
                        const tfValue = correctAnswer.option_text.toLowerCase();
                        $(`input[name="tfAnswer"][value="${tfValue}"]`).prop('checked', true);
                    }
                }, 100);
            }
            
            $('#addQuestionModal').modal('show');
        }

        function updateQuestionForm() {
            const type = $('#newQuestionType').val();
            let html = '';

            if (type === 'mcq_single' || type === 'mcq_multiple') {
                html = `
            <label class="form-label">Options <span class="text-danger">*</span></label>
            <div id="mcqOptions">
                ${generateOptionInput(1, type)}
                ${generateOptionInput(2, type)}
                ${generateOptionInput(3, type)}
                ${generateOptionInput(4, type)}
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addMCQOption()">
                <i class="fas fa-plus"></i> Add Option
            </button>
        `;
            } else if (type === 'true_false') {
                html = `
            <label class="form-label">Correct Answer <span class="text-danger">*</span></label>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="tfAnswer" id="tfTrue" value="true" checked>
                <label class="form-check-label" for="tfTrue">True</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="tfAnswer" id="tfFalse" value="false">
                <label class="form-check-label" for="tfFalse">False</label>
            </div>
        `;
            }

            $('#optionsContainer').html(html);
        }

        function generateOptionInput(num, type) {
            const inputType = type === 'mcq_multiple' ? 'checkbox' : 'radio';
            return `
        <div class="input-group mb-2 mcq-option">
            <div class="input-group-text">
                <input class="${inputType === 'checkbox' ? 'form-check-input' : 'form-check-input'}" 
                       type="${inputType}" name="correctOption" value="${num}">
            </div>
            <input type="text" class="form-control" placeholder="Option ${num}" id="option_${num}">
            ${num > 2 ? '<button type="button" class="btn btn-outline-danger" onclick="$(this).parent().remove()"><i class="fas fa-times"></i></button>' : ''}
        </div>
    `;
        }

        let optionCounter = 5;

        function addMCQOption() {
            const type = $('#newQuestionType').val();
            $('#mcqOptions').append(generateOptionInput(optionCounter++, type));
        }

        function saveNewQuestion() {
            // Validate form fields
            if (!$('#newQuestionText').val().trim()) {
                Swal.fire('Error', 'Please enter question text', 'error');
                return;
            }

            if (!$('#newQuestionMarks').val()) {
                Swal.fire('Error', 'Please enter marks', 'error');
                return;
            }

            const editingQuestionId = $('#editingQuestionId').val();
            const isEditing = !!editingQuestionId;

            // Validate and save question
            const questionData = {
                question_type: $('#newQuestionType').val(),
                question_text: $('#newQuestionText').val(),
                marks: parseFloat($('#newQuestionMarks').val()),
                exam_id: parseInt($('#exam_id').val()),
                save_to_bank: $('#saveToBank').is(':checked')
            };

            // Don't include id in body for PUT requests (it's in the URL)
            // Only include for POST to link to exam
            if (!isEditing) {
                questionData.exam_id = parseInt($('#exam_id').val());
            }

            console.log(isEditing ? 'Updating question:' : 'Saving question:', questionData);

            // Collect options
            if (questionData.question_type.includes('mcq') || questionData.question_type === 'true_false') {
                questionData.options = [];

                if (questionData.question_type === 'true_false') {
                    const tfAnswer = $('input[name="tfAnswer"]:checked').val();
                    questionData.options = [{
                            text: 'True',
                            is_correct: tfAnswer === 'true',
                            order: 1
                        },
                        {
                            text: 'False',
                            is_correct: tfAnswer === 'false',
                            order: 2
                        }
                    ];
                } else {
                    $('.mcq-option').each(function(index) {
                        const optionText = $(this).find('input[type="text"]').val();
                        const isCorrect = $(this).find('input[type="radio"], input[type="checkbox"]').is(':checked');
                        if (optionText && optionText.trim()) {
                            questionData.options.push({
                                text: optionText.trim(),
                                is_correct: isCorrect,
                                order: index + 1
                            });
                        }
                    });

                    // Validate at least 2 options
                    if (questionData.options.length < 2) {
                        Swal.fire('Error', 'Please provide at least 2 options', 'error');
                        return;
                    }

                    // Validate at least one correct answer
                    if (!questionData.options.some(opt => opt.is_correct)) {
                        Swal.fire('Error', 'Please mark at least one option as correct', 'error');
                        return;
                    }
                }
            }

            console.log('Question data with options:', questionData);

            const apiUrl = isEditing 
                ? '../api/teacher/save_question.php?id=' + editingQuestionId
                : '../api/teacher/save_question.php';

            $.ajax({
                url: apiUrl,
                method: isEditing ? 'PUT' : 'POST',
                data: JSON.stringify(questionData),
                contentType: 'application/json',
                success: function(response) {
                    console.log('Save question response:', response);
                    if (response.success) {
                        Swal.fire('Success', isEditing ? 'Question updated successfully' : 'Question added successfully', 'success');
                        $('#addQuestionModal').modal('hide');
                        // Reset form
                        $('#newQuestionText').val('');
                        $('#newQuestionMarks').val(1);
                        $('#saveToBank').prop('checked', false);
                        loadExamQuestions();
                    } else {
                        Swal.fire('Error', response.message || 'Failed to add question', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Save question error:', xhr, status, error);
                    console.error('Response text:', xhr.responseText);
                    let errorMsg = 'Failed to add question';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMsg = response.message || errorMsg;
                    } catch (e) {
                        errorMsg = xhr.responseText || errorMsg;
                    }
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        }

        function loadExamQuestions() {
            const examId = $('#exam_id').val();
            if (!examId) return;

            $.get('../api/teacher/exam_questions.php?exam_id=' + examId, function(response) {
                if (response.success) {
                    displayQuestions(response.data);
                }
            });
        }

        function displayQuestions(questionList) {
            questions = questionList;
            let html = '';

            if (questions.length === 0) {
                html = '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No questions added yet.</div>';
            } else {
                questions.forEach((q, index) => {
                    html += `
                <div class="question-item" data-question-id="${q.id}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <span class="question-number">${index + 1}</span>
                            <strong>${q.question_text}</strong>
                            <div class="mt-2">
                                <span class="badge bg-info">${q.question_type.replace('_', ' ').toUpperCase()}</span>
                                <span class="badge bg-success">${q.marks} marks</span>
                            </div>
                            ${q.options ? '<div class="mt-2">' + q.options.map(opt => 
                                `<span class="option-badge ${opt.is_correct ? 'correct' : ''}">${opt.option_text}</span>`
                            ).join('') + '</div>' : ''}
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="editQuestion(${q.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeQuestion(${q.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
                });
            }

            $('#questionsContainer').html(html);
        }

        function removeQuestion(questionId) {
            Swal.fire({
                title: 'Remove Question?',
                text: 'Are you sure you want to remove this question?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, remove it'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '../api/teacher/exam_questions.php?id=' + questionId,
                        method: 'DELETE',
                        success: function(response) {
                            if (response.success) {
                                loadExamQuestions();
                            }
                        }
                    });
                }
            });
        }

        function generatePreview() {
            const formData = $('#examForm').serializeArray();
            let preview = '<div class="row">';

            preview += `
        <div class="col-md-6">
            <h6 class="text-primary">Basic Details</h6>
            <table class="table table-sm">
                <tr><td><strong>Title:</strong></td><td>${$('#title').val()}</td></tr>
                <tr><td><strong>Type:</strong></td><td>${$('#exam_type option:selected').text()}</td></tr>
                <tr><td><strong>Duration:</strong></td><td>${$('#duration_minutes').val()} minutes</td></tr>
                <tr><td><strong>Total Marks:</strong></td><td>${$('#total_marks').val()}</td></tr>
                <tr><td><strong>Passing Marks:</strong></td><td>${$('#passing_marks').val()}</td></tr>
                <tr><td><strong>Start Time:</strong></td><td>${$('#start_time').val()}</td></tr>
                <tr><td><strong>End Time:</strong></td><td>${$('#end_time').val()}</td></tr>
            </table>
        </div>
        <div class="col-md-6">
            <h6 class="text-primary">Settings</h6>
            <table class="table table-sm">
                <tr><td><strong>Max Attempts:</strong></td><td>${$('#max_attempts').val()}</td></tr>
                <tr><td><strong>Randomize Questions:</strong></td><td>${$('#randomize_questions').is(':checked') ? 'Yes' : 'No'}</td></tr>
                <tr><td><strong>Randomize Options:</strong></td><td>${$('#randomize_options').is(':checked') ? 'Yes' : 'No'}</td></tr>
                <tr><td><strong>Allow Review:</strong></td><td>${$('#allow_review').is(':checked') ? 'Yes' : 'No'}</td></tr>
                <tr><td><strong>Tab Switch Detection:</strong></td><td>${$('#detect_tab_switch').is(':checked') ? 'Enabled' : 'Disabled'}</td></tr>
                <tr><td><strong>Negative Marking:</strong></td><td>${$('#negative_marking').is(':checked') ? 'Yes' : 'No'}</td></tr>
            </table>
        </div>
    `;

            preview += `
        </div>
        <div class="mt-3">
            <h6 class="text-primary">Questions (${questions.length})</h6>
            <div class="alert ${questions.length > 0 ? 'alert-success' : 'alert-warning'}">
                ${questions.length} question(s) added to this exam
            </div>
        </div>
    `;

            $('#previewContent').html(preview);
        }

        function saveAsDraft() {
            submitExam(0);
        }

        $('#examForm').submit(function(e) {
            e.preventDefault();
            submitExam(1);
        });

        function submitExam(isActive) {
            console.log('submitExam called with isActive:', isActive);

            const examId = $('#exam_id').val();
            console.log('Exam ID:', examId);

            // Validate required fields
            if (!$('#title').val()) {
                Swal.fire('Error', 'Please enter exam title', 'error');
                return;
            }
            if (!$('#subject_id').val()) {
                Swal.fire('Error', 'Please select a subject', 'error');
                return;
            }
            if (!$('#course_id').val()) {
                Swal.fire('Error', 'Please select a course', 'error');
                return;
            }
            if (!$('#start_time').val() || !$('#end_time').val()) {
                Swal.fire('Error', 'Please select start and end date/time', 'error');
                return;
            }

            const formData = {
                exam_id: examId,
                id: examId, // Added: API expects 'id' for PUT requests
                title: $('#title').val(),
                description: $('#description').val(),
                exam_type: $('#exam_type').val(),
                subject_id: parseInt($('#subject_id').val()),
                course_id: parseInt($('#course_id').val()),
                batch_id: $('#batch_id').val() ? parseInt($('#batch_id').val()) : null,
                start_time: $('#start_time').val(),
                end_time: $('#end_time').val(),
                duration_minutes: parseInt($('#duration_minutes').val()),
                total_marks: parseInt($('#total_marks').val()),
                passing_marks: parseInt($('#passing_marks').val()),
                max_attempts: parseInt($('#max_attempts').val()),
                allow_review: $('#allow_review').is(':checked') ? 1 : 0,
                show_correct_answers: $('#show_correct_answers').is(':checked') ? 1 : 0,
                randomize_questions: $('#randomize_questions').is(':checked') ? 1 : 0,
                randomize_options: $('#randomize_options').is(':checked') ? 1 : 0,
                show_results_after: $('#show_results_after').val(),
                enable_proctoring: $('#enable_proctoring').is(':checked') ? 1 : 0,
                detect_tab_switch: $('#detect_tab_switch').is(':checked') ? 1 : 0,
                disable_copy_paste: $('#disable_copy_paste').is(':checked') ? 1 : 0,
                full_screen_required: $('#full_screen_required').is(':checked') ? 1 : 0,
                auto_submit_on_time_end: $('#auto_submit_on_time_end').is(':checked') ? 1 : 0,
                negative_marking: $('#negative_marking').is(':checked') ? 1 : 0,
                negative_marks_per_question: parseFloat($('#negative_marks_per_question').val()) || 0,
                instructions: $('#instructions').val(),
                is_active: isActive
            };

            console.log('Form data to submit:', formData);

            const method = examId ? 'PUT' : 'POST';
            console.log('HTTP method:', method);

            $.ajax({
                url: '../api/teacher/exams.php',
                method: method,
                data: JSON.stringify(formData),
                contentType: 'application/json',
                beforeSend: function() {
                    Swal.fire({
                        title: isActive ? 'Publishing...' : 'Saving...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response) {
                    console.log('Submit exam response:', response);
                    if (response.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: isActive ? 'Exam published successfully' : 'Exam saved as draft',
                            icon: 'success'
                        }).then(() => {
                            window.location.href = 'manage_exams.php';
                        });
                    } else {
                        Swal.fire('Error', response.message || 'Failed to save exam', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Submit exam error:', xhr, status, error);
                    console.error('Response text:', xhr.responseText);
                    let errorMsg = 'Failed to save exam';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMsg = response.message || errorMsg;
                    } catch (e) {
                        errorMsg = xhr.responseText || errorMsg;
                    }
                    Swal.fire('Error', errorMsg, 'error');
                }
            });
        }

        function loadExistingQuestions(examId) {
            $.get('../api/teacher/exam_questions.php?exam_id=' + examId, function(response) {
                if (response.success && response.data.length > 0) {
                    displayQuestions(response.data);
                }
            });
        }
    </script>

    <?php require_once '../includes/footer.php'; ?>
</main>