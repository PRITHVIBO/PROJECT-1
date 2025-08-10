<?php
require_once 'init.php';
require_once 'config/email_config.php';

// Simple admin authentication - in production, use proper admin authentication
if (!isset($_SESSION['admin_access'])) {
    if (isset($_POST['admin_key']) && $_POST['admin_key'] === 'techforum2025') {
        $_SESSION['admin_access'] = true;
    } else {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Admin Access - TechForum</title>
            <style>
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    margin: 0;
                    padding: 0;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .login-container {
                    background: rgba(255, 255, 255, 0.95);
                    backdrop-filter: blur(10px);
                    padding: 2rem;
                    border-radius: 15px;
                    box-shadow: 0 15px 40px rgba(0,0,0,0.1);
                    text-align: center;
                    max-width: 400px;
                    width: 100%;
                }
                .login-container h1 {
                    color: #333;
                    margin-bottom: 1rem;
                }
                .form-group {
                    margin: 1rem 0;
                    text-align: left;
                }
                .form-control {
                    width: 100%;
                    padding: 12px;
                    border: 2px solid #e1e5e9;
                    border-radius: 8px;
                    font-size: 1rem;
                    transition: border-color 0.3s ease;
                    box-sizing: border-box;
                }
                .form-control:focus {
                    outline: none;
                    border-color: #667eea;
                }
                .btn {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 12px 30px;
                    border: none;
                    border-radius: 8px;
                    font-size: 1rem;
                    font-weight: 600;
                    cursor: pointer;
                    transition: transform 0.2s ease;
                    width: 100%;
                }
                .btn:hover {
                    transform: translateY(-2px);
                }
            </style>
        </head>
        <body>
            <div class="login-container">
                <h1>🔐 Admin Panel Access</h1>
                <form method="POST">
                    <div class="form-group">
                        <input type="password" name="admin_key" class="form-control" placeholder="Enter admin key" required>
                    </div>
                    <button type="submit" class="btn">Access Admin Panel</button>
                </form>
                <p style="margin-top: 1rem; color: #666; font-size: 0.9rem;">
                    Default key: techforum2025
                </p>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Handle admin actions
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'clear_logs':
            file_put_contents(__DIR__ . '/reset_links.log', '');
            set_flash('Reset logs cleared successfully.');
            break;
        
        case 'test_email':
            $test_email = $_POST['test_email'] ?? '';
            if ($test_email) {
                try {
                    $test_link = SITE_URL . '/techforum/reset_password.php?token=TEST_TOKEN_' . time();
                    $result = send_password_reset_email($test_email, 'Test User', $test_link);
                    if ($result) {
                        set_flash('Test email sent successfully to ' . $test_email);
                    } else {
                        set_flash('Test email failed. Check debug information.');
                    }
                } catch (Exception $e) {
                    set_flash('Error: ' . $e->getMessage());
                }
            }
            break;
        
        case 'cleanup_old_tokens':
            try {
                $stmt = $pdo->prepare("DELETE FROM password_resets WHERE expires_at < NOW()");
                $deleted = $stmt->execute();
                $count = $stmt->rowCount();
                set_flash("Cleaned up $count expired tokens.");
            } catch (PDOException $e) {
                set_flash('Database error: ' . $e->getMessage());
            }
            break;
    }
    redirect('admin_panel.php');
}

// Get statistics
try {
    $stats = [
        'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'active_tokens' => $pdo->query("SELECT COUNT(*) FROM password_resets WHERE expires_at > NOW()")->fetchColumn(),
        'expired_tokens' => $pdo->query("SELECT COUNT(*) FROM password_resets WHERE expires_at <= NOW()")->fetchColumn(),
    ];
} catch (PDOException $e) {
    $stats = ['error' => $e->getMessage()];
}

// Get recent reset requests
try {
    $stmt = $pdo->prepare("SELECT email, token, expires_at, created_at FROM password_resets ORDER BY created_at DESC LIMIT 10");
    $stmt->execute();
    $recent_resets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recent_resets = [];
}

