<?php

declare(strict_types=1);

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('405 Method Not Allowed');
    exit(1);
}

use Money\Money;
use OpenEMR\PaymentProcessing\Rainforest;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Core\OEGlobalsBag;

// Aped from other AJAX endpoints; this isn't an ideal way to read config.
SessionUtil::portalSessionStart();
$ignoreAuth_onsite_portal = true;
require_once __DIR__ . '/../interface/globals.php';

// Future scope: proper JSON API
$dollars = $_POST['dollars'] ?? '0.00';
assert(is_string($dollars));
// TODO: validate this properly
$cents = intval(100 * floatval($dollars));
$money = Money::USD($cents);

$gb = OEGlobalsBag::getInstance();

$rf = Rainforest::makeFromGlobals($gb);
$params = $rf->getPaymentComponentParameters($money);
header('Content-type: application/json');
echo json_encode($params);
error_log("New Path");
exit;
