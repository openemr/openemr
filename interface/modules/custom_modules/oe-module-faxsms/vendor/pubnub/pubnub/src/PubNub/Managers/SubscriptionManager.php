<?php

namespace PubNub\Managers;

use PubNub\Exceptions\PubNubResponseParsingException;
use PubNub\Models\Consumer\PubSub\PNPresenceEventResult;
use PubNub\Builders\DTO\SubscribeOperation;
use PubNub\Builders\DTO\UnsubscribeOperation;
use PubNub\Callbacks\SubscribeCallback;
use PubNub\Endpoints\Presence\Leave;
use PubNub\Exceptions\PubNubUnsubscribeException;
use PubNub\Models\Server\PresenceEnvelope;
use PubNub\Models\Server\SubscribeMessage;
use PubNub\Models\Server\MessageType;
use PubNub\Endpoints\PubSub\Subscribe;
use PubNub\Enums\PNStatusCategory;
use PubNub\Exceptions\PubNubConnectionException;
use PubNub\Exceptions\PubNubServerException;
use PubNub\Models\Consumer\PubSub\PNMessageResult;
use PubNub\Models\Consumer\PubSub\PNSignalMessageResult;
use PubNub\Models\Consumer\PubSub\SubscribeEnvelope;
use PubNub\Models\ResponseHelpers\PNStatus;
use PubNub\PubNub;
use PubNub\PubNubUtil;

class SubscriptionManager
{
    /** @var  PubNub */
    protected $pubnub;

    /** @var  ListenerManager */
    protected $listenerManager;

    /** @var  int */
    protected $timetoken;

    /** @var  string */
    protected $region;

    /** @var  bool */
    protected $subscriptionStatusAnnounced;

    public StateManager $subscriptionState;

    /**
     * SubscriptionManager constructor.
     * @param PubNub $pubnub
     */
    public function __construct(PubNub $pubnub)
    {
        $this->pubnub = $pubnub;
        $this->listenerManager = new ListenerManager($pubnub);
        $this->subscriptionState = new StateManager($pubnub);
        $this->subscriptionStatusAnnounced = false;
    }

    public function start()
    {
        while (true) {
            $combinedChannels = $this->subscriptionState->prepareChannelList(true);
            $combinedChannelGroups = $this->subscriptionState->prepareChannelGroupList(true);

            if (empty($combinedChannels) && empty($combinedChannelGroups)) {
                $pnStatus = (new PNStatus())->setCategory(PNStatusCategory::PNCancelledCategory);
                $this->listenerManager->announceStatus($pnStatus);
                return;
            }

            try {
                /** @var SubscribeEnvelope $result */
                $result = (new Subscribe($this->pubnub))
                    ->channels($combinedChannels)
                    ->channelGroups($combinedChannelGroups)
                    ->setTimetoken($this->timetoken)
                    ->setRegion($this->region)
                    ->setFilterExpression($this->pubnub->getConfiguration()->getFilterExpression())
                    ->sync();
            } catch (PubNubConnectionException $e) {
                if ($e->getStatus()->getCategory() === PNStatusCategory::PNTimeoutCategory) {
                    continue;
                }

                // TODO: ensure this happens when an already established radio / connectivity is lost
                $pnStatus = $e->getStatus();
                $pnStatus->setCategory(PNStatusCategory::PNUnexpectedDisconnectCategory);
                $this->listenerManager->announceStatus($pnStatus);
                return;
            } catch (PubNubServerException $e) {
                $pnStatus = $e->getStatus();

                if ($e->getStatusCode() === 403) {
                    $pnStatus->setCategory(PNStatusCategory::PNAccessDeniedCategory);
                } else if ($e->getStatusCode() === 400) {
                    $pnStatus->setCategory(PNStatusCategory::PNBadRequestCategory);
                } else if ($e->getStatusCode() === 530) {
                    $pnStatus->setCategory(PNStatusCategory::PNNoStubMatchedCategory);
                } else {
                    $pnStatus->setCategory(PNStatusCategory::PNUnknownCategory);
                }

                $this->listenerManager->announceStatus($pnStatus);
                return;
            } catch (PubNubResponseParsingException $e) {
                $pnStatus = $e->getStatus();
                $pnStatus->setCategory(PNStatusCategory::PNMalformedResponseCategory);
                $this->listenerManager->announceStatus($pnStatus);
            } catch (\Exception $e) {
                $this->pubnub->getLogger()->error('Subscription Manager loop: ' . $e->getMessage());

                $pnStatus = (new PNStatus())
                    ->setCategory(PNStatusCategory::PNUnknownCategory);
                $this->listenerManager->announceStatus($pnStatus);
                return;
            }

            if (!$this->subscriptionStatusAnnounced) {
                $pnStatus = (new PNStatus())->setCategory(PNStatusCategory::PNConnectedCategory);

                try {
                    $this->listenerManager->announceStatus($pnStatus);
                } catch (PubNubUnsubscribeException $e) {
                    $this->adaptUnsubscribeBuilder($e->getUnsubscribeOperation($this), false);
                    break;
                }

                $this->subscriptionStatusAnnounced = true;
            }

            if (!$result->isEmpty()) {
                try {
                    foreach ($result->getMessages() as $message) {
                        $this->processIncomingPayload($message);
                    }
                } catch (PubNubUnsubscribeException $e) {
                    $this->adaptUnsubscribeBuilder($e->getUnsubscribeOperation($this));
                    break;
                }
            }

            $this->timetoken = $result->getMetadata()->getTimetoken();
            $this->region = (int) $result->getMetadata()->getRegion();
        }
    }

