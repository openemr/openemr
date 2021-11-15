<?php

/**
 * Stripe Abstract Request.
 */

namespace Omnipay\Stripe\Message\PaymentIntents;

/**
 * Stripe Payment Intent Abstract Request.
 *
 * This is the parent class for all Stripe payment intent requests.
 * It adds just a getter and setter.
 *
 * @see \Omnipay\Stripe\PaymentIntentsGateway
 * @see \Omnipay\Stripe\Message\AbstractRequest
 * @link https://stripe.com/docs/api/payment_intents
 */
abstract class AbstractRequest extends \Omnipay\Stripe\Message\AbstractRequest
{
    /**
     * @param string $value
     *
     * @return AbstractRequest provides a fluent interface.
     */
    public function setPaymentIntentReference($value)
    {
        return $this->setParameter('paymentIntentReference', $value);
    }

    /**
     * @return mixed
     */
    public function getPaymentIntentReference()
    {
        return $this->getParameter('paymentIntentReference');
    }

    /**
     * If there's a reference to a payment method, return that instead.
     *
     * @inheritdoc
     */
    public function getCardReference()
    {
        if ($paymentMethod = $this->getPaymentMethod()) {
            return $paymentMethod;
        }

        return parent::getCardReference();
    }

    /**
     * Actually, set the payment method, which is the preferred API.
     *
     * @inheritdoc
     */
    public function setCardReference($reference)
    {
        $this->setPaymentMethod($reference);
    }
}
