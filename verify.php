<?php

chdir(__DIR__);
require 'vendor/autoload.php';
header('Content-type: text/plain');

$ignoreAuth_onsite_portal = true;
require_once __DIR__ . '/interface/globals.php';

use OpenEMR\PaymentProcessing\Rainforest\Webhooks\{Dispatcher, Verifier, RecordPayment};
use Monolog\Logger;
use Http\Discovery\Psr17Factory;

\Dotenv\Dotenv::createImmutable('.')->load();

$mid = $_ENV['RAINFOREST_MERCHANT_ID'] ?? null;
if ($mid === null) {
    throw new InvalidArgumentException('RAINFOREST_MERCHANT_ID envvar is missing.');
}

$whv = new Verifier($_ENV['RAINFOREST_WEBHOOK_SECRET']);

$req = (new Psr17Factory())->createServerRequestFromGlobals();

try {
    $wh = $whv->verify($req);
    // In the future, we may want this to have an async "save for later and
    // write into a queue" receiver, and immediately yield a 2xx. As long as
    // the Webhook structure is serializable, the processors should work just
    // fine asynchronously.
    $disp = new Dispatcher(
        processors: [
            new RecordPayment(),
        ],
        merchantId: $mid,
        logger: new Logger('OpenEMR'),
    );
    print_r($wh);
    $disp->dispatch($wh);
    // TODO: figure out the processing rules here.
    // More or less, when payin.authorized (or .succeeded?), look up the ar_ data
    // and update it so it shows the payment has cleared.
} catch (Throwable $e) {
    error_log((string)$e);
    header('HTTP/1.1 400 Bad Request');
}
