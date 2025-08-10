<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- theme removed -->
    <link rel="icon" type="image/png" href="assets/images/im.png">
    <link rel="shortcut icon" type="image/png" href="assets/images/im.png">
</head>

<body>
    <header style="background:#667eea;color:white;padding:1rem 0;">
        <div class="tf-bar" style="max-width:1200px;margin:0 auto;display:flex;justify-content:space-between;align-items:center;padding:0 20px;gap:1rem;">
            <h1 style="margin:0;font-size:1.35rem;"><a href="index.php" style="color:white;text-decoration:none;">TechForum</a></h1>
            <button id="menuToggle" aria-label="Menu" style="display:none;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);color:#fff;padding:.5rem .75rem;border-radius:6px;cursor:pointer;font-size:.9rem;">Menu</button>
            <nav id="mainNav" style="display:flex;flex-wrap:wrap;align-items:center;gap:.5rem;">
                <?php if (is_logged_in()): ?>
                    <a href="dashboard.php" class="nav-link">DASHBOARD</a>
                <?php endif; ?>
                <a href="index.php" class="nav-link">HOME</a>
                <a href="categories.php" class="nav-link">CATEGORIES</a>
                <a href="popular.php" class="nav-link">POPULAR</a>
                <a href="about.php" class="nav-link">ABOUT</a>
                <?php if (is_logged_in()): ?>
                    <a href="posts.php" class="nav-link">POSTS</a>
                    <a href="logout.php" class="nav-link">LOGOUT</a>
                <?php else: ?>
                    <a href="auth.php" class="nav-link">SIGN IN</a>
                    <a href="admin_access.php" class="nav-link" style="background: rgba(255,255,255,0.15); padding: 0.25rem 0.5rem; border-radius: 4px; margin-left: 0.5rem;">ADMIN</a>
                <?php endif; ?>
            </nav>
        </div>
        <style>
            body {
                background: url('assets/images/dsi.jpg') center/cover fixed no-repeat;
                margin: 0;
                font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
            }

            main {
                flex: 1 0 auto;
            }

            h2,
            h3 {
                color: #5a3fc5;
                margin-top: 0;
            }

            .nav-link {
                color: #fff;
                text-decoration: none;
                margin: 0 .9rem;
                font-size: .9rem;
                display: inline-block;
                letter-spacing: .5px;
                font-weight: 600;
            }

            .nav-link:hover {
                text-decoration: underline;
            }

            /* Mobile slide-in menu */
            #navOverlay {
                display: none;
            }

            @media (max-width:760px) {
                #menuToggle {
                    display: inline-block !important;
                }

                #mainNav {
                    position: fixed;
                    top: 0;
                    left: 0;
                    height: 100vh;
                    width: 230px;
                    background: #667eea;
                    padding: 1rem 1rem 2rem;
                    display: flex;
                    flex-direction: column;
                    align-items: flex-start;
                    gap: .25rem;
                    transform: translateX(-100%);
                    transition: transform .3s ease;
                    box-shadow: 2px 0 12px rgba(0, 0, 0, .25);
                    z-index: 1000;
                    border-top-right-radius: 12px;
                    border-bottom-right-radius: 12px;
                }

                #mainNav.open {
                    transform: translateX(0);
                }

                .nav-link {
                    margin: .35rem 0;
                    padding: .5rem .6rem;
                    width: 100%;
                    border-radius: 6px;
                }

                .nav-link:hover {
                    background: rgba(255, 255, 255, .15);
                    text-decoration: none;
                }

                #navOverlay {
                    position: fixed;
                    inset: 0;
                    background: rgba(0, 0, 0, .45);
                    backdrop-filter: blur(2px);
                    z-index: 999;
                    opacity: 0;
                    transition: opacity .3s;
                    display: block;
                    pointer-events: none;
                }

                #navOverlay.show {
                    opacity: 1;
                    pointer-events: auto;
                }

                body.nav-open {
                    overflow: hidden;
                }

                /* Hide inline desktop nav spacing */
                .tf-bar nav {
                    position: static;
                }
            }
        </style>
        <script>
            (function() {
                const btn = document.getElementById('menuToggle');
                const nav = document.getElementById('mainNav');
                const overlay = document.getElementById('navOverlay');

                function toggle() {
                    const open = nav.classList.toggle('open');
                    overlay.classList.toggle('show', open);
                    document.body.classList.toggle('nav-open', open);
                    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                }
                if (btn && nav && overlay) {
                    btn.addEventListener('click', toggle);
                    overlay.addEventListener('click', () => {
                        if (nav.classList.contains('open')) toggle();
                    });
                    // Close on ESC
                    window.addEventListener('keydown', e => {
                        if (e.key === 'Escape' && nav.classList.contains('open')) toggle();
                    });
                }
            })();
        </script>
    </header>
    <div id="navOverlay" aria-hidden="true"></div>