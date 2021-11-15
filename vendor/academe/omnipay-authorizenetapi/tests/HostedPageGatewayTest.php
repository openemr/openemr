<?php

namespace Omnipay\AuthorizeNetApi;

use Omnipay\Tests\GatewayTestCase;

class HostedPageGatewayTest extends GatewayTestCase
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
}
