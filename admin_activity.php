<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// Fetch recent activity across users
$posts = [];
$replies = [];
$resets = [];

try {
    // posts
    $pCols = $pdo->query('DESCRIBE posts')->fetchAll(PDO::FETCH_COLUMN, 0);
    $postFk = in_array('user_id', $pCols, true) ? 'user_id' : (in_array('author_id', $pCols, true) ? 'author_id' : null);
    if ($postFk) {
        $hasDel = in_array('is_deleted', $pCols, true);
        $pSelect = $hasDel ? 'p.id,p.title,p.created_at,p.is_deleted,u.username,u.id AS uid' : 'p.id,p.title,p.created_at,0 AS is_deleted,u.username,u.id AS uid';
        $posts = $pdo->query("SELECT $pSelect FROM posts p JOIN users u ON u.id = p.$postFk ORDER BY p.created_at DESC LIMIT 50")->fetchAll();
    }

    // replies
    $rCols = $pdo->query('DESCRIBE replies')->fetchAll(PDO::FETCH_COLUMN, 0);
    $textPriority = ['content', 'body', 'reply', 'message', 'text', 'comment'];
    $replyCol = null;
    foreach ($textPriority as $c) {
        if (in_array($c, $rCols, true)) {
            $replyCol = $c;
            break;
        }
    }
    $replyFkCandidates = ['user_id', 'author_id', 'uid', 'created_by', 'user'];
    $replyFk = null;
    foreach ($replyFkCandidates as $c) {
        if (in_array($c, $rCols, true)) {
            $replyFk = $c;
            break;
        }
    }
    if ($replyFk) {
        $hasRDel = in_array('is_deleted', $rCols, true);
        $delSel = $hasRDel ? 'r.is_deleted' : '0 AS is_deleted';
        $contentSel = $replyCol ? "r.`$replyCol` AS content" : "'' AS content";
        $replies = $pdo->query("SELECT r.id,r.post_id,$contentSel,r.created_at,$delSel,u.username,u.id AS uid,p.title AS post_title FROM replies r JOIN users u ON u.id=r.`$replyFk` LEFT JOIN posts p ON p.id=r.post_id ORDER BY r.created_at DESC LIMIT 100")->fetchAll();
    }
} catch (PDOException $e) {
    // ignore, show empty lists
}

// recent password resets (approved)
try {
    $prCols = $pdo->query('DESCRIBE password_requests')->fetchAll(PDO::FETCH_COLUMN, 0);
    $hasNewPwd = in_array('new_password', $prCols, true);
    $hasRespAt = in_array('response_at', $prCols, true);
    $select = 'pr.id, pr.user_id, pr.user_email, pr.status';
    $select .= $hasRespAt ? ', pr.response_at' : ", NULL AS response_at";
    $select .= $hasNewPwd ? ', pr.new_password' : ", NULL AS new_password";
    $resets = $pdo->query("SELECT $select, u.username FROM password_requests pr LEFT JOIN users u ON u.id = pr.user_id WHERE pr.status='approved' ORDER BY pr.response_at DESC LIMIT 20")->fetchAll();
} catch (PDOException $e) {
    $resets = [];
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Activity - Tech Forum</title>
    <link rel="icon" href="assets/images/im.png" type="image/png">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
            margin: 0
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem
        }

        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .1);
            padding: 1rem 1.5rem;
            margin-bottom: 1rem
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee
        }

        th {
            background: #f8f9fa
        }

        .pill {
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 12px
        }

        .pill.del {
            background: #fee;
            color: #c33
        }

        .pill.ok {
            background: #e6ffed;
            color: #2f855a
        }

        .btn {
            display: inline-block;
            background: #667eea;
            color: #fff;
            padding: 6px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px
        }
    </style>
</head>

<body>
    <div class="header">
        <div style="display:flex;align-items:center;gap:10px;">
            <a href="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'admin_activity.php') ?>" title="Reload this page" style="display:inline-flex;align-items:center;">
                <img src="assets/images/im.png" alt="Tech Forum" width="28" height="28" style="display:block;border-radius:6px;background:#fff;object-fit:contain;" />
            </a>
            <div>Admin Activity</div>
        </div>
        <div><a class="btn" href="admin_dashboard.php">Back to Dashboard</a></div>
    </div>
    <div class="container">
        <div class="card">
            <h3 style="margin-bottom:.5rem;">Recent Password Resets</h3>
            <?php if (empty($resets)): ?>
                <p style="color:#666;">No approved resets found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Approved At</th>
                            <th>Temporary Password</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resets as $r): ?>
                            <tr>
                                <td><?= (int)$r['id'] ?></td>
                                <td><?= h($r['username'] ?? ('User #' . (int)($r['user_id'] ?? 0))) ?></td>
                                <td><?= h($r['user_email'] ?? '') ?></td>
                                <td><?= !empty($r['response_at']) ? date('M j, Y H:i', strtotime($r['response_at'])) : '—' ?></td>
                                <td>
                                    <?php if (!empty($_SESSION['admin_logged_in']) && !empty($r['new_password'])): ?>
                                        <span style="font-weight:600; color:#444;"><?= h($r['new_password']) ?></span>
                                    <?php elseif (!empty($r['new_password'])): ?>
                                        <span class="muted">Hidden</span>
                                    <?php else: ?>
                                        <span class="muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <div class="card">
            <h3 style="margin-bottom:.5rem;">Recent Posts (All Users)</h3>
            <?php if (empty($posts)): ?>
                <p style="color:#666;">No posts found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>User</th>
                            <th>Created</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $p): ?>
                            <tr>
                                <td><?= (int)$p['id'] ?></td>
                                <td><?= h($p['title']) ?></td>
                                <td><a href="admin_user_inspect.php?user_id=<?= (int)$p['uid'] ?>" class="btn" style="background:#6b7280;">Inspect <?= h($p['username']) ?></a></td>
                                <td><?= date('M j, Y H:i', strtotime($p['created_at'])) ?></td>
                                <td><?= !empty($p['is_deleted']) ? '<span class="pill del">Deleted</span>' : '<span class="pill ok">Active</span>' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3 style="margin-bottom:.5rem;">Recent Replies (All Users)</h3>
            <?php if (empty($replies)): ?>
                <p style="color:#666;">No replies found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Post</th>
                            <th>User</th>
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
                                <td><a href="admin_user_inspect.php?user_id=<?= (int)$r['uid'] ?>" class="btn" style="background:#6b7280;">Inspect <?= h($r['username']) ?></a></td>
                                <td><?= h(mb_strimwidth(strip_tags($r['content']), 0, 80, '…')) ?></td>
                                <td><?= date('M j, Y H:i', strtotime($r['created_at'])) ?></td>
                                <td><?= !empty($r['is_deleted']) ? '<span class="pill del">Deleted</span>' : '<span class="pill ok">Active</span>' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>