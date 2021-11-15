<?php

/**
 * Stripe Abstract Request.
 */

namespace Omnipay\Stripe\Message;

use Money\Currency;
use Money\Money;
use Money\Number;
use Money\Parser\DecimalMoneyParser;
use Omnipay\Common\Exception\InvalidRequestException;

/**
 * Stripe Abstract Request.
 *
 * This is the parent class for all Stripe requests.
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
 * @see \Omnipay\Stripe\Gateway
 * @link https://stripe.com/docs/api
 */
abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    /**
     * Live or Test Endpoint URL.
     *
     * @var string URL
     */
    protected $endpoint = 'https://api.stripe.com/v1';

    /**
     * Get the gateway API Key.
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
     * @return AbstractRequest provides a fluent interface.
     */
    public function setApiKey($value)
    {
        return $this->setParameter('apiKey', $value);
    }

    /**
     * @deprecated
     */
    public function getCardToken()
    {
        return $this->getCardReference();
    }

    /**
     * @deprecated
     */
    public function setCardToken($value)
    {
        return $this->setCardReference($value);
    }

    /**
     * Get the customer reference.
     *
     * @return string
     */
    public function getCustomerReference()
    {
        return $this->getParameter('customerReference');
    }

    /**
     * Set the customer reference.
     *
     * Used when calling CreateCard on an existing customer.  If this
     * parameter is not set then a new customer is created.
     *
     * @return AbstractRequest provides a fluent interface.
     */
    public function setCustomerReference($value)
    {
        return $this->setParameter('customerReference', $value);
    }

    public function getMetadata()
    {
        return $this->getParameter('metadata');
    }

    public function setMetadata($value)
    {
        return $this->setParameter('metadata', $value);
    }

    /**
     * Connect only
     *
     * @return mixed
     */
    public function getConnectedStripeAccountHeader()
    {
        return $this->getParameter('connectedStripeAccount');
    }

    /**
     * @param string $value
     *
     * @return AbstractRequest
     */
    public function setConnectedStripeAccountHeader($value)
    {
        return $this->setParameter('connectedStripeAccount', $value);
    }

    /**
     * Connect only
     *
     * @return mixed
     */
    public function getIdempotencyKeyHeader()
    {
        return $this->getParameter('idempotencyKey');
    }

    /**
     *
     * @return string
     */
    public function getStripeVersion()
    {
        return $this->getParameter('stripeVersion');
    }

    /**
     * @param string $value
     *
     * @return AbstractRequest
     */
    public function setStripeVersion($value)
    {
        return $this->setParameter('stripeVersion', $value);
    }

    /**
     * @param string $value
     *
     * @return AbstractRequest
     */
    public function setIdempotencyKeyHeader($value)
    {
        return $this->setParameter('idempotencyKey', $value);
    }

    abstract public function getEndpoint();

    /**
     * Get HTTP Method.
     *
     * This is nearly always POST but can be over-ridden in sub classes.
     *
     * @return string
     */
    public function getHttpMethod()
    {
        return 'POST';
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        $headers = array();

        if ($this->getConnectedStripeAccountHeader()) {
            $headers['Stripe-Account'] = $this->getConnectedStripeAccountHeader();
        }

        if ($this->getIdempotencyKeyHeader()) {
            $headers['Idempotency-Key'] = $this->getIdempotencyKeyHeader();
        }

        if ($this->getStripeVersion()) {
            $headers['Stripe-Version'] = $this->getStripeVersion();
        }

        return $headers;
    }

    /**
     * {@inheritdoc}
     */
    public function sendData($data)
    {
        $headers = array_merge(
            $this->getHeaders(),
            array('Authorization' => 'Basic ' . base64_encode($this->getApiKey() . ':'))
        );

        $body = $data ? http_build_query($data, '', '&') : null;
        $httpResponse = $this->httpClient->request($this->getHttpMethod(), $this->getEndpoint(), $headers, $body);

        return $this->createResponse($httpResponse->getBody()->getContents(), $httpResponse->getHeaders());
    }


    protected function createResponse($data, $headers = [])
    {
        return $this->response = new Response($this, $data, $headers);
    }
    
    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->getParameter('source');
    }

    /**
     * @param $value
     *
     * @return AbstractRequest provides a fluent interface.
     */
    public function setSource($value)
    {
        return $this->setParameter('source', $value);
    }

    /**
     * @param string $parameterName
     *
     * @return null|Money
     * @throws InvalidRequestException
     */
    public function getMoney($parameterName = 'amount')
    {
        $amount = $this->getParameter($parameterName);

        if ($amount instanceof Money) {
            return $amount;
        }

        if ($amount !== null) {
            $moneyParser = new DecimalMoneyParser($this->getCurrencies());
            $currencyCode = $this->getCurrency() ?: 'USD';
            $currency = new Currency($currencyCode);

            $number = Number::fromString($amount);

            // Check for rounding that may occur if too many significant decimal digits are supplied.
            $decimal_count = strlen($number->getFractionalPart());
            $subunit = $this->getCurrencies()->subunitFor($currency);
            if ($decimal_count > $subunit) {
                throw new InvalidRequestException('Amount precision is too high for currency.');
            }

            $money = $moneyParser->parse((string) $number, $currency->getCode());

            // Check for a negative amount.
            if (!$this->negativeAmountAllowed && $money->isNegative()) {
                throw new InvalidRequestException('A negative amount is not allowed.');
            }

            // Check for a zero amount.
            if (!$this->zeroAmountAllowed && $money->isZero()) {
                throw new InvalidRequestException('A zero amount is not allowed.');
            }

            return $money;
        }
    }

    /**
     * Get the card data.
     *
     * Because the stripe gateway uses a common format for passing
     * card data to the API, this function can be called to get the
     * data from the associated card object in the format that the
     * API requires.
     *
     * @return array
     */
    protected function getCardData()
    {
        $card = $this->getCard();
        $card->validate();

        $data = array();
        $data['object'] = 'card';
        $data['number'] = $card->getNumber();
        $data['exp_month'] = $card->getExpiryMonth();
        $data['exp_year'] = $card->getExpiryYear();
        if ($card->getCvv()) {
            $data['cvc'] = $card->getCvv();
        }
        $data['name'] = $card->getName();
        $data['address_line1'] = $card->getAddress1();
        $data['address_line2'] = $card->getAddress2();
        $data['address_city'] = $card->getCity();
        $data['address_zip'] = $card->getPostcode();
        $data['address_state'] = $card->getState();
        $data['address_country'] = $card->getCountry();
        $data['email'] = $card->getEmail();

        return $data;
    }
}
