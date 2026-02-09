<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Left side of && is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_invoice.php',
];
$ignoreErrors[] = [
    'message' => '#^Left side of && is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/drugs/add_edit_lot.php',
];
$ignoreErrors[] = [
    'message' => '#^Left side of && is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/patient_file/merge_patients.php',
];
$ignoreErrors[] = [
    'message' => '#^Left side of && is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/pos_checkout_ippf.php',
];
$ignoreErrors[] = [
    'message' => '#^Left side of && is always true\\.$#',
    'count' => 7,
    'path' => __DIR__ . '/../../library/classes/Tree.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Left side of && is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_277_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Left side of && is always true\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../library/edihistory/edih_278_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Left side of && is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_835_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Left side of && is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_997_error.php',
];
$ignoreErrors[] = [
    'message' => '#^Left side of && is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Left side of && is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_segments.php',
];
$ignoreErrors[] = [
    'message' => '#^Left side of && is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_x12file_class.php',
];
$ignoreErrors[] = [
    'message' => '#^Left side of && is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Left side of && is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_select_time.php',
];
$ignoreErrors[] = [
    'message' => '#^Left side of && is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/templates/relation_form.php',
];
$ignoreErrors[] = [
    'message' => '#^Left side of && is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/_machine_config.php',
];
$ignoreErrors[] = [
    'message' => '#^Left side of && is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/util/parsecsv.lib.php',
];
$ignoreErrors[] = [
    'message' => '#^Left side of && is always true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Patient/Cards/CareTeamViewCard.php',
];
$ignoreErrors[] = [
    'message' => '#^Left side of && is always true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/Services/FHIR/FhirMedicationRequestServiceUSCore8Test.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
