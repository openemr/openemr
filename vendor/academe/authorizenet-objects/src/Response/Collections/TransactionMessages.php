<?php

namespace Academe\AuthorizeNet\Response\Collections;

/**
 * Collection of response messages, with an overall result code.
 */

use Academe\AuthorizeNet\Request\Model\HostedPaymentSetting;
use Academe\AuthorizeNet\Response\Model\TransactionMessage;
use Academe\AuthorizeNet\Response\HasDataTrait;
use Academe\AuthorizeNet\AbstractCollection;

class TransactionMessages extends AbstractCollection
{
    use HasDataTrait;

    /**
     * @param array $data Array of transaction messages data.
     */
    public function __construct(array $data = [])
    {
        $this->setData($data);

        foreach ($this->getData() as $message_data) {
            $this->push(new TransactionMessage($message_data));
        }
    }

    protected function hasExpectedStrictType($item)
    {
        // Make sure the item is the correct type, and is not empty.
        return $item instanceof TransactionMessage;
    }
}
