<?php
session_start();
require_once 'config/db.php';
require_once __DIR__ . '/includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: admin_access.php');
    exit;
}

$error = '';
$success = '';
$admin_id = $_SESSION['admin_id'];
$admin_role = $_SESSION['admin_role'];

// Handle password request actions
if (isset($_POST['action']) && $_POST['action'] === 'handle_request' && isset($_POST['request_id']) && isset($_POST['decision'])) {
    $request_id = (int)$_POST['request_id'];
    $decision = $_POST['decision'];

    try {
        if ($decision === 'approve') {
            $new_password = 'temp' . rand(1000, 9999);
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("SELECT user_id, user_email FROM password_requests WHERE id = ?");
            $stmt->execute([$request_id]);
            $request = $stmt->fetch();

            if ($request) {
                // Update user password and optionally mark password_changed_at if column exists
                try {
                    $uCols = $pdo->query("DESCRIBE users")->fetchAll(PDO::FETCH_COLUMN, 0);
                } catch (Throwable $te) {
                    $uCols = [];
                }
                if (in_array('password_changed_at', $uCols, true)) {
                    $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, password_changed_at = NOW() WHERE id = ?");
                    $stmt->execute([$password_hash, $request['user_id']]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                    $stmt->execute([$password_hash, $request['user_id']]);
                }

                // Update request status
                $stmt = $pdo->prepare("UPDATE password_requests SET status = 'approved', admin_id = ?, admin_response = ?, response_at = NOW(), new_password = ? WHERE id = ?");
                $stmt->execute([$admin_id, "Password reset approved. New temporary password provided.", $new_password, $request_id]);

                // Note: Email sending was removed per request.

                $success = "Password reset approved. Temporary password: <strong>$new_password</strong> (User: {$request['user_email']})";
            }
        } else {
            $reason = $_POST['reject_reason'] ?? 'Request rejected by admin';
            $stmt = $pdo->prepare("UPDATE password_requests SET status = 'rejected', admin_id = ?, admin_response = ?, response_at = NOW() WHERE id = ?");
            $stmt->execute([$admin_id, $reason, $request_id]);
            $success = 'Password reset request rejected.';
        }
    } catch (PDOException $e) {
        $error = 'Error processing request: ' . $e->getMessage();
    }
}

// Handle user ban/unban
if (isset($_POST['action']) && $_POST['action'] === 'ban_user' && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    $ban_reason = $_POST['ban_reason'] ?? 'Banned by admin';

    try {
        $stmt = $pdo->prepare("UPDATE users SET is_banned = 1, banned_by = ?, ban_reason = ?, banned_at = NOW() WHERE id = ?");
        $stmt->execute([$admin_id, $ban_reason, $user_id]);

        // Send message to user
        $stmt = $pdo->prepare("INSERT INTO admin_messages (from_admin_id, to_user_id, subject, message, message_type) VALUES (?, ?, 'Account Banned', ?, 'ban_notice')");
        $stmt->execute([$admin_id, $user_id, "Your account has been banned. Reason: $ban_reason",]);

        $success = 'User banned successfully.';
    } catch (PDOException $e) {
        $error = 'Error banning user: ' . $e->getMessage();
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'unban_user' && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];

    try {
        $stmt = $pdo->prepare("UPDATE users SET is_banned = 0, banned_by = NULL, ban_reason = NULL, banned_at = NULL WHERE id = ?");
        $stmt->execute([$user_id]);

        // Send message to user
        $stmt = $pdo->prepare("INSERT INTO admin_messages (from_admin_id, to_user_id, subject, message, message_type) VALUES (?, ?, 'Account Unbanned', 'Your account has been unbanned. You can now access the forum normally.', 'unban_notice')");
        $stmt->execute([$admin_id, $user_id]);

        $success = 'User unbanned successfully.';
    } catch (PDOException $e) {
        $error = 'Error unbanning user: ' . $e->getMessage();
    }
}

// Handle sending messages
if (isset($_POST['action']) && $_POST['action'] === 'send_message' && isset($_POST['to_user_id'])) {
    $to_user_id = (int)$_POST['to_user_id'];
    $subject = trim($_POST['message_subject']);
    $message = trim($_POST['message_content']);
    $message_type = $_POST['message_type'] ?? 'info';

    try {
        $stmt = $pdo->prepare("INSERT INTO admin_messages (from_admin_id, to_user_id, subject, message, message_type) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$admin_id, $to_user_id, $subject, $message, $message_type]);
        $success = 'Message sent successfully.';
    } catch (PDOException $e) {
        $error = 'Error sending message: ' . $e->getMessage();
    }
}

// Get statistics
try {
    $stats = [];

    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
    $stats['total_users'] = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) as banned_users FROM users WHERE is_banned = 1");
    $stats['banned_users'] = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) as total_posts FROM posts WHERE is_deleted = 0");
    $stats['total_posts'] = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) as pending_requests FROM password_requests WHERE status = 'pending'");
    $stats['pending_requests'] = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) as unread_messages FROM admin_messages WHERE is_read = 0");
    $stats['unread_messages'] = $stmt->fetchColumn();
} catch (PDOException $e) {
    $error = 'Error loading statistics.';
    $stats = ['total_users' => 0, 'banned_users' => 0, 'total_posts' => 0, 'pending_requests' => 0, 'unread_messages' => 0];
}

