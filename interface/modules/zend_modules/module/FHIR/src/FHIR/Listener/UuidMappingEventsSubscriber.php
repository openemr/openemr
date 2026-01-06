<?php

/**
 * UuidMappingEventsSubscriber.php  Listens to system data save events where we may have a FHIR mapping resource that needs
 * a uuid populated.  The subscriber checks to see if uuids are needed and then populates them if they are.  This is
 * done on each save.  If for some reason an event isn't fired or is dropped before the subscribe receives it, a CRON
 * job goes through every 6 hours and populates the uuids so nothing is missed.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\ZendModules\FHIR\Listener;

use OpenEMR\Common\Uuid\UuidMapping;
use OpenEMR\Events\Patient\PatientCreatedEvent;
use OpenEMR\Events\Services\ServiceSaveEvent;
use OpenEMR\Services\FHIR\Observation\FhirObservationHistorySdohService;
use OpenEMR\Services\FHIR\Observation\FhirObservationPatientService;
use OpenEMR\Services\FHIR\Observation\FhirObservationSocialHistoryService;
use OpenEMR\Services\FHIR\Observation\FhirObservationVitalsService;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\SDOH\HistorySdohService;
use OpenEMR\Services\SocialHistoryService;
use OpenEMR\Services\VitalsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UuidMappingEventsSubscriber implements EventSubscriberInterface
{
    /**
     * @var array Holds the cached resource paths for vital observation records
     */
    private $fhirVitalObservationResourcePaths = [];

    /**
     * @var array Holds the cached resource paths for social history observation records
     */
    private $fhirSocialObservationResourcePaths = [];

    /**
     * @var array Holds the cached resource paths for SDOH observation records
     */
    private array $fhirSdohObservationResourcePaths = [];

    private const RESOURCE_OBSERVATION = "Observation";

    /**
     * @var array Holds the cached resource paths for patient data observation records
     */
    private array $fhirPatientDataObservationResourcePaths = [];

    public function __construct()
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            ServiceSaveEvent::EVENT_POST_SAVE => 'onServicePostSaveEvent',
            PatientCreatedEvent::EVENT_HANDLE => 'onPatientCreatedEvent'
        ];
    }

    public function onPatientCreatedEvent(PatientCreatedEvent $event)
    {
        $record = $event->getPatientData();
        $targetUuid = $record['uuid'];
        // this should never happen that on a create we already have a uuid, but just in case we need to double check
        // and make sure we don't have a mapped care team resource created already for our resource
        $mappedRecords = UuidMapping::getMappedRecordsForTableUUID($targetUuid);
        $resourceRecordsToCreate = [
            'Observation' => true
        ];
        foreach ($mappedRecords as $record) {
            if (in_array($record['resource'], $resourceRecordsToCreate)) {
                $resourceRecordsToCreate[$record['resource']] = false;
            }
        }
        // for observations we have to grab a bunch of keys
        if ($resourceRecordsToCreate['Observation']) {
            $keysToCreate = $this->getFhirPatientObservationResourcePaths();
            $this->createMappingRecordsForService($targetUuid, self::RESOURCE_OBSERVATION, PatientService::TABLE_NAME, $keysToCreate);
        }
    }

    /**
     * Receives all of the save events from the OpenEMR services (that support the event) and populates any mapping uuids
     * that are needed.
     * @param ServiceSaveEvent $event
     */
    public function onServicePostSaveEvent(ServiceSaveEvent $event, $eventName)
    {
        $service = $event->getService();
        $serviceClass = $service::class;
        $data = $event->getSaveData();
        $targetUuid = $data['uuid'] ?? null;

        match ($serviceClass) {
            VitalsService::class => $this->createMappingRecordsForService($targetUuid, self::RESOURCE_OBSERVATION, VitalsService::TABLE_VITALS, $this->getFhirVitalObservationResourcePaths()),
            SocialHistoryService::class => $this->createMappingRecordsForService($targetUuid, self::RESOURCE_OBSERVATION, SocialHistoryService::TABLE_NAME, $this->getFhirSocialObservationResourcePaths()),
            HistorySdohService::class => $this->createMappingRecordsForService($targetUuid, self::RESOURCE_OBSERVATION, HistorySdohService::TABLE_NAME, $this->getFhirSdohObservationResourcePaths()),
            default => null
        };
    }

    private function createMappingRecordsForService($targetUuid, $resource, $table, $keysToCreate)
    {
        $records = UuidMapping::getMappedRecordsForTableUUID($targetUuid);
        foreach ($records as $mappedRecord) {
            $key = $mappedRecord['resource_path'];
            if ($mappedRecord['resource'] == $resource && isset($keysToCreate[$key])) {
                unset($keysToCreate[$key]);
            }
        }

        // now let's create our uuids here
        $values = array_values($keysToCreate);
        if (!empty($values)) {
            UuidMapping::createMappingRecordForResourcePaths($targetUuid, $resource, $table, $values);
        }
    }

    protected function getFhirSdohObservationResourcePaths()
    {
        if (!empty($this->fhirSdohObservationResourcePaths)) {
            return $this->fhirSdohObservationResourcePaths;
        }

        $this->fhirSdohObservationResourcePaths = [];
        $service = new FhirObservationHistorySdohService();
        $columnMappings = $service->getColumnMappings();
        foreach ($columnMappings as $mapping) {
            if (isset($mapping['category']) && isset($mapping['code'])) {
                $resourcePath = "category=" . $mapping['category'] . '&code=' . $mapping['code'];
                $this->fhirSdohObservationResourcePaths[$resourcePath] = $resourcePath;
            }
        }
        return $this->fhirSdohObservationResourcePaths;
    }

    private function getFhirSocialObservationResourcePaths()
    {
        if (!empty($this->fhirSocialObservationResourcePaths)) {
            return $this->fhirSocialObservationResourcePaths;
        }

        $this->fhirSocialObservationResourcePaths = [];
        foreach (FhirObservationSocialHistoryService::COLUMN_MAPPINGS as $mapping) {
            $resourcePath = $this->getSocialResourcePathForCode($mapping['code']);
            $this->fhirSocialObservationResourcePaths[$resourcePath] = $resourcePath;
        }
        return $this->fhirSocialObservationResourcePaths;
    }

    private function getFhirVitalObservationResourcePaths()
    {
        if (!empty($this->fhirVitalObservationResourcePaths)) {
            return $this->fhirVitalObservationResourcePaths;
        }

        $this->fhirVitalObservationResourcePaths = [];
        foreach (FhirObservationVitalsService::COLUMN_MAPPINGS as $mapping) {
            $resourcePath = $this->getVitalsResourcePathForCode($mapping['code']);
            $this->fhirVitalObservationResourcePaths[$resourcePath] = $resourcePath;
        }
        return $this->fhirVitalObservationResourcePaths;
    }

    private function getFhirPatientObservationResourcePaths()
    {
        if (empty($this->fhirPatientDataObservationResourcePaths)) {
            $resourcePaths = [];
            foreach (FhirObservationPatientService::COLUMN_MAPPINGS as $mapping) {
                $resourcePath = $this->getSocialResourcePathForCode($mapping['code']);
                $resourcePaths[$resourcePath] = $resourcePath;
            }
            $this->fhirPatientDataObservationResourcePaths = $resourcePaths;
        }

        return $this->fhirPatientDataObservationResourcePaths;
    }

    private function getSocialResourcePathForCode($code)
    {
        return "category=" . FhirObservationSocialHistoryService::CATEGORY . "&code=" . $code;
    }

    private function getVitalsResourcePathForCode($code)
    {
        return "category=" . FhirObservationVitalsService::CATEGORY . "&code=" . $code;
    }

    private function getPatientResourcePathForCode($code)
    {
        return "category=" . FhirObservationPatientService::CATEGORY_SOCIAL_HISTORY . "&code=" . $code;
    }
}
