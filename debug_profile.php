<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Profile Page</title>
    <link rel="icon" href="assets/images/im.png" type="image/png">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f8f9fa;
        }

        .debug-section {
            background: white;
            padding: 20px;
            margin: 10px 0;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .status-ok {
            color: #28a745;
        }

        .status-error {
            color: #dc3545;
        }

        .status-warning {
            color: #ffc107;
        }
    </style>
</head>

<body>
    <h1>🔍 Profile Page Debug</h1>

    <div class="debug-section">
        <h2>Current Status</h2>
        <p>Let me help you find the delete post button. Here's what to check:</p>

        <h3>Step-by-Step Guide:</h3>
        <ol>
            <li><strong>Login Required:</strong> Make sure you're logged in to your account</li>
            <li><strong>Go to Profile:</strong> Navigate to <code>http://localhost/techforum/profile.php</code></li>
            <li><strong>Click "My Posts" Tab:</strong> Click the third tab in the navigation</li>
            <li><strong>Create a Post:</strong> If you don't have any posts, create one first using "Create New Post" button</li>
            <li><strong>Find Delete Button:</strong> Look for a red "Delete" button on the right side of each post card</li>
        </ol>
    </div>

    <div class="debug-section">
        <h2>Delete Button Location</h2>
        <p>The delete button appears in the <strong>post header</strong> section of each post card:</p>
        <ul>
            <li><span class="status-ok">✅</span> Backend processing code: Lines 7-23 in profile.php</li>
            <li><span class="status-ok">✅</span> Frontend button code: Around line 470 in profile.php</li>
            <li><span class="status-ok">✅</span> Button styling: Red gradient with hover effects</li>
            <li><span class="status-ok">✅</span> Confirmation dialog: "Are you sure you want to delete this post?"</li>
        </ul>
    </div>

    <div class="debug-section">
        <h2>Quick Links</h2>
        <a href="profile.php" target="_blank" style="display:inline-block; background:#667eea; color:white; padding:10px 20px; text-decoration:none; border-radius:5px; margin:5px;">🔗 Profile Page</a>
        <a href="new_post.php" target="_blank" style="display:inline-block; background:#28a745; color:white; padding:10px 20px; text-decoration:none; border-radius:5px; margin:5px;">➕ Create New Post</a>
        <a href="auth.php" target="_blank" style="display:inline-block; background:#17a2b8; color:white; padding:10px 20px; text-decoration:none; border-radius:5px; margin:5px;">🔐 Login/Register</a>
    </div>

    <div class="debug-section">
        <h2>Expected Appearance</h2>
        <div style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; background: white;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                <div>
                    <div style="font-weight: bold; color: #2d3748; margin-bottom: 0.5rem; font-size: 1.1rem;">
                        Sample Post Title
                    </div>
                    <div style="font-size: 0.875rem; color: #718096;">
                        📁 Posted in General • 🕒 Jan 15, 2024 10:30
                    </div>
                </div>
                <button style="background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%); color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; font-weight: 500; cursor: pointer;">Delete</button>
            </div>
            <div style="color: #4a5568; margin: 1rem 0; line-height: 1.6;">
                This is what your post content would look like...
            </div>
            <div style="display: flex; gap: 1.5rem; font-size: 0.875rem; color: #718096; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                <span>💬 5 replies</span>
                <span>👁️ 25 views</span>
            </div>
        </div>
        <p style="margin-top: 1rem;"><strong>The red "Delete" button should appear in the top-right corner of each post card like shown above.</strong></p>
    </div>
</body>

</html>