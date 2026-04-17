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
 * Comprehensive API Testing Tool
 * Tests all API endpoints in the system with role-based authentication
 */

session_start();

// Handle login
$loginError = '';
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $user_password = $_POST['password'];
    
    require_once "config/database.php";
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if (!$conn->connect_error) {
        $stmt = $conn->prepare("SELECT id, name, email, password_hash, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($user_password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                
                // If teacher, also get faculty_id
                if ($user['role'] === 'teacher') {
                    $faculty_stmt = $conn->prepare("SELECT id FROM faculties WHERE user_id = ?");
                    $faculty_stmt->bind_param("i", $user['id']);
                    $faculty_stmt->execute();
                    $faculty_result = $faculty_stmt->get_result();
                    
                    if ($faculty_result->num_rows === 1) {
                        $faculty = $faculty_result->fetch_assoc();
                        $_SESSION['faculty_id'] = $faculty['id'];
                    }
                    $faculty_stmt->close();
                }
                
                // If student, also get student_id
                if ($user['role'] === 'student') {
                    $student_stmt = $conn->prepare("SELECT id, college_id FROM students WHERE email = ?");
                    $student_stmt->bind_param("s", $user['email']);
                    $student_stmt->execute();
                    $student_result = $student_stmt->get_result();
                    
                    if ($student_result->num_rows === 1) {
                        $student = $student_result->fetch_assoc();
                        $_SESSION['student_id'] = $student['id'];
                        $_SESSION['college_id'] = $student['college_id'];
                    }
                    $student_stmt->close();
                }
            } else {
                $loginError = 'Invalid password';
            }
        } else {
            $loginError = 'User not found';
        }
        $stmt->close();
        $conn->close();
    } else {
        $loginError = 'Database connection failed';
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: test_api_all.php");
    exit;
}

