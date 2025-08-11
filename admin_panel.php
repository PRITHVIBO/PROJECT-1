<?php
// If execution continues (it shouldn't), halt explicitly.
http_response_code(410); // Gone
echo 'This legacy admin_panel endpoint is deprecated. Use admin_access.php instead.';
exit;
