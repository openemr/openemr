<?php

/*
 * CdaValidateDocumentsTest.php  Does a smoke test of the CdaValidateDocuments service to make sure the validation is running
 * and reporting errors as expected.
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\Cda;

use OpenEMR\Services\Cda\CdaValidateDocuments;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CdaValidateDocumentsTest extends TestCase {
    const EXAMPLE_DIR = __DIR__ . "/../../data/Services/Modules/CareCoordination/Model/CcdaServiceDocumentRequestor/";

    /**
     * The generator output for this sparse sample now meets every SHALL rule in
     * Consolidation.sch. If an error returns, the generator has regressed; fix
     * the generator rather than adding an expected count here.
     */
    public function testValidateDocumentWithCcdaTypeWithValidDocument(): void
    {
        $ccda = file_get_contents(self::EXAMPLE_DIR . "ccda-example-response1.xml");
        $this->assertIsString($ccda, 'Example CCDA fixture must be readable');
        $cdaDocumentValidator = new CdaValidateDocuments();
        $validationResponse = $cdaDocumentValidator->validateDocument($ccda, 'ccda');

        $context = $this->describeValidation($validationResponse);
        $this->assertEquals(0, $validationResponse['errorCount'], "Expected the generator output to validate cleanly.\n" . $context);
        $this->assertEquals(0, $validationResponse['ignoredCount'], "Expected no ignored validation issues.\n" . $context);
    }

    public function testValidateDocumentWithCcdaTypeWithInvalidDocument(): void
    {
        $ccda = $this->withoutHeaderTelecoms(self::EXAMPLE_DIR . "ccda-example-response1.xml");
        $cdaDocumentValidator = new CdaValidateDocuments();
        $cdaDocumentValidator->setSystemLogger($this->createMock(LoggerInterface::class));
        $validationResponse = $cdaDocumentValidator->validateDocument($ccda, 'ccda');

        $this->assertNotEmpty($validationResponse);
        $this->assertArrayHasKey('errorCount', $validationResponse);
        $this->assertArrayHasKey('warningCount', $validationResponse);
        $this->assertArrayHasKey('ignoredCount', $validationResponse);
        $this->assertArrayHasKey('errors', $validationResponse);

        // Snapshot of the validator's findings against a deliberately invalid
        // document. The generator output (ccda-example-response1.xml, shared with
        // CcdaGeneratorTest's golden comparison) now validates cleanly; see
        // testValidateDocumentWithCcdaTypeWithValidDocument. To keep exercising
        // error reporting, this test removes the patientRole and header author
        // telecoms from it, so the findings are fixed by the test rather than by
        // whatever the generator emits. describeValidation() dumps the full
        // finding list on any mismatch so drift points straight at the rule.
        //
        // errorCount = 4: patientRole (CONF:1198-5280) and assignedAuthor
        //   (CONF:1198-5428) each missing a required telecom, reported under both
        //   the US Realm Header (2.16.840.1.113883.10.20.22.1.1) and CCD (...1.2)
        //   header patterns: 2 issues x 2 templates = 4 errors.
        //
        // ignoredCount = 0: nothing in Consolidation.sch is now unevaluable. The
        //   prior Node-service snapshot showed 8 ignored because the JS xpath library
        //   could not evaluate `document('voc.xml')/...` value-set predicates and
        //   silently punted; the pure-PHP validator rewrites those against a
        //   precomputed vocab lookup (they all pass on this fixture, so errorCount is
        //   unchanged). The one entry that survived that change was the
        //   R1.1-compatibility meta-rule, whose test references the `$root`
        //   <sch:let> variable; XPathVariableExpander now inlines <sch:let>
        //   definitions, so it evaluates too.
        //
        // warningCount = 0: warnings are opt-in and CdaValidateDocuments does not ask
        //   for them, so only SHALL-level findings are reported. Enabling them would add
        //   201 SHOULD-level findings on this fixture without moving errorCount -- the
        //   flag filters per finding, not per pattern.
        //
        //   A non-zero ignoredCount now means a real regression: an assertion the
        //   validator could not evaluate. Find it in the dump below rather than
        //   raising this number.
        $context = $this->describeValidation($validationResponse);

        $this->assertEquals(4, $validationResponse['errorCount'], "Expected 4 validation errors for invalid CCDA document.\n" . $context);
        $this->assertEquals(0, $validationResponse['warningCount'], "Expected no validation warnings: they are opt-in.\n" . $context);
        $this->assertEquals(0, $validationResponse['ignoredCount'], "Expected no ignored validation issues for invalid CCDA document.\n" . $context);
        $this->assertNotEmpty($validationResponse['errors'], "Expected validation errors for invalid CCDA document.");
        $this->assertCount(4, $validationResponse['errors'], "Expected 4 validation errors for invalid CCDA document.\n" . $context);
    }

    /**
     * Load a CCDA and remove every patientRole and header author telecom, which
     * are SHALL [1..*] in the US Realm Header.
     */
    private function withoutHeaderTelecoms(string $path): string
    {
        $dom = new \DOMDocument();
        $this->assertTrue($dom->load($path), 'Example CCDA fixture must be readable');
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');
        $telecoms = $xpath->query(
            '/hl7:ClinicalDocument/hl7:recordTarget/hl7:patientRole/hl7:telecom'
            . ' | /hl7:ClinicalDocument/hl7:author/hl7:assignedAuthor/hl7:telecom'
        );
        $this->assertNotFalse($telecoms);
        $this->assertGreaterThan(0, $telecoms->length, 'The fixture must carry header telecoms to remove');
        foreach (iterator_to_array($telecoms) as $telecom) {
            if ($telecom instanceof \DOMElement) {
                $telecom->parentNode?->removeChild($telecom);
            }
        }
        $xml = $dom->saveXML();
        $this->assertIsString($xml);
        return $xml;
    }

    /**
     * Render the validation response as a readable block for failure messages:
     * the three counts followed by every entry in the errors / warnings /
     * ignored buckets, so a snapshot mismatch shows exactly which findings moved.
     *
     * @param array<array-key, mixed> $validationResponse
     */
    private function describeValidation(array $validationResponse): string
    {
        $lines = [];

        foreach (['errorCount', 'warningCount', 'ignoredCount'] as $countKey) {
            if (array_key_exists($countKey, $validationResponse)) {
                $lines[] = sprintf('%s = %s', $countKey, $this->stringify($validationResponse[$countKey]));
            }
        }

        foreach (['errors', 'warnings', 'ignored'] as $listKey) {
            $list = $validationResponse[$listKey] ?? null;
            if (!is_array($list) || $list === []) {
                continue;
            }
            $lines[] = strtoupper($listKey) . ':';
            foreach ($list as $i => $entry) {
                $lines[] = sprintf('  [%s] %s', (string)$i, $this->stringify($entry));
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Render a validation finding (scalar or structured) as a single string for
     * failure output. Non-scalars are JSON-encoded. Centralizes the mixed-to-string
     * conversion for the untyped validator response.
     */
    private function stringify(mixed $value): string
    {
        if (is_scalar($value)) {
            return (string)$value;
        }

        return (string)json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }


}
