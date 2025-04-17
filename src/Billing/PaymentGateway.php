<?php

/**
 * Payment Gateways for credit card transactions
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing;

use Omnipay\Omnipay;
use Omnipay\Common\CreditCard;
use OpenEMR\Common\Crypto\CryptoGen;

class PaymentGateway
{
    private $gateway;
    private $card;
    private $apiKey;
    private $transactionKey;
    private $production;

    public function __construct($name)
    {
        $this->production = !$GLOBALS['gateway_mode_production'];

        $cryptoGen = new CryptoGen();
        $this->apiKey = $cryptoGen->decryptStandard($GLOBALS['gateway_api_key']);
        $this->transactionKey = $cryptoGen->decryptStandard($GLOBALS['gateway_transaction_key']);

        // Setup payment Gateway
        $this->setGateway($name);
    }

    public function setApiKey($key)
    {
        $this->apiKey = $key;
    }

    public function setTransactionKey($key)
    {
        $this->transactionKey = $key;
    }

    public function setProduction($tf)
    {
        $this->production = $tf;
    }

    /**
     * @param $card
     * @return bool|string
     * $card = [];
     * $card['card'] = '';
     * $card['expiremonth'] = '';
     * $card['expireyear'] = '';
     * $card['cvv'] = '';
     */
    public function setCard($card)
    {
        try {
            $ccard = new CreditCard($card);
            $ccard->validate();
            $this->card = $card;
            return true;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * @param $pay
     * @return bool|string
     */
    public function submitPaymentCard($pay)
    {
        try {
            // Send purchase request
            $response = $this->gateway->purchase(
                [
                    'amount' => $pay['amount'],
                    'currency' => $pay['currency'],
                    'card' => $this->card
                ]
            )->send();
            // Process response
            if ($response->isSuccessful()) {
                return $response;
            } elseif ($response->isRedirect()) {
                // Redirect to offsite payment gateway
                return $response->getMessage();
            } else {
                // Payment failed
                return $response->getMessage();
            }
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * @param $pay
     * @return bool|string
     */
    public function submitPaymentToken($pay)
    {
        try {
            // Send purchase request with card token
            $response = $this->gateway->purchase($pay)->send();
            // Process response
            if ($response->isSuccessful()) {
                return $response;
            } elseif ($response->isRedirect()) {
                // Redirect to offsite payment gateway
                return $response->getMessage();
            } else {
                // Payment failed
                return $response->getMessage();
            }
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     * @param $which
     * @return string
     */
    public function setGateway($which)
    {
        if (isset($this->gateway)) {
            unset($this->gateway);
        }
        try {
            if (stripos($which, "stripe") !== false) {
                $gatewayName = 'Stripe';
                $this->gateway = Omnipay::create($gatewayName);
                $this->gateway->setApiKey($this->apiKey);
            } else {
                $gatewayName = 'AuthorizeNetApi_Api';
                $this->gateway = Omnipay::create($gatewayName);
                $this->gateway->setAuthName($this->apiKey);
                $this->gateway->setTransactionKey($this->transactionKey);
                $this->gateway->setTestMode($this->production);
            }
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }
}
