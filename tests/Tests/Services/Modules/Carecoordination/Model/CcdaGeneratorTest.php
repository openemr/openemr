<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Modules\CareCoordination\Model;

use Carecoordination\Model\CcdaGenerator;
use Carecoordination\Model\EncounterccdadispatchTable;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for CcdaGenerator::socket_get().
 *
 * This test exercises the Node.js CCDA service path through CcdaGenerator
 * to ensure the full pipeline produces expected output.
 */
class CcdaGeneratorTest extends TestCase
{
    private const FIXTURE_DIR = __DIR__ . '/../../../../data/Services/Modules/CareCoordination/Model/CcdaServiceDocumentRequestor/';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        if (!defined('IS_PORTAL')) {
            define('IS_PORTAL', false);
        }
        if (!defined('IS_DASHBOARD')) {
            define('IS_DASHBOARD', true);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['ccda_alt_service_enable'] = 3;
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['ccda_alt_service_enable']);
        parent::tearDown();
    }

    public function testSocketGetProducesValidCda(): void
    {
        $inputData = file_get_contents(self::FIXTURE_DIR . 'ccda-example-input1.xml');
        self::assertNotFalse($inputData, 'Failed to read input fixture');
        $inputData = trim($inputData);

        $expectedOutput = file_get_contents(self::FIXTURE_DIR . 'ccda-example-response1.xml');
        self::assertNotFalse($expectedOutput, 'Failed to read expected fixture');

        $dispatchTable = $this->createMock(EncounterccdadispatchTable::class);
        $generator = new CcdaGenerator($dispatchTable);

        $actualOutput = $generator->socket_get($inputData);

        self::assertNotEmpty($actualOutput, 'socket_get returned empty response');

        $this->assertCdaEquals($expectedOutput, $actualOutput);
    }

    /**
     * Cert-oriented invariant checks on the generated document.
     *
     * Unlike the golden comparison, this pins IG/datatype *properties* rather than
     * exact bytes, so it (a) survives intentional output improvements without a
     * fixture regeneration, and (b) asserts properties of the output rather than a
     * stored document, so it carries over unchanged to any generator — including
     * the PHP module intended to replace the Node service.
     *
     * Every assertion below is calibrated against a known ONC SITE-passing OpenEMR
     * document.
     */
    public function testGeneratedCdaMeetsIgInvariants(): void
    {
        // Generate from the populated cert scenario (the payload that produces a
        // known ONC SITE-passing document), not the sparse example input.
        $inputData = file_get_contents(self::FIXTURE_DIR . 'ccda-cert-data.xml');
        self::assertNotFalse($inputData, 'Failed to read input fixture');

        $dispatchTable = $this->createMock(EncounterccdadispatchTable::class);
        $generator = new CcdaGenerator($dispatchTable);
        $xml = $generator->socket_get(trim($inputData));
        self::assertNotEmpty($xml, 'socket_get returned empty response');

        $dom = $this->loadDom($xml);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        // Root is a US Realm Clinical Document.
        self::assertSame('ClinicalDocument', $dom->documentElement->localName, 'Root is not ClinicalDocument');

        // No empty required attributes. An empty @extension / @code / @displayName /
        // @root / @value / @nullFlavor is an HL7 datatype violation (MDHT "bad value").
        $emptyAttrs = $xpath->query(
            '//*[@extension=""] | //*[@code=""] | //*[@displayName=""] | //*[@root=""] | //*[@value=""] | //*[@nullFlavor=""]'
        );
        self::assertSame(0, $emptyAttrs->length, $this->describeNodes('empty attribute', $emptyAttrs));

        // No empty name / address ST parts (ADXP / ENXP validateST): each part
        // carries text, or the element is omitted.
        $emptyParts = $xpath->query(
            '//hl7:family[not(node())] | //hl7:given[not(node())] | //hl7:prefix[not(node())] | //hl7:suffix[not(node())]'
            . ' | //hl7:streetAddressLine[not(node())] | //hl7:city[not(node())] | //hl7:state[not(node())]'
            . ' | //hl7:postalCode[not(node())]'
        );
        self::assertSame(0, $emptyParts->length, $this->describeNodes('empty name/address part', $emptyParts));

        // No empty <name> that also lacks a nullFlavor.
        $emptyNames = $xpath->query('//hl7:name[not(node()) and not(@nullFlavor)]');
        self::assertSame(0, $emptyNames->length, $this->describeNodes('empty <name> without nullFlavor', $emptyNames));

        // Every assignedPerson carries a name (fielded or nullFlavor).
        $namelessAuthors = $xpath->query('//hl7:assignedPerson[not(hl7:name)]');
        self::assertSame(0, $namelessAuthors->length, $this->describeNodes('assignedPerson without name', $namelessAuthors));

        // Every date value is a well-formed HL7 timestamp (YYYY..YYYYMMDDHHMMSS,
        // optional timezone). Unknown dates use nullFlavor rather than an empty,
        // "Invalid date", or fabricated value.
        $dateRegex = '/^\d{4}(\d{2}(\d{2}(\d{2}(\d{2}(\d{2})?)?)?)?)?([+-]\d{4})?$/';
        foreach ($xpath->query('//hl7:low | //hl7:high | //hl7:center | //hl7:effectiveTime | //hl7:time') as $el) {
            if (!$el instanceof \DOMElement || !$el->hasAttribute('value')) {
                continue;
            }
            $value = $el->getAttribute('value');
            self::assertMatchesRegularExpression($dateRegex, $value, 'Malformed date @value: "' . $value . '"');
        }

        // Required US Realm CCD sections present (SHALL, entries-required).
        $requiredSections = [
            '48765-2' => 'Allergies',
            '10160-0' => 'Medications',
            '11450-4' => 'Problems',
            '47519-4' => 'Procedures',
            '30954-2' => 'Results',
        ];
        foreach ($requiredSections as $code => $label) {
            $section = $xpath->query("//hl7:structuredBody/hl7:component/hl7:section/hl7:code[@code='" . $code . "']");
            self::assertGreaterThan(0, $section->length, "Missing required section: {$label} ({$code})");
        }
    }

    private function describeNodes(string $what, \DOMNodeList $nodes): string
    {
        if ($nodes->length === 0) {
            return "Found 0 {$what}(s)";
        }
        $samples = [];
        $limit = min(5, $nodes->length);
        for ($i = 0; $i < $limit; $i++) {
            $node = $nodes->item($i);
            if ($node !== null) {
                $samples[] = trim((string)$node->ownerDocument->saveXML($node));
            }
        }
        return "Found {$nodes->length} {$what}(s), e.g.:\n" . implode("\n", $samples);
    }

    private function assertCdaEquals(string $expected, string $actual): void
    {
        $expectedDom = $this->loadDom($expected);
        $actualDom = $this->loadDom($actual);

        // Normalize both documents identically so the comparison pins structure,
        // not values that legitimately vary between runs: wall-clock "now" dates
        // and per-run UUID roots (rootId / uniqueId). Data dates and OID roots
        // come from the input and match on both sides regardless.
        foreach ([$expectedDom, $actualDom] as $dom) {
            $this->cleanWhitespace($dom);
            $this->normalizeVolatile($dom);
        }

        self::assertXmlStringEqualsXmlString(
            $expectedDom->C14N(),
            $actualDom->C14N(),
            'Generated CDA does not match expected output (timestamps and UUID roots normalized)'
        );
    }

    /**
     * Collapse values that vary per run to fixed tokens, applied identically to
     * the expected and actual documents:
     *   - date/time @value attributes (YYYYMMDD or YYYYMMDDHHMM[+/-ZZZZ])
     *   - narrative date text in <td> (YYYY-MM-DD)
     *   - UUID @root attributes (OID roots such as 2.16.* are left intact)
     */
    private function normalizeVolatile(DOMDocument $dom): void
    {
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');
        $xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

        $valueNodes = $xpath->query('//*[@value]');
        if ($valueNodes !== false) {
            foreach ($valueNodes as $node) {
                if ($node instanceof \DOMElement
                    && preg_match('/^\d{8}(\d{4}([+-]\d{4})?)?$/', $node->getAttribute('value')) === 1) {
                    $node->setAttribute('value', 'NORMALIZED_DATE');
                }
            }
        }

        $textNodes = $xpath->query('//hl7:td/text() | //xhtml:td/text()');
        if ($textNodes !== false) {
            foreach ($textNodes as $textNode) {
                if (preg_match('/^\s*\d{4}-\d{2}-\d{2}\s*$/', (string)$textNode->nodeValue) === 1) {
                    $textNode->nodeValue = 'NORMALIZED_DATE';
                }
            }
        }

        $rootNodes = $xpath->query('//*[@root]');
        if ($rootNodes !== false) {
            foreach ($rootNodes as $node) {
                if ($node instanceof \DOMElement
                    && preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $node->getAttribute('root')) === 1) {
                    $node->setAttribute('root', 'NORMALIZED_UUID');
                }
            }
        }
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

    private function cleanWhitespace(DOMDocument $dom): DOMDocument
    {
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');
        $xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

        $nodes = $xpath->query('//hl7:text//text() | //xhtml:td//text() | //hl7:value//text()');
        if ($nodes !== false) {
            foreach ($nodes as $node) {
                $node->nodeValue = trim((string)preg_replace('/\s+/u', ' ', (string)$node->nodeValue));
            }
        }

        return $dom;
    }
}
