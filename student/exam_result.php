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

if (!isset($_GET['id'])) {
    header("Location: exams.php");
    exit;
}

$pageTitle = "Exam Result";
$basePath = "..";
require_once '../includes/header.php';
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();
$student_exam_id = $_GET['id'];
$student_id = $_SESSION['student_id'];

// Get exam result
$query = "SELECT se.*, e.*, s.title as subject_name
          FROM student_exams se
          JOIN exams e ON se.exam_id = e.id
          JOIN subjects s ON e.subject_id = s.id
          WHERE se.id = :id AND se.student_id = :student_id
          AND se.status IN ('submitted', 'graded')";
$stmt = $db->prepare($query);
$stmt->execute([':id' => $student_exam_id, ':student_id' => $student_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    $_SESSION['error'] = "Result not found";
    header("Location: exams.php");
    exit;
}

// Check if results should be shown
$show_results = false;
if ($result['show_results_after'] == 'immediate') {
    $show_results = true;
} elseif ($result['show_results_after'] == 'submit' && strtotime($result['end_time']) < time()) {
    $show_results = true;
} elseif ($result['show_results_after'] == 'manual' && $result['status'] == 'graded') {
    $show_results = true;
}

if (!$show_results) {
    $_SESSION['error'] = "Results are not available yet";
    header("Location: exams.php");
    exit;
}

// Get question-wise results if review is allowed
$questions = [];
if ($result['allow_review']) {
    $qQuery = "SELECT 
                sa.*, 
                qb.question_text, 
                qb.question_type,
                qb.explanation,
                eq.marks as max_marks
              FROM student_answers sa
              JOIN exam_questions eq ON sa.exam_question_id = eq.id
              JOIN question_bank qb ON eq.question_bank_id = qb.id
              WHERE sa.student_exam_id = :student_exam_id
              ORDER BY eq.question_order";
    $stmt = $db->prepare($qQuery);
    $stmt->execute([':student_exam_id' => $student_exam_id]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get options for each question if show_correct_answers is enabled
    if ($result['show_correct_answers']) {
        foreach ($questions as &$question) {
            if (in_array($question['question_type'], ['mcq_single', 'mcq_multiple', 'true_false'])) {
                $optQuery = "SELECT * FROM question_options 
                             WHERE question_bank_id = 
                             (SELECT question_bank_id FROM exam_questions WHERE id = :eq_id)
                             ORDER BY option_order";
                $stmt = $db->prepare($optQuery);
                $stmt->execute([':eq_id' => $question['exam_question_id']]);
                $question['options'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    }
}

// Calculate statistics
$total_questions = count($questions);
$correct_answers = array_filter($questions, fn($q) => $q['is_correct'] == 1);
$correct_count = count($correct_answers);
$wrong_count = $total_questions - $correct_count;
?>

<main class="main-content">
    <!-- Print Only Header -->
    <div class="print-only-header" style="display: none;">
        <div class="text-center mb-4">
            <h2 class="mb-1">Exam Result Report</h2>
            <h4><?= htmlspecialchars($result['title']) ?></h4>
            <p class="mb-1"><strong>Subject:</strong> <?= htmlspecialchars($result['subject_name']) ?></p>
            <p class="mb-1"><strong>Student:</strong> <?= htmlspecialchars($_SESSION['name'] ?? 'Student') ?></p>
            <p class="mb-1"><strong>Submitted:</strong> <?= date('d M Y, h:i A', strtotime($result['submit_time'])) ?></p>
            <p class="mb-1"><strong>Attempt:</strong> <?= $result['attempt_number'] ?> of <?= $result['max_attempts'] ?></p>
            <hr class="my-3">
        </div>
    </div>
    
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-certificate me-2"></i> Exam Result</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="exams.php" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left"></i> Back to Exams
            </a>
            <button class="btn btn-success" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h4><?= htmlspecialchars($result['title']) ?></h4>
                        <p class="text-muted mb-0">
                            <?= htmlspecialchars($result['subject_name']) ?> |
                            Attempt <?= $result['attempt_number'] ?> of <?= $result['max_attempts'] ?> |
                            Submitted: <?= date('d M Y, h:i A', strtotime($result['submit_time'])) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Result Summary -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center <?= $result['is_passed'] ? 'border-success' : 'border-danger' ?>">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Status</h6>
                        <h3 class="<?= $result['is_passed'] ? 'text-success' : 'text-danger' ?>">
                            <i class="fas fa-<?= $result['is_passed'] ? 'check-circle' : 'times-circle' ?>"></i>
                            <?= $result['is_passed'] ? 'PASSED' : 'FAILED' ?>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center border-primary">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Score</h6>
                        <h3 class="text-primary">
                            <?= $result['obtained_marks'] ?> / <?= $result['total_marks'] ?>
                        </h3>
                        <small class="text-muted">Marks Obtained</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center border-info">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Percentage</h6>
                        <h3 class="text-info"><?= number_format($result['percentage'], 2) ?>%</h3>
                        <small class="text-muted">Pass: <?= ($result['passing_marks'] / $result['total_marks'] * 100) ?>%</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center border-warning">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Time Taken</h6>
                        <h3 class="text-warning"><?= $result['time_taken_minutes'] ?> min</h3>
                        <small class="text-muted">Out of <?= $result['duration_minutes'] ?> min</small>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($result['allow_review']): ?>
            <!-- Performance Chart -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Answer Distribution</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="answerChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-info-circle"></i> Exam Statistics</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <td><i class="fas fa-question-circle text-primary"></i> Total Questions:</td>
                                    <td><strong><?= $total_questions ?></strong></td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-check-circle text-success"></i> Correct Answers:</td>
                                    <td><strong><?= $correct_count ?></strong></td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-times-circle text-danger"></i> Wrong Answers:</td>
                                    <td><strong><?= $wrong_count ?></strong></td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-clock text-warning"></i> Time Taken:</td>
                                    <td><strong><?= $result['time_taken_minutes'] ?> minutes</strong></td>
                                </tr>
                                <?php if ($result['tab_switch_count'] > 0): ?>
                                    <tr>
                                        <td><i class="fas fa-exclamation-triangle text-danger"></i> Tab Switches:</td>
                                        <td><strong class="text-danger"><?= $result['tab_switch_count'] ?></strong></td>
                                    </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Question-wise Review -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-list"></i> Question-wise Review</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($questions as $index => $question): ?>
                                <div class="question-review mb-4 p-3 border rounded <?= $question['is_correct'] ? 'border-success bg-light-success' : 'border-danger bg-light-danger' ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6>
                                                <span class="badge <?= $question['is_correct'] ? 'bg-success' : 'bg-danger' ?>">
                                                    Q<?= $index + 1 ?>
                                                </span>
                                                <?= htmlspecialchars($question['question_text']) ?>
                                            </h6>
                                            <small class="text-muted">
                                                Type: <?= ucwords(str_replace('_', ' ', $question['question_type'])) ?> |
                                                Marks: <?= $question['marks_obtained'] ?> / <?= $question['max_marks'] ?>
                                            </small>
                                        </div>
                                        <div>
                                            <span class="badge <?= $question['is_correct'] ? 'bg-success' : 'bg-danger' ?>">
                                                <?= $question['is_correct'] ? '✓ Correct' : '✗ Wrong' ?>
                                            </span>
                                        </div>
                                    </div>

                                    <?php if (in_array($question['question_type'], ['mcq_single', 'mcq_multiple', 'true_false']) && isset($question['options'])): ?>
                                        <div class="mt-3">
                                            <?php
                                            $selectedOptions = $question['selected_option_ids'] ? json_decode($question['selected_option_ids']) : [];
                                            if (!is_array($selectedOptions)) $selectedOptions = [$selectedOptions];
                                            ?>
                                            <?php foreach ($question['options'] as $option): ?>
                                                <div class="option-review mb-2 p-2 rounded <?php
                                                                                            if ($result['show_correct_answers'] && $option['is_correct']) echo 'bg-success text-white';
                                                                                            elseif (in_array($option['id'], $selectedOptions)) echo 'bg-warning';
                                                                                            else echo 'bg-light';
                                                                                            ?>">
                                                    <?php if ($result['show_correct_answers'] && $option['is_correct']): ?>
                                                        <i class="fas fa-check-circle"></i>
                                                    <?php elseif (in_array($option['id'], $selectedOptions)): ?>
                                                        <i class="fas fa-arrow-right"></i>
                                                    <?php else: ?>
                                                        <i class="far fa-circle"></i>
                                                    <?php endif; ?>
                                                    <?= htmlspecialchars($option['option_text']) ?>
                                                    <?php if ($result['show_correct_answers'] && $option['is_correct']): ?>
                                                        <span class="badge bg-light text-dark ms-2">Correct Answer</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php elseif (in_array($question['question_type'], ['short_answer', 'long_answer'])): ?>
                                        <div class="mt-3">
                                            <strong>Your Answer:</strong>
                                            <div class="p-3 bg-light rounded mt-2">
                                                <?= $question['answer_text'] ? nl2br(htmlspecialchars($question['answer_text'])) : '<em class="text-muted">No answer provided</em>' ?>
                                            </div>
                                            <?php if ($question['marks_obtained'] < $question['max_marks'] && $question['marks_obtained'] > 0): ?>
                                                <div class="alert alert-info mt-2 mb-0">
                                                    <i class="fas fa-info-circle"></i> Partially correct. Marks awarded: <?= $question['marks_obtained'] ?> / <?= $question['max_marks'] ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($result['show_correct_answers'] && $question['explanation']): ?>
                                        <div class="mt-3 p-2 bg-info bg-opacity-10 border-start border-info border-4">
                                            <strong><i class="fas fa-lightbulb text-warning"></i> Explanation:</strong>
                                            <p class="mb-0 mt-1"><?= htmlspecialchars($question['explanation']) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
    .bg-light-success {
        background-color: #d1e7dd20;
    }

    .bg-light-danger {
        background-color: #f8d7da20;
    }

    .option-review {
        transition: all 0.3s;
    }

    @media print {
        .btn-toolbar, .sidebar, .navbar {
            display: none !important;
        }
        
        .print-only-header {
            display: block !important;
            page-break-after: avoid;
        }
        
        .d-flex.border-bottom {
            display: none !important;
        }
        
        .main-content {
            margin: 0 !important;
            padding: 20px !important;
        }
        
        .card {
            page-break-inside: avoid;
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
        }
        
        .question-review {
            page-break-inside: avoid;
            margin-bottom: 20px !important;
        }
        
        body {
            font-size: 12pt;
        }
        
        .row {
            page-break-inside: avoid;
        }
        
        h2, h4 {
            color: #000 !important;
        }
    }
</style>

<script>
    $(document).ready(function() {
        // Create answer distribution chart
        <?php if ($result['allow_review']): ?>
        const ctx = document.getElementById('answerChart');
        if (ctx) {
            new Chart(ctx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Correct', 'Wrong'],
                    datasets: [{
                        data: [<?= $correct_count ?>, <?= $wrong_count ?>],
                        backgroundColor: ['#198754', '#dc3545'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
        <?php endif; ?>
    });
</script>

<?php require_once '../includes/footer.php'; ?>