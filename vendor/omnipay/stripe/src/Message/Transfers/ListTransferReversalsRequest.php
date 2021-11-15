<?php

/**
 * Stripe List Transfer Reversals Request (Connect only).
 */

namespace Omnipay\Stripe\Message\Transfers;

use Omnipay\Stripe\Message\AbstractRequest;

/**
 * Stripe List Transfer Reversals Request.
 *
 * You can see a list of the reversals belonging to a specific transfer.
 *
 * Note that the 10 most recent reversals are always available by default
 * on the transfer object. If you need more than those 10, you can use
 * this API method and the `limit` and `starting_after` parameters to
 * page through additional reversals.
 *
 * @link https://stripe.com/docs/api#list_transfer_reversals
 */
class ListTransferReversalsRequest extends AbstractRequest
{
    /**
     * @return mixed
     */
    public function getTransferReference()
    {
        return $this->getParameter('transferReference');
    }

    /**
     * @param string $value
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setTransferReference($value)
    {
        return $this->setParameter('transferReference', $value);
    }

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
        $this->validate('transferReference');

        $data = array();

        if ($this->getLimit()) {
            $data['limit'] = $this->getLimit();
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
        return $this->endpoint.'/transfers/'.$this->getTransferReference().'/reversals';
    }

    /**
     * {@inheritdoc}
     */
    public function getHttpMethod()
    {
        return 'GET';
    }
}
