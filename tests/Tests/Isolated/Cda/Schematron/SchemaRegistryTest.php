<?php

/**
 * SchemaRegistry isolated test.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Cda\Schematron;

use InvalidArgumentException;
use OpenEMR\Services\Cda\Schematron\SchemaRegistry;
use PHPUnit\Framework\TestCase;

final class SchemaRegistryTest extends TestCase
{
    public function testAllShippedSchemasHaveSchematronAndVocab(): void
    {
        $registry = new SchemaRegistry();
        foreach ([SchemaRegistry::TYPE_CCDA, SchemaRegistry::TYPE_QRDA1, SchemaRegistry::TYPE_QRDA3] as $type) {
            self::assertFileExists($registry->schematronPath($type), "$type: schematron file missing");
            self::assertFileExists($registry->vocabPath($type), "$type: vocab file missing");
        }
    }

    public function testLoadValidatorProducesUsableValidator(): void
    {
        $registry = new SchemaRegistry();
        $validator = $registry->loadValidator(SchemaRegistry::TYPE_CCDA);
        $result = $validator->validate(
            '<?xml version="1.0"?><ClinicalDocument xmlns="urn:hl7-org:v3"/>',
            (string) file_get_contents($registry->schematronPath(SchemaRegistry::TYPE_CCDA)),
        );
        // Doesn't matter what it found; only that the validator produced a result object.
        self::assertGreaterThanOrEqual(0, $result->toArray()['errorCount']);
    }

    public function testUnknownTypeThrows(): void
    {
        $registry = new SchemaRegistry();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown schematron type');
        $registry->schematronPath('mystery');
    }

    public function testGeneratedVocabFilesReturnArrays(): void
    {
        foreach (['ccda', 'qrda1', 'qrda3'] as $type) {
            $path = __DIR__ . "/../../../../../src/Services/Cda/Schematron/schemas/$type/vocab.php";
            $data = require $path;
            self::assertIsArray($data, "$type/vocab.php did not return an array");
            self::assertNotEmpty($data, "$type/vocab.php is empty");
            foreach ($data as $oid => $values) {
                self::assertMatchesRegularExpression('/^[0-9.]+$/', $oid, "$type: OID key looks wrong: $oid");
                self::assertIsArray($values, "$type: values for $oid must be an array");
            }
        }
    }
}
