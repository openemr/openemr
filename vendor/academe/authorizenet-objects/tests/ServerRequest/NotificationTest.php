<?php

namespace Academe\AuthorizeNet\ServerRequest;

/**
 * The notification webhook.
 */

use PHPUnit\Framework\TestCase;

use GuzzleHttp\Exception\ClientException;
use Academe\AuthorizeNet\Response\Model\TransactionResponse;
use Academe\AuthorizeNet\ServerRequest\Payload\Payment;
use Academe\AuthorizeNet\ServerRequest\Payload\CustomerPaymentProfile;
use Academe\AuthorizeNet\ServerRequest\Payload\Fraud;
use Academe\AuthorizeNet\ServerRequest\Payload\Subscription;
use Academe\AuthorizeNet\ServerRequest\Payload\CustomerProfile;
use Academe\AuthorizeNet\ServerRequest\Payload\Unknown;

class NotificationTest extends TestCase
{
    public function setUp()
    {
    }

    public function testCreatePayment()
    {
        $data = json_decode('{
            "notificationId": "d0e8e7fe-c3e7-4add-a480-27bc5ce28a18",
            "eventType": "net.authorize.payment.authcapture.created",
            "eventDate": "2017-03-29T20:48:02.0080095Z",
            "webhookId": "63d6fea2-aa13-4b1d-a204-f5fbc15942b7",
            "payload": {
                "responseCode": 1,
                "authCode": "LZ6I19",
                "avsResponse": "Y",
                "authAmount": 45.00,
                "entityName": "transaction",
                "id": "60020981676"
            }
        }', true);

        $notification = new Notification($data);

        $this->assertSame('d0e8e7fe-c3e7-4add-a480-27bc5ce28a18', $notification->notificationId);
        $this->assertSame('net.authorize.payment.authcapture.created', $notification->eventType);
        $this->assertSame('2017-03-29T20:48:02.0080095Z', $notification->eventDate);
        $this->assertSame('63d6fea2-aa13-4b1d-a204-f5fbc15942b7', $notification->webhookId);

        $this->assertInstanceOf(Payment::class, $notification->payload);

        $this->assertSame($notification::EVENT_TARGET_PAYMENT, $notification->eventTarget);
        $this->assertSame($notification::EVENT_SUBTARGET_AUTHCAPTURE, $notification->eventSubtarget);
        $this->assertSame($notification::EVENT_ACTION_CREATED, $notification->eventAction);

        $this->assertArraySubset($data, $notification->toData(true));
    }

    public function testCreateFraud()
    {
        $data = json_decode('{
            "notificationId": "26024a6c-3b78-4d18-8ce7-53dd020aee72",
            "eventType": "net.authorize.payment.fraud.held",
            "eventDate": "2016-10-24T17:47:39.7740424Z",
            "webhookId": "71400fce-085f-46fe-9758-8311ca01d33e",
            "payload": {
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
            }
        }', true);

        $notification = new Notification($data);

        $this->assertInstanceOf(Fraud::class, $notification->payload);

        $this->assertArraySubset($data, $notification->toData(true));
    }

    public function testCreatePaymentProfile()
    {
        $data = json_decode('{
            "notificationId": "7201C905-B01E-4622-B807-AC2B646A3815",
            "eventType": "net.authorize.customer.paymentProfile.created",
            "eventDate": "2016-03-23T06:19:09.5297562Z",
            "webhookId": "6239A0BE-D8F4-4A33-8FAD-901C02EED51F",
            "payload": {
                "customerProfileId": 394,
                "entityName": "customerPaymentProfile",
                "id": "694",
                "customerType": "business"
            }
        }', true);

        $notification = new Notification($data);

        $this->assertInstanceOf(CustomerPaymentProfile::class, $notification->payload);

        $this->assertArraySubset($data, $notification->toData(true));
    }

    public function testCreateSubscription()
    {
        $data = json_decode('{
            "notificationId": "35e4c150-1d64-40b3-ba52-9356ebecd3c7",
            "eventType": "net.authorize.customer.subscription.created",
            "eventDate": "2016-03-08T08:18:27.434Z",
            "webhookId": "873B7193-31FF-4881-9593-FA6578D52510",
            "payload": {
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
            }
        }', true);

        $notification = new Notification($data);

        $this->assertInstanceOf(Subscription::class, $notification->payload);

        $this->assertArraySubset($data, $notification->toData(true));
    }

    public function testCreateCustomerProfile()
    {
        $data = json_decode('{
            "notificationId": "5c3f7e00-1265-4e8e-abd0-a7d734163881",
            "eventType": "net.authorize.customer.created",
            "eventDate": "2016-03-23T05:23:06.5430555Z",
            "webhookId": "0b90f2e8-02ae-4d1d-b2e0-1bd167e60176",
            "payload": {
                "paymentProfiles": [{
                    "id": "694",
                    "customerType": "individual"
                }],
                "merchantCustomerId": "cust457",
                "description": "Profile created by Subscription: 1447",
                "entityName": "customerProfile",
                "id": "394"
            }
        }', true);

        $notification = new Notification($data);

        $this->assertInstanceOf(CustomerProfile::class, $notification->payload);

        $this->assertArraySubset($data, $notification->toData(true));
    }

    public function testUnknownEvent()
    {
        $data = json_decode('{
            "notificationId": "5c3f7e00-1265-4e8e-abd0-a7d734163881",
            "eventType": "net.authorize.foobar.stuff",
            "eventDate": "2016-03-23T05:23:06.5430555Z",
            "webhookId": "0b90f2e8-02ae-4d1d-b2e0-1bd167e60176",
            "payload": {
                "entityName": "foo",
                "id": "bar"
            }
        }', true);

        $notification = new Notification($data);

        $this->assertInstanceOf(Unknown::class, $notification->payload);

        $this->assertArraySubset($data, $notification->toData(true));
    }
}
