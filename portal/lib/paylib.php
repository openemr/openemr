<?php

/**
 *  Patient Portal
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Billing\PaymentGateway;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\PortalSessionPidGuard;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Services\PatientReview\Portal\SessionReviewActor;
use OpenEMR\Services\PatientReview\ReviewQueueFactory;

// Will start the (patient) portal OpenEMR session/cookie.
// Need access to classes, so run autoloader now instead of in globals.php.
require_once(__DIR__ . "/../../vendor/autoload.php");
// Every request writes session state (portal_init, whereto), so keep session writable
// to avoid repeated reopen-write-close cycles from read_and_close mode.
$sessionAllowWrite = true;
SessionWrapperFactory::getInstance()->setSessionReadOnly(false);
$session = SessionWrapperFactory::getInstance()->getActiveSession();

$isPortal = false;
$pid = null;
if (!empty($session->get('pid')) && !empty($session->get('patient_portal_onsite_two'))) {
    $isPortal = true;
    $pid = $session->get('pid');
    $ignoreAuth_onsite_portal = true;
    require_once(__DIR__ . "/../../interface/globals.php");
} else {
    SessionWrapperFactory::getInstance()->destroyPortalSession();
    $ignoreAuth = false;
    $session = SessionWrapperFactory::getInstance()->getCoreSession();
    require_once(__DIR__ . "/../../interface/globals.php");
    if (!$session->has('authUserID')) {
        $landingpage = "index.php";
        header('Location: ' . $landingpage);
        exit();
    }
}

if ($session->get('portal_init') !== true) {
    SessionUtil::setSession('whereto', '#paymentcard');
}

SessionUtil::setSession('portal_init', false);

if (filter_input(INPUT_SERVER, 'REQUEST_METHOD') === 'POST') {
    CsrfUtils::checkCsrfInput(INPUT_POST, subject: 'portal-payment', dieOnFail: true);
}

if ($_POST['mode'] === 'Sphere') {
    // Sphere patient portal payments require an authenticated portal session
    if (!isset($pid) || !is_int($pid)) {
        http_response_code(403);
        echo 'Unauthorized';
        exit;
    }

    $ticket = filter_input(INPUT_POST, 'sphere_ticket') ?? '';
    $sessionKey = 'sphere_payment_result_' . $ticket;

    $paymentResult = $session->get($sessionKey);
    SessionUtil::unsetSession($sessionKey);

    if (!is_array($paymentResult)) {
        http_response_code(400);
        echo 'Missing or invalid payment data';
        exit;
    }

    $form_pid = $paymentResult['patient_id'] ?? 0;
    if ($isPortal) {
        PortalSessionPidGuard::assertMatchesSession($form_pid, $pid);
    }

    $cc = [
        'cardHolderName' => $paymentResult['name'] ?? '',
        'status' => $paymentResult['status_name'] ?? '',
        'authCode' => $paymentResult['authcode'] ?? '',
        'transId' => $paymentResult['transid'] ?? '',
        'cardNumber' => '******** ' . ($paymentResult['cc'] ?? ''),
        'cc_type' => $paymentResult['ccBrand'] ?? '',
        'zip' => '',
    ];
    $ccaudit = json_encode($cc);
    $invoice = $_POST['invValues'] ?? '';

    SessionUtil::setSession('whereto', '#paymentcard');

    SaveAudit($form_pid, $invoice, $ccaudit);

    echo 'ok';
    exit;
}

if ($_POST['mode'] == 'AuthorizeNet') {
    $form_pid = $_POST['form_pid'];
    if ($isPortal) {
        PortalSessionPidGuard::assertMatchesSession($form_pid, $pid);
    }
    $pay = new PaymentGateway("AuthorizeNetApi_Api");
    $transaction['amount'] = $_POST['payment'];
    $transaction['currency'] = "USD";
    $transaction['opaqueDataDescriptor'] = $_POST['dataDescriptor'];
    $transaction['opaqueDataValue'] = $_POST['dataValue'];
    try {
        /** @var \Omnipay\AuthorizeNetApi\Message\Response|string $response */
        $response = $pay->submitPaymentToken($transaction);
        if (is_string($response)) {
            echo $response;
            exit();
        }
        $r = $response->getParsedData();
        $cc = [];
        $cc["cardHolderName"] = $_POST["cardHolderName"];
        $cc['status'] = $response->getMessage();
        $cc['authCode'] = $r->transactionResponse->authCode;
        $cc['transId'] = $r->transactionResponse->transId;
        $cc['cardNumber'] = $r->transactionResponse->accountNumber;
        $cc['cc_type'] = $r->transactionResponse->accountType;
        $cc['zip'] = $_POST["zip"];
        $ccaudit = json_encode($cc);
        $invoice = $_POST['invValues'] ?? '';
    } catch (\Throwable $ex) {
        return $ex->getMessage();
    }

    SessionUtil::setSession('whereto', '#paymentcard');
    if (!$response->isSuccessful()) {
        echo $response;
        exit();
    }
    $s = SaveAudit($form_pid, $invoice, $ccaudit);

    echo 'ok';
}

