<?php
require_once 'init.php';
require_login();

// Optional: restrict to admin
if (isset(current_user()['is_admin']) && !current_user()['is_admin']) {
    die('Admin access required.');
}

try {
    $stmt = $pdo->query("DESCRIBE posts");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    if (!in_array('views', $cols)) {
        $pdo->exec("ALTER TABLE posts ADD views INT UNSIGNED NOT NULL DEFAULT 0 AFTER category");
        echo "Added 'views' column successfully.<br>";
    } else {
        echo "'views' column already exists.<br>";
    }
    echo "Done.";
} catch (PDOException $e) {
    echo "Error: " . h($e->getMessage());
}