// Show login form if not logged in
if (!isset($_SESSION['user_id'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>API Testing Tool - Login</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .login-container {
                background: white;
                border-radius: 15px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                padding: 40px;
                max-width: 500px;
                width: 100%;
            }
            h1 {
                color: #667eea;
                margin-bottom: 10px;
                text-align: center;
            }
            .subtitle {
                text-align: center;
                color: #6c757d;
                margin-bottom: 30px;
            }
            .role-cards {
                display: grid;
                gap: 15px;
                margin-bottom: 30px;
            }
            .role-card {
                border: 2px solid #e9ecef;
                border-radius: 8px;
                padding: 15px;
                cursor: pointer;
                transition: all 0.3s;
            }
            .role-card:hover {
                border-color: #667eea;
                background: #f8f9fa;
            }
            .role-card.selected {
                border-color: #667eea;
                background: #e8ebff;
            }
            .role-title {
                font-weight: 600;
                font-size: 1.1em;
                color: #212529;
                margin-bottom: 5px;
            }
            .role-creds {
                font-size: 0.85em;
                color: #6c757d;
                font-family: monospace;
            }
            .form-group {
                margin-bottom: 20px;
            }
            label {
                display: block;
                margin-bottom: 8px;
                color: #495057;
                font-weight: 600;
            }
            input {
                width: 100%;
                padding: 12px;
                border: 2px solid #e9ecef;
                border-radius: 8px;
                font-size: 16px;
                transition: border 0.3s;
            }
            input:focus {
                outline: none;
                border-color: #667eea;
            }
            .error {
                background: #fdf2f3;
                color: #dc3545;
                padding: 12px;
                border-radius: 8px;
                margin-bottom: 20px;
                border-left: 4px solid #dc3545;
            }
            .btn {
                width: 100%;
                padding: 14px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: transform 0.2s;
            }
            .btn:hover {
                transform: translateY(-2px);
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <h1>🔍 API Testing Tool</h1>
            <p class="subtitle">Login to test APIs</p>
            
            <?php if ($loginError): ?>
                <div class="error">❌ <?php echo htmlspecialchars($loginError); ?></div>
            <?php endif; ?>
            
            <div class="role-cards">
                <div class="role-card" onclick="fillCredentials('admin')">
                    <div class="role-title">👨‍💼 Admin</div>
                    <div class="role-creds">vk5045343@gmail.com / Admin@123</div>
                </div>
                <div class="role-card" onclick="fillCredentials('teacher')">
                    <div class="role-title">👨‍🏫 Teacher</div>
                    <div class="role-creds">vk5045343+teacher1@gmail.com / teacher</div>
                </div>
                <div class="role-card" onclick="fillCredentials('student')">
                    <div class="role-title">👨‍🎓 Student</div>
                    <div class="role-creds">vk5045343+stu@gmail.com / student</div>
                </div>
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" name="login" class="btn">🔐 Login & Test APIs</button>
            </form>
        </div>
        
        <script>
            function fillCredentials(role) {
                const credentials = {
                    admin: {
                        email: 'vk5045343@gmail.com',
                        password: 'Admin@123'
                    },
                    teacher: {
                        email: 'vk5045343+teacher1@gmail.com',
                        password: 'teacher'
                    },
                    student: {
                        email: 'vk5045343+stu@gmail.com',
                        password: 'student'
                    }
                };
                
                document.getElementById('email').value = credentials[role].email;
                document.getElementById('password').value = credentials[role].password;
                
                // Visual feedback
                document.querySelectorAll('.role-card').forEach(card => {
                    card.classList.remove('selected');
                });
                event.currentTarget.classList.add('selected');
            }
        </script>
    </body>
    </html>
    <?php
    exit;
}

require_once "config/database.php";

// Function to test API endpoint
function testAPI($name, $url, $method = 'GET', $data = null, $isJSON = true) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $isJSON ? json_encode($data) : http_build_query($data));
        }
    } elseif ($method === 'PUT' || $method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    if ($isJSON && ($method === 'POST' || $method === 'PUT' || $method === 'DELETE')) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $status = 'unknown';
    $message = '';
    $responseData = null;
    
    if ($error) {
        $status = 'error';
        $message = 'cURL Error: ' . $error;
    } elseif ($httpCode >= 200 && $httpCode < 300) {
        $responseData = @json_decode($response, true);
        if ($responseData !== null) {
            if (isset($responseData['success'])) {
                $status = $responseData['success'] ? 'success' : 'failed';
                $message = $responseData['message'] ?? 'No message';
            } else {
                $status = 'success';
                $message = 'Response received';
            }
        } else {
            $status = 'warning';
            $message = 'Non-JSON response: ' . substr($response, 0, 100);
        }
    } else {
        $status = 'error';
        $message = 'HTTP ' . $httpCode . ': ' . substr($response, 0, 200);
        $responseData = @json_decode($response, true);
    }
    
    return [
        'name' => $name,
        'url' => $url,
        'method' => $method,
        'status' => $status,
        'http_code' => $httpCode,
        'message' => $message,
        'response' => $responseData,
        'raw' => $status === 'warning' ? $response : null
    ];
}

// Get base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$baseUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);

// Define all API endpoints to test based on user role
$userRole = $_SESSION['role'];

$apiTests = [];

