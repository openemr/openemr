<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Unary operation "\\-" on mixed results in an error\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/barcode_label.php',
];
$ignoreErrors[] = [
    'message' => '#^Unary operation "\\-" on mixed results in an error\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Unary operation "\\-" on mixed results in an error\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../library/classes/fpdf/fpdf.php',
];
$ignoreErrors[] = [
    'message' => '#^Unary operation "\\-" on mixed results in an error\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/php-barcode.php',
];
$ignoreErrors[] = [
    'message' => '#^Unary operation "\\-" on mixed results in an error\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_email_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Unary operation "\\-" on mixed results in an error\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_sms_notification.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
