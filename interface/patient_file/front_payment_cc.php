<?php

/**
 *  Front Payment CC and Terminal Readers support.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$ignoreAuth = false;
require_once(__DIR__ . "/../globals.php");

use OpenEMR\Billing\PaymentGateway;
use OpenEMR\Common\Crypto\CryptoGen;

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
        $cc['status'] = $response->isSuccessful() ? "ok" : "failed";
        $cc['authCode'] = $r->transactionResponse->authCode;
        $cc['transId'] = $r->transactionResponse->transId;
        $cc['cardNumber'] = $r->transactionResponse->accountNumber;
        $cc['cc_type'] = $r->transactionResponse->accountType;
        $cc['zip'] = $_POST["zip"];
        $ccaudit = json_encode($cc);
    } catch (\Exception $ex) {
        return $ex->getMessage();
    }

    if (!$response->isSuccessful()) {
        echo $response->getMessage();
        exit();
    }

    echo $ccaudit;
    exit();
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
        $cc['status'] = $response->isSuccessful() ? "ok" : "failed";
        $cc['authCode'] = $r['fingerprint'];
        $cc['transId'] = $response->getTransactionReference();
        $cc['cardNumber'] = "******** " . $r['last4'];
        $cc['cc_type'] = $r['brand'];
        $cc['zip'] = $r->address_zip;
        $ccaudit = json_encode($cc);
    } catch (\Exception $ex) {
        echo $ex->getMessage();
    }

    if (!$response->isSuccessful()) {
        echo $response;
        exit();
    }

    echo $ccaudit;
    exit();
}
