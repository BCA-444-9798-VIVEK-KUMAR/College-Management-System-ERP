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

// Check if required parameters are provided
if (!isset($_GET['subject_id']) || !isset($_GET['batch_id']) || !isset($_GET['date'])) {
    // Redirect to dashboard
    header("Location: dashboard.php");
    exit;
}

// Set page title and include header
$pageTitle = "Take Attendance";
$basePath = "..";
include_once "../includes/header.php";

// Get parameters
$subject_id = $_GET['subject_id'];
$batch_id = $_GET['batch_id'];
$date = $_GET['date'];
?>

<main class="main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-clipboard-check me-2"></i> Take Attendance</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="dashboard.php" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Class Information -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i> Class Information
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Subject:</strong> <span id="subject-name">Loading...</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Batch:</strong> <span id="batch-name">Loading...</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Course:</strong> <span id="course-name">Loading...</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Date:</strong> <span id="attendance-date"><?php echo date('d-m-Y', strtotime($date)); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Form -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-clipboard-check"></i> Attendance
        </div>
        <div class="card-body">
            <div id="attendance-alert" class="alert d-none"></div>

            <form id="attendance-form">
                <input type="hidden" id="subject-id" name="subject_id" value="<?php echo $subject_id; ?>">
                <input type="hidden" id="batch-id" name="batch_id" value="<?php echo $batch_id; ?>">
                <input type="hidden" id="date" name="date" value="<?php echo $date; ?>">

                <div class="table-responsive">
                    <table class="table table-bordered" id="students-table" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Roll No</th>
                                <th>Name</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="students-body">
                            <!-- Students data will be loaded here via AJAX -->
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 text-center">
                    <button type="submit" class="btn btn-primary">Save Attendance</button>
                </div>
            </form>
        </div>
    </div>
</main>
</div>
</div>

<script>
    $(document).ready(function() {
        // Load class information and students
        loadClassInfo();

        // Function to load class information
        function loadClassInfo() {
            $.ajax({
                type: "GET",
                url: "../api/teacher/class_info.php",
                data: {
                    subject_id: $("#subject-id").val(),
                    batch_id: $("#batch-id").val()
                },
                dataType: "json",
                success: function(response) {
                    console.log("Class info response:", response);
                    if (response.success) {
                        // Update class information
                        $("#subject-name").text(response.data.subject);
                        $("#batch-name").text(response.data.batch);
                        $("#course-name").text(response.data.course);

                        // Load students
                        loadStudents();
                    } else {
                        console.error("Error loading class information:", response.error);
                        alert("Error loading class information: " + response.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    console.error("Response Text:", xhr.responseText);
                    alert("Error loading class information. Check console for details.");
                }
            });
        }

        // Function to load students
        function loadStudents() {
            $.ajax({
                type: "GET",
                url: "../api/teacher/batch_students.php",
                data: {
                    batch_id: $("#batch-id").val(),
                    date: $("#date").val(),
                    subject_id: $("#subject-id").val()
                },
                dataType: "json",
                success: function(response) {
                    console.log("Students response:", response);
                    if (response.success) {
                        // Update students table
                        var studentsHtml = "";
                        if (response.data.length > 0) {
                            $.each(response.data, function(index, student) {
                                studentsHtml += "<tr>";
                                studentsHtml += "<td>" + student.roll_no + "</td>";
                                studentsHtml += "<td>" + student.name + "</td>";
                                studentsHtml += "<td>";
                                studentsHtml += "<input type='hidden' name='student_ids[]' value='" + student.id + "'>";

                                // Status radio buttons - Default to Absent if no status exists
                                var defaultChecked = !student.status || student.status === '';

                                studentsHtml += "<div class='form-check form-check-inline'>";
                                studentsHtml += "<input class='form-check-input' type='radio' name='status_" + student.id + "' id='present_" + student.id + "' value='present' " + (student.status === 'present' ? 'checked' : '') + ">";
                                studentsHtml += "<label class='form-check-label' for='present_" + student.id + "'>Present</label>";
                                studentsHtml += "</div>";

                                studentsHtml += "<div class='form-check form-check-inline'>";
                                studentsHtml += "<input class='form-check-input' type='radio' name='status_" + student.id + "' id='absent_" + student.id + "' value='absent' " + (student.status === 'absent' || defaultChecked ? 'checked' : '') + ">";
                                studentsHtml += "<label class='form-check-label' for='absent_" + student.id + "'>Absent</label>";
                                studentsHtml += "</div>";

                                studentsHtml += "</td>";
                                studentsHtml += "</tr>";
                            });
                        } else {
                            studentsHtml = "<tr><td colspan='3' class='text-center'>No students found in this batch</td></tr>";
                        }
                        $("#students-body").html(studentsHtml);
                    } else {
                        console.error("Error loading students:", response.error);
                        $("#students-body").html("<tr><td colspan='3' class='text-center text-danger'>Error: " + response.error + "</td></tr>");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    console.error("Response Text:", xhr.responseText);
                    $("#students-body").html("<tr><td colspan='3' class='text-center text-danger'>Error loading students. Check console for details.</td></tr>");
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

                attendanceData.push({
                    student_id: studentId,
                    status: status
                });
            });

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
                        $("#attendance-alert").removeClass("d-none alert-danger").addClass("alert-success").text(response.message);

                        // Redirect to dashboard after 2 seconds
                        setTimeout(function() {
                            window.location.href = "dashboard.php";
                        }, 2000);
                    } else {
                        // Show error message
                        $("#attendance-alert").removeClass("d-none alert-success").addClass("alert-danger").text(response.error);
                    }
                },
                error: function(xhr, status, error) {
                    // Show error message
                    $("#attendance-alert").removeClass("d-none alert-success").addClass("alert-danger").text("An error occurred. Please try again.");
                }
            });
        });
    });
</script>

<?php
// Include footer
include_once "../includes/footer.php";
?>