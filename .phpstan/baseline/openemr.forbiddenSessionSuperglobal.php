<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/public/backend/index-backend.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/public/backend/questionnaire-audit.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/http-foundation/Session/Storage/NativeSessionStorage.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/src/Commands/HealthlinkMessagesPurgeConnectionCommand.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/src/Commands/HealthlinkMessagesPurgeSingleCommand.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/src/Commands/HealthlinkMessagesPurgeTypeCommand.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/src/Common/OpenEmrPatientSessionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/src/Controller/MessageImportController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/src/Controller/PatientMatchController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/src/Controller/ReferralController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/src/Controller/UnprocessedListController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/src/Processing/Pdf/MpdfPdfGenerator.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/src/Processing/Processors/DefaultMessageProcessor.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/src/Processing/Processors/OruResultProcessor.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/vendor/symfony/http-foundation/Session/Storage/NativeSessionStorage.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/src/Service/IhiHistoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/guzzlehttp/guzzle/src/Cookie/SessionCookieJar.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/http-foundation/Session/Storage/NativeSessionStorage.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/http-kernel/EventListener/AbstractSessionListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module-config-settings/assets/interface/forms/clinical_notes/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module-config-settings/assets/interface/patient_file/summary/add_edit_issue.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module-config-settings/assets/src/Services/EmployerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module-config-settings/public/forms/care_plan/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module-config-settings/public/forms/care_plan/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module-config-settings/public/patient_file/merge_patients.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module-config-settings/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module-config-settings/src/Common/Twig/ProviderSearchWidgetExtension.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module-config-settings/src/Controllers/AppointmentsReportController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module-config-settings/src/Controllers/CarePlanController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module-config-settings/src/Controllers/EncountersReportController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module-config-settings/src/GlobalConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module-config-settings/src/Services/DebugFooterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module-config-settings/src/Services/Forms/CarePlanFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/assets/interface/modules/custom_modules/oe-module-faxsms/library/rc_sms_notification.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/assets/interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/public/patient_file/merge_patients.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/vendor/symfony/http-foundation/Session/Storage/NativeSessionStorage.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SESSION is forbidden\\. Use the session wrapper instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-opit-compatibility/vendor/symfony/http-foundation/Session/Storage/NativeSessionStorage.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
