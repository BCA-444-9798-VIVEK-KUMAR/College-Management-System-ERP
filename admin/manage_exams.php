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
require_once '../config/database.php';
require_once '../includes/security.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$page_title = 'Manage Admit Cards';
$pageTitle = 'Manage Admit Cards';
$basePath = '..';
include '../includes/header.php';

// Initialize database connection
$database = new Database();
$pdo = $database->getConnection();

// Check if exam_admit_cards table exists, if not show setup message
try {
    $check_table = $pdo->query("SHOW TABLES LIKE 'exam_admit_cards'");
    $table_exists = $check_table->rowCount() > 0;

    if (!$table_exists) {
        echo '<div class="alert alert-warning m-4">
                <h4>Database Setup Required</h4>
                <p>The admit cards table needs to be created. Please run the SQL file: <code>database/exam_admit_cards.sql</code></p>
                <p>Or execute this in phpMyAdmin/MySQL:</p>
                <pre>source ' . __DIR__ . '/../database/exam_admit_cards.sql</pre>
              </div>';
        include '../includes/footer.php';
        exit();
    }
} catch (Exception $e) {
    $table_exists = false;
}

// Get all courses
try {
    $courses_query = "SELECT * FROM courses ORDER BY title";
    $courses_stmt = $pdo->query($courses_query);
    $courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $courses = [];
}

// Get all sessions
try {
    $sessions_query = "SELECT * FROM sessions ORDER BY start_year DESC";
    $sessions_stmt = $pdo->query($sessions_query);
    $sessions = $sessions_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $sessions = [];
}

// Get all batches
try {
    $batches_query = "SELECT b.*, c.title as course_name, s.title as session_name 
                      FROM batches b 
                      JOIN courses c ON b.course_id = c.id 
                      JOIN sessions s ON b.session_id = s.id 
                      ORDER BY b.name";
    $batches_stmt = $pdo->query($batches_query);
    $batches = $batches_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $batches = [];
}

// Get existing admit cards
try {
    $admit_cards_query = "SELECT ac.*, c.title as course_name, s.title as session_name, b.name as batch_name 
                          FROM exam_admit_cards ac 
                          LEFT JOIN courses c ON ac.course_id = c.id 
                          LEFT JOIN sessions s ON ac.session_id = s.id 
                          LEFT JOIN batches b ON ac.batch_id = b.id 
                          ORDER BY ac.created_at DESC";
    $admit_cards_stmt = $pdo->query($admit_cards_query);
    $admit_cards = $admit_cards_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $admit_cards = [];
    error_log("Error fetching admit cards: " . $e->getMessage());
}

// Examination centers array
$exam_centres = [
    [
        'name' => 'CIMAGE Professional College',
        'address' => 'Vivekanand Marg, 2nd House, Boring Road, In-Front of A.N College, Anandpuri, Patna, Bihar 800013',
        'code' => 'CIMG-PC-01'
    ],
    [
        'name' => 'Cimage Center of Digital Technology & Entrepreneurship',
        'address' => 'C-10, 11, Patliputra Industrial Area, Patna',
        'code' => 'CIMG-CDTE-02'
    ],
    [
        'name' => 'CIMAGE College Old Building',
        'address' => 'Patliputra Industrial Area, Patna',
        'code' => 'CIMG-OB-03'
    ],
    [
        'name' => 'CATALYST College',
        'address' => 'SK PURI PARK, PATNA, Bihar, 800013',
        'code' => 'CATAL-04'
    ]
];
?>

<style>
    .card {
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
    }

    .subject-row {
        margin-bottom: 15px;
        padding: 15px;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        background: #f8f9fa;
        position: relative;
    }

    .remove-row-btn {
        position: absolute;
        top: 10px;
        right: 10px;
    }

    .center-option {
        padding: 15px;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.3s;
        background: white;
    }

    .center-option:hover {
        border-color: #667eea;
        background: #f8f9ff;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(102, 126, 234, 0.2);
    }

    .center-option input[type="radio"]:checked+label {
        color: #667eea;
        font-weight: 600;
    }

    .center-option input[type="radio"] {
        margin-right: 10px;
        width: 20px;
        height: 20px;
    }

    .badge-published {
        background: #28a745;
    }

    .badge-draft {
        background: #ffc107;
    }

    .table-actions {
        white-space: nowrap;
    }

    .modal-xl {
        max-width: 1200px;
    }

    .form-check-input:checked {
        background-color: #28a745;
        border-color: #28a745;
    }

    #toggleLabel {
        font-weight: 600;
        min-width: 35px;
        display: inline-block;
    }
