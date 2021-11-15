<?php
/**
 * CreateSourceRequest
 */
namespace Omnipay\Stripe\Message;

use Omnipay\Common\Exception\InvalidRequestException;

/**
 * Class CreateSourceRequest
 *
 * TODO : Add docblock
 */
class CreateSourceRequest extends AbstractRequest
{
    /**
     * Get the request secure flag. This is a boolean flag to indicate
     * whether 3-D secure is required for this source or not
     *
     * @return mixed
     */
    public function getSecure()
    {
        return $this->getParameter('secure');
    }

    /**
     * Set the request secure flag. This is a boolean flag to indicate
     * whether 3-D secure is required for this source or not
     *
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setSecure($value)
    {
        return $this->setParameter('secure', $value);
    }

    /**
     * Get the secure redirect url where the user will be redirected back after OTP verification
     *
     * @return string
     */
    public function getSecureRedirectUrl()
    {
        return $this->getParameter('secureRedirectUrl');
    }

    /**
     * Set the secure redirect url where the user will be redirected back after OTP verification
     *
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest]
     */
    public function setSecureRedirectUrl($value)
    {
        return $this->setParameter('secureRedirectUrl', $value);
    }

    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return mixed
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $data['amount']   = $this->getAmountInteger();
        $data['currency'] = strtolower($this->getCurrency());

        if ($this->getSecure()) {
            $data['type']                   = 'three_d_secure';
            $data['three_d_secure']['card'] = $this->getSource();
            $data['redirect']['return_url'] = $this->getSecureRedirectUrl();
        } elseif ($card = $this->getCard()) {
            $data['type']              = 'card';
            $data['card']['number']    = $card->getNumber();
            $data['card']['exp_month'] = $card->getExpiryMonth();
            $data['card']['exp_year']  = $card->getExpiryYear();
            if ($card->getCvv()) {
                $data['card']['cvc'] = $card->getCvv();
            }
            $data['owner']['email'] = $card->getEmail();
            $data['owner']['name']  = $card->getName();
        }

        return $data;
    }

    /**
     * @inheritdoc
     *
     * @return string The endpoint for the create token request.
     */
    public function getEndpoint()
    {
        return $this->endpoint . '/sources';
    }
}
