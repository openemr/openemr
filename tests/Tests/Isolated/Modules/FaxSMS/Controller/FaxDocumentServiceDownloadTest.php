<?php

/**
 * Isolated test for FaxDocumentService::downloadAndStoreFromUrl().
 *
 * This is the shared download-and-store routine used by both the inbound
 * webhook receiver and the polling path (SignalWireClient::getPending()).
 * The method orchestrates: URL whitelist validation, credential guard,
 * media download, patient match, and document storage.
 *
 * The download, the patient lookup, and the document storage all hit the
 * framework (oeHttp, the database, the Document/crypto stack), so the test
 * drives the method through a partial mock: the original constructor is
 * disabled (no OEGlobalsBag/session/crypto/mkdir), and the three collaborator
 * methods are stubbed. The real SignalWireWebhookValidator is used because it
 * is a pure host-whitelist/coercion helper. The fetchMediaBytes() seam stands
 * in for the oeHttp transport, which is exercised by the live smoke test
 * rather than here.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    SignalWire Integration
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\FaxSMS\Controller;

use Composer\Autoload\ClassLoader;
use OpenEMR\Modules\FaxSMS\Controller\FaxDocumentService;
use OpenEMR\Modules\FaxSMS\Exception\FaxDocumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class FaxDocumentServiceDownloadTest extends TestCase
{
    private const SID = 'bdc152fc-3251-4a05-8665-99c79cad6cd7';
    private const FROM = '+19094608319';
    private const PROJECT = '152c7660-e9cb-4d62-a4c9-c74c226f9cd3';
    private const TOKEN = 'PT_secret_token_value';
    private const PDF_BYTES = "%PDF-1.4\nfake-fax-content";
    private const MEDIA_URL =
        'https://files.signalwire.com/6361e299/152c7660/faxes/20260616-bdc152fc.pdf';

    /**
     * @codeCoverageIgnore Fixture wiring; runs before coverage attribution.
     */
    public static function setUpBeforeClass(): void
    {
        $loaders = ClassLoader::getRegisteredLoaders();
        $loader = reset($loaders);
        if (!$loader instanceof ClassLoader) {
            self::fail('Composer ClassLoader not available to register module autoload prefix.');
        }
        $loader->addPsr4(
            'OpenEMR\\Modules\\FaxSMS\\',
            dirname(__DIR__, 6) . '/interface/modules/custom_modules/oe-module-faxsms/src/'
        );
    }

    public function testDownloadsWithBearerForFilesHostMatchesPatientAndStores(): void
    {
        $svc = $this->service();

        // files.signalwire.com -> Bearer auth (useBearer === true).
        $svc->expects($this->once())
            ->method('fetchMediaBytes')
            ->with(self::MEDIA_URL, self::PROJECT, self::TOKEN, true)
            ->willReturn(['status' => 200, 'body' => self::PDF_BYTES, 'contentType' => 'application/pdf']);

        $svc->expects($this->once())
            ->method('findPatientByPhone')
            ->with(self::FROM)
            ->willReturn(42);

        $svc->expects($this->once())
            ->method('storeFaxDocument')
            ->with(self::SID, self::PDF_BYTES, self::FROM, 42, 'application/pdf')
            ->willReturn(['success' => true, 'document_id' => 7, 'media_path' => '/docs/fax_7.pdf', 'patient_id' => 42]);

        $result = $svc->downloadAndStoreFromUrl(self::SID, self::MEDIA_URL, self::FROM, self::PROJECT, self::TOKEN);

        self::assertTrue($result['success']);
        self::assertSame(7, $result['document_id']);
        self::assertSame('/docs/fax_7.pdf', $result['media_path']);
        self::assertSame(42, $result['patient_id']);
    }

    public function testProvidedPatientIdSkipsPhoneMatch(): void
    {
        $svc = $this->service();

        $svc->method('fetchMediaBytes')
            ->willReturn(['status' => 200, 'body' => self::PDF_BYTES, 'contentType' => 'application/pdf']);

        // A caller-supplied patient id must not be overwritten by a phone match.
        $svc->expects($this->never())->method('findPatientByPhone');

        $svc->expects($this->once())
            ->method('storeFaxDocument')
            ->with(self::SID, self::PDF_BYTES, self::FROM, 99, 'application/pdf')
            ->willReturn(['success' => true, 'document_id' => null, 'media_path' => '/unassigned/fax.pdf', 'patient_id' => 99]);

        $result = $svc->downloadAndStoreFromUrl(self::SID, self::MEDIA_URL, self::FROM, self::PROJECT, self::TOKEN, 99);

        self::assertTrue($result['success']);
        self::assertNull($result['document_id']);
        self::assertSame(99, $result['patient_id']);
    }

    public function testInvalidMediaUrlReturnsFailureWithoutDownload(): void
    {
        $svc = $this->service();

        // Real SignalWireWebhookValidator must reject a non-SignalWire host
        // before any download is attempted.
        $svc->expects($this->never())->method('fetchMediaBytes');
        $svc->expects($this->never())->method('storeFaxDocument');

        $result = $svc->downloadAndStoreFromUrl(
            self::SID,
            'https://evil.example.com/steal.pdf',
            self::FROM,
            self::PROJECT,
            self::TOKEN
        );

        self::assertFalse($result['success']);
        self::assertNull($result['media_path']);
    }

    public function testMissingCredentialsReturnsFailureWithoutDownload(): void
    {
        $svc = $this->service();

        $svc->expects($this->never())->method('fetchMediaBytes');
        $svc->expects($this->never())->method('storeFaxDocument');

        $result = $svc->downloadAndStoreFromUrl(self::SID, self::MEDIA_URL, self::FROM, '', '');

        self::assertFalse($result['success']);
    }

    public function testTransportFailureReturnsFailure(): void
    {
        $svc = $this->service();

        $svc->method('fetchMediaBytes')->willReturn(null);
        $svc->expects($this->never())->method('storeFaxDocument');

        $result = $svc->downloadAndStoreFromUrl(self::SID, self::MEDIA_URL, self::FROM, self::PROJECT, self::TOKEN);

        self::assertFalse($result['success']);
    }

    public function testNon200ResponseReturnsFailure(): void
    {
        $svc = $this->service();

        $svc->method('fetchMediaBytes')
            ->willReturn(['status' => 500, 'body' => 'upstream error', 'contentType' => 'text/plain']);
        $svc->expects($this->never())->method('storeFaxDocument');

        $result = $svc->downloadAndStoreFromUrl(self::SID, self::MEDIA_URL, self::FROM, self::PROJECT, self::TOKEN);

        self::assertFalse($result['success']);
    }

    public function testEmptyBodyReturnsFailure(): void
    {
        $svc = $this->service();

        $svc->method('fetchMediaBytes')
            ->willReturn(['status' => 200, 'body' => '', 'contentType' => 'application/pdf']);
        $svc->expects($this->never())->method('storeFaxDocument');

        $result = $svc->downloadAndStoreFromUrl(self::SID, self::MEDIA_URL, self::FROM, self::PROJECT, self::TOKEN);

        self::assertFalse($result['success']);
    }

    public function testStoreFailureReturnsFailure(): void
    {
        $svc = $this->service();

        $svc->method('fetchMediaBytes')
            ->willReturn(['status' => 200, 'body' => self::PDF_BYTES, 'contentType' => 'application/pdf']);
        $svc->method('findPatientByPhone')->willReturn(0);
        $svc->method('storeFaxDocument')
            ->willThrowException(new FaxDocumentException('disk full'));

        $result = $svc->downloadAndStoreFromUrl(self::SID, self::MEDIA_URL, self::FROM, self::PROJECT, self::TOKEN);

        self::assertFalse($result['success']);
    }

    /**
     * Partial mock: real downloadAndStoreFromUrl(), stubbed collaborators, no
     * constructor (so no OEGlobalsBag/session/crypto/filesystem wiring runs).
     *
     * @return FaxDocumentService&MockObject
     */
    private function service(): FaxDocumentService
    {
        return $this->getMockBuilder(FaxDocumentService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchMediaBytes', 'findPatientByPhone', 'storeFaxDocument'])
            ->getMock();
    }
}
