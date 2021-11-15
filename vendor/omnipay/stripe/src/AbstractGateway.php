<?php

/**
 * Stripe base gateway.
 */
namespace Omnipay\Stripe;

use Omnipay\Common\AbstractGateway as AbstractOmnipayGateway;

/**
 * Stripe Gateway.
 *
 * Example:
 *
 * <code>
 *   // Create a gateway for the Stripe Gateway
 *   // (routes to GatewayFactory::create)
 *   $gateway = Omnipay::create('Stripe');
 *
 *   // Initialise the gateway
 *   $gateway->initialize(array(
 *       'apiKey' => 'MyApiKey',
 *   ));
 *
 *   // Create a credit card object
 *   // This card can be used for testing.
 *   $card = new CreditCard(array(
 *               'firstName'    => 'Example',
 *               'lastName'     => 'Customer',
 *               'number'       => '4242424242424242',
 *               'expiryMonth'  => '01',
 *               'expiryYear'   => '2020',
 *               'cvv'          => '123',
 *               'email'                 => 'customer@example.com',
 *               'billingAddress1'       => '1 Scrubby Creek Road',
 *               'billingCountry'        => 'AU',
 *               'billingCity'           => 'Scrubby Creek',
 *               'billingPostcode'       => '4999',
 *               'billingState'          => 'QLD',
 *   ));
 *
 *   // Do a purchase transaction on the gateway
 *   $transaction = $gateway->purchase(array(
 *       'amount'                   => '10.00',
 *       'currency'                 => 'USD',
 *       'card'                     => $card,
 *   ));
 *   $response = $transaction->send();
 *   if ($response->isSuccessful()) {
 *       echo "Purchase transaction was successful!\n";
 *       $sale_id = $response->getTransactionReference();
 *       echo "Transaction reference = " . $sale_id . "\n";
 *
 *       $balance_transaction_id = $response->getBalanceTransactionReference();
 *       echo "Balance Transaction reference = " . $balance_transaction_id . "\n";
 *   }
 * </code>
 *
 * Test modes:
 *
 * Stripe accounts have test-mode API keys as well as live-mode
 * API keys. These keys can be active at the same time. Data
 * created with test-mode credentials will never hit the credit
 * card networks and will never cost anyone money.
 *
 * Unlike some gateways, there is no test mode endpoint separate
 * to the live mode endpoint, the Stripe API endpoint is the same
 * for test and for live.
 *
 * Setting the testMode flag on this gateway has no effect.  To
 * use test mode just use your test mode API key.
 *
 * You can use any of the cards listed at https://stripe.com/docs/testing
 * for testing.
 *
 * Authentication:
 *
 * Authentication is by means of a single secret API key set as
 * the apiKey parameter when creating the gateway object.
 *
 * @see \Omnipay\Common\AbstractGateway
 * @see \Omnipay\Stripe\Message\AbstractRequest
 *
 * @link https://stripe.com/docs/api
 *
 * @method \Omnipay\Common\Message\RequestInterface completeAuthorize(array $options = array())
 * @method \Omnipay\Common\Message\RequestInterface completePurchase(array $options = array())
 */
abstract class AbstractGateway extends AbstractOmnipayGateway
{
    const BILLING_PLAN_FREQUENCY_DAY    = 'day';
    const BILLING_PLAN_FREQUENCY_WEEK   = 'week';
    const BILLING_PLAN_FREQUENCY_MONTH  = 'month';
    const BILLING_PLAN_FREQUENCY_YEAR   = 'year';

    /**
     * @inheritdoc
     */
    abstract public function getName();

    /**
     * Get the gateway parameters.
     *
     * @return array
     */
    public function getDefaultParameters()
    {
        return array(
            'apiKey' => '',
        );
    }

    /**
     * Get the gateway API Key.
     *
     * Authentication is by means of a single secret API key set as
     * the apiKey parameter when creating the gateway object.
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->getParameter('apiKey');
    }

    /**
     * Set the gateway API Key.
     *
     * Authentication is by means of a single secret API key set as
     * the apiKey parameter when creating the gateway object.
     *
     * Stripe accounts have test-mode API keys as well as live-mode
     * API keys. These keys can be active at the same time. Data
     * created with test-mode credentials will never hit the credit
     * card networks and will never cost anyone money.
     *
     * Unlike some gateways, there is no test mode endpoint separate
     * to the live mode endpoint, the Stripe API endpoint is the same
     * for test and for live.
     *
     * Setting the testMode flag on this gateway has no effect.  To
     * use test mode just use your test mode API key.
     *
     * @param string $value
     *
     * @return Gateway provides a fluent interface.
     */
    public function setApiKey($value)
    {
        return $this->setParameter('apiKey', $value);
    }

