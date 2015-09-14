<?php
//SANITIZE ALL ESCAPES
$sanitize_all_escapes = true;

//STOP FAKE REGISTER GLOBALS
$fake_register_globals = false;

require_once('../../globals.php');
require_once 'coffee_store_settings.php';

if ($METHOD_TO_USE == "AIM") {
    $transaction = new AuthorizeNetAIM;
    $transaction->setSandbox(AUTHORIZENET_SANDBOX);
    $transaction->setFields(
        array(
        'amount' => $amount, 
        'card_num' => $_POST['x_card_num'], 
        'exp_date' => $_POST['x_exp_date'],
        'first_name' => $_POST['x_first_name'],
        'last_name' => $_POST['x_last_name'],
        'address' => $_POST['x_address'],
        'city' => $_POST['x_city'],
        'state' => $_POST['x_state'],
        'country' => $_POST['x_country'],
        'zip' => $_POST['x_zip'],
        'email' => $_POST['x_email'],
        'card_code' => $_POST['x_card_code'],
        )
    );
    $response = $transaction->authorizeAndCapture();
    if ($response->approved) {
        // Transaction approved! Do your logic here.
        header('Location: thank_you_page.php?transaction_id=' . $response->transaction_id);
    } else {
        header('Location: error_page.php?response_reason_code='.$response->response_reason_code.'&response_code='.$response->response_code.'&response_reason_text=' .$response->response_reason_text);
    }
} elseif (count($_POST)) {
    $response = new AuthorizeNetSIM;
    if ($response->isAuthorizeNet()) {
        if ($response->approved) {
            // Transaction approved! Do your logic here.
            // Redirect the user back to your site.
            $return_url = $site_root . 'thank_you_page.php?transaction_id=' .$response->transaction_id;
        } else {
            // There was a problem. Do your logic here.
            // Redirect the user back to your site.
            $return_url = $site_root . 'error_page.php?response_reason_code='.$response->response_reason_code.'&response_code='.$response->response_code.'&response_reason_text=' .$response->response_reason_text;
        }
        echo AuthorizeNetDPM::getRelayResponseSnippet($return_url);
    } else {
        echo "MD5 Hash failed. Check to make sure your MD5 Setting matches the one in config.php";
    }
}