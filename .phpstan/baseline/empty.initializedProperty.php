<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Validators\\\\BaseValidator\\:\\:\\$validator in empty\\(\\) is not falsy nor uninitialized\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Validators/BaseValidator.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
