<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Admin gate
if (!is_admin()) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

// Load email config for display
if (file_exists(__DIR__ . '/config/email_config.php')) {
    require_once __DIR__ . '/config/email_config.php';
}

$resultMsg = null;
$sent = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = trim($_POST['to'] ?? '');
    $provider = trim($_POST['provider'] ?? '');
    $subject = 'TechForum test email';
    $body = '<p>This is a test email from TechForum admin test page.</p>';

    if ($provider) {
        // Temporarily override provider just for this request
        $GLOBALS['EMAIL_PROVIDER_OVERRIDE'] = $provider;
    }

    if (!$to) {
        $resultMsg = 'Please provide a destination email address.';
        $sent = false;
    } else {
        //$sent = send_email($to, $subject, $body);
        $resultMsg = $sent ? 'Email sent successfully.' : 'Failed to send email. Check configuration and error logs.';
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Email Test</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 1rem;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 6px
        }

        .row {
            display: flex;
            gap: 1rem;
            align-items: center
        }

        label {
            min-width: 120px
        }

        input,
        select {
            padding: .5rem;
            width: 100%
        }

        .btn {
            padding: .5rem 1rem
        }

        .ok {
            color: green
        }

        .err {
            color: #c00
        }

        code {
            background: #f6f8fa;
            padding: 2px 4px;
            border-radius: 4px
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Email Configuration Test</h2>
        <form method="post" class="form">
            <div class="row">
                <label for="to">To</label>
                <input type="email" name="to" id="to" placeholder="you@example.com" required>
            </div>
            <div class="row">
                <label for="provider">Provider</label>
                <select name="provider" id="provider">
                    <option value="">Auto</option>
                    <option value="sendgrid">SendGrid</option>
                    <option value="mailgun">Mailgun</option>
                    <option value="mail">PHP mail()</option>
                </select>
            </div>
            <button class="btn" type="submit">Send Test Email</button>
        </form>
        <?php if ($resultMsg !== null): ?>
            <p class="<?= $sent ? 'ok' : 'err' ?>"><?= htmlspecialchars($resultMsg) ?></p>
        <?php endif; ?>

        <h3>Current Settings</h3>
        <ul>
            <li>EMAIL_PROVIDER: <code><?= defined('EMAIL_PROVIDER') ? htmlspecialchars(constant('EMAIL_PROVIDER')) : '' ?></code></li>
            <li>EMAIL_FROM: <code><?= defined('EMAIL_FROM') ? htmlspecialchars(constant('EMAIL_FROM')) : '' ?></code></li>
            <li>EMAIL_FROM_NAME: <code><?= defined('EMAIL_FROM_NAME') ? htmlspecialchars(constant('EMAIL_FROM_NAME')) : '' ?></code></li>
            <li>SENDGRID_API_KEY set: <code><?= (defined('SENDGRID_API_KEY') && constant('SENDGRID_API_KEY')) ? 'yes' : 'no' ?></code></li>
            <li>MAILGUN_API_KEY set: <code><?= (defined('MAILGUN_API_KEY') && constant('MAILGUN_API_KEY')) ? 'yes' : 'no' ?></code></li>
            <li>MAILGUN_DOMAIN: <code><?= defined('MAILGUN_DOMAIN') ? htmlspecialchars(constant('MAILGUN_DOMAIN')) : '' ?></code></li>
        </ul>
        <p>Tip: Edit <code>config/email_config.php</code> to add API keys. You can set <code>EMAIL_DEBUG</code> to <code>true</code> to log failures to error_log.</p>
    </div>
</body>

</html>