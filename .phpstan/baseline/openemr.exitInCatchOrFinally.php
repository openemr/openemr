<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../apis/dispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../ccdaservice/ccda_gateway.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/dispense_drug.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newpatient/common.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/questionnaire_assessments/questionnaire_assessments.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/questionnaire_assessments/save.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/globals.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/modules/PostCalendar/pnuserapi.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-claimrev-connect/public/EraDownload.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/api_onetime.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/webhook_receiver.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/TwilioSMSClient.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/scripts/weno_log_sync.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/templates/synch.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_sdoh_health_concerns.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_sdoh_save.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/report/custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/demographics_save.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/product_registration/product_registration_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/smart/admin-client.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/smart/ehr-launch-client.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/mfa_u2f.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/usergroup/npi_lookup.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/webhooks/payment/rainforest.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/messages/validate_messages_document_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/deletedrug.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../oauth2/authorize.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/account/index_reset.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/account/register.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/home.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../portal/import_template.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/index.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/lib/doc_lib.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../portal/report/portal_custom_report.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/sign/assets/signer_modal.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/Runner/CommandRunner.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Command/SymfonyCommandRunner.php',
];
$ignoreErrors[] = [
    'message' => '#^exit/die inside a catch block swallows the caught exception and aborts the process\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Cqm/QrdaControllers/QrdaReportController.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
