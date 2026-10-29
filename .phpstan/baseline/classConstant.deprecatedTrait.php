<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated trait Symfony\\\\Contracts\\\\Service\\\\ServiceSubscriberTrait\\:
since symfony/contracts v3\\.5, use ServiceMethodsSubscriberTrait instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/service-contracts/ServiceSubscriberTrait.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated trait Symfony\\\\Component\\\\VarExporter\\\\LazyGhostTrait\\:
since Symfony 7\\.3, use native lazy objects instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/var-exporter/ProxyHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated trait Symfony\\\\Component\\\\VarExporter\\\\LazyProxyTrait\\:
since Symfony 7\\.3, use native lazy objects instead$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/var-exporter/ProxyHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated trait Symfony\\\\Contracts\\\\Service\\\\ServiceSubscriberTrait\\:
since symfony/contracts v3\\.5, use ServiceMethodsSubscriberTrait instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-file-integrity/vendor/symfony/service-contracts/ServiceSubscriberTrait.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated trait Symfony\\\\Contracts\\\\Service\\\\ServiceSubscriberTrait\\:
since symfony/contracts v3\\.5, use ServiceMethodsSubscriberTrait instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/service-contracts/ServiceSubscriberTrait.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated trait Symfony\\\\Component\\\\VarExporter\\\\LazyGhostTrait\\:
since Symfony 7\\.3, use native lazy objects instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/var-exporter/LazyGhostTrait.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated trait Symfony\\\\Component\\\\VarExporter\\\\LazyProxyTrait\\:
since Symfony 7\\.3, use native lazy objects instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/var-exporter/LazyProxyTrait.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated trait Symfony\\\\Component\\\\VarExporter\\\\LazyProxyTrait\\:
since Symfony 7\\.3, use native lazy objects instead$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/var-exporter/ProxyHelper.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
