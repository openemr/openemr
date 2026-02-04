<?php

/*
 * CcdaGeneratorTest.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
namespace OpenEMR\Tests\Services\Modules\CareCoordination\Model;

use Carecoordination\Model\CcdaServiceDocumentRequestor;
use Monolog\Level;
use OpenEMR\Common\Logging\SystemLogger;
use PHPUnit\Framework\TestCase;
use DOMXPath;
use DOMDocument;

class CcdaServiceDocumentRequestorTest extends TestCase
{
    const EXAMPLE_DIR = __DIR__ . "/../../../../data/Services/Modules/CareCoordination/Model/CcdaServiceDocumentRequestor/";

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Define context constants required by isServiceEnabled()
        // These are normally defined during OpenEMR runtime but not in test environment
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

        // Enable C-CDA alternate service for dashboard context
        // 1 = Dashboard only, 2 = Portal only, 3 = Both
        $GLOBALS['ccda_alt_service_enable'] = 3;
    }

    protected function tearDown(): void
    {
        // Clean up global settings
        unset($GLOBALS['ccda_alt_service_enable']);
        parent::tearDown();
    }

    public function testSocket_get(): void
    {
        $data = file_get_contents(self::EXAMPLE_DIR . "ccda-example-input1.xml");
        $this->assertNotEmpty($data);

        $data = trim($data); // trim whitespace as CCDA service requires the <CCDA> and </CCDA> tag to be at the start and end of the data

        $docRequestor = new CcdaServiceDocumentRequestor();
        $docRequestor->setSystemLogger(new SystemLogger(Level::Critical));
        $response = $docRequestor->socket_get($data);

        $this->assertNotEmpty($response);

        $responseCheck = file_get_contents(self::EXAMPLE_DIR . "ccda-example-response1.xml");

        $domXMLContent = $this->getDomXml($response);
        $domXMLExpectedContent = $this->getDomXml($responseCheck);
        $domXMLExpectedContent = $this->cleanWhitespaceInTextNodes($domXMLExpectedContent);

        $docGeneratedLatestDate = "20251215";
        $currentGeneratedDate = date("Ymd");

        $updatedDomXMLContent = $this->replaceLatestTimeStamp($domXMLContent, $currentGeneratedDate, $docGeneratedLatestDate);
        $updatedDomXMLContent = $this->updateRootIds($updatedDomXMLContent, $domXMLExpectedContent);

        // updating the root nodes and timestamps is cleaning up whitespace in the updated DOMDocument
        // these unfortunately are causing issues with the comparison
        $updatedDomXMLContent = $this->cleanWhitespaceInTextNodes($updatedDomXMLContent);

        $this->assertXmlStringEqualsXmlString($domXMLExpectedContent->C14N(), $updatedDomXMLContent->C14N(), "The generated CCDA document does not match the expected content.");
    }

    // TODO: move to a shared test utility class
    private function getDomXml(string $xmlContent): \DOMDocument
    {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        if (!$dom->loadXML($xmlContent, LIBXML_NOBLANKS)) {
            throw new \RuntimeException('Invalid XML');
        }
        return $dom;
    }

    private function getDocumentGenerationTime(\DOMDocument $xml): string
    {
        $xpath = new DOMXPath($xml);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');
        $effectiveTime = $xpath->query("//hl7:effectiveTime")->item(0);
        if ($effectiveTime && $effectiveTime->hasAttribute('value')) {
            return $effectiveTime->getAttribute('value');
        } else {
            throw new \RuntimeException('effectiveTime element with value attribute not found in XML');
        }
    }

    private function replaceLatestTimeStamp(\DOMDocument $xml, string $currentTimestamp, string $newTimeStamp): \DOMDocument
    {
        $xpath = new DOMXPath($xml);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        $expr = '//*[@value="' . $currentTimestamp .  '"]';
        $timestampValues = $xpath->query($expr);
        foreach ($timestampValues as $timestamp) {
            $timestamp->setAttribute('value', $newTimeStamp);
        }

        $dateTime = \DateTimeImmutable::createFromFormat("Ymd", $currentTimestamp);
        $dateTimeNewFormat = \DateTimeImmutable::createFromFormat("Ymd", $newTimeStamp);

        $expr = "//hl7:tr/hl7:td/text()[normalize-space(.) = '" . $dateTime->format("Y-m-d") .  "']";
        $timestampTextNodes = $xpath->query($expr);
        foreach ($timestampTextNodes as $textNode) {
            $textNode->nodeValue = $dateTimeNewFormat->format("Y-m-d");
        }

        return $xml;
    }

    private function updateRootIds(\DOMDocument $updatedDomXMLContent, \DOMDocument $domXMLExpectedContent)
    {
        // TODO: We need to figure out why we are generating unique identifiers for these fields that change on
        // each document generation instead of using something the db uuid fields.
        // I don't know if that is required per spec, or if that's a bug in the CCDA generation code.
        $xpath = new DOMXPath($updatedDomXMLContent);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        $xpathExpected = new DOMXPath($domXMLExpectedContent);
        $xpathExpected->registerNamespace('hl7', 'urn:hl7-org:v3');

        // need to swap out the 76691-5 Gender Identity root id (only 1)
        // need to swap out the 46098-0 Sex root id (only 1)
        // need to swap out the 76690-7 Sexual Orientation (only 1)
        // need to swap out the 86744-0 Care Team (only 1)
        // need to swap out the 85847-2 Patient Care Team Information section...
        $this->replaceRootIdForXpathQuery("//hl7:observation/hl7:code[@code='76691-5']", $xpath, $xpathExpected);
        $this->replaceRootIdForXpathQuery("//hl7:observation/hl7:code[@code='46098-0']", $xpath, $xpathExpected);
        $this->replaceRootIdForXpathQuery("//hl7:observation/hl7:code[@code='76690-7']", $xpath, $xpathExpected);
        $this->replaceRootIdForXpathQuery("//hl7:section/hl7:entry/hl7:organizer/hl7:code[@code='86744-0']", $xpath, $xpathExpected);
        $this->replaceRootIdForXpathQuery("//hl7:component/hl7:act/hl7:code[@code='85847-2']", $xpath, $xpathExpected);

        return $updatedDomXMLContent;
    }

    private function replaceRootIdForXpathQuery(string $query, DOMXPath $path, DOMXPath $expectedXpath): void
    {
        $currentList1 = $path->query($query);
        $expectedList2 = $expectedXpath->query($query);
        $this->replaceRootIdForNodes($path, $expectedXpath, $currentList1, $expectedList2);
    }

    private function replaceRootIdForNodes(DOMXPath $path, DOMXPath $expectedXpath, \DOMNodeList $currentList1, \DOMNodeList $expectedList2): void
    {
        $count = $currentList1->count();
        if ($currentList1->count() != $expectedList2->count()) {
            throw new \RuntimeException('Node lists have different counts');
        }

        for ($i = 0; $i < $count; $i++) {
            $currentNode = $currentList1->item($i)->parentElement;
            $expectedNode = $expectedList2->item($i)->parentElement;

            $currentNodeId = $path->query(".//hl7:id", $currentNode)->item(0);
            $expectedNodeId = $expectedXpath->query(".//hl7:id", $expectedNode)->item(0);

            $expectedRootId = $expectedNodeId->getAttribute('root');
            $currentNodeId->setAttribute('root', $expectedRootId);
        }
    }

    private function cleanWhitespaceInTextNodes(\DOMDocument $updatedDomXMLContent)
    {
        $xp = new DOMXPath($updatedDomXMLContent);
        $xp->registerNamespace('hl7', 'urn:hl7-org:v3');
        $xp->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');
        $nodes = $xp->query('//hl7:text//text() | //xhtml:td//text() | //hl7:value//text()');

        foreach ($nodes as $text) {
            $text->nodeValue = trim((string) preg_replace('/\s+/u', ' ', (string) $text->nodeValue));
        }

        return $updatedDomXMLContent;
    }
}
