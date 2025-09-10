<?php
// Collect empty or blank files into archive_unused/empty_blanks
// Usage:
//  - Preview (default): open this script in a browser to see what would be moved
//  - Apply: append ?apply=1 to actually move files

declare(strict_types=1);

session_start();
require_once __DIR__ . '/includes/functions.php';

$root = realpath(__DIR__);
$archiveBase = $root . DIRECTORY_SEPARATOR . 'archive_unused' . DIRECTORY_SEPARATOR . 'empty_blanks';
$apply = isset($_GET['apply']) && $_GET['apply'] == '1';

// Exclude paths (relative to root) that should never be moved even if empty
$excludedPaths = [
    'archive_unused',
    '.git',
];

// File patterns to exclude (case-insensitive)
$excludedPatterns = [
    // keep core entry points even if empty by mistake
    '/^index\.php$/i',
    '/^init\.php$/i',
    '/^config' . preg_quote(DIRECTORY_SEPARATOR, '/') . 'db\.php$/i',
];

function is_path_excluded(string $pathRel, array $excludedPaths): bool
{
    foreach ($excludedPaths as $ex) {
        if ($pathRel === $ex || strpos($pathRel, rtrim($ex, '/\\') . DIRECTORY_SEPARATOR) === 0) {
            return true;
        }
    }
    return false;
}

function is_file_pattern_excluded(string $pathRel, array $patterns): bool
{
    foreach ($patterns as $rx) {
        if (preg_match($rx, $pathRel)) return true;
    }
    return false;
}

function is_blank_file(string $absPath): bool
{
    if (!is_file($absPath)) return false;
    $size = filesize($absPath);
    if ($size === 0) return true; // empty
    // Avoid loading huge files fully
    if ($size > 1024 * 1024) return false; // >1MB treated as non-blank
    $contents = @file_get_contents($absPath);
    if ($contents === false) return false;
    // If all whitespace after trimming
    return trim($contents) === '';
}

$toMove = [];

$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($it as $file) {
    /** @var SplFileInfo $file */
    if (!$file->isFile()) continue;
    $abs = $file->getPathname();
    // relative path from root
    $rel = ltrim(str_replace($root, '', $abs), '\\/');

    // Skip this script itself
    if (basename($abs) === basename(__FILE__)) continue;
    // Skip excluded paths
    if (is_path_excluded($rel, $excludedPaths)) continue;
    // Skip the archive target
    if (strpos($rel, 'archive_unused' . DIRECTORY_SEPARATOR) === 0) continue;
    // Skip patterns
    if (is_file_pattern_excluded($rel, $excludedPatterns)) continue;

    if (is_blank_file($abs)) {
        $toMove[] = $rel;
    }
}

// Ensure archive directory
if (!is_dir($archiveBase)) {
    @mkdir($archiveBase, 0777, true);
}

$moved = [];
$errors = [];

if ($apply) {
    foreach ($toMove as $rel) {
        $src = $root . DIRECTORY_SEPARATOR . $rel;
        // Preserve folder structure
        $dest = $archiveBase . DIRECTORY_SEPARATOR . $rel;
        $destDir = dirname($dest);
        if (!is_dir($destDir)) {
            @mkdir($destDir, 0777, true);
        }
        // If destination exists, append timestamp
        if (file_exists($dest)) {
            $pi = pathinfo($dest);
            $alt = $pi['dirname'] . DIRECTORY_SEPARATOR . $pi['filename'] . '-' . date('Ymd_His') . (isset($pi['extension']) ? '.' . $pi['extension'] : '');
            $dest = $alt;
        }
        if (@rename($src, $dest)) {
            $moved[] = [$rel, str_replace($root . DIRECTORY_SEPARATOR, '', $dest)];
        } else {
            $errors[] = $rel;
        }
    }
}

// Simple HTML output
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collect Empty/Blank Files</title>
    <link rel="icon" href="assets/images/im.png" type="image/png">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 1rem 2rem;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 1.5rem;
        }

        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .1);
            padding: 1rem 1.5rem;
            margin-bottom: 1rem;
        }

        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .btn {
            display: inline-block;
            background: #667eea;
            color: #fff;
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
        }

        .muted {
            color: #666;
        }

        ul {
            margin: .5rem 0 0 1.25rem;
        }
    </style>
    <script>
        function applyMove() {
            if (confirm('Move all listed empty/blank files into archive_unused/empty_blanks?')) {
                window.location.search = '?apply=1';
            }
        }
    </script>
</head>

<body>
    <div class="header"><strong>Collect Empty/Blank Files</strong></div>
    <div class="container">
        <div class="card">
            <p>Root: <code><?= h($root) ?></code></p>
            <p>Archive: <code><?= h($archiveBase) ?></code></p>
            <?php if (!$apply): ?>
                <p class="muted">Preview mode. Click the button to move files.</p>
                <button class="btn" onclick="applyMove()">Move Files</button>
            <?php else: ?>
                <p><strong>Move executed.</strong></p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3 style="margin:.25rem 0;">Files to Move (<?= count($toMove) ?>)</h3>
            <?php if (empty($toMove)): ?>
                <p class="muted">No empty/blank files found.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($toMove as $rel): ?>
                        <li><code><?= h($rel) ?></code></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <?php if ($apply): ?>
            <div class="card">
                <h3 style="margin:.25rem 0;">Moved (<?= count($moved) ?>)</h3>
                <?php if (empty($moved)): ?>
                    <p class="muted">No files moved.</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($moved as $pair): ?>
                            <li><code><?= h($pair[0]) ?></code> → <code><?= h($pair[1]) ?></code></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <?php if (!empty($errors)): ?>
                <div class="card">
                    <h3 style="margin:.25rem 0; color:#c33;">Errors (<?= count($errors) ?>)</h3>
                    <ul>
                        <?php foreach ($errors as $rel): ?>
                            <li><code><?= h($rel) ?></code></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>

</html>