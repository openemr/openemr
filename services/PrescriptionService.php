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

    public function getAllFormattedRow($pid){
        include_once('../library/options.inc.php');
        $rxs=[];
        $serviceResult = $this->getAll($pid);
        foreach ($serviceResult as $row){
            $runit = generate_display_field(array('data_type'=>'1','list_id'=>'drug_units'), $row['unit']);
            $rin = generate_display_field(array('data_type'=>'1','list_id'=>'drug_form'), $row['form']);
            $rroute = generate_display_field(array('data_type'=>'1','list_id'=>'drug_route'), $row['route']);
            $rint = generate_display_field(array('data_type'=>'1','list_id'=>'drug_interval'), $row['interval']);
            $unit='';
            if ($row['size'] > 0) {
                $unit = text($row['size']) . " " . $runit . " ";
            }
            $rxs[] = $unit . " " . text($row['dosage']) . " " . $rin . " " . $rroute . " " . $rint;
        }
        return $rxs;
    }

}
