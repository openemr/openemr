<?php
/**
 * Turn off/on Birthday alert .
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sharon Cohen <sharonco@matrix.co.il>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Sharon Cohen <sharonco@matrix.co.il>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "/../../interface/globals.php");
use OpenEMR\Reminder\BirthdayReminder;

if (!empty($_POST['pid']) && !empty($_POST['user_id'])) {
    $birthdayReminder = new BirthdayReminder($_POST['pid'], $_POST['user_id']);
    $birthdayReminder->birthdayAlertResponse($_POST['turnOff']);
}
