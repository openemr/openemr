<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Parameter mixed of print cannot be converted to string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/billing/edih_main.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter mixed of print cannot be converted to string\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter mixed of print cannot be converted to string\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/rx_print.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter mixed of print cannot be converted to string\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
