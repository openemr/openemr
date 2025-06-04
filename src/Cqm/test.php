<?php

use OpenEMR\Cqm\CqmClient;

if (php_sapi_name() !== 'cli') {
    exit;
}

session_name("OpenEMR");
$ignoreAuth = true;
$fake_register_globals = false;
$sanitize_all_escapes = true;
$_SESSION['site_id'] = 'default';

require_once(__DIR__ . "/../../vendor/autoload.php");

$manager = new CqmClient();
print_r($manager->getHealth());
