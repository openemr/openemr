<?php

namespace Omnipay\Stripe\Message\PaymentIntents;

use Omnipay\Tests\TestCase;

class PurchaseRequestTest extends TestCase
{
    /**
     * @var PurchaseRequest
     */
    protected $request;

    public function setUp()
    {
        $this->request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize(
            array(
                'amount' => '12.00',
                'currency' => 'USD',
                'paymentMethod' => 'pm_valid_payment_method',
                'description' => 'Order #42',
                'metadata' => array(
                    'foo' => 'bar',
                ),
                'applicationFee' => '1.00'
            )
        );
    }

    public function testGetData()
    {
        $data = $this->request->getData();

        $this->assertSame(1200, $data['amount']);
        $this->assertSame('usd', $data['currency']);
        $this->assertSame('Order #42', $data['description']);
        $this->assertSame('automatic', $data['capture_method']);
        $this->assertSame('manual', $data['confirmation_method']);
        $this->assertSame('pm_valid_payment_method', $data['payment_method']);
        $this->assertSame(array('foo' => 'bar'), $data['metadata']);
        $this->assertSame(100, $data['application_fee']);
    }

    public function testSendSuccessAndRequireConfirmation()
    {
        $this->setMockHttpResponse('PurchaseSuccess.txt');
        /** @var PaymentIntentsResponse $response */
        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertFalse($response->requiresConfirmation());
        $this->assertSame('automatic', $response->getCaptureMethod());
        $this->assertSame('pm_1EW0FPFSbr6xR4YAZOSMHIOE', $response->getCardReference());
        $this->assertSame('req_8PDHeZazN2LwML', $response->getRequestId());
        $this->assertSame('cus_F0026biLhRIcO9', $response->getCustomerReference());
        $this->assertNull($response->getMessage());
    }
}
