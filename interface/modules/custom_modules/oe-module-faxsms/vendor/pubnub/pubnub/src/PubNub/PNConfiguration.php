<?php

namespace PubNub;

use PubNub\Exceptions\PubNubConfigurationException;
use PubNub\Exceptions\PubNubValidationException;
use WpOrg\Requests\Transport;
use PubNub\CryptoModule;

class PNConfiguration
{
    private const DEFAULT_NON_SUBSCRIBE_REQUEST_TIMEOUT = 10;
    private const DEFAULT_SUBSCRIBE_TIMEOUT = 310;
    private const DEFAULT_CONNECT_TIMEOUT = 10;
    private const DEFAULT_USE_RANDOM_IV = true;

    /** @var  string Subscribe key provided by PubNub */
    private $subscribeKey;

    /** @var  string Publish key provided by PubNub */
    private $publishKey;

    /** @var  string Secret key provided by PubNub */
    private $secretKey;

    /** @var  string */
    private $authKey;

    /** @var  string */
    private $uuid;

    /** @var  string */
    private $origin;

    /** @var  bool Set to true to switch the client to HTTPS:// based communications. */
    private $secure = true;

    /** @var  PubNubCryptoCore */
    private $crypto;

    /** @var  string */
    private $filterExpression;

    /** @var int */
    protected $nonSubscribeRequestTimeout;

    /** @var int */
    protected $connectTimeout;

    /** @var  int */
    protected $subscribeTimeout;

    /** @var  Transport */
    protected $transport;

    /** @var bool */
    protected $useRandomIV;

    private $usingUserId = null;

    /**
     * PNConfiguration constructor.
     */
    public function __construct()
    {
        $this->nonSubscribeRequestTimeout = static::DEFAULT_NON_SUBSCRIBE_REQUEST_TIMEOUT;
        $this->connectTimeout = static::DEFAULT_CONNECT_TIMEOUT;
        $this->subscribeTimeout = static::DEFAULT_SUBSCRIBE_TIMEOUT;
        $this->useRandomIV = static::DEFAULT_USE_RANDOM_IV;
    }

    /**
     * Already configured PNConfiguration object with demo/demo as publish/subscribe keys.
     *
     * @return PNConfiguration config
     */
    public static function demoKeys()
    {
        $config = new self();
        $config->setSubscribeKey("demo");
        $config->setPublishKey("demo");
        $config->setUuid("demo");

        return $config;
    }

    /**
     * @param string $subscribeKey
     * @return $this
     */
    public function setSubscribeKey($subscribeKey)
    {
        $this->subscribeKey = $subscribeKey;

        return $this;
    }

    /**
     * @param string $publishKey
     * @return $this
     */
    public function setPublishKey($publishKey)
    {
        $this->publishKey = $publishKey;

        return $this;
    }

    /**
     * @param string $secretKey
     * @return $this
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getCipherKey()
    {
        return $this->getCrypto()->getCipherKey();
    }

    public function isAesEnabled()
    {
        return !!$this->crypto;
    }

    /**
     * @param string $cipherKey
     * @return $this
     */
    public function setCipherKey($cipherKey)
    {
        if ($this->crypto == null) {
            $this->crypto = CryptoModule::legacyCryptor($cipherKey, $this->getUseRandomIV());
        } else {
            $this->getCrypto()->setCipherKey($cipherKey);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getNonSubscribeRequestTimeout()
    {
        return $this->nonSubscribeRequestTimeout;
    }

    /**
     * @return int
     */
    public function getSubscribeTimeout()
    {
        return $this->subscribeTimeout;
    }

    /**
     * @return int
     */
    public function getConnectTimeout()
    {
        return $this->connectTimeout;
    }

    /**
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @param string $origin
     * @return $this
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubscribeKey()
    {
        return $this->subscribeKey;
    }

    /**
     * @return string
     */
    public function getPublishKey()
    {
        return $this->publishKey;
    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * @return bool
     */
    public function isSecure()
    {
        return $this->secure;
    }

    /**
     * @param $ssl
     * @return $this
     */
    public function setSecure($ssl)
    {
        $this->secure = $ssl;

        return $this;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     * @return $this
     */
    public function setUuid($uuid)
    {
        if (!is_null($this->usingUserId) && $this->usingUserId) {
            throw new PubNubConfigurationException("Cannot use UserId and UUID simultaneously");
        }
        if (!$this->validateNotEmptyString($uuid)) {
            throw new PubNubConfigurationException("UUID should not be empty");
        }
        $this->usingUserId = false;
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->uuid;
    }

    /**
     * @param string $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        if (!is_null($this->usingUserId) && !$this->usingUserId) {
            throw new PubNubConfigurationException("Cannot use UserId and UUID simultaneously");
        }
        if (!$this->validateNotEmptyString($userId)) {
            throw new PubNubConfigurationException("UserID should not be empty");
        }
        $this->usingUserId = true;
        $this->uuid = $userId;

        return $this;
    }

    /**
     * @return string|null authKey
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @param string|null $authKey
     * @return $this
     */
    public function setAuthKey($authKey)
    {
        $this->authKey = $authKey;

        return $this;
    }

    /**
     * @return PubNubCryptoCore
     * @throws \Exception
     */
    public function getCrypto()
    {
        if (!$this->crypto) {
            throw new PubNubValidationException("You should set up either a cipher key or a crypto instance before");
        }

        return $this->crypto;
    }

    /**
     * @return null|PubNubCryptoCore
     */
    public function getCryptoSafe()
    {
        try {
            return $this->getCrypto();
        } catch (PubNubValidationException $e) {
            return null;
        }
    }

    /**
     * @param PubNubCryptoCore $crypto
     * @return $this
     */
    public function setCrypto($crypto)
    {
        $this->crypto = $crypto;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilterExpression()
    {
        return $this->filterExpression;
    }

    /**
     * @param string $filterExpression
     * @return $this
     */
    public function setFilterExpression($filterExpression)
    {
        $this->filterExpression = $filterExpression;

        return $this;
    }

    /**
     * @param int $nonSubscribeRequestTimeout
     * @return $this
     */
    public function setNonSubscribeRequestTimeout($nonSubscribeRequestTimeout)
    {
        $this->nonSubscribeRequestTimeout = $nonSubscribeRequestTimeout;

        return $this;
    }

    /**
     * @param int $connectTimeout
     * @return $this
     */
    public function setConnectTimeout($connectTimeout)
    {
        $this->connectTimeout = $connectTimeout;

        return $this;
    }

    /**
     * @param int $subscribeTimeout
     * @return $this
     */
    public function setSubscribeTimeout($subscribeTimeout)
    {
        $this->subscribeTimeout = $subscribeTimeout;

        return $this;
    }

    /**
     * @return Transport
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * @param Transport $transport
     * @return $this
     */
    public function setTransport($transport)
    {
        $this->transport = $transport;

        return $this;
    }

    /**
     * @return bool
     */
    public function getUseRandomIV()
    {
        return $this->useRandomIV;
    }

    /**
     * @param bool $useRandomIV
     * @return $this
     */
    public function setUseRandomIV($useRandomIV)
    {
        $this->useRandomIV = $useRandomIV;

        if ($this->crypto != null) {
            $this->crypto->setUseRandomIV($this->useRandomIV);
        }

        return $this;
    }

    private function validateNotEmptyString($value)
    {
        return (is_string($value) && strlen(trim($value)) > 0);
    }
}
