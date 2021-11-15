<?php

namespace Academe\AuthorizeNet\ServerRequest\Payload;

/**
 *
 */

use PHPUnit\Framework\TestCase;

use GuzzleHttp\Exception\ClientException;
use Academe\AuthorizeNet\Response\Model\TransactionResponse;

class PaymentTest extends TestCase
{
    public function setUp()
    {
    }

    public function testCreate()
    {
        $data = json_decode('{
            "responseCode": 1,
            "authCode": "LZ6I19",
            "avsResponse": "Y",
            "authAmount": 45.00,
            "entityName": "transaction",
            "id": "60020981676"
        }', true);

        $payload = new Payment($data);

        $this->assertSame('transaction', $payload->entityName);
        $this->assertSame('60020981676', $payload->id);

        // The ID represents the original transId, so this will be aliased.
        $this->assertSame('60020981676', $payload->transId);

        $this->assertSame(TransactionResponse::RESPONSE_CODE_APPROVED, $payload->responseCode);
        $this->assertSame('LZ6I19', $payload->authCode);
        $this->assertSame('Y', $payload->avsResponse);
        $this->assertSame(45.00, $payload->authAmount);

        $this->assertArraySubset($data, $payload->toData(true));
    }

    /**
     * Test that additonal values this library does not yet
     * know about are accessible.
     */
    public function testAdditionalParams()
    {
        $data = json_decode('{
            "responseCode": 1,
            "authCode": "LZ6I19",
            "avsResponse": "Y",
            "authAmount": 45.00,
            "entityName": "transaction",
            "id": "60020981676",
            "foo": 123,
            "bar": "xyz"
        }', true);

        $payload = new Payment($data);

        // Normal data.
        $this->assertSame('transaction', $payload->entityName);
        $this->assertSame('60020981676', $payload->id);

        // New fields "foo" and "bar".
        $this->assertSame(123, $payload->getDataValue('foo'));
        $this->assertSame('xyz', $payload->getDataValue('bar'));
    }
}
