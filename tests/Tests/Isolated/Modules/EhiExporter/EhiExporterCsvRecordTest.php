<?php

/**
 * The EHI export writes a Task's binary references (task.focus_uuid and the others, task_input.value_uuid,
 * task_output.value_uuid) as uuid strings, as it writes `uuid`, and leaves other tables' records as they were.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace {
    $ehiExporterModuleSrc = dirname(__DIR__, 5) . '/interface/modules/custom_modules/oe-module-ehi-exporter/src/';
    if (!is_dir($ehiExporterModuleSrc)) {
        throw new RuntimeException(
            'EHI exporter module source not found at ' . $ehiExporterModuleSrc
            . ' - this test moved and the relative path needs updating.'
        );
    }
    spl_autoload_register(static function (string $class) use ($ehiExporterModuleSrc): void {
        $prefix = 'OpenEMR\\Modules\\EhiExporter\\';
        if (str_starts_with($class, $prefix)) {
            $file = $ehiExporterModuleSrc . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (is_file($file)) {
                require_once $file;
            }
        }
    });
}

namespace OpenEMR\Tests\Isolated\Modules\EhiExporter {

    use OpenEMR\Modules\EhiExporter\Services\EhiExporter;
    use PHPUnit\Framework\Attributes\Test;
    use PHPUnit\Framework\TestCase;
    use Ramsey\Uuid\Uuid;

    class EhiExporterCsvRecordTest extends TestCase
    {
        private const FOCUS = '7d5a9a52-0f2b-4b8e-9c61-1f2e3d4c5b6a';
        private const OWNER = '1c2b3a4d-5e6f-4a7b-8c9d-0e1f2a3b4c5d';
        private const VALUE = 'a0b1c2d3-e4f5-4a6b-9c7d-8e9fa0b1c2d3';
        private const TASK_UUID = 'f1e2d3c4-b5a6-4978-8695-a4b3c2d1e0f9';

        #[Test]
        public function aTasksReferencesAreWrittenAsUuidStrings(): void
        {
            $row = EhiExporter::csvRecord('task', [
                'id' => '4',
                'uuid' => Uuid::fromString(self::TASK_UUID)->getBytes(),
                'focus_uuid' => Uuid::fromString(self::FOCUS)->getBytes(),
                'requester_uuid' => null,
                'owner_uuid' => Uuid::fromString(self::OWNER)->getBytes(),
                'part_of_uuid' => null,
                'status' => 'requested',
            ], true);

            $this->assertSame(self::TASK_UUID, $row['uuid']);
            $this->assertSame(self::FOCUS, $row['focus_uuid']);
            $this->assertSame(self::OWNER, $row['owner_uuid']);
            $this->assertNull($row['requester_uuid'], 'no reference stays empty');
            $this->assertNull($row['part_of_uuid']);
            $this->assertSame(['id' => '4', 'status' => 'requested'], array_intersect_key($row, ['id' => 1, 'status' => 1]));
        }

        #[Test]
        public function anInputOrOutputReferenceIsWrittenAsAUuidString(): void
        {
            foreach (['task_input', 'task_output'] as $table) {
                $row = EhiExporter::csvRecord($table, ['id' => '9', 'task_id' => '4', 'value_uuid' => Uuid::fromString(self::VALUE)->getBytes(), 'value_string' => null], false);
                $this->assertSame(self::VALUE, $row['value_uuid'], $table);
                $this->assertSame('4', $row['task_id'], $table);
            }
        }

        #[Test]
        public function otherTablesAreWrittenAsBefore(): void
        {
            $bytes = Uuid::fromString(self::FOCUS)->getBytes();
            $this->assertSame(['uuid' => self::FOCUS, 'drive_uuid' => $bytes], EhiExporter::csvRecord('documents', ['uuid' => $bytes, 'drive_uuid' => $bytes], true));
            $this->assertSame(['uuid' => $bytes], EhiExporter::csvRecord('some_table', ['uuid' => $bytes], false), 'uuid only for a registered table');
            $this->assertSame(['uuid' => ''], EhiExporter::csvRecord('some_table', ['uuid' => ''], true));
        }
    }
}
