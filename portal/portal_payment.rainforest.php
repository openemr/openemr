<?php

declare(strict_types=1);

chdir(dirname(__DIR__));
require_once 'vendor/autoload.php';

use Http\Discovery\Psr17Factory;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\PaymentProcessing\Rainforest\Apis\GetPayinComponentParameters;

SessionUtil::portalSessionStart();
$ignoreAuth_onsite_portal = true;
require_once  'interface/globals.php';

$gb = OEGlobalsBag::getInstance();
$req = (new Psr17Factory())->createServerRequestFromGlobals();

$params = GetPayinComponentParameters::parseRawRequest($req, $gb);
header('Content-type: application/json');
echo json_encode($params);
