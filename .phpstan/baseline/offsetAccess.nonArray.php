<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on array\\|false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_debug.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_test2.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_test3.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/edit_group.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../gacl/admin/edit_object_sections.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../gacl/admin/edit_objects.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/object_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/era_payments.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_process.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/view.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on list\\<string\\>\\|string\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/includes/pnMod.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/common.api.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnadmin.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuser.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/ReceiveHl7Results.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/ClickatellSMSClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EmailClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/receive_hl7_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/super/edit_globals.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Installer.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Compiler_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.load_plugins.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/internals/core.read_cache_file.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/_global_config.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/Dispatcher.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sql_patch.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sql_upgrade.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ExternalClinicalDecisionSupport/RouteController.php',
];
$ignoreErrors[] = [
    'message' => '#^Cannot use array destructuring on mixed\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
