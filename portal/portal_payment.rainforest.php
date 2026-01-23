<?php

declare(strict_types=1);

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\PaymentProcessing\Rainforest\Apis\GetPayinComponentParameters;

SessionUtil::portalSessionStart();
$ignoreAuth_onsite_portal = true;
require_once __DIR__ . '/../interface/globals.php';

$gb = OEGlobalsBag::getInstance();

$params = GetPayinComponentParameters::parseRawRequest($gb);
header('Content-type: application/json');
echo json_encode($params);
