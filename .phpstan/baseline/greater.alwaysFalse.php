<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between 0 and 500 is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CarecoordinationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between 0\\|\'\' and 500 is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CarecoordinationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between 0 and 0 is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between 0 and 0 is always false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/block.textformat.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between 0 and 5 is always false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between 1 and 1 is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/UuidUserAccount.php',
];
$ignoreErrors[] = [
    'message' => '#^Comparison operation "\\>" between 0 and 0 is always false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/eventdispatcher/RestApiEventHookExample/Module.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
