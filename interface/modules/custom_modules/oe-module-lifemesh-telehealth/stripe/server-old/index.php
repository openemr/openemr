<?php

$ignoreAuth = true;
// Set $sessionAllowWrite to true to prevent session concurrency issues during authorization related code
$sessionAllowWrite = true;

require_once "../../../../../globals.php";
require_once "../../vendor/autoload.php";

use Slim\Http\Request;
use Slim\Http\Response;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);

try {
    //ENV array is being loaded into c
    $v = $dotenv->load();
} catch ( Exception $e ) {
    echo $e->getMessage();
}


require './config.php';

$app = new Slim\App();

// Instantiate the logger as a dependency
$container = $app->getContainer();
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(
        new Monolog\Handler\StreamHandler(
            __DIR__ . '/logs/app.log',
            \Monolog\Logger::DEBUG
        )
    );
    return $logger;
};

/* Initialize the Stripe client */
$container['stripe'] = function ($v) {
    // For sample support and debugging. Not required for production:
    Stripe\Stripe::setAppInfo(
      "stripe-samples/subscription-use-cases/usage-based-subscriptions",
      "0.0.1",
      "https://github.com/stripe-samples/subscription-use-cases/usage-based-subscriptions"
    );

    $stripe = new Stripe\StripeClient([
      'api_key' => $_ENV['STRIPE_SECRET_KEY'],
      'stripe_version' => '2020-08-27',
    ]);

    return $stripe;
};

$app->get('/', function (Request $request, Response $response, array $args) {
    // Display checkout page
    return $response->write(file_get_contents('../../client/index.html'));
});

$app->get('/config', function (
    Request $request,
    Response $response,
    array $args
) {
    $pub_key = $_ENV['STRIPE__PUBLISHABLE_KEY'];

    return $response->withJson(['publishableKey' => $pub_key]);
});

$app->post('/create-customer', function (
    Request $request,
    Response $response,
    array $args
) {
    $body = json_decode($request->getBody());
    $stripe = $this->stripe;

    // Create a new customer object
    $customer = $stripe->customers->create([
        'email' => $body->email,
    ]);

    return $response->withJson(['customer' => $customer]);
});

$app->post('/create-subscription', function (
    Request $request,
    Response $response,
    array $args
) {
    $body = json_decode($request->getBody());
    $stripe = $this->stripe;

    try {
        $payment_method = $stripe->paymentMethods->retrieve(
            $body->paymentMethodId
        );
        $payment_method->attach([
            'customer' => $body->customerId,
        ]);
    } catch (Exception $e) {
        return $response->withJson(['error' => $e->getError()]);
    }

    // Set the default payment method on the customer
    $stripe->customers->update($body->customerId, [
        'invoice_settings' => [
            'default_payment_method' => $body->paymentMethodId,
        ],
    ]);

    // Create the subscription
    $subscription = $stripe->subscriptions->create([
        'customer' => $body->customerId,
        'items' => [
            [
                'price' => getenv($body->priceId),
            ],
        ],
        'expand' => ['latest_invoice.payment_intent', 'pending_setup_intent'],
    ]);

    return $response->withJson($subscription);
});

$app->post('/retry-invoice', function (
    Request $request,
    Response $response,
    array $args
) {
    $body = json_decode($request->getBody());
    $stripe = $this->stripe;

    try {
        $payment_method = $stripe->paymentMethods->retrieve(
            $body->paymentMethodId
        );
        $payment_method->attach([
            'customer' => $body->customerId,
        ]);
    } catch (Exception $e) {
        return $response->withJson($e->jsonBody);
    }

    // Set the default payment method on the customer
    $stripe->customers->update($body->customerId, [
        'invoice_settings' => [
            'default_payment_method' => $body->paymentMethodId,
        ],
    ]);

    $invoice = $stripe->invoices->retrieve($body->invoiceId, [
        'expand' => ['payment_intent'],
    ]);

    return $response->withJson($invoice);
});

