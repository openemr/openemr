<?php

namespace OpenEMR\Modules\EhiExporter;

use OpenEMR\Common\Database\QueryUtils;

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
            'amendment_by' => ['localValueOverride' => 'amendment_from', 'foreignKeyColumn' => 'list_id']
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
    ];

    public function filterKey(ExportKeyDefinition $key)
    {
        // override our table settings so we can get better exports here.
        if (isset(self::LIST_OPTIONS_KEY_FILTER_DEFINITIONS[$key->localTable])
            && isset(self::LIST_OPTIONS_KEY_FILTER_DEFINITIONS[$key->localTable][$key->localColumn])) {
            $keyDef = self::LIST_OPTIONS_KEY_FILTER_DEFINITIONS[$key->localTable][$key->localColumn];
            $key->localValueOverride = $keyDef['localValueOverride'];
            $key->foreignKeyColumn =$keyDef['foreignKeyColumn'];
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