<?php declare(strict_types = 1);

// total 1 error

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Match expression does not handle remaining value\\: mixed$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_sms_notification.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
