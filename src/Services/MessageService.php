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
        $sql .= "     body=?,";
        $sql .= "     pid=?,";
        $sql .= "     groupname=?,";
        $sql .= "     user=?,";
        $sql .= "     assigned_to=?,";
        $sql .= "     message_status=?,";
        $sql .= "     title=?";

        $results = sqlInsert(
            $sql,
            array(
                $this->getFormattedMessageBody($data["from"], $data["to"], $data["body"]),
                $pid,
                $data['groupname'],
                $data['from'],
                $data['to'],
                $data['message_status'],
                $data['title']
            )
        );

        if (!$results) {
            return false;
        }

        return $results;
    }

    public function update($pid, $mid, $data)
    {
        $existingBody = sqlQuery("SELECT body FROM pnotes WHERE id = ?", $mid);

        $sql  = " UPDATE pnotes SET";
        $sql .= "     body=?,";
        $sql .= "     groupname=?,";
        $sql .= "     user=?,";
        $sql .= "     assigned_to=?,";
        $sql .= "     message_status=?,";
        $sql .= "     title=?";
        $sql .= "     WHERE id=?";

        $results = sqlStatement(
            $sql,
            array(
                $existingBody["body"] . $this->getFormattedMessageBody($data["from"], $data["to"], $data["body"]),
                $data['groupname'],
                $data['from'],
                $data['to'],
                $data['message_status'],
                $data['title'],
                $mid
            )
        );

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
