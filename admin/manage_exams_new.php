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
include '../includes/header.php';

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
</style>

<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2">
                <i class="fas fa-id-card me-2"></i><?php echo $page_title; ?>
            </h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAdmitCardModal">
                <i class="fas fa-plus me-2"></i>Create Admit Card
            </button>
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
                                            <?php if ($card['status'] !== 'published'): ?>
                                                <button class="btn btn-sm btn-success" onclick="publishAdmitCard(<?php echo $card['id']; ?>)" title="Publish">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-warning text-white" onclick="unpublishAdmitCard(<?php echo $card['id']; ?>)" title="Unpublish">
                                                    <i class="fas fa-ban"></i>
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
                        <input type="hidden" name="centre_code" id="centreCode">
                    </div>

                    <!-- Step 4: Exam Schedule -->
                    <div class="mb-4">
                        <h6 class="text-primary mb-3"><i class="fas fa-calendar-alt me-2"></i>Examination Schedule</h6>
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
                            <textarea class="form-control" name="reporting_instructions" rows="2"
                                placeholder="e.g., 30 minutes before exam. No entry after 15 minutes of exam start.">30 minutes before exam. No entry after 15 minutes of exam start.</textarea>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let scheduleRowCount = 0;

    // Update centre details when radio button changes
    document.querySelectorAll('input[name="centre"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('centreName').value = this.dataset.name;
            document.getElementById('centreAddress').value = this.dataset.address;
            document.getElementById('centreCode').value = this.dataset.code;
        });
    });

    function addScheduleRow() {
        scheduleRowCount++;
        const row = document.createElement('div');
        row.className = 'subject-row';
        row.id = `scheduleRow${scheduleRowCount}`;
        row.innerHTML = `
        <button type="button" class="btn btn-sm btn-danger remove-row-btn" onclick="removeScheduleRow(${scheduleRowCount})">
            <i class="fas fa-times"></i>
        </button>
        <div class="row">
            <div class="col-md-2 mb-2">
                <label class="form-label">Date *</label>
                <input type="date" class="form-control form-control-sm" name="exam_dates[]" required>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Day *</label>
                <input type="text" class="form-control form-control-sm" name="exam_days[]" placeholder="Monday" required>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Start Time *</label>
                <input type="time" class="form-control form-control-sm" name="start_times[]" value="10:00" required>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">End Time *</label>
                <input type="time" class="form-control form-control-sm" name="end_times[]" value="13:00" required>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Subject Code *</label>
                <input type="text" class="form-control form-control-sm" name="subject_codes[]" placeholder="BCA-301" required>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">Subject Name *</label>
                <input type="text" class="form-control form-control-sm" name="subject_names[]" placeholder="Data Structures" required>
            </div>
            <div class="col-md-1 mb-2">
                <label class="form-label">Marks *</label>
                <input type="number" class="form-control form-control-sm" name="max_marks[]" value="100" min="1" required>
            </div>
        </div>
    `;
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
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Admit card created successfully',
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
        window.open(`../student/admit_card.php?id=${id}`, '_blank');
    }

    function publishAdmitCard(id) {
        Swal.fire({
            title: 'Publish Admit Card?',
            text: 'Students will be able to download their admit cards',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Publish',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#28a745'
        }).then((result) => {
            if (result.isConfirmed) {
                updateAdmitCardStatus(id, 'published');
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

    function updateAdmitCardStatus(id, status) {
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
                    status: status
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: `Admit card ${status === 'published' ? 'published' : 'unpublished'} successfully`,
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
</script>

<?php include '../includes/footer.php'; ?>