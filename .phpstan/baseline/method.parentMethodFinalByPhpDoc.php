<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Method Symfony\\\\Component\\\\HttpFoundation\\\\Response@anonymous/interface/modules/custom_modules/oe\\-module\\-dc\\-assessments/vendor/symfony/console/Debug/CliRequest\\.php\\:51\\:\\:getStatusCode\\(\\) overrides @final method Symfony\\\\Component\\\\HttpFoundation\\\\Response\\:\\:getStatusCode\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/console/Debug/CliRequest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Symfony\\\\Component\\\\HttpFoundation\\\\Response@anonymous/interface/modules/custom_modules/oe\\-module\\-file\\-integrity/vendor/symfony/console/Debug/CliRequest\\.php\\:51\\:\\:getStatusCode\\(\\) overrides @final method Symfony\\\\Component\\\\HttpFoundation\\\\Response\\:\\:getStatusCode\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-file-integrity/vendor/symfony/console/Debug/CliRequest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
