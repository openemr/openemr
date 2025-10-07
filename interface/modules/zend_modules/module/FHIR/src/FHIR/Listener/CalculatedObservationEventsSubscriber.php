<?php

/**
 * CalculatedObservationEventsSubscriber.php  Listens to system data save events where we may have a FHIR Observation
 * resource that is being saved that has calculated fields (IE records that are derivative data of the primary source)
 * This
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author   Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Discover and Change, Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\ZendModules\FHIR\Listener;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidMapping;
use OpenEMR\Events\Patient\PatientCreatedEvent;
use OpenEMR\Events\Services\ServiceSaveEvent;
use OpenEMR\Services\CareTeamService;
use OpenEMR\Services\FHIR\Observation\FhirObservationHistorySdohService;
use OpenEMR\Services\FHIR\Observation\FhirObservationPatientService;
use OpenEMR\Services\FHIR\Observation\FhirObservationSocialHistoryService;
use OpenEMR\Services\FHIR\Observation\FhirObservationVitalsService;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\SDOH\HistorySdohService;
use OpenEMR\Services\SocialHistoryService;
use OpenEMR\Services\VitalsCalculatedService;
use OpenEMR\Services\VitalsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CalculatedObservationEventsSubscriber implements EventSubscriberInterface
{
    public function __construct()
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            ServiceSaveEvent::EVENT_POST_SAVE => 'onServicePostSaveEvent',
        ];
    }

    /**
     * Receives all of the save events from the OpenEMR services (that support the event) and populates any mapping uuids
     * that are needed.
     * @param ServiceSaveEvent $event
     */
    public function onServicePostSaveEvent(ServiceSaveEvent $event, $eventName)
    {
        $service = $event->getService();
        $serviceClass = get_class($service);
        match ($serviceClass) {
            VitalsService::class => $this->createVitalCalculatedRecords($event->getSaveData()),
            default => null
        };
    }
    public function createVitalCalculatedRecords(array $vitalRecord): void {
        try {
            $vitalCalculations = new VitalsCalculatedService();
            $vitalCalculations->saveCalculatedVitalsForRecord($vitalRecord);
        }
        catch (\Exception $exception) {
            (new SystemLogger())->errorLogCaller("Failed to save calculated record ", ['exception' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(), 'form_vitals.id' => $vitalRecord['id']]);
        }
    }
}
