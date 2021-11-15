<?php

namespace Academe\AuthorizeNet\Auth;

/**
 * TODO: protect this data from var_dump.
 * Also authenticaion methods include:
 *  sessionToken, password, impersonationAuthentication, fingerPrint, clientKey and mobileDeviceId.
 * Whether we need separate objects to support different combinations of mandatory parameters,
 * needs to be looked at.
 */

use Academe\AuthorizeNet\AbstractModel;

class MerchantAuthentication extends AbstractModel
{
    protected $name;
    protected $transactionKey;

    // Either mobileDeviceId or refId can be used, but never both.
    protected $mobileDeviceId;
    protected $refId;

    /**
     * string and string
     */
    public function __construct($name, $transactionKey)
    {
        parent::__construct();

        $this->setName($name);
        $this->setTransactionKey($transactionKey);
    }

    public function jsonSerialize()
    {
        $data = [
            'name' => $this->name,
            'transactionKey' => $this->transactionKey,
        ];

        // Mutually exclusive options.
        // We arbitrarily look at the refId first.
        if ($this->hasRefId()) {
            $data['refId'] = $this->getRefId();
        } elseif ($this->hasMobileDeviceId()) {
            $data['mobileDeviceId'] = $this->getMobileDeviceId();
        }

        return $data;
    }

    // These setters can include validation.

    protected function setName($value)
    {
        $this->name = $value;
    }

    protected function setTransactionKey($value)
    {
        $this->transactionKey = $value;
    }

    protected function setMobileDeviceId($value)
    {
        $this->mobileDeviceId = $value;
    }

    protected function setRefId($value)
    {
        $this->refId = $value;
    }
}
