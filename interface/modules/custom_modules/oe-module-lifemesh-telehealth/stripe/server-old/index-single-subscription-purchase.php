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
require_once "../../vendor/autoload.php";

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Slim;
use Stripe\Checkout\Session;
use Stripe\Stripe;

//terminal testing
//curl -X POST "http://54.200.69.182/mindful_v6/interface/modules/custom_modules/oe-module-lifemesh-telehealth/stripe/server/create-session"

//create session
$DBSQL = <<<'DB'
 CREATE TABLE IF NOT EXISTS `stripe_sessions`
(
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `stripe_id` TEXT DEFAULT NULL,
    `status` TEXT DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB COMMENT = 'Lifemesh Telehealth';
DB;
$db = $GLOBALS['dbase'];
$exist = sqlQuery("SHOW TABLES FROM `$db` LIKE 'stripe_sessions'");
if (empty($exist)) {
    $exist = sqlQuery($DBSQL);
}

function createSession($sessionId) {
    sqlStatement("INSERT INTO stripe_sessions SET stripe_id = ?, status = ?", array($sessionId, 'pending'));
    return "session pending";
}
//mark session paid
function markSessionPaid($sessionId) {
    sqlQuery("UPDATE stripe_sessions SET status = 'paid' WHERE stripe_id = ?", array($sessionId));
    return "session paid";
}

//getSessionStatus
function getSessionStatus($sessionId) {
    $stmt = sqlQuery("SELECT status FROM stripe_sessions WHERE stripe_id = ?", array($sessionId));
    return $stmt['status'];
}

$app = new App();


$app->add(function ($request, $response, $next) {
    //Secret key
    Stripe::setApiKey('sk_test_51J5VXMGznLM7QeknNafiIDRr7yOXNNrs548ToJ0qzDrF6RisKiroXWakvWObtG5NKslS7LIsjwm1fcpcw7PW0FbS00sDfmleJt');
    return $next($request, $response);
});

$app->post('/create-session-1', function(Request $request, Response $response, array $args) {
    if (!isset($_SERVER['HTTPS'])) {
        $urlpart_http = "http://";
    } else {
        $urlpart_http = "https://";
    }
    //$body = json_decode($request->getBody());

    try {
        // See https://stripe.com/docs/api/checkout/sessions/create
        // for additional parameters to pass.
        // {CHECKOUT_SESSION_ID} is a string literal; do not change it!
        // the actual Session ID is returned in the query parameter when your customer
        // is redirected to the success page.
        $checkout_session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => 'price_1J7Ab0GznLM7QeknfGOjmpd2',
                // For metered billing, do not pass quantity
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => $urlpart_http . $_SERVER[HTTP_HOST] . "/" . $GLOBALS['webroot'] . "/interface/modules/custom_modules/oe-module-lifemesh-telehealth/stripe/client/success.php?session_id={CHECKOUT_SESSION_ID}",
            'cancel_url' =>  $urlpart_http . $_SERVER[HTTP_HOST] . "/" . $GLOBALS['webroot']   . "/interface/modules/custom_modules/oe-module-lifemesh-telehealth/stripe/client/cancel.php",
        ]);
        createSession($checkout_session->id);
    } catch (Exception $e) {
        return $response->withJson([
            'error' => [
                'message' => $e->getError()->message,
            ],
        ], 400);
    }
    return $response->withJson(['sessionId' => $checkout_session['id']]);
    //return $response->withHeader('Location', $checkout_session->url)->withStatus(303);
});

$app->run();
