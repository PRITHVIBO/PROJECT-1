<?php
// Helper functions for the application

function is_logged_in() {
    return !empty($_SESSION['user']);
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: auth.php?msg=Please+login+to+continue');
        exit;
    }
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function flash_message() {
    if (!empty($_SESSION['flash'])) {
        echo '<div class="flash-message" style="background:#d4edda;color:#155724;padding:10px;border-radius:4px;margin-bottom:20px;">' . h($_SESSION['flash']) . '</div>';
        unset($_SESSION['flash']);
    }
}

function set_flash($message) {
    $_SESSION['flash'] = $message;
}

function format_date($date) {
    return date('M j, Y g:i A', strtotime($date));
}
// Forum categories central definition
function get_categories(): array {
    return [
        'Soft Skills',
        'Technology',
        'Academics',
        'Sports',
        'Lifestyle'
    ];
}

function sanitize_category(string $cat): string {
    $cats = get_categories();
    return in_array($cat, $cats, true) ? $cat : $cats[0];
}

/**
 * Process content to render Mermaid diagrams and apply basic formatting
 * Converts ```mermaid code blocks to HTML div elements for Mermaid.js to process
 */
function process_content($content) {
    // Process Mermaid diagrams first, before HTML escaping
    $pattern = '/```mermaid\s*\n(.*?)\n```/s';
    $processed_content = preg_replace_callback($pattern, function($matches) {
        static $diagram_id = 0;
        $diagram_id++;
        $diagram_code = trim($matches[1]);
        
        // Create a placeholder that won't be affected by nl2br
        return '<div class="mermaid" id="mermaid-diagram-' . $diagram_id . '">' . 
               htmlspecialchars($diagram_code, ENT_QUOTES, 'UTF-8') . 
               '</div>';
    }, $content);
    
    // Now process the content in parts, avoiding the mermaid divs
    $parts = preg_split('/(<div class="mermaid"[^>]*>.*?<\/div>)/s', $processed_content, -1, PREG_SPLIT_DELIM_CAPTURE);
    
    $result = '';
    foreach ($parts as $part) {
        if (preg_match('/^<div class="mermaid"/', $part)) {
            // This is a mermaid diagram, keep as is
            $result .= $part;
        } else {
            // This is regular content, escape and apply line breaks
            $result .= nl2br(h($part));
        }
    }
    
    return $result;
}

/**
 * Process content for preview (truncated) display
 * Handles Mermaid diagrams properly when truncating
 */
function process_content_preview($content, $max_length = 300) {
    // Check if content contains Mermaid diagrams
    if (strpos($content, '```mermaid') !== false) {
        // If it contains Mermaid diagrams, show a preview message instead of truncating
        return '<div style="background: #f8f9fa; padding: 0.75rem; border-radius: 6px; color: #6c757d; font-style: italic;">
                    📊 This post contains diagrams. <a href="#" style="color: #667eea;">Click to view full content with diagrams</a>
                </div>';
    }
    
    // For regular content, use normal truncation
    $escaped_content = h($content);
    if (strlen($escaped_content) > $max_length) {
        return nl2br(substr($escaped_content, 0, $max_length));
    }
    return nl2br($escaped_content);
}
?>