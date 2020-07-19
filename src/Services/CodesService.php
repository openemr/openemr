<?php

/**
 * Codes Service
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Alberto Moliner <amolicas79@gmail.com>
 * @copyright Copyright (c) 2020 Alberto Moliner <amolicas79@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use Particle\Validator\Validator;

class CodesService
{

    /**
     * Default constructor.
     */
    public function __construct()
    {
    }

    public function validate($codes)
    {
        $validator = new Validator();

        $validator->required('code_text')->lengthBetween(2, 255);
        $validator->required('code_text_short')->lengthBetween(1, 24);
        $validator->required('code')->lengthBetween(1, 25);
        return $validator->validate($codes);
    }

    public function insert($data)
    {
        $sql = " INSERT INTO codes SET";
        $sql .= "     code_text=?,";
        $sql .= "     code_text_short=?,";
        $sql .= "     code=?,";
        $sql .= "     code_type=?";

        $results = sqlInsert(
            $sql,
            array(
                $data["code_text"],
                $data["code_text_short"],
                $data["code"],
                $data["code_type"]
            )
        );
        
        return $results;
    }

    public function update($cid, $data)
    {
        $sql = " UPDATE codes SET";
        $sql .= "     code_text=?,";
        $sql .= "     code_text_short=?,";
        $sql .= "     code=?,";
        $sql .= "     code_type=?";
        $sql .= "     where id=?";

        return sqlStatement(
            $sql,
            array(
                $data["code_text"],
                $data["code_text_short"],
                $data["code"],
                $data["code_type"],
                $cid
            )
        );
    }

    public function getAll()
    {
        $sqlBindArray = array();
        $sql = "SELECT id,
                   code_text,
                   code_text_short,
                   code, 
                   code_type
                FROM codes";
        $statementResults = sqlStatement($sql);
        $results = array();
        while ($row = sqlFetchArray($statementResults)) {
            array_push($results, $row);
        }
        return $results;
    }

    public function getOne($cid)
    {
        $sql = "SELECT code_text,
                   code_text_short,
                   code,
                   code_type
                FROM codes
                WHERE id = ?";

        return sqlQuery($sql, $cid);
    }
}
