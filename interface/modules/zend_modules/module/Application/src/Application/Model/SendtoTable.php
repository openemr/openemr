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
use OpenEMR\Common\Database\QueryUtils;

class SendtoTable extends AbstractTableGateway
{
    /**
     * @return list<array<string, mixed>>
     */
    public function getFacility(): array
    {
        $sql = "SELECT * FROM facility ORDER BY name";
        return QueryUtils::fetchRecords($sql);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getUsers(string $type): array
    {
        $sql = "SELECT * FROM users WHERE abook_type = ?";
        return QueryUtils::fetchRecords($sql, [$type]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getFaxRecievers(): array
    {
        $sql = "SELECT option_id, title FROM list_options WHERE list_id = 'abook_type'";
        return QueryUtils::fetchRecords($sql);
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
