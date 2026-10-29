<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Function gen_hl7_order not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/src/Controller/ProcedureListReportsController.php',
];
$ignoreErrors[] = [
    'message' => '#^Function send_hl7_order not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/src/Controller/ProcedureListReportsController.php',
];
$ignoreErrors[] = [
    'message' => '#^Function Amp\\\\Dns\\\\resolver not found\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/http-client/Internal/AmpResolverV4.php',
];
$ignoreErrors[] = [
    'message' => '#^Function Amp\\\\Dns\\\\resolve not found\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/http-client/Internal/AmpResolverV5.php',
];
$ignoreErrors[] = [
    'message' => '#^Function Amp\\\\Future\\\\awaitFirst not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/http-client/Response/AmpResponseV5.php',
];
$ignoreErrors[] = [
    'message' => '#^Function Amp\\\\delay not found\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/http-client/Response/AmpResponseV5.php',
];
$ignoreErrors[] = [
    'message' => '#^Used function Amp\\\\Future\\\\awaitFirst not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/http-client/Response/AmpResponseV5.php',
];
$ignoreErrors[] = [
    'message' => '#^Used function Amp\\\\delay not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/http-client/Response/AmpResponseV5.php',
];
$ignoreErrors[] = [
    'message' => '#^Function request_parse_body not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/http-foundation/Request.php',
];
$ignoreErrors[] = [
    'message' => '#^Function escape_limit not found\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module-config-settings/assets/interface/patient_file/history/encounters.php',
];
$ignoreErrors[] = [
    'message' => '#^Function escape_limit not found\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/public/patient_file/history/encounters.php',
];
$ignoreErrors[] = [
    'message' => '#^Function request_parse_body not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/vendor/symfony/http-foundation/Request.php',
];
$ignoreErrors[] = [
    'message' => '#^Function request_parse_body not found\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-opit-compatibility/vendor/symfony/http-foundation/Request.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
