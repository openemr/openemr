<?php

/** @package    verysimple::Payment */

/**
 * PaymentRespones is returned by a PaymentProcessor after an attempt
 * to process a payment through a payment gateway
 *
 * @package verysimple::Payment
 * @author VerySimple Inc.
 * @copyright 1997-2007 VerySimple, Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 2.0
 */
class PaymentResponse
{
    var $OrderNumber = "";
    var $IsSuccess = false;
    var $TransactionId = "";
    var $ResponseCode = "";
    var $ResponseMessage = "";
    var $RawResponse = "";
    var $ParsedResponse = array ();
}
