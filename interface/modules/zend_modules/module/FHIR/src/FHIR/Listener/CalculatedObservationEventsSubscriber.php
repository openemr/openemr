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
use OpenEMR\Events\Services\ServiceSaveEvent;
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
        $serviceClass = $service::class;
        match ($serviceClass) {
            VitalsService::class => $this->createVitalCalculatedRecords($event->getSaveData()),
            default => null
        };
    }
    public function createVitalCalculatedRecords(array $vitalRecord): void
    {
        try {
            $vitalRecord['encounter'] = intval($vitalRecord['eid'] ?? 0);
            $vitalCalculations = new VitalsCalculatedService();
            $vitalCalculations->saveCalculatedVitalsForRecord($vitalRecord);
        } catch (\Exception $exception) {
            (new SystemLogger())->errorLogCaller("Failed to save calculated record ", ['exception' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(), 'form_vitals.id' => $vitalRecord['id']]);
        }
    }
}
