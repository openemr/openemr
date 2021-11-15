<?php

namespace Academe\AuthorizeNet\ServerRequest\Payload;

/**
 * The customer profile notification payload.
 */

use Academe\AuthorizeNet\ServerRequest\Payload\Payment;
use Academe\AuthorizeNet\ServerRequest\Model\Profile;
use Academe\AuthorizeNet\ServerRequest\Collections\CustomerPaymentProfiles;
use Academe\AuthorizeNet\ServerRequest\AbstractPayload;

class CustomerProfile extends AbstractPayload
{
    protected $merchantCustomerId;
    protected $description;

    protected $paymentProfiles;

    public function __construct($data)
    {
        parent::__construct($data);

        $this->merchantCustomerId = $this->getDataValue('merchantCustomerId');
        $this->description = $this->getDataValue('description');

        $paymentProfiles = $this->getDataValue('paymentProfiles');

        if ($paymentProfiles) {
            $this->paymentProfiles = new CustomerPaymentProfiles($paymentProfiles);
        }
    }

    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();

        $data['merchantCustomerId'] = $this->merchantCustomerId;
        $data['description'] = $this->description;

        $data['paymentProfiles'] = $this->paymentProfiles;

        return $data;
    }

    /**
     * The customerProfileId is an alias for the id.
     */
    public function getCustomerProfileId()
    {
        return $this->id;
    }

    /**
     *
     */
    protected function setMerchantCustomerId($value)
    {
        $this->merchantCustomerId = $value;
    }

    /**
     *
     */
    protected function setDescription($value)
    {
        $this->description = $value;
    }

    /**
     *
     */
    protected function setPaymentProfiles(PaymentProfiles $value)
    {
        $this->profile = $value;
    }
}
