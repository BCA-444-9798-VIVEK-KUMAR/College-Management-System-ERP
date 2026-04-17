<?php
// Start session
session_start();

// Check if user is logged in and redirect to their dashboard
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: admin/dashboard.php");
            exit;
        case 'teacher':
            header("Location: teacher/dashboard.php");
            exit;
        case 'student':
            header("Location: student/dashboard.php");
            exit;
        default:
            session_destroy();
            header("Location: auth/login.php");
            exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COLLEGE ERP | Next-Gen ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* --- CORE VARIABLES --- */
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --accent: #f472b6;
            --accent-deep: #ec4899;
            --glass-bg: rgba(255, 255, 255, .65);
            --glass-border: rgba(255, 255, 255, .45);
            --text-dark: #0f172a;
            --text-light: #64748b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }

        /* Animated mesh background */
        body {
            background: #f0f4ff;
            background-image:
                radial-gradient(ellipse 80% 50% at  20% 40%, rgba(99,102,241,.12) 0%, transparent 70%),
                radial-gradient(ellipse 60% 55% at  80% 20%, rgba(236,72,153,.10) 0%, transparent 70%),
                radial-gradient(ellipse 70% 60% at  50% 80%, rgba(56,189,248,.08) 0%, transparent 70%);
            color: var(--text-dark);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        a { text-decoration: none; transition: .3s; }

        /* --- UTILITIES --- */
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(14px) saturate(1.5);
            -webkit-backdrop-filter: blur(14px) saturate(1.5);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(31,38,135,.06);
            transition: all .35s ease;
        }
        .glass-card:hover {
            box-shadow: 0 14px 40px rgba(31,38,135,.10);
            transform: translateY(-4px);
        }
        .badge {
            display: inline-block; padding: 6px 14px; border-radius: 50px;
            font-size: .82rem; font-weight: 600; margin-right: 6px;
            backdrop-filter: blur(6px);
        }
        .bg-php   { background: rgba(119,123,180,.85); color: white; }
        .bg-mysql  { background: rgba(68,121,161,.85); color: white; }
        .bg-boot   { background: rgba(121,82,179,.85); color: white; }

        /* --- NAVBAR --- */
        nav { padding: 20px 0; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.5rem; font-weight: 800; color: var(--primary-dark); letter-spacing: -.3px; }
        .logo span { color: var(--accent-deep); }
        .nav-btn {
            padding: 10px 24px; background: var(--primary); color: white;
            border-radius: 10px; font-weight: 600;
            box-shadow: 0 4px 14px rgba(99,102,241,.35);
            transition: all .3s ease;
        }
        .nav-btn:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 8px 20px rgba(99,102,241,.4); }

        /* --- HERO --- */
        .hero {
            min-height: 100vh; display: flex; align-items: center;
            text-align: center; position: relative; padding: 20px 0 120px;
        }
        .hero .container { width: 100%; }
        .hero h1 {
            font-size: 3.8rem; line-height: 1.08; margin-bottom: 22px;
            background: linear-gradient(135deg, var(--primary), var(--accent-deep), #38bdf8);
            background-size: 200% 200%;
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            animation: heroGrad 6s ease infinite;
            letter-spacing: -.5px;
        }
        @keyframes heroGrad {
            0%,100% { background-position: 0% 50%; }
            50%     { background-position: 100% 50%; }
        }
        .hero p { font-size: 1.18rem; color: var(--text-light); max-width: 720px; margin: 0 auto 32px; line-height: 1.65; }
        .tech-stack { margin-bottom: 30px; }

        .hero-image { margin-top: 50px; position: relative; }
        .mockup {
            width: 100%; max-width: 900px; border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0,0,0,.12); border: 3px solid white;
        }

        .login-btn-main {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 16px 44px; border-radius: 50px; font-size: 1.1rem; font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 8px 24px rgba(99,102,241,.4);
            transition: all .35s ease;
            position: relative; overflow: hidden;
        }
        .login-btn-main::before {
            content: '';
            position: absolute; top: 0; left: -75%;
            width: 50%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.2), transparent);
            transform: skewX(-20deg);
            animation: btnShimmer 3.5s ease-in-out infinite;
        }
        @keyframes btnShimmer {
            0%   { left: -75%; }
            100% { left: 125%; }
        }
        .login-btn-main:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 32px rgba(99,102,241,.5);
        }

        /* --- SCROLL INDICATOR --- */
        .scroll-indicator {
            position: absolute; bottom: 80px; left: 50%;
            transform: translateX(-50%);
            display: flex; flex-direction: column; align-items: center; gap: 8px;
            color: var(--primary); font-size: 1rem; font-weight: 600;
            animation: bounce 2s infinite; cursor: pointer; transition: all .3s;
        }
        .scroll-indicator:hover { color: var(--primary-dark); transform: translateX(-50%) scale(1.1); }
        .scroll-indicator i { font-size: 2rem; }
        @keyframes bounce {
            0%,20%,50%,80%,100% { transform: translateX(-50%) translateY(0); }
            40%  { transform: translateX(-50%) translateY(-10px); }
            60%  { transform: translateX(-50%) translateY(-5px); }
        }

        /* --- SHOWCASE ZIG‑ZAG --- */
        .dashboard-showcase { margin-top: 80px; display: flex; flex-direction: column; gap: 100px; padding-bottom: 80px; }
        .showcase-row { display: flex; align-items: center; gap: 60px; }
        .showcase-row.reverse { flex-direction: row-reverse; }
        .showcase-image-container { flex: 1; position: relative; }
        .showcase-image {
            width: 100%; height: 400px;
            background-size: cover; background-position: center;
            border-radius: 24px;
            box-shadow: 0 20px 48px rgba(0,0,0,.12);
            border: 3px solid rgba(255,255,255,.7);
            transition: transform .4s ease;
        }
        .showcase-row:hover .showcase-image { transform: scale(1.02); }
        .icon-overlay {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%);
            width: 96px; height: 96px;
            background: rgba(255,255,255,.7); backdrop-filter: blur(10px);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 2.8rem; color: var(--primary);
            box-shadow: 0 8px 28px rgba(0,0,0,.08); border: 1px solid var(--glass-border);
        }
        .showcase-text { flex: 1; text-align: left; }
        .showcase-text h3 { font-size: 2.4rem; margin-bottom: 18px; color: var(--primary-dark); line-height: 1.2; letter-spacing: -.3px; }
        .showcase-text p  { font-size: 1.12rem; color: var(--text-light); line-height: 1.7; margin-bottom: 24px; }
        .showcase-list li { list-style: none; margin-bottom: 11px; font-weight: 600; color: var(--text-dark); display: flex; align-items: center; }
        .showcase-list li i { color: var(--accent-deep); margin-right: 10px; }

        /* --- FEATURES GRID --- */
        .features { padding: 90px 0; background: white; }
        .section-title { text-align: center; margin-bottom: 60px; }
        .section-title h2 { font-size: 2.5rem; margin-bottom: 10px; letter-spacing: -.3px; }
        .section-title p { color: var(--text-light); font-size: 1.1rem; }

        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 28px; }

        .feature-box {
            padding: 32px; border-radius: 20px;
            transition: all .35s ease;
            border: 1px solid transparent;
        }
        .feature-box:hover {
            transform: translateY(-8px);
            background: white;
            border-color: rgba(99,102,241,.12);
            box-shadow: 0 12px 36px rgba(99,102,241,.08);
        }
        .feature-box h3 { font-size: 1.15rem; margin-bottom: 8px; color: var(--text-dark); }
        .feature-box p  { font-size: .95rem; color: var(--text-light); line-height: 1.6; }
        .icon {
            width: 56px; height: 56px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; margin-bottom: 18px;
        }
        .i-blue   { background: #eef2ff; color: var(--primary); }
        .i-pink   { background: #fdf2f8; color: var(--accent-deep); }
        .i-green  { background: #f0fdf4; color: #16a34a; }
        .i-orange { background: #fff7ed; color: #ea580c; }
        .i-purple { background: #faf5ff; color: #9333ea; }
        .i-teal   { background: #f0fdfa; color: #0d9488; }

        /* --- ROLE CARDS --- */
        .roles { padding: 80px 0; }
        .role-card {
            border: 1px solid #e2e8f0; border-radius: 18px; padding: 36px;
            text-align: left; position: relative; overflow: hidden;
            transition: all .35s ease;
            border-top: 4px solid transparent;
        }
        .role-card:hover {
            border-top-color: var(--primary);
            background: white;
            box-shadow: 0 12px 36px rgba(99,102,241,.08);
            transform: translateY(-4px);
        }
        .role-card h3 { font-size: 1.45rem; margin-bottom: 16px; display: flex; align-items: center; gap: 10px; }
        .role-list li { margin-bottom: 10px; color: var(--text-light); list-style: none; display: flex; align-items: center; }
        .role-list li i { color: var(--primary); margin-right: 10px; font-size: .9rem; }

        /* --- STATS BAR --- */
        .stats-bar {
            background: linear-gradient(135deg, var(--primary-dark), #312e81);
            color: white; padding: 30px 0; text-align: center;
            position: relative; overflow: hidden;
        }
        .stats-bar::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.05), transparent);
            animation: statsShine 4s ease-in-out infinite;
        }
        @keyframes statsShine {
            0%   { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        .stat-grid { display: flex; justify-content: space-around; flex-wrap: wrap; gap: 20px; position: relative; z-index: 1; }
        .stat-num  { font-size: 2rem; font-weight: 800; color: var(--accent); }

        /* --- FOOTER --- */
        footer { background: #0f172a; color: #94a3b8; padding: 44px 0 16px; text-align: center; }
        .developer-credit { margin-top: 30px; padding-top: 20px; border-top: 1px solid #1e293b; font-size: .9rem; }
        .developer-credit span { color: white; font-weight: 600; }

        /* --- RESPONSIVE --- */
        @media(max-width: 992px) {
            .hero h1 { font-size: 2.5rem; }
            .hero p  { font-size: 1rem; }
            .nav-btn { display: none; }
            .stat-grid { flex-direction: column; }

            .dashboard-showcase { margin-top: 40px; gap: 60px; padding-bottom: 40px; }
            .showcase-row, .showcase-row.reverse { flex-direction: column; gap: 30px; }
            .showcase-image-container { width: 100%; max-width: 100%; }
            .showcase-image { height: 250px; border-radius: 16px; border-width: 2px; }
            .icon-overlay { width: 76px; height: 76px; font-size: 2.1rem; }
            .showcase-text { text-align: center; padding: 0 10px; }
            .showcase-text h3 { font-size: 1.8rem; margin-bottom: 15px; }
            .showcase-text p  { font-size: 1rem; margin-bottom: 20px; }
            .showcase-list { display: inline-block; text-align: left; margin: 0 auto; }
            .showcase-list li { font-size: .95rem; }

            .grid { grid-template-columns: 1fr; gap: 20px; }
            .feature-box { padding: 25px; }
            .section-title h2 { font-size: 2rem; }
        }

        @media(max-width: 576px) {
            .hero h1 { font-size: 2rem; }
            .tech-stack { margin-bottom: 25px; }
            .badge { font-size: .75rem; padding: 5px 10px; }
            .login-btn-main { padding: 13px 30px; font-size: 1rem; }

            .scroll-indicator { bottom: 90px; font-size: .9rem; }
            .scroll-indicator i { font-size: 1.5rem; }

            .showcase-image { height: 200px; }
            .showcase-text h3 { font-size: 1.5rem; }
            .icon-overlay { width: 58px; height: 58px; font-size: 1.7rem; }

            .role-card { padding: 25px; }
            .stat-num { font-size: 1.5rem; }
        }
    </style>
</head>
<body>

    <div class="container">
        <nav>
            <div class="logo"><i class="fa-solid fa-graduation-cap"></i> CIMAGE <span>ERP</span></div>
            <a href="https://github.com/its-vivek-sharma/" class="nav-btn"><i class="fa-brands fa-github"></i> View on GitHub</a>
        </nav>
    </div>

    <section class="hero">
        <div class="container">
            <div class="tech-stack">
                <span class="badge bg-php"><i class="fa-brands fa-php"></i> PHP 7.4+</span>
                <span class="badge bg-mysql"><i class="fa-solid fa-database"></i> MySQL 8.0</span>
                <span class="badge bg-boot"><i class="fa-brands fa-bootstrap"></i> Bootstrap 5.3</span>
            </div>
            <h1>The Complete College<br>Management System</h1>
            <p>A comprehensive, web-based solution for College featuring attendance tracking, online examinations, admit card generation, and automated email notifications.</p>
            
            <div style="margin-bottom: 40px;">
                 <a href="auth/login.php" class="login-btn-main">
                    <i class="fa-solid fa-right-to-bracket"></i> Login to Portal
                </a>
            </div>
        </div>
        
        <div class="scroll-indicator" onclick="document.querySelector('.dashboard-showcase').scrollIntoView({behavior: 'smooth'});">
            <span>Explore More</span>
            <i class="fa-solid fa-chevron-down"></i>
        </div>
    </section>


    <section class="container dashboard-showcase">
        
        <div class="showcase-row">
            <div class="showcase-image-container">
                <div class="showcase-image" style="background-image: url('assets/images/index-page/admin_placeholder.jpg');"></div>
                <div class="icon-overlay"><i class="fa-solid fa-chart-line"></i></div>
            </div>
            <div class="showcase-text">
                <h3>Admin Panel</h3>
                <p>Complete control over institutional data. Manage courses, track global attendance trends, and oversee all system activities from a central command center.</p>
                <ul class="showcase-list">
                    <li><i class="fa-solid fa-circle-check"></i> Real-time Analytics & Reporting</li>
                    <li><i class="fa-solid fa-circle-check"></i> User & Role Management</li>
                    <li><i class="fa-solid fa-circle-check"></i> Publish Admit Cards Globally</li>
                </ul>
            </div>
        </div>

        <div class="showcase-row reverse">
            <div class="showcase-image-container">
                <div class="showcase-image" style="background-image: url('assets/images/index-page/teacher_placeholder.jpg');"></div>
                <div class="icon-overlay" style="color: var(--accent);"><i class="fa-solid fa-chalkboard-user"></i></div>
            </div>
            <div class="showcase-text">
                <h3>Teacher Panel</h3>
                <p>Streamline daily tasks. Mark attendance quickly, create timer-based online exams, and view detailed performance reports for your assigned batches.</p>
                <ul class="showcase-list">
                    <li><i class="fa-solid fa-circle-check"></i> Daily Attendance Marking</li>
                    <li><i class="fa-solid fa-circle-check"></i> Create Online Exams & Question Banks</li>
                    <li><i class="fa-solid fa-circle-check"></i> Batch-wise Student Reports</li>
                </ul>
            </div>
        </div>

        <div class="showcase-row">
            <div class="showcase-image-container">
                <div class="showcase-image" style="background-image: url('assets/images/index-page/student_placeholder.jpg"></div>
                <div class="icon-overlay" style="color: #16a34a;"><i class="fa-solid fa-user-graduate"></i></div>
            </div>
            <div class="showcase-text">
                <h3>Student Panel</h3>
                <p>A personalized space for learning. Students can check their attendance status, take scheduled exams securely, and download important documents.</p>
                <ul class="showcase-list">
                    <li><i class="fa-solid fa-circle-check"></i> View Attendance History</li>
                    <li><i class="fa-solid fa-circle-check"></i> Take Secure Online Exams</li>
                    <li><i class="fa-solid fa-circle-check"></i> Download Admit Cards instantly</li>
                </ul>
            </div>
        </div>

    </section>
    <section class="features">
        <div class="container">
            <div class="section-title">
                <h2>Powerful Features</h2>
                <p>Designed for Security, Speed, and Scalability</p>
            </div>
        <div class="grid">
            <div class="glass-card feature-box">
                <div class="icon i-blue"><i class="fa-solid fa-id-card"></i></div>
                <h3>Admit Card System</h3>
                <p>Auto-generate PDF admit cards with exam schedules. Includes email notifications and download tracking.</p>
            </div>
            <div class="glass-card feature-box">
                <div class="icon i-pink"><i class="fa-solid fa-laptop-code"></i></div>
                <h3>Online Exams</h3>
                <p>Timer-based exams with violation detection (tab switching/copy-paste prevention) and auto-grading.</p>
            </div>

             <div class="glass-card feature-box">
                <div class="icon i-teal"><i class="fa-solid fa-calendar-check"></i></div>
                <h3>Smart Attendance</h3>
                <p>Subject-wise tracking with a 15-day edit window for teachers. Auto-calculates monthly percentages.</p>
            </div>

            <div class="glass-card feature-box">
                <div class="icon i-orange"><i class="fa-solid fa-envelope-open-text"></i></div>
                <h3>Email Notifications</h3>
                <p>Integrated SMTP system sends alerts for low attendance, exam schedules, and OTP-based password resets.</p>
            </div>

            <div class="glass-card feature-box">
                <div class="icon i-purple"><i class="fa-solid fa-chart-pie"></i></div>
                <h3>Performance Analytics</h3>
                <p>Visual charts and graphs for student performance, batch-wise attendance trends, and exam results.</p>
            </div>

            <div class="glass-card feature-box">
                <div class="icon i-green"><i class="fa-solid fa-user-shield"></i></div>
                <h3>Security</h3>
                <p>Secure Role-based access login with Bcrypt hashing, CSRF protection, and distinct access levels for Admin, Teachers, and Students.</p>
            </div>

        </div>
    </section>

    <section class="roles">
        <div class="container">
            <div class="section-title">
                <h2>Tailored for Everyone</h2>
            </div>
            <div class="grid">
                <div class="role-card">
                    <h3><i class="fa-solid fa-user-tie" style="color: var(--primary);"></i> Admin</h3>
                    <ul class="role-list">
                        <li><i class="fa-solid fa-check"></i> Manage Courses & Sessions</li>
                        <li><i class="fa-solid fa-check"></i> Generate Admit Cards</li>
                        <li><i class="fa-solid fa-check"></i> View Attendance Analytics</li>
                        <li><i class="fa-solid fa-check"></i> Activity Logging</li>
                    </ul>
                </div>
                <div class="role-card">
                    <h3><i class="fa-solid fa-chalkboard-user" style="color: var(--accent);"></i> Teacher</h3>
                    <ul class="role-list">
                        <li><i class="fa-solid fa-check"></i> Mark Daily Attendance</li>
                        <li><i class="fa-solid fa-check"></i> Create Online Exams</li>
                        <li><i class="fa-solid fa-check"></i> Send Bulk Email Alerts</li>
                        <li><i class="fa-solid fa-check"></i> Export PDF Reports</li>
                    </ul>
                </div>
                <div class="role-card">
                    <h3><i class="fa-solid fa-user-graduate" style="color: #16a34a;"></i> Student</h3>
                    <ul class="role-list">
                        <li><i class="fa-solid fa-check"></i> View Attendance History</li>
                        <li><i class="fa-solid fa-check"></i> Take Timer-based Exams</li>
                        <li><i class="fa-solid fa-check"></i> Download Admit Cards</li>
                        <li><i class="fa-solid fa-check"></i> Submit Problem Reports</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="stats-bar">
        <div class="container stat-grid">
            <div>
                <div class="stat-num">v4.0.1</div>
                <div class="stat-label">Current Version</div>
            </div>
            <div>
                <div class="stat-num">100%</div>
                <div class="stat-label">Paperless Exams</div>
            </div>
            <div>
                <div class="stat-num">SECURE</div>
                <div class="stat-label">Role-Based Access</div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <h2>College Management System</h2>
            <p style="margin-top: 15px;">A project built with PHP, MySQL, and Passion.</p>
            
            <div class="developer-credit">
                <p>Made with <i class="fa-solid fa-heart" style="color: var(--accent);"></i> by <span>Vivek Kumar</span></p>
                <p style="font-size: 0.8rem; margin-top: 5px;">Licensed under Proprietary Software<br class="d-none d-md-inline">
                    <small>
                         This software is protected by copyright law. Unauthorized use, reproduction, or distribution is strictly prohibited.
                    </small></p>
            </div>
        </div>
    </footer>

</body>
</html>