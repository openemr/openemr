<?php declare(strict_types = 1);

// total 1 error

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Path in include_once\\(\\) "\\.\\./\\.\\./custom/fee_sheet_codes\\.php" is not a file or it does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/printed_fee_sheet.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
