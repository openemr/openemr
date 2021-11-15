<?php

namespace Omnipay\AuthorizeNetApi\Message;

/**
 * TODO: Soem of these methods are relevant only to a transaction, so could
 * be moved to an intermendiate transaction request abstract.
 */

use Omnipay\Common\Message\AbstractRequest as OmnipayAbstractRequest;

use Academe\AuthorizeNet\Auth\MerchantAuthentication;
use Academe\AuthorizeNet\TransactionRequestInterface;
use Academe\AuthorizeNet\Request\CreateTransaction;
use Academe\AuthorizeNet\Request\AbstractRequest as ApiAbstractRequest;
use Omnipay\AuthorizeNetApi\Traits\HasGatewayParams;

abstract class AbstractRequest extends OmnipayAbstractRequest
{
    use HasGatewayParams;

    /**
     * The live and test gateway endpoints.
     */
    protected $endpointSandbox = 'https://apitest.authorize.net/xml/v1/request.api';
    protected $endpointLive = 'https://api.authorize.net/xml/v1/request.api';

    /**
     * Get the authentication credentials object.
     */
    public function getAuth()
    {
         return new MerchantAuthentication($this->getAuthName(), $this->getTransactionKey());
    }

    /**
     * Return the relevant endpoint.
     */
    public function getEndpoint()
    {
        if ($this->getTestMode()) {
            return $this->endpointSandbox;
        } else {
            return $this->endpointLive;
        }
    }

    /**
     * Send a HTTP request to the gateway.
     *
     * @param array|\JsonSerializable $data The body data to send to the gateway
     * @return GuzzleHttp\Psr7\Response
     */
    protected function sendRequest($data, $method = 'POST')
    {
        $response = $this->httpClient->request(
            $method,
            $this->getEndpoint(),
            array(
                'Content-Type' => 'application/json',
            ),
            json_encode($data)
        );

        return $response;
    }

    /**
     * Strip a Byte Order Mark (BOM) from the start of a string.
     *
     * @param string $string A string with a potential BOM prefix.
     * @return string The string with the BOM removed.
     */
    public function removeBOM($string)
    {
        return preg_replace('/^[\x00-\x1F\x80-\xFF]{1,3}/', '', $string);
    }

    /**
     * Send a transaction and return the decoded data.
     * Any movement of funds is normnally done by creating a transaction
     * to perform the action. Requests that involve profiles, fetching
     * information, won't involve transactions.
     *
     * @param TransactionRequestInterface $transaction The transaction object
     * @return array The decoded data returned by the gateway.
     */
    public function sendTransaction(TransactionRequestInterface $transaction)
    {
        // Wrap the transaction detail into a request.
        $request = $this->wrapTransaction($this->getAuth(), $transaction);

        // The merchant site ID.
        $request = $request->withRefId($this->getTransactionId());

        return $this->sendMessage($request);
    }

    /**
     * Send a messgae and return the resulting decoded response data.
     *
     * TODO: handle unexpected results and HTTP return codes.
     *
     * @param ApiAbstractRequest $message The hydrated request message
     *      (from the academe/authorizenet-objects package)
     */
    protected function sendMessage(ApiAbstractRequest $message)
    {
        // Send the request to the gateway.
        $response = $this->sendRequest($message);

        // The caller will know what object to put this data into.
        $body = (string)($response->getBody());

        // The body will be JSON, but *may* have a Byte Order Mark (BOM) prefix.
        // Remove the BOM.
        $body = $this->removeBOM($body);

        // Now decode the JSON body.
        $data = json_decode($body, true);

        // Return a data response.
        return $data;
    }

    /**
     * Wrap the transaction detail into a full request for an action on
     * the transaction.
     */
    protected function wrapTransaction($auth, $transaction)
    {
        return new CreateTransaction($auth, $transaction);
    }

    /**
     * @param string Merchant-defined invoice number associated with the order.
     * @return $this
     */
    public function setInvoiceNumber($value)
    {
        return $this->setParameter('invoiceNumber', $value);
    }

    /**
     * @return string
     */
    public function getInvoiceNumber()
    {
        return $this->getParameter('invoiceNumber');
    }

    /**
     * @param string Merchant-defined invoice number associated with the order.
     * @return $this
     */
    public function setTerminalNumber($value)
    {
        return $this->setParameter('terminalNumber', $value);
    }

    /**
     * @return string
     */
    public function getTerminalNumber()
    {
        return $this->getParameter('terminalNumber');
    }

    /**
     * authenticationIndicator and authenticationValue are used as a pair.
     * @param string 3D Secure indicator.
     * @return $this
     */
    public function setAuthenticationIndicator($value)
    {
        return $this->setParameter('authenticationIndicator', $value);
    }

    /**
     * @return string
     */
    public function getAuthenticationIndicator()
    {
        return $this->getParameter('authenticationIndicator');
    }

    /**
     * authenticationIndicator and authenticationValue are used as a pair.
     * @param string 3D Secure value.
     * @return $this
     */
    public function setAuthenticationValue($value)
    {
        return $this->setParameter('authenticationValue', $value);
    }

    /**
     * @return string
     */
    public function getAuthenticationValue()
    {
        return $this->getParameter('authenticationValue');
    }

    /**
     * @param string customer ID.
     * @return $this
     */
    public function setCustomerId($value)
    {
        return $this->setParameter('customerId', $value);
    }

    /**
     * @return string
     */
    public function getCustomerId()
    {
        return $this->getParameter('customerId');
    }

    /**
     * Valid values are one of
     * \Academe\AuthorizeNet\Request\Model\Customer::CUSTOMER_TYPE_*
     * @param string customer type
     * @return $this
     */
    public function setCustomerType($value)
    {
        return $this->setParameter('customerType', $value);
    }

    /**
     * @return string
     */
    public function getCustomerType()
    {
        return $this->getParameter('customerType');
    }

    /**
     * @param string Customer Drivers License.
     * @return $this
     */
    public function setCustomerDriversLicense($value)
    {
        return $this->setParameter('customerDriversLicense', $value);
    }

    /**
     * @return string
     */
    public function getCustomerDriversLicense()
    {
        return $this->getParameter('customerDriversLicense');
    }

    /**
     * @param string Customer Tax ID.
     * @return $this
     */
    public function setCustomerTaxId($value)
    {
        return $this->setParameter('customerTaxId', $value);
    }

    /**
     * @return string
     */
    public function getCustomerTaxId()
    {
        return $this->getParameter('customerTaxId');
    }
}
