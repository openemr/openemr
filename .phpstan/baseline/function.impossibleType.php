<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with int will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-prior-authorizations/src/Controller/ListAuthorizations.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_null\\(\\) with DOMNodeList\\<DOMNameSpaceNode\\|DOMNode\\>\\|false will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Model/CcrTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_string\\(\\) with \\-1\\|Application\\\\Model\\\\type will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Installer/src/Installer/Model/InstModuleTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with string will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_null\\(\\) with array\\{id\\: int, uuid\\: string\\|null, username\\: string\\|null, password\\: string\\|null, authorized\\: int\\|null, info\\: string\\|null, source\\: int\\|null, fname\\: string\\|null, \\.\\.\\.\\}\\|false will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/summary/pnotes_full.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_countable\\(\\) with string will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_null\\(\\) with object will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/TreeMenu.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_null\\(\\) with non\\-empty\\-list\\<string\\|null\\> will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_null\\(\\) with non\\-empty\\-array will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_null\\(\\) with non\\-empty\\-list\\<string\\|null\\> will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_csv_inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with callback will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with string will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_null\\(\\) with string will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/savant/Savant3/resources/Savant3_Plugin_date.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_null\\(\\) with string will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/savant/Savant3/resources/Savant3_Plugin_image.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with VARIANT will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_string\\(\\) with VARIANT will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/PortalController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_null\\(\\) with OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\Rule will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerDetail.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_null\\(\\) with OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\Rule will always evaluate to false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerEdit.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_null\\(\\) with OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleAction will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerEdit.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_null\\(\\) with OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteria will always evaluate to false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerEdit.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_null\\(\\) with OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\Rule will always evaluate to false\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/Controller/ControllerReview.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_null\\(\\) with OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\TimeUnit will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleCriteria.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_null\\(\\) with string will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleCriteria.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_null\\(\\) with OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\Code will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleCriteriaDiagnosis.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_null\\(\\) with OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\RuleCriteria will always evaluate to false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleCriteriaFactory.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_null\\(\\) with OpenEMR\\\\ClinicalDecisionRules\\\\Interface\\\\RuleLibrary\\\\ReminderIntervalType will always evaluate to false\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_null\\(\\) with string will always evaluate to false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_string\\(\\) with array will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/Export/ExportJob.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_scalar\\(\\) with array will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/PHPFHIRResponseParser.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with OpenEMR\\\\Gacl\\\\ADORecordSet will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/GaclApi.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_string\\(\\) with array will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/AuthorizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCodeableConcept will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirOrganizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_string\\(\\) with OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRString will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_string\\(\\) with OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\ISearchField will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with \'pregnancy_intent\'\\|\'pregnancy_status\' will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationHistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_string\\(\\) with OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRQuestionnaireResponseStatus will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_null\\(\\) with Effective will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Globals/UserSettingsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_null\\(\\) with OpenEMR\\\\Services\\\\Globals\\\\Effective will always evaluate to false\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/Globals/UserSettingsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_null\\(\\) with string will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientTrackerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_object\\(\\) with bool will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/SearchFieldComparableValue.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to function is_array\\(\\) with string\\|false will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Api/AuthorizationGrantFlowTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
