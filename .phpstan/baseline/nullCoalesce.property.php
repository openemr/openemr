<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\ClaimRevConnector\\\\Bootstrap\\:\\:\\$moduleDirectoryName \\(string\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-claimrev-connect/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\Dorn\\\\Bootstrap\\:\\:\\$moduleDirectoryName \\(string\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dorn/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Modules\\\\EhiExporter\\\\Bootstrap\\:\\:\\$moduleDirectoryName \\(string\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ehi-exporter/src/Bootstrap.php',
];
$ignoreErrors[] = [
    'message' => '#^Property HTML_TreeMenu_Listbox\\:\\:\\$promoText \\(string\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/TreeMenu.php',
];
$ignoreErrors[] = [
    'message' => '#^Property Smarty_Legacy\\:\\:\\$error_reporting \\(int\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/smarty_legacy/smarty/Smarty_Legacy.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Events\\\\Core\\\\TemplatePageEvent\\:\\:\\$twigTemplate \\(string\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Core/TemplatePageEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Events\\\\Core\\\\TemplatePageEvent\\:\\:\\$twigVariables \\(array\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/Core/TemplatePageEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Events\\\\PatientDocuments\\\\PatientDocumentCreateCCDAEvent\\:\\:\\$components \\(array\\<string\\>\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/PatientDocuments/PatientDocumentCreateCCDAEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Events\\\\PatientDocuments\\\\PatientDocumentCreateCCDAEvent\\:\\:\\$sections \\(array\\<string\\>\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Events/PatientDocuments/PatientDocumentCreateCCDAEvent.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Account \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRAccount\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$ActivityDefinition \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRActivityDefinition\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$AdverseEvent \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRAdverseEvent\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$AllergyIntolerance \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRAllergyIntolerance\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Appointment \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRAppointment\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$AppointmentResponse \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRAppointmentResponse\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$AuditEvent \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRAuditEvent\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Basic \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRBasic\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Binary \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRBinary\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$BiologicallyDerivedProduct \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRBiologicallyDerivedProduct\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$BodyStructure \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRBodyStructure\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Bundle \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRBundle\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$CapabilityStatement \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCapabilityStatement\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$CarePlan \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCarePlan\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$CareTeam \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCareTeam\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$CatalogEntry \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCatalogEntry\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$ChargeItem \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRChargeItem\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$ChargeItemDefinition \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRChargeItemDefinition\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Claim \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRClaim\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$ClaimResponse \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRClaimResponse\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$ClinicalImpression \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRClinicalImpression\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$CodeSystem \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCodeSystem\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Communication \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCommunication\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$CommunicationRequest \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCommunicationRequest\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$CompartmentDefinition \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCompartmentDefinition\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Composition \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRComposition\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$ConceptMap \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRConceptMap\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Condition \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCondition\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Consent \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRConsent\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Contract \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRContract\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Coverage \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCoverage\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$CoverageEligibilityRequest \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCoverageEligibilityRequest\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$CoverageEligibilityResponse \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCoverageEligibilityResponse\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$DetectedIssue \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDetectedIssue\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Device \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDevice\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$DeviceDefinition \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDeviceDefinition\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$DeviceMetric \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDeviceMetric\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$DeviceRequest \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDeviceRequest\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$DeviceUseStatement \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDeviceUseStatement\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$DiagnosticReport \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDiagnosticReport\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$DocumentManifest \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDocumentManifest\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$DocumentReference \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDocumentReference\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$EffectEvidenceSynthesis \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIREffectEvidenceSynthesis\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Encounter \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIREncounter\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Endpoint \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIREndpoint\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$EnrollmentRequest \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIREnrollmentRequest\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$EnrollmentResponse \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIREnrollmentResponse\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$EpisodeOfCare \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIREpisodeOfCare\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$EventDefinition \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIREventDefinition\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Evidence \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIREvidence\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$EvidenceVariable \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIREvidenceVariable\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$ExampleScenario \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRExampleScenario\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$ExplanationOfBenefit \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRExplanationOfBenefit\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$FamilyMemberHistory \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRFamilyMemberHistory\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Flag \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRFlag\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Goal \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRGoal\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$GraphDefinition \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRGraphDefinition\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Group \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRGroup\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$GuidanceResponse \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRGuidanceResponse\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$HealthcareService \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRHealthcareService\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$ImagingStudy \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRImagingStudy\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Immunization \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRImmunization\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$ImmunizationEvaluation \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRImmunizationEvaluation\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$ImmunizationRecommendation \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRImmunizationRecommendation\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$ImplementationGuide \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRImplementationGuide\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$InsurancePlan \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRInsurancePlan\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Invoice \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRInvoice\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Library \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRLibrary\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Linkage \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRLinkage\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$List \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRList\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Location \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRLocation\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Measure \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMeasure\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$MeasureReport \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMeasureReport\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Media \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedia\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Medication \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedication\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$MedicationAdministration \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationAdministration\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$MedicationDispense \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationDispense\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$MedicationKnowledge \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationKnowledge\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$MedicationRequest \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationRequest\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$MedicationStatement \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationStatement\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$MedicinalProduct \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicinalProduct\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$MedicinalProductAuthorization \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicinalProductAuthorization\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$MedicinalProductContraindication \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicinalProductContraindication\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$MedicinalProductIndication \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicinalProductIndication\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$MedicinalProductIngredient \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicinalProductIngredient\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$MedicinalProductInteraction \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicinalProductInteraction\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$MedicinalProductManufactured \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicinalProductManufactured\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$MedicinalProductPackaged \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicinalProductPackaged\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$MedicinalProductPharmaceutical \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicinalProductPharmaceutical\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$MedicinalProductUndesirableEffect \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicinalProductUndesirableEffect\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$MessageDefinition \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMessageDefinition\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$MessageHeader \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMessageHeader\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$MolecularSequence \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMolecularSequence\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$NamingSystem \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRNamingSystem\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$NutritionOrder \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRNutritionOrder\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Observation \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRObservation\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$ObservationDefinition \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRObservationDefinition\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$OperationDefinition \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIROperationDefinition\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$OperationOutcome \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIROperationOutcome\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Organization \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIROrganization\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$OrganizationAffiliation \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIROrganizationAffiliation\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Parameters \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRParameters\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Patient \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRPatient\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$PaymentNotice \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRPaymentNotice\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$PaymentReconciliation \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRPaymentReconciliation\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Person \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRPerson\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$PlanDefinition \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRPlanDefinition\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Practitioner \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRPractitioner\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$PractitionerRole \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRPractitionerRole\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Procedure \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProcedure\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Provenance \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Questionnaire \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRQuestionnaire\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$QuestionnaireResponse \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRQuestionnaireResponse\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$RelatedPerson \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRRelatedPerson\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$RequestGroup \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRRequestGroup\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$ResearchDefinition \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRResearchDefinition\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$ResearchElementDefinition \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRResearchElementDefinition\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$ResearchStudy \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRResearchStudy\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$ResearchSubject \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRResearchSubject\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$RiskAssessment \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRRiskAssessment\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$RiskEvidenceSynthesis \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRRiskEvidenceSynthesis\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Schedule \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSchedule\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$SearchParameter \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSearchParameter\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$ServiceRequest \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRServiceRequest\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Slot \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSlot\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Specimen \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSpecimen\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$SpecimenDefinition \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSpecimenDefinition\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$StructureDefinition \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRStructureDefinition\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$StructureMap \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRStructureMap\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Subscription \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSubscription\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Substance \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSubstance\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$SubstanceNucleicAcid \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSubstanceNucleicAcid\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$SubstancePolymer \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSubstancePolymer\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$SubstanceProtein \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSubstanceProtein\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$SubstanceReferenceInformation \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSubstanceReferenceInformation\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$SubstanceSourceMaterial \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSubstanceSourceMaterial\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$SubstanceSpecification \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSubstanceSpecification\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$SupplyDelivery \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSupplyDelivery\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$SupplyRequest \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRSupplyRequest\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$Task \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRTask\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$TerminologyCapabilities \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRTerminologyCapabilities\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$TestReport \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRTestReport\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$TestScript \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRTestScript\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$ValueSet \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRValueSet\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$VerificationResult \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRVerificationResult\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\FHIR\\\\R4\\\\FHIRResourceContainer\\:\\:\\$VisionPrescription \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRVisionPrescription\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRResourceContainer.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\PaymentProcessing\\\\Sphere\\\\SpherePayment\\:\\:\\$mainUrlRetail \\(string\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/PaymentProcessing/Sphere/SpherePayment.php',
];
$ignoreErrors[] = [
    'message' => '#^Static property OpenEMR\\\\RestControllers\\\\Config\\\\RestConfig\\:\\:\\$ROOT_URL \\(OpenEMR\\\\RestControllers\\\\Config\\\\root\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/RestControllers/Config/RestConfig.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Services\\\\CodeTypesService\\:\\:\\$installedCodeTypes \\(array\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/CodeTypesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Property OpenEMR\\\\Services\\\\PatientAccessOnsiteService\\:\\:\\$kernel \\(OpenEMR\\\\Core\\\\Kernel\\) on left side of \\?\\? is not nullable\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/PatientAccessOnsiteService.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
