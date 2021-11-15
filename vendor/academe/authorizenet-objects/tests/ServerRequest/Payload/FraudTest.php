<?php

namespace Academe\AuthorizeNet\ServerRequest\Payload;

/**
 *
 */

use PHPUnit\Framework\TestCase;

use GuzzleHttp\Exception\ClientException;
use Academe\AuthorizeNet\Response\Model\TransactionResponse;

class FraudTest extends TestCase
{
    public function setUp()
    {
    }

    public function testCreate()
    {
        $data = json_decode('{
            "responseCode": 4,
            "authCode": "24904A",
            "avsResponse": "Y",
            "authAmount": 50000.0,
            "fraudList": [
                {
                    "fraudFilter": "AmountFilter",
                    "fraudAction": "authAndHold"
                }
            ],
            "entityName": "transaction",
            "id": "2154067719"
        }', true);

        $payload = new Fraud($data);

        $this->assertSame('transaction', $payload->entityName);
        $this->assertSame('2154067719', $payload->id);

        // The ID represents the original transId, so this will be aliased.
        $this->assertSame('2154067719', $payload->transId);

        $this->assertSame(TransactionResponse::RESPONSE_CODE_PENDING, $payload->responseCode);
        $this->assertSame('24904A', $payload->authCode);
        $this->assertSame('Y', $payload->avsResponse);
        $this->assertSame(50000.00, $payload->authAmount);

        $this->assertArraySubset($data, $payload->toData(true));
    }
}
