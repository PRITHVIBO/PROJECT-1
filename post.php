<?php
require_once 'init.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT p.*, u.username FROM posts p JOIN users u ON u.id=p.user_id WHERE p.id=?");
$stmt->execute([$id]);
$post = $stmt->fetch();
if (!$post) {
  set_flash('Post not found.');
  redirect('posts.php');
}

// Increment views if column exists (and avoid counting multiple times per session quickly)
try {
  $colCheck = $pdo->query("DESCRIBE posts")->fetchAll(PDO::FETCH_COLUMN, 0);
  if (in_array('views', $colCheck)) {
    // Simple throttling: only count once per post per session
    if (!isset($_SESSION['viewed_posts'])) $_SESSION['viewed_posts'] = [];
    if (empty($_SESSION['viewed_posts'][$id]) || (time() - $_SESSION['viewed_posts'][$id]) > 1800) { // 30 min
      $upd = $pdo->prepare("UPDATE posts SET views = views + 1 WHERE id=?");
      $upd->execute([$id]);
      $_SESSION['viewed_posts'][$id] = time();
      // Refresh post views in memory
      $post['views'] = ($post['views'] ?? 0) + 1;
    }
  }
} catch (PDOException $e) {
  // ignore view errors
}

// Handle reply deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_reply'])) {
  $reply_id = (int)($_POST['reply_id'] ?? 0);

  // Verify reply belongs to current user or user is admin
  $checkStmt = $pdo->prepare("SELECT id, user_id FROM replies WHERE id = ?");
  $checkStmt->execute([$reply_id]);
  $reply = $checkStmt->fetch();

  if ($reply && ($reply['user_id'] == current_user()['id'] || current_user()['is_admin'])) {
    $delStmt = $pdo->prepare("DELETE FROM replies WHERE id = ?");
    if ($delStmt->execute([$reply_id])) {
      set_flash('Reply deleted successfully.');
    } else {
      set_flash('Failed to delete reply.');
    }
  } else {
    set_flash('You can only delete your own replies.');
  }
  redirect('post.php?id=' . $id);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply'])) {
  $body = trim($_POST['body'] ?? '');
  if ($body !== '') {
    // Detect reply text column dynamically
    try {
      $cols = $pdo->query("DESCRIBE replies")->fetchAll(PDO::FETCH_COLUMN, 0);
    } catch (PDOException $e) {
      $cols = [];
    }
    $textPriority = ['content', 'body', 'reply', 'message', 'text', 'comment'];
    $replyCol = null;
    foreach ($textPriority as $colName) {
      if (in_array($colName, $cols, true)) {
        $replyCol = $colName;
        break;
      }
    }
    $column = $replyCol ?: 'content';
    $ins = $pdo->prepare("INSERT INTO replies (post_id,user_id,`$column`) VALUES (?,?,?)");
    $ins->execute([$id, current_user()['id'], $body]);
    redirect('post.php?id=' . $id);
  } else {
    set_flash('Reply cannot be empty.');
  }
}

// Build replies query using whichever text column exists
try {
  $rCols = $pdo->query("DESCRIBE replies")->fetchAll(PDO::FETCH_COLUMN, 0);
} catch (PDOException $e) {
  $rCols = [];
}
$textPriority = ['content', 'body', 'reply', 'message', 'text', 'comment'];
$replyCol = null;
foreach ($textPriority as $colName) {
  if (in_array($colName, $rCols, true)) {
    $replyCol = $colName;
    break;
  }
}
$replySelect = $replyCol ? ("r.`$replyCol` AS reply_body,") : ("'' AS reply_body,");
$sql = "SELECT $replySelect r.*, u.username FROM replies r JOIN users u ON u.id=r.user_id WHERE r.post_id=? ORDER BY r.created_at ASC";
$repliesStmt = $pdo->prepare($sql);
$repliesStmt->execute([$id]);
$replies = $repliesStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title><?php echo h($post['title']); ?> - TechForum</title>
  <link rel="icon" href="assets/images/im.png" type="image/png">
  <link rel="stylesheet" href="assets/css/style1.css">
</head>

<body>
  <?php require_once __DIR__ . '/includes/header.php'; ?>
  <main style="max-width:900px;margin:2rem auto;padding:0 20px;">
    <?php flash_message(); ?>
    <article style="background:#fff;padding:1.5rem;border-radius:15px;box-shadow:0 5px 15px rgba(0,0,0,0.05);">
      <h1 style="margin-top:0;"><?php echo h($post['title']); ?></h1>
      <div style="color:#666;font-size:.85rem;margin-bottom:1rem;display:flex;flex-wrap:wrap;gap:.5rem;">
        <span>by <?php echo h($post['username']); ?></span>
        <span>• <?php echo h($post['created_at']); ?></span>
        <span>• Category: <?php echo h($post['category']); ?></span>
        <?php if (isset($post['views'])): ?>
          <span>• 👁️ <?php echo (int)$post['views']; ?> views</span>
        <?php endif; ?>
      </div>
      <div style="white-space:pre-wrap;line-height:1.5;"><?php echo nl2br(h($post['body'])); ?></div>
    </article>

    <section style="margin-top:2rem;">
      <h2>Replies (<?php echo count($replies); ?>)</h2>
      <?php if (!$replies): ?>
        <p>No replies yet.</p>
      <?php else: ?>
        <ul style="list-style:none;padding:0;display:grid;gap:1rem;margin-top:1rem;">
          <?php foreach ($replies as $r): ?>
            <li style="background:#fff;padding:1rem;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.05);position:relative;">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.4rem;">
                <div style="font-size:.85rem;color:#666;">
                  <?php echo h($r['username']); ?> • <?php echo h($r['created_at']); ?>
                </div>

                <?php if ($r['user_id'] == current_user()['id'] || (current_user()['is_admin'] ?? false)): ?>
                  <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this reply?');">
                    <input type="hidden" name="reply_id" value="<?php echo $r['id']; ?>">
                    <button type="submit" name="delete_reply" style="background:#dc3545;color:#fff;border:none;padding:4px 8px;border-radius:4px;font-size:0.75rem;cursor:pointer;transition:background 0.2s;" onmouseover="this.style.background='#c82333'" onmouseout="this.style.background='#dc3545'">
                      🗑️ Delete
                    </button>
                  </form>
                <?php endif; ?>
              </div>
              <div style="white-space:pre-wrap;"><?php echo nl2br(h($r['reply_body'])); ?></div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>

    <section style="margin-top:2rem;">
      <h2>Add a Reply</h2>
      <form method="post" style="display:flex;flex-direction:column;gap:.75rem;margin-top:1rem;">
        <textarea name="body" rows="5" required placeholder="Your reply..." style="padding:.7rem;border:1px solid #ccc;border-radius:8px;"></textarea>
        <button type="submit" name="reply" style="background:#667eea;color:#fff;border:none;padding:.8rem 1.2rem;border-radius:8px;font-weight:600;cursor:pointer;">Post Reply</button>
      </form>
    </section>
  </main>
  <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>

</html>