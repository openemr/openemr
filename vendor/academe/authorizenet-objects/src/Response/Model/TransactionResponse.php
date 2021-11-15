<?php

namespace Academe\AuthorizeNet\Response\Model;

/**
 *
 */

use Academe\AuthorizeNet\AbstractModel;
use Academe\AuthorizeNet\Response\HasDataTrait;

use Academe\AuthorizeNet\Response\Collections\TransactionMessages;
use Academe\AuthorizeNet\Response\Model\PrePaidCard;
use Academe\AuthorizeNet\Response\Collections\Errors;
use Academe\AuthorizeNet\Response\Collections\UserFields;

use Academe\AuthorizeNet\Response\Model\SplitTenderPayments;

class TransactionResponse extends AbstractModel
{
    use HasDataTrait;

    /**
     * The overall transaction response codes.
     * PEDNING is "Held for Review".
     */
    const RESPONSE_CODE_APPROVED    = 1;
    const RESPONSE_CODE_DECLINED    = 2;
    const RESPONSE_CODE_ERROR       = 3;
    const RESPONSE_CODE_PENDING     = 4;

    /**
     * avsResultCode values
     *
     * A = Address (Street) matches, ZIP does not.
     * B = Address information not provided for AVS check.
     * E = AVS error.
     * G = Non-U.S. Card Issuing Bank.
     * N = No Match on Address (Street) or ZIP.
     * P = AVS not applicable for this transaction.
     * R = Retry — System unavailable or timed out.
     * S = Service not supported by issuer.
     * U = Address information is unavailable.
     * W = Nine digit ZIP matches, Address (Street) does not.
     * X = Address (Street) and nine digit ZIP match.
     * Y = Address (Street) and five digit ZIP match.
     * Z = Five digit ZIP matches, Address (Street) does not.
     */
    const AVS_RESULT_CODE_A = 'A';
    const AVS_RESULT_CODE_B = 'B';
    const AVS_RESULT_CODE_E = 'E';
    const AVS_RESULT_CODE_G = 'G';
    const AVS_RESULT_CODE_N = 'N';
    const AVS_RESULT_CODE_P = 'P';
    const AVS_RESULT_CODE_R = 'R';
    const AVS_RESULT_CODE_S = 'S';
    const AVS_RESULT_CODE_U = 'U';
    const AVS_RESULT_CODE_W = 'W';
    const AVS_RESULT_CODE_X = 'X';
    const AVS_RESULT_CODE_Y = 'Y';
    const AVS_RESULT_CODE_Z = 'Z';

    /**
     * cvvResultCode values.
     *
     * M = Match.
     * N = No Match.
     * P = Not Processed.
     * S = Should have been present.
     * U = Issuer unable to process request.
     */
    const CVV_RESULT_CODE_MATCH         = 'M';
    const CVV_RESULT_CODE_NO_MATCH      = 'N';
    const CVV_RESULT_CODE_NOT_PROCESSED = 'P';
    const CVV_RESULT_CODE_SHOULD_HAVE   = 'S';
    const CVV_RESULT_CODE_ISSUER_UNABLE = 'U';

    /**
     * cavvResultCode values.
     *
     * Blank or not present = CAVV not validated.
     * 0 = CAVV not validated because erroneous data was submitted.
     * 1 = CAVV failed validation.
     * 2 = CAVV passed validation.
     * 3 = CAVV validation could not be performed; issuer attempt incomplete.
     * 4 = CAVV validation could not be performed; issuer system error.
     * 5 = Reserved for future use.
     * 6 = Reserved for future use.
     * 7 = CAVV attempt — failed validation — issuer available (U.S.-issued card/non-U.S acquirer).
     * 8 = CAVV attempt — passed validation — issuer available (U.S.-issued card/non-U.S. acquirer).
     * 9 = CAVV attempt — failed validation — issuer unavailable (U.S.-issued card/non-U.S. acquirer).
     * A = CAVV attempt — passed validation — issuer unavailable (U.S.-issued card/non-U.S. acquirer).
     * B = CAVV passed validation, information only, no liability shift.
     */
    const CAVV_RESULT_CODE_NOT_VALIDATED = '';
    const CAVV_RESULT_CODE_0 = '0';
    const CAVV_RESULT_CODE_1 = '1';
    const CAVV_RESULT_CODE_2 = '2';
    const CAVV_RESULT_CODE_3 = '3';
    const CAVV_RESULT_CODE_4 = '4';
    const CAVV_RESULT_CODE_5 = '5';
    const CAVV_RESULT_CODE_6 = '6';
    const CAVV_RESULT_CODE_7 = '7';
    const CAVV_RESULT_CODE_8 = '8';
    const CAVV_RESULT_CODE_9 = '9';
    const CAVV_RESULT_CODE_A = 'A';
    const CAVV_RESULT_CODE_B = 'B';


    /**
     * @property string $responseCode
     */
    protected $responseCode;

    /**
     * @property string $rawResponseCode
     * Used by PayPal responses.
     */
    protected $rawResponseCode;

    /**
     * @property string $authCode
     */
    protected $authCode;

    /**
     * @property string $avsResultCode
     */
    protected $avsResultCode;

    /**
     * @property string $cvvResultCode
     */
    protected $cvvResultCode;

    /**
     * @property string $cavvResultCode
     */
    protected $cavvResultCode;

    /**
     * @property string $transId
     */
    protected $transId;

    /**
     * @property string $refTransID
     */
    protected $refTransID;

    /**
     * @property string $transHash
     */
    protected $transHash;

    /**
     * @property string $testRequest
     */
    protected $testRequest;

    /**
     * @property string $accountNumber
     */
    protected $accountNumber;

    /**
     * @property string $entryMode
     */
    protected $entryMode;

