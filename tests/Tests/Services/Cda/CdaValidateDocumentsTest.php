<?php
/*
 * CdaValidateDocumentsTest.php  Does a smoke test of the CdaValidateDocuments service to make sure the validation is running
 * and reporting errors as expected.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\Cda;

use Monolog\Level;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\Cda\CdaValidateDocuments;
use PHPUnit\Framework\TestCase;

class CdaValidateDocumentsTest extends TestCase {
    const EXAMPLE_DIR = __DIR__ . "/../../data/Services/Modules/CareCoordination/Model/CcdaServiceDocumentRequestor/";

    public function testValidateDocumentWithCcdaTypeWithInvalidDocument(): void
    {
        $ccda = file_get_contents(self::EXAMPLE_DIR . "ccda-example-response1.xml");
        $cdaDocumentValidator = new CdaValidateDocuments();
        $cdaDocumentValidator->setSystemLogger(new SystemLogger(Level::Critical));
        $validationResponse = $cdaDocumentValidator->validateDocument($ccda, 'ccda');
        $this->assertNotEmpty($validationResponse);
        $this->assertArrayHasKey('errorCount', $validationResponse);
        $this->assertEquals(5, $validationResponse['errorCount'], "Expected 5 validation errors for invalid CCDA document.");
        $this->assertArrayHasKey('warningCount', $validationResponse);
        $this->assertEquals(0, $validationResponse['warningCount'], "Expected no validation warnings for invalid CCDA document.");
        $this->assertArrayHasKey('ignoredCount', $validationResponse);
        $this->assertEquals(8, $validationResponse['ignoredCount'], "Expected 8 ignored validation issues for invalid CCDA document.");
        $this->assertArrayHasKey('errors', $validationResponse);
        $this->assertNotEmpty($validationResponse['errors'], "Expected validation errors for invalid CCDA document.");
        $this->assertCount(5, $validationResponse['errors'], "Expected 5 validation errors for invalid CCDA document.");
    }


}
