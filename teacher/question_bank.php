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

$pageTitle = "Question Bank";
require_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2><i class="fas fa-database"></i> Question Bank</h2>
            <p class="text-muted">Manage reusable questions for your exams</p>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-primary" onclick="openAddModal()">
                <i class="fas fa-plus"></i> Add Question
            </button>
            <button class="btn btn-success" onclick="openImportModal()">
                <i class="fas fa-file-import"></i> Import from Excel
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <input type="text" class="form-control" id="searchBox" placeholder="Search questions...">
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="typeFilter">
                        <option value="">All Types</option>
                        <option value="mcq_single">MCQ (Single)</option>
                        <option value="mcq_multiple">MCQ (Multiple)</option>
                        <option value="true_false">True/False</option>
                        <option value="short_answer">Short Answer</option>
                        <option value="long_answer">Long Answer</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="subjectFilter">
                        <option value="">All Subjects</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="difficultyFilter">
                        <option value="">All Difficulty</option>
                        <option value="easy">Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" onclick="loadQuestions()">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                        <i class="fas fa-redo"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Questions List -->
    <div class="row" id="questionsContainer">
        <!-- Questions will be loaded here -->
    </div>
</div>

<!-- Add/Edit Question Modal -->
<div class="modal fade" id="questionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="questionForm">
                    <input type="hidden" id="question_id">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <select class="form-select" id="subject_id" required>
                                <option value="">Select Subject</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Question Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="question_type" required onchange="updateOptionsSection()">
                                <option value="mcq_single">MCQ (Single)</option>
                                <option value="mcq_multiple">MCQ (Multiple)</option>
                                <option value="true_false">True/False</option>
                                <option value="short_answer">Short Answer</option>
                                <option value="long_answer">Long Answer</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Difficulty</label>
                            <select class="form-select" id="difficulty">
                                <option value="easy">Easy</option>
                                <option value="medium">Medium</option>
                                <option value="hard">Hard</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Question Text <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="question_text" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Explanation/Hint (Optional)</label>
                        <textarea class="form-control" id="explanation" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tags (comma-separated)</label>
                        <input type="text" class="form-control" id="tags" placeholder="e.g., loops, arrays, basic">
                    </div>

                    <div id="optionsSection">
                        <!-- Options will be dynamically added based on question type -->
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveQuestion()">
                    <i class="fas fa-save"></i> Save Question
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Questions from Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <strong>Excel Format:</strong><br>
                    Columns: Question, Type, Option1, Option2, Option3, Option4, CorrectAnswer, Difficulty, Subject
                </div>
                <div class="mb-3">
                    <label class="form-label">Select Excel File</label>
                    <input type="file" class="form-control" id="excelFile" accept=".xlsx,.xls">
                </div>
                <button class="btn btn-sm btn-outline-primary" onclick="downloadTemplate()">
                    <i class="fas fa-download"></i> Download Template
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="importQuestions()">
                    <i class="fas fa-upload"></i> Import
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .question-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        transition: all 0.3s;
        background: white;
    }

    .question-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }

    .option-item {
        padding: 8px 12px;
        margin: 5px 0;
        border-radius: 5px;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
    }

    .option-item.correct {
        background: #d1e7dd;
        border-color: #0f5132;
    }

    .option-input-group {
        margin-bottom: 10px;
    }
</style>