</style>

<main class="main-content">
    <div class="container-fluid">
        <!-- Test Mode Warning Banner (shown via JS) -->
        <div id="testModeWarning" class="alert alert-warning alert-dismissible fade show mb-4" style="display: none;">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div class="flex-grow-1">
                    <h5 class="alert-heading mb-1">
                        <i class="fas fa-flask"></i> Test Mode Active
                    </h5>
                    <p class="mb-0">
                        All admit card emails will be sent to the test email address: <strong id="testEmailAddress"></strong>
                        <br><small>To send to real students, disable test mode in <a href="email_settings.php" class="alert-link">Email Settings</a></small>
                    </p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <h1 class="h2 mb-0">
                <i class="fas fa-id-card me-2"></i><?php echo $page_title; ?>
            </h1>
            <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-md-auto">
                <div class="d-flex align-items-center justify-content-between bg-light p-2 rounded" style="min-width: 200px;">
                    <label class="mb-0 small">
                        <i class="fas fa-envelope me-1"></i>Auto-Send:
                    </label>
                    <div class="form-check form-switch mb-0 ms-2">
                        <input class="form-check-input" type="checkbox" id="autoEmailToggle" checked>
                        <label class="form-check-label fw-bold small" for="autoEmailToggle" id="toggleLabel">ON</label>
                    </div>
                </div>
                <button class="btn btn-primary w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#createAdmitCardModal">
                    <i class="fas fa-plus me-2"></i>Create Admit Card
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Total Admit Cards</h6>
                                <h3 class="mb-0"><?php echo count($admit_cards); ?></h3>
                            </div>
                            <div class="text-primary">
                                <i class="fas fa-id-card fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Published</h6>
                                <h3 class="mb-0"><?php echo count(array_filter($admit_cards, fn($c) => $c['status'] === 'published')); ?></h3>
                            </div>
                            <div class="text-success">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Draft</h6>
                                <h3 class="mb-0"><?php echo count(array_filter($admit_cards, fn($c) => $c['status'] === 'draft')); ?></h3>
                            </div>
                            <div class="text-warning">
                                <i class="fas fa-edit fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Exam Centers</h6>
                                <h3 class="mb-0"><?php echo count($exam_centres); ?></h3>
                            </div>
                            <div class="text-info">
                                <i class="fas fa-building fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Existing Admit Cards -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Admit Cards</h5>
            </div>
            <div class="card-body">
                <?php if (empty($admit_cards)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-id-card fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No Admit Cards Created Yet</h4>
                        <p class="text-muted">Click "Create Admit Card" button to create your first admit card</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Exam Title</th>
                                    <th>Course</th>
                                    <th>Session</th>
                                    <th>Batch</th>
                                    <th>Exam Period</th>
                                    <th>Centre</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($admit_cards as $card): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($card['exam_title']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($card['exam_type']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($card['course_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($card['session_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($card['batch_name'] ?? 'All Batches'); ?></td>
                                        <td>
                                            <small>
                                                <?php echo date('d M Y', strtotime($card['start_date'])); ?><br>
                                                to<br>
                                                <?php echo date('d M Y', strtotime($card['end_date'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <small><?php echo htmlspecialchars($card['centre_name']); ?></small>
                                        </td>
                                        <td>
                                            <?php if ($card['status'] === 'published'): ?>
                                                <span class="badge badge-published">
                                                    <i class="fas fa-check me-1"></i>Published
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-draft">
                                                    <i class="fas fa-edit me-1"></i>Draft
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="table-actions">
                                            <button class="btn btn-sm btn-info" onclick="viewAdmitCard(<?php echo $card['id']; ?>)" title="Preview">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning" onclick="editAdmitCard(<?php echo $card['id']; ?>)" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if ($card['status'] !== 'published'): ?>
                                                <button class="btn btn-sm btn-success" onclick="publishAdmitCard(<?php echo $card['id']; ?>)" title="Publish">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-warning text-white" onclick="unpublishAdmitCard(<?php echo $card['id']; ?>)" title="Unpublish">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                                <button class="btn btn-sm btn-primary" onclick="sendEmailsManually(<?php echo $card['id']; ?>)" title="Send Emails">
                                                    <i class="fas fa-envelope"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-danger" onclick="deleteAdmitCard(<?php echo $card['id']; ?>)" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<!-- Create Admit Card Modal -->
<div class="modal fade" id="createAdmitCardModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Create New Admit Card</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="admitCardForm">
                    <!-- Step 1: Basic Information -->
                    <div class="mb-4">
                        <h6 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Basic Information</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Examination Title *</label>
                                <input type="text" class="form-control" name="exam_title"
                                    placeholder="e.g., Internal Examination - December 2025" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Examination Type *</label>
                                <select class="form-control" name="exam_type" required>
                                    <option value="">Select Type</option>
                                    <option value="Internal Examination">Internal Examination</option>
                                    <option value="Mid-Semester Examination">Mid-Semester Examination</option>
                                    <option value="End-Semester Examination">End-Semester Examination</option>
                                    <option value="Practical Examination">Practical Examination</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Course Details -->
                    <div class="mb-4">
                        <h6 class="text-primary mb-3"><i class="fas fa-graduation-cap me-2"></i>Course Details</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Course *</label>
                                <select class="form-control" name="course_id" id="courseSelect" required>
                                    <option value="">Select Course</option>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?php echo $course['id']; ?>">
                                            <?php echo htmlspecialchars($course['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Session *</label>
                                <select class="form-control" name="session_id" id="sessionSelect" required>
                                    <option value="">Select Session</option>
                                    <?php foreach ($sessions as $session): ?>
                                        <option value="<?php echo $session['id']; ?>">
                                            <?php echo htmlspecialchars($session['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Batch (Optional)</label>
                                <select class="form-control" name="batch_id" id="batchSelect">
                                    <option value="">All Batches</option>
                                    <?php foreach ($batches as $batch): ?>
                                        <option value="<?php echo $batch['id']; ?>">
                                            <?php echo htmlspecialchars($batch['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Examination Centre -->
                    <div class="mb-4">
                        <h6 class="text-primary mb-3"><i class="fas fa-building me-2"></i>Examination Centre *</h6>
                        <?php foreach ($exam_centres as $index => $centre): ?>
                            <div class="center-option">
                                <input type="radio" name="centre" value="<?php echo $index; ?>"
                                    id="centre<?php echo $index; ?>"
                                    data-name="<?php echo htmlspecialchars($centre['name']); ?>"
                                    data-address="<?php echo htmlspecialchars($centre['address']); ?>"
                                    data-code="<?php echo htmlspecialchars($centre['code']); ?>"
                                    required>
                                <label for="centre<?php echo $index; ?>" style="cursor: pointer; flex: 1;">
                                    <strong><?php echo htmlspecialchars($centre['name']); ?></strong>
                                    <span class="badge bg-secondary float-end"><?php echo $centre['code']; ?></span><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($centre['address']); ?></small>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        <input type="hidden" name="centre_name" id="centreName">
                        <input type="hidden" name="centre_address" id="centreAddress">
                    </div>

                    <!-- Step 4: Exam Schedule -->
                    <div class="mb-4">
                        <h6 class="text-primary mb-3"><i class="fas fa-calendar-alt me-2"></i>Examination Schedule</h6>

                        <!-- Column Selection -->
                        <div class="mb-3 p-3" style="background: #f8f9fa; border-radius: 5px;">
                            <strong>Select fields to display on admit card:</strong>
                            <div class="row mt-2">
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="showSubjectCode">
                                        <label class="form-check-label" for="showSubjectCode">
                                            Subject Code
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="showMaxMarks">
                                        <label class="form-check-label" for="showMaxMarks">
                                            Max Marks
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="showDay">
                                        <label class="form-check-label" for="showDay">
                                            Day Name
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="scheduleContainer"></div>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addScheduleRow()">
                            <i class="fas fa-plus me-2"></i>Add Subject
                        </button>
                    </div>

                    <!-- Step 5: Additional Information -->
                    <div class="mb-4">
                        <h6 class="text-primary mb-3"><i class="fas fa-info me-2"></i>Additional Information</h6>
                        <div class="mb-3">
                            <label class="form-label">Reporting Instructions</label>
                            <textarea class="form-control" name="reporting_instructions" rows="3"
                                placeholder="e.g., 30 minutes before exam. No entry after 15 minutes of exam start."> • 30 minutes before exam. No entry after 15 minutes of exam start.
• Paste a recent self-attested passport-size photograph in the designated space on the admit card.
• Maintain strict silence and decorum inside the examination hall. Writing on the question paper is strictly prohibited.</textarea>
                        </div>
                    </div>

                    <!-- Publish Option -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="publish_now" id="publishCheckbox" value="1">
                            <label class="form-check-label" for="publishCheckbox">
                                <strong>Publish immediately</strong> (Students will be able to download admit card)
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="saveAdmitCard()">
                    <i class="fas fa-save me-2"></i>Create Admit Card
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Admit Card Modal -->
<div class="modal fade" id="editAdmitCardModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Admit Card</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editAdmitCardForm">
                    <input type="hidden" name="admit_card_id" id="editAdmitCardId">
                    
                    <!-- Step 1: Basic Information -->
                    <div class="mb-4">
                        <h6 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Basic Information</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Examination Title *</label>
                                <input type="text" class="form-control" name="exam_title" id="editExamTitle"
                                    placeholder="e.g., Internal Examination - December 2025" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Examination Type *</label>
                                <select class="form-control" name="exam_type" id="editExamType" required>
                                    <option value="">Select Type</option>
                                    <option value="Internal Examination">Internal Examination</option>
                                    <option value="Mid-Semester Examination">Mid-Semester Examination</option>
                                    <option value="End-Semester Examination">End-Semester Examination</option>
                                    <option value="Practical Examination">Practical Examination</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Course Details -->
                    <div class="mb-4">
                        <h6 class="text-primary mb-3"><i class="fas fa-graduation-cap me-2"></i>Course Details</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Course *</label>
                                <select class="form-control" name="course_id" id="editCourseSelect" required>
                                    <option value="">Select Course</option>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?php echo $course['id']; ?>">
                                            <?php echo htmlspecialchars($course['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Session *</label>
                                <select class="form-control" name="session_id" id="editSessionSelect" required>
                                    <option value="">Select Session</option>
                                    <?php foreach ($sessions as $session): ?>
                                        <option value="<?php echo $session['id']; ?>">
                                            <?php echo htmlspecialchars($session['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Batch (Optional)</label>
                                <select class="form-control" name="batch_id" id="editBatchSelect">
                                    <option value="">All Batches</option>
                                    <?php foreach ($batches as $batch): ?>
                                        <option value="<?php echo $batch['id']; ?>">
                                            <?php echo htmlspecialchars($batch['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Examination Centre -->
                    <div class="mb-4">
                        <h6 class="text-primary mb-3"><i class="fas fa-building me-2"></i>Examination Centre *</h6>
                        <?php foreach ($exam_centres as $index => $centre): ?>
                            <div class="center-option">
                                <input type="radio" name="centre" value="<?php echo $index; ?>"
                                    id="editCentre<?php echo $index; ?>"
                                    data-name="<?php echo htmlspecialchars($centre['name']); ?>"
                                    data-address="<?php echo htmlspecialchars($centre['address']); ?>"
                                    data-code="<?php echo htmlspecialchars($centre['code']); ?>"
                                    required>
                                <label for="editCentre<?php echo $index; ?>" style="cursor: pointer; flex: 1;">
                                    <strong><?php echo htmlspecialchars($centre['name']); ?></strong>
                                    <span class="badge bg-secondary float-end"><?php echo $centre['code']; ?></span><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($centre['address']); ?></small>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        <input type="hidden" name="centre_name" id="editCentreName">
                        <input type="hidden" name="centre_address" id="editCentreAddress">
                    </div>

                    <!-- Step 4: Exam Schedule -->
                    <div class="mb-4">
                        <h6 class="text-primary mb-3"><i class="fas fa-calendar-alt me-2"></i>Examination Schedule</h6>

                        <!-- Column Selection -->
                        <div class="mb-3 p-3" style="background: #f8f9fa; border-radius: 5px;">
                            <strong>Select fields to display on admit card:</strong>
                            <div class="row mt-2">
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="editShowSubjectCode">
                                        <label class="form-check-label" for="editShowSubjectCode">
                                            Subject Code
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="editShowMaxMarks">
                                        <label class="form-check-label" for="editShowMaxMarks">
                                            Max Marks
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="editShowDay">
                                        <label class="form-check-label" for="editShowDay">
                                            Day Name
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="editScheduleContainer"></div>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addEditScheduleRow()">
                            <i class="fas fa-plus me-2"></i>Add Subject
                        </button>
                    </div>

                    <!-- Step 5: Additional Information -->
                    <div class="mb-4">
                        <h6 class="text-primary mb-3"><i class="fas fa-info me-2"></i>Additional Information</h6>
                        <div class="mb-3">
                            <label class="form-label">Reporting Instructions</label>
                            <textarea class="form-control" name="reporting_instructions" id="editReportingInstructions" rows="3"
                                placeholder="e.g., 30 minutes before exam. No entry after 15 minutes of exam start."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-warning" onclick="updateAdmitCard()">
                    <i class="fas fa-save me-2"></i>Update Admit Card
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let scheduleRowCount = 0;
    let editScheduleRowCount = 0;
    let autoSendEmails = true; // Default: auto-send ON
    const examCentres = <?php echo json_encode($exam_centres); ?>;

    // Check for test mode on page load
    $(document).ready(function() {
        checkTestMode();
    });

    // Function to check if test mode is enabled
    function checkTestMode() {
        $.ajax({
            url: '../api/admin/get_smtp_settings.php',
            type: 'GET',
            success: function(response) {
                if (response.success && response.data) {
                    const settings = response.data;
                    if (settings.use_test_email == 1 && settings.test_email) {
                        // Show test mode warning
                        $('#testEmailAddress').text(settings.test_email);
                        $('#testModeWarning').slideDown();
                    }
                }
            },
            error: function() {
                console.error('Failed to check test mode status');
            }
        });
    }

    // Toggle auto-send emails
    document.getElementById('autoEmailToggle').addEventListener('change', function() {
        autoSendEmails = this.checked;
        document.getElementById('toggleLabel').textContent = this.checked ? 'ON' : 'OFF';

        // Save preference to localStorage
        localStorage.setItem('autoSendEmails', this.checked);

        // Show notification
        const status = this.checked ? 'enabled' : 'disabled';
        Swal.fire({
            icon: 'info',
            title: `Auto-Send Emails ${status.toUpperCase()}`,
            text: this.checked ?
                'Emails will be sent automatically when publishing admit cards' :
                'You will need to manually send emails using the envelope button',
            timer: 3000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    });

    // Load saved preference on page load
    document.addEventListener('DOMContentLoaded', function() {
        const savedPref = localStorage.getItem('autoSendEmails');
        if (savedPref !== null) {
            autoSendEmails = savedPref === 'true';
            document.getElementById('autoEmailToggle').checked = autoSendEmails;
            document.getElementById('toggleLabel').textContent = autoSendEmails ? 'ON' : 'OFF';
        }
    });

    // Update centre details when radio button changes
    document.querySelectorAll('input[name="centre"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.id.startsWith('editCentre')) {
                document.getElementById('editCentreName').value = this.dataset.name;
                document.getElementById('editCentreAddress').value = this.dataset.address;
            } else {
                document.getElementById('centreName').value = this.dataset.name;
                document.getElementById('centreAddress').value = this.dataset.address;
            }
        });
    });

    function addScheduleRow() {
        scheduleRowCount++;

        // Check which columns are visible
        const showSubjectCode = document.getElementById('showSubjectCode').checked;
        const showMaxMarks = document.getElementById('showMaxMarks').checked;
        const showDay = document.getElementById('showDay').checked;

        const row = document.createElement('div');
        row.className = 'subject-row';
        row.id = `scheduleRow${scheduleRowCount}`;

        let htmlContent = `
        <button type="button" class="btn btn-sm btn-danger remove-row-btn" onclick="removeScheduleRow(${scheduleRowCount})">
            <i class="fas fa-times"></i>
        </button>
        <div class="row">
            <div class="col-md-2 mb-2">
                <label class="form-label">Date *</label>
                <input type="date" class="form-control form-control-sm" name="exam_dates[]" required>
            </div>`;

        if (showDay) {
            htmlContent += `
            <div class="col-md-2 mb-2">
                <label class="form-label">Day *</label>
                <input type="text" class="form-control form-control-sm" name="exam_days[]" placeholder="Monday" required>
            </div>`;
        } else {
            htmlContent += `<input type="hidden" name="exam_days[]" value="">`;
        }

        htmlContent += `
            <div class="col-md-2 mb-2">
                <label class="form-label">Start Time *</label>
                <input type="time" class="form-control form-control-sm" name="start_times[]" value="10:00" required>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">End Time *</label>
                <input type="time" class="form-control form-control-sm" name="end_times[]" value="13:00" required>
            </div>`;

        if (showSubjectCode) {
            htmlContent += `
            <div class="col-md-2 mb-2">
                <label class="form-label">Subject Code *</label>
                <input type="text" class="form-control form-control-sm" name="subject_codes[]" placeholder="BCA-301" required>
            </div>`;
        } else {
            htmlContent += `<input type="hidden" name="subject_codes[]" value="">`;
        }

        const subjectNameWidth = showSubjectCode && showMaxMarks ? '3' : (showSubjectCode || showMaxMarks ? '4' : '6');

        htmlContent += `
            <div class="col-md-${subjectNameWidth} mb-2">
                <label class="form-label">Subject Name *</label>
                <input type="text" class="form-control form-control-sm" name="subject_names[]" placeholder="Data Structures" required>
            </div>`;

        if (showMaxMarks) {
            htmlContent += `
            <div class="col-md-1 mb-2">
                <label class="form-label">Marks *</label>
                <input type="number" class="form-control form-control-sm" name="max_marks[]" value="100" min="1" required>
            </div>`;
        } else {
            htmlContent += `<input type="hidden" name="max_marks[]" value="">`;
        }

        htmlContent += `
        </div>
    `;

        row.innerHTML = htmlContent;
        document.getElementById('scheduleContainer').appendChild(row);
    }

    function removeScheduleRow(rowId) {
        const row = document.getElementById(`scheduleRow${rowId}`);
        if (row) {
            row.remove();
        }
    }

    function saveAdmitCard() {
        const form = document.getElementById('admitCardForm');

        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // Check if at least one subject is added
        const dates = form.querySelectorAll('input[name="exam_dates[]"]');
        if (dates.length === 0) {
            Swal.fire('Error!', 'Please add at least one subject to the exam schedule', 'error');
            return;
        }

        const formData = new FormData(form);

        // Add column visibility settings
        formData.append('show_subject_code', document.getElementById('showSubjectCode').checked ? '1' : '0');
        formData.append('show_max_marks', document.getElementById('showMaxMarks').checked ? '1' : '0');
        formData.append('show_day', document.getElementById('showDay').checked ? '1' : '0');

        // Show loading
        Swal.fire({
            title: 'Creating Admit Card...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('../api/admin/create_admit_card.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let message = data.message;
                    if (data.email_results && data.email_results.sent > 0) {
                        message += `\n\nEmails Sent: ${data.email_results.sent}`;
                        if (data.email_results.failed > 0) {
                            message += `\nFailed: ${data.email_results.failed}`;
                        }
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: message,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Failed to create admit card',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while creating the admit card',
                    confirmButtonText: 'OK'
                });
            });
    }

    function viewAdmitCard(id) {
        window.open(`preview_admit_card.php?id=${id}`, '_blank');
    }

    function editAdmitCard(id) {
        // Show loading
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Fetch admit card details
        fetch(`../api/admin/get_admit_card.php?id=${id}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server returned non-JSON response');
                }
                return response.json();
            })
            .then(data => {
                Swal.close();
                
                if (data.success) {
                    const card = data.data;
                    
                    // Populate basic information
                    document.getElementById('editAdmitCardId').value = card.id;
                    document.getElementById('editExamTitle').value = card.exam_title;
                    document.getElementById('editExamType').value = card.exam_type;
                    
                    // Populate course details
                    document.getElementById('editCourseSelect').value = card.course_id;
                    document.getElementById('editSessionSelect').value = card.session_id;
                    document.getElementById('editBatchSelect').value = card.batch_id || '';
                    
                    // Populate centre - find matching centre
                    const centreName = card.centre_name;
                    examCentres.forEach((centre, index) => {
                        if (centre.name === centreName) {
                            document.getElementById(`editCentre${index}`).checked = true;
                            document.getElementById('editCentreName').value = centre.name;
                            document.getElementById('editCentreAddress').value = centre.address;
                        }
                    });
                    
                    // Populate reporting instructions
                    document.getElementById('editReportingInstructions').value = card.reporting_instructions || '';
                    
                    // Parse and populate schedule
                    const schedule = JSON.parse(card.exam_schedule || '[]');
                    
                    // Set column visibility
                    const showSubjectCode = schedule.some(s => s.subject_code);
                    const showMaxMarks = schedule.some(s => s.max_marks);
                    const showDay = schedule.some(s => s.day);
                    
                    document.getElementById('editShowSubjectCode').checked = showSubjectCode;
                    document.getElementById('editShowMaxMarks').checked = showMaxMarks;
                    document.getElementById('editShowDay').checked = showDay;
                    
                    // Clear existing schedule rows
                    document.getElementById('editScheduleContainer').innerHTML = '';
                    editScheduleRowCount = 0;
                    
                    // Add schedule rows
                    schedule.forEach(item => {
                        addEditScheduleRow(item);
                    });
                    
                    // Show modal
                    new bootstrap.Modal(document.getElementById('editAdmitCardModal')).show();
                } else {
                    Swal.fire('Error!', data.message || 'Failed to load admit card details', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error Loading Data',
                    html: `<p>Failed to load admit card details.</p><p><small>${error.message}</small></p>`,
                    confirmButtonText: 'OK'
                });
            });
    }

    function addEditScheduleRow(data = null) {
        editScheduleRowCount++;

        const showSubjectCode = document.getElementById('editShowSubjectCode').checked;
        const showMaxMarks = document.getElementById('editShowMaxMarks').checked;
        const showDay = document.getElementById('editShowDay').checked;

        const row = document.createElement('div');
        row.className = 'subject-row';
        row.id = `editScheduleRow${editScheduleRowCount}`;

        let htmlContent = `
        <button type="button" class="btn btn-sm btn-danger remove-row-btn" onclick="removeEditScheduleRow(${editScheduleRowCount})">
            <i class="fas fa-times"></i>
        </button>
        <div class="row">
            <div class="col-md-2 mb-2">
                <label class="form-label">Date *</label>
                <input type="date" class="form-control form-control-sm" name="exam_dates[]" value="${data?.date || ''}" required>
            </div>`;

        if (showDay) {
            htmlContent += `
            <div class="col-md-2 mb-2">
                <label class="form-label">Day *</label>
                <input type="text" class="form-control form-control-sm" name="exam_days[]" value="${data?.day || ''}" placeholder="Monday" required>
            </div>`;
        } else {
            htmlContent += `<input type="hidden" name="exam_days[]" value="">`;
        }

        htmlContent += `
            <div class="col-md-2 mb-2">
                <label class="form-label">Start Time *</label>
                <input type="time" class="form-control form-control-sm" name="start_times[]" value="${data?.start_time || '10:00'}" required>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">End Time *</label>
                <input type="time" class="form-control form-control-sm" name="end_times[]" value="${data?.end_time || '13:00'}" required>
            </div>`;

        if (showSubjectCode) {
            htmlContent += `
            <div class="col-md-2 mb-2">
                <label class="form-label">Subject Code *</label>
                <input type="text" class="form-control form-control-sm" name="subject_codes[]" value="${data?.subject_code || ''}" placeholder="BCA-301" required>
            </div>`;
        } else {
            htmlContent += `<input type="hidden" name="subject_codes[]" value="">`;
        }

        const subjectNameWidth = showSubjectCode && showMaxMarks ? '3' : (showSubjectCode || showMaxMarks ? '4' : '6');

        htmlContent += `
            <div class="col-md-${subjectNameWidth} mb-2">
                <label class="form-label">Subject Name *</label>
                <input type="text" class="form-control form-control-sm" name="subject_names[]" value="${data?.subject_name || ''}" placeholder="Data Structures" required>
            </div>`;

        if (showMaxMarks) {
            htmlContent += `
            <div class="col-md-1 mb-2">
                <label class="form-label">Marks *</label>
                <input type="number" class="form-control form-control-sm" name="max_marks[]" value="${data?.max_marks || '100'}" min="1" required>
            </div>`;
        } else {
            htmlContent += `<input type="hidden" name="max_marks[]" value="">`;
        }

        htmlContent += `
        </div>
    `;

        row.innerHTML = htmlContent;
        document.getElementById('editScheduleContainer').appendChild(row);
    }

    function removeEditScheduleRow(rowId) {
        const row = document.getElementById(`editScheduleRow${rowId}`);
        if (row) {
            row.remove();
        }
    }

    function updateAdmitCard() {
        const form = document.getElementById('editAdmitCardForm');

        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // Check if at least one subject is added
        const dates = form.querySelectorAll('input[name="exam_dates[]"]');
        if (dates.length === 0) {
            Swal.fire('Error!', 'Please add at least one subject to the exam schedule', 'error');
            return;
        }

        const formData = new FormData(form);

        // Add column visibility settings
        formData.append('show_subject_code', document.getElementById('editShowSubjectCode').checked ? '1' : '0');
        formData.append('show_max_marks', document.getElementById('editShowMaxMarks').checked ? '1' : '0');
        formData.append('show_day', document.getElementById('editShowDay').checked ? '1' : '0');

        // Show loading
        Swal.fire({
            title: 'Updating Admit Card...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('../api/admin/update_admit_card.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server returned non-JSON response');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message || 'Failed to update admit card',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Update Failed',
                    html: `<p>An error occurred while updating the admit card.</p><p><small>${error.message}</small></p>`,
                    confirmButtonText: 'OK'
                });
            });
    }

    function publishAdmitCard(id) {
        const willSendEmails = autoSendEmails;

        Swal.fire({
            title: 'Publish Admit Card?',
            html: `Students will be able to download their admit cards.<br><br>` +
                `<strong>${willSendEmails ? '📧 Emails will be sent automatically' : '⚠️ No emails will be sent (Auto-send is OFF)'}</strong>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Publish',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#28a745'
        }).then((result) => {
            if (result.isConfirmed) {
                updateAdmitCardStatus(id, 'published', willSendEmails);
            }
        });
    }

    function unpublishAdmitCard(id) {
        Swal.fire({
            title: 'Unpublish Admit Card?',
            text: 'Students will no longer be able to download this admit card',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Unpublish',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#ffc107'
        }).then((result) => {
            if (result.isConfirmed) {
                updateAdmitCardStatus(id, 'draft');
            }
        });
    }

    function updateAdmitCardStatus(id, status, sendEmails = false) {
        Swal.fire({
            title: 'Updating...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch(`../api/admin/update_admit_card_status.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: id,
                    status: status,
                    send_emails: sendEmails
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let message = data.message;
                    let html = `<p>${message}</p>`;

                    if (data.email_results && data.email_results.sent > 0) {
                        html += `<hr><p><strong>📧 Email Report:</strong></p>`;
                        html += `<p>✅ Sent: ${data.email_results.sent}</p>`;
                        if (data.email_results.failed > 0) {
                            html += `<p>❌ Failed: ${data.email_results.failed}</p>`;
                        }
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        html: html,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'An error occurred', 'error');
            });
    }

    function sendEmailsManually(id) {
        Swal.fire({
            title: 'Send Email Notifications?',
            text: 'This will send admit card emails to all eligible students',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Send Emails',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#007bff'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Sending Emails...',
                    text: 'Please wait while emails are being sent',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch(`../api/admin/send_admit_card_emails.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            admit_card_id: id
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            let html = `<p>${data.message}</p>`;

                            if (data.email_results) {
                                html += `<hr><p><strong>📧 Email Report:</strong></p>`;
                                html += `<p>✅ Sent: ${data.email_results.sent}</p>`;
                                if (data.email_results.failed > 0) {
                                    html += `<p>❌ Failed: ${data.email_results.failed}</p>`;
                                }
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Emails Sent!',
                                html: html,
                                confirmButtonText: 'OK'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: data.message,
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to send emails',
                            confirmButtonText: 'OK'
                        });
                    });
            }
        });
    }

    function deleteAdmitCard(id) {
        Swal.fire({
            title: 'Delete Admit Card?',
            text: 'This action cannot be undone. All student admit cards will be deleted.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Deleting...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch(`../api/admin/delete_admit_card.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id: id
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Admit card has been deleted',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error!', 'An error occurred', 'error');
                    });
            }
        });
    }

    // Initialize with one schedule row when modal opens
    document.getElementById('createAdmitCardModal').addEventListener('shown.bs.modal', function() {
        if (scheduleRowCount === 0) {
            addScheduleRow();
        }
    });

    // Reset form when modal closes
    document.getElementById('createAdmitCardModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('admitCardForm').reset();
        document.getElementById('scheduleContainer').innerHTML = '';
        scheduleRowCount = 0;
    });

    // Reset edit form when modal closes
    document.getElementById('editAdmitCardModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('editAdmitCardForm').reset();
        document.getElementById('editScheduleContainer').innerHTML = '';
        editScheduleRowCount = 0;
    });
</script>

<?php include '../includes/footer.php'; ?>