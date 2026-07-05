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
                if (preg_match('/^\s*\d{4}-\d{2}-\d{2}\s*$/', (string) $textNode->nodeValue) === 1) {
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
                $node->nodeValue = trim((string) preg_replace('/\s+/u', ' ', (string) $node->nodeValue));
            }
        }

        return $dom;
    }
}
