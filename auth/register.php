<?php
file_put_contents(__DIR__ . '/../register_debug.txt', 'Register script executed at ' . date('c') . PHP_EOL, FILE_APPEND);
require_once __DIR__ . '/../init.php';

// Only check request method; relying on the submit button name fails because JS disables it (disabled controls are not submitted)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../auth.php');
}

try {
    // Debug: log all POST data
    error_log('REGISTER POST: ' . print_r($_POST, true));

    // Basic validation
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    error_log("REGISTER username: $username, email: $email, password length: " . strlen($password));

    if ($username === '' || $email === '' || $password === '') {
        error_log('REGISTER: missing fields');
        set_flash('All fields required.');
        redirect('../auth.php');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log('REGISTER: invalid email');
        set_flash('Invalid email format.');
        redirect('../auth.php');
    }

    if (strlen($password) < 6) {
        error_log('REGISTER: password too short');
        set_flash('Password must be at least 6 characters.');
        redirect('../auth.php');
    }

    // Check uniqueness
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    if ($stmt->fetch()) {
        error_log('REGISTER: email or username taken');
        set_flash('Email or username already taken.');
        redirect('../auth.php');
    }

    // Hash
    $hash = password_hash($password, PASSWORD_DEFAULT); // default → future‑proof
    error_log('REGISTER: password hash generated');

    // Insert (include created_at if your table needs it)
    $insert = $pdo->prepare("INSERT INTO users (username, email, password_hash, created_at) VALUES (?, ?, ?, NOW())");
    error_log('REGISTER: prepared insert statement');

    $ok = $insert->execute([$username, $email, $hash]);
    error_log('REGISTER: insert execute result: ' . var_export($ok, true));
    if (!$ok) {
        $errorInfo = $insert->errorInfo();
        error_log('Insert error: ' . print_r($errorInfo, true));
        set_flash('Registration failed (insert error): ' . $errorInfo[2]);
        redirect('../auth.php');
    }

    // Optional sanity check fetch
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    $row = $check->fetch();
    error_log('REGISTER: sanity check fetch: ' . print_r($row, true));
    if (!$row) {
        error_log('REGISTER: sanity check failed');
        set_flash('Registration failed (verify insert).');
        redirect('../auth.php');
    }

    error_log('REGISTER: registration successful');
    set_flash('Registration successful! Please sign in.');
    redirect('../auth.php');
} catch (PDOException $e) {
    error_log('Registration error: ' . $e->getMessage());
    set_flash('Database error during registration.');
    redirect('../auth.php');
}