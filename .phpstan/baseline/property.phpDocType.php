<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^PHPDoc type string of property C_AbstractClickmap\\:\\:\\$template_dir is not the same as PHPDoc type array\\<string\\>\\|string of overridden property Controller\\:\\:\\$template_dir\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/clickmap/C_AbstractClickmap.php',
];
$ignoreErrors[] = [
    'message' => '#^PHPDoc type array\\<string\\>\\|string of property Controller\\:\\:\\$template_dir is not the same as PHPDoc type array of overridden property Smarty\\:\\:\\$template_dir\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Controller.class.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
