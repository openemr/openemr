<?php

namespace PubNub\Managers;

use PubNub\Enums\PNOperationType;


class TelemetryManager
{
    const MAXIMUM_LATENCY_DATA_AGE = 60;

    /** @var array */
    private $latencies = [];

    public function __construct(){}

    public function operationLatencies()
    {
        $operationLatencies = [];

        foreach ($this->latencies as $endpointName => $endpointLatencies) {
            $latencyKey = "l_".$endpointName;

            $endpointAvgLatency = $this->averageLatencyFromData($endpointLatencies);

            if ($endpointAvgLatency > 0) {
                $operationLatencies[$latencyKey] = $endpointAvgLatency;
            }
        }

        return $operationLatencies;
    }

    public function cleanUpTelemetryData()
    {
        $currentTimestamp = microtime(true);

        foreach ($this->latencies as $endpointName => $endpointLatencies) {
            $index = 0;

            foreach ($endpointLatencies as $latencyInformation) {
                if ($currentTimestamp - $latencyInformation["d"] > static::MAXIMUM_LATENCY_DATA_AGE) {
                    array_splice($this->latencies[$endpointName], $index, 1);
                    continue;
                }

                $index++;
            }

            if (count($this->latencies[$endpointName]) === 0) {
                unset($this->latencies[$endpointName]);
            }
        }
    }

    public function storeLatency($latency, $operationType)
    {
        if ($operationType != PNOperationType::PNSubscribeOperation && $latency > 0) {
            $endpointName = $this->endpointNameForOperation($operationType);

            $storeDate = microtime(true);

            if (!array_key_exists($endpointName, $this->latencies)) {
                $this->latencies[$endpointName] = [];
            }

            $latencyEntry = [
                "d" => $storeDate,
                "l" => $latency,
            ];

            $this->latencies[$endpointName][] = $latencyEntry;
        }
    }

    public static function averageLatencyFromData($endpointLatencies) {
        $totalLatency = 0;

        foreach ($endpointLatencies as $value) {
            $totalLatency += $value["l"];
        }

        return $totalLatency / count($endpointLatencies);
    }

    private function endpointNameForOperation($operationType)
    {
        $endpoint = "";

        switch($operationType) {
            case PNOperationType::PNPublishOperation:
                $endpoint = "pub";
                break;
            case PNOperationType::PNHistoryOperation:
            case PNOperationType::PNHistoryDeleteOperation:
                $endpoint = "hist";
                break;
            case PNOperationType::PNUnsubscribeOperation:
            case PNOperationType::PNWhereNowOperation:
            case PNOperationType::PNHereNowOperation:
            case PNOperationType::PNGetState:
            case PNOperationType::PNSetStateOperation:
                $endpoint = "pres";
                break;
            case PNOperationType::PNAddChannelsToGroupOperation:
            case PNOperationType::PNRemoveChannelsFromGroupOperation:
            case PNOperationType::PNChannelGroupsOperation:
            case PNOperationType::PNChannelsForGroupOperation:
            case PNOperationType::PNRemoveGroupOperation:
                $endpoint = "cg";
                break;
            case PNOperationType::PNAddPushNotificationsOnChannelsOperation:
            case PNOperationType::PNPushNotificationEnabledChannelsOperation:
            case PNOperationType::PNRemoveAllPushNotificationsOperation:
            case PNOperationType::PNRemovePushNotificationsFromChannelsOperation:
                $endpoint = "push";
                break;
            case PNOperationType::PNAccessManagerAudit:
            case PNOperationType::PNAccessManagerGrant:
                $endpoint = "pam";
                break;
            case PNOperationType::PNAccessManagerGrantToken:
            case PNOperationType::PNAccessManagerRevokeToken:
                $endpoint = "pamv3";
                break;
            default:
                $endpoint = "time";
                break;
        }

        return $endpoint;
    }
}
