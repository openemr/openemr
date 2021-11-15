<?php

namespace Omnipay\Stripe\Message\PaymentIntents;

use Omnipay\Tests\TestCase;

class AuthorizeRequestTest extends TestCase
{
    /**
     * @var AuthorizeRequest
     */
    private $request;

    public function setUp()
    {
        $this->request = new AuthorizeRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize(
            array(
                'amount' => '12.00',
                'currency' => 'USD',
                'paymentMethod' => 'pm_valid_payment_method',
                'description' => 'Order #42',
                'metadata' => array(
                    'foo' => 'bar',
                ),
                'applicationFee' => '1.00',
                'returnUrl' => 'complete-payment',
                'confirm' => true,
            )
        );
    }

    public function testGetData()
    {
        $data = $this->request->getData();

        $this->assertSame(1200, $data['amount']);
        $this->assertSame('usd', $data['currency']);
        $this->assertSame('Order #42', $data['description']);
        $this->assertSame('manual', $data['capture_method']);
        $this->assertSame('manual', $data['confirmation_method']);
        $this->assertSame('pm_valid_payment_method', $data['payment_method']);
        $this->assertSame(array('foo' => 'bar'), $data['metadata']);
        $this->assertSame(100, $data['application_fee']);
    }

    /**
     * Test that providing card data won't work.
     *
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The paymentMethod parameter is required
     */
    public function testDataWithCardData()
    {
        $this->request->setPaymentMethod(null);
        $this->request->setCard($this->getValidCard());
        $this->request->getData();
    }

    public function testDataWithCardReference()
    {
        $this->request->setPaymentMethod(null);
        $this->request->setCardReference('card_visa');
        $data = $this->request->getData();

        $this->assertSame('card_visa', $data['payment_method']);
    }

    public function testDataWithSource()
    {
        $this->request->setPaymentMethod(null);
        $this->request->setSource('src_visa');
        $data = $this->request->getData();

        $this->assertSame('src_visa', $data['payment_method']);
    }

    public function testDataWithToken()
    {
        $this->request->setPaymentMethod(null);
        $this->request->setToken('tok_visa');
        $data = $this->request->getData();

        $this->assertArrayHasKey('payment_method_data', $data);
    }

    public function testDataWithCustomerReference()
    {
        $this->request->setCustomerReference('abc');
        $data = $this->request->getData();

        $this->assertSame('abc', $data['customer']);
    }


    public function testDataWithStatementDescriptor()
    {
        $this->request->setStatementDescriptor('OMNIPAY');
        $data = $this->request->getData();

        $this->assertSame('OMNIPAY', $data['statement_descriptor']);
    }

    public function testDataWithDestination()
    {
        $this->request->setDestination('xyz');
        $data = $this->request->getData();

        $this->assertSame('xyz', $data['transfer_data']['destination']);
    }

    /**
     * Confirming a payment intent without a return url would destroy the flow for 3DS 2.0,
     * so let's make sure that setting confirm to true and skipping return url is
     * not permitted.
     *
     * @expectedException \Omnipay\Common\Exception\InvalidRequestException
     * @expectedExceptionMessage The returnUrl parameter is required
     */
    public function testReturnUrlMustBeSetWhenConfirming()
    {
        $this->request->setReturnUrl(null);
        $data = $this->request->getData();
    }

    /**
     * If not confirming automatically, don't set the return url.
     */
    public function testReturnUrlNotInData()
    {
        $this->request->setConfirm(false);
        $data = $this->request->getData();
        $this->assertArrayNotHasKey('return_url', $data);
    }

    public function testSendSuccessAndRequireConfirmation()
    {
        $this->setMockHttpResponse('AuthorizeSuccess.txt');
        /** @var PaymentIntentsResponse $response */
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertTrue($response->requiresConfirmation());
        $this->assertSame('manual', $response->getCaptureMethod());
        $this->assertSame('pm_1Euf5RFSbr6xR4YAwZ5fP28B', $response->getCardReference());
        $this->assertSame('req_8PDHeZazN2LwML', $response->getRequestId());
        $this->assertSame('cus_F1UMEEnT2OBgMg', $response->getCustomerReference());
        $this->assertNull($response->getMessage());
    }

    public function testSendError()
    {
        $this->setMockHttpResponse('AuthorizeFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getCardReference());
        $this->assertSame('No such payment_method: pm_invalid_method', $response->getMessage());
    }
}