<script>
    $(document).ready(function() {
        loadSubjects();
        loadQuestions();
    });

    function loadSubjects() {
        $.get('../api/teacher/my_subjects.php', function(response) {
            if (response.success) {
                let options = '<option value="">Select Subject</option>';
                response.data.forEach(subject => {
                    options += `<option value="${subject.id}">${subject.title}</option>`;
                });
                $('#subject_id, #subjectFilter').html(options);
            }
        });
    }

    function loadQuestions() {
        const params = {
            search: $('#searchBox').val(),
            type: $('#typeFilter').val(),
            subject_id: $('#subjectFilter').val(),
            difficulty: $('#difficultyFilter').val()
        };

        $.get('../api/teacher/question_bank.php', params, function(response) {
            if (response.success) {
                displayQuestions(response.data);
            }
        });
    }

    function displayQuestions(questions) {
        let html = '';

        if (questions.length === 0) {
            html = '<div class="col-12"><div class="alert alert-info">No questions found. Add your first question!</div></div>';
        } else {
            questions.forEach((q, index) => {
                const typeIcon = {
                    'mcq_single': 'fa-check-circle',
                    'mcq_multiple': 'fa-check-double',
                    'true_false': 'fa-balance-scale',
                    'short_answer': 'fa-align-left',
                    'long_answer': 'fa-align-justify'
                };

                const difficultyColor = {
                    'easy': 'success',
                    'medium': 'warning',
                    'hard': 'danger'
                };

                html += `
                <div class="col-md-6">
                    <div class="question-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge bg-primary"><i class="fas ${typeIcon[q.question_type] || 'fa-question'}"></i> ${q.question_type.replace('_', ' ').toUpperCase()}</span>
                                <span class="badge bg-${difficultyColor[q.difficulty] || 'secondary'}">${q.difficulty || 'N/A'}</span>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick='editQuestion(${JSON.stringify(q).replace(/'/g, "&#39;")})' title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-outline-danger" onclick="deleteQuestion(${q.id})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        
                        <h6 class="mb-2">${q.question_text}</h6>
                        
                        ${q.options && q.options.length > 0 ? `
                            <div class="mt-2">
                                ${q.options.map(opt => `
                                    <div class="option-item ${opt.is_correct ? 'correct' : ''}">
                                        ${opt.is_correct ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="far fa-circle"></i>'} 
                                        ${opt.option_text}
                                    </div>
                                `).join('')}
                            </div>
                        ` : ''}
                        
                        ${q.explanation ? `<div class="mt-2"><small class="text-muted"><i class="fas fa-lightbulb"></i> ${q.explanation}</small></div>` : ''}
                        
                        ${q.tags ? `<div class="mt-2">${q.tags.split(',').map(tag => `<span class="badge bg-secondary">${tag.trim()}</span>`).join(' ')}</div>` : ''}
                        
                        <div class="mt-2">
                            <small class="text-muted">Subject: ${q.subject_title || 'N/A'} | Used: ${q.usage_count || 0} times</small>
                        </div>
                    </div>
                </div>
            `;
            });
        }

        $('#questionsContainer').html(html);
    }

    function openAddModal() {
        $('#question_id').val('');
        $('#questionForm')[0].reset();
        $('#modalTitle').text('Add Question');
        updateOptionsSection();
        $('#questionModal').modal('show');
    }

    function editQuestion(question) {
        $('#question_id').val(question.id);
        $('#subject_id').val(question.subject_id);
        $('#question_type').val(question.question_type);
        $('#difficulty').val(question.difficulty);
        $('#question_text').val(question.question_text);
        $('#explanation').val(question.explanation);
        $('#tags').val(question.tags);

        $('#modalTitle').text('Edit Question');
        updateOptionsSection(question.options);
        $('#questionModal').modal('show');
    }

    function updateOptionsSection(existingOptions = []) {
        const type = $('#question_type').val();
        let html = '';

        if (type === 'mcq_single' || type === 'mcq_multiple') {
            const inputType = type === 'mcq_multiple' ? 'checkbox' : 'radio';
            html = `
            <label class="form-label">Options <span class="text-danger">*</span></label>
            <div id="optionsContainer">
        `;

            const optionCount = existingOptions.length > 0 ? existingOptions.length : 4;
            for (let i = 0; i < Math.max(optionCount, 2); i++) {
                const option = existingOptions[i] || {
                    option_text: '',
                    is_correct: false
                };
                html += `
                <div class="input-group option-input-group">
                    <div class="input-group-text">
                        <input class="form-check-input mt-0" type="${inputType}" name="correctOption" 
                               value="${i}" ${option.is_correct ? 'checked' : ''}>
                    </div>
                    <input type="text" class="form-control" placeholder="Option ${i + 1}" 
                           value="${option.option_text}" data-option-index="${i}">
                    ${i >= 2 ? `<button type="button" class="btn btn-outline-danger" onclick="$(this).parent().remove()"><i class="fas fa-times"></i></button>` : ''}
                </div>
            `;
            }

            html += `
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addOption()">
                <i class="fas fa-plus"></i> Add Option
            </button>
        `;
        } else if (type === 'true_false') {
            const correctAnswer = existingOptions.length > 0 ? existingOptions.find(o => o.is_correct)?.option_text : 'True';
            html = `
            <label class="form-label">Correct Answer <span class="text-danger">*</span></label>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="tfAnswer" value="True" ${correctAnswer === 'True' ? 'checked' : ''}>
                <label class="form-check-label">True</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="tfAnswer" value="False" ${correctAnswer === 'False' ? 'checked' : ''}>
                <label class="form-check-label">False</label>
            </div>
        `;
        } else {
            html = '<div class="alert alert-info">Descriptive questions don\'t require options. They will be graded manually.</div>';
        }

        $('#optionsSection').html(html);
    }

    let optionCounter = 4;

    function addOption() {
        const type = $('#question_type').val();
        const inputType = type === 'mcq_multiple' ? 'checkbox' : 'radio';
        const html = `
        <div class="input-group option-input-group">
            <div class="input-group-text">
                <input class="form-check-input mt-0" type="${inputType}" name="correctOption" value="${optionCounter}">
            </div>
            <input type="text" class="form-control" placeholder="Option ${optionCounter + 1}" data-option-index="${optionCounter}">
            <button type="button" class="btn btn-outline-danger" onclick="$(this).parent().remove()"><i class="fas fa-times"></i></button>
        </div>
    `;
        $('#optionsContainer').append(html);
        optionCounter++;
    }

    function saveQuestion() {
        const questionData = {
            id: $('#question_id').val(),
            subject_id: $('#subject_id').val(),
            question_type: $('#question_type').val(),
            question_text: $('#question_text').val(),
            difficulty: $('#difficulty').val(),
            explanation: $('#explanation').val(),
            tags: $('#tags').val(),
            options: []
        };

        // Collect options
        const type = questionData.question_type;
        if (type === 'mcq_single' || type === 'mcq_multiple') {
            $('.option-input-group').each(function(index) {
                const optionText = $(this).find('input[type="text"]').val();
                const isCorrect = $(this).find('input[type="radio"], input[type="checkbox"]').is(':checked');
                if (optionText.trim()) {
                    questionData.options.push({
                        option_text: optionText,
                        is_correct: isCorrect ? 1 : 0,
                        option_order: index + 1
                    });
                }
            });

            if (questionData.options.length < 2) {
                Swal.fire('Error', 'Please add at least 2 options', 'error');
                return;
            }

            const hasCorrect = questionData.options.some(opt => opt.is_correct);
            if (!hasCorrect) {
                Swal.fire('Error', 'Please mark at least one correct answer', 'error');
                return;
            }
        } else if (type === 'true_false') {
            const tfAnswer = $('input[name="tfAnswer"]:checked').val();
            questionData.options = [{
                    option_text: 'True',
                    is_correct: tfAnswer === 'True' ? 1 : 0,
                    option_order: 1
                },
                {
                    option_text: 'False',
                    is_correct: tfAnswer === 'False' ? 1 : 0,
                    option_order: 2
                }
            ];
        }

        $.ajax({
            url: '../api/teacher/question_bank.php',
            method: questionData.id ? 'PUT' : 'POST',
            data: JSON.stringify(questionData),
            contentType: 'application/json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', 'Question saved successfully', 'success');
                    $('#questionModal').modal('hide');
                    loadQuestions();
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Failed to save question', 'error');
            }
        });
    }

    function deleteQuestion(id) {
        Swal.fire({
            title: 'Delete Question?',
            text: 'This action cannot be undone',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../api/teacher/question_bank.php?id=' + id,
                    method: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', 'Question has been deleted', 'success');
                            loadQuestions();
                        }
                    }
                });
            }
        });
    }

    function resetFilters() {
        $('#searchBox, #typeFilter, #subjectFilter, #difficultyFilter').val('');
        loadQuestions();
    }

    function openImportModal() {
        $('#importModal').modal('show');
    }

    function downloadTemplate() {
        window.open('../api/teacher/download_template.php', '_blank');
    }

    function importQuestions() {
        const file = $('#excelFile')[0].files[0];
        if (!file) {
            Swal.fire('Error', 'Please select a file', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('file', file);

        $.ajax({
            url: '../api/teacher/import_questions.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', `${response.imported} questions imported successfully`, 'success');
                    $('#importModal').modal('hide');
                    loadQuestions();
                }
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Import failed', 'error');
            }
        });
    }

    // Auto-search on enter
    $('#searchBox').keypress(function(e) {
        if (e.which === 13) loadQuestions();
    });
</script>

<?php require_once '../includes/footer.php'; ?>