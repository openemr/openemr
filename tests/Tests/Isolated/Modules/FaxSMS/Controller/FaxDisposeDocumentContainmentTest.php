<?php

/**
 * Isolated tests for the shared fax document disposal guard
 * (FaxDocumentDisposalTrait::disposeDocument()).
 *
 * disposeDocument() takes a request-supplied file_path and either streams it
 * (action=download) or writes decoded content to it (action=setup). Without
 * containment this is an authenticated arbitrary file read/write. These tests
 * pin the two guarantees the trait must uphold for every fax vendor client:
 *   - the caller must pass the document authorisation gate, and
 *   - the resolved path must stay inside the module's temporary base directory,
 * for both the read (download) and write (setup) branches.
 *
 * The clients are driven through a partial mock: the original constructor is
 * disabled (no OEGlobalsBag/session/crypto/vendor SDK wiring), the ACL gate
 * (authorizeDocumentAccess) and the streaming seam (sendFile) are stubbed, and
 * the request/baseDir/crypto collaborators are injected by reflection. An
 * identity xlt() stub declared in the controller namespace (matching
 * ServiceTypeTest's in-namespace style) keeps the suite free of OpenEMR's
 * gettext/DB bootstrap.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Discover and Change, Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\FaxSMS\Controller {
    // The disposal guard's rejection paths call xlt(); an identity stub declared
    // IN the controller namespace resolves the unqualified call here instead of
    // OpenEMR's DB-backed translation layer, which is absent in the isolated suite.
    if (!function_exists('OpenEMR\Modules\FaxSMS\Controller\xlt')) {
        function xlt(string $s): string
        {
            return $s;
        }
    }
}

namespace {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'OpenEMR\\Modules\\FaxSMS\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }
        $relative = str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        $file = __DIR__
            . '/../../../../../../interface/modules/custom_modules/oe-module-faxsms/src/'
            . $relative;
        if (is_file($file)) {
            require_once $file;
        }
    });
}

namespace OpenEMR\Tests\Isolated\Modules\FaxSMS\Controller {

    use OpenEMR\Common\Crypto\CryptoInterface;
    use OpenEMR\Modules\FaxSMS\Contracts\FaxDocumentDisposalInterface;
    use OpenEMR\Modules\FaxSMS\Controller\EtherFaxActions;
    use OpenEMR\Modules\FaxSMS\Controller\RCFaxClient;
    use OpenEMR\Modules\FaxSMS\Controller\SignalWireClient;
    use PHPUnit\Framework\Attributes\DataProvider;
    use PHPUnit\Framework\MockObject\MockObject;
    use PHPUnit\Framework\TestCase;

    final class FaxDisposeDocumentContainmentTest extends TestCase
    {
        private string $baseDir = '';

        /** @var list<string> Absolute paths created outside baseDir, cleaned up after each test. */
        private array $outsidePaths = [];

        protected function setUp(): void
        {
            $this->baseDir = sys_get_temp_dir() . '/faxdispose_' . uniqid('', true);
            if (!mkdir($this->baseDir, 0700, true) && !is_dir($this->baseDir)) {
                self::fail('Could not create test base directory.');
            }
            $this->baseDir = realpath($this->baseDir) ?: $this->baseDir;
            $this->outsidePaths = [];
        }

        protected function tearDown(): void
        {
            foreach ($this->outsidePaths as $path) {
                if (is_file($path)) {
                    @unlink($path);
                }
            }
            if (is_dir($this->baseDir)) {
                foreach (glob($this->baseDir . '/*') ?: [] as $file) {
                    @unlink($file);
                }
                @rmdir($this->baseDir);
            }
        }

        /**
         * @return array<string, array{class-string}>
         *
         * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
         */
        public static function clientProvider(): array
        {
            return [
                'EtherFax' => [EtherFaxActions::class],
                'RingCentral' => [RCFaxClient::class],
                'SignalWire' => [SignalWireClient::class],
            ];
        }

        /**
         * A download whose path escapes the base directory must be rejected
         * before any bytes are streamed, even when the target is a real,
         * readable file.
         *
         * @param class-string $class
         */
        #[DataProvider('clientProvider')]
        public function testDownloadOutsideBaseDirIsBlockedBeforeStreaming(string $class): void
        {
            $secret = dirname($this->baseDir) . '/secret_' . uniqid('', true) . '.txt';
            file_put_contents($secret, 'top-secret-db-credentials');
            $this->outsidePaths[] = $secret;

            $client = $this->makeClient($class, true);
            // The read must be refused before it ever reaches the streaming seam.
            $client->expects($this->never())->method('sendFile');
            $this->setRequest($client, ['file_path' => $secret, 'action' => 'download']);

            $result = $this->dispose($client);
            self::assertFalse($result['success']);
            // The out-of-bounds file must be untouched (not streamed, not unlinked).
            self::assertFileExists($secret);
        }

        /**
         * A download of an existing file inside the base directory streams
         * normally.
         *
         * @param class-string $class
         */
        #[DataProvider('clientProvider')]
        public function testDownloadInsideBaseDirStreams(string $class): void
        {
            $target = $this->baseDir . '/Fax_1.pdf';
            file_put_contents($target, 'encrypted-bytes');

            $client = $this->makeClient($class, true);
            $client->expects($this->once())->method('sendFile')->with($target);
            $this->setRequest($client, ['file_path' => $target, 'action' => 'download']);

            $result = $this->dispose($client);
            self::assertTrue($result['success']);
        }

        /**
         * A setup write whose path escapes the base directory must be refused
         * and must not create the out-of-bounds file.
         *
         * @param class-string $class
         */
        #[DataProvider('clientProvider')]
        public function testSetupWriteOutsideBaseDirIsRejected(string $class): void
        {
            $target = dirname($this->baseDir) . '/pwned_' . uniqid('', true) . '.pdf';
            $this->outsidePaths[] = $target;

            $client = $this->makeClient($class, true);
            $this->setRequest($client, [
                'file_path' => $target,
                'action' => 'setup',
                'content' => base64_encode('%PDF-1.4 payload'),
            ]);

            $result = $this->dispose($client);
            self::assertFalse($result['success']);
            self::assertFileDoesNotExist($target);
        }

        /**
         * A traversal payload (../ escaping the base directory) must be
         * normalized and rejected, not followed.
         *
         * @param class-string $class
         */
        #[DataProvider('clientProvider')]
        public function testSetupWriteTraversalIsRejected(string $class): void
        {
            $escaped = dirname($this->baseDir) . '/pwned_' . uniqid('', true) . '.pdf';
            $this->outsidePaths[] = $escaped;
            $traversal = $this->baseDir . '/../' . basename($escaped);

            $client = $this->makeClient($class, true);
            $this->setRequest($client, [
                'file_path' => $traversal,
                'action' => 'setup',
                'content' => base64_encode('%PDF-1.4 payload'),
            ]);

            $result = $this->dispose($client);
            self::assertFalse($result['success']);
            self::assertFileDoesNotExist($escaped);
        }

        /**
         * A setup write inside the base directory with an allowed extension
         * writes the (encrypted-at-rest) file.
         *
         * @param class-string $class
         */
        #[DataProvider('clientProvider')]
        public function testSetupWriteInsideBaseDirSucceeds(string $class): void
        {
            $target = $this->baseDir . '/Fax_42.pdf';

            $client = $this->makeClient($class, true);
            $this->setRequest($client, [
                'file_path' => $target,
                'action' => 'setup',
                'content' => base64_encode('hello-fax'),
            ]);

            $result = $this->dispose($client);
            self::assertTrue($result['success']);
            self::assertSame($target, $result['url']);
            self::assertFileExists($target);
            // Identity crypto stub -> on-disk bytes equal the decoded content.
            self::assertSame('hello-fax', file_get_contents($target));
        }

        /**
         * Even inside the base directory, a non-fax extension is refused.
         *
         * @param class-string $class
         */
        #[DataProvider('clientProvider')]
        public function testSetupWriteRejectsDisallowedExtension(string $class): void
        {
            $target = $this->baseDir . '/shell.php';

            $client = $this->makeClient($class, true);
            $this->setRequest($client, [
                'file_path' => $target,
                'action' => 'setup',
                'content' => base64_encode('plain'),
            ]);

            $result = $this->dispose($client);
            self::assertFalse($result['success']);
            self::assertFileDoesNotExist($target);
        }

        /**
         * Executable content is refused even with an allowed extension inside
         * the base directory.
         *
         * @param class-string $class
         */
        #[DataProvider('clientProvider')]
        public function testSetupWriteRejectsExecutableContent(string $class): void
        {
            $target = $this->baseDir . '/Fax_evil.pdf';

            $client = $this->makeClient($class, true);
            $this->setRequest($client, [
                'file_path' => $target,
                'action' => 'setup',
                'content' => base64_encode('<?php system($_GET["c"]); ?>'),
            ]);

            $result = $this->dispose($client);
            self::assertFalse($result['success']);
            self::assertFileDoesNotExist($target);
        }

        /**
         * A caller who fails the document authorisation gate gets nothing, and
         * no filesystem side effect occurs.
         *
         * @param class-string $class
         */
        #[DataProvider('clientProvider')]
        public function testUnauthorizedCallerIsRejectedWithoutFilesystemAccess(string $class): void
        {
            $target = $this->baseDir . '/Fax_99.pdf';

            $client = $this->makeClient($class, false);
            $client->expects($this->never())->method('sendFile');
            $this->setRequest($client, [
                'file_path' => $target,
                'action' => 'setup',
                'content' => base64_encode('hello-fax'),
            ]);

            $result = $this->dispose($client);
            self::assertFalse($result['success']);
            self::assertFileDoesNotExist($target);
        }

        /**
         * Build a fax client partial mock with the ACL gate and streaming seam
         * stubbed, and baseDir/crypto injected.
         *
         * @param class-string $class
         * @return MockObject
         */
        private function makeClient(string $class, bool $authorized): MockObject
        {
            $client = $this->getMockBuilder($class)
                ->disableOriginalConstructor()
                ->onlyMethods(['authorizeDocumentAccess', 'sendFile'])
                ->getMock();
            $client->method('authorizeDocumentAccess')->willReturn($authorized);

            $crypto = $this->createMock(CryptoInterface::class);
            $crypto->method('encryptForFilesystem')->willReturnArgument(0);

            $this->setProp($client, 'baseDir', $this->baseDir);
            $this->setProp($client, 'crypto', $crypto);

            return $client;
        }

        /**
         * @param array<string, string> $request
         */
        private function setRequest(MockObject $client, array $request): void
        {
            $this->setProp($client, '_request', $request);
        }

        /**
         * Narrow the mock to the disposal contract, invoke it, and decode the
         * JSON status payload.
         *
         * @return array<array-key, mixed>
         */
        private function dispose(MockObject $client): array
        {
            self::assertInstanceOf(FaxDocumentDisposalInterface::class, $client);
            $decoded = json_decode($client->disposeDocument(), true);
            self::assertIsArray($decoded);

            return $decoded;
        }

        private function setProp(object $obj, string $name, mixed $value): void
        {
            $class = new \ReflectionClass($obj);
            while (!$class->hasProperty($name)) {
                $parent = $class->getParentClass();
                if ($parent === false) {
                    self::fail("Property {$name} not found on " . $obj::class);
                }
                $class = $parent;
            }
            $prop = new \ReflectionProperty($class->getName(), $name);
            $prop->setValue($obj, $value);
        }
    }
}
