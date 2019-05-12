<?php
/**
 * MessageService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


namespace OpenEMR\Services;

use Particle\Validator\Validator;

class LayoutService
{
    public function __construct()
    {
    }

    public function validate($message)
    {
//        $validator = new Validator();
//
//        $validator->required('body')->lengthBetween(2, 65535);
//        $validator->required('to')->lengthBetween(2, 255);
//        $validator->required('from')->lengthBetween(2, 255);
//        $validator->required('groupname')->lengthBetween(2, 255);
//        $validator->required('title')->lengthBetween(2, 255);
//        $validator->required('message_status')->lengthBetween(2, 20);
//
//        return $validator->validate($message);
    }

    public function getGroupsListByFormId($form_id)
    {
        $sql = "SELECT grp_group_id,grp_title FROM layout_group_properties WHERE grp_form_id=? AND grp_group_id > 0 AND grp_activity = 1 ";

        $statementResults = sqlStatement($sql, array($form_id));

        $results = array();
        while ($row = sqlFetchArray($statementResults)) {
            array_push($results, $row);
        }

        return $results;
    }

    public function getFieldsByFormId($form_id, $group_id)
    {
        $sql = "SELECT form_id, field_id, group_id, title, seq, description FROM layout_options WHERE form_id=? AND group_id=? ";

        $statementResults = sqlStatement($sql, array($form_id, $group_id));

        $results = array();
        while ($row = sqlFetchArray($statementResults)) {
            array_push($results, $row);
        }

        return $results;
    }


    public function getFormattedMessageBody($from, $to, $body)
    {
        return "\n" . date("Y-m-d H:i") . " (" . $from . " to " . $to . ") " . $body;
    }

    public function insert($pid, $data)
    {
//        if (!$results) {
//            return false;
//        }
//
//        return $results;
    }

    public function update($pid, $mid, $data)
    {
//        if (!$results) {
//            return false;
//        }
//
//        return $results;
    }

    public function delete($pid, $mid)
    {
    }
}
