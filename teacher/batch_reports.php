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
$pageTitle = "Batch Attendance Reports";
$basePath = "..";
include_once "../includes/header.php";
?>

<main class="main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-users me-2"></i> Batch Attendance Reports</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group btn-group-sm me-2">
                <button type="button" class="btn btn-danger" id="print-btn">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
                <button type="button" class="btn btn-success" id="export-btn">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
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
                <div class="col-md-3">
                    <label for="batch-filter" class="form-label">Batch</label>
                    <select class="form-select" id="batch-filter" required>
                        <option value="">Select Batch</option>
                        <!-- Batches will be loaded here via AJAX -->
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="subject-filter" class="form-label">Subject</label>
                    <select class="form-select" id="subject-filter" required>
                        <option value="">Select Subject</option>
                        <!-- Subjects will be loaded here via AJAX -->
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="from-date" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="from-date" value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>">
                </div>
                <div class="col-md-3">
                    <label for="to-date" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="to-date" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Content (initially hidden) -->
    <div id="report-content" class="d-none">
        <!-- Batch Information -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle"></i> Batch Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Batch:</strong> <span id="batch-name"></span>
                    </div>
                    <div class="col-md-3">
                        <strong>Course:</strong> <span id="course-name"></span>
                    </div>
                    <div class="col-md-3">
                        <strong>Subject:</strong> <span id="subject-name"></span>
                    </div>
                    <div class="col-md-3">
                        <strong>Period:</strong> <span id="date-range"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-pie"></i> Attendance Summary
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <canvas id="attendance-chart" width="100%" height="50"></canvas>
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

        <!-- Daily Attendance Trend -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-line"></i> Daily Attendance Trend
            </div>
            <div class="card-body">
                <canvas id="trend-chart" width="100%" height="40"></canvas>
            </div>
        </div>

        <!-- Student-wise Attendance -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-table"></i> Student-wise Attendance
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="student-table" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Roll No</th>
                                <th>Name</th>
                                <th>Present</th>
                                <th>Absent</th>
                                <th>Total</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody id="student-body">
                            <!-- Student data will be loaded here via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- No Data Message (initially shown) -->
    <div id="no-data-message" class="card mb-4">
        <div class="card-body text-center">
            <i class="fas fa-chart-bar fa-3x mb-3 text-muted"></i>
            <h4>No Report Data</h4>
            <p>Please select a batch, subject, and date range to generate a report.</p>
        </div>
    </div>
</main>
</div>
</div>

