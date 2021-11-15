<?php

namespace Omnipay\Stripe;

use Omnipay\Tests\GatewayTestCase;

/**
 * @property Gateway gateway
 */
class ChargeGatewayTest extends GatewayTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());
    }

    public function testAuthorize()
    {
        $request = $this->gateway->authorize(array('amount' => '10.00'));

        $this->assertInstanceOf('Omnipay\Stripe\Message\AuthorizeRequest', $request);
        $this->assertSame('10.00', $request->getAmount());
    }

    public function testCapture()
    {
        $request = $this->gateway->capture(array('amount' => '10.00'));

        $this->assertInstanceOf('Omnipay\Stripe\Message\CaptureRequest', $request);
        $this->assertSame('10.00', $request->getAmount());
    }

    public function testPurchase()
    {
        $request = $this->gateway->purchase(array('amount' => '10.00'));

        $this->assertInstanceOf('Omnipay\Stripe\Message\PurchaseRequest', $request);
        $this->assertSame('10.00', $request->getAmount());
    }

    public function testRefund()
    {
        $request = $this->gateway->refund(array('amount' => '10.00'));

        $this->assertInstanceOf('Omnipay\Stripe\Message\RefundRequest', $request);
        $this->assertSame('10.00', $request->getAmount());
    }

    public function testVoid()
    {
        $request = $this->gateway->void();

        $this->assertInstanceOf('Omnipay\Stripe\Message\VoidRequest', $request);
    }

    public function testFetchTransaction()
    {
        $request = $this->gateway->fetchTransaction(array());

        $this->assertInstanceOf('Omnipay\Stripe\Message\FetchTransactionRequest', $request);
    }

    public function testFetchBalanceTransaction()
    {
        $request = $this->gateway->fetchBalanceTransaction(array());

        $this->assertInstanceOf('Omnipay\Stripe\Message\FetchBalanceTransactionRequest', $request);
    }

    public function testFetchToken()
    {
        $request = $this->gateway->fetchToken(array());

        $this->assertInstanceOf('Omnipay\Stripe\Message\FetchTokenRequest', $request);
    }

    public function testCreateToken()
    {
        $request = $this->gateway->createToken(array('customer' => 'cus_foo'));

        $this->assertInstanceOf('Omnipay\Stripe\Message\CreateTokenRequest', $request);
        $params = $request->getParameters();
        $this->assertSame('cus_foo', $params['customer']);
    }

    public function testCreateCard()
    {
        $request = $this->gateway->createCard(array('description' => 'foo'));

        $this->assertInstanceOf('Omnipay\Stripe\Message\CreateCardRequest', $request);
        $this->assertSame('foo', $request->getDescription());
    }

    public function testUpdateCard()
    {
        $request = $this->gateway->updateCard(array('cardReference' => 'cus_1MZSEtqSghKx99'));

        $this->assertInstanceOf('Omnipay\Stripe\Message\UpdateCardRequest', $request);
        $this->assertSame('cus_1MZSEtqSghKx99', $request->getCardReference());
    }

    public function testDeleteCard()
    {
        $request = $this->gateway->deleteCard(array('cardReference' => 'cus_1MZSEtqSghKx99'));

        $this->assertInstanceOf('Omnipay\Stripe\Message\DeleteCardRequest', $request);
        $this->assertSame('cus_1MZSEtqSghKx99', $request->getCardReference());
    }

    public function testCreateCustomer()
    {
        $request = $this->gateway->createCustomer(array('description' => 'foo@foo.com'));

        $this->assertInstanceOf('Omnipay\Stripe\Message\CreateCustomerRequest', $request);
        $this->assertSame('foo@foo.com', $request->getDescription());
    }

    public function testFetchCustomer()
    {
        $request = $this->gateway->fetchCustomer(array('customerReference' => 'cus_1MZSEtqSghKx99'));
        $this->assertInstanceOf('Omnipay\Stripe\Message\FetchCustomerRequest', $request);
        $this->assertSame('cus_1MZSEtqSghKx99', $request->getCustomerReference());
    }

    public function testUpdateCustomer()
    {
        $request = $this->gateway->updateCustomer(array('customerReference' => 'cus_1MZSEtqSghKx99'));

        $this->assertInstanceOf('Omnipay\Stripe\Message\UpdateCustomerRequest', $request);
        $this->assertSame('cus_1MZSEtqSghKx99', $request->getCustomerReference());
    }

    public function testDeleteCustomer()
    {
        $request = $this->gateway->deleteCustomer(array('customerReference' => 'cus_1MZSEtqSghKx99'));

        $this->assertInstanceOf('Omnipay\Stripe\Message\DeleteCustomerRequest', $request);
        $this->assertSame('cus_1MZSEtqSghKx99', $request->getCustomerReference());
    }

    public function testCreatePlan()
    {
        $request = $this->gateway->createPlan(array('id' => 'basic'));

        $this->assertInstanceOf('Omnipay\Stripe\Message\CreatePlanRequest', $request);
        $this->assertSame('basic', $request->getId());
    }

    public function testFetchPlan()
    {
        $request = $this->gateway->fetchPlan(array('id' => 'basic'));

        $this->assertInstanceOf('Omnipay\Stripe\Message\FetchPlanRequest', $request);
        $this->assertSame('basic', $request->getId());
    }

    public function testDeletePlan()
    {
        $request = $this->gateway->deletePlan(array('id' => 'basic'));

        $this->assertInstanceOf('Omnipay\Stripe\Message\DeletePlanRequest', $request);
        $this->assertSame('basic', $request->getId());
    }

    public function testListPlans()
    {
        $request = $this->gateway->listPlans(array());

        $this->assertInstanceOf('Omnipay\Stripe\Message\ListPlansRequest', $request);
    }

    public function testCreateSubscription()
    {
        $request = $this->gateway->createSubscription(array('plan' => 'basic'));

        $this->assertInstanceOf('Omnipay\Stripe\Message\CreateSubscriptionRequest', $request);
        $this->assertSame('basic', $request->getPlan());
    }

    public function testFetchSubscription()
    {
        $request = $this->gateway->fetchSubscription(array(
            'customerReference' => 'cus_1MZSEtqZghix99',
            'subscriptionReference' => 'sub_1mokfidgjdidf'
        ));

        $this->assertInstanceOf('Omnipay\Stripe\Message\FetchSubscriptionRequest', $request);
        $this->assertSame('cus_1MZSEtqZghix99', $request->getCustomerReference());
        $this->assertSame('sub_1mokfidgjdidf', $request->getSubscriptionReference());
    }

    public function testUpdateSubscription()
    {
        $request = $this->gateway->updateSubscription(array('subscriptionReference' => 'sub_1mokfidgjdidf'));

        $this->assertInstanceOf('Omnipay\Stripe\Message\UpdateSubscriptionRequest', $request);
        $this->assertSame('sub_1mokfidgjdidf', $request->getSubscriptionReference());
    }

    public function testCancelSubscription()
    {
        $request = $this->gateway->cancelSubscription(array(
            'customerReference' => 'cus_1MZSEtqZghix99',
            'subscriptionReference' => 'sub_1mokfidgjdidf'
        ));

        $this->assertInstanceOf('Omnipay\Stripe\Message\CancelSubscriptionRequest', $request);
        $this->assertSame('cus_1MZSEtqZghix99', $request->getCustomerReference());
        $this->assertSame('sub_1mokfidgjdidf', $request->getSubscriptionReference());
    }

    public function testFetchEvent()
    {
        $request = $this->gateway->fetchEvent(array('eventReference' => 'evt_17X23UCryC4r2g4vdolh6muI'));

        $this->assertInstanceOf('Omnipay\Stripe\Message\FetchEventRequest', $request);
        $this->assertSame('evt_17X23UCryC4r2g4vdolh6muI', $request->getEventReference());
    }

    public function testFetchInvoiceLines()
    {
        $request = $this->gateway->fetchInvoiceLines(array('invoiceReference' => 'in_17ZPbRCryC4r2g4vIdAFxptK'));

        $this->assertInstanceOf('Omnipay\Stripe\Message\FetchInvoiceLinesRequest', $request);
        $this->assertSame('in_17ZPbRCryC4r2g4vIdAFxptK', $request->getInvoiceReference());
    }

    public function testFetchInvoice()
    {
        $request = $this->gateway->fetchInvoice(array('invoiceReference' => 'in_17ZPbRCryC4r2g4vIdAFxptK'));

        $this->assertInstanceOf('Omnipay\Stripe\Message\FetchInvoiceRequest', $request);
        $this->assertSame('in_17ZPbRCryC4r2g4vIdAFxptK', $request->getInvoiceReference());
    }

    public function testListInvoices()
    {
        $request = $this->gateway->listInvoices(array());

        $this->assertInstanceOf('Omnipay\Stripe\Message\ListInvoicesRequest', $request);
    }

    public function testCreateInvoiceItem()
    {
        $request = $this->gateway->createInvoiceItem(array('invoiceItemReference' => 'ii_17ZPbRCryC4r2g4vIdAFxptK'));

        $this->assertInstanceOf('Omnipay\Stripe\Message\CreateInvoiceItemRequest', $request);
        $this->assertSame('ii_17ZPbRCryC4r2g4vIdAFxptK', $request->getInvoiceItemReference());
    }

    public function testFetchInvoiceItem()
    {
        $request = $this->gateway->fetchInvoiceItem(array('invoiceItemReference' => 'ii_17ZPbRCryC4r2g4vIdAFxptK'));

        $this->assertInstanceOf('Omnipay\Stripe\Message\FetchInvoiceItemRequest', $request);
        $this->assertSame('ii_17ZPbRCryC4r2g4vIdAFxptK', $request->getInvoiceItemReference());
    }

    public function testDeleteInvoiceItem()
    {
        $request = $this->gateway->deleteInvoiceItem(array('invoiceItemReference' => 'ii_17ZPbRCryC4r2g4vIdAFxptK'));

        $this->assertInstanceOf('Omnipay\Stripe\Message\DeleteInvoiceItemRequest', $request);
        $this->assertSame('ii_17ZPbRCryC4r2g4vIdAFxptK', $request->getInvoiceItemReference());
    }
}
