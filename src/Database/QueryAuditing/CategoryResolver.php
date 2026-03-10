<?php

/**
 * Resolves audit categories from table and SQL content.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Database\QueryAuditing;

/**
 * Resolves the detailed audit category based on table and SQL content.
 *
 * This provides more granular classification than AuditEventType by
 * analyzing the specific table and sometimes the SQL content to determine
 * the type of clinical or administrative data involved.
 *
 * Extracted from EventAuditLogger::eventCategoryFinder().
 */
final class CategoryResolver
{
    /**
     * Resolve the audit category for a query.
     *
     * @param AuditEventType $eventType The high-level event type
     * @param string $primaryTable The main table being accessed
     * @param string $sql The full SQL statement (used for list type detection)
     */
    public function resolve(
        AuditEventType $eventType,
        string $primaryTable,
        string $sql,
    ): ?AuditCategory {
        return match ($primaryTable) {
            'lists', 'lists_touch' => $this->resolveListsCategory($sql),
            'immunizations' => AuditCategory::Immunization,
            'form_vitals' => AuditCategory::Vitals,
            'history_data' => AuditCategory::SocialAndFamilyHistory,
            'forms', 'form_encounter' => AuditCategory::EncounterForm,
            'insurance_data' => AuditCategory::PatientInsurance,
            'patient_data', 'employer_data' => AuditCategory::PatientDemographics,
            'payments', 'billing', 'claims' => AuditCategory::Billing,
            'pnotes' => AuditCategory::ClinicalMail,
            'prescriptions' => AuditCategory::Medication,
            'transactions' => $this->resolveTransactionsCategory($sql),
            'amendments', 'amendments_history' => AuditCategory::Amendments,
            'openemr_postcalendar_events' => AuditCategory::Scheduling,
            'procedure_order', 'procedure_order_code' => AuditCategory::LabOrder,
            'procedure_report', 'procedure_result' => AuditCategory::LabResult,
            default => $this->resolveDefault($eventType, $primaryTable),
        };
    }

    /**
     * Resolve category for the lists/lists_touch tables.
     *
     * The lists table stores multiple types of data distinguished by the
     * 'type' column value.
     */
    private function resolveListsCategory(string $sql): ?AuditCategory
    {
        if (str_contains($sql, "'medical_problem'")) {
            return AuditCategory::ProblemList;
        }
        if (str_contains($sql, "'medication'")) {
            return AuditCategory::Medication;
        }
        if (str_contains($sql, "'allergy'")) {
            return AuditCategory::Allergy;
        }
        return null;
    }

    /**
     * Resolve category for the transactions table.
     *
     * Transactions include referrals and other transaction types.
     */
    private function resolveTransactionsCategory(string $sql): ?AuditCategory
    {
        if (str_contains($sql, "'LBTref'")) {
            return AuditCategory::Referral;
        }
        return null;
    }

    /**
     * Resolve category for tables not explicitly mapped.
     */
    private function resolveDefault(AuditEventType $eventType, string $table): ?AuditCategory
    {
        // Dynamic form tables (form_*) are encounter forms
        if (str_starts_with($table, 'form_')) {
            return AuditCategory::EncounterForm;
        }
        if ($eventType === AuditEventType::SecurityAdministration) {
            return AuditCategory::Security;
        }
        return null;
    }
}
