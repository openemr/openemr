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

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
