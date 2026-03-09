<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Database\QueryAuditing;

use OpenEMR\Database\QueryAuditing\AuditCategory;
use OpenEMR\Database\QueryAuditing\AuditEventType;
use OpenEMR\Database\QueryAuditing\CategoryResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(CategoryResolver::class)]
final class CategoryResolverTest extends TestCase
{
    private CategoryResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new CategoryResolver();
    }

    #[DataProvider('provideTableCategories')]
    public function testResolve(
        AuditEventType $eventType,
        string $table,
        string $sql,
        ?AuditCategory $expected,
    ): void {
        self::assertSame($expected, $this->resolver->resolve($eventType, $table, $sql));
    }

    /**
     * @return iterable<string, array{0: AuditEventType, 1: string, 2: string, 3: ?AuditCategory}>
     */
    public static function provideTableCategories(): iterable
    {
        // Direct table mappings
        yield 'immunizations' => [
            AuditEventType::PatientRecord,
            'immunizations',
            'SELECT * FROM immunizations',
            AuditCategory::Immunization,
        ];

        yield 'form_vitals' => [
            AuditEventType::PatientRecord,
            'form_vitals',
            'SELECT * FROM form_vitals',
            AuditCategory::Vitals,
        ];

        yield 'history_data' => [
            AuditEventType::PatientRecord,
            'history_data',
            'SELECT * FROM history_data',
            AuditCategory::SocialAndFamilyHistory,
        ];

        yield 'forms' => [
            AuditEventType::PatientRecord,
            'forms',
            'SELECT * FROM forms',
            AuditCategory::EncounterForm,
        ];

        yield 'form_encounter' => [
            AuditEventType::PatientRecord,
            'form_encounter',
            'SELECT * FROM form_encounter',
            AuditCategory::EncounterForm,
        ];

        yield 'insurance_data' => [
            AuditEventType::PatientRecord,
            'insurance_data',
            'SELECT * FROM insurance_data',
            AuditCategory::PatientInsurance,
        ];

        yield 'patient_data' => [
            AuditEventType::PatientRecord,
            'patient_data',
            'SELECT * FROM patient_data',
            AuditCategory::PatientDemographics,
        ];

        yield 'employer_data' => [
            AuditEventType::PatientRecord,
            'employer_data',
            'SELECT * FROM employer_data',
            AuditCategory::PatientDemographics,
        ];

        yield 'payments' => [
            AuditEventType::PatientRecord,
            'payments',
            'SELECT * FROM payments',
            AuditCategory::Billing,
        ];

        yield 'billing' => [
            AuditEventType::PatientRecord,
            'billing',
            'SELECT * FROM billing',
            AuditCategory::Billing,
        ];

        yield 'claims' => [
            AuditEventType::PatientRecord,
            'claims',
            'SELECT * FROM claims',
            AuditCategory::Billing,
        ];

        yield 'pnotes' => [
            AuditEventType::PatientRecord,
            'pnotes',
            'SELECT * FROM pnotes',
            AuditCategory::ClinicalMail,
        ];

        yield 'prescriptions' => [
            AuditEventType::Order,
            'prescriptions',
            'SELECT * FROM prescriptions',
            AuditCategory::Medication,
        ];

        yield 'amendments' => [
            AuditEventType::PatientRecord,
            'amendments',
            'SELECT * FROM amendments',
            AuditCategory::Amendments,
        ];

        yield 'amendments_history' => [
            AuditEventType::PatientRecord,
            'amendments_history',
            'SELECT * FROM amendments_history',
            AuditCategory::Amendments,
        ];

        yield 'openemr_postcalendar_events' => [
            AuditEventType::Scheduling,
            'openemr_postcalendar_events',
            'SELECT * FROM openemr_postcalendar_events',
            AuditCategory::Scheduling,
        ];

        yield 'procedure_order' => [
            AuditEventType::LabOrder,
            'procedure_order',
            'SELECT * FROM procedure_order',
            AuditCategory::LabOrder,
        ];

        yield 'procedure_order_code' => [
            AuditEventType::LabOrder,
            'procedure_order_code',
            'SELECT * FROM procedure_order_code',
            AuditCategory::LabOrder,
        ];

        yield 'procedure_report' => [
            AuditEventType::LabResults,
            'procedure_report',
            'SELECT * FROM procedure_report',
            AuditCategory::LabResult,
        ];

        yield 'procedure_result' => [
            AuditEventType::LabResults,
            'procedure_result',
            'SELECT * FROM procedure_result',
            AuditCategory::LabResult,
        ];

        // Lists table with type detection
        yield 'lists_medical_problem' => [
            AuditEventType::PatientRecord,
            'lists',
            "INSERT INTO lists (type) VALUES ('medical_problem')",
            AuditCategory::ProblemList,
        ];

        yield 'lists_medication' => [
            AuditEventType::PatientRecord,
            'lists',
            "INSERT INTO lists (type) VALUES ('medication')",
            AuditCategory::Medication,
        ];

        yield 'lists_allergy' => [
            AuditEventType::PatientRecord,
            'lists',
            "INSERT INTO lists (type) VALUES ('allergy')",
            AuditCategory::Allergy,
        ];

        yield 'lists_unknown_type' => [
            AuditEventType::PatientRecord,
            'lists',
            "INSERT INTO lists (type) VALUES ('something_else')",
            null,
        ];

        yield 'lists_touch_medical_problem' => [
            AuditEventType::PatientRecord,
            'lists_touch',
            "UPDATE lists_touch SET type = 'medical_problem'",
            AuditCategory::ProblemList,
        ];

        // Transactions table with type detection
        yield 'transactions_referral' => [
            AuditEventType::PatientRecord,
            'transactions',
            "INSERT INTO transactions (title) VALUES ('LBTref')",
            AuditCategory::Referral,
        ];

        yield 'transactions_other' => [
            AuditEventType::PatientRecord,
            'transactions',
            "INSERT INTO transactions (title) VALUES ('something')",
            null,
        ];

        // Dynamic form tables
        yield 'form_custom_xyz' => [
            AuditEventType::PatientRecord,
            'form_custom_xyz',
            'SELECT * FROM form_custom_xyz',
            AuditCategory::EncounterForm,
        ];

        // Security administration
        yield 'users_security' => [
            AuditEventType::SecurityAdministration,
            'users',
            'SELECT * FROM users',
            AuditCategory::Security,
        ];

        yield 'gacl_aro' => [
            AuditEventType::SecurityAdministration,
            'gacl_aro',
            'SELECT * FROM gacl_aro',
            AuditCategory::Security,
        ];

        // Unknown table returns null
        yield 'unknown_table' => [
            AuditEventType::Other,
            'some_unknown_table',
            'SELECT * FROM some_unknown_table',
            null,
        ];
    }
}
