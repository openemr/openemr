<?php

declare(strict_types=1);

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit(1);
}
error_log(print_r($_SERVER, true));
if (!str_starts_with($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
    header('HTTP/1.1 406 Not Acceptable');
    header('Accept: application/json');
    exit(1);
}

use Money\Money;
use OpenEMR\PaymentProcessing\Rainforest;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Core\OEGlobalsBag;

// Aped from other AJAX endpoints; this isn't an ideal way to read config but
// it's how most of the current app does it.
SessionUtil::portalSessionStart();
$ignoreAuth_onsite_portal = true;
require_once __DIR__ . '/../interface/globals.php';

$rawJson = file_get_contents('php://input');
$postBody = json_decode($rawJson, true, flags: JSON_THROW_ON_ERROR);
error_log(print_r($postBody, true));

// Future scope: proper JSON API (e.g. PSR-7 resources)
$dollars = $_POST['dollars'] ?? '0.00';
assert(is_string($dollars));
// TODO: validate this properly
$cents = intval(100 * floatval($dollars));
if ($cents <= 0) {
    throw new UnexpectedValueException('Payment amount must be positive');
}
$money = Money::USD($cents);

$gb = OEGlobalsBag::getInstance();

$rf = Rainforest::makeFromGlobals($gb);
$params = $rf->getPaymentComponentParameters($money);
header('Content-type: application/json');
echo json_encode($params);
