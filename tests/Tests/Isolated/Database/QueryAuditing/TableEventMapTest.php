<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Database\QueryAuditing;

use OpenEMR\Database\QueryAuditing\AuditEventType;
use OpenEMR\Database\QueryAuditing\TableEventMap;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TableEventMap::class)]
final class TableEventMapTest extends TestCase
{
    private TableEventMap $map;

    protected function setUp(): void
    {
        $this->map = new TableEventMap();
    }

    #[DataProvider('provideTableMappings')]
    public function testGetEventType(array $tables, AuditEventType $expected): void
    {
        self::assertSame($expected, $this->map->getEventType($tables));
    }

    /**
     * @return iterable<string, array{0: string[], 1: AuditEventType}>
     */
    public static function provideTableMappings(): iterable
    {
        yield 'patient_data' => [['patient_data'], AuditEventType::PatientRecord];
        yield 'billing' => [['billing'], AuditEventType::PatientRecord];
        yield 'forms' => [['forms'], AuditEventType::PatientRecord];
        yield 'immunizations' => [['immunizations'], AuditEventType::PatientRecord];
        yield 'prescriptions' => [['prescriptions'], AuditEventType::Order];
        yield 'users' => [['users'], AuditEventType::SecurityAdministration];
        yield 'facility' => [['facility'], AuditEventType::SecurityAdministration];
        yield 'gacl_aro' => [['gacl_aro'], AuditEventType::SecurityAdministration];
        yield 'openemr_postcalendar_events' => [['openemr_postcalendar_events'], AuditEventType::Scheduling];
        yield 'procedure_order' => [['procedure_order'], AuditEventType::LabOrder];
        yield 'procedure_result' => [['procedure_result'], AuditEventType::LabResults];
        yield 'unknown_table' => [['some_unknown_table'], AuditEventType::Other];
        yield 'empty_tables' => [[], AuditEventType::Other];

        // form_* pattern matching
        yield 'form_vitals' => [['form_vitals'], AuditEventType::PatientRecord];
        yield 'form_custom_xyz' => [['form_custom_xyz'], AuditEventType::PatientRecord];

        // Multiple tables - returns first match
        yield 'multiple_with_known' => [['unknown', 'patient_data'], AuditEventType::PatientRecord];
        yield 'multiple_unknown' => [['unknown1', 'unknown2'], AuditEventType::Other];

        // Backtick-quoted table names (as returned by SQL parser)
        yield 'backticked_patient_data' => [['`patient_data`'], AuditEventType::PatientRecord];
        yield 'backticked_form_custom' => [['`form_custom`'], AuditEventType::PatientRecord];
    }

    public function testGetPrimaryTableReturnsFirstMapped(): void
    {
        self::assertSame('patient_data', $this->map->getPrimaryTable(['unknown', 'patient_data', 'users']));
    }

    public function testGetPrimaryTableReturnsDynamicForm(): void
    {
        self::assertSame('form_custom', $this->map->getPrimaryTable(['unknown', 'form_custom']));
    }

    public function testGetPrimaryTableReturnsFirstWhenNoneMapped(): void
    {
        self::assertSame('unknown1', $this->map->getPrimaryTable(['unknown1', 'unknown2']));
    }

    public function testGetPrimaryTableReturnsNullForEmptyArray(): void
    {
        self::assertNull($this->map->getPrimaryTable([]));
    }
}
