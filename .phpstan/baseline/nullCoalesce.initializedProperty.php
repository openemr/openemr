<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\DiscoverAndChange\\\\Assessments\\\\Models\\\\AssessmentGroup\\:\\:\\$assessments on left side of \\?\\? is not nullable nor uninitialized\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/src/Models/AssessmentGroup.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Symfony\\\\Component\\\\Console\\\\Command\\\\Command\\:\\:\\$definition on left side of \\?\\? is not nullable nor uninitialized\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/console/Command/Command.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Symfony\\\\Component\\\\Console\\\\Command\\\\Command\\:\\:\\$definition on left side of \\?\\? is not nullable nor uninitialized\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-file-integrity/vendor/symfony/console/Command/Command.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\HealthLink\\\\Bootstrap\\:\\:\\$moduleDirectoryName on left side of \\?\\? is not nullable nor uninitialized\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\IHI\\\\Tests\\\\Service\\\\IhiServiceTest\\:\\:\\$dbRecords on left side of \\?\\? is not nullable nor uninitialized\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/tests/Service/IhiServiceTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
