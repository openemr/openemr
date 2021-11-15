<?php

namespace Academe\AuthorizeNet\Request\Model;

/**
 * Some of the results of this object are retuned in JSON as a sting
 * containng a CSV list of lists. Ouch.
 */

use Academe\AuthorizeNet\Request\Model\NameAddress;
use Academe\AuthorizeNet\PaymentInterface;
use Academe\AuthorizeNet\AbstractModel;

class PaymentProfile extends AbstractModel
{
    // Also defined in the Customer class.
    const CUSTOMER_TYPE_INDIVIDUAL = 'individual';
    const CUSTOMER_TYPE_BUSINESS = 'business';

    // CHECKME: should this be two classes, one for defining a payment
    // profile definition, and the other for using a payment profile?
    // Or perhaps using a payment profile should not even be an object,
    // but simply an ID scalar?

    // For creating a new prfile.
    protected $customerType;
    protected $billTo;
    protected $payment;
    protected $defaultPaymentProfile;

    // For using an existing profile.
    protected $paymentProfileId;
    protected $cardCode;

    public function __construct()
    {
        parent::__construct();
    }

    public function jsonSerialize()
    {
        $data = [];

        if ($this->hasCustomerType()) {
            $data['type'] = $this->getCustomerType();
        }

        if ($this->hasBillTo()) {
            $billTo = $this->getBillTo();

            if ($billTo->hasAny()) {
                $data['billTo'] = $billTo;
            }
        }

        if ($this->hasPayment()) {
            $data['payment'] = [
                $this->getPayment()->getObjectName() => $this->getPayment(),
            ];
        }

        if ($this->hasDefaultPaymentProfile()) {
            $defaultPaymentProfile = $this->getDefaultPaymentProfile();

            if ($defaultPaymentProfile->hasAny()) {
                $data['defaultPaymentProfile'] = $defaultPaymentProfile;
            }
        }

        if ($this->hasPaymentProfileId()) {
            $data['paymentProfileId'] = $this->getPaymentProfileId();
        }

        if ($this->hasCardCode()) {
            $data['cardCode'] = $this->getCardCode();
        }

        return $data;
    }

    protected function setCustomerType($value)
    {
        $this->assertValueCustomerType($value);
        $this->customerType = $value;
    }

    protected function setBillTo(NameAddress $value)
    {
        $this->billTo = $value;
    }

    // Allowed payment types: creditCard, bankAccount, or opaqueData
    // Academe\AuthorizeNet\Payment\CreditCard|BankAccount|OpaqueData
    protected function setPayment(PaymentInterface $value)
    {
        $this->payment = $value;
    }

    // When set to true, this field designates the payment profile as the default
    protected function setDefaultPaymentProfile($value)
    {
        if ($value !== true) {
            $value = false;
        }

        $this->defaultPaymentProfile = $value;
    }

    protected function setPaymentProfileId($value)
    {
        $this->paymentProfileId = $value;
    }

    protected function setCardCode($value)
    {
        $this->cardCode = $value;
    }
}
