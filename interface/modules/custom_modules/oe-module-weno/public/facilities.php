<?php

require_once("../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use Twig\Environment;

use OpenEMR\Modules\WenoModule\Services\FacilityProperties;

//ensure user has proper access
if (!AclMain::aclCheckCore('admin', 'super')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Weno Admin")]);
    exit;
}

$facilityObj = new FacilityProperties();

function getTemplatePath()
{
    return \dirname(__DIR__) . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR;
}

$facilities = $facilityObj->getFacilities();
$viewArgs = [
    'facilities' => $facilities,
    'name' => 'kofi'
];

$twig = new TwigContainer(getTemplatePath(), $GLOBALS['kernel']);
echo (new TwigContainer(getTemplatePath(), 
$GLOBALS['kernel']))->getTwig()->render('facilities.html.twig', [$viewArgs]);
// $twigEnv = $twig->getTwig();
// $twig_ = $twigEnv;

?>