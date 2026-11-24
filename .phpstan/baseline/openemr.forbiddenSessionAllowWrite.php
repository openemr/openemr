<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Setting \\$sessionAllowWrite holds the session lock for the whole request\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/taskman.php',
];
$ignoreErrors[] = [
    'message' => '#^Setting \\$sessionAllowWrite holds the session lock for the whole request\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newpatient/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Setting \\$sessionAllowWrite holds the session lock for the whole request\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/login/login.php',
];
$ignoreErrors[] = [
    'message' => '#^Setting \\$sessionAllowWrite holds the session lock for the whole request\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/login_screen.php',
];
$ignoreErrors[] = [
    'message' => '#^Setting \\$sessionAllowWrite holds the session lock for the whole request\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/logout.php',
];
$ignoreErrors[] = [
    'message' => '#^Setting \\$sessionAllowWrite holds the session lock for the whole request\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/main_screen.php',
];
$ignoreErrors[] = [
    'message' => '#^Setting \\$sessionAllowWrite holds the session lock for the whole request\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/tabs/main.php',
];
$ignoreErrors[] = [
    'message' => '#^Setting \\$sessionAllowWrite holds the session lock for the whole request\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/contact.php',
];
$ignoreErrors[] = [
    'message' => '#^Setting \\$sessionAllowWrite holds the session lock for the whole request\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/rc_sms_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Setting \\$sessionAllowWrite holds the session lock for the whole request\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/setup_services.php',
];
$ignoreErrors[] = [
    'message' => '#^Setting \\$sessionAllowWrite holds the session lock for the whole request\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/messageUI.php',
];
$ignoreErrors[] = [
    'message' => '#^Setting \\$sessionAllowWrite holds the session lock for the whole request\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/moduleConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Setting \\$sessionAllowWrite holds the session lock for the whole request\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/new/new_comprehensive_save.php',
];
$ignoreErrors[] = [
    'message' => '#^Setting \\$sessionAllowWrite holds the session lock for the whole request\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/encounter/encounter_top.php',
];
$ignoreErrors[] = [
    'message' => '#^Setting \\$sessionAllowWrite holds the session lock for the whole request\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/appointments_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Setting \\$sessionAllowWrite holds the session lock for the whole request\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/mfa_totp.php',
];
$ignoreErrors[] = [
    'message' => '#^Setting \\$sessionAllowWrite holds the session lock for the whole request\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/execute_background_services.php',
];
$ignoreErrors[] = [
    'message' => '#^Setting \\$sessionAllowWrite holds the session lock for the whole request\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/account/verify.php',
];
$ignoreErrors[] = [
    'message' => '#^Setting \\$sessionAllowWrite holds the session lock for the whole request\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/ccda_gateway.php',
];
$ignoreErrors[] = [
    'message' => '#^Setting \\$sessionAllowWrite holds the session lock for the whole request\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/get_patient_info.php',
];
$ignoreErrors[] = [
    'message' => '#^Setting \\$sessionAllowWrite holds the session lock for the whole request\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/index.php',
];
$ignoreErrors[] = [
    'message' => '#^Setting \\$sessionAllowWrite holds the session lock for the whole request\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/paylib.php',
];
$ignoreErrors[] = [
    'message' => '#^Setting \\$sessionAllowWrite holds the session lock for the whole request\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/persist.php',
];
$ignoreErrors[] = [
    'message' => '#^Setting \\$sessionAllowWrite holds the session lock for the whole request\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../sql_upgrade.php',
];
$ignoreErrors[] = [
    'message' => '#^Setting \\$sessionAllowWrite holds the session lock for the whole request\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/SiteSetupListener.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
