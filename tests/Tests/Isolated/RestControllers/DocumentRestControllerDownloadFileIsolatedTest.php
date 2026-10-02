<?php

/**
 * DocumentRestControllerDownloadFileIsolatedTest
 *
 * The REST document download endpoint must not feed the stored document
 * body -- which is client-supplied bytes -- into
 * Symfony\Component\HttpFoundation\BinaryFileResponse.
 * BinaryFileResponse's first constructor argument is interpreted as a
 * filesystem path, so a document whose body happens to look like an
 * absolute path (e.g. `/var/www/localhost/htdocs/openemr/sites/default/sqlconf.php`)
 * would cause Symfony to stream the local file at that path rather than
 * the bytes the uploader stored.
 *
 * The response uses Symfony\Component\HttpFoundation\StreamedResponse
 * with a callback that emits the stored bytes verbatim -- the response class
 * never touches the filesystem. These tests exercise the bytes-are-bytes
 * invariant, the metadata-not-body header derivation, and the empty-result
 * path.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestControllers;

use OpenEMR\RestControllers\DocumentRestController;
use OpenEMR\Services\DocumentService;
use OpenEMR\Services\PatientService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Group('isolated')]
#[Group('security')]
class DocumentRestControllerDownloadFileIsolatedTest extends TestCase
{
    private DocumentService&MockObject $documentService;
    private PatientService&MockObject $patientService;
    private DocumentRestController $controller;

    public static function setUpBeforeClass(): void
    {
        // Prevent sql.inc.php from opening a real DB connection when it is
        // eventually loaded by the require chain below.
        if (!defined('OPENEMR_STATIC_ANALYSIS')) {
            define('OPENEMR_STATIC_ANALYSIS', true);
        }

        // DocumentService.php `require_once`s controllers/C_Document.class.php
        // at file load time, which pulls in library/patient.inc.php, which
        // unconditionally runs `new FacilityService()` at top-level. That
        // constructor calls QueryUtils::listTableFields() and dies without a
        // DB. This cascade would fire on the FIRST call to createMock() in
        // setUp() and blow up that one test even though the mock never
        // actually needs the underlying DB.
        //
        // Prime the load here inside a try/catch: PHP finishes parsing every
        // class in the require chain before the FacilityService constructor
        // runs and throws, so the class definitions we need for createMock()
        // are all installed by the time we swallow the error. Every real
        // method that DocumentService would call is stubbed via ->method(),
        // so the half-completed patient.inc.php state does not matter.
        //
        // Suppress the load-time PHP notice/warning ("Trying to access array
        // offset on null" from QueryUtils::getDatabase() reading the missing
        // adodb bag entry) that fires before the Throwable is raised -- it
        // is a diagnostic-only side effect of the load path we already know
        // is not going to complete.
        $previous = error_reporting(0);
        try {
            class_exists(DocumentService::class);
            class_exists(PatientService::class);
        } catch (\Error $expectedLoadCascadeError) {
            // Expected on load: `new FacilityService()` at patient.inc.php:37
            // hits QueryUtils on a null DB and raises an Error ("Call to a
            // member function ExecuteNoLog() on null"). The DocumentService
            // and PatientService class definitions are already installed by
            // the time PHP evaluates the FacilityService constructor, so we
            // observe the expected message, then wrap-and-rethrow anything
            // else so a genuine setup failure still propagates. The
            // ending-throw form is deliberate: the ForbiddenCatchType rule
            // exempts catch blocks whose last statement is an unconditional
            // throw. See tests/PHPStan/Rules/ForbiddenCatchTypeRule.php.
            if (str_contains($expectedLoadCascadeError->getMessage(), 'ExecuteNoLog')) {
                error_reporting($previous);
                return;
            }
            throw $expectedLoadCascadeError;
        } finally {
            error_reporting($previous);
        }
    }

    protected function setUp(): void
    {
        $this->documentService = $this->createMock(DocumentService::class);
        $this->patientService = $this->createMock(PatientService::class);
        $this->controller = new DocumentRestController(
            $this->documentService,
            $this->patientService,
        );
    }

    // -------------------------------------------------------------------
    // Invariant: bytes-in-storage are bytes-in-response.
    // -------------------------------------------------------------------

    public function testDownloadFileReturnsStreamedResponseNotBinaryFileResponse(): void
    {
        // Sentinel: if a future refactor reintroduces BinaryFileResponse (or
        // any Response class whose constructor interprets its first argument
        // as a filesystem path), this assertion fires. The class-name
        // mention here keeps BinaryFileResponse surfaced in a repo-wide grep
        // so a partial-migration change trips this sentinel first.
        $this->stubValidPid('1');
        $this->documentService->method('getFile')->willReturn([
            'filename' => 'note.txt',
            'mimetype' => 'text/plain',
            'file' => 'hello world',
        ]);

        $response = $this->downloadFileAsStream('1', '42');

        $this->assertSame(
            StreamedResponse::class,
            $response::class,
            'downloadFile() must not return a BinaryFileResponse',
        );
    }

    /**
     * A stored document whose body is a path to a local file must emit
     * those literal bytes, NOT the contents of the referenced file.
     *
     * @param string $storedBody Body persisted by the upload endpoint.
     */
    #[DataProvider('pathTraversalBodyProvider')]
    public function testDownloadFileStreamsStoredBytesEvenWhenBodyLooksLikeAFilesystemPath(string $storedBody): void
    {
        $this->stubValidPid('1');
        $this->documentService->method('getFile')->willReturn([
            'filename' => 'note.txt',
            'mimetype' => 'text/plain',
            'file' => $storedBody,
        ]);

        $response = $this->downloadFileAsStream('1', '42');

        $this->assertSame($storedBody, $this->captureStreamedBody($response));
        // Content-Length must reflect the stored bytes, not any file the
        // path might resolve to on disk. If the response ever changed
        // to reading a filesystem path, the length would drift from the
        // stored bytes' length.
        $this->assertSame(
            (string) strlen($storedBody),
            $response->headers->get('Content-Length'),
        );
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function pathTraversalBodyProvider(): array
    {
        return [
            'body is absolute path to sqlconf' => [
                '/var/www/localhost/htdocs/openemr/sites/default/sqlconf.php',
            ],
            'body is absolute path to /etc/passwd' => [
                '/etc/passwd',
            ],
            'body is dotdot traversal' => [
                '../../../../etc/passwd',
            ],
            'body is bytes that happen to contain a path' => [
                "Contact admin -- see /etc/passwd for details",
            ],
            'body is empty string (short-circuit safety)' => [
                '',
            ],
        ];
    }

    // -------------------------------------------------------------------
    // Metadata-not-body: headers come from the stored row, not the body.
    // -------------------------------------------------------------------

    public function testContentTypeComesFromStoredMimeTypeNotDocumentBody(): void
    {
        $this->stubValidPid('1');
        $this->documentService->method('getFile')->willReturn([
            'filename' => 'note.txt',
            'mimetype' => 'text/plain',
            'file' => "<?php echo 'caller-supplied body'; ?>",
        ]);

        $response = $this->downloadFileAsStream('1', '42');

        $this->assertSame('text/plain', $response->headers->get('Content-Type'));
    }

    public function testContentTypeFallsBackToOctetStreamWhenStoredMimeMissing(): void
    {
        $this->stubValidPid('1');
        $this->documentService->method('getFile')->willReturn([
            'filename' => 'note.txt',
            'mimetype' => '',
            'file' => 'body',
        ]);

        $response = $this->downloadFileAsStream('1', '42');

        $this->assertSame('application/octet-stream', $response->headers->get('Content-Type'));
    }

    public function testContentDispositionUsesBasenameOfStoredFilename(): void
    {
        // Even if a stored filename ever contained path separators (which
        // shouldn't happen for uploads through insertAtPath, but is worth
        // additionally checking), the Content-Disposition header must
        // reduce to a bare basename so downstream browsers/proxies do not
        // treat the header as a path.
        $this->stubValidPid('1');
        $this->documentService->method('getFile')->willReturn([
            'filename' => '/etc/passwd',
            'mimetype' => 'text/plain',
            'file' => 'body',
        ]);

        $response = $this->downloadFileAsStream('1', '42');

        $disposition = $response->headers->get('Content-Disposition') ?? '';
        $this->assertStringContainsString('attachment', $disposition);
        $this->assertStringContainsString('passwd', $disposition);
        $this->assertStringNotContainsString('/etc/', $disposition);
    }

    public function testContentDispositionFallsBackWhenStoredFilenameIsUnsafe(): void
    {
        $this->stubValidPid('1');
        $this->documentService->method('getFile')->willReturn([
            'filename' => '..',
            'mimetype' => 'text/plain',
            'file' => 'body',
        ]);

        $response = $this->downloadFileAsStream('1', '42');

        $disposition = $response->headers->get('Content-Disposition') ?? '';
        // basename('..') === '..' which we replace with 'document'; the
        // header must not contain the raw '..' component.
        $this->assertStringContainsString('document', $disposition);
        $this->assertStringNotContainsString('..', $disposition);
    }

    // -------------------------------------------------------------------
    // Regression: legit happy-path still works end to end.
    // -------------------------------------------------------------------

    public function testAuthorizedDownloadEmitsStoredBytesWithExpectedHeaders(): void
    {
        $this->stubValidPid('7');
        $this->documentService->method('getFile')->with('7', '99')->willReturn([
            'filename' => 'lab-report.pdf',
            'mimetype' => 'application/pdf',
            'file' => "%PDF-1.4 fake pdf payload",
        ]);

        $response = $this->downloadFileAsStream('7', '99');

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertSame(
            (string) strlen("%PDF-1.4 fake pdf payload"),
            $response->headers->get('Content-Length'),
        );
        $disposition = $response->headers->get('Content-Disposition') ?? '';
        $this->assertStringContainsString('attachment', $disposition);
        $this->assertStringContainsString('lab-report.pdf', $disposition);
        $this->assertSame("%PDF-1.4 fake pdf payload", $this->captureStreamedBody($response));
    }

    public function testCacheHeadersAreSetSoBrowserDoesNotRetainPhi(): void
    {
        // PHI-download responses need to be non-cacheable so a shared
        // browser cannot expose a previous patient's document.
        $this->stubValidPid('1');
        $this->documentService->method('getFile')->willReturn([
            'filename' => 'note.txt',
            'mimetype' => 'text/plain',
            'file' => 'body',
        ]);

        $response = $this->downloadFileAsStream('1', '42');

        $cacheControl = $response->headers->get('Cache-Control') ?? '';
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('must-revalidate', $cacheControl);
        // Expires is set an hour in the past for legacy caches.
        $expires = $response->headers->get('Expires');
        $this->assertIsString($expires);
        $expiresTimestamp = strtotime($expires);
        $this->assertNotFalse($expiresTimestamp);
        $this->assertLessThan(time(), $expiresTimestamp);
    }

    // -------------------------------------------------------------------
    // Boundary paths -- kept green as guards.
    // -------------------------------------------------------------------

    public function testInvalidPidReturnsBadRequestBeforeTouchingDocumentService(): void
    {
        $this->patientService->method('getUuid')->willReturn(false);
        $this->documentService->expects($this->never())->method('getFile');

        $response = $this->downloadFileAsResponse('99999', '42');

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testNonScalarPidReturnsBadRequest(): void
    {
        $this->documentService->expects($this->never())->method('getFile');

        $response = $this->downloadFileAsResponse(['bad'], '42');

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testEmptyServiceResultReturnsPlainBadRequest(): void
    {
        // Pre-existing (annotated TODO) contract: an empty result maps to
        // 400 rather than 404. Locking this so a future ACL change does
        // not silently alter the status code. StreamedResponse extends
        // Response, so exclude it explicitly to confirm the empty path
        // returns the plain 400 rather than an empty stream.
        $this->stubValidPid('1');
        $this->documentService->method('getFile')->willReturn(false);

        $response = $this->downloadFileAsResponse('1', '42');

        $this->assertNotInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    // -------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------

    private function stubValidPid(string $pid): void
    {
        $this->patientService
            ->method('getUuid')
            ->with($pid)
            ->willReturn('11111111-1111-1111-1111-111111111111');
    }

    /**
     * Narrow the controller's untyped return to StreamedResponse for tests
     * that exercise the successful download path. Assertion + narrowing
     * happens once here so per-test bodies can use header accessors without
     * PHPStan flagging them as calls on mixed.
     */
    private function downloadFileAsStream(mixed $pid, mixed $did): StreamedResponse
    {
        $response = $this->controller->downloadFile($pid, $did);
        $this->assertInstanceOf(StreamedResponse::class, $response);

        return $response;
    }

    /**
     * Narrow the controller's untyped return to the base Response for the
     * bad-request / empty-service-result tests. Also used to keep PHPStan
     * happy about calls to getStatusCode() on a mixed return.
     */
    private function downloadFileAsResponse(mixed $pid, mixed $did): Response
    {
        $response = $this->controller->downloadFile($pid, $did);
        $this->assertInstanceOf(Response::class, $response);

        return $response;
    }

    /**
     * Capture the body emitted by StreamedResponse::sendContent() without
     * writing to STDOUT. StreamedResponse only invokes its callback when
     * sendContent() is called; the callback echoes into the current output
     * buffer, so we start a buffer to intercept it.
     */
    private function captureStreamedBody(StreamedResponse $response): string
    {
        ob_start();
        try {
            $response->sendContent();
            $body = ob_get_contents();
        } finally {
            ob_end_clean();
        }

        return $body === false ? '' : $body;
    }
}
