<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Using nullsafe property access "\\?\\-\\>city" on left side of \\?\\? is unnecessary\\. Use \\-\\> instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/practice/ins_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Using nullsafe property access "\\?\\-\\>country" on left side of \\?\\? is unnecessary\\. Use \\-\\> instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/practice/ins_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Using nullsafe property access "\\?\\-\\>line1" on left side of \\?\\? is unnecessary\\. Use \\-\\> instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/practice/ins_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Using nullsafe property access "\\?\\-\\>line2" on left side of \\?\\? is unnecessary\\. Use \\-\\> instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/practice/ins_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Using nullsafe property access "\\?\\-\\>plusFour" on left side of \\?\\? is unnecessary\\. Use \\-\\> instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/practice/ins_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Using nullsafe property access "\\?\\-\\>state" on left side of \\?\\? is unnecessary\\. Use \\-\\> instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/practice/ins_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Using nullsafe property access "\\?\\-\\>zip" on left side of \\?\\? is unnecessary\\. Use \\-\\> instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/practice/ins_search.php',
];
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
