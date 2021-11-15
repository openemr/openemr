<?php

/**
 * Stripe Payment Intents Authorize Request.
 */
namespace Omnipay\Stripe\Message\PaymentIntents;

use Money\Formatter\DecimalMoneyFormatter;

/**
 * Stripe Payment Intents Authorize Request.
 *
 * An authorize request is similar to a purchase request but the
 * charge issues an authorization (or pre-authorization), and no money
 * is transferred.  The transaction will need to be captured later
 * in order to effect payment. Uncaptured charges expire in 7 days.
 *
 * A payment method is required. It can be set using the `paymentMethod`, `source`,
 * `cardReference` or `token` parameters.
 *
 * *Important*: Please note, that this gateway is a hybrid between credit card and
 * off-site gateway. It acts as a normal credit card gateway, unless the payment method
 * requires 3DS authentication, in which case it also performs a redirect to an
 * off-site authentication form.
 *
 * Example:
 *
 * <code>
 *   // Create a gateway for the Stripe Gateway
 *   // (routes to GatewayFactory::create)
 *   $gateway = Omnipay::create('Stripe\PaymentIntents');
 *
 *   // Initialise the gateway
 *   $gateway->initialize(array(
 *       'apiKey' => 'MyApiKey',
 *   ));
 *
 *   // Create a payment method using a credit card object.
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
 *   $paymentMethod = $gateway->createCard(['card' => $card])->send()->getCardReference();
 *
 *   // Code above can be skipped if you use Stripe.js and have a payment method reference
 *   // in the $paymentMethod variable already.
 *
 *   // For backwards compatibility, it's also possible to use card and source references
 *   // as well as tokens. However, a data dictionary containing card data  cannot
 *   // be used at this stage.
 *
 *   // Also note the setting of a return url. This is needed for cards that require
 *   // the 3DS 2.0 authentication. If you do not set a return url, payment with such
 *   // cards will fail.
 *
 *  // Do a purchase transaction on the gateway
 *  $paymentIntent = $gateway->authorize(array(
 *      'amount'                   => '10.00',
 *      'currency'                 => 'USD',
 *      'description'              => 'This is a test purchase transaction.',
 *      'paymentMethod'            => $paymentMethod,
 *      'returnUrl'                => $completePaymentUrl,
 *      'confirm'                  => true,
 *  ));
 *
 *  $paymentIntent = $paymentIntent->send();
 *
 *  // Alternatively, if you don't want to confirm it at one go for whatever reason, you
 *  // can use this code block below to confirm it. Otherwise, skip it.
 *  $paymentIntent = $gateway->confirm(array(
 *      'returnUrl'                => $completePaymentUrl
 *      'paymentIntentReference'   => $paymentIntent->getPaymentIntentReference(),
 *  ));
 *
 *  $response = $paymentIntent->send();
 *
 *  // If you set the confirm to true when performing the authorize transaction,
 *  // resume here.
 *
 *  // 3DS 2.0 time!
 *  if ($response->isRedirect()) {
 *      $response->redirect();
 *  } else if ($response->isSuccessful()) {
 *       echo "Authorize transaction was successful!\n";
 *       $sale_id = $response->getTransactionReference();
 *       echo "Transaction reference = " . $sale_id . "\n";
 *   }
 * </code>
 *
 * @see \Omnipay\Stripe\PaymentIntentsGateway
 * @see \Omnipay\Stripe\Message\PaymentIntents\CreatePaymentMethodRequest
 * @see \Omnipay\Stripe\Message\PaymentIntents\ConfirmPaymentIntentRequest
 * @link https://stripe.com/docs/api/payment_intents
 */
class AuthorizeRequest extends AbstractRequest
{
    /**
     * Set the confirm parameter.
     *
     * @param $value
     */
    public function setConfirm($value)
    {
        $this->setParameter('confirm', $value);
    }

    /**
     * Get the confirm parameter.
     *
     * @return mixed
     */
    public function getConfirm()
    {
        return $this->getParameter('confirm');
    }

    /**
     * @return mixed
     */
    public function getDestination()
    {
        return $this->getParameter('destination');
    }

    /**
     * @param string $value
     *
     * @return AbstractRequest provides a fluent interface.
     */
    public function setDestination($value)
    {
        return $this->setParameter('destination', $value);
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->getParameter('source');
    }

    /**
     * @param string $value
     *
     * @return AbstractRequest provides a fluent interface.
     */
    public function setSource($value)
    {
        return $this->setParameter('source', $value);
    }

