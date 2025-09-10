<?php
require_once 'init.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $body  = trim($_POST['body'] ?? '');
  $category = sanitize_category(trim($_POST['category'] ?? ''));
  if ($title === '' || $body === '') {
    set_flash('Title and body required.');
  } else {
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, body, category) VALUES (?,?,?,?)");
    $stmt->execute([current_user()['id'], $title, $body, $category]);
    redirect('posts.php');
  }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>New Post - TechForum</title>
  <link rel="icon" href="assets/images/im.png" type="image/png">
  <link rel="stylesheet" href="assets/css/style1.css">
</head>

<body>
  <?php require_once __DIR__ . '/includes/header.php'; ?>
  <main style="max-width:800px;margin:2rem auto;padding:0 20px;">
    <h2>Create New Post</h2>
    <?php flash_message(); ?>
    <form method="post" style="display:flex;flex-direction:column;gap:.75rem;margin-top:1rem;">
      <input type="text" name="title" placeholder="Post Title" required maxlength="200" style="padding:.7rem;border:1px solid #ccc;border-radius:8px;">
      <select name="category" required style="padding:.7rem;border:1px solid #ccc;border-radius:8px;">
        <?php foreach (get_categories() as $c): ?>
          <option value="<?php echo h($c); ?>"><?php echo h($c); ?></option>
        <?php endforeach; ?>
      </select>
      <textarea name="body" placeholder="Write your content..." required rows="8" style="padding:.7rem;border:1px solid #ccc;border-radius:8px;"></textarea>
      <button type="submit" style="background:#667eea;color:#fff;border:none;padding:.8rem 1.2rem;border-radius:8px;font-weight:600;cursor:pointer;">Publish</button>
    </form>
  </main>
  <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>

</html>