<?php

/**
 * Stripe Create Credit Card Request.
 */
namespace Omnipay\Stripe\Message;

/**
 * Stripe Create Credit Card Request.
 *
 * In the stripe system, creating a credit card requires passing
 * a customer ID.  The card is then added to the customer's account.
 * If the customer has no default card then the newly added
 * card becomes the customer's default card.
 *
 * This call can be used to create a new customer or add a card
 * to an existing customer.  If a customerReference is passed in then
 * a card is added to an existing customer.  If there is no
 * customerReference passed in then a new customer is created.  The
 * response in that case will then contain both a customer token
 * and a card token, and is essentially the same as CreateCustomerRequest
 *
 * ### Example
 *
 * This example assumes that you have already created a
 * customer, and that the customer reference is stored in $customer_id.
 * See CreateCustomerRequest for the first part of this transaction.
 *
 * <code>
 *   // Create a credit card object
 *   // This card can be used for testing.
 *   // The CreditCard object is also used for creating customers.
 *   $new_card = new CreditCard(array(
 *               'firstName'    => 'Example',
 *               'lastName'     => 'Customer',
 *               'number'       => '5555555555554444',
 *               'expiryMonth'  => '01',
 *               'expiryYear'   => '2020',
 *               'cvv'          => '456',
 *               'email'                 => 'customer@example.com',
 *               'billingAddress1'       => '1 Lower Creek Road',
 *               'billingCountry'        => 'AU',
 *               'billingCity'           => 'Upper Swan',
 *               'billingPostcode'       => '6999',
 *               'billingState'          => 'WA',
 *   ));
 *
 *   // Do a create card transaction on the gateway
 *   $response = $gateway->createCard(array(
 *       'card'              => $new_card,
 *       'customerReference' => $customer_id,
 *   ))->send();
 *   if ($response->isSuccessful()) {
 *       echo "Gateway createCard was successful.\n";
 *       // Find the card ID
 *       $card_id = $response->getCardReference();
 *       echo "Card ID = " . $card_id . "\n";
 *   }
 * </code>
 *
 * @see CreateCustomerRequest
 * @link https://stripe.com/docs/api#create_card
 */
class CreateCardRequest extends AbstractRequest
{
    public function getData()
    {
        $data = array();

        // Only set the description if we are creating a new customer.
        if (!$this->getCustomerReference()) {
            $data['description'] = $this->getDescription();
        }

        if ($this->getSource()) {
            $data['source'] = $this->getSource();
        } elseif ($this->getCardReference()) {
            $data['source'] = $this->getCardReference();
        } elseif ($this->getToken()) {
            $data['source'] = $this->getToken();
        } elseif ($this->getCard()) {
            $this->getCard()->validate();
            $data['source'] = $this->getCardData();
            // Only set the email address if we are creating a new customer.
            if (!$this->getCustomerReference()) {
                $data['email'] = $this->getCard()->getEmail();
            }
        } else {
            // one of token or card is required
            $this->validate('source');
        }

        return $data;
    }

    public function getEndpoint()
    {
        if ($this->getCustomerReference()) {
            // Create a new card on an existing customer
            return $this->endpoint.'/customers/'.
                $this->getCustomerReference().'/cards';
        }
        // Create a new customer and card
        return $this->endpoint.'/customers';
    }

    public function getCardData()
    {
        $data = parent::getCardData();
        unset($data['email']);

        return $data;
    }
}
