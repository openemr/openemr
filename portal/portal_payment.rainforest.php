<?php

declare(strict_types=1);

chdir(dirname(__DIR__));
require_once 'vendor/autoload.php';

use Http\Discovery\Psr17Factory;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\PaymentProcessing\Rainforest\Apis\GetPayinComponentParameters;

$req = (new Psr17Factory())->createServerRequestFromGlobals();
$csrfToken = $req->getHeaderLine('X-CSRF-TOKEN');

$session = SessionWrapperFactory::getInstance()->getActiveSession();
if (!CsrfUtils::verifyCsrfToken($csrfToken, $session, 'rainforest')) {
    CsrfUtils::csrfNotVerified();
}

// Bind patient identity to the authenticated portal session BEFORE parsing
// the JSON body. The body's `patientId` field is untrusted (any authenticated
// portal patient can submit any value) and is signed into the Rainforest
// `payin_config`'s metadata; when the webhook fires, that metadata is what
// the recorder writes to `ar_activity`. Ignoring the body value here forces
// the eventual `ar_activity` credit to attribute to the paying patient.
$rawSessionPid = $session->get('pid');
$sessionPid = is_scalar($rawSessionPid) ? (string) $rawSessionPid : '';
if ($sessionPid === '' || !$session->has('patient_portal_onsite_two')) {
    // No authenticated portal patient session — refuse the request.
    header('HTTP/1.1 403 Forbidden');
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Portal session required']);
    exit(1);
}

$ignoreAuth_onsite_portal = true;
require_once  'interface/globals.php';

$gb = OEGlobalsBag::getInstance();

$params = GetPayinComponentParameters::parseRawRequest(
    $req,
    $gb,
    trustedPatientId: $sessionPid,
);
header('Content-type: application/json');
echo json_encode($params);
