# TechForum

Modern lightweight PHP/MySQL discussion platform for threaded discussion and lightweight community knowledge sharing.

---
## 🌐 Live Web View (Conceptual Walkthrough)
Below is an at-a-glance experience of what a user (and admin) sees when the site is running locally at `http://localhost/techforum`.

| Page | Route | Purpose | Key UI Elements |
|------|-------|---------|-----------------|
| Landing / Home | `index.php` | Entry point, featured or recent posts | Top nav, hero header, link to sign in / browse |
| Auth Portal | `auth.php` | Combined Sign In / Sign Up + Password Recovery toggle | Animated panel switch, Forgot Password flow, Admin Access button, Visit Forum link |
| Password Reset Guide | `password_reset.php` | User submits email for manual admin reset | Instructional panel, form submission |
| Posts Listing (All/My) | `posts.php?view=all|my` | Browse or manage posts | Filter toggle, edit/delete (owner), view counts, reply counts, soft delete badge |
| Single Post View | `post.php?id=###` | Read post + replies, increments views | View counter, reply form, owner/admin delete buttons |
| New Post | `new_post.php` | Create a post | Title/body/category inputs, validation |
| Edit Post | `edit_post.php?id=###` | Modify existing post | Live preview (if implemented), ownership check |
| Popular Posts | `popular.php` | (Optional) Highlighted/popular content | Views-based ordering |
| Categories | `categories.php` | Category navigation (if categories implemented) | List/grid of categories |
| Profile | `profile.php` | User info & admin messages (posts managed externally) | Update profile form, Manage My Posts button |
| About | `about.php` | How-to, platform usage & FAQ | Documentation blocks |
| Admin Access (Token Gate) | `admin_access.php` | Layer 1 (token) + Layer 2 (fixed credentials) | Token form then credential form |
| Admin Dashboard | `admin_dashboard.php` | Administrative management | Placeholder to extend |

> Screenshots Placeholder: Create a `docs/screenshots/` directory and add annotated images. Then embed: `![Auth Portal](docs/screenshots/auth.png)` etc.

---
## ✨ Features
- User registration & login (session-based)
- Two-layer admin security (gateway token + fixed superadmin credentials)
- Create / edit / soft delete posts (owner & admin control)
- Replies with ownership validation
- Dynamic schema detection (`user_id` vs `author_id`, optional `views` column)
- View counter with session throttling to prevent spam inflation
- External post management (My Posts via `posts.php?view=my` instead of profile tab)
- About / help page for onboarding
- Migration helper (`migrate_add_views.php`) for adding `views` column lazily

---
## 🚀 Quick Start
1. Clone repository
2. Copy `config/config.example.php` → `config/config.php` and set DB credentials
3. Create MySQL database (default name: `techforum`)
4. Run any migration scripts if needed (e.g. `migrate_add_views.php` once)
5. Place project under Apache root (e.g. `C:/xampp/htdocs/techforum`)
6. Navigate to `http://localhost/techforum/auth.php` to register or sign in
7. (Admin) Access secure portal via Auth page → “Admin Access” (enter token + credentials)

---
## 🔐 Admin Access Overview
Two steps:
1. Security Token (gateway) – prevents casual discovery of admin login
2. Fixed Credentials (superadmin) – establishes admin session

See `ADMIN_SECURITY_README.md` for full operational and rotation guidance.

> IMPORTANT: The repository ships with placeholder constants. Replace them immediately for any deployed environment.

---
## 🧭 User Journey Flow
1. Visitor lands on Home or Auth.
2. Signs up (username/email/password) → redirected to Dashboard or Posts list.
3. Creates a new post (optional) with title/body/category.
4. Other users browse `posts.php`, click a post → `post.php?id=##` (views increment).
5. Users reply; owners/admin can delete their own posts/replies (soft delete where implemented).
6. User manages their posts via `posts.php?view=my` (edit/delete actions visible only for owners/admin).
7. Admin uses secure access path to moderate and review system state.

---
## 🧱 Architecture Snapshot
| Layer | Components |
|-------|-----------|
| Presentation | PHP templates/pages (`*.php`), CSS in `assets/css`, JS behaviors in `assets/js` |
| Application | Session auth, soft delete logic, dynamic SQL query adaptation |
| Data | MySQL tables: `users`, `posts`, `replies`, optional `admin_messages`, plus migrations |
| Security | Token-gated admin portal, credential checks, session flags, input sanitization (`h()` helper) |

