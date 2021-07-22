<?php

/**
 *  Front Payment CC and Terminal Readers support.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$ignoreAuth = false;
require_once(__DIR__ . "/../globals.php");

use OpenEMR\Billing\PaymentGateway;
use OpenEMR\Common\Crypto\CryptoGen;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Terminal\ConnectionToken;
use Stripe\Terminal\Location;

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
    $pd = sqlQuery("SELECT " .
        "p.fname, p.mname, p.lname, p.pubpid, p.pid, i.copay " .
        "FROM patient_data AS p " .
        "LEFT OUTER JOIN insurance_data AS i ON " .
        "i.pid = p.pid AND i.type = 'primary' " .
        "WHERE p.pid = ? ORDER BY i.date DESC LIMIT 1", array($pid));
    $pay = new PaymentGateway("Stripe");
    $transaction['amount'] = $_POST['payment'];
    $transaction['currency'] = "USD";
    $transaction['token'] = $_POST['stripeToken'];
    $transaction['description'] = $pd['lname'] . ' ' . $pd['fname'] . ' ' . $pd['mname'];
    $transaction['metadata'] = [
        'Patient' => $pd['lname'] . ' ' . $pd['fname'] . ' ' . $pd['mname'],
        'MRN' => $pd['pubpid'],
        'Invoice Items (date encounter)' => $_POST['encs'],
        'Invoice Total' => $transaction['amount']
    ];
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

if ($_GET['mode'] == 'terminal_token') {
    $cryptoGen = new CryptoGen();
    $apiKey = $cryptoGen->decryptStandard($GLOBALS['gateway_api_key']);
    Stripe::setApiKey($apiKey);

    header('Content-Type: application/json');

    try {
        $connectionToken = ConnectionToken::create();
        echo json_encode(array('secret' => $connectionToken->secret), JSON_THROW_ON_ERROR);
    } catch (\Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()], JSON_THROW_ON_ERROR);
    }
}
if ($_GET['mode'] == 'cancel_intent') {
    $cryptoGen = new CryptoGen();
    $apiKey = $cryptoGen->decryptStandard($GLOBALS['gateway_api_key']);
    Stripe::setApiKey($apiKey);

    header('Content-Type: application/json');

    try {
        $json_str = file_get_contents('php://input');
        $json_obj = json_decode($json_str);

        $intent = PaymentIntent::retrieve($json_obj->id);
        $rtn = $intent->cancel();

        echo json_encode(['status' => (string)$rtn->status]);
    } catch (\Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

if ($_GET['mode'] == 'terminal_capture') {
    $cryptoGen = new CryptoGen();
    $apiKey = $cryptoGen->decryptStandard($GLOBALS['gateway_api_key']);
    Stripe::setApiKey($apiKey);

    header('Content-Type: application/json');

    try {
        // retrieve JSON from POST body
        $json_str = file_get_contents('php://input');
        $json_obj = json_decode($json_str);

        $intent = PaymentIntent::retrieve($json_obj->id);
        $intent = $intent->capture();

        echo json_encode($intent);
    } catch (\Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()], JSON_THROW_ON_ERROR);
    }
}

if ($_GET['mode'] == 'terminal_create') {
    $cryptoGen = new CryptoGen();
    $apiKey = $cryptoGen->decryptStandard($GLOBALS['gateway_api_key']);
    Stripe::setApiKey($apiKey);

    header('Content-Type: application/json');

    try {
        $json_str = file_get_contents('php://input');
        $json_obj = json_decode($json_str);
        $pd = sqlQuery("SELECT " .
            "p.fname, p.mname, p.lname, p.pubpid,p.pid, p.email, i.copay " .
            "FROM patient_data AS p " .
            "LEFT OUTER JOIN insurance_data AS i ON " .
            "i.pid = p.pid AND i.type = 'primary' " .
            "WHERE p.pid = ? ORDER BY i.date DESC LIMIT 1", array($pid));

        $intent = PaymentIntent::create([
            'amount' => $json_obj->amount,
            'currency' => 'usd',
            'payment_method_types' => ['card_present'],
            'capture_method' => 'manual',
            'description' => $pd['lname'] . ' ' . $pd['fname'] . ' ' . $pd['mname'],
            'metadata' => [
                'Patient' => $pd['lname'] . ' ' . $pd['fname'] . ' ' . $pd['mname'],
                'MRN' => $pd['pubpid'],
                'Invoice Items (date encounter)' => $json_obj->encs,
                'Invoice Total' => number_format(($json_obj->amount / 100), 2, '.', '')
                ]
        ]);
        echo json_encode(array('client_secret' => $intent->client_secret), JSON_THROW_ON_ERROR);
    } catch (\Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()], JSON_THROW_ON_ERROR);
    }
}
