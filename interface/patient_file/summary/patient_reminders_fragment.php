<?php

/**
 * This simply shows the Clinical Reminder Widget
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once(\OpenEMR\Core\OEGlobalsBag::getInstance()->getSrcDir() . "/reminders.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$pid = $session->get('pid', 0);
CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);

patient_reminder_widget($pid);
