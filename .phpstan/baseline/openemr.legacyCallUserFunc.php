<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Use uniform variable syntax \\$callable\\(\\.\\.\\.\\$args\\) or the argument unpacking operator instead of call_user_func\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Carecoordination/Model/PhpCcdaBuilder/CcdaTemplateEngine.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
