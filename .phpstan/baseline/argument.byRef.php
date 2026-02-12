<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$children of method eRxXMLBuilder\\:\\:appendChildren\\(\\) is passed by reference, so it expects variables only\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/eRxXMLBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#2 \\$out of method OpenEMR\\\\Modules\\\\Dorn\\\\DornGenHl7Order\\:\\:genHl7Order\\(\\) is passed by reference, so it expects variables only\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/EventSubscriber/DornLabSubscriber.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$array of function array_pop is passed by reference, so it expects variables only\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/patient_file/download_template.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$array of function reset is passed by reference, so it expects variables only\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/options.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$array of function end is passed by reference, so it expects variables only\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/ClinicalDecisionRules/Interface/RuleLibrary/RuleManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$array of function array_pop is passed by reference, so it expects variables only\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/EncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$array of function reset is passed by reference, so it expects variables only\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirEncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$array of function asort is passed by reference, so it expects variables only\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Qdm/ResultsCalculator.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
