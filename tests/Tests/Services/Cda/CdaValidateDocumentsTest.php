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

    public function testValidateDocumentWithCcdaTypeWithInvalidDocument(): void
    {
        $ccda = file_get_contents(self::EXAMPLE_DIR . "ccda-example-response1.xml");
        $cdaDocumentValidator = new CdaValidateDocuments();
        $cdaDocumentValidator->setSystemLogger($this->createMock(LoggerInterface::class));
        $validationResponse = $cdaDocumentValidator->validateDocument($ccda, 'ccda');

        $this->assertNotEmpty($validationResponse);
        $this->assertArrayHasKey('errorCount', $validationResponse);
        $this->assertArrayHasKey('warningCount', $validationResponse);
        $this->assertArrayHasKey('ignoredCount', $validationResponse);
        $this->assertArrayHasKey('errors', $validationResponse);

        // Snapshot of the validator's findings against this deliberately imperfect
        // sample. ccda-example-response1.xml is generator output from a sparse input
        // (shared with CcdaGeneratorTest's golden comparison), not a hand-authored
        // valid document, so it is expected to report a stable set of errors. The
        // counts below are calibrated against that output; describeValidation() dumps
        // the full finding list on any mismatch so drift points straight at the rule.
        //
        // errorCount = 6: patientRole (CONF:1198-5280), providerOrganization
        //   (CONF:1198-5420) and assignedAuthor (CONF:1198-5428) each missing a
        //   required telecom, reported under both the US Realm Header
        //   (2.16.840.1.113883.10.20.22.1.1) and CCD (...1.2) header patterns:
        //   3 issues x 2 templates = 6 errors. Expected for this sparse sample;
        //   header telecom is SHALL [1..*].
        //
        //   The prior Goals Section duplicate-templateId finding (CONF:1098-29584)
        //   is resolved: serveccda.js now emits the bare Goals templateId
        //   (2.16.840.1.113883.10.20.22.2.60) only -- it is R2.1-only with no R1.1
        //   predecessor, per the current C-CDA IG / Companion Guide R3 (v3). If that
        //   count returns, the versioned extension="2015-08-01" templateId has crept
        //   back into the generator; fix the generator, do not raise this count.
        //
        // ignoredCount = 8: schematron rules the validator cannot resolve
        //   ("Assertion skipped or malformed") -- voc.xml value-set lookups and the
        //   R1.1-compatibility meta-rule. warningCount = 0.
        $context = $this->describeValidation($validationResponse);

        $this->assertEquals(6, $validationResponse['errorCount'], "Expected 6 validation errors for invalid CCDA document.\n" . $context);
        $this->assertEquals(0, $validationResponse['warningCount'], "Expected no validation warnings for invalid CCDA document.\n" . $context);
        $this->assertEquals(8, $validationResponse['ignoredCount'], "Expected 8 ignored validation issues for invalid CCDA document.\n" . $context);
        $this->assertNotEmpty($validationResponse['errors'], "Expected validation errors for invalid CCDA document.");
        $this->assertCount(6, $validationResponse['errors'], "Expected 6 validation errors for invalid CCDA document.\n" . $context);
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
