<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Use MyMailer instead of mail\\(\\), so sends respect the configured EMAIL_METHOD/SMTP settings\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/monolog/monolog/src/Monolog/Handler/NativeMailerHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Use MyMailer instead of mail\\(\\), so sends respect the configured EMAIL_METHOD/SMTP settings\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/vendor/monolog/monolog/src/Monolog/Handler/NativeMailerHandler.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
