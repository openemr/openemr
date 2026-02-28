<?php

use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Cqm\CqmClient;

if (php_sapi_name() !== 'cli') {
    exit;
}

$ignoreAuth = true;
$fake_register_globals = false;
$sanitize_all_escapes = true;

require_once(__DIR__ . "/../../vendor/autoload.php");
$session = SessionWrapperFactory::getInstance()->getCoreSession();
$session->set('site_id', 'default');

$manager = new CqmClient();
print_r($manager->getHealth());
