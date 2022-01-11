<?php

/**
 * BirthdayReminder class.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sharon Cohen <sharonco@matrix.co.il>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Sharon Cohen <sharonco@matrix.co.il>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reminder;

class BirthdayReminder
{
    private $pid;

    private $user_id;

    public function __construct($pid, $user_id)
    {
        $this->pid = $pid;
        $this->user_id = $user_id;
    }

    public function birthdayAlertResponse($turnOff)
    {
        if ($turnOff == "true") {
            $date = date('Y-m-d', strtotime("now"));
        } else {
            $date  = date('Y-m-d', strtotime("-1 year"));
        }

        $sql = "REPLACE INTO `patient_birthday_alert` (`pid`, `user_id`, `turned_off_on`) VALUES (?,?,?)";
        $res = sqlStatement($sql, array($this->pid, $this->user_id, $date));
    }

    public function isDisplayBirthdayAlert()
    {
        //Collect dob and if deceased for the patient
        $sql = "SELECT `DOB` FROM `patient_data` WHERE `pid` = ?";
        $res = sqlQuery($sql, array($this->pid));

        if (is_patient_deceased($this->pid)) {
            return false;
        }
        // only need month and day for birthdate
        $today = date('m-d');
        $dobStr = strtotime($res['DOB']);

        // fix for December birthdays check in January
        if (date('m') == '01' && date('m', $dobStr) == '12') {
            $dobStr = "00-" . date('d', $dobStr);
        } else {
            $dobStr = date('m-d', $dobStr);
        }

        if (
            // on and up to 28 days
            (
                $GLOBALS['patient_birthday_alert'] == 3 &&
                $today >= $dobStr &&
                $today <= date('m-d', strtotime('+28 days', strtotime($res['DOB'])))
            ) ||
            // on and after
            (
                $GLOBALS['patient_birthday_alert'] == 2 &&
                $today >= $dobStr
            ) ||
            (
                $GLOBALS['patient_birthday_alert'] == 1 &&
                $today == $dobStr
            )
        ) {
            if ($this->isbirthdayAlertOff()) {
                return false;
            }
            return true;
        }
        return false;
    }

    private function isBirthdayAlertOff()
    {
        $sql = "SELECT `turned_off_on` FROM `patient_birthday_alert` WHERE pid = ? AND user_id = ?";
        $res = sqlQuery($sql, array($this->pid, $this->user_id));
        //if there is result
        if (!empty($res['turned_off_on'])) {
            //if the alert has been turned off this year
            if (date('Y') == date('Y', strtotime($res['turned_off_on']))) {
                return true;
            }
        }
        return false;
    }
}
