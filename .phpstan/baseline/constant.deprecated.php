<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Use of constant DATE_ISO8601 is deprecated\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Repository/TeleHealthUserRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^8\\.5 use FILTER_UNSAFE_RAW instead$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/edihistory/test_edih_sftp_files.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
