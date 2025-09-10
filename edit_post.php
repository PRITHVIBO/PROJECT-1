<?php
require_once 'init.php';
require_login();

$user = current_user();
$post_id = (int)($_GET['id'] ?? 0);

if ($post_id === 0) {
    redirect('posts.php');
}

// Get post details
$post = null;
$error_message = '';

try {
    // First try user_id column
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
    $stmt->execute([$post_id, $user['id']]);
    $post = $stmt->fetch();

    if (!$post) {
        // Try author_id column as fallback
        try {
            $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND author_id = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
            $stmt->execute([$post_id, $user['id']]);
            $post = $stmt->fetch();
        } catch (PDOException $e) {
            // author_id column doesn't exist
        }
    }

    if (!$post) {
        set_flash('Post not found or you do not have permission to edit it.');
        redirect('posts.php');
    }
} catch (PDOException $e) {
    error_log('Edit post fetch error: ' . $e->getMessage());
    set_flash('Database error occurred.');
    redirect('posts.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $category = trim($_POST['category'] ?? '');

    $errors = [];

    if (empty($title)) {
        $errors[] = 'Title is required';
    } elseif (strlen($title) > 200) {
        $errors[] = 'Title must be 200 characters or less';
    }

    if (empty($body)) {
        $errors[] = 'Post content is required';
    } elseif (strlen($body) > 10000) {
        $errors[] = 'Post content must be 10,000 characters or less';
    }

    if (empty($category)) {
        $errors[] = 'Category is required';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE posts SET title = ?, body = ?, category = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
            $success = $stmt->execute([$title, $body, $category, $post_id, $user['id']]);

            if (!$success || $stmt->rowCount() === 0) {
                // Try with author_id
                try {
                    $stmt = $pdo->prepare("UPDATE posts SET title = ?, body = ?, category = ?, updated_at = NOW() WHERE id = ? AND author_id = ?");
                    $success = $stmt->execute([$title, $body, $category, $post_id, $user['id']]);
                } catch (PDOException $e) {
                    $success = false;
                }
            }

            if ($success && $stmt->rowCount() > 0) {
                set_flash('Post updated successfully!');
                redirect('post.php?id=' . $post_id);
            } else {
                $errors[] = 'Failed to update post. You may not have permission.';
            }
        } catch (PDOException $e) {
            error_log('Update post error: ' . $e->getMessage());
            $errors[] = 'Database error occurred while updating post.';
        }
    }

    if (!empty($errors)) {
        $error_message = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - TechForum</title>
    <link rel="icon" href="assets/images/im.png" type="image/png">
    <link rel="stylesheet" href="assets/css/style1.css">
    <style>
        .edit-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 20px;
        }

        .edit-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .edit-header h2 {
            color: #2d3748;
            margin-bottom: 0.5rem;
            font-size: 1.75rem;
        }

        .edit-header p {
            color: #718096;
            margin: 0;
        }

        .edit-form {
            background: white;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #374151;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f9fafb;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-control.textarea {
            min-height: 200px;
            resize: vertical;
            font-family: inherit;
            line-height: 1.6;
        }

        select.form-control {
            cursor: pointer;
        }

        .error-message {
            background: #fed7d7;
            color: #c53030;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border: 1px solid #feb2b2;
            font-size: 0.875rem;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
            margin-top: 1.5rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #f7fafc;
            color: #4a5568;
            border: 1px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background: #edf2f7;
            color: #2d3748;
            text-decoration: none;
        }

        .btn-danger {
            background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(229, 62, 62, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(229, 62, 62, 0.4);
        }

        .post-preview {
            background: #f7fafc;
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 1rem;
            border: 1px solid #e2e8f0;
            display: none;
        }

        .post-preview.show {
            display: block;
        }

        .post-preview h4 {
            color: #2d3748;
            margin: 0 0 1rem 0;
            font-size: 1.25rem;
        }

        .post-preview .content {
            color: #4a5568;
            line-height: 1.6;
            white-space: pre-wrap;
        }

        .char-counter {
            text-align: right;
            font-size: 0.75rem;
            color: #718096;
            margin-top: 0.25rem;
        }

        .char-counter.warning {
            color: #d69e2e;
        }

        .char-counter.danger {
            color: #e53e3e;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            color: #718096;
        }

        .breadcrumb a {
            color: #667eea;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .breadcrumb a:hover {
            color: #4c51bf;
        }

        .breadcrumb .separator {
            color: #cbd5e0;
        }

        @media (max-width: 768px) {
            .edit-container {
                padding: 0 1rem;
                margin: 1rem auto;
            }

            .edit-form {
                padding: 1.5rem;
            }

            .form-actions {
                flex-direction: column-reverse;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <?php require_once __DIR__ . '/includes/header.php'; ?>

    <main class="edit-container">
        <div class="breadcrumb">
            <a href="dashboard.php">🏠 Dashboard</a>
            <span class="separator">›</span>
            <a href="posts.php">📋 Posts</a>
            <span class="separator">›</span>
            <a href="post.php?id=<?= $post_id ?>">📄 <?= h(substr($post['title'], 0, 30)) ?><?= strlen($post['title']) > 30 ? '...' : '' ?></a>
            <span class="separator">›</span>
            <span>✏️ Edit</span>
        </div>

        <div class="edit-header">
            <h2>✏️ Edit Post</h2>
            <p>Make changes to your post and click save when you're ready</p>
        </div>

        <?php if ($error_message): ?>
            <div class="error-message">
                <?= $error_message ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="edit-form">
            <div class="form-group">
                <label for="title">Post Title</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    class="form-control"
                    value="<?= h($post['title'] ?? '') ?>"
                    required
                    maxlength="200"
                    placeholder="Enter a compelling title for your post...">
                <div class="char-counter" id="title-counter">
                    <?= strlen($post['title'] ?? '') ?>/200 characters
                </div>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" class="form-control" required>
                    <option value="">Select a category...</option>
                    <option value="Technology" <?= ($post['category'] ?? '') === 'Technology' ? 'selected' : '' ?>>💻 Technology</option>
                    <option value="Programming" <?= ($post['category'] ?? '') === 'Programming' ? 'selected' : '' ?>>⌨️ Programming</option>
                    <option value="Web Development" <?= ($post['category'] ?? '') === 'Web Development' ? 'selected' : '' ?>>🌐 Web Development</option>
                    <option value="Mobile Development" <?= ($post['category'] ?? '') === 'Mobile Development' ? 'selected' : '' ?>>📱 Mobile Development</option>
                    <option value="Data Science" <?= ($post['category'] ?? '') === 'Data Science' ? 'selected' : '' ?>>📊 Data Science</option>
                    <option value="AI/Machine Learning" <?= ($post['category'] ?? '') === 'AI/Machine Learning' ? 'selected' : '' ?>>🤖 AI/Machine Learning</option>
                    <option value="DevOps" <?= ($post['category'] ?? '') === 'DevOps' ? 'selected' : '' ?>>⚙️ DevOps</option>
                    <option value="Cybersecurity" <?= ($post['category'] ?? '') === 'Cybersecurity' ? 'selected' : '' ?>>🔒 Cybersecurity</option>
                    <option value="Career" <?= ($post['category'] ?? '') === 'Career' ? 'selected' : '' ?>>💼 Career</option>
                    <option value="General" <?= ($post['category'] ?? '') === 'General' ? 'selected' : '' ?>>💬 General Discussion</option>
                </select>
            </div>

            <div class="form-group">
                <label for="body">Post Content</label>
                <textarea
                    id="body"
                    name="body"
                    class="form-control textarea"
                    required
                    maxlength="10000"
                    placeholder="Share your thoughts, ideas, questions, or insights with the community..."><?= h($post['body'] ?? '') ?></textarea>
                <div class="char-counter" id="body-counter">
                    <?= strlen($post['body'] ?? '') ?>/10,000 characters
                </div>
            </div>

            <div class="form-group">
                <button type="button" id="preview-btn" class="btn btn-secondary">
                    👁️ Preview Post
                </button>
                <div class="post-preview" id="post-preview">
                    <h4 id="preview-title">Post Title</h4>
                    <div class="content" id="preview-content">Post content will appear here...</div>
                </div>
            </div>

            <div class="form-actions">
                <a href="post.php?id=<?= $post_id ?>" class="btn btn-secondary">
                    ❌ Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    💾 Save Changes
                </button>
                <a href="posts.php?view=my" class="btn btn-secondary">
                    📝 My Posts
                </a>
            </div>
        </form>
    </main>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script>
        const titleInput = document.getElementById('title');
        const bodyInput = document.getElementById('body');
        const titleCounter = document.getElementById('title-counter');
        const bodyCounter = document.getElementById('body-counter');
        const previewBtn = document.getElementById('preview-btn');
        const postPreview = document.getElementById('post-preview');
        const previewTitle = document.getElementById('preview-title');
        const previewContent = document.getElementById('preview-content');

        // Character counters
        function updateCounter(input, counter, maxLength) {
            const current = input.value.length;
            const remaining = maxLength - current;
            counter.textContent = `${current}/${maxLength} characters`;

            counter.className = 'char-counter';
            if (remaining < 50) counter.classList.add('danger');
            else if (remaining < 150) counter.classList.add('warning');
        }

        titleInput.addEventListener('input', () => updateCounter(titleInput, titleCounter, 200));
        bodyInput.addEventListener('input', () => updateCounter(bodyInput, bodyCounter, 10000));

        // Preview functionality
        let previewVisible = false;
        previewBtn.addEventListener('click', function() {
            previewVisible = !previewVisible;

            if (previewVisible) {
                previewTitle.textContent = titleInput.value || 'Post Title';
                previewContent.textContent = bodyInput.value || 'Post content will appear here...';
                postPreview.classList.add('show');
                previewBtn.innerHTML = '🙈 Hide Preview';
            } else {
                postPreview.classList.remove('show');
                previewBtn.innerHTML = '👁️ Preview Post';
            }
        });

        // Auto-save warning
        let hasUnsavedChanges = false;
        const originalTitle = titleInput.value;
        const originalBody = bodyInput.value;

        titleInput.addEventListener('input', () => {
            hasUnsavedChanges = (titleInput.value !== originalTitle || bodyInput.value !== originalBody);
        });

        bodyInput.addEventListener('input', () => {
            hasUnsavedChanges = (titleInput.value !== originalTitle || bodyInput.value !== originalBody);
        });

        window.addEventListener('beforeunload', function(e) {
            if (hasUnsavedChanges) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                return e.returnValue;
            }
        });

        // Form submission handler
        document.querySelector('.edit-form').addEventListener('submit', function() {
            hasUnsavedChanges = false; // Prevent warning on submit

            const submitBtn = document.querySelector('.btn-primary');
            submitBtn.style.opacity = '0.6';
            submitBtn.style.pointerEvents = 'none';
            submitBtn.innerHTML = '⏳ Saving Changes...';
        });

        // Initialize counters
        updateCounter(titleInput, titleCounter, 200);
        updateCounter(bodyInput, bodyCounter, 10000);

        // Auto-focus title if empty
        if (!titleInput.value.trim()) {
            titleInput.focus();
        }
    </script>
</body>

</html>