<?php

/**
 * Stripe Payment Intents Purchase Request.
 */
namespace Omnipay\Stripe\Message\PaymentIntents;

/**
 * Stripe Payment Intents Purchase Request.
 *
 * A payment method is required. It can be set using the `paymentMethod`, `source`,
 * `cardReference` or `token` parameters.
 *
 * *Important*: Please note, that this gateway is a hybrid between credit card and
 * off-site gateway. It acts as a normal credit card gateway, unless the payment method
 * requires 3DS authentication, in which case it also performs a redirect to an
 * off-site authentication form.
 *
 * Because a purchase request in Stripe looks similar to an Authorize request, this
 * class simply extends the AuthorizeRequest class and overrides the
 * getData method setting capture_method to be automatic.
 *
 * You should also look at that class for code examples.
 *
 * @see \Omnipay\Stripe\PaymentIntentsGateway
 * @see \Omnipay\Stripe\Message\PaymentIntents\AuthorizeRequest
 * @see \Omnipay\Stripe\Message\PaymentIntents\CreatePaymentMethodRequest
 * @see \Omnipay\Stripe\Message\PaymentIntents\ConfirmPaymentIntentRequest
 * @link https://stripe.com/docs/api/payment_intents
 */
class PurchaseRequest extends AuthorizeRequest
{
    public function getData()
    {
        $data = parent::getData();
        $data['capture_method'] = 'automatic';

        return $data;
    }
}
