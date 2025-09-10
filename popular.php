<?php
require_once 'init.php';
$stmt = $pdo->query("SELECT p.id,p.title,p.created_at,p.category,u.username,
        (SELECT COUNT(*) FROM replies r WHERE r.post_id=p.id) AS reply_count
        FROM posts p
        JOIN users u ON u.id=p.user_id
        ORDER BY reply_count DESC, p.created_at DESC LIMIT 10");
$popular = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Popular - TechForum</title>
  <link rel="icon" href="assets/images/im.png" type="image/png">
  <link rel="stylesheet" href="assets/css/style3.css">
</head>

<body>
  <?php require_once __DIR__ . '/includes/header.php'; ?>
  <main style="max-width:1200px;margin:2rem auto;padding:0 20px;">
    <h2>Popular Discussions</h2>
    <?php if (!$popular): ?>
      <p>No discussions yet.</p>
    <?php else: ?>
      <ul style="list-style:none;padding:0;display:grid;gap:.75rem;">
        <?php foreach ($popular as $p): ?>
          <li style="background:#fff;padding:1rem;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,.05);">
            <a href="post.php?id=<?php echo (int)$p['id']; ?>" style="text-decoration:none;font-weight:600;color:#333;"><?php echo h($p['title']); ?></a>
            <div style="color:#666;font-size:.85rem;margin-top:.25rem;">
              by <?php echo h($p['username']); ?> • <?php echo h($p['category']); ?> • replies: <?php echo (int)$p['reply_count']; ?> • <?php echo h($p['created_at']); ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </main>
  <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>

</html>