    /**
     * Connect only
     *
     * @return mixed
     */
    public function getTransferGroup()
    {
        return $this->getParameter('transferGroup');
    }

    /**
     * @param string $value
     *
     * @return AbstractRequest provides a fluent interface.
     */
    public function setTransferGroup($value)
    {
        return $this->setParameter('transferGroup', $value);
    }

    /**
     * Connect only
     *
     * @return mixed
     */
    public function getOnBehalfOf()
    {
        return $this->getParameter('onBehalfOf');
    }

    /**
     * @param string $value
     *
     * @return AbstractRequest provides a fluent interface.
     */
    public function setOnBehalfOf($value)
    {
        return $this->setParameter('onBehalfOf', $value);
    }


    /**
     * @return string
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getApplicationFee()
    {
        $money = $this->getMoney('applicationFee');

        if ($money !== null) {
            $moneyFormatter = new DecimalMoneyFormatter($this->getCurrencies());

            return $moneyFormatter->format($money);
        }

        return '';
    }

    /**
     * Get the payment amount as an integer.
     *
     * @return integer
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getApplicationFeeInteger()
    {
        $money = $this->getMoney('applicationFee');

        if ($money !== null) {
            return (integer) $money->getAmount();
        }

        return 0;
    }

    /**
     * @param string $value
     *
     * @return AbstractRequest provides a fluent interface.
     */
    public function setApplicationFee($value)
    {
        return $this->setParameter('applicationFee', $value);
    }

    /**
     * @return mixed
     */
    public function getStatementDescriptor()
    {
        return $this->getParameter('statementDescriptor');
    }

    /**
     * @param string $value
     *
     * @return AbstractRequest provides a fluent interface.
     */
    public function setStatementDescriptor($value)
    {
        $value = str_replace(array('<', '>', '"', '\''), '', $value);

        return $this->setParameter('statementDescriptor', $value);
    }

    /**
     * @return mixed
     */
    public function getReceiptEmail()
    {
        return $this->getParameter('receipt_email');
    }

    /**
     * @param mixed $email
     * @return $this
     */
    public function setReceiptEmail($email)
    {
        $this->setParameter('receipt_email', $email);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        $this->validate('amount', 'currency');

        $data = array();

        $data['amount'] = $this->getAmountInteger();
        $data['currency'] = strtolower($this->getCurrency());
        $data['description'] = $this->getDescription();
        $data['metadata'] = $this->getMetadata();

        if ($this->getStatementDescriptor()) {
            $data['statement_descriptor'] = $this->getStatementDescriptor();
        }
        if ($this->getDestination()) {
            $data['transfer_data']['destination'] = $this->getDestination();
        }

        if ($this->getOnBehalfOf()) {
            $data['on_behalf_of'] = $this->getOnBehalfOf();
        }

        if ($this->getApplicationFee()) {
            $data['application_fee'] = $this->getApplicationFeeInteger();
        }

        if ($this->getTransferGroup()) {
            $data['transfer_group'] = $this->getTransferGroup();
        }

        if ($this->getReceiptEmail()) {
            $data['receipt_email'] = $this->getReceiptEmail();
        }

        if ($this->getPaymentMethod()) {
            $data['payment_method'] = $this->getPaymentMethod();
        } elseif ($this->getSource()) {
            $data['payment_method'] = $this->getSource();
        } elseif ($this->getCardReference()) {
            $data['payment_method'] = $this->getCardReference();
        } elseif ($this->getToken()) {
            $data['payment_method_data'] = [
                'type' => 'card',
                'card' => ['token' => $this->getToken()],
            ];
        } else {
            // one of cardReference, token, or card is required
            $this->validate('paymentMethod');
        }

        if ($this->getCustomerReference()) {
            $data['customer'] = $this->getCustomerReference();
        }

        $data['confirmation_method'] = 'manual';
        $data['capture_method'] = 'manual';

        $data['confirm'] = $this->getConfirm() ? 'true' : 'false';

        if ($this->getConfirm()) {
            $this->validate('returnUrl');
            $data['return_url'] = $this->getReturnUrl();
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getEndpoint()
    {
        return $this->endpoint.'/payment_intents';
    }

    /**
     * @inheritdoc
     */
    protected function createResponse($data, $headers = [])
    {
        return $this->response = new Response($this, $data, $headers);
    }
}
