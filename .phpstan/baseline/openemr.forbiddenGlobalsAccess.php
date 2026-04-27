<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$GLOBALS is forbidden\\. Use OEGlobalsBag\\:\\:getInstance\\(\\)\\-\\>get\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/vitals/C_FormVitals.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$GLOBALS is forbidden\\. Use OEGlobalsBag\\:\\:getInstance\\(\\)\\-\\>get\\(\\) instead\\.$#',
    'count' => 31,
    'path' => __DIR__ . '/../../interface/globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$GLOBALS is forbidden\\. Use OEGlobalsBag\\:\\:getInstance\\(\\)\\-\\>get\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/ajax/sql_server_status.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$GLOBALS is forbidden\\. Use OEGlobalsBag\\:\\:getInstance\\(\\)\\-\\>get\\(\\) instead\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../library/classes/Installer.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$GLOBALS is forbidden\\. Use OEGlobalsBag\\:\\:getInstance\\(\\)\\-\\>get\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.assign_smarty_interface.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$GLOBALS is forbidden\\. Use OEGlobalsBag\\:\\:getInstance\\(\\)\\-\\>get\\(\\) instead\\.$#',
    'count' => 15,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$GLOBALS is forbidden\\. Use OEGlobalsBag\\:\\:getInstance\\(\\)\\-\\>get\\(\\) instead\\.$#',
    'count' => 27,
    'path' => __DIR__ . '/../../sites/default/config.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
