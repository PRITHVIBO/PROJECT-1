<?php
// Redirect to new secure admin access page
header('Location: admin_access.php');
exit;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tech Forum - Login & Password Reset</title>
    <link rel="icon" href="assets/images/im.png" type="image/png">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 800px;
            width: 100%;
            padding: 20px;
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 30px;
            margin-bottom: 20px;
        }

        .tabs {
            display: flex;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
        }

        .tab {
            padding: 15px 30px;
            cursor: pointer;
            border: none;
            background: none;
            font-size: 16px;
            font-weight: bold;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }

        input:focus {
            border-color: #667eea;
            outline: none;
        }

        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            width: 100%;
            transition: transform 0.2s ease;
        }

        button:hover {
            transform: translateY(-2px);
        }

        .error {
            background: #fee;
            color: #c33;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #fcc;
        }

        .success {
            background: #efe;
            color: #393;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #cfc;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
        }

        .help-text {
            font-size: 14px;
            color: #666;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <div class="tabs">
                <button class="tab active" onclick="showTab('admin')">Admin Login</button>
                <button class="tab" onclick="showTab('reset')">Forgot Password</button>
            </div>

            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <!-- Admin Login Tab -->
            <div id="admin-tab" class="tab-content active">
                <h2>Admin Login</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="admin_login">
                    <div class="form-group">
                        <label for="admin_username">Admin Username:</label>
                        <input type="text" id="admin_username" name="admin_username" required>
                    </div>
                    <div class="form-group">
                        <label for="admin_password">Admin Password:</label>
                        <input type="password" id="admin_password" name="admin_password" required>
                    </div>
                    <button type="submit">Login as Admin</button>
                    <div class="help-text">
                        Default admin: <strong>superadmin</strong> / <strong>admin123</strong>
                    </div>
                </form>
            </div>

            <!-- Password Reset Tab -->
            <div id="reset-tab" class="tab-content">
                <h2>Request Password Reset</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="request_reset">
                    <div class="form-group">
                        <label for="reset_email">Your Email Address:</label>
                        <input type="email" id="reset_email" name="reset_email" required>
                    </div>
                    <button type="submit">Send Reset Request to Admin</button>
                    <div class="help-text">
                        Your request will be sent to the admin for manual review. You'll receive a new password once approved.
                    </div>
                </form>
            </div>
        </div>

        <div class="back-link">
            <a href="index.php">← Back to Tech Forum</a>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });

            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Show selected tab content
            document.getElementById(tabName + '-tab').classList.add('active');

            // Add active class to clicked tab
            event.target.classList.add('active');
        }
    </script>
</body>

</html>