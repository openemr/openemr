<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Cda;

use DOMDocument;
use DOMXPath;
use OpenEMR\Cda\InternalToCdaConverter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class InternalToCdaConverterTest extends TestCase
{
    private const FIXTURE_DIR = __DIR__ . '/../../data/Services/Modules/CareCoordination/Model/CcdaServiceDocumentRequestor/';

    private ?string $actualOutput = null;
    private ?string $expectedOutput = null;

    public function testConvertProducesValidCda(): void
    {
        [$actual, $expected] = $this->getConvertedAndExpected();
        $this->assertCdaEquals($expected, $actual);
    }

    /**
     * The Functional Status Organizer (4.66) must contain the Functional Status
     * Observation (4.67) and Self Care Activities (4.128) as two separate member
     * observations. Regression guard for the 4.128 templateId being incorrectly
     * nested inside the 4.67 observation.
     */
    public function testFunctionalStatusSelfCareIsSeparateObservation(): void
    {
        $input = <<<'XML'
            <CCDA>
                <functional_status>
                    <item>
                        <extension>FS-1</extension>
                        <date>2021-07-23</date>
                        <code>3298001</code>
                        <code_text>Amnestic disorder</code_text>
                        <code_type>SNOMED CT</code_type>
                    </item>
                </functional_status>
            </CCDA>
            XML;

        $converter = new InternalToCdaConverter();
        $dom = $this->loadDom($converter->convert($input));
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        $organizers = $xpath->query("//hl7:organizer[hl7:templateId[@root='2.16.840.1.113883.10.20.22.4.66']]");
        self::assertNotFalse($organizers, 'Organizer query must be valid');
        self::assertSame(1, $organizers->length, 'Expected exactly one Functional Status Organizer');
        $organizer = $organizers->item(0);
        self::assertInstanceOf(\DOMElement::class, $organizer, 'Organizer node must be an element');

        $allObs = $xpath->query('hl7:component/hl7:observation', $organizer);
        self::assertNotFalse($allObs, 'Component observation query must be valid');
        self::assertSame(2, $allObs->length, 'Organizer must contain two separate member observations');

        $funcObs = $xpath->query("hl7:component/hl7:observation[hl7:templateId[@root='2.16.840.1.113883.10.20.22.4.67']]", $organizer);
        self::assertNotFalse($funcObs, 'Functional Status Observation query must be valid');
        self::assertSame(1, $funcObs->length, 'Functional Status Observation (4.67) must be its own component');

        $selfCareObs = $xpath->query("hl7:component/hl7:observation[hl7:templateId[@root='2.16.840.1.113883.10.20.22.4.128']]", $organizer);
        self::assertNotFalse($selfCareObs, 'Self Care Activities query must be valid');
        self::assertSame(1, $selfCareObs->length, 'Self Care Activities (4.128) must be its own component');

        $misplaced = $xpath->query(
            "hl7:component/hl7:observation[hl7:templateId[@root='2.16.840.1.113883.10.20.22.4.67']]"
            . "/hl7:templateId[@root='2.16.840.1.113883.10.20.22.4.128']",
            $organizer
        );
        self::assertNotFalse($misplaced, 'Misplaced-templateId query must be valid');
        self::assertSame(0, $misplaced->length, 'Self Care Activities templateId must not be nested in the Functional Status Observation');
    }

    /**
     * A procedure with a code but no code type must omit the codeSystemName
     * attribute rather than emit codeSystemName="", which is an empty st value
     * the C-CDA IG rejects. Mirrors Node's translate.code omit-empty behavior.
     */
    public function testProcedureCodeOmitsEmptyCodeSystemName(): void
    {
        $input = <<<'XML'
            <CCDA>
                <procedures>
                    <procedure>
                        <extension>PROC-1</extension>
                        <date>2021-07-23</date>
                        <code>73761001</code>
                        <description>Colonoscopy</description>
                        <code_type></code_type>
                    </procedure>
                </procedures>
            </CCDA>
            XML;

        $code = $this->firstProcedureCode($input);
        self::assertSame('73761001', $code->getAttribute('code'), 'Code value must be preserved');
        self::assertFalse($code->hasAttribute('codeSystemName'), 'Empty code type must not emit codeSystemName');
        self::assertFalse($code->hasAttribute('codeSystem'), 'Empty code type must not emit codeSystem');
        self::assertFalse($code->hasAttribute('nullFlavor'), 'A present code must not be nullFlavored');
    }

    /**
     * A procedure with no code at all must collapse to nullFlavor="UNK" rather
     * than emit an empty code="" attribute. Mirrors Node's translate.code.
     */
    public function testProcedureWithEmptyCodeUsesNullFlavor(): void
    {
        $input = <<<'XML'
            <CCDA>
                <procedures>
                    <procedure>
                        <extension>PROC-1</extension>
                        <date>2021-07-23</date>
                        <code></code>
                        <description>Unknown procedure</description>
                        <code_type>SNOMED CT</code_type>
                    </procedure>
                </procedures>
            </CCDA>
            XML;

        $code = $this->firstProcedureCode($input);
        self::assertSame('UNK', $code->getAttribute('nullFlavor'), 'Missing code must be nullFlavor UNK');
        self::assertFalse($code->hasAttribute('code'), 'nullFlavor code must not carry an empty code attribute');
        self::assertFalse($code->hasAttribute('codeSystemName'), 'nullFlavor code must not carry codeSystemName');
    }

    /**
     * The author code element is guarded by existsWhen propertyNotEmpty('code')
     * in Node, so an unknown physician type must omit the whole code element
     * rather than emit empty coded attributes.
     */
    public function testDocumentAuthorOmitsCodeWhenTypeEmpty(): void
    {
        $input = <<<'XML'
            <CCDA>
                <created_time_timezone>20210723</created_time_timezone>
                <author>
                    <npi>1234567890</npi>
                    <physician_type_code></physician_type_code>
                    <physician_type></physician_type>
                    <physician_type_system></physician_type_system>
                    <physician_type_system_name></physician_type_system_name>
                </author>
            </CCDA>
            XML;

        $xpath = $this->convertToXPath($input);
        $codes = $xpath->query('/hl7:ClinicalDocument/hl7:author/hl7:assignedAuthor/hl7:code');
        self::assertNotFalse($codes, 'Author code query must be valid');
        self::assertSame(0, $codes->length, 'Empty author type code must omit the code element');
    }

    public function testDocumentAuthorEmitsCodeWhenTypePresent(): void
    {
        $input = <<<'XML'
            <CCDA>
                <created_time_timezone>20210723</created_time_timezone>
                <author>
                    <npi>1234567890</npi>
                    <physician_type_code>207Q00000X</physician_type_code>
                    <physician_type>Family Medicine</physician_type>
                    <physician_type_system>2.16.840.1.113883.6.101</physician_type_system>
                    <physician_type_system_name>NUCC</physician_type_system_name>
                </author>
            </CCDA>
            XML;

        $xpath = $this->convertToXPath($input);
        $codes = $xpath->query('/hl7:ClinicalDocument/hl7:author/hl7:assignedAuthor/hl7:code');
        self::assertNotFalse($codes, 'Author code query must be valid');
        self::assertSame(1, $codes->length, 'Present author type code must emit the code element');
    }

    /**
     * The medication manufacturedMaterial code uses Node's leafLevel.code
     * (no existsWhen), so a missing RxNorm code collapses to nullFlavor="UNK"
     * rather than emitting code="null_flavor" or an empty codeSystemName.
     */
    public function testMedicationCodeUsesNullFlavorWhenRxnormEmpty(): void
    {
        $input = <<<'XML'
            <CCDA>
                <medications>
                    <medication>
                        <extension>MED-1</extension>
                        <drug>Aspirin</drug>
                        <rxnorm></rxnorm>
                    </medication>
                </medications>
            </CCDA>
            XML;

        $xpath = $this->convertToXPath($input);
        $codes = $xpath->query('//hl7:manufacturedMaterial/hl7:code');
        self::assertNotFalse($codes, 'Material code query must be valid');
        $code = $codes->item(0);
        self::assertInstanceOf(\DOMElement::class, $code, 'Material code element must exist');
        self::assertSame('UNK', $code->getAttribute('nullFlavor'), 'Missing RxNorm must be nullFlavor UNK');
        self::assertFalse($code->hasAttribute('code'), 'nullFlavor code must not carry the null_flavor sentinel');
        self::assertFalse($code->hasAttribute('codeSystemName'), 'nullFlavor code must not carry codeSystemName');
    }

    /**
     * The immunization manufacturedMaterial code uses Node's leafLevel.code, so
     * a missing CVX code collapses to nullFlavor="UNK" rather than emit empty
     * coded attributes.
     */
    public function testImmunizationCodeUsesNullFlavorWhenCvxEmpty(): void
    {
        $input = <<<'XML'
            <CCDA>
                <immunizations>
                    <immunization>
                        <extension>IMM-1</extension>
                        <cvx_code></cvx_code>
                        <code_text>Influenza</code_text>
                    </immunization>
                </immunizations>
            </CCDA>
            XML;

        $xpath = $this->convertToXPath($input);
        $codes = $xpath->query('//hl7:manufacturedMaterial/hl7:code');
        self::assertNotFalse($codes, 'Material code query must be valid');
        $code = $codes->item(0);
        self::assertInstanceOf(\DOMElement::class, $code, 'Material code element must exist');
        self::assertSame('UNK', $code->getAttribute('nullFlavor'), 'Missing CVX code must be nullFlavor UNK');
        self::assertFalse($code->hasAttribute('codeSystemName'), 'nullFlavor code must not carry codeSystemName');
    }

    private function firstProcedureCode(string $input): \DOMElement
    {
        $xpath = $this->convertToXPath($input);
        $codes = $xpath->query('//hl7:procedure/hl7:code');
        self::assertNotFalse($codes, 'Procedure code query must be valid');
        $code = $codes->item(0);
        self::assertInstanceOf(\DOMElement::class, $code, 'Procedure code element must exist');
        return $code;
    }

    private function convertToXPath(string $input): DOMXPath
    {
        $converter = new InternalToCdaConverter();
        $dom = $this->loadDom($converter->convert($input));
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');
        return $xpath;
    }

    #[DataProvider('demoFixtureProvider')]
    public function testDemoFixture(string $inputFile, string $expectedFile): void
    {
        $input = file_get_contents(self::FIXTURE_DIR . $inputFile);
        self::assertNotFalse($input, "Failed to read input fixture: $inputFile");
        $expected = file_get_contents(self::FIXTURE_DIR . $expectedFile);
        self::assertNotFalse($expected, "Failed to read expected fixture: $expectedFile");

        $converter = new InternalToCdaConverter();
        $actual = $converter->convert(trim($input));

        $this->assertCdaEquals($expected, $actual);
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function demoFixtureProvider(): array
    {
        return [
            'demo1' => ['ccda-input-demo1.xml', 'ccda-output-demo1.xml'],
            'demo2' => ['ccda-input-demo2.xml', 'ccda-output-demo2.xml'],
        ];
    }

    #[DataProvider('sectionTemplateIdProvider')]
    public function testSection(string $name, string $templateId): void
    {
        [$actual, $expected] = $this->getConvertedAndExpected();
        $this->assertSectionMatches($actual, $expected, $templateId, $name);
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function sectionTemplateIdProvider(): array
    {
        return [
            'Care Team' => ['Care Team', '2.16.840.1.113883.10.20.22.2.500'],
            'Allergies' => ['Allergies', '2.16.840.1.113883.10.20.22.2.6.1'],
            'Medications' => ['Medications', '2.16.840.1.113883.10.20.22.2.1.1'],
            'Problems' => ['Problems', '2.16.840.1.113883.10.20.22.2.5.1'],
            'Procedures' => ['Procedures', '2.16.840.1.113883.10.20.22.2.7.1'],
            'Results' => ['Results', '2.16.840.1.113883.10.20.22.2.3.1'],
            'Encounters' => ['Encounters', '2.16.840.1.113883.10.20.22.2.22.1'],
            'Immunizations' => ['Immunizations', '2.16.840.1.113883.10.20.22.2.2.1'],
            'Vital Signs' => ['Vital Signs', '2.16.840.1.113883.10.20.22.2.4.1'],
            'Social History' => ['Social History', '2.16.840.1.113883.10.20.22.2.17'],
            'Payers' => ['Payers', '2.16.840.1.113883.10.20.22.2.18'],
            'Medical Equipment' => ['Medical Equipment', '2.16.840.1.113883.10.20.22.2.23'],
            'Functional Status' => ['Functional Status', '2.16.840.1.113883.10.20.22.2.14'],
            'Mental Status' => ['Mental Status', '2.16.840.1.113883.10.20.22.2.56'],
            'Plan of Care' => ['Plan of Care', '2.16.840.1.113883.10.20.22.2.10'],
            'Goals' => ['Goals', '2.16.840.1.113883.10.20.22.2.60'],
            'Health Concerns' => ['Health Concerns', '2.16.840.1.113883.10.20.22.2.58'],
            'Assessment' => ['Assessment', '2.16.840.1.113883.10.20.22.2.8'],
        ];
    }

    /**
     * @return array{string, string}
     */
    private function getConvertedAndExpected(): array
    {
        if ($this->actualOutput === null) {
            $input = file_get_contents(self::FIXTURE_DIR . 'ccda-example-input1.xml');
            self::assertNotFalse($input, 'Failed to read input fixture');
            $expected = file_get_contents(self::FIXTURE_DIR . 'ccda-example-response1.xml');
            self::assertNotFalse($expected, 'Failed to read expected fixture');
            $this->expectedOutput = $expected;

            $converter = new InternalToCdaConverter();
            $this->actualOutput = $converter->convert(trim($input));
        }
        self::assertNotNull($this->expectedOutput, 'Expected output not initialized');
        return [$this->actualOutput, $this->expectedOutput];
    }

    private function assertCdaEquals(string $expected, string $actual): void
    {
        $expectedDom = $this->loadDom($expected);
        $actualDom = $this->loadDom($actual);

        $expectedDom = $this->cleanWhitespace($expectedDom);

        // Extract fixture date from expected document's effectiveTime
        $fixtureDate = $this->extractFixtureDate($expectedDom);
        $currentDate = date('Ymd');
        $actualDom = $this->replaceTimestamps($actualDom, $currentDate, $fixtureDate);
        $actualDom = $this->normalizeDynamicIds($actualDom, $expectedDom);
        $actualDom = $this->cleanWhitespace($actualDom);

        self::assertXmlStringEqualsXmlString(
            $expectedDom->C14N(),
            $actualDom->C14N(),
            'Generated CDA does not match expected output'
        );
    }

    private function assertSectionMatches(string $actual, string $expected, string $templateId, string $name = ''): void
    {
        $actualDom = $this->loadDom($actual);
        $expectedDom = $this->loadDom($expected);

        // Extract fixture date from the full expected document before extracting sections
        $fixtureDate = $this->extractFixtureDate($expectedDom);

        $actualSection = $this->extractSection($actualDom, $templateId);
        $expectedSection = $this->extractSection($expectedDom, $templateId);

        $label = $name !== '' ? "$name ($templateId)" : $templateId;

        if ($expectedSection === '') {
            self::markTestSkipped("Section $label not found in expected output");
        }

        self::assertNotSame('', $actualSection, "Section $label missing from actual output");

        $actualDom = $this->loadDom($actualSection);
        $expectedDom = $this->loadDom($expectedSection);

        $currentDate = date('Ymd');
        $actualDom = $this->replaceTimestamps($actualDom, $currentDate, $fixtureDate);
        $actualDom = $this->cleanWhitespace($actualDom);
        $expectedDom = $this->cleanWhitespace($expectedDom);

        self::assertXmlStringEqualsXmlString(
            $expectedDom->C14N(),
            $actualDom->C14N(),
            "Section $label mismatch"
        );
    }

    private function extractSection(DOMDocument $dom, string $templateId): string
    {
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');
        $result = $xpath->query("//hl7:section[hl7:templateId[@root='$templateId']]");
        if ($result === false) {
            return '';
        }
        $section = $result->item(0);
        if (!$section instanceof \DOMElement) {
            return '';
        }

        $newDoc = new DOMDocument();
        $newDoc->preserveWhiteSpace = false;
        $imported = $newDoc->importNode($section, true);
        $newDoc->appendChild($imported);
        return $newDoc->saveXML() ?: '';
    }

    private function loadDom(string $xml): DOMDocument
    {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $loaded = $dom->loadXML($xml, LIBXML_NOBLANKS);
        if ($loaded === false) {
            throw new \RuntimeException('Invalid XML');
        }
        return $dom;
    }

    private function replaceTimestamps(DOMDocument $xml, string $currentTimestamp, string $newTimestamp): DOMDocument
    {
        $xpath = new DOMXPath($xml);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        $expr = '//*[@value="' . $currentTimestamp . '"]';
        $timestampValues = $xpath->query($expr);
        if ($timestampValues !== false) {
            foreach ($timestampValues as $timestamp) {
                if ($timestamp instanceof \DOMElement) {
                    $timestamp->setAttribute('value', $newTimestamp);
                }
            }
        }

        $dateTime = \DateTimeImmutable::createFromFormat('Ymd', $currentTimestamp);
        $dateTimeNew = \DateTimeImmutable::createFromFormat('Ymd', $newTimestamp);
        if ($dateTime !== false && $dateTimeNew !== false) {
            $expr = "//hl7:tr/hl7:td/text()[normalize-space(.) = '" . $dateTime->format('Y-m-d') . "']";
            $timestampTextNodes = $xpath->query($expr);
            if ($timestampTextNodes !== false) {
                foreach ($timestampTextNodes as $textNode) {
                    $textNode->nodeValue = $dateTimeNew->format('Y-m-d');
                }
            }
        }

        return $xml;
    }

    private function normalizeDynamicIds(DOMDocument $actual, DOMDocument $expected): DOMDocument
    {
        $xpath = new DOMXPath($actual);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');
        $xpathExpected = new DOMXPath($expected);
        $xpathExpected->registerNamespace('hl7', 'urn:hl7-org:v3');

        $this->replaceRootIdForQuery("//hl7:observation/hl7:code[@code='76691-5']", $xpath, $xpathExpected);
        $this->replaceRootIdForQuery("//hl7:observation/hl7:code[@code='46098-0']", $xpath, $xpathExpected);
        $this->replaceRootIdForQuery("//hl7:observation/hl7:code[@code='76690-7']", $xpath, $xpathExpected);
        $this->replaceRootIdForQuery("//hl7:section/hl7:entry/hl7:organizer/hl7:code[@code='86744-0']", $xpath, $xpathExpected);
        $this->replaceRootIdForQuery("//hl7:component/hl7:act/hl7:code[@code='85847-2']", $xpath, $xpathExpected);

        return $actual;
    }

    private function replaceRootIdForQuery(string $query, DOMXPath $actual, DOMXPath $expected): void
    {
        $actualList = $actual->query($query);
        $expectedList = $expected->query($query);

        if ($actualList === false || $expectedList === false) {
            return;
        }

        $count = $actualList->count();
        if ($count !== $expectedList->count()) {
            return;
        }

        for ($i = 0; $i < $count; $i++) {
            $actualNode = $actualList->item($i)?->parentNode;
            $expectedNode = $expectedList->item($i)?->parentNode;

            if (!$actualNode instanceof \DOMElement || !$expectedNode instanceof \DOMElement) {
                continue;
            }

            $actualIdList = $actual->query('.//hl7:id', $actualNode);
            $expectedIdList = $expected->query('.//hl7:id', $expectedNode);
            if ($actualIdList === false || $expectedIdList === false) {
                continue;
            }

            $actualId = $actualIdList->item(0);
            $expectedId = $expectedIdList->item(0);
            if ($actualId instanceof \DOMElement && $expectedId instanceof \DOMElement) {
                $actualId->setAttribute('root', $expectedId->getAttribute('root'));
            }
        }
    }

    private function extractFixtureDate(DOMDocument $dom): string
    {
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        // Get the effectiveTime from the document header (ClinicalDocument/effectiveTime)
        $effectiveTime = $xpath->query('/hl7:ClinicalDocument/hl7:effectiveTime/@value');
        if ($effectiveTime !== false && $effectiveTime->length > 0) {
            $value = $effectiveTime->item(0)->nodeValue ?? '';
            // Extract just the date portion (first 8 chars: YYYYMMDD)
            if (strlen($value) >= 8) {
                return substr($value, 0, 8);
            }
        }

        // Fallback to a default if not found
        return '20251215';
    }

    private function cleanWhitespace(DOMDocument $dom): DOMDocument
    {
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');
        $xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

        $nodes = $xpath->query('//hl7:text//text() | //xhtml:td//text() | //hl7:value//text()');
        if ($nodes !== false) {
            foreach ($nodes as $node) {
                $node->nodeValue = trim((string) preg_replace('/\s+/u', ' ', (string) $node->nodeValue));
            }
        }

        return $dom;
    }
}
