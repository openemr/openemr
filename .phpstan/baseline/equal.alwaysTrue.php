<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\=\\= between 1 and 1 will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/print_billing_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\=\\= between 1 and 1 will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/print_daysheet_report_num2.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\=\\= between 1 and 1 will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/print_daysheet_report_num3.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\=\\= between 0 and 0 will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/billing/sl_eob_search.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\=\\= between 1 and 1 will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/clinic_note/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\=\\= between \'1\' and \'1\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/eye_mag/php/eye_mag_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\=\\= between 1 and 1 will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/soap/report.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\=\\= between \'child\' and \'child\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Services/EhiExporter.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\=\\= between \'\' and \'\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/reports/audit_log_tamper_report.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\=\\= between \'0\' and \'0\' will always evaluate to true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\=\\= between \'1\' and \'1\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/MedEx/API.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\=\\= between \'2100\' and \'2100\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_835_html.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\=\\= between \'claims\' and \'claims\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/edihistory/edih_archive.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\=\\= between \'down\' and \'down\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_table.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\=\\= between \'right\' and \'right\' will always evaluate to true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/plugins/function.html_table.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\=\\= between 0 and 0 will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../modules/sms_email_reminder/cron_functions.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\=\\= between 0 and 0 will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Billing/X125010837I.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\=\\= between \'2y\' and \'2y\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/AuthHash.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\=\\= between \'g10_certification\' and \'g10_certification\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G10_Certification/SinglePatient311APITest.php',
];
$ignoreErrors[] = [
    'message' => '#^Loose comparison using \\=\\= between \'g10_certification\' and \'g10_certification\' will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Certification/HIT1/G10_Certification/SinglePatient700APITest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
