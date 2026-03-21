<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to AppBasePortalController\\:\\:Init\\(\\) on a separate line has no effect\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/DefaultController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to AppBasePortalController\\:\\:Init\\(\\) on a separate line has no effect\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsiteActivityViewController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to AppBasePortalController\\:\\:Init\\(\\) on a separate line has no effect\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsiteDocumentController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to AppBasePortalController\\:\\:Init\\(\\) on a separate line has no effect\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/OnsitePortalActivityController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to AppBasePortalController\\:\\:Init\\(\\) on a separate line has no effect\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to AppBasePortalController\\:\\:Init\\(\\) on a separate line has no effect\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/PortalPatientController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to AppBasePortalController\\:\\:Init\\(\\) on a separate line has no effect\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/libs/Controller/ProviderController.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
