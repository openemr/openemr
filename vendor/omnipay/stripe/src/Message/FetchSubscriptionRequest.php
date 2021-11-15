<?php

/**
 * Stripe Fetch Subscription Request.
 */

namespace Omnipay\Stripe\Message;

/**
 * Stripe Fetch Subscription Request.
 *
 * @link https://stripe.com/docs/api#retrieve_subscription
 */
class FetchSubscriptionRequest extends AbstractRequest
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
     * Set the subscription reference.
     *
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest|FetchSubscriptionRequest
     */
    public function setSubscriptionReference($value)
    {
        return $this->setParameter('subscriptionReference', $value);
    }

    public function getData()
    {
        $this->validate('customerReference', 'subscriptionReference');

        return array();
    }

    public function getEndpoint()
    {
        return $this->endpoint.'/customers/'.$this->getCustomerReference()
            .'/subscriptions/'.$this->getSubscriptionReference();
    }

    public function getHttpMethod()
    {
        return 'GET';
    }
}
