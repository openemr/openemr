<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to function rtrim\\(\\) on a separate line has no effect\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function array_merge\\(\\) on a separate line has no effect\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_uploads.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
