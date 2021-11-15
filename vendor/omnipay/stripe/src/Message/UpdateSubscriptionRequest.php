<?php

/**
 * Stripe Update Subscription Request.
 */

namespace Omnipay\Stripe\Message;

/**
 * Stripe Update Subscription Request
 *
 * @see \Omnipay\Stripe\Gateway
 * @link https://stripe.com/docs/api#update_subscription
 */
class UpdateSubscriptionRequest extends AbstractRequest
{
    /**
     * Get the plan
     *
     * @return string
     */
    public function getPlan()
    {
        return $this->getParameter('plan');
    }

    /**
     * Set the plan
     *
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest|UpdateSubscriptionRequest
     */
    public function setPlan($value)
    {
        return $this->setParameter('plan', $value);
    }

    /**
     * @deprecated
     */
    public function getPlanId()
    {
        return $this->getPlan();
    }

    /**
     * @deprecated
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest|UpdateSubscriptionRequest
     */
    public function setPlanId($value)
    {
        return $this->setPlan($value);
    }

    /**
     * Get the subscription reference
     *
     * @return string
     */
    public function getSubscriptionReference()
    {
        return $this->getParameter('subscriptionReference');
    }

    /**
     * Set the subscription reference
     *
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest|UpdateSubscriptionRequest
     */
    public function setSubscriptionReference($value)
    {
        return $this->setParameter('subscriptionReference', $value);
    }

    public function getData()
    {
        $this->validate('customerReference', 'subscriptionReference', 'plan');

        $data = array(
            'plan' => $this->getPlan()
        );

        if ($this->parameters->has('tax_percent')) {
            $data['tax_percent'] = (float)$this->getParameter('tax_percent');
        }

        if ($this->getMetadata()) {
            $data['metadata'] = $this->getMetadata();
        }

        return $data;
    }

    public function getEndpoint()
    {
        return $this->endpoint.'/customers/'.$this->getCustomerReference()
            .'/subscriptions/'.$this->getSubscriptionReference();
    }
}
