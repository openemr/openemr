<?php
/*
 * @package openemr
 * @link https://www.open-emr.org
 * @author Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright (c) 2023
 * @license All Rights Reserved
 */

namespace OpenEMR\Events\Billing\Payments;

use Symfony\Contracts\EventDispatcher\Event;

class DeletePayment extends Event
{
    const ACTION_DELETE_PAYMENT = 'billing.payment.action.delete.payment';
    private  $paymentId = 0;
    public function __construct($paymentId)
    {
        $this->paymentId = $paymentId;
    }
    public function getPaymentId()
    {
        return $this->paymentId;
    }
}
