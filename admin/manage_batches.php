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
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
$pageTitle = "Manage Batches";
$basePath = "..";
include_once "../includes/header.php";
?>

<!-- Main content -->`n<main class="main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Manage Batches</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addBatchModal">
                <i class="fas fa-plus"></i> Add New Batch
            </button>
        </div>
    </div>

    <!-- Batches List -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">Batches List</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="batchesTable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Batch Code</th>
                            <th>Batch Name</th>
                            <th>Course</th>
                            <th>Session</th>
                            <th>Start Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="batches-list">
                        <!-- Batch data will be loaded here via AJAX -->
                        <tr>
                            <td colspan="7" class="text-center">Loading batches...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>`n`n<!-- Add Batch Modal -->
<div class="modal fade" id="addBatchModal" tabindex="-1" aria-labelledby="addBatchModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addBatchModalLabel">Add New Batch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addBatchForm">
                    <div class="mb-3">
                        <label for="batchCode" class="form-label">Batch Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="batchCode" name="batchCode" placeholder="e.g., 444, 445, 452" required>
                        <small class="text-muted">Unique identifier for the batch</small>
                    </div>
                    <div class="mb-3">
                        <label for="batchName" class="form-label">Batch Name</label>
                        <input type="text" class="form-control" id="batchName" name="batchName" required>
                    </div>
                    <div class="mb-3">
                        <label for="courseId" class="form-label">Course</label>
                        <select class="form-select" id="courseId" name="courseId" required>
                            <option value="">Select Course</option>
                            <!-- Courses will be loaded here via AJAX -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="sessionId" class="form-label">Session</label>
                        <select class="form-select" id="sessionId" name="sessionId" required>
                            <option value="">Select Session</option>
                            <!-- Sessions will be loaded here via AJAX -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="startDate" class="form-label">Start Date (Optional)</label>
                        <input type="date" class="form-control" id="startDate" name="startDate">
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveBatchBtn">Save Batch</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Batch Modal -->
<div class="modal fade" id="editBatchModal" tabindex="-1" aria-labelledby="editBatchModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editBatchModalLabel">Edit Batch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editBatchForm">
                    <input type="hidden" id="editBatchId" name="batchId">
                    <div class="mb-3">
                        <label for="editBatchCode" class="form-label">Batch Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editBatchCode" name="batchCode" placeholder="e.g., 444, 445, 452" required>
                        <small class="text-muted">Unique identifier for the batch</small>
                    </div>
                    <div class="mb-3">
                        <label for="editBatchName" class="form-label">Batch Name</label>
                        <input type="text" class="form-control" id="editBatchName" name="batchName" required>
                    </div>
                    <div class="mb-3">
                        <label for="editCourseId" class="form-label">Course</label>
                        <select class="form-select" id="editCourseId" name="courseId" required>
                            <option value="">Select Course</option>
                            <!-- Courses will be loaded here via AJAX -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editSessionId" class="form-label">Session</label>
                        <select class="form-select" id="editSessionId" name="sessionId" required>
                            <option value="">Select Session</option>
                            <!-- Sessions will be loaded here via AJAX -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editStartDate" class="form-label">Start Date (Optional)</label>
                        <input type="date" class="form-control" id="editStartDate" name="startDate">
                    </div>
                    <div class="mb-3">
                        <label for="editStatus" class="form-label">Status</label>
                        <select class="form-select" id="editStatus" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="updateBatchBtn">Update Batch</button>
            </div>
        </div>
    </div>
</div>

<?php include_once "../includes/footer.php"; ?>

<script>
    $(document).ready(function() {
        // Load batches, courses and sessions on page load
        loadBatches();
        loadCourses();
        loadSessions();

        // Function to load batches
        function loadBatches() {
            $.ajax({
                type: "GET",
                url: "../api/admin/batches.php",
                dataType: "json",
                cache: false,
                success: function(response) {
                    if (response.success) {
                        displayBatches(response.data);
                    } else {
                        console.error("API Error:", response);
                        $("#batches-list").html('<tr><td colspan="8" class="text-center text-danger">' + (response.message || 'Unknown error') + '</td></tr>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", xhr.status, xhr.responseText);
                    var errorMsg = 'Error loading batches. ';
                    if (xhr.status === 500) {
                        errorMsg += 'Server error (500). Check console for details.';
                    } else if (xhr.status === 401 || xhr.status === 403) {
                        errorMsg += 'Session expired. Please <a href="../auth/login.php">login again</a>.';
                    } else {
                        errorMsg += 'Please try again or refresh the page.';
                    }
                    $("#batches-list").html('<tr><td colspan="8" class="text-center text-danger">' + errorMsg + '</td></tr>');
                }
            });
        }

        // Function to load courses
        function loadCourses() {
            $.ajax({
                type: "GET",
                url: "../api/admin/courses.php",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        var html = '<option value="">Select Course</option>';
                        $.each(response.data, function(index, course) {
                            html += '<option value="' + course.id + '">' + course.name + '</option>';
                        });
                        $("#courseId, #editCourseId").html(html);
                    }
                }
            });
        }

        // Function to load sessions
        function loadSessions() {
            $.ajax({
                type: "GET",
                url: "../api/admin/sessions.php",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        var html = '<option value="">Select Session</option>';
                        $.each(response.data, function(index, session) {
                            html += '<option value="' + session.id + '">' + session.name + '</option>';
                        });
                        $("#sessionId, #editSessionId").html(html);
                    }
                }
            });
        }

        // Function to display batches
        function displayBatches(batches) {
            var html = '';
            if (batches.length > 0) {
                $.each(batches, function(index, batch) {
                    var statusClass = '';
                    var statusText = batch.status || 'active';
                    switch (statusText) {
                        case 'active':
                            statusClass = 'badge bg-success';
                            break;
                        case 'inactive':
                            statusClass = 'badge bg-secondary';
                            break;
                        case 'completed':
                            statusClass = 'badge bg-dark';
                            break;
                        default:
                            statusClass = 'badge bg-success';
                            statusText = 'active';
                    }

                    html += '<tr>';
                    html += '<td>' + batch.id + '</td>';
                    html += '<td><strong>' + (batch.batch_code || 'N/A') + '</strong></td>';
                    html += '<td>' + batch.name + '</td>';
                    html += '<td>' + (batch.course_name || 'N/A') + '</td>';
                    html += '<td>' + (batch.session_name || 'N/A') + '</td>';
                    html += '<td>' + (batch.start_date || 'N/A') + '</td>';
                    html += '<td><span class="' + statusClass + '">' + statusText.charAt(0).toUpperCase() + statusText.slice(1) + '</span></td>';
                    html += '<td>';
                    html += '<button class="btn btn-sm btn-primary edit-batch me-1" data-id="' + batch.id + '"><i class="fas fa-edit"></i></button>';
                    html += '<button class="btn btn-sm btn-danger delete-batch" data-id="' + batch.id + '"><i class="fas fa-trash"></i></button>';
                    html += '</td>';
                    html += '</tr>';
                });
            } else {
                html = '<tr><td colspan="8" class="text-center">No batches found</td></tr>';
            }
            $("#batches-list").html(html);
        }

        // Save new batch
        $("#saveBatchBtn").on("click", function() {
            var formData = $("#addBatchForm").serialize();
            $.ajax({
                type: "POST",
                url: "../api/admin/batches.php",
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $("#addBatchModal").modal("hide");
                        $("#addBatchForm")[0].reset();
                        loadBatches();
                        alert("Batch added successfully!");
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function() {
                    alert("Error adding batch. Please try again.");
                }
            });
        });

        // Edit batch - populate modal
        $(document).on("click", ".edit-batch", function() {
            var batchId = $(this).data("id");
            $.ajax({
                type: "GET",
                url: "../api/admin/batches.php?id=" + batchId,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        var batch = response.data;
                        $("#editBatchId").val(batch.id);
                        $("#editBatchCode").val(batch.batch_code);
                        $("#editBatchName").val(batch.name);
                        $("#editCourseId").val(batch.course_id);
                        $("#editSessionId").val(batch.session_id);
                        $("#editStartDate").val(batch.start_date);
                        $("#editStatus").val(batch.status);
                        $("#editBatchModal").modal("show");
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function() {
                    alert("Error loading batch details. Please try again.");
                }
            });
        });

        // Update batch
        $("#updateBatchBtn").on("click", function() {
            var formData = {
                id: $("#editBatchId").val(),
                batch_code: $("#editBatchCode").val(),
                name: $("#editBatchName").val(),
                course_id: $("#editCourseId").val(),
                session_id: $("#editSessionId").val(),
                start_date: $("#editStartDate").val(),
                status: $("#editStatus").val()
            };

            $.ajax({
                type: "PUT",
                url: "../api/admin/batches.php",
                contentType: "application/json",
                data: JSON.stringify(formData),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        $("#editBatchModal").modal("hide");
                        loadBatches();
                        alert("Batch updated successfully!");
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Update error:", xhr.responseText);
                    alert("Error updating batch. Please try again.");
                }
            });
        });

        // Delete batch
        $(document).on("click", ".delete-batch", function() {
            if (confirm("Are you sure you want to delete this batch?")) {
                var batchId = $(this).data("id");
                $.ajax({
                    type: "DELETE",
                    url: "../api/admin/batches.php",
                    contentType: "application/json",
                    data: JSON.stringify({
                        id: batchId
                    }),
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            loadBatches();
                            alert("Batch deleted successfully!");
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Delete error:", xhr.responseText);
                        alert("Error deleting batch. Please try again.");
                    }
                });
            }
        });
    });
</script>