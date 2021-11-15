<?php

namespace Omnipay\AuthorizeNetApi\Message;

use Omnipay\Tests\TestCase;
use Omnipay\Common\CreditCard;
use Academe\AuthorizeNet\Request\Model\NameAddress;
use Omnipay\AuthorizeNetApi\ApiGateway;

class AuthorizeRequestTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->gateway = new ApiGateway(
            $this->getHttpClient(),
            $this->getHttpRequest()
        );

        $this->gateway->setAuthName('authName');
        $this->gateway->setTransactionKey('transactionKey');
        $this->gateway->setRefId('refId');
    }

    public function testOpaqueData()
    {
        $opaqueDescriptor = 'COMMON.ACCEPT.INAPP.PAYMENT';
        $opaqueValue = str_shuffle(str_repeat('1234567890ABCDEFGHIJ', 10));

        $cardToken = $opaqueDescriptor . ':' . $opaqueValue;

        $request = $this->gateway->authorize([
            'opaqueDataDescriptor' => $opaqueDescriptor,
            'opaqueDataValue' => $opaqueValue,
        ]);

        $this->assertSame($cardToken, $request->getToken());

        $this->assertSame($opaqueDescriptor, $request->getOpaqueDataDescriptor());
        $this->assertSame($opaqueValue, $request->getOpaqueDataValue());
    }

    public function testCardToken()
    {
        $opaqueDescriptor = 'COMMON.ACCEPT.INAPP.PAYMENT';
        $opaqueValue = str_shuffle(str_repeat('1234567890ABCDEFGHIJ', 10));

        $cardToken = $opaqueDescriptor . ':' . $opaqueValue;

        $request = $this->gateway->authorize([
            'token' => $cardToken,
        ]);

        $this->assertSame($cardToken, $request->getToken());

        $this->assertSame($opaqueDescriptor, $request->getOpaqueDataDescriptor());
        $this->assertSame($opaqueValue, $request->getOpaqueDataValue());
    }

    public function testCustomerData()
    {
        $request = $this->gateway->authorize([
            'amount' => 1.23,
            'customerId' => 'customerId',
            'customerType' => 'individual',
            'customerTaxId' => 'customerTaxId',
            'customerDriversLicense' => 'customerDriversLicense',
            'card' => new CreditCard([
                'email' => 'email@example.com',
            ]),
        ]);

        // The request data will have a customer object with this data in.

        $this->assertArraySubset(
            [
                'id' => 'customerId',
                'type' => 'individual',
                'email' => 'email@example.com',
                'driversLicense' => 'customerDriversLicense',
                'taxId' => 'customerTaxId',
            ],
            $request->getData()->getCustomer()->jsonSerialize()
        );
    }

    /**
     * If there is no address information, then don't send empty
     * address objects to the gateway; just suppress them.
     */
    public function testAddressSetNotSet()
    {
        $request = $this->gateway->authorize([
            'amount' => 1.23,
            'card' => new CreditCard([
            ]),
        ]);

        $this->assertNull($request->getData()->getBillTo());
        $this->assertNull($request->getData()->getShipTo());

        $request = $this->gateway->authorize([
            'amount' => 1.23,
            'card' => new CreditCard([
                'billingAddress1' => 'Street Number',
                'shippingCity' => 'City',
            ]),
        ]);

        $this->assertInstanceOf(
            NameAddress::class,
            $request->getData()->getBillTo()
        );

        $this->assertInstanceOf(
            NameAddress::class,
            $request->getData()->getShipTo()
        );
    }
}
