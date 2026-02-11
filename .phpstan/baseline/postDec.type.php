<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Cannot use \\-\\- on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/drug_inventory.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use \\-\\- on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/content_parser.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use \\-\\- on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnincludes/Date/Calc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use \\-\\- on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use \\-\\- on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/src/Acl/Controller/AclController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use \\-\\- on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use \\-\\- on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use \\-\\- on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/smtp/smtp.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
