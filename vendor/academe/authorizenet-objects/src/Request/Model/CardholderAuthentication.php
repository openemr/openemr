<?php

namespace Academe\AuthorizeNet\Request\Model;

/**
 *
 */

use Academe\AuthorizeNet\TransactionRequestInterface;
use Academe\AuthorizeNet\AbstractModel;

class CardholderAuthentication extends AbstractModel
{
    protected $authenticationIndicator;
    protected $cardholderAuthenticationValue;

    public function __construct($authenticationIndicator = null, $cardholderAuthenticationValue = null)
    {
        parent::__construct();

        $this->setAuthenticationIndicator($authenticationIndicator);
        $this->setCardholderAuthenticationValue($cardholderAuthenticationValue);
    }

    public function hasAny()
    {
        return $this->hasAuthenticationIndicator() || $this->hasCardholderAuthenticationValue();
    }

    /**
     * FIXME: the documentation says that special characters in both these strings
     * must be URL encoded. The API example code does not give any examples of this,
     * so it's unclear if it really needs to be done.
     */
    public function jsonSerialize()
    {
        $data = [];

        if ($this->hasAuthenticationIndicator()) {
            $data['authenticationIndicator'] = $this->getAuthenticationIndicator();
        }

        if ($this->hasCardholderAuthenticationValue()) {
            $data['cardholderAuthenticationValue'] = $this->getCardholderAuthenticationValue();
        }

        return $data;
    }

    protected function setAuthenticationIndicator($value)
    {
        $this->authenticationIndicator = $value;
    }

    protected function setCardholderAuthenticationValue($value)
    {
        $this->cardholderAuthenticationValue = $value;
    }
}
