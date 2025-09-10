<?php
require_once 'init.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Home - TechForum</title>
  <link rel="icon" href="assets/images/im.png" type="image/png">
  <link rel="stylesheet" href="assets/css/style1.css">
</head>

<body>
  <?php require_once __DIR__ . '/includes/header.php'; ?>
  <main class="main" style="padding:2rem 0;">
    <div class="container">
      <h2 style="margin-bottom:1rem;">Latest Posts</h2>
      <?php
      $hasCategory = true;
      try {
        $pdo->query('SELECT category FROM posts LIMIT 1');
      } catch (Throwable $e) {
        $hasCategory = false;
      }
      $cols = $hasCategory ? 'p.id,p.title,p.created_at,p.category,u.username' : 'p.id,p.title,p.created_at,u.username';
      $stmt = $pdo->query("SELECT $cols FROM posts p JOIN users u ON u.id = p.user_id ORDER BY p.created_at DESC LIMIT 10");
      $rows = $stmt->fetchAll();
      if (!$rows) {
        echo "<p>No posts yet. <a href='new_post.php'>Create one</a>.</p>";
      } else {
        echo '<ul style="list-style:none;padding:0;display:grid;gap:.75rem;">';
        foreach ($rows as $r) {
          $cat = $hasCategory ? ' • ' . h($r['category']) : '';
          echo '<li style="background:#fff;padding:1rem;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,.05);">'
            . '<a href="post.php?id=' . (int)$r['id'] . '" style="text-decoration:none;color:#333;font-weight:600;">' . h($r['title']) . '</a>'
            . '<div style="color:#666;font-size:.85rem;margin-top:.25rem;">by ' . h($r['username']) . ' • ' . h($r['created_at']) . $cat . '</div>'
            . '</li>';
        }
        echo '</ul>';
      }
      if (!$hasCategory) {
        echo '<p style="margin-top:1rem;font-size:.75rem;color:#a00;">Add category column: <code>ALTER TABLE posts ADD category VARCHAR(50) NOT NULL DEFAULT "Soft Skills";</code></p>';
      }
      ?>
    </div>
  </main>
  <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>

</html>