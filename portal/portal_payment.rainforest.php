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

use Money\{Currency, Currencies\ISOCurrencies, Parser\DecimalMoneyParser};
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

// Future scope: proper JSON API (e.g. PSR-7 resources)

$currencies = new ISOCurrencies();
$parser = new DecimalMoneyParser($currencies);

$usd = new Currency('USD');

$money = $parser->parse($postBody['dollars'], $usd);

if (!$money->isPositive()) {
    throw new UnexpectedValueException('Payment amount must be positive');
}

$encounters = array_map(function ($row) use ($parser, $usd) {
    return [
        'id' => $row['id'],
        'code' => $row['code'],
        'codeType' => $row['codeType'],
        'amount' => $parser->parse($row['value'], $usd),
    ];
}, $postBody['encounters']);

$gb = OEGlobalsBag::getInstance();

$rf = Rainforest::makeFromGlobals($gb);
$params = $rf->getPaymentComponentParameters($money, patientId: $postBody['patientId'], encounters: $encounters);
header('Content-type: application/json');
echo json_encode($params);
