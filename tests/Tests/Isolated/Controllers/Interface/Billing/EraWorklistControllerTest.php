<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Controllers\Interface\Billing;

use OpenEMR\Billing\EraAutoPost\AutoPostFileStatus;
use OpenEMR\Billing\EraPosting\ClaimIssue;
use OpenEMR\Billing\EraWorklist\OpenCounts;
use OpenEMR\Billing\EraWorklist\ReceivedFile;
use OpenEMR\Billing\EraWorklist\Worklist;
use OpenEMR\Billing\EraWorklist\WorklistFilter;
use OpenEMR\Billing\EraWorklist\WorklistPage;
use OpenEMR\Billing\EraWorklist\WorklistUser;
use OpenEMR\Controllers\Interface\Billing\EraWorklistController;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

#[Group('isolated')]
final class EraWorklistControllerTest extends TestCase
{
    private const NOW = '2026-11-27 09:30:00';

    private Worklist&MockObject $worklist;

    private Session $session;

    private EraWorklistController $controller;

    protected function setUp(): void
    {
        $this->worklist = $this->createMock(Worklist::class);
        $this->worklist->method('openCounts')->willReturn(new OpenCounts(4, 2));

        $this->session = new Session(new MockArraySessionStorage());
        $this->session->set('csrf_private_key', 'controller-test-key');
        $this->session->set('authUserID', '7');

        $twig = new Environment(new ArrayLoader([
            EraWorklistController::TEMPLATE => '{{ view }}|{{ rows|length }}/{{ total }}/{{ limit }}|{{ selfQuery|raw }}'
                . '|{{ counts.review }},{{ counts.files }}|{{ payers|join(",") }}|{{ reasons|length }}|{{ today }}'
                . '|{{ csrfToken is empty ? "" : "token" }}',
        ]));
        $clock = new class implements ClockInterface {
            public function now(): \DateTimeImmutable
            {
                return new \DateTimeImmutable('2026-11-27 09:30:00');
            }
        };

        $this->controller = new EraWorklistController($this->worklist, $twig, $this->session, $clock);
    }

    #[Test]
    public function opensOnTheClaimPaymentsLeftForReview(): void
    {
        $this->worklist->expects(self::once())
            ->method('reviewClaims')
            ->with(new WorklistFilter(), EraWorklistController::REVIEW_LIMIT)
            ->willReturn(new WorklistPage([], 0));
        $this->worklist->expects(self::never())->method('postedChecks');
        $this->worklist->expects(self::never())->method('receivedFiles');
        $this->worklist->method('payerNames')->willReturn(['ACME HEALTH', 'OTHER PLAN']);

        $response = $this->controller->show(Request::create('/interface/billing/era_worklist.php'));

        self::assertSame('review|0/0/500|view=review|4,2|ACME HEALTH,OTHER PLAN|7|2026-11-27|token', $response->getContent());
        self::assertSame('text/html; charset=utf-8', $response->headers->get('Content-Type'));
    }

    /**
     * A view the page does not have opens the review list too.
     */
    #[Test]
    public function anUnknownViewOpensTheReviewList(): void
    {
        $this->worklist->expects(self::once())->method('reviewClaims')->willReturn(new WorklistPage([], 0));

        $response = $this->controller->show(Request::create('/', 'GET', ['view' => 'everything']));

        self::assertStringStartsWith('review|', (string) $response->getContent());
    }

    #[Test]
    public function passesTheFilterToTheListAndBackToThePage(): void
    {
        $filter = new WorklistFilter(
            payerName: 'ACME & SONS',
            checkNumber: '1001',
            from: new \DateTimeImmutable('2026-11-01'),
            to: new \DateTimeImmutable('2026-11-30'),
            reason: ClaimIssue::Denied,
            showWorked: true,
        );
        $this->worklist->expects(self::once())
            ->method('reviewClaims')
            ->with($filter, EraWorklistController::REVIEW_LIMIT)
            ->willReturn(new WorklistPage([], 612));

        $response = $this->controller->show(Request::create('/', 'GET', [
            'view' => 'review',
            'payer' => 'ACME & SONS',
            'check' => '1001',
            'from' => '2026-11-01',
            'to' => '2026-11-30',
            'reason' => 'denied',
            'worked' => '1',
        ]));

        self::assertStringStartsWith(
            'review|0/612/500|view=review&payer=ACME+%26+SONS&check=1001&from=2026-11-01&to=2026-11-30&reason=denied&worked=1|',
            (string) $response->getContent()
        );
    }

    #[Test]
    public function listsThePostedChecks(): void
    {
        $this->worklist->expects(self::once())
            ->method('postedChecks')
            ->with(new WorklistFilter(checkNumber: '1001'), EraWorklistController::CHECK_LIMIT)
            ->willReturn(new WorklistPage([], 0));
        $this->worklist->expects(self::never())->method('reviewClaims');
        $this->worklist->method('payerNames')->willReturn(['ACME HEALTH']);

        $response = $this->controller->show(Request::create('/', 'GET', ['view' => 'posted', 'check' => '1001']));

        self::assertStringStartsWith('posted|0/0/50|view=posted&check=1001|4,2|ACME HEALTH|', (string) $response->getContent());
    }

