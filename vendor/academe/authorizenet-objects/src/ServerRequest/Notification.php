<?php

namespace Academe\AuthorizeNet\ServerRequest;

/**
 * The notification message sent by a webhook.
 */

use Academe\AuthorizeNet\Response\HasDataTrait;
use Academe\AuthorizeNet\AbstractModel;
use Academe\AuthorizeNet\ServerRequest\AbstractPayload;
use Academe\AuthorizeNet\ServerRequest\Payload\CustomerPaymentProfile;
use Academe\AuthorizeNet\ServerRequest\Payload\Fraud;
use Academe\AuthorizeNet\ServerRequest\Payload\Payment;
use Academe\AuthorizeNet\ServerRequest\Payload\Subscription;
use Academe\AuthorizeNet\ServerRequest\Payload\CustomerProfile;
use Academe\AuthorizeNet\ServerRequest\Payload\Unknown;

class Notification extends AbstractModel
{
    use HasDataTrait;

    const EVENT_NAMESPACE = 'net.authorize';

    /**
     * The event name prefix indicates the payload type.
     * Note that some prefixes are subsets of others, so be
     * careful what order they are checked.
     */
    const EVENT_TARGET_PAYMENT          = 'payment';
    const EVENT_TARGET_CUSTOMER         = 'customer';

    const EVENT_SUBTARGET_FRAUD = 'fraud';
    const EVENT_SUBTARGET_AUTHORIZATION = 'authorization';
    const EVENT_SUBTARGET_AUTHCAPTURE = 'authcapture';
    const EVENT_SUBTARGET_CAPTURE = 'capture';
    const EVENT_SUBTARGET_REFUND = 'refund';
    const EVENT_SUBTARGET_PRIORAUTHCAPTURE = 'priorAuthCapture';
    const EVENT_SUBTARGET_VOID = 'void';

    const EVENT_SUBTARGET_PAYMENTPROFILE = 'paymentProfile';
    const EVENT_SUBTARGET_SUBSCRIPTION = 'subscription';

    /**
     * A list of event actions we know about.
     */
    const EVENT_ACTION_CREATED = 'created';
    const EVENT_ACTION_UPDATED = 'updated';
    const EVENT_ACTION_DELETED = 'deleted';
    const EVENT_ACTION_SUSPENDED = 'suspended';
    const EVENT_ACTION_TERMINATED = 'terminated';
    const EVENT_ACTION_CANCELLED = 'cancelled';
    const EVENT_ACTION_EXPIRING = 'expiring';
    const EVENT_ACTION_HELD = 'held';
    const EVENT_ACTION_APPROVED = 'approved';
    const EVENT_ACTION_DECLINED = 'declined';

    protected $notificationId;
    protected $eventType;
    protected $eventDate;
    protected $webhookId;
    protected $payload;

    protected $eventTarget;
    protected $eventSubtarget;
    protected $eventAction;

    // TODO: the deliberyStatus and retryLog is not a part of the
    // webhook notifications, but the REST API for managing and
    // reporting on the webhooks. Move it there when the management
    // API is done.
    //
    // Fetching past notifications returns the deliveryStatus,
    // racking whether the merchant site has received this
    // notification. Also the retryLog.
    // The past notifications do not include the original payload,
    // unless the delivery status shows it has failed to be delivered.
    //protected $deliveryStatus; // Failed, Delivered and ??? (maybe not visible until one or the other)
    //protected $retryLog;

    /**
     * Feed in the raw data structure (array or nested objects).
     */
    public function __construct($data)
    {
        $this->setData($data);

        $this->setNotificationId($this->getDataValue('notificationId'));
        $this->setEventType($this->getDataValue('eventType'));
        $this->setEventDate($this->getDataValue('eventDate'));
        $this->setWebhookId($this->getDataValue('webhookId'));

        $eventType = $this->eventType;

        // Parse the eventType.
        // We split it up into the following parts:
        //    {namespace}.{target}[.{sub-target}].{action}
        // The namespace is discarded, and the remainder are used
        // to determine the payload class.

        // Strip off the namespace prefix if it is present.
        // If not present, we may want to throw an exception
        // or fall back to a default 'unknown' payload.

        if (strpos($eventType, static::EVENT_NAMESPACE) === 0) {
            $arr = explode('.', $eventType);

            foreach (explode('.', static::EVENT_NAMESPACE) as $i) {
                array_shift($arr);
            }

            // The main target is the section after the prefix.

            $this->eventTarget = array_shift($arr);

            // The action being notified is the last part of the event type.

            $this->eventAction = array_pop($arr);

            // The sub-target is what's left, which should be one or zero
            // elements, i.e. optional.

            $this->eventSubtarget = implode('.', $arr);
        }

        if ($payload = $this->getDataValue('payload')) {
            if ($this->eventTarget === static::EVENT_TARGET_PAYMENT) {
                if ($this->eventSubtarget === static::EVENT_SUBTARGET_FRAUD) {
                    // Notification of a payment fraud.
                    $this->setPayload(new Fraud($payload));
                } else {
                    // Notification of a payment (any other sub-target).
                    $this->setPayload(new Payment($payload));
                }
            } elseif ($this->eventTarget === static::EVENT_TARGET_CUSTOMER) {
                if ($this->eventSubtarget === static::EVENT_SUBTARGET_PAYMENTPROFILE) {
                    // Notification of a customer payment profile change.
                    $this->setPayload(new CustomerPaymentProfile($payload));
                } elseif ($this->eventSubtarget === static::EVENT_SUBTARGET_SUBSCRIPTION) {
                    // Notification of a change to a customer subscription.
                    $this->setPayload(new Subscription($payload));
                } else {
                    // Notification of a change to a customer profile.
                    $this->setPayload(new CustomerProfile($payload));
                }
            } else {
                // Fall back to a default payload.
                $this->setPayload(new Unknown($payload));
            }
        }
    }

    public function jsonSerialize()
    {
        $data = [
            'notificationId' => $this->notificationId,
            'eventType' => $this->eventType,
            'eventDate' => $this->eventDate,
            'webhookId' => $this->webhookId,
        ];

        if ($this->payload) {
            $data['payload'] = $this->payload;
        }

        return $data;
    }

    protected function setNotificationId($value)
    {
        $this->notificationId = $value;
    }

    protected function setEventType($value)
    {
        $this->eventType = $value;
    }

    /**
     * Example: 2017-10-22T15:09:49.0609961Z
     */
    protected function setEventDate($value)
    {
        $this->eventDate = $value;
    }

    protected function setWebhookId($value)
    {
        $this->webhookId = $value;
    }

    protected function setPayload(AbstractPayload $value)
    {
        $this->payload = $value;
    }

    protected function getEventTarget()
    {
        return $this->eventTarget;
    }

    protected function getEventSubtarget()
    {
        return $this->eventSubtarget;
    }

    protected function getEventAction()
    {
        return $this->eventAction;
    }
}
