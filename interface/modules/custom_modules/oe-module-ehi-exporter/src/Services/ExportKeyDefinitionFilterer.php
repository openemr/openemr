<?php

/**
 * Responsible for filtering and customizing an ExportKeyDefinition for custom table logic especially our list_options
 * table where the foreign keys are not unique or need special processing such as denormalized keys.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com
 * @copyright Copyright (c) 2023 OpenEMR Foundation, Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\EhiExporter\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Modules\EhiExporter\Models\ExportKeyDefinition;

class ExportKeyDefinitionFilterer
{
    private $issueTypes;

    // most of our foreign keys for list_options are defined in option_id and do not refer to list_id
    // which makes it hard for referential integrity, so we need to make sure on the referenced key that we actually grab
    // all the entries for a list and we don't end up getting option_ids that are part of OTHER lists because
    // they share the same key

    const LIST_OPTIONS_KEY_FILTER_DEFINITIONS = [
        'lists' => [
            'subtype' => ['localValueOverride' => 'issue_subtypes', 'foreignKeyColumn' => 'list_id']
            ,'occurrence' => ['localValueOverride' => 'occurrence', 'foreignKeyColumn' => 'list_id']
            ,'outcome' => ['localValueOverride' => 'outcome', 'foreignKeyColumn' => 'list_id']
            ,'reaction' => ['localValueOverride' => 'reaction', 'foreignKeyColumn' => 'list_id']
            ,'verification' => ['localValueOverride' => 'allergyintolerance-verification', 'foreignKeyColumn' => 'list_id']
            ,'severity_al' => ['localValueOverride' => 'severity_ccda', 'foreignKeyColumn' => 'list_id']
        ]
        ,'lists_medication' => [
            'usage_category' => ['localValueOverride' => 'medication-usage-category', 'foreignKeyColumn' => 'list_id']
            ,'request_intent' => ['localValueOverride' => 'medication-request-intent', 'foreignKeyColumn' => 'list_id']
        ]
        ,'pnotes' => [
            'message_status' => ['localValueOverride' => 'message_status', 'foreignKeyColumn' => 'list_id']
        ]
        ,'amendments' => [
            'amendment_by' => ['localValueOverride' => 'amendment_from', 'foreignKeyColumn' => 'list_id'],
            'amendment_status' => ['localValueOverride' => 'amendment_status', 'foreignKeyColumn' => 'list_id']
        ]
        ,'amendments_history' => [
            'amendment_by' => ['localValueOverride' => 'amendment_from', 'foreignKeyColumn' => 'list_id']
        ]
        ,'patient_reminders' => [
            'reason_inactivated' => ['localValueOverride' => 'rule_reminder_inactive_opt', 'foreignKeyColumn' => 'list_id']
            ,'due_status' => ['localValueOverride' => 'rule_reminder_due_opt', 'foreignKeyColumn' => 'list_id']
        ]
        , 'rule_action_item' => [
            'category' => ['localValueOverride' => 'message_status', 'foreignKeyColumn' => 'list_id']
            ,'item' => ['localValueOverride' => 'message_status', 'foreignKeyColumn' => 'list_id']
        ]
        , 'form_vital_details' => [
            'interpretation_option_id' => ['localValueOverride' => 'observation_interpretation', 'foreignKeyColumn' => 'list_id']
        ]
        ,'ar_session' => [
            'payment_type' => ['localValueOverride' => 'payment_type', 'foreignKeyColumn' => 'list_id'],
            'adjustment_code' => ['localValueOverride' => 'payment_adjustment_code', 'foreignKeyColumn' => 'list_id'],
            'payment_method' => ['localValueOverride' => 'payment_method', 'foreignKeyColumn' => 'list_id']
        ]
        ,'form_clinical_notes' => [
            'clinical_notes_type' => ['localValueOverride' => 'Clinical_Note_Type', 'foreignKeyColumn' => 'list_id'],
            'clinical_notes_category' => ['localValueOverride' => 'Clinical_Note_Category', 'foreignKeyColumn' => 'list_id']
        ]
        ,'patient_tracker_element' => [
            'room' => ['localValueOverride' => 'patient_flow_board_rooms', 'foreignKeyColumn' => 'list_id']
        ]
        ,'openemr_postcalendar_events' => [
            'pc_apptstatus' => ['localValueOverride' => 'apptstat', 'foreignKeyColumn' => 'list_id'],
            'pc_room' => ['localValueOverride' => 'patient_flow_board_rooms', 'foreignKeyColumn' => 'list_id']
        ]
        ,'therapy_groups_participant_attendance' => [
            'meeting_patient_status' => ['localValueOverride' => 'groupstat', 'foreignKeyColumn' => 'list_id'],
        ]
        ,'procedure_type' => [
            'procedure_type' => ['localValueOverride' => 'proc_type', 'foreignKeyColumn' => 'list_id']
        ]
        ,'procedure_order' => [
            'order_priority' => ['localValueOverride' => 'ord_priority', 'foreignKeyColumn' => 'list_id'],
            'order_status' => ['localValueOverride' => 'ord_status', 'foreignKeyColumn' => 'list_id'],
            'billing_type' => ['localValueOverride' => 'procedure_billing', 'foreignKeyColumn' => 'list_id'],
            'procedure_order_type' => ['localValueOverride' => 'order_type', 'foreignKeyColumn' => 'list_id']
        ]
        ,'procedure_report' => [
            'procedure_type' => ['localValueOverride' => 'proc_rep_status', 'foreignKeyColumn' => 'list_id']
        ]
        ,'immunizations' => [
            'immunization_id' => ['localValueOverride' => 'immunizations', 'foreignKeyColumn' => 'list_id'],
            'amount_administered_unit' => ['localValueOverride' => 'drug_units', 'foreignKeyColumn' => 'list_id'],
            'manufacturer' => ['localValueOverride' => 'Immunization_Manufacturer', 'foreignKeyColumn' => 'list_id'],
            'route' => ['localValueOverride' => 'drug_route', 'foreignKeyColumn' => 'list_id'],
            'administration_site' => ['localValueOverride' => 'immunization_administered_site', 'foreignKeyColumn' => 'list_id'],
            'information_source' => ['localValueOverride' => 'immunization_informationsource', 'foreignKeyColumn' => 'list_id'],
            'completion_status' => ['localValueOverride' => 'Immunization_Completion_Status', 'foreignKeyColumn' => 'list_id'],
            'refusal_reason' => ['localValueOverride' => 'immunization_refusal_reason', 'foreignKeyColumn' => 'list_id'],

        ]
    ];

    public function filterKey(ExportKeyDefinition $key)
    {
        // override our table settings so we can get better exports here.
        if (
            isset(self::LIST_OPTIONS_KEY_FILTER_DEFINITIONS[$key->localTable])
            && isset(self::LIST_OPTIONS_KEY_FILTER_DEFINITIONS[$key->localTable][$key->localColumn])
        ) {
            $keyDef = self::LIST_OPTIONS_KEY_FILTER_DEFINITIONS[$key->localTable][$key->localColumn];
            $key->localValueOverride = $keyDef['localValueOverride'];
            $key->foreignKeyColumn = $keyDef['foreignKeyColumn'];
        }
        return $key;
    }

    public function hasMultipleKeysForColumn(ExportKeyDefinition $key)
    {
        if ($key->localTable == 'lists' && $key->localColumn == 'list_option_id') {
            return true;
        }
        return false;
    }

    public function filterMultipleKeys(ExportKeyDefinition $key)
    {
        $keys = [];
        if ($key->localTable == 'lists') {
            if (!isset($this->issueTypes)) {
                $this->issueTypes = QueryUtils::fetchTableColumn("select type from issue_types", 'type');
            }

            if (!empty($this->issueTypes)) {
                foreach ($this->issueTypes as $type) {
                    $newKey = clone $key;
                    $newKey->foreignKeyColumn = 'list_id';
                    $newKey->localValueOverride = $type . '_issue_list';
                    $keys[] = $newKey;
                }
            }
        }
        return $keys;
    }
}