// Admin APIs - Only show for admin role
if ($userRole === 'admin') {
    $apiTests['Admin APIs'] = [
        ['name' => 'Dashboard Data', 'url' => $baseUrl . '/api/admin/dashboard_data.php'],
        ['name' => 'Batches - List', 'url' => $baseUrl . '/api/admin/batches.php'],
        ['name' => 'Courses - List', 'url' => $baseUrl . '/api/admin/courses.php'],
        ['name' => 'Sessions - List', 'url' => $baseUrl . '/api/admin/sessions.php'],
        ['name' => 'Subjects - List', 'url' => $baseUrl . '/api/admin/subjects.php'],
        ['name' => 'Teachers - List', 'url' => $baseUrl . '/api/admin/teachers.php'],
        ['name' => 'Students - List', 'url' => $baseUrl . '/api/admin/students.php'],
        ['name' => 'Subject Assignments', 'url' => $baseUrl . '/api/admin/subject_assignments.php'],
        ['name' => 'Announcements', 'url' => $baseUrl . '/api/admin/announcements.php'],
        ['name' => 'Attendance Reports', 'url' => $baseUrl . '/api/admin/attendance_reports.php'],
        ['name' => 'Admin Info', 'url' => $baseUrl . '/api/admin/admin_info.php'],
        ['name' => 'SMTP Settings - Get', 'url' => $baseUrl . '/api/admin/get_smtp_settings.php'],
        ['name' => 'Email Activity', 'url' => $baseUrl . '/api/admin/get_email_activity.php'],
    ];
}

// Common APIs - For all roles
$apiTests['Common APIs'] = [
    ['name' => 'Common Batches', 'url' => $baseUrl . '/api/common/batches.php'],
    ['name' => 'Common Courses', 'url' => $baseUrl . '/api/common/courses.php'],
];

// Teacher APIs - Only show for teacher role
if ($userRole === 'teacher') {
    $apiTests['Teacher APIs'] = [
        ['name' => 'Teacher Info', 'url' => $baseUrl . '/api/teacher/teacher_info.php'],
        ['name' => 'My Subjects', 'url' => $baseUrl . '/api/teacher/my_subjects.php'],
        ['name' => 'My Batches', 'url' => $baseUrl . '/api/teacher/my_batches.php'],
        ['name' => 'Today Classes', 'url' => $baseUrl . '/api/teacher/today_classes.php'],
        ['name' => 'Problem Reports', 'url' => $baseUrl . '/api/teacher/problem_reports.php'],
        ['name' => 'Announcements', 'url' => $baseUrl . '/api/teacher/announcements.php'],
        ['name' => 'Email Settings - Get', 'url' => $baseUrl . '/api/teacher/get_email_settings.php'],
        ['name' => 'SMTP Status', 'url' => $baseUrl . '/api/teacher/get_smtp_status.php'],
        ['name' => 'Exams List', 'url' => $baseUrl . '/api/teacher/exams.php'],
        ['name' => 'Question Bank', 'url' => $baseUrl . '/api/teacher/question_bank.php'],
    ];
}

