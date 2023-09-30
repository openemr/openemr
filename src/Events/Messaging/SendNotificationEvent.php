<?php

/**
 * Notify event class.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/*
 * Example
$event_data = [
    'notification_template_name' => 'Before Appointment', // Create in Portal Dashboard templates
    'event_task' => 'before_appointment',
    'include_email' => true, // To also send an email with SMS
    'alt_content' => '', // use if want to pass in message instead of a template.
    'recipient_phone' => '' // if included will override patient cell phone.
];
$eventDispatcher->dispatch(new SendNotificationEvent($pid, $event_data), SendNotificationEvent::SEND_NOTIFICATION_BY_SERVICE);
*/

namespace OpenEMR\Events\Messaging;

use OpenEMR\Common\Acl\AclMain;
use Symfony\Contracts\EventDispatcher\Event;

class SendNotificationEvent extends Event
{
    const ACTIONS_RENDER_NOTIFICATION_POST = 'sendNotification.actions.render.post';
    const JAVASCRIPT_READY_NOTIFICATION_POST = 'sendNotification.javascript.load.post';
    const SEND_NOTIFICATION_BY_SERVICE = 'sendNotification.send';
    const SEND_NOTIFICATION_SERVICE_ONETIME = 'sendNotification.service.onetime';

    private mixed $pid;
    private array|bool $patientDetails;
    private mixed $eventData;

    public function __construct($pid, $data = [])
    {
        $this->pid = $pid ?? 0;
        $this->eventData = $data;
        $this->patientDetails = $this->fetchPatientDetails($pid);
    }

    /**
     * @param $id
     * @return bool|array
     */
    public function fetchPatientDetails($id): bool|array
    {
        $query = "SELECT fname, lname, phone_cell as phone, email, hipaa_allowsms, hipaa_allowemail FROM patient_data WHERE pid = ?";
        $result = sqlQuery($query, array($id));
        return $result ?? false;
    }

    public function getEventData()
    {
        return $this->eventData;
    }

    /**
     * @return bool|string
     */
    public function getEncodedPatientDetails(): bool|string
    {
        return json_encode($this->patientDetails);
    }

    /**
     * @return string
     */
    public function getPid(): string
    {
        return $this->pid;
    }

    /**
     * @param $sect
     * @param $v
     * @param $u
     * @return bool
     */
    public function verifyAcl($sect = 'admin', $v = 'docs', $u = ''): bool
    {
        return AclMain::aclCheckCore($sect, $v, $u);
    }
}
