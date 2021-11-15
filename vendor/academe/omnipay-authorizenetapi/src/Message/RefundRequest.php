<?php

namespace Omnipay\AuthorizeNetApi\Message;

use Academe\AuthorizeNet\Amount\MoneyPhp;
use Academe\AuthorizeNet\Amount\Amount;
use Academe\AuthorizeNet\AmountInterface;
use Academe\AuthorizeNet\Request\Transaction\Refund;
use Academe\AuthorizeNet\Request\Model\Order;
use Academe\AuthorizeNet\Payment\CreditCard;

class RefundRequest extends CaptureRequest
{
    public function getData()
    {
        $transaction = parent::getData();

        $card = $this->getCard();

        if ($card) {
            // A credit card has been supplied.

            if ($card->getNumber()) {
                $creditCard = new CreditCard(
                    $card->getNumber(),
                    // Either MMYY or MMYYYY will work.
                    // (This will be overwritten with 'XXXX' for now)
                    $card->getExpiryMonth() . $card->getExpiryYear()
                );

                $transaction = $transaction->withPayment($creditCard);
            }
        }

        // Instead of supplying the full credit card dtails, just
        // provide the lasy four digits of the card number.

        if ($this->getNumberLastFour()) {
            $creditCard = new CreditCard(
                $this->getNumberLastFour(),
                'XXXX'
            );

            $transaction = $transaction->withPayment($creditCard);
        }

        return $transaction;
    }

    protected function createTransaction(AmountInterface $amount, $refTransId)
    {
        return new Refund($amount, $refTransId);
    }

    /**
     * The last four digits of the origonal credit card.
     */
    public function getNumberLastFour()
    {
        return $this->getParameter('numberLastFour');
    }

    public function setNumberLastFour($value)
    {
        return $this->setParameter('numberLastFour', $value);
    }
}
