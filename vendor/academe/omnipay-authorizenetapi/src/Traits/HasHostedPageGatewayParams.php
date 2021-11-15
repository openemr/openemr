<?php

namespace Omnipay\AuthorizeNetApi\Traits;

/**
 * Gateway setters and getters for hosted page only.
 */

trait HasHostedPageGatewayParams
{
    /**
     * Used only by the hosted payment page at this time.
     */
    public function setCancelUrl($value)
    {
        $this->setParameter('cancelUrl', $value);
    }

    /**
     * Used only by the hosted payment page at this time.
     */
    public function setReturnUrl($value)
    {
        $this->setParameter('returnUrl', $value);
    }
}
