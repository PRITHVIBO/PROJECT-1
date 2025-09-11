<?php
session_start();

// Optional external admin configuration (not committed to VCS)
if (file_exists(__DIR__ . '/admin_config.php')) {
    require_once __DIR__ . '/admin_config.php';
}

// Define defaults only if not overridden by admin_config.php
if (!defined('ADMIN_ACCESS_TOKEN')) {
    define('ADMIN_ACCESS_TOKEN', 'TF_SECURE_2025_ADM_PORTAL_7f3e9a2cF1B8d4'); // default placeholder
}
if (!defined('ADMIN_USERNAME')) {
    define('ADMIN_USERNAME', 'techforum_admin');
}
if (!defined('ADMIN_PASSWORD')) {
    define('ADMIN_PASSWORD', 'SecureAdmin@2025!');
}

$error = '';
$token_verified = false;

// Check if token is already verified in session
if (isset($_SESSION['admin_token_verified']) && $_SESSION['admin_token_verified'] === true) {
    $token_verified = true;
}

// Handle token submission
if (isset($_POST['security_token']) && !empty($_POST['security_token'])) {
    $submitted_token = trim($_POST['security_token']);

    if ($submitted_token === ADMIN_ACCESS_TOKEN) {
        $_SESSION['admin_token_verified'] = true;
        $token_verified = true;
    } else {
        $error = 'Invalid security token. Access denied.';
        // Log failed attempts (optional)
        error_log('Failed admin access attempt from IP: ' . $_SERVER['REMOTE_ADDR'] . ' at ' . date('Y-m-d H:i:s'));
    }
}

// If token is verified, show admin login interface
if ($token_verified) {
    $login_error = '';

    // Handle admin login
    if (isset($_POST['action']) && $_POST['action'] === 'admin_login' && isset($_POST['admin_username']) && isset($_POST['admin_password'])) {
        $username = trim($_POST['admin_username']);
        $password = $_POST['admin_password'];

        if (empty($username) || empty($password)) {
            $login_error = 'Please fill in all fields.';
        } else {
            // Check permanent admin credentials
            if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
                // Set admin session
                $_SESSION['admin_id'] = 1;
                $_SESSION['admin_username'] = ADMIN_USERNAME;
                $_SESSION['admin_role'] = 'superadmin';
                $_SESSION['admin_permissions'] = 'full';
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_login_time'] = time();

                header('Location: admin_dashboard.php');
                exit;
            } else {
                $login_error = 'Invalid admin credentials.';
                error_log('Failed admin login attempt for username: ' . $username . ' from IP: ' . $_SERVER['REMOTE_ADDR'] . ' at ' . date('Y-m-d H:i:s'));
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tech Forum - Secure Admin Access</title>
    <link rel="icon" href="assets/images/im.png" type="image/png">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            /* allow footer at bottom */
        }

        /* Center the content area and leave space for footer */
        main.page-main {
            flex: 1 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 12px;
        }

        .container {
            max-width: 500px;
            width: 100%;
            padding: 20px;
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            padding: 40px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .security-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .security-icon {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
        }

        h1,
        h2 {
            color: #2a5298;
            text-align: center;
            margin-bottom: 25px;
            font-weight: 600;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 30px;
            font-size: 14px;
        }

        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            color: #2a5298;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"] {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
            background: white;
        }

        input:focus {
            border-color: #2a5298;
            outline: none;
            box-shadow: 0 0 0 3px rgba(42, 82, 152, 0.1);
        }

        button {
            background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%);
            color: white;
            padding: 14px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(42, 82, 152, 0.4);
        }

        button:active {
            transform: translateY(0);
        }

        .error {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            text-align: center;
        }

        .success {
            background: linear-gradient(135deg, #51cf66, #40c057);
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            text-align: center;
        }

        .back-link {
            text-align: center;
            margin-top: 25px;
        }

        .back-link a {
            color: #2a5298;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .token-help {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 12px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 13px;
        }

        .security-warning {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 8px;
            line-height: 1.4;
        }
    </style>
</head>

<body>
    <a href="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'admin_access.php') ?>" title="Reload this page" style="position:fixed;top:10px;left:10px;z-index:10000;display:inline-flex;align-items:center;">
        <img src="assets/images/im.png" alt="Tech Forum" width="28" height="28" style="display:block;border-radius:6px;background:#fff;object-fit:contain;box-shadow:0 2px 8px rgba(0,0,0,.2);" />
    </a>
    <main class="page-main">
        <div class="container">
            <div class="card">
                <?php if (!$token_verified): ?>
                    <div class="security-header">
                        <span class="security-icon">🔒</span>
                        <h1>Secure Admin Access</h1>
                        <p class="subtitle">Enter security token to access admin portal</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="error"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <div class="security-warning">
                        <strong>⚠️ Authorized Access Only:</strong> This portal is restricted to authorized administrators only. Unauthorized access attempts are logged and monitored.
                    </div>

                    <form method="POST">
                        <div class="form-group">
                            <label for="security_token">Security Token:</label>
                            <input type="password" id="security_token" name="security_token" required placeholder="Enter your security token">
                            <div class="help-text">Contact your system administrator if you don't have the security token.</div>
                        </div>
                        <button type="submit">Verify Token & Access Admin Portal</button>
                    </form>

                    <div class="token-help">
                        <strong>For developers:</strong> The security token is defined in the admin_access.php file. Change the ADMIN_ACCESS_TOKEN constant to your own secure value.
                    </div>

                <?php else: ?>
                    <div class="security-header">
                        <span class="security-icon">✅</span>
                        <h1>Admin Portal Access</h1>
                        <p class="subtitle">Security token verified - Enter admin credentials</p>
                    </div>

                    <?php if ($login_error): ?>
                        <div class="error"><?= htmlspecialchars($login_error) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="action" value="admin_login">
                        <div class="form-group">
                            <label for="admin_username">Admin Username:</label>
                            <input type="text" id="admin_username" name="admin_username" required placeholder="Enter admin username">
                        </div>
                        <div class="form-group">
                            <label for="admin_password">Admin Password:</label>
                            <input type="password" id="admin_password" name="admin_password" required placeholder="Enter admin password">
                        </div>
                        <button type="submit">Access Admin Dashboard</button>
                        <div class="help-text" style="margin-top: 15px; text-align: center;">
                            For security, rotate credentials regularly. (Defaults active if not overridden.)
                        </div>
                    </form>
                <?php endif; ?>

                <div class="back-link">
                    <a href="index.php">← Back to Tech Forum</a>
                </div>
            </div>
        </div>
    </main>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script>
        // No JavaScript needed - simplified admin login
    </script>
</body>

</html>