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
$pageTitle = "Assign Subjects";
$basePath = "..";
include_once "../includes/header.php";
?>

<!-- Main content -->`n<main class="main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Assign Subjects</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignSubjectModal">
                        <i class="fas fa-plus"></i> Assign New Subject
                    </button>
                </div>
            </div>

            <!-- Filter Options -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Filter Assignments</h5>
                </div>
                <div class="card-body">
                    <form id="filterForm" class="row g-3">
                        <div class="col-md-3">
                            <label for="filterTeacher" class="form-label">Teacher</label>
                            <select class="form-select" id="filterTeacher">
                                <option value="">All Teachers</option>
                                <!-- Teachers will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterCourse" class="form-label">Course</label>
                            <select class="form-select" id="filterCourse">
                                <option value="">All Courses</option>
                                <!-- Courses will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filterBatch" class="form-label">Batch</label>
                            <select class="form-select" id="filterBatch">
                                <option value="">All Batches</option>
                                <!-- Batches will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" id="applyFilter" class="btn btn-primary me-2">Apply Filter</button>
                            <button type="button" id="resetFilter" class="btn btn-secondary">Reset</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Subject Assignments List -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Subject Assignments</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="assignmentsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Teacher</th>
                                    <th>Subject</th>
                                    <th>Course</th>
                                    <th>Batch</th>
                                    <th>Session</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="assignments-list">
                                <!-- Assignment data will be loaded here via AJAX -->
                                <tr>
                                    <td colspan="8" class="text-center">Loading assignments...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div></main>`n`n<!-- Assign Subject Modal -->
<div class="modal fade" id="assignSubjectModal" tabindex="-1" aria-labelledby="assignSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignSubjectModalLabel">Assign Subject to Teacher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="assignSubjectForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="teacherId" class="form-label">Teacher</label>
                            <select class="form-select" id="teacherId" name="teacherId" required>
                                <option value="">Select Teacher</option>
                                <!-- Teachers will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="subjectId" class="form-label">Subject</label>
                            <select class="form-select" id="subjectId" name="subjectId" required>
                                <option value="">Select Subject</option>
                                <!-- Subjects will be loaded dynamically -->
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="courseId" class="form-label">Course</label>
                            <select class="form-select" id="courseId" name="courseId" required>
                                <option value="">Select Course</option>
                                <!-- Courses will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="batchId" class="form-label">Batch</label>
                            <select class="form-select" id="batchId" name="batchId" required>
                                <option value="">Select Batch</option>
                                <!-- Batches will be loaded dynamically -->
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="sessionId" class="form-label">Session</label>
                            <select class="form-select" id="sessionId" name="sessionId" required>
                                <option value="">Select Session</option>
                                <!-- Sessions will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveAssignmentBtn">Save Assignment</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Assignment Modal -->
<div class="modal fade" id="editAssignmentModal" tabindex="-1" aria-labelledby="editAssignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAssignmentModalLabel">Edit Subject Assignment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editAssignmentForm">
                    <input type="hidden" id="editAssignmentId" name="assignmentId">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editTeacherId" class="form-label">Teacher</label>
                            <select class="form-select" id="editTeacherId" name="teacherId" required>
                                <option value="">Select Teacher</option>
                                <!-- Teachers will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="editSubjectId" class="form-label">Subject</label>
                            <select class="form-select" id="editSubjectId" name="subjectId" required>
                                <option value="">Select Subject</option>
                                <!-- Subjects will be loaded dynamically -->
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editCourseId" class="form-label">Course</label>
                            <select class="form-select" id="editCourseId" name="courseId" required>
                                <option value="">Select Course</option>
                                <!-- Courses will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="editBatchId" class="form-label">Batch</label>
                            <select class="form-select" id="editBatchId" name="batchId" required>
                                <option value="">Select Batch</option>
                                <!-- Batches will be loaded dynamically -->
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editSessionId" class="form-label">Session</label>
                            <select class="form-select" id="editSessionId" name="sessionId" required>
                                <option value="">Select Session</option>
                                <!-- Sessions will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="editStatus" class="form-label">Status</label>
                            <select class="form-select" id="editStatus" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="updateAssignmentBtn">Update Assignment</button>
            </div>
        </div>
    </div>
</div>

<?php include_once "../includes/footer.php"; ?>

<script>
    $(document).ready(function() {
        // Load assignments and dropdown options on page load
        loadAssignments();
        loadDropdownOptions();

        // Function to load assignments
        function loadAssignments(filters = {}) {
            $.ajax({
                type: "GET",
                url: "../api/admin/subject_assignments.php",
                data: filters,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        displayAssignments(response.data);
                    } else {
                        $("#assignments-list").html('<tr><td colspan="8" class="text-center text-danger">' + response.message + '</td></tr>');
                    }
                },
                error: function() {
                    $("#assignments-list").html('<tr><td colspan="8" class="text-center text-danger">Error loading assignments. Please try again.</td></tr>');
                }
            });
        }

        // Function to load all dropdown options
        function loadDropdownOptions() {
            // Load teachers
            $.ajax({
                type: "GET",
                url: "../api/admin/teachers.php",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        populateTeacherDropdowns(response.data);
                    }
                }
            });

            // Load subjects
            $.ajax({
                type: "GET",
                url: "../api/admin/subjects.php",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        populateSubjectDropdowns(response.data);
                    }
                }
            });

            // Load courses
            $.ajax({
                type: "GET",
                url: "../api/admin/courses.php",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        populateCourseDropdowns(response.data);
                    }
                }
            });

            // Load batches
            $.ajax({
                type: "GET",
                url: "../api/admin/batches.php",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        populateBatchDropdowns(response.data);
                    }
                }
            });

            // Load sessions
            $.ajax({
                type: "GET",
                url: "../api/admin/sessions.php",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        populateSessionDropdowns(response.data);
                    }
                }
            });
        }

        // Function to populate teacher dropdowns
        function populateTeacherDropdowns(teachers) {
            var options = '<option value="">Select Teacher</option>';
            var filterOptions = '<option value="">All Teachers</option>';

            $.each(teachers, function(index, teacher) {
                options += '<option value="' + teacher.id + '">' + teacher.name + '</option>';
                filterOptions += '<option value="' + teacher.id + '">' + teacher.name + '</option>';
            });

            $("#teacherId, #editTeacherId").html(options);
            $("#filterTeacher").html(filterOptions);
        }

        // Function to populate subject dropdowns
        function populateSubjectDropdowns(subjects) {
            var options = '<option value="">Select Subject</option>';

            $.each(subjects, function(index, subject) {
                options += '<option value="' + subject.id + '">' + subject.name + '</option>';
            });

            $("#subjectId, #editSubjectId").html(options);
        }

        // Function to populate course dropdowns
        function populateCourseDropdowns(courses) {
            var options = '<option value="">Select Course</option>';
            var filterOptions = '<option value="">All Courses</option>';

            $.each(courses, function(index, course) {
                options += '<option value="' + course.id + '">' + course.name + '</option>';
                filterOptions += '<option value="' + course.id + '">' + course.name + '</option>';
            });

            $("#courseId, #editCourseId").html(options);
            $("#filterCourse").html(filterOptions);
        }

        // Function to populate batch dropdowns
        function populateBatchDropdowns(batches) {
            var options = '<option value="">Select Batch</option>';
            var filterOptions = '<option value="">All Batches</option>';

            $.each(batches, function(index, batch) {
                options += '<option value="' + batch.id + '">' + batch.name + '</option>';
                filterOptions += '<option value="' + batch.id + '">' + batch.name + '</option>';
            });

            $("#batchId, #editBatchId").html(options);
            $("#filterBatch").html(filterOptions);
        }

        // Function to populate session dropdowns
        function populateSessionDropdowns(sessions) {
            var options = '<option value="">Select Session</option>';

            $.each(sessions, function(index, session) {
                options += '<option value="' + session.id + '">' + session.name + '</option>';
            });

            $("#sessionId, #editSessionId").html(options);
        }

        // Function to display assignments
        function displayAssignments(assignments) {
            var html = '';
            if (assignments.length > 0) {
                $.each(assignments, function(index, assignment) {
                    var statusClass = assignment.status === 'active' ? 'badge bg-success' : 'badge bg-secondary';

                    html += '<tr>';
                    html += '<td>' + assignment.id + '</td>';
                    html += '<td>' + assignment.teacher_name + '</td>';
                    html += '<td>' + assignment.subject_name + '</td>';
                    html += '<td>' + assignment.course_name + '</td>';
                    html += '<td>' + assignment.batch_name + '</td>';
                    html += '<td>' + assignment.session_name + '</td>';
                    html += '<td><span class="' + statusClass + '">' + assignment.status.charAt(0).toUpperCase() + assignment.status.slice(1) + '</span></td>';
                    html += '<td>';
                    html += '<button class="btn btn-sm btn-primary edit-assignment me-1" data-id="' + assignment.id + '"><i class="fas fa-edit"></i></button>';
                    html += '<button class="btn btn-sm btn-danger delete-assignment" data-id="' + assignment.id + '"><i class="fas fa-trash"></i></button>';
                    html += '</td>';
                    html += '</tr>';
                });
            } else {
                html = '<tr><td colspan="8" class="text-center">No assignments found</td></tr>';
            }
            $("#assignments-list").html(html);
        }

        // Apply filter
        $("#applyFilter").on("click", function() {
            var filters = {
                teacher_id: $("#filterTeacher").val(),
                course_id: $("#filterCourse").val(),
                batch_id: $("#filterBatch").val()
            };
            loadAssignments(filters);
        });

        // Reset filter
        $("#resetFilter").on("click", function() {
            $("#filterForm")[0].reset();
            loadAssignments();
        });

        // Save new assignment
        $("#saveAssignmentBtn").on("click", function() {
            var formData = $("#assignSubjectForm").serialize();
            console.log("Form data being sent:", formData);
            $.ajax({
                type: "POST",
                url: "../api/admin/subject_assignments.php",
                data: formData,
                dataType: "json",
                success: function(response) {
                    console.log("Server response:", response);
                    if (response.success) {
                        $("#assignSubjectModal").modal("hide");
                        $("#assignSubjectForm")[0].reset();
                        loadAssignments();
                        alert("Subject assigned successfully!");
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", xhr.responseText);
                    console.error("Status:", status);
                    console.error("Error:", error);
                    alert("Error assigning subject. Check console for details.");
                }
            });
        });

        // Edit assignment - populate modal
        $(document).on("click", ".edit-assignment", function() {
            var assignmentId = $(this).data("id");
            $.ajax({
                type: "GET",
                url: "../api/admin/subject_assignments.php?id=" + assignmentId,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        var assignment = response.data;
                        $("#editAssignmentId").val(assignment.id);
                        $("#editTeacherId").val(assignment.teacher_id);
                        $("#editSubjectId").val(assignment.subject_id);
                        $("#editCourseId").val(assignment.course_id);
                        $("#editBatchId").val(assignment.batch_id);
                        $("#editSessionId").val(assignment.session_id);
                        $("#editStatus").val(assignment.status);
                        $("#editAssignmentModal").modal("show");
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function() {
                    alert("Error loading assignment details. Please try again.");
                }
            });
        });

        // Update assignment
        $("#updateAssignmentBtn").on("click", function() {
            var formData = $("#editAssignmentForm").serialize();
            $.ajax({
                type: "PUT",
                url: "../api/admin/subject_assignments.php",
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $("#editAssignmentModal").modal("hide");
                        loadAssignments();
                        alert("Assignment updated successfully!");
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function() {
                    alert("Error updating assignment. Please try again.");
                }
            });
        });

        // Delete assignment
        $(document).on("click", ".delete-assignment", function() {
            if (confirm("Are you sure you want to delete this assignment?")) {
                var assignmentId = $(this).data("id");
                $.ajax({
                    type: "DELETE",
                    url: "../api/admin/subject_assignments.php",
                    data: {
                        id: assignmentId
                    },
                    success: function(response) {
                        if (response.success) {
                            loadAssignments();
                            alert("Assignment deleted successfully!");
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function() {
                        alert("Error deleting assignment. Please try again.");
                    }
                });
            }
        });
    });
</script>