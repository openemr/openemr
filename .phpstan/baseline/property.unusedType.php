<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\WenoModule\\\\Services\\\\LogProperties\\:\\:\\$key \\(string\\|false\\) is never assigned false so it can be removed from the property type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/LogProperties.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\WenoModule\\\\Services\\\\TransmitProperties\\:\\:\\$csrf \\(string\\|false\\) is never assigned false so it can be removed from the property type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-weno/src/Services/TransmitProperties.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Common\\\\Forms\\\\FormVitalDetails\\:\\:\\$form_id \\(float\\|int\\) is never assigned float so it can be removed from the property type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Forms/FormVitalDetails.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Common\\\\Session\\\\Predis\\\\SentinelUtil\\:\\:\\$predisMasterPassword \\(string\\|null\\) is never assigned null so it can be removed from the property type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Session/Predis/SentinelUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Common\\\\Session\\\\Predis\\\\SentinelUtil\\:\\:\\$predisSentinelCertKeyPath \\(string\\|null\\) is never assigned null so it can be removed from the property type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Session/Predis/SentinelUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Common\\\\Session\\\\Predis\\\\SentinelUtil\\:\\:\\$predisSentinelsPassword \\(string\\|null\\) is never assigned null so it can be removed from the property type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Session/Predis/SentinelUtil.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Events\\\\Messaging\\\\SendNotificationEvent\\:\\:\\$sendNotificationMethod \\(string\\|null\\) is never assigned null so it can be removed from the property type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Messaging/SendNotificationEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Services\\\\FHIR\\\\FhirPatientService\\:\\:\\$listService \\(OpenEMR\\\\Services\\\\ListService\\|null\\) is never assigned null so it can be removed from the property type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Services\\\\FHIR\\\\FhirPatientService\\:\\:\\$searchParameters \\(array\\|null\\) is never assigned array so it can be removed from the property type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Services\\\\FHIR\\\\Questionnaire\\\\FhirQuestionnaireFormService\\:\\:\\$service \\(OpenEMR\\\\Services\\\\QuestionnaireService\\|null\\) is never assigned null so it can be removed from the property type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Questionnaire/FhirQuestionnaireFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Services\\\\Search\\\\TokenSearchValue\\:\\:\\$code \\(bool\\|float\\|int\\|string\\) is never assigned bool so it can be removed from the property type\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/TokenSearchValue.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
