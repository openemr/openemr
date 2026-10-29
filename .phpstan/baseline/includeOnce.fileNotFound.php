<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Path in include_once\\(\\) "demographics\\.php" is not a file or it does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module-config-settings/assets/interface/patient_file/summary/demographics_save.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
