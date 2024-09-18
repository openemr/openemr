<?php

namespace PubNub;

use Monolog\Logger;
use PubNub\Builders\SubscribeBuilder;
use PubNub\Callbacks\SubscribeCallback;
use PubNub\Endpoints\Access\Audit;
use PubNub\Endpoints\Access\Grant;
use PubNub\Endpoints\Access\GrantToken;
use PubNub\Endpoints\Access\Revoke;
use PubNub\Endpoints\Access\RevokeToken;
use PubNub\Endpoints\ChannelGroups\AddChannelToChannelGroup;
use PubNub\Endpoints\ChannelGroups\ListChannelsInChannelGroup;
use PubNub\Endpoints\ChannelGroups\RemoveChannelFromChannelGroup;
use PubNub\Endpoints\ChannelGroups\RemoveChannelGroup;
use PubNub\Endpoints\History;
use PubNub\Endpoints\HistoryDelete;
use PubNub\Endpoints\MessageCount;
use PubNub\Endpoints\MessagePersistance\FetchMessages;
use PubNub\Endpoints\Objects\Channel\SetChannelMetadata;
use PubNub\Endpoints\Objects\Channel\GetChannelMetadata;
use PubNub\Endpoints\Objects\Channel\GetAllChannelMetadata;
use PubNub\Endpoints\Objects\Channel\RemoveChannelMetadata;
use PubNub\Endpoints\Objects\UUID\SetUUIDMetadata;
use PubNub\Endpoints\Objects\UUID\GetUUIDMetadata;
use PubNub\Endpoints\Objects\UUID\GetAllUUIDMetadata;
use PubNub\Endpoints\Objects\UUID\RemoveUUIDMetadata;
use PubNub\Endpoints\Objects\Member\SetMembers;
use PubNub\Endpoints\Objects\Member\GetMembers;
use PubNub\Endpoints\Objects\Member\RemoveMembers;
use PubNub\Endpoints\Objects\Membership\SetMemberships;
use PubNub\Endpoints\Objects\Membership\GetMemberships;
use PubNub\Endpoints\Objects\Membership\RemoveMemberships;
use PubNub\Endpoints\Presence\GetState;
use PubNub\Endpoints\Presence\HereNow;
use PubNub\Endpoints\Presence\SetState;
use PubNub\Endpoints\Presence\WhereNow;
use PubNub\Endpoints\PubSub\Publish;
use PubNub\Endpoints\PubSub\Signal;
use PubNub\Endpoints\PubSub\Fire;
use PubNub\Endpoints\Push\AddChannelsToPush;
use PubNub\Endpoints\Push\ListPushProvisions;
use PubNub\Endpoints\Push\RemoveChannelsFromPush;
use PubNub\Endpoints\Push\RemoveDeviceFromPush;
use PubNub\Endpoints\Time;
use PubNub\Exceptions\PubNubConfigurationException;
use PubNub\Managers\BasePathManager;
use PubNub\Managers\SubscriptionManager;
use PubNub\Managers\TelemetryManager;
use PubNub\Managers\TokenManager;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\NullLogger;
use PubNub\Endpoints\FileSharing\{SendFile, DeleteFile, DownloadFile, GetFileDownloadUrl, ListFiles};

class PubNub implements LoggerAwareInterface
{
    protected const SDK_VERSION = "6.3.0";
    protected const SDK_NAME = "PubNub-PHP";

    public static $MAX_SEQUENCE = 65535;

    /** @var PNConfiguration */
    protected PNConfiguration $configuration;

    /** @var  BasePathManager */
    protected $basePathManager;

    /** @var  SubscriptionManager */
    protected $subscriptionManager;

    /** @var TelemetryManager */
    protected $telemetryManager;

    /** @var TokenManager */
    protected $tokenManager;

    /** @var  LoggerInterface */
    protected LoggerInterface $logger;

    /** @var  int $nextSequence */
    protected $nextSequence = 0;

    protected ?CryptoModule $cryptoModule = null;

    /**
     * PNConfiguration constructor.
     *
     * @param $initialConfig PNConfiguration
     */
    public function __construct($initialConfig)
    {
        $this->validateConfig($initialConfig);
        $this->configuration = $initialConfig;
        $this->basePathManager = new BasePathManager($initialConfig);
        $this->subscriptionManager = new SubscriptionManager($this);
        $this->telemetryManager = new TelemetryManager();
        $this->tokenManager = new TokenManager();
        $this->logger = new NullLogger();
    }

