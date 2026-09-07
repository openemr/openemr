<?php

/**
 * SchematronValidator isolated test - end-to-end validation semantics on a
 * small hand-crafted schematron + XML pair.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Cda\Schematron;

use OpenEMR\Services\Cda\Schematron\ArrayVocabularyLookup;
use OpenEMR\Services\Cda\Schematron\SchematronValidator;
use PHPUnit\Framework\TestCase;

final class SchematronValidatorTest extends TestCase
{
    private const NAMESPACES = [
        '2.16.840.1.113883.11.20.9.19' => ['completed', 'active', 'aborted'],
    ];

    private const SCHEMATRON = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<sch:schema xmlns:sch="http://purl.oclc.org/dsdl/schematron">
    <sch:ns prefix="cda" uri="urn:hl7-org:v3"/>
    <sch:ns prefix="voc" uri="http://www.lantanagroup.com/voc"/>
    <sch:phase id="errors">
        <sch:active pattern="p-1"/>
    </sch:phase>
    <sch:pattern id="p-1">
        <sch:rule id="r-observation" context="cda:observation">
            <sch:assert id="a-code" test="cda:code">SHALL contain code.</sch:assert>
            <sch:assert id="a-status" test="cda:statusCode[@code and @code=document('voc.xml')/voc:systems/voc:system[@valueSetOid='2.16.840.1.113883.11.20.9.19']/voc:code/@value]">SHALL have valid statusCode.</sch:assert>
        </sch:rule>
    </sch:pattern>
</sch:schema>
XML;

    public function testAssertionPassesWhenBothAssertionsSatisfied(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<ClinicalDocument xmlns="urn:hl7-org:v3">
    <observation><code code="X"/><statusCode code="completed"/></observation>
</ClinicalDocument>
XML;
        $result = $this->validate($xml);
        self::assertCount(0, $result->errors);
        self::assertCount(0, $result->ignored);
    }

    public function testAssertionErrorWhenValueSetMissing(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<ClinicalDocument xmlns="urn:hl7-org:v3">
    <observation><code code="X"/><statusCode code="invalid-status"/></observation>
</ClinicalDocument>
XML;
        $result = $this->validate($xml);
        self::assertCount(1, $result->errors);
        self::assertSame('a-status', $result->errors[0]['assertionId']);
    }

    public function testAssertionErrorWhenMissingRequiredChild(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<ClinicalDocument xmlns="urn:hl7-org:v3">
    <observation><statusCode code="completed"/></observation>
</ClinicalDocument>
XML;
        $result = $this->validate($xml);
        self::assertCount(1, $result->errors);
        self::assertSame('a-code', $result->errors[0]['assertionId']);
    }

    public function testMultipleContextNodesEachEvaluated(): void
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<ClinicalDocument xmlns="urn:hl7-org:v3">
    <observation><statusCode code="completed"/></observation>
    <observation><code code="Y"/><statusCode code="completed"/></observation>
    <observation><code code="Z"/></observation>
</ClinicalDocument>
XML;
        $result = $this->validate($xml);
        // obs1 missing code; obs3 missing statusCode. obs2 clean.
        self::assertCount(2, $result->errors);
    }

    /**
     * @dataProvider goldenFixtureProvider
     */
    public function testGoldenParityAgainstNodeService(string $fixture, string $schemaType, string $schPath, string $vocabPath): void
    {
        $fixtureDir = __DIR__ . '/fixtures';
        $xml = self::readFile("$fixtureDir/$fixture.xml");
        $sch = self::readFile(__DIR__ . "/../../../../../src/Services/Cda/Schematron/schemas/$schPath");
        /** @var array<string, list<string>> $vocab */
        $vocab = require __DIR__ . "/../../../../../src/Services/Cda/Schematron/schemas/$vocabPath";

        $validator = new SchematronValidator(new ArrayVocabularyLookup($vocab));
        $result = $validator->validate($xml, $sch);
        $out = $result->toArray();

        // Change-detection golden: exact PHP output snapshot.
        $expectedPhp = self::readJson("$fixtureDir/$fixture.php-golden.json");
        self::assertSame($expectedPhp, $out, "$schemaType: PHP output diverged from php-golden");

        // Regression parity: every error the Node service produced must also appear in PHP output.
        $nodeGolden = self::readJson("$fixtureDir/$fixture.node-golden.json");
        /** @var list<array<string, mixed>> $nodeErrors */
        $nodeErrors = $nodeGolden['errors'] ?? [];
        /** @var list<array<string, mixed>> $phpErrors */
        $phpErrors = $out['errors'];
        $missing = array_diff(self::errorKeys($nodeErrors), self::errorKeys($phpErrors));
        self::assertSame([], array_values($missing), "$schemaType: PHP validator missed errors that Node caught");
    }

    /**
     * @return array<string, array{string, string, string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function goldenFixtureProvider(): array
    {
        return [
            'ccda' => ['ccda-example-response1', 'ccda', 'ccda/Consolidation.sch', 'ccda/vocab.php'],
            'qrda1' => ['qrda1-catI-doc-28', 'qrda1', 'qrda1/2022_CMS_QRDA_I.sch', 'qrda1/vocab.php'],
            'qrda3' => ['qrda3-minimal', 'qrda3', 'qrda3/2022_CMS_QRDA_Category_III.sch', 'qrda3/vocab.php'],
        ];
    }

    /**
     * @param list<array<string, mixed>> $errors
     * @return list<string>
     */
    private static function errorKeys(array $errors): array
    {
        $out = [];
        foreach ($errors as $e) {
            $out[] = self::stringField($e, 'assertionId')
                . '|' . self::stringField($e, 'patternId')
                . '|' . self::stringField($e, 'ruleId');
        }
        return $out;
    }

    /**
     * @param array<string, mixed> $entry
     */
    private static function stringField(array $entry, string $key): string
    {
        $v = $entry[$key] ?? null;
        return is_string($v) ? $v : '';
    }

    private static function readFile(string $path): string
    {
        $data = file_get_contents($path);
        if ($data === false) {
            self::fail("Failed to read $path");
        }
        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private static function readJson(string $path): array
    {
        /** @var array<string, mixed> $decoded */
        $decoded = json_decode(self::readFile($path), true, flags: JSON_THROW_ON_ERROR);
        return $decoded;
    }

    private function validate(string $xml): \OpenEMR\Services\Cda\Schematron\ValidationResult
    {
        $validator = new SchematronValidator(new ArrayVocabularyLookup(self::NAMESPACES));
        return $validator->validate($xml, self::SCHEMATRON);
    }
}
