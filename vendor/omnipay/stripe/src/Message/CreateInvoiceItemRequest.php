<?php

/**
 * Stripe Create Invoice Item Request.
 */
namespace Omnipay\Stripe\Message;

/**
 * Stripe Create Invoice Item Request
 *
 * @see \Omnipay\Stripe\Gateway
 * @link https://stripe.com/docs/api#create_invoiceitem
 *
 * Providing the invoice-item reference will update the invoice-item
 * @link https://stripe.com/docs/api#update_invoiceitem
 */
class CreateInvoiceItemRequest extends AbstractRequest
{
    /**
     * Get the invoice-item reference
     *
     * @return string
     */
    public function getInvoiceItemReference()
    {
        return $this->getParameter('invoiceItemReference');
    }

    /**
     * Set the invoice-item reference
     *
     * @return CreateInvoiceItemRequest provides a fluent interface.
     */
    public function setInvoiceItemReference($invoiceItemReference)
    {
        return $this->setParameter('invoiceItemReference', $invoiceItemReference);
    }

    /**
     * Get the invoice-item amount
     *
     * @return int
     */
    public function getAmount()
    {
        return $this->getParameter('amount');
    }

    /**
     * Set the invoice-item amount
     *
     * @return CreateInvoiceItemRequest provides a fluent interface.
     */
    public function setAmount($value)
    {
        return $this->setParameter('amount', $value);
    }

    /**
     * Set the invoice-item currency
     *
     * @return CreateInvoiceItemRequest provides a fluent interface.
     */
    public function setCurrency($currency)
    {
        return $this->setParameter('currency', $currency);
    }

    /**
     * Get the invoice-item currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->getParameter('currency');
    }

    /**
     * Set the invoice-item description
     *
     * @return CreateInvoiceItemRequest provides a fluent interface.
     */
    public function setDescription($description)
    {
        return $this->setParameter('description', $description);
    }

    /**
     * Get the invoice-item description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getParameter('description');
    }

    /**
     * Set the invoice-item discountable
     *
     * @return CreateInvoiceItemRequest provides a fluent interface.
     */
    public function setDiscountable($discountable)
    {
        return $this->setParameter('discountable', $discountable);
    }

    /**
     * Get the invoice-item discountable
     *
     * @return string
     */
    public function getDiscountable()
    {
        return $this->getParameter('discountable');
    }

    /**
     * Set the invoice-item invoice reference
     *
     * @return CreateInvoiceItemRequest provides a fluent interface.
     */
    public function setInvoiceReference($invoiceReference)
    {
        return $this->setParameter('invoiceReference', $invoiceReference);
    }

    /**
     * Get the invoice-item invoice reference
     *
     * @return string
     */
    public function getInvoiceReference()
    {
        return $this->getParameter('invoiceReference');
    }

    /**
     * Set the invoice-item subscription reference
     *
     * @return CreateInvoiceItemRequest provides a fluent interface.
     */
    public function setSubscriptionReference($subscriptionReference)
    {
        return $this->setParameter('subscriptionReference', $subscriptionReference);
    }

    /**
     * Get the invoice-item subscription reference
     *
     * @return string
     */
    public function getSubscriptionReference()
    {
        return $this->getParameter('subscriptionReference');
    }

    public function getData()
    {
        $data = array();

        if ($this->getInvoiceItemReference() == null) {
            $this->validate('customerReference', 'amount', 'currency');
        }

        if ($this->getCustomerReference()) {
            $data['customer'] = $this->getCustomerReference();
        }

        if ($this->getAmount()) {
            $data['amount'] = $this->getAmount();
        }

        if ($this->getCurrency()) {
            $data['currency'] = $this->getCurrency();
        }

        if ($this->getDescription()) {
            $data['description'] = $this->getDescription();
        }

        if ($this->getDiscountable() !== null) {
            $data['discountable'] = $this->getDiscountable();
        }

        if ($this->getInvoiceReference()) {
            $data['invoice'] = $this->getInvoiceReference();
        }

        if ($this->getSubscriptionReference()) {
            $data['subscription'] = $this->getSubscriptionReference();
        }

        return $data;
    }

    public function getEndpoint()
    {
        return $this->endpoint.'/invoiceitems'
            .($this->getInvoiceItemReference() != null ? '/'.$this->getInvoiceItemReference() : '');
    }
}
