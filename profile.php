<?php
require_once 'init.php';
require_login();
$user = current_user();

// Initialize arrays to prevent errors
$errors = [];
$admin_messages = [];
$user_posts = []; // removed usage (legacy)

// Handle post deletion
if (isset($_POST['action']) && $_POST['action'] === 'delete_post' && isset($_POST['post_id'])) {
  $post_id = (int)$_POST['post_id'];
  try {
    // Check both user_id and author_id columns for compatibility
    $stmt = $pdo->prepare("SELECT id FROM posts WHERE id = ? AND (user_id = ? OR author_id = ?)");
    $stmt->execute([$post_id, $user['id'], $user['id']]);
    if ($stmt->fetch()) {
      $stmt = $pdo->prepare("UPDATE posts SET is_deleted = 1, deleted_at = NOW() WHERE id = ?");
      $stmt->execute([$post_id]);
      set_flash('Post deleted successfully.');
    } else {
      set_flash('Post not found or you do not have permission to delete it.');
    }
  } catch (PDOException $e) {
    error_log('Delete post error: ' . $e->getMessage());
    set_flash('Error deleting post: ' . $e->getMessage());
  }
  redirect('profile.php#posts');
}

// Handle marking messages as read
if (isset($_POST['action']) && $_POST['action'] === 'mark_read' && isset($_POST['message_id'])) {
  $message_id = (int)$_POST['message_id'];
  try {
    $stmt = $pdo->prepare("UPDATE admin_messages SET is_read = 1 WHERE id = ? AND to_user_id = ?");
    $stmt->execute([$message_id, $user['id']]);
    set_flash('Message marked as read.');
  } catch (PDOException $e) {
    error_log('Mark message as read error: ' . $e->getMessage());
  }
}

// Handle form submission (only for profile updates)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
  $newName = trim($_POST['username'] ?? '');
  $currentPassword = $_POST['current_password'] ?? '';
  $newPassword = $_POST['new_password'] ?? '';
  $confirmPassword = $_POST['confirm_password'] ?? '';

  if ($newName === '') {
    $errors[] = 'Username cannot be empty.';
  } elseif (strlen($newName) < 3 || strlen($newName) > 50) {
    $errors[] = 'Username must be 3-50 characters.';
  }

  $passwordChange = ($currentPassword !== '' || $newPassword !== '' || $confirmPassword !== '');

  if ($passwordChange) {
    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
      $errors[] = 'Fill all password fields to change password.';
    } else {
      // Fetch hash
      $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
      $stmt->execute([$user['id']]);
      $hash = $stmt->fetchColumn();
      if (!$hash || !password_verify($currentPassword, $hash)) {
        $errors[] = 'Current password is incorrect.';
      }
      if (strlen($newPassword) < 6) {
        $errors[] = 'New password must be at least 6 characters.';
      }
      if ($newPassword !== $confirmPassword) {
        $errors[] = 'New password and confirmation do not match.';
      }
    }
  }

  // Check username uniqueness if changed
  if ($newName !== $user['username']) {
    $chk = $pdo->prepare('SELECT id FROM users WHERE username = ? AND id <> ? LIMIT 1');
    $chk->execute([$newName, $user['id']]);
    if ($chk->fetch()) {
      $errors[] = 'Username already taken.';
    }
  }

  if (empty($errors)) {
    try {
      $pdo->beginTransaction();
      // Update username if changed
      if ($newName !== $user['username']) {
        $upd = $pdo->prepare('UPDATE users SET username = ? WHERE id = ?');
        $upd->execute([$newName, $user['id']]);
        $_SESSION['user']['username'] = $newName; // refresh session
      }
      // Update password if requested
      if ($passwordChange && empty(array_filter($errors))) {
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $updP = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $updP->execute([$newHash, $user['id']]);
      }
      $pdo->commit();
      set_flash('Profile updated successfully.');
      redirect('profile.php');
    } catch (Throwable $e) {
      if ($pdo->inTransaction()) $pdo->rollBack();
      error_log('Profile update error: ' . $e->getMessage());
      $errors[] = 'Database error updating profile: ' . $e->getMessage();
    }
  }
}

