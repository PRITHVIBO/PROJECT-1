<?php
require_once 'init.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In / Sign Up - TechForum</title>
    <!-- Connect your external CSS file -->
    <link rel="icon" href="assets/images/im.png" type="image/png">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: url("assets/images/dsi.jpg") center/cover fixed no-repeat;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Additional styles specific to PHP integration */
        .msg {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(102, 126, 234, 0.95);
            color: #fff;
            padding: 12px 24px;
            border-radius: 25px;
            font-weight: 600;
            z-index: 9999;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            animation: slideDown 0.5s ease;
        }

        .flash-message {
            position: fixed;
            top: 60px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(40, 167, 69, 0.95);
            color: #fff;
            padding: 12px 24px;
            border-radius: 25px;
            font-weight: 600;
            z-index: 9999;
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }

        /* Ensure proper form positioning */
        .form-container form {
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 40px;
            height: 100%;
            text-align: center;
        }

        .form-container h1 {
            font-weight: bold;
            margin: 0 0 20px 0;
            color: #333;
        }

        .form-container span {
            font-size: 12px;
            margin: 0 0 20px 0;
            color: #666;
        }

        /* Enhanced transition effects */
        .container {
            transition: transform 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .toggle-container {
            transition: transform 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .forgot-password {
            position: absolute;
            top: 0;
            height: 100%;
            transition: all 0.6s ease-in-out;
            left: 0;
            width: 100%;
            opacity: 0;
            z-index: -1;
            pointer-events: none;
            background-color: #fff;
        }

        .container.forgot .forgot-password {
            transform: translateX(0%);
            opacity: 1;
            z-index: 10;
            pointer-events: auto;
            animation: show 0.6s;
        }

        .container.forgot .sign-in {
            opacity: 0;
            pointer-events: none;
            z-index: -1;
        }

        .container.forgot .sign-up {
            opacity: 0;
            pointer-events: none;
            z-index: -1;
        }

        .container.forgot .toggle-container {
            opacity: 0;
            pointer-events: none;
            z-index: -1;
        }

        .message-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            padding: 0 40px;
        }

        .message-container p {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
            margin: 0 0 20px 0;
        }

        /* Override any conflicting styles for better PHP integration */
        .container button[type="submit"] {
            background-color: #3d13e2 !important;
            color: #fff !important;
            border: 1px solid transparent !important;
            cursor: pointer;
        }

        .container button[type="submit"]:hover {
            background-color: #2a0fb3 !important;
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 8px 20px rgba(61, 19, 226, 0.6);
        }

        /* Hover effects for interactive elements */
        .forgot-password-link:hover {
            background-color: rgba(102, 126, 234, 0.1) !important;
            color: #764ba2 !important;
        }
    </style>
</head>

<body>
    <!-- Floating logo (self-link) -->
    <a href="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'auth.php') ?>" title="Reload this page" style="position:fixed;top:10px;left:10px;z-index:10000;display:inline-flex;align-items:center;">
        <img src="assets/images/im.png" alt="Tech Forum" width="32" height="32" style="display:block;border-radius:6px;background:#fff;object-fit:contain;box-shadow:0 2px 8px rgba(0,0,0,.2);" />
    </a>
    <!-- PHP Messages -->
    <?php if ($msg): ?>
        <div class="msg"><?= h($msg) ?></div>
    <?php endif; ?>

    <?php flash_message(); ?>

    <div class="container" id="container">
        <!-- Sign Up Form -->
        <div class="form-container sign-up">
            <form action="auth/register.php" method="POST" autocomplete="off">
                <input type="hidden" name="register" value="1">
                <h1>Create Account</h1>
                <span>or use your email for registration</span>
                <input type="text" name="username" placeholder="Username" required maxlength="50" pattern="[a-zA-Z0-9_-]{3,20}" title="Username must be 3-20 characters">
                <input type="email" name="email" placeholder="Email" required maxlength="120">
                <input type="password" name="password" placeholder="Password" required minlength="6" maxlength="255">
                <div style="margin:10px 0 15px; text-align:left; width:100%; background:#fff8e1; color:#8a6d3b; border:1px solid #ffecb5; padding:10px 12px; border-radius:8px; font-size:12px; line-height:1.5;">
                    ⚠️ Please save your password securely. If you lose it, recovery may take days and you’ll need to contact an admin for approval.
                </div>
                <button type="submit" name="register">Sign Up</button>
            </form>
        </div>

        <!-- Sign In Form -->
        <div class="form-container sign-in">
            <form action="auth/login.php" method="POST" autocomplete="off">
                <input type="hidden" name="login" value="1">
                <h1>Sign In</h1>
                <span>or use your account</span>
                <input type="email" name="email" placeholder="Email" required maxlength="120">
                <input type="password" name="password" placeholder="Password" required>
                <a href="#" onclick="showForgotPassword()" style="
                    color: #667eea; 
                    font-size: 13px; 
                    text-decoration: none; 
                    font-weight: 600;
                    transition: all 0.3s ease;
                    padding: 4px 8px;
                    border-radius: 4px;
                " class="forgot-password-link">
                    🔐 Forgot your password?
                </a>
                <button type="submit" name="login">Sign In</button>
                <div style="text-align: center; margin-top: 15px;">
                    <button type="button" onclick="showAdminAccess()" style="
                        display: inline-block;
                        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
                        color: white;
                        padding: 8px 16px;
                        border: none;
                        border-radius: 20px;
                        text-decoration: none;
                        font-size: 12px;
                        font-weight: 600;
                        transition: transform 0.3s ease;
                        cursor: pointer;
                    " onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'">
                        🔒 Admin Access
                    </button>
                    <div style="margin-top:10px;">
                        <a href="index.php" style="
                            display: inline-block;
                            background: linear-gradient(135deg, #36d1dc 0%, #5b86e5 100%);
                            color: white;
                            padding: 8px 18px;
                            border: none;
                            border-radius: 20px;
                            text-decoration: none;
                            font-size: 12px;
                            font-weight: 600;
                            letter-spacing: .5px;
                            transition: transform 0.3s ease, box-shadow .3s ease;
                            box-shadow: 0 4px 12px rgba(91,134,229,.35);
                        " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 18px rgba(91,134,229,.5)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(91,134,229,.35)'">
                            🌐 Visit Tech Forum
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Forgot Password Form -->
        <div class="form-container forgot-password" id="forgotPassword">
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; padding: 0 40px; background-color: #fff;">
                <div style="text-align: center; max-width: 450px; width: 100%;">
                    <h1 style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 32px; font-weight: 600; color: #2a5298; margin-bottom: 15px;">🔑 Password Recovery</h1>
                    <p style="color: #667eea; font-weight: 500; margin-bottom: 30px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 18px; line-height: 1.4;">Don't worry! We'll help you regain access to your account.</p>

                    <!-- Step-by-step instructions -->
                    <div style="background: linear-gradient(135deg, #f8f9ff 0%, #e8f4ff 100%); padding: 25px; border-radius: 15px; margin-bottom: 30px; text-align: left; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.1);">
                        <h3 style="color: #667eea; margin: 0 0 20px 0; font-size: 20px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-weight: 600; text-align: center;">📋 How it works:</h3>
                        <div style="color: #495057; font-size: 16px; line-height: 1.8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                            <div style="margin-bottom: 15px; display: flex; align-items: flex-start;">
                                <span style="color: #667eea; font-weight: bold; margin-right: 12px; font-size: 18px;">1.</span>
                                <span>Click the button below to go to our password reset page</span>
                            </div>
                            <div style="margin-bottom: 15px; display: flex; align-items: flex-start;">
                                <span style="color: #667eea; font-weight: bold; margin-right: 12px; font-size: 18px;">2.</span>
                                <span>Enter your registered email address</span>
                            </div>
                            <div style="margin-bottom: 15px; display: flex; align-items: flex-start;">
                                <span style="color: #667eea; font-weight: bold; margin-right: 12px; font-size: 18px;">3.</span>
                                <span>Our admin team will review your request</span>
                            </div>
                            <div style="display: flex; align-items: flex-start;">
                                <span style="color: #667eea; font-weight: bold; margin-right: 12px; font-size: 18px;">4.</span>
                                <span>You'll receive new login credentials via our platform</span>
                            </div>
                        </div>
                    </div>

                    <div style="text-align: center; margin: 30px 0;">
                        <a href="password_reset.php" style="
                            display: inline-block;
                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                            color: white;
                            padding: 16px 32px;
                            border-radius: 30px;
                            text-decoration: none;
                            font-weight: 600;
                            font-size: 18px;
                            transition: all 0.3s ease;
                            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
                        " class="password-reset-btn" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(102, 126, 234, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 6px 20px rgba(102, 126, 234, 0.3)'">
                            🚀 Start Password Reset
                        </a>
                    </div>

                    <p style="font-size: 15px; color: #6c757d; margin-bottom: 30px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-weight: 500;">
                        ⏱️ <strong>Response time:</strong> Usually within 24 hours during business days
                    </p>

                    <button type="button" style="
                        background: #6c757d;
                        color: white;
                        border: none;
                        padding: 12px 24px;
                        border-radius: 25px;
                        cursor: pointer;
                        font-size: 16px;
                        font-weight: 600;
                        transition: all 0.3s ease;
                    " onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-2px)'" onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0)'" onclick="hideForgotPassword()">← Back to Sign In</button>
                </div>
            </div>
        </div>

        <!-- Toggle Container -->
        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Welcome Back!</h1>
                    <p>Enter your personal details to use all of site features</p>
                    <button class="hidden" id="login">Sign In</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Hello, Friend!</h1>
                    <p>Register with your personal details to use all of site features</p>
                    <button class="hidden" id="register">Sign Up</button>
                </div>
            </div>
        </div>
    </div>

    <!-- External JavaScript file -->
    <script src="assets/js/auth.js"></script>

    <!-- Debug script to test functionality -->
    <script>
        // Debug: Check if functions are loaded

        <?php require_once __DIR__ . '/includes/footer.php'; ?>

        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded');

            // Test if functions exist
            if (typeof showForgotPassword === 'function') {
                console.log('✅ showForgotPassword function loaded');
            } else {
                console.log('❌ showForgotPassword function missing');
            }

            if (typeof showAdminAccess === 'function') {
                console.log('✅ showAdminAccess function loaded');
            } else {
                console.log('❌ showAdminAccess function missing');
            }

            // Add click listeners as backup
            const forgotLink = document.querySelector('.forgot-password-link');
            if (forgotLink) {
                console.log('✅ Forgot password link found');
                forgotLink.addEventListener('click', function(e) {
                    console.log('Forgot password link clicked');
                });
            } else {
                console.log('❌ Forgot password link not found');
            }
        });
    </script>
</body>

</html>