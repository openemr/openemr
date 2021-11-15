<?php

namespace Academe\AuthorizeNet\ServerRequest;

/**
 * The payload detail of a notification sent by a webhook.
 */

use Academe\AuthorizeNet\Response\HasDataTrait;
use Academe\AuthorizeNet\AbstractModel;

// This may end up being an interface and a trait, used to
// extend the non-notification response nodels.
// Or maybe the payloads are simple enough to extend them
// from a simple abstract.

abstract class AbstractPayload extends AbstractModel
{
    use HasDataTrait;

    /**
     * The contents of the payload.
     */
    const ENTITY_NAME_TRANSACTION = 'transaction';
    const ENTITY_NAME_CUSTOMERPROFILE = 'customerProfile';
    const ENTITY_NAME_CUSTOMERPAYMENTPROFILE = 'customerPaymentProfile';
    const ENTITY_NAME_SUBSCRIPTION = 'subscription';

    /**
     * The original ID that the id represents.
     */
    const ID_NAME_TRANSACTION = 'transId';
    const ID_NAME_CUSTOMERPROFILE = 'customerProfileId';
    const ID_NAME_CUSTOMERPAYMENTPROFILE = 'customerPaymentProfileId';
    const ID_NAME_SUBSCRIPTION = 'subscriptionId';

    /**
     * Two properties shared by all payloads.
     */
    protected $entityName;
    protected $id;

    public function __construct($data)
    {
        $this->setData($data);

        $this->setEntityName($this->getDataValue('entityName'));
        $this->setId($this->getDataValue('id'));
    }

    public function setEntityName($value)
    {
        $this->entityName = $value;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function jsonSerialize()
    {
        $data = [
            'entityName' => $this->entityName,
            'id' => $this->id,
        ];

        return $data;
    }
}
