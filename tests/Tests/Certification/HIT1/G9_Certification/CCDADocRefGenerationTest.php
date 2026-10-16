<?php

/*
 * CCDADocRefGenerationTest.php
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Certification\HIT1\G9_Certification;

use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMNameSpaceNode;
use DOMNode;
use DOMText;
use DOMXPath;
use OpenEMR\Tests\Api\ApiTestClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class CCDADocRefGenerationTest extends TestCase
{
    // due to the code sets used in the CCDA generation we have to use a specific expected output file that's stored
    // in a private location not accessible to the public repo
    const EXPECTED_RESPONSE_CCDA = __DIR__ . '/../../../../../ci/inferno/inferno-files/files/data/ccda/CCDA_Newman_Alice_expected.xml';

    const DEFAULT_OPENEMR_BASE_URL_API = 'http://openemr';
    const PATIENT_ID_PRIMARY = '96506861-511f-4f6d-bc97-b65a78cf1995';
    private static ApiTestClient $testClient;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: self::DEFAULT_OPENEMR_BASE_URL_API;
        self::$testClient = new ApiTestClient($baseUrl, false);
        // for now this uses the admin user to authenticate
        // TODO: @adunsulag need to implement this using a test practitioner user so we can test the inferno single patient API from a regular provider
        self::$testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    public function testGenerateCCDADocRef(): void
    {
        if (!file_exists(self::EXPECTED_RESPONSE_CCDA)) {
            $this->markTestSkipped('Expected CCDA response file not found. Running test w/o private repo so Skipping test.');
        }
        $ccdaContent = $this->generateCcda();
        // a second document shows which ids the generator creates at random on each run
        $secondCcdaContent = $this->generateCcda();
        // now we will do a strict comparison check on the ccda generated document to ensure it matches expected output
        // eventually we'd want a deeper validation of the CCDA structure but for now this will do to verify generation and retrieval
        $expectedCcdaContent = file_get_contents(self::EXPECTED_RESPONSE_CCDA);
        $this->assertIsString($expectedCcdaContent);
        $generated = $this->getDomXml($ccdaContent);
        $expected = $this->getDomXml($expectedCcdaContent);
        $this->cleanWhitespaceInTextNodes($expected);
        $this->replaceLatestTimeStamp(
            $generated,
            $this->getDocumentGenerationTime($generated),
            $this->getDocumentGenerationTime($expected)
        );
        $this->replaceGeneratedIds($generated, $this->getDomXml($secondCcdaContent), $expected);
        // updating the root nodes and timestamps is cleaning up whitespace in the updated DOMDocument
        // these unfortunately are causing issues with the comparison
        $this->cleanWhitespaceInTextNodes($generated);
        $expectedXml = $expected->C14N();
        $generatedXml = $generated->C14N();
        $this->assertIsString($expectedXml);
        $this->assertIsString($generatedXml);
        $this->assertXmlStringEqualsXmlString($expectedXml, $generatedXml, "The generated CCDA document does not match the expected content.");
    }

    private function generateCcda(): string
    {
        // TODO: @snielson we would rather use post with parameters but the current implementation uses get parameters for the patient
        $response = self::$testClient->post("/apis/default/fhir/DocumentReference/\$docref?patient=" . self::PATIENT_ID_PRIMARY, []);

        $result = $response->getBody()->getContents();
        $this->assertNotEmpty($result);
        $data = json_decode($result, true);
        $this->assertIsArray($data);

        $this->assertArrayHasKey('entry', $data, "Expected 'entry' key in the operation response");
        $entry = $data['entry'];
        $this->assertIsArray($entry);
        $this->assertNotEmpty($entry, "Expected at least one entry in the operation bundle response");
        $firstEntry = $entry[0] ?? null;
        $this->assertIsArray($firstEntry);
        $this->assertArrayHasKey("resource", $firstEntry, "Expected 'resource' key in the first entry of the operation response");
        $docReferenceResource = $firstEntry['resource'];
        $this->assertIsArray($docReferenceResource);
        $this->assertArrayHasKey('content', $docReferenceResource, "Expected 'content' key in the DocumentReference resource");
        $contentArray = $docReferenceResource['content'];
        $this->assertIsArray($contentArray);
        $this->assertNotEmpty($contentArray, "Expected at least one content item in the DocumentReference resource");
        $firstContent = $contentArray[0] ?? null;
        $this->assertIsArray($firstContent);
        $this->assertArrayHasKey('attachment', $firstContent, "Expected 'attachment' key in the first content item of the DocumentReference resource");
        $attachment = $firstContent['attachment'];
        $this->assertIsArray($attachment);
        $this->assertArrayHasKey('url', $attachment, "Expected 'url' key in the attachment of the DocumentReference resource");
        $url = $attachment['url'];
        $this->assertIsString($url);
        $this->assertStringStartsWith("/fhir/Binary/", $url, "Expected attachment URL to start with '/fhir/Binary/'");

        $ccdaResponse = self::$testClient->get("/apis/default" . $url);
        $this->assertEquals(Response::HTTP_OK, $ccdaResponse->getStatusCode(), "Expected 200 OK response when retrieving CCDA Binary resource");
        $ccdaContent = $ccdaResponse->getBody()->getContents();
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $ccdaContent, "Expected CCDA content to contain XML declaration");
        return $ccdaContent;
    }

    private function getDomXml(string $xmlContent): DOMDocument
    {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        if (!$dom->loadXML($xmlContent, LIBXML_NOBLANKS)) {
            throw new \RuntimeException('Invalid XML');
        }
        return $dom;
    }

    private function getDocumentGenerationTime(DOMDocument $xml): string
    {
        $xpath = new DOMXPath($xml);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');
        $effectiveTime = $this->queryFirst($xpath, '//hl7:effectiveTime');
        if (!$effectiveTime instanceof DOMElement || !$effectiveTime->hasAttribute('value')) {
            throw new \RuntimeException('effectiveTime element with value attribute not found in XML');
        }
        return $effectiveTime->getAttribute('value');
    }

    private function replaceLatestTimeStamp(DOMDocument $xml, string $currentTimestamp, string $newTimeStamp): void
    {
        $xpath = new DOMXPath($xml);
        $timestampValues = $xpath->query('//*[@value="' . $currentTimestamp . '"]');
        $this->assertNotFalse($timestampValues);
        foreach ($timestampValues as $timestamp) {
            if ($timestamp instanceof DOMElement) {
                $timestamp->setAttribute('value', $newTimeStamp);
            }
        }
    }

    /**
     * The generator gives some ids (the document id and setId, care team and
     * status entries) a new random UUID on every run. Any id attribute that
     * differs between two generations gets the expected document's value at
     * the same position, so only those random ids are ignored.
     */
    private function replaceGeneratedIds(DOMDocument $current, DOMDocument $secondRun, DOMDocument $expected): void
    {
        $currentXpath = new DOMXPath($current);
        $secondRunXpath = new DOMXPath($secondRun);
        $expectedXpath = new DOMXPath($expected);
        $attributes = $currentXpath->query('//@root | //@extension');
        $this->assertNotFalse($attributes);
        foreach ($attributes as $attribute) {
            if (!$attribute instanceof DOMAttr) {
                continue;
            }
            $path = $attribute->getNodePath();
            if ($path === null) {
                continue;
            }
            $secondRunAttribute = $this->queryFirst($secondRunXpath, $path);
            if (!$secondRunAttribute instanceof DOMAttr || $secondRunAttribute->value === $attribute->value) {
                continue;
            }
            $expectedAttribute = $this->queryFirst($expectedXpath, $path);
            if ($expectedAttribute instanceof DOMAttr) {
                $attribute->value = $expectedAttribute->value;
            }
        }
    }

    private function queryFirst(DOMXPath $xpath, string $expression): DOMNode|DOMNameSpaceNode|null
    {
        $nodes = $xpath->query($expression);
        return $nodes === false ? null : $nodes->item(0);
    }

    private function cleanWhitespaceInTextNodes(DOMDocument $xml): void
    {
        $xp = new DOMXPath($xml);
        $xp->registerNamespace('hl7', 'urn:hl7-org:v3');
        $xp->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');
        $nodes = $xp->query('//hl7:text//text() | //xhtml:td//text() | //hl7:value//text()');
        $this->assertNotFalse($nodes);

        foreach ($nodes as $text) {
            if ($text instanceof DOMText) {
                $text->data = trim(preg_replace('/\s+/u', ' ', $text->data) ?? $text->data);
            }
        }
    }
}
