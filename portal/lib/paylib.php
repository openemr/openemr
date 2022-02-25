<?php

/**
 *  Patient Portal
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Will start the (patient) portal OpenEMR session/cookie.
require_once(dirname(__FILE__) . "/../../src/Common/Session/SessionUtil.php");
OpenEMR\Common\Session\SessionUtil::portalSessionStart();

if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two'])) {
    $pid = $_SESSION['pid'];
    $ignoreAuth_onsite_portal = true;
    require_once(dirname(__FILE__) . "/../../interface/globals.php");
} else {
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    $ignoreAuth = false;
    require_once(dirname(__FILE__) . "/../../interface/globals.php");
    if (!isset($_SESSION['authUserID'])) {
        $landingpage = "index.php";
        header('Location: ' . $landingpage);
        exit();
    }
}

require_once("./appsql.class.php");

use OpenEMR\Billing\PaymentGateway;
use OpenEMR\Common\Crypto\CryptoGen;

if ($_SESSION['portal_init'] !== true) {
    $_SESSION['whereto'] = '#paymentcard';
}

$_SESSION['portal_init'] = false;

if ($_POST['mode'] == 'Sphere') {
    $cryptoGen = new CryptoGen();
    $dataTrans = $cryptoGen->decryptStandard($_POST['enc_data']);
    $dataTrans = json_decode($dataTrans, true);

    $form_pid = $dataTrans['get']['patient_id_cc'];

    $cc = array();
    $cc["cardHolderName"] = $dataTrans['post']['name'];
    $cc['status'] = $dataTrans['post']['status_name'];
    $cc['authCode'] = $dataTrans['post']['authcode'];
    $cc['transId'] = $dataTrans['post']['transid'];
    $cc['cardNumber'] = "******** " . $dataTrans['post']['cc'];
    $cc['cc_type'] = $dataTrans['post']['ccBrand'];
    $cc['zip'] = '';
    $ccaudit = json_encode($cc);
    $invoice = $_POST['invValues'] ?? '';

    $_SESSION['whereto'] = '#paymentcard';

    SaveAudit($form_pid, $invoice, $ccaudit);

    echo 'ok';
}

if ($_POST['mode'] == 'AuthorizeNet') {
    $form_pid = $_POST['form_pid'];
    $pay = new PaymentGateway("AuthorizeNetApi_Api");
    $transaction['amount'] = $_POST['payment'];
    $transaction['currency'] = "USD";
    $transaction['opaqueDataDescriptor'] = $_POST['dataDescriptor'];
    $transaction['opaqueDataValue'] = $_POST['dataValue'];
    try {
        $response = $pay->submitPaymentToken($transaction);
        if (is_string($response)) {
            echo $response;
            exit();
        }
        $r = $response->getParsedData();
        $cc = array();
        $cc["cardHolderName"] = $_POST["cardHolderName"];
        $cc['status'] = $response->getMessage();
        $cc['authCode'] = $r->transactionResponse->authCode;
        $cc['transId'] = $r->transactionResponse->transId;
        $cc['cardNumber'] = $r->transactionResponse->accountNumber;
        $cc['cc_type'] = $r->transactionResponse->accountType;
        $cc['zip'] = $_POST["zip"];
        $ccaudit = json_encode($cc);
        $invoice = isset($_POST['invValues']) ? $_POST['invValues'] : '';
    } catch (\Exception $ex) {
        return $ex->getMessage();
    }

    $_SESSION['whereto'] = '#paymentcard';
    if (!$response->isSuccessful()) {
        echo $response;
        exit();
    }
    $s = SaveAudit($form_pid, $invoice, $ccaudit);

    echo 'ok';
}

if ($_POST['mode'] == 'Stripe') {
    $form_pid = $_POST['form_pid'];
    $pay = new PaymentGateway("Stripe");
    $transaction['amount'] = $_POST['payment'];
    $transaction['currency'] = "USD";
    $transaction['token'] = $_POST['stripeToken'];
    try {
        $response = $pay->submitPaymentToken($transaction);
        if (is_string($response)) {
            echo $response;
            exit();
        }
        $r = $response->getSource();
        $cc = array();
        $cc["cardHolderName"] = $_POST["cardHolderName"];
        $cc['status'] = $response->isSuccessful() ? "Payment Successful" : "Failed";
        $cc['authCode'] = $r['fingerprint'];
        $cc['transId'] = $response->getTransactionReference();
        $cc['cardNumber'] = "******** " . $r['last4'];
        $cc['cc_type'] = $r['brand'];
        $cc['zip'] = $r->address_zip;
        $ccaudit = json_encode($cc);
        $invoice = $_POST['invValues'] ?? '';
    } catch (\Exception $ex) {
        echo $ex->getMessage();
    }

    $_SESSION['whereto'] = '#paymentcard';
    if (!$response->isSuccessful()) {
        echo $response;
        exit();
    }
    $s = SaveAudit($form_pid, $invoice, $ccaudit);

    echo 'ok';
}

if ($_POST['mode'] == 'portal-save') {
    $form_pid = $_POST['form_pid'];
    $form_method = trim($_POST['form_method']);
    $form_source = trim($_POST['form_source']);
    $upay = isset($_POST['form_upay']) ? $_POST['form_upay'] : '';
    $cc = isset($_POST['extra_values']) ? $_POST['extra_values'] : '';
    $amts = isset($_POST['inv_values']) ? $_POST['inv_values'] : '';
    $s = SaveAudit($form_pid, $amts, $cc);
    if ($s) {
        echo 'failed';
        exit();
    }

    echo true;
} elseif ($_POST['mode'] == 'review-save') {
    $form_pid = $_POST['form_pid'];
    $form_method = trim($_POST['form_method']);
    $form_source = trim($_POST['form_source']);
    $upay = isset($_POST['form_upay']) ? $_POST['form_upay'] : '';
    $cc = isset($_POST['extra_values']) ? $_POST['extra_values'] : '';
    $amts = isset($_POST['inv_values']) ? $_POST['inv_values'] : '';
    $s = CloseAudit($form_pid, $amts, $cc);
    if ($s) {
        echo 'failed';
        exit();
    }

    echo true;
}

function SaveAudit($pid, $amts, $cc)
{
    $appsql = new ApplicationTable();
    try {
        $audit = array();
        $audit['patient_id'] = $pid;
        $audit['activity'] = "payment";
        $audit['require_audit'] = "1";
        $audit['pending_action'] = "review";
        $audit['action_taken'] = "";
        $audit['status'] = "waiting";
        $audit['narrative'] = "Authorize online payment.";
        $audit['table_action'] = '';
        $audit['table_args'] = $amts;
        $audit['action_user'] = "0";
        $audit['action_taken_time'] = "";
        $cryptoGen = new CryptoGen();
        $audit['checksum'] = $cryptoGen->encryptStandard($cc);

        $edata = $appsql->getPortalAudit($pid, 'review', 'payment');
        $audit['date'] = $edata['date'];
        if ($edata['id'] > 0) {
            $appsql->portalAudit('update', $edata['id'], $audit);
        } else {
            $appsql->portalAudit('insert', '', $audit);
        }
    } catch (Exception $ex) {
        return $ex;
    }

    return 0;
}

function CloseAudit($pid, $amts, $cc, $action = 'payment posted', $paction = 'notify patient')
{
    $appsql = new ApplicationTable();
    try {
        $audit = array();
        $audit['patient_id'] = $pid;
        $audit['activity'] = "payment";
        $audit['require_audit'] = "1";
        $audit['pending_action'] = $paction;//'review';//
        $audit['action_taken'] = $action;
        $audit['status'] = "closed";//'waiting';
        $audit['narrative'] = "Payment authorized.";
        $audit['table_action'] = "update";
        $audit['table_args'] = $amts;
        $audit['action_user'] = isset($_SESSION['authUserID']) ? $_SESSION['authUserID'] : "0";
        $audit['action_taken_time'] = date("Y-m-d H:i:s");
        $cryptoGen = new CryptoGen();
        $audit['checksum'] = $cryptoGen->encryptStandard($cc);

        $edata = $appsql->getPortalAudit($pid, 'review', 'payment');
        $audit['date'] = $edata['date'];
        if ($edata['id'] > 0) {
            $appsql->portalAudit('update', $edata['id'], $audit);
        }
    } catch (Exception $ex) {
        return $ex;
    }

    return 0;
}
