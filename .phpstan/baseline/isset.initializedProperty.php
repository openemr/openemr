<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Property Symfony\\\\Component\\\\DependencyInjection\\\\Argument\\\\LazyClosure\\:\\:\\$initializer in isset\\(\\) is not nullable nor uninitialized\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/dependency-injection/Argument/LazyClosure.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Symfony\\\\Component\\\\HttpClient\\\\Internal\\\\Canary\\:\\:\\$canceller in isset\\(\\) is not nullable nor uninitialized\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/http-client/Internal/Canary.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Symfony\\\\Component\\\\HttpKernel\\\\Event\\\\ControllerEvent\\:\\:\\$controller in isset\\(\\) is not nullable nor uninitialized\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/http-kernel/Event/ControllerEvent.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
