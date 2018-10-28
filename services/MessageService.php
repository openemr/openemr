<?php
/**
 * MessageService
 *
 * Copyright (C) 2018 Matthew Vita <matthewvita48@gmail.com>
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
 * @author  Matthew Vita <matthewvita48@gmail.com>
 * @link    http://www.open-emr.org
 */

namespace OpenEMR\Services;

use Particle\Validator\Validator;

class MessageService
{
    public function __construct()
    {

    }

    public function validate($message)
    {
        $validator = new Validator();

        $validator->required('body')->lengthBetween(2, 65535);
        $validator->required('to')->lengthBetween(2, 255);
        $validator->required('from')->lengthBetween(2, 255);
        $validator->required('groupname')->lengthBetween(2, 255);
        $validator->required('title')->lengthBetween(2, 255);
        $validator->required('message_status')->lengthBetween(2, 20);

        return $validator->validate($message);
    }

    public function getFormattedMessageBody($from, $to, $body)
    {
        return "\n" . date("Y-m-d H:i") . " (" . $from . " to " . $to . ") " . $body;
    }

    public function insert($pid, $data)
    {
        $sql  = " INSERT INTO pnotes SET";
        $sql .= "     date=NOW(),";
        $sql .= "     activity=1,";
        $sql .= "     authorized=1,";
        $sql .= "     body='" . add_escape_custom($this->getFormattedMessageBody($data["from"], $data["to"], $data["body"])) . "',";
        $sql .= "     pid='" . add_escape_custom($pid) . "',";
        $sql .= "     groupname='" . add_escape_custom($data['groupname']) . "',";
        $sql .= "     user='" . add_escape_custom($data['from']) . "',";
        $sql .= "     assigned_to='" . add_escape_custom($data['to']) . "',";
        $sql .= "     message_status='" . add_escape_custom($data['message_status']) . "',";
        $sql .= "     title='" . add_escape_custom($data['title']) . "'";

        $results = sqlInsert($sql);

        if (!$results) {
            return false;
        }

        return $results;
    }

    public function update($pid, $mid, $data)
    {
        $existingBody = sqlQuery("SELECT body FROM pnotes WHERE id = ?", $mid);

        $sql  = " UPDATE pnotes SET";
        $sql .= "     body='" . add_escape_custom($existingBody["body"] . $this->getFormattedMessageBody($data["from"], $data["to"], $data["body"])) . "',";
        $sql .= "     groupname='" . add_escape_custom($data['groupname']) . "',";
        $sql .= "     user='" . add_escape_custom($data['from']) . "',";
        $sql .= "     assigned_to='" . add_escape_custom($data['to']) . "',";
        $sql .= "     message_status='" . add_escape_custom($data['message_status']) . "',";
        $sql .= "     title='" . add_escape_custom($data['title']) . "'";
        $sql .= "     WHERE id='" . add_escape_custom($mid) . "'";

        $results = sqlStatement($sql);

        if (!$results) {
            return false;
        }

        return $results;
    }

    public function delete($pid, $mid)
    { 
        $sql = "UPDATE pnotes SET deleted=1 WHERE pid=? AND id=?";

        return sqlStatement($sql, array($pid, $mid));
    }
}