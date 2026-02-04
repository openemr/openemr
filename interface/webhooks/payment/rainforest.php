<?php

/**
 * Webhook receiver for Rainforest data
 *
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright (c) 2026 OpenCoreEMR, Inc
 * @license   https://www.gnu.org/licenses/licenses.html#GPL GNU GPL V3+
 * @link      https://docs.rainforestpay.com/docs/payin-webhooks
 * @package   OpenEMR
 */

chdir(__DIR__ . '/../../../');

require_once 'vendor/autoload.php';
header('Content-type: text/plain');

$ignoreAuth_onsite_portal = true;
require_once 'interface/globals.php';

use Http\Discovery\Psr17Factory;
use Lcobucci\Clock\SystemClock;
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
$whv = new Verifier(
    clock: SystemClock::fromSystemTimezone(),
    webhookSecret: $crypto->decryptStandard($gb->getString('rainforest_webhook_secret'))
);

$req = (new Psr17Factory())->createServerRequestFromGlobals();
$logger = new Logger('OpenEMR');

try {
    $wh = $whv->verify($req);
} catch (Throwable $e) {
    $logger->error('Webhook verification failed', ['exception' => $e]);
    header('HTTP/1.1 400 Bad Request');
    exit;
}

try {
    // In the future, we may want this to have an async "save for later and
    // write into a queue" receiver, and immediately yield a 2xx. As long as
    // the Webhook structure is serializable, the processors should work just
    // fine asynchronously.
    // See openemr/openemr#10334.
    $disp = new Dispatcher(
        processors: [
            // Future: this should all get wired through DI, etc.
            new RecordPayment(logger: $logger),
        ],
        merchantId: $mid,
        logger: $logger,
        // Since there is no internal retry, let a failure propagate so we get
        // external retries due to the non-2xx response.
        rethrowLastProcessingException: true,
    );
    $disp->dispatch($wh);
} catch (Throwable) {
    // Already logged by dispatcher.
    header('HTTP/1.1 500 Internal Server Error');
    exit;
}
