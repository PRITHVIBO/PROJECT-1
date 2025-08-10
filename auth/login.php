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

    $stmt = $pdo->prepare("SELECT id, username, email, password_hash FROM users WHERE email = ?");
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

    if (!password_verify($password, $user['password_hash'])) {
        set_flash('Invalid email or password.');
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