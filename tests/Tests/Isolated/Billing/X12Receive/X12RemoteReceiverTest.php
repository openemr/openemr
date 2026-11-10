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
     * @return array{X12RemoteReceiver, object{files: array<string, array<string, string>>, moves: list<array{string, string}>}, object{rows: array<string, StoreOutcome>}, object{stored: list<array{X12FileType, string, string}>}}
     */
    private function receiver(array $remoteFiles, array $truncated = [], bool $reachable = true): array
    {
        $remote = new class ($remoteFiles, $truncated) implements RemoteDirectory {
            /** @var list<array{string, string}> */
            public array $moves = [];

            /**
             * @param array<string, array<string, string>> $files
             * @param list<string> $truncated
             */
            public function __construct(public array $files, private readonly array $truncated)
            {
            }

            public function listFiles(string $directory): ?array
            {
                if (!isset($this->files[$directory])) {
                    return null;
                }
                $list = [];
                foreach ($this->files[$directory] as $name => $content) {
                    $list[] = new RemoteFile($name, strlen($content), 1767268800);
                }
                return $list;
            }

            public function download(string $remotePath, string $localPath): bool
            {
                $content = $this->files[dirname($remotePath)][basename($remotePath)] ?? null;
                if ($content === null) {
                    return false;
                }
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

            public function isSettled(int $partnerId, string $folder, RemoteFile $file): bool
            {
                $row = $this->rows[ReceivedFileRepository::remoteKey($partnerId, $folder, $file)] ?? null;
                return $row !== null && $row->status->isSettled();
            }

            public function record(
                int $partnerId,
                string $folder,
                RemoteFile $file,
                X12FileType $type,
                StoreOutcome $outcome,
                \DateTimeImmutable $at,
            ): void {
                $this->rows[ReceivedFileRepository::remoteKey($partnerId, $folder, $file)] = $outcome;
            }
        };
        $store = new class implements ReceivedFileStore {
            /** @var list<array{X12FileType, string, string}> */
            public array $stored = [];

            public function store(X12FileType $type, string $localPath, string $remoteName): StoreOutcome
            {
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
        return [new X12RemoteReceiver($opener, $log, $store, $clock, new NullLogger(), sys_get_temp_dir()), $remote, $log, $store];
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
            ['partners' => 1, 'partnersUnreachable' => 0, 'foldersUnreadable' => 1, 'stored' => 0, 'duplicates' => 0, 'rejected' => 0, 'failed' => 0, 'moved' => 0],
            $summary->toArray()
        );
    }
}
