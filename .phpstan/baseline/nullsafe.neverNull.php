<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Using nullsafe property access "\\?\\-\\>nodeValue" on left side of \\?\\? is unnecessary\\. Use \\-\\> instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaComponentParseHelpers.php',
];
$ignoreErrors[] = [
    'message' => '#^Using nullsafe property access "\\?\\-\\>nodeValue" on left side of \\?\\? is unnecessary\\. Use \\-\\> instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Cda/ClinicalNoteParser.php',
];
$ignoreErrors[] = [
    'message' => '#^Using nullsafe property access "\\?\\-\\>textContent" on left side of \\?\\? is unnecessary\\. Use \\-\\> instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/Services/Cda/ClinicalNoteParser.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
