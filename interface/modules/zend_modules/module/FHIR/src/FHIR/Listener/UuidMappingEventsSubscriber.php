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
use OpenEMR\Services\CareTeamService;
use OpenEMR\Services\FHIR\Observation\FhirObservationSocialHistoryService;
use OpenEMR\Services\FHIR\Observation\FhirObservationVitalsService;
use OpenEMR\Services\PatientService;
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

    private const RESOURCE = "Observation";

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
        // TODO: @adunsulag do we need to handle the patient save event?
        // the 6 hour cron job will populate any records that have been imported and aren't using the service to create a record

        $record = $event->getPatientData();
        $targetUuid = $record['uuid'];
        // this should never happen that on a create we already have a uuid, but just in case we need to double check
        // and make sure we don't have a mapped care team resource created already for our resource
        $mappedRecords = UuidMapping::getMappedRecordsForTableUUID($targetUuid);
        foreach ($mappedRecords as $record) {
            if ($record['resource'] == CareTeamService::MAPPING_RESOURCE_NAME) {
                return;
            }
        }

        UuidMapping::createMappingRecord($targetUuid, CareTeamService::MAPPING_RESOURCE_NAME, PatientService::TABLE_NAME);
    }

    /**
     * Receives all of the save events from the OpenEMR services (that support the event) and populates any mapping uuids
     * that are needed.
     * @param ServiceSaveEvent $event
     */
    public function onServicePostSaveEvent(ServiceSaveEvent $event, $eventName)
    {
        if ($event->getService() instanceof VitalsService) {
            // now we need to generate our uuid mappings if they don't exist
            $data = $event->getSaveData() ?? [];
            $targetUuid = $data['uuid'] ?? null;
            $keysToCreate = $this->getFhirVitalObservationResourcePaths();
            // vitals as of October 13th 2021 had 27 mapping uuids... so we batch this process
            $this->createMappingRecordsForService($targetUuid, self::RESOURCE, VitalsService::TABLE_VITALS, $keysToCreate);
        }
        if ($event->getService() instanceof SocialHistoryService) {
            // now we need to generate our uuid mappings if they don't exist
            $data = $event->getSaveData() ?? [];
            $targetUuid = $data['uuid'] ?? null;
            $keysToCreate = $this->getFhirSocialObservationResourcePaths();
            $this->createMappingRecordsForService($targetUuid, self::RESOURCE, SocialHistoryService::TABLE_NAME, $keysToCreate);
        }
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

    private function getFhirSocialObservationResourcePaths()
    {
        if (!empty($this->fhirSocialObservationResourcePaths)) {
            return $this->fhirSocialObservationResourcePaths;
        }

        $this->fhirSocialObservationResourcePaths = [];
        foreach (FhirObservationSocialHistoryService::COLUMN_MAPPINGS as $column => $mapping) {
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
        foreach (FhirObservationVitalsService::COLUMN_MAPPINGS as $column => $mapping) {
            $resourcePath = $this->getVitalsResourcePathForCode($mapping['code']);
            $this->fhirVitalObservationResourcePaths[$resourcePath] = $resourcePath;
        }
        return $this->fhirVitalObservationResourcePaths;
    }

    private function getSocialResourcePathForCode($code)
    {
        return "category=" . FhirObservationSocialHistoryService::CATEGORY . "&code=" . $code;
    }

    private function getVitalsResourcePathForCode($code)
    {
        return "category=" . FhirObservationVitalsService::CATEGORY . "&code=" . $code;
    }
}
