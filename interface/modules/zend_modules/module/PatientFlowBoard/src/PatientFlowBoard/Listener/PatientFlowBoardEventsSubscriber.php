<?php

/**
 * PatientFlowBoardEventsSubscriber Listens to system data save events for the patient flow board and updates flow board
 * data.  It can be used to listen to any patient flow board events and trigger system functionality for the flow board
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @author    Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (C) 2015 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\ZendModules\PatientFlowBoard\Listener;

use OpenEMR\Events\Services\ServiceSaveEvent;
use OpenEMR\Services\AppointmentService;
use OpenEMR\Services\PatientTrackerService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PatientFlowBoardEventsSubscriber implements EventSubscriberInterface
{
   /**
     * @return array<string, mixed> The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        $events = [];
        // we only subscribe to this event if the drug_screen is enabled as a feature
        if ($GLOBALS['drug_screen']) {
            $events[ServiceSaveEvent::EVENT_POST_SAVE] = 'onServicePostSaveEvent';
        }
        return $events;
    }

    /**
     * Receives all of the save events from the OpenEMR services (that support the event) and populates any mapping uuids
     * that are needed.
     * @param ServiceSaveEvent $event
     */
    public function onServicePostSaveEvent(ServiceSaveEvent $event, $eventName)
    {
        if (!$event->getService() instanceof PatientTrackerService) {
            return;
        }

        $trackerData = $event->getSaveData();
        $element = $trackerData['element'] ?? [];
        $status = $element['status'] ?? null;

        if (!empty($status)) {
            $apptService = new AppointmentService();
            if ($apptService->isCheckInStatus($status)) {
                $yearly_limit = $GLOBALS['maximum_drug_test_yearly'];
                $percentage = $GLOBALS['drug_testing_percentage'];
                $this->random_drug_test($trackerData['id'], $percentage, $yearly_limit);
            }
        }
    }

    /**
     * @param $tracker_id
     * @param $percentage
     * @param $yearly_limit
     */
    private function random_drug_test($tracker_id, $percentage, $yearly_limit)
    {

        # Check if randomization has not yet been done (is random_drug_test NULL). If already done, then exit.
        $drug_test_done = sqlQuery("SELECT `random_drug_test`, pid from patient_tracker " .
            "WHERE id =? ", array($tracker_id));
        $Patient_id = $drug_test_done['pid'];

        if (is_null($drug_test_done['random_drug_test'])) {
            # get a count of the number of times the patient has been screened.
            if ($yearly_limit > 0) {
                # check to see if screens are within the current year.
                $lastyear = date("Y-m-d", strtotime("-1 year", strtotime(date("Y-m-d H:i:s"))));
                $drug_test_count = sqlQuery("SELECT COUNT(*) from patient_tracker " .
                    "WHERE drug_screen_completed = '1' AND apptdate >= ? AND pid =? ", array($lastyear,$Patient_id));
            }

            # check that the patient is not at the yearly limit.
            if ($drug_test_count['COUNT(*)'] >= $yearly_limit && ($yearly_limit > 0)) {
                $drugtest = 0;
            } else {
                # Now do the randomization and set random_drug_test to the outcome.

                $drugtest = 0;
                $testdrug = mt_rand(0, 100);
                if ($testdrug <= $percentage) {
                    $drugtest = 1;
                }
            }

            #Update the tracker file.
            sqlStatement("UPDATE patient_tracker SET " .
                "random_drug_test = ? " .
                "WHERE id =? ", array($drugtest,$tracker_id));
        }
    }
}
