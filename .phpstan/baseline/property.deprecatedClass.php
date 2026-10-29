<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Access to property \\$expression of deprecated class Symfony\\\\Component\\\\HttpFoundation\\\\ExpressionRequestMatcher\\:
since Symfony 6\\.2, use "Symfony\\\\Component\\\\HttpFoundation\\\\RequestMatcher\\\\ExpressionRequestMatcher" instead$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/http-foundation/ExpressionRequestMatcher.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to property \\$language of deprecated class Symfony\\\\Component\\\\HttpFoundation\\\\ExpressionRequestMatcher\\:
since Symfony 6\\.2, use "Symfony\\\\Component\\\\HttpFoundation\\\\RequestMatcher\\\\ExpressionRequestMatcher" instead$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/http-foundation/ExpressionRequestMatcher.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to property \\$attributes of deprecated class Symfony\\\\Component\\\\HttpFoundation\\\\RequestMatcher\\:
since Symfony 6\\.2, use ChainRequestMatcher instead$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/http-foundation/RequestMatcher.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to property \\$host of deprecated class Symfony\\\\Component\\\\HttpFoundation\\\\RequestMatcher\\:
since Symfony 6\\.2, use ChainRequestMatcher instead$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/http-foundation/RequestMatcher.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to property \\$ips of deprecated class Symfony\\\\Component\\\\HttpFoundation\\\\RequestMatcher\\:
since Symfony 6\\.2, use ChainRequestMatcher instead$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/http-foundation/RequestMatcher.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to property \\$methods of deprecated class Symfony\\\\Component\\\\HttpFoundation\\\\RequestMatcher\\:
since Symfony 6\\.2, use ChainRequestMatcher instead$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/http-foundation/RequestMatcher.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to property \\$path of deprecated class Symfony\\\\Component\\\\HttpFoundation\\\\RequestMatcher\\:
since Symfony 6\\.2, use ChainRequestMatcher instead$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/http-foundation/RequestMatcher.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to property \\$port of deprecated class Symfony\\\\Component\\\\HttpFoundation\\\\RequestMatcher\\:
since Symfony 6\\.2, use ChainRequestMatcher instead$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/http-foundation/RequestMatcher.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to property \\$schemes of deprecated class Symfony\\\\Component\\\\HttpFoundation\\\\RequestMatcher\\:
since Symfony 6\\.2, use ChainRequestMatcher instead$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/http-foundation/RequestMatcher.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
