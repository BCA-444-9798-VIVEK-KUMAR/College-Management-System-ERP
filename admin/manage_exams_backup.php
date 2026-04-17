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

$page_title = 'Manage Exams';
include '../includes/header.php';

// Get all courses
$courses_query = "SELECT * FROM courses ORDER BY title";
$courses_stmt = $pdo->query($courses_query);
$courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all sessions
$sessions_query = "SELECT * FROM sessions ORDER BY start_year DESC";
$sessions_stmt = $pdo->query($sessions_query);
$sessions = $sessions_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all batches
$batches_query = "SELECT b.*, c.title as course_name, s.title as session_name 
                  FROM batches b 
                  JOIN courses c ON b.course_id = c.id 
                  JOIN sessions s ON b.session_id = s.id 
                  ORDER BY b.name";
$batches_stmt = $pdo->query($batches_query);
$batches = $batches_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get existing admit cards
$admit_cards_query = "SELECT ac.*, c.title as course_name, s.title as session_name, b.name as batch_name 
                      FROM exam_admit_cards ac 
                      LEFT JOIN courses c ON ac.course_id = c.id 
                      LEFT JOIN sessions s ON ac.session_id = s.id 
                      LEFT JOIN batches b ON ac.batch_id = b.id 
                      ORDER BY ac.created_at DESC";
$admit_cards_stmt = $pdo->query($admit_cards_query);
$admit_cards = $admit_cards_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .card {
        margin-bottom: 20px;
    }
    .subject-row {
        margin-bottom: 15px;
        padding: 15px;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        background: #f8f9fa;
    }
    .center-option {
        padding: 10px;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.3s;
    }
    .center-option:hover {
        background: #e9ecef;
    }
    .center-option input[type="radio"] {
        margin-right: 10px;
    }
</style>

