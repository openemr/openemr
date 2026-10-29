<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Path in include\\(\\) "add_edit_issue_medication_fragment\\.php" is not a file or it does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module-config-settings/assets/interface/patient_file/summary/add_edit_issue.php',
];
$ignoreErrors[] = [
    'message' => '#^Path in include\\(\\) "add_edit_issue_medication_fragment\\.php" is not a file or it does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/assets/interface/patient_file/summary/add_edit_issue.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
