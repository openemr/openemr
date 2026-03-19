<?php declare(strict_types = 1);

// total 5 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Path in include\\(\\) "footer\\.php" is not a file or it does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnHTML.php',
];
$ignoreErrors[] = [
    'message' => '#^Path in include\\(\\) "header\\.php" is not a file or it does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnHTML.php',
];
$ignoreErrors[] = [
    'message' => '#^Path in include\\(\\) "\\.\\./\\.\\./\\.\\./custom/demographics_print\\.php" is not a file or it does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_print.php',
];
$ignoreErrors[] = [
    'message' => '#^Path in include\\(\\) "views/esign_signature_log\\.php" is not a file or it does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ESign/DbRow/Signable.php',
];
$ignoreErrors[] = [
    'message' => '#^Path in include\\(\\) "getmxrr\\.php" is not a file or it does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_functions.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
