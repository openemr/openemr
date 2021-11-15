<?php

namespace Academe\AuthorizeNet\ServerRequest\Payload;

/**
 * The payment notification payload.
 */

use Academe\AuthorizeNet\Response\HasDataTrait;
use Academe\AuthorizeNet\AbstractModel;
use Academe\AuthorizeNet\ServerRequest\AbstractPayload;
use Academe\AuthorizeNet\Response\Model\TransactionResponse;

class Payment extends AbstractPayload
{
    protected $responseCode;
    protected $authCode;
    protected $avsResponse;
    protected $authAmount;

    public function __construct($data)
    {
        parent::__construct($data);

        $this->responseCode = $this->getDataValue('responseCode');
        $this->authCode = $this->getDataValue('authCode');
        $this->avsResponse = $this->getDataValue('avsResponse');
        $this->authAmount = $this->getDataValue('authAmount');
    }

    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();

        $data['responseCode'] = $this->responseCode;
        $data['authCode'] = $this->authCode;
        $data['avsResponse'] = $this->avsResponse;
        $data['authAmount'] = $this->authAmount;

        return $data;
    }

    /**
     * The transId is an alias for the id.
     */
    public function getTransId()
    {
        return $this->id;
    }

    /**
     * One of TransactionResponse::RESPONSE_CODE_*
     */
    protected function setResponseCode($value)
    {
        $this->responseCode = $value;
    }

    /**
     * @param string $value
     */
    protected function setAuthCode($value)
    {
        $this->authCode = $value;
    }

    /**
     * One of TransactionResponse::AVS_RESULT_CODE_*
     */
    protected function setAvsResponse($value)
    {
        $this->avsResponse = $value;
    }

    /**
     * The amount is sent as a float, unfortuneately.
     * We cannot convert it to a money object, as we don't know
     * what currency was used.
     */
    protected function setAuthAmount($value)
    {
        $this->authAmount = $value;
    }
}
