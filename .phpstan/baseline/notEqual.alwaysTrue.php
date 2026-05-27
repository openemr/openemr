<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\!\\= between \'00193FC7\\-AEE4\\-4507…\'\\|\'0899A359\\-0CD8\\-4977…\'\\|\'0ED7B212\\-369B\\-489A…\'\\|\'201F5A6E\\-4DDE\\-43A2…\'\\|\'35B1A6DF\\-1871\\-4633…\'\\|\'3F4CDE57\\-1C5C\\-4250…\'\\|\'663FB12B\\-0FF4\\-49AB…\'\\|\'7549BA9E\\-1841\\-4231…\'\\|\'C948D0D2\\-D6E9\\-4099…\' and \'\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../custom/export_qrda_xml.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\!\\= between int\\<min, \\-1\\>\\|int\\<1, max\\> and 0 will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/clinical_instructions/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\!\\= between int\\<min, \\-1\\>\\|int\\<1, max\\> and 0 will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/functional_cognitive_status/save.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\!\\= between \'delete\'\\|\'insert\'\\|\'replace\'\\|\'select\'\\|\'update\' and \'\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/logview/logview.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\!\\= between non\\-falsy\\-string and \'\' will always evaluate to true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/main/calendar/add_edit_event.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\!\\= between non\\-empty\\-string and \'\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/orders/single_order_results.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\!\\= between null and \'ALL\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/appointments_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\!\\= between null and \'ALL\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/patient_flow_board_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\!\\= between non\\-falsy\\-string and 0 will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/super/edit_list.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\!\\= between int and \'\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\!\\= between int and \'\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Note.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\!\\= between int\\<min, \\-1\\>\\|int\\<1, max\\> and 0 will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\!\\= between int and \'all\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/report_database.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\!\\= between non\\-falsy\\-string and \'\' will always evaluate to true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/standard_tables_capture.inc.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
