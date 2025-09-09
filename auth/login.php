<?php
require_once __DIR__ . '/../init.php';

// Only validate request method; JS disables submit button so its name may not be posted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../auth.php');
}

try {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        set_flash('Email and password required.');
        redirect('../auth.php');
    }

    // Detect optional columns to keep compatibility across schemas
    try {
        $cols = $pdo->query("DESCRIBE users")->fetchAll(PDO::FETCH_COLUMN, 0);
    } catch (Throwable $te) {
        $cols = [];
    }
    $hasBanned = in_array('is_banned', $cols, true);
    $hasPwdChangedAt = in_array('password_changed_at', $cols, true);

    $select = 'id, username, email, password_hash';
    $select .= $hasBanned ? ', is_banned' : ', 0 AS is_banned';
    $select .= $hasPwdChangedAt ? ', password_changed_at' : ', NULL AS password_changed_at';

    $stmt = $pdo->prepare("SELECT $select FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        set_flash('Invalid email or password.');
        redirect('../auth.php');
    }

    // Verify hash length (debug help)
    if (strlen($user['password_hash']) < 55) {
        // hash truncated
        set_flash('Password hash stored incorrectly (column too short). Contact admin.');
        redirect('../auth.php');
    }

    // If banned column exists and user is banned, block with clear warning
    if (isset($user['is_banned']) && (int)$user['is_banned'] === 1) {
        set_flash('Your account is banned. Please contact admin for assistance.');
        redirect('../auth.php');
    }

    if (!password_verify($password, $user['password_hash'])) {
        // If password was changed recently (and column exists), provide a tailored warning
        if (!empty($user['password_changed_at'])) {
            set_flash('Password has been changed. If you used an old password, please use the latest credentials or request a reset.');
        } else {
            // Fallback: detect recently approved reset for this email
            try {
                $pr = $pdo->prepare("SELECT status, response_at FROM password_requests WHERE user_email = ? ORDER BY response_at DESC LIMIT 1");
                $pr->execute([$email]);
                $latest = $pr->fetch(PDO::FETCH_ASSOC);
                if ($latest && ($latest['status'] ?? '') === 'approved') {
                    set_flash('Your password was recently changed by admin. Please use the latest credentials. If needed, submit a new reset request.');
                } else {
                    set_flash('Invalid email or password.');
                }
            } catch (Throwable $t) {
                set_flash('Invalid email or password.');
            }
        }
        redirect('../auth.php');
    }

    // Update last_login
    $upd = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $upd->execute([$user['id']]);

    $_SESSION['user'] = [
        'id'       => $user['id'],
        'username' => $user['username'],
        'email'    => $user['email']
    ];

    redirect('../dashboard.php');
} catch (PDOException $e) {
    error_log('Login error: ' . $e->getMessage());
    set_flash('Database error during login.');
    redirect('../auth.php');
}
