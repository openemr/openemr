<?php

chdir(__DIR__);
require 'vendor/autoload.php';
header('Content-type: text/plain');

use OpenEMR\PaymentProcessing\Rainforest\Webhooks\Verifier;
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
    if ($wh->getMerchantId() !== $mid) {
        error_log('Webhook for other merchant, ignoring');
        header('HTTP/1.1 204 No Content');
        exit;
    }
    print_r($wh);
    // TODO: figure out the processing rules here.
    // More or less, when payin.authorized (or .succeeded?), look up the ar_ data
    // and update it so it shows the payment has cleared.
} catch (Throwable $e) {
    error_log((string)$e);
    header('HTTP/1.1 400 Bad Request');
}
