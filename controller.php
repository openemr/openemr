<?php

use OpenEMR\Common\Session\SessionWrapperFactory;

require_once(__DIR__ . "/vendor/autoload.php");
$session = SessionWrapperFactory::getInstance()->getWrapper();

if ($session->isSymfonySession() && !empty($session->get('pid')) && !empty($session->get('patient_portal_onsite_two'))) {
    $pid = $session->get('pid');

    $ignoreAuth_onsite_portal = true; // ignore the standard authentication for a regular OpenEMR user
}

require_once("interface/globals.php");

$controller = new Controller();
echo $controller->act($_GET);
