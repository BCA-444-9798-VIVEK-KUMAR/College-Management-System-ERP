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


/**
 * Simple PDF Generator using FPDF-like approach
 * Generates actual PDF files without external dependencies
 */

class SimplePDF
{
    private $content = '';

    public function __construct()
    {
        // PDF Header
        $this->content = "%PDF-1.4\n";
    }

    public function addText($text)
    {
        $this->content .= $text;
    }

    public function output()
    {
        // This is a minimal PDF that will work
        $pdf_content = "%PDF-1.4
1 0 obj
<<
/Type /Catalog
/Pages 2 0 R
>>
endobj
2 0 obj
<<
/Type /Pages
/Kids [3 0 R]
/Count 1
>>
endobj
3 0 obj
<<
/Type /Page
/Parent 2 0 R
/MediaBox [0 0 612 792]
/Contents 4 0 R
/Resources <<
/Font <<
/F1 <<
/Type /Font
/Subtype /Type1
/BaseFont /Helvetica
>>
/F2 <<
/Type /Font
/Subtype /Type1
/BaseFont /Helvetica-Bold
>>
>>
>>
>>
endobj
4 0 obj
<<
/Length " . strlen($this->content) . "
>>
stream
" . $this->content . "
endstream
endobj
xref
0 5
0000000000 65535 f 
0000000009 00000 n 
0000000058 00000 n 
0000000115 00000 n 
0000000317 00000 n 
trailer
<<
/Size 5
/Root 1 0 R
>>
startxref
" . (317 + strlen($this->content)) . "
%%EOF";

        return $pdf_content;
    }
}

/**
 * PDFGenerator class for exam result certificates
 */
