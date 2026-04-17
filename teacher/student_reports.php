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
$pageTitle = "Student Attendance Reports";
$basePath = "..";
include_once "../includes/header.php";
?>

<!-- SweetAlert2 CSS & JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<main class="main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-user-graduate me-2"></i> Student Attendance Reports</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-danger" id="print-btn" disabled>
                    <i class="fas fa-file-pdf"></i> Print as PDF
                </button>
                <button type="button" class="btn btn-sm btn-success" id="export-btn" disabled>
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
                <button type="button" class="btn btn-sm btn-primary" id="email-btn" disabled>
                    <i class="fas fa-envelope"></i> Email Report
                </button>
            </div>
        </div>

        <!-- Email Options -->
        <div class="card mb-3" id="email-options-card" style="display: none;">
            <div class="card-body">
                <div class="alert alert-info mb-2">
                    <i class="fas fa-info-circle"></i> <strong>Email Settings</strong>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="sendToRealStudent" checked>
                    <label class="form-check-label" for="sendToRealStudent">
                        <strong><i class="fas fa-user-check"></i> Send to Student's Real Email Address</strong>
                    </label>
                    <small class="d-block text-muted">
                        When unchecked, email will be sent to the configured test email address for safety.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Options -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter"></i> Filter Options
        </div>
        <div class="card-body">
            <form id="filter-form" class="row g-3">
                <div class="col-md-2">
                    <label for="course-filter" class="form-label">Course</label>
                    <select class="form-select" id="course-filter" required>
                        <option value="">Select Course</option>
                        <!-- Courses will be loaded here via AJAX -->
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="batch-filter" class="form-label">Batch (Session)</label>
                    <select class="form-select" id="batch-filter" required>
                        <option value="">Select Batch</option>
                        <!-- Batches will be loaded here via AJAX -->
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="student-filter" class="form-label">Student</label>
                    <select class="form-select" id="student-filter" required>
                        <option value="">Select Student</option>
                        <!-- Students will be loaded here via AJAX -->
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="month-filter" class="form-label">Month</label>
                    <select class="form-select" id="month-filter">
                        <option value="">All Months</option>
                        <option value="01">January</option>
                        <option value="02">February</option>
                        <option value="03">March</option>
                        <option value="04">April</option>
                        <option value="05">May</option>
                        <option value="06">June</option>
                        <option value="07">July</option>
                        <option value="08">August</option>
                        <option value="09">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="year-filter" class="form-label">Year</label>
                    <select class="form-select" id="year-filter">
                        <option value="">All Years</option>
                        <?php
                        $currentYear = date('Y');
                        for ($i = $currentYear; $i >= $currentYear - 5; $i--) {
                            echo "<option value='$i'" . ($i == $currentYear ? " selected" : "") . ">$i</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-chart-line"></i> Generate Report
                    </button>
                    <button type="button" class="btn btn-secondary" id="reset-btn">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Content (initially hidden) -->
    <div id="report-content" class="d-none">
        <!-- Student Information -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle"></i> Student Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <strong>Name:</strong> <span id="student-name"></span>
                    </div>
                    <div class="col-md-2">
                        <strong>Roll No:</strong> <span id="student-roll"></span>
                    </div>
                    <div class="col-md-2">
                        <strong>Batch:</strong> <span id="student-batch"></span>
                    </div>
                    <div class="col-md-2">
                        <strong>Course:</strong> <span id="student-course"></span>
                    </div>
                    <div class="col-md-2">
                        <strong>Period:</strong> <span id="report-period"></span>
                    </div>
                    <div class="col-md-2">
                        <strong>Generated:</strong> <span id="report-date"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overall Attendance Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-pie"></i> Overall Attendance Summary
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <canvas id="overall-chart" width="100%" height="50"></canvas>
                    </div>
                    <div class="col-md-6">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="summary-table" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>Count</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="table-success">
                                        <td>Present</td>
                                        <td id="present-count">0</td>
                                        <td id="present-percentage">0%</td>
                                    </tr>
                                    <tr class="table-danger">
                                        <td>Absent</td>
                                        <td id="absent-count">0</td>
                                        <td id="absent-percentage">0%</td>
                                    </tr>
                                    <tr class="table-primary">
                                        <td><strong>Total</strong></td>
                                        <td id="total-count">0</td>
                                        <td>100%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subject-wise Attendance -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-bar"></i> Subject-wise Attendance
            </div>
            <div class="card-body">
                <canvas id="subject-chart" width="100%" height="40"></canvas>
            </div>
        </div>

        <!-- Detailed Attendance Records -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-table"></i> Detailed Attendance Records
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="attendance-table" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Subject</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="attendance-body">
                            <!-- Attendance data will be loaded here via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- No Data Message (initially shown) -->
    <div id="no-data-message" class="card mb-4">
        <div class="card-body text-center">
            <i class="fas fa-user-graduate fa-3x mb-3 text-muted"></i>
            <h4>No Student Report Data</h4>
            <p>Please select a batch, student, and date range to generate a report.</p>
        </div>
    </div>
</main>
</div>
</div>

<script>
    $(document).ready(function() {
        let allBatchesData = [];

        // Load batches for filter
        loadBatches();

        // Handle course change to filter batches
        $("#course-filter").change(function() {
            filterBatchesByCourse($(this).val());
            // Reset student filter
            $("#student-filter").html("<option value=''>Select Student</option>");
        });

        // Handle batch change to load students
        $("#batch-filter").change(function() {
            loadStudents($(this).val());
        });

        // Handle month/year change
        $("#month-filter, #year-filter").change(function() {
            // No auto-reload, user must click Generate Report
        });

        // Handle reset button
        $("#reset-btn").click(function() {
            $("#filter-form")[0].reset();
            $("#course-filter").val("");
            $("#batch-filter").html("<option value=''>Select Batch</option>");
            $("#student-filter").html("<option value=''>Select Student</option>");
            $("#year-filter").val("<?php echo date('Y'); ?>");
            $("#report-content").addClass("d-none");
            $("#no-data-message").removeClass("d-none");
            $("#print-btn, #export-btn").prop('disabled', true);
        });

        // Handle filter form submission
        $("#filter-form").submit(function(e) {
            e.preventDefault();

            // Validate form
            if (!$(this)[0].checkValidity()) {
                e.stopPropagation();
                $(this).addClass('was-validated');
                return;
            }

            // Generate report
            generateReport();
        });

        // Handle print button click
        $("#print-btn").click(function() {
            if ($(this).prop('disabled')) return;
            printReport();
        });

        // Handle export button click
        $("#export-btn").click(function() {
            if ($(this).prop('disabled')) return;
            exportToExcel();
        });

        // Handle email button click
        $("#email-btn").click(function() {
            if ($(this).prop('disabled')) return;
            sendEmailReport();
        });

        // Function to load batches
        function loadBatches() {
            $.ajax({
                type: "GET",
                url: "../api/teacher/teacher_batches.php",
                dataType: "json",
                success: function(response) {
                    console.log("Batches response:", response);
                    if (response.success) {
                        allBatchesData = response.data;
                        loadCourses();
                    } else {
                        console.error("Error loading batches:", response.error);
                        alert("Error loading batches: " + response.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    console.error("Response Text:", xhr.responseText);
                    alert("Failed to load batches. Please try again.");
                }
            });
        }

        // Function to extract and load unique courses
        function loadCourses() {
            const uniqueCourses = {};
            allBatchesData.forEach(function(batch) {
                if (batch.course && !uniqueCourses[batch.course]) {
                    uniqueCourses[batch.course] = true;
                }
            });

            let coursesHtml = "<option value=''>Select Course</option>";
            Object.keys(uniqueCourses).sort().forEach(function(course) {
                coursesHtml += "<option value='" + course + "'>" + course + "</option>";
            });

            $("#course-filter").html(coursesHtml);

            // Store all batches data for filtering
            $("#course-filter").data('allBatches', allBatchesData);
        }

        // Function to filter batches by course
        function filterBatchesByCourse(courseName) {
            if (!courseName) {
                $("#batch-filter").html("<option value=''>Select Batch</option>");
                return;
            }

            const filteredBatches = allBatchesData.filter(function(batch) {
                return batch.course === courseName;
            });

            let batchesHtml = "<option value=''>Select Batch</option>";
            filteredBatches.forEach(function(batch) {
                batchesHtml += "<option value='" + batch.batch_id + "'>" + batch.batch_name + "</option>";
            });

            $("#batch-filter").html(batchesHtml);
        }

        // Function to load students
        function loadStudents(batchId) {
            if (!batchId) {
                $("#student-filter").html("<option value=''>Select Student</option>");
                return;
            }

            $.ajax({
                type: "GET",
                url: "../api/teacher/batch_students.php",
                data: {
                    batch_id: batchId,
                    date: new Date().toISOString().split('T')[0] // Today's date for API compatibility
                },
                dataType: "json",
                success: function(response) {
                    console.log("Students response:", response);
                    if (response.success) {
                        // Update student filter dropdown
                        var studentsHtml = "<option value=''>Select Student</option>";
                        $.each(response.data, function(index, student) {
                            studentsHtml += "<option value='" + student.id + "'>" +
                                student.roll_no + " - " + student.name + "</option>";
                        });
                        $("#student-filter").html(studentsHtml);
                    } else {
                        console.error("Error loading students:", response.error);
                        alert("Error loading students: " + response.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    console.error("Response Text:", xhr.responseText);
                    alert("Failed to load students. Please try again.");
                }
            });
        }

        // Function to generate report
        function generateReport() {
            var studentId = $("#student-filter").val();
            var month = $("#month-filter").val();
            var year = $("#year-filter").val();

            // Calculate from and to dates based on month/year selection
            var fromDate, toDate;

            if (month && year) {
                // Specific month and year
                fromDate = year + "-" + month + "-01";
                var lastDay = new Date(year, parseInt(month), 0).getDate();
                toDate = year + "-" + month + "-" + ("0" + lastDay).slice(-2);
            } else if (year && !month) {
                // Entire year
                fromDate = year + "-01-01";
                toDate = year + "-12-31";
            } else if (month && !year) {
                // Current year, specific month
                var currentYear = new Date().getFullYear();
                fromDate = currentYear + "-" + month + "-01";
                var lastDay = new Date(currentYear, parseInt(month), 0).getDate();
                toDate = currentYear + "-" + month + "-" + ("0" + lastDay).slice(-2);
            } else {
                // Last 30 days if nothing selected
                toDate = new Date().toISOString().split('T')[0];
                var thirtyDaysAgo = new Date();
                thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
                fromDate = thirtyDaysAgo.toISOString().split('T')[0];
            }

            $.ajax({
                type: "GET",
                url: "../api/teacher/student_attendance_report.php",
                data: {
                    student_id: studentId,
                    from_date: fromDate,
                    to_date: toDate
                },
                dataType: "json",
                beforeSend: function() {
                    $("#filter-form button[type='submit']").prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
                },
                success: function(response) {
                    console.log("Report response:", response);
                    if (response.success) {
                        // Update student information
                        $("#student-name").text(response.data.student_info.name);
                        $("#student-roll").text(response.data.student_info.roll_no);
                        $("#student-batch").text(response.data.student_info.batch);
                        $("#student-course").text(response.data.student_info.course);

                        // Set period and date
                        var periodText = formatDateRange(fromDate, toDate);
                        $("#report-period").text(periodText);
                        $("#report-date").text(formatDate(new Date().toISOString().split('T')[0]));

                        // Update attendance summary
                        $("#present-count").text(response.data.summary.present_count);
                        $("#absent-count").text(response.data.summary.absent_count);
                        $("#total-count").text(response.data.summary.total_count);

                        $("#present-percentage").text(response.data.summary.present_percentage + "%");
                        $("#absent-percentage").text(response.data.summary.absent_percentage + "%");

                        // Create overall attendance pie chart
                        var ctx = document.getElementById("overall-chart").getContext("2d");
                        if (window.overallChart) {
                            window.overallChart.destroy();
                        }
                        window.overallChart = new Chart(ctx, {
                            type: 'pie',
                            data: {
                                labels: ['Present', 'Absent'],
                                datasets: [{
                                    data: [
                                        response.data.summary.present_count,
                                        response.data.summary.absent_count
                                    ],
                                    backgroundColor: [
                                        'rgba(40, 167, 69, 0.7)',
                                        'rgba(220, 53, 69, 0.7)'
                                    ],
                                    borderColor: [
                                        'rgba(40, 167, 69, 1)',
                                        'rgba(220, 53, 69, 1)'
                                    ],
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        position: 'bottom'
                                    }
                                }
                            }
                        });

                        // Create subject-wise attendance chart
                        var subjectCtx = document.getElementById("subject-chart").getContext("2d");
                        if (window.subjectChart) {
                            window.subjectChart.destroy();
                        }

                        // Prepare data for subject chart
                        var subjectLabels = response.data.subject_attendance.map(function(item) {
                            return item.subject;
                        });

                        var presentData = response.data.subject_attendance.map(function(item) {
                            return item.present_percentage;
                        });

                        window.subjectChart = new Chart(subjectCtx, {
                            type: 'bar',
                            data: {
                                labels: subjectLabels,
                                datasets: [{
                                    label: 'Attendance Percentage',
                                    data: presentData,
                                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                                    borderColor: 'rgba(54, 162, 235, 1)',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        max: 100,
                                        title: {
                                            display: true,
                                            text: 'Attendance Percentage (%)'
                                        }
                                    },
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Subjects'
                                        }
                                    }
                                }
                            }
                        });

                        // Update detailed attendance records table
                        var attendanceHtml = "";
                        if (response.data.attendance_records && response.data.attendance_records.length > 0) {
                            $.each(response.data.attendance_records, function(index, record) {
                                var statusClass = "";
                                if (record.status === 'present') {
                                    statusClass = "table-success";
                                } else if (record.status === 'absent') {
                                    statusClass = "table-danger";
                                }

                                attendanceHtml += "<tr class='" + statusClass + "'>";
                                attendanceHtml += "<td>" + formatDate(record.date) + "</td>";
                                attendanceHtml += "<td>" + record.subject + "</td>";
                                attendanceHtml += "<td>" + capitalizeFirstLetter(record.status) + "</td>";
                                attendanceHtml += "</tr>";
                            });
                        } else {
                            attendanceHtml = "<tr><td colspan='3' class='text-center'>No attendance records found for this period</td></tr>";
                        }
                        $("#attendance-body").html(attendanceHtml);

                        // Show report content and hide no data message
                        $("#report-content").removeClass("d-none");
                        $("#no-data-message").addClass("d-none");

                        // Enable export buttons and show email options
                        $("#print-btn, #export-btn, #email-btn").prop('disabled', false);
                        $("#email-options-card").slideDown();
                    } else {
                        // Show error message
                        alert("Error generating report: " + response.error);

                        // Hide report content and show no data message
                        $("#report-content").addClass("d-none");
                        $("#no-data-message").removeClass("d-none");
                        $("#email-options-card").slideUp();
                        $("#print-btn, #export-btn, #email-btn").prop('disabled', true);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    console.error("Response Text:", xhr.responseText);
                    // Show error message
                    alert("An error occurred while generating the report. Please try again.");

                    // Hide report content and show no data message
                    $("#report-content").addClass("d-none");
                    $("#no-data-message").removeClass("d-none");
                    $("#print-btn, #export-btn, #email-btn").prop('disabled', true);
                },
                complete: function() {
                    $("#filter-form button[type='submit']").prop('disabled', false).html('<i class="fas fa-chart-line"></i> Generate Report');
                }
            });
        }

        // Function to export to Excel
        function exportToExcel() {
            var studentId = $("#student-filter").val();
            var month = $("#month-filter").val();
            var year = $("#year-filter").val();

            if (!studentId) {
                alert("Please generate a report before exporting.");
                return;
            }

            // Calculate from and to dates based on month/year selection
            var fromDate, toDate;

            if (month && year) {
                fromDate = year + "-" + month + "-01";
                var lastDay = new Date(year, parseInt(month), 0).getDate();
                toDate = year + "-" + month + "-" + ("0" + lastDay).slice(-2);
            } else if (year && !month) {
                fromDate = year + "-01-01";
                toDate = year + "-12-31";
            } else if (month && !year) {
                var currentYear = new Date().getFullYear();
                fromDate = currentYear + "-" + month + "-01";
                var lastDay = new Date(currentYear, parseInt(month), 0).getDate();
                toDate = currentYear + "-" + month + "-" + ("0" + lastDay).slice(-2);
            } else {
                toDate = new Date().toISOString().split('T')[0];
                var thirtyDaysAgo = new Date();
                thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
                fromDate = thirtyDaysAgo.toISOString().split('T')[0];
            }

            // Redirect to export API
            window.location.href = "../api/teacher/export_student_attendance.php?student_id=" + studentId +
                "&from_date=" + fromDate +
                "&to_date=" + toDate +
                "&format=excel";
        }

        // Function to print report
        function printReport() {
            if (!$("#student-name").text()) {
                alert("Please generate a report before printing.");
                return;
            }

            // Create print-friendly version
            var printContent = `
            <html>
            <head>
                <title>Student Attendance Report</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    h1 { text-align: center; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
                    h2 { color: #007bff; margin-top: 20px; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
                    .info-section { margin: 20px 0; padding: 15px; background-color: #f8f9fa; border: 1px solid #ddd; border-radius: 5px; }
                    .info-row { display: flex; justify-content: space-between; margin: 5px 0; }
                    .info-label { font-weight: bold; }
                    table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                    th, td { border: 2px solid #333; padding: 10px; text-align: left; }
                    th { 
                        background-color: #007bff !important; 
                        color: white !important;
                        font-weight: bold;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    .present { 
                        background-color: #d4edda !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    .absent { 
                        background-color: #f8d7da !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    .leave { 
                        background-color: #fff3cd !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    .summary-table { max-width: 500px; }
                    @media print {
                        body { margin: 0; }
                        .no-print { display: none; }
                        th { 
                            background-color: #007bff !important; 
                            color: white !important;
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                        }
                        .present { 
                            background-color: #d4edda !important;
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                        }
                        .absent { 
                            background-color: #f8d7da !important;
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                        }
                    }
                </style>
            </head>
            <body>
                <h1>Cimage College - Student Attendance Report</h1>
                
                <div class="info-section">
                    <div class="info-row">
                        <div><span class="info-label">Student Name:</span> ${$("#student-name").text()}</div>
                        <div><span class="info-label">Roll No:</span> ${$("#student-roll").text()}</div>
                    </div>
                    <div class="info-row">
                        <div><span class="info-label">Course:</span> ${$("#student-course").text()}</div>
                        <div><span class="info-label">Batch:</span> ${$("#student-batch").text()}</div>
                    </div>
                    <div class="info-row">
                        <div><span class="info-label">Period:</span> ${$("#report-period").text()}</div>
                        <div><span class="info-label">Report Date:</span> ${$("#report-date").text()}</div>
                    </div>
                </div>
                
                <h2>Overall Attendance Summary</h2>
                <table class="summary-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="present">
                            <td>Present</td>
                            <td>${$("#present-count").text()}</td>
                            <td>${$("#present-percentage").text()}</td>
                        </tr>
                        <tr class="absent">
                            <td>Absent</td>
                            <td>${$("#absent-count").text()}</td>
                            <td>${$("#absent-percentage").text()}</td>
                        </tr>
                        <tr>
                            <td><strong>Total</strong></td>
                            <td><strong>${$("#total-count").text()}</strong></td>
                            <td><strong>100%</strong></td>
                        </tr>
                    </tbody>
                </table>
                
                <h2>Detailed Attendance Records</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Subject</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${$("#attendance-body").html()}
                    </tbody>
                </table>
                
                <p style="text-align: center; margin-top: 40px; color: #666;">
                    Generated on ${new Date().toLocaleDateString()} at ${new Date().toLocaleTimeString()}
                </p>
            </body>
            </html>
        `;

            var printWindow = window.open('', '_blank');
            printWindow.document.write(printContent);
            printWindow.document.close();
            printWindow.focus();
            setTimeout(function() {
                printWindow.print();
            }, 250);
        }

        // Function to format date range
        function formatDateRange(fromDate, toDate) {
            var from = new Date(fromDate);
            var to = new Date(toDate);

            var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
            ];

            if (from.getMonth() === to.getMonth() && from.getFullYear() === to.getFullYear()) {
                // Same month
                return monthNames[from.getMonth()] + " " + from.getFullYear();
            } else if (from.getFullYear() === to.getFullYear() && from.getMonth() === 0 && to.getMonth() === 11) {
                // Full year
                return "Year " + from.getFullYear();
            } else {
                // Date range
                return formatDate(fromDate) + " to " + formatDate(toDate);
            }
        }

        // Function to format date
        function formatDate(dateString) {
            var date = new Date(dateString);
            return ("0" + date.getDate()).slice(-2) + "-" + ("0" + (date.getMonth() + 1)).slice(-2) + "-" + date.getFullYear();
        }

        // Function to capitalize first letter
        function capitalizeFirstLetter(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }

        // Function to send email report
        function sendEmailReport() {
            // Get student data from filter form and displayed report
            var studentId = $('#student-filter').val();
            var studentName = $('#student-name').text();
            var month = $('#month-filter').val();
            var year = $('#year-filter').val();

            // Calculate from and to dates
            var fromDate, toDate;
            if (month && year) {
                fromDate = year + "-" + month + "-01";
                var lastDay = new Date(year, parseInt(month), 0).getDate();
                toDate = year + "-" + month + "-" + ("0" + lastDay).slice(-2);
            } else if (year && !month) {
                fromDate = year + "-01-01";
                toDate = year + "-12-31";
            } else if (month && !year) {
                var currentYear = new Date().getFullYear();
                fromDate = currentYear + "-" + month + "-01";
                var lastDay = new Date(currentYear, parseInt(month), 0).getDate();
                toDate = currentYear + "-" + month + "-" + ("0" + lastDay).slice(-2);
            } else {
                toDate = new Date().toISOString().split('T')[0];
                var thirtyDaysAgo = new Date();
                thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
                fromDate = thirtyDaysAgo.toISOString().split('T')[0];
            }

            // Validate that report is generated
            if (!studentId || !studentName || !fromDate || !toDate) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Report Available',
                    text: 'Please generate a report before sending via email.',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            // Confirmation dialog
            Swal.fire({
                title: 'Send Attendance Report?',
                html: '<p>Send attendance report for <strong>' + studentName + '</strong></p>' +
                    '<p>Period: ' + formatDate(fromDate) + ' to ' + formatDate(toDate) + '</p>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Send Email',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Sending Email...',
                        html: 'Please wait while we generate and send the report.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Send AJAX request
                    $.ajax({
                        url: '../api/teacher/send_attendance_email.php',
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            student_id: studentId,
                            from_date: fromDate,
                            to_date: toDate,
                            override_test: $('#sendToRealStudent').is(':checked') ? 1 : 0
                        },
                        success: function(response) {
                            Swal.close();

                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Email Sent!',
                                    html: response.message || 'Attendance report has been sent successfully.',
                                    confirmButtonColor: '#28a745'
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Failed to Send',
                                    text: response.error || 'Could not send the email. Please try again.',
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.close();
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occurred while sending the email. Please try again.',
                                confirmButtonColor: '#dc3545'
                            });
                            console.error('Email error:', error);
                        }
                    });
                }
            });
        }
    });
</script>

<?php
// Include footer
include_once "../includes/footer.php";
?>