// Get admin messages for this user
try {
  $stmt = $pdo->prepare("SELECT am.*, a.username as admin_username FROM admin_messages am LEFT JOIN admins a ON am.from_admin_id = a.id WHERE am.to_user_id = ? ORDER BY am.sent_at DESC");
  $stmt->execute([$user['id']]);
  $admin_messages = $stmt->fetchAll();
} catch (PDOException $e) {
  error_log('Admin messages error: ' . $e->getMessage());
  $admin_messages = [];
}

// Removed user posts retrieval (My Posts section deprecated)
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Profile - TechForum</title>
  <link rel="icon" href="assets/images/im.png" type="image/png">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/profile.css">
  <style>
    /* Enhanced CSS with better error handling */
    .profile-wrapper {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2rem;
      background: white;
      border-radius: 15px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .profile-meta {
      display: flex;
      gap: 1rem;
      margin: 1rem 0 2rem 0;
      flex-wrap: wrap;
    }

    .pill {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 25px;
      font-size: 0.875rem;
      font-weight: 500;
    }

    .nav-tabs {
      display: flex;
      border-bottom: 2px solid #e2e8f0;
      margin-bottom: 2rem;
      overflow-x: auto;
    }

    .nav-tab {
      background: none;
      border: none;
      padding: 1rem 1.5rem;
      cursor: pointer;
      font-size: 1rem;
      color: #718096;
      border-bottom: 3px solid transparent;
      transition: all 0.3s ease;
      white-space: nowrap;
      min-width: fit-content;
    }

    .nav-tab.active,
    .nav-tab:hover {
      color: #667eea;
      border-bottom-color: #667eea;
      background: rgba(102, 126, 234, 0.05);
    }

    .tab-content {
      display: none;
      animation: fadeIn 0.3s ease-in;
    }

    .tab-content.active {
      display: block;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .error-list {
      background: #fed7d7;
      border: 1px solid #feb2b2;
      color: #742a2a;
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 1.5rem;
    }

    .error-list ul {
      margin: 0.5rem 0 0 0;
      padding-left: 1.5rem;
    }

    .form-section {
      background: #f7fafc;
      padding: 1.5rem;
      border-radius: 10px;
      margin-bottom: 2rem;
      border: 1px solid #e2e8f0;
    }

    .form-section h2 {
      margin: 0 0 1rem 0;
      color: #2d3748;
      font-size: 1.25rem;
    }

    .grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.5rem;
    }

    .form-row {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }

    .form-row label {
      font-weight: 600;
      color: #2d3748;
    }

    .form-row input {
      padding: 0.75rem;
      border: 1px solid #cbd5e0;
      border-radius: 8px;
      font-size: 1rem;
      transition: border-color 0.3s ease;
    }

    .form-row input:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-row input.readonly {
      background: #edf2f7;
      color: #718096;
    }

    .note {
      font-size: 0.875rem;
      color: #718096;
      margin: 0;
    }

    .actions {
      display: flex;
      justify-content: flex-end;
      margin-top: 2rem;
    }

    .primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      padding: 0.75rem 2rem;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }

    .message-card {
      background: #f7fafc;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      transition: all 0.3s ease;
    }

    .message-card.unread {
      background: #ebf8ff;
      border-color: #bee3f8;
      box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    }

    .message-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 1rem;
      gap: 1rem;
    }

    .message-subject {
      font-weight: bold;
      color: #2d3748;
      font-size: 1.1rem;
    }

    .message-meta {
      font-size: 0.875rem;
      color: #718096;
      margin-top: 0.5rem;
    }

    .message-type {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 15px;
      font-size: 0.75rem;
      font-weight: bold;
      text-transform: uppercase;
      margin-left: 0.5rem;
    }

    .message-type.info {
      background: #bee3f8;
      color: #2b6cb0;
    }

    .message-type.warning {
      background: #fed7d7;
      color: #c53030;
    }

    .message-type.ban_notice {
      background: #fed7d7;
      color: #742a2a;
    }

    .message-type.unban_notice {
      background: #c6f6d5;
      color: #22543d;
    }

    .post-card {
      background: white;
      border: 1px solid #e2e8f0;
      border-radius: 15px;
      padding: 2rem;
      margin-bottom: 2rem;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      position: relative;
    }

    .post-card:hover {
      box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
      border-color: #cbd5e0;
      transform: translateY(-2px);
    }

    .post-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 1.5rem;
      gap: 1rem;
    }

    .post-title {
      font-weight: bold;
      color: #2d3748;
      margin-bottom: 0.75rem;
      font-size: 1.25rem;
      line-height: 1.4;
    }

    .post-title a {
      color: inherit;
      text-decoration: none;
      transition: color 0.3s ease;
    }

    .post-title a:hover {
      color: #667eea !important;
    }

    .post-meta {
      font-size: 0.875rem;
      color: #718096;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      flex-wrap: wrap;
    }

    .post-content {
      color: #4a5568;
      margin: 1.5rem 0;
      line-height: 1.7;
      font-size: 1rem;
    }

    .post-stats {
      display: flex;
      gap: 2rem;
      font-size: 0.875rem;
      color: #718096;
      padding-top: 1.5rem;
      border-top: 1px solid #e2e8f0;
    }

    .post-stats span {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-weight: 500;
    }

    .btn-small {
      padding: 0.5rem 1rem;
      font-size: 0.875rem;
      border-radius: 8px;
      font-weight: 600;
      transition: all 0.3s ease;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 0.25rem;
    }

    .btn-danger {
      background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
      color: white;
    }

    .btn-danger:hover {
      background: linear-gradient(135deg, #c53030 0%, #9c2626 100%);
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(229, 62, 62, 0.4);
    }

    .empty-state {
      text-align: center;
      color: #718096;
      padding: 4rem 2rem;
      background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
      border-radius: 15px;
      border: 2px dashed #cbd5e0;
      margin: 2rem 0;
    }

    .empty-state .icon {
      font-size: 4rem;
      margin-bottom: 1rem;
      opacity: 0.5;
    }

    .empty-state p {
      font-size: 1.2rem;
      margin-bottom: 1.5rem;
      font-weight: 500;
    }

    .empty-state a {
      display: inline-flex;
      align-items: center;
      gap: 0.75rem;
      padding: 1rem 2rem;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      text-decoration: none;
      border-radius: 10px;
      font-weight: 600;
      font-size: 1.1rem;
      transition: all 0.3s ease;
      box-shadow: 0 6px 16px rgba(102, 126, 234, 0.3);
    }

    .empty-state a:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
    }

    .posts-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
      flex-wrap: wrap;
      gap: 1rem;
    }

    .posts-header h2 {
      margin: 0;
      color: #2d3748;
      font-size: 1.75rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .create-post-btn {
      display: inline-flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.875rem 1.75rem;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      text-decoration: none;
      border-radius: 10px;
      font-weight: 600;
      font-size: 1rem;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
      white-space: nowrap;
    }

    .create-post-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .profile-wrapper {
        padding: 1rem;
        margin: 1rem;
      }

      .grid-2 {
        grid-template-columns: 1fr;
      }

      .post-card {
        padding: 1.5rem;
      }

      .post-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
      }

      .post-stats {
        flex-direction: column;
        gap: 0.75rem;
      }

      .nav-tabs {
        flex-wrap: wrap;
      }

      .nav-tab {
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
      }

      .posts-header {
        flex-direction: column;
        align-items: flex-start;
      }

      .posts-header h2 {
        font-size: 1.5rem;
      }
    }

    /* Loading states */
    .loading {
      opacity: 0.6;
      pointer-events: none;
    }

    /* Debug styles */
    .debug-info {
      background: #fff3cd;
      border: 1px solid #ffeaa7;
      padding: 1rem;
      border-radius: 8px;
      margin: 1rem 0;
      font-size: 0.875rem;
    }
  </style>