    /**
     * The files view has no payer to choose: a file never read has none.
     */
    #[Test]
    public function listsTheReceivedFilesWithoutLookingUpPayers(): void
    {
        $file = new ReceivedFile(9, 'Partner', 'remote.RMT', 'stored.edi', new \DateTimeImmutable(self::NOW), null, [], null);
        $this->worklist->expects(self::once())
            ->method('receivedFiles')
            ->with(new WorklistFilter(), EraWorklistController::FILE_LIMIT)
            ->willReturn(new WorklistPage([$file], 1));
        $this->worklist->expects(self::never())->method('payerNames');

        $response = $this->controller->show(Request::create('/', 'GET', ['view' => 'files']));

        self::assertStringStartsWith('files|1/1/200|view=files|4,2||', (string) $response->getContent());
    }

    #[Test]
    public function marksAClaimPaymentWorkedByTheSignedInUserNow(): void
    {
        $this->worklist->expects(self::once())
            ->method('markClaimWorked')
            ->with(31, new WorklistUser(7), new \DateTimeImmutable(self::NOW))
            ->willReturn(true);

        self::assertTrue($this->controller->mark(self::post('claim', 'worked', '31')));
    }

    #[Test]
    public function marksAFileWorkedByTheSignedInUserNowAsThePageShowedIt(): void
    {
        $this->worklist->expects(self::exactly(2))
            ->method('markFileWorked')
            ->willReturnCallback(static function (int $id, WorklistUser $by, \DateTimeImmutable $at, ?AutoPostFileStatus $seen, int $heldSeen): bool {
                self::assertSame([9, 7, self::NOW], [$id, $by->id, $at->format('Y-m-d H:i:s')]);

                return [$seen, $heldSeen] === [AutoPostFileStatus::Done, 2] || [$seen, $heldSeen] === [null, 0];
            });

        self::assertTrue($this->controller->mark(self::post('file', 'worked', '9', 'done', '2')));
        // A file that was never for unattended posting has no state.
        self::assertTrue($this->controller->mark(self::post('file', 'worked', '9', '', '0')));
    }

    #[Test]
    #[DataProvider('fileNotAsShownProvider')]
    public function marksNoFileWhenTheFormDoesNotSayHowThePageShowedIt(string $state, string $held): void
    {
        $this->expectNoMark();

        self::assertFalse($this->controller->mark(self::post('file', 'worked', '9', $state, $held)));
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function fileNotAsShownProvider(): array
    {
        return [
            'no held count' => ['done', ''],
            'unknown state' => ['stuck', '0'],
            'held count not a number' => ['done', 'two'],
            'negative held count' => ['', '-1'],
        ];
    }

    #[Test]
    public function clearsAMark(): void
    {
        $this->worklist->expects(self::once())->method('clearClaimWorked')->with(31)->willReturn(true);
        $this->worklist->expects(self::once())->method('clearFileWorked')->with(9)->willReturn(false);

        self::assertTrue($this->controller->mark(self::post('claim', 'clear', '31')));
        // The file was not marked: nothing changed.
        self::assertFalse($this->controller->mark(self::post('file', 'clear', '9')));
    }

    #[Test]
    public function takesAFileFromUnattendedPostingInTheStateItWasShownIn(): void
    {
        $this->worklist->expects(self::once())
            ->method('takeFromUnattended')
            ->with(9, AutoPostFileStatus::Running)
            ->willReturn(true);

        self::assertTrue($this->controller->mark(self::post('file', 'take', '9', 'running')));
    }

    #[Test]
    public function takesNoFileWhenTheFormNamesNoState(): void
    {
        $this->expectNoMark();

        self::assertFalse($this->controller->mark(self::post('file', 'take', '9')));
        self::assertFalse($this->controller->mark(self::post('file', 'take', '9', 'stuck')));
    }

    #[Test]
    #[DataProvider('nothingToMarkProvider')]
    public function marksNothingWhenTheFormNamesNoRow(string $kind, string $action, string $id): void
    {
        $this->expectNoMark();

        self::assertFalse($this->controller->mark(self::post($kind, $action, $id)));
    }

    /**
     * @return array<string, array{string, string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function nothingToMarkProvider(): array
    {
        return [
            'no id' => ['claim', 'worked', ''],
            'id zero' => ['claim', 'worked', '0'],
            'id not a number' => ['file', 'worked', '9 or 1=1'],
            'unknown kind' => ['check', 'worked', '9'],
            'unknown action' => ['claim', 'delete', '31'],
            'a claim payment is not taken' => ['claim', 'take', '31'],
        ];
    }

    #[Test]
    public function marksNothingWithoutASignedInUser(): void
    {
        $this->session->remove('authUserID');
        $this->expectNoMark();

        self::assertFalse($this->controller->mark(self::post('claim', 'worked', '31')));
    }

    private function expectNoMark(): void
    {
        foreach (['markClaimWorked', 'clearClaimWorked', 'markFileWorked', 'clearFileWorked', 'takeFromUnattended'] as $method) {
            $this->worklist->expects(self::never())->method($method);
        }
    }

    private static function post(string $kind, string $action, string $id, string $state = '', string $held = ''): Request
    {
        return Request::create(
            '/interface/billing/era_worklist.php?view=review',
            'POST',
            ['kind' => $kind, 'action' => $action, 'id' => $id, 'state' => $state, 'held' => $held]
        );
    }
}
