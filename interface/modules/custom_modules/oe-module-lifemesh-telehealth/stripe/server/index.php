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

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Stripe;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$app = new App;

$app->add(function ($request, $response, $next) {
    // Set your secret key. Remember to switch to your live secret key in production.
    // See your keys here: https://dashboard.stripe.com/apikeys
    Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

    return $next($request, $response);
});

$app->get('/success', function (Request $request, Response $response) {
    $session = Session::retrieve($request->get('session_id'));
    $customer = Customer::retrieve($session->customer);

    return $response->write("<html><body><h1>Thanks for your order, $customer->name!</h1><p>Please close this tab and return to OpenEMR.</p></body></html>");
});

$app->run();
