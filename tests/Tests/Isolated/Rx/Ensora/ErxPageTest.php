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
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Encryption\Plaintext;
use OpenEMR\Rx\Ensora\ErxGlobalsInterface;
use OpenEMR\Rx\Ensora\ErxPage;
use OpenEMR\Rx\Ensora\ErxStoreInterface;
use OpenEMR\Rx\Ensora\ErxXmlBuilder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;
use UnexpectedValueException;

class ErxPageTest extends TestCase
{
    private bool $hadDisableTranslation = false;
    private mixed $previousDisableTranslation = null;

    protected function setUp(): void
    {
        // xl() returns its input when translation is disabled. OEGlobalsBag
        // writes through to $GLOBALS, so tearDown() restores $GLOBALS itself.
        $this->hadDisableTranslation = array_key_exists('disable_translation', $GLOBALS);
        $this->previousDisableTranslation = $GLOBALS['disable_translation'] ?? null;
        OEGlobalsBag::getInstance()->set('disable_translation', true);
    }

    protected function tearDown(): void
    {
        if ($this->hadDisableTranslation) {
            $GLOBALS['disable_translation'] = $this->previousDisableTranslation;
        } else {
            unset($GLOBALS['disable_translation']);
        }
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function emptyResponseProvider(): array
    {
        return [
            'empty' => [''],
            'whitespace' => [' ' . chr(10)],
            'zero' => ['0'],
        ];
    }

    #[DataProvider('emptyResponseProvider')]
    public function testAnEmptyResponseIsAnError(string $page): void
    {
        $this->assertSame(
            ['Ensora eRx returned an empty response, please contact your systems administrator.'],
            $this->page($this->builderReturning($page))->checkError('<NCScript/>'),
        );
    }

    /**
     * @return array<string, array{Throwable, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function requestFailureProvider(): array
    {
        return [
            'unreachable' => [
                new ConnectException('Connection refused', new Request('POST', 'https://erx.example.test/')),
                'Ensora eRx could not be reached, please contact your systems administrator.',
            ],
            'no path' => [
                new UnexpectedValueException('The Ensora eRx path is not configured'),
                'The Ensora eRx path is not configured, please contact your systems administrator.',
            ],
        ];
    }

    #[DataProvider('requestFailureProvider')]
    public function testARequestThatCannotBeSentIsAnError(Throwable $failure, string $message): void
    {
        $builder = $this->createMock(ErxXmlBuilder::class);
        $builder->method('checkError')->willThrowException($failure);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error');

        $this->assertSame([$message], (new ErxPage($builder, $logger))->checkError('<NCScript/>'));
    }

    public function testGetXmlReturnsTheDocumentUnedited(): void
    {
        $builder = $this->realBuilder();
        $builder->getNCScript()->appendChild($builder->createElementText('sig', 'take "as needed"'));

        $xml = $this->page($builder)->getXML();

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
        $this->assertSame($expected, $this->page($this->builderReturning(''))->setPrescriptionIds($ids)->getPrescriptionIds());
    }

    public function testCheckErrorListsTheValidationErrors(): void
    {
        $page = '<html><textarea rows="4">Error: no NPI Error: no DEA Original XML: <NCScript/></textarea>';

        $this->assertSame([': no NPI ', ': no DEA '], $this->page($this->builderReturning($page))->checkError('<NCScript/>'));
    }

    public function testCheckErrorIsEmptyWhenThePageHasNoErrors(): void
    {
        $this->assertSame([], $this->page($this->builderReturning('<html>ok</html>'))->checkError('<NCScript/>'));
    }

    public function testAnErrorLogThatCannotBeWrittenStillReturnsTheErrors(): void
    {
        $page = '<form action="RxEntry.aspx"><textarea>Error: no NPI Original XML:';

        $this->assertSame([': no NPI '], $this->page($this->builderReturning($page))->checkError('<NCScript/>'));
    }

    public function testTheErrorLogRedactsThePassword(): void
    {
        $xml = '<NCScript><password>p&amp;ss&lt;1</password></NCScript>';
        $page = '<form action="RxEntry.aspx"><textarea>Error: bad password Original XML: &lt;password&gt;p&amp;amp;ss&amp;lt;1&lt;/password&gt; raw p&ss<1</textarea>';

        $log = $this->errorLogFor($xml, $page, 'p&ss<1');

        $this->assertStringNotContainsString('ss<1', $log);
        $this->assertStringNotContainsString('ss&lt;1', $log);
        $this->assertStringNotContainsString('ss&amp;lt;1', $log);
        // The request comes from the builder's redacted copy; the response loses both echoes.
        $this->assertStringContainsString('<password>[redacted]</password>', $log);
        $this->assertSame(3, substr_count($log, '[redacted]'));
    }

    public function testTheErrorLogRedactsAPasswordWithQuotes(): void
    {
        $page = '<form action="RxEntry.aspx"><textarea>Error: bad password Original XML: p&quot;s&#039;1 p&quot;s&#39;1 p&quot;s&apos;1 p&amp;quot;s&amp;#039;1 p"s\'1</textarea>';

        $log = $this->errorLogFor('<NCScript/>', $page, 'p"s\'1');

        $this->assertStringNotContainsString('s&#', $log);
        $this->assertStringNotContainsString('s&apos;', $log);
        $this->assertStringNotContainsString('s&amp;#', $log);
        $this->assertStringNotContainsString('"s\'', $log);
        $this->assertSame(6, substr_count($log, '[redacted]'));
    }

    /**
     * The error log checkError() writes for a failed request.
     */
    private function errorLogFor(string $xml, string $page, string $password): string
    {
        $siteDirectory = sys_get_temp_dir() . '/erx-page-test-' . bin2hex(random_bytes(4));
        $globals = $this->createStub(ErxGlobalsInterface::class);
        $globals->method('getOpenEMRSiteDirectory')->willReturn($siteDirectory);

        $builder = $this->builderReturning($page, $globals);
        $builder->method('getSentPassword')->willReturn(new Plaintext($password));
        $builder->method('getRedactedXml')->willReturn('<NCScript><password>[redacted]</password></NCScript>');

        try {
            $this->page($builder)->checkError($xml);
            $log = (new Filesystem())->readFile($siteDirectory . '/documents/erx_error/erx_error-' . date('Y-m-d') . '.log');
        } finally {
            (new Filesystem())->remove($siteDirectory);
        }

        return $log;
    }

    private function page(ErxXmlBuilder $builder): ErxPage
    {
        return new ErxPage($builder, new NullLogger());
    }

    private function realBuilder(): ErxXmlBuilder
    {
        return new ErxXmlBuilder($this->createStub(ErxGlobalsInterface::class), $this->createStub(ErxStoreInterface::class), new Client(), true);
    }

    private function builderReturning(string $page, ?ErxGlobalsInterface $globals = null): ErxXmlBuilder&MockObject
    {
        $builder = $this->createMock(ErxXmlBuilder::class);
        $builder->method('checkError')->willReturn($page);
        $builder->method('getGlobals')->willReturn($globals ?? $this->createStub(ErxGlobalsInterface::class));

        return $builder;
    }
}
