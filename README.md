# TechForum

Modern lightweight PHP/MySQL discussion platform.

## Features
- User registration/login
- Admin two-layer security (token + fixed credentials)
- Create / edit / delete posts (soft delete)
- Replies with ownership checks
- Dynamic schema detection (supports user_id / author_id variants)
- View counter with throttling
- Profile management & external post management page
- About page & documentation

## Quick Start
1. Clone repository
2. Copy `config/config.example.php` to `config/config.php` and set database credentials
3. Create MySQL database `techforum` (or update name in config)
4. Import or let the app create necessary tables (see migration scripts like `migrate_add_views.php`)
5. Serve via Apache (e.g., XAMPP) at `http://localhost/techforum`

## Admin Access
See `ADMIN_SECURITY_README.md` for current token and credentials. CHANGE THEM IN PRODUCTION.

## Environment / Secrets
Real `config.php` and `db.php` are excluded from version control. Do not commit secrets.

## Contributing
PRs welcome. Please open an issue for major changes first.

## License
(Choose a license and add here, e.g. MIT)
