<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Access to undefined constant static\\(Symfony\\\\Component\\\\Cache\\\\Adapter\\\\AbstractTagAwareAdapter\\)\\:\\:NS_SEPARATOR\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/cache/Adapter/AbstractTagAwareAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to undefined constant ReflectionProperty\\:\\:IS_PRIVATE_SET\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/var-exporter/Internal/Hydrator.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to undefined constant ReflectionProperty\\:\\:IS_PRIVATE_SET\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/var-exporter/Internal/LazyObjectRegistry.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to undefined constant ReflectionProperty\\:\\:IS_PROTECTED_SET\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/var-exporter/Internal/LazyObjectRegistry.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to undefined constant ReflectionProperty\\:\\:IS_ABSTRACT\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/var-exporter/ProxyHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to undefined constant ReflectionProperty\\:\\:IS_FINAL\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/var-exporter/ProxyHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to undefined constant ReflectionProperty\\:\\:IS_VIRTUAL\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/var-exporter/ProxyHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to undefined constant static\\(PhpParser\\\\ParserAbstract\\)\\:\\:T_ECHO\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-file-integrity/vendor/nikic/php-parser/lib/PhpParser/ParserAbstract.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to undefined constant static\\(PhpParser\\\\ParserAbstract\\)\\:\\:T_ECHO\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/vendor/nikic/php-parser/lib/PhpParser/ParserAbstract.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to undefined constant static\\(PhpParser\\\\ParserAbstract\\)\\:\\:T_ECHO\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/nikic/php-parser/lib/PhpParser/ParserAbstract.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to undefined constant ReflectionProperty\\:\\:IS_PRIVATE_SET\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/var-exporter/Internal/Hydrator.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to undefined constant ReflectionProperty\\:\\:IS_PRIVATE_SET\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/var-exporter/Internal/LazyObjectRegistry.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to undefined constant ReflectionProperty\\:\\:IS_PROTECTED_SET\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/var-exporter/Internal/LazyObjectRegistry.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to undefined constant ReflectionProperty\\:\\:IS_ABSTRACT\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/var-exporter/ProxyHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to undefined constant ReflectionProperty\\:\\:IS_FINAL\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/var-exporter/ProxyHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to undefined constant ReflectionProperty\\:\\:IS_VIRTUAL\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/var-exporter/ProxyHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to undefined constant static\\(PhpParser\\\\ParserAbstract\\)\\:\\:T_ECHO\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/vendor/nikic/php-parser/lib/PhpParser/ParserAbstract.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to undefined constant static\\(PhpParser\\\\ParserAbstract\\)\\:\\:T_ECHO\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-opit-compatibility/vendor/nikic/php-parser/lib/PhpParser/ParserAbstract.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
