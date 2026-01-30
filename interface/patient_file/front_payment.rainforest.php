<?php

declare(strict_types=1);

chdir(dirname(__DIR__, 2));
require 'vendor/autoload.php';

use Http\Discovery\Psr17Factory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\PaymentProcessing\Rainforest\Apis\GetPayinComponentParameters;

require_once 'interface/globals.php';

$gb = OEGlobalsBag::getInstance();
$req = (new Psr17Factory())->createServerRequestFromGlobals();

$params = GetPayinComponentParameters::parseRawRequest($req, $gb);
header('Content-type: application/json');
echo json_encode($params);
