<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^While loop condition is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/ereqs/ereq_universal_form.php',
];
$ignoreErrors[] = [
    'message' => '#^While loop condition is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/procedure_tools/labcorp/ereq_form.php',
];
$ignoreErrors[] = [
    'message' => '#^While loop condition is always true\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/DataSet.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
