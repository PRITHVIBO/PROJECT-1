<?php
require_once 'config/db.php';

echo "<h1>🔄 Database Migration Tool</h1>";
echo "<p>This script will help identify and fix column name issues.</p>";

try {
    // Check if posts table has both columns
    echo "<h2>📋 Table Structure Analysis:</h2>";
    $stmt = $pdo->query("DESCRIBE posts");
    $columns = $stmt->fetchAll();

    $hasAuthorId = false;
    $hasUserId = false;

    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f8f9fa;'><th>Column</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    foreach ($columns as $col) {
        if ($col['Field'] == 'author_id') $hasAuthorId = true;
        if ($col['Field'] == 'user_id') $hasUserId = true;

        $highlight = ($col['Field'] == 'author_id' || $col['Field'] == 'user_id') ? 'background: #ffffcc;' : '';
        echo "<tr style='$highlight'>";
        echo "<td><strong>" . $col['Field'] . "</strong></td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<h2>🔍 Column Status:</h2>";
    if ($hasUserId) echo "<p style='color: green;'>✅ <strong>user_id</strong> column exists</p>";
    if ($hasAuthorId) echo "<p style='color: orange;'>⚠️ <strong>author_id</strong> column exists</p>";

    if ($hasAuthorId && $hasUserId) {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>⚠️ Both Columns Exist!</h3>";
        echo "<p>Your table has both user_id and author_id columns. We need to migrate data.</p>";

        // Check data in both columns
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM posts WHERE author_id IS NOT NULL");
        $authorIdCount = $stmt->fetch()['count'];

        $stmt = $pdo->query("SELECT COUNT(*) as count FROM posts WHERE user_id IS NOT NULL");
        $userIdCount = $stmt->fetch()['count'];

        echo "<p><strong>Posts with author_id:</strong> $authorIdCount</p>";
        echo "<p><strong>Posts with user_id:</strong> $userIdCount</p>";

        if ($authorIdCount > 0 && $userIdCount == 0) {
            echo "<h4>🔧 Migration Needed</h4>";
            echo "<p>All posts are using author_id. We need to copy data to user_id.</p>";
            echo "<form method='post' style='margin: 10px 0;'>";
            echo "<button type='submit' name='migrate' value='author_to_user' style='background: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer;'>Migrate author_id → user_id</button>";
            echo "</form>";
        }
        echo "</div>";
    }

    if (!$hasUserId && $hasAuthorId) {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>❌ Missing user_id Column</h3>";
        echo "<p>Your table only has author_id. We need to add user_id column or rename author_id.</p>";
        echo "<form method='post' style='margin: 10px 0;'>";
        echo "<button type='submit' name='add_user_id' value='1' style='background: #dc3545; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer;'>Add user_id Column</button>";
        echo "</form>";
        echo "</div>";
    }

    // Handle migration
    if ($_POST['migrate'] ?? false) {
        if ($_POST['migrate'] == 'author_to_user') {
            echo "<h3>🔄 Migrating Data...</h3>";
            $stmt = $pdo->prepare("UPDATE posts SET user_id = author_id WHERE user_id IS NULL AND author_id IS NOT NULL");
            $affected = $stmt->execute();
            $count = $stmt->rowCount();
            echo "<p style='color: green;'>✅ Migrated $count posts from author_id to user_id</p>";
        }
    }

    if ($_POST['add_user_id'] ?? false) {
        echo "<h3>🔧 Adding user_id Column...</h3>";
        $stmt = $pdo->exec("ALTER TABLE posts ADD COLUMN user_id INT");
        echo "<p style='color: green;'>✅ Added user_id column</p>";
        echo "<p>Now copying data from author_id to user_id...</p>";
        $stmt = $pdo->prepare("UPDATE posts SET user_id = author_id");
        $stmt->execute();
        $count = $stmt->rowCount();
        echo "<p style='color: green;'>✅ Copied data for $count posts</p>";
    }

    // Show current posts
    echo "<h2>📝 Current Posts:</h2>";
    $stmt = $pdo->query("SELECT p.id, p.title, p.user_id, p.author_id, p.is_deleted, p.created_at, u.username 
                        FROM posts p 
                        LEFT JOIN users u ON u.id = COALESCE(p.user_id, p.author_id) 
                        ORDER BY p.created_at DESC LIMIT 10");
    $posts = $stmt->fetchAll();

    if (empty($posts)) {
        echo "<p>No posts found in database.</p>";
    } else {
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f8f9fa;'><th>ID</th><th>Title</th><th>user_id</th><th>author_id</th><th>Username</th><th>Status</th><th>Created</th></tr>";
        foreach ($posts as $post) {
            $status = $post['is_deleted'] ? 'DELETED' : 'ACTIVE';
            $rowColor = $post['is_deleted'] ? 'background: #f8d7da;' : 'background: #d4edda;';
            echo "<tr style='$rowColor'>";
            echo "<td>" . $post['id'] . "</td>";
            echo "<td>" . htmlspecialchars($post['title']) . "</td>";
            echo "<td>" . ($post['user_id'] ?? 'NULL') . "</td>";
            echo "<td>" . ($post['author_id'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($post['username'] ?? 'Unknown') . "</td>";
            echo "<td>" . $status . "</td>";
            echo "<td>" . $post['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (PDOException $e) {
    echo "<h3 style='color: red;'>❌ Database Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<a href='debug_posts.php' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin: 5px;'>Debug Posts</a>";
echo "<a href='profile.php' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin: 5px;'>Go to Profile</a>";
?>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        max-width: 1000px;
        margin: 0 auto;
        padding: 20px;
        background: #f8f9fa;
    }

    table {
        width: 100%;
        margin: 10px 0;
    }

    th,
    td {
        text-align: left;
        padding: 8px;
    }

    th {
        background: #e9ecef;
        font-weight: 600;
    }
</style>