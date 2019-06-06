<?php
/**
 * PrescriptionService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sharon Cohen
 * @copyright Copyright (c) 2019
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


namespace OpenEMR\Services;

use Particle\Validator\Validator;

class PrescriptionService
{
    public function __construct()
    {
    }

    public function getAll($pid)
    {
        $sql = "SELECT * FROM prescriptions WHERE patient_id=? and active = 1";

        $statementResults = sqlStatement($sql, array($pid));

        $results = array();
        while ($row = sqlFetchArray($statementResults)) {
            array_push($results, $row);
        }

        return $results;
    }



}
