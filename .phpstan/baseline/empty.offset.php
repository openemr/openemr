<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Offset \'extension\' on array\\{dirname\\?\\: string, basename\\: string, extension\\: \'dcm\', filename\\: string\\} in empty\\(\\) always exists and is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../controllers/C_Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 7 on array\\{0\\: array\\{\\}, 2\\: mixed, 4\\: mixed, 8\\: non\\-falsy\\-string, 10\\: non\\-falsy\\-string, 3\\: non\\-falsy\\-string, 6\\: mixed\\} in empty\\(\\) does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/ub04_dispose.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'samesite\' on array\\{lifetime\\: int\\<0, max\\>, path\\: non\\-falsy\\-string, domain\\: string, secure\\: bool, httponly\\: bool, samesite\\: \'Lax\'\\|\'lax\'\\|\'None\'\\|\'none\'\\|\'Strict\'\\|\'strict\'\\} in empty\\(\\) always exists and is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/login/login.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'items\' on array\\{group_name\\: mixed, group_id\\: mixed, items\\: non\\-empty\\-list\\} in empty\\(\\) always exists and is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Acl/src/Acl/Controller/AclController.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'date\' on non\\-empty\\-list in empty\\(\\) does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/single_order_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'diagnosis\' on array\\{id\\: non\\-falsy\\-string, title\\: mixed, diagnosis\\: non\\-falsy\\-string, comments\\: mixed, date\\: mixed, author_id\\: mixed\\} in empty\\(\\) always exists and is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/history/history_sdoh_health_concerns.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'form_submit\' on non\\-empty\\-array in empty\\(\\) always exists and is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/merge_patients.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'inhouse_pharmacy\' on non\\-empty\\-array in empty\\(\\) always exists and is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/user_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'id\' on non\\-empty\\-array in empty\\(\\) always exists and is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/ajax/person_search_ajax.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'samesite\' on array\\{lifetime\\: int\\<0, max\\>, path\\: non\\-falsy\\-string, domain\\: string, secure\\: bool, httponly\\: bool, samesite\\: \'Lax\'\\|\'lax\'\\|\'None\'\\|\'none\'\\|\'Strict\'\\|\'strict\'\\} in empty\\(\\) always exists and is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/restoreSession.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'cache_serials\' on array\\{\\} in empty\\(\\) does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'insert_tags\' on array\\{\\} in empty\\(\\) does not exist\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'medication_adherence\' on non\\-empty\\-array in empty\\(\\) always exists and is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationRequestService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'medication…\' on non\\-empty\\-array in empty\\(\\) always exists and is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationRequestService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'list_id\' on array\\{fullcode\\: \'LOINC\\:11341\\-5\', code\\: \'11341\\-5\', description\\: \'Occupation\', column\\: \'occupation\', list_id\\: \'OccupationODH\', category\\: \'social\\-history\', screening_category_code\\: null, screening_category_display\\: null, \\.\\.\\.\\} in empty\\(\\) always exists and is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationEmployerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'list_id\' on array\\{fullcode\\: \'LOINC\\:86188\\-0\', code\\: \'86188\\-0\', description\\: \'History of…\', column\\: \'industry\', list_id\\: \'IndustryODH\'\\} in empty\\(\\) always exists and is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationEmployerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'list_id\' on array\\{fullcode\\: \'LOINC\\:76690\\-7\', code\\: \'76690\\-7\', description\\: \'Sexual Orientation\', column\\: \'sexual_orientation\', list_id\\: \'sexual_orientation\', category\\: \'social\\-history\', screening_category_code\\: null, screening_category_display\\: null, \\.\\.\\.\\} in empty\\(\\) always exists and is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset mixed on array\\{\\} in empty\\(\\) does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'measure\' on array\\{category\\: array\\{coding\\: array\\{array\\{system\\: mixed, code\\: mixed, display\\: mixed\\}\\}, text\\: mixed\\}, measure\\: array\\{coding\\?\\: array\\{array\\{system\\: mixed, code\\: mixed, display\\: mixed\\}\\}, text\\: mixed\\}, target\\: array\\{coding\\?\\: array\\{array\\{system\\: mixed, code\\: mixed, display\\: mixed\\}\\}, text\\: mixed\\}, description\\: array\\{coding\\?\\: array\\{array\\{system\\: mixed, code\\: mixed, display\\: mixed\\}\\}, text\\: mixed\\}\\} in empty\\(\\) always exists and is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/SDOH/HistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'target\' on array\\{category\\: array\\{coding\\: array\\{array\\{system\\: mixed, code\\: mixed, display\\: mixed\\}\\}, text\\: mixed\\}, measure\\: array\\{coding\\?\\: array\\{array\\{system\\: mixed, code\\: mixed, display\\: mixed\\}\\}, text\\: mixed\\}, target\\: array\\{coding\\?\\: array\\{array\\{system\\: mixed, code\\: mixed, display\\: mixed\\}\\}, text\\: mixed\\}, description\\: array\\{coding\\?\\: array\\{array\\{system\\: mixed, code\\: mixed, display\\: mixed\\}\\}, text\\: mixed\\}\\} in empty\\(\\) always exists and is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/SDOH/HistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 2 on array\\{non\\-empty\\-string, non\\-falsy\\-string\\|null, non\\-falsy\\-string&numeric\\-string, non\\-falsy\\-string\\|null, non\\-falsy\\-string\\|null, non\\-falsy\\-string\\|null, non\\-falsy\\-string\\|null, non\\-falsy\\-string\\|null, \\.\\.\\.\\} in empty\\(\\) always exists and is not falsy\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/DateSearchField.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
