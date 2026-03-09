<?php

/**
 * Maps database tables to audit event types.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <eric.stern@gmail.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Database\QueryAuditing;

/**
 * Maps database table names to their corresponding audit event types.
 *
 * This replaces the LOG_TABLES constant from EventAuditLogger with a
 * type-safe implementation using enums.
 */
final class TableEventMap
{
    /** @var array<string, AuditEventType> */
    private const TABLE_MAP = [
        // Patient record tables
        'billing' => AuditEventType::PatientRecord,
        'claims' => AuditEventType::PatientRecord,
        'employer_data' => AuditEventType::PatientRecord,
        'forms' => AuditEventType::PatientRecord,
        'form_encounter' => AuditEventType::PatientRecord,
        'form_dictation' => AuditEventType::PatientRecord,
        'form_misc_billing_options' => AuditEventType::PatientRecord,
        'form_reviewofs' => AuditEventType::PatientRecord,
        'form_ros' => AuditEventType::PatientRecord,
        'form_soap' => AuditEventType::PatientRecord,
        'form_vitals' => AuditEventType::PatientRecord,
        'history_data' => AuditEventType::PatientRecord,
        'immunizations' => AuditEventType::PatientRecord,
        'insurance_data' => AuditEventType::PatientRecord,
        'issue_encounter' => AuditEventType::PatientRecord,
        'lists' => AuditEventType::PatientRecord,
        'patient_data' => AuditEventType::PatientRecord,
        'payments' => AuditEventType::PatientRecord,
        'pnotes' => AuditEventType::PatientRecord,
        'onotes' => AuditEventType::PatientRecord,
        'transactions' => AuditEventType::PatientRecord,
        'amendments' => AuditEventType::PatientRecord,
        'amendments_history' => AuditEventType::PatientRecord,

        // Order tables
        'prescriptions' => AuditEventType::Order,

        // Security/administration tables
        'facility' => AuditEventType::SecurityAdministration,
        'pharmacies' => AuditEventType::SecurityAdministration,
        'addresses' => AuditEventType::SecurityAdministration,
        'phone_numbers' => AuditEventType::SecurityAdministration,
        'x12_partners' => AuditEventType::SecurityAdministration,
        'insurance_companies' => AuditEventType::SecurityAdministration,
        'codes' => AuditEventType::SecurityAdministration,
        'registry' => AuditEventType::SecurityAdministration,
        'users' => AuditEventType::SecurityAdministration,
        'groups' => AuditEventType::SecurityAdministration,
        'openemr_postcalendar_categories' => AuditEventType::SecurityAdministration,
        'openemr_postcalendar_limits' => AuditEventType::SecurityAdministration,
        'openemr_postcalendar_topics' => AuditEventType::SecurityAdministration,
        'gacl_acl' => AuditEventType::SecurityAdministration,
        'gacl_acl_sections' => AuditEventType::SecurityAdministration,
        'gacl_acl_seq' => AuditEventType::SecurityAdministration,
        'gacl_aco' => AuditEventType::SecurityAdministration,
        'gacl_aco_map' => AuditEventType::SecurityAdministration,
        'gacl_aco_sections' => AuditEventType::SecurityAdministration,
        'gacl_aco_sections_seq' => AuditEventType::SecurityAdministration,
        'gacl_aco_seq' => AuditEventType::SecurityAdministration,
        'gacl_aro' => AuditEventType::SecurityAdministration,
        'gacl_aro_groups' => AuditEventType::SecurityAdministration,
        'gacl_aro_groups_id_seq' => AuditEventType::SecurityAdministration,
        'gacl_aro_groups_map' => AuditEventType::SecurityAdministration,
        'gacl_aro_map' => AuditEventType::SecurityAdministration,
        'gacl_aro_sections' => AuditEventType::SecurityAdministration,
        'gacl_aro_sections_seq' => AuditEventType::SecurityAdministration,
        'gacl_aro_seq' => AuditEventType::SecurityAdministration,
        'gacl_axo' => AuditEventType::SecurityAdministration,
        'gacl_axo_groups' => AuditEventType::SecurityAdministration,
        'gacl_axo_groups_map' => AuditEventType::SecurityAdministration,
        'gacl_axo_map' => AuditEventType::SecurityAdministration,
        'gacl_axo_sections' => AuditEventType::SecurityAdministration,
        'gacl_groups_aro_map' => AuditEventType::SecurityAdministration,
        'gacl_groups_axo_map' => AuditEventType::SecurityAdministration,
        'gacl_phpgacl' => AuditEventType::SecurityAdministration,

        // Scheduling tables
        'openemr_postcalendar_events' => AuditEventType::Scheduling,

        // Lab tables
        'procedure_order' => AuditEventType::LabOrder,
        'procedure_order_code' => AuditEventType::LabOrder,
        'procedure_report' => AuditEventType::LabResults,
        'procedure_result' => AuditEventType::LabResults,
    ];

    /**
     * Get the audit event type for a list of tables.
     *
     * Returns the event type of the first matched table, or Other if none match.
     *
     * @param string[] $tables List of table names from the query
     */
    public function getEventType(array $tables): AuditEventType
    {
        foreach ($tables as $table) {
            // Check exact match first
            if (isset(self::TABLE_MAP[$table])) {
                return self::TABLE_MAP[$table];
            }
            // Check form_* pattern (dynamic forms not in the map)
            if (str_starts_with($table, 'form_')) {
                return AuditEventType::PatientRecord;
            }
        }
        return AuditEventType::Other;
    }

    /**
     * Get the primary table from a list (first one that maps to an event type).
     *
     * @param string[] $tables List of table names from the query
     */
    public function getPrimaryTable(array $tables): ?string
    {
        foreach ($tables as $table) {
            if (isset(self::TABLE_MAP[$table]) || str_starts_with($table, 'form_')) {
                return $table;
            }
        }
        return $tables[0] ?? null;
    }
}
