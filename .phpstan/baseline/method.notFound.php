<?php declare(strict_types = 1);

// total 29 errors

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Application\\\\Controller\\\\IndexController\\:\\:CommonPlugin\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/IndexController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Application\\\\Controller\\\\SendtoController\\:\\:escapeHtml\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Application/src/Application/Controller/SendtoController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Carecoordination\\\\Controller\\\\CarecoordinationController\\:\\:CommonPlugin\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CarecoordinationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Carecoordination\\\\Controller\\\\CarecoordinationController\\:\\:Documents\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CarecoordinationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Carecoordination\\\\Controller\\\\CcdController\\:\\:updateDocumentCategoryUsingCatname\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/CcdController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Carecoordination\\\\Controller\\\\EncountermanagerController\\:\\:CommonPlugin\\(\\)\\.$#',
    'count' => 5,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Controller/EncountermanagerController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Ccr\\\\Controller\\\\CcrController\\:\\:CommonPlugin\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Ccr/src/Ccr/Controller/CcrController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Documents\\\\Controller\\\\DocumentsController\\:\\:Documents\\(\\)\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Documents/src/Documents/Controller/DocumentsController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Immunization\\\\Controller\\\\ImmunizationController\\:\\:CommonPlugin\\(\\)\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Immunization/src/Immunization/Controller/ImmunizationController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method Syndromicsurveillance\\\\Controller\\\\SyndromicsurveillanceController\\:\\:CommonPlugin\\(\\)\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/zend_modules/module/Syndromicsurveillance/src/Syndromicsurveillance/Controller/SyndromicsurveillanceController.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method HTML_TreeMenu_Presentation\\:\\:toHTML\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/TreeMenu.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to an undefined method OpenEMR\\\\Common\\\\ORDataObject\\\\ORDataObject\\:\\:get_id\\(\\)\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/ORDataObject/ORDataObject.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
