<?php

namespace Omnipay\AuthorizeNetApi\Traits;

use Omnipay\Common\Exception\InvalidRequestException;

/**
 * Gateway setters and getters shared across all gateway types.
 */

trait HasGatewayParams
{
    /**
     * The application auth name.
     */
    public function setAuthName($value)
    {
        if (!is_string($value)) {
            throw new InvalidRequestException('Auth name must be a string.');
        }

        return $this->setParameter('authName', $value);
    }

    public function getAuthName()
    {
        return $this->getParameter('authName');
    }

    /**
     * The mobile device ID.
     */
    public function setMobileDeviceId($value)
    {
        if ($value !== null && ! is_string($value)) {
            throw new InvalidRequestException('Mobile device ID must be a string.');
        }

        return $this->setParameter('mobileDeviceId', $value);
    }

    public function getMobileDeviceId()
    {
        return $this->getParameter('mobileDeviceId');
    }

    /**
     * The ref ID.
     */
    public function setRefId($value)
    {
        if ($value !== null && ! is_string($value)) {
            throw new InvalidRequestException('Ref ID must be a string.');
        }

        return $this->setParameter('refId', $value);
    }

    public function getRefId()
    {
        return $this->getParameter('refId');
    }

    /**
     * The application auth transaction key.
     */
    public function setTransactionKey($value)
    {
        if (! is_string($value)) {
            throw new InvalidRequestException('Transaction Key must be a string.');
        }

        return $this->setParameter('transactionKey', $value);
    }

    public function getTransactionKey()
    {
        return $this->getParameter('transactionKey');
    }

    /**
     * The shared signature key.used to sign notifications sent by the
     * webhooks in the X-Anet-Signature HTTP header.
     * Only needed when receiving a notification.
     * Optional; the signature hash will only be checked if the signature
     * is supplied.
     */
    public function setSignatureKey($value)
    {
        if ($value !== null && ! is_string($value)) {
            throw new InvalidRequestException('Signature Key must be a string.');
        }

        return $this->setParameter('signatureKey', $value);
    }

    public function getSignatureKey()
    {
        return $this->getParameter('signatureKey');
    }

    /**
     * @param mixed $value cast to boolean when referenced
     */
    public function setDisableWebhookSignature($value)
    {
        return $this->setParameter('disableWebhookSignature', $value);
    }

    public function getDisableWebhookSignature()
    {
        return $this->getParameter('disableWebhookSignature');
    }
}
