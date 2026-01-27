<?php

/**
 * Webhook receiver for Rainforest data
 * @link https://docs.rainforestpay.com/docs/payin-webhooks
 *
 * @package   OpenEMR
 * @license   https://www.gnu.org/licenses/licenses.html#GPL GNU GPL V3+
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright (c) 2026 OpenCoreEMR, Inc
 */

chdir(__DIR__ . '/../../../');

require 'vendor/autoload.php';
header('Content-type: text/plain');

$ignoreAuth_onsite_portal = true;
require_once 'interface/globals.php';

use Http\Discovery\Psr17Factory;
use Monolog\Logger;
use OpenEMR\PaymentProcessing\Rainforest\Webhooks\{
    Dispatcher,
    RecordPayment,
    Verifier,
};
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Core\OEGlobalsBag;

$gb = OEGlobalsBag::getInstance();

$mid = $gb->getString('rainforest_merchant_id');
if ($mid === '') {
    throw new InvalidArgumentException('rainforest_merchant_id config is missing.');
}

$crypto = new CryptoGen();
$sec = $crypto->decryptStandard($gb->getString('rainforest_webhook_secret'));
$whv = new Verifier($crypto->decryptStandard($gb->getString('rainforest_webhook_secret')));

$req = (new Psr17Factory())->createServerRequestFromGlobals();

try {
    $wh = $whv->verify($req);
    // In the future, we may want this to have an async "save for later and
    // write into a queue" receiver, and immediately yield a 2xx. As long as
    // the Webhook structure is serializable, the processors should work just
    // fine asynchronously.
    // See openemr/openemr#10334.
    $disp = new Dispatcher(
        processors: [
            // Future: this should all get wired through DI, etc.
            new RecordPayment(),
        ],
        merchantId: $mid,
        logger: new Logger('OpenEMR'),
    );
    $disp->dispatch($wh);
} catch (Throwable $e) {
    error_log((string)$e);
    header('HTTP/1.1 400 Bad Request');
}
