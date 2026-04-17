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

// Check if user is logged in and is teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    // Redirect to login page
    header("Location: ../auth/login.php");
    exit;
}

// Set page title and include header
$pageTitle = "Edit Attendance";
$basePath = "..";
include_once "../includes/header.php";
?>

<main class="main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-edit me-2"></i> Edit Attendance</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="refresh-btn">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
    </div>

    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> You can edit attendance records from the last 15 days only.
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter"></i> Select Class to Edit
        </div>
        <div class="card-body">
            <form id="filter-form">
                <div class="row">
                    <div class="col-md-2">
                        <label for="course-select" class="form-label">Course</label>
                        <select class="form-select" id="course-select" required>
                            <option value="">Select Course</option>
                            <!-- Courses will be loaded via AJAX -->
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="batch-select" class="form-label">Batch (Session)</label>
                        <select class="form-select" id="batch-select" required>
                            <option value="">Select Batch</option>
                            <!-- Batches will be loaded via AJAX -->
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="subject-select" class="form-label">Subject</label>
                        <select class="form-select" id="subject-select" required>
                            <option value="">Select Subject</option>
                            <!-- Subjects will be loaded via AJAX -->
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date-select" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date-select"
                            max="<?php echo date('Y-m-d'); ?>"
                            min="<?php echo date('Y-m-d', strtotime('-15 days')); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary d-block w-100">
                            <i class="fas fa-search"></i> Load Attendance
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Attendance Edit Form -->
    <div class="card mb-4 d-none" id="attendance-card">
        <div class="card-header">
            <i class="fas fa-edit"></i> Edit Attendance - <span id="class-info"></span>
        </div>
        <div class="card-body">
            <div id="attendance-alert" class="alert d-none"></div>

            <form id="attendance-form">
                <input type="hidden" id="subject-id" name="subject_id">
                <input type="hidden" id="batch-id" name="batch_id">
                <input type="hidden" id="date" name="date">

                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="students-table" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Roll No</th>
                                <th>Name</th>
                                <th>Current Status</th>
                                <th>New Status</th>
                            </tr>
                        </thead>
                        <tbody id="students-body">
                            <!-- Students data will be loaded here via AJAX -->
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 text-center">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <button type="button" class="btn btn-secondary" id="cancel-btn">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>
</main>

