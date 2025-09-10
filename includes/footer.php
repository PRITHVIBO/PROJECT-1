<footer style="background:linear-gradient(135deg,#1f2937,#111827);color:#e5e7eb;padding:20px 0;margin-top:auto;">
  <div style="max-width:1200px;margin:0 auto;padding:0 20px;display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;align-items:flex-start;">
    <div style="display:flex;align-items:center;gap:10px;">
      <img src="assets/images/im.png" alt="TechForum" width="36" height="36" style="display:block;border-radius:8px;background:#fff;object-fit:contain;box-shadow:0 2px 8px rgba(0,0,0,.3);" />
      <div>
        <div style="font-weight:700;color:#fff;">TechForum</div>
        <div style="font-size:.8rem;color:#9ca3af;">Discuss. Learn. Share.</div>
      </div>
    </div>

    <div>
      <div style="font-weight:600;color:#fff;margin-bottom:8px;">Quick Links</div>
      <div style="display:flex;flex-direction:column;gap:6px;font-size:.9rem;">
        <a href="index.php" style="color:#e5e7eb;text-decoration:none;">Home</a>
        <a href="popular.php" style="color:#e5e7eb;text-decoration:none;">Popular</a>
        <a href="categories.php" style="color:#e5e7eb;text-decoration:none;">Categories</a>
        <a href="about.php" style="color:#e5e7eb;text-decoration:none;">About</a>
      </div>
    </div>

    <div>
      <div style="font-weight:600;color:#fff;margin-bottom:8px;">Account</div>
      <div style="display:flex;flex-direction:column;gap:6px;font-size:.9rem;">
        <?php if (function_exists('is_logged_in') && is_logged_in()): ?>
          <a href="dashboard.php" style="color:#e5e7eb;text-decoration:none;">Dashboard</a>
          <a href="posts.php" style="color:#e5e7eb;text-decoration:none;">My Posts</a>
          <a href="logout.php" style="color:#e5e7eb;text-decoration:none;">Logout</a>
        <?php else: ?>
          <a href="auth.php" style="color:#e5e7eb;text-decoration:none;">Sign In / Sign Up</a>
          <a href="password_reset.php" style="color:#e5e7eb;text-decoration:none;">Password Reset</a>
        <?php endif; ?>
      </div>
    </div>

    <div>
      <div style="font-weight:600;color:#fff;margin-bottom:8px;">Stay Connected</div>
      <p style="margin:0 0 8px;color:#9ca3af;font-size:.9rem;">Made with <span style="color:#f87171;">❤</span> for the community.</p>
      <div style="display:flex;gap:10px;">
        <a href="index.php" title="Back to top" style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:#374151;color:#fff;text-decoration:none;">↑</a>
        <a href="auth.php" title="Account" style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:#374151;color:#fff;text-decoration:none;">👤</a>
      </div>
    </div>
  </div>
  <div style="max-width:1200px;margin:10px auto 0;padding:0 20px;border-top:1px solid rgba(255,255,255,.08);display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
    <p style="margin:6px 0;font-size:.85rem;color:#d1d5db;">&copy; <?php echo date('Y'); ?> TechForum. All rights reserved.</p>
    <p style="margin:6px 0;font-size:.75rem;color:#9ca3af;">Updated: <?php echo date('M d, Y'); ?></p>
  </div>
</footer>