<?php

namespace RingCentral\SDK\WebSocket;

use Exception;
use RingCentral\SDK\Platform\Platform;
use RingCentral\SDK\WebSocket\WebSocket;
use RingCentral\SDK\WebSocket\ApiException;
use RingCentral\SDK\WebSocket\ApiResponse;
use RingCentral\SDK\WebSocket\ApiRequest;
use RingCentral\SDK\WebSocket\Events\ErrorEvent;
use RingCentral\SDK\WebSocket\Events\NotificationEvent;
use RingCentral\SDK\WebSocket\Events\SuccessEvent;
use RingCentral\SDK\Subscription\SubscriptionBase;

class Subscription extends SubscriptionBase
{
    /** @var WebSocket */
    protected $_webSocket;

    function __construct(Platform $platform, WebSocket $webSocket)
    {
        $this->_webSocket = $webSocket;
        parent::__construct($platform);
    }

    /**
     * @return bool
     */
    public function subscribed()
    {
        return (!empty($this->_subscription) &&
                !empty($this->_subscription['id']));
    }

    /**
     * @param array $options
     *
     * @throws Exception
     *
     * @return Subscription
     */
    public function subscribe(array $options = [])
    {
        if (!empty($options['events'])) {
            $this->setEvents($options['events']);
        }
        try {
            $request = new ApiRequest([
                'method' => 'POST',
                'path'   => '/restapi/v1.0/subscription',
                'body' => [
                    'eventFilters' => $this->getFullEventFilters(),
                    'deliveryMode' => [
                        'transportType' => 'WebSocket'
                    ]
                ]
            ]);
            $response = $this->_webSocket->sendRequest($request, function (ApiResponse $response) {
                if (!$response->ok()) {
                    $exception = new ApiException($response);
                    $this->dispatch(new ErrorEvent($exception), self::EVENT_SUBSCRIBE_ERROR);
                    return;
                }
                $this->setSubscription($response->body());
                $this->dispatch(new SuccessEvent($response), self::EVENT_SUBSCRIBE_SUCCESS);
            });
        } catch (Exception $e) {
            $this->reset();
            throw $e;
        }
    }

    public function renew(array $options = [])
    {
        if (!empty($options['events'])) {
            $this->setEvents($options['events']);
        }

        if (!$this->subscribed()) {
            throw new Exception('No subscription');
        }

        try {
            $request = new ApiRequest([
                'method' => 'PUT',
                'path' => '/restapi/v1.0/subscription/' . $this->_subscription['id'],
                'body' => [
                    'eventFilters' => $this->getFullEventFilters(),
                ],
            ]);
            $response = $this->_webSocket->sendRequest($request, function (ApiResponse $response) {
                if (!$response->ok()) {
                    $exception = new ApiException($response);
                    $this->dispatch(new ErrorEvent($exception), self::EVENT_RENEW_ERROR);
                    return;
                }
                $this->setSubscription($response->body());
                $this->dispatch(new SuccessEvent($response), self::EVENT_RENEW_SUCCESS);
            });
        } catch (Exception $e) {
            $this->reset();
            throw $e;
        }
    }

    /**
     * @param array $options
     *
     * @throws Exception
     *
     * @return ApiResponse|$this
     */
    public function register(array $options = [])
    {
        $this->_webSocket->removeListener(WebSocket::EVENT_NOTIFICATION, [$this, 'onNotification']);
        $this->_webSocket->addListener(WebSocket::EVENT_NOTIFICATION, [$this, 'onNotification']);
        if ($this->alive()) {
            return $this->renew($options);
        } else {
            return $this->subscribe($options);
        }
    }

    protected function onNotification($event)
    {
        $this->dispatch($event, self::EVENT_NOTIFICATION);
    }

    public function reset() {
        $this->_subscription = null;
    }

    /**
     * @return bool
     */
    public function alive()
    {
        return $this->subscribed();
    }
}
