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

        $expectedDom = $this->cleanWhitespace($expectedDom);

        $fixtureDate = '20251215';
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
        assert($timestampValues !== false);
        foreach ($timestampValues as $timestamp) {
            if ($timestamp instanceof \DOMElement) {
                $timestamp->setAttribute('value', $newTimestamp);
            }
        }

        $dateTime = \DateTimeImmutable::createFromFormat('Ymd', $currentTimestamp);
        $dateTimeNew = \DateTimeImmutable::createFromFormat('Ymd', $newTimestamp);
        if ($dateTime !== false && $dateTimeNew !== false) {
            $expr = "//hl7:tr/hl7:td/text()[normalize-space(.) = '" . $dateTime->format('Y-m-d') . "']";
            $timestampTextNodes = $xpath->query($expr);
            assert($timestampTextNodes !== false);
            foreach ($timestampTextNodes as $textNode) {
                $textNode->nodeValue = $dateTimeNew->format('Y-m-d');
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

            $actualId = $actual->query('.//hl7:id', $actualNode)->item(0);
            $expectedId = $expected->query('.//hl7:id', $expectedNode)->item(0);

            if ($actualId instanceof \DOMElement && $expectedId instanceof \DOMElement) {
                $actualId->setAttribute('root', $expectedId->getAttribute('root'));
            }
        }
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
