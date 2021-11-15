<?php

/**
 * Stripe Fetch Invoice Request.
 */
namespace Omnipay\Stripe\Message;

/**
 * Stripe Fetch Invoice Request.
 *
 * @link https://stripe.com/docs/api#retrieve_invoice
 */
class FetchInvoiceRequest extends AbstractRequest
{
    /**
     * Get the invoice reference.
     *
     * @return string
     */
    public function getInvoiceReference()
    {
        return $this->getParameter('invoiceReference');
    }

    /**
     * Set the set invoice reference.
     *
     * @return FetchInvoiceRequest provides a fluent interface.
     */
    public function setInvoiceReference($value)
    {
        return $this->setParameter('invoiceReference', $value);
    }

    public function getData()
    {
        $this->validate('invoiceReference');
        $data = array();

        return $data;
    }

    public function getEndpoint()
    {
        return $this->endpoint.'/invoices/'.$this->getInvoiceReference();
    }

    public function getHttpMethod()
    {
        return 'GET';
    }
}
