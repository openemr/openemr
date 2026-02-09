<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Instanceof between array and OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRAllergyIntolerance will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirAllergyIntoleranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Instanceof between array and OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCoverage will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCoverageService.php',
];
$ignoreErrors[] = [
    'message' => '#^Instanceof between array and OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIROrganization will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Organization/FhirOrganizationInsuranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Instanceof between PhpParser\\\\Node\\\\Expr\\|PhpParser\\\\Node\\\\Identifier and PhpParser\\\\Node\\\\Name will always evaluate to false\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/PHPStan/Rules/ForbiddenStaticMethodsRule.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
