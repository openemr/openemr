<?php

/**
 * Stripe List Transfers Request (Connect only).
 */

namespace Omnipay\Stripe\Message\Transfers;

use Omnipay\Stripe\Message\AbstractRequest;

/**
 * Stripe List Transfers Request.
 *
 * Returns a list of existing transfers sent to connected accounts. The
 * transfers are returned in sorted order, with the most recently created
 * transfers appearing first.
 *
 * @link https://stripe.com/docs/api#list_transfers
 */
class ListTransfersRequest extends AbstractRequest
{
    /**
     * @return mixed
     */
    public function getLimit()
    {
        return $this->getParameter('limit');
    }

    /**
     * @param string $value
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setLimit($value)
    {
        return $this->setParameter('limit', $value);
    }

    /**
     * @return mixed
     */
    public function getDestination()
    {
        return $this->getParameter('destination');
    }

    /**
     * @param string $value
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setDestination($value)
    {
        return $this->setParameter('destination', $value);
    }

    /**
     * Connect only
     *
     * @return mixed
     */
    public function getTransferGroup()
    {
        return $this->getParameter('transferGroup');
    }

    /**
     * @param string $value
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setTransferGroup($value)
    {
        return $this->setParameter('transferGroup', $value);
    }

    /**
     * A cursor for use in pagination. `ending_before` is an object ID that defines your place in the list.
     * For instance, if you make a list request and receive 100 objects, starting with `obj_bar`, your
     * subsequent call can include `ending_before=obj_ba`r in order to fetch the previous page of the list.
     *
     * @return mixed
     */
    public function getEndingBefore()
    {
        return $this->getParameter('endingBefore');
    }

    /**
     * @param string $value
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setEndingBefore($value)
    {
        return $this->setParameter('endingBefore', $value);
    }

    /**
     * A cursor for use in pagination. `starting_after` is an object ID that defines your place in the list.
     * For instance, if you make a list request and receive 100 objects, ending with `obj_foo`, your
     * subsequent call can include `starting_after=obj_foo` in order to fetch the next page of the list.
     *
     * @return mixed
     */
    public function getStartingAfter()
    {
        return $this->getParameter('startingAfter');
    }

    /**
     * @param string $value
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setStartingAfter($value)
    {
        return $this->setParameter('startingAfter', $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $data = array();

        if ($this->getLimit()) {
            $data['limit'] = $this->getLimit();
        }

        if ($this->getDestination()) {
            $data['destination'] = $this->getDestination();
        }

        if ($this->getTransferGroup()) {
            $data['transfer_group'] = $this->getTransferGroup();
        }

        if ($this->getEndingBefore()) {
            $data['ending_before'] = $this->getEndingBefore();
        }

        if ($this->getStartingAfter()) {
            $data['starting_after'] = $this->getStartingAfter();
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getEndpoint()
    {
        return $this->endpoint.'/transfers';
    }

    /**
     * {@inheritdoc}
     */
    public function getHttpMethod()
    {
        return 'GET';
    }
}
