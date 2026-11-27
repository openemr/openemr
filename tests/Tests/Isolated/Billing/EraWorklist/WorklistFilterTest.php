<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\EraWorklist;

use OpenEMR\Billing\EraAutoPost\AutoPostFileStatus;
use OpenEMR\Billing\EraPosting\ClaimIssue;
use OpenEMR\Billing\EraWorklist\ReceivedFile;
use OpenEMR\Billing\EraWorklist\WorklistFilter;
use OpenEMR\Billing\EraWorklist\WorklistUser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;

#[Group('isolated')]
final class WorklistFilterTest extends TestCase
{
    /**
     * @param array<string, string> $parameters
     * @return InputBag<string> a query string as the page receives it
     */
    private static function query(array $parameters): InputBag
    {
        return Request::create('/interface/billing/era_worklist.php', 'GET', $parameters)->query;
    }

    #[Test]
    public function readsWhatTheFilterFormSends(): void
    {
        $filter = WorklistFilter::fromQuery(self::query([
            'payer' => ' ACME HEALTH ',
            'check' => ' 1001 ',
            'from' => '2026-11-01',
            'to' => '2026-11-30',
            'reason' => 'lines_pended',
            'worked' => '1',
        ]));

        self::assertSame('ACME HEALTH', $filter->payerName);
        self::assertSame('1001', $filter->checkNumber);
        self::assertSame('2026-11-01 00:00:00', $filter->from?->format('Y-m-d H:i:s'));
        self::assertSame('2026-11-30 00:00:00', $filter->to?->format('Y-m-d H:i:s'));
        self::assertSame(ClaimIssue::LinesPended, $filter->reason);
        self::assertSame('lines_pended', $filter->reasonValue());
        self::assertTrue($filter->showWorked);
    }

    #[Test]
    public function listsEverythingNotWorkedWhenNothingIsAsked(): void
    {
        $filter = WorklistFilter::fromQuery(self::query([]));

        self::assertEquals(new WorklistFilter(), $filter);
        self::assertSame('', $filter->reasonValue());
        self::assertFalse($filter->showWorked);
    }

    #[Test]
    #[DataProvider('notADayProvider')]
    public function ignoresADateThatIsNotADay(string $typed): void
    {
        $filter = WorklistFilter::fromQuery(self::query(['from' => $typed, 'to' => $typed]));

        self::assertNull($filter->from);
        self::assertNull($filter->to);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function notADayProvider(): array
    {
        return [
            'empty' => [''],
            'words' => ['yesterday'],
            'another format' => ['11/01/2026'],
            'rolls over' => ['2026-02-31'],
            'with a time' => ['2026-11-01 10:00:00'],
        ];
    }

    #[Test]
    #[DataProvider('notAReviewReasonProvider')]
    public function ignoresAReasonNoClaimIsLeftForReviewFor(string $reason): void
    {
        self::assertNull(WorklistFilter::fromQuery(self::query(['reason' => $reason]))->reason);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function notAReviewReasonProvider(): array
    {
        return [
            'none' => [''],
            'unknown' => ['no_such_reason'],
            // Already posted from the check: skipped, not left for review.
            'skipped' => ['already_posted'],
            // A held check is worked from its file.
            'held' => ['check_held'],
        ];
    }

    #[Test]
    public function offersEveryReasonAClaimIsLeftForReviewFor(): void
    {
        self::assertSame(
            ['claim_not_found', 'denied', 'reversal', 'charge_mismatch', 'code_not_on_invoice', 'unpaid_adjustment', 'lines_pended'],
            WorklistFilter::reviewReasons()
        );
        foreach (WorklistFilter::reviewReasons() as $reason) {
            self::assertSame($reason, WorklistFilter::fromQuery(self::query(['reason' => $reason]))->reasonValue());
        }
    }

    #[Test]
    public function aWorklistUserNeedsAUserId(): void
    {
        self::assertSame(7, (new WorklistUser(7))->id);

        $this->expectException(\DomainException::class);
        new WorklistUser(0);
    }

    #[Test]
    #[DataProvider('eraNameProvider')]
    public function theNameThePostingWindowOpensAFileBy(string $storedName, string $eraName): void
    {
        $file = new ReceivedFile(1, 'Partner', 'remote.RMT', $storedName, new \DateTimeImmutable('2026-11-19 10:00:00'), null, [], null);

        self::assertSame($eraName, $file->eraName());
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function eraNameProvider(): array
    {
        return [
            'as stored for posting' => ['20261020_1234_87726.edi', '20261020_1234_87726'],
            'no extension' => ['20261020_1234_87726', '20261020_1234_87726'],
            'only the last extension goes' => ['a.edi.edi', 'a.edi'],
        ];
    }

    #[Test]
    public function aFileAPostingRunIsStillToFinishAwaitsTheRun(): void
    {
        $awaiting = [];
        foreach ([null, ...AutoPostFileStatus::cases()] as $status) {
            $file = new ReceivedFile(1, 'Partner', 'remote.RMT', 'a.edi', new \DateTimeImmutable('2026-11-19 10:00:00'), $status, [], null);
            if ($file->awaitsRun()) {
                $awaiting[] = $status;
            }
        }

        self::assertSame([AutoPostFileStatus::Pending, AutoPostFileStatus::Running], $awaiting);
    }
}
