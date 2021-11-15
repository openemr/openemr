<?php

/**
 * Stripe Delete Credit Card Request.
 */
namespace Omnipay\Stripe\Message;

/**
 * Stripe Delete Credit Card Request.
 *
 * This is normally used to delete a credit card from an existing
 * customer.
 *
 * You can delete cards from a customer or recipient. If you delete a
 * card that is currently the default card on a customer or recipient,
 * the most recently added card will be used as the new default. If you
 * delete the last remaining card on a customer or recipient, the
 * default_card attribute on the card's owner will become null.
 *
 * Note that for cards belonging to customers, you may want to prevent
 * customers on paid subscriptions from deleting all cards on file so
 * that there is at least one default card for the next invoice payment
 * attempt.
 *
 * In deference to the previous incarnation of this gateway, where
 * all CreateCard requests added a new customer and the customer ID
 * was used as the card ID, if a cardReference is passed in but no
 * customerReference then we assume that the cardReference is in fact
 * a customerReference and delete the customer.  This might be
 * dangerous but it's the best way to ensure backwards compatibility.
 *
 * @link https://stripe.com/docs/api#delete_card
 */
class DeleteCardRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate('cardReference');

        return;
    }

    public function getHttpMethod()
    {
        return 'DELETE';
    }

    public function getEndpoint()
    {
        if ($this->getCustomerReference()) {
            // Delete a card from a customer
            return $this->endpoint.'/customers/'.
                $this->getCustomerReference().'/cards/'.
                $this->getCardReference();
        }
        // Delete the customer.  Oops?
        return $this->endpoint.'/customers/'.$this->getCardReference();
    }
}
