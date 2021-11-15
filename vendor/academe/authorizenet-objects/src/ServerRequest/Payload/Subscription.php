<?php

namespace Academe\AuthorizeNet\ServerRequest\Payload;

/**
 * The subscription notification payload.
 */

use Academe\AuthorizeNet\ServerRequest\Payload\Payment;
use Academe\AuthorizeNet\ServerRequest\Model\Profile;
use Academe\AuthorizeNet\ServerRequest\AbstractPayload;

class Subscription extends AbstractPayload
{
    protected $name;
    protected $amount;
    protected $status;

    protected $profile;

    public function __construct($data)
    {
        parent::__construct($data);

        $this->name = $this->getDataValue('name');
        $this->amount = $this->getDataValue('amount');
        $this->status = $this->getDataValue('status');

        $profile = $this->getDataValue('profile');

        if ($profile) {
            $this->profile = new Profile($profile);
        }
    }

    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();

        $data['name'] = $this->name;
        $data['amount'] = $this->amount;
        $data['status'] = $this->status;

        $data['profile'] = $this->profile;

        return $data;
    }

    /**
     * The subscriptionId is an alias for the id.
     */
    public function getSubscriptionId()
    {
        return $this->id;
    }

    /**
     *
     */
    protected function setName($value)
    {
        $this->name = $value;
    }

    /**
     *
     */
    protected function setAmount($value)
    {
        $this->amount = $value;
    }

    /**
     *
     */
    protected function setStatus($value)
    {
        $this->status = $value;
    }

    /**
     *
     */
    protected function setProfile(Profile $value)
    {
        $this->profile = $value;
    }
}
