<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between int\\<1, max\\> and 0 is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CarecoordinationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between int\\<1, max\\> and 0 is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between 2\\|int\\<4, max\\> and 1 is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between int\\<1, max\\> and 0 is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/daily_summary_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between int\\<1, max\\> and 0 is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between int\\<2, max\\> and 1 is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/encounter_events.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between 4 and 0 is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between int\\<1, max\\> and 0 is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between int\\<2, max\\> and 1 is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between int\\<1, max\\> and 0 is always true\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/patient.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between int\\<1, max\\> and 0 is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateImportDispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between int\\<2, max\\> and 1 is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateParse.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between int\\<1, max\\> and 0 is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Utils/SQLUpgradeService.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between 2 and 0 is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/eventdispatcher/RestApiEventHookExample/Module.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