    /**
     * Authorize Request.
     *
     * An Authorize request is similar to a purchase request but the
     * charge issues an authorization (or pre-authorization), and no money
     * is transferred.  The transaction will need to be captured later
     * in order to effect payment. Uncaptured charges expire in 7 days.
     *
     * Either a customerReference or a card is required.  If a customerReference
     * is passed in then the cardReference must be the reference of a card
     * assigned to the customer.  Otherwise, if you do not pass a customer ID,
     * the card you provide must either be a token, like the ones returned by
     * Stripe.js, or a dictionary containing a user's credit card details.
     *
     * IN OTHER WORDS: You cannot just pass a card reference into this request,
     * you must also provide a customer reference if you want to use a stored
     * card.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Stripe\Message\AbstractRequest
     */
    abstract public function authorize(array $parameters = array());

    /**
     * Capture Request.
     *
     * Use this request to capture and process a previously created authorization.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Stripe\Message\AbstractRequest
     */
    abstract public function capture(array $parameters = array());

    /**
     * Purchase request.
     *
     * To charge a credit card, you create a new charge object. If your API key
     * is in test mode, the supplied card won't actually be charged, though
     * everything else will occur as if in live mode. (Stripe assumes that the
     * charge would have completed successfully).
     *
     * Either a customerReference or a card is required.  If a customerReference
     * is passed in then the cardReference must be the reference of a card
     * assigned to the customer.  Otherwise, if you do not pass a customer ID,
     * the card you provide must either be a token, like the ones returned by
     * Stripe.js, or a dictionary containing a user's credit card details.
     *
     * IN OTHER WORDS: You cannot just pass a card reference into this request,
     * you must also provide a customer reference if you want to use a stored
     * card.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Stripe\Message\AbstractRequest
     */
    abstract public function purchase(array $parameters = array());

    /**
     * Refund Request.
     *
     * When you create a new refund, you must specify a
     * charge to create it on.
     *
     * Creating a new refund will refund a charge that has
     * previously been created but not yet refunded. Funds will
     * be refunded to the credit or debit card that was originally
     * charged. The fees you were originally charged are also
     * refunded.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Stripe\Message\RefundRequest
     */
    public function refund(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\Stripe\Message\RefundRequest', $parameters);
    }

    /**
     * Void a transaction.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Stripe\Message\VoidRequest
     */
    public function void(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\Stripe\Message\VoidRequest', $parameters);
    }

    /**
     * Fetch a transaction.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Stripe\Message\FetchTransactionRequest
     */
    public function fetchTransaction(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\FetchTransactionRequest', $parameters);
    }

    /**
     * @param array $parameters
     * @return \Omnipay\Stripe\Message\FetchBalanceTransactionRequest
     */
    public function fetchBalanceTransaction(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\FetchBalanceTransactionRequest', $parameters);
    }

    //
    // Transfers
    // @link https://stripe.com/docs/api#transfers
    //

