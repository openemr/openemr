<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Offset \'city\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'state\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../controllers/C_Prescription.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 0 on non\\-empty\\-list\\<string\\> on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/code_types.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'referrerID\' on array\\<string, string\\> on left side of \\?\\? does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/import_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'ippfconmeth\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 0 on non\\-empty\\-list\\<string\\> on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/fee_sheet/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'is_hospitalized\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/misc_billing_options/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'is_unable_to_work\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/forms/misc_billing_options/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 0 on non\\-empty\\-list\\<string\\> on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newpatient/C_EncounterVisitForm.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'questionnaire…\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/questionnaire_assessments/questionnaire_assessments.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'totalscore\' on non\\-empty\\-array\\<mixed\\> on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/sdoh/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'language_select\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/language/lang_definition.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'pid\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/dated_reminders/dated_reminders_add.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'fname\' on array\\{id\\: int, uuid\\: string\\|null, title\\: string, language\\: string, financial\\: string, fname\\: string, lname\\: string, mname\\: string, \\.\\.\\.\\} on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/document_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'lname\' on array\\{id\\: int, uuid\\: string\\|null, title\\: string, language\\: string, financial\\: string, fname\\: string, lname\\: string, mname\\: string, \\.\\.\\.\\} on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/document_select.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'fname\' on array\\{id\\: int, uuid\\: string\\|null, title\\: string, language\\: string, financial\\: string, fname\\: string, lname\\: string, mname\\: string, \\.\\.\\.\\} on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/trusted-messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'lname\' on array\\{id\\: int, uuid\\: string\\|null, title\\: string, language\\: string, financial\\: string, fname\\: string, lname\\: string, mname\\: string, \\.\\.\\.\\} on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/trusted-messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'message_code\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/messages/trusted-messages.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'pubpid\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/utility.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'site\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/library/webhook_receiver.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'email_message\' on array\\{sender_name\\: mixed, sender_email\\: mixed, notification_email\\: mixed, email_transport\\: mixed, smtp_host\\: mixed, smtp_port\\: mixed, smtp_user\\: mixed, smtp_password\\: mixed, \\.\\.\\.\\} on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 1 on non\\-empty\\-list\\<string\\|null\\> on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/LogImportBuild.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'authUserID\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/ModuleService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'facilityId\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/TransmitProperties.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 0 on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'field_value\' on array\\{table_name\\: string, field_name\\: string, field_value\\: string, entry_identification\\: string\\} on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'is_qrda_document\' on array\\{table_name\\: string, entry_identification\\: string\\} on left side of \\?\\? does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'is_unstructured…\' on array\\{table_name\\: string, entry_identification\\: string\\} on left side of \\?\\? does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CarecoordinationTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'default\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/CcdaUserPreferencesTransformer.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 0 on non\\-empty\\-list\\<string\\> on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'fname\' on array\\{id\\: int, uuid\\: string\\|null, title\\: string, language\\: string, financial\\: string, fname\\: string, lname\\: string, mname\\: string, \\.\\.\\.\\} on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_sdoh_health_concerns.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'lname\' on array\\{id\\: int, uuid\\: string\\|null, title\\: string, language\\: string, financial\\: string, fname\\: string, lname\\: string, mname\\: string, \\.\\.\\.\\} on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_sdoh_health_concerns.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'form_patient\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/custom_report_range.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'form_patient\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/patient_flow_board_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 0 on non\\-empty\\-list\\<string\\> on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/super/edit_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'authorized\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/usergroup_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'encounter_patient…\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/payment_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'insurance_text_ajax\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/payment_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'patient_code\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/payment_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'height\' on array\\{0\\: int\\<0, max\\>, 1\\: int\\<0, max\\>, 2\\: int, 3\\: string, mime\\: string, channels\\?\\: int, bits\\?\\: int\\} on left side of \\?\\? does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/thumbnail/Thumbnail.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'width\' on array\\{0\\: int\\<0, max\\>, 1\\: int\\<0, max\\>, 2\\: int, 3\\: string, mime\\: string, channels\\?\\: int, bits\\?\\: int\\} on left side of \\?\\? does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/thumbnail/Thumbnail.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 0 on non\\-empty\\-list\\<string\\> on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/clinical_rules.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \\*NEVER\\* on null on left side of \\?\\? does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_checkboxes.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \\*NEVER\\* on null on left side of \\?\\? does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_radios.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'pc_apptstatus\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/add_edit_event_user.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'upload_name\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/import_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'searchparm\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/patient_groups.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'setting_patient\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/lib/persist.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'modifier\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/portal_payment.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 0 on non\\-empty\\-list\\<string\\> on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/ControllerRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'uuid\' on array\\{id\\: int, uuid\\: non\\-falsy\\-string, username\\: string\\|null, password\\: string\\|null, authorized\\: int\\|null, info\\: string\\|null, source\\: int\\|null, fname\\: string\\|null, \\.\\.\\.\\} on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Grant/CustomClientCredentialsGrant.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 0 on non\\-empty\\-list\\<string\\> on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Forms/Types/SmokingStatusType.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 0 on non\\-empty\\-list\\<string\\> on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/Address.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'time_throttle\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Common/Session/SessionTracker.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 0 on non\\-empty\\-list\\<string\\> on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ExternalClinicalDecisionSupport/RouteController.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 0 on non\\-empty\\-list\\<string\\> on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/SMART/ResourceConstraintFilterer.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'heading_title\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/OeUI/OemrUI.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'http\\://hl7\\.org/fhir…\'\\|\'http\\://terminology…\' on array\\{\'http\\://hl7\\.org/fhir/us/core/CodeSystem/condition\\-category\\|health\\-concern\'\\: \'Health Concerns\', \'http\\://terminology\\.hl7\\.org/CodeSystem/condition\\-category\\|encounter\\-diagnosis\'\\: \'Encounter Diagnoses\', \'http\\://terminology\\.hl7\\.org/CodeSystem/condition\\-category\\|problem\\-list\\-item\'\\: \'Problem List Items\', \'http\\://terminology\\.hl7\\.org/CodeSystem/observation\\-category\\|clinical\\-test\'\\: \'Clinical Test\', \'http\\://terminology\\.hl7\\.org/CodeSystem/observation\\-category\\|laboratory\'\\: \'Laboratory\', \'http\\://terminology\\.hl7\\.org//CodeSystem\\-observation\\-category\\|social\\-history\'\\: \'Social History\', \'http\\://hl7\\.org/fhir/us/core/CodeSystem/us\\-core\\-category\\|sdoh\'\\: \'Social Determinants…\', \'http\\://terminology\\.hl7\\.org/CodeSystem/observation\\-category\\|survey\'\\: \'Survey\', \\.\\.\\.\\} on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/SMART/ScopePermissionParser.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 0 on non\\-empty\\-list\\<string\\> on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/CarePlanService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'2710\\-2\'\\|\'29463\\-7\'\\|\'3141\\-9\'\\|\'39156\\-5\'\\|\'59408\\-5\'\\|\'8287\\-5\'\\|\'8302\\-2\'\\|\'8310\\-5\'\\|\'8462\\-4\'\\|\'8480\\-6\'\\|\'8867\\-4\'\\|\'9279\\-1\' on array\\{\'8310\\-5\'\\: \'temperature\', \'8462\\-4\'\\: \'bpd\', \'8480\\-6\'\\: \'bps\', \'8287\\-5\'\\: \'head_circ\', \'8867\\-4\'\\: \'pulse\', \'8302\\-2\'\\: \'height\', \'2710\\-2\'\\: \'oxygen_saturation\', \'59408\\-5\'\\: \'oxygen_saturation\', \\.\\.\\.\\} on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateParse.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'2\\.16\\.840\\.1\\.113883…\' on array\\{\'2\\.16\\.840\\.1\\.113883\\.10\\.20\\.24\\.3\\.147\'\\: \'fetchAllergyIntoler…\', \'2\\.16\\.840\\.1\\.113883\\.10\\.20\\.24\\.3\\.41\'\\: \'fetchMedicationData\', \'2\\.16\\.840\\.1\\.113883\\.10\\.20\\.24\\.3\\.42\'\\: \'fetchMedicationData\', \'2\\.16\\.840\\.1\\.113883\\.10\\.20\\.24\\.3\\.139\'\\: \'fetchMedicationData\', \'2\\.16\\.840\\.1\\.113883\\.10\\.20\\.24\\.3\\.105\'\\: \'fetchMedicationData\', \'2\\.16\\.840\\.1\\.113883\\.10\\.20\\.24\\.3\\.137\'\\: \'fetchMedicalProblem…\', \'2\\.16\\.840\\.1\\.113883\\.10\\.20\\.24\\.3\\.138\'\\: \'fetchMedicalProblem…\', \'2\\.16\\.840\\.1\\.113883\\.10\\.20\\.24\\.3\\.140\'\\: \'fetchImmunizationDa…\', \\.\\.\\.\\} on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateParse.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'HIP\'\\|\'PUBLICPOL\' on array\\{HIP\\: \'health insurance…\', PUBLICPOL\\: \'public healthcare\', EHCPOL\\: \'extended healthcare\'\\} on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCoverageService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'reason\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirEncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 0 on non\\-empty\\-list\\<string\\> on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProvenanceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'profiles\' on array\\{fullcode\\: \'LOINC\\:42348\\-3\', code\\: \'42348\\-3\', description\\: \'Advance directive\', category\\: \'observation\\-adi…\', document_type\\: \'Advance Directive\', profiles\\: array\\{\'http\\://hl7\\.org/fhir/us/core/StructureDefinition/us\\-core\\-observation\\-adi\\-documentation\'\\: array\\{\'8\\.0\\.0\'\\}\\}\\}\\|array\\{fullcode\\: \'LOINC\\:75320\\-2\', code\\: \'75320\\-2\', description\\: \'Advance directive \\-…\', category\\: \'observation\\-adi…\', document_type\\: \'Living Will\', profiles\\: array\\{\'http\\://hl7\\.org/fhir/us/core/StructureDefinition/us\\-core\\-observation\\-adi\\-documentation\'\\: array\\{\'8\\.0\\.0\'\\}\\}\\}\\|array\\{fullcode\\: \'LOINC\\:75787\\-2\', code\\: \'75787\\-2\', description\\: \'Advance directive \\-…\', category\\: \'observation\\-adi…\', document_type\\: \'Durable Power of…\', profiles\\: array\\{\'http\\://hl7\\.org/fhir/us/core/StructureDefinition/us\\-core\\-observation\\-adi\\-documentation\'\\: array\\{\'8\\.0\\.0\'\\}\\}\\}\\|array\\{fullcode\\: \'LOINC\\:78823\\-2\', code\\: \'78823\\-2\', description\\: \'Do not resuscitate…\', category\\: \'observation\\-adi…\', document_type\\: \'Do Not Resuscitate…\', profiles\\: array\\{\'http\\://hl7\\.org/fhir/us/core/StructureDefinition/us\\-core\\-observation\\-adi\\-documentation\'\\: array\\{\'8\\.0\\.0\'\\}\\}\\} on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'profiles\' on array\\{fullcode\\: \'LOINC\\:11341\\-5\', code\\: \'11341\\-5\', description\\: \'Occupation\', column\\: \'occupation\', list_id\\: \'OccupationODH\', category\\: \'social\\-history\', screening_category_code\\: null, screening_category_display\\: null, \\.\\.\\.\\} on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationEmployerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'result_abnormal\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset mixed on array\\{\\} on left side of \\?\\? does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'profiles\' on array\\{fullcode\\: \'LOINC\\:76690\\-7\', code\\: \'76690\\-7\', description\\: \'Sexual Orientation\', column\\: \'sexual_orientation\', list_id\\: \'sexual_orientation\', category\\: \'social\\-history\', screening_category_code\\: null, screening_category_display\\: null, \\.\\.\\.\\} on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'profiles\' on array\\{fullcode\\: \'LOINC\\:2708\\-6\', code\\: \'2708\\-6\', description\\: \'Oxygen saturation…\', column\\: array\\{\'oxygen_saturation\', \'oxygen_saturation…\', \'oxygen_flow_rate\', \'oxygen_flow_rate…\', \'inhaled_oxygen…\', \'inhaled_oxygen…\'\\}, in_vitals_panel\\: true, profiles\\: array\\{\'http\\://hl7\\.org/fhir/us/core/StructureDefinition/us\\-core\\-pulse\\-oximetry\'\\: array\\{\'\', \'3\\.1\\.1\', \'7\\.0\\.0\', \'8\\.0\\.0\'\\}\\}\\}\\|array\\{fullcode\\: \'LOINC\\:29463\\-7\', code\\: \'29463\\-7\', description\\: \'Body weight\', column\\: array\\{\'weight\', \'weight_unit\'\\}, in_vitals_panel\\: true, profiles\\: array\\{\'http\\://hl7\\.org/fhir/StructureDefinition/bodyweight\'\\: array\\{\'\', \'3\\.1\\.1\'\\}, \'http\\://hl7\\.org/fhir/us/core/StructureDefinition/us\\-core\\-body\\-weight\'\\: array\\{\'\', \'7\\.0\\.0\', \'8\\.0\\.0\'\\}\\}\\}\\|array\\{fullcode\\: \'LOINC\\:39156\\-5\', code\\: \'39156\\-5\', description\\: \'Body mass index …\', column\\: array\\{\'BMI\', \'BMI_status\', \'BMI_unit\'\\}, in_vitals_panel\\: true, profiles\\: array\\{\'http\\://hl7\\.org/fhir/us/core/StructureDefinition/us\\-core\\-bmi\'\\: array\\{\'\', \'3\\.1\\.1\', \'7\\.0\\.0\', \'8\\.0\\.0\'\\}\\}\\}\\|array\\{fullcode\\: \'LOINC\\:59408\\-5\', code\\: \'59408\\-5\', description\\: \'Oxygen saturation…\', column\\: array\\{\'oxygen_saturation\', \'oxygen_saturation…\', \'oxygen_flow_rate\', \'oxygen_flow_rate…\', \'inhaled_oxygen…\', \'inhaled_oxygen…\'\\}, in_vitals_panel\\: true, profiles\\: array\\{\'http\\://hl7\\.org/fhir/us/core/StructureDefinition/us\\-core\\-pulse\\-oximetry\'\\: array\\{\'\', \'3\\.1\\.1\', \'7\\.0\\.0\', \'8\\.0\\.0\'\\}\\}\\}\\|array\\{fullcode\\: \'LOINC\\:59576\\-9\', code\\: \'59576\\-9\', description\\: \'Body mass index …\', column\\: array\\{\'ped_bmi\', \'ped_bmi_unit\'\\}, in_vitals_panel\\: false, profiles\\: array\\{\'http\\://hl7\\.org/fhir/us/core/StructureDefinition/pediatric\\-bmi\\-for\\-age\'\\: array\\{\'\', \'3\\.1\\.1\', \'7\\.0\\.0\', \'8\\.0\\.0\'\\}\\}\\}\\|array\\{fullcode\\: \'LOINC\\:77606\\-2\', code\\: \'77606\\-2\', description\\: \'Weight\\-for\\-length…\', column\\: array\\{\'ped_weight_height\', \'ped_weight_height…\'\\}, in_vitals_panel\\: false, profiles\\: array\\{\'http\\://hl7\\.org/fhir/us/core/StructureDefinition/pediatric\\-weight\\-for\\-height\'\\: array\\{\'\', \'3\\.1\\.1\', \'7\\.0\\.0\', \'8\\.0\\.0\'\\}\\}\\}\\|array\\{fullcode\\: \'LOINC\\:8289\\-1\', code\\: \'8289\\-1\', description\\: \'Head Occipital…\', column\\: array\\{\'ped_head_circ\', \'ped_head_circ_unit\'\\}, in_vitals_panel\\: false, profiles\\: array\\{\'http\\://hl7\\.org/fhir/us/core/StructureDefinition/head\\-occipital\\-frontal\\-circumference\\-percentile\'\\: array\\{\'\', \'3\\.1\\.1\', \'7\\.0\\.0\', \'8\\.0\\.0\'\\}\\}\\}\\|array\\{fullcode\\: \'LOINC\\:8302\\-2\', code\\: \'8302\\-2\', description\\: \'Body height\', column\\: array\\{\'height\', \'height_unit\'\\}, in_vitals_panel\\: true, profiles\\: array\\{\'http\\://hl7\\.org/fhir/StructureDefinition/bodyheight\'\\: array\\{\'\', \'3\\.1\\.1\'\\}, \'http\\://hl7\\.org/fhir/us/core/StructureDefinition/us\\-core\\-body\\-height\'\\: array\\{\'\', \'7\\.0\\.0\', \'8\\.0\\.0\'\\}\\}\\}\\|array\\{fullcode\\: \'LOINC\\:8310\\-5\', code\\: \'8310\\-5\', description\\: \'Body Temperature\', column\\: array\\{\'temperature\', \'temperature_unit\'\\}, in_vitals_panel\\: true, profiles\\: array\\{\'http\\://hl7\\.org/fhir/StructureDefinition/bodytemp\'\\: array\\{\'\', \'3\\.1\\.1\'\\}, \'http\\://hl7\\.org/fhir/us/core/StructureDefinition/us\\-core\\-body\\-temperature\'\\: array\\{\'\', \'7\\.0\\.0\', \'8\\.0\\.0\'\\}\\}\\}\\|array\\{fullcode\\: \'LOINC\\:8327\\-9\', code\\: \'8327\\-9\', description\\: \'Temperature Location\', column\\: array\\{\'temp_method\'\\}, in_vitals_panel\\: true, profiles\\: array\\{\'http\\://hl7\\.org/fhir/us/core/StructureDefinition/us\\-core\\-simple\\-observation\'\\: array\\{\'\', \'7\\.0\\.0\', \'8\\.0\\.0\'\\}\\}\\}\\|array\\{fullcode\\: \'LOINC\\:85353\\-1\', code\\: \'85353\\-1\', description\\: \'Vital signs, weight…\', column\\: \'\', in_vitals_panel\\: false, profiles\\: array\\{\'http\\://hl7\\.org/fhir/R4/observation\\-vitalsigns\'\\: array\\{\'\', \'3\\.1\\.1\'\\}, \'http\\://hl7\\.org/fhir/us/core/StructureDefinition/us\\-core\\-vital\\-signs\'\\: array\\{\'\', \'7\\.0\\.0\', \'8\\.0\\.0\'\\}\\}\\}\\|array\\{fullcode\\: \'LOINC\\:85354\\-9\', code\\: \'85354\\-9\', description\\: \'Blood pressure…\', column\\: array\\{\'bps\', \'bps_unit\', \'bpd\', \'bpd_unit\'\\}, in_vitals_panel\\: true, profiles\\: array\\{\'http\\://hl7\\.org/fhir/StructureDefinition/bp\'\\: array\\{\'\', \'3\\.1\\.1\'\\}, \'http\\://hl7\\.org/fhir/us/core/StructureDefinition/us\\-core\\-blood\\-pressure\'\\: array\\{\'\', \'7\\.0\\.0\', \'8\\.0\\.0\'\\}\\}\\}\\|array\\{fullcode\\: \'LOINC\\:8867\\-4\', code\\: \'8867\\-4\', description\\: \'Heart rate\', column\\: array\\{\'pulse\', \'pulse_unit\'\\}, in_vitals_panel\\: true, profiles\\: array\\{\'http\\://hl7\\.org/fhir/StructureDefinition/heartrate\'\\: array\\{\'\', \'3\\.1\\.1\'\\}, \'http\\://hl7\\.org/fhir/us/core/StructureDefinition/us\\-core\\-heart\\-rate\'\\: array\\{\'\', \'7\\.0\\.0\', \'8\\.0\\.0\'\\}\\}\\}\\|array\\{fullcode\\: \'LOINC\\:9279\\-1\', code\\: \'9279\\-1\', description\\: \'Respiratory Rate\', column\\: array\\{\'respiration\', \'respiration_unit\'\\}, in_vitals_panel\\: true, profiles\\: array\\{\'http\\://hl7\\.org/fhir/StructureDefinition/resprate\'\\: array\\{\'\', \'3\\.1\\.1\'\\}, \'http\\://hl7\\.org/fhir/us/core/StructureDefinition/us\\-core\\-respiratory\\-rate\'\\: array\\{\'\', \'7\\.0\\.0\', \'8\\.0\\.0\'\\}\\}\\}\\|array\\{fullcode\\: \'LOINC\\:9843\\-4\', code\\: \'9843\\-4\', description\\: \'Head Occipital…\', column\\: array\\{\'head_circ\', \'head_circ_unit\'\\}, in_vitals_panel\\: true, profiles\\: array\\{\'http\\://hl7\\.org/fhir/us/core/StructureDefinition/us\\-core\\-head\\-circumference\'\\: array\\{\'\', \'3\\.1\\.1\', \'7\\.0\\.0\', \'8\\.0\\.0\'\\}\\}\\} on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationVitalsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'code_type\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'ob_code\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'result_uuid\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/ProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'childcare_needs\'\\|\'digital_access\'\\|\'financial_strain\'\\|\'food_insecurity\'\\|\'housing_instability\'\\|\'interpersonal_safety\'\\|\'social_isolation\'\\|\'transportation…\'\\|\'utilities_insecurity\' on array\\{food_insecurity\\: \'Food insecurity …\', housing_instability\\: \'Housing instability…\', transportation_insecurity\\: \'Transportation…\', utilities_insecurity\\: \'Utilities…\', interpersonal_safety\\: \'Interpersonal…\', financial_strain\\: \'Financial strain …\', social_isolation\\: \'Social isolation …\', childcare_needs\\: \'Childcare needs …\', \\.\\.\\.\\} on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/SDOH/HistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'childcare_needs\'\\|\'digital_access\'\\|\'financial_strain\'\\|\'food_insecurity\'\\|\'housing_instability\'\\|\'interpersonal_safety\'\\|\'social_isolation\'\\|\'transportation…\'\\|\'utilities_insecurity\' on array\\{food_insecurity\\: array\\{\'yes\', \'positive\', \'at_risk\', \'often\', \'sometimes\'\\}, housing_instability\\: array\\{\'yes\', \'positive\', \'at_risk\'\\}, transportation_insecurity\\: array\\{\'yes\', \'positive\', \'at_risk\'\\}, utilities_insecurity\\: array\\{\'yes\', \'positive\', \'at_risk\'\\}, interpersonal_safety\\: array\\{\'yes\', \'positive\'\\}, financial_strain\\: array\\{\'yes\', \'positive\', \'high\', \'very hard\', \'hard\'\\}, social_isolation\\: array\\{\'yes\', \'positive\'\\}, childcare_needs\\: array\\{\'yes\', \'positive\', \'needs\'\\}, \\.\\.\\.\\} on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/SDOH/HistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'country_name\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/GeoTelemetry.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'geoplugin…\' on non\\-empty\\-array on left side of \\?\\? always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Telemetry/GeoTelemetry.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'region\' on array\\{input\\: array\\{\'2125551234\', \'212\\-555\\-1234\', \'\\(212\\) 555\\-1234\', \'212\\.555\\.1234\', \'212 555 1234\', \'\\+1 212 555 1234\', \'\\+12125551234\', \'1\\-212\\-555\\-1234\'\\}, e164\\: \'\\+12125551234\', national\\: \'2125551234\', formatted\\: array\\{local\\: \'\\(212\\) 555\\-1234\', global\\: \'\\+1 212\\-555\\-1234\'\\}, hl7\\: \'212\\^5551234\', parts\\: array\\{area_code\\: \'212\', prefix\\: \'555\', number\\: \'1234\'\\}\\}\\|array\\{input\\: array\\{\'2128675309\', \'212\\-867\\-5309\', \'\\(212\\) 867\\-5309\', \'\\+1\\-212\\-867\\-5309\'\\}, e164\\: \'\\+12128675309\', national\\: \'2128675309\', formatted\\: array\\{local\\: \'\\(212\\) 867\\-5309\', global\\: \'\\+1 212\\-867\\-5309\'\\}, hl7\\: \'212\\^8675309\', parts\\: array\\{area_code\\: \'212\', prefix\\: \'867\', number\\: \'5309\'\\}\\}\\|array\\{input\\: array\\{\'3105551234\', \'310\\-555\\-1234\', \'\\(310\\) 555\\-1234\', \'310\\.555\\.1234\', \'310   555   1234\', \'\\+1 \\(310\\) 555\\-1234\'\\}, e164\\: \'\\+13105551234\', national\\: \'3105551234\', formatted\\: array\\{local\\: \'\\(310\\) 555\\-1234\', global\\: \'\\+1 310\\-555\\-1234\'\\}, hl7\\: \'310\\^5551234\', parts\\: array\\{area_code\\: \'310\', prefix\\: \'555\', number\\: \'1234\'\\}\\} on left side of \\?\\? does not exist\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Common/Utils/ValidationUtilsIsolatedTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'region\' on array\\{input\\: array\\{\'2125551234\', \'212\\-555\\-1234\', \'\\(212\\) 555\\-1234\', \'212\\.555\\.1234\', \'212 555 1234\', \'\\+1 212 555 1234\', \'\\+12125551234\', \'1\\-212\\-555\\-1234\'\\}, e164\\: \'\\+12125551234\', national\\: \'2125551234\', formatted\\: array\\{local\\: \'\\(212\\) 555\\-1234\', global\\: \'\\+1 212\\-555\\-1234\'\\}, hl7\\: \'212\\^5551234\', parts\\: array\\{area_code\\: \'212\', prefix\\: \'555\', number\\: \'1234\'\\}\\}\\|array\\{input\\: array\\{\'2128675309\', \'212\\-867\\-5309\', \'\\(212\\) 867\\-5309\', \'\\+1\\-212\\-867\\-5309\'\\}, e164\\: \'\\+12128675309\', national\\: \'2128675309\', formatted\\: array\\{local\\: \'\\(212\\) 867\\-5309\', global\\: \'\\+1 212\\-867\\-5309\'\\}, hl7\\: \'212\\^8675309\', parts\\: array\\{area_code\\: \'212\', prefix\\: \'867\', number\\: \'5309\'\\}\\}\\|array\\{input\\: array\\{\'3105551234\', \'310\\-555\\-1234\', \'\\(310\\) 555\\-1234\', \'310\\.555\\.1234\', \'310   555   1234\', \'\\+1 \\(310\\) 555\\-1234\'\\}, e164\\: \'\\+13105551234\', national\\: \'3105551234\', formatted\\: array\\{local\\: \'\\(310\\) 555\\-1234\', global\\: \'\\+1 310\\-555\\-1234\'\\}, hl7\\: \'310\\^5551234\', parts\\: array\\{area_code\\: \'310\', prefix\\: \'555\', number\\: \'1234\'\\}\\}\\|array\\{input\\: array\\{\'5551234567\', \'555\\-123\\-4567\', \'\\(555\\) 123\\-4567\'\\}, e164\\: \'\\+15551234567\', national\\: \'5551234567\', formatted\\: array\\{local\\: \'\\(555\\) 123\\-4567\', global\\: \'\\+1 555\\-123\\-4567\'\\}, valid\\: false, possible\\: true\\} on left side of \\?\\? does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Common/Utils/ValidationUtilsIsolatedTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
