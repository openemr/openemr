<?php
// This code can be run on an interval (e.g., every 24 hours) for each active
// metered subscription.

use Stripe;
use Ramsey\Uuid\Uuid;
require '../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

// Set your secret key. Remember to switch to your live secret key in production!
// See your keys here: https://dashboard.stripe.com/account/apikeys
$stripe = new Stripe\StripeClient(getenv('STRIPE_SECRET_KEY'));

// You need to write some of your own business logic before creating the
// usage record. Pull a record of a customer from your database
// and extract the customer's Stripe Subscription Item ID and
// usage for the day. If you aren't storing subscription item IDs,
// you can retrieve the subscription and check for subscription items
// https://stripe.com/docs/api/subscriptions/object#subscription_object-items.
$subscription_item_id = '';
// The usage number you've been keeping track of in your database for
// the last 24 hours.
$usage_quantity = 100;
$action = 'set';

$date = date_create();
$timestamp = date_timestamp_get($date);
// The idempotency key allows you to retry this usage record call if it fails.
$idempotency_key = Uuid::uuid4()->toString();

try {
    $stripe->subscriptionItems->createUsageRecord(
        $subscription_item_id,
        [
            'quantity' => $usage_quantity,
            'timestamp' => $timestamp,
            'action' => $action,
        ],
        [
            'idempotency_key' => $idempotency_key,
        ]
    );
} catch (Stripe\Exception\ApiErrorException $error) {
    echo "Usage report failed for item ID $subscription_item_id with idempotency key $idempotency_key: $error.toString()";
}
