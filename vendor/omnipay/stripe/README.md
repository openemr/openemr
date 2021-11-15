# Omnipay: Stripe

**Stripe driver for the Omnipay PHP payment processing library**

[![Build Status](https://travis-ci.org/thephpleague/omnipay-stripe.png?branch=master)](https://travis-ci.org/thephpleague/omnipay-stripe)
[![Latest Stable Version](https://poser.pugx.org/omnipay/stripe/version.png)](https://packagist.org/packages/omnipay/stripe)
[![Total Downloads](https://poser.pugx.org/omnipay/stripe/d/total.png)](https://packagist.org/packages/omnipay/stripe)

[Omnipay](https://github.com/thephpleague/omnipay) is a framework agnostic, multi-gateway payment
processing library for PHP. This package implements Stripe support for Omnipay.

## Installation

Omnipay is installed via [Composer](http://getcomposer.org/). To install, simply require `league/omnipay` and `omnipay/stripe` with Composer:

```
composer require league/omnipay omnipay/stripe
```

## Basic Usage

The following gateways are provided by this package:

* [Stripe Charge](https://stripe.com/docs/charges)
* [Stripe Payment Intents](https://stripe.com/docs/payments/payment-intents)

For general usage instructions, please see the main [Omnipay](https://github.com/thephpleague/omnipay)
repository.

### Stripe.js

The Stripe integration is fairly straight forward. Essentially you just pass
a `token` field through to Stripe instead of the regular credit card data.

Start by following the standard Stripe JS guide here:
[https://stripe.com/docs/tutorials/forms](https://stripe.com/docs/tutorials/forms)

After that you will have a `stripeToken` field which will be submitted to your server.
Simply pass this through to the gateway as `token`, instead of the usual `card` array:

```php
        $token = $_POST['stripeToken'];

        $response = $gateway->purchase([
            'amount' => '10.00',
            'currency' => 'USD',
            'token' => $token,
        ])->send();
```

### Stripe Payment Intents

Stripe Payment Intents is the Stripe's new foundational payment API. As opposed to Charges API, Payment Intents supports [Strong Customer Authentication](https://stripe.com/docs/strong-customer-authentication). It means that during the payment process, the user _might_ be redirected to an off-site page hosted by the customer's bank for authentication purposes.

This plugin's implementation uses the manual Payment Intent confirmation flow, which is pretty similar to the one the Charges API uses. It shouldn't be too hard to modify your current payment flow.

1) Start by [collecting the payment method details](https://stripe.com/docs/payments/payment-intents/quickstart#collect-payment-method) from the customer. Alternatively, if the customer has provided this earlier and has saved a payment method in your system, you can re-use that.

2) Proceed to authorize or purchase as when using the Charges API.

```php
$paymentMethod = $_POST['paymentMethodId'];

$response = $gateway->authorize([
     'amount'                   => '10.00',
     'currency'                 => 'USD',
     'description'              => 'This is a test purchase transaction.',
     'paymentMethod'            => $paymentMethod,
     'returnUrl'                => $completePaymentUrl,
     'confirm'                  => true,
 ])->send();
```

* If you have a token, instead of a payment method, you can use that by setting the `token` parameter, instead of setting the `paymentMethod` parameter.
* The `returnUrl` must point to where you would redirect every off-site gateway. This parameter is mandatory, if `confirm` is set to true.
* If you don't set the `confirm` parameter to `true`, you will have to manually confirm the payment intent as shown below.

```php
$paymentIntentReference = $response->getPaymentIntentReference();

$response = $gateway->confirm([
    'paymentIntentReference' => $paymentIntentReference,
    'returnUrl' => $completePaymentUrl,
])->send();
```

At this point, you'll need to save a reference to the payment intent. `$_SESSION` can be used for this purpose, but a more common pattern is to have a reference to the current order encoded in the `$completePaymentUrl` URL. In this case, now would be an excellent time to save the relationship between the order and the payment intent somewhere so that you can retrieve the payment intent reference at a later point.

3) Check if the payment is successful. If it is, that means the 3DS authentication was not required. This decision is up to Stripe (taking into account any custom Radar rules you have set) and the issuing bank.

```php
if ($response->isSuccessful()) {
    // Pop open that champagne bottle, because the payment is complete.
} else if($response->isRedirect()) {
    $response->redirect();
} else {
    // The payment has failed. Use $response->getMessage() to figure out why and return to step (1).
}
```

4) The customer is redirected to the 3DS authentication page. Once they authenticate (or fail to do so), the customer is redirected to the URL specified earlier with `completePaymentUrl`.

5) Retrieve the `$paymentIntentReference` mentioned at the end of step (2).

6) Now we have to confirm the payment intent, to signal Stripe that everything is under control.

```php
$response = $gateway->confirm([
    'paymentIntentReference' => $paymentIntentReference,
    'returnUrl' => $completePaymentUrl,
])->send();

if ($response->isSuccessful()) {
    // All done!! Big bucks!
} else {
    // The response will not be successful if the 3DS authentication process failed or the card has been declined. Either way, it's back to step (1)!
}
```

### Stripe Connect

Stripe connect applications can charge an additional fee on top of Stripe's fees for charges they make on behalf of 
their users. To do this you need to specify an additional `transactionFee` parameter as part of an authorize or purchase
request.

When a charge is refunded the transaction fee is refunded with an amount proportional to the amount of the charge
refunded and by default this will come from your connected user's Stripe account effectively leaving them out of pocket.
To refund from your (the applications) Stripe account instead you can pass a ``refundApplicationFee`` parameter with a
boolean value of true as part of a refund request.

Note: making requests with Stripe Connect specific parameters can only be made using the OAuth access token you received
as part of the authorization process. Read more on Stripe Connect [here](https://stripe.com/docs/connect).

## Test Mode

Stripe accounts have test-mode API keys as well as live-mode API keys. These keys can be active
at the same time. Data created with test-mode credentials will never hit the credit card networks
and will never cost anyone money.

Unlike some gateways, there is no test mode endpoint separate to the live mode endpoint, the
Stripe API endpoint is the same for test and for live.

## Support

If you are having general issues with Omnipay, we suggest posting on
[Stack Overflow](http://stackoverflow.com/). Be sure to add the
[omnipay tag](http://stackoverflow.com/questions/tagged/omnipay) so it can be easily found.

If you want to keep up to date with release announcements, discuss ideas for the project,
or ask more detailed questions, there is also a [mailing list](https://groups.google.com/forum/#!forum/omnipay) which
you can subscribe to.

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/thephpleague/omnipay-stripe/issues),
or better yet, fork the library and submit a pull request.
