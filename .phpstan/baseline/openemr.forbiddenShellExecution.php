<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Shell execution function exec\\(\\) is forbidden\\. exec\\(\\) executes a shell command\\. Use Symfony\\\\Component\\\\Process\\\\Process with array arguments as a safer alternative\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../contrib/util/ccda_import/import_ccda.php',
];
$ignoreErrors[] = [
    'message' => '#^Shell execution function exec\\(\\) is forbidden\\. exec\\(\\) executes a shell command\\. Use Symfony\\\\Component\\\\Process\\\\Process with array arguments as a safer alternative\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Shell execution function exec\\(\\) is forbidden\\. exec\\(\\) executes a shell command\\. Use Symfony\\\\Component\\\\Process\\\\Process with array arguments as a safer alternative\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Shell execution function exec\\(\\) is forbidden\\. exec\\(\\) executes a shell command\\. Use Symfony\\\\Component\\\\Process\\\\Process with array arguments as a safer alternative\\.$#',
    'count' => 13,
    'path' => __DIR__ . '/../../interface/fax/fax_dispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Shell execution function passthru\\(\\) is forbidden\\. passthru\\(\\) executes a command and passes raw output\\. Use Symfony\\\\Component\\\\Process\\\\Process with array arguments as a safer alternative\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/fax/fax_view.php',
];
$ignoreErrors[] = [
    'message' => '#^Shell execution function exec\\(\\) is forbidden\\. exec\\(\\) executes a shell command\\. Use Symfony\\\\Component\\\\Process\\\\Process with array arguments as a safer alternative\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/fax/faxq.php',
];
$ignoreErrors[] = [
    'message' => '#^Shell execution function exec\\(\\) is forbidden\\. exec\\(\\) executes a shell command\\. Use Symfony\\\\Component\\\\Process\\\\Process with array arguments as a safer alternative\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/backup.php',
];
$ignoreErrors[] = [
    'message' => '#^Shell execution function system\\(\\) is forbidden\\. system\\(\\) executes a shell command and displays output\\. Use Symfony\\\\Component\\\\Process\\\\Process with array arguments as a safer alternative\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/backuplog.php',
];
$ignoreErrors[] = [
    'message' => '#^Shell execution function exec\\(\\) is forbidden\\. exec\\(\\) executes a shell command\\. Use Symfony\\\\Component\\\\Process\\\\Process with array arguments as a safer alternative\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CcdaServiceDocumentRequestor.php',
];
$ignoreErrors[] = [
    'message' => '#^Shell execution function popen\\(\\) is forbidden\\. popen\\(\\) opens a pipe to a process\\. Use Symfony\\\\Component\\\\Process\\\\Process with array arguments as a safer alternative\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CcdaServiceDocumentRequestor.php',
];
$ignoreErrors[] = [
    'message' => '#^Shell execution function exec\\(\\) is forbidden\\. exec\\(\\) executes a shell command\\. Use Symfony\\\\Component\\\\Process\\\\Process with array arguments as a safer alternative\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Installer.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Shell execution function exec\\(\\) is forbidden\\. exec\\(\\) executes a shell command\\. Use Symfony\\\\Component\\\\Process\\\\Process with array arguments as a safer alternative\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/Mime_Types.php',
];
$ignoreErrors[] = [
    'message' => '#^Shell execution function exec\\(\\) is forbidden\\. exec\\(\\) executes a shell command\\. Use Symfony\\\\Component\\\\Process\\\\Process with array arguments as a safer alternative\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/System/System.php',
];
$ignoreErrors[] = [
    'message' => '#^Shell execution function popen\\(\\) is forbidden\\. popen\\(\\) opens a pipe to a process\\. Use Symfony\\\\Component\\\\Process\\\\Process with array arguments as a safer alternative\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/System/System.php',
];
$ignoreErrors[] = [
    'message' => '#^Shell execution function proc_open\\(\\) is forbidden\\. proc_open\\(\\) opens a process file pointer\\. Use Symfony\\\\Component\\\\Process\\\\Process with array arguments as a safer alternative\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/System/System.php',
];
$ignoreErrors[] = [
    'message' => '#^Shell execution function exec\\(\\) is forbidden\\. exec\\(\\) executes a shell command\\. Use Symfony\\\\Component\\\\Process\\\\Process with array arguments as a safer alternative\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaValidateDocuments.php',
];
$ignoreErrors[] = [
    'message' => '#^Shell execution function popen\\(\\) is forbidden\\. popen\\(\\) opens a pipe to a process\\. Use Symfony\\\\Component\\\\Process\\\\Process with array arguments as a safer alternative\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaValidateDocuments.php',
];
$ignoreErrors[] = [
    'message' => '#^Shell execution function exec\\(\\) is forbidden\\. exec\\(\\) executes a shell command\\. Use Symfony\\\\Component\\\\Process\\\\Process with array arguments as a safer alternative\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/VersionFileTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
