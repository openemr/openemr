<?php

/**
 * This file contains a function to keep track of which issues
 * types get modified.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../interface/globals.php");

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;

$session = SessionWrapperFactory::getInstance()->getActiveSession();
CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);

$request = CurrentRequest::get()->request;
$postedPatientId = $request->getString('patient_id', '');
$type = $request->getString('type', '');

//  IF there is a pid and type then will set the entry in lists_touch table.
//  The "None known" marker is a clinical write; mirror the issue-write ACL
//  the UI applies to the noneCheck checkbox, and bind the write to the active
//  session patient so an authorized caller cannot stamp another patient's
//  reviewed-list state.
if ($postedPatientId !== '' && $type !== '') {
    if (!AclMain::aclCheckIssue($type, '', 'write')) {
        AccessDeniedHelper::deny('ACL check failed for issue write on lists_touch');
    }
    $sessionPidRaw = $session->get('pid');
    $sessionPid = is_scalar($sessionPidRaw) ? (string) $sessionPidRaw : '';
    if ($sessionPid === '' || $postedPatientId !== $sessionPid) {
        AccessDeniedHelper::deny('lists_touch patient_id does not match the active session patient');
    }
    setListTouch($postedPatientId, $type);
}
