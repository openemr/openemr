<?php

namespace Academe\AuthorizeNet\Request\Transaction;

/**
 * Transaction used to capture a transaction previously authorized through
 * an external channel. All payment details supported by AuthCapturte must
 * be sent through here.
 */

use Academe\AuthorizeNet\AmountInterface;

class CaptureOnly extends AuthCapture
{
    protected $objectName = 'transactionRequest';
    protected $transactionType = 'captureOnlyTransaction';

    protected $authCodeSupported = true;

    /**
     * The amount is a value object.
     * @param string $authCode Between 1 and 6 characters.
     */
    public function __construct(AmountInterface $amount, $authCode)
    {
        parent::__construct($amount);

        $this->setAuthCode($authCode);
    }
}
