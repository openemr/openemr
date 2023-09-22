<?php

/*
 * @package openemr
 * @link https://www.open-emr.org
 * @author Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright (c) 2023
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Billing\Payments;

use Symfony\Contracts\EventDispatcher\Event;

class DeletePayment extends Event
{
    const ACTION_DELETE_PAYMENT = 'billing.payment.action.delete.payment';
    private int $paymentId = 0;
    public function __construct($paymentId)
    {
        $this->paymentId = $paymentId;
    }
    public function getPaymentId(): int
    {
        return $this->paymentId;
    }
}