<main class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2"><i class="fas fa-id-card me-2"></i><?php echo $page_title; ?></h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAdmitCardModal">
                <i class="fas fa-plus me-2"></i>Create Admit Card
            </button>
        </div>

        <!-- Existing Admit Cards -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Existing Admit Cards</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Exam Title</th>
                                <th>Course</th>
                                <th>Session</th>
                                <th>Batch</th>
                                <th>Exam Dates</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="admitCardsTable">
                            <?php if (empty($admit_cards)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No admit cards created yet</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($admit_cards as $card): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($card['exam_title']); ?></td>
                                        <td><?php echo htmlspecialchars($card['course_name']); ?></td>
                                        <td><?php echo htmlspecialchars($card['session_name']); ?></td>
                                        <td><?php echo htmlspecialchars($card['batch_name'] ?? 'All Batches'); ?></td>
                                        <td><?php echo date('d M Y', strtotime($card['start_date'])); ?> - <?php echo date('d M Y', strtotime($card['end_date'])); ?></td>
                                        <td>
                                            <?php if ($card['status'] === 'published'): ?>
                                                <span class="badge bg-success">Published</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Draft</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="viewAdmitCard(<?php echo $card['id']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-primary" onclick="editAdmitCard(<?php echo $card['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if ($card['status'] !== 'published'): ?>
                                                <button class="btn btn-sm btn-success" onclick="publishAdmitCard(<?php echo $card['id']; ?>)">
                                                    <i class="fas fa-check"></i> Publish
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-warning" onclick="unpublishAdmitCard(<?php echo $card['id']; ?>)">
                                                    <i class="fas fa-ban"></i> Unpublish
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-danger" onclick="deleteAdmitCard(<?php echo $card['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Create Admit Card Modal -->
<div class="modal fade" id="createAdmitCardModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Admit Card</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="admitCardForm">
                    <!-- Basic Information -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Examination Title *</label>
                            <input type="text" class="form-control" name="exam_title" placeholder="e.g., Internal Examination - December 2025" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Examination Type *</label>
                            <select class="form-control" name="exam_type" required>
                                <option value="Internal Examination">Internal Examination</option>
                                <option value="Mid-Semester Examination">Mid-Semester Examination</option>
                                <option value="End-Semester Examination">End-Semester Examination</option>
                                <option value="Practical Examination">Practical Examination</option>
                            </select>
                        </div>
                    </div>

                    <!-- Course, Session, Batch -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Course *</label>
                            <select class="form-control" name="course_id" id="courseSelect" required>
                                <option value="">Select Course</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Session *</label>
                            <select class="form-control" name="session_id" id="sessionSelect" required>
                                <option value="">Select Session</option>
                                <?php foreach ($sessions as $session): ?>
                                    <option value="<?php echo $session['id']; ?>"><?php echo htmlspecialchars($session['title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Batch (Optional)</label>
                            <select class="form-control" name="batch_id" id="batchSelect">
                                <option value="">All Batches</option>
                                <?php foreach ($batches as $batch): ?>
                                    <option value="<?php echo $batch['id']; ?>"><?php echo htmlspecialchars($batch['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Examination Centre -->
                    <div class="mb-3">
                        <label class="form-label">Examination Centre *</label>
                        <div class="center-option">
                            <input type="radio" name="centre_name" value="CIMAGE Professional College" id="centre1" required>
                            <label for="centre1">
                                <strong>CIMAGE Professional College</strong><br>
                                <small>Vivekanand Marg, 2nd House, Boring Road, In-Front of A.N College, Anandpuri, Patna, Bihar 800013</small>
                            </label>
                        </div>
                        <div class="center-option">
                            <input type="radio" name="centre_name" value="Cimage Center of Digital Technology & Entrepreneurship" id="centre2">
                            <label for="centre2">
                                <strong>Cimage Center of Digital Technology & Entrepreneurship</strong><br>
                                <small>C-10, 11, Patliputra Industrial Area, Patna</small>
                            </label>
                        </div>
                        <div class="center-option">
                            <input type="radio" name="centre_name" value="CIMAGE College Old Building" id="centre3">
                            <label for="centre3">
                                <strong>CIMAGE College Old Building</strong><br>
                                <small>Patliputra Industrial Area, Patna</small>
                            </label>
                        </div>
                        <div class="center-option">
                            <input type="radio" name="centre_name" value="CATALYST College" id="centre4">
                            <label for="centre4">
                                <strong>CATALYST College</strong><br>
                                <small>SK PURI PARK, PATNA, PATNA, PATNA, Bihar, 800013</small>
                            </label>
                        </div>
                    </div>

                    <!-- Hidden field for centre address -->
                    <input type="hidden" name="centre_address" id="centreAddress">

                    <!-- Exam Schedule Section -->
                    <div class="mb-3">
                        <label class="form-label">Examination Schedule</label>
                        <div id="scheduleContainer">
                            <!-- Schedule rows will be added here -->
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="addScheduleRow()">
                            <i class="fas fa-plus me-2"></i>Add Subject
                        </button>
                    </div>

                    <!-- Important Instructions -->
                    <div class="mb-3">
                        <label class="form-label">Reporting Time Instructions</label>
                        <input type="text" class="form-control" name="reporting_instructions" value="30 minutes before exam. No entry after 15 minutes of exam start." required>
                    </div>

                    <!-- Publish Option -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_published" id="publishCheckbox" value="1">
                            <label class="form-check-label" for="publishCheckbox">
                                Publish immediately (Students will be able to download admit card)
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveAdmitCard()">Create Admit Card</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let scheduleRowCount = 0;

// Update centre address when radio button changes
document.querySelectorAll('input[name="centre_name"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const label = document.querySelector(`label[for="${this.id}"]`);
        const address = label.querySelector('small').textContent;
        document.getElementById('centreAddress').value = address;
    });
});

function addScheduleRow() {
    scheduleRowCount++;
    const row = document.createElement('div');
    row.className = 'subject-row';
    row.id = `scheduleRow${scheduleRowCount}`;
    row.innerHTML = `
        <div class="row">
            <div class="col-md-2">
                <label class="form-label">Date</label>
                <input type="date" class="form-control" name="exam_dates[]" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Day</label>
                <input type="text" class="form-control" name="exam_days[]" placeholder="Monday" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Start Time</label>
                <input type="time" class="form-control" name="start_times[]" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">End Time</label>
                <input type="time" class="form-control" name="end_times[]" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Subject Code</label>
                <input type="text" class="form-control" name="subject_codes[]" placeholder="BCA-301" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Subject Name</label>
                <input type="text" class="form-control" name="subject_names[]" placeholder="Data Structures" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Max Marks</label>
                <input type="number" class="form-control" name="max_marks[]" value="100" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeScheduleRow(${scheduleRowCount})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    document.getElementById('scheduleContainer').appendChild(row);
}

function removeScheduleRow(rowId) {
    document.getElementById(`scheduleRow${rowId}`).remove();
}

function saveAdmitCard() {
    const form = document.getElementById('admitCardForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);

    fetch('../api/admin/create_admit_card.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Success!', 'Admit card created successfully', 'success')
                .then(() => location.reload());
        } else {
            Swal.fire('Error!', data.message || 'Failed to create admit card', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error!', 'An error occurred', 'error');
    });
}

function publishAdmitCard(id) {
    Swal.fire({
        title: 'Publish Admit Card?',
        text: 'Students will be able to download their admit cards',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Publish',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`../api/admin/update_admit_card_status.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, is_published: 1 })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Published!', 'Admit card is now available to students', 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            });
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
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`../api/admin/update_admit_card_status.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, is_published: 0 })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Unpublished!', 'Admit card is no longer available', 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            });
        }
    });
}

function deleteAdmitCard(id) {
    Swal.fire({
        title: 'Delete Admit Card?',
        text: 'This action cannot be undone',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`../api/admin/delete_admit_card.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Deleted!', 'Admit card has been deleted', 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            });
        }
    });
}

// Initialize with one schedule row
document.addEventListener('DOMContentLoaded', function() {
    addScheduleRow();
});
</script>

<?php include '../includes/footer.php'; ?>
