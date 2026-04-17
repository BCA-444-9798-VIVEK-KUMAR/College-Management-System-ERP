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
$pageTitle = "Manage Students";
$basePath = "..";
include_once "../includes/header.php";
?>

<!-- Main content --><main class="main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Manage Students</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                <i class="fas fa-plus"></i> Add New Student
            </button>
        </div>
    </div>

    <!-- Filter Options -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter & Search Students</h5>
        </div>
        <div class="card-body">
            <!-- Search Box -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="searchBox" class="form-label"><i class="fas fa-search me-2"></i>Search Students</label>
                    <input type="text" class="form-control" id="searchBox" placeholder="Search by name, email, roll number, college ID, or phone...">
                    <small class="text-muted">Start typing to filter results instantly</small>
                </div>
            </div>
            
            <hr>
            
            <!-- Advanced Filters -->
            <form id="filterForm" class="row g-3">
                <div class="col-md-2">
                    <label for="filterCourse" class="form-label">Course</label>
                    <select class="form-select" id="filterCourse">
                        <option value="">All Courses</option>
                        <!-- Courses will be loaded dynamically -->
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filterSession" class="form-label">Session</label>
                    <select class="form-select" id="filterSession">
                        <option value="">All Sessions</option>
                        <!-- Sessions will be loaded dynamically -->
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filterBatch" class="form-label">Batch</label>
                    <select class="form-select" id="filterBatch">
                        <option value="">All Batches</option>
                        <!-- Batches will be loaded dynamically -->
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filterStatus" class="form-label">Status</label>
                    <select class="form-select" id="filterStatus">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" id="applyFilter" class="btn btn-primary me-2">
                        <i class="fas fa-check me-1"></i>Apply Filter
                    </button>
                    <button type="button" id="resetFilter" class="btn btn-secondary">
                        <i class="fas fa-redo me-1"></i>Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Students List -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Students List</h5>
            <span class="badge bg-primary" id="studentCount">0 students</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="studentsTable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>College ID</th>
                            <th>Name</th>
                            <th>Roll No</th>
                            <th>Course</th>
                            <th>Session</th>
                            <th>Batch</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="students-list">
                        <!-- Student data will be loaded here via AJAX -->
                        <tr>
                            <td colspan="11" class="text-center">Loading students...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main><!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStudentModalLabel">Add New Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addStudentForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="studentCollegeId" class="form-label">College ID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="studentCollegeId" name="studentCollegeId" placeholder="e.g., 444-9798" required>
                            <small class="text-muted">Unique college identifier</small>
                        </div>
                        <div class="col-md-6">
                            <label for="studentName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="studentName" name="studentName" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="studentRollNo" class="form-label">Roll Number</label>
                            <input type="text" class="form-control" id="studentRollNo" name="studentRollNo" required>
                        </div>
                        <div class="col-md-6">
                            <label for="studentEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="studentEmail" name="studentEmail" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="studentPhone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="studentPhone" name="studentPhone" placeholder="9876543210" maxlength="10" pattern="[0-9]{10}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="studentCourse" class="form-label">Course</label>
                            <select class="form-select" id="studentCourse" name="studentCourse" required>
                                <option value="">Select Course</option>
                                <!-- Courses will be loaded dynamically -->
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="studentSession" class="form-label">Session</label>
                            <select class="form-select" id="studentSession" name="studentSession" required>
                                <option value="">Select Session</option>
                                <!-- Sessions will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="studentBatch" class="form-label">Batch</label>
                            <select class="form-select" id="studentBatch" name="studentBatch" required>
                                <option value="">Select Batch</option>
                                <!-- Batches will be loaded dynamically -->
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="studentPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="studentPassword" name="studentPassword" required>
                        </div>
                        <div class="col-md-6">
                            <label for="studentStatus" class="form-label">Status</label>
                            <select class="form-select" id="studentStatus" name="studentStatus" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="studentAddress" class="form-label">Address</label>
                        <textarea class="form-control" id="studentAddress" name="studentAddress" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveStudentBtn">Save Student</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editStudentModalLabel">Edit Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editStudentForm">
                    <input type="hidden" id="editStudentId" name="studentId">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editStudentCollegeId" class="form-label">College ID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editStudentCollegeId" name="studentCollegeId" placeholder="e.g., 2025001" required>
                            <small class="text-muted">Unique college identifier</small>
                        </div>
                        <div class="col-md-6">
                            <label for="editStudentName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="editStudentName" name="studentName" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editStudentRollNo" class="form-label">Roll Number</label>
                            <input type="text" class="form-control" id="editStudentRollNo" name="studentRollNo" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editStudentEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editStudentEmail" name="studentEmail" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editStudentPhone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="editStudentPhone" name="studentPhone" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editStudentCourse" class="form-label">Course</label>
                            <select class="form-select" id="editStudentCourse" name="studentCourse" required>
                                <option value="">Select Course</option>
                                <!-- Courses will be loaded dynamically -->
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editStudentSession" class="form-label">Session</label>
                            <select class="form-select" id="editStudentSession" name="studentSession" required>
                                <option value="">Select Session</option>
                                <!-- Sessions will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="editStudentBatch" class="form-label">Batch</label>
                            <select class="form-select" id="editStudentBatch" name="studentBatch" required>
                                <option value="">Select Batch</option>
                                <!-- Batches will be loaded dynamically -->
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editStudentPassword" class="form-label">New Password (leave blank to keep current)</label>
                            <input type="password" class="form-control" id="editStudentPassword" name="studentPassword">
                        </div>
                        <div class="col-md-6">
                            <label for="editStudentStatus" class="form-label">Status</label>
                            <select class="form-select" id="editStudentStatus" name="studentStatus" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editStudentAddress" class="form-label">Address</label>
                        <textarea class="form-control" id="editStudentAddress" name="studentAddress" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="updateStudentBtn">Update Student</button>
            </div>
        </div>
    </div>
