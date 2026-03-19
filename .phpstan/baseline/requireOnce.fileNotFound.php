<?php declare(strict_types = 1);

// total 4 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Path in require_once\\(\\) "PEAR/PHPExcel\\.php" is not a file or it does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ExportUtility.php',
];
$ignoreErrors[] = [
    'message' => '#^Path in require_once\\(\\) "verysimple/RSS/IRSSFeedItem\\.php" is not a file or it does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Path in require_once\\(\\) "verysimple/RSS/Writer\\.php" is not a file or it does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Path in require_once\\(\\) "\\.\\./\\.\\./library/options\\.inc\\.php" is not a file or it does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/templates/PatientListView.tpl.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
