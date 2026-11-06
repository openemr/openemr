<?php

/**
 * Isolated tests for the XML and errors eRxPage hands to interface/eRx.php.
 *
 * interface/eRxPage.php and the files it depends on declare nothing but their
 * classes, so they can be required on their own.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\ERx;

use DOMDocument;
use eRxGlobals;
use eRxPage;
use eRxXMLBuilder;
use GuzzleHttp\Client;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ERxPageTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../../../../interface/eRxGlobals.php';
        require_once __DIR__ . '/../../../../interface/eRxStore.php';
        require_once __DIR__ . '/../../../../interface/eRxXMLBuilder.php';
        require_once __DIR__ . '/../../../../interface/eRxPage.php';
    }

    public function testGetXmlReturnsTheDocumentUnedited(): void
    {
        $builder = new eRxXMLBuilder(null, null, new Client());
        $builder->getNCScript()->appendChild($builder->createElementText('sig', 'take "as needed"'));

        $xml = (new eRxPage($builder))->getXML();

        $this->assertSame($builder->getDocument()->saveXML(), $xml);
        $parsed = new DOMDocument();
        $this->assertTrue($parsed->loadXML($xml));
        $this->assertSame("take 'as needed'", $parsed->getElementsByTagName('sig')->item(0)?->textContent);
    }

    /**
     * @return array<string, array{string, list<int>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function prescriptionIdsProvider(): array
    {
        return [
            'none' => ['', []],
            'one' => ['12', [12]],
            'several' => ['12:7', [12, 7]],
            'blanks, zeros and junk dropped' => ['12::0:7:x', [12, 7]],
        ];
    }

    /**
     * @param list<int> $expected
     */
    #[DataProvider('prescriptionIdsProvider')]
    public function testSetPrescriptionIdsSplitsOnColons(string $ids, array $expected): void
    {
        $this->assertSame($expected, (new eRxPage())->setPrescriptionIds($ids)->getPrescriptionIds());
    }

    public function testCheckErrorListsTheValidationErrors(): void
    {
        $page = '<html><textarea rows="4">Error: no NPI Error: no DEA Original XML: <NCScript/></textarea>';

        $this->assertSame([': no NPI ', ': no DEA '], (new eRxPage($this->builderReturning($page)))->checkError('<NCScript/>'));
    }

    public function testCheckErrorIsEmptyWhenThePageHasNoErrors(): void
    {
        $this->assertSame([], (new eRxPage($this->builderReturning('<html>ok</html>')))->checkError('<NCScript/>'));
    }

    private function builderReturning(string $page): eRxXMLBuilder
    {
        $configuration = ['erx_debug_setting' => '0'];
        $builder = $this->createMock(eRxXMLBuilder::class);
        $builder->method('checkError')->willReturn($page);
        $builder->method('getGlobals')->willReturn(new eRxGlobals($configuration));

        return $builder;
    }
}
