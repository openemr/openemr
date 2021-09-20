<?php
/*
 *
 * @package      OpenEMR
 * @link               https://www.open-emr.org
 *
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2021 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

$ignoreAuth = true;
// Set $sessionAllowWrite to true to prevent session concurrency issues during authorization related code
$sessionAllowWrite = true;

require_once "../../../../../globals.php";
require '../../vendor/autoload.php';

use Stripe\Checkout\Session;
use Stripe\Stripe;

$dotenv = Dotenv\Dotenv::createImmutable( __DIR__);
try {
    //ENV array is being loaded into c
    $dotenv->load();
} catch ( Exception $e ) {
    echo $e->getMessage();
}

// For sample support and debugging. Not required for production:
Stripe::setAppInfo(
    "Lifemesh OpenEMR Module for Telehealth",
    "0.0.3",
    "https://lifemesh.com"
);

Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
if (!isset($_SERVER['HTTPS'])) {
    $urlpart_http = "http://";
} else {
    $urlpart_http = "https://";
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo 'Invalid request';
    exit;
}

// Create new Checkout Session for the order
// Other optional params include:
// [billing_address_collection] - to display billing address details on the page
// [customer] - if you have an existing Stripe Customer ID
// [payment_intent_data] - lets capture the payment later
// [customer_email] - lets you prefill the email input in the form
// For full details see https://stripe.com/docs/api/checkout/sessions/create

// ?session_id={CHECKOUT_SESSION_ID} means the redirect will have the session ID set as a query param
$checkout_session = Session::create([
    'success_url' => $urlpart_http . $_SERVER[HTTP_HOST] . '/' . $GLOBALS['webroot'] . '/interface/modules/custom_modules/oe-module-lifemesh-telehealth/stripe/server/success?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url' => $urlpart_http . $_SERVER[HTTP_HOST] . '/' . $GLOBALS['webroot'] . '/canceled.php',
    'payment_method_types' => ['card'],
    'mode' => 'subscription',
    'line_items' => [[
        'price' => $_POST['priceId'],
        //'quantity' => 1,
    ]]
]);

header("HTTP/1.1 303 See Other");
header("Location: " . $checkout_session->url);
