<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke \'documentUploadPostP…\' but it\'s not a callable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke Laminas\\\\View\\\\Helper\\\\HelperInterface but it might not be a callable\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/src/Acl/Controller/AclController.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke non\\-falsy\\-string but it might not be a callable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke mixed but it\'s not a callable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Controller/InstallerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke mixed but it\'s not a callable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_sdoh.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke mixed but it\'s not a callable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/criteria.tab.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke mixed but it\'s not a callable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/appointments.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke mixed but it\'s not a callable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/php-barcode.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke non\\-falsy\\-string but it might not be a callable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke mixed but it\'s not a callable\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke mixed but it\'s not a callable\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke non\\-falsy\\-string but it might not be a callable\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke mixed but it\'s not a callable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.is_secure.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke mixed but it\'s not a callable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.is_trusted.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke mixed but it\'s not a callable\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.process_cached_inserts.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke mixed but it\'s not a callable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.read_cache_file.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke mixed but it\'s not a callable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.run_insert_handler.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke mixed but it\'s not a callable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.write_cache_file.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke mixed but it\'s not a callable\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/savant/Savant3.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke mixed but it\'s not a callable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/Mime_Types.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke mixed but it\'s not a callable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/ParseERA.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke mixed but it\'s not a callable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Common.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke mixed but it\'s not a callable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/HttpRestRouteHandler.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke mixed but it\'s not a callable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/oeHttpRequest.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke mixed but it\'s not a callable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Http/oeOAuth.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke mixed but it\'s not a callable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke mixed but it\'s not a callable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/VitalsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke mixed but it\'s not a callable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/api/InternalApiTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Trying to invoke mixed but it\'s not a callable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/api/InternalFhirTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
