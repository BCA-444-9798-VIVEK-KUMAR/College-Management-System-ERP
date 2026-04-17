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

// Start session
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect to login page
    header("Location: ../auth/login.php");
    exit;
}

// Set page title and include header
$pageTitle = "Manage Sessions";
$basePath = "..";
include_once "../includes/header.php";
?>

<!-- Main content -->
<main class="main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Manage Sessions</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addSessionModal">
                <i class="fas fa-plus"></i> Add New Session
            </button>
        </div>
    </div>

    <!-- Sessions List -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">Academic Sessions</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="sessionsTable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Session Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="sessions-list">
                        <!-- Session data will be loaded here via AJAX -->
                        <tr>
                            <td colspan="6" class="text-center">Loading sessions...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>`n`n<!-- Add Session Modal -->
<div class="modal fade" id="addSessionModal" tabindex="-1" aria-labelledby="addSessionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSessionModalLabel">Add New Session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addSessionForm">
                    <div class="mb-3">
                        <label for="sessionName" class="form-label">Session Name</label>
                        <input type="text" class="form-control" id="sessionName" name="sessionName" required>
                    </div>
                    <div class="mb-3">
                        <label for="startDate" class="form-label">Start Date (Optional)</label>
                        <input type="date" class="form-control" id="startDate" name="startDate">
                    </div>
                    <div class="mb-3">
                        <label for="endDate" class="form-label">End Date (Optional)</label>
                        <input type="date" class="form-control" id="endDate" name="endDate">
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="upcoming">Upcoming</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveSessionBtn">Save Session</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Session Modal -->
<div class="modal fade" id="editSessionModal" tabindex="-1" aria-labelledby="editSessionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSessionModalLabel">Edit Session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editSessionForm">
                    <input type="hidden" id="editSessionId" name="sessionId">
                    <div class="mb-3">
                        <label for="editSessionName" class="form-label">Session Name</label>
                        <input type="text" class="form-control" id="editSessionName" name="sessionName" required>
                    </div>
                    <div class="mb-3">
                        <label for="editStartDate" class="form-label">Start Date (Optional)</label>
                        <input type="date" class="form-control" id="editStartDate" name="startDate">
                    </div>
                    <div class="mb-3">
                        <label for="editEndDate" class="form-label">End Date (Optional)</label>
                        <input type="date" class="form-control" id="editEndDate" name="endDate">
                    </div>
                    <div class="mb-3">
                        <label for="editStatus" class="form-label">Status</label>
                        <select class="form-select" id="editStatus" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="upcoming">Upcoming</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="updateSessionBtn">Update Session</button>
            </div>
        </div>
    </div>
</div>

<?php include_once "../includes/footer.php"; ?>

<script>
    $(document).ready(function() {
        // Load sessions on page load
        loadSessions();

        // Function to load sessions
        function loadSessions() {
            $.ajax({
                type: "GET",
                url: "../api/admin/sessions.php",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        displaySessions(response.data);
                    } else {
                        $("#sessions-list").html('<tr><td colspan="6" class="text-center text-danger">' + response.message + '</td></tr>');
                    }
                },
                error: function() {
                    $("#sessions-list").html('<tr><td colspan="6" class="text-center text-danger">Error loading sessions. Please try again.</td></tr>');
                }
            });
        }

        // Function to display sessions
        function displaySessions(sessions) {
            var html = '';
            if (sessions.length > 0) {
                $.each(sessions, function(index, session) {
                    var statusClass = '';
                    var statusText = session.status || 'active';
                    switch (statusText) {
                        case 'active':
                            statusClass = 'badge bg-success';
                            break;
                        case 'inactive':
                            statusClass = 'badge bg-secondary';
                            break;
                        case 'upcoming':
                            statusClass = 'badge bg-info';
                            break;
                        case 'completed':
                            statusClass = 'badge bg-dark';
                            break;
                        default:
                            statusClass = 'badge bg-success';
                            statusText = 'active';
                    }

                    html += '<tr>';
                    html += '<td>' + session.id + '</td>';
                    html += '<td>' + (session.name || session.title || 'N/A') + '</td>';
                    html += '<td>' + (session.start_date || 'N/A') + '</td>';
                    html += '<td>' + (session.end_date || 'N/A') + '</td>';
                    html += '<td><span class="' + statusClass + '">' + statusText.charAt(0).toUpperCase() + statusText.slice(1) + '</span></td>';
                    html += '<td>';
                    html += '<button class="btn btn-sm btn-primary edit-session me-1" data-id="' + session.id + '"><i class="fas fa-edit"></i></button>';
                    html += '<button class="btn btn-sm btn-danger delete-session" data-id="' + session.id + '"><i class="fas fa-trash"></i></button>';
                    html += '</td>';
                    html += '</tr>';
                });
            } else {
                html = '<tr><td colspan="6" class="text-center">No sessions found</td></tr>';
            }
            $("#sessions-list").html(html);
        }

        // Save new session
        $("#saveSessionBtn").on("click", function() {
            var formData = $("#addSessionForm").serialize();
            $.ajax({
                type: "POST",
                url: "../api/admin/sessions.php",
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $("#addSessionModal").modal("hide");
                        $("#addSessionForm")[0].reset();
                        loadSessions();
                        alert("Session added successfully!");
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function() {
                    alert("Error adding session. Please try again.");
                }
            });
        });

        // Edit session - populate modal
        $(document).on("click", ".edit-session", function() {
            var sessionId = $(this).data("id");
            $.ajax({
                type: "GET",
                url: "../api/admin/sessions.php?id=" + sessionId,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        var session = response.data;
                        $("#editSessionId").val(session.id);
                        $("#editSessionName").val(session.name);
                        $("#editStartDate").val(session.start_date);
                        $("#editEndDate").val(session.end_date);
                        $("#editStatus").val(session.status);
                        $("#editSessionModal").modal("show");
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function() {
                    alert("Error loading session details. Please try again.");
                }
            });
        });

        // Update session
        $("#updateSessionBtn").on("click", function() {
            var formData = {
                id: $("#editSessionId").val(),
                name: $("#editSessionName").val(),
                start_date: $("#editStartDate").val(),
                end_date: $("#editEndDate").val(),
                status: $("#editStatus").val()
            };

            $.ajax({
                type: "PUT",
                url: "../api/admin/sessions.php",
                contentType: "application/json",
                data: JSON.stringify(formData),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        $("#editSessionModal").modal("hide");
                        loadSessions();
                        alert("Session updated successfully!");
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Update error:", xhr.responseText);
                    alert("Error updating session. Please try again.");
                }
            });
        });

        // Delete session
        $(document).on("click", ".delete-session", function() {
            if (confirm("Are you sure you want to delete this session?")) {
                var sessionId = $(this).data("id");
                $.ajax({
                    type: "DELETE",
                    url: "../api/admin/sessions.php",
                    contentType: "application/json",
                    data: JSON.stringify({
                        id: sessionId
                    }),
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            loadSessions();
                            alert("Session deleted successfully!");
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Delete error:", xhr.responseText);
                        alert("Error deleting session. Please try again.");
                    }
                });
            }
        });
    });
</script>