<?php

/**
 * Isolated tests for X12RemoteReceiver with an in-memory remote, log and store.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\X12Receive;

use OpenEMR\Billing\X12Receive\ReceivedFileLog;
use OpenEMR\Billing\X12Receive\ReceivedFileRepository;
use OpenEMR\Billing\X12Receive\ReceivedFileStore;
use OpenEMR\Billing\X12Receive\ReceivedStatus;
use OpenEMR\Billing\X12Receive\ReceivePartner;
use OpenEMR\Billing\X12Receive\RemoteDirectory;
use OpenEMR\Billing\X12Receive\RemoteDirectoryOpener;
use OpenEMR\Billing\X12Receive\RemoteFile;
use OpenEMR\Billing\X12Receive\StoreOutcome;
use OpenEMR\Billing\X12Receive\X12FileType;
use OpenEMR\Billing\X12Receive\X12RemoteReceiver;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\Log\NullLogger;

class X12RemoteReceiverTest extends TestCase
{
    private const ISA = 'ISA*00*          *00*          *ZZ*PAYER          *ZZ*RECEIVER       *260101*1200*^*00501*000000001*0*P*:~';

    private static function x12(string $groupCode): string
    {
        return self::ISA . "GS*$groupCode*PAYER*RECEIVER*20260101*1200*1*X*005010~ST*000*0001~SE*2*0001~GE*1*1~IEA*1*000000001~";
    }

    /**
     * @param array<value-of<X12FileType>, string> $folders
     */
    private function partner(array $folders, string $worked = ''): ReceivePartner
    {
        return new ReceivePartner(646, 'sftp.example.test', 22, 'biller', null, $folders, $worked);
    }

    /**
     * @param array<string, array<string, string>> $remoteFiles content by folder and name
     * @param list<string> $truncated names whose download comes back short
     * @param list<string> $grown names that are longer on download than in the listing
     * @return array{X12RemoteReceiver, object{files: array<string, array<string, string>>, moves: list<array{string, string}>, downloads: list<string>}, object{rows: array<string, StoreOutcome>, types: array<string, ?X12FileType>}, object{stored: list<array{X12FileType, string, string}>}}
     */
    private function receiver(array $remoteFiles, array $truncated = [], bool $reachable = true, array $grown = [], int $maxFileBytes = X12RemoteReceiver::DEFAULT_MAX_FILE_BYTES): array
    {
        $remote = new class ($remoteFiles, $truncated, $grown) implements RemoteDirectory {
            /** @var list<array{string, string}> */
            public array $moves = [];

            /** @var list<string> */
            public array $downloads = [];

            /**
             * @param array<string, array<string, string>> $files
             * @param list<string> $truncated
             * @param list<string> $grown names that are longer on download than in the listing
             */
            public function __construct(public array $files, private readonly array $truncated, private readonly array $grown)
            {
            }

            public function listFiles(string $directory): ?array
            {
                if ($directory === '/dropped') {
                    throw new \RuntimeException('Connection closed prematurely');
                }
                if (!isset($this->files[$directory])) {
                    return null;
                }
                $list = [];
                foreach ($this->files[$directory] as $name => $content) {
                    $list[] = new RemoteFile($name, strlen($content), 1767268800);
                }
                return $list;
            }

            public function download(string $remotePath, string $localPath, int $maxBytes): bool
            {
                $this->downloads[] = basename($remotePath);
                $content = $this->files[dirname($remotePath)][basename($remotePath)] ?? null;
                if ($content === null) {
                    return false;
                }
                if (in_array(basename($remotePath), $this->grown, true)) {
                    $content .= str_repeat('X', 500);
                }
                $content = substr($content, 0, $maxBytes);
                if (in_array(basename($remotePath), $this->truncated, true)) {
                    $content = substr($content, 0, 10);
                }
                return file_put_contents($localPath, $content) !== false;
            }

            public function move(string $fromPath, string $toPath): bool
            {
                $this->moves[] = [$fromPath, $toPath];
                return !str_contains($fromPath, 'stuck');
            }
        };
        $opener = new class ($reachable ? $remote : null) implements RemoteDirectoryOpener {
            public function __construct(private readonly ?RemoteDirectory $remote)
            {
            }

            public function open(ReceivePartner $partner): ?RemoteDirectory
            {
                return $this->remote;
            }
        };
        $log = new class implements ReceivedFileLog {
            /** @var array<string, StoreOutcome> */
            public array $rows = [];

            /** @var array<string, ?X12FileType> the kind recorded for each file name */
            public array $types = [];

            public function isSettled(int $partnerId, string $folder, RemoteFile $file): bool
            {
                $row = $this->rows[ReceivedFileRepository::remoteKey($partnerId, $folder, $file)] ?? null;
                return $row !== null && $row->status->isSettled();
            }

            public function record(
                int $partnerId,
                string $folder,
                RemoteFile $file,
                ?X12FileType $type,
                StoreOutcome $outcome,
                \DateTimeImmutable $at,
            ): void {
                if (str_contains($file->name, 'dbfail')) {
                    throw new \RuntimeException('the record could not be written');
                }
                $this->rows[ReceivedFileRepository::remoteKey($partnerId, $folder, $file)] = $outcome;
                $this->types[$file->name] = $type;
            }
        };
        $store = new class implements ReceivedFileStore {
            /** @var list<array{X12FileType, string, string}> */
            public array $stored = [];

            public function store(X12FileType $type, string $localPath, string $remoteName): StoreOutcome
            {
                if (str_contains($remoteName, 'boom')) {
                    throw new \RuntimeException('the file could not be stored');
                }
                $this->stored[] = [$type, $remoteName, (string) file_get_contents($localPath)];
                return new StoreOutcome(ReceivedStatus::Stored, 'stored-' . $remoteName);
            }
        };
        $clock = new class implements ClockInterface {
            public function now(): \DateTimeImmutable
            {
                return new \DateTimeImmutable('2026-11-10 12:00:00');
            }
        };
        return [new X12RemoteReceiver($opener, $log, $store, $clock, new NullLogger(), sys_get_temp_dir(), $maxFileBytes), $remote, $log, $store];
    }

    public function testFilesOfTheRightKindAreStoredAndLeftOnTheRemote(): void
    {
        [$receiver, $remote, , $store] = $this->receiver([
            '/remits' => ['a.RMT' => self::x12('HP')],
            '/277' => ['b.277' => self::x12('HN')],
        ]);

        $summary = $receiver->receive([$this->partner(['f835' => '/remits', 'f277' => '/277'])]);

        $this->assertSame(2, $summary->stored);
        $this->assertSame(0, $summary->moved);
        $this->assertSame([], $remote->moves);
        $this->assertSame(
            [[X12FileType::Remittance835, 'a.RMT', self::x12('HP')], [X12FileType::ClaimStatus277, 'b.277', self::x12('HN')]],
            $store->stored
        );
    }

    public function testSecondRunSkipsWhatTheFirstSettled(): void
    {
        [$receiver, , , $store] = $this->receiver(['/277' => ['b.277' => self::x12('HN'), 'note.txt' => 'hello']]);
        $partner = $this->partner(['f277' => '/277']);

        $first = $receiver->receive([$partner]);
        $second = $receiver->receive([$partner]);

        $this->assertSame([1, 1], [$first->stored, $first->rejected]);
        $this->assertSame([0, 0, 0], [$second->stored, $second->rejected, $second->failed]);
        $this->assertCount(1, $store->stored);
    }

    public function testOneFolderSharedByTwoTypesStoresEachFileUnderItsOwnType(): void
    {
        [$receiver, $remote, $log, $store] = $this->receiver(['/out' => ['a.RMT' => self::x12('HP'), 'b.277' => self::x12('HN')]]);
        $partner = $this->partner(['f835' => '/out', 'f277' => '/out']);

        $first = $receiver->receive([$partner]);
        $second = $receiver->receive([$partner]);

        $this->assertSame([2, 0], [$first->stored, $first->rejected]);
        $this->assertSame(
            [[X12FileType::Remittance835, 'a.RMT', self::x12('HP')], [X12FileType::ClaimStatus277, 'b.277', self::x12('HN')]],
            $store->stored
        );
        $this->assertSame(['a.RMT', 'b.277'], $remote->downloads, 'each file is downloaded once, not once per kind');
        $this->assertSame(['a.RMT' => X12FileType::Remittance835, 'b.277' => X12FileType::ClaimStatus277], $log->types);
        $this->assertSame([0, 0, 0], [$second->stored, $second->rejected, $second->failed]);
    }

    public function testWrongKindOfFileIsRejectedAndNotStored(): void
    {
        [$receiver, , $log, $store] = $this->receiver(['/277' => ['remit.835' => self::x12('HP'), 'readme.txt' => 'hello, not x12']]);

        $summary = $receiver->receive([$this->partner(['f277' => '/277'])]);

        $this->assertSame(2, $summary->rejected);
        $this->assertSame([], $store->stored);
        $this->assertSame(['Not an X12 277 file', 'Not an X12 277 file'], array_values(array_map(static fn(StoreOutcome $o): string => $o->message, $log->rows)));
    }

    public function testShortDownloadFailsAndIsTriedAgainNextRun(): void
    {
        [$receiver, , , $store] = $this->receiver(['/277' => ['b.277' => self::x12('HN')]], truncated: ['b.277']);
        $partner = $this->partner(['f277' => '/277']);

        $first = $receiver->receive([$partner]);
        $second = $receiver->receive([$partner]);

        $this->assertSame(1, $first->failed);
        $this->assertSame(1, $second->failed);
        $this->assertSame([], $store->stored);
    }

    public function testStoredFileIsMovedWhenThePartnerHasAWorkedFolder(): void
    {
        [$receiver, $remote] = $this->receiver(['/remits' => ['a.RMT' => self::x12('HP'), 'stuck.RMT' => self::x12('HP'), 'bad.txt' => 'no']]);

        $summary = $receiver->receive([$this->partner(['f835' => '/remits'], '/WorkedTrans')]);

        $this->assertSame(2, $summary->stored);
        $this->assertSame(1, $summary->moved);
        $this->assertSame([['/remits/a.RMT', '/WorkedTrans/a.RMT'], ['/remits/stuck.RMT', '/WorkedTrans/stuck.RMT']], $remote->moves);
    }

    public function testFileOverTheSizeLimitIsRejectedWithoutADownload(): void
    {
        [$receiver, $remote, $log, $store] = $this->receiver(['/277' => ['big.277' => self::x12('HN')]], maxFileBytes: 50);

        $summary = $receiver->receive([$this->partner(['f277' => '/277'])]);

        $this->assertSame(1, $summary->rejected);
        $this->assertSame([], $remote->downloads);
        $this->assertSame([], $store->stored);
        $this->assertSame(['Larger than the 50 byte limit for a received file'], array_values(array_map(static fn(StoreOutcome $o): string => $o->message, $log->rows)));
    }

    public function testFileThatGrewSinceTheListingIsCutOffAndFails(): void
    {
        [$receiver, $remote, , $store] = $this->receiver(['/277' => ['b.277' => self::x12('HN')]], grown: ['b.277']);

        $summary = $receiver->receive([$this->partner(['f277' => '/277'])]);

        $this->assertSame(1, $summary->failed);
        $this->assertSame(['b.277'], $remote->downloads);
        $this->assertSame([], $store->stored);
    }

    public function testFileThatDoesNotEndWithACompleteInterchangeFailsAndIsTriedAgain(): void
    {
        $partial = substr(self::x12('HN'), 0, -20);
        [$receiver, , $log, $store] = $this->receiver(['/277' => ['partial.277' => $partial]]);
        $partner = $this->partner(['f277' => '/277']);

        $first = $receiver->receive([$partner]);
        $second = $receiver->receive([$partner]);

        $this->assertSame([1, 1], [$first->failed, $second->failed]);
        $this->assertSame([], $store->stored);
        $this->assertSame(['partial.277' => X12FileType::ClaimStatus277], $log->types);
    }

    public function testAcknowledgmentWithATa1BeforeItsGroupOrAloneIsStored(): void
    {
        $withTa1 = self::ISA . 'TA1*000000001*260101*1200*A*000~GS*FA*PAYER*RECEIVER*20260101*1200*1*X*005010~ST*999*0001~SE*2*0001~GE*1*1~IEA*1*000000001~';
        $ta1Only = self::ISA . "TA1*000000001*260101*1200*A*000~IEA*0*000000001~\r\n";
        [$receiver, , , $store] = $this->receiver(['/999' => ['a.999' => $withTa1, 'b.ta1' => $ta1Only]]);

        $summary = $receiver->receive([$this->partner(['f997' => '/999'])]);

        $this->assertSame(2, $summary->stored);
        $this->assertSame([X12FileType::Acknowledgment999, X12FileType::Acknowledgment999], array_column($store->stored, 0));
    }

    public function testOneFileThatThrowsDoesNotStopTheFilesAfterIt(): void
    {
        [$receiver, , $log, $store] = $this->receiver(['/277' => ['a-boom.277' => self::x12('HN'), 'b.277' => self::x12('HN')]]);

        $summary = $receiver->receive([$this->partner(['f277' => '/277'])]);

        $this->assertSame([1, 1, 0], [$summary->failed, $summary->stored, $summary->partnersInterrupted]);
        $this->assertSame([[X12FileType::ClaimStatus277, 'b.277', self::x12('HN')]], $store->stored);
        $this->assertContains('An error interrupted this file', array_map(static fn(StoreOutcome $o): string => $o->message, $log->rows));
    }

    public function testPartnerWhoseConnectionDropsDoesNotStopThePartnersAfterIt(): void
    {
        [$receiver, , , $store] = $this->receiver(['/277' => ['b.277' => self::x12('HN')]]);
        $dropped = new ReceivePartner(645, 'sftp.example.test', 22, 'biller', null, ['f277' => '/dropped'], '');

        $summary = $receiver->receive([$dropped, $this->partner(['f277' => '/277'])]);

        $this->assertSame([2, 1, 1], [$summary->partners, $summary->partnersInterrupted, $summary->stored]);
        $this->assertCount(1, $store->stored);
    }

    public function testRecordThatCannotBeWrittenEndsThatPartnersTurnButNotTheRun(): void
    {
        [$receiver, , , $store] = $this->receiver([
            '/a' => ['a-dbfail.277' => self::x12('HN'), 'z.277' => self::x12('HN')],
            '/b' => ['b.277' => self::x12('HN')],
        ]);
        $first = new ReceivePartner(645, 'sftp.example.test', 22, 'biller', null, ['f277' => '/a'], '');

        $summary = $receiver->receive([$first, $this->partner(['f277' => '/b'])]);

        $this->assertSame([2, 1], [$summary->partners, $summary->partnersInterrupted]);
        // a-dbfail.277 was stored but could not be recorded, so it is not counted and is tried again next
        // run (the store finds it and reports a duplicate); z.277 waits for that run too.
        $this->assertSame(['a-dbfail.277', 'b.277'], array_column($store->stored, 1));
        $this->assertSame(1, $summary->stored);
    }

    public function testFilesInTheRemoteRootAreFetchedAndMovedByTheirPlainPath(): void
    {
        [$receiver, $remote] = $this->receiver(['/' => ['a.277' => self::x12('HN')]]);

        $summary = $receiver->receive([$this->partner(['f277' => '/'], '/done')]);

        $this->assertSame(1, $summary->stored);
        $this->assertSame([['/a.277', '/done/a.277']], $remote->moves);
    }

    public function testUnusableRemoteNameIsRejectedWithoutADownload(): void
    {
        [$receiver, , , $store] = $this->receiver(['/277' => ['.hidden' => self::x12('HN')]]);

        $summary = $receiver->receive([$this->partner(['f277' => '/277'])]);

        $this->assertSame(1, $summary->rejected);
        $this->assertSame([], $store->stored);
    }

    public function testUnreachablePartnerAndUnreadableFolderAreCounted(): void
    {
        [$unreachable] = $this->receiver([], reachable: false);
        [$noFolder] = $this->receiver([]);
        $partner = $this->partner(['f277' => '/277']);

        $this->assertSame(1, $unreachable->receive([$partner])->partnersUnreachable);
        $summary = $noFolder->receive([$partner]);
        $this->assertSame(1, $summary->foldersUnreadable);
        $this->assertSame(
            ['partners' => 1, 'partnersUnreachable' => 0, 'partnersInterrupted' => 0, 'foldersUnreadable' => 1, 'stored' => 0, 'duplicates' => 0, 'rejected' => 0, 'failed' => 0, 'moved' => 0],
            $summary->toArray()
        );
    }
}