<script>
    $(document).ready(function() {
        // Load teacher's courses
        loadCourses();

        // Load courses for the teacher
        function loadCourses() {
            $.ajax({
                type: "GET",
                url: "../api/teacher/teacher_batches.php",
                dataType: "json",
                success: function(response) {
                    console.log("Batches response:", response);
                    if (response.success) {
                        if (response.data && response.data.length > 0) {
                            // Extract unique courses
                            var courses = {};
                            $.each(response.data, function(i, batch) {
                                if (!courses[batch.course]) {
                                    courses[batch.course] = batch.course;
                                }
                            });

                            var options = '<option value="">Select Course</option>';
                            $.each(courses, function(courseName, courseValue) {
                                options += '<option value="' + courseName + '">' + courseName + '</option>';
                            });
                            $("#course-select").html(options);

                            // Store all batches data for filtering
                            $("#course-select").data('allBatches', response.data);
                        } else {
                            $("#course-select").html('<option value="">No courses assigned</option>');
                            console.warn("No batches found for teacher");
                        }
                    } else {
                        console.error("Error loading courses:", response.error);
                        alert("Error loading courses: " + (response.error || "Unknown error"));
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    console.error("Response Text:", xhr.responseText);
                    alert("Failed to load courses. Please refresh the page.");
                }
            });
        }

        // When course is selected, load batches for that course
        $("#course-select").change(function() {
            var selectedCourse = $(this).val();
            $("#batch-select").html('<option value="">Select Batch</option>');
            $("#subject-select").html('<option value="">Select Subject</option>');

            if (selectedCourse) {
                var allBatches = $(this).data('allBatches');
                var filteredBatches = allBatches.filter(function(batch) {
                    return batch.course === selectedCourse;
                });

                if (filteredBatches.length > 0) {
                    var options = '<option value="">Select Batch</option>';
                    $.each(filteredBatches, function(i, batch) {
                        options += '<option value="' + batch.batch_id + '">' + batch.batch_name + '</option>';
                    });
                    $("#batch-select").html(options);
                } else {
                    $("#batch-select").html('<option value="">No batches found</option>');
                }
            }
        });

        // When batch is selected, load subjects for that batch
        $("#batch-select").change(function() {
            var batchId = $(this).val();
            if (batchId) {
                loadSubjects(batchId);
            } else {
                $("#subject-select").html('<option value="">Select Subject</option>');
            }
        });

        // Load subjects for selected batch
        function loadSubjects(batchId) {
            $.ajax({
                type: "GET",
                url: "../api/teacher/batch_subjects.php",
                data: {
                    batch_id: batchId
                },
                dataType: "json",
                success: function(response) {
                    console.log("Subjects response:", response);
                    if (response.success) {
                        if (response.data && response.data.length > 0) {
                            var options = '<option value="">Select Subject</option>';
                            $.each(response.data, function(i, subject) {
                                options += '<option value="' + subject.subject_id + '">' + subject.subject_name + '</option>';
                            });
                            $("#subject-select").html(options);
                        } else {
                            $("#subject-select").html('<option value="">No subjects assigned</option>');
                            console.warn("No subjects found for batch");
                        }
                    } else {
                        console.error("Error loading subjects:", response.error);
                        alert("Error loading subjects: " + (response.error || "Unknown error"));
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    console.error("Response Text:", xhr.responseText);
                    alert("Failed to load subjects. Please try again.");
                }
            });
        }

        // Handle filter form submission
        $("#filter-form").submit(function(e) {
            e.preventDefault();

            var batchId = $("#batch-select").val();
            var subjectId = $("#subject-select").val();
            var date = $("#date-select").val();

            if (!batchId || !subjectId || !date) {
                alert("Please select all fields");
                return;
            }

            // Set hidden field values
            $("#subject-id").val(subjectId);
            $("#batch-id").val(batchId);
            $("#date").val(date);

            // Update class info display
            var batchName = $("#batch-select option:selected").text();
            var subjectName = $("#subject-select option:selected").text();
            var displayDate = new Date(date).toLocaleDateString('en-GB');
            $("#class-info").text(subjectName + " - " + batchName + " (" + displayDate + ")");

            // Load attendance
            loadAttendance(batchId, subjectId, date);
        });

        // Load attendance data
        function loadAttendance(batchId, subjectId, date) {
            // Show loading message
            $("#students-body").html("<tr><td colspan='4' class='text-center'><div class='spinner-border spinner-border-sm' role='status'><span class='visually-hidden'>Loading...</span></div> Loading students...</td></tr>");
            $("#attendance-card").removeClass("d-none");

            $.ajax({
                type: "GET",
                url: "../api/teacher/batch_students.php",
                data: {
                    batch_id: batchId,
                    subject_id: subjectId,
                    date: date
                },
                dataType: "json",
                success: function(response) {
                    console.log("Students response:", response);
                    if (response.success) {
                        var studentsHtml = "";
                        if (response.data.length > 0) {
                            $.each(response.data, function(index, student) {
                                var currentStatus = student.status || 'not_marked';
                                var statusBadge = '';

                                if (currentStatus === 'present') {
                                    statusBadge = '<span class="badge bg-success">Present</span>';
                                } else if (currentStatus === 'absent') {
                                    statusBadge = '<span class="badge bg-danger">Absent</span>';
                                } else {
                                    statusBadge = '<span class="badge bg-secondary">Not Marked</span>';
                                }

                                studentsHtml += "<tr>";
                                studentsHtml += "<td>" + student.roll_no + "</td>";
                                studentsHtml += "<td>" + student.name + "</td>";
                                studentsHtml += "<td>" + statusBadge + "</td>";
                                studentsHtml += "<td>";
                                studentsHtml += "<input type='hidden' name='student_ids[]' value='" + student.id + "'>";

                                // Status radio buttons
                                studentsHtml += "<div class='btn-group' role='group'>";
                                studentsHtml += "<input type='radio' class='btn-check' name='status_" + student.id + "' id='present_" + student.id + "' value='present' " + (currentStatus === 'present' ? 'checked' : '') + ">";
                                studentsHtml += "<label class='btn btn-outline-success btn-sm' for='present_" + student.id + "'>Present</label>";

                                studentsHtml += "<input type='radio' class='btn-check' name='status_" + student.id + "' id='absent_" + student.id + "' value='absent' " + (currentStatus === 'absent' ? 'checked' : '') + ">";
                                studentsHtml += "<label class='btn btn-outline-danger btn-sm' for='absent_" + student.id + "'>Absent</label>";
                                studentsHtml += "</div>";

                                studentsHtml += "</td>";
                                studentsHtml += "</tr>";
                            });

                            $("#students-body").html(studentsHtml);
                        } else {
                            $("#students-body").html("<tr><td colspan='4' class='text-center text-danger'>No students found in this batch</td></tr>");
                            console.warn("No students found in batch");
                        }
                    } else {
                        $("#students-body").html("<tr><td colspan='4' class='text-center text-danger'>Error: " + (response.error || "Failed to load students") + "</td></tr>");
                        console.error("Error loading attendance:", response.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    console.error("Response Text:", xhr.responseText);
                    $("#students-body").html("<tr><td colspan='4' class='text-center text-danger'>Failed to load students. Please check console for details.</td></tr>");
                }
            });
        }

        // Handle attendance form submission
        $("#attendance-form").submit(function(e) {
            e.preventDefault();

            // Prepare attendance data
            var attendanceData = [];
            $("input[name='student_ids[]']").each(function() {
                var studentId = $(this).val();
                var status = $("input[name='status_" + studentId + "']:checked").val();

                if (status) {
                    attendanceData.push({
                        student_id: studentId,
                        status: status
                    });
                }
            });

            if (attendanceData.length === 0) {
                alert("Please mark attendance for at least one student");
                return;
            }

            // Send AJAX request
            $.ajax({
                type: "POST",
                url: "../api/teacher/save_attendance.php",
                data: {
                    subject_id: $("#subject-id").val(),
                    batch_id: $("#batch-id").val(),
                    date: $("#date").val(),
                    attendance: JSON.stringify(attendanceData)
                },
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        $("#attendance-alert").removeClass("d-none alert-danger").addClass("alert-success").text("Attendance updated successfully!");

                        // Scroll to top
                        $('html, body').animate({
                            scrollTop: 0
                        }, 'fast');

                        // Reload the attendance after 2 seconds
                        setTimeout(function() {
                            $("#filter-form").submit();
                        }, 2000);
                    } else {
                        // Show error message
                        $("#attendance-alert").removeClass("d-none alert-success").addClass("alert-danger").text(response.error || "Error updating attendance");
                        $('html, body').animate({
                            scrollTop: $("#attendance-alert").offset().top - 100
                        }, 'fast');
                    }
                },
                error: function(xhr, status, error) {
                    // Show error message
                    $("#attendance-alert").removeClass("d-none alert-success").addClass("alert-danger").text("An error occurred. Please try again.");
                    console.error("AJAX Error:", error);
                }
            });
        });

        // Cancel button
        $("#cancel-btn").click(function() {
            $("#attendance-card").addClass("d-none");
            $("#filter-form")[0].reset();
        });

        // Refresh button
        $("#refresh-btn").click(function() {
            location.reload();
        });
    });
</script>

<?php
// Include footer
include_once "../includes/footer.php";
?>