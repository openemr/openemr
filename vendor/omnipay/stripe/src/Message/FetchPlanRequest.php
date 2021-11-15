<?php

/**
 * Stripe Fetch Plan Request.
 */
namespace Omnipay\Stripe\Message;

/**
 * Stripe Fetch Plan Request.
 *
 * @link https://stripe.com/docs/api#retrieve_plan
 */
class FetchPlanRequest extends AbstractRequest
{
    /**
     * Get the plan id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->getParameter('id');
    }

    /**
     * Set the plan id.
     *
     * @return FetchPlanRequest provides a fluent interface.
     */
    public function setId($planId)
    {
        return $this->setParameter('id', $planId);
    }

    public function getData()
    {
        $this->validate('id');
        $data = array();

        return $data;
    }

    public function getEndpoint()
    {
        return $this->endpoint.'/plans/'.$this->getId();
    }

    public function getHttpMethod()
    {
        return 'GET';
    }
}