if ($_POST['mode'] == 'Stripe') {
    $form_pid = $_POST['form_pid'];
    if ($isPortal) {
        PortalSessionPidGuard::assertMatchesSession($form_pid, $pid);
    }
    $pay = new PaymentGateway("Stripe");
    $transaction['amount'] = $_POST['payment'];
    $transaction['currency'] = "USD";
    $transaction['token'] = $_POST['stripeToken'];
    try {
        /** @var \Omnipay\Stripe\Message\Response|string $response */
        $response = $pay->submitPaymentToken($transaction);
        if (is_string($response)) {
            echo $response;
            exit();
        }
        $r = $response->getSource();
        $cc = [];
        $cc["cardHolderName"] = $_POST["cardHolderName"];
        $cc['status'] = $response->isSuccessful() ? "Payment Successful" : "Failed";
        $cc['authCode'] = $r['fingerprint'];
        $cc['transId'] = $response->getTransactionReference();
        $cc['cardNumber'] = "******** " . $r['last4'];
        $cc['cc_type'] = $r['brand'];
        $cc['zip'] = $r['address_zip'] ?? null;
        $ccaudit = json_encode($cc);
        $invoice = $_POST['invValues'] ?? '';
    } catch (\Throwable $ex) {
        echo $ex->getMessage();
    }

    SessionUtil::setSession('whereto', '#paymentcard');
    if (!$response->isSuccessful()) {
        echo $response;
        exit();
    }
    $s = SaveAudit($form_pid, $invoice, $ccaudit);

    echo 'ok';
}

if ($_POST['mode'] == 'portal-save') {
    $form_pid = $_POST['form_pid'];
    if ($isPortal) {
        PortalSessionPidGuard::assertMatchesSession($form_pid, $pid);
    }
    $form_method = trim((string) $_POST['form_method']);
    $form_source = trim((string) $_POST['form_source']);
    $upay = $_POST['form_upay'] ?? '';
    $cc = $_POST['extra_values'] ?? '';
    $amts = $_POST['inv_values'] ?? '';
    $s = SaveAudit($form_pid, $amts, $cc);
    if ($s) {
        echo 'failed';
        exit();
    }

    echo true;
} elseif ($_POST['mode'] == 'review-save') {
    $form_pid = $_POST['form_pid'];
    if ($isPortal) {
        PortalSessionPidGuard::assertMatchesSession($form_pid, $pid);
    }
    $form_method = trim((string) $_POST['form_method']);
    $form_source = trim((string) $_POST['form_source']);
    // The posted invoice and card values are not kept: the payment itself is recorded in
    // ar_session/ar_activity, and the card details are deleted when the request closes.
    $s = CloseAudit($form_pid);
    if ($s) {
        echo 'failed';
        exit();
    }

    echo true;
}

/**
 * Holds the patient's online payment for staff to post. A second payment before posting
 * replaces the first. The card details are stored encrypted and deleted once posted.
 *
 * @return int|\Throwable 0 on success
 */
function SaveAudit($pid, $amts, $cc)
{
    try {
        $patientId = filter_var($pid, FILTER_VALIDATE_INT);
        if (!is_int($patientId) || $patientId <= 0) {
            throw new InvalidArgumentException('A valid patient ID is required');
        }
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        ReviewQueueFactory::portalWorkflows()->submitPayment(
            $patientId,
            is_string($amts) ? $amts : '',
            is_string($cc) ? $cc : '',
            SessionReviewActor::from($session),
        );
    } catch (\Throwable $ex) {
        return $ex;
    }

    return 0;
}

/**
 * Staff posted the patient's pending online payment.
 *
 * @return int|\Throwable 0 on success
 */
function CloseAudit($pid)
{
    try {
        $patientId = filter_var($pid, FILTER_VALIDATE_INT);
        if (!is_int($patientId) || $patientId <= 0) {
            throw new InvalidArgumentException('A valid patient ID is required');
        }
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        ReviewQueueFactory::portalWorkflows()->completePayment($patientId, SessionReviewActor::staff($session));
    } catch (\Throwable $ex) {
        return $ex;
    }

    return 0;
}
