<?php

/**
 * Stripe Create Subscription Request.
 */

namespace Omnipay\Stripe\Message;

/**
 * Stripe Create Subscription Request
 *
 * @see \Omnipay\Stripe\Gateway
 * @link https://stripe.com/docs/api/php#create_subscription
 */
class CreateSubscriptionRequest extends AbstractRequest
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
     * @return \Omnipay\Common\Message\AbstractRequest|CreateSubscriptionRequest
     */
    public function setPlan($value)
    {
        return $this->setParameter('plan', $value);
    }

    /**
     * Get the tax percent
     *
     * @return string
     */
    public function getTaxPercent()
    {
        return $this->getParameter('tax_percent');
    }

    /**
     * Set the tax percentage
     *
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest|CreateSubscriptionRequest
     */
    public function setTaxPercent($value)
    {
        return $this->setParameter('tax_percent', $value);
    }

    public function getData()
    {
        $this->validate('customerReference', 'plan');

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
        return $this->endpoint.'/customers/'.$this->getCustomerReference().'/subscriptions';
    }
}
