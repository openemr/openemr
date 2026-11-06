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

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Billing\PaymentGateway;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\PortalSessionPidGuard;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Services\PatientReview\Portal\PaymentPostingLock;
use OpenEMR\Services\PatientReview\Portal\PortalChargeGate;
use OpenEMR\Services\PatientReview\Portal\PortalInvoiceLines;
use OpenEMR\Services\PatientReview\Portal\PortalPaymentAmount;
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
    // Only a portal session reaches here (checked above), so the payment must be its patient's.
    PortalSessionPidGuard::assertMatchesSession($form_pid, $pid);

    $cc = [
        'cardHolderName' => $paymentResult['name'] ?? '',
        'status' => $paymentResult['status_name'] ?? '',
        'authCode' => $paymentResult['authcode'] ?? '',
        'transId' => $paymentResult['transid'] ?? '',
        'cardNumber' => '******** ' . ($paymentResult['cc'] ?? ''),
        'cc_type' => $paymentResult['ccBrand'] ?? '',
        'zip' => '',
    ];
    $invoice = CurrentRequest::get()->request->getString('invValues');
    // Sphere has already charged the card, so the payment is recorded whatever the amount. When
    // the amount charged is not the invoice selection's total, staff are warned before posting.
    $sphereAmount = $paymentResult['amount'] ?? '';
    $cc['amount'] = is_string($sphereAmount) ? $sphereAmount : '';
    // The amount comes from Sphere's redirect through the browser, not from Sphere itself, so a
    // match proves nothing: staff are always asked to check it at Sphere.
    $cc[PortalPaymentAmount::UNVERIFIED] = true;
    $patientId = filter_var($form_pid, FILTER_VALIDATE_INT);
    try {
        $invoiceLines = is_int($patientId) ? (new PortalInvoiceLines())->encounters($patientId) : [];
        $amountMatches = PortalPaymentAmount::matchesInvoice($cc['amount'], $invoice, $invoiceLines);
    } catch (\RuntimeException $lookupError) {
        // The card is already charged and the Sphere result is gone from the session, so the
        // payment is still recorded; staff check it instead.
        ServiceContainer::getLogger()->error('Could not check a Sphere payment against its invoice selection', [
            'pid' => $patientId,
            'transid' => $cc['transId'],
            'exception' => $lookupError,
        ]);
        $amountMatches = false;
    }
    if (!$amountMatches) {
        $cc[PortalPaymentAmount::MISMATCH] = true;
        ServiceContainer::getLogger()->warning('Sphere payment flagged: its amount does not match the invoice selection, or could not be checked', [
            'pid' => $patientId,
            'transid' => $cc['transId'],
        ]);
    }
    $ccaudit = json_encode($cc);

    SessionUtil::setSession('whereto', '#paymentcard');

    SaveAudit($form_pid, $invoice, $ccaudit, charged: true);

    echo 'ok';
    exit;
}

if ($_POST['mode'] == 'AuthorizeNet') {
    $form_pid = $_POST['form_pid'];
    if ($isPortal) {
        PortalSessionPidGuard::assertMatchesSession($form_pid, $pid);
    }
    // The amount charged must be the amount the payment is saved with, which staff post.
    // Checked before the charge gate so a mismatch is refused without taking the payment lock.
    $chargeRequest = CurrentRequest::get()->request;
    $invoiceLines = (new PortalInvoiceLines())->encounters($chargeRequest->getInt('form_pid'));
    if (!PortalPaymentAmount::matchesInvoice($chargeRequest->getString('payment'), $chargeRequest->getString('invValues'), $invoiceLines)) {
        echo xlt('The payment amount does not match the invoice. Reload the page and try again.');
        exit();
    }
    // Refuse a second charge while a charged payment is open (a page loaded before the first
    // one can still submit), holding the patient's payment lock from the check through the charge.
    $chargeRefusal = (new PortalChargeGate(ReviewQueueFactory::portalWorkflows()))->open($form_pid, new PaymentPostingLock());
    if ($chargeRefusal !== null) {
        echo text($chargeRefusal->message());
        exit();
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
    $s = SaveAudit($form_pid, $invoice, $ccaudit, charged: true);

    echo 'ok';
}

if ($_POST['mode'] == 'Stripe') {
    $form_pid = $_POST['form_pid'];
    if ($isPortal) {
        PortalSessionPidGuard::assertMatchesSession($form_pid, $pid);
    }
    // The amount charged must be the amount the payment is saved with, which staff post.
    // Checked before the charge gate so a mismatch is refused without taking the payment lock.
    $chargeRequest = CurrentRequest::get()->request;
    $invoiceLines = (new PortalInvoiceLines())->encounters($chargeRequest->getInt('form_pid'));
    if (!PortalPaymentAmount::matchesInvoice($chargeRequest->getString('payment'), $chargeRequest->getString('invValues'), $invoiceLines)) {
        echo xlt('The payment amount does not match the invoice. Reload the page and try again.');
        exit();
    }
    // Refuse a second charge while a charged payment is open (a page loaded before the first
    // one can still submit), holding the patient's payment lock from the check through the charge.
    $chargeRefusal = (new PortalChargeGate(ReviewQueueFactory::portalWorkflows()))->open($form_pid, new PaymentPostingLock());
    if ($chargeRefusal !== null) {
        echo text($chargeRefusal->message());
        exit();
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
    $s = SaveAudit($form_pid, $invoice, $ccaudit, charged: true);

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
    $s = SaveAudit($form_pid, $amts, $cc, charged: false);
    if ($s) {
        echo 'failed';
        exit();
    }

    echo true;
}

/**
 * Holds the patient's online payment for staff to post. A payment the gateway charged is
 * always kept; one left for staff to charge replaces an earlier uncharged one. The card details
 * are stored encrypted and deleted once posted.
 *
 * @return int|\Throwable 0 on success
 */
function SaveAudit($pid, $amts, $cc, bool $charged)
{
    try {
        $patientId = filter_var($pid, FILTER_VALIDATE_INT);
        if (!is_int($patientId) || $patientId <= 0) {
            throw new InvalidArgumentException('A valid patient ID is required');
        }
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $workflows = ReviewQueueFactory::portalWorkflows();
        $invoiceValues = is_string($amts) ? $amts : '';
        $cardDetails = is_string($cc) ? $cc : '';
        $actor = SessionReviewActor::from($session);
        // A new payment replaces the pending one, so it waits for a staff posting of this
        // patient's pending payment to finish (portal_payment.php holds the same lock).
        $postingLock = new PaymentPostingLock();
        try {
            $postingLock->acquire($patientId);
        } catch (\RuntimeException $lockError) {
            // Staff may be posting the pending payment right now, so it must not be cancelled.
            // The card is already charged, so failing would lose the only record of it: keep it
            // as a second pending payment for staff to post.
            ServiceContainer::getLogger()->warning('Portal payment recorded alongside the pending one: the posting lock was busy', [
                'pid' => $patientId,
                'exception' => $lockError,
            ]);
            $workflows->submitAdditionalPayment($patientId, $invoiceValues, $cardDetails, $charged, $actor);
            return 0;
        }
        try {
            $workflows->submitPayment($patientId, $invoiceValues, $cardDetails, $charged, $actor);
        } finally {
            $postingLock->release();
        }
    } catch (\Throwable $ex) {
        return $ex;
    }

    return 0;
}
