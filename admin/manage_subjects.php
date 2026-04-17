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
$pageTitle = "Manage Subjects";
$basePath = "..";
include_once "../includes/header.php";
?>

<!-- Main content -->`n<main class="main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Subjects</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                        <i class="fas fa-plus"></i> Add New Subject
                    </button>
                </div>
            </div>

            <!-- Subjects List -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Subjects List</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="subjectsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Subject Code</th>
                                    <th>Subject Name</th>
                                    <th>Course</th>
                                    <th>Credit Hours</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="subjects-list">
                                <!-- Subject data will be loaded here via AJAX -->
                                <tr>
                                    <td colspan="6" class="text-center">Loading subjects...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div></main>`n`n<!-- Add Subject Modal -->
<div class="modal fade" id="addSubjectModal" tabindex="-1" aria-labelledby="addSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSubjectModalLabel">Add New Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addSubjectForm">
                    <div class="mb-3">
                        <label for="subjectCode" class="form-label">Subject Code</label>
                        <input type="text" class="form-control" id="subjectCode" name="subjectCode" required>
                    </div>
                    <div class="mb-3">
                        <label for="subjectName" class="form-label">Subject Name</label>
                        <input type="text" class="form-control" id="subjectName" name="subjectName" required>
                    </div>
                    <div class="mb-3">
                        <label for="courseId" class="form-label">Course</label>
                        <select class="form-select" id="courseId" name="courseId" required>
                            <option value="">Select Course</option>
                            <!-- Courses will be loaded here via AJAX -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="creditHours" class="form-label">Credit Hours</label>
                        <input type="number" class="form-control" id="creditHours" name="creditHours" min="1" max="10" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveSubjectBtn">Save Subject</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Subject Modal -->
<div class="modal fade" id="editSubjectModal" tabindex="-1" aria-labelledby="editSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSubjectModalLabel">Edit Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editSubjectForm">
                    <input type="hidden" id="editSubjectId" name="subjectId">
                    <div class="mb-3">
                        <label for="editSubjectCode" class="form-label">Subject Code</label>
                        <input type="text" class="form-control" id="editSubjectCode" name="subjectCode" required>
                    </div>
                    <div class="mb-3">
                        <label for="editSubjectName" class="form-label">Subject Name</label>
                        <input type="text" class="form-control" id="editSubjectName" name="subjectName" required>
                    </div>
                    <div class="mb-3">
                        <label for="editCourseId" class="form-label">Course</label>
                        <select class="form-select" id="editCourseId" name="courseId" required>
                            <option value="">Select Course</option>
                            <!-- Courses will be loaded here via AJAX -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editCreditHours" class="form-label">Credit Hours</label>
                        <input type="number" class="form-control" id="editCreditHours" name="creditHours" min="1" max="10" required>
                    </div>
                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editDescription" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="updateSubjectBtn">Update Subject</button>
            </div>
        </div>
    </div>
</div>

<?php include_once "../includes/footer.php"; ?>

<script>
    $(document).ready(function() {
        // Load subjects and courses on page load
        loadSubjects();
        loadCourses();

        // Function to load subjects
        function loadSubjects() {
            $.ajax({
                type: "GET",
                url: "../api/admin/subjects.php",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        displaySubjects(response.data);
                    } else {
                        $("#subjects-list").html('<tr><td colspan="6" class="text-center text-danger">' + response.message + '</td></tr>');
                    }
                },
                error: function() {
                    $("#subjects-list").html('<tr><td colspan="6" class="text-center text-danger">Error loading subjects. Please try again.</td></tr>');
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

        // Function to display subjects
        function displaySubjects(subjects) {
            var html = '';
            if (subjects.length > 0) {
                $.each(subjects, function(index, subject) {
                    html += '<tr>';
                    html += '<td>' + subject.id + '</td>';
                    html += '<td>' + subject.code + '</td>';
                    html += '<td>' + subject.name + '</td>';
                    html += '<td>' + subject.course_name + '</td>';
                    html += '<td>' + subject.credit_hours + '</td>';
                    html += '<td>';
                    html += '<button class="btn btn-sm btn-primary edit-subject me-1" data-id="' + subject.id + '"><i class="fas fa-edit"></i></button>';
                    html += '<button class="btn btn-sm btn-danger delete-subject" data-id="' + subject.id + '"><i class="fas fa-trash"></i></button>';
                    html += '</td>';
                    html += '</tr>';
                });
            } else {
                html = '<tr><td colspan="6" class="text-center">No subjects found</td></tr>';
            }
            $("#subjects-list").html(html);
        }

        // Save new subject
        $("#saveSubjectBtn").on("click", function() {
            var formData = $("#addSubjectForm").serialize();
            $.ajax({
                type: "POST",
                url: "../api/admin/subjects.php",
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $("#addSubjectModal").modal("hide");
                        $("#addSubjectForm")[0].reset();
                        loadSubjects();
                        alert("Subject added successfully!");
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function() {
                    alert("Error adding subject. Please try again.");
                }
            });
        });

        // Edit subject - populate modal
        $(document).on("click", ".edit-subject", function() {
            var subjectId = $(this).data("id");
            $.ajax({
                type: "GET",
                url: "../api/admin/subjects.php?id=" + subjectId,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        var subject = response.data;
                        $("#editSubjectId").val(subject.id);
                        $("#editSubjectCode").val(subject.code);
                        $("#editSubjectName").val(subject.name);
                        $("#editCourseId").val(subject.course_id);
                        $("#editCreditHours").val(subject.credit_hours);
                        $("#editDescription").val(subject.description);
                        $("#editSubjectModal").modal("show");
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function() {
                    alert("Error loading subject details. Please try again.");
                }
            });
        });

        // Update subject
        $("#updateSubjectBtn").on("click", function() {
            var formData = {
                id: $("#editSubjectId").val(),
                code: $("#editSubjectCode").val(),
                title: $("#editSubjectTitle").val(),
                name: $("#editSubjectName").val(),
                course_id: $("#editCourseId").val()
            };

            $.ajax({
                type: "PUT",
                url: "../api/admin/subjects.php",
                contentType: "application/json",
                data: JSON.stringify(formData),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        $("#editSubjectModal").modal("hide");
                        loadSubjects();
                        alert("Subject updated successfully!");
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Update error:", xhr.responseText);
                    alert("Error updating subject. Please try again.");
                }
            });
        });

        // Delete subject
        $(document).on("click", ".delete-subject", function() {
            if (confirm("Are you sure you want to delete this subject?")) {
                var subjectId = $(this).data("id");
                $.ajax({
                    type: "DELETE",
                    url: "../api/admin/subjects.php",
                    contentType: "application/json",
                    data: JSON.stringify({
                        id: subjectId
                    }),
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            loadSubjects();
                            alert("Subject deleted successfully!");
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function() {
                        alert("Error deleting subject. Please try again.");
                    }
                });
            }
        });
    });
</script>