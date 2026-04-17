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

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$pageTitle = 'Manage Announcements';
$basePath = "..";
include '../includes/header.php';
?>

<!-- SweetAlert2 CSS & JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- TinyMCE for rich text editing - Using jsDelivr CDN -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>

<main class="main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-bullhorn me-2"></i> Manage Announcements</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-primary" id="addAnnouncementBtn">
                <i class="fas fa-plus me-2"></i> Create Announcement
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search announcements...">
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="priorityFilter">
                        <option value="">All Priorities</option>
                        <option value="urgent">Urgent</option>
                        <option value="normal">Normal</option>
                        <option value="info">Info</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="expired">Expired</option>
                        <option value="inactive">Inactive</option>
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

    <!-- Announcements Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="announcementsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Priority</th>
                            <th>Target</th>
                            <th>Valid Period</th>
                            <th>Status</th>
                            <th>Reads</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="announcementsList">
                        <tr>
                            <td colspan="8" class="text-center">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Create/Edit Modal -->
<div class="modal fade" id="announcementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Create Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="announcementForm">
                    <input type="hidden" id="announcementId">

                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" required maxlength="255">
                    </div>

                    <div class="mb-3">
                        <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="message" rows="8" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                            <select class="form-select" id="priority" required>
                                <option value="normal">Normal</option>
                                <option value="urgent">Urgent</option>
                                <option value="info">Info</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="targetAudience" class="form-label">Target Audience <span class="text-danger">*</span></label>
                            <select class="form-select" id="targetAudience" required>
                                <option value="all">All Students</option>
                                <option value="course">Specific Course</option>
                                <option value="batch">Specific Batch</option>
                                <option value="teachers">Teachers Only</option>
                            </select>
                        </div>
                    </div>

                    <div class="row" id="targetDetailsRow" style="display: none;">
                        <div class="col-md-6 mb-3" id="courseSelectDiv">
                            <label for="courseId" class="form-label">Course</label>
                            <select class="form-select" id="courseId">
                                <option value="">Select Course</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3" id="batchSelectDiv">
                            <label for="batchId" class="form-label">Batch</label>
                            <select class="form-select" id="batchId">
                                <option value="">Select Batch</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="validFrom" class="form-label">Valid From <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="validFrom" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="validUntil" class="form-label">Valid Until</label>
                            <input type="date" class="form-control" id="validUntil">
                            <small class="text-muted">Leave empty for no expiry</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="isActive" checked>
                            <label class="form-check-label" for="isActive">
                                Active (visible to students)
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveAnnouncementBtn">
                    <i class="fas fa-save me-2"></i> Save Announcement
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        let coursesData = [];
        let batchesData = [];
        let editor;

        // Initialize TinyMCE
        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: '#message',
                height: 300,
                menubar: false,
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'charmap', 'preview',
                    'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'table', 'help', 'wordcount'
                ],
                toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | bullist numlist | removeformat | help',
                content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }'
            });
        } else {
            console.error('TinyMCE not loaded. Using plain textarea.');
            // Optionally show a message to the user
            $('#message').before('<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Rich text editor not available. Using plain text mode.</div>');
        }

        // Load initial data
        loadAnnouncements();
        loadCourses();
        loadBatches();

        // Set default date to today
        $('#validFrom').val(new Date().toISOString().split('T')[0]);

        // Search and filter handlers
        $('#searchInput, #priorityFilter, #statusFilter').on('change keyup', debounce(loadAnnouncements, 500));
        $('#refreshBtn').click(loadAnnouncements);

        // Add announcement button
        $('#addAnnouncementBtn').click(function() {
            resetForm();
            $('#modalTitle').text('Create Announcement');
            $('#announcementModal').modal('show');
        });

        // Target audience change handler
        $('#targetAudience').change(function() {
            const val = $(this).val();
            if (val === 'course') {
                $('#targetDetailsRow, #courseSelectDiv').show();
                $('#batchSelectDiv').hide();
            } else if (val === 'batch') {
                $('#targetDetailsRow, #courseSelectDiv, #batchSelectDiv').show();
            } else {
                $('#targetDetailsRow').hide();
            }
        });

        // Course change handler
        $('#courseId').change(function() {
            filterBatchesByCourse($(this).val());
        });

        // Save announcement
        $('#saveAnnouncementBtn').click(function() {
            saveAnnouncement();
        });

        // Load announcements
        function loadAnnouncements() {
            const search = $('#searchInput').val();
            const priority = $('#priorityFilter').val();
            const status = $('#statusFilter').val();

            console.log('Loading announcements with params:', {
                search,
                priority,
                status
            });

            $.ajax({
                url: '../api/admin/announcements.php',
                method: 'GET',
                data: {
                    search,
                    priority,
                    status
                },
                dataType: 'json',
                success: function(response) {
                    console.log('API Response:', response);
                    if (response.success) {
                        displayAnnouncements(response.data);
                    } else {
                        console.error('API Error:', response.error);
                        $('#announcementsList').html('<tr><td colspan="8" class="text-center text-danger">Error: ' + response.error + '</td></tr>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        xhr,
                        status,
                        error
                    });
                    console.error('Response Text:', xhr.responseText);
                    $('#announcementsList').html('<tr><td colspan="8" class="text-center text-danger">Failed to load announcements. Check console for details.</td></tr>');
                }
            });
        }

        // Display announcements
        function displayAnnouncements(announcements) {
            console.log('Displaying announcements:', announcements);

            if (announcements.length === 0) {
                $('#announcementsList').html('<tr><td colspan="8" class="text-center text-muted">No announcements found</td></tr>');
                return;
            }

            let html = '';
            announcements.forEach(function(ann) {
                const priorityBadge = {
                    'urgent': '<span class="badge bg-danger">Urgent</span>',
                    'normal': '<span class="badge bg-primary">Normal</span>',
                    'info': '<span class="badge bg-info">Info</span>'
                } [ann.priority];

                const targetText = {
                    'all': 'All Students',
                    'course': `Course: ${ann.course_name || 'N/A'}`,
                    'batch': `Batch: ${ann.batch_name || 'N/A'}`,
                    'teachers': 'Teachers Only'
                } [ann.target_audience];

                const isExpired = ann.valid_until && new Date(ann.valid_until) < new Date();
                const statusBadge = ann.is_active == 1 && !isExpired ?
                    '<span class="badge bg-success">Active</span>' :
                    isExpired ?
                    '<span class="badge bg-warning">Expired</span>' :
                    '<span class="badge bg-secondary">Inactive</span>';

                html += `
                <tr>
                    <td><strong>${ann.title}</strong></td>
                    <td>${priorityBadge}</td>
                    <td>${targetText}</td>
                    <td>
                        <small>${formatDate(ann.valid_from)}<br>
                        ${ann.valid_until ? 'to ' + formatDate(ann.valid_until) : '<em>No expiry</em>'}</small>
                    </td>
                    <td>${statusBadge}</td>
                    <td><span class="badge bg-info">${ann.read_count || 0}</span></td>
                    <td><small>${formatDateTime(ann.created_at)}</small></td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="viewAnnouncement(${ann.id})" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-warning" onclick="editAnnouncement(${ann.id})" title="Edit">
                            <i class="fas fa-pen-to-square"></i>
                        </button>
                        <button class="btn btn-sm ${ann.is_active == 1 ? 'btn-secondary' : 'btn-success'}" 
                                onclick="toggleStatus(${ann.id})"
                                title="${ann.is_active == 1 ? 'Deactivate' : 'Activate'}">
                            <i class="fas fa-${ann.is_active == 1 ? 'toggle-off' : 'toggle-on'}"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteAnnouncement(${ann.id}, '${ann.title}')" title="Delete">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `;
            });

            $('#announcementsList').html(html);
        }

        // Load courses
        function loadCourses() {
            $.ajax({
                url: '../api/admin/courses.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        coursesData = response.data;
                        let html = '<option value="">Select Course</option>';
                        response.data.forEach(function(course) {
                            html += `<option value="${course.id}">${course.title}</option>`;
                        });
                        $('#courseId').html(html);
                    }
                }
            });
        }

        // Load batches
        function loadBatches() {
            $.ajax({
                url: '../api/admin/batches.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        batchesData = response.data;
                    }
                }
            });
        }

        // Filter batches by course
        function filterBatchesByCourse(courseId) {
            if (!courseId) {
                $('#batchId').html('<option value="">Select Batch</option>');
                return;
            }

            const filtered = batchesData.filter(b => b.course_id == courseId);
            let html = '<option value="">Select Batch</option>';
            filtered.forEach(function(batch) {
                html += `<option value="${batch.id}">${batch.name}</option>`;
            });
            $('#batchId').html(html);
        }

        // Save announcement
        function saveAnnouncement() {
            const id = $('#announcementId').val();
            const data = {
                id: id || null,
                title: $('#title').val(),
                message: (typeof tinymce !== 'undefined' && tinymce.get('message')) ?
                    tinymce.get('message').getContent() :
                    $('#message').val(),
                priority: $('#priority').val(),
                target_audience: $('#targetAudience').val(),
                course_id: $('#courseId').val() || null,
                batch_id: $('#batchId').val() || null,
                valid_from: $('#validFrom').val(),
                valid_until: $('#validUntil').val() || null,
                is_active: $('#isActive').is(':checked') ? 1 : 0
            };

            if (!data.title || !data.message || !data.valid_from) {
                Swal.fire('Error', 'Please fill all required fields', 'error');
                return;
            }

            const method = id ? 'PUT' : 'POST';

            $.ajax({
                url: '../api/admin/announcements.php',
                method: method,
                data: JSON.stringify(data),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success', response.message, 'success');
                        $('#announcementModal').modal('hide');
                        loadAnnouncements();
                    } else {
                        Swal.fire('Error', response.error, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to save announcement', 'error');
                }
            });
        }

        // Reset form
        function resetForm() {
            $('#announcementForm')[0].reset();
            $('#announcementId').val('');
            if (typeof tinymce !== 'undefined' && tinymce.get('message')) {
                tinymce.get('message').setContent('');
            } else {
                $('#message').val('');
            }
            $('#validFrom').val(new Date().toISOString().split('T')[0]);
            $('#isActive').prop('checked', true);
            $('#targetDetailsRow').hide();
        }

        // Global functions for buttons
        window.viewAnnouncement = function(id) {
            $.ajax({
                url: '../api/admin/announcements.php?id=' + id,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const ann = response.data;
                        Swal.fire({
                            title: ann.title,
                            html: `
                            <div class="text-start">
                                <p><strong>Priority:</strong> ${ann.priority}</p>
                                <p><strong>Target:</strong> ${ann.target_audience}</p>
                                <p><strong>Valid:</strong> ${formatDate(ann.valid_from)} ${ann.valid_until ? '- ' + formatDate(ann.valid_until) : ''}</p>
                                <hr>
                                <div>${ann.message}</div>
                            </div>
                        `,
                            width: 800
                        });
                    }
                }
            });
        };

        window.editAnnouncement = function(id) {
            $.ajax({
                url: '../api/admin/announcements.php?id=' + id,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const ann = response.data;
                        $('#announcementId').val(ann.id);
                        $('#title').val(ann.title);
                        if (typeof tinymce !== 'undefined' && tinymce.get('message')) {
                            tinymce.get('message').setContent(ann.message);
                        } else {
                            $('#message').val(ann.message);
                        }
                        $('#priority').val(ann.priority);
                        $('#targetAudience').val(ann.target_audience).trigger('change');
                        $('#courseId').val(ann.course_id || '');
                        if (ann.course_id) {
                            filterBatchesByCourse(ann.course_id);
                            setTimeout(() => $('#batchId').val(ann.batch_id || ''), 100);
                        }
                        $('#validFrom').val(ann.valid_from);
                        $('#validUntil').val(ann.valid_until || '');
                        $('#isActive').prop('checked', ann.is_active == 1);

                        $('#modalTitle').text('Edit Announcement');
                        $('#announcementModal').modal('show');
                    }
                }
            });
        };

        window.toggleStatus = function(id) {
            $.ajax({
                url: '../api/admin/announcements.php',
                method: 'PUT',
                data: JSON.stringify({
                    id: id,
                    toggle_active: true
                }),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        loadAnnouncements();
                        Swal.fire('Success', response.message, 'success');
                    }
                }
            });
        };

        window.deleteAnnouncement = function(id, title) {
            Swal.fire({
                title: 'Delete Announcement?',
                text: `Are you sure you want to delete "${title}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Yes, delete it'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '../api/admin/announcements.php?id=' + id,
                        method: 'DELETE',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Deleted!', response.message, 'success');
                                loadAnnouncements();
                            } else {
                                Swal.fire('Error', response.error, 'error');
                            }
                        }
                    });
                }
            });
        };

        // Utility functions
        function formatDate(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            return d.toLocaleDateString('en-GB');
        }

        function formatDateTime(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            return d.toLocaleString('en-GB');
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

<?php include '../includes/footer.php'; ?>