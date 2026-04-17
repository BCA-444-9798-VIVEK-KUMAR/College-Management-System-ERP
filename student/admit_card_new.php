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
require_once '../config/database.php';

// Check if user is logged in as student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit();
}

// Get admit card ID
$admit_card_id = $_GET['id'] ?? null;

if (!$admit_card_id) {
    die('Admit card ID required');
}

// Initialize database connection
$database = new Database();
$pdo = $database->getConnection();

try {
    // Get student details from users table
    $user_query = "SELECT u.*, s.roll_no, s.college_id, s.course_id, s.session_id, s.batch_id,
                   c.title as course_name, c.code as course_code,
                   b.name as batch_name, sess.title as session_name
                   FROM users u
                   JOIN students s ON u.id = s.user_id
                   JOIN courses c ON s.course_id = c.id
                   LEFT JOIN batches b ON s.batch_id = b.id
                   JOIN sessions sess ON s.session_id = sess.id
                   WHERE u.id = :user_id";

    $user_stmt = $pdo->prepare($user_query);
    $user_stmt->execute([':user_id' => $_SESSION['user_id']]);
    $student = $user_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        die('Student not found');
    }

    // Get admit card details and verify student access
    $query = "SELECT ac.*, c.title as course_title, s.title as session_title, b.name as batch_title
              FROM exam_admit_cards ac
              LEFT JOIN courses c ON ac.course_id = c.id
              LEFT JOIN sessions s ON ac.session_id = s.id
              LEFT JOIN batches b ON ac.batch_id = b.id
              WHERE ac.id = :admit_card_id 
              AND ac.course_id = :course_id
              AND ac.session_id = :session_id
              AND (ac.batch_id IS NULL OR ac.batch_id = :batch_id)
              AND ac.status = 'published'";

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':admit_card_id' => $admit_card_id,
        ':course_id' => $student['course_id'],
        ':session_id' => $student['session_id'],
        ':batch_id' => $student['batch_id']
    ]);

    $admit_card = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admit_card) {
        die('Admit card not found or access denied');
    }

    // Parse exam schedule
    $exam_schedule = json_decode($admit_card['exam_schedule'], true);

    // Track download
    $track_query = "INSERT INTO admit_card_downloads (admit_card_id, student_id, ip_address) 
                    SELECT :admit_card_id, s.id, :ip_address FROM students s WHERE s.user_id = :user_id";
    try {
        $track_stmt = $pdo->prepare($track_query);
        $track_stmt->execute([
            ':admit_card_id' => $admit_card_id,
            ':user_id' => $_SESSION['user_id'],
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    } catch (Exception $e) {
        // Ignore duplicate tracking errors
    }
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admit Card - <?php echo htmlspecialchars($student['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        @media print {
            @page {
                size: A4;
                margin: 5mm;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            body {
                background: white;
                padding: 0;
                margin: 0;
            }

            .row {
                display: table !important;
                width: 100% !important;
                margin: 0 !important;
            }

            .col-md-9,
            .col-md-3 {
                display: table-cell !important;
                float: none !important;
                vertical-align: top !important;
            }

            .col-md-9 {
                width: 75% !important;
            }

            .col-md-3 {
                width: 25% !important;
            }

            .admit-card-container {
                box-shadow: none;
                border: 2px solid #000;
                max-width: 100%;
                margin: 0;
                page-break-after: avoid;
            }

            .action-buttons {
                display: none !important;
            }

            .card-header-section {
                padding: 10px 20px !important;
            }

            .card-header-section h1 {
                font-size: 22px !important;
            }

            .card-header-section h2 {
                font-size: 14px !important;
            }

            .admit-card-title {
                padding: 8px !important;
            }

            .admit-card-title h3 {
                font-size: 16px !important;
            }

            .card-body-section {
                padding: 12px !important;
            }

            .info-table td {
                padding: 5px 8px !important;
                font-size: 12px !important;
            }

            .student-photo {
                width: 120px !important;
                height: 145px !important;
                font-size: 40px !important;
            }

            .exam-center-info {
                padding: 8px 12px !important;
                margin: 10px 0 !important;
            }

            .exam-center-info p {
                font-size: 12px !important;
                margin: 3px 0 !important;
            }

            .exam-details-section {
                margin: 8px 0 !important;
                padding: 5px 0 !important;
            }

            .exam-details-section h4 {
                font-size: 14px !important;
                margin-bottom: 8px !important;
                padding-bottom: 5px !important;
            }

            .exam-schedule-table th {
                padding: 5px !important;
                font-size: 10px !important;
            }

            .exam-schedule-table td {
                padding: 5px !important;
                font-size: 10px !important;
            }

            .venue-section {
                margin: 8px 0 !important;
                padding: 8px !important;
            }

            .venue-section h5 {
                font-size: 13px !important;
                margin-bottom: 5px !important;
            }

            .venue-section p {
                font-size: 11px !important;
                margin: 2px 0 !important;
            }

            .instructions-section {
                margin: 8px 0 !important;
                padding: 8px !important;
            }

            .instructions-section h5 {
                font-size: 13px !important;
                margin-bottom: 5px !important;
            }

            .instructions-section ul {
                margin: 0 !important;
                padding-left: 18px !important;
            }

            .instructions-section li {
                margin-bottom: 2px !important;
                font-size: 10px !important;
                line-height: 1.3 !important;
            }

            .signature-section {
                margin-top: 5px !important;
            }

            .signature-box div[style*="height: 60px"] {
                height: 60px !important;
            }

            .signature-box p {
                font-size: 11px !important;
            }

            .signature-box small {
                font-size: 9px !important;
            }

            .text-center.mt-4 {
                margin-top: 10px !important;
                padding-top: 8px !important;
            }

            .text-center.mt-4 small {
                font-size: 9px !important;
            }
        }

        .admit-card-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border: 3px solid #000;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .card-header-section {
            background: white;
            padding: 10px 10px;
            text-align: center;
            border-bottom: 3px solid #000;
        }

        .college-logo {
            width: 70px;
            height: 60px;
            background: #f8f9fa;
            border: 2px solid #000;
            margin: 0 auto 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
            color: #000;
        }

        .card-header-section h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
        }

        .card-header-section h2 {
            margin: 5px 0 0;
            font-size: 16px;
            font-weight: 500;
            color: #333;
        }

        .admit-card-title {
            background: #000;
            padding: 10px;
            text-align: center;
        }

        .admit-card-title h3 {
            margin: 0;
            color: white;
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .card-body-section {
            padding: 20px;
        }

        .student-photo {
            width: 125px;
            height: 145px;
            border: 2px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            font-size: 50px;
            color: #666;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table tr {
            border-bottom: 1px solid #dee2e6;
        }

        .info-table tr:last-child {
            border-bottom: none;
        }

        .info-table td {
            padding: 8px;
            font-size: 14px;
        }

        .info-table td:first-child {
            font-weight: 600;
            color: #000;
            width: 40%;
        }

        .info-table td:last-child {
            color: #000;
            font-weight: 500;
        }

        .exam-schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            border: 2px solid #000;
        }

        .exam-schedule-table th {
            background: #000;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: 600;
            border: 1px solid #000;
            font-size: 12px;
            text-transform: uppercase;
        }

        .exam-schedule-table td {
            padding: 8px;
            border: 1px solid #000;
            font-size: 12px;
            color: #000;
        }

        .exam-schedule-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .exam-schedule-table tbody tr:hover {
            background: #e9ecef;
        }

        .exam-details-section {
            padding: 10px 0;
            margin: 15px 0;
        }

        .exam-details-section h4 {
            color: #000;
            margin-bottom: 15px;
            font-weight: bold;
            font-size: 18px;
            text-transform: uppercase;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
        }

        .venue-section {
            border: 2px solid #000;
            padding: 12px;
            margin: 15px 0;
            background: white;
        }

        .venue-section h5 {
            color: #000;
            margin-bottom: 10px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 16px;
        }

        .venue-section p {
            color: #000;
            font-size: 14px;
        }

        .instructions-section {
            border: 2px solid #000;
            padding: 12px;
            margin: 15px 0;
            background: white;
        }

        .instructions-section h5 {
            color: #000;
            margin-bottom: 10px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 16px;
        }

        .instructions-section ul {
            margin: 0;
            padding-left: 20px;
            color: #000;
        }

        .instructions-section li {
            margin-bottom: 3px;
            font-size: 12px;
        }

        .signature-section {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .signature-box {
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #333;
            width: 200px;
            margin: 1.5px auto;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(0, 0, 0, 0.03);
            font-weight: bold;
            pointer-events: none;
            white-space: nowrap;
        }

        .exam-center-info {
            border: 2px solid #000;
            padding: 10px 15px;
            margin: 15px 0;
            background: #f8f9fa;
        }

        .exam-center-info p {
            margin: 5px 0;
            font-size: 14px;
            color: #000;
        }

        .exam-center-info strong {
            font-weight: 700;
        }

        .action-buttons {
            padding: 20px;
            background: #f8f9fa;
            text-align: center;
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .badge-custom {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .badge-published {
            background: #d4edda;
            color: #155724;
        }
    </style>
</head>

<body>
    <div class="admit-card-container">
        <!-- Header -->
        <div class="card-header-section">
            <img src="../assets/images/gen-admit-card/cimage-logo.png" alt="CIMAGE College" style="max-width: 100%; height: auto; max-height: 100px; scale: 1.1;" onerror="this.style.display='none'">
        </div>

        <!-- Title -->
        <div class="admit-card-title">
            <h3><?php echo strtoupper(htmlspecialchars($admit_card['exam_title'])); ?></h3>
        </div>

        <!-- Body -->
        <div class="card-body-section position-relative">
            <div class="watermark">CIMAGE COLLEGE</div>

            <div class="row">
                <div class="col-md-9">
                    <!-- Student Details -->
                    <table class="info-table" style="display: table;">
                        <tr>
                            <td>Candidate Name:</td>
                            <td><?php echo strtoupper(htmlspecialchars($student['name'])); ?></td>
                        </tr>
                        <tr>
                            <td>College ID:</td>
                            <td><?php echo htmlspecialchars($student['college_id'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td>Roll Number:</td>
                            <td><?php echo htmlspecialchars($student['roll_no'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td>Course:</td>
                            <td><?php echo htmlspecialchars($student['course_name']); ?></td>
                        </tr>
                        <tr>
                            <td>Batch:</td>
                            <td><?php echo htmlspecialchars($student['batch_name'] ?? 'All Batches'); ?></td>
                        </tr>
                        <tr>
                            <td>Session:</td>
                            <td><?php echo htmlspecialchars($student['session_name']); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-3 text-end">
                    <!-- Student Photo -->
                    <div class="student-photo" style="float: none; margin: 0 0 0 auto; display: flex;">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            </div>

            <!-- Exam Center Details -->
            <div class="exam-center-info">
                <p><strong>Examination:</strong> <?php echo htmlspecialchars($admit_card['exam_type']); ?></p>
                <p><strong>Centre Name:</strong> <?php echo htmlspecialchars($admit_card['centre_name']); ?></p>
                <p><strong>Centre Address:</strong> <?php echo htmlspecialchars($admit_card['centre_address']); ?></p>
                <p><strong>Centre Code:</strong> <?php echo htmlspecialchars($admit_card['centre_code']); ?></p>
            </div>

            <!-- Exam Schedule -->
            <div class="exam-details-section mt-4">
                <h4>Examination Schedule</h4>
                <table class="exam-schedule-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">S.No.</th>
                            <th style="width: 15%;">Date</th>
                            <th style="width: 10%;">Day</th>
                            <th style="width: 15%;">Time</th>
                            <th style="width: 15%;">Subject Code</th>
                            <th style="width: 30%;">Subject Name</th>
                            <th style="width: 10%;">Max Marks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($exam_schedule as $index => $subject): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo date('d-m-Y', strtotime($subject['date'])); ?></td>
                                <td><?php echo htmlspecialchars($subject['day']); ?></td>
                                <td><?php echo date('h:i A', strtotime($subject['start_time'])) . ' - ' . date('h:i A', strtotime($subject['end_time'])); ?></td>
                                <td><?php echo htmlspecialchars($subject['subject_code']); ?></td>
                                <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                <td><?php echo htmlspecialchars($subject['max_marks']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Important Note -->
            <div class="venue-section">
                <h5>Important Note</h5>
                <p class="mb-0"><strong>Reporting Time:</strong> <?php echo htmlspecialchars($admit_card['reporting_instructions'] ?? '30 minutes before exam. No entry after 15 minutes of exam start.'); ?></p>
            </div>

            <!-- Instructions -->
            <div class="instructions-section">
                <h5>Instructions for Candidates</h5>
                <ul>
                    <li>Candidates must bring this admit card and valid College ID to the examination centre.</li>
                    <li>Candidates should occupy their allotted seats as per the seating arrangement displayed at the centre.</li>
                    <li>Use of mobile phones, electronic gadgets is strictly prohibited inside the examination hall.</li>
                    <li>Candidates found using unfair means will be expelled and their result may be cancelled.</li>
                </ul>
            </div>

            <!-- Signatures -->
            <div class="signature-section">
                <div class="signature-box">
                    <div style="height: 30px;"></div>
                    <div class="signature-line"></div>
                    <p class="mb-0 mt-1"><strong>Candidate Signature</strong></p>
                </div>
                <div class="signature-box">
                    <img src="../assets/images/gen-admit-card/signature.png" alt="Signature" style="height: 60px; margin-bottom: 3px; scale: 2.0;" onerror="this.style.display='none'">
                    <div class="signature-line"></div>
                    <p class="mb-0 mt-1"><strong>Controller of Examinations</strong></p>
                </div>
            </div>

            <div class="text-center mt-3 pt-2" style="border-top: 2px solid #000;">
                <small style="color: #000; font-weight: 500; font-size: 10px;">
                    Computer-generated admit card. Contact: exam@cimagecollege.edu.in | Ph: 0612-2234567
                </small>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button class="btn btn-primary btn-lg" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Print Admit Card
            </button>
            <button class="btn btn-secondary btn-lg" onclick="window.location.href='dashboard.php'">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Student Admit Card Loaded');
        });
    </script>
</body>

</html>