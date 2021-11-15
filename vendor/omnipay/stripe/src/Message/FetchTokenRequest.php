<?php

/**
 * Stripe Fetch Token Request.
 */
namespace Omnipay\Stripe\Message;

/**
 * Stripe Fetch Token Request.
 *
 * Often you want to be able to charge credit cards or send payments
 * to bank accounts without having to hold sensitive card information
 * on your own servers. Stripe.js makes this easy in the browser, but
 * you can use the same technique in other environments with our token API.
 *
 * Tokens can be created with your publishable API key, which can safely
 * be embedded in downloadable applications like iPhone and Android apps.
 * You can then use a token anywhere in our API that a card or bank account
 * is accepted. Note that tokens are not meant to be stored or used more
 * than once—to store these details for use later, you should create
 * Customer or Recipient objects.
 *
 * @link https://stripe.com/docs/api#tokens
 */
class FetchTokenRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate('token');

        $data = array();

        return $data;
    }

    public function getEndpoint()
    {
        return $this->endpoint.'/tokens/'.$this->getToken();
    }

    public function getHttpMethod()
    {
        return 'GET';
    }
}
