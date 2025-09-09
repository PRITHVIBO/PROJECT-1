<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: admin_access.php');
    exit;
}

$message = '';
try {
    $cols = $pdo->query('DESCRIBE replies')->fetchAll(PDO::FETCH_COLUMN, 0);
    if (!in_array('content', $cols, true)) {
        $pdo->exec('ALTER TABLE replies ADD COLUMN content TEXT NULL AFTER user_id');
        $message .= "Added column replies.content. ";
    } else {
        $message .= "Column replies.content already exists. ";
    }
    // Backfill empty content from body when present
    $pdo->exec("UPDATE replies SET content = body WHERE (content IS NULL OR content = '') AND body IS NOT NULL");
    $message .= "Backfill complete.";
} catch (PDOException $e) {
    $message = 'Migration error: ' . $e->getMessage();
}

set_flash($message);
header('Location: admin_schema_check.php');
exit;
