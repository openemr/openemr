<?php

namespace Academe\AuthorizeNet\Request\Transaction;

/**
 *
 */

use Academe\AuthorizeNet\Request\Model\CardholderAuthentication;
use Academe\AuthorizeNet\Request\Collections\TransactionSettings;
use Academe\AuthorizeNet\Request\Model\ExtendedAmount;
use Academe\AuthorizeNet\TransactionRequestInterface;
use Academe\AuthorizeNet\Request\Model\NameAddress;
use Academe\AuthorizeNet\Request\Model\Customer;
use Academe\AuthorizeNet\Request\Collections\UserFields;
use Academe\AuthorizeNet\Request\Collections\LineItems;
use Academe\AuthorizeNet\Request\Model\Surcharge;
use Academe\AuthorizeNet\Request\Model\Retail;
use Academe\AuthorizeNet\Request\Model\Order;
use Academe\AuthorizeNet\PaymentInterface;
use Academe\AuthorizeNet\AmountInterface;
use Academe\AuthorizeNet\AbstractModel;

class AuthCapture extends AbstractModel implements TransactionRequestInterface
{
    protected $objectName = 'transactionRequest';
    protected $transactionType = 'authCaptureTransaction';

    protected $amount;
    protected $payment;
    protected $createProfile;
    protected $solutionId;
    protected $terminalNumber;
    protected $authCode;
    protected $order;
    protected $lineItems;
    protected $tax;
    protected $duty;
    protected $shipping;
    protected $taxExempt;
    protected $poNumber;
    protected $customer;
    protected $billTo;
    protected $shipTo;
    protected $cardholderAuthentication;
    protected $retail;
    protected $employeeId;
    protected $customerIP;
    protected $transactionSettings;
    protected $userFields;
    protected $surcharge;
    protected $merchantDescriptor;
    protected $tip;

    protected $authCodeSupported = false;

    /**
     * The amount is a value object.
     */
    public function __construct(AmountInterface $amount)
    {
        parent::__construct();

        $this->setAmount($amount);
    }

    public function jsonSerialize()
    {
        $data = [];

        $data['transactionType'] = $this->getTransactionType();

        // This value object will be formatted according to its currency.
        $data['amount'] = $this->getAmount();

        // The currencyCode is optional, but serves as an extra check that we are sending
        // the correct currency to the account. Each account will support just one
        // currency at present, so this also offers some future-proofing.
        $data['currencyCode'] = $this->getAmount()->getCurrencyCode();

        if ($this->hasPayment()) {
            $data['payment'] = [
                $this->getPayment()->getObjectName() => $this->getPayment(),
            ];
        }

        // CHECKME: The docs do not give examples of how a boolean should be formatted.
        // TODO: it looks like an authorisation can either create a profile, or use a
        // a previously created profile to pay. Some checks will be needed to confirm
        // that.
        // Maybe the profile should be considered a payment method? Depends what else
        // profiles can be used for, I guess.

        if ($this->hasCreateProfile()) {
            $data['profile']['createProfile'] = $this->getCreateProfile();
        }

        if ($this->hasSolutionId()) {
            $data['solution']['id'] = $this->getSolutionId();
        }

        if ($this->hasTerminalNumber()) {
            $data['terminalNumber'] = $this->getTerminalNumber();
        }

        // The authCode is used only by the captureOnlyTransaction message type.
        if ($this->authCodeSupported && $this->hasAuthCode()) {
            $data['authCode'] = $this->getAuthCode();
        }

        if ($this->hasOrder()) {
            $order = $this->getOrder();

            // The order needs at least one of the two optional fields.
            if ($order->hasAny()) {
                // If the order becames more complex, we may need to pick out the
                // individual fields we need.

                $data[$order->getObjectName()] = $order;
            }
        }

        if ($this->hasLineItems()) {
            $lineItems = $this->getLineItems();

            if (count($lineItems)) {
                $data[$lineItems->getObjectName()] = $lineItems;
            }
        }

        if ($this->hasTax()) {
            $tax = $this->getTax();

            if ($tax->hasAny()) {
                $data['tax'] = $tax;
            }
        }

        if ($this->hasDuty()) {
            $duty = $this->getDuty();

            if ($duty->hasAny()) {
                $data['duty'] = $duty;
            }
        }

        if ($this->hasShipping()) {
            $shipping = $this->getShipping();

            if ($shipping->hasAny()) {
                $data['shipping'] = $shipping;
            }
        }

        if ($this->hasTaxExempt()) {
            $data['taxExempt'] = $this->getTaxExempt();
        }

        if ($this->hasPoNumber()) {
            $data['poNumber'] = $this->getPoNumber();
        }

        if ($this->hasCustomer()) {
            $customer = $this->getCustomer();

            if ($customer->hasAny()) {
                $data['customer'] = $customer;
            }
        }

        if ($this->hasBillTo()) {
            $billTo = $this->getBillTo();

            if ($billTo->hasAny()) {
                $data['billTo'] = $billTo;
            }
        }

        if ($this->hasShipTo()) {
            $shipTo = $this->getShipTo();

            if ($shipTo->hasAny()) {
                $data['shipTo'] = $shipTo;
            }
        }

        if ($this->hasCustomerIP()) {
            $data['customerIP'] = $this->getCustomerIP();
        }

        if ($this->hasCardholderAuthentication()) {
            $data['cardholderAuthentication'] = $this->getCardholderAuthentication();
        }

        if ($this->hasRetail()) {
            $data['retail'] = $this->getRetail();
        }

        if ($this->hasEmployeeId()) {
            $data['employeeId'] = $this->getEmployeeId();
        }

        if ($this->hasTransactionSettings()) {
            $transactionSettings = $this->getTransactionSettings();

            if (count($transactionSettings)) {
                $data[$transactionSettings->getObjectName()] = $transactionSettings;
            }
        }

        if ($this->hasUserFields()) {
            $userFields = $this->getUserFields();

            if (count($userFields)) {
                $data[$userFields->getObjectName()] = $userFields;
            }
        }

        if ($this->hasSurcharge()) {
            $surcharge = $this->getSurcharge();

            if ($surcharge->hasAny()) {
                $data['surcharge'] = $surcharge;
            }
        }

        if ($this->hasMerchantDescriptor()) {
            $data['merchantDescriptor'] = $this->getMerchantDescriptor();
        }

        if ($this->hasTip()) {
            $data['tip'] = $this->getTip();
        }

        return $data;
    }

