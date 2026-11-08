<?php

/**
 * SchematronValidator isolated test - end-to-end validation semantics on a
 * small hand-crafted schematron + XML pair.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Cda\Schematron;

use OpenEMR\Services\Cda\Schematron\ArrayVocabularyLookup;
use OpenEMR\Services\Cda\Schematron\SchematronValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

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

    #[DataProvider('goldenFixtureProvider')]
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

        // Regression parity: every error the Node service produced must also appear in PHP
        // output, and at least as many times. array_diff() compares distinct values, so
        // it cannot see Node reporting the same assertion on four nodes where PHP
        // reports it on one; compare occurrence counts instead.
        $nodeGolden = self::readJson("$fixtureDir/$fixture.node-golden.json");
        /** @var list<array<string, mixed>> $nodeErrors */
        $nodeErrors = $nodeGolden['errors'] ?? [];
        /** @var list<array<string, mixed>> $phpErrors */
        $phpErrors = $out['errors'];
        $nodeCounts = array_count_values(self::errorKeys($nodeErrors));
        $phpCounts = array_count_values(self::errorKeys($phpErrors));
        $shortfall = [];
        foreach ($nodeCounts as $key => $nodeCount) {
            $phpCount = $phpCounts[$key] ?? 0;
            if ($phpCount < $nodeCount) {
                $shortfall[$key] = "node=$nodeCount php=$phpCount";
            }
        }
        self::assertSame([], $shortfall, "$schemaType: PHP validator missed errors that Node caught");
    }

    /**
     * Regression for the shipped C-CDA hasCompatibleR1.1TemplateId rule, reduced to
     * its essentials. $root is defined as @root on the rule context and then used
     * inside a predicate. Inlining the expression rather than its value turns the
     * comparison into `@root=@root`, which is true for every candidate sibling, so a
     * document whose only extension-less templateId carries a different root would
     * pass and a real CDA error would be lost.
     */
    public function testRuleScopedVariableIsEvaluatedAgainstTheRuleContext(): void
    {
        $sch = <<<'XML'
            <?xml version="1.0"?>
            <sch:schema xmlns:sch="http://purl.oclc.org/dsdl/schematron">
              <sch:ns prefix="cda" uri="urn:hl7-org:v3"/>
              <sch:phase id="errors"><sch:active pattern="p-compat"/></sch:phase>
              <sch:pattern id="p-compat">
                <sch:rule context="//cda:templateId[@extension]">
                  <sch:let name="root" value="@root"/>
                  <sch:assert id="a-compat" test="../cda:templateId[(@root=$root) and not(@extension)]">SHALL carry a matching R1.1 templateId.</sch:assert>
                </sch:rule>
              </sch:pattern>
            </sch:schema>
            XML;

        $mismatched = <<<'XML'
            <?xml version="1.0"?>
            <ClinicalDocument xmlns="urn:hl7-org:v3">
              <templateId root="2.16.840.1.113883.10.20.22.1.1" extension="2015-08-01"/>
              <templateId root="9.9.9.9.9.9"/>
            </ClinicalDocument>
            XML;
        $result = (new SchematronValidator(new ArrayVocabularyLookup([])))->validate($mismatched, $sch);
        self::assertSame([], $result->ignored);
        self::assertCount(1, $result->errors, 'a sibling with a different root must not satisfy the rule');
        self::assertSame('a-compat', $result->errors[0]['assertionId']);

        $matching = <<<'XML'
            <?xml version="1.0"?>
            <ClinicalDocument xmlns="urn:hl7-org:v3">
              <templateId root="2.16.840.1.113883.10.20.22.1.1" extension="2015-08-01"/>
              <templateId root="2.16.840.1.113883.10.20.22.1.1"/>
            </ClinicalDocument>
            XML;
        $result = (new SchematronValidator(new ArrayVocabularyLookup([])))->validate($matching, $sch);
        self::assertSame([], $result->errors, 'a sibling with the same root must satisfy the rule');
    }

    /**
     * The XML snippet attached to a finding is capped at a byte budget. Cutting on a
     * byte boundary can split a multibyte character, and the whole finding list is
     * json_encode()d into documents.document_data by CdaValidateDocuments -
     * json_encode() returns false on malformed UTF-8, which stores an empty column
     * and renders as "No Errors". A validation failure disguised as a pass.
     *
     * The padding sweep walks the multibyte character across the cut so one of the
     * cases lands on the boundary regardless of how long the serialized prefix is.
     */
    /**
     * Warnings are opt-in. Turning them on or off must never change the error count:
     * the filter works per finding, so a SHALL assertion in a warnings-phase pattern
     * still reports as an error with warnings disabled.
     */
    public function testWarningsAreOffByDefaultAndOptIn(): void
    {
        $sch = <<<'XML'
            <?xml version="1.0"?>
            <sch:schema xmlns:sch="http://purl.oclc.org/dsdl/schematron">
              <sch:ns prefix="cda" uri="urn:hl7-org:v3"/>
              <sch:phase id="errors"><sch:active pattern="p-strict"/></sch:phase>
              <sch:phase id="warnings"><sch:active pattern="p-lenient"/></sch:phase>
              <sch:pattern id="p-strict">
                <sch:rule context="//cda:patient">
                  <sch:assert id="a-shall" test="@absent">SHALL carry the attribute.</sch:assert>
                </sch:rule>
              </sch:pattern>
              <sch:pattern id="p-lenient">
                <sch:rule context="//cda:patient">
                  <sch:assert id="a-should" test="@alsoAbsent">has the optional attribute.</sch:assert>
                </sch:rule>
              </sch:pattern>
            </sch:schema>
            XML;
        $xml = '<?xml version="1.0"?><ClinicalDocument xmlns="urn:hl7-org:v3"><patient/></ClinicalDocument>';

        $default = (new SchematronValidator(new ArrayVocabularyLookup([])))->validate($xml, $sch)->toArray();
        self::assertSame(1, $default['errorCount']);
        self::assertSame(0, $default['warningCount'], 'warnings must be off unless explicitly enabled');

        $on = (new SchematronValidator(new ArrayVocabularyLookup([]), includeWarnings: true))->validate($xml, $sch)->toArray();
        self::assertSame(1, $on['errorCount'], 'enabling warnings must not change the error count');
        self::assertSame(1, $on['warningCount']);
        self::assertSame('a-should', $on['warnings'][0]['assertionId']);
    }

    public function testTruncatedSnippetStaysValidUtf8(): void
    {
        $sch = <<<'XML'
            <?xml version="1.0"?>
            <sch:schema xmlns:sch="http://purl.oclc.org/dsdl/schematron">
              <sch:ns prefix="cda" uri="urn:hl7-org:v3"/>
              <sch:phase id="errors"><sch:active pattern="p-snippet"/></sch:phase>
              <sch:pattern id="p-snippet">
                <sch:rule context="//cda:patient">
                  <sch:assert id="a-snippet" test="@absent">SHALL have the attribute.</sch:assert>
                </sch:rule>
              </sch:pattern>
            </sch:schema>
            XML;

        for ($pad = 150; $pad <= 260; $pad++) {
            $name = str_repeat('a', $pad) . 'é' . str_repeat('b', 40);
            $xml = '<?xml version="1.0" encoding="UTF-8"?>'
                . '<ClinicalDocument xmlns="urn:hl7-org:v3"><patient>' . $name . '</patient></ClinicalDocument>';

            $out = (new SchematronValidator(new ArrayVocabularyLookup([])))->validate($xml, $sch)->toArray();
            self::assertCount(1, $out['errors'], "pad=$pad: expected the assertion to fail");

            $snippet = $out['errors'][0]['xml'];
            self::assertIsString($snippet);
            self::assertTrue(
                mb_check_encoding($snippet, 'UTF-8'),
                "pad=$pad: snippet was cut mid-character and is not valid UTF-8"
            );
            self::assertNotFalse(
                json_encode($out),
                "pad=$pad: the finding list could not be encoded, which would store an empty report"
            );
        }
    }

    /**
     * A pattern-scoped <sch:let> with a *relative* value is calculated against the
     * instance document root, not against each rule context node (ISO/IEC 19757-3
     * 5.4.5). The root node's child step `cda:ClinicalDocument` reaches the document
     * element; from the rule context node (an entry) the same step selects nothing,
     * so a merged scope would leave $docCode empty and flip the assertion.
     */
    public function testDocumentScopedVariableResolvesAgainstTheDocumentRoot(): void
    {
        $sch = <<<'XML'
            <?xml version="1.0"?>
            <sch:schema xmlns:sch="http://purl.oclc.org/dsdl/schematron">
              <sch:ns prefix="cda" uri="urn:hl7-org:v3"/>
              <sch:phase id="errors"><sch:active pattern="p-scope"/></sch:phase>
              <sch:pattern id="p-scope">
                <sch:let name="docCode" value="cda:ClinicalDocument/@code"/>
                <sch:rule context="//cda:entry">
                  <sch:assert id="a-scope" test="$docCode = 'DOC'">SHALL see the document-level code.</sch:assert>
                </sch:rule>
              </sch:pattern>
            </sch:schema>
            XML;
        $xml = '<?xml version="1.0"?>'
            . '<ClinicalDocument xmlns="urn:hl7-org:v3" code="DOC"><entry code="RULE"/></ClinicalDocument>';

        $result = (new SchematronValidator(new ArrayVocabularyLookup([])))->validate($xml, $sch);
        self::assertSame([], $result->ignored);
        self::assertSame([], $result->errors, 'the pattern-scoped let must see the document element');
    }

    public function testRuleScopedVariableShadowsADocumentScopedOne(): void
    {
        $sch = <<<'XML'
            <?xml version="1.0"?>
            <sch:schema xmlns:sch="http://purl.oclc.org/dsdl/schematron">
              <sch:ns prefix="cda" uri="urn:hl7-org:v3"/>
              <sch:phase id="errors"><sch:active pattern="p-shadow"/></sch:phase>
              <sch:pattern id="p-shadow">
                <sch:let name="code" value="@code"/>
                <sch:rule context="//cda:entry">
                  <sch:let name="code" value="@code"/>
                  <sch:assert id="a-shadow" test="$code = 'RULE'">SHALL see the rule-level code.</sch:assert>
                </sch:rule>
              </sch:pattern>
            </sch:schema>
            XML;
        $xml = '<?xml version="1.0"?>'
            . '<ClinicalDocument xmlns="urn:hl7-org:v3" code="DOC"><entry code="RULE"/></ClinicalDocument>';

        $result = (new SchematronValidator(new ArrayVocabularyLookup([])))->validate($xml, $sch);
        self::assertSame([], $result->ignored);
        self::assertSame([], $result->errors, 'the inner declaration must win');
    }

    public function testInvalidRuleContextThrows(): void
    {
        $sch = <<<'XML'
            <?xml version="1.0"?>
            <sch:schema xmlns:sch="http://purl.oclc.org/dsdl/schematron">
              <sch:ns prefix="cda" uri="urn:hl7-org:v3"/>
              <sch:phase id="errors"><sch:active pattern="p-bad"/></sch:phase>
              <sch:pattern id="p-bad">
                <sch:rule id="r-bad" context="cda:patient[unclosed(">
                  <sch:assert id="a-bad" test="@code">SHALL have a code.</sch:assert>
                </sch:rule>
              </sch:pattern>
            </sch:schema>
            XML;

        // A context that does not compile selects nothing, so every assertion in the
        // rule would be skipped and the document would look conformant.
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid schematron rule context');
        (new SchematronValidator(new ArrayVocabularyLookup([])))
            ->validate('<?xml version="1.0"?><ClinicalDocument xmlns="urn:hl7-org:v3"><patient/></ClinicalDocument>', $sch);
    }

    public function testExtendsNamingAnUndefinedRuleThrows(): void
    {
        $sch = <<<'XML'
            <?xml version="1.0"?>
            <sch:schema xmlns:sch="http://purl.oclc.org/dsdl/schematron">
              <sch:ns prefix="cda" uri="urn:hl7-org:v3"/>
              <sch:phase id="errors"><sch:active pattern="p-broken"/></sch:phase>
              <sch:pattern id="p-broken">
                <sch:rule id="r-concrete" context="//cda:observation">
                  <sch:extends rule="r-does-not-exist"/>
                  <sch:assert id="a-status" test="cda:statusCode">SHALL have statusCode.</sch:assert>
                </sch:rule>
              </sch:pattern>
            </sch:schema>
            XML;

        // Skipping the inherited assertions would report a clean document instead.
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Undefined schematron rule: r-does-not-exist');
        (new SchematronValidator(new ArrayVocabularyLookup([])))
            ->validate('<?xml version="1.0"?><ClinicalDocument xmlns="urn:hl7-org:v3"><observation/></ClinicalDocument>', $sch);
    }

    /**
     * qrda3-minimal.xml is 2.5 KB and reaches exactly one of the 35 QRDA III assertions
     * that reference a <sch:let> variable, so on its own it cannot tell "the expander
     * works" from "the expander is never asked". This fixture drives the variable-bearing
     * rules deliberately and asserts on the assertion ids that come back.
     *
     * The NPI cases are the sharp ones: four cda:id[@root='2.16.840.1.113883.4.6'] nodes
     * go through the $s -> $n -> $sum chain, and the one with a correct check digit must
     * come back clean. A chain that merely ran without computing correctly would fail
     * every NPI, including that one.
     */
    public function testSchLetVariablesEvaluateAgainstQrdaCategoryThree(): void
    {
        $fixtureDir = __DIR__ . '/fixtures';
        $schemaDir = __DIR__ . '/../../../../../src/Services/Cda/Schematron/schemas';
        $xml = self::readFile("$fixtureDir/qrda3-cms-variables.xml");
        $sch = self::readFile("$schemaDir/qrda3/2022_CMS_QRDA_Category_III.sch");
        /** @var array<string, list<string>> $vocab */
        $vocab = require "$schemaDir/qrda3/vocab.php";

        $out = (new SchematronValidator(new ArrayVocabularyLookup($vocab)))->validate($xml, $sch)->toArray();

        self::assertSame([], $out['ignored'], 'no assertion in this document may be unevaluable');

        $byAssertion = [];
        foreach ($out['errors'] as $error) {
            $id = self::stringField($error, 'assertionId');
            $byAssertion[$id][] = self::npiExtension(is_string($error['xml'] ?? null) ? $error['xml'] : '');
        }

        // One assertion over the whole NPI picture: a-CMS_0115 is $n, a-CMS_0116 is
        // number($s) = $s, and a-CMS_0117 is the full $sum check-digit computation.
        $npiFindings = [
            'a-CMS_0115-error' => self::findingsFor($byAssertion, 'a-CMS_0115-error'),
            'a-CMS_0116-error' => self::findingsFor($byAssertion, 'a-CMS_0116-error'),
            'a-CMS_0117-error' => self::findingsFor($byAssertion, 'a-CMS_0117-error'),
        ];
        self::assertSame(
            [
                'a-CMS_0115-error' => ['123456789'],
                'a-CMS_0116-error' => ['12345678AB'],
                'a-CMS_0117-error' => ['123456789', '12345678AB', '1234567890'],
            ],
            $npiFindings,
            'each malformed NPI must fail exactly the checks it violates'
        );
        self::assertNotContains(
            '1234567893',
            array_merge(...array_values($npiFindings)),
            'a valid NPI must pass all three checks - this is what proves $sum computes'
        );

        // $timeZoneExists, declared at pattern scope
        self::assertArrayHasKey('a-CMS_0122-error', $byAssertion, 'the offset-less serviceEvent time must be caught');
        // $intendedRecipient-Doc, $NPI-Count and $TIN-Count, declared at rule scope
        self::assertArrayHasKey(
            'a-4506-18177_C01-MIPSGROUP-assignedEntity-error',
            $byAssertion,
            'MIPS_GROUP with both a TIN and an NPI must be caught'
        );

        // Change-detection snapshot for everything above and the rest of the findings.
        self::assertSame(self::readJson("$fixtureDir/qrda3-cms-variables.php-golden.json"), $out);
    }

    /**
     * Read one assertion's findings out of the map. Taking the id as a plain string
     * parameter keeps the lookup honest: indexing the map with a literal inline lets
     * PHPStan narrow the offset through assertSame and then flag the ?? as dead.
     *
     * @param array<string, list<string>> $byAssertion
     * @return list<string>
     */
    private static function findingsFor(array $byAssertion, string $assertionId): array
    {
        return $byAssertion[$assertionId] ?? [];
    }

    /**
     * Pull the extension attribute out of a finding's XML snippet so a failure message
     * names the offending NPI rather than a positional path.
     */
    private static function npiExtension(string $xmlSnippet): string
    {
        return preg_match('/extension="([^"]*)"/', $xmlSnippet, $m) === 1 ? $m[1] : '';
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
