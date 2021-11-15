<?php

/**
 * Stripe Create Customer Request.
 */
namespace Omnipay\Stripe\Message;

/**
 * Stripe Create Customer Request.
 *
 * Customer objects allow you to perform recurring charges and
 * track multiple charges that are associated with the same customer.
 * The API allows you to create, delete, and update your customers.
 * You can retrieve individual customers as well as a list of all of
 * your customers.
 *
 * ### Examples
 *
 * #### Create Customer from Email Address
 *
 * This is the recommended way to create a customer object.
 *
 * <code>
 * $response = $gateway->createCustomer(array(
 *     'description'       => 'Test Customer',
 *     'email'             => 'test123@example.com',
 * ))->send();
 * if ($response->isSuccessful()) {
 *     echo "Gateway createCustomer was successful.\n";
 *     // Find the card ID
 *     $customer_id = $response->getCustomerReference();
 *     echo "Customer ID = " . $customer_id . "\n";
 * } else {
 *     echo "Gateway createCustomer failed.\n";
 *     echo "Error message == " . $response->getMessage() . "\n";
 * }
 * </code>
 *
 * The $customer_id can now be used in a createCard() call.
 *
 * #### Create Customer using Card Object
 *
 * Historically, this library used a card object to create customers.
 * Although this is no longer the recommended path, it is still supported.
 * Using this approach, a customer object and a card object can be created
 * at the same time.
 *
 * <code>
 * // Create a credit card object
 * // This card can be used for testing.
 * // The CreditCard object is also used for creating customers.
 * $card = new CreditCard(array(
 *             'firstName'    => 'Example',
 *             'lastName'     => 'Customer',
 *             'number'       => '4242424242424242',
 *             'expiryMonth'  => '01',
 *             'expiryYear'   => '2020',
 *             'cvv'          => '123',
 *             'email'                 => 'customer@example.com',
 *             'billingAddress1'       => '1 Scrubby Creek Road',
 *             'billingCountry'        => 'AU',
 *             'billingCity'           => 'Scrubby Creek',
 *             'billingPostcode'       => '4999',
 *             'billingState'          => 'QLD',
 * ));
 *
 * // Do a create customer transaction on the gateway
 * $response = $gateway->createCustomer(array(
 *     'card'                     => $card,
 * ))->send();
 * if ($response->isSuccessful()) {
 *     echo "Gateway createCustomer was successful.\n";
 *     // Find the customer ID
 *     $customer_id = $response->getCustomerReference();
 *     echo "Customer ID = " . $customer_id . "\n";
 *     // Find the card ID
 *     $card_id = $response->getCardReference();
 *     echo "Card ID = " . $card_id . "\n";
 * }
 * </code>
 *
 * @link https://stripe.com/docs/api#customers
 */
class CreateCustomerRequest extends AbstractRequest
{
    /**
     * Get the customer's email address.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getParameter('email');
    }

    /**
     * Sets the customer's email address.
     *
     * @param string $value
     * @return CreateCustomerRequest provides a fluent interface.
     */
    public function setEmail($value)
    {
        return $this->setParameter('email', $value);
    }

    public function getName()
    {
        return $this->getParameter('name');
    }

    /**
     * Sets the customer's name.
     *
     * @param string $value
     * @return CreateCustomerRequest provides a fluent interface.
     */
    public function setName($value)
    {
        return $this->setParameter('name', $value);
    }

    public function getSource()
    {
        return $this->getParameter('source');
    }

    public function setSource($value)
    {
        $this->setParameter('source', $value);
    }

    public function getData()
    {
        $data = array();
        $data['description'] = $this->getDescription();

        if ($this->getToken()) {
            $data['card'] = $this->getToken();

            if ($this->getEmail()) {
                $data['email'] = $this->getEmail();
            }
        } elseif ($this->getCard()) {
            $data['card'] = $this->getCardData();
            $data['email'] = $this->getCard()->getEmail();
        } elseif ($this->getEmail()) {
            $data['email'] = $this->getEmail();
        }

        if ($this->getMetadata()) {
            $data['metadata'] = $this->getMetadata();
        }

        if ($this->getSource()) {
            $data['source'] = $this->getSource();
        }

        if ($this->getName()) {
            $data['name'] = $this->getName();
        }

        if ($this->getPaymentMethod()) {
            $data['payment_method'] = $this->getPaymentMethod();
        }

        return $data;
    }

    public function getEndpoint()
    {
        return $this->endpoint.'/customers';
    }
}
