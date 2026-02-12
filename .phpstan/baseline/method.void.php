<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Result of method OpenEMR\\\\Billing\\\\MiscBillingOptions\\:\\:generateDateQualifierSelect\\(\\) \\(void\\) is used\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/forms/misc_billing_options/new.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of method OpenEMR\\\\OeUI\\\\OemrUI\\:\\:oeBelowContainerDiv\\(\\) \\(void\\) is used\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/main/finder/dynamic_finder.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of method Laminas\\\\EventManager\\\\EventManagerInterface\\:\\:detach\\(\\) \\(void\\) is used\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Listener/Listener.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of method Carecoordination\\\\Model\\\\CarecoordinationTable\\:\\:import\\(\\) \\(void\\) is used\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CarecoordinationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of method OpenEMR\\\\Events\\\\PatientDocuments\\\\PatientDocumentStoreOffsite\\:\\:setPatientId\\(\\) \\(void\\) is used\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of method OpenEMR\\\\Events\\\\PatientDocuments\\\\PatientDocumentStoreOffsite\\:\\:setRemoteCategory\\(\\) \\(void\\) is used\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of method OpenEMR\\\\Events\\\\PatientDocuments\\\\PatientDocumentStoreOffsite\\:\\:setRemoteFileName\\(\\) \\(void\\) is used\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of method OpenEMR\\\\Events\\\\PatientDocuments\\\\PatientDocumentStoreOffsite\\:\\:setRemoteMimeType\\(\\) \\(void\\) is used\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Document.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of method Symfony\\\\Component\\\\HttpFoundation\\\\Session\\\\Session\\:\\:set\\(\\) \\(void\\) is used\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Session/SymfonySessionWrapper.php',
];
$ignoreErrors[] = [
    'message' => '#^Result of method OpenEMR\\\\Modules\\\\FaxSMS\\\\Controller\\\\EmailClient\\:\\:emailReminder\\(\\) \\(void\\) is used\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../tests/Tests/E2e/FaxSmsEmailTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
