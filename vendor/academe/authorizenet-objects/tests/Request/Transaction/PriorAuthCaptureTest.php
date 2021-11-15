<?php

namespace Academe\AuthorizeNet\Request\Transaction;

use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use Academe\AuthorizeNet\Amount\Amount;
use Academe\AuthorizeNet\Request\Model\Order;

class PriorAuthCaptureTest extends TestCase
{
    protected $transaction;

    public function setUp()
    {
        $amount = new Amount('GBP', 123);
        $this->transaction = new PriorAuthCapture($amount, 'REF123');
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
            'transactionType' => 'priorAuthCaptureTransaction',
            'amount' => '1.23',
            'refTransId' => 'REF123',
        ];

        $this->assertSame($data, $this->transaction->toData(true));

        $this->assertSame(
            '{"transactionType":"priorAuthCaptureTransaction","amount":"1.23","refTransId":"REF123"}',
            json_encode($this->transaction)
        );
    }

    /**
     * All parameters populated.
     */
    public function testFull()
    {
        $order = new Order('INV123', 'Description');

        $transaction = $this->transaction->with([
            'terminalNumber' => 'TERM999',
            'order' => $order,
        ]);

        $data = [
            'transactionType' => 'priorAuthCaptureTransaction',
            'amount' => '1.23',
            'terminalNumber' => 'TERM999',
            'refTransId' => 'REF123',
            'order' => [
                'invoiceNumber' => 'INV123',
                'description' => 'Description',
            ],
        ];

        $this->assertSame($data, $transaction->toData(true));
    }
}
