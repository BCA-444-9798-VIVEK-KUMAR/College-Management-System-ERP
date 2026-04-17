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

$pageTitle = "Manage Exams";
$basePath = "..";
include_once "../includes/header.php";
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<main class="main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-file-alt me-2"></i> Manage Exams</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="create_exam.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Create New Exam
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <i class="fas fa-file-alt fa-2x text-primary mb-2"></i>
                    <h3 class="mb-0" id="totalExams">0</h3>
                    <p class="text-muted mb-0">Total Exams</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <h3 class="mb-0" id="activeExams">0</h3>
                    <p class="text-muted mb-0">Active Exams</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                    <h3 class="mb-0" id="ongoingExams">0</h3>
                    <p class="text-muted mb-0">Ongoing Exams</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-2x text-info mb-2"></i>
                    <h3 class="mb-0" id="totalStudentsAttempted">0</h3>
                    <p class="text-muted mb-0">Students Attempted</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search exams...">
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="upcoming">Upcoming</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="completed">Completed</option>
                        <option value="draft">Draft/Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="subjectFilter">
                        <option value="">All Subjects</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="batchFilter">
                        <option value="">All Batches</option>
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

    <!-- Exams Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="examsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Exam Title</th>
                            <th>Subject</th>
                            <th>Batch</th>
                            <th>Schedule</th>
                            <th>Duration</th>
                            <th>Marks</th>
                            <th>Questions</th>
                            <th>Attempts</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="examsList">
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="fas fa-spinner fa-spin fa-2x"></i>
                                <p class="mt-2">Loading exams...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script>
    $(document).ready(function() {
        loadExams();
        loadStats();
        loadFilters();

        $('#searchInput').on('keyup', debounce(loadExams, 500));
        $('#statusFilter, #subjectFilter, #batchFilter').on('change', loadExams);
        $('#refreshBtn').on('click', function() {
            loadExams();
            loadStats();
        });

        function loadStats() {
            $.ajax({
                url: '../api/teacher/exam_stats.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#totalExams').text(response.data.total || 0);
                        $('#activeExams').text(response.data.active || 0);
                        $('#ongoingExams').text(response.data.ongoing || 0);
                        $('#totalStudentsAttempted').text(response.data.students_attempted || 0);
                    }
                }
            });
        }

        function loadFilters() {
            // Load subjects
            $.ajax({
                url: '../api/teacher/my_subjects.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let html = '<option value="">All Subjects</option>';
                        response.data.forEach(function(subject) {
                            html += `<option value="${subject.id}">${subject.title}</option>`;
                        });
                        $('#subjectFilter').html(html);
                    }
                }
            });

            // Load batches
            $.ajax({
                url: '../api/teacher/my_batches.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let html = '<option value="">All Batches</option>';
                        response.data.forEach(function(batch) {
                            html += `<option value="${batch.id}">${batch.name}</option>`;
                        });
                        $('#batchFilter').html(html);
                    }
                }
            });
        }

        function loadExams() {
            const params = {
                search: $('#searchInput').val(),
                status: $('#statusFilter').val(),
                subject_id: $('#subjectFilter').val(),
                batch_id: $('#batchFilter').val()
            };

            $.ajax({
                url: '../api/teacher/exams.php',
                method: 'GET',
                data: params,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        displayExams(response.data);
                    } else {
                        $('#examsList').html('<tr><td colspan="10" class="text-center text-danger">Error: ' + response.message + '</td></tr>');
                    }
                },
                error: function() {
                    $('#examsList').html('<tr><td colspan="10" class="text-center text-danger">Failed to load exams</td></tr>');
                }
            });
        }

        function displayExams(exams) {
            if (exams.length === 0) {
                $('#examsList').html('<tr><td colspan="10" class="text-center text-muted">No exams found. <a href="create_exam.php">Create your first exam</a></td></tr>');
                return;
            }

            let html = '';
            exams.forEach(function(exam) {
                const now = new Date();
                const startTime = new Date(exam.start_time);
                const endTime = new Date(exam.end_time);

                let statusBadge = '';
                let statusClass = '';

                if (!exam.is_active) {
                    statusBadge = '<span class="badge bg-secondary">Inactive</span>';
                    statusClass = 'table-secondary';
                } else if (now < startTime) {
                    statusBadge = '<span class="badge bg-info">Upcoming</span>';
                } else if (now >= startTime && now <= endTime) {
                    statusBadge = '<span class="badge bg-warning">Ongoing</span>';
                    statusClass = 'table-warning-subtle';
                } else {
                    statusBadge = '<span class="badge bg-success">Completed</span>';
                }

                html += `
                <tr class="${statusClass}">
                    <td>
                        <strong>${exam.title}</strong>
                        <br><small class="text-muted">${exam.exam_type}</small>
                    </td>
                    <td>${exam.subject_name}</td>
                    <td>${exam.batch_name || 'All Batches'}</td>
                    <td>
                        <small>
                            <i class="fas fa-calendar-alt"></i> ${formatDateTime(exam.start_time)}<br>
                            <i class="fas fa-calendar-check"></i> ${formatDateTime(exam.end_time)}
                        </small>
                    </td>
                    <td><span class="badge bg-primary">${exam.duration_minutes} min</span></td>
                    <td>${exam.total_marks}</td>
                    <td><span class="badge bg-info">${exam.question_count || 0}</span></td>
                    <td><span class="badge bg-secondary">${exam.attempt_count || 0} / ${exam.max_attempts}</span></td>
                    <td>${statusBadge}</td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-info" onclick="viewExam(${exam.id})" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="create_exam.php?id=${exam.id}" class="btn btn-warning" title="Edit">
                                <i class="fas fa-pen-to-square"></i>
                            </a>
                            <button class="btn btn-${exam.is_active ? 'secondary' : 'success'}" 
                                    onclick="toggleStatus(${exam.id})" 
                                    title="${exam.is_active ? 'Deactivate' : 'Activate'}">
                                <i class="fas fa-${exam.is_active ? 'toggle-off' : 'toggle-on'}"></i>
                            </button>
                            <a href="exam_results.php?exam_id=${exam.id}" class="btn btn-primary" title="View Results">
                                <i class="fas fa-chart-bar"></i>
                            </a>
                            <button class="btn btn-danger" onclick="deleteExam(${exam.id}, '${exam.title}')" title="Delete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            });

            $('#examsList').html(html);
        }

        window.viewExam = function(id) {
            $.ajax({
                url: '../api/teacher/exams.php?id=' + id,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const exam = response.data;
                        Swal.fire({
                            title: exam.title,
                            html: `
                            <div class="text-start">
                                <p><strong>Subject:</strong> ${exam.subject_name}</p>
                                <p><strong>Batch:</strong> ${exam.batch_name || 'All Batches'}</p>
                                <p><strong>Type:</strong> ${exam.exam_type}</p>
                                <p><strong>Duration:</strong> ${exam.duration_minutes} minutes</p>
                                <p><strong>Total Marks:</strong> ${exam.total_marks}</p>
                                <p><strong>Passing Marks:</strong> ${exam.passing_marks}</p>
                                <p><strong>Questions:</strong> ${exam.question_count || 0}</p>
                                <p><strong>Max Attempts:</strong> ${exam.max_attempts}</p>
                                <p><strong>Schedule:</strong><br>
                                   From: ${formatDateTime(exam.start_time)}<br>
                                   To: ${formatDateTime(exam.end_time)}</p>
                                ${exam.description ? '<hr><p>' + exam.description + '</p>' : ''}
                                ${exam.instructions ? '<hr><p><strong>Instructions:</strong><br>' + exam.instructions + '</p>' : ''}
                            </div>
                        `,
                            width: 700
                        });
                    }
                }
            });
        };

        window.toggleStatus = function(id) {
            $.ajax({
                url: '../api/teacher/exams.php',
                method: 'PUT',
                data: JSON.stringify({
                    id: id,
                    toggle_active: true
                }),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success', response.message, 'success');
                        loadExams();
                        loadStats();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }
            });
        };

        window.deleteExam = function(id, title) {
            Swal.fire({
                title: 'Delete Exam?',
                html: `Are you sure you want to delete "<strong>${title}</strong>"?<br><br><span class="text-danger">This will delete all student attempts and answers!</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '../api/teacher/exams.php?id=' + id,
                        method: 'DELETE',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Deleted!', response.message, 'success');
                                loadExams();
                                loadStats();
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        }
                    });
                }
            });
        };

        function formatDateTime(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            return d.toLocaleString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function debounce(func, wait) {
            let timeout;
            return function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, arguments), wait);
            };
        }
    });
</script>

<?php include_once "../includes/footer.php"; ?>