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
$pageTitle = "Attendance Reports";
$basePath = "..";
include_once "../includes/header.php";
?>

<!-- Main content -->
 <main class="main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Attendance Reports</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="printReport">
                    <i class="fas fa-print"></i> Print
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="exportReport">
                    <i class="fas fa-file-export"></i> Export
                </button>
            </div>
        </div>
    </div>

    <!-- Report Filters -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">Generate Report</h5>
        </div>
        <div class="card-body">
            <form id="reportForm" class="row g-3">
                <div class="col-md-3">
                    <label for="reportType" class="form-label">Report Type</label>
                    <select class="form-select" id="reportType" name="reportType" required>
                        <option value="">Select Report Type</option>
                        <option value="batch" selected>Batch Report (Default)</option>
                        <option value="daily">Daily Attendance</option>
                        <option value="monthly">Monthly Summary</option>
                        <option value="subject">Subject-wise</option>
                        <option value="student">Student-wise</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="courseId" class="form-label">Course</label>
                    <select class="form-select" id="courseId" name="courseId">
                        <option value="">Select Course</option>
                        <!-- Courses will be loaded dynamically -->
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="batchId" class="form-label">Batch</label>
                    <select class="form-select" id="batchId" name="batchId">
                        <option value="">Select Batch</option>
                        <!-- Batches will be loaded dynamically -->
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="subjectId" class="form-label">Subject</label>
                    <select class="form-select" id="subjectId" name="subjectId">
                        <option value="">Select Subject</option>
                        <!-- Subjects will be loaded dynamically -->
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="fromDate" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="fromDate" name="fromDate">
                </div>
                <div class="col-md-3">
                    <label for="toDate" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="toDate" name="toDate">
                </div>
                <div class="col-md-3">
                    <label for="studentId" class="form-label">Student (Optional)</label>
                    <select class="form-select" id="studentId" name="studentId">
                        <option value="">Select Student</option>
                        <!-- Students will be loaded dynamically -->
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" id="generateReport" class="btn btn-primary">Generate Report</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Results -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Report Results</h5>
            <div>
                <button class="btn btn-sm btn-outline-primary" id="refreshReport">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
        <div class="card-body">
            <div id="reportContainer">
                <div class="text-center py-5">
                    <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Click "Generate Report" to view batch-wise attendance data</p>
                    <p class="text-muted"><small>Or select filters above to customize the report</small></p>
                    <button type="button" class="btn btn-primary mt-3" onclick="$('#generateReport').click()">
                        <i class="fas fa-file-alt"></i> Generate Batch Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Summary -->
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Attendance Summary</h5>
                </div>
                <div class="card-body">
                    <div id="attendanceSummaryChart" style="height: 300px;">
                        <!-- Chart will be rendered here -->
                        <div class="text-center py-5">
                            <p class="text-muted">Generate a report to view summary</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Attendance Trends</h5>
                </div>
                <div class="card-body">
                    <div id="attendanceTrendChart" style="height: 300px;">
                        <!-- Chart will be rendered here -->
                        <div class="text-center py-5">
                            <p class="text-muted">Generate a report to view trends</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
</div>
</div>

<?php include_once "../includes/footer.php"; ?>