    // Cannot be used if a customer profile is being used.
    protected function setPayment(PaymentInterface $value)
    {
        $this->payment = $value;
    }

    protected function setAmount(AmountInterface $value)
    {
        $this->amount = $value;
    }

    // This is presumably mutually incompatible with using a profile.
    protected function setCreateProfile($value)
    {
        if ($value !== null) {
            $value = (bool)$value;
        }

        $this->createProfile = $value;
    }

    protected function setSolutionId($value)
    {
        $this->solutionId = $value;
    }

    protected function setTerminalNumber($value)
    {
        $this->terminalNumber = $value;
    }

    protected function setAuthCode($value)
    {
        $this->authCode = $value;
    }

    protected function setOrder(Order $value)
    {
        $this->order = $value;
    }

    protected function setLineItems(LineItems $value)
    {
        $this->lineItems = $value;
    }

    protected function setTax(ExtendedAmount $value)
    {
        $this->tax = $value;
    }

    protected function setDuty(ExtendedAmount $value)
    {
        $this->duty = $value;
    }

    protected function setShipping(ExtendedAmount $value)
    {
        $this->shipping = $value;
    }

    protected function setTaxExempt($value)
    {
        if ($value !== null) {
            $value = (bool)$value;
        }

        $this->taxExempt = $value;
    }

    protected function setPoNumber($value)
    {
        $this->poNumber = $value;
    }

    protected function setCustomer(Customer $value)
    {
        $this->customer = $value;
    }

    // Cannot be used if a customer profile is being used.
    protected function setBillTo(NameAddress $value)
    {
        $this->billTo = $value;
    }

    // Cannot be used if a customer profile is being used with a shipping profile.
    protected function setShipTo(NameAddress $value)
    {
        $this->shipTo = $value;
    }

    protected function setCardholderAuthentication(CardholderAuthentication $value)
    {
        $this->cardholderAuthentication = $value;
    }

    protected function setRetail(Retail $value)
    {
        $this->retail = $value;
    }

    // Numeric, 4 digits
    protected function setEmployeeId($value)
    {
        $this->employeeId = $value;
    }

    // IPv4? IPv6? It's not clear what validation will be needed.
    protected function setCustomerIP($value)
    {
        $this->customerIP = $value;
    }

    protected function setTransactionSettings(TransactionSettings $value)
    {
        $this->transactionSettings = $value;
    }

    protected function setUserFields(UserFields $value)
    {
        $this->userFields = $value;
    }

    protected function setSurcharge(Surcharge $value)
    {
        $this->surcharge = $value;
    }

    protected function setMerchantDescriptor($value)
    {
        $this->merchantDescriptor = $value;
    }

    protected function setTip(AmountInterface $value)
    {
        $this->tip = $value;
    }
}
