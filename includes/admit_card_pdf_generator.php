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

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Generate admit card PDF file - Exact match to preview_admit_card.php design
 * @param array $student Student information
 * @param array $admit_card Admit card details
 * @param array $exam_schedule Exam schedule array
 * @return string|false Path to generated PDF file or false on failure
 */
function generateAdmitCardPDF($student, $admit_card, $exam_schedule)
{
    try {
        // Create new PDF document
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('CIMAGE College ERP');
        $pdf->SetAuthor('CIMAGE College');
        $pdf->SetTitle('Admit Card - ' . $admit_card['exam_title']);
        $pdf->SetSubject('Examination Admit Card');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);

        // Add a page
        $pdf->AddPage();

        // Get column visibility settings
        $showSubjectCode = $admit_card['show_subject_code'] ?? 0;
        $showMaxMarks = $admit_card['show_max_marks'] ?? 0;
        $showDay = $admit_card['show_day'] ?? 0;

        // Check if logo and signature exist
        $logoPath = __DIR__ . '/../assets/images/gen-admit-card/cimage-logo.png';
        $logoExists = file_exists($logoPath);

        $signaturePath = __DIR__ . '/../assets/images/gen-admit-card/signature.png';
        $signatureExists = file_exists($signaturePath);

        // Build HTML content - exact match to preview_admit_card.php
        $html = '
        <style>
            * { margin: 0; padding: 0; }
            body { font-family: helvetica, arial, sans-serif; }
            .admit-card-container { border: 3px solid #000; }
            .card-header-section {
                background: white;
                padding: 8px;
                text-align: center;
                border-bottom: 3px solid #000;
            }
            .admit-card-title {
                background: #000;
                padding: 8px;
                text-align: center;
            }
            .admit-card-title h3 {
                margin: 0;
                color: white;
                font-size: 16px;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            .card-body-section { padding: 12px; }
            .info-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 8px;
            }
            .info-table tr { border-bottom: 1px solid #dee2e6; }
            .info-table tr:last-child { border-bottom: none; }
            .info-table td {
                padding: 5px 6px;
                font-size: 10px;
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
            .student-photo {
                width: 90px;
                height: 110px;
                border: 2px solid #000;
                background: #f8f9fa;
                text-align: center;
                font-size: 30px;
                color: #666;
                padding-top: 30px;
            }
            .exam-center-info {
                border: 2px solid #000;
                padding: 6px 10px;
                margin: 8px 0;
                background: #f8f9fa;
            }
            .exam-center-info p {
                margin: 2px 0;
                font-size: 10px;
                color: #000;
            }
            .exam-center-info strong { font-weight: 700; }
            .exam-details-section {
                padding: 4px 0;
                margin: 8px 0;
            }
            .exam-details-section h4 {
                color: #000;
                margin: 6px 0;
                font-weight: bold;
                font-size: 12px;
                text-transform: uppercase;
                border-bottom: 2px solid #000;
                padding-bottom: 4px;
            }
            .exam-schedule-table {
                width: 100%;
                border-collapse: collapse;
                margin: 6px 0;
                border: 2px solid #000;
            }
            .exam-schedule-table th {
                background: #000;
                color: white;
                padding: 4px;
                text-align: left;
                font-weight: 600;
                border: 1px solid #000;
                font-size: 8px;
                text-transform: uppercase;
            }
            .exam-schedule-table td {
                padding: 4px;
                border: 1px solid #000;
                font-size: 8px;
                color: #000;
            }
            .exam-schedule-table tbody tr:nth-child(even) { background: #f8f9fa; }
            .venue-section {
                border: 2px solid #000;
                padding: 6px;
                margin: 6px 0;
                background: white;
            }
            .venue-section h5 {
                color: #000;
                margin: 0 0 4px 0;
                font-weight: bold;
                text-transform: uppercase;
                font-size: 11px;
            }
            .venue-section p {
                color: #000;
                font-size: 10px;
                margin: 0;
            }
            .instructions-section {
                border: 2px solid #000;
                padding: 6px;
                margin: 6px 0;
                background: white;
            }
            .instructions-section h5 {
                color: #000;
                margin: 0 0 4px 0;
                font-weight: bold;
                text-transform: uppercase;
                font-size: 11px;
            }
            .instructions-section ul {
                margin: 4px 0;
                padding-left: 12px;
                color: #000;
            }
            .instructions-section li {
                margin-bottom: 2px;
                font-size: 8px;
                line-height: 1.3;
            }
            .signature-line {
                border-top: 1px solid #333;
                margin: 2px auto;
                width: 130px;
            }
            .footer-text {
                text-align: center;
                margin-top: 6px;
                padding-top: 4px;
                border-top: 2px solid #000;
                font-size: 7px;
                color: #000;
                font-weight: 500;
            }
        </style>
        
        <div class="admit-card-container">';

        // Header with Logo
        if ($logoExists) {
            $html .= '
            <div class="card-header-section">
                <img src="' . $logoPath . '" style="max-height: 70px;" />
            </div>';
        } else {
            $html .= '
            <div class="card-header-section">
                <div style="font-size: 18px; font-weight: bold; color: #000; text-transform: uppercase;">CIMAGE COLLEGE</div>
                <div style="font-size: 9px; font-weight: 500; color: #333;">Affiliated to University | Approved by Govt.</div>
            </div>';
        }

        $html .= '
            
            <!-- Title -->
            <div class="admit-card-title">
                <h3>' . strtoupper(htmlspecialchars($admit_card['exam_title'])) . '</h3>
            </div>
            
            <!-- Body -->
            <div class="card-body-section">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 70%; vertical-align: top;">
                            <table class="info-table">
                                <tr>
                                    <td>Candidate Name:</td>
                                    <td>' . htmlspecialchars($student['name']) . '</td>
                                </tr>
                                <tr>
                                    <td>College ID:</td>
                                    <td>' . htmlspecialchars($student['college_id']) . '</td>
                                </tr>
                                <tr>
                                    <td>Roll Number:</td>
                                    <td>' . htmlspecialchars($student['roll_no']) . '</td>
                                </tr>
                                <tr>
                                    <td>Course:</td>
                                    <td>' . htmlspecialchars($admit_card['course_name']) . '</td>
                                </tr>
                                <tr>
                                    <td>Batch:</td>
                                    <td>' . htmlspecialchars($admit_card['batch_name'] ?? 'All Batches') . '</td>
                                </tr>
                                <tr>
                                    <td>Session:</td>
                                    <td>' . htmlspecialchars($admit_card['session_name']) . '</td>
                                </tr>
                            </table>
                        </td>
                        <td style="width: 30%; vertical-align: top; text-align: right;">
                            <div class="student-photo">☺</div>
                        </td>
                    </tr>
                </table>
                
                <!-- Exam Center Details -->
                <div class="exam-center-info">
                    <p><strong>Examination:</strong> ' . htmlspecialchars($admit_card['exam_type']) . '</p>
                    <p><strong>Centre Name:</strong> ' . htmlspecialchars($admit_card['centre_name']) . '</p>
                    <p><strong>Centre Address:</strong> ' . htmlspecialchars($admit_card['centre_address']) . '</p>
                </div>
                
                <!-- Exam Schedule -->
                <div class="exam-details-section">
                    <h4>Examination Schedule</h4>
                    <table class="exam-schedule-table">
                        <thead>
                            <tr>
                                <th style="width: 5%; text-align: center;">S.No.</th>
                                <th style="width: ' . ($showDay && $showSubjectCode && $showMaxMarks ? '15%' : ($showDay || $showSubjectCode || $showMaxMarks ? '20%' : '25%')) . '; text-align: left;">Date</th>';

        if ($showDay) {
            $html .= '<th style="width: 10%; text-align: left;">Day</th>';
        }

        $html .= '<th style="width: 15%; text-align: left;">Time</th>';

        if ($showSubjectCode) {
            $html .= '<th style="width: 15%; text-align: left;">Subject Code</th>';
        }

        $html .= '<th style="width: ' . ($showSubjectCode && $showMaxMarks ? '30%' : ($showSubjectCode || $showMaxMarks ? '40%' : '50%')) . '; text-align: left;">Subject Name</th>';

        if ($showMaxMarks) {
            $html .= '<th style="width: 10%; text-align: left;">Max Marks</th>';
        }

        $html .= '</tr>
                        </thead>
                        <tbody>';

        foreach ($exam_schedule as $index => $subject) {
            $html .= '<tr>
                <td style="text-align: center;">' . ($index + 1) . '</td>
                <td>' . date('d-m-Y', strtotime($subject['date'])) . '</td>';

            if ($showDay) {
                $html .= '<td>' . htmlspecialchars($subject['day']) . '</td>';
            }

            $html .= '<td>' . date('h:i A', strtotime($subject['start_time'])) . ' - ' . date('h:i A', strtotime($subject['end_time'])) . '</td>';

            if ($showSubjectCode) {
                $html .= '<td>' . htmlspecialchars($subject['subject_code']) . '</td>';
            }

            $html .= '<td>' . htmlspecialchars($subject['subject_name']) . '</td>';

            if ($showMaxMarks) {
                $html .= '<td>' . htmlspecialchars($subject['max_marks']) . '</td>';
            }

            $html .= '</tr>';
        }

        $html .= '</tbody>
                    </table>
                </div>
                
                <!-- Important Note -->
                <div class="venue-section">
                    <h5>Important Note</h5>
                    <ul style="margin-bottom: 0;">';

        $instructions = $admit_card['reporting_instructions'] ?? '• 30 minutes before exam. No entry after 15 minutes of exam start.';
        $lines = explode("\n", $instructions);
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                // Remove bullet character if exists, we'll add it via CSS
                $line = ltrim($line, '•');
                $line = trim($line);
                $html .= '<li>' . htmlspecialchars($line) . '</li>';
            }
        }

        $html .= '</ul>
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
                <div style="margin-top: 8px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 50%; text-align: center; vertical-align: bottom;">
                                <div style="height: 35px;"></div>
                                <div class="signature-line"></div>
                                <p style="margin: 2px 0; font-size: 9px; font-weight: bold;">Candidate Signature</p>
                            </td>
                            <td style="width: 50%; text-align: center; vertical-align: bottom;">';

        if ($signatureExists) {
            $html .= '<img src="' . $signaturePath . '" style="height: 45px; margin-bottom: 3px;" />';
        } else {
            $html .= '<div style="height: 35px;"></div>';
        }

        $html .= '
                                <div class="signature-line"></div>
                                <p style="margin: 2px 0; font-size: 9px; font-weight: bold;">Controller of Examinations</p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="footer-text">
                    Computer-generated admit card. Contact: exam@cimagecollege.edu.in | Ph: 0612-2234567
                </div>
            </div>
        </div>';

        // Write HTML content
        $pdf->writeHTML($html, true, false, true, false, '');

        // Create temp directory if it doesn't exist
        $tempDir = __DIR__ . '/../temp';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // Generate filename
        $filename = 'admit_card_' . preg_replace('/[^a-zA-Z0-9]/', '_', $student['roll_no']) . '_' . time() . '.pdf';
        $filepath = $tempDir . '/' . $filename;

        // Output PDF to file
        $pdf->Output($filepath, 'F');

        return $filepath;
    } catch (Exception $e) {
        error_log("PDF Generation Error: " . $e->getMessage());
        return false;
    }
}
