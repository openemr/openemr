<?php declare(strict_types = 1);

// total 4 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to method file\\(\\) of deprecated class Lcobucci\\\\JWT\\\\Signer\\\\Key\\\\LocalFileReference\\:
please use \\{@see InMemory\\} instead$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Command/CreateClientCredentialsAssertionCommand.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method file\\(\\) of deprecated class Lcobucci\\\\JWT\\\\Signer\\\\Key\\\\LocalFileReference\\:
please use \\{@see InMemory\\} instead$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Unit/Common/Auth/OpenIDConnect/Grant/CustomClientCredentialsGrantTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
