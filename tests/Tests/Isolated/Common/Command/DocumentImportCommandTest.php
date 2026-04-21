<?php

/**
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Command;

use OpenEMR\Common\Command\DocumentImportCommand;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[Group('isolated')]
#[Group('document-import')]
class DocumentImportCommandTest extends TestCase
{
    /**
     * @codeCoverageIgnore PHPUnit lifecycle hook; bootstrap only.
     */
    public static function setUpBeforeClass(): void
    {
        $helpers = realpath(__DIR__ . '/../../../../../library/htmlspecialchars.inc.php');
        if ($helpers !== false && !function_exists('xlt')) {
            require_once $helpers;
        }
        // xl() reaches into the database via sqlStatementNoLog to look up
        // translations. Short-circuit it for isolated tests.
        $GLOBALS['disable_translation'] = true;
    }

    private function createTester(DocumentImportCommandStub $command): CommandTester
    {
        $app = new Application();
        $app->addCommand($command);
        return new CommandTester($app->find('documents:import'));
    }

    /**
     * @return array{pathname: string, filename: string, extension: string, size: int}
     */
    private static function makeFile(
        string $filename = 'scan.pdf',
        string $extension = 'pdf',
        int $size = 1024,
    ): array {
        return [
            'pathname' => '/var/scanner/' . $filename,
            'filename' => $filename,
            'extension' => $extension,
            'size' => $size,
        ];
    }

    public function testMissingGlobalFails(): void
    {
        $command = new DocumentImportCommandStub(
            globalsOverrides: ['scanner_output_directory' => ''],
        );
        $tester = $this->createTester($command);

        $tester->execute([]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('scanner_output_directory', $tester->getDisplay());
    }

    public function testNonexistentDirectoryFails(): void
    {
        $command = new DocumentImportCommandStub(
            globalsOverrides: ['scanner_output_directory' => '/no/such/directory/for/tests'],
        );
        $tester = $this->createTester($command);

        $tester->execute([]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('Scanner directory does not exist', $tester->getDisplay());
    }

    public function testEmptyDirectoryReturnsSuccess(): void
    {
        $command = new DocumentImportCommandStub(files: []);
        $tester = $this->createTester($command);

        $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertSame([], $command->addDocumentCalls);
        $this->assertSame([], $command->persistInSituCalls);
    }

    public function testMoveModeCallsAddDocumentAndRemovesFile(): void
    {
        $command = new DocumentImportCommandStub(
            files: [self::makeFile('alpha.pdf', 'pdf', 512)],
            mimeOverride: 'application/pdf',
            addDocumentResult: ['url' => 'stored://alpha.pdf'],
            removeFileResult: true,
        );
        $tester = $this->createTester($command);

        $tester->execute(['--pid' => '7', '--owner' => '3', '--limit' => '5']);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertCount(1, $command->addDocumentCalls);
        $call = $command->addDocumentCalls[0];
        $this->assertSame('alpha.pdf', $call['name']);
        $this->assertSame('application/pdf', $call['mime']);
        $this->assertSame(512, $call['size']);
        $this->assertSame('7', $call['pid']);
        $this->assertSame(3, $call['owner']);
        $this->assertSame(['/var/scanner/alpha.pdf'], $command->removedFiles);
    }

    public function testMoveModeFailsWhenAddDocumentReturnsNull(): void
    {
        $command = new DocumentImportCommandStub(
            files: [self::makeFile()],
            addDocumentResult: null,
        );
        $tester = $this->createTester($command);

        $tester->execute([]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('Documents setup error', $tester->getDisplay());
        $this->assertSame([], $command->removedFiles);
    }

    public function testMoveModeFailsWhenUnlinkFails(): void
    {
        $command = new DocumentImportCommandStub(
            files: [self::makeFile()],
            addDocumentResult: ['url' => 'stored://x'],
            removeFileResult: false,
        );
        $tester = $this->createTester($command);

        $tester->execute([]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('Original file deletion error', $tester->getDisplay());
    }

    public function testInSituModeSkipsExistingRecords(): void
    {
        $command = new DocumentImportCommandStub(
            files: [self::makeFile('dup.pdf', 'pdf', 100)],
            existingDocuments: [[['id' => 99]]],
        );
        $tester = $this->createTester($command);

        $tester->execute(['--in-situ' => true]);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertSame([], $command->persistInSituCalls);
        $this->assertSame([], $command->attachCalls);
    }

    public function testInSituModePersistsAndAttachesCategory(): void
    {
        $file = self::makeFile('new.pdf', 'pdf', 200);
        $command = new DocumentImportCommandStub(
            files: [$file],
            persistResult: ['id' => 42, 'url' => 'file:///var/scanner/new.pdf'],
        );
        $tester = $this->createTester($command);

        $tester->execute(['--in-situ' => true, '--category' => '5']);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertCount(1, $command->persistInSituCalls);
        $this->assertSame('file:///var/scanner/new.pdf', $command->persistInSituCalls[0]['docUrl']);
        $this->assertSame([[5, 42]], $command->attachCalls);
    }

    public function testInSituModeReportsPersistFailure(): void
    {
        $command = new DocumentImportCommandStub(
            files: [self::makeFile()],
            persistResult: null,
        );
        $tester = $this->createTester($command);

        $tester->execute(['--in-situ' => true]);

        // In-situ mode logs the error but keeps iterating; per-file failures
        // don't abort the batch the way move-mode failures do.
        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertStringContainsString('Documents setup error', $tester->getDisplay());
        $this->assertSame([], $command->attachCalls);
    }

    public function testLimitOptionStopsIteration(): void
    {
        $files = [
            self::makeFile('a.pdf'),
            self::makeFile('b.pdf'),
            self::makeFile('c.pdf'),
        ];
        $command = new DocumentImportCommandStub(
            files: $files,
            addDocumentResult: ['url' => 'stored://x'],
            removeFileResult: true,
        );
        $tester = $this->createTester($command);

        $tester->execute(['--limit' => '2']);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertCount(2, $command->addDocumentCalls);
    }

    public function testCategoryLookupByNameIsUrlDecoded(): void
    {
        $command = new DocumentImportCommandStub(
            files: [],
            categoryByName: ['Medical Records' => 17],
        );
        $tester = $this->createTester($command);

        $tester->execute(['--category' => 'Medical%20Records', '--in-situ' => true]);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertSame(17, $command->lastResolvedCategoryId);
    }

    public function testNumericCategoryBypassesLookup(): void
    {
        $command = new DocumentImportCommandStub(
            files: [self::makeFile()],
            persistResult: ['id' => 1, 'url' => 'file://x'],
        );
        $tester = $this->createTester($command);

        $tester->execute(['--in-situ' => true, '--category' => '42']);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertSame(42, $command->lastResolvedCategoryId);
        $this->assertSame([[42, 1]], $command->attachCalls);
    }

    public function testUnknownCategoryNameFallsBackToOne(): void
    {
        $command = new DocumentImportCommandStub(
            files: [self::makeFile()],
            categoryByName: [],
            persistResult: ['id' => 8, 'url' => 'file://y'],
        );
        $tester = $this->createTester($command);

        $tester->execute(['--in-situ' => true, '--category' => 'ghost']);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertSame(1, $command->lastResolvedCategoryId);
    }

    public function testInvalidOwnerCoercesToZero(): void
    {
        $command = new DocumentImportCommandStub(
            files: [self::makeFile()],
            addDocumentResult: ['url' => 'stored://x'],
            removeFileResult: true,
        );
        $tester = $this->createTester($command);

        $tester->execute(['--owner' => 'not-a-number']);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertSame(0, $command->addDocumentCalls[0]['owner']);
    }
}

/**
 * Stub for DocumentImportCommand that replaces filesystem, DB, and
 * Document ORM calls with fixture data and call recorders.
 */
class DocumentImportCommandStub extends DocumentImportCommand
{
    /** @var list<array{name: string, mime: string, docPath: string, size: int, owner: int, pid: string}> */
    public array $addDocumentCalls = [];

    /** @var list<string> */
    public array $removedFiles = [];

    /** @var list<array{docPath: string, docUrl: string, mime: string, size: int, pid: string, owner: int}> */
    public array $persistInSituCalls = [];

    /** @var list<array{0: int, 1: int}> */
    public array $attachCalls = [];

    public int $lastResolvedCategoryId = 0;

    /**
     * @param list<array{pathname: string, filename: string, extension: string, size: int}> $files
     * @param array<string, mixed> $globalsOverrides
     * @param array{id: int, url: string}|null $persistResult
     * @param array<string, mixed>|null $addDocumentResult
     * @param list<list<array{id: int|string}>> $existingDocuments
     * @param array<string, int> $categoryByName
     */
    public function __construct(
        private readonly array $files = [],
        array $globalsOverrides = [],
        private readonly ?string $mimeOverride = 'application/octet-stream',
        private readonly ?array $persistResult = ['id' => 1, 'url' => 'file://fake'],
        private readonly ?array $addDocumentResult = ['url' => 'stored://fake'],
        private readonly bool $removeFileResult = true,
        private readonly array $existingDocuments = [],
        private readonly array $categoryByName = [],
    ) {
        parent::__construct();
        $this->setGlobalsBag(new OEGlobalsBag(array_replace([
            'scanner_output_directory' => '/tmp',
        ], $globalsOverrides)));
    }

    protected function loadHelpers(): void
    {
        // Skip legacy helper loading in tests.
    }

    protected function iterateDirectory(string $path): iterable
    {
        yield from $this->files;
    }

    protected function detectMimeType(string $path, string $extension): string
    {
        return $this->mimeOverride ?? parent::detectMimeType($path, $extension);
    }

    protected function resolveCategoryId(string $raw): int
    {
        $id = ctype_digit($raw) ? (int) $raw : $this->categoryByName[urldecode($raw)] ?? 1;
        $this->lastResolvedCategoryId = $id;
        return $id;
    }

    protected function findExistingDocument(string $docPath, string $docUrl): array
    {
        $this->existingQueue ??= $this->existingDocuments;
        return array_shift($this->existingQueue) ?? [];
    }

    /** @var list<list<array{id: int|string}>>|null */
    private ?array $existingQueue = null;

    protected function persistInSituDocument(
        string $docPath,
        string $docUrl,
        string $mime,
        int $size,
        string $pid,
        int $owner,
    ): ?array {
        $this->persistInSituCalls[] = [
            'docPath' => $docPath,
            'docUrl' => $docUrl,
            'mime' => $mime,
            'size' => $size,
            'pid' => $pid,
            'owner' => $owner,
        ];
        return $this->persistResult;
    }

    protected function attachToCategory(int $categoryId, int $documentId): void
    {
        $this->attachCalls[] = [$categoryId, $documentId];
    }

    protected function addDocument(
        string $name,
        string $mime,
        string $docPath,
        int $size,
        int $owner,
        string $pid,
    ): ?array {
        $this->addDocumentCalls[] = [
            'name' => $name,
            'mime' => $mime,
            'docPath' => $docPath,
            'size' => $size,
            'owner' => $owner,
            'pid' => $pid,
        ];
        return $this->addDocumentResult;
    }

    protected function removeFile(string $path): bool
    {
        $this->removedFiles[] = $path;
        return $this->removeFileResult;
    }

}
