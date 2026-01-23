<?php

declare(strict_types=1);

/**
 * Rainforest Payment Component Endpoint
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Firehed
 * @copyright Copyright (c) 2026 TBD <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

chdir(dirname(__DIR__, 3));
require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit(1);
}

if (!str_starts_with($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
    header('HTTP/1.1 406 Not Acceptable');
    header('Accept: application/json');
    exit(1);
}

use Money\{Currency, Currencies\ISOCurrencies, Parser\DecimalMoneyParser};
use OpenEMR\Modules\RainforestPayment\Rainforest\Rainforest;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Core\OEGlobalsBag;

// Start session and load globals
SessionUtil::portalSessionStart();
$ignoreAuth_onsite_portal = true;
require_once dirname(__DIR__, 3) . '/interface/globals.php';

$rawJson = file_get_contents('php://input');
$postBody = json_decode($rawJson, true, flags: JSON_THROW_ON_ERROR);

$currencies = new ISOCurrencies();
$parser = new DecimalMoneyParser($currencies);

$usd = new Currency('USD');

$money = $parser->parse($postBody['dollars'], $usd);

if (!$money->isPositive()) {
    throw new \UnexpectedValueException('Payment amount must be positive');
}

$encounters = array_map(function ($row) use ($parser, $usd) {
    return new \OpenEMR\Modules\RainforestPayment\Rainforest\EncounterData(
        id: $row['id'],
        code: $row['code'],
        codeType: $row['codeType'],
        amount: $parser->parse($row['value'], $usd),
    );
}, $postBody['encounters']);

$gb = OEGlobalsBag::getInstance();

$rf = Rainforest::makeFromGlobals($gb);
$params = $rf->getPaymentComponentParameters($money, patientId: $postBody['patientId'], encounters: $encounters);
header('Content-type: application/json');
echo json_encode($params);