// Student APIs - Only show for student role
if ($userRole === 'student') {
    $apiTests['Student APIs'] = [
        ['name' => 'Student Info', 'url' => $baseUrl . '/api/student/student_info.php'],
        ['name' => 'My Subjects', 'url' => $baseUrl . '/api/student/my_subjects.php'],
        ['name' => 'Recent Attendance', 'url' => $baseUrl . '/api/student/recent_attendance.php'],
        ['name' => 'Attendance Summary', 'url' => $baseUrl . '/api/student/attendance_summary.php'],
        ['name' => 'Detailed Attendance', 'url' => $baseUrl . '/api/student/detailed_attendance.php'],
        ['name' => 'Announcements', 'url' => $baseUrl . '/api/student/announcements.php'],
        ['name' => 'Exams List', 'url' => $baseUrl . '/api/student/exams.php'],
        ['name' => 'Exam History', 'url' => $baseUrl . '/api/student/exam_history.php'],
        ['name' => 'Previous Problems', 'url' => $baseUrl . '/api/student/previous_problems.php'],
        ['name' => 'Admit Cards', 'url' => $baseUrl . '/api/student/get_admit_cards.php'],
    ];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Testing Tool - All Endpoints</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .session-info {
            background: #f8f9fa;
            padding: 15px 30px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .session-info strong {
            color: #495057;
        }
        
        .controls {
            padding: 20px 30px;
            background: #fff;
            border-bottom: 2px solid #e9ecef;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .content {
            padding: 30px;
        }
        
        .category {
            margin-bottom: 40px;
        }
        
        .category-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 1.3em;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .category-stats {
            font-size: 0.9em;
            opacity: 0.9;
        }
        
        .api-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 15px;
        }
        
        .api-card {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            transition: all 0.3s;
        }
        
        .api-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .api-card.success {
            border-left: 5px solid #28a745;
            background: #f1f9f3;
        }
        
        .api-card.error {
            border-left: 5px solid #dc3545;
            background: #fdf2f3;
        }
        
        .api-card.failed {
            border-left: 5px solid #ffc107;
            background: #fffbf0;
        }
        
        .api-card.warning {
            border-left: 5px solid #17a2b8;
            background: #f0f9fb;
        }
        
        .api-card.testing {
            border-left: 5px solid #6c757d;
            background: #f8f9fa;
        }
        
        .api-name {
            font-weight: 600;
            font-size: 1.1em;
            margin-bottom: 8px;
            color: #212529;
        }
        
        .api-url {
            font-size: 0.85em;
            color: #6c757d;
            word-break: break-all;
            margin-bottom: 10px;
        }
        
        .api-status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .status-success {
            background: #28a745;
            color: white;
        }
        
        .status-error {
            background: #dc3545;
            color: white;
        }
        
        .status-failed {
            background: #ffc107;
            color: #000;
        }
        
        .status-warning {
            background: #17a2b8;
            color: white;
        }
        
        .status-testing {
            background: #6c757d;
            color: white;
        }
        
        .api-message {
            font-size: 0.9em;
            color: #495057;
            margin-top: 8px;
            padding: 8px;
            background: white;
            border-radius: 4px;
        }
        
        .api-response {
            margin-top: 10px;
            max-height: 200px;
            overflow: auto;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            font-size: 0.85em;
            font-family: 'Courier New', monospace;
        }
        
        .summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .summary-item {
            background: rgba(255,255,255,0.2);
            padding: 20px;
            border-radius: 8px;
        }
        
        .summary-number {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .summary-label {
            font-size: 0.9em;
            opacity: 0.9;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            font-size: 1.2em;
            color: #6c757d;
        }
        
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 API Testing Tool</h1>
            <p>Comprehensive testing of all API endpoints</p>
        </div>
        
        <div class="session-info">
            <strong>Logged in as:</strong> 
            <?php 
            $roleIcon = ['admin' => '👨‍💼', 'teacher' => '👨‍🏫', 'student' => '👨‍🎓'];
            echo $roleIcon[$_SESSION['role']] . ' ' . $_SESSION['name']; 
            ?> 
            (<?php echo ucfirst($_SESSION['role']); ?>) | 
            <?php echo $_SESSION['email']; ?> | 
            User ID: <?php echo $_SESSION['user_id']; ?>
            <a href="?logout=1" style="float:right; color:#dc3545; text-decoration:none; font-weight:600;">🚪 Logout</a>
        </div>
        
        <div class="controls">
            <button class="btn btn-primary" onclick="runAllTests()">🚀 Run All Tests</button>
            <button class="btn btn-success" onclick="showSuccessOnly()">✅ Show Success Only</button>
            <button class="btn btn-danger" onclick="showErrorsOnly()">❌ Show Errors Only</button>
            <button class="btn btn-danger" onclick="copyAllErrors()" id="copyErrorsBtn" style="display:none;">📋 Copy All Errors</button>
            <button class="btn btn-primary" onclick="location.reload()">🔄 Refresh Page</button>
        </div>
        
        <div class="content">
            <div id="summary" style="display:none;"></div>
            <div id="loading" class="loading" style="display:none;">
                <div class="spinner"></div>
                Testing APIs... Please wait...
            </div>
            <div id="results"></div>
        </div>
    </div>
    
    <script>
        const apiTests = <?php echo json_encode($apiTests); ?>;
        let testResults = {};
        let allTestResults = [];
        
        async function testAPI(name, url, method = 'GET') {
            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    cache: 'no-cache'
                });
                
                const text = await response.text();
                let data;
                
                try {
                    data = JSON.parse(text);
                } catch(e) {
                    return {
                        name,
                        url,
                        method,
                        status: 'warning',
                        http_code: response.status,
                        message: 'Non-JSON response: ' + text.substring(0, 100),
                        response: null,
                        raw: text
                    };
                }
                
                return {
                    name,
                    url,
                    method,
                    status: data.success ? 'success' : 'failed',
                    http_code: response.status,
                    message: data.message || 'No message',
                    response: data,
                    raw: null
                };
                
            } catch(error) {
                return {
                    name,
                    url,
                    method,
                    status: 'error',
                    http_code: 0,
                    message: error.message,
                    response: null,
                    raw: null
                };
            }
        }
        
        async function runAllTests() {
            document.getElementById('loading').style.display = 'block';
            document.getElementById('results').innerHTML = '';
            document.getElementById('summary').style.display = 'none';
            document.getElementById('copyErrorsBtn').style.display = 'none';
            allTestResults = [];
            
            let totalSuccess = 0;
            let totalFailed = 0;
            let totalError = 0;
            let totalWarning = 0;
            
            for (const [category, tests] of Object.entries(apiTests)) {
                const categoryDiv = document.createElement('div');
                categoryDiv.className = 'category';
                categoryDiv.innerHTML = `
                    <div class="category-header">
                        <span>${category}</span>
                        <span class="category-stats" id="stats-${category.replace(/\s+/g, '-')}">Testing...</span>
                    </div>
                    <div class="api-grid" id="grid-${category.replace(/\s+/g, '-')}"></div>
                `;
                document.getElementById('results').appendChild(categoryDiv);
                
                const gridDiv = document.getElementById(`grid-${category.replace(/\s+/g, '-')}`);
                let catSuccess = 0, catFailed = 0, catError = 0, catWarning = 0;
                
                for (const test of tests) {
                    const cardId = `card-${Date.now()}-${Math.random()}`;
                    const card = document.createElement('div');
                    card.className = 'api-card testing';
                    card.id = cardId;
                    card.innerHTML = `
                        <div class="api-name">${test.name}</div>
                        <div class="api-url">${test.url}</div>
                        <span class="api-status status-testing">Testing...</span>
                    `;
                    gridDiv.appendChild(card);
                    
                    const result = await testAPI(test.name, test.url);
                    result.category = category;
                    allTestResults.push(result);
                    
                    if (result.status === 'success') catSuccess++;
                    else if (result.status === 'failed') catFailed++;
                    else if (result.status === 'error') catError++;
                    else if (result.status === 'warning') catWarning++;
                    
                    card.className = `api-card ${result.status}`;
                    card.innerHTML = `
                        <div class="api-name">${result.name}</div>
                        <div class="api-url">${result.url}</div>
                        <span class="api-status status-${result.status}">
                            ${result.status.toUpperCase()} (HTTP ${result.http_code})
                        </span>
                        <div class="api-message">${result.message}</div>
                        ${result.response ? `<div class="api-response">${JSON.stringify(result.response, null, 2)}</div>` : ''}
                        ${result.raw ? `<div class="api-response">${result.raw.substring(0, 500)}</div>` : ''}
                    `;
                }
                
                document.getElementById(`stats-${category.replace(/\s+/g, '-')}`).innerHTML = 
                    `✅ ${catSuccess} | ⚠️ ${catFailed} | ❌ ${catError} | ℹ️ ${catWarning}`;
                
                totalSuccess += catSuccess;
                totalFailed += catFailed;
                totalError += catError;
                totalWarning += catWarning;
            }
            
            document.getElementById('loading').style.display = 'none';
            
            // Show copy errors button if there are any errors
            if (totalError > 0 || totalFailed > 0 || totalWarning > 0) {
                document.getElementById('copyErrorsBtn').style.display = 'inline-block';
            }
            
            const summaryDiv = document.getElementById('summary');
            summaryDiv.style.display = 'block';
            summaryDiv.innerHTML = `
                <div class="summary">
                    <h2>Test Summary</h2>
                    <div class="summary-grid">
                        <div class="summary-item">
                            <div class="summary-number">${totalSuccess + totalFailed + totalError + totalWarning}</div>
                            <div class="summary-label">Total Tests</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-number">${totalSuccess}</div>
                            <div class="summary-label">Success ✅</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-number">${totalFailed}</div>
                            <div class="summary-label">Failed ⚠️</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-number">${totalError}</div>
                            <div class="summary-label">Errors ❌</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-number">${totalWarning}</div>
                            <div class="summary-label">Warnings ℹ️</div>
                        </div>
                    </div>
                </div>
            `;
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        function showSuccessOnly() {
            document.querySelectorAll('.api-card').forEach(card => {
                card.style.display = card.classList.contains('success') ? 'block' : 'none';
            });
        }
        
        function showErrorsOnly() {
            document.querySelectorAll('.api-card').forEach(card => {
                card.style.display = (card.classList.contains('error') || card.classList.contains('failed')) ? 'block' : 'none';
            });
        }
        
        function copyAllErrors() {
            const errors = allTestResults.filter(result => 
                result.status === 'error' || result.status === 'failed' || result.status === 'warning'
            );
            
            if (errors.length === 0) {
                alert('No errors found!');
                return;
            }
            
            let errorReport = '='.repeat(80) + '\n';
            errorReport += 'API TEST ERROR REPORT\n';
            errorReport += '='.repeat(80) + '\n';
            errorReport += 'Role: <?php echo strtoupper($_SESSION['role']); ?>\n';
            errorReport += 'User: <?php echo $_SESSION['name']; ?> (<?php echo $_SESSION['email']; ?>)\n';
            errorReport += 'Date: ' + new Date().toLocaleString() + '\n';
            errorReport += 'Total Errors: ' + errors.length + '\n';
            errorReport += '='.repeat(80) + '\n\n';
            
            errors.forEach((error, index) => {
                errorReport += `\n${'='.repeat(80)}\n`;
                errorReport += `ERROR #${index + 1}\n`;
                errorReport += `${'='.repeat(80)}\n`;
                errorReport += `Category: ${error.category}\n`;
                errorReport += `API Name: ${error.name}\n`;
                errorReport += `URL: ${error.url}\n`;
                errorReport += `Method: ${error.method}\n`;
                errorReport += `Status: ${error.status.toUpperCase()}\n`;
                errorReport += `HTTP Code: ${error.http_code}\n`;
                errorReport += `Message: ${error.message}\n`;
                
                if (error.response) {
                    errorReport += `\nResponse:\n${JSON.stringify(error.response, null, 2)}\n`;
                }
                
                if (error.raw) {
                    errorReport += `\nRaw Response:\n${error.raw.substring(0, 1000)}\n`;
                }
                
                errorReport += `\n${'-'.repeat(80)}\n`;
            });
            
            errorReport += '\n' + '='.repeat(80) + '\n';
            errorReport += 'END OF ERROR REPORT\n';
            errorReport += '='.repeat(80) + '\n';
            
            // Copy to clipboard
            navigator.clipboard.writeText(errorReport).then(() => {
                const btn = document.getElementById('copyErrorsBtn');
                const originalText = btn.innerHTML;
                btn.innerHTML = '✅ Copied!';
                btn.style.background = '#28a745';
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.style.background = '';
                }, 2000);
            }).catch(err => {
                alert('Failed to copy to clipboard. Please try again.');
                console.error('Copy error:', err);
            });
        }
        
        // Auto-run tests on page load
        window.onload = function() {
            runAllTests();
        };
    </script>
</body>
</html>
