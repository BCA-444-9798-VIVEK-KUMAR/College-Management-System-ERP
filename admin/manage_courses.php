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
$pageTitle = "Manage Courses";
$basePath = "..";
include_once "../includes/header.php";
?>

<!-- Main content -->
<main class="main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Manage Courses</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                <i class="fas fa-plus"></i> Add New Course
            </button>
        </div>
    </div>

    <!-- Courses List -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">Courses List</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="coursesTable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Course Name</th>
                            <th>Course Code</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="courses-list">
                        <!-- Course data will be loaded here via AJAX -->
                        <tr>
                            <td colspan="4" class="text-center">Loading courses...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Add Course Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCourseModalLabel">Add New Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addCourseForm">
                    <div class="mb-3">
                        <label for="courseName" class="form-label">Course Name</label>
                        <input type="text" class="form-control" id="courseName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="courseCode" class="form-label">Course Code</label>
                        <input type="text" class="form-control" id="courseCode" name="code" placeholder="Optional - will be auto-generated if empty">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveCourseBtn">Save Course</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Course Modal -->
<div class="modal fade" id="editCourseModal" tabindex="-1" aria-labelledby="editCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCourseModalLabel">Edit Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCourseForm">
                    <input type="hidden" id="editCourseId" name="id">
                    <div class="mb-3">
                        <label for="editCourseName" class="form-label">Course Name</label>
                        <input type="text" class="form-control" id="editCourseName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editCourseCode" class="form-label">Course Code</label>
                        <input type="text" class="form-control" id="editCourseCode" name="code" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="updateCourseBtn">Update Course</button>
            </div>
        </div>
    </div>
</div>

<?php include_once "../includes/footer.php"; ?>

<script>
    $(document).ready(function() {
        // Load courses on page load
        loadCourses();

        // Function to load courses
        function loadCourses() {
            $.ajax({
                type: "GET",
                url: "../api/admin/courses.php",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        displayCourses(response.data);
                    } else {
                        $("#courses-list").html('<tr><td colspan="4" class="text-center text-danger">' + response.message + '</td></tr>');
                    }
                },
                error: function() {
                    $("#courses-list").html('<tr><td colspan="4" class="text-center text-danger">Error loading courses. Please try again.</td></tr>');
                }
            });
        }

        // Function to display courses
        function displayCourses(courses) {
            var html = '';
            if (courses.length > 0) {
                $.each(courses, function(index, course) {
                    html += '<tr>';
                    html += '<td>' + course.id + '</td>';
                    html += '<td>' + course.name + '</td>';
                    html += '<td>' + (course.code || 'N/A') + '</td>';
                    html += '<td>';
                    html += '<button class="btn btn-sm btn-primary edit-course me-1" data-id="' + course.id + '"><i class="fas fa-edit"></i></button>';
                    html += '<button class="btn btn-sm btn-danger delete-course" data-id="' + course.id + '"><i class="fas fa-trash"></i></button>';
                    html += '</td>';
                    html += '</tr>';
                });
            } else {
                html = '<tr><td colspan="4" class="text-center">No courses found</td></tr>';
            }
            $("#courses-list").html(html);
        }

        // Save new course
        $("#saveCourseBtn").on("click", function() {
            var formData = $("#addCourseForm").serialize();
            $.ajax({
                type: "POST",
                url: "../api/admin/courses.php",
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $("#addCourseModal").modal("hide");
                        $("#addCourseForm")[0].reset();
                        loadCourses();
                        alert("Course added successfully!");
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function() {
                    alert("Error adding course. Please try again.");
                }
            });
        });

        // Edit course - populate modal
        $(document).on("click", ".edit-course", function() {
            var courseId = $(this).data("id");
            // Find the course in the current data
            var row = $(this).closest('tr');
            var name = row.find('td:eq(1)').text();
            var code = row.find('td:eq(2)').text();

            $("#editCourseId").val(courseId);
            $("#editCourseName").val(name);
            $("#editCourseCode").val(code === 'N/A' ? '' : code);
            $("#editCourseModal").modal("show");
        });

        // Update course
        $("#updateCourseBtn").on("click", function() {
            var courseName = $("#editCourseName").val();
            var courseCode = $("#editCourseCode").val();

            // Validate before sending
            if (!courseName || courseName.trim() === '') {
                alert("Course name is required");
                return;
            }

            var formData = {
                id: $("#editCourseId").val(),
                code: courseCode,
                title: courseName,
                name: courseName // Send both title and name for compatibility
            };

            $.ajax({
                type: "PUT",
                url: "../api/admin/courses.php",
                contentType: "application/json",
                data: JSON.stringify(formData),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        $("#editCourseModal").modal("hide");
                        loadCourses();
                        alert("Course updated successfully!");
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Update error:", xhr.responseText);
                    alert("Error updating course. Please try again.");
                }
            });
        });

        // Delete course
        $(document).on("click", ".delete-course", function() {
            if (confirm("Are you sure you want to delete this course?")) {
                var courseId = $(this).data("id");
                $.ajax({
                    type: "DELETE",
                    url: "../api/admin/courses.php",
                    contentType: "application/json",
                    data: JSON.stringify({
                        id: courseId
                    }),
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            loadCourses();
                            alert("Course deleted successfully!");
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function() {
                        alert("Error deleting course. Please try again.");
                    }
                });
            }
        });
    });
</script>