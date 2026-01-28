<?php declare(strict_types = 1);

// total 24 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Offset \'action\' on non\\-empty\\-array in isset\\(\\) always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_admin.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'re_id_code\' on non\\-empty\\-array in isset\\(\\) always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/de_identification_forms/re_identification_op_single_patient.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 0 on non\\-empty\\-list\\<string\\> in isset\\(\\) always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/ReceiveHl7Results.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'limit\' on non\\-empty\\-array\\<\'address_purpose\'\\|\'city\'\\|\'country_code\'\\|\'enumeration_type\'\\|\'first_name\'\\|\'last_name\'\\|\'limit\'\\|\'number\'\\|\'organization_name\'\\|\'postal_code\'\\|\'skip\'\\|\'state\'\\|\'taxonomy_description\'\\|\'version\', string\\> in isset\\(\\) always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/usergroup/npi_lookup.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 0 on non\\-empty\\-list\\<string\\> in isset\\(\\) always exists and is not nullable\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/edihistory/edih_277_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 0 on non\\-empty\\-list\\<string\\> in isset\\(\\) always exists and is not nullable\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../library/edihistory/edih_278_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 1 on non\\-empty\\-list\\<string\\> in isset\\(\\) always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_835_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 1 on non\\-empty\\-list\\<string\\> in isset\\(\\) always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/test_edih_835_accounting.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'post_sftp\' on non\\-empty\\-array in isset\\(\\) always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/test_edih_sftp_files.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'blank\\-nav\\-button\' on non\\-empty\\-array in isset\\(\\) always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/import_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset 0 on non\\-empty\\-list\\<string\\> in isset\\(\\) always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/Phreeze/ActionRouter.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'x12_sftp_local_dir\' on non\\-empty\\-array in isset\\(\\) always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/BillingProcessor/Tasks/GeneratorX12Direct.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'expiration\' on non\\-empty\\-array in isset\\(\\) always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'components\' on array\\{fullcode\\: \'LOINC\\:11341\\-5\', code\\: \'11341\\-5\', description\\: \'Occupation\', column\\: \'occupation\', list_id\\: \'OccupationODH\', category\\: \'social\\-history\', screening_category_code\\: null, screening_category_display\\: null, \\.\\.\\.\\} in isset\\(\\) always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationEmployerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'components\' on array\\{fullcode\\: \'LOINC\\:76690\\-7\', code\\: \'76690\\-7\', description\\: \'Sexual Orientation\', column\\: \'sexual_orientation\', list_id\\: \'sexual_orientation\', category\\: \'social\\-history\', screening_category_code\\: null, screening_category_display\\: null, \\.\\.\\.\\} in isset\\(\\) does not exist\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset int on array\\{\\} in isset\\(\\) does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset string on array\\{\\} in isset\\(\\) does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/QuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset OpenEMR\\\\Services\\\\Search\\\\ServiceField on array\\<OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\> in isset\\(\\) does not exist\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/FHIRSearchFieldFactory.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