// Get log file content
$log_file = __DIR__ . '/reset_links.log';
$log_content = file_exists($log_file) ? file_get_contents($log_file) : '';
$log_lines = array_filter(array_reverse(explode("\n", $log_content)));
$log_lines = array_slice($log_lines, 0, 20); // Last 20 entries
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - TechForum</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .header h1 {
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .header p {
            color: #666;
        }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .card h2 {
            color: #333;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .stat-item {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            border: 2px solid #e9ecef;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            display: block;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #666;
            margin-top: 0.25rem;
        }
        
        .form-group {
            margin: 1rem 0;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
            margin: 5px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        .table th,
        .table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
            font-size: 0.9rem;
        }
        
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .log-content {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            font-family: 'Courier New', monospace;
            font-size: 0.8rem;
            max-height: 300px;
            overflow-y: auto;
            white-space: pre-wrap;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin: 1rem 0;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .navbar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            transition: background 0.3s ease;
        }
        
        .navbar a:hover {
            background: rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛡️ TechForum Admin Panel</h1>
            <p>Email System Management & Monitoring</p>
            
            <div class="navbar">
                <a href="index.php">🏠 Home</a>
                <a href="view_reset_links.php">🔗 Reset Links</a>
                <a href="debug_reset.php">🔧 Debug</a>
                <a href="?logout=1">🚪 Logout</a>
            </div>
        </div>
        
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['flash_message']) ?>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>
        
        <div class="grid">
            <!-- Statistics Card -->
            <div class="card">
                <h2>📊 System Statistics</h2>
                <?php if (isset($stats['error'])): ?>
                    <div class="alert alert-danger">
                        Database Error: <?= htmlspecialchars($stats['error']) ?>
                    </div>
                <?php else: ?>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <span class="stat-number"><?= $stats['total_users'] ?></span>
                            <div class="stat-label">Total Users</div>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?= $stats['active_tokens'] ?></span>
                            <div class="stat-label">Active Tokens</div>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?= $stats['expired_tokens'] ?></span>
                            <div class="stat-label">Expired Tokens</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Email Testing Card -->
            <div class="card">
                <h2>📧 Email Testing</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="test_email">
                    <div class="form-group">
                        <label for="test_email">Test Email Address:</label>
                        <input type="email" name="test_email" id="test_email" class="form-control" placeholder="test@example.com" required>
                    </div>
                    <button type="submit" class="btn btn-success">Send Test Email</button>
                </form>
                
                <hr style="margin: 1.5rem 0;">
                
                <div class="form-group">
                    <label>Quick Actions:</label>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="cleanup_old_tokens">
                        <button type="submit" class="btn">🧹 Cleanup Expired Tokens</button>
                    </form>
                    
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="clear_logs">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Clear all logs?')">🗑️ Clear Logs</button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Recent Password Resets -->
        <div class="card">
            <h2>🔄 Recent Password Reset Requests</h2>
            <?php if (empty($recent_resets)): ?>
                <p>No recent password reset requests.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Token</th>
                            <th>Created</th>
                            <th>Expires</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_resets as $reset): ?>
                            <tr>
                                <td><?= htmlspecialchars($reset['email']) ?></td>
                                <td style="font-family: monospace; font-size: 0.8rem;">
                                    <?= htmlspecialchars(substr($reset['token'], 0, 16)) ?>...
                                </td>
                                <td><?= date('M j, H:i', strtotime($reset['created_at'])) ?></td>
                                <td><?= date('M j, H:i', strtotime($reset['expires_at'])) ?></td>
                                <td>
                                    <?php if (strtotime($reset['expires_at']) > time()): ?>
                                        <span style="color: #28a745; font-weight: bold;">Active</span>
                                    <?php else: ?>
                                        <span style="color: #dc3545; font-weight: bold;">Expired</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Email Logs -->
        <div class="card">
            <h2>📋 Email Activity Log</h2>
            <?php if (empty($log_content)): ?>
                <p>No email activity recorded yet.</p>
            <?php else: ?>
                <div class="log-content">
<?php foreach ($log_lines as $line): ?>
<?= htmlspecialchars($line) . "\n" ?>
<?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_GET['logout'])): ?>
        <?php 
        unset($_SESSION['admin_access']);
        redirect('admin_panel.php');
        ?>
    <?php endif; ?>
</body>
</html>
