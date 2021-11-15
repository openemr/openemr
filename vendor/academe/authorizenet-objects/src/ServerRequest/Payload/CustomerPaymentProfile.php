<?php

namespace Academe\AuthorizeNet\ServerRequest\Payload;

/**
 * The payment profile notification payload.
 */

use Academe\AuthorizeNet\Response\HasDataTrait;
use Academe\AuthorizeNet\AbstractModel;
use Academe\AuthorizeNet\ServerRequest\AbstractPayload;

class CustomerPaymentProfile extends AbstractPayload
{
    protected $customerProfileId;
    protected $customerType;

    public function __construct($data)
    {
        parent::__construct($data);

        $this->customerProfileId = $this->getDataValue('customerProfileId');
        $this->customerType = $this->getDataValue('customerType');
    }

    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();

        $data['customerProfileId'] = $this->customerProfileId;
        $data['customerType'] = $this->customerType;

        return $data;
    }

    /**
     * The customerPaymentProfileId is an alias for the id.
     */
    public function getCustomerPaymentProfileId()
    {
        return $this->id;
    }

    /**
     * @param int $value
     */
    protected function setCustomerProfileId($value)
    {
        $this->customerProfileId = $value;
    }

    /**
     * @param string $value One of PaymentProfile::CUSTOMER_TYPE_*
     */
    protected function setCustomerType($value)
    {
        $this->customerType = $value;
    }
}