    /**
     * Transfer Request.
     *
     * To send funds from your Stripe account to a connected account, you create
     * a new transfer object. Your Stripe balance must be able to cover the
     * transfer amount, or you'll receive an "Insufficient Funds" error.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function transfer(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\Transfers\CreateTransferRequest', $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function fetchTransfer(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\Transfers\FetchTransferRequest', $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function updateTransfer(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\Transfers\UpdateTransferRequest', $parameters);
    }

    /**
     * List Transfers
     *
     * @param array $parameters
     *
     * @return \Omnipay\Common\Message\AbstractRequest|\Omnipay\Stripe\Message\Transfers\ListTransfersRequest
     */
    public function listTransfers(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\Transfers\ListTransfersRequest', $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function reverseTransfer(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\Transfers\CreateTransferReversalRequest', $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function fetchTransferReversal(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\Transfers\FetchTransferReversalRequest', $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function updateTransferReversal(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\Transfers\UpdateTransferReversalRequest', $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function listTransferReversals(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\Transfers\ListTransferReversalsRequest', $parameters);
    }

    //
    // Cards
    // @link https://stripe.com/docs/api#cards
    //

    /**
     * Create Card.
     *
     * This call can be used to create a new customer or add a card
     * to an existing customer.  If a customerReference is passed in then
     * a card is added to an existing customer.  If there is no
     * customerReference passed in then a new customer is created.  The
     * response in that case will then contain both a customer token
     * and a card token, and is essentially the same as CreateCustomerRequest
     *
     * @param array $parameters
     *
     * @return \Omnipay\Stripe\Message\AbstractRequest
     */
    public function createCard(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\CreateCardRequest', $parameters);
    }

    /**
     * Update Card.
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
     *
     * @param array $parameters
     *
     * @return \Omnipay\Stripe\Message\AbstractRequest
     */
    public function updateCard(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\UpdateCardRequest', $parameters);
    }

    /**
     * Delete a card.
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
     * @param array $parameters
     *
     * @return \Omnipay\Stripe\Message\AbstractRequest
     */
    public function deleteCard(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\DeleteCardRequest', $parameters);
    }

    //
    // Customers
    // link: https://stripe.com/docs/api#customers
    //

    /**
     * Create Customer.
     *
     * Customer objects allow you to perform recurring charges and
     * track multiple charges that are associated with the same customer.
     * The API allows you to create, delete, and update your customers.
     * You can retrieve individual customers as well as a list of all of
     * your customers.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Stripe\Message\CreateCustomerRequest
     */
    public function createCustomer(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\CreateCustomerRequest', $parameters);
    }

    /**
     * Fetch Customer.
     *
     * Fetches customer by customer reference.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Stripe\Message\FetchCustomerRequest
     */
    public function fetchCustomer(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\FetchCustomerRequest', $parameters);
    }

    /**
     * Update Customer.
     *
     * This request updates the specified customer by setting the values
     * of the parameters passed. Any parameters not provided will be left
     * unchanged. For example, if you pass the card parameter, that becomes
     * the customer's active card to be used for all charges in the future,
     * and the customer email address is updated to the email address
     * on the card. When you update a customer to a new valid card: for
     * each of the customer's current subscriptions, if the subscription
     * is in the `past_due` state, then the latest unpaid, unclosed
     * invoice for the subscription will be retried (note that this retry
     * will not count as an automatic retry, and will not affect the next
     * regularly scheduled payment for the invoice). (Note also that no
     * invoices pertaining to subscriptions in the `unpaid` state, or
     * invoices pertaining to canceled subscriptions, will be retried as
     * a result of updating the customer's card.)
     *
     * This request accepts mostly the same arguments as the customer
     * creation call.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Stripe\Message\CreateCustomerRequest
     */
    public function updateCustomer(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\UpdateCustomerRequest', $parameters);
    }

    /**
     * Delete a customer.
     *
     * Permanently deletes a customer. It cannot be undone. Also immediately
     * cancels any active subscriptions on the customer.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Stripe\Message\DeleteCustomerRequest
     */
    public function deleteCustomer(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\DeleteCustomerRequest', $parameters);
    }

    /**
     * Create Plan
     *
     * @param array $parameters
     * @return \Omnipay\Stripe\Message\CreatePlanRequest
     */
    public function createPlan(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\CreatePlanRequest', $parameters);
    }

    /**
     * Fetch Plan
     *
     * @param array $parameters
     * @return \Omnipay\Stripe\Message\FetchPlanRequest
     */
    public function fetchPlan(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\FetchPlanRequest', $parameters);
    }

    /**
     * Delete Plan
     *
     * @param array $parameters
     * @return \Omnipay\Stripe\Message\DeletePlanRequest
     */
    public function deletePlan(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\DeletePlanRequest', $parameters);
    }

    /**
     * List Plans
     *
     * @param array $parameters
     * @return \Omnipay\Stripe\Message\ListPlansRequest
     */
    public function listPlans(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\ListPlansRequest', $parameters);
    }

    /**
     * Create Subscription
     *
     * @param array $parameters
     * @return \Omnipay\Stripe\Message\CreateSubscriptionRequest
     */
    public function createSubscription(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\CreateSubscriptionRequest', $parameters);
    }

    /**
     * Fetch Subscription
     *
     * @param array $parameters
     * @return \Omnipay\Stripe\Message\FetchSubscriptionRequest
     */
    public function fetchSubscription(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\FetchSubscriptionRequest', $parameters);
    }

    /**
     * Update Subscription
     *
     * @param array $parameters
     * @return \Omnipay\Stripe\Message\UpdateSubscriptionRequest
     */
    public function updateSubscription(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\UpdateSubscriptionRequest', $parameters);
    }

    /**
     * Cancel Subscription
     *
     * @param array $parameters
     * @return \Omnipay\Stripe\Message\CancelSubscriptionRequest
     */
    public function cancelSubscription(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\CancelSubscriptionRequest', $parameters);
    }

    /**
     * Fetch Event
     *
     * @param array $parameters
     * @return \Omnipay\Stripe\Message\FetchEventRequest
     */
    public function fetchEvent(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\FetchEventRequest', $parameters);
    }

    /**
     * Fetch Invoice Lines
     *
     * @param array $parameters
     * @return \Omnipay\Stripe\Message\FetchInvoiceLinesRequest
     */
    public function fetchInvoiceLines(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\FetchInvoiceLinesRequest', $parameters);
    }

    /**
     * Fetch Invoice
     *
     * @param array $parameters
     * @return \Omnipay\Stripe\Message\FetchInvoiceRequest
     */
    public function fetchInvoice(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\FetchInvoiceRequest', $parameters);
    }

    /**
     * List Invoices
     *
     * @param array $parameters
     * @return \Omnipay\Stripe\Message\ListInvoicesRequest
     */
    public function listInvoices(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\ListInvoicesRequest', $parameters);
    }

    /**
     * Create Invoice Item
     *
     * @param array $parameters
     * @return \Omnipay\Stripe\Message\CreateInvoiceItemRequest
     */
    public function createInvoiceItem(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\CreateInvoiceItemRequest', $parameters);
    }

    /**
     * Fetch Invoice Item
     *
     * @param array $parameters
     * @return \Omnipay\Stripe\Message\FetchInvoiceItemRequest
     */
    public function fetchInvoiceItem(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\FetchInvoiceItemRequest', $parameters);
    }

    /**
     * Delete Invoice Item
     *
     * @param array $parameters
     * @return \Omnipay\Stripe\Message\DeleteInvoiceItemRequest
     */
    public function deleteInvoiceItem(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Stripe\Message\DeleteInvoiceItemRequest', $parameters);
    }
}
