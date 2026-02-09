<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$fhirResource \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirPersonService\\:\\:parseFhirResource\\(\\) should be compatible with parameter \\$fhirResource \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseFhirResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPersonService.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\#1 \\$fhirResource \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Organization\\\\FhirOrganizationInsuranceService\\:\\:parseFhirResource\\(\\) should be compatible with parameter \\$fhirResource \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseFhirResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Organization/FhirOrganizationInsuranceService.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
