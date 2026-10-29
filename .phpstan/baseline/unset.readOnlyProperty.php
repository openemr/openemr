<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Cannot unset readonly Symfony\\\\Component\\\\DependencyInjection\\\\Argument\\\\LazyClosure\\:\\:\\$service property\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/dependency-injection/Argument/LazyClosure.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
