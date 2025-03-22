<?php

/**
 * Notify event class.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023-2025 Jerry Padgett <sjpadgett@gmail.com>
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
    const ACTIONS_RENDER_NOTIFICATION_POST = 'sendNotification.actions.render.post'; // button
    const JAVASCRIPT_READY_NOTIFICATION_POST = 'sendNotification.javascript.load.post'; // javascript load
    const SEND_NOTIFICATION_BY_SERVICE = 'sendNotification.send';
    const SEND_NOTIFICATION_SERVICE_ONETIME = 'sendNotification.service.onetime';
    const ACTIONS_RENDER_NOTIFICATION_UNIVERSAL = 'sendNotification.actions.render.universal'; // button
    const JAVASCRIPT_LOAD_NOTIFICATION_UNIVERSAL = 'sendNotification.javascript.load.universal';
    const SEND_NOTIFICATION_SERVICE_UNIVERSAL_ONETIME = 'sendNotification.service.universal.onetime';

    private mixed $eventData;
    private array|bool $patientDetails;
    private mixed $pid;
    private string|null $sendNotificationMethod;

    public function __construct($pid, $data = [], $sendMethod = 'both') // 'both', 'sms', 'email'
    {
        $this->pid = $pid ?? 0;
        $this->setEventData($data);
        $this->setSendNotificationMethod($sendMethod);
        $this->setPatientDetails($this->pid);
    }

    /**
     * @param string $sendNotificationMethod
     * @return SendNotificationEvent
     */
    public function setSendNotificationMethod(string $sendNotificationMethod)
    {
        $this->sendNotificationMethod = $sendNotificationMethod;
    }

    /**
     * @param array|bool $pid
     * @return void
     */
    public function setPatientDetails($pid): void
    {
        $this->patientDetails = $this->fetchPatientDetails($pid);
    }

    /**
     * @return array|bool
     */
    public function getPatientDetails(): bool|array
    {
        return $this->patientDetails;
    }

    /**
     * @return string
     */
    public function getSendNotificationMethod(): string
    {
        return $this->sendNotificationMethod;
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
        return $this->eventData ?? [];
    }

    public function setEventData($data)
    {
        $this->eventData = $data;
    }

    /**
     * @return bool|string
     */
    public function getEncodedPatientDetails()
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
     * @param string $sect
     * @param string $v
     * @param string $u
     * @return bool
     */
    public function verifyAcl(string $sect = 'patients', string $v = 'docs', string $u = ''): bool
    {
        return AclMain::aclCheckCore($sect, $v, $u);
    }
}
