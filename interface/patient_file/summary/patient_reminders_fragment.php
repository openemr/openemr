<?php
/**
 * This simply shows the Clinical Reminder Widget
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once(dirname(__FILE__) . "/../../globals.php");
require_once("$srcdir/reminders.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

//To improve performance and not freeze the session when running this
// report, turn off session writing. Note that php session variables
// can not be modified after the line below. So, if need to do any php
// session work in the future, then will need to remove this line.
session_write_close();

patient_reminder_widget($pid);
