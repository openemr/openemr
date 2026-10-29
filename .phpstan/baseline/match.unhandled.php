<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Match expression does not handle remaining values\\: int\\<min, \\-1\\>\\|int\\<3, max\\>\\|null$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/property-info/Extractor/PhpDocExtractor.php',
];
$ignoreErrors[] = [
    'message' => '#^Match expression does not handle remaining values\\: OpenEMR\\\\Modules\\\\HealthLink\\\\Models\\\\HealthLinkMessageType\\:\\:BREAST_CLINIC_REFERRAL\\|OpenEMR\\\\Modules\\\\HealthLink\\\\Models\\\\HealthLinkMessageType\\:\\:BREAST_CLINIC_REFERRAL_RESPONSE\\|OpenEMR\\\\Modules\\\\HealthLink\\\\Models\\\\HealthLinkMessageType\\:\\:COLPOSCOPY_REFERRAL\\|OpenEMR\\\\Modules\\\\HealthLink\\\\Models\\\\HealthLinkMessageType\\:\\:COLPOSCOPY_REFERRAL_RESPONSE\\|OpenEMR\\\\Modules\\\\HealthLink\\\\Models\\\\HealthLinkMessageType\\:\\:COVID_19_TESTING_REFERRAL\\|OpenEMR\\\\Modules\\\\HealthLink\\\\Models\\\\HealthLinkMessageType\\:\\:COVID_19_TESTING_REFERRAL_RESPONSE\\|OpenEMR\\\\Modules\\\\HealthLink\\\\Models\\\\HealthLinkMessageType\\:\\:MEMORY_CLINIC_REFERRAL\\|OpenEMR\\\\Modules\\\\HealthLink\\\\Models\\\\HealthLinkMessageType\\:\\:MEMORY_CLINIC_REFERRAL_RESPONSE\\|OpenEMR\\\\Modules\\\\HealthLink\\\\Models\\\\HealthLinkMessageType\\:\\:PALLIATIVE_CARE_REFERRAL\\|OpenEMR\\\\Modules\\\\HealthLink\\\\Models\\\\HealthLinkMessageType\\:\\:PALLIATIVE_CARE_REFERRAL_RESPONSE$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/src/Models/HealthLinkMessageType.php',
];
$ignoreErrors[] = [
    'message' => '#^Match expression does not handle remaining value\\: true$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/dom-crawler/Crawler.php',
];
$ignoreErrors[] = [
    'message' => '#^Match expression does not handle remaining value\\: mixed$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/http-client/CurlHttpClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Match expression does not handle remaining value\\: mixed$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/process/Process.php',
];
$ignoreErrors[] = [
    'message' => '#^Match expression does not handle remaining value\\: mixed$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/var-dumper/Caster/SymfonyCaster.php',
];
$ignoreErrors[] = [
    'message' => '#^Match expression does not handle remaining value\\: true$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/var-exporter/Internal/Exporter.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
