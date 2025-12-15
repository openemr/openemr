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

class CcdaServiceDocumentRequestorTest extends TestCase {

    const EXAMPLE_DIR = __DIR__ . "/../../../../data/Services/Modules/CareCoordination/Model/CcdaServiceDocumentRequestor/";
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
        $this->assertXmlStringEqualsXmlString($response, $responseCheck, "CCDA response does not match expected output");
    }
}
