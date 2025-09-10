<?php
require_once 'init.php';
$categories = get_categories();

// Detect whether the posts table already has the category column.
$hasCategory = true;
try {
  $pdo->query("SELECT category FROM posts LIMIT 1");
} catch (Throwable $e) {
  $hasCategory = false;
}

$randomByCategory = [];
if ($hasCategory) {
  foreach ($categories as $cat) {
    $q = $pdo->prepare("SELECT id,title,category FROM posts WHERE category = ? ORDER BY RAND() LIMIT 3");
    $q->execute([$cat]);
    $randomByCategory[$cat] = $q->fetchAll();
  }
}

// Sample representative topics per category (fallback display)
$sampleTopics = [
  'Soft Skills' => ['Communication Basics', 'Time Management 101', 'Leadership Mindset'],
  'Technology' => ['Intro to HTML/CSS', 'Understanding APIs', 'Database Indexing'],
  'Academics' => ['Exam Prep Strategy', 'Note-taking Methods', 'Research Tips'],
  'Sports' => ['Stamina Building', 'Injury Prevention', 'Teamwork Drills'],
  'Lifestyle' => ['Healthy Morning Routine', 'Minimalist Living', 'Mindfulness Start']
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Categories - TechForum</title>
  <link rel="icon" href="assets/images/im.png" type="image/png">
  <link rel="stylesheet" href="assets/css/style2.css">
</head>

<body>
  <?php require_once __DIR__ . '/includes/header.php'; ?>
  <main style="max-width:1200px;margin:2rem auto;padding:0 20px;">
    <h2>Categories</h2>
    <?php foreach ($categories as $c): ?>
      <section style="margin:1.5rem 0;">
        <h3 style="margin:.25rem 0;"><?php echo h($c); ?></h3>
        <?php if (empty($randomByCategory[$c])): ?>
          <p style="color:#fff;font-size:.9rem;text-shadow:0 1px 3px rgba(0,0,0,.5);">No posts yet in this category. Sample topics:</p>
          <ul style="list-style:disc;margin:.3rem 0 .5rem 1.25rem;color:#fff;font-size:.85rem;">
            <?php foreach (($sampleTopics[$c] ?? []) as $t): ?>
              <li><?php echo h($t); ?></li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <ul style="list-style:none;padding:0;display:flex;flex-wrap:wrap;gap:.75rem;">
            <?php foreach ($randomByCategory[$c] as $p): ?>
              <li style="background:#fff;padding:.75rem 1rem;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,.05);min-width:220px;">
                <a href="post.php?id=<?php echo (int)$p['id']; ?>" style="text-decoration:none;font-weight:600;color:#333;">
                  <?php echo h($p['title']); ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>
    <?php endforeach; ?>
    <p style="font-size:.8rem;color:#666;">Showing up to 3 random posts per category. Refresh to reshuffle.</p>

  </main>
  <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>

</html>