<?php declare(strict_types = 1);

// total 247 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden in controllers\\. Use \\$request\\-\\>request\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/Admin/TeleHealthPatientAdminController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden in controllers\\. Use \\$request\\-\\>request\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-comlink-telehealth/src/Controller/Admin/TeleHealthUserAdminController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden in controllers\\. Use \\$request\\-\\>query\\-\\>get\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/src/Controller/AdminController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden in controllers\\. Use \\$request\\-\\>request\\-\\>get\\(\\) instead\\.$#',
    'count' => 37,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/src/Controller/AdminController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden in controllers\\. Use \\$request\\-\\>query\\-\\>get\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/src/Controller/UserContextController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden in controllers\\. Use \\$request\\-\\>request\\-\\>get\\(\\) instead\\.$#',
    'count' => 12,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dashboard-context/src/Controller/UserContextController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden in controllers\\. Use \\$request\\-\\>query\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden in controllers\\. Use \\$request\\-\\>request\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EmailClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden in controllers\\. Use \\$request\\-\\>files\\-\\>get\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden in controllers\\. Use \\$request\\-\\>files\\-\\>get\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden in controllers\\. Use \\$request\\-\\>files\\-\\>get\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/SignalWireClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/TwilioSMSClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden in controllers\\. Use \\$request\\-\\>request\\-\\>get\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/SendtoController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden in controllers\\. Use \\$request\\-\\>files\\-\\>get\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CarecoordinationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden in controllers\\. Use \\$request\\-\\>files\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CcdController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden in controllers\\. Use \\$request\\-\\>request\\-\\>get\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden in controllers\\. Use \\$request\\-\\>files\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_FILES is forbidden in controllers\\. Use \\$request\\-\\>files\\-\\>get\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Controller/DocumentsController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden in controllers\\. Use \\$request\\-\\>query\\-\\>get\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/participants_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden in controllers\\. Use \\$request\\-\\>request\\-\\>get\\(\\) instead\\.$#',
    'count' => 16,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/participants_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden in controllers\\. Use \\$request\\-\\>query\\-\\>get\\(\\) instead\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/therapy_groups_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden in controllers\\. Use \\$request\\-\\>request\\-\\>get\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../interface/therapy_groups/therapy_groups_controllers/therapy_groups_controller.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden in controllers\\. Use \\$request\\-\\>query\\-\\>get\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/Controller.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden in controllers\\. Use \\$request\\-\\>request\\-\\>get\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../library/classes/Controller.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/classes/Controller.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden in controllers\\. Use \\$request\\-\\>query\\-\\>get\\(\\) instead\\.$#',
    'count' => 25,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsiteDocumentController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden in controllers\\. Use \\$request\\-\\>query\\-\\>get\\(\\) instead\\.$#',
    'count' => 10,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden in controllers\\. Use \\$request\\-\\>query\\-\\>get\\(\\) instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden in controllers\\. Use \\$request\\-\\>query\\-\\>get\\(\\) instead\\.$#',
    'count' => 6,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerAjax.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden in controllers\\. Use \\$request\\-\\>request\\-\\>get\\(\\) instead\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerAlerts.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../src/RestControllers/Config/RestConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirAllergyIntoleranceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirAppointmentRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirCarePlanRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirCareTeamRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirConditionRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirCoverageRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirDeviceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirDiagnosticReportRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirDocumentReferenceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirEncounterRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirGoalRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirGroupRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirImmunizationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirLocationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirMediaRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirMedicationDispenseRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirMedicationRequestRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirMedicationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirObservationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirOrganizationRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPatientRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPersonRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPractitionerRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirPractitionerRoleRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirProcedureRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirProvenanceRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirQuestionnaireResponseRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirQuestionnaireRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirRelatedPersonRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirServiceRequestRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirSpecimenRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/FhirValueSetRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/FHIR/Operations/FhirOperationDocRefRestController.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden in controllers\\. Use \\$request\\-\\>query\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/RestControllerHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/RestControllerHelper.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden in controllers\\. Use \\$request\\-\\>query\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/SiteSetupListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_SERVER is forbidden in controllers\\. Use \\$request\\-\\>server\\-\\>get\\(\\) instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Subscriber/SiteSetupListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_GET is forbidden in controllers\\. Use \\$request\\-\\>query\\-\\>get\\(\\) instead\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../tests/Tests/Unit/ClinicalDecisionRules/ControllerEditTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Direct access to \\$_POST is forbidden in controllers\\. Use \\$request\\-\\>request\\-\\>get\\(\\) instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Unit/ClinicalDecisionRules/ControllerEditTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