</head>

<body>
  <?php include __DIR__ . '/includes/header.php'; ?>
  <main>
    <div class="profile-wrapper">
      <h1>My Profile</h1>
      <div class="profile-meta">
        <span class="pill">User ID #<?= (int)$user['id']; ?></span>
        <span class="pill">Email: <?= h($user['email'] ?? 'Not set'); ?></span>
        <?php if (isset($user['is_banned']) && $user['is_banned']): ?>
          <span class="pill" style="background: #fed7d7; color: #c53030;">BANNED</span>
        <?php endif; ?>
      </div>

      <?php flash_message(); ?>

      <?php if (isset($_GET['debug'])): ?>
        <div class="debug-info">
          <strong>Debug Info:</strong><br>
          Posts found: <?= count($user_posts) ?><br>
          User ID: <?= $user['id'] ?><br>
          <?php if (isset($hasUserId, $hasAuthorId)): ?>
            Has user_id column: <?= $hasUserId ? 'Yes' : 'No' ?><br>
            Has author_id column: <?= $hasAuthorId ? 'Yes' : 'No' ?>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <!-- Navigation Tabs -->
      <div class="nav-tabs">
        <button class="nav-tab active" onclick="showTab('profile')">Edit Profile</button>
        <button class="nav-tab" onclick="showTab('messages')">Admin Messages
          <?php
          $unread_count = 0;
          foreach ($admin_messages as $m) {
            if (!$m['is_read']) $unread_count++;
          }
          if ($unread_count > 0):
          ?>
            <span style="background: #e53e3e; color: white; border-radius: 10px; padding: 2px 8px; font-size: 0.7rem; margin-left: 4px;">
              <?= $unread_count ?>
            </span>
          <?php endif; ?>
        </button>
        <!-- My Posts tab removed -->
      </div>

      <div style="margin:1rem 0 2rem 0; display:flex; gap:.75rem; flex-wrap:wrap;">
        <a href="posts.php?view=my" class="manage-posts-link" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;text-decoration:none;padding:.55rem 1.1rem;border-radius:8px;font-size:.85rem;font-weight:600;display:inline-flex;align-items:center;gap:.4rem;box-shadow:0 4px 12px rgba(102,126,234,.35);transition:.25s;">
          📝 Manage My Posts
        </a>
        <a href="new_post.php" style="background:#f7fafc;border:1px solid #e2e8f0;color:#4a5568;text-decoration:none;padding:.55rem 1.1rem;border-radius:8px;font-size:.85rem;font-weight:600;display:inline-flex;align-items:center;gap:.4rem;transition:.25s;">
          ➕ New Post
        </a>
      </div>

      <!-- Profile Edit Tab -->
      <div id="profile-tab" class="tab-content active">
        <?php if (!empty($errors)): ?>
          <div class="error-list">
            <strong>Please fix the following errors:</strong>
            <ul>
              <?php foreach ($errors as $e): ?>
                <li><?= h($e) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="post" action="profile.php" autocomplete="off">
          <div class="form-section">
            <h2>Account Information</h2>
            <div class="grid-2">
              <div class="form-row">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" value="<?= h($user['username']); ?>" required minlength="3" maxlength="50" pattern="[A-Za-z0-9_-]{3,50}">
                <p class="note">Allowed: letters, numbers, underscore, dash (3-50 characters)</p>
              </div>
              <div class="form-row">
                <label for="email_readonly">Email Address</label>
                <input type="text" id="email_readonly" class="readonly" value="<?= h($user['email'] ?? 'Not set'); ?>" disabled>
                <p class="note">Contact support to change your email address</p>
              </div>
            </div>
          </div>
          <div class="form-section">
            <h2>Change Password <span style="font-weight:400;color:#718096;">(Optional)</span></h2>
            <div class="grid-2">
              <div class="form-row">
                <label for="current_password">Current Password</label>
                <input type="password" name="current_password" id="current_password" autocomplete="current-password">
              </div>
              <div></div>
              <div class="form-row">
                <label for="new_password">New Password</label>
                <input type="password" name="new_password" id="new_password" minlength="6" autocomplete="new-password">
              </div>
              <div class="form-row">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" minlength="6" autocomplete="new-password">
              </div>
            </div>
            <p class="note">Leave password fields blank if you don't want to change your password. New password must be at least 6 characters.</p>
          </div>
          <div class="actions">
            <button type="submit" class="primary">
              💾 Save Changes
            </button>
          </div>
        </form>
      </div>

      <!-- Admin Messages Tab -->
      <div id="messages-tab" class="tab-content">
        <h2>Messages from Administrators</h2>
        <?php if (empty($admin_messages)): ?>
          <div class="empty-state">
            <div class="icon">📬</div>
            <p>No messages from administrators.</p>
          </div>
        <?php else: ?>
          <?php foreach ($admin_messages as $message): ?>
            <div class="message-card <?= $message['is_read'] ? '' : 'unread' ?>">
              <div class="message-header">
                <div style="flex: 1;">
                  <div class="message-subject"><?= h($message['subject']) ?></div>
                  <div class="message-meta">
                    From: <?= h($message['admin_username'] ?? 'Admin') ?> •
                    <?= date('M j, Y H:i', strtotime($message['sent_at'])) ?>
                    <span class="message-type <?= $message['message_type'] ?>"><?= $message['message_type'] ?></span>
                  </div>
                </div>
                <?php if (!$message['is_read']): ?>
                  <form method="POST" style="margin: 0;">
                    <input type="hidden" name="action" value="mark_read">
                    <input type="hidden" name="message_id" value="<?= $message['id'] ?>">
                    <button type="submit" class="btn-small">✓ Mark as Read</button>
                  </form>
                <?php endif; ?>
              </div>
              <div class="message-content">
                <?= nl2br(h($message['message'])) ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- My Posts section removed -->
    </div>
  </main>

  <?php include __DIR__ . '/includes/footer.php'; ?>

  <script>
    function showTab(tabName) {
      // Hide all tab contents
      document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
      });

      // Remove active class from all tabs
      document.querySelectorAll('.nav-tab').forEach(tab => {
        tab.classList.remove('active');
      });

      // Show selected tab content
      const targetTab = document.getElementById(tabName + '-tab');
      if (targetTab) {
        targetTab.classList.add('active');
      }

      // Add active class to clicked tab
      if (event && event.target) {
        event.target.classList.add('active');
      }

      // Update URL hash
      history.replaceState(null, null, '#' + tabName);
    }

    // Auto-switch to tab based on URL hash
    window.addEventListener('load', function() {
      const hash = window.location.hash.substring(1);
      if (hash && ['profile', 'messages'].includes(hash)) {
        showTab(hash);
        // Update the clicked tab to be active
        document.querySelectorAll('.nav-tab').forEach((tab, index) => {
          tab.classList.remove('active');
          if ((hash === 'profile' && index === 0) ||
            (hash === 'messages' && index === 1)) {
            tab.classList.add('active');
          }
        });
      }
    });

    // Add loading states for form submissions
    document.querySelectorAll('form').forEach(form => {
      form.addEventListener('submit', function() {
        const button = form.querySelector('button[type="submit"]');
        if (button) {
          button.style.opacity = '0.6';
          button.style.pointerEvents = 'none';
          button.innerHTML = button.innerHTML.replace(/💾|🗑️|✓/, '⏳');
        }
      });
    });

    // Removed auto-refresh logic for My Posts
  </script>
</body>

</html>