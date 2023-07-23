<?php

/**
 * PatientReportEvent class.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\Messaging;

use OpenEMR\Common\Acl\AclMain;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class SendSmsEvent
 *
 * @package OpenEMR\Events\Messaging
 *
 * Enables adding a general send SMS messages dialog. It includes a patient select dialog.
 * See for example: interface/modules/custom_modules/oe-module-faxsms/openemr.bootstrap.php
 */

class SendSmsEvent extends Event
{
    // sms send button
    const ACTIONS_RENDER_SMS_POST = 'sendSMS.actions.render.post';
    // sms send dialog sendSMS('mobile phone number')
    const JAVASCRIPT_READY_SMS_POST = 'sendSMS.javascript.load.post';

    private string $pid;
    private string $recipientMessagePhone;

    public function __construct($pid)
    {
        $this->pid = $pid ?? null;
        $this->recipientMessagePhone = (string)$this->fetchPatientPhone($pid);
    }

    /**
     * @return string
     */
    public function getPid(): string
    {
        return $this->pid;
    }

    /**
     * @param $id
     * @return bool|array|string
     */
    public function fetchPatientPhone($id): bool|array|string
    {
        $query = "SELECT phone_cell FROM patient_data WHERE pid = ?";
        return sqlQuery($query, array($id))['phone_cell'] ?? '';
    }

    /**
     * @return string
     */
    public function getRecipientPhone(): string
    {
        return $this->recipientMessagePhone;
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