// Get pending password requests
try {
    $stmt = $pdo->query("SELECT pr.*, u.username FROM password_requests pr LEFT JOIN users u ON pr.user_id = u.id WHERE pr.status = 'pending' ORDER BY pr.requested_at DESC");
    $pending_requests = $stmt->fetchAll();
} catch (PDOException $e) {
    $pending_requests = [];
}

// Get all users for management
try {
    // Detect if last_login column exists
    $uCols = $pdo->query("DESCRIBE users")->fetchAll(PDO::FETCH_COLUMN, 0);
    $hasLastLogin = in_array('last_login', $uCols, true);
    $select = $hasLastLogin
        ? "id, username, email, created_at, last_login, is_banned, ban_reason"
        : "id, username, email, created_at, NULL AS last_login, is_banned, ban_reason";
    $stmt = $pdo->query("SELECT $select FROM users ORDER BY created_at DESC LIMIT 20");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Tech Forum</title>
    <link rel="icon" href="assets/images/im.png" type="image/png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-size: 1.5rem;
        }

        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            color: #666;
            margin-top: 0.5rem;
        }

        .section {
            background: white;
            margin-bottom: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .section-header {
            background: #f8f9fa;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #eee;
            font-weight: bold;
            color: #333;
        }

        .section-content {
            padding: 1.5rem;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }

        .alert.error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert.success {
            background: #efe;
            color: #393;
            border: 1px solid #cfc;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8f9fa;
            font-weight: bold;
        }

        .btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 5px;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background: #5a67d8;
        }

        .btn.danger {
            background: #e53e3e;
        }

        .btn.danger:hover {
            background: #c53030;
        }

        .btn.success {
            background: #38a169;
        }

        .btn.success:hover {
            background: #2f855a;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 500px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: black;
        }

        .status-pending {
            background: #fef5e7;
            color: #744210;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }

        .status-banned {
            background: #fed7d7;
            color: #742a2a;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }

        .status-active {
            background: #c6f6d5;
            color: #22543d;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="brand" style="display:flex;align-items:center;gap:10px;">
            <a href="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'admin_dashboard.php') ?>" title="Reload this page" style="display:inline-flex;align-items:center;">
                <img src="assets/images/im.png" alt="Tech Forum" width="32" height="32" style="display:block;border-radius:6px;background:#fff;object-fit:contain;" />
            </a>
            <h1 style="margin:0;">Admin Dashboard - Tech Forum</h1>
        </div>
        <div>
            <a href="admin_activity.php" class="logout-btn" style="margin-right:8px;">Activity</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <?php if (function_exists('flash_message')) {
            flash_message();
        } ?>
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success"><?= $success ?></div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total_users'] ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['banned_users'] ?></div>
                <div class="stat-label">Banned Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total_posts'] ?></div>
                <div class="stat-label">Total Posts</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['pending_requests'] ?></div>
                <div class="stat-label">Pending Requests</div>
            </div>
        </div>

        <!-- Pending Password Requests -->
        <div class="section">
            <div class="section-header">Pending Password Reset Requests</div>
            <div class="section-content">
                <?php if (empty($pending_requests)): ?>
                    <p>No pending password reset requests.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Requested</th>
                                <th>Reason</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_requests as $request): ?>
                                <tr>
                                    <td><?= htmlspecialchars($request['username'] ?? 'Unknown') ?></td>
                                    <td><?= htmlspecialchars($request['user_email']) ?></td>
                                    <td><?= date('M j, Y H:i', strtotime($request['requested_at'])) ?></td>
                                    <td><?= htmlspecialchars($request['reason']) ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="handle_request">
                                            <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                            <input type="hidden" name="decision" value="approve">
                                            <button type="submit" class="btn success" onclick="return confirm('Approve this password reset request?')">Approve</button>
                                        </form>
                                        <button class="btn danger" onclick="showRejectModal(<?= $request['id'] ?>)">Reject</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- User Management -->
        <div class="section">
            <div class="section-header">User Management</div>
            <div class="section-content">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Joined</th>
                            <th>Last Login</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= (int)$user['id'] ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                <td><?= $user['last_login'] ? date('M j, Y H:i', strtotime($user['last_login'])) : '—' ?></td>
                                <td>
                                    <?php if ($user['is_banned']): ?>
                                        <span class="status-banned">Banned</span>
                                    <?php else: ?>
                                        <span class="status-active">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user['is_banned']): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="unban_user">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" class="btn success" onclick="return confirm('Unban this user?')">Unban</button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn danger" onclick="showBanModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>')">Ban</button>
                                    <?php endif; ?>
                                    <button class="btn" onclick="showMessageModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>')">Message</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Reject Request Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('rejectModal')">&times;</span>
            <h3>Reject Password Reset Request</h3>
            <form method="POST">
                <input type="hidden" name="action" value="handle_request">
                <input type="hidden" name="decision" value="reject">
                <input type="hidden" name="request_id" id="reject_request_id">
                <div class="form-group">
                    <label>Reason for rejection:</label>
                    <textarea name="reject_reason" rows="3" placeholder="Enter reason for rejecting this request..."></textarea>
                </div>
                <button type="submit" class="btn danger">Reject Request</button>
            </form>
        </div>
    </div>

    <!-- Ban User Modal -->
    <div id="banModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('banModal')">&times;</span>
            <h3>Ban User</h3>
            <form method="POST">
                <input type="hidden" name="action" value="ban_user">
                <input type="hidden" name="user_id" id="ban_user_id">
                <div class="form-group">
                    <label>User: <span id="ban_username"></span></label>
                </div>
                <div class="form-group">
                    <label>Reason for ban:</label>
                    <textarea name="ban_reason" rows="3" placeholder="Enter reason for banning this user..." required></textarea>
                </div>
                <button type="submit" class="btn danger">Ban User</button>
            </form>
        </div>
    </div>

    <!-- Message User Modal -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('messageModal')">&times;</span>
            <h3>Send Message to User</h3>
            <form method="POST">
                <input type="hidden" name="action" value="send_message">
                <input type="hidden" name="to_user_id" id="message_user_id">
                <div class="form-group">
                    <label>To: <span id="message_username"></span></label>
                </div>
                <div class="form-group">
                    <label>Subject:</label>
                    <input type="text" name="message_subject" placeholder="Message subject..." required>
                </div>
                <div class="form-group">
                    <label>Message Type:</label>
                    <select name="message_type">
                        <option value="info">Information</option>
                        <option value="warning">Warning</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Message:</label>
                    <textarea name="message_content" rows="5" placeholder="Enter your message..." required></textarea>
                </div>
                <button type="submit" class="btn">Send Message</button>
            </form>
        </div>
    </div>

    <script>
        function showRejectModal(requestId) {
            document.getElementById('reject_request_id').value = requestId;
            document.getElementById('rejectModal').style.display = 'block';
        }

        function showBanModal(userId, username) {
            document.getElementById('ban_user_id').value = userId;
            document.getElementById('ban_username').textContent = username;
            document.getElementById('banModal').style.display = 'block';
        }

        function showMessageModal(userId, username) {
            document.getElementById('message_user_id').value = userId;
            document.getElementById('message_username').textContent = username;
            document.getElementById('messageModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            var modals = document.getElementsByClassName('modal');
            for (var i = 0; i < modals.length; i++) {
                if (event.target == modals[i]) {
                    modals[i].style.display = 'none';
                }
            }
        }
    </script>
</body>

</html>