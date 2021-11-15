<?php

namespace Academe\AuthorizeNet\Request\Model;

/**
 *
 */

use Academe\AuthorizeNet\TransactionRequestInterface;
use Academe\AuthorizeNet\AbstractModel;

class Retail extends AbstractModel
{
    const MARKET_TYPE_ECOMMERCE = 0;
    const MARKET_TYPE_MOTO = 1;
    const MARKET_TYPE_RETAIL = 2;

    // Unknown
    const DEVICE_TYPE_UNKNOWN = 1;
    // Unattended Terminal
    const DEVICE_TYPE_UNATTENDED = 2;
    // Self Service Terminal
    const DEVICE_TYPE_SELF_SERVICE = 3;
    // Electronic Cash Register
    const DEVICE_TYPE_CASH_REGISTER = 4;
    // Personal Computer-Based Terminal
    const DEVICE_TYPE_PC = 5;
    // Wireless POS
    const DEVICE_TYPE_WIRELESS_POS = 7;
    // Website
    const DEVICE_TYPE_WEBSITE = 8;
    // Dial Terminal
    const DEVICE_TYPE_DIAL = 9;
    // Virtual Terminal
    const DEVICE_TYPE_VIRTUAL = 10;

    protected $marketType;
    protected $deviceType;
    protected $customerSignature;

    public function __construct(
        $marketType,
        $deviceType,
        $customerSignature = null
    ) {
        parent::__construct();

        $this->setMarketType($marketType);
        $this->setDeviceType($deviceType);
        $this->setCustomerSignature($customerSignature);
    }

    public function jsonSerialize()
    {
        $data = [];

        $data['marketType'] = $this->getMarketType();
        $data['deviceType'] = $this->getDeviceType();

        if ($this->hasCustomerSignature()) {
            $data['customerSignature'] = $this->getCustomerSignature();
        }

        return $data;
    }

    protected function setMarketType($value)
    {
        $this->assertValueMarketType($value);

        $this->marketType = (int)$value;
    }

    protected function setDeviceType($value)
    {
        $this->assertValueDeviceType($value);

        $this->deviceType = (int)$value;
    }

    /**
     * The signature must be PNG formatted data.
     * It will make sense to support supplying a PNG image as a stream.
     * Also some escaping may be required if this data is effectively binary.
     */
    protected function setCustomerSignature($value)
    {
        $this->customerSignature = $value;
    }
}
