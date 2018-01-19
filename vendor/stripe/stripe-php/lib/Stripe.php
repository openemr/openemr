<?php

namespace Stripe;

/**
 * Class Stripe
 *
 * @package Stripe
 */
class Stripe
{
    // @var string The Stripe API key to be used for requests.
    public static $apiKey;

    // @var string The Stripe client_id to be used for Connect requests.
    public static $clientId;

    // @var string The base URL for the Stripe API.
    public static $apiBase = 'https://api.stripe.com';

    // @var string The base URL for the OAuth API.
    public static $connectBase = 'https://connect.stripe.com';

    // @var string The base URL for the Stripe API uploads endpoint.
    public static $apiUploadBase = 'https://uploads.stripe.com';

    // @var string|null The version of the Stripe API to use for requests.
    public static $apiVersion = null;

    // @var string|null The account ID for connected accounts requests.
    public static $accountId = null;

    // @var boolean Defaults to true.
    public static $verifySslCerts = true;

    // @var array The application's information (name, version, URL)
    public static $appInfo = null;

    // @var Util\LoggerInterface|null The logger to which the library will
    //   produce messages.
    public static $logger = null;

    const VERSION = '5.2.0';

    /**
     * @return string The API key used for requests.
     */
    public static function getApiKey()
    {
        return self::$apiKey;
    }

    /**
     * @return string The client_id used for Connect requests.
     */
    public static function getClientId()
    {
        return self::$clientId;
    }

    /**
     * @return Util\LoggerInterface The logger to which the library will
     *   produce messages.
     */
    public static function getLogger()
    {
        if (self::$logger == null) {
            return new Util\DefaultLogger();
        }
        return self::$logger;
    }

    /**
     * @param Util\LoggerInterface $logger The logger to which the library
     *   will produce messages.
     */
    public static function setLogger($logger)
    {
        self::$logger = $logger;
    }

    /**
     * Sets the API key to be used for requests.
     *
     * @param string $apiKey
     */
    public static function setApiKey($apiKey)
    {
        self::$apiKey = $apiKey;
    }

    /**
     * Sets the client_id to be used for Connect requests.
     *
     * @param string $clientId
     */
    public static function setClientId($clientId)
    {
        self::$clientId = $clientId;
    }

    /**
     * @return string The API version used for requests. null if we're using the
     *    latest version.
     */
    public static function getApiVersion()
    {
        return self::$apiVersion;
    }

    /**
     * @param string $apiVersion The API version to use for requests.
     */
    public static function setApiVersion($apiVersion)
    {
        self::$apiVersion = $apiVersion;
    }

    /**
     * @return boolean
     */
    public static function getVerifySslCerts()
    {
        return self::$verifySslCerts;
    }

    /**
     * @param boolean $verify
     */
    public static function setVerifySslCerts($verify)
    {
        self::$verifySslCerts = $verify;
    }

    /**
     * @return string | null The Stripe account ID for connected account
     *   requests.
     */
    public static function getAccountId()
    {
        return self::$accountId;
    }

    /**
     * @param string $accountId The Stripe account ID to set for connected
     *   account requests.
     */
    public static function setAccountId($accountId)
    {
        self::$accountId = $accountId;
    }

    /**
     * @return array | null The application's information
     */
    public static function getAppInfo()
    {
        return self::$appInfo;
    }

    /**
     * @param string $appName The application's name
     * @param string $appVersion The application's version
     * @param string $appUrl The application's URL
     */
    public static function setAppInfo($appName, $appVersion = null, $appUrl = null)
    {
        if (self::$appInfo === null) {
            self::$appInfo = array();
        }
        self::$appInfo['name'] = $appName;
        self::$appInfo['version'] = $appVersion;
        self::$appInfo['url'] = $appUrl;
    }
}
