<?php

/**
 * Isolated tests for storing received Direct message parts as documents.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\DirectMessaging;

use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\DirectMessaging\LegacyPhiMailDocumentStore;
use OpenEMR\Common\DirectMessaging\PhiMailDocumentException;
use OpenEMR\Common\DirectMessaging\PhiMailStoredDocument;
use OpenEMR\Events\Core\Sanitize\IsAcceptedFileFilterEvent;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Filesystem\Filesystem;

final class LegacyPhiMailDocumentStoreTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        $filesystem = new Filesystem();
        $this->path = $filesystem->tempnam(sys_get_temp_dir(), 'phimail-store-test-');
        $filesystem->dumpFile($this->path, 'hello');
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->path);
    }

    public function testStoresTheFileAndAcceptsAnyFileTypeMeanwhile(): void
    {
        $dispatcher = new EventDispatcher();
        $calls = [];
        $store = new LegacyPhiMailDocumentStore(
            $dispatcher,
            function (string $name, string $mimeType, string $path, int $size, int $ownerId) use ($dispatcher, &$calls): array {
                $rejected = (new IsAcceptedFileFilterEvent($path))->setAllowedFile(false);
                $event = $dispatcher->dispatch($rejected, IsAcceptedFileFilterEvent::EVENT_FILTER_IS_ACCEPTED_FILE);
                $calls[] = [$name, $mimeType, $path, $size, $ownerId, $event->isAllowedFile()];
                return ['doc_id' => '12', 'url' => 'file:///documents/direct/dm-report.xml', 'name' => $name];
            },
        );

        $document = $store->store('dm-report.xml', 'text/xml', $this->path, 7);

        self::assertEquals(new PhiMailStoredDocument(12, 'file:///documents/direct/dm-report.xml', 5), $document);
        self::assertSame('dm-report.xml', $document?->fileName());
        self::assertSame([['dm-report.xml', 'text/xml', $this->path, 5, 7, true]], $calls);
        self::assertFalse($dispatcher->hasListeners(IsAcceptedFileFilterEvent::EVENT_FILTER_IS_ACCEPTED_FILE), 'the bypass outlived the store');
    }

    public function testStoresWithoutAKernel(): void
    {
        $store = new LegacyPhiMailDocumentStore(null, static fn(): array => ['doc_id' => 12, 'url' => 'file:///x/y']);

        self::assertEquals(new PhiMailStoredDocument(12, 'file:///x/y', 5), $store->store('dm-x', 'text/plain', $this->path, 7));
    }

    public function testAllowAnyFileTypeAcceptsARejectedFile(): void
    {
        $event = (new IsAcceptedFileFilterEvent($this->path))->setAllowedFile(false);

        $store = new LegacyPhiMailDocumentStore(null, static fn(): bool => false);

        self::assertTrue($store->allowAnyFileType($event)->isAllowedFile());
    }

    /**
     * @return array<string, array{mixed, ?PhiMailStoredDocument}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function resultProvider(): array
    {
        return [
            'upload failed' => [false, null],
            'no document ID' => [['url' => 'file:///x/y'], null],
            'non-numeric document ID' => [['doc_id' => 'abc', 'url' => 'file:///x/y'], null],
            'no URL' => [['doc_id' => 12], new PhiMailStoredDocument(12, null, 5)],
        ];
    }

    #[DataProvider('resultProvider')]
    public function testInterpretsTheResult(mixed $result, ?PhiMailStoredDocument $expected): void
    {
        $store = new LegacyPhiMailDocumentStore(null, static fn(): mixed => $result);

        self::assertEquals($expected, $store->store('dm-x', 'text/plain', $this->path, 7));
    }

    public function testStoreErrorIsWrappedAndTheBypassRemoved(): void
    {
        $dispatcher = new EventDispatcher();
        $failure = new SqlQueryException(sqlStatement: 'INSERT', message: 'database is down');
        $store = new LegacyPhiMailDocumentStore($dispatcher, static fn(): never => throw $failure);

        try {
            $store->store('dm-x', 'text/plain', $this->path, 7);
            self::fail('store() hid a failed upload.');
        } catch (PhiMailDocumentException $e) {
            self::assertSame($failure, $e->getPrevious());
        }

        self::assertFalse($dispatcher->hasListeners(IsAcceptedFileFilterEvent::EVENT_FILTER_IS_ACCEPTED_FILE));
    }
}
