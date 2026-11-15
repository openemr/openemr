<?php

/**
 * Faxing a patient document: the request-supplied document id is authorized,
 * and the send path check doesn't refuse it.
 *
 * Every vendor's sendFax() takes `docid` from the request when `isDocuments`
 * is set. etherFax and SignalWire read that id with no ACL, so any user who
 * could reach the send action could fax any document; RingCentral used
 * AppDispatch::readAuthorizedFaxDocument(), which checked only the coarse
 * patients/docs ACL. etherFax and RingCentral also ran their "file must be
 * under send/" check before looking at `isDocuments`, so every document send
 * was refused with "Invalid file location" (the document's own path is never
 * under send/).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Modules\FaxSMS;

use Composer\Autoload\ClassLoader;
use Document;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\FaxSMS\Contracts\FaxChannelInterface;
use OpenEMR\Modules\FaxSMS\Controller\AppDispatch;
use OpenEMR\Modules\FaxSMS\Controller\EtherFaxActions;
use OpenEMR\Modules\FaxSMS\Controller\RCFaxClient;
use OpenEMR\Modules\FaxSMS\Controller\SignalWireClient;
use OpenEMR\Modules\FaxSMS\EtherFax\EtherFaxClient;
use OpenEMR\Modules\FaxSMS\EtherFax\FaxResult;
use OpenEMR\Modules\FaxSMS\EtherFax\FaxStatus;
use OpenEMR\Modules\FaxSMS\Exception\FaxDocumentAccessException;
use OpenEMR\Modules\FaxSMS\RestClient\SignalWire\Rest\Client as SignalWireRestClient;
use OpenEMR\Modules\FaxSMS\Service\FaxUploadStaging;
use OpenEMR\Services\UserService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use ReflectionMethod;
use ReflectionProperty;
use RingCentral\SDK\Http\MultipartBuilder;
use RingCentral\SDK\Platform\Platform;
use RingCentral\SDK\SDK as RingCentralSdk;

class FaxDocumentSendAuthorizationTest extends TestCase
{
    private const MODULE_DIR = '/interface/modules/custom_modules/oe-module-faxsms';

    /** Fixture patients, high enough not to meet real or demo data. */
    private const PID = 990417;
    private const OTHER_PID = 990418;

    private const CONTENT = "%PDF-1.4\nfax document send test\n%%EOF\n";

    private int $documentId = 0;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $loaders = ClassLoader::getRegisteredLoaders();
        $loader = reset($loaders);
        if (!$loader instanceof ClassLoader) {
            self::fail('Composer ClassLoader not available to register module autoload prefix.');
        }
        $loader->addPsr4('OpenEMR\\Modules\\FaxSMS\\', dirname(__DIR__, 5) . self::MODULE_DIR . '/src/');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanUp();
        foreach ([self::PID, self::OTHER_PID] as $pid) {
            QueryUtils::sqlInsert(
                'INSERT INTO `patient_data` (`pid`, `pubpid`, `fname`, `lname`) VALUES (?, ?, ?, ?)',
                [$pid, 'fax-doc-test-' . $pid, 'Fax', 'Test']
            );
        }
        $this->documentId = $this->createDocument(self::PID);
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
        parent::tearDown();
    }

    public function testAuthorizedDocumentReturnsContentAndName(): void
    {
        $document = $this->read($this->reader(), (string) $this->documentId);

        $this->assertSame(self::CONTENT, $document['content']);
        $this->assertStringStartsWith('fax-doc-test-', $document['name']);
    }

    public function testRealCategoryCheckAllowsAnUncategorizedDocument(): void
    {
        // Only the session and coarse ACL seams are stubbed: can_access() runs
        // for real and grants a document filed in no category.
        $reader = $this->reader(stubCategoryAcl: false);

        $this->assertSame(self::CONTENT, $this->read($reader, $this->documentId)['content']);
    }

    public function testRefusedWithoutDocumentsAcl(): void
    {
        $this->assertRefused($this->reader(docsAcl: false), $this->documentId, 'Not authorized to access documents');
    }

    public function testRefusedWhenACategoryIsNotAllowed(): void
    {
        $this->assertRefused($this->reader(categoryAcl: false), $this->documentId, 'Not authorized to access this document');
    }

    public function testRefusedForAnotherPatientsDocument(): void
    {
        $this->assertRefused($this->reader(activePid: self::OTHER_PID), $this->documentId, 'does not belong to the current patient');
    }

    public function testRefusedWithNoPatientInTheSession(): void
    {
        $this->assertRefused($this->reader(activePid: 0), $this->documentId, 'does not belong to the current patient');
    }

    public function testRefusedForADocumentWithNoPatient(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM `patient_data` WHERE `pid` = ?', [self::PID]);

        $this->assertRefused($this->reader(), $this->documentId, 'not associated with a patient');
    }

    /**
     * @return array<string, array{mixed}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function badIdProvider(): array
    {
        return [
            'zero' => [0],
            'negative' => ['-5'],
            'not a number' => ['12 OR 1=1'],
            'array' => [['1']],
            'missing' => [null],
        ];
    }

    #[DataProvider('badIdProvider')]
    public function testRefusedForAnInvalidId(mixed $docId): void
    {
        $this->assertRefused($this->reader(), $docId, 'Invalid document reference');
    }

    public function testUnreadableDocumentGivesAMessageWithoutThePath(): void
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE `documents` SET `url` = ? WHERE `id` = ?',
            ['file:///nonexistent/fax-doc-test/missing.pdf', $this->documentId]
        );

        try {
            $this->read($this->reader(), $this->documentId);
            $this->fail('An unreadable document must be refused.');
        } catch (FaxDocumentAccessException $e) {
            $this->assertStringContainsString('could not be read', $e->getMessage());
            $this->assertStringNotContainsString('/', $e->getMessage());
            $this->assertNotNull($e->getPrevious());
        }
    }

    /**
     * @return array<string, array{class-string<AppDispatch>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function vendorProvider(): array
    {
        return [
            'etherFax' => [EtherFaxActions::class],
            'RingCentral' => [RCFaxClient::class],
            'SignalWire' => [SignalWireClient::class],
        ];
    }

    /**
     * A document send names the document's storage path in `file`, which is
     * outside send/. It must reach the authorized reader (not be refused by the
     * path check), and a refusal there must end the send with its message.
     *
     * @param class-string<AppDispatch> $class
     */
    #[DataProvider('vendorProvider')]
    public function testDocumentSendGoesThroughTheAuthorizedReader(string $class): void
    {
        $vendor = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->onlyMethods(['authenticate', 'readAuthorizedFaxDocument'])
            ->getMock();
        $vendor->method('authenticate')->willReturn(1);
        $vendor->expects($this->once())
            ->method('readAuthorizedFaxDocument')
            ->with('94810')
            ->willThrowException(new FaxDocumentAccessException('refused by the reader'));
        if ($vendor instanceof RCFaxClient) {
            // RingCentral refuses inside its try, whose finally cleans up uploads.
            $this->initialize($vendor, RCFaxClient::class, 'uploadStaging', FaxUploadStaging::create());
        }
        if ($vendor instanceof SignalWireClient) {
            // Past the "not configured" guard; the refusal must come first.
            (new ReflectionProperty(SignalWireClient::class, 'client'))
                ->setValue($vendor, $this->createMock(SignalWireRestClient::class));
        }
        $this->setRequest($vendor, [
            'isDocuments' => '1',
            'docid' => '94810',
            'file' => 'file://' . __FILE__,
            'phone' => '5551234567',
            // The dialog always posts the optional email copy field.
            'email' => '',
        ]);

        $this->assertInstanceOf(FaxChannelInterface::class, $vendor);
        $this->assertSame('refused by the reader', $vendor->sendFax());
    }

    /**
     * A document kept in CouchDB names no local file, so its `file` value
     * doesn't resolve; SignalWire's path handling must not refuse the send
     * before the document is read by id.
     */
    public function testSignalWireDocumentSendWithNoLocalPathReachesTheReader(): void
    {
        $vendor = $this->getMockBuilder(SignalWireClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['authenticate', 'readAuthorizedFaxDocument'])
            ->getMock();
        $vendor->method('authenticate')->willReturn(1);
        $vendor->expects($this->once())
            ->method('readAuthorizedFaxDocument')
            ->with('94810')
            ->willThrowException(new FaxDocumentAccessException('refused by the reader'));
        (new ReflectionProperty(SignalWireClient::class, 'client'))
            ->setValue($vendor, $this->createMock(SignalWireRestClient::class));
        $this->setRequest($vendor, [
            'isDocuments' => '1',
            'docid' => '94810',
            'file' => '7f3a9c2e1b4d-couchdb-document.pdf',
            'phone' => '5551234567',
            'email' => '',
        ]);

        $this->assertSame('refused by the reader', $vendor->sendFax());
    }

    /**
     * `isContent` says the payload is bytes; it must not exempt an existing
     * server path from the send/ check, or the send reads that file.
     *
     * @param class-string<AppDispatch> $class
     */
    #[DataProvider('vendorProvider')]
    public function testInlineContentFlagDoesNotExemptAServerPath(string $class): void
    {
        $vendor = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->onlyMethods(['authenticate'])
            ->getMock();
        $vendor->method('authenticate')->willReturn(1);
        if ($vendor instanceof RCFaxClient) {
            $this->initialize($vendor, RCFaxClient::class, 'uploadStaging', FaxUploadStaging::create());
        }
        if ($vendor instanceof SignalWireClient) {
            (new ReflectionProperty(SignalWireClient::class, 'client'))
                ->setValue($vendor, $this->createMock(SignalWireRestClient::class));
            $this->initialize($vendor, SignalWireClient::class, 'uploadStaging', FaxUploadStaging::create());
        }
        if ($vendor instanceof EtherFaxActions) {
            $this->initialize($vendor, EtherFaxActions::class, 'uploadStaging', FaxUploadStaging::create());
        }
        $this->setRequest($vendor, [
            'isContent' => '1',
            'file' => __FILE__,
            'phone' => '5551234567',
            'email' => '',
        ]);

        $this->assertInstanceOf(FaxChannelInterface::class, $vendor);
        $this->assertSame('Error: Invalid file location', $vendor->sendFax());
    }

    /**
     * A document send is read by id whatever else the request claims: with
     * `isContent` set as well, the `file` value (which skipped the send/ path
     * check) must not become the fax payload, which the send would otherwise
     * read as a server path.
     */
    public function testRingCentralDocumentSendIgnoresTheContentFlag(): void
    {
        $vendor = $this->getMockBuilder(RCFaxClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['authenticate', 'readAuthorizedFaxDocument'])
            ->getMock();
        $vendor->method('authenticate')->willReturn(1);
        $vendor->expects($this->once())
            ->method('readAuthorizedFaxDocument')
            ->with('94810')
            ->willThrowException(new FaxDocumentAccessException('refused by the reader'));
        $this->initialize($vendor, RCFaxClient::class, 'uploadStaging', FaxUploadStaging::create());
        $this->setRequest($vendor, [
            'isDocuments' => '1',
            'isContent' => '1',
            'docid' => '94810',
            'file' => __FILE__,
            'phone' => '5551234567',
            'email' => '',
        ]);

        $this->assertSame('refused by the reader', $vendor->sendFax());
    }

    /**
     * The payload RingCentral receives is the document's bytes even when those
     * bytes spell an existing server path: a patient document can hold any
     * text, and the send must not read the file it names.
     */
    public function testRingCentralFaxesDocumentBytesThatNameAFile(): void
    {
        $vendor = $this->getMockBuilder(RCFaxClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['authenticate', 'readAuthorizedFaxDocument'])
            ->getMock();
        $vendor->method('authenticate')->willReturn(1);
        $vendor->method('readAuthorizedFaxDocument')
            ->willReturn(['content' => __FILE__, 'name' => 'note.txt']);

        $builder = $this->createMock(MultipartBuilder::class);
        $builder->method('setBody')->willReturnSelf();
        $builder->expects($this->once())
            ->method('add')
            ->with(__FILE__, 'note.txt', ['Content-Type' => 'text/plain'])
            ->willReturnSelf();
        $builder->method('request')->willReturn($this->createMock(RequestInterface::class));
        $sdk = $this->createMock(RingCentralSdk::class);
        $sdk->method('createMultipartBuilder')->willReturn($builder);
        $this->initialize($vendor, RCFaxClient::class, 'rcsdk', $sdk);
        $this->initialize($vendor, RCFaxClient::class, 'platform', $this->createMock(Platform::class));
        $this->initialize($vendor, RCFaxClient::class, 'crypto', ServiceContainer::getCrypto());
        $this->initialize($vendor, RCFaxClient::class, 'uploadStaging', FaxUploadStaging::create());
        $this->setRequest($vendor, [
            'isDocuments' => '1',
            'docid' => '84259',
            'file' => 'file://' . __FILE__,
            'phone' => '5551234567',
            'email' => '',
        ]);

        $this->assertSame('Fax Successfully Sent', $vendor->sendFax());
    }

    /**
     * The email copy of a document send carries the authorized document's
     * bytes, never the request's `file` value, which skipped the send/ path
     * check and could name any readable server file.
     */
    public function testRingCentralEmailsTheDocumentBytesNotTheRequestPath(): void
    {
        $vendor = $this->getMockBuilder(RCFaxClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['authenticate', 'readAuthorizedFaxDocument', 'emailCopy'])
            ->getMock();
        $vendor->method('authenticate')->willReturn(1);
        $vendor->method('readAuthorizedFaxDocument')
            ->willReturn(['content' => self::CONTENT, 'name' => 'letter.pdf']);
        $vendor->expects($this->once())
            ->method('emailCopy')
            ->with('copy@example.test', '', self::CONTENT, $this->anything(), true)
            ->willReturn(null);
        $this->initialize($vendor, RCFaxClient::class, 'crypto', ServiceContainer::getCrypto());
        $this->initialize($vendor, RCFaxClient::class, 'uploadStaging', FaxUploadStaging::create());
        $this->setRequest($vendor, [
            'isDocuments' => '1',
            'docid' => '84259',
            // An existing file the sender has no business emailing.
            'file' => __FILE__,
            'phone' => '5551234567',
            'email' => 'copy@example.test',
        ]);

        $globals = OEGlobalsBag::getInstance();
        $smtpHost = $globals->getString('SMTP_HOST');
        $globals->set('SMTP_HOST', 'smtp.example.test');
        try {
            // The fax request itself fails (no RingCentral platform); the email
            // copy is handed over before that.
            $this->assertStringStartsWith('Error:', (string) $vendor->sendFax());
        } finally {
            $globals->set('SMTP_HOST', $smtpHost);
        }
    }

    public function testEtherFaxSendsTheAuthorizedDocument(): void
    {
        $vendor = $this->getMockBuilder(EtherFaxActions::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['authenticate', 'readAuthorizedFaxDocument'])
            ->getMock();
        $vendor->method('authenticate')->willReturn(1);
        $vendor->method('readAuthorizedFaxDocument')->willReturn(['content' => self::CONTENT, 'name' => 'letter.pdf']);

        $status = new FaxStatus();
        $status->FaxResult = FaxResult::Success;
        $client = $this->createMock(EtherFaxClient::class);
        $client->expects($this->once())
            ->method('etherFaxSend')
            ->with('+15551234567', self::CONTENT, null, $this->anything(), $this->anything(), $this->anything(), true, 'letter.pdf')
            ->willReturn($status);
        $this->initialize($vendor, EtherFaxActions::class, 'client', $client);
        $this->initialize($vendor, EtherFaxActions::class, 'uploadStaging', FaxUploadStaging::create());
        $this->setRequest($vendor, [
            'isDocuments' => '1',
            'docid' => '84259',
            'file' => 'file://' . __FILE__,
            'phone' => '5551234567',
            // The dialog always posts the optional email copy field.
            'email' => '',
        ]);

        $this->assertSame(json_encode(FaxResult::getFaxResult(FaxResult::Success)), $vendor->sendFax());
    }

    /**
     * An EtherFaxActions whose session and ACL answers are fixed; the database
     * lookups and Document loading run for real.
     */
    private function reader(
        int $activePid = self::PID,
        bool $docsAcl = true,
        bool $categoryAcl = true,
        bool $stubCategoryAcl = true,
    ): EtherFaxActions&MockObject {
        $methods = ['activePatientId', 'userHasDocumentsAcl'];
        if ($stubCategoryAcl) {
            $methods[] = 'userCanAccessDocument';
        }
        $reader = $this->getMockBuilder(EtherFaxActions::class)
            ->disableOriginalConstructor()
            ->onlyMethods($methods)
            ->getMock();
        $reader->method('activePatientId')->willReturn($activePid);
        $reader->method('userHasDocumentsAcl')->willReturn($docsAcl);
        if ($stubCategoryAcl) {
            $reader->method('userCanAccessDocument')->willReturn($categoryAcl);
        }
        return $reader;
    }

    /**
     * @return array{content: string, name: string}
     */
    private function read(AppDispatch $reader, mixed $docId): array
    {
        $method = new ReflectionMethod(AppDispatch::class, 'readAuthorizedFaxDocument');
        $result = $method->invoke($reader, $docId);
        $this->assertIsArray($result);
        $this->assertIsString($result['content'] ?? null);
        $this->assertIsString($result['name'] ?? null);
        return ['content' => $result['content'], 'name' => $result['name']];
    }

    private function assertRefused(AppDispatch $reader, mixed $docId, string $reason): void
    {
        try {
            $this->read($reader, $docId);
            $this->fail('The document must be refused: ' . $reason);
        } catch (FaxDocumentAccessException $e) {
            $this->assertStringContainsString($reason, $e->getMessage());
        }
    }

    /**
     * @param array<string, string> $request
     */
    private function setRequest(AppDispatch $vendor, array $request): void
    {
        (new ReflectionProperty(AppDispatch::class, '_request'))->setValue($vendor, $request);
    }

    /**
     * Initialize a readonly property the disabled constructor would have set.
     *
     * @param class-string $declaringClass
     */
    private function initialize(object $object, string $declaringClass, string $property, mixed $value): void
    {
        \Closure::bind(function () use ($property, $value): void {
            $this->{$property} = $value;
        }, $object, $declaringClass)();
    }

    private function createDocument(int $pid): int
    {
        $systemUser = (new UserService())->getSystemUser();
        $this->assertIsArray($systemUser, 'system user must be resolvable for the fixture');
        $document = new Document();
        // createDocument() takes the data by reference.
        $content = self::CONTENT;
        $error = $document->createDocument(
            (string) $pid,
            0,
            'fax-doc-test-' . uniqid() . '.pdf',
            'application/pdf',
            $content,
            '',
            1,
            (int) $systemUser['id']
        );
        $this->assertSame('', $error);
        $id = $document->get_id();
        $this->assertTrue(is_numeric($id), 'fixture document should expose a numeric id');
        return (int) $id;
    }

    private function cleanUp(): void
    {
        if ($this->documentId !== 0) {
            QueryUtils::sqlStatementThrowException('DELETE FROM `categories_to_documents` WHERE `document_id` = ?', [$this->documentId]);
            QueryUtils::sqlStatementThrowException('DELETE FROM `documents` WHERE `id` = ?', [$this->documentId]);
            $this->documentId = 0;
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM `patient_data` WHERE `pid` IN (?, ?)', [self::PID, self::OTHER_PID]);
    }
}
