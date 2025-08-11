<?php
// Copy to admin_config.php (which is gitignored) and customize securely.
// Generate strong values: use long random strings (>=48 chars for token).

define('ADMIN_ACCESS_TOKEN', 'REPLACE_WITH_LONG_RANDOM_TOKEN');

define('ADMIN_USERNAME', 'your_admin_username');
// Store a hashed password instead of plain where feasible; fallback to plain for current simple flow.
// For enhancement you could compare password_verify() with a stored hash.
define('ADMIN_PASSWORD', 'your_admin_password');