<script>
    $(document).ready(function() {
        // Load batches and subjects for filter
        loadBatches();

        // Handle batch change to load subjects
        $("#batch-filter").change(function() {
            loadSubjects($(this).val());
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
            if ($("#report-content").hasClass("d-none")) {
                alert("Please generate a report first before printing.");
                return;
            }

            // Create print window
            var printWindow = window.open('', '_blank');

            var batchName = $("#batch-name").text();
            var courseName = $("#course-name").text();
            var subjectName = $("#subject-name").text();
            var dateRange = $("#date-range").text();

            var presentCount = $("#present-count").text();
            var absentCount = $("#absent-count").text();
            var totalCount = $("#total-count").text();

            var presentPercentage = $("#present-percentage").text();
            var absentPercentage = $("#absent-percentage").text();

            var studentTableHtml = $("#student-table").clone();

            var printHtml = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Batch Attendance Report</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .header h1 { margin: 0; font-size: 24px; }
                .header h2 { margin: 5px 0; font-size: 18px; color: #666; }
                .info-section { margin: 20px 0; padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; }
                .info-row { display: flex; justify-content: space-between; margin: 10px 0; }
                .summary-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                .summary-table th, .summary-table td { border: 1px solid #dee2e6; padding: 8px; text-align: left; }
                .summary-table th { background-color: #f8f9fa; font-weight: bold; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                table th, table td { border: 1px solid #dee2e6; padding: 8px; text-align: left; }
                table th { background-color: #f8f9fa; font-weight: bold; }
                .text-success { color: #28a745; font-weight: bold; }
                .text-warning { color: #ffc107; font-weight: bold; }
                .text-danger { color: #dc3545; font-weight: bold; }
                .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
                @media print { body { margin: 0; } }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Batch Attendance Report</h1>
                <h2>Cimage College</h2>
            </div>
            
            <div class="info-section">
                <h3>Report Information</h3>
                <div class="info-row">
                    <div><strong>Batch:</strong> ${batchName}</div>
                    <div><strong>Course:</strong> ${courseName}</div>
                </div>
                <div class="info-row">
                    <div><strong>Subject:</strong> ${subjectName}</div>
                    <div><strong>Period:</strong> ${dateRange}</div>
                </div>
            </div>
            
            <h3>Attendance Summary</h3>
            <table class="summary-table">
                <tr>
                    <th>Status</th>
                    <th>Count</th>
                    <th>Percentage</th>
                </tr>
                <tr>
                    <td>Present</td>
                    <td>${presentCount}</td>
                    <td>${presentPercentage}</td>
                </tr>
                <tr>
                    <td>Absent</td>
                    <td>${absentCount}</td>
                    <td>${absentPercentage}</td>
                </tr>
                <tr>
                    <td><strong>Total</strong></td>
                    <td><strong>${totalCount}</strong></td>
                    <td><strong>100%</strong></td>
                </tr>
            </table>
            
            <h3>Student-wise Attendance</h3>
            ${studentTableHtml.prop('outerHTML')}
            
            <div class="footer">
                <p>Generated on ${new Date().toLocaleString()}</p>
            </div>
            
            <script type="text/javascript">
                window.onload = function() {
                    window.print();
                    window.onafterprint = function() {
                        window.close();
                    };
                };
            <\/script>
        </body>
        </html>
        `;

            printWindow.document.write(printHtml);
            printWindow.document.close();
        });

        // Handle export button click
        $("#export-btn").click(function() {
            var batchId = $("#batch-filter").val();
            var subjectId = $("#subject-filter").val();
            var fromDate = $("#from-date").val();
            var toDate = $("#to-date").val();

            if (!batchId || !subjectId) {
                alert("Please generate a report first before exporting.");
                return;
            }

            // Redirect to export API with format parameter for Excel
            window.location.href = "../api/teacher/export_attendance.php?batch_id=" + batchId +
                "&subject_id=" + subjectId +
                "&from_date=" + fromDate +
                "&to_date=" + toDate +
                "&format=excel";
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
                        // Update batch filter dropdown
                        var batchesHtml = "<option value=''>Select Batch</option>";
                        $.each(response.data, function(index, batch) {
                            batchesHtml += "<option value='" + batch.batch_id + "'>" + batch.batch_name + " - " + batch.course + "</option>";
                        });
                        $("#batch-filter").html(batchesHtml);
                    } else {
                        console.error("Error loading batches:", response.error);
                        alert("Error loading batches: " + (response.error || "Unknown error"));
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    console.error("Response Text:", xhr.responseText);
                    alert("Failed to load batches. Please refresh the page.");
                }
            });
        }

        // Function to load subjects
        function loadSubjects(batchId) {
            if (!batchId) {
                $("#subject-filter").html("<option value=''>Select Subject</option>");
                return;
            }

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
                        // Update subject filter dropdown
                        var subjectsHtml = "<option value=''>Select Subject</option>";
                        $.each(response.data, function(index, subject) {
                            subjectsHtml += "<option value='" + subject.subject_id + "'>" + subject.subject_name + "</option>";
                        });
                        $("#subject-filter").html(subjectsHtml);
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

        // Function to generate report
        function generateReport() {
            var batchId = $("#batch-filter").val();
            var subjectId = $("#subject-filter").val();
            var fromDate = $("#from-date").val();
            var toDate = $("#to-date").val();

            $.ajax({
                type: "GET",
                url: "../api/teacher/batch_attendance_report.php",
                data: {
                    batch_id: batchId,
                    subject_id: subjectId,
                    from_date: fromDate,
                    to_date: toDate
                },
                dataType: "json",
                success: function(response) {
                    console.log("Report response:", response);
                    if (response.success) {
                        // Update batch information
                        $("#batch-name").text(response.data.batch_info.batch);
                        $("#course-name").text(response.data.batch_info.course);
                        $("#subject-name").text(response.data.batch_info.subject);
                        $("#date-range").text(formatDate(fromDate) + " to " + formatDate(toDate));

                        // Update attendance summary
                        $("#present-count").text(response.data.summary.present_count);
                        $("#absent-count").text(response.data.summary.absent_count);
                        $("#total-count").text(response.data.summary.total_count);

                        $("#present-percentage").text(response.data.summary.present_percentage + "%");
                        $("#absent-percentage").text(response.data.summary.absent_percentage + "%");

                        // Create attendance pie chart
                        var ctx = document.getElementById("attendance-chart").getContext("2d");
                        if (window.attendanceChart) {
                            window.attendanceChart.destroy();
                        }
                        window.attendanceChart = new Chart(ctx, {
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

                        // Create daily attendance trend chart
                        var trendCtx = document.getElementById("trend-chart").getContext("2d");
                        if (window.trendChart) {
                            window.trendChart.destroy();
                        }
                        window.trendChart = new Chart(trendCtx, {
                            type: 'bar',
                            data: {
                                labels: response.data.daily_trend.map(function(item) {
                                    return formatDate(item.date);
                                }),
                                datasets: [{
                                    label: 'Present',
                                    data: response.data.daily_trend.map(function(item) {
                                        return item.present_count;
                                    }),
                                    backgroundColor: 'rgba(40, 167, 69, 0.8)',
                                    borderColor: 'rgba(40, 167, 69, 1)',
                                    borderWidth: 1
                                }, {
                                    label: 'Absent',
                                    data: response.data.daily_trend.map(function(item) {
                                        return item.absent_count;
                                    }),
                                    backgroundColor: 'rgba(220, 53, 69, 0.8)',
                                    borderColor: 'rgba(220, 53, 69, 1)',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                interaction: {
                                    mode: 'index',
                                    intersect: false
                                },
                                plugins: {
                                    legend: {
                                        position: 'top',
                                        labels: {
                                            usePointStyle: true,
                                            padding: 15,
                                            font: {
                                                size: 12
                                            }
                                        }
                                    },
                                    tooltip: {
                                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                        padding: 12,
                                        titleFont: {
                                            size: 14
                                        },
                                        bodyFont: {
                                            size: 13
                                        },
                                        callbacks: {
                                            label: function(context) {
                                                return context.dataset.label + ': ' + context.parsed.y + ' students';
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            stepSize: 1,
                                            font: {
                                                size: 11
                                            }
                                        },
                                        grid: {
                                            color: 'rgba(0, 0, 0, 0.05)'
                                        },
                                        title: {
                                            display: true,
                                            text: 'Number of Students',
                                            font: {
                                                size: 13,
                                                weight: 'bold'
                                            }
                                        }
                                    },
                                    x: {
                                        ticks: {
                                            maxRotation: 45,
                                            minRotation: 45,
                                            font: {
                                                size: 10
                                            }
                                        },
                                        grid: {
                                            display: false
                                        },
                                        title: {
                                            display: true,
                                            text: 'Date',
                                            font: {
                                                size: 13,
                                                weight: 'bold'
                                            }
                                        }
                                    }
                                }
                            }
                        });

                        // Update student-wise attendance table
                        var studentHtml = "";
                        $.each(response.data.student_attendance, function(index, student) {
                            studentHtml += "<tr>";
                            studentHtml += "<td>" + student.roll_no + "</td>";
                            studentHtml += "<td>" + student.name + "</td>";
                            studentHtml += "<td>" + student.present_count + "</td>";
                            studentHtml += "<td>" + student.absent_count + "</td>";
                            studentHtml += "<td>" + student.total_count + "</td>";

                            // Attendance percentage with color coding
                            var percentageClass = "";
                            if (student.percentage >= 75) {
                                percentageClass = "text-success";
                            } else if (student.percentage >= 50) {
                                percentageClass = "text-warning";
                            } else {
                                percentageClass = "text-danger";
                            }

                            studentHtml += "<td class='" + percentageClass + "'>" + student.percentage + "%</td>";
                            studentHtml += "</tr>";
                        });
                        $("#student-body").html(studentHtml);

                        // Show report content and hide no data message
                        $("#report-content").removeClass("d-none");
                        $("#no-data-message").addClass("d-none");
                    } else {
                        // Show error message
                        console.error("Error generating report:", response.error);
                        alert("Error generating report: " + (response.error || "Unknown error"));

                        // Hide report content and show no data message
                        $("#report-content").addClass("d-none");
                        $("#no-data-message").removeClass("d-none");
                    }
                },
                error: function(xhr, status, error) {
                    // Show error message
                    console.error("AJAX Error:", error);
                    console.error("Response Text:", xhr.responseText);
                    alert("An error occurred while generating the report. Please check console for details.");

                    // Hide report content and show no data message
                    $("#report-content").addClass("d-none");
                    $("#no-data-message").removeClass("d-none");
                }
            });
        }

        // Function to format date
        function formatDate(dateString) {
            var date = new Date(dateString);
            return ("0" + date.getDate()).slice(-2) + "-" + ("0" + (date.getMonth() + 1)).slice(-2) + "-" + date.getFullYear();
        }
    });
</script>

<?php
// Include footer
include_once "../includes/footer.php";
?>