<script>
    $(document).ready(function() {
        // Load dropdown options on page load
        loadDropdownOptions();

        // Auto-generate default report on page load
        setTimeout(function() {
            generateReport();
        }, 1000);

        // Function to load all dropdown options
        function loadDropdownOptions() {
            // Load courses
            $.ajax({
                type: "GET",
                url: "../api/admin/courses.php",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        populateCourseDropdown(response.data);
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
                        populateSubjectDropdown(response.data);
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
                        populateBatchDropdown(response.data);
                    }
                }
            });
        }

        // Function to populate course dropdown
        function populateCourseDropdown(courses) {
            var options = '<option value="">Select Course</option>';

            $.each(courses, function(index, course) {
                options += '<option value="' + course.id + '">' + course.name + '</option>';
            });

            $("#courseId").html(options);
        }

        // Function to populate subject dropdown
        function populateSubjectDropdown(subjects) {
            var options = '<option value="">Select Subject</option>';

            $.each(subjects, function(index, subject) {
                options += '<option value="' + subject.id + '">' + subject.name + '</option>';
            });

            $("#subjectId").html(options);
        }

        // Function to populate batch dropdown
        function populateBatchDropdown(batches) {
            var options = '<option value="">Select Batch</option>';

            $.each(batches, function(index, batch) {
                options += '<option value="' + batch.id + '">' + batch.name + '</option>';
            });

            $("#batchId").html(options);
        }

        // Load students based on selected course and batch
        $("#courseId, #batchId").on("change", function() {
            var courseId = $("#courseId").val();
            var batchId = $("#batchId").val();

            if (courseId && batchId) {
                $.ajax({
                    type: "GET",
                    url: "../api/admin/students.php",
                    data: {
                        course_id: courseId,
                        batch_id: batchId
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            populateStudentDropdown(response.data);
                        }
                    }
                });
            }
        });

        // Function to populate student dropdown
        function populateStudentDropdown(students) {
            var options = '<option value="">Select Student</option>';

            $.each(students, function(index, student) {
                options += '<option value="' + student.id + '">' + student.name + ' (' + student.roll_no + ')</option>';
            });

            $("#studentId").html(options);
        }

        // Generate report
        $("#generateReport").on("click", function() {
            generateReport();
        });

        // Function to generate report
        function generateReport() {
            var reportType = $("#reportType").val() || 'batch';
            var formData = $("#reportForm").serialize();

            // Show loading indicator
            $("#reportContainer").html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Generating report...</p></div>');

            $.ajax({
                type: "GET",
                url: "../api/admin/attendance_reports.php",
                data: formData,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        displayReport(response.data, reportType);
                        renderCharts(response.data);
                    } else {
                        $("#reportContainer").html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error:", error);
                    $("#reportContainer").html('<div class="alert alert-danger">Error generating report. Please try again.</div>');
                }
            });
        }

        // Function to display report based on type
        function displayReport(data, reportType) {
            var html = '';

            switch (reportType) {
                case 'batch':
                    html = generateBatchReport(data);
                    break;
                case 'daily':
                    html = generateDailyReport(data);
                    break;
                case 'monthly':
                    html = generateMonthlyReport(data);
                    break;
                case 'subject':
                    html = generateSubjectReport(data);
                    break;
                case 'student':
                    html = generateStudentReport(data);
                    break;
                default:
                    html = '<div class="alert alert-warning">Invalid report type selected</div>';
            }

            $("#reportContainer").html(html);
        }

        // Function to generate batch attendance report (default)
        function generateBatchReport(data) {
            var html = '<div class="table-responsive">';
            html += '<table class="table table-bordered table-hover table-striped" id="batchReportTable">';
            html += '<thead class="table-light">';
            html += '<tr>';
            html += '<th>Course</th>';
            html += '<th>Batch</th>';
            html += '<th>Total Students</th>';
            html += '<th>Classes Held</th>';
            html += '<th>Avg. Present/Class</th>';
            html += '<th>Avg. Absent/Class</th>';
            html += '<th>Overall %</th>';
            html += '<th>Status</th>';
            html += '</tr>';
            html += '</thead>';
            html += '<tbody>';

            if (data.length > 0) {
                $.each(data, function(index, item) {
                    console.log('Batch Report Item:', item); // Debug log

                    var totalStudents = parseInt(item.total_students) || 0;
                    var present = parseInt(item.present_count) || 0;
                    var absent = parseInt(item.absent_count) || 0;
                    var totalClasses = parseInt(item.total_classes) || 1;

                    // Calculate total attendance records
                    var totalRecords = present + absent;

                    // Calculate average students per class
                    // Example: If 101 students, 5 classes held
                    // Total possible = 101 × 5 = 505
                    // If present_count = 400, avg = 400 ÷ 5 = 80 students per class
                    var avgPresent = totalClasses > 0 ? (present / totalClasses) : 0;
                    var avgAbsent = totalClasses > 0 ? (absent / totalClasses) : 0;

                    // If average exceeds total students, it means data is cumulative across subjects
                    // In that case, we need to divide by number of subjects too
                    // For now, just cap it at total students and show warning
                    var avgPresentDisplay = avgPresent;
                    var avgAbsentDisplay = avgAbsent;
                    var dataWarning = '';

                    if (avgPresent > totalStudents) {
                        // Likely counting multiple subjects - estimate subjects
                        var estimatedSubjects = Math.ceil(avgPresent / totalStudents);
                        avgPresentDisplay = (avgPresent / estimatedSubjects);
                        avgAbsentDisplay = (avgAbsent / estimatedSubjects);
                        dataWarning = ' <small class="text-muted">(multi-subject)</small>';
                    }

                    avgPresent = avgPresentDisplay.toFixed(1);
                    avgAbsent = avgAbsentDisplay.toFixed(1);

                    // Calculate overall attendance percentage
                    var percentage = totalRecords > 0 ? ((present / totalRecords) * 100).toFixed(2) : 0;

                    // Color code based on attendance percentage
                    var badgeClass = 'bg-secondary';
                    var statusText = 'No Data';
                    var statusIcon = 'fa-question';

                    if (percentage >= 75) {
                        badgeClass = 'bg-success';
                        statusText = 'Excellent';
                        statusIcon = 'fa-check-circle';
                    } else if (percentage >= 60) {
                        badgeClass = 'bg-warning';
                        statusText = 'Fair';
                        statusIcon = 'fa-exclamation-triangle';
                    } else if (percentage > 0) {
                        badgeClass = 'bg-danger';
                        statusText = 'Poor';
                        statusIcon = 'fa-times-circle';
                    }

                    html += '<tr>';
                    html += '<td><strong>' + item.course_name + '</strong></td>';
                    html += '<td>' + item.batch_name + '</td>';
                    html += '<td><span class="badge bg-primary">' + totalStudents + ' students</span></td>';
                    html += '<td><span class="badge bg-info">' + totalClasses + ' classes</span></td>';
                    html += '<td><span class="text-success"><i class="fas fa-user-check"></i> ' + avgPresent + ' students' + dataWarning + '</span></td>';
                    html += '<td><span class="text-danger"><i class="fas fa-user-times"></i> ' + avgAbsent + ' students' + dataWarning + '</span></td>';
                    html += '<td><span class="badge ' + badgeClass + ' fs-6">' + percentage + '%</span></td>';
                    html += '<td><i class="fas ' + statusIcon + ' me-1"></i>' + statusText + '</td>';
                    html += '</tr>';
                });
            } else {
                html += '<tr><td colspan="8" class="text-center">No data found. Make sure attendance has been recorded.</td></tr>';
            }

            html += '</tbody>';
            html += '</table>';
            html += '</div>';

            return html;
        }

        // Function to generate daily attendance report
        function generateDailyReport(data) {
            var html = '<div class="table-responsive">';
            html += '<table class="table table-bordered table-hover table-striped" id="dailyReportTable">';
            html += '<thead class="table-light">';
            html += '<tr>';
            html += '<th>Date</th>';
            html += '<th>Course</th>';
            html += '<th>Batch</th>';
            html += '<th>Subject</th>';
            html += '<th>Present</th>';
            html += '<th>Absent</th>';
            html += '<th>Percentage</th>';
            html += '</tr>';
            html += '</thead>';
            html += '<tbody>';

            if (data.length > 0) {
                $.each(data, function(index, item) {
                    var percentage = ((item.present / (item.present + item.absent)) * 100).toFixed(2);

                    html += '<tr>';
                    html += '<td>' + item.date + '</td>';
                    html += '<td>' + item.course_name + '</td>';
                    html += '<td>' + item.batch_name + '</td>';
                    html += '<td>' + item.subject_name + '</td>';
                    html += '<td>' + item.present + '</td>';
                    html += '<td>' + item.absent + '</td>';
                    html += '<td>' + percentage + '%</td>';
                    html += '</tr>';
                });
            } else {
                html += '<tr><td colspan="7" class="text-center">No data found</td></tr>';
            }

            html += '</tbody>';
            html += '</table>';
            html += '</div>';

            return html;
        }

        // Function to generate monthly attendance report
        function generateMonthlyReport(data) {
            var html = '<div class="table-responsive">';
            html += '<table class="table table-bordered table-hover table-striped" id="monthlyReportTable">';
            html += '<thead class="table-light">';
            html += '<tr>';
            html += '<th>Month</th>';
            html += '<th>Course</th>';
            html += '<th>Batch</th>';
            html += '<th>Total Students</th>';
            html += '<th>Classes Held</th>';
            html += '<th>Avg. Present/Class</th>';
            html += '<th>Percentage</th>';
            html += '</tr>';
            html += '</thead>';
            html += '<tbody>';

            if (data.length > 0) {
                $.each(data, function(index, item) {
                    var totalStudents = parseInt(item.total_students) || 0;
                    var totalClasses = parseInt(item.total_classes) || 1;
                    var totalPresent = parseInt(item.total_present) || 0;
                    var totalAbsent = parseInt(item.total_absent) || 0;
                    var avgAttendance = parseFloat(item.avg_attendance) || 0;

                    // Calculate total attendance records
                    var totalRecords = totalPresent + totalAbsent;

                    // Calculate correct percentage: (total_present / total_records) × 100
                    var percentage = totalRecords > 0 ? ((totalPresent / totalRecords) * 100).toFixed(2) : 0;

                    // Color code based on attendance percentage
                    var badgeClass = 'bg-secondary';
                    if (percentage >= 75) {
                        badgeClass = 'bg-success';
                    } else if (percentage >= 60) {
                        badgeClass = 'bg-warning';
                    } else if (percentage > 0) {
                        badgeClass = 'bg-danger';
                    }

                    html += '<tr>';
                    html += '<td><strong>' + item.month + '</strong></td>';
                    html += '<td>' + item.course_name + '</td>';
                    html += '<td>' + item.batch_name + '</td>';
                    html += '<td><span class="badge bg-primary">' + totalStudents + ' students</span></td>';
                    html += '<td><span class="badge bg-info">' + totalClasses + ' classes</span></td>';
                    html += '<td><span class="text-success">' + avgAttendance.toFixed(1) + ' students</span></td>';
                    html += '<td><span class="badge ' + badgeClass + ' fs-6">' + percentage + '%</span></td>';
                    html += '</tr>';
                });
            } else {
                html += '<tr><td colspan="7" class="text-center">No data found</td></tr>';
            }

            html += '</tbody>';
            html += '</table>';
            html += '</div>';

            return html;
        }

        // Function to generate subject-wise attendance report
        function generateSubjectReport(data) {
            var html = '<div class="table-responsive">';
            html += '<table class="table table-bordered table-hover table-striped" id="subjectReportTable">';
            html += '<thead class="table-light">';
            html += '<tr>';
            html += '<th>Subject</th>';
            html += '<th>Course</th>';
            html += '<th>Batch</th>';
            html += '<th>Total Students</th>';
            html += '<th>Classes Held</th>';
            html += '<th>Avg. Present/Class</th>';
            html += '<th>Percentage</th>';
            html += '</tr>';
            html += '</thead>';
            html += '<tbody>';

            if (data.length > 0) {
                $.each(data, function(index, item) {
                    var totalStudents = parseInt(item.total_students) || 0;
                    var totalClasses = parseInt(item.total_classes) || 1;
                    var totalPresent = parseInt(item.total_present) || 0;
                    var totalAbsent = parseInt(item.total_absent) || 0;
                    var avgAttendance = parseFloat(item.avg_attendance) || 0;

                    // Calculate total attendance records
                    var totalRecords = totalPresent + totalAbsent;

                    // Calculate correct percentage: (total_present / total_records) × 100
                    var percentage = totalRecords > 0 ? ((totalPresent / totalRecords) * 100).toFixed(2) : 0;

                    // Color code based on attendance percentage
                    var badgeClass = 'bg-secondary';
                    if (percentage >= 75) {
                        badgeClass = 'bg-success';
                    } else if (percentage >= 60) {
                        badgeClass = 'bg-warning';
                    } else if (percentage > 0) {
                        badgeClass = 'bg-danger';
                    }

                    html += '<tr>';
                    html += '<td><strong>' + item.subject_name + '</strong></td>';
                    html += '<td>' + item.course_name + '</td>';
                    html += '<td>' + item.batch_name + '</td>';
                    html += '<td><span class="badge bg-primary">' + totalStudents + ' students</span></td>';
                    html += '<td><span class="badge bg-info">' + totalClasses + ' classes</span></td>';
                    html += '<td><span class="text-success">' + avgAttendance.toFixed(1) + ' students</span></td>';
                    html += '<td><span class="badge ' + badgeClass + ' fs-6">' + percentage + '%</span></td>';
                    html += '</tr>';
                });
            } else {
                html += '<tr><td colspan="7" class="text-center">No data found</td></tr>';
            }

            html += '</tbody>';
            html += '</table>';
            html += '</div>';

            return html;
        }

        // Function to generate student-wise attendance report
        function generateStudentReport(data) {
            var html = '<div class="table-responsive">';
            html += '<table class="table table-bordered table-hover table-striped" id="studentReportTable">';
            html += '<thead class="table-light">';
            html += '<tr>';
            html += '<th>Student</th>';
            html += '<th>Roll No</th>';
            html += '<th>Course</th>';
            html += '<th>Batch</th>';
            html += '<th>Present Days</th>';
            html += '<th>Absent Days</th>';
            html += '<th>Total Days</th>';
            html += '<th>Percentage</th>';
            html += '</tr>';
            html += '</thead>';
            html += '<tbody>';

            if (data.length > 0) {
                $.each(data, function(index, item) {
                    var totalDays = parseInt(item.total_days) || 0;
                    var presentDays = parseInt(item.present_days) || 0;
                    var percentage = totalDays > 0 ? ((presentDays / totalDays) * 100).toFixed(2) : 0;

                    // Color code based on attendance percentage
                    var rowClass = '';
                    var badgeClass = 'bg-secondary';
                    if (percentage >= 75) {
                        badgeClass = 'bg-success';
                    } else if (percentage >= 60) {
                        badgeClass = 'bg-warning';
                    } else if (percentage > 0) {
                        badgeClass = 'bg-danger';
                    }

                    html += '<tr>';
                    html += '<td>' + item.student_name + '</td>';
                    html += '<td>' + item.roll_no + '</td>';
                    html += '<td>' + item.course_name + '</td>';
                    html += '<td>' + item.batch_name + '</td>';
                    html += '<td><span class="badge bg-success">' + presentDays + '</span></td>';
                    html += '<td><span class="badge bg-danger">' + (item.absent_days || 0) + '</span></td>';
                    html += '<td><strong>' + totalDays + '</strong></td>';
                    html += '<td><span class="badge ' + badgeClass + '">' + percentage + '%</span></td>';
                    html += '</tr>';
                });
            } else {
                html += '<tr><td colspan="8" class="text-center">No data found. Make sure attendance has been recorded.</td></tr>';
            }

            html += '</tbody>';
            html += '</table>';
            html += '</div>';

            return html;
        }

        // Function to render charts
        function renderCharts(data) {
            if (!data || data.length === 0) {
                $("#attendanceSummaryChart").html('<div class="alert alert-info text-center">No data available for charts</div>');
                $("#attendanceTrendChart").html('<div class="alert alert-info text-center">No data available for trends</div>');
                return;
            }

            // Prepare data for summary chart (Pie chart)
            var totalPresent = 0;
            var totalAbsent = 0;

            $.each(data, function(index, item) {
                totalPresent += parseInt(item.present_days || item.present || item.present_count || 0);
                totalAbsent += parseInt(item.absent_days || item.absent || item.absent_count || 0);
            });

            // Create Summary Chart (Pie/Doughnut)
            var summaryCtx = document.createElement('canvas');
            summaryCtx.id = 'summaryChartCanvas';
            $("#attendanceSummaryChart").html('').append(summaryCtx);

            new Chart(summaryCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Present', 'Absent'],
                    datasets: [{
                        data: [totalPresent, totalAbsent],
                        backgroundColor: [
                            'rgba(40, 167, 69, 0.8)',
                            'rgba(220, 53, 69, 0.8)'
                        ],
                        borderColor: [
                            'rgba(40, 167, 69, 1)',
                            'rgba(220, 53, 69, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        title: {
                            display: true,
                            text: 'Overall Attendance Distribution'
                        }
                    }
                }
            });

            // Create Trend Chart (Bar chart)
            var trendLabels = [];
            var trendPresent = [];
            var trendAbsent = [];

            // Take first 10 items for trend
            var trendData = data.slice(0, 10);

            $.each(trendData, function(index, item) {
                var label = item.batch_name || item.student_name || item.subject_name || item.date || item.month || 'Item ' + (index + 1);
                trendLabels.push(label);
                trendPresent.push(parseInt(item.present_days || item.present || item.present_count || 0));
                trendAbsent.push(parseInt(item.absent_days || item.absent || item.absent_count || 0));
            });

            var trendCtx = document.createElement('canvas');
            trendCtx.id = 'trendChartCanvas';
            $("#attendanceTrendChart").html('').append(trendCtx);

            new Chart(trendCtx, {
                type: 'bar',
                data: {
                    labels: trendLabels,
                    datasets: [{
                            label: 'Present',
                            data: trendPresent,
                            backgroundColor: 'rgba(40, 167, 69, 0.7)',
                            borderColor: 'rgba(40, 167, 69, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Absent',
                            data: trendAbsent,
                            backgroundColor: 'rgba(220, 53, 69, 0.7)',
                            borderColor: 'rgba(220, 53, 69, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        title: {
                            display: true,
                            text: 'Attendance Comparison'
                        }
                    }
                }
            });
        }

        // Refresh report
        $("#refreshReport").on("click", function() {
            $("#generateReport").click();
        });

        // Print report
        $("#printReport").on("click", function() {
            window.print();
        });

        // Export report
        $("#exportReport").on("click", function() {
            var reportType = $("#reportType").val();

            if (!reportType) {
                alert("Please generate a report first");
                return;
            }

            var formData = $("#reportForm").serialize() + "&export=true";

            window.location.href = "../api/admin/attendance_reports.php?" + formData;
        });
    });
</script>