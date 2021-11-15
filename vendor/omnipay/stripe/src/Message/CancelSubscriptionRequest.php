<?php

/**
 * Stripe Cancel Subscription Request.
 */
namespace Omnipay\Stripe\Message;

/**
 * Stripe Cancel Subscription Request.
 *
 * @see \Omnipay\Stripe\Gateway
 * @link https://stripe.com/docs/api/#cancel_subscription
 */
class CancelSubscriptionRequest extends AbstractRequest
{
    /**
     * Get the subscription reference.
     *
     * @return string
     */
    public function getSubscriptionReference()
    {
        return $this->getParameter('subscriptionReference');
    }

    /**
     * Set the set subscription reference.
     *
     * @param string $value
     *
     * @return CancelSubscriptionRequest provides a fluent interface.
     */
    public function setSubscriptionReference($value)
    {
        return $this->setParameter('subscriptionReference', $value);
    }

    /**
     * Set whether or not to cancel the subscription at period end.
     *
     * @param bool $value
     *
     * @return CancelSubscriptionRequest provides a fluent interface.
     */
    public function setAtPeriodEnd($value)
    {
        return $this->setParameter('atPeriodEnd', $value);
    }

    /**
     * Get whether or not to cancel the subscription at period end.
     *
     * @return bool
     */
    public function getAtPeriodEnd()
    {
        return $this->getParameter('atPeriodEnd');
    }

    public function getData()
    {
        $this->validate('customerReference', 'subscriptionReference');

        $data = array();

        // NOTE: Boolean must be passed as string
        // Otherwise it will be converted to numeric 0 or 1
        // Causing an error with the API
        if ($this->getAtPeriodEnd()) {
            $data['at_period_end'] = 'true';
        }

        return $data;
    }

    public function getEndpoint()
    {
        return $this->endpoint
            .'/customers/'.$this->getCustomerReference()
            .'/subscriptions/'.$this->getSubscriptionReference();
    }

    public function getHttpMethod()
    {
        return 'DELETE';
    }
}
