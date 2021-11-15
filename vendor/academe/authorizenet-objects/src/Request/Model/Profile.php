<?php

namespace Academe\AuthorizeNet\Request\Model;

/**
 * Some of the results of this object are retuned in JSON as a sting
 * containng a CSV list of lists. Ouch.
 */

use Academe\AuthorizeNet\Request\Collections\PaymentProfiles;
use Academe\AuthorizeNet\TransactionRequestInterface;
use Academe\AuthorizeNet\AbstractModel;

class Profile extends AbstractModel
{
    protected $merchantCustomerId;
    protected $description;
    protected $email;
    protected $customerProfileId;
    protected $paymentProfiles;

    public function __construct()
    {
        parent::__construct();
    }

    public function jsonSerialize()
    {
        $data = [];

        // Not convinced this is the check we need, but it goes with
        // the documentation.
        if (! $this->hasMerchantCustomerId() && ! $this->hasDescription() && ! $this->Email()) {
            throw new \InvalidArgumentException(sprintf(
                'At least one of merchantCustomerId, description and email must be set; none are set'
            ));
        }

        if ($this->hasMerchantCustomerId()) {
            $data['merchantCustomerId'] = $this->getMerchantCustomerId();
        }

        if ($this->hasDescription()) {
            $data['description'] = $this->getDescription();
        }

        if ($this->hasEmail()) {
            $data['email'] = $this->getEmail();
        }

        if ($this->hasCustomerProfileId()) {
            $data['customerProfileId'] = $this->getCustomerProfileId();
        }

        if ($this->hasPaymentProfiles()) {
            $data['paymentProfiles'] = $this->getPaymentProfiles();
        }

        return $data;
    }

    // Up to 20 characters.
    // Required only when no description and email supplied.
    protected function setMerchantCustomerId($value)
    {
        $this->merchantCustomerId = $value;
    }

    // Up to 255 characters.
    protected function setDescription($value)
    {
        $this->description = $value;
    }

    // Up to 255 characters.
    protected function setEmail($value)
    {
        $this->email = $value;
    }

    protected function setCustomerProfileId($value)
    {
        $this->customerProfileId = $value;
    }

    // The documentation examples is not consistent with this item
    // being a list of payment profiles, so some experiments are needed.
    // It is likely this will be a Collections\PaymentProfiles object.
    protected function setPaymentProfiles(PaymentProfiles $value)
    {
        $this->paymentProfiles = $value;
    }
}
