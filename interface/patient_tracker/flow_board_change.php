<?php

/**
 * Flow Board status and room dropdowns: saves the change (see FlowBoardChangeEndpoint).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once __DIR__ . '/../globals.php';
require_once __DIR__ . '/../../library/encounter_events.inc.php';

use OpenEMR\Appointment\FlowBoard\FlowBoardChange;
use OpenEMR\Appointment\FlowBoard\FlowBoardChangeEndpoint;
use OpenEMR\Appointment\FlowBoard\LegacyFlowBoardStore;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

$session = SessionWrapperFactory::getInstance()->getActiveSession();
CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);
// The same gate as the status popup.
if (!AclMain::aclCheckCore('patients', 'appt')) {
    AccessDeniedHelper::deny('flow_board_change.php: patients/appt required');
}

$user = $session->get('authUser');
$change = new FlowBoardChange(
    new LegacyFlowBoardStore(),
    OEGlobalsBag::getInstance()->getInt('auto_create_new_encounters') !== 0,
    ServiceContainer::getClock()
);
(new FlowBoardChangeEndpoint($change))->respond(CurrentRequest::get(), is_string($user) ? $user : '')->send();
