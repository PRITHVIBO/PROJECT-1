<?php
require_once 'init.php';
require_login();

$user = current_user();

// Check what view mode we're in
$view = $_GET['view'] ?? 'all'; // 'all' or 'my'

// Handle post deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_post') {
    $post_id = (int)($_POST['post_id'] ?? 0);

    if ($post_id > 0) {
        try {
            // Check if post belongs to current user
            $stmt = $pdo->prepare("SELECT id, title FROM posts WHERE id = ? AND user_id = ?");
            $stmt->execute([$post_id, $user['id']]);
            $post = $stmt->fetch();

            if (!$post) {
                // Fallback check for author_id column if exists
                try {
                    $stmt = $pdo->prepare("SELECT id, title FROM posts WHERE id = ? AND author_id = ?");
                    $stmt->execute([$post_id, $user['id']]);
                    $post = $stmt->fetch();
                } catch (PDOException $e) {
                    // author_id column doesn't exist
                }
            }

            if ($post) {
                // Soft delete the post
                $stmt = $pdo->prepare("UPDATE posts SET is_deleted = 1, deleted_at = NOW() WHERE id = ?");
                $stmt->execute([$post_id]);
                set_flash('Post "' . h($post['title']) . '" deleted successfully.');
            } else {
                set_flash('Post not found or you do not have permission to delete it.');
            }
        } catch (PDOException $e) {
            error_log('Delete post error: ' . $e->getMessage());
            set_flash('Error deleting post. Please try again.');
        }
    }
    redirect('posts.php' . ($view === 'my' ? '?view=my' : ''));
}

// Handle post editing redirect
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_post') {
    $post_id = (int)($_POST['post_id'] ?? 0);
    if ($post_id > 0) {
        redirect('edit_post.php?id=' . $post_id);
    }
}

// Get posts with improved query
$posts = [];
$error_message = '';

