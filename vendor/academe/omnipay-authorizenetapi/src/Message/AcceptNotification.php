<?php

namespace Omnipay\AuthorizeNetApi\Message;

/**
 * TODO: validate the server request signature, which will be in
 * the X-Anet-Signature header.
 */

use Omnipay\Common\Message\NotificationInterface;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Common\Message\RequestInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

use Omnipay\AuthorizeNetApi\Traits\HasGatewayParams;
use Academe\AuthorizeNet\ServerRequest\Notification;
use Academe\AuthorizeNet\Response\Model\TransactionResponse;

use Omnipay\Common\Exception\InvalidRequestException;

class AcceptNotification extends AbstractRequest implements NotificationInterface
{
    use HasGatewayParams;

    const SIGNATURE_HEADER_NAME = 'X-Anet-Signature';

    /**
     * The raw payload as a JSON string, used for signature validation.
     */
    protected $payload = '{}';

    /**
     * The payload data as a nested array.
     */
    protected $data;

    /**
     * The response data parsed into nested value objects.
     */
    protected $parsedData;

    /**
     * The signature sent with the server request.
     */
    protected $signature;

    public function __construct(ClientInterface $httpClient, HttpRequest $httpRequest)
    {
        // The request is a \Symfony\Component\HttpFoundation\Request object
        // and not (yet) a PSR-7 message.

        if ($httpRequest->getContentType() === 'json') {
            $this->payload = (string)$httpRequest->getContent();
        }

        $this->data = json_decode($this->payload, true);

        $this->setParsedData(new Notification($this->data));

        // Save the signature for validating later.
        // It cannot be validated until this object is initialised with parameters.

        $this->signature = $httpRequest->headers->get(
            static::SIGNATURE_HEADER_NAME
        );
    }

    /**
     * Set the data parsed into a nested value object.
     */
    public function setParsedData(Notification $value)
    {
        $this->parsedData = $value;
    }

    /**
     * Get the data parsed into a nested value object.
     */
    public function getParsedData()
    {
        return $this->parsedData;
    }

    /**
     * Get the raw data array for this message.
     * The raw data is from the JSON payload.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Gateway Reference
     *
     * @throws InvalidRequestException
     * @return string The gateway key for this transaction
     */
    public function getTransactionReference()
    {
        $this->assertSignature();

        if ($this->getEventTarget() === $this->getParsedData()::EVENT_TARGET_PAYMENT) {
            return $this->getPayload()->getTransId();
        }
    }

    /**
     * Was the transaction successful?
     *
     * @throws InvalidRequestException
     * @return string Transaction status, one of {@see STATUS_COMPLETED}, {@see #STATUS_PENDING},
     * or {@see #STATUS_FAILED}.
     */
    public function getTransactionStatus()
    {
        $this->assertSignature();

        $responseCode = $this->getResponseCode();

        if ($responseCode === TransactionResponse::RESPONSE_CODE_APPROVED) {
            return static::STATUS_COMPLETED;
        } elseif ($responseCode === TransactionResponse::RESPONSE_CODE_PENDING) {
            return static::STATUS_PENDIND;
        } elseif ($responseCode !== null) {
            return static::STATUS_FAILED;
        }
    }

    /**
     * Response Message
     *
     * @throws InvalidRequestException
     * @return string A response message from the payment gateway
     */
    public function getMessage()
    {
        $this->assertSignature();

        // There are actually no messages in the notifications.

        return '';
    }

    /**
     * There is nothing to send in order to response to this webhook.
     * The merchant site just needs to return a HTTP 200.
     *
     * @param  mixed $data The data to send
     * @return ResponseInterface
     */
    public function sendData($data)
    {
        return $this;
    }

    /**
     * The main target of the notificaiton: payment or customer.
     */
    public function getEventTarget()
    {
        return $this->getParsedData()->getEventTarget();
    }

    /**
     * The sub-target of the notificaiton.
     */
    public function getEventSubtarget()
    {
        return $this->getParsedData()->getEventSubtarget();
    }

    /**
     * The action against the target of the notificaito.
     */
    public function getEventAction()
    {
        return $this->getParsedData()->getEventAction();
    }

    /**
     * The UUID identifying this specific notification.
     */
    public function getNotificationId()
    {
        return $this->getParsedData()->getNotificationId();
    }

    /**
     * The UUID identifying the webhook being fired.
     */
    public function getWebhookId()
    {
        return $this->getParsedData()->getWebhookId();
    }

    /**
     * Optional notification payload.
     */
    public function getPayload()
    {
        return $this->getParsedData()->getPayload();
    }

    /**
     * @return int Raw response code
     */
    public function getResponseCode()
    {
        if ($this->getEventTarget() === $this->getParsedData()::EVENT_TARGET_PAYMENT) {
            return $this->getPayload()->getResponseCode();
        }
    }

    /**
     * @return string Raw response code
     */
    public function getAuthCode()
    {
        if ($this->getEventTarget() === $this->getParsedData()::EVENT_TARGET_PAYMENT) {
            return $this->getPayload()->getAuthCode();
        }
    }

    /**
     * @return string Raw AVS response code
     */
    public function getAvsResponse()
    {
        if ($this->getEventTarget() === $this->getParsedData()::EVENT_TARGET_PAYMENT) {
            return $this->getPayload()->getAvsResponse();
        }
    }

    /**
     * @return float authAmount, no currency, no stated units
     */
    public function getAuthAmount()
    {
        if ($this->getEventTarget() === $this->getParsedData()::EVENT_TARGET_PAYMENT) {
            return $this->getPayload()->getAuthAmount();
        }
    }

    /**
     * Assert that the signature of the webhook is valid.
     * Will honour the flag to skip this check.
     *
     * @throws InvalidRequestException
     */
    public function assertSignature()
    {
        // Signature checking can be explicitly disabled.

        if ((bool)$this->getDisableWebhookSignature()) {
            return;
        }

        if (! $this->isSignatureValid()) {
            throw new InvalidRequestException('Invalid or missing signature');
        }
    }

    /**
     * Check whether the signature is valid.
     *
     * @return bool true = valid; false = invalid.
     */
    public function isSignatureValid()
    {
        // A missing or malformed signature is invalid.

        if ($this->signature === null || strpos($this->signature, 'sha512=') !== 0) {
            return false;
        }

        // A missing signature key is also invalid.

        if (($signatureKey = $this->getSignatureKey()) === null) {
            return false;
        }

        // Check the signature.

        list ($algorithm, $signatureString) = explode('=', $this->signature, 2);

        $hashedPayload = strtoupper(
            hash_hmac($algorithm, $this->payload, $signatureKey)
        );

        return $hashedPayload === $signatureString;
    }
}
