<?php
require_once 'init.php';
require_login();
$user = current_user();

// Basic stats
$postCountStmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE user_id = ?");
$postCountStmt->execute([$user['id']]);
$postsCreated = $postCountStmt->fetchColumn();

$replyCountStmt = $pdo->prepare("SELECT COUNT(*) FROM replies WHERE user_id = ?");
$replyCountStmt->execute([$user['id']]);
$repliesMade = $replyCountStmt->fetchColumn();

$viewsSimulated = 1247; // Placeholder since no tracking implemented
$memberSince = $pdo->query("SELECT DATE_FORMAT(created_at, '%b %Y') AS joined FROM users WHERE id = {$user['id']}")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Dashboard - TechForum</title>
  <link rel="icon" href="assets/images/im.png" type="image/png">
  <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>

<body>
  <?php require_once __DIR__ . '/includes/header.php'; ?>
  <main class="dashboard-main" style="max-width:1200px;margin:2rem auto;padding:0 20px;">
    <?php flash_message(); ?>
    <section class="welcome-section" style="background:#fff;padding:2rem;border-radius:20px;display:flex;justify-content:space-between;gap:2rem;box-shadow:0 10px 30px rgba(0,0,0,0.08);">
      <div>
        <h1 style="margin:0 0 .5rem;font-size:2rem;">Welcome back, <span style="color:#667eea;"><?php echo h($user['username']); ?></span>!</h1>
        <p class="welcome-subtitle">Ready to join the conversation?</p>
        <p class="last-login">Member since: <?php echo h($memberSince); ?></p>
      </div>
      <div style="display:flex;align-items:center;gap:1rem;">
        <i class="fas fa-user-circle" style="font-size:4rem;color:#667eea;"></i>
      </div>
    </section>

    <section style="margin-top:2rem;">
      <h2>Your Activity</h2>
      <div class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-top:1rem;">
        <div class="stat-card" style="background:#fff;padding:1.5rem;border-radius:15px;box-shadow:0 5px 15px rgba(0,0,0,0.05);">
          <h3 style="margin:0;font-size:1.5rem;"><?php echo (int)$postsCreated; ?></h3>
          <p>Posts Created</p>
        </div>
        <div class="stat-card" style="background:#fff;padding:1.5rem;border-radius:15px;box-shadow:0 5px 15px rgba(0,0,0,0.05);">
          <h3 style="margin:0;font-size:1.5rem;"><?php echo (int)$repliesMade; ?></h3>
          <p>Replies Made</p>
        </div>
        <div class="stat-card" style="background:#fff;padding:1.5rem;border-radius:15px;box-shadow:0 5px 15px rgba(0,0,0,0.05);">
          <h3 style="margin:0;font-size:1.5rem;"><?php echo number_format($viewsSimulated); ?></h3>
          <p>Profile Views (demo)</p>
        </div>
        <div class="stat-card" style="background:#fff;padding:1.5rem;border-radius:15px;box-shadow:0 5px 15px rgba(0,0,0,0.05);">
          <h3 style="margin:0;font-size:1.5rem;"><?php echo h($memberSince); ?></h3>
          <p>Member Since</p>
        </div>
      </div>
    </section>

    <section style="margin-top:2rem;">
      <h2>Quick Actions</h2>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;margin-top:1rem;">
        <a href="new_post.php" style="text-decoration:none;background:#fff;padding:1.5rem;border-radius:15px;box-shadow:0 5px 15px rgba(0,0,0,0.05);text-align:center;display:block;">
          <i class="fas fa-plus-circle" style="font-size:2rem;color:#667eea;"></i>
          <h3 style="margin:.5rem 0 0;">New Post</h3>
        </a>
        <a href="posts.php" style="text-decoration:none;background:#fff;padding:1.5rem;border-radius:15px;box-shadow:0 5px 15px rgba(0,0,0,0.05);text-align:center;display:block;">
          <i class="fas fa-list" style="font-size:2rem;color:#667eea;"></i>
          <h3 style="margin:.5rem 0 0;">Browse Posts</h3>
        </a>
        <a href="categories.php" style="text-decoration:none;background:#fff;padding:1.5rem;border-radius:15px;box-shadow:0 5px 15px rgba(0,0,0,0.05);text-align:center;display:block;">
          <i class="fas fa-tags" style="font-size:2rem;color:#667eea;"></i>
          <h3 style="margin:.5rem 0 0;">Categories</h3>
        </a>
        <a href="popular.php" style="text-decoration:none;background:#fff;padding:1.5rem;border-radius:15px;box-shadow:0 5px 15px rgba(0,0,0,0.05);text-align:center;display:block;">
          <i class="fas fa-fire" style="font-size:2rem;color:#667eea;"></i>
          <h3 style="margin:.5rem 0 0;">Popular</h3>
        </a>
        <a href="profile.php" style="text-decoration:none;background:#fff;padding:1.5rem;border-radius:15px;box-shadow:0 5px 15px rgba(0,0,0,0.05);text-align:center;display:block;">
          <i class="fas fa-user-cog" style="font-size:2rem;color:#667eea;"></i>
          <h3 style="margin:.5rem 0 0;">Profile Settings</h3>
        </a>
      </div>
    </section>
  </main>
  <?php include 'includes/footer.php'; ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js" defer></script>
</body>

</html>