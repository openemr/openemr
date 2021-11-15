<?php

namespace Academe\AuthorizeNet\Request\Transaction;

/**
 * A refund is nearly identical to an original payment, so we will
 * base this class on the payment (AuthCapture), with some alterations.
 *
 * A refund can only be provided after the transaction has been settled,
 * which can take up to 24 hours. Until that point, use void to void the
 * transaction before it is settled.
 */

use Academe\AuthorizeNet\TransactionRequestInterface;
use Academe\AuthorizeNet\Payment\BankAccount;
use Academe\AuthorizeNet\Payment\CreditCard;
use Academe\AuthorizeNet\AmountInterface;

class Refund extends AuthCapture implements TransactionRequestInterface
{
    protected $objectName = 'transactionRequest';
    protected $transactionType = 'refundTransaction';

    protected $refTransId;

    /**
     * The amount to refund and the original transaction reference ID are required.
     */
    public function __construct(AmountInterface $amount, $refTransId)
    {
        parent::__construct($amount);

        $this->setRefTransId($refTransId);
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
            // For a refund, only partial payment method details are sent.
            // This is dependant on the payment method (CC, Bank etc).
            // CHECKME: it does not appear that opaqueData is supported for refunds.

            $payment = $this->getPayment();

            if ($payment instanceof CreditCard) {
                // The gateway expects either the full card number plus expiry,
                // or the originakl transaction ID and the last four digits
                // of the card.
                // However, it is still a little ambiguous.

                $data['payment'] = [
                    'creditCard' => [
                        'cardNumber' => $payment->getLastFourDigits(),
                        'expirationDate' => 'XXXX',
                    ]
                ];
            }

            if ($payment instanceof BankAccount) {
                // The documentation appears to be wrong, by listing just the masked bank
                // account number in the spec, but providing the full original bank account
                // details in the example. It may yet be a hybrid, with all details, but
                // masked.

                $data['payment'] = [
                    $this->getPayment()->getObjectName() => $this->getPayment(),
                ];
            }
        }

        $data['refTransId'] = $this->getRefTransId();

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

        return $data;
    }

    /**
     * @param $value string Reference transaction ID
     */
    protected function setRefTransId($value)
    {
        $this->refTransId = $value;
    }
}
