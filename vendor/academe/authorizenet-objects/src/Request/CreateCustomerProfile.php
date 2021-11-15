<?php

namespace Academe\AuthorizeNet\Request;

/**
 *
 */

use Academe\AuthorizeNet\Auth\MerchantAuthentication;
use Academe\AuthorizeNet\AbstractModel;
use Academe\AuthorizeNet\Request\Model\Profile;

class CreateCustomerProfile extends AbstractRequest
{
    /**
     * How the card or bank details are validated when setting
     * up the payment profile.
     */
    const VALIDATION_MODE_NONE = 'none';
    const VALIDATION_MODE_TESTMODE = 'testMode';
    const VALIDATION_MODE_LIVEMODE = 'liveMode';

    protected $refId;
    protected $profile;
    // List of shipto addresses, or maybe just one (docs look wrong).
    protected $shipToList;
    protected $validationMode;

    public function __construct(
        MerchantAuthentication $merchantAuthentication,
        Profile $customerProfile
    ) {
        parent::__construct($merchantAuthentication);

        $this->setCustomerProfile($customerProfile);
    }

    public function jsonSerialize()
    {
        $data = [];

        // Start with the authentication details.
        $data[$this->getMerchantAuthentication()->getObjectName()] = $this->getMerchantAuthentication();

        if ($this->hasRefId()) {
            $data['refId'] = $this->getRefId();
        }

        if ($this->hasCustomerProfile()) {
            $data['profile'] = $this->getProfile();
        }

        if ($this->hasShipToList()) {
            $data['shipToList'] = $this->getShipToList();
        }

        if ($this->hasValidationMode()) {
            $data['validationMode'] = $this->getValidationMode();
        }

        return [
            $this->getObjectName() => $data,
        ];
    }

    protected function setRefId($value)
    {
        $this->refId = $value;
    }

    protected function setProfile($value)
    {
        $this->profile = $value;
    }

    // One address or a collection? Docs are ambiguous.
    protected function setShipToList(TBC $value)
    {
        $this->shipToList = $value;
    }

    protected function setValidationMode($value)
    {
        $this->assertValueValidationMode($value);
        $this->validationMode = $value;
    }
}
