<?php

namespace Academe\AuthorizeNet\ServerRequest\Payload;

/**
 *
 */

use PHPUnit\Framework\TestCase;

use GuzzleHttp\Exception\ClientException;
use Academe\AuthorizeNet\Response\Model\TransactionResponse;
use Academe\AuthorizeNet\ServerRequest\Model\Profile;

class SubscriptionTest extends TestCase
{
    public function setUp()
    {
    }

    public function testCreate()
    {
        $data = json_decode('{
            "entityName": "subscription",
            "id": "1405",
            "name": "testSubscription",
            "amount": 23,
            "status": "active",
            "profile": {
                "customerProfileId": 348,
                "customerPaymentProfileId": 644,
                "customerShippingAddressId": 675
            }
        }', true);

        $payload = new Subscription($data);

        $this->assertSame('1405', $payload->id);

        // The ID represents the original subscriptionId, so this will be aliased.
        $this->assertSame('1405', $payload->subscriptionId);

        $this->assertSame('testSubscription', $payload->name);

        // The amount is unfortunately a floating point number, which
        // arraives as an integer when there are a whole number of
        // major units.

        $this->assertSame(23, $payload->amount);

        $this->assertSame('active', $payload->status);

        $this->assertInstanceOf(Profile::class, $payload->profile);

        $this->assertArraySubset($data, $payload->toData(true));
    }
}
