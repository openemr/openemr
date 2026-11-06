<?php

/**
 * The EHI export walks the relations in its schema files, so a table in b10-tables.yml that's missing from them is
 * silently left out of the export. Patient-submitted requests and their history have to be reachable from the patient.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
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
    use SimpleXMLElement;
    use Symfony\Component\Yaml\Yaml;

    class EhiExporterPatientReviewTablesTest extends TestCase
    {
        private const EHI_DOCS = '/Documentation/EHI_Export/';
        private const REQUEST_UUID = 'f1e2d3c4-b5a6-4978-8695-a4b3c2d1e0f9';

        /**
         * A table added to b10-tables.yml alone is left out of the export without any error, so every listed
         * table has to be in the schema file the exporter walks.
         */
        #[Test]
        public function everyTableInTheEhiTableSetIsInTheExportersSchema(): void
        {
            $tableSet = Yaml::parseFile($this->docsPath('b10-tables.yml'));
            $this->assertIsArray($tableSet);
            $groups = $tableSet['groups'] ?? null;
            $this->assertIsArray($groups);

            $missing = [];
            foreach ($groups as $tables) {
                $this->assertIsArray($tables);
                foreach ($tables as $table) {
                    $this->assertIsString($table);
                    if ($this->schemaNodes("//table[@name='{$table}']") === []) {
                        $missing[] = $table;
                    }
                }
            }

            $this->assertSame([], $missing);
        }

        #[Test]
        public function requestsAreReachedFromThePatient(): void
        {
            $this->assertNotSame([], $this->schemaNodes(
                "//table[@name='patient_data']/column[@name='pid']/child[@table='patient_review_request'][@column='pid']"
            ));
            $this->assertNotSame([], $this->schemaNodes(
                "//table[@name='patient_review_request']/column[@name='pid']/parent[@table='patient_data'][@column='pid']"
            ));
        }

        #[Test]
        public function historyIsReachedFromItsRequest(): void
        {
            $this->assertNotSame([], $this->schemaNodes(
                "//table[@name='patient_review_request']/column[@name='id']/child[@table='patient_review_event'][@column='request_id']"
            ));
            $this->assertNotSame([], $this->schemaNodes(
                "//table[@name='patient_review_event']/column[@name='request_id']/parent[@table='patient_review_request'][@column='id']"
            ));
            $this->assertContains('patient_review_request', EhiExporter::PARENT_FK_TABLES_TRAVERSAL, 'child keys are only followed for listed tables');
        }

        #[Test]
        public function theMetaFileDefinesTheSameRelations(): void
        {
            $meta = simplexml_load_file($this->docsPath('schemaspy/schemas/openemr.meta.xml'));
            $this->assertInstanceOf(SimpleXMLElement::class, $meta);

            $this->assertNotSame([], $meta->xpath(
                "//table[@name='patient_review_request']/column[@name='pid']/foreignKey[@table='patient_data'][@column='pid']"
            ));
            $this->assertNotSame([], $meta->xpath(
                "//table[@name='patient_review_event']/column[@name='request_id']/foreignKey[@table='patient_review_request'][@column='id']"
            ));
        }

        #[Test]
        public function cardSecretsAreNotInTheSchema(): void
        {
            $this->assertSame([], $this->schemaNodes("//table[@name='patient_review_secret']"));
        }

        #[Test]
        public function aRequestsUuidIsWrittenAsAUuidString(): void
        {
            $bytes = Uuid::fromString(self::REQUEST_UUID)->getBytes();

            $this->assertSame(
                ['uuid' => self::REQUEST_UUID, 'status' => 'denied'],
                EhiExporter::csvRecord('patient_review_request', ['uuid' => $bytes, 'status' => 'denied'], false)
            );
        }

        /**
         * @return array<SimpleXMLElement>
         */
        private function schemaNodes(string $xpath): array
        {
            $schema = simplexml_load_file($this->docsPath('docs/openemr.openemr.xml'));
            $this->assertInstanceOf(SimpleXMLElement::class, $schema);
            $nodes = $schema->xpath($xpath);
            $this->assertIsArray($nodes);

            return $nodes;
        }

        private function docsPath(string $file): string
        {
            return dirname(__DIR__, 5) . self::EHI_DOCS . $file;
        }
    }
}
