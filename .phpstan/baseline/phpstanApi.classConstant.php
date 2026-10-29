<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Accessing PHPStan\\\\PharAutoloader\\:\\:class is not covered by backward compatibility promise\\. The class might change in a minor PHPStan version\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-file-integrity/vendor/phpstan/phpstan/bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Accessing PHPStan\\\\PharAutoloader\\:\\:class is not covered by backward compatibility promise\\. The class might change in a minor PHPStan version\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/phpstan/phpstan/bootstrap.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
