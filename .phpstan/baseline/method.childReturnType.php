<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Return type \\(void\\) of method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EtherFaxActions\\:\\:index\\(\\) should be compatible with return type \\(null\\) of method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\AppDispatch\\:\\:index\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EtherFaxActions.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(string\\|null\\) of method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\RCFaxClient\\:\\:index\\(\\) should be covariant with return type \\(null\\) of method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\AppDispatch\\:\\:index\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/RCFaxClient.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\<string\\>\\) of method OpenEMR\\\\Modules\\\\FaxSMS\\\\Events\\\\NotificationEventListener\\:\\:getSubscribedEvents\\(\\) should be covariant with return type \\(array\\<string, list\\<array\\{0\\: string, 1\\?\\: int\\}\\|int\\|string\\>\\|string\\>\\) of method Symfony\\\\Component\\\\EventDispatcher\\\\EventSubscriberInterface\\:\\:getSubscribedEvents\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-faxsms/src/Events/NotificationEventListener.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(void\\) of method Carecoordination\\\\Controller\\\\EncounterccdadispatchController\\:\\:indexAction\\(\\) should be compatible with return type \\(Laminas\\\\View\\\\Model\\\\ViewModel\\) of method Laminas\\\\Mvc\\\\Controller\\\\AbstractActionController\\:\\:indexAction\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncounterccdadispatchController.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(Laminas\\\\Stdlib\\\\ResponseInterface\\) of method Multipledb\\\\Controller\\\\MultipledbController\\:\\:indexAction\\(\\) should be covariant with return type \\(Laminas\\\\View\\\\Model\\\\ViewModel\\) of method Laminas\\\\Mvc\\\\Controller\\\\AbstractActionController\\:\\:indexAction\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Multipledb/src/Multipledb/Controller/MultipledbController.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\<string, mixed\\>\\) of method OpenEMR\\\\ZendModules\\\\PatientFlowBoard\\\\Listener\\\\PatientFlowBoardEventsSubscriber\\:\\:getSubscribedEvents\\(\\) should be covariant with return type \\(array\\<string, list\\<array\\{0\\: string, 1\\?\\: int\\}\\|int\\|string\\>\\|string\\>\\) of method Symfony\\\\Component\\\\EventDispatcher\\\\EventSubscriberInterface\\:\\:getSubscribedEvents\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/PatientFlowBoard/src/PatientFlowBoard/Listener/PatientFlowBoardEventsSubscriber.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(Laminas\\\\Stdlib\\\\ResponseInterface\\) of method Patientvalidation\\\\Controller\\\\PatientvalidationController\\:\\:indexAction\\(\\) should be covariant with return type \\(Laminas\\\\View\\\\Model\\\\ViewModel\\) of method Laminas\\\\Mvc\\\\Controller\\\\AbstractActionController\\:\\:indexAction\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Patientvalidation/src/Patientvalidation/Controller/PatientvalidationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mysqli\\) of method DataDriverMySQLi\\:\\:Open\\(\\) should be covariant with return type \\(connection\\) of method IDataDriver\\:\\:Open\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/DB/DataDriver/MySQLi.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Entities\\\\ClientEntity\\|false\\) of method OpenEMR\\\\Common\\\\Auth\\\\OpenIDConnect\\\\Repositories\\\\ClientRepository\\:\\:getClientEntity\\(\\) should be covariant with return type \\(League\\\\OAuth2\\\\Server\\\\Entities\\\\ClientEntityInterface\\|null\\) of method League\\\\OAuth2\\\\Server\\\\Repositories\\\\ClientRepositoryInterface\\:\\:getClientEntity\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Auth/OpenIDConnect/Repositories/ClientRepository.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAccountStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAccountStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRActionCardinalityBehavior\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRActionCardinalityBehavior.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRActionConditionKind\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRActionConditionKind.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRActionGroupingBehavior\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRActionGroupingBehavior.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRActionParticipantType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRActionParticipantType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRActionPrecheckBehavior\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRActionPrecheckBehavior.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRActionRelationshipType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRActionRelationshipType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRActionRequiredBehavior\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRActionRequiredBehavior.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRActionSelectionBehavior\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRActionSelectionBehavior.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAddressType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAddressType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAddressUse\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAddressUse.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAdministrativeGender\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAdministrativeGender.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAdverseEventActuality\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAdverseEventActuality.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAggregationMode\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAggregationMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAllergyIntoleranceCategory\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAllergyIntoleranceCategory.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAllergyIntoleranceCriticality\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAllergyIntoleranceCriticality.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAllergyIntoleranceSeverity\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAllergyIntoleranceSeverity.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAllergyIntoleranceType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAllergyIntoleranceType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAppointmentStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAppointmentStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAssertionDirectionType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAssertionDirectionType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAssertionOperatorType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAssertionOperatorType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAssertionResponseTypes\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAssertionResponseTypes.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAuditEventAction\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAuditEventAction.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAuditEventAgentNetworkType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAuditEventAgentNetworkType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRAuditEventOutcome\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRAuditEventOutcome.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRBase64Binary\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRBase64Binary.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRBindingStrength\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRBindingStrength.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRBiologicallyDerivedProductCategory\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRBiologicallyDerivedProductCategory.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRBiologicallyDerivedProductStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRBiologicallyDerivedProductStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRBiologicallyDerivedProductStorageScale\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRBiologicallyDerivedProductStorageScale.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRBoolean\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRBoolean.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRBundleType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRBundleType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCanonical\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCanonical.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCapabilityStatementKind\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCapabilityStatementKind.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCarePlanActivityKind\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCarePlanActivityKind.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCarePlanActivityStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCarePlanActivityStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCarePlanIntent\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCarePlanIntent.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCareTeamStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCareTeamStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCatalogEntryRelationType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCatalogEntryRelationType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRChargeItemStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRChargeItemStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRClaimProcessingCodes\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRClaimProcessingCodes.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRClinicalImpressionStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRClinicalImpressionStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCode\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCode.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCodeSearchSupport\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCodeSearchSupport.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCodeSystemContentMode\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCodeSystemContentMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCodeSystemHierarchyMeaning\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCodeSystemHierarchyMeaning.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCompartmentType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCompartmentType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCompositionAttestationMode\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCompositionAttestationMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRCompositionStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRCompositionStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRConceptMapEquivalence\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRConceptMapEquivalence.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRConceptMapGroupUnmappedMode\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRConceptMapGroupUnmappedMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRConditionalDeleteStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRConditionalDeleteStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRConditionalReadStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRConditionalReadStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRConsentDataMeaning\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRConsentDataMeaning.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRConsentProvisionType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRConsentProvisionType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRConsentState\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRConsentState.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRConstraintSeverity\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRConstraintSeverity.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRContactPointSystem\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRContactPointSystem.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRContactPointUse\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRContactPointUse.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRContractResourcePublicationStatusCodes\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRContractResourcePublicationStatusCodes.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRContractResourceStatusCodes\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRContractResourceStatusCodes.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRContributorType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRContributorType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDate\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDate.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDateTime\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDateTime.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDaysOfWeek\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDaysOfWeek.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDecimal\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDecimal.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDetectedIssueSeverity\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDetectedIssueSeverity.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDeviceMetricCalibrationState\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDeviceMetricCalibrationState.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDeviceMetricCalibrationType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDeviceMetricCalibrationType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDeviceMetricCategory\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDeviceMetricCategory.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDeviceMetricColor\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDeviceMetricColor.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDeviceMetricOperationalStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDeviceMetricOperationalStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDeviceNameType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDeviceNameType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDeviceUseStatementStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDeviceUseStatementStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDiagnosticReportStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDiagnosticReportStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDiscriminatorType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDiscriminatorType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDocumentMode\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDocumentMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDocumentReferenceStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDocumentReferenceStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRDocumentRelationshipType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRDocumentRelationshipType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIREligibilityRequestPurpose\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIREligibilityRequestPurpose.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIREligibilityResponsePurpose\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIREligibilityResponsePurpose.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIREnableWhenBehavior\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIREnableWhenBehavior.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIREncounterLocationStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIREncounterLocationStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIREncounterStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIREncounterStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIREndpointStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIREndpointStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIREpisodeOfCareStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIREpisodeOfCareStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIREventCapabilityMode\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIREventCapabilityMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIREventStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIREventStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIREventTiming\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIREventTiming.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIREvidenceVariableType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIREvidenceVariableType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRExampleScenarioActorType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRExampleScenarioActorType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRExplanationOfBenefitStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRExplanationOfBenefitStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRExposureState\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRExposureState.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRExpressionLanguage\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRExpressionLanguage.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRExtensionContextType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRExtensionContextType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRFHIRDeviceStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRFHIRDeviceStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRFHIRSubstanceStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRFHIRSubstanceStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRFHIRVersion\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRFHIRVersion.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRFamilyHistoryStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRFamilyHistoryStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRFilterOperator\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRFilterOperator.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRFinancialResourceStatusCodes\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRFinancialResourceStatusCodes.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRFlagStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRFlagStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRGoalLifecycleStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRGoalLifecycleStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRGraphCompartmentRule\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRGraphCompartmentRule.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRGraphCompartmentUse\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRGraphCompartmentUse.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRGroupMeasure\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRGroupMeasure.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRGroupType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRGroupType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRGuidanceResponseStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRGuidanceResponseStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRGuidePageGeneration\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRGuidePageGeneration.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRGuideParameterCode\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRGuideParameterCode.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRHTTPVerb\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRHTTPVerb.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRId\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRId.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRIdentifierUse\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRIdentifierUse.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRIdentityAssuranceLevel\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRIdentityAssuranceLevel.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRImagingStudyStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRImagingStudyStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRImmunizationEvaluationStatusCodes\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRImmunizationEvaluationStatusCodes.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRImmunizationStatusCodes\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRImmunizationStatusCodes.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRInstant\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRInstant.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRInteger\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRInteger.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRInvoicePriceComponentType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRInvoicePriceComponentType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRInvoiceStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRInvoiceStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRIssueSeverity\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRIssueSeverity.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRIssueType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRIssueType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRLinkType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRLinkType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRLinkageType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRLinkageType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRListMode\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRListMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRListStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRListStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRLocationMode\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRLocationMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRLocationStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRLocationStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRMarkdown\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRMarkdown.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRMeasureReportStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRMeasureReportStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRMeasureReportType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRMeasureReportType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRMedicationRequestIntent\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRMedicationRequestIntent.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRMedicationStatusCodes\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRMedicationStatusCodes.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRMedicationrequestStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRMedicationrequestStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRMessageSignificanceCategory\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRMessageSignificanceCategory.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRMessageheaderResponseRequest\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRMessageheaderResponseRequest.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRNameUse\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRNameUse.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRNamingSystemIdentifierType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRNamingSystemIdentifierType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRNamingSystemType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRNamingSystemType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRNarrativeStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRNarrativeStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRNoteType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRNoteType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRObservationDataType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRObservationDataType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRObservationRangeCategory\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRObservationRangeCategory.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRObservationStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRObservationStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIROid\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIROid.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIROperationKind\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIROperationKind.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIROperationParameterUse\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIROperationParameterUse.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIROrientationType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIROrientationType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRParticipantRequired\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRParticipantRequired.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRParticipationStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRParticipationStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRPositiveInt\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRPositiveInt.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRPropertyRepresentation\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRPropertyRepresentation.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRPropertyType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRPropertyType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRProvenanceEntityRole\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRProvenanceEntityRole.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRPublicationStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRPublicationStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRQualityType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRQualityType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRQuantityComparator\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRQuantityComparator.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRQuestionnaireItemOperator\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRQuestionnaireItemOperator.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRQuestionnaireItemType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRQuestionnaireItemType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRQuestionnaireResponseStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRQuestionnaireResponseStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRReferenceHandlingPolicy\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRReferenceHandlingPolicy.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRReferenceVersionRules\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRReferenceVersionRules.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRRelatedArtifactType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRRelatedArtifactType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRRemittanceOutcome\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRRemittanceOutcome.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRRepositoryType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRRepositoryType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRRequestIntent\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRRequestIntent.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRRequestPriority\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRRequestPriority.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRRequestResourceType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRRequestResourceType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRRequestStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRRequestStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRResearchElementType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRResearchElementType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRResearchStudyStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRResearchStudyStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRResearchSubjectStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRResearchSubjectStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRResourceType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRResourceType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRResourceVersionPolicy\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRResourceVersionPolicy.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRResponseType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRResponseType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRRestfulCapabilityMode\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRRestfulCapabilityMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSPDXLicense\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSPDXLicense.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSampledDataDataType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSampledDataDataType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSearchComparator\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSearchComparator.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSearchEntryMode\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSearchEntryMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSearchModifierCode\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSearchModifierCode.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSearchParamType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSearchParamType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSequenceType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSequenceType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSlicingRules\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSlicingRules.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSlotStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSlotStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSortDirection\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSortDirection.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSpecimenContainedPreference\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSpecimenContainedPreference.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSpecimenStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSpecimenStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRStrandType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRStrandType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRString\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRString.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRStructureDefinitionKind\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRStructureDefinitionKind.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRStructureMapContextType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRStructureMapContextType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRStructureMapGroupTypeMode\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRStructureMapGroupTypeMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRStructureMapInputMode\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRStructureMapInputMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRStructureMapModelMode\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRStructureMapModelMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRStructureMapSourceListMode\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRStructureMapSourceListMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRStructureMapTargetListMode\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRStructureMapTargetListMode.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRStructureMapTransform\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRStructureMapTransform.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSubscriptionChannelType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSubscriptionChannelType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSubscriptionStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSubscriptionStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSupplyDeliveryStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSupplyDeliveryStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSupplyRequestStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSupplyRequestStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRSystemRestfulInteraction\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRSystemRestfulInteraction.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRTaskIntent\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRTaskIntent.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRTaskStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRTaskStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRTestReportActionResult\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRTestReportActionResult.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRTestReportParticipantType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRTestReportParticipantType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRTestReportResult\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRTestReportResult.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRTestReportStatus\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRTestReportStatus.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRTestScriptRequestMethodCode\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRTestScriptRequestMethodCode.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRTime\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRTime.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRTriggerType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRTriggerType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRTypeDerivationRule\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRTypeDerivationRule.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRTypeRestfulInteraction\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRTypeRestfulInteraction.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRUDIEntryType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRUDIEntryType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRUnitsOfTime\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRUnitsOfTime.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRUnsignedInt\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRUnsignedInt.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRUri\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRUri.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRUrl\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRUrl.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRUse\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRUse.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRUuid\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRUuid.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRVConfidentialityClassification\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRVConfidentialityClassification.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRVariableType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRVariableType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRVisionBase\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRVisionBase.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRVisionEyes\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRVisionEyes.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\\\FHIRXPathUsageType\\:\\:jsonSerialize\\(\\) should be covariant with return type \\(array\\) of method OpenEMR\\\\FHIR\\\\R4\\\\FHIRElement\\:\\:jsonSerialize\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/FHIR/R4/FHIRElement/FHIRXPathUsageType.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(bool\\|OpenEMR\\\\Validators\\\\ProcessingResult\\|null\\) of method OpenEMR\\\\Services\\\\DocumentService\\:\\:search\\(\\) should be covariant with return type \\(OpenEMR\\\\Validators\\\\ProcessingResult\\) of method OpenEMR\\\\Services\\\\BaseService\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/DocumentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(bool\\|OpenEMR\\\\Validators\\\\ProcessingResult\\|null\\) of method OpenEMR\\\\Services\\\\EncounterService\\:\\:search\\(\\) should be covariant with return type \\(OpenEMR\\\\Validators\\\\ProcessingResult\\) of method OpenEMR\\\\Services\\\\BaseService\\:\\:search\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/EncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCondition\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionEncounterDiagnosisService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionEncounterDiagnosisService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionEncounterDiagnosisService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionEncounterDiagnosisService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionEncounterDiagnosisService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionEncounterDiagnosisService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionHealthConcernService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionHealthConcernService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Condition\\\\FhirConditionProblemListItemService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Condition/FhirConditionProblemListItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\Services\\\\FHIR\\\\DiagnosticReport\\\\the\\) of method OpenEMR\\\\Services\\\\FHIR\\\\DiagnosticReport\\\\FhirDiagnosticReportClinicalNotesService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|false\\) of method OpenEMR\\\\Services\\\\FHIR\\\\DiagnosticReport\\\\FhirDiagnosticReportLaboratoryService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DiagnosticReport/FhirDiagnosticReportLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\Services\\\\FHIR\\\\DocumentReference\\\\the\\) of method OpenEMR\\\\Services\\\\FHIR\\\\DocumentReference\\\\FhirClinicalNotesService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DocumentReference/FhirClinicalNotesService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRDocumentReference\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\DocumentReference\\\\FhirDocumentReferenceAdvanceCareDirectiveService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/DocumentReference/FhirDocumentReferenceAdvanceCareDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRAllergyIntolerance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirAllergyIntoleranceService\\:\\:createProvenanceResource\\(\\) should be compatible with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirAllergyIntoleranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirAllergyIntoleranceService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirAllergyIntoleranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\Services\\\\FHIR\\\\the\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirAppointmentService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirAppointmentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\Services\\\\FHIR\\\\the\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirAppointmentService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirAppointmentService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirCarePlanService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCarePlanService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRCareTeam\\|string\\|false\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirCareTeamService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCareTeamService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirCareTeamService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCareTeamService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirConditionService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirConditionService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|false\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirCoverageService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCoverageService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirCoverageService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirCoverageService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\Services\\\\FHIR\\\\the\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirDeviceService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDeviceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\Services\\\\FHIR\\\\the\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirDeviceService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirDeviceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirEncounterService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirEncounterService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirGoalService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirGoalService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirImmunizationService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirImmunizationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirLocationService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirLocationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedia\\|string\\|false\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirMediaService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMediaService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirMediaService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMediaService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationRequest\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirMedicationRequestService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationRequestService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirMedicationRequestService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationRequestService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirMedicationRequestService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationRequestService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirMedicationService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirMedicationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirPatientService\\:\\:getProfileURIs\\(\\) should be covariant with return type \\(array\\<string\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceUSCIGProfileService\\:\\:getProfileURIs\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirPatientService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirPersonService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPersonService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirPractitionerRoleService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPractitionerRoleService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirPractitionerService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirPractitionerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirProcedureService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\Services\\\\FHIR\\\\the\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirProvenanceService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirProvenanceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirQuestionnaireResponseService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireResponseService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirQuestionnaireService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirQuestionnaireService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirQuestionnaireService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirSpecimenService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirSpecimenService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirSpecimenService\\:\\:getProfileURIs\\(\\) should be covariant with return type \\(array\\<string\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\IResourceUSCIGProfileService\\:\\:getProfileURIs\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/FhirSpecimenService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRMedicationDispense\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\MedicationDispense\\\\FhirMedicationDispenseLocalDispensaryService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/MedicationDispense/FhirMedicationDispenseLocalDispensaryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationAdvanceDirectiveService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationAdvanceDirectiveService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationAdvanceDirectiveService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|false\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationCareExperiencePreferenceService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationCareExperiencePreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationCareExperiencePreferenceService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationCareExperiencePreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationEmployerService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationEmployerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationEmployerService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationEmployerService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationHistorySdohService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationHistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationHistorySdohService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationHistorySdohService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|false\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationLaboratoryService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\the\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationLaboratoryService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationLaboratoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationObservationFormService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationObservationFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationObservationFormService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationObservationFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationPatientService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationPatientService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationPatientService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|null\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationQuestionnaireItemService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationQuestionnaireItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationQuestionnaireItemService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationQuestionnaireItemService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|false\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationSocialHistoryService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationSocialHistoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationSocialHistoryService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationSocialHistoryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\|false\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationTreatmentInterventionPreferenceService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationTreatmentInterventionPreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationTreatmentInterventionPreferenceService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationTreatmentInterventionPreferenceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationVitalsService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationVitalsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Observation\\\\FhirObservationVitalsService\\:\\:parseOpenEMRRecord\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRResource\\\\FHIRDomainResource\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:parseOpenEMRRecord\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Observation/FhirObservationVitalsService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Organization\\\\FhirOrganizationFacilityService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Organization/FhirOrganizationFacilityService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Organization\\\\FhirOrganizationInsuranceService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Organization/FhirOrganizationInsuranceService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Organization\\\\FhirOrganizationProcedureProviderService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Organization/FhirOrganizationProcedureProviderService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\the\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\FhirProcedureOEProcedureService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureOEProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\FhirProcedureOEProcedureService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureOEProcedureService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\the\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\FhirProcedureSurgeryService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureSurgeryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Procedure\\\\FhirProcedureSurgeryService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Procedure/FhirProcedureSurgeryService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Questionnaire\\\\FhirQuestionnaireFormService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Questionnaire/FhirQuestionnaireFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\Questionnaire\\\\FhirQuestionnaireFormService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/Questionnaire/FhirQuestionnaireFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\|string\\) of method OpenEMR\\\\Services\\\\FHIR\\\\QuestionnaireResponse\\\\FhirQuestionnaireResponseFormService\\:\\:createProvenanceResource\\(\\) should be covariant with return type \\(OpenEMR\\\\FHIR\\\\R4\\\\FHIRDomainResource\\\\FHIRProvenance\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:createProvenanceResource\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\FHIR\\\\QuestionnaireResponse\\\\FhirQuestionnaireResponseFormService\\:\\:loadSearchParameters\\(\\) should be covariant with return type \\(array\\<string, OpenEMR\\\\Services\\\\Search\\\\FhirSearchParameterDefinition\\>\\) of method OpenEMR\\\\Services\\\\FHIR\\\\FhirServiceBase\\:\\:loadSearchParameters\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/FHIR/QuestionnaireResponse/FhirQuestionnaireResponseFormService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Services\\\\ObservationService\\:\\:createResultRecordFromDatabaseResult\\(\\) should be covariant with return type \\(array\\<string, mixed\\>\\) of method OpenEMR\\\\Services\\\\BaseService\\:\\:createResultRecordFromDatabaseResult\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/ObservationService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(mixed\\) of method OpenEMR\\\\Services\\\\Search\\\\BasicSearchField\\:\\:getField\\(\\) should be covariant with return type \\(string\\) of method OpenEMR\\\\Services\\\\Search\\\\ISearchField\\:\\:getField\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Search/BasicSearchField.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\Services\\\\Utils\\\\SQLUpgradeService\\) of method OpenEMR\\\\Services\\\\Utils\\\\SQLUpgradeService\\:\\:setRenderOutputToScreen\\(\\) should be covariant with return type \\(\\$this\\(OpenEMR\\\\Services\\\\Utils\\\\Interfaces\\\\ISQLUpgradeService\\)\\) of method OpenEMR\\\\Services\\\\Utils\\\\Interfaces\\\\ISQLUpgradeService\\:\\:setRenderOutputToScreen\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Utils/SQLUpgradeService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(OpenEMR\\\\Services\\\\Utils\\\\SQLUpgradeService\\) of method OpenEMR\\\\Services\\\\Utils\\\\SQLUpgradeService\\:\\:setThrowExceptionOnError\\(\\) should be covariant with return type \\(\\$this\\(OpenEMR\\\\Services\\\\Utils\\\\Interfaces\\\\ISQLUpgradeService\\)\\) of method OpenEMR\\\\Services\\\\Utils\\\\Interfaces\\\\ISQLUpgradeService\\:\\:setThrowExceptionOnError\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Services/Utils/SQLUpgradeService.php',
];
$ignoreErrors[] = [
    'message' => '#^Return type \\(array\\) of method OpenEMR\\\\Tests\\\\Fixtures\\\\GaclFixtureManager\\:\\:getSingleFixture\\(\\) should be compatible with return type \\(OpenEMR\\\\Tests\\\\Fixtures\\\\a\\) of method OpenEMR\\\\Tests\\\\Fixtures\\\\BaseFixtureManager\\:\\:getSingleFixture\\(\\)$#',
    'count' => 1,
    'path' => __DIR__ . '/../../tests/Tests/Fixtures/GaclFixtureManager.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
