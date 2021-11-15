<?php

/**
 * Stripe Fetch Invoice Lines Request.
 */
namespace Omnipay\Stripe\Message;

/**
 * Stripe Fetch Invoice Lines Request.
 *
 * @link https://stripe.com/docs/api#invoice_lines
 */
class FetchInvoiceLinesRequest extends AbstractRequest
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
     * @return FetchInvoiceLinesRequest provides a fluent interface.
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
        return $this->endpoint.'/invoices/'.$this->getInvoiceReference().'/lines';
    }

    public function getHttpMethod()
    {
        return 'GET';
    }
}
