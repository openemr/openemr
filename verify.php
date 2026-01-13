<?php

chdir(__DIR__);
require 'vendor/autoload.php';
header('Content-type: text/plain');

use OpenEMR\PaymentProcessing\Rainforest\WebhookVerifier;
use Http\Discovery\Psr17Factory;

\Dotenv\Dotenv::createUnsafeImmutable('.')->load();

$whv = new WebhookVerifier(getenv('RAINFOREST_WEBHOOK_SECRET'));

$req = (new Psr17Factory())->createServerRequestFromGlobals();

try {
    $wh = $whv->verify($req);
    print_r($wh);
} catch (Throwable $e) {
    error_log((string)$e);
    header('HTTP/1.1 400 Bad Request');
}
