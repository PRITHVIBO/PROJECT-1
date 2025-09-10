<?php
session_start();
require_once 'config/db.php';

$error = '';
$success = '';

// Handle password reset requests
if (isset($_POST['action']) && $_POST['action'] === 'request_reset' && isset($_POST['reset_email'])) {
    $email = trim($_POST['reset_email']);

    if (empty($email)) {
        $error = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // Check if there's already a pending request
                $stmt = $pdo->prepare("SELECT id FROM password_requests WHERE user_email = ? AND status = 'pending' AND requested_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
                $stmt->execute([$email]);
                $existing = $stmt->fetch();

                if (!$existing) {
                    $stmt = $pdo->prepare("INSERT INTO password_requests (user_email, user_id, reason, status) VALUES (?, ?, 'User requested password reset', 'pending')");
                    $stmt->execute([$email, $user['id']]);
                    $success = 'Your password reset request has been submitted. An admin will review it shortly and provide you with new credentials.';
                } else {
                    $error = 'You already have a pending password reset request. Please wait for admin response.';
                }
            } else {
                $error = 'No account found with that email address.';
            }
        } catch (PDOException $e) {
            $error = 'Request failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tech Forum - Password Reset Request</title>
    <link rel="icon" href="assets/images/im.png" type="image/png">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            /* Ensure footer stays at bottom */
        }

        main.page-main {
            flex: 1 0 auto;
            /* Take available height between header (none here) and footer */
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 12px;
        }

        .container {
            max-width: 450px;
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

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .icon {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
        }

        h1 {
            color: #2a5298;
            text-align: center;
            margin-bottom: 10px;
            font-weight: 600;
            font-size: 24px;
        }

        .subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 30px;
            font-size: 14px;
            line-height: 1.5;
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
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
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
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 8px;
            line-height: 1.4;
        }

        .info-box {
            background: #e8f4ff;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 14px;
            line-height: 1.5;
        }
    </style>
</head>

<body>
    <a href="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'password_reset.php') ?>" title="Reload this page" style="position:fixed;top:10px;left:10px;z-index:10000;display:inline-flex;align-items:center;">
        <img src="assets/images/im.png" alt="Tech Forum" width="28" height="28" style="display:block;border-radius:6px;background:#fff;object-fit:contain;box-shadow:0 2px 8px rgba(0,0,0,.2);" />
    </a>
    <main class="page-main">
        <div class="container">
            <div class="card">
                <div class="header">
                    <span class="icon">🔑</span>
                    <h1>Password Reset Request</h1>
                    <p class="subtitle">Submit a request to our admin team for password assistance</p>
                </div>

                <?php if ($error): ?>
                    <div class="error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="success"><?= htmlspecialchars($success) ?></div>
                <?php else: ?>
                    <div class="info-box">
                        <strong>How it works:</strong><br>
                        1. Enter your registered email address<br>
                        2. Our admin team will review your request<br>
                        3. You'll receive new login credentials via the platform<br>
                        4. Login with your new credentials and update your password
                    </div>

                    <form method="POST">
                        <input type="hidden" name="action" value="request_reset">
                        <div class="form-group">
                            <label for="reset_email">Your Registered Email:</label>
                            <input type="email" id="reset_email" name="reset_email" required placeholder="Enter your email address">
                            <div class="help-text">
                                Make sure to use the same email address you registered with.
                            </div>
                        </div>
                        <button type="submit">Submit Reset Request</button>
                    </form>
                <?php endif; ?>

                <div class="back-link">
                    <a href="auth.php">← Back to Sign In/Sign Up</a>
                </div>
            </div>
        </div>
    </main>
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>

</html>