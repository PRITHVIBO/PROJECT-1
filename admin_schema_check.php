<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: admin_access.php');
    exit;
}

$checks = [
    'users' => [
        'id' => 'INT PRIMARY KEY',
        'username' => 'VARCHAR(255)',
        'email' => 'VARCHAR(255)',
        'password_hash' => 'VARCHAR(255)',
        'created_at' => 'DATETIME',
        // optional
        'last_login' => '(optional) DATETIME',
        'is_banned' => '(optional) TINYINT(1) DEFAULT 0',
        'ban_reason' => '(optional) TEXT',
        'banned_at' => '(optional) DATETIME',
        'password_changed_at' => '(optional) DATETIME',
    ],
    'posts' => [
        'id' => 'INT PRIMARY KEY',
        'title' => 'VARCHAR(255)',
        'body' => 'TEXT',
        'created_at' => 'DATETIME',
        // one of the following must exist
        'user_id|author_id' => 'INT (FK to users.id)',
        // soft delete
        'is_deleted' => '(optional) TINYINT(1) DEFAULT 0',
        'deleted_at' => '(optional) DATETIME',
        'views' => '(optional) INT DEFAULT 0'
    ],
    'replies' => [
        'id' => 'INT PRIMARY KEY',
        'post_id' => 'INT (FK posts.id)',
        'user_id' => 'INT (FK users.id)',
        'content' => 'TEXT',
        'created_at' => 'DATETIME',
        'is_deleted' => '(optional) TINYINT(1) DEFAULT 0'
    ],
    'password_requests' => [
        'id' => 'INT PRIMARY KEY',
        'user_id' => 'INT (FK users.id)',
        'user_email' => 'VARCHAR(255)',
        'reason' => 'TEXT',
        'status' => "ENUM('pending','approved','rejected')",
        'requested_at' => 'DATETIME',
        'response_at' => '(optional) DATETIME',
        'admin_id' => '(optional) INT',
        'new_password' => '(optional) VARCHAR(255)'
    ],
];

$results = [];
foreach ($checks as $table => $columns) {
    try {
        $cols = $pdo->query('DESCRIBE ' . $table)->fetchAll(PDO::FETCH_COLUMN, 0);
        $missing = [];
        foreach ($columns as $name => $desc) {
            if ($name === 'user_id|author_id') {
                if (!(in_array('user_id', $cols, true) || in_array('author_id', $cols, true))) {
                    $missing[] = 'user_id or author_id';
                }
                continue;
            }
            // optional columns are recommendations, not required
            $isOptional = stripos($desc, '(optional)') !== false;
            if (!$isOptional && !in_array($name, $cols, true)) {
                $missing[] = $name;
            }
        }
        $results[$table] = ['ok' => empty($missing), 'missing' => $missing, 'present' => $cols];
    } catch (PDOException $e) {
        $results[$table] = ['error' => $e->getMessage()];
    }
}

function sql_suggestion($table, $column)
{
    switch ($table) {
        case 'users':
            if ($column === 'last_login') return "ALTER TABLE users ADD COLUMN last_login DATETIME NULL;";
            if ($column === 'is_banned') return "ALTER TABLE users ADD COLUMN is_banned TINYINT(1) NOT NULL DEFAULT 0;";
            if ($column === 'ban_reason') return "ALTER TABLE users ADD COLUMN ban_reason TEXT NULL;";
            if ($column === 'banned_at') return "ALTER TABLE users ADD COLUMN banned_at DATETIME NULL;";
            if ($column === 'password_changed_at') return "ALTER TABLE users ADD COLUMN password_changed_at DATETIME NULL;";
            break;
        case 'posts':
            if ($column === 'is_deleted') return "ALTER TABLE posts ADD COLUMN is_deleted TINYINT(1) NOT NULL DEFAULT 0;";
            if ($column === 'deleted_at') return "ALTER TABLE posts ADD COLUMN deleted_at DATETIME NULL;";
            if ($column === 'views') return "ALTER TABLE posts ADD COLUMN views INT NOT NULL DEFAULT 0;";
            if ($column === 'user_id or author_id') return "ALTER TABLE posts ADD COLUMN user_id INT NULL; -- or author_id INT NULL;";
            break;
        case 'replies':
            if ($column === 'is_deleted') return "ALTER TABLE replies ADD COLUMN is_deleted TINYINT(1) NOT NULL DEFAULT 0;";
            break;
        case 'password_requests':
            // common baseline
            if ($column === 'user_id') return "ALTER TABLE password_requests ADD COLUMN user_id INT NULL;";
            if ($column === 'user_email') return "ALTER TABLE password_requests ADD COLUMN user_email VARCHAR(255) NULL;";
            if ($column === 'reason') return "ALTER TABLE password_requests ADD COLUMN reason TEXT NULL;";
            if ($column === 'status') return "ALTER TABLE password_requests ADD COLUMN status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending';";
            if ($column === 'requested_at') return "ALTER TABLE password_requests ADD COLUMN requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;";
            break;
    }
    return '-- Add the required column as per your schema.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schema Check - Tech Forum</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            color: #333;
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

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        th {
            background: #f8f9fa;
        }

        code {
            background: #f8f9fa;
            padding: 3px 6px;
            border-radius: 4px;
        }

        .ok {
            color: #2f855a;
        }

        .warn {
            color: #c53030;
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
        <div>Schema Check</div>
        <div>
            <a class="btn" href="admin_dashboard.php" style="margin-right:8px;">Back to Dashboard</a>
            <a class="btn" href="admin_migrate_replies_content.php" onclick="return confirm('This will add replies.content if missing and backfill from body. Continue?');">Fix Replies Content</a>
        </div>
    </div>
    <div class="container">
        <?php foreach ($results as $table => $info): ?>
            <div class="card">
                <h3 style="margin:0 0 .5rem 0;">Table: <?= h($table) ?></h3>
                <?php if (!empty($info['error'])): ?>
                    <p class="warn">Error: <?= h($info['error']) ?></p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Missing Columns</th>
                                <th>Suggestion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($info['ok']): ?>
                                <tr>
                                    <td class="ok">OK</td>
                                    <td>—</td>
                                    <td>—</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($info['missing'] as $col): ?>
                                    <tr>
                                        <td class="warn">Missing</td>
                                        <td><?= h($col) ?></td>
                                        <td><code><?= h(sql_suggestion($table, $col)) ?></code></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <p style="margin-top:.5rem; color:#666;">Present columns: <code><?= h(implode(', ', $info['present'])) ?></code></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>

</html>