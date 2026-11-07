<?php

/**
 * Isolated tests for the XML and errors ErxPage hands to interface/eRx.php.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Rx\Ensora;

use DOMDocument;
use GuzzleHttp\Client;
use OpenEMR\Rx\Ensora\ErxGlobalsInterface;
use OpenEMR\Rx\Ensora\ErxPage;
use OpenEMR\Rx\Ensora\ErxXmlBuilder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class ErxPageTest extends TestCase
{
    public function testGetXmlReturnsTheDocumentUnedited(): void
    {
        $builder = new ErxXmlBuilder(null, null, new Client());
        $builder->getNCScript()->appendChild($builder->createElementText('sig', 'take "as needed"'));

        $xml = (new ErxPage($builder))->getXML();

        $this->assertSame($builder->getDocument()->saveXML(), $xml);
        $parsed = new DOMDocument();
        $this->assertTrue($parsed->loadXML($xml));
        $this->assertSame('take "as needed"', $parsed->getElementsByTagName('sig')->item(0)?->textContent);
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
        $this->assertSame($expected, (new ErxPage())->setPrescriptionIds($ids)->getPrescriptionIds());
    }

    public function testCheckErrorListsTheValidationErrors(): void
    {
        $page = '<html><textarea rows="4">Error: no NPI Error: no DEA Original XML: <NCScript/></textarea>';

        $this->assertSame([': no NPI ', ': no DEA '], (new ErxPage($this->builderReturning($page)))->checkError('<NCScript/>'));
    }

    public function testCheckErrorIsEmptyWhenThePageHasNoErrors(): void
    {
        $this->assertSame([], (new ErxPage($this->builderReturning('<html>ok</html>')))->checkError('<NCScript/>'));
    }

    public function testAnErrorLogThatCannotBeWrittenStillReturnsTheErrors(): void
    {
        $page = '<form action="RxEntry.aspx"><textarea>Error: no NPI Original XML:';

        $this->assertSame([': no NPI '], (new ErxPage($this->builderReturning($page)))->checkError('<NCScript/>'));
    }

    public function testTheErrorLogRedactsThePassword(): void
    {
        $xml = '<NCScript><password>p&amp;ss&lt;1</password></NCScript>';
        $page = '<form action="RxEntry.aspx"><textarea>Error: bad password Original XML: '
            . '&lt;password&gt;p&amp;amp;ss&amp;lt;1&lt;/password&gt; raw p&ss<1</textarea>';

        $log = $this->errorLogFor('p&ss<1', $xml, $page);

        $this->assertStringNotContainsString('ss<1', $log);
        $this->assertStringNotContainsString('ss&lt;1', $log);
        $this->assertStringNotContainsString('ss&amp;lt;1', $log);
        $this->assertSame(3, substr_count($log, '[redacted]'));
    }

    public function testTheErrorLogRedactsThePasswordAsTheRequestCleanedIt(): void
    {
        $password = 'p' . chr(9) . 'ss<1';
        $builder = new ErxXmlBuilder(null, null, new Client());
        $builder->getNCScript()->appendChild($builder->createElementText('password', $password));
        $xml = (new ErxPage($builder))->getXML();
        $this->assertStringContainsString('pss&lt;1', $xml);

        $log = $this->errorLogFor($password, $xml, '<form action="RxEntry.aspx"><textarea>Error: bad password Original XML:');

        $this->assertStringNotContainsString('pss&lt;1', $log);
        $this->assertSame(1, substr_count($log, '[redacted]'));
    }

    /**
     * The error log checkError() writes for a failed request.
     */
    private function errorLogFor(string $password, string $xml, string $page): string
    {
        $siteDirectory = sys_get_temp_dir() . '/erx-page-test-' . bin2hex(random_bytes(4));
        $globals = $this->createStub(ErxGlobalsInterface::class);
        $globals->method('getOpenEMRSiteDirectory')->willReturn($siteDirectory);
        $globals->method('getCredentials')->willReturn(['partner', 'account', $password]);

        try {
            (new ErxPage($this->builderReturning($page, $globals)))->checkError($xml);
            $log = file_get_contents($siteDirectory . '/documents/erx_error/erx_error-' . date('Y-m-d') . '.log');
        } finally {
            (new Filesystem())->remove($siteDirectory);
        }

        $this->assertIsString($log);

        return $log;
    }

    private function builderReturning(string $page, ?ErxGlobalsInterface $globals = null): ErxXmlBuilder
    {
        $builder = $this->createMock(ErxXmlBuilder::class);
        $builder->method('checkError')->willReturn($page);
        $builder->method('getGlobals')->willReturn($globals ?? $this->createStub(ErxGlobalsInterface::class));

        return $builder;
    }
}