Dynamic schema logic inspects table columns (e.g., `DESCRIBE posts`) to determine whether to reference `user_id` or `author_id`, and whether a `views` column exists—enabling flexible deployment against slightly divergent schemas.

---
## 🗄 Database (Typical Minimal Schema)
High-level (pseudocode):
```
users(id, username, email, password_hash, created_at, ...)
posts(id, user_id|author_id, title, body, category, views?, is_deleted, created_at, updated_at, deleted_at)
replies(id, post_id, user_id, body, created_at, is_deleted)
admin_messages(id, user_id, message, created_at)  // optional
```
`views` may be added post-deployment via `migrate_add_views.php`.

---
## 🔎 Security Features
- Hidden admin interface (token gate)
- Hardcoded (placeholder) constants for bootstrap simplicity
- Session-based auth + admin session segregation
- Soft delete avoids immediate data loss
- Basic output escaping helper for XSS mitigation
- Throttled view counting to reduce artificial inflation

Recommended Hardening Next:
- CSRF tokens on state-changing forms
- Password hashing (ensure using `password_hash()` in auth handlers)
- Rate limiting for login & token form
- Centralized logging & IP-based alerting

---
## 💡 UI Highlights
- Animated auth panel transitions
- Gradient buttons & subtle hover elevation
- “My Post” badge styling in listings
- Responsive navigation bar with ABOUT entry, uppercase branding

---
## 🛠 Development Notes
- Local stack: XAMPP (Apache + MySQL) or any LAMP equivalent
- No Composer dependencies currently – pure core PHP
- Add packages (e.g., for dotenv or routing) as project evolves

### Common Scripts / Pages
| File | Purpose |
|------|---------|
| `init.php` | Bootstraps sessions & includes config/db |
| `includes/functions.php` | Utility functions (escaping, auth helpers) |
| `posts.php` | Central listing & management of posts |
| `post.php` | Single post view + replies + view increment logic |
| `edit_post.php` | Edit existing post (ownership enforced) |
| `migrate_add_views.php` | Adds `views` column if absent |
| `admin_access.php` | Token gate + admin credential form |

---
## 📈 Roadmap (Ideas)
- Full-text search & category filtering
- Pagination / infinite scroll for large post sets
- Role-based multi-admin system with granular permissions
- Rich text or Markdown editor
- Email integration for password resets & notifications
- Dark mode toggle
- API endpoints (JSON) for headless integration

---
## 🧪 Testing Suggestions
Manual sanity checklist:
1. Register new user → login → create post → verify appears in All & My views
2. Open post in new tab twice (within throttle window) → views increments only once
3. Delete post (soft) → verify hidden from standard listing (unless logic includes deleted)
4. Attempt editing another user’s post → should be blocked
5. Admin token wrong → access denied, log entry in PHP error log

---
## 🔐 Secrets & Environment
`config/config.php` and `config/db.php` are intentionally gitignored. Use the example files to configure your local environment. Never commit real credentials.

---
## 🤝 Contributing
1. Fork
2. Create feature branch (`git checkout -b feature/your-feature`)
3. Commit changes (`git commit -m "Add feature"`)
4. Push (`git push origin feature/your-feature`)
5. Open Pull Request

Please include a brief rationale for structural changes.

---
## 📄 License
MIT (see `LICENSE` file)

---
## 📬 Support / Questions
Open an issue on GitHub or start a discussion thread once discussions are enabled.

---
## 📝 Changelog (Condensed)
- Added dynamic posts schema detection
- Implemented soft delete & edit capability
- Added view counter & migration
- Moved My Posts management external to profile
- Added About page & admin security hardening
- Sanitized credentials for public repository

---
## 🧭 Quick Demo Script (Narrative)
1. Register a user (auth.php) → redirected to dashboard.
2. Create a post (new_post.php) with a category.
3. View All Posts (posts.php) – note view count 0.
4. Click post → single view page increments count.
5. Refresh within throttle window → view count unchanged.
6. Return to posts list → toggle to My Posts to edit/delete.
7. Use Admin Access (token + creds) to reach dashboard.

Enjoy building on TechForum! 🧩
