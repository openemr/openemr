<?php

declare(strict_types=1);

chdir(dirname(__DIR__, 2));
require_once 'vendor/autoload.php';

use Http\Discovery\Psr17Factory;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\PaymentProcessing\Rainforest\Apis\GetPayinComponentParameters;

$req = (new Psr17Factory())->createServerRequestFromGlobals();
$csrfToken = $req->getHeaderLine('X-CSRF-TOKEN');

$session = SessionWrapperFactory::getInstance()->getWrapper();
if (!CsrfUtils::verifyCsrfToken($csrfToken, 'rainforest', $session->getSymfonySession())) {
    CsrfUtils::csrfNotVerified();
}

require_once 'interface/globals.php';

$gb = OEGlobalsBag::getInstance();

$params = GetPayinComponentParameters::parseRawRequest($req, $gb);
header('Content-type: application/json');
echo json_encode($params);