</div>

<?php include_once "../includes/footer.php"; ?>

<script>
    $(document).ready(function() {
        // Store all students for client-side search
        let allStudents = [];
        
        // Load students, courses, sessions, and batches on page load
        loadStudents();
        loadCoursesSessionsAndBatches();

        // Function to load students
        function loadStudents(filters = {}) {
            $.ajax({
                type: "GET",
                url: "../api/admin/students.php",
                data: filters,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        allStudents = response.data; // Store for search
                        displayStudents(response.data);
                        updateStudentCount(response.data.length);
                    } else {
                        $("#students-list").html('<tr><td colspan="11" class="text-center text-danger">' + response.message + '</td></tr>');
                        updateStudentCount(0);
                    }
                },
                error: function() {
                    $("#students-list").html('<tr><td colspan="11" class="text-center text-danger">Error loading students. Please try again.</td></tr>');
                    updateStudentCount(0);
                }
            });
        }
        
        // Function to update student count badge
        function updateStudentCount(count) {
            $("#studentCount").text(count + " student" + (count !== 1 ? "s" : ""));
        }

        // Function to load courses, sessions, and batches for dropdowns
        function loadCoursesSessionsAndBatches() {
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
        }

        // Function to populate course dropdowns
        function populateCourseDropdowns(courses) {
            var options = '<option value="">Select Course</option>';
            var filterOptions = '<option value="">All Courses</option>';

            $.each(courses, function(index, course) {
                options += '<option value="' + course.id + '">' + course.name + '</option>';
                filterOptions += '<option value="' + course.id + '">' + course.name + '</option>';
            });

            $("#studentCourse, #editStudentCourse").html(options);
            $("#filterCourse").html(filterOptions);
        }

        // Function to populate session dropdowns
        function populateSessionDropdowns(sessions) {
            var options = '<option value="">Select Session</option>';
            var filterOptions = '<option value="">All Sessions</option>';

            $.each(sessions, function(index, session) {
                options += '<option value="' + session.id + '">' + session.title + '</option>';
                filterOptions += '<option value="' + session.id + '">' + session.title + '</option>';
            });

            $("#studentSession, #editStudentSession").html(options);
            $("#filterSession").html(filterOptions);
        }

        // Function to populate batch dropdowns
        function populateBatchDropdowns(batches) {
            var options = '<option value="">Select Batch</option>';
            var filterOptions = '<option value="">All Batches</option>';

            $.each(batches, function(index, batch) {
                options += '<option value="' + batch.id + '">' + batch.name + '</option>';
                filterOptions += '<option value="' + batch.id + '">' + batch.name + '</option>';
            });

            $("#studentBatch, #editStudentBatch").html(options);
            $("#filterBatch").html(filterOptions);
        }

        // Function to display students
        function displayStudents(students) {
            var html = '';
            if (students.length > 0) {
                $.each(students, function(index, student) {
                    var statusClass = student.status === 'active' ? 'badge bg-success' : 'badge bg-secondary';

                    html += '<tr>';
                    html += '<td>' + student.id + '</td>';
                    html += '<td><strong class="badge bg-primary">' + (student.college_id || 'N/A') + '</strong></td>';
                    html += '<td>' + student.name + '</td>';
                    html += '<td>' + student.roll_no + '</td>';
                    html += '<td>' + (student.course_name || 'N/A') + '</td>';
                    html += '<td>' + (student.session_name || 'N/A') + '</td>';
                    html += '<td>' + (student.batch_name || 'N/A') + '</td>';
                    html += '<td>' + student.email + '</td>';
                    html += '<td>' + (student.phone || 'N/A') + '</td>';
                    html += '<td><span class="' + statusClass + '">' + student.status.charAt(0).toUpperCase() + student.status.slice(1) + '</span></td>';
                    html += '<td>';
                    html += '<button class="btn btn-sm btn-primary edit-student me-1" data-id="' + student.id + '"><i class="fas fa-edit"></i></button>';
                    html += '<button class="btn btn-sm btn-danger delete-student" data-id="' + student.id + '"><i class="fas fa-trash"></i></button>';
                    html += '</td>';
                    html += '</tr>';
                });
            } else {
                html = '<tr><td colspan="11" class="text-center">No students found</td></tr>';
            }
            $("#students-list").html(html);
        }

        // Apply filter
        $("#applyFilter").on("click", function() {
            var filters = {
                course_id: $("#filterCourse").val(),
                session_id: $("#filterSession").val(),
                batch_id: $("#filterBatch").val(),
                status: $("#filterStatus").val()
            };
            loadStudents(filters);
        });

        // Reset filter
        $("#resetFilter").on("click", function() {
            $("#filterForm")[0].reset();
            $("#searchBox").val('');
            loadStudents();
        });
        
        // Real-time search functionality
        $("#searchBox").on("keyup", function() {
            var searchTerm = $(this).val().toLowerCase().trim();
            
            if (searchTerm === '') {
                // If search is empty, show all students
                displayStudents(allStudents);
                updateStudentCount(allStudents.length);
            } else {
                // Filter students based on search term
                var filteredStudents = allStudents.filter(function(student) {
                    return (
                        (student.name && student.name.toLowerCase().includes(searchTerm)) ||
                        (student.email && student.email.toLowerCase().includes(searchTerm)) ||
                        (student.roll_no && student.roll_no.toLowerCase().includes(searchTerm)) ||
                        (student.college_id && student.college_id.toLowerCase().includes(searchTerm)) ||
                        (student.phone && student.phone.toLowerCase().includes(searchTerm)) ||
                        (student.course_name && student.course_name.toLowerCase().includes(searchTerm)) ||
                        (student.batch_name && student.batch_name.toLowerCase().includes(searchTerm)) ||
                        (student.session_name && student.session_name.toLowerCase().includes(searchTerm))
                    );
                });
                
                displayStudents(filteredStudents);
                updateStudentCount(filteredStudents.length);
            }
        });

        // Save new student
        $("#saveStudentBtn").on("click", function() {
            var formData = $("#addStudentForm").serialize();
            $.ajax({
                type: "POST",
                url: "../api/admin/students.php",
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $("#addStudentModal").modal("hide");
                        $("#addStudentForm")[0].reset();
                        $("#searchBox").val(''); // Clear search
                        loadStudents();
                        
                        // Show better notification with Bootstrap alert
                        var alertClass = 'alert-success';
                        var alertIcon = '<i class="fas fa-check-circle me-2"></i>';
                        var alertMessage = '<strong>Success!</strong> ' + response.message;
                        
                        if (response.email_sent) {
                            alertMessage += '<br><i class="fas fa-envelope-circle-check me-2"></i>Welcome email has been sent successfully!';
                        } else if (response.email_error) {
                            alertClass = 'alert-warning';
                            alertIcon = '<i class="fas fa-exclamation-triangle me-2"></i>';
                            alertMessage += '<br><i class="fas fa-envelope-open-text me-2"></i><small>Note: Welcome email could not be sent - ' + response.email_error + '</small>';
                        }
                        
                        var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                            alertIcon + alertMessage +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                            '</div>';
                        
                        // Insert alert at the top of main content
                        $('main.main-content').prepend(alertHtml);
                        
                        // Auto-dismiss after 5 seconds
                        setTimeout(function() {
                            $('.alert').fadeOut('slow', function() {
                                $(this).remove();
                            });
                        }, 5000);
                    } else {
                        var errorAlert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                            '<i class="fas fa-times-circle me-2"></i><strong>Error!</strong> ' + response.message +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                            '</div>';
                        $('main.main-content').prepend(errorAlert);
                    }
                },
                error: function() {
                    var errorAlert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                        '<i class="fas fa-times-circle me-2"></i><strong>Error!</strong> Failed to add student. Please try again.' +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>';
                    $('main.main-content').prepend(errorAlert);
                }
            });
        });

        // Edit student - populate modal
        $(document).on("click", ".edit-student", function() {
            var studentId = $(this).data("id");
            $.ajax({
                type: "GET",
                url: "../api/admin/students.php?id=" + studentId,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        var student = response.data;
                        $("#editStudentId").val(student.id);
                        $("#editStudentCollegeId").val(student.college_id || '');
                        $("#editStudentName").val(student.name);
                        $("#editStudentRollNo").val(student.roll_no);
                        $("#editStudentEmail").val(student.email);
                        $("#editStudentPhone").val(student.phone);
                        $("#editStudentCourse").val(student.course_id);
                        $("#editStudentSession").val(student.session_id);
                        $("#editStudentBatch").val(student.batch_id);
                        $("#editStudentStatus").val(student.status);
                        $("#editStudentAddress").val(student.address);
                        $("#editStudentModal").modal("show");
                    } else {
                        var errorAlert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">' +
                            '<i class="fas fa-exclamation-triangle me-2"></i><strong>Not Found!</strong> ' + response.message +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                            '</div>';
                        $('main.main-content').prepend(errorAlert);
                    }
                },
                error: function() {
                    var errorAlert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                        '<i class="fas fa-times-circle me-2"></i><strong>Error!</strong> Failed to load student details.' +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>';
                    $('main.main-content').prepend(errorAlert);
                }
            });
        });

        // Update student
        $("#updateStudentBtn").on("click", function() {
            var formData = {
                id: $("#editStudentId").val(),
                college_id: $("#editStudentCollegeId").val(),
                name: $("#editStudentName").val(),
                email: $("#editStudentEmail").val(),
                roll_no: $("#editStudentRollNo").val(),
                phone: $("#editStudentPhone").val(),
                course_id: $("#editStudentCourse").val(),
                session_id: $("#editStudentSession").val(),
                batch_id: $("#editStudentBatch").val(),
                status: $("#editStudentStatus").val(),
                address: $("#editStudentAddress").val()
            };

            // Only include password if it's not empty
            var password = $("#editStudentPassword").val();
            if (password && password.trim() !== '') {
                formData.password = password;
            }

            $.ajax({
                type: "PUT",
                url: "../api/admin/students.php",
                contentType: "application/json",
                data: JSON.stringify(formData),
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        $("#editStudentModal").modal("hide");
                        loadStudents();
                        
                        var successAlert = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                            '<i class="fas fa-check-circle me-2"></i><strong>Updated!</strong> Student updated successfully!' +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                            '</div>';
                        $('main.main-content').prepend(successAlert);
                        
                        setTimeout(function() {
                            $('.alert').fadeOut('slow', function() { $(this).remove(); });
                        }, 3000);
                    } else {
                        var errorAlert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                            '<i class="fas fa-times-circle me-2"></i><strong>Error!</strong> ' + response.message +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                            '</div>';
                        $('main.main-content').prepend(errorAlert);
                    }
                },
                error: function() {
                    var errorAlert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                        '<i class="fas fa-times-circle me-2"></i><strong>Error!</strong> Failed to update student. Please try again.' +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                        '</div>';
                    $('main.main-content').prepend(errorAlert);
                }
            });
        });

        // Delete student
        $(document).on("click", ".delete-student", function() {
            var studentId = $(this).data("id");
            var studentName = $(this).closest('tr').find('td:nth-child(3)').text();
            
            if (confirm("Are you sure you want to delete student: " + studentName + "?\n\nThis action cannot be undone.")) {
                $.ajax({
                    type: "DELETE",
                    url: "../api/admin/students.php",
                    contentType: "application/json",
                    data: JSON.stringify({
                        id: studentId
                    }),
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            loadStudents();
                            
                            var successAlert = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                                '<i class="fas fa-check-circle me-2"></i><strong>Deleted!</strong> Student has been removed successfully.' +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                '</div>';
                            $('main.main-content').prepend(successAlert);
                            
                            setTimeout(function() {
                                $('.alert').fadeOut('slow', function() { $(this).remove(); });
                            }, 3000);
                        } else {
                            var errorAlert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                                '<i class="fas fa-times-circle me-2"></i><strong>Error!</strong> ' + response.message +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                '</div>';
                            $('main.main-content').prepend(errorAlert);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Delete error:", xhr.responseText);
                        var errorAlert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                            '<i class="fas fa-times-circle me-2"></i><strong>Error!</strong> Failed to delete student. Please try again.' +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                            '</div>';
                        $('main.main-content').prepend(errorAlert);
                    }
                });
            }
        });
    });
</script>