    /**
     * @property string $accountType
     */
    protected $accountType;

    /**
     * @property string $splitTenderId
     */
    protected $splitTenderId;

    /**
     * @property
     * TBC class
     * $prePaidCard
     */
    protected $prePaidCard;

    /**
     * @property Collection\Messages
     * $messages
     */
    protected $transactionMessages;

    /**
     * @property Collections\Errors
     * $errors
     */
    protected $errors;

    /**
     * @property Collection\SplitTenderPayments
     * $splitTenderPayments
     */
    protected $splitTenderPayments;

    /**
     * @property Collections\UserFields
     */
    protected $userFields;

    /**
     * @property TODO class $shipTo (for PayPal)
     */
    protected $shipTo;

    /**
     * @property
     * TODO class (optional properties include secureAcceptanceUrl, PayerID)
     * $secureAcceptance
     */
    protected $secureAcceptance;

    /**
     * @property
     * TBC collection
     * $emvResponse
     */
    protected $emvResponse;

    /**
     * @property string $transHashSha2
     */
    protected $transHashSha2;

    /**
     * @property \net\authorize\api\contract\v1\CustomerProfileIdType $profile
     */
    protected $profile;

    public function __construct($data)
    {
        $this->setData($data);

        $this->setResponseCode($this->getDataValue('responseCode'));
        $this->setRawResponseCode($this->getDataValue('rawResponseCode'));
        $this->setAuthCode($this->getDataValue('authCode'));
        $this->setAvsResultCode($this->getDataValue('avsResultCode'));
        $this->setCvvResultCode($this->getDataValue('cvvResultCode'));
        $this->setCavvResultCode($this->getDataValue('cavvResultCode'));

        $this->setTransId($this->getDataValue('transId'));
        $this->setRefTransId($this->getDataValue('refTransId'));
        $this->setTransHash($this->getDataValue('transHash'));

        $this->setTestRequest($this->getDataValue('testRequest'));

        $this->setAccountNumber($this->getDataValue('accountNumber'));
        $this->setEntryMode($this->getDataValue('entryMode'));
        $this->setAccountType($this->getDataValue('accountType'));
        $this->setSplitTenderId($this->getDataValue('splitTenderId'));

        if ($prePaidCard = $this->getDataValue('prePaidCard')) {
            $this->setPrePaidCard(new PrePaidCard($prePaidCard));
        }

        if ($messages = $this->getDataValue('messages')) {
            $this->setTransactionMessages(new TransactionMessages($messages));
        }

        if ($errors = $this->getDataValue('errors')) {
            $this->setErrors(new Errors($errors));
        }

        if ($splitTenderPayments = $this->getDataValue('splitTenderPayments')) {
            $this->setSplitTenderPayments(new SplitTenderPayments($splitTenderPayments));
        }

        if ($userFields = $this->getDataValue('userFields')) {
            $this->setUserFields(new UserFields($userFields));
        }
    }

    public function jsonSerialize()
    {
        $data = [
            'responseCode' => $this->getResponseCode(),
            'rawResponseCode' => $this->getRawResponseCode(),
            'authCode' => $this->getAuthCode(),
            'avsResultCode' => $this->getAvsResultCode(),
            'cvvResultCode' => $this->getCvvResultCode(),
            'cavvResultCode' => $this->getCavvResultCode(),
            'transId' => $this->getTransId(),
            'refTransId' => $this->getRefTransId(),
            'transHash' => $this->getTransHash(),
            'testRequest' => $this->getTestRequest(),
            'accountNumber' => $this->getAccountNumber(),
            'entryMode' => $this->getEntryMode(),
            'accountType' => $this->getAccountType(),
        ];

        if ($this->hasSplitTenderId()) {
            $data['splitTenderId'] = $this->getSplitTenderId();
        }

        if ($this->hasTransactionMessages()) {
            $data['messages'] = $this->getTransactionMessages();
        }

        if ($this->hasErrors()) {
            $data['errors'] = $this->getErrors();
        }

        if ($this->hasUserFields()) {
            $data['userFields'] = $this->getUserFields();
        }

        return $data;
    }

    protected function setResponseCode($value)
    {
        $this->responseCode = $value;
    }

    protected function setRawResponseCode($value)
    {
        $this->rawResponseCode = $value;
    }

    protected function setAuthCode($value)
    {
        $this->authCode = $value;
    }

    protected function setAvsResultCode($value)
    {
        $this->avsResultCode = $value;
    }

    protected function setCvvResultCode($value)
    {
        $this->cvvResultCode = $value;
    }

    protected function setCavvResultCode($value)
    {
        $this->cavvResultCode = $value;
    }

    protected function setTransId($value)
    {
        $this->transId = $value;
    }

    protected function setRefTransId($value)
    {
        $this->refTransId = $value;
    }

    protected function setTransHash($value)
    {
        $this->transHash = $value;
    }

    protected function setTestRequest($value)
    {
        $this->testRequest = $value;
    }

    protected function setAccountNumber($value)
    {
        $this->accountNumber = $value;
    }

    protected function setEntryMode($value)
    {
        $this->entryMode = $value;
    }

    protected function setAccountType($value)
    {
        $this->accountType = $value;
    }

    protected function setSplitTenderId($value)
    {
        $this->splitTenderId = $value;
    }

    protected function setPrePaidCard(PrePaidCard $value)
    {
        $this->prePaidCard = $value;
    }

    protected function setTransactionMessages(TransactionMessages $value)
    {
        $this->transactionMessages = $value;
    }

    protected function setErrors(Errors $value)
    {
        $this->errors = $value;
    }

    protected function setUserFields(UserFields $value)
    {
        $this->userFields = $value;
    }
}
