<?php

namespace PubNub\Models\Server;

/**
 * Class PresenceEnvelope
 * @package PubNub\Endpoints\Presence\Server
 */
class PresenceEnvelope
{
    /**
     * @var
     */
    private $action;
    /**
     * @var
     */
    private $uuid;
    /**
     * @var
     */
    private $occupancy;
    /**
     * @var
     */
    private $timestamp;
    /**
     * @var
     */
    private $data;

    /**
     * PresenceEnvelope constructor.
     * @param $action
     * @param $uuid
     * @param $occupancy
     * @param $timestamp
     * @param $data
     */
    public function __construct($action, $uuid, $occupancy, $timestamp, $data)
    {
        $this->action = $action;
        $this->uuid = $uuid;
        $this->occupancy = $occupancy;
        $this->timestamp = $timestamp;
        $this->data = $data;
    }

    /**
     * @param $json
     * @return PresenceEnvelope
     */
    public static function fromJson($json)
    {
        return new PresenceEnvelope(
            $json['action'],
            $json['uuid'],
            $json['occupancy'],
            $json['timestamp'],
            array_key_exists("data", $json) ? $json['data'] : null
        );
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getOccupancy()
    {
        return $this->occupancy;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function getData()
    {
        return $this->data;
    }
}