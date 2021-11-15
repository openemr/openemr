<?php

namespace Academe\AuthorizeNet\Request\Transaction;

/**
 * NOTE: this whole test should be identical to AuthCapture, except for the
 * additional of an authCode field. However, we will only test a handful of
 * the fields that are interesting to CaptureOnly.
 */

use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use Academe\AuthorizeNet\Amount\Amount;
use Academe\AuthorizeNet\Payment\CreditCard;
use Academe\AuthorizeNet\Request\Model\Order;
use Academe\AuthorizeNet\Request\Model\Surcharge;

class CaptureOnlyTest extends TestCase
{
    protected $transaction;

    public function setUp()
    {
        $amount = new Amount('GBP', 123);
        $this->transaction = new CaptureOnly($amount, 'AUTH12');

        $creditCard = new CreditCard(
            '4000000000000001',
            '2020-12'
        );

        $this->transaction = $this->transaction->withPayment($creditCard);
    }

    /**
     * A minimaal request.
     * The documentation does not make it clear whether the amount should be
     * a string or a float. We are going for a float for now, but have a hunch
     * that may need to be changed throughout.
     */
    public function testSimple()
    {
        $data = [
            'transactionType' => 'captureOnlyTransaction',
            'amount' => '1.23',
            'currencyCode' => 'GBP',
            'payment' => [
                'creditCard' => [
                    'cardNumber' => '4000000000000001',
                    'expirationDate' => '2020-12',
                ],
            ],
            'authCode' => 'AUTH12',
        ];

        $this->assertSame($data, $this->transaction->toData(true));

        $this->assertSame(
            '{"transactionType":"captureOnlyTransaction","amount":"1.23","currencyCode":"GBP","payment":{"creditCard":{"cardNumber":"4000000000000001","expirationDate":"2020-12"}},"authCode":"AUTH12"}',
            json_encode($this->transaction)
        );
    }

    /**
     * All parameters populated.
     */
    public function testFull()
    {
        $order = new Order('INV123', 'Description');

        $surcharge = new Surcharge(
            new Amount('GBP', 99),
            'Surcharge Description'
        );

        $transaction = $this->transaction->with([
            'terminalNumber' => 'TERM999',
            'order' => $order,
            'surcharge' => $surcharge,
            'merchantDescriptor' => 'Merchant Desc',
            'tip' => new Amount('GBP', 500),
            'employeeId' => 1234,
        ]);

        $data = [
            'transactionType' => 'captureOnlyTransaction',
            'amount' => '1.23',
            'currencyCode' => 'GBP',
            'payment' => [
                'creditCard' => [
                    'cardNumber' => '4000000000000001',
                    'expirationDate' => '2020-12',
                ],
            ],
            'terminalNumber' => 'TERM999',
            'authCode' => 'AUTH12',
            'order' => [
                'invoiceNumber' => 'INV123',
                'description' => 'Description',
            ],
            'employeeId' => 1234,
            'surcharge' => [
                'amount' => '0.99',
                'description' => 'Surcharge Description',
            ],
            'merchantDescriptor' => 'Merchant Desc',
            'tip' => '5.00',
        ];

        $this->assertSame($data, $transaction->toData(true));
    }
}
