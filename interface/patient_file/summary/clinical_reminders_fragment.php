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

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/clinical_rules.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;

$session = SessionWrapperFactory::instance()->getWrapper();

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"], $session->getSymfonySession())) {
    CsrfUtils::csrfNotVerified();
}

clinical_summary_widget($pid, "reminders-due", '', 'default', $session->get('authUser'));
