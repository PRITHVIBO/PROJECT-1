<?php
// Simple DB connectivity health check for local and InfinityFree
// Usage:
//   - Local: http://localhost/techforum/db_health.php
//   - Prod:  https://<your-domain>/db_health.php?token=ok
// Change the token below if you want a different secret.
$token = $_GET['token'] ?? '';
$allowWithoutToken = (($_SERVER['HTTP_HOST'] ?? '') === 'localhost' || strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') === 0);
if (!$allowWithoutToken && $token !== 'ok') {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Forbidden: missing or invalid token. Append ?token=ok";
    exit;
}

header('Content-Type: text/plain; charset=utf-8');

$startedAt = microtime(true);
require_once __DIR__ . '/config/db.php';

function mask($s)
{
    if ($s === null || $s === '') return '<empty>';
    $len = strlen($s);
    if ($len <= 4) return str_repeat('*', max(0, $len - 1)) . substr($s, -1);
    return substr($s, 0, 2) . str_repeat('*', $len - 4) . substr($s, -2);
}

$ok = true;
$errors = [];

try {
    $stmt = $pdo->query('SELECT 1');
    $one = $stmt->fetchColumn();
    if ((string)$one !== '1') {
        $ok = false;
        $errors[] = 'SELECT 1 did not return 1';
    }
} catch (Throwable $e) {
    $ok = false;
    $errors[] = 'Query failed: ' . $e->getMessage();
}

$elapsed = round((microtime(true) - $startedAt) * 1000);

echo ($ok ? 'OK' : 'FAIL') . "\n";

echo "php_version: " . PHP_VERSION . "\n";
try {
    echo "pdo_driver: " . ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) ?: 'unknown') . "\n";
    echo "mysql_server: " . ($pdo->getAttribute(PDO::ATTR_SERVER_VERSION) ?: 'unknown') . "\n";
} catch (Throwable $e) {
    echo "pdo_info_error: " . $e->getMessage() . "\n";
}

echo "db_host: " . (defined('DB_HOST') ? mask(DB_HOST) : '<undef>') . "\n";

echo "db_name: " . (defined('DB_NAME') ? mask(DB_NAME) : '<undef>') . "\n";

echo "db_user: " . (defined('DB_USER') ? mask(DB_USER) : '<undef>') . "\n";

echo "time_ms: $elapsed\n";

if (!$ok) {
    echo "errors:\n";
    foreach ($errors as $err) echo " - $err\n";
}
