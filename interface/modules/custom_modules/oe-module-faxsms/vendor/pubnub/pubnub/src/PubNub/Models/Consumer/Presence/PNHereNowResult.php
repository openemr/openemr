<?php

namespace PubNub\Models\Consumer\Presence;

use PubNub\PubNubUtil;

class PNHereNowResult
{
    /** @var  int */
    protected $totalChannels;

    /** @var  int */
    protected $totalOccupancy;

    /** @var  PNHereNowChannelData[] */
    protected $channels;

    public function __construct($totalChannels, $totalOccupancy, $channels)
    {
        $this->totalChannels = $totalChannels;
        $this->totalOccupancy = $totalOccupancy;
        $this->channels = $channels;
    }

    /**
     * @return PNHereNowChannelData[]
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * @return int
     */
    public function getTotalChannels()
    {
        return $this->totalChannels;
    }

    /**
     * @return int
     */
    public function getTotalOccupancy()
    {
        return $this->totalOccupancy;
    }

    public function __toString()
    {
        return sprintf("HereNow Result total occupancy: %s, total channels: %s", $this->totalOccupancy, $this->totalChannels);
    }

    public static function fromJson($json, $channelNames)
    {
        /** multiple */
        if (array_key_exists('payload', $json) && PubNubUtil::isAssoc($json['payload'])) {
            $jsonInput = $json['payload'];

            $channels = [];

            if (count($jsonInput['channels']) > 0) {
                foreach ($jsonInput['channels'] as $channelName => $rawData) {
                    $channels[] = PNHereNowChannelData::fromJson($channelName, $rawData);
                }

                return new PNHereNowResult(
                    (int) $jsonInput['total_channels'],
                    (int) $jsonInput['total_occupancy'],
                    $channels
                );
            } else if (count($channelNames) === 1) {
                return new PNHereNowResult(
                    1,
                    (int) $jsonInput['total_occupancy'],
                    [new PNHereNowChannelData($channelNames[0], 0, [])]
                );
            } else {
                return new PNHereNowResult(
                    (int) $jsonInput['total_channels'],
                    (int) $jsonInput['total_occupancy'],
                    []
                );
            }

        /** empty */
        } else if (array_key_exists('occupancy', $json) && (int)$json['occupancy'] === 0) {
            return new PNHereNowResult(
                1,
                (int) $json['occupancy'],
                [new PNHereNowChannelData($channelNames[0], 0, [])]

            );

        /** single */
        } else if (array_key_exists('uuids', $json) && is_array($json['uuids'])) {
            $occupants = [];

            foreach ($json['uuids'] as $user) {
                if (is_string($user)) {
                    $occupants[] = (new PNHereNowOccupantsData($user, null));
                } else {

                    if (array_key_exists('state', $user)) {
                        $state = $user['state'];
                    } else {
                        $state = null;
                    }

                    $occupants[] = new PNHereNowOccupantsData($user['uuid'], $state);
                }
            }

            return new PNHereNowResult(
                1,
                (int) $json['occupancy'],
                [new PNHereNowChannelData(
                    $channelNames[0],
                    $json['occupancy'],
                    $occupants
                )]
            );
        }

        else {
            return new PNHereNowResult(
                1,
                (int)$json['occupancy'],
                [new PNHereNowChannelData(
                    $channelNames[0],
                    $json['occupancy'],
                    []
                )]
            );
        }
    }
}
