<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Instanceof between OpenEMR\\\\Events\\\\Core\\\\TemplatePageEvent and OpenEMR\\\\Events\\\\Core\\\\TemplatePageEvent will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/newpatient/C_EncounterVisitForm.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Instanceof between AmcReportFactory\\|CqmReportFactory and RsReportFactoryAbstract will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/rulesets/ReportManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Instanceof between OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer and OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResource/FHIRDomainResource.php',
];
$ignoreErrors[] = [
    'message' => '#^Instanceof between OpenEMR\\\\Services\\\\Search\\\\ISearchField and OpenEMR\\\\Services\\\\Search\\\\ISearchField will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/CarePlanService.php',
];
$ignoreErrors[] = [
    'message' => '#^Instanceof between OpenEMR\\\\Events\\\\CDA\\\\CDAPostParseEvent and OpenEMR\\\\Events\\\\CDA\\\\CDAPostParseEvent will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Cda/CdaTemplateParse.php',
];
$ignoreErrors[] = [
    'message' => '#^Instanceof between OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDiagnosticReport and OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDiagnosticReport will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Instanceof between DateTime and DateTime will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirExportJobService.php',
];
$ignoreErrors[] = [
    'message' => '#^Instanceof between \\$this\\(OpenEMR\\\\Services\\\\FHIR\\\\FhirProvenanceService\\) and OpenEMR\\\\Services\\\\FHIR\\\\IResourceReadableService will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProvenanceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Instanceof between OpenEMR\\\\Services\\\\Search\\\\TokenSearchField and OpenEMR\\\\Services\\\\Search\\\\TokenSearchField will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Instanceof between OpenEMR\\\\Services\\\\Search\\\\TokenSearchField and OpenEMR\\\\Services\\\\Search\\\\TokenSearchField will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationEmployerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Instanceof between OpenEMR\\\\Services\\\\Search\\\\TokenSearchField and OpenEMR\\\\Services\\\\Search\\\\TokenSearchField will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationHistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Instanceof between OpenEMR\\\\Services\\\\Search\\\\TokenSearchField and OpenEMR\\\\Services\\\\Search\\\\TokenSearchField will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Instanceof between OpenEMR\\\\Services\\\\Search\\\\TokenSearchField and OpenEMR\\\\Services\\\\Search\\\\TokenSearchField will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationSocialHistoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Instanceof between OpenEMR\\\\Services\\\\Search\\\\ISearchField and OpenEMR\\\\Services\\\\Search\\\\ISearchField will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ObservationLabService.php',
];
$ignoreErrors[] = [
    'message' => '#^Instanceof between OpenEMR\\\\Services\\\\Search\\\\ISearchField and OpenEMR\\\\Services\\\\Search\\\\ISearchField will always evaluate to true\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../src/Services/PractitionerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Instanceof between OpenEMR\\\\Services\\\\Search\\\\ISearchField and OpenEMR\\\\Services\\\\Search\\\\ISearchField will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Instanceof between OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition and OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/FHIRSearchFieldFactory.php',
];
$ignoreErrors[] = [
    'message' => '#^Instanceof between OpenEMR\\\\Services\\\\Search\\\\ISearchField and OpenEMR\\\\Services\\\\Search\\\\ISearchField will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/FhirSearchWhereClauseBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Instanceof between DateTime and DateTime will always evaluate to true\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/SearchFieldStatementResolver.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
