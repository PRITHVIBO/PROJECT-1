<?php
// Helper functions for the application

function is_logged_in()
{
    return !empty($_SESSION['user']);
}

function current_user()
{
    return $_SESSION['user'] ?? null;
}

function require_login()
{
    if (!is_logged_in()) {
        header('Location: auth.php?msg=Please+login+to+continue');
        exit;
    }
}

function redirect($url)
{
    header("Location: $url");
    exit;
}

function h($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function flash_message()
{
    if (!empty($_SESSION['flash'])) {
        echo '<div class="flash-message" style="background:#d4edda;color:#155724;padding:10px;border-radius:4px;margin-bottom:20px;">' . h($_SESSION['flash']) . '</div>';
        unset($_SESSION['flash']);
    }
}

function set_flash($message)
{
    $_SESSION['flash'] = $message;
}

function is_admin(): bool
{
    return !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function format_date($date)
{
    return date('M j, Y g:i A', strtotime($date));
}
// Forum categories central definition
function get_categories(): array
{
    return [
        'Soft Skills',
        'Technology',
        'Academics',
        'Sports',
        'Lifestyle'
    ];
}

function sanitize_category(string $cat): string
{
    $cats = get_categories();
    return in_array($cat, $cats, true) ? $cat : $cats[0];
}
