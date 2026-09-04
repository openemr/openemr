<?php

/**
 * Issue list functions and data structure building.
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
 * @link    https://www.open-emr.org
 */

use OpenEMR\Common\Session\SessionWrapperFactory;

function getListById($id, $cols = "*")
{
    return sqlQuery("select " . escape_sql_column_name(process_cols_escape($cols), ['lists']) . " from lists where id=? order by date DESC limit 0,1", [$id]);
}


function addList($pid, $type, $title, $comments, $activity = "1")
{
    $session = SessionWrapperFactory::getInstance()->getActiveSession();
    return sqlInsert("insert into lists (date, pid, type, title, activity, comments, user, groupname) values (NOW(), ?, ?, ?, ?, ?, ?, ?)", [$pid, $type, $title, $activity, $comments, $session->get('authUser'), $session->get('authProvider')]);
}

function disappearList($id): bool
{
    sqlStatement("update lists set activity = '0' where id=?", [$id]);
    return true;
}

function reappearList($id): bool
{
    sqlStatement("update lists set activity = '1' where id=?", [$id]);
    return true;
}

function getListTouch($patient_id, $type)
{
    $ret = sqlQuery("SELECT `date` FROM `lists_touch` WHERE pid=? AND type=?", [$patient_id,$type]);

    if (!empty($ret)) {
        return $ret['date'];
    } else {
        return false;
    }
}

function setListTouch($patient_id, $type): void
{
    $ret = sqlQuery("SELECT `date` FROM `lists_touch` WHERE pid=? AND type=?", [$patient_id,$type]);

    if (!empty($ret)) {
                // Already touched, so can exit
        return;
    } else {
        sqlStatement("INSERT INTO `lists_touch` ( `pid`,`type`,`date` ) VALUES ( ?, ?, NOW() )", [$patient_id,$type]);
    }
}
