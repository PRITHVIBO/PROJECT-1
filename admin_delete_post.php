<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// Only admins
if (empty($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: admin_access.php');
    exit;
}

$post_id = (int)($_POST['post_id'] ?? 0);
$redirect_user_id = (int)($_POST['user_id'] ?? 0);

if ($post_id <= 0) {
    set_flash('Invalid post id.');
    header('Location: admin_dashboard.php');
    exit;
}

try {
    // Soft delete the post
    $stmt = $pdo->prepare('UPDATE posts SET is_deleted = 1, deleted_at = NOW() WHERE id = ?');
    $stmt->execute([$post_id]);
    set_flash('Post deleted (soft).');
} catch (PDOException $e) {
    error_log('Admin delete post error: ' . $e->getMessage());
    set_flash('Error deleting post.');
}

if ($redirect_user_id > 0) {
    header('Location: admin_user_inspect.php?user_id=' . $redirect_user_id);
} else {
    header('Location: admin_dashboard.php');
}
exit;
