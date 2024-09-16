<?php

namespace PubNub\Models\Consumer\Presence;

use PubNub\PubNubUtil;

class PNHereNowChannelData
{
    /** @var  string */
    protected $channelName;

    /** @var  int */
    protected $occupancy;

    /** @var  PNHereNowOccupantsData[] */
    protected $occupants;

    /**
     * PNHereNowChannelData constructor.
     * @param string $channelName
     * @param int $occupancy
     * @param PNHereNowOccupantsData[] $occupants
     */
    public function __construct($channelName, $occupancy, $occupants)
    {
        $this->channelName = $channelName;
        $this->occupancy = $occupancy;
        $this->occupants = $occupants;
    }

    /**
     * @return string
     */
    public function getChannelName()
    {
        return $this->channelName;
    }

    /**
     * @return int
     */
    public function getOccupancy()
    {
        return $this->occupancy;
    }

    /**
     * @return PNHereNowOccupantsData[]
     */
    public function getOccupants()
    {
        return $this->occupants;
    }

    public function __toString()
    {
        return sprintf("HereNow Channel Data for channel '%s': occupancy: %s, occupants: %s",
            $this->channelName, $this->occupancy, $this->occupants);
    }

    /**
     * @param string $name
     * @param array $json
     * @return PNHereNowChannelData
     */
    public static function fromJson($name, $json)
    {
        if (array_key_exists('uuids', $json)) {
            $occupants = [];

            foreach ($json['uuids'] as $user) {
                if (PubNubUtil::isAssoc($user) && count($user) > 0) {
                    if (array_key_exists('state', $user)) {
                        $occupants[] = new PNHereNowOccupantsData($user['uuid'], $user['state']);
                    } else  {
                        $occupants[] = new PNHereNowOccupantsData($user['uuid'], null);
                    }
                } else {
                    $occupants[] = new PNHereNowOccupantsData($user, null);
                }
            }
        } else {
            $occupants = null;
        }

        return new PNHereNowChannelData(
            $name,
            (int) $json['occupancy'],
            $occupants
        );
    }
}
