<?php

/**
 * interface/modules/zend_modules/module/Application/src/Application/Model/SendtoTable.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    BASIL PT <basil@zhservices.com>
 * @copyright Copyright (c) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Application\Model;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Application\Model\ApplicationTable;
use Laminas\Db\Adapter\Driver\Pdo\Result;

class SendtoTable extends AbstractTableGateway
{
    /*
    * getFacility
    * @return array facility
    *
    **/
    public function getFacility()
    {
        $appTable   = new ApplicationTable();
        $sql        = "SELECT * FROM facility ORDER BY name";
        $result     = $appTable->zQuery($sql);
        return $result;
    }


    /*
    * getUsers
    * @param String $type
    * @return array
    *
    **/
    public function getUsers($type)
    {
        $appTable   = new ApplicationTable();
        $sql        = "SELECT * FROM users WHERE abook_type = ?";
        $result     = $appTable->zQuery($sql, [$type]);
        return $result;
    }


    /*
    * getFaxRecievers
    * @return array fax reciever types
    *
    **/
    public function getFaxRecievers()
    {
        $appTable   = new ApplicationTable();
        $sql        = "SELECT option_id, title FROM list_options WHERE list_id = 'abook_type'";
        $result     = $appTable->zQuery($sql);
        return $result;
    }

    /*
    * CCDA component list
    *
    * @param    $type
    * @return   $components     Array of CCDA components
    **/
    public function getCCDAComponents($type)
    {
        $components = [];
        // removed dependency on the ccda_table_mapping table sjp 07/25/25
        if ($type == 0) {
            // sections
            $components = [
                'progress_note' => 'Progress Notes',
                'consultation_note' => 'Consultation Note',
                'continuity_care_document' => 'Continuity Care Document',
                'diagnostic_image_reporting' => 'Diagnostic Image Reporting',
                'discharge_summary' => 'Discharge Summary',
                'history_physical_note' => 'History and Physical Note',
                'operative_note' => 'Operative Note',
                'procedure_note' => 'Procedure Note',
                'unstructured_document' => 'Unstructured Document',
            ];
        } elseif ($type == 1) {
            // entry components
            $components = [
                'allergies' => 'Allergies',
                'medications' => 'Medications',
                'problems' => 'Problems',
                'immunizations' => 'Immunizations',
                'procedures' => 'Procedures',
                'results' => 'Results',
                'plan_of_care' => 'Plan Of Care',
                'vitals' => 'Vitals',
                'social_history' => 'Social History',
                'encounters' => 'Encounters',
                'functional_status' => 'Functional Status',
                'referral' => 'Reason for Referral',
                'instructions' => 'Instructions',
                'medical_devices' => 'Medical Devices',
                'goals' => 'Goals',
                'payers' => 'Health Insurance Providers',
                'advance_directives' => 'Advance Directives',
            ];
        }
        return $components;
    }
}
