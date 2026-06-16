<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Function edih_sftp_connect not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/test_edih_sftp_files.php',
];
$ignoreErrors[] = [
    'message' => '#^Function generic_sql_affected_rows not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Uuid/UuidRegistry.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
