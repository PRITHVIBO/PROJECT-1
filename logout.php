<?php
require_once __DIR__ . '/init.php';

$redirect_page = 'auth.php?msg=Logged+out';

// Check if admin was logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    $redirect_page = 'admin_access.php?msg=Admin+logged+out';
}

session_unset();
session_destroy();
redirect($redirect_page);
