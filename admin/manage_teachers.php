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
$pageTitle = "Manage Teachers";
$basePath = "..";
include_once "../includes/header.php";
?>

<!-- Main content -->`n<main class="main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Teachers</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
                        <i class="fas fa-plus"></i> Add New Teacher
                    </button>
                </div>
            </div>

            <!-- Teachers List -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Teachers List</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="teachersTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Qualification</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="teachers-list">
                                <!-- Teacher data will be loaded here via AJAX -->
                                <tr>
                                    <td colspan="7" class="text-center">Loading teachers...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div></main>`n`n<!-- Add Teacher Modal -->
<div class="modal fade" id="addTeacherModal" tabindex="-1" aria-labelledby="addTeacherModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTeacherModalLabel">Add New Teacher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addTeacherForm">
                    <div class="mb-3">
                        <label for="teacherName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="teacherName" name="teacherName" required>
                    </div>
                    <div class="mb-3">
                        <label for="teacherEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="teacherEmail" name="teacherEmail" required>
                    </div>
                    <div class="mb-3">
                        <label for="teacherPhone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="teacherPhone" name="teacherPhone" placeholder="9876543210" maxlength="10" pattern="[0-9]{10}" required>
                    </div>
                    <div class="mb-3">
                        <label for="teacherQualification" class="form-label">Qualification</label>
                        <input type="text" class="form-control" id="teacherQualification" name="teacherQualification" required>
                    </div>
                    <div class="mb-3">
                        <label for="teacherPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="teacherPassword" name="teacherPassword" required>
                    </div>
                    <div class="mb-3">
                        <label for="teacherStatus" class="form-label">Status</label>
                        <select class="form-select" id="teacherStatus" name="teacherStatus" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveTeacherBtn">Save Teacher</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Teacher Modal -->
<div class="modal fade" id="editTeacherModal" tabindex="-1" aria-labelledby="editTeacherModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTeacherModalLabel">Edit Teacher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editTeacherForm">
                    <input type="hidden" id="editTeacherId" name="teacherId">
                    <div class="mb-3">
                        <label for="editTeacherName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="editTeacherName" name="teacherName" required>
                    </div>
                    <div class="mb-3">
                        <label for="editTeacherEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editTeacherEmail" name="teacherEmail" required>
                    </div>
                    <div class="mb-3">
                        <label for="editTeacherPhone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="editTeacherPhone" name="teacherPhone" required>
                    </div>
                    <div class="mb-3">
                        <label for="editTeacherQualification" class="form-label">Qualification</label>
                        <input type="text" class="form-control" id="editTeacherQualification" name="teacherQualification" required>
                    </div>
                    <div class="mb-3">
                        <label for="editTeacherPassword" class="form-label">New Password (leave blank to keep current)</label>
                        <input type="password" class="form-control" id="editTeacherPassword" name="teacherPassword">
                    </div>
                    <div class="mb-3">
                        <label for="editTeacherStatus" class="form-label">Status</label>
                        <select class="form-select" id="editTeacherStatus" name="teacherStatus" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="updateTeacherBtn">Update Teacher</button>
            </div>
        </div>
    </div>
</div>

<?php include_once "../includes/footer.php"; ?>

<script>
    $(document).ready(function() {
        // Load teachers on page load
        loadTeachers();

        // Function to load teachers
        function loadTeachers() {
            $.ajax({
                type: "GET",
                url: "../api/admin/teachers.php",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        displayTeachers(response.data);
                    } else {
                        $("#teachers-list").html('<tr><td colspan="7" class="text-center text-danger">' + response.message + '</td></tr>');
                    }
                },
                error: function() {
                    $("#teachers-list").html('<tr><td colspan="7" class="text-center text-danger">Error loading teachers. Please try again.</td></tr>');
                }
            });
        }

        // Function to display teachers
        function displayTeachers(teachers) {
            var html = '';
            if (teachers.length > 0) {
                $.each(teachers, function(index, teacher) {
                    var statusClass = teacher.status === 'active' ? 'badge bg-success' : 'badge bg-secondary';

                    html += '<tr>';
                    html += '<td>' + teacher.id + '</td>';
                    html += '<td>' + teacher.name + '</td>';
                    html += '<td>' + teacher.email + '</td>';
                    html += '<td>' + teacher.phone + '</td>';
                    html += '<td>' + teacher.qualification + '</td>';
                    html += '<td><span class="' + statusClass + '">' + teacher.status.charAt(0).toUpperCase() + teacher.status.slice(1) + '</span></td>';
                    html += '<td>';
                    html += '<button class="btn btn-sm btn-primary edit-teacher me-1" data-id="' + teacher.id + '"><i class="fas fa-edit"></i></button>';
                    html += '<button class="btn btn-sm btn-danger delete-teacher" data-id="' + teacher.id + '"><i class="fas fa-trash"></i></button>';
                    html += '</td>';
                    html += '</tr>';
                });
            } else {
                html = '<tr><td colspan="7" class="text-center">No teachers found</td></tr>';
            }
            $("#teachers-list").html(html);
        }

        // Save new teacher
        $("#saveTeacherBtn").on("click", function() {
            var formData = $("#addTeacherForm").serialize();
            $.ajax({
                type: "POST",
                url: "../api/admin/teachers.php",
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $("#addTeacherModal").modal("hide");
                        $("#addTeacherForm")[0].reset();
                        loadTeachers();
                        alert("Teacher added successfully!");
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function() {
                    alert("Error adding teacher. Please try again.");
                }
            });
        });

        // Edit teacher - populate modal
        $(document).on("click", ".edit-teacher", function() {
            var teacherId = $(this).data("id");
            $.ajax({
                type: "GET",
                url: "../api/admin/teachers.php?id=" + teacherId,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        var teacher = response.data;
                        $("#editTeacherId").val(teacher.id);
                        $("#editTeacherName").val(teacher.name);
                        $("#editTeacherEmail").val(teacher.email);
                        $("#editTeacherPhone").val(teacher.phone);
                        $("#editTeacherQualification").val(teacher.qualification);
                        $("#editTeacherStatus").val(teacher.status);
                        $("#editTeacherModal").modal("show");
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function() {
                    alert("Error loading teacher details. Please try again.");
                }
            });
        });

        // Update teacher
        $("#updateTeacherBtn").on("click", function() {

            var formData = {
                id: $("#editTeacherId").val(),
                name: $("#editTeacherName").val(),
                email: $("#editTeacherEmail").val(),
                phone: $("#editTeacherPhone").val(),
                qualification: $("#editTeacherQualification").val(),
                status: $("#editTeacherStatus").val()
            };

            // Only include password if it's not empty
            var password = $("#editTeacherPassword").val();
            if (password && password.trim() !== '') {
                formData.password = password;
            }

            $.ajax({
                type: "PUT",
                url: "../api/admin/teachers.php",
                contentType: "application/json",
                data: JSON.stringify(formData),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        $("#editTeacherModal").modal("hide");
                        loadTeachers();
                        alert("Teacher updated successfully!");
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Update error:", xhr.responseText);
                    alert("Error updating teacher. Please try again.");
                }
            });
        });

        // Delete teacher
        $(document).on("click", ".delete-teacher", function() {
            if (confirm("Are you sure you want to delete this teacher?")) {
                var teacherId = $(this).data("id");
                $.ajax({
                    type: "DELETE",
                    url: "../api/admin/teachers.php",
                    contentType: "application/json",
                    data: JSON.stringify({
                        id: teacherId
                    }),
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            loadTeachers();
                            alert("Teacher deleted successfully!");
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function() {
                        alert("Error deleting teacher. Please try again.");
                    }
                });
            }
        });
    });
</script>