    /**
     * Pre-configured PubNub client with demo-keys
     * @return static
     */
    public static function demo()
    {
        return new PubNub(PNConfiguration::demoKeys());
    }

    /**
     * @param $configuration PNConfiguration
     *
     * @throws PubNubConfigurationException
     */
    private function validateConfig(PNConfiguration $configuration)
    {
        if (empty($configuration->getUuid())) {
            throw new PubNubConfigurationException('UUID should not be empty');
        }
    }

    /**
     * @param SubscribeCallback $listener
     */
    public function addListener($listener)
    {
        $this->subscriptionManager->addListener($listener);
    }

    /**
     * @param SubscribeCallback $listener
     */
    public function removeListener($listener)
    {
        $this->subscriptionManager->removeListener($listener);
    }

    /**
     * @return Publish
     */
    public function publish()
    {
        return new Publish($this);
    }

    /**
     * @return Fire
     */
    public function fire()
    {
        return new Fire($this);
    }

    /**
     * @return Signal
     */
    public function signal()
    {
        return new Signal($this);
    }

    /**
     * @return SubscribeBuilder
     */
    public function subscribe()
    {
        return new SubscribeBuilder($this->subscriptionManager);
    }

    /**
     * @return History
     */
    public function history()
    {
        return new History($this);
    }

    /**
     * @return HereNow
     */
    public function hereNow()
    {
        return new HereNow($this);
    }

    /**
     * @return WhereNow
     */
    public function whereNow()
    {
        return new WhereNow($this);
    }

    /**
     * @return Grant
     */
    public function grant()
    {
        return new Grant($this);
    }

    /**
     * @return PNAccessManagerTokenResult
     */
    public function parseToken($token)
    {
        return (new GrantToken($this))->parseToken($token);
    }

    /**
     * @return GrantToken
     */
    public function grantToken()
    {
        return new GrantToken($this);
    }

    /**
     * @return RevokeToken
     */
    public function revokeToken()
    {
        return new RevokeToken($this);
    }

    /**
     * @return Audit
     */
    public function audit()
    {
        return new Audit($this);
    }

    /**
     * @return Revoke
     */
    public function revoke()
    {
        return new Revoke($this);
    }

    /**
     * @return AddChannelToChannelGroup
     */
    public function addChannelToChannelGroup()
    {
        return new AddChannelToChannelGroup($this);
    }

    /**
     * @return RemoveChannelFromChannelGroup
     */
    public function removeChannelFromChannelGroup()
    {
        return new RemoveChannelFromChannelGroup($this);
    }

    /**
     * @return RemoveChannelGroup
     */
    public function removeChannelGroup()
    {
        return new RemoveChannelGroup($this);
    }

    /**
     * @return ListChannelsInChannelGroup
     */
    public function listChannelsInChannelGroup()
    {
        return new ListChannelsInChannelGroup($this);
    }

    /**
     * @return Time
     */
    public function time(): Time
    {
        return new Time($this);
    }

    /**
     * @return AddChannelsToPush
     */
    public function addChannelsToPush()
    {
        return new AddChannelsToPush($this);
    }

    /**
     * @return RemoveChannelsFromPush
     */
    public function removeChannelsFromPush()
    {
        return new RemoveChannelsFromPush($this);
    }

    /**
     * @return RemoveDeviceFromPush
     */
    public function removeAllPushChannelsForDevice()
    {
        return new RemoveDeviceFromPush($this);
    }

    /**
     * @return ListPushProvisions
     */
    public function listPushProvisions()
    {
        return new ListPushProvisions($this);
    }

    /**
     * @return SetChannelMetadata
     */
    public function setChannelMetadata()
    {
        return new SetChannelMetadata($this);
    }

    /**
     * @return GetChannelMetadata
     */
    public function getChannelMetadata()
    {
        return new GetChannelMetadata($this);
    }

    /**
     * @return GetAllChannelMetadata
     */
    public function getAllChannelMetadata()
    {
        return new GetAllChannelMetadata($this);
    }

    /**
     * @return RemoveChannelMetadata
     */
    public function removeChannelMetadata()
    {
        return new RemoveChannelMetadata($this);
    }

