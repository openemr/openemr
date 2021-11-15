<?php

/**
 * Stripe List Invoices Request.
 */
namespace Omnipay\Stripe\Message;

/**
 * Stripe List Invoices Request.
 *
 * @see Omnipay\Stripe\Gateway
 * @link https://stripe.com/docs/api#list_invoices
 */
class ListInvoicesRequest extends AbstractRequest
{
    public function getData()
    {
        $data = array();

        return $data;
    }

    public function getEndpoint()
    {
        $endpoint = $this->endpoint.'/invoices';
        if ($customerReference = $this->getCustomerReference()) {
            return $endpoint . '?customer=' . $customerReference;
        }

        return $endpoint;
    }

    public function getHttpMethod()
    {
        return 'GET';
    }
}
