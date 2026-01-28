<?php declare(strict_types = 1);

// total 5 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\<" between int\\<1, 99\\> and 100 is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/find_group_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\<" between int\\<0, 99\\> and 100 is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/find_patient_popup.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\<" between int\\<0, 24\\> and 25 is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/document_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\<" between int\\<1, 499\\> and 500 is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/TableDefinitions/ExportTableDefinition.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\<" between false and 15 is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/CreateReleaseChangelogCommand.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
