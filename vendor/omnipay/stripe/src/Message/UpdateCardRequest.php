<?php

/**
 * Stripe Update Credit Card Request.
 */
namespace Omnipay\Stripe\Message;

/**
 * Stripe Update Credit Card Request.
 *
 * If you need to update only some card details, like the billing
 * address or expiration date, you can do so without having to re-enter
 * the full card details. Stripe also works directly with card networks
 * so that your customers can continue using your service without
 * interruption.
 *
 * When you update a card, Stripe will automatically validate the card.
 *
 * This requires both a customerReference and a cardReference.
 *
 * @link https://stripe.com/docs/api#update_card
 */
class UpdateCardRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate('cardReference');
        $this->validate('customerReference');

        if ($this->getCard()) {
            return $this->getCardData();
        } else {
            return array();
        }
    }

    public function getEndpoint()
    {
        return $this->endpoint.'/customers/'.$this->getCustomerReference().
            '/cards/'.$this->getCardReference();
    }

    /**
     * Get the card data.
     *
     * This request uses a slightly different format for card data to
     * the other requests and does not require the card data to be
     * complete in full (or valid).
     *
     * @return array
     */
    protected function getCardData()
    {
        $data = array();
        $card = $this->getCard();
        if (!empty($card)) {
            if ($card->getExpiryMonth()) {
                $data['exp_month'] = $card->getExpiryMonth();
            }
            if ($card->getExpiryYear()) {
                $data['exp_year'] = $card->getExpiryYear();
            }
            if ($card->getName()) {
                $data['name'] = $card->getName();
            }
            if ($card->getNumber()) {
                $data['number'] = $card->getNumber();
            }
            if ($card->getAddress1()) {
                $data['address_line1'] = $card->getAddress1();
            }
            if ($card->getAddress2()) {
                $data['address_line2'] = $card->getAddress2();
            }
            if ($card->getCity()) {
                $data['address_city'] = $card->getCity();
            }
            if ($card->getPostcode()) {
                $data['address_zip'] = $card->getPostcode();
            }
            if ($card->getState()) {
                $data['address_state'] = $card->getState();
            }
            if ($card->getCountry()) {
                $data['address_country'] = $card->getCountry();
            }
        }

        return $data;
    }
}
