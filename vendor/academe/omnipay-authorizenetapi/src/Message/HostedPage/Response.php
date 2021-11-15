<?php

namespace Omnipay\AuthorizeNetApi\Message\HostedPage;

/**
 * The HostedPage Response contains the token needed to redirect to
 * the Hosted Page, or errors.
 */

use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\AuthorizeNetApi\Message\AbstractResponse;

class Response extends AbstractResponse implements RedirectResponseInterface
{
    /**
     * The live and test hosted payment page enpoints.
     */
    protected $endpointHostedPageSandbox = 'https://test.authorize.net/payment/payment';
    protected $endpointHostedPageLive = 'https://accept.authorize.net/payment/payment';

    /**
     * The test mode explicitly set by the originating request.
     * There are no clues in the response data to indicate whether it is a response
     * from a test or live request, so the request originator must pass that in.
     */
    protected $testMode = false;

    /**
     * Return the relevant endpoint.
     */
    public function getEndpoint()
    {
        if ($this->getTestMode()) {
            return $this->endpointHostedPageSandbox;
        } else {
            return $this->endpointHostedPageLive;
        }
    }

    /**
     * Not yet complete, as the hosted payment form still needs to be processed.
     */
    public function isSuccessful()
    {
        return false;
    }

    /**
     * @returns string The token used to invoke the remote form.
     */
    public function getToken()
    {
        return $this->getValue('token');
    }

    /**
     * @returns bool Is a redirect if the request is successful.
     */
    public function isRedirect()
    {
        return $this->responseIsSuccessful();
    }

    /**
     * @returns string The redirect method is a POST.
     */
    public function getRedirectMethod()
    {
        return 'POST';
    }

    /**
     * @returns array
     */
    public function getRedirectData()
    {
        return [
            'token' => $this->getToken()
        ];
    }

    /**
     * @returns array
     */
    public function getRedirectUrl()
    {
        return $this->getEndpoint();
    }

    /**
     * Gets the test mode of the original request.
     *
     * @return boolean
     */
    public function getTestMode()
    {
        return $this->testMode;
    }

    /**
     * Sets the test mode of the response.
     *
     * @param boolean $value True for test mode on.
     * @return AbstractRequest
     */
    public function setTestMode($value)
    {
        return $this->testMode = $value;
    }
}
