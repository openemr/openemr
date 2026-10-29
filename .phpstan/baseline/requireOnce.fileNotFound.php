<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Path in require_once\\(\\) "Doctrine/Common/ClassLoader\\.php" is not a file or it does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/orm/bin/doctrine-pear.php',
];
$ignoreErrors[] = [
    'message' => '#^Path in require_once\\(\\) "lib/byte_safe_strings\\.php" is not a file or it does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/vendor/paragonie/random_compat/psalm-autoload.php',
];
$ignoreErrors[] = [
    'message' => '#^Path in require_once\\(\\) "lib/cast_to_int\\.php" is not a file or it does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/vendor/paragonie/random_compat/psalm-autoload.php',
];
$ignoreErrors[] = [
    'message' => '#^Path in require_once\\(\\) "lib/error_polyfill\\.php" is not a file or it does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/vendor/paragonie/random_compat/psalm-autoload.php',
];
$ignoreErrors[] = [
    'message' => '#^Path in require_once\\(\\) "other/ide_stubs/libsodium\\.php" is not a file or it does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/vendor/paragonie/random_compat/psalm-autoload.php',
];
$ignoreErrors[] = [
    'message' => '#^Path in require_once\\(\\) "\\.\\./\\.\\./interface/globals\\.php" is not a file or it does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-opit-uk/assets/interface/code_systems/list_staged.php',
];
$ignoreErrors[] = [
    'message' => '#^Path in require_once\\(\\) "\\.\\./\\.\\./interface/globals\\.php" is not a file or it does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-opit-uk/assets/interface/code_systems/snomed_howto.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
