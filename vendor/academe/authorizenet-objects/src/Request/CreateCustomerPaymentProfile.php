<?php

namespace Academe\AuthorizeNet\Request;

/**
 * This function is used to create a new customer payment profile
 * for an existing customer profile.
 * CHECKME: customer profile ID should therefore be required?
 */

use Academe\AuthorizeNet\Request\Model\PaymentProfile;
use Academe\AuthorizeNet\Auth\MerchantAuthentication;
use Academe\AuthorizeNet\Request\AbstractRequest;

class CreateCustomerPaymentProfile extends AbstractRequest
{
    /**
     * How the card or bank details are validated when setting
     * up the payment profile.
     * Also defined in CreateCustomerProfile
     */
    const VALIDATION_MODE_NONE = 'none';
    const VALIDATION_MODE_TESTMODE = 'testMode';
    const VALIDATION_MODE_LIVEMODE = 'liveMode';

    protected $refId;
    protected $customerProfileId;
    // Single payment profile.
    protected $paymentProfile;
    protected $validationMode;

    public function __construct(
        MerchantAuthentication $merchantAuthentication,
        PaymentProfile $paymentProfile
    ) {
        parent::__construct($merchantAuthentication);
    }

    public function jsonSerialize()
    {
        $data = [];

        // Start with the authentication details.
        $data[$this->getMerchantAuthentication()->getObjectName()] = $this->getMerchantAuthentication();

        if ($this->hasRefId()) {
            $data['refId'] = $this->getRefId();
        }

        if ($this->hasCustomerProfileId()) {
            $data['customerProfileId'] = $this->getCustomerProfileId();
        }

        if ($this->hasPaymentProfile()) {
            $data['paymentProfile'] = $this->getPaymentProfile();
        }

        if ($this->hasValidationMode()) {
            $data['validationMode'] = $this->getValidationMode();
        }

        return [
            $this->getObjectName() => $data,
        ];
    }

    // Merchant-assigned reference ID for the request. Up to 20 characters.
    protected function setRefId($value)
    {
        $this->refId = $value;
    }

    // Payment gateway assigned ID. Numeric.
    protected function setCustomerProfileId($value)
    {
        $this->customerProfileId = $value;
    }

    protected function setPaymentProfile(PaymentProfile $value)
    {
        $this->paymentProfile = $value;
    }

    protected function setValidationMode($value)
    {
        $this->assertValueValidationMode($value);
        $this->validationMode = $value;
    }
}
