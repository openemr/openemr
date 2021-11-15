<?php

/**
 * Stripe Delete Customer Request.
 */
namespace Omnipay\Stripe\Message;

/**
 * Stripe Delete Customer Request.
 *
 * Permanently deletes a customer. It cannot be undone. Also immediately
 * cancels any active subscriptions on the customer.
 *
 * @link https://stripe.com/docs/api#delete_customer
 */
class DeleteCustomerRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate('customerReference');

        return;
    }

    public function getHttpMethod()
    {
        return 'DELETE';
    }

    public function getEndpoint()
    {
        return $this->endpoint.'/customers/'.$this->getCustomerReference();
    }
}
