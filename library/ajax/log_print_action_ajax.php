<?php

/**
 * AJAX handler for logging a printing action.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../interface/globals.php");

use Html2Text\Html2Text;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Session\SessionWrapperFactory;

$session = SessionWrapperFactory::getInstance()->getActiveSession();
if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"], session: $session)) {
    CsrfUtils::csrfNotVerified();
}

EventAuditLogger::getInstance()->newEvent("print", $session->get('authUser'), $session->get('authProvider'), 1, (new Html2Text($_POST['comments'], ['do_links' => 'none', 'width' => 0]))->getText());