try {
    // First check what columns exist
    $stmt = $pdo->query("DESCRIBE posts");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    $hasUserId = in_array('user_id', $columns);
    $hasAuthorId = in_array('author_id', $columns);
    $hasViews = in_array('views', $columns);
    // Provide a safe selectable segment for views to avoid SQL errors when column missing
    $viewsSelect = $hasViews ? 'p.views,' : 'NULL AS views,';

    if ($view === 'my') {
        // Show only current user's posts
        if ($hasUserId && $hasAuthorId) {
            $query = "SELECT p.id, p.title, p.body, p.created_at, p.category, $viewsSelect
                             COALESCE(p.user_id, p.author_id) as post_user_id,
                             u.username,
                             (SELECT COUNT(*) FROM replies r WHERE r.post_id = p.id) AS replies
                      FROM posts p 
                      LEFT JOIN users u ON (u.id = COALESCE(p.user_id, p.author_id))
                      WHERE (p.user_id = ? OR p.author_id = ?) 
                      AND (p.is_deleted = 0 OR p.is_deleted IS NULL)
                      ORDER BY p.created_at DESC";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$user['id'], $user['id']]);
        } elseif ($hasUserId) {
            $query = "SELECT p.id, p.title, p.body, p.created_at, p.category, $viewsSelect
                             p.user_id as post_user_id,
                             u.username,
                             (SELECT COUNT(*) FROM replies r WHERE r.post_id = p.id) AS replies
                      FROM posts p 
                      LEFT JOIN users u ON u.id = p.user_id
                      WHERE p.user_id = ? 
                      AND (p.is_deleted = 0 OR p.is_deleted IS NULL)
                      ORDER BY p.created_at DESC";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$user['id']]);
        } elseif ($hasAuthorId) {
            $query = "SELECT p.id, p.title, p.body, p.created_at, p.category, $viewsSelect
                             p.author_id as post_user_id,
                             u.username,
                             (SELECT COUNT(*) FROM replies r WHERE r.post_id = p.id) AS replies
                      FROM posts p 
                      LEFT JOIN users u ON u.id = p.author_id
                      WHERE p.author_id = ? 
                      AND (p.is_deleted = 0 OR p.is_deleted IS NULL)
                      ORDER BY p.created_at DESC";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$user['id']]);
        }
    } else {
        // Show all posts
        if ($hasUserId) {
            $query = "SELECT p.id, p.title, p.body, p.created_at, p.category, $viewsSelect
                             p.user_id as post_user_id,
                             u.username,
                             (SELECT COUNT(*) FROM replies r WHERE r.post_id = p.id) AS replies
                      FROM posts p 
                      LEFT JOIN users u ON u.id = p.user_id
                      WHERE (p.is_deleted = 0 OR p.is_deleted IS NULL)
                      ORDER BY p.created_at DESC";
        } elseif ($hasAuthorId) {
            $query = "SELECT p.id, p.title, p.body, p.created_at, p.category, $viewsSelect
                             p.author_id as post_user_id,
                             u.username,
                             (SELECT COUNT(*) FROM replies r WHERE r.post_id = p.id) AS replies
                      FROM posts p 
                      LEFT JOIN users u ON u.id = p.author_id
                      WHERE (p.is_deleted = 0 OR p.is_deleted IS NULL)
                      ORDER BY p.created_at DESC";
        } else {
            throw new Exception('Posts table missing user identification columns');
        }

        $stmt = $pdo->query($query);
    }

    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Posts query error: ' . $e->getMessage());
    $error_message = 'Database error: ' . $e->getMessage();
} catch (Exception $e) {
    error_log('Posts error: ' . $e->getMessage());
    $error_message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $view === 'my' ? 'My Posts' : 'Browse Posts' ?> - TechForum</title>
    <link rel="icon" href="assets/images/im.png" type="image/png">
    <link rel="stylesheet" href="assets/css/style1.css">
    <style>
        .posts-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 20px;
        }

        .posts-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .posts-header h2 {
            margin: 0;
            color: #2d3748;
            font-size: 1.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .view-toggle {
            display: flex;
            background: #f7fafc;
            border-radius: 10px;
            padding: 4px;
            gap: 4px;
            border: 1px solid #e2e8f0;
        }

        .view-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            background: transparent;
            color: #718096;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .view-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
        }

        .view-btn:hover:not(.active) {
            background: #edf2f7;
            color: #4a5568;
        }

        .btn-new-post {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-new-post:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
            text-decoration: none;
            color: white;
        }

        .posts-grid {
            list-style: none;
            padding: 0;
            display: grid;
            gap: 1.5rem;
            margin: 0;
        }

        .post-item {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            position: relative;
        }

        .post-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
            border-color: #cbd5e0;
        }

        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .post-title {
            flex: 1;
        }

        .post-title a {
            font-weight: 600;
            color: #2d3748;
            text-decoration: none;
            font-size: 1.25rem;
            line-height: 1.4;
            display: block;
            margin-bottom: 0.75rem;
            transition: color 0.3s ease;
        }

        .post-title a:hover {
            color: #667eea;
        }

        .post-meta {
            color: #718096;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .post-meta span {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .post-content {
            color: #4a5568;
            line-height: 1.6;
            margin: 1rem 0 1.5rem 0;
            font-size: 1rem;
        }

        .post-stats {
            display: flex;
            gap: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
            font-size: 0.875rem;
            color: #718096;
        }

        .post-stats span {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-weight: 500;
        }

        .post-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn-small {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-edit {
            background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
            color: white;
        }

        .btn-edit:hover {
            background: linear-gradient(135deg, #3182ce 0%, #2c5282 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(66, 153, 225, 0.4);
        }

        .btn-delete {
            background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
            color: white;
        }

        .btn-delete:hover {
            background: linear-gradient(135deg, #c53030 0%, #9c2626 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(229, 62, 62, 0.4);
        }

        .my-post-indicator {
            position: absolute;
            top: 0.6rem;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            padding: 0.35rem 1rem;
            border-radius: 999px;
            font-size: 0.7rem;
            letter-spacing: 0.5px;
            font-weight: 600;
            text-transform: uppercase;
            box-shadow: 0 2px 8px rgba(56, 161, 105, 0.35);
            z-index: 5;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #718096;
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border-radius: 15px;
            border: 2px dashed #cbd5e0;
        }

        .empty-state .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.6;
        }

        .empty-state h3 {
            margin: 0 0 1rem 0;
            font-size: 1.5rem;
            color: #4a5568;
        }

        .error-message {
            background: #fed7d7;
            color: #c53030;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #feb2b2;
        }

        .stats-bar {
            background: #f7fafc;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .stats-info {
            color: #718096;
            font-size: 0.875rem;
        }

        @media (max-width: 768px) {
            .posts-container {
                padding: 0 1rem;
            }

            .posts-header {
                flex-direction: column;
                align-items: stretch;
            }

            .view-toggle {
                justify-content: center;
            }

            .post-item {
                padding: 1.5rem;
            }

            .post-header {
                flex-direction: column;
                gap: 1rem;
            }

            .post-actions {
                justify-content: flex-start;
            }

            .post-meta {
                flex-direction: column;
                gap: 0.5rem;
                align-items: flex-start;
            }

            .post-stats {
                flex-direction: column;
                gap: 0.5rem;
            }

            .my-post-indicator {
                position: static;
                align-self: flex-start;
                margin-bottom: 0.5rem;
            }

            .stats-bar {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <?php require_once __DIR__ . '/includes/header.php'; ?>

    <main class="posts-container">
        <div class="posts-header">
            <h2>
                <?php if ($view === 'my'): ?>
                    📝 My Posts
                <?php else: ?>
                    🌐 Browse All Posts
                <?php endif; ?>
            </h2>

            <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <div class="view-toggle">
                    <a href="posts.php" class="view-btn <?= $view === 'all' ? 'active' : '' ?>">
                        🌐 All Posts
                    </a>
                    <a href="posts.php?view=my" class="view-btn <?= $view === 'my' ? 'active' : '' ?>">
                        📝 My Posts
                    </a>
                </div>

                <a href="new_post.php" class="btn-new-post">
                    <span>➕</span>
                    Create New Post
                </a>
            </div>
        </div>

        <?php flash_message(); ?>

        <?php if ($error_message): ?>
            <div class="error-message">
                <strong>Error:</strong> <?= h($error_message) ?>
                <br><small>Please check the server logs for more details.</small>
            </div>
        <?php endif; ?>

        <?php if (!empty($posts)): ?>
            <div class="stats-bar">
                <div class="stats-info">
                    <?php if ($view === 'my'): ?>
                        <strong>Your Posts:</strong> <?= count($posts) ?> posts found
                    <?php else: ?>
                        <strong>All Posts:</strong> <?= count($posts) ?> posts found
                    <?php endif; ?>
                </div>
                <div class="stats-info">
                    <a href="profile.php#posts" style="color: #667eea; text-decoration: none;">View Profile</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($posts) && !$error_message): ?>
            <div class="empty-state">
                <div class="icon">
                    <?= $view === 'my' ? '📝' : '🌐' ?>
                </div>
                <h3>
                    <?php if ($view === 'my'): ?>
                        You Haven't Created Any Posts Yet
                    <?php else: ?>
                        No Posts Found
                    <?php endif; ?>
                </h3>
                <p>
                    <?php if ($view === 'my'): ?>
                        Start sharing your thoughts and ideas with the community!
                    <?php else: ?>
                        Be the first to create a post and start the conversation!
                    <?php endif; ?>
                </p>
                <a href="new_post.php" class="btn-new-post" style="margin-top: 1rem;">
                    <span>✨</span>
                    <?= $view === 'my' ? 'Create Your First Post' : 'Create First Post' ?>
                </a>
            </div>
        <?php else: ?>
            <ul class="posts-grid">
                <?php foreach ($posts as $post): ?>
                    <li class="post-item">
                        <?php if ($post['post_user_id'] == $user['id']): ?>
                            <div class="my-post-indicator">My Post</div>
                        <?php endif; ?>

                        <div class="post-header">
                            <div class="post-title">
                                <a href="post.php?id=<?= (int)$post['id']; ?>">
                                    <?= h($post['title']); ?>
                                </a>
                                <div class="post-meta">
                                    <span>👤 <?= h($post['username'] ?? 'Unknown User'); ?></span>
                                    <span>📁 <?= h($post['category'] ?? 'General'); ?></span>
                                    <span>🕒 <?= date('M j, Y H:i', strtotime($post['created_at'])); ?></span>
                                </div>
                            </div>

                            <?php if ($post['post_user_id'] == $user['id']): ?>
                                <div class="post-actions">
                                    <a href="edit_post.php?id=<?= $post['id']; ?>" class="btn-small btn-edit">
                                        ✏️ Edit
                                    </a>
                                    <form method="POST" style="margin: 0; display: inline;" onsubmit="return confirm('Are you sure you want to delete this post? This action cannot be undone.');">
                                        <input type="hidden" name="action" value="delete_post">
                                        <input type="hidden" name="post_id" value="<?= $post['id']; ?>">
                                        <button type="submit" class="btn-small btn-delete">
                                            🗑️ Delete
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($post['body'])): ?>
                            <div class="post-content">
                                <?= nl2br(h(substr($post['body'], 0, 300))); ?>
                                <?php if (strlen($post['body']) > 300): ?>
                                    <a href="post.php?id=<?= $post['id']; ?>" style="color: #667eea; text-decoration: none;">... Read more</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="post-stats">
                            <span>💬 <?= (int)$post['replies']; ?> replies</span>
                            <span>👁️ <?= (int)($post['views'] ?? 0); ?> views</span>
                            <span>📅 <?= date('M j, Y', strtotime($post['created_at'])); ?></span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if (count($posts) >= 50): ?>
                <div style="text-align: center; margin: 3rem 0 2rem 0; color: #718096;">
                    <p>Showing recent posts • <a href="#" style="color: #667eea; text-decoration: none;">Load more posts</a></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script>
        // Add loading states to action buttons
        document.querySelectorAll('form[method="POST"]').forEach(form => {
            form.addEventListener('submit', function() {
                const button = form.querySelector('button[type="submit"]');
                if (button) {
                    button.style.opacity = '0.6';
                    button.style.pointerEvents = 'none';
                    const originalText = button.innerHTML;
                    button.innerHTML = originalText.includes('Delete') ? '⏳ Deleting...' : '⏳ Processing...';
                }
            });
        });

        // Smooth scroll for navigation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Auto-highlight user posts when coming from profile
        if (document.referrer.includes('profile.php')) {
            const myPosts = document.querySelectorAll('.my-post-indicator');
            if (myPosts.length > 0) {
                setTimeout(() => {
                    myPosts[0].closest('.post-item').style.border = '2px solid #667eea';
                    myPosts[0].closest('.post-item').scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }, 500);
            }
        }
    </script>
</body>

</html>