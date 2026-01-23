<?php

declare(strict_types=1);

chdir(dirname(__DIR__, 2));
require 'vendor/autoload.php';

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\PaymentProcessing\Rainforest\Apis\GetPayinComponentParameters;

require_once 'interface/globals.php';

$gb = OEGlobalsBag::getInstance();

$params = GetPayinComponentParameters::parseRawRequest($gb);
header('Content-type: application/json');
echo json_encode($params);
