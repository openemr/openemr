<?php

namespace Omnipay\AuthorizeNetApi\Message;

use Academe\AuthorizeNet\Amount\MoneyPhp;
use Academe\AuthorizeNet\Amount\Amount;
use Academe\AuthorizeNet\AmountInterface;
use Academe\AuthorizeNet\Request\Transaction\PriorAuthCapture;
use Academe\AuthorizeNet\Request\Transaction\CaptureOnly;
use Academe\AuthorizeNet\Request\Model\Order;

class CaptureRequest extends AbstractRequest
{
    /**
     * Return the complete message object.
     */
    public function getData()
    {
        $amount = new Amount(
            $this->getCurrency(),
            $this->getAmountInteger()
        );

        // Identify the original transaction being authorised.
        $refTransId = $this->getTransactionReference();

        $transaction = $this->createTransaction($amount, $refTransId);

        // The description and invoice number go into an Order object.
        if ($this->getInvoiceNumber() || $this->getDescription()) {
            $order = new Order(
                $this->getInvoiceNumber(),
                $this->getDescription()
            );

            $transaction = $transaction->withOrder($order);
        }

        $transaction = $transaction->with([
            'terminalNumber' => $this->getTerminalNumber(),
        ]);

        return $transaction;
    }

    /**
     * Create a new instance of the transaction object.
     *
     * - PriorAuthCapture is used for transactions authorised through
     *   the API, e.g. a credit card authorisation.
     * - CaptureOnly is used to capture amounts authorized through
     *   other channels, such as a telephone order (MOTO).
     *
     * Only the first is supported at this time. Which gets used will
     * depend on what data is passed in.
     */
    protected function createTransaction(AmountInterface $amount, $refTransId)
    {
        return new PriorAuthCapture($amount, $refTransId);
    }

    /**
     * Accept a transaction and sends it as a request.
     *
     * @param $data TransactionRequestInterface
     * @returns CaptureResponse
     */
    public function sendData($data)
    {
        $response_data = $this->sendTransaction($data);

        return new Response($this, $response_data);
    }
}
