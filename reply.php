<?php
// Optional separate endpoint for reply submissions
// Created: 2025-08-10 03:54:32 UTC by PRITHVIBO

require_once 'init.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = (int)($_POST['post_id'] ?? 0);
    $body = trim($_POST['body'] ?? '');
    
    if ($post_id && $body !== '') {
        // Verify post exists
        $checkStmt = $pdo->prepare("SELECT id FROM posts WHERE id = ?");
        $checkStmt->execute([$post_id]);
        
        if ($checkStmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO replies (post_id, user_id, body) VALUES (?, ?, ?)");
            if ($stmt->execute([$post_id, current_user()['id'], $body])) {
                set_flash('Reply posted successfully!');
            } else {
                set_flash('Failed to post reply.');
            }
        } else {
            set_flash('Post not found.');
        }
        
        redirect('post.php?id=' . $post_id);
    } else {
        set_flash('Invalid input.');
        if ($post_id) {
            redirect('post.php?id=' . $post_id);
        }
    }
}

redirect('posts.php');
?>