<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\!\\= between \'\' and \'\' will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/CAMOS/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\!\\= between 0 and 0 will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\!\\= between \'\' and \'\' will always evaluate to false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/reports/audit_log_tamper_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\!\\= between \'\' and \'\' will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/ssl_certificates_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\!\\= between 0 and 0 will always evaluate to false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/fpdf/fpdf.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\!\\= between \'cols\' and \'cols\' will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_table.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\!\\= between 1 and 1 will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/AuthHash.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
