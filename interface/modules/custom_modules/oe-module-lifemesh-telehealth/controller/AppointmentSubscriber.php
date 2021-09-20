<?php
/*
 *
 * @package      OpenEMR
 * @link               https://www.open-emr.org
 *
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2021 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

namespace OpenEMR\Modules\LifeMesh;

use DateTimeZone;
use OpenEMR\Events\Appointments\AppointmentSetEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

require_once dirname(__FILE__)."/Container.php";

class AppointmentSubscriber implements EventSubscriberInterface
{
    private $credentials;
    private $patientcell;
    private $retrieve;
    private $timezone;

    /**
     * @return string[]
     */
    public static function getSubscribedEvents() : array
    {
        return [
          AppointmentSetEvent::EVENT_HANDLE  => 'isEventTelehealth'
        ];
    }

    /**
     *
     */
    public function __construct()
    {
        $db = new Container();
        $this->retrieve = $db->getDatabase();
        $this->timezone = $this->retrieve->getTimeZone();
        if ($this->retrieve->doesTableExist() == 'exist') {
            $this->credentials = $this->retrieve->getCredentials();
        }

    }

    /**
     * @param AppointmentSetEvent $event
     */
    public function isEventTelehealth(AppointmentSetEvent $event)
    {
        $appointmentdata = $event->givenAppointmentData();

        if (stristr($appointmentdata['form_title'], 'telehealth')) {

            $pid = $appointmentdata['form_pid'];
            $comm_data = $this->retrieve->getPatientDetails($pid);
            $patient = explode(", ", $appointmentdata['form_patient']);
            $eventdatetime = $appointmentdata['form_date'] . " " . $appointmentdata['form_hour'] . ":" . $appointmentdata['form_minute'] . ":00";
            $hour = $appointmentdata['form_hour'] . ":" . $appointmentdata['form_minute'] . ":00";
            $phone = preg_replace('/[^0-9]/', '', $comm_data['phone_cell']);
            $this->patientcell = "+" . $GLOBALS['phone_country_code'] . $phone;
                //check to see if the event has been scheduled if not enter data
            $checkExistingAppointment = $this->retrieve->hasAppointment($event->eid);
            if (empty($checkExistingAppointment)) {
                    $creatsession = new AppDispatch();
                    $creatsession->apiRequestSession(
                        $this->credentials[1],
                        $this->credentials[0],
                        'createSession',
                        $GLOBALS['unique_installation_id'],
                        $event->eid,
                        $this->setEventUtcTime($eventdatetime),
                        $this->setEventLocalTime($eventdatetime),
                        $patient[0],
                        $patient[1],
                        $comm_data['email'],
                        $this->patientcell
                    );
            } elseif($checkExistingAppointment['event_date'] != $appointmentdata['form_date'] ||
            $checkExistingAppointment['time'] != $hour) {
                //update lifemesh if time or date of the appointment has changed
                $reschedule_session = new AppDispatch();
                $reschedule_session->rescheduleSession(
                    $this->credentials[1],
                    $this->credentials[0],
                    $GLOBALS['unique_installation_id'],
                    $this->setEventUtcTime($eventdatetime),
                    $this->setEventLocalTime($eventdatetime),
                    $event->eid,
                    'rescheduleSession'
                );
                //if scheduled update the existing schedule with new date and time.
                $this->retrieve->updateSession($event->eid, $this->setEventLocalTime($eventdatetime));
            }
        }
    }

    /**
     * @param $eventdatetime
     * @return string
     */
    private function setEventUtcTime($eventdatetime)
    {
        $z = 'UTC';
        $format = "Y-m-d\TH:i:s\Z";
        $date = date_create($eventdatetime, new DateTimeZone($this->timezone));
        $date->setTimezone(new DateTimeZone($z));
        return $date->format($format);
    }

    /**
     * @param $eventdatetime
     * @return string
     */
    private function setEventLocalTime($eventdatetime)
    {
        $newDateTime = date_create($eventdatetime, new DateTimeZone($this->timezone));
        return $newDateTime->format("Y-m-d\TH:i:s");
    }


}