$app->post('/retrieve-upcoming-invoice', function (
    Request $request,
    Response $response,
    array $args
) {
    $body = json_decode($request->getBody());
    $stripe = $this->stripe;

    $subscription = $stripe->subscriptions->retrieve($body->subscriptionId);

    $invoice = $stripe->invoices->upcoming([
        "customer" => $body->customerId,
        "subscription_prorate" => true,
        "subscription" => $body->subscriptionId,
        "subscription_items" => [
            [
                'id' => $subscription->items->data[0]->id,
                'deleted' => true,
                'clear_usage' => true,
            ],
            [
                'price' => getenv($body->newPriceId),
                'deleted' => false,
            ],
        ],
    ]);

    return $response->withJson($invoice);
});

$app->post('/cancel-subscription', function (
    Request $request,
    Response $response,
    array $args
) {
    $body = json_decode($request->getBody());
    $stripe = $this->stripe;

    $subscription = $stripe->subscriptions->retrieve($body->subscriptionId);
    $subscription->delete();

    return $response->withJson($subscription);
});

$app->post('/update-subscription', function (
    Request $request,
    Response $response,
    array $args
) {
    $body = json_decode($request->getBody());
    $stripe = $this->stripe;

    $subscription = $stripe->subscriptions->retrieve($body->subscriptionId);

    $updatedSubscription = $stripe->subscriptions->update(
        $body->subscriptionId,
        [
            'items' => [
                [
                    'id' => $subscription->items->data[0]->id,
                    'price' => getenv($body->newPriceId),
                ],
            ],
        ]
    );

    return $response->withJson($updatedSubscription);
});

$app->post('/retrieve-customer-payment-method', function (
    Request $request,
    Response $response,
    array $args
) {
    $body = json_decode($request->getBody());
    $stripe = $this->stripe;

    $paymentMethod = $stripe->paymentMethods->retrieve($body->paymentMethodId);

    return $response->withJson($paymentMethod);
});

$app->post('/webhook', function (Request $request, Response $response) {
    $logger = $this->get('logger');
    $event = $request->getParsedBody();
    $stripe = $this->stripe;

    // Parse the message body (and check the signature if possible)
    $webhookSecret = getenv('STRIPE_WEBHOOK_SECRET');
    if ($webhookSecret) {
        try {
            $event = \Stripe\Webhook::constructEvent(
                $request->getBody(),
                $request->getHeaderLine('stripe-signature'),
                $webhookSecret
            );
        } catch (\Exception $e) {
            return $response
                ->withJson(['error' => $e->getMessage()])
                ->withStatus(403);
        }
    } else {
        $event = $request->getParsedBody();
    }
    $type = $event['type'];
    $object = $event['data']['object'];

    // Handle the event
    // Review important events for Billing webhooks
    // https://stripe.com/docs/billing/webhooks
    // Remove comment to see the various objects sent for this sample
    switch ($type) {
        case 'invoice.paid':
            // The status of the invoice will show up as paid. Store the status in your
            // database to reference when a user accesses your service to avoid hitting rate
            // limits.
            $logger->info('🔔  Webhook received! ' . $object);
            break;
        case 'invoice.payment_failed':
            // If the payment fails or the customer does not have a valid payment method,
            // an invoice.payment_failed event is sent, the subscription becomes past_due.
            // Use this webhook to notify your user that their payment has
            // failed and to retrieve new card details.
            $logger->info('🔔  Webhook received! ' . $object);
            break;
        case 'invoice.finalized':
            // If you want to manually send out invoices to your customers
            // or store them locally to reference to avoid hitting Stripe rate limits.
            $logger->info('🔔  Webhook received! ' . $object);
            break;
        case 'customer.subscription.deleted':
            // handle subscription cancelled automatically based
            // upon your subscription settings. Or if the user
            // cancels it.
            $logger->info('🔔  Webhook received! ' . $object);
            break;
        case 'customer.subscription.trial_will_end':
            // Send notification to your user that the trial will end
            $logger->info('🔔  Webhook received! ' . $object);
            break;
        // ... handle other event types
        default:
        // Unhandled event type
    }

    return $response->withJson(['status' => 'success'])->withStatus(200);
});

$app->run();
