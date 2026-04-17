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

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect to appropriate dashboard
    header("Location: ../index.php");
    exit;
}

// Include header
$pageTitle = "Forgot Password";
include_once "../includes/header.php";
?>

<!-- Animated SVG Waves Background -->
<svg id="waves" viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice">
    <defs>
        <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#38bdf8" stop-opacity="0.45" />
            <stop offset="50%" stop-color="#818cf8" stop-opacity="0.45" />
            <stop offset="100%" stop-color="#22d3ee" stop-opacity="0.45" />
        </linearGradient>
    </defs>
    <rect width="1440" height="900" fill="#0f172a" />
    <path id="wave1" fill="url(#grad)" d="" opacity="0.8"></path>
    <path id="wave2" fill="url(#grad)" d="" opacity="0.5"></path>
    <path id="wave3" fill="url(#grad)" d="" opacity="0.35"></path>
</svg>

<div class="login-wrapper">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5 col-lg-4">
                <div class="glass-card">
                    <div class="glass-card-body">
                        <div class="text-center mb-4">
                            <div class="logo-icon">
                                <i class="fas fa-key"></i>
                            </div>
                            <h2 class="glass-title">Reset Password</h2>
                            <p class="glass-subtitle">CIMAGE - College Management System</p>
                        </div>

                        <div id="forgot-alert" class="alert-glass d-none"></div>

                        <!-- Step 1: Email Form -->
                        <div id="email-form-container">
                            <p class="text-center mb-4" style="color: #cbd5e1; font-size: 0.9rem;">Enter your email address to receive a password reset OTP</p>
                            <form id="email-form">
                                <div class="input-group-glass">
                                    <label for="email" class="glass-label"><i class="fas fa-envelope me-2"></i>Email Address</label>
                                    <input type="email" class="form-control-glass" id="email" name="email" placeholder="you@example.com" required autocomplete="email">
                                </div>
                                <button type="submit" class="btn-glass-login" id="send-otp-btn">
                                    <i class="fas fa-paper-plane me-2"></i>SEND OTP
                                </button>
                            </form>
                        </div>

                        <!-- Step 2: OTP Verification Form -->
                        <div id="otp-form-container" class="d-none">
                            <div class="alert-info-glass">
                                <i class="fas fa-info-circle me-2"></i>
                                <small>OTP has been sent to <strong id="sent-email"></strong></small>
                            </div>
                            <p class="text-center mb-4" style="color: #cbd5e1; font-size: 0.9rem;">Enter the OTP and your new password</p>
                            <form id="otp-form">
                                <input type="hidden" id="reset-email" name="email">
                                <div class="input-group-glass">
                                    <label for="otp" class="glass-label"><i class="fas fa-lock me-2"></i>OTP Code</label>
                                    <input type="text" class="form-control-glass" id="otp" name="otp" placeholder="Enter 6-digit OTP" maxlength="6" required autocomplete="off">
                                    <small style="color: #cbd5e1; font-size: 0.85rem; display: block; margin-top: 4px;">Valid for 15 minutes</small>
                                </div>
                                <div class="input-group-glass">
                                    <label for="new-password" class="glass-label"><i class="fas fa-key me-2"></i>New Password</label>
                                    <div class="password-input-wrapper">
                                        <input type="password" class="form-control-glass" id="new-password" name="new_password" placeholder="••••••••" required autocomplete="new-password">
                                        <span class="password-toggle-icon" onclick="togglePassword('new-password', 'toggleIcon1')">
                                            <i class="fas fa-eye" id="toggleIcon1"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="input-group-glass">
                                    <label for="confirm-password" class="glass-label"><i class="fas fa-check me-2"></i>Confirm Password</label>
                                    <div class="password-input-wrapper">
                                        <input type="password" class="form-control-glass" id="confirm-password" name="confirm_password" placeholder="••••••••" required autocomplete="new-password">
                                        <span class="password-toggle-icon" onclick="togglePassword('confirm-password', 'toggleIcon2')">
                                            <i class="fas fa-eye" id="toggleIcon2"></i>
                                        </span>
                                    </div>
                                </div>
                                <button type="submit" class="btn-glass-login" id="reset-btn">
                                    <i class="fas fa-sync me-2"></i>RESET PASSWORD
                                </button>
                            </form>
                        </div>

                        <div class="glass-footer" style="padding-top: 15px; margin-top: 20px;">
                            <a href="login.php" class="glass-link">
                                <i class="fas fa-arrow-left me-1"></i>Back to Login
                            </a>
                        </div>

                        <div class="glass-footer">
                            <small>&copy; <?php echo date('Y'); ?> CIMAGE College - All rights reserved</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Reset & Base */
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        height: 100vh; width: 100%;
        overflow-x: hidden;
        background: #0b0f19;
        color: #e2e8f0;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    /* SVG Waves */
    #waves { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; }

    /* Wrapper */
    .login-wrapper {
        min-height: 100vh;
        display: flex; align-items: center; justify-content: center;
        padding: 20px; position: relative; z-index: 1;
    }

    /* Glass Card */
    .glass-card {
        width: 100%; max-width: 420px;
        border-radius: 24px;
        background: rgba(255,255,255,.07);
        backdrop-filter: blur(20px) saturate(1.4);
        -webkit-backdrop-filter: blur(20px) saturate(1.4);
        border: 1px solid rgba(255,255,255,.12);
        box-shadow:
            0 0 0 1px rgba(99,102,241,.10),
            0 16px 56px rgba(0,0,0,.55),
            inset 0 1px 0 rgba(255,255,255,.08);
        overflow: hidden;
        animation: cardIn .55s ease;
    }
    @keyframes cardIn {
        from { opacity: 0; transform: translateY(24px) scale(.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    .glass-card-body { padding: 40px 36px; color: white; }

    /* Logo */
    .logo-icon {
        width: 82px; height: 82px;
        margin: 0 auto 20px;
        background: linear-gradient(135deg, rgba(99,102,241,.25), rgba(56,189,248,.2));
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 36px;
        backdrop-filter: blur(10px);
        animation: logoPulse 3s ease infinite;
        box-shadow: 0 0 30px rgba(99,102,241,.2);
        border: 1px solid rgba(255,255,255,.1);
    }
    @keyframes logoPulse {
        0%,100% { transform: scale(1); box-shadow: 0 0 30px rgba(99,102,241,.2); }
        50%     { transform: scale(1.04); box-shadow: 0 0 40px rgba(99,102,241,.3); }
    }

    .glass-title {
        margin-bottom: 6px; font-weight: 700;
        letter-spacing: 1.5px; font-size: 1.65rem;
        background: linear-gradient(135deg, #fff, #94a3b8);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }
    .glass-subtitle {
        font-size: .9rem; margin-bottom: 0;
        opacity: .8; font-weight: 400; color: #94a3b8;
    }

    /* Form */
    .input-group-glass { margin-bottom: 18px; text-align: left; }
    .glass-label {
        display: block; margin-bottom: 7px;
        font-size: .85rem; font-weight: 500; color: #94a3b8;
        letter-spacing: .3px;
    }

    .password-input-wrapper { position: relative; width: 100%; }
    .password-toggle-icon {
        position: absolute; right: 14px; top: 50%;
        transform: translateY(-50%);
        cursor: pointer; color: rgba(255,255,255,.45);
        font-size: 1.05rem;
        transition: all .25s ease;
        user-select: none; -webkit-tap-highlight-color: transparent;
        padding: 8px; z-index: 10;
    }
    .password-toggle-icon:hover { color: #818cf8; transform: translateY(-50%) scale(1.1); }
    .password-toggle-icon:active { transform: translateY(-50%) scale(.95); }

    .form-control-glass {
        width: 100%; padding: 13px 16px;
        border: 1.5px solid rgba(255,255,255,.12);
        border-radius: 12px;
        background: rgba(255,255,255,.06);
        color: white; outline: none;
        transition: all .3s ease;
        font-size: .95rem;
        -webkit-text-fill-color: white;
    }
    .password-input-wrapper .form-control-glass { padding-right: 45px; }
    .form-control-glass::placeholder { color: rgba(255,255,255,.35); }
    .form-control-glass:focus {
        background: rgba(255,255,255,.09);
        box-shadow: 0 0 0 3px rgba(99,102,241,.25), 0 0 20px rgba(99,102,241,.12);
        border-color: rgba(99,102,241,.5);
        transform: translateY(-1px);
    }

    /* Button */
    .btn-glass-login {
        width: 100%; padding: 14px;
        border: none; border-radius: 12px;
        background: linear-gradient(135deg, #6366f1, #818cf8);
        color: white; font-weight: 700; font-size: 1rem;
        cursor: pointer; transition: all .3s ease;
        margin-top: 12px; letter-spacing: .8px;
        box-shadow: 0 6px 22px rgba(99,102,241,.35);
        position: relative; overflow: hidden;
    }
    .btn-glass-login::before {
        content: '';
        position: absolute; top: 0; left: -75%;
        width: 50%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.15), transparent);
        transform: skewX(-20deg);
        animation: btnShimmer 3.5s ease-in-out infinite;
    }
    @keyframes btnShimmer {
        0%   { left: -75%; }
        100% { left: 125%; }
    }
    .btn-glass-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 32px rgba(99,102,241,.45);
        background: linear-gradient(135deg, #4f46e5, #6366f1);
    }
    .btn-glass-login:active { transform: translateY(0); }
    .btn-glass-login:disabled { opacity: .6; cursor: not-allowed; transform: none; }

    /* Alert */
    .alert-glass {
        padding: 12px 16px; border-radius: 12px;
        background: rgba(239,68,68,.12);
        border: 1px solid rgba(239,68,68,.25);
        color: #fca5a5; margin-bottom: 20px;
        backdrop-filter: blur(10px);
        animation: shake .5s ease; font-size: .88rem;
    }
    .alert-glass.alert-success {
        background: rgba(34,197,94,.12);
        border: 1px solid rgba(34,197,94,.25);
        color: #86efac;
    }
    .alert-info-glass {
        padding: 10px 14px; border-radius: 10px;
        background: rgba(99,102,241,.1);
        border: 1px solid rgba(99,102,241,.2);
        color: #c7d2fe; margin-bottom: 18px;
        backdrop-filter: blur(10px); font-size: .88rem;
    }
    @keyframes shake {
        0%,100% { transform: translateX(0); }
        25%     { transform: translateX(-6px); }
        75%     { transform: translateX(6px); }
    }

    /* Links */
    .glass-link {
        color: #c7d2fe; text-decoration: none;
        border-bottom: 1px dashed rgba(255,255,255,.25);
        transition: all .25s ease; font-size: .88rem;
    }
    .glass-link:hover { color: #818cf8; border-bottom-color: #818cf8; }

    /* Footer */
    .glass-footer {
        margin-top: 18px; padding-top: 16px;
        border-top: 1px solid rgba(255,255,255,.08);
        text-align: center; opacity: .7;
        font-size: .82rem; color: #94a3b8;
    }

    /* Responsive */
    @media (max-width: 576px) {
        .glass-card-body { padding: 28px 22px; }
        .glass-title { font-size: 1.4rem; }
        .logo-icon { width: 68px; height: 68px; font-size: 30px; }
        .password-toggle-icon { font-size: .95rem; right: 12px; padding: 10px; }
    }

    /* Force color scheme */
    @media (prefers-color-scheme: dark) {
        body { color-scheme: dark; }
        .form-control-glass { color: white !important; -webkit-text-fill-color: white !important; }
    }
    @media (prefers-color-scheme: light) {
        body { color-scheme: dark; }
        .form-control-glass { color: white !important; -webkit-text-fill-color: white !important; }
    }
</style>

<script>
    // SVG Wave Animation
    const w1 = document.getElementById('wave1');
    const w2 = document.getElementById('wave2');
    const w3 = document.getElementById('wave3');

    function wavePath(amplitude, wavelength, phase) {
        let d = 'M0 600 ';
        for (let x = 0; x <= 1440; x += 20) {
            const y = 600 + Math.sin((x + phase) / wavelength) * amplitude;
            d += `L${x} ${y} `;
        }
        d += 'L1440 900 L0 900 Z';
        return d;
    }

    let waveTime = 0;

    function animateWaves() {
        waveTime += 1.5;
        w1.setAttribute('d', wavePath(32, 280, waveTime * 3));
        w2.setAttribute('d', wavePath(44, 340, waveTime * 2));
        w3.setAttribute('d', wavePath(56, 420, waveTime * 1.4));
        requestAnimationFrame(animateWaves);
    }
    animateWaves();

    // Password Toggle Function
    function togglePassword(inputId, iconId) {
        const passwordInput = document.getElementById(inputId);
        const toggleIcon = document.getElementById(iconId);

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }

    // Form Handlers
    $(document).ready(function() {
        // Handle email form submission
        $("#email-form").submit(function(e) {
            e.preventDefault();

            // Get email
            var email = $("#email").val();

            // Disable button and show loading
            var $submitBtn = $("#send-otp-btn");
            var originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Sending OTP...');

            // Hide previous alerts
            $("#forgot-alert").addClass("d-none");

            // Send AJAX request
            $.ajax({
                type: "POST",
                url: "../api/auth/forgot_password_request.php",
                data: {
                    email: email
                },
                dataType: "json",
                encode: true,
            }).done(function(response) {
                console.log("OTP Response:", response);

                if (response.success) {
                    // Set email in hidden field and display
                    $("#reset-email").val(email);
                    $("#sent-email").text(email);

                    // Show OTP form - remove d-none first, then fade
                    $("#email-form-container").fadeOut(300, function() {
                        // Clear the success alert before showing OTP form
                        $("#forgot-alert").removeClass("alert-success alert-danger").addClass("d-none");

                        $("#otp-form-container").removeClass("d-none").hide().fadeIn(300);
                        // Focus on OTP input after fade completes
                        setTimeout(function() {
                            $("#otp").focus();
                        }, 100);
                    });
                } else {
                    // Show error message
                    $("#forgot-alert").removeClass("d-none alert-success").addClass("alert-glass").html(
                        '<i class="fas fa-exclamation-circle me-2"></i>' + response.error
                    );

                    // Re-enable button
                    $submitBtn.prop('disabled', false).html(originalText);
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                console.error("Response:", jqXHR.responseText);

                // Show error message
                $("#forgot-alert").removeClass("d-none alert-success").addClass("alert-glass").html(
                    '<i class="fas fa-exclamation-circle me-2"></i>An error occurred. Please try again.'
                );

                // Re-enable button
                $submitBtn.prop('disabled', false).html(originalText);
            });
        });

        // Handle OTP form submission
        $("#otp-form").submit(function(e) {
            e.preventDefault();

            // Get form data
            var email = $("#reset-email").val();
            var otp = $("#otp").val().trim();
            var newPassword = $("#new-password").val();
            var confirmPassword = $("#confirm-password").val();

            // Validate OTP format (6 digits)
            if (!/^\d{6}$/.test(otp)) {
                $("#forgot-alert").removeClass("d-none alert-success").addClass("alert-glass").html(
                    '<i class="fas fa-exclamation-circle me-2"></i>OTP must be 6 digits'
                );
                return;
            }

            // Validate passwords match
            if (newPassword !== confirmPassword) {
                $("#forgot-alert").removeClass("d-none alert-success").addClass("alert-glass").html(
                    '<i class="fas fa-exclamation-circle me-2"></i>Passwords do not match'
                );
                return;
            }

            // Validate password length
            if (newPassword.length < 6) {
                $("#forgot-alert").removeClass("d-none alert-success").addClass("alert-glass").html(
                    '<i class="fas fa-exclamation-circle me-2"></i>Password must be at least 6 characters'
                );
                return;
            }

            // Disable button and show loading
            var $submitBtn = $("#reset-btn");
            var originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Resetting Password...');

            // Hide previous alerts
            $("#forgot-alert").addClass("d-none");

            // Send AJAX request
            $.ajax({
                type: "POST",
                url: "../api/auth/verify_otp.php",
                data: {
                    email: email,
                    otp: otp,
                    new_password: newPassword
                },
                dataType: "json",
                encode: true,
            }).done(function(response) {
                console.log("Reset Response:", response);

                if (response.success) {
                    // Show success message
                    $("#forgot-alert").removeClass("d-none alert-glass").addClass("alert-glass alert-success").html(
                        '<i class="fas fa-check-circle me-2"></i>' + response.message
                    );

                    // Update button
                    $submitBtn.html('<i class="fas fa-check me-2"></i>Success! Redirecting...');

                    // Redirect to login after 2 seconds
                    setTimeout(function() {
                        window.location.href = "login.php";
                    }, 2000);
                } else {
                    // Show error message
                    $("#forgot-alert").removeClass("d-none alert-success").addClass("alert-glass").html(
                        '<i class="fas fa-exclamation-circle me-2"></i>' + response.error
                    );

                    // Re-enable button
                    $submitBtn.prop('disabled', false).html(originalText);

                    // If invalid OTP, focus on OTP field
                    if (response.error.toLowerCase().includes('otp')) {
                        $("#otp").focus().select();
                    }
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                console.error("Response:", jqXHR.responseText);

                // Show error message
                $("#forgot-alert").removeClass("d-none alert-success").addClass("alert-glass").html(
                    '<i class="fas fa-exclamation-circle me-2"></i>An error occurred. Please try again.'
                );

                // Re-enable button
                $submitBtn.prop('disabled', false).html(originalText);
            });
        });

        // Auto-format OTP input (only digits)
        $("#otp").on('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });

        // Add enter key support
        $("#email").keypress(function(e) {
            if (e.which == 13) {
                $("#email-form").submit();
                return false;
            }
        });
    });
</script>

<?php
// Include footer
include_once "../includes/footer.php";
?>