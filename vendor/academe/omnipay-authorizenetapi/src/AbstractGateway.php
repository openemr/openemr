<?php

namespace Omnipay\AuthorizeNetApi;

/**
 *
 */

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\AbstractGateway as OmnipayAbstractGateway;
use Omnipay\AuthorizeNetApi\Traits\HasGatewayParams;

abstract class AbstractGateway extends OmnipayAbstractGateway
{
    use HasGatewayParams;

    /**
     *
     */
    public function getDefaultParameters()
    {
        return array(
            // Required.
            // The name assigned for th application.
            'authName' => null,
            // Required.
            // The access token assigned to this application.
            'transactionKey' => null,
            // Optional.
            // Either mobileDeviceId or refId can be provided.
            'mobileDeviceId' => null,
            'refId' => null,
            // True to run against the sandbox.
            'testMode' => false,
            // The shared key used to sign notifications.
            'signatureKey' => null,
            // Set to disable the webhook signature assertions.
            'disableWebhookSignature' => false,
        );
    }

    /**
     * The capture transaction.
     */
    public function capture(array $parameters = [])
    {
        return $this->createRequest(
            \Omnipay\AuthorizeNetApi\Message\CaptureRequest::class,
            $parameters
        );
    }

    /**
     * Fetch a transaction.
     */
    public function fetchTransaction(array $parameters = [])
    {
        return $this->createRequest(
            \Omnipay\AuthorizeNetApi\Message\FetchTransactionRequest::class,
            $parameters
        );
    }

    /**
     * Handle notifcation server requests (webhooks).
     */
    public function acceptNotification(array $parameters = [])
    {
        return $this->createRequest(
            \Omnipay\AuthorizeNetApi\Message\AcceptNotification::class,
            $parameters
        );
    }
}
