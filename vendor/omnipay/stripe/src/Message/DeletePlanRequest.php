<?php

/**
 * Stripe Delete Plan Request.
 */
namespace Omnipay\Stripe\Message;

/**
 * Stripe Delete Plan Request.
 *
 * @link https://stripe.com/docs/api#delete_plan
 */
class DeletePlanRequest extends AbstractRequest
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
     * @return DeletePlanRequest provides a fluent interface.
     */
    public function setId($planId)
    {
        return $this->setParameter('id', $planId);
    }

    public function getData()
    {
        $this->validate('id');

        return;
    }

    public function getEndpoint()
    {
        return $this->endpoint.'/plans/'.$this->getId();
    }

    public function getHttpMethod()
    {
        return 'DELETE';
    }
}
