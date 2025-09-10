<?php
require_once 'init.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>About & How It Works - TechForum</title>
    <link rel="icon" href="assets/images/im.png" type="image/png">
    <link rel="stylesheet" href="assets/css/style1.css">
    <style>
        .about-wrapper {
            max-width: 1000px;
            margin: 2.5rem auto;
            padding: 2rem 2.2rem;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
            backdrop-filter: blur(3px);
        }

        h1 {
            margin-top: 0;
            font-size: 2rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        h2 {
            margin-top: 2.2rem;
            font-size: 1.3rem;
            color: #2d3748;
            position: relative;
            padding-left: .75rem;
        }

        h2:before {
            content: '';
            position: absolute;
            left: 0;
            top: 0.35rem;
            width: 4px;
            height: 1.1rem;
            background: linear-gradient(180deg, #667eea, #764ba2);
            border-radius: 2px;
        }

        p {
            line-height: 1.6;
            color: #4a5568;
            font-size: 0.98rem;
        }

        ul {
            margin: 0 0 1.25rem 1.25rem;
            padding: 0;
            color: #4a5568;
        }

        li {
            margin: .4rem 0;
            line-height: 1.45;
        }

        .callout {
            background: linear-gradient(135deg, #f7fafc, #edf2f7);
            border: 1px solid #e2e8f0;
            padding: 1.1rem 1.25rem;
            border-radius: 12px;
            margin: 1.5rem 0;
            font-size: .9rem;
            display: flex;
            gap: .85rem;
        }

        .steps {
            display: grid;
            gap: 1rem;
            margin: 1.25rem 0;
        }

        .step {
            background: #f8faff;
            border: 1px solid #e2e8f0;
            padding: 1rem 1.1rem;
            border-radius: 12px;
            position: relative;
        }

        .step:before {
            content: attr(data-step);
            position: absolute;
            top: -10px;
            left: 12px;
            background: #667eea;
            color: #fff;
            font-size: .65rem;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 20px;
            letter-spacing: .5px;
        }

        code,
        .mono {
            background: #2d3748;
            color: #fff;
            padding: 2px 6px;
            border-radius: 6px;
            font-size: .75rem;
        }

        .faq-item {
            margin: 1.1rem 0;
        }

        .faq-item summary {
            cursor: pointer;
            font-weight: 600;
            color: #2d3748;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.1rem;
            margin: 1.25rem 0;
        }

        .card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 1.1rem 1rem;
            box-shadow: 0 4px 14px -4px rgba(0, 0, 0, .06);
        }

        a.inline {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        a.inline:hover {
            text-decoration: underline;
        }

        @media (max-width:720px) {
            .about-wrapper {
                padding: 1.4rem 1.2rem;
            }

            h1 {
                font-size: 1.6rem;
            }
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    <main class="about-wrapper">
        <h1>About TechForum</h1>
        <p>TechForum is a lightweight discussion platform where developers share knowledge, ask questions, and collaborate. This page explains what you can do here and how everything works behind the scenes.</p>

        <h2>Core Concepts</h2>
        <ul>
            <li><strong>Posts</strong>: Long-form questions, tutorials, announcements, or ideas you publish.</li>
            <li><strong>Replies</strong>: Conversation messages under a post (threaded chronologically).</li>
            <li><strong>Views</strong>: Each unique visit (per user/session window) increments the post counter.</li>
            <li><strong>Categories</strong>: Optional grouping (e.g., Programming, DevOps, AI).</li>
            <li><strong>Soft Delete</strong>: Deleting a post only flags it (kept for moderation).</li>
        </ul>

        <h2>Quick Start</h2>
        <div class="steps">
            <div class="step" data-step="STEP 1">Register or sign in from the top navigation. You must be logged in to create posts or reply.</div>
            <div class="step" data-step="STEP 2">Click <span class="mono">Create New Post</span> (or from profile actions) and fill in title, category, and content.</div>
            <div class="step" data-step="STEP 3">Browse or filter posts and open one to read. The view counter increments once per user every 30 minutes.</div>
            <div class="step" data-step="STEP 4">Add replies to contribute. You can edit or delete only your own content.</div>
            <div class="step" data-step="STEP 5">Manage your existing posts from the <a href="posts.php?view=my" class="inline">My Posts view</a>.</div>
        </div>

        <h2>How Posting Works</h2>
        <p>When you submit a post the system stores it with your user ID. If the <code>views</code> column exists it starts at 0 and increments when unique sessions open the detail page. Replies are linked to a post via <code>post_id</code>. Removing a post flips an <code>is_deleted</code> flag—content is hidden from normal listings but preserved for potential recovery.</p>

        <h2>Permissions</h2>
        <div class="grid-2">
            <div class="card"><strong>Guests</strong><br>Can read public pages and the about page.</div>
            <div class="card"><strong>Users</strong><br>Create posts, reply, delete their own posts (soft), delete their own replies.</div>
            <div class="card"><strong>Admins</strong><br>Send messages, moderate, potentially restore or hard-delete content.</div>
        </div>

        <h2>Best Practices</h2>
        <ul>
            <li>Use clear, searchable titles.</li>
            <li>Break long questions into bullet points.</li>
            <li>Credit sources when sharing solutions.</li>
            <li>Be respectful—constructive and concise replies help everyone.</li>
            <li>Report issues or missing features to an admin.</li>
        </ul>

        <h2>Behind the Scenes</h2>
        <ul>
            <li><strong>Security</strong>: Sessions + prepared statements to prevent SQL injection.</li>
            <li><strong>Soft Deletes</strong>: Keeps audit trail; can be migrated to hard delete later.</li>
            <li><strong>Adaptive Schema</strong>: Works if posts table uses <code>user_id</code> or legacy <code>author_id</code>.</li>
            <li><strong>View Throttle</strong>: Avoids inflated counts by limiting increments to once per 30 mins per session.</li>
        </ul>

        <h2>FAQ</h2>
        <div class="faq-item">
            <details>
                <summary>Why doesn't my view count go up every refresh?</summary>
                <p>We throttle increments to keep stats meaningful. Wait 30 minutes or open from a different browser/session.</p>
            </details>
        </div>
        <div class="faq-item">
            <details>
                <summary>Can I edit a post?</summary>
                <p>Yes. Open your post and use the Edit button in the list view.</p>
            </details>
        </div>
        <div class="faq-item">
            <details>
                <summary>How do I recover a deleted post?</summary>
                <p>Currently only admins can recover soft-deleted items. Contact support if needed.</p>
            </details>
        </div>

        <h2>Need Help?</h2>
        <p>Reach out via an admin message or open a placeholder help post describing your issue.</p>
    </main>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>

</html>