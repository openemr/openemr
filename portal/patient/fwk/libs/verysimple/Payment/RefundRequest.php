<?php

/** @package    verysimple::Payment */

/**
 * PaymentRequest is a generic object containing information necessary
 * to process a refund through a payment gateway.
 * To ensure that
 * your RefundRequest can be processed independantly of payment gateway,
 * it is best not to extend this class, rather write a PaymentProcessor
 * that will work correctly for the particular payment gateway.
 *
 * @package verysimple::Payment
 * @author VerySimple Inc.
 * @copyright 1997-2012 VerySimple, Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 2.0
 */
class RefundRequest
{
    static $REFUND_TYPE_FULL = 'Full';
    static $REFUND_TYPE_PARTIAL = 'Partial';
    public $TransactionId = "";
    public $InvoiceId = "";
    public $Memo = "";
    public $RefundType = "";
    public $RefundAmount = "";
    public $TransactionCurrency = "USD";
    public $CustomerIP = "";

    /**
     * Constructor
     */
    final function __construct()
    {
        $this->Init();
    }

    /**
     * Called by base object on construction.
     * override this
     * to handle any special initialization
     */
    function Init()
    {
        $this->RefundType = self::$REFUND_TYPE_FULL;
        $this->CustomerIP = $_SERVER ['REMOTE_ADDR'];
    }
}