    /**
     * @return SetUUIDMetadata
     */
    public function setUUIDMetadata()
    {
        return new SetUUIDMetadata($this);
    }

    /**
     * @return GetUUIDMetadata
     */
    public function getUUIDMetadata()
    {
        return new GetUUIDMetadata($this);
    }

    /**
     * @return GetAllUUIDMetadata
     */
    public function getAllUUIDMetadata()
    {
        return new GetAllUUIDMetadata($this);
    }

    /**
     * @return RemoveUUIDMetadata
     */
    public function removeUUIDMetadata()
    {
        return new RemoveUUIDMetadata($this);
    }

    /**
     * @return GetMembers
     */
    public function getMembers()
    {
        return new GetMembers($this);
    }

    /**
     * @return SetMembers
     */
    public function setMembers()
    {
        return new SetMembers($this);
    }

    /**
     * @return RemoveMembers
     */
    public function removeMembers()
    {
        return new RemoveMembers($this);
    }

    /**
     * @return GetMemberships
     */
    public function getMemberships()
    {
        return new GetMemberships($this);
    }

    /**
     * @return SetMemberships
     */
    public function setMemberships()
    {
        return new SetMemberships($this);
    }

    /**
     * @return RemoveMemberships
     */
    public function removeMemberships()
    {
        return new RemoveMemberships($this);
    }

    /**
     * @return int
     */
    public function timestamp()
    {
        return time();
    }

    /**
     * @return string
     */
    public static function getSdkVersion()
    {
        return static::SDK_VERSION;
    }

    /**
     * @return string
     */
    public static function getSdkName()
    {
        return static::SDK_NAME;
    }

    /**
     * @return string
     */
    public static function getSdkFullName()
    {
        $fullName = static::SDK_NAME . "/" . static::SDK_VERSION;

        return $fullName;
    }

    /**
     * Get PubNub configuration object
     *
     * @return PNConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @return string Base path
     */
    public function getBasePath($customHost = null)
    {
        return $this->basePathManager->getBasePath($customHost);
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @return GetState
     */
    public function getState()
    {
        return new GetState($this);
    }

    /**
     * @return SetState
     */
    public function setState()
    {
        return new SetState($this);
    }

    /**
     * @return HistoryDelete
     */
    public function deleteMessages()
    {
        return new HistoryDelete($this);
    }

    /**
     * @return MessageCount
     */
    public function messageCounts()
    {
        return new MessageCount($this);
    }

    /**
     * @return TelemetryManager
     */
    public function getTelemetryManager()
    {
        return $this->telemetryManager;
    }

    /**
     * @return int unique sequence identifier
     */
    public function getSequenceId()
    {
        if (static::$MAX_SEQUENCE === $this->nextSequence) {
            $this->nextSequence = 1;
        } else {
            $this->nextSequence += 1;
        }

        return $this->nextSequence;
    }

    /**
     * @return string Token previously set by $this->setToken
     */
    public function getToken()
    {
        return $this->tokenManager->getToken();
    }

    /**
     * @param string $token Token obtained by GetToken
     */
    public function setToken($token)
    {
        return $this->tokenManager->setToken($token);
    }

    public function getCrypto(): CryptoModule | null
    {
        if ($this->cryptoModule) {
            return $this->cryptoModule;
        } else {
            return $this->configuration->getCryptoSafe();
        }
    }

    public function getCryptoSafe(): CryptoModule|null
    {
        if ($this->cryptoModule) {
            return $this->cryptoModule;
        } else {
            return $this->configuration->getCryptoSafe();
        }
    }

    public function isCryptoEnabled(): bool
    {
        return !empty($this->cryptoModule) || !empty($this->configuration->getCryptoSafe());
    }

    public function setCrypto(CryptoModule $cryptoModule)
    {
        $this->cryptoModule = $cryptoModule;
    }

    public function fetchMessages(): FetchMessages
    {
        return new FetchMessages($this);
    }

    public function sendFile()
    {
        return new SendFile($this);
    }

    public function deleteFile()
    {
        return new DeleteFile($this);
    }

    public function downloadFile()
    {
        return new DownloadFile($this);
    }

    public function listFiles()
    {
        return new ListFiles($this);
    }

    public function getFileDownloadUrl()
    {
        return new GetFileDownloadUrl($this);
    }
}
