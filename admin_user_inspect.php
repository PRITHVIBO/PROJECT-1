<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$user_id = (int)($_GET['user_id'] ?? 0);
if ($user_id <= 0) {
    set_flash('Invalid user id.');
    header('Location: admin_dashboard.php');
    exit;
}

// Load user safely
try {
    $uCols = $pdo->query("DESCRIBE users")->fetchAll(PDO::FETCH_COLUMN, 0);
} catch (PDOException $e) {
    $uCols = [];
}
$hasLastLogin = in_array('last_login', $uCols, true);
$select = $hasLastLogin
    ? 'id, username, email, created_at, last_login'
    : 'id, username, email, created_at, NULL AS last_login';
$stmt = $pdo->prepare("SELECT $select FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
if (!$user) {
    set_flash('User not found.');
    header('Location: admin_dashboard.php');
    exit;
}

// Posts list (tolerant)
$posts = [];
try {
    $pCols = $pdo->query("DESCRIBE posts")->fetchAll(PDO::FETCH_COLUMN, 0);
    $postFk = in_array('user_id', $pCols, true) ? 'user_id' : (in_array('author_id', $pCols, true) ? 'author_id' : null);
    if ($postFk) {
        $hasPostDeleted = in_array('is_deleted', $pCols, true);
        $pSelect = $hasPostDeleted ? 'id, title, created_at, is_deleted' : 'id, title, created_at, 0 AS is_deleted';
        $pstmt = $pdo->prepare("SELECT $pSelect FROM posts WHERE $postFk = ? ORDER BY created_at DESC LIMIT 50");
        $pstmt->execute([$user_id]);
        $posts = $pstmt->fetchAll();
    }
} catch (PDOException $e) {
    $posts = [];
}

// Replies list (tolerant)
$replies = [];
try {
    $rCols = $pdo->query('DESCRIBE replies')->fetchAll(PDO::FETCH_COLUMN, 0);
    // detect reply text column
    $textPriority = ['content', 'body', 'reply', 'message', 'text', 'comment'];
    $replyCol = null;
    foreach ($textPriority as $colName) {
        if (in_array($colName, $rCols, true)) {
            $replyCol = $colName;
            break;
        }
    }
    $replySelect = $replyCol ? ("r.`$replyCol` AS content,") : ("'' AS content,");
    // detect replies user foreign key
    $replyFkCandidates = ['user_id', 'author_id', 'uid', 'created_by', 'user'];
    $replyFk = null;
    foreach ($replyFkCandidates as $cand) {
        if (in_array($cand, $rCols, true)) {
            $replyFk = $cand;
            break;
        }
    }
    if ($replyFk) {
        $hasReplyDeleted = in_array('is_deleted', $rCols, true);
        $deletedSelect = $hasReplyDeleted ? 'r.is_deleted' : '0 AS is_deleted';
        $rstmt = $pdo->prepare("SELECT r.id, r.post_id, $replySelect r.created_at, $deletedSelect, p.title AS post_title FROM replies r LEFT JOIN posts p ON p.id = r.post_id WHERE r.`$replyFk` = ? ORDER BY r.created_at DESC LIMIT 100");
        $rstmt->execute([$user_id]);
        $replies = $rstmt->fetchAll();
    }
} catch (PDOException $e) {
    $replies = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspect User - <?= h($user['username']) ?> | Tech Forum</title>
    <link rel="icon" href="assets/images/im.png" type="image/png">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
            margin: 0;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 1.5rem;
        }

        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 1rem 1.5rem;
            margin-bottom: 1rem;
        }

        .muted {
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8f9fa;
        }

        .pill {
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 12px;
        }

        .pill.del {
            background: #fee;
            color: #c33;
        }

        .pill.ok {
            background: #e6ffed;
            color: #2f855a;
        }

        .btn {
            display: inline-block;
            background: #667eea;
            color: #fff;
            padding: 6px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div style="display:flex;align-items:center;gap:10px;">
            <a href="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? ('admin_user_inspect.php?user_id=' . (int)$user['id'])) ?>" title="Reload this page" style="display:inline-flex;align-items:center;">
                <img src="assets/images/im.png" alt="Tech Forum" width="28" height="28" style="display:block;border-radius:6px;background:#fff;object-fit:contain;" />
            </a>
            <div>Inspect User: <?= h($user['username']) ?> (ID <?= (int)$user['id'] ?>)</div>
        </div>
        <div><a class="btn" href="admin_dashboard.php">Back to Dashboard</a></div>
    </div>
    <div class="container">
        <div class="card">
            <h3 style="margin-bottom:.5rem;">Credentials</h3>
            <div class="muted">Email: <?= h($user['email']) ?></div>
            <div class="muted">Joined: <?= date('M j, Y H:i', strtotime($user['created_at'])) ?></div>
            <div class="muted">Last Login: <?= $user['last_login'] ? date('M j, Y H:i', strtotime($user['last_login'])) : '—' ?></div>
            <p class="muted" style="margin-top:.5rem;">Note: Only non-sensitive user info is displayed. Password hashes are not shown.</p>
        </div>

        <div class="card">
            <h3 style="margin-bottom:.5rem;">Recent Posts</h3>
            <?php if (empty($posts)): ?>
                <p class="muted">No posts found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Created</th>
                            <th>Status</th>
                            <?php if (!empty($_SESSION['admin_logged_in'])): ?>
                                <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $p): ?>
                            <tr>
                                <td><?= (int)$p['id'] ?></td>
                                <td><?= h($p['title']) ?></td>
                                <td><?= date('M j, Y H:i', strtotime($p['created_at'])) ?></td>
                                <td><?= $p['is_deleted'] ? '<span class="pill del">Deleted</span>' : '<span class="pill ok">Active</span>' ?></td>
                                <?php if (!empty($_SESSION['admin_logged_in'])): ?>
                                    <td>
                                        <?php if (empty($p['is_deleted'])): ?>
                                            <form method="POST" action="admin_delete_post.php" style="display:inline;" onsubmit="return confirm('Delete this post? This is a soft delete and can hide the content from users.');">
                                                <input type="hidden" name="post_id" value="<?= (int)$p['id'] ?>">
                                                <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                                                <button type="submit" class="btn" style="background:#e53e3e;">Delete</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3 style="margin-bottom:.5rem;">Recent Replies</h3>
            <?php if (empty($replies)): ?>
                <p class="muted">No replies found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Post</th>
                            <th>Excerpt</th>
                            <th>Created</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($replies as $r): ?>
                            <tr>
                                <td><?= (int)$r['id'] ?></td>
                                <td><?= h($r['post_title'] ?? ('Post #' . (int)$r['post_id'])) ?></td>
                                <td><?= h(mb_strimwidth(strip_tags($r['content']), 0, 80, '…')) ?></td>
                                <td><?= date('M j, Y H:i', strtotime($r['created_at'])) ?></td>
                                <td><?= $r['is_deleted'] ? '<span class="pill del">Deleted</span>' : '<span class="pill ok">Active</span>' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>