class PDFGenerator
{
    public static function generateExamResult($data)
    {
        // Extract data
        $student = $data['student'];
        $exam = $data['exam'];
        $result = $data['result'];

        // Calculate grade
        $percentage = $result['percentage'];
        $grade = 'F';
        if ($percentage >= 90) $grade = 'A+';
        elseif ($percentage >= 80) $grade = 'A';
        elseif ($percentage >= 70) $grade = 'B+';
        elseif ($percentage >= 60) $grade = 'B';
        elseif ($percentage >= 50) $grade = 'C+';
        elseif ($percentage >= 40) $grade = 'C';

        $resultStatus = $result['is_passed'] ? 'PASSED' : 'FAILED';
        $resultColor = $result['is_passed'] ? '#28a745' : '#dc3545';

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #0d6efd; padding-bottom: 20px; }
        .header h1 { color: #0d6efd; margin: 0; font-size: 32px; }
        .header p { color: #666; margin: 5px 0; }
        .certificate-title { text-align: center; font-size: 24px; color: #0d6efd; margin: 30px 0; font-weight: bold; }
        .result-badge { background: ' . $resultColor . '; color: white; padding: 15px 30px; border-radius: 8px; display: inline-block; margin: 20px 0; font-size: 20px; font-weight: bold; }
        .section { margin: 30px 0; }
        .section h2 { color: #0d6efd; border-bottom: 2px solid #0d6efd; padding-bottom: 10px; font-size: 18px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table td { padding: 12px; border: 1px solid #ddd; }
        .info-table td:first-child { font-weight: bold; width: 200px; background: #f8f9fa; color: #555; }
        .performance { text-align: center; margin: 30px 0; }
        .performance .grade { font-size: 72px; color: #0d6efd; font-weight: bold; margin: 20px 0; }
        .performance .marks { font-size: 24px; color: #666; }
        .footer { margin-top: 50px; padding-top: 20px; border-top: 2px solid #ddd; text-align: center; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CIMAGE COLLEGE</h1>
        <p>Patna, Bihar</p>
        <p>Online Examination System</p>
    </div>
    
    <div class="certificate-title">EXAM RESULT CERTIFICATE</div>
    
    <div style="text-align: center;">
        <div class="result-badge">' . $resultStatus . '</div>
    </div>
    
    <div class="section">
        <h2>Student Details</h2>
        <table class="info-table">
            <tr><td>Name</td><td>' . htmlspecialchars($student['name']) . '</td></tr>
            <tr><td>Roll Number</td><td>' . htmlspecialchars($student['roll_no']) . '</td></tr>
            <tr><td>Course</td><td>' . htmlspecialchars($student['course']) . '</td></tr>
            <tr><td>Session</td><td>' . htmlspecialchars($student['session']) . '</td></tr>
        </table>
    </div>
    
    <div class="section">
        <h2>Exam Details</h2>
        <table class="info-table">
            <tr><td>Exam Title</td><td>' . htmlspecialchars($exam['title']) . '</td></tr>
            <tr><td>Subject</td><td>' . htmlspecialchars($exam['subject']) . '</td></tr>
            <tr><td>Exam Type</td><td>' . strtoupper($exam['exam_type']) . '</td></tr>
            <tr><td>Date</td><td>' . date('d M Y', strtotime($result['submit_time'])) . '</td></tr>
            <tr><td>Duration</td><td>' . $exam['duration_minutes'] . ' minutes</td></tr>
        </table>
    </div>
    
    <div class="performance">
        <div class="grade">' . $grade . '</div>
        <div class="marks">
            <strong>' . $result['obtained_marks'] . '</strong> out of <strong>' . $exam['total_marks'] . '</strong> marks
        </div>
        <div class="marks" style="margin-top: 10px;">
            Percentage: <strong>' . number_format($percentage, 2) . '%</strong>
        </div>
    </div>
    
    <div class="section">
        <h2>Performance Summary</h2>
        <table class="info-table">
            <tr><td>Total Questions</td><td>' . ($result['total_questions'] ?? 'N/A') . '</td></tr>
            <tr><td>Correct Answers</td><td>' . ($result['correct_answers'] ?? 'N/A') . '</td></tr>
            <tr><td>Wrong Answers</td><td>' . ($result['wrong_answers'] ?? 'N/A') . '</td></tr>
            <tr><td>Time Taken</td><td>' . ($result['time_taken'] ?? 'N/A') . '</td></tr>
            <tr><td>Tab Switches</td><td>' . ($result['tab_switch_count'] ?? 0) . '</td></tr>
        </table>
    </div>
    
    <div class="footer">
        <p><strong>This is a computer-generated certificate and does not require a signature.</strong></p>
        <p>Generated on: ' . date('d M Y, h:i A') . '</p>
        <p>Cimage College - Online Examination System</p>
    </div>
</body>
</html>';

        return $html;
    }
}

/**
 * Generate HTML-based PDF (works in browsers)
 */
function generateHTMLPDF($student, $from_date, $to_date, $percentage, $present, $absent, $total, $records, $alert_label)
{

    $alert_color = '#17a2b8';
    if ($percentage < 50) {
        $alert_color = '#dc3545';
    } elseif ($percentage < 75) {
        $alert_color = '#ffc107';
    } elseif ($percentage < 85) {
        $alert_color = '#17a2b8';
    } else {
        $alert_color = '#28a745';
    }

    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #667eea; padding-bottom: 20px; }
        .header h1 { color: #667eea; margin: 0; }
        .header p { color: #666; margin: 5px 0; }
        .alert-badge { background: ' . $alert_color . '; color: white; padding: 10px 20px; border-radius: 5px; display: inline-block; margin: 20px 0; }
        .section { margin: 30px 0; }
        .section h2 { color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table td { padding: 8px; border-bottom: 1px solid #ddd; }
        .info-table td:first-child { font-weight: bold; width: 150px; color: #555; }
        .summary-box { display: flex; justify-content: space-around; margin: 20px 0; }
        .summary-item { text-align: center; padding: 20px; border: 2px solid #ddd; border-radius: 8px; min-width: 100px; }
        .summary-item h3 { margin: 0; font-size: 32px; }
        .summary-item p { margin: 5px 0 0; color: #666; }
        .present { border-color: #28a745; color: #28a745; }
        .absent { border-color: #dc3545; color: #dc3545; }
        .percentage { border-color: #667eea; background: #667eea; color: white; }
        .attendance-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .attendance-table th { background: #667eea; color: white; padding: 12px; text-align: left; }
        .attendance-table td { padding: 10px; border-bottom: 1px solid #ddd; }
        .attendance-table tr:nth-child(even) { background: #f8f9fa; }
        .status-present { color: #28a745; font-weight: bold; }
        .status-absent { color: #dc3545; font-weight: bold; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 2px solid #ddd; text-align: center; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CIMAGE COLLEGE</h1>
        <p>Attendance Management System</p>
        <div class="alert-badge">' . $alert_label . ': ' . $percentage . '% Attendance</div>
    </div>
    
    <div class="section">
        <h2>Student Information</h2>
        <table class="info-table">
            <tr><td>Name:</td><td>' . htmlspecialchars($student['name']) . '</td></tr>
            <tr><td>Roll Number:</td><td>' . htmlspecialchars($student['roll_no']) . '</td></tr>
            <tr><td>Course:</td><td>' . htmlspecialchars($student['course']) . '</td></tr>
            <tr><td>Batch:</td><td>' . htmlspecialchars($student['batch']) . '</td></tr>
            <tr><td>Session:</td><td>' . htmlspecialchars($student['session']) . '</td></tr>
            <tr><td>Report Period:</td><td>' . $from_date . ' to ' . $to_date . '</td></tr>
        </table>
    </div>
    
    <div class="section">
        <h2>Attendance Summary</h2>
        <div class="summary-box">
            <div class="summary-item present">
                <h3>' . $present . '</h3>
                <p>Present</p>
            </div>
            <div class="summary-item absent">
                <h3>' . $absent . '</h3>
                <p>Absent</p>
            </div>
            <div class="summary-item percentage">
                <h3>' . $percentage . '%</h3>
                <p>Attendance</p>
            </div>
        </div>
    </div>';

    if ($total > 0) {
        $html .= '
    <div class="section">
        <h2>Detailed Attendance Records</h2>
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Subject</th>
                    <th>Subject Code</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($records as $record) {
            $date_formatted = date('d M Y', strtotime($record['date']));
            $status_class = 'status-' . $record['status'];
            $status_text = ucfirst($record['status']);

            $html .= '
                <tr>
                    <td>' . $date_formatted . '</td>
                    <td>' . htmlspecialchars($record['subject']) . '</td>
                    <td>' . htmlspecialchars($record['subject_code']) . '</td>
                    <td class="' . $status_class . '">' . $status_text . '</td>
                </tr>';
        }

        $html .= '
            </tbody>
        </table>
    </div>';
    }

    $html .= '
    <div class="footer">
        <p><strong>CIMAGE College - Management System</strong></p>
        <p>Generated on: ' . date('d M Y, h:i A') . '</p>
        <p>This is an automated report. Please do not reply.</p>
    </div>
</body>
</html>';

    return $html;
}