    /**
     * @param UnsubscribeOperation $operation
     * @param bool $announceStatus
     */
    public function adaptUnsubscribeBuilder(UnsubscribeOperation $operation, $announceStatus = true)
    {
        $leave = (new Leave($this->pubnub))
            ->channels($operation->getChannels())
            ->channelGroups($operation->getChannelGroups());

        $this->subscriptionState->adaptUnsubscribeBuilder($operation);

        $this->subscriptionStatusAnnounced = false;

        $leave->sync();

        if ($announceStatus) {
            $pnStatus = (new PNStatus())->setCategory(PNStatusCategory::PNDisconnectedCategory);

            $this->listenerManager->announceStatus($pnStatus);
        }
    }

    /**
     * @param SubscribeCallback $listener
     */
    public function addListener($listener)
    {
        $this->listenerManager->addListener($listener);
    }

    /**
     * @param SubscribeCallback $listener
     */
    public function removeListener($listener)
    {
        $this->listenerManager->removeListener($listener);
    }

    /**
     * @return \string[]
     */
    public function getSubscribedGroups()
    {
        return $this->subscriptionState->prepareChannelList(false);
    }

    /**
     * @return \string[]
     */
    public function getSubscribedChannelGroups()
    {
        return $this->subscriptionState->prepareChannelGroupList(false);
    }

    /**
     * @param SubscribeOperation $subscribeOperation
     */
    public function adaptSubscribeBuilder($subscribeOperation)
    {
        $this->subscriptionState->adaptSubscribeBuilder($subscribeOperation);

        if ($subscribeOperation->getTimetoken() !== null) {
            $this->timetoken = $subscribeOperation->getTimetoken();
        }
    }

    /**
     * @param SubscribeMessage $message
     * @throws PubNubUnsubscribeException
     */
    protected function processIncomingPayload($message)
    {
        $channel = $message->getChannel();
        $subscriptionMatch = $message->getSubscriptionMatch();
        $publishMetadata = $message->getPublishMetaData();

        if ($channel !== null && $channel === $subscriptionMatch) {
            $subscriptionMatch = null;
        }

        if (PubNubUtil::stringEndsWith($channel, '-pnpres')) {
            $presencePayload = PresenceEnvelope::fromJson($message->getPayload());

            $strippedPresenceChannel = null;
            $strippedPresenceSubscription = null;

            if ($channel !== null) {
                $strippedPresenceChannel = str_replace("-pnpres", "", $channel);
            }

            if ($subscriptionMatch !== null) {
                $strippedPresenceSubscription = str_replace("-pnpres", "", $subscriptionMatch);
            }

            $pnPresenceResult = new PNPresenceEventResult(
                $presencePayload->getAction(),
                $presencePayload->getUuid(),
                $presencePayload->getTimestamp(),
                $presencePayload->getOccupancy(),
                $strippedPresenceSubscription,
                $strippedPresenceChannel,
                $publishMetadata->getPublishTimetoken(),
                $presencePayload->getData()
            );

            $this->listenerManager->announcePresence($pnPresenceResult);
        } else {
            $messageError = null;
            try {
                $extractedMessage = $this->processMessage($message->getPayload());
            } catch (PubNubResponseParsingException $exception) {
                $extractedMessage = $message->getPayload();
                $messageError = $exception;
            }
            $publisher = $message->getIssuingClientId();

            if ($extractedMessage === null) {
                $this->pubnub->getLogger()->debug("unable to parse payload on #processIncomingMessages");
            }

            if (MessageType::SIGNAL == $message->getMessageType()) {
                $pnSignalResult = new PNSignalMessageResult(
                    $extractedMessage,
                    $channel,
                    $subscriptionMatch,
                    $publishMetadata->getPublishTimetoken(),
                    $publisher
                );

                $this->listenerManager->announceSignal($pnSignalResult);
            } else {
                $pnMessageResult = new PNMessageResult(
                    $extractedMessage,
                    $channel,
                    $subscriptionMatch,
                    $publishMetadata->getPublishTimetoken(),
                    $publisher,
                    $messageError
                );

                $this->listenerManager->announceMessage($pnMessageResult);
            }
        }
    }

    /**
     * @param mixed $message
     * @return mixed
     */
    protected function processMessage($message)
    {
        if ($this->pubnub->getConfiguration()->getCryptoSafe() === null) {
            return $message;
        } else {
            return $this->pubnub->getConfiguration()->getCryptoSafe()->decrypt($message);
        }
    }
}
