<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// Auto-update last activity (optional)
if (is_logged_in()) {
    $_SESSION['last_activity'] = time();
}
?>