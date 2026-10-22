<?php

/**
 * Issue list functions and data structure building.
 *
 * Thin delegators kept for the existing call sites of getListById(), addList(),
 * disappearList(), reappearList(), getListTouch() and setListTouch(). The bodies live in
 * PatientIssuesService; see the migration tracker, openemr/openemr#11674.
 *
 * The data structure is the $ISSUE_TYPES array.
 * The $ISSUE_TYPES array is built from the issue_types sql table and provides
 * abstraction of issue types to allow customization.
 * <pre>Attributes of the $ISSUE_TYPES array are:
 *  key - The identifier. (Do NOT create element with token 'prescription_erx' since this is reserved by NewCropRx Module that leverages lists_touch table to support MU calculations)
 *  0   - The plural title.
 *  1   - The singular title.
 *  2   - The abbreviated title (one letter abbreviation).
 *  3   - Style ('0 - Normal; 1 - Simplified: only title, start date, comments and an Active checkbox;no diagnosis, occurrence, end date, referred-by or sports fields.; 2 - Football Injury; 3 and 4 are IPPF specific)
 *  4   - Force show this issue category in the patient summary screen even if empty (setting to 1 will force it to show and setting it to 0 will turn this off).
 *  5   - ACO for this type, for example "patients|med".
 *
 * Note there is a mechanism to show whether a category is explicitly set to
 * 'Nothing' via the getListTouch() and setListTouch() functions that store
 * applicable information in the lists_touch sql table.
 *
 *  </pre>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author  Teny <teny@zhservices.com>
 * @author  Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @link    https://www.open-emr.org
 */

use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Services\PatientIssuesService;

/**
 * Most recent `lists` row by id; false when $id is not int|string or unmatched.
 */
function getListById($id, $cols = "*")
{
    if (!is_int($id) && !is_string($id)) {
        return false;
    }
    $cols = is_scalar($cols) ? (string) $cols : '*';

    return PatientIssuesService::getListById($id, $cols) ?? false;
}


/**
 * Creates a `lists` row using the current session's user/groupname. Returns 0 without
 * touching the database when $pid or $activity is not int|string.
 */
function addList($pid, $type, $title, $comments, $activity = "1"): int
{
    if ((!is_int($pid) && !is_string($pid)) || (!is_int($activity) && !is_string($activity))) {
        return 0;
    }
    $type = is_scalar($type) ? (string) $type : '';
    $title = is_scalar($title) ? (string) $title : '';
    $comments = is_scalar($comments) ? (string) $comments : '';

    $session = SessionWrapperFactory::getInstance()->getActiveSession();
    $user = $session->get('authUser');
    $user = is_string($user) ? $user : null;
    $groupname = $session->get('authProvider');
    $groupname = is_string($groupname) ? $groupname : null;

    return PatientIssuesService::addList($pid, $type, $title, $comments, $user, $groupname, $activity);
}

/**
 * Sets a `lists` row's `activity` flag to '0'; does nothing and returns false when $id is not
 * int|string.
 */
function disappearList($id): bool
{
    if (!is_int($id) && !is_string($id)) {
        return false;
    }

    PatientIssuesService::disappearList($id);
    return true;
}

/**
 * Sets a `lists` row's `activity` flag to '1'; does nothing and returns false when $id is not
 * int|string.
 */
function reappearList($id): bool
{
    if (!is_int($id) && !is_string($id)) {
        return false;
    }

    PatientIssuesService::reappearList($id);
    return true;
}

/**
 * The `lists_touch` timestamp for a patient/type; false when $patient_id is not int|string or
 * unmatched.
 */
function getListTouch($patient_id, $type)
{
    if (!is_int($patient_id) && !is_string($patient_id)) {
        return false;
    }
    $type = is_scalar($type) ? (string) $type : '';

    return PatientIssuesService::getListTouch($patient_id, $type) ?? false;
}

/**
 * Records that a patient/type combination has been touched, unless already touched; does
 * nothing when $patient_id is not int|string.
 */
function setListTouch($patient_id, $type): void
{
    if (!is_int($patient_id) && !is_string($patient_id)) {
        return;
    }
    $type = is_scalar($type) ? (string) $type : '';

    PatientIssuesService::setListTouch($patient_id, $type);
}
