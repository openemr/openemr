<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Offset \'action\' on non\\-empty\\-array in isset\\(\\) always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../gacl/admin/acl_admin.php',
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
$ignoreErrors[] = [
    'message' => '#^Offset \'e164\' on array\\{input\\: array\\{\'\\+33 1 23 45 67 89\', \'\\+33123456789\'\\}, e164\\: \'\\+33123456789\', formatted\\: array\\{local\\: \'01 23 45 67 89\', global\\: \'\\+33 1 23 45 67 89\'\\}, region\\: \'FR\'\\}\\|array\\{input\\: array\\{\'\\+44 20 7946 0958\', \'\\+442079460958\'\\}, e164\\: \'\\+442079460958\', formatted\\: array\\{local\\: \'020 7946 0958\', global\\: \'\\+44 20 7946 0958\'\\}, region\\: \'GB\'\\}\\|array\\{input\\: array\\{\'\\+49 30 12345678\', \'\\+493012345678\'\\}, e164\\: \'\\+493012345678\', formatted\\: array\\{local\\: \'030 12345678\', global\\: \'\\+49 30 12345678\'\\}, region\\: \'DE\'\\}\\|array\\{input\\: array\\{\'\\+61 2 1234 5678\', \'\\+61212345678\'\\}, e164\\: \'\\+61212345678\', formatted\\: array\\{local\\: \'\\(02\\) 1234 5678\', global\\: \'\\+61 2 1234 5678\'\\}, region\\: \'AU\'\\}\\|array\\{input\\: array\\{\'2125551234\', \'212\\-555\\-1234\', \'\\(212\\) 555\\-1234\', \'212\\.555\\.1234\', \'212 555 1234\', \'\\+1 212 555 1234\', \'\\+12125551234\', \'1\\-212\\-555\\-1234\'\\}, e164\\: \'\\+12125551234\', national\\: \'2125551234\', formatted\\: array\\{local\\: \'\\(212\\) 555\\-1234\', global\\: \'\\+1 212\\-555\\-1234\'\\}, hl7\\: \'212\\^5551234\', parts\\: array\\{area_code\\: \'212\', prefix\\: \'555\', number\\: \'1234\'\\}\\}\\|array\\{input\\: array\\{\'2128675309\', \'212\\-867\\-5309\', \'\\(212\\) 867\\-5309\', \'\\+1\\-212\\-867\\-5309\'\\}, e164\\: \'\\+12128675309\', national\\: \'2128675309\', formatted\\: array\\{local\\: \'\\(212\\) 867\\-5309\', global\\: \'\\+1 212\\-867\\-5309\'\\}, hl7\\: \'212\\^8675309\', parts\\: array\\{area_code\\: \'212\', prefix\\: \'867\', number\\: \'5309\'\\}\\}\\|array\\{input\\: array\\{\'3105551234\', \'310\\-555\\-1234\', \'\\(310\\) 555\\-1234\', \'310\\.555\\.1234\', \'310   555   1234\', \'\\+1 \\(310\\) 555\\-1234\'\\}, e164\\: \'\\+13105551234\', national\\: \'3105551234\', formatted\\: array\\{local\\: \'\\(310\\) 555\\-1234\', global\\: \'\\+1 310\\-555\\-1234\'\\}, hl7\\: \'310\\^5551234\', parts\\: array\\{area_code\\: \'310\', prefix\\: \'555\', number\\: \'1234\'\\}\\}\\|array\\{input\\: array\\{\'5551234567\', \'555\\-123\\-4567\', \'\\(555\\) 123\\-4567\'\\}, e164\\: \'\\+15551234567\', national\\: \'5551234567\', formatted\\: array\\{local\\: \'\\(555\\) 123\\-4567\', global\\: \'\\+1 555\\-123\\-4567\'\\}, valid\\: false, possible\\: true\\} in isset\\(\\) always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Common/Utils/ValidationUtilsIsolatedTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'formatted\' on array\\{input\\: array\\{\'\\+33 1 23 45 67 89\', \'\\+33123456789\'\\}, e164\\: \'\\+33123456789\', formatted\\: array\\{local\\: \'01 23 45 67 89\', global\\: \'\\+33 1 23 45 67 89\'\\}, region\\: \'FR\'\\}\\|array\\{input\\: array\\{\'\\+44 20 7946 0958\', \'\\+442079460958\'\\}, e164\\: \'\\+442079460958\', formatted\\: array\\{local\\: \'020 7946 0958\', global\\: \'\\+44 20 7946 0958\'\\}, region\\: \'GB\'\\}\\|array\\{input\\: array\\{\'\\+49 30 12345678\', \'\\+493012345678\'\\}, e164\\: \'\\+493012345678\', formatted\\: array\\{local\\: \'030 12345678\', global\\: \'\\+49 30 12345678\'\\}, region\\: \'DE\'\\}\\|array\\{input\\: array\\{\'\\+61 2 1234 5678\', \'\\+61212345678\'\\}, e164\\: \'\\+61212345678\', formatted\\: array\\{local\\: \'\\(02\\) 1234 5678\', global\\: \'\\+61 2 1234 5678\'\\}, region\\: \'AU\'\\}\\|array\\{input\\: array\\{\'2125551234\', \'212\\-555\\-1234\', \'\\(212\\) 555\\-1234\', \'212\\.555\\.1234\', \'212 555 1234\', \'\\+1 212 555 1234\', \'\\+12125551234\', \'1\\-212\\-555\\-1234\'\\}, e164\\: \'\\+12125551234\', national\\: \'2125551234\', formatted\\: array\\{local\\: \'\\(212\\) 555\\-1234\', global\\: \'\\+1 212\\-555\\-1234\'\\}, hl7\\: \'212\\^5551234\', parts\\: array\\{area_code\\: \'212\', prefix\\: \'555\', number\\: \'1234\'\\}\\}\\|array\\{input\\: array\\{\'2128675309\', \'212\\-867\\-5309\', \'\\(212\\) 867\\-5309\', \'\\+1\\-212\\-867\\-5309\'\\}, e164\\: \'\\+12128675309\', national\\: \'2128675309\', formatted\\: array\\{local\\: \'\\(212\\) 867\\-5309\', global\\: \'\\+1 212\\-867\\-5309\'\\}, hl7\\: \'212\\^8675309\', parts\\: array\\{area_code\\: \'212\', prefix\\: \'867\', number\\: \'5309\'\\}\\}\\|array\\{input\\: array\\{\'3105551234\', \'310\\-555\\-1234\', \'\\(310\\) 555\\-1234\', \'310\\.555\\.1234\', \'310   555   1234\', \'\\+1 \\(310\\) 555\\-1234\'\\}, e164\\: \'\\+13105551234\', national\\: \'3105551234\', formatted\\: array\\{local\\: \'\\(310\\) 555\\-1234\', global\\: \'\\+1 310\\-555\\-1234\'\\}, hl7\\: \'310\\^5551234\', parts\\: array\\{area_code\\: \'310\', prefix\\: \'555\', number\\: \'1234\'\\}\\}\\|array\\{input\\: array\\{\'5551234567\', \'555\\-123\\-4567\', \'\\(555\\) 123\\-4567\'\\}, e164\\: \'\\+15551234567\', national\\: \'5551234567\', formatted\\: array\\{local\\: \'\\(555\\) 123\\-4567\', global\\: \'\\+1 555\\-123\\-4567\'\\}, valid\\: false, possible\\: true\\} in isset\\(\\) always exists and is not nullable\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Common/Utils/ValidationUtilsIsolatedTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'global\' on array\\{local\\: \'\\(02\\) 1234 5678\', global\\: \'\\+61 2 1234 5678\'\\}\\|array\\{local\\: \'\\(212\\) 555\\-1234\', global\\: \'\\+1 212\\-555\\-1234\'\\}\\|array\\{local\\: \'\\(212\\) 867\\-5309\', global\\: \'\\+1 212\\-867\\-5309\'\\}\\|array\\{local\\: \'\\(310\\) 555\\-1234\', global\\: \'\\+1 310\\-555\\-1234\'\\}\\|array\\{local\\: \'\\(555\\) 123\\-4567\', global\\: \'\\+1 555\\-123\\-4567\'\\}\\|array\\{local\\: \'01 23 45 67 89\', global\\: \'\\+33 1 23 45 67 89\'\\}\\|array\\{local\\: \'020 7946 0958\', global\\: \'\\+44 20 7946 0958\'\\}\\|array\\{local\\: \'030 12345678\', global\\: \'\\+49 30 12345678\'\\} in isset\\(\\) always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Common/Utils/ValidationUtilsIsolatedTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Offset \'local\' on array\\{local\\: \'\\(02\\) 1234 5678\', global\\: \'\\+61 2 1234 5678\'\\}\\|array\\{local\\: \'\\(212\\) 555\\-1234\', global\\: \'\\+1 212\\-555\\-1234\'\\}\\|array\\{local\\: \'\\(212\\) 867\\-5309\', global\\: \'\\+1 212\\-867\\-5309\'\\}\\|array\\{local\\: \'\\(310\\) 555\\-1234\', global\\: \'\\+1 310\\-555\\-1234\'\\}\\|array\\{local\\: \'\\(555\\) 123\\-4567\', global\\: \'\\+1 555\\-123\\-4567\'\\}\\|array\\{local\\: \'01 23 45 67 89\', global\\: \'\\+33 1 23 45 67 89\'\\}\\|array\\{local\\: \'020 7946 0958\', global\\: \'\\+44 20 7946 0958\'\\}\\|array\\{local\\: \'030 12345678\', global\\: \'\\+49 30 12345678\'\\} in isset\\(\\) always exists and is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Isolated/Common/Utils/ValidationUtilsIsolatedTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
