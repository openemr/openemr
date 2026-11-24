<?php

/**
 * Isolated tests for the Direct Message Log report's paging and view.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Controllers\Interface\Reports;

use OpenEMR\Common\DirectMessaging\DirectMessageLogRecord;
use OpenEMR\Common\DirectMessaging\DirectMessageLogRepository;
use OpenEMR\Common\DirectMessaging\DirectMessageLogStatus;
use OpenEMR\Common\DirectMessaging\DirectMessageType;
use OpenEMR\Controllers\Interface\Reports\DirectMessageLogController;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

#[Group('isolated')]
final class DirectMessageLogControllerTest extends TestCase
{
    private DirectMessageLogRepository&MockObject $repository;

    private DirectMessageLogController $controller;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(DirectMessageLogRepository::class);

        $session = new Session(new MockArraySessionStorage());
        $session->set('csrf_private_key', 'controller-test-key');

        $twig = new Environment(new ArrayLoader([
            DirectMessageLogController::TEMPLATE => '{{ logstart }}|{{ records|length }}|{{ csrfToken is empty ? "" : "token" }}',
        ]));

        $this->controller = new DirectMessageLogController($this->repository, $twig, $session);
    }

    /**
     * Refresh posts lognext 0, so it always returns to the newest page, wherever logstart was.
     */
    #[Test]
    #[DataProvider('newestPageProvider')]
    public function showsTheNewestPage(int $logstart, int $lognext): void
    {
        $this->repository->expects(self::once())->method('findLatest')->with(100)->willReturn([]);
        $this->repository->expects(self::never())->method('findByIdRange');

        self::assertSame([], $this->controller->findPage($logstart, $lognext));
    }

    /**
     * @return array<string, array{int, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function newestPageProvider(): array
    {
        return [
            'first visit' => [0, 0],
            'refresh' => [500, 0],
            // Stepping older from a page topped at 100 lands on id 0, which the report has always
            // read as "no page asked for".
            'older lands on zero' => [100, -100],
        ];
    }

    #[Test]
    #[DataProvider('idRangeProvider')]
    public function stepsByIdRange(int $logstart, int $lognext, int $lowestId, int $highestId): void
    {
        $records = [self::record(400)];
        $this->repository->expects(self::never())->method('findLatest');
        $this->repository->expects(self::once())
            ->method('findByIdRange')
            ->with($lowestId, $highestId)
            ->willReturn($records);

        self::assertSame($records, $this->controller->findPage($logstart, $lognext));
    }

    /**
     * @return array<string, array{int, int, int, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function idRangeProvider(): array
    {
        return [
            'older' => [500, -100, 301, 400],
            'newer' => [500, 100, 501, 600],
            'past the oldest row' => [50, -100, -149, -50],
        ];
    }

    #[Test]
    public function postsTheNewestIdOnThePageBackAsLogstart(): void
    {
        $this->repository->method('findByIdRange')->willReturn([self::record(400), self::record(399)]);

        $response = $this->controller->render(Request::create('/', 'POST', ['logstart' => '500', 'lognext' => '-100']));

        self::assertSame('400|2|token', $response->getContent());
        self::assertSame('text/html; charset=utf-8', $response->headers->get('Content-Type'));
    }

    /**
     * Refresh submits the form with lognext still at its empty initial value.
     */
    #[Test]
    public function refreshShowsTheNewestPage(): void
    {
        $this->repository->expects(self::once())->method('findLatest')->with(100)->willReturn([self::record(900)]);
        $this->repository->expects(self::never())->method('findByIdRange');

        $response = $this->controller->render(Request::create('/', 'POST', ['logstart' => '500', 'lognext' => '']));

        self::assertSame('900|1|token', $response->getContent());
    }

    #[Test]
    public function postsZeroAsLogstartForAnEmptyPage(): void
    {
        $this->repository->method('findLatest')->willReturn([]);

        $response = $this->controller->render(Request::create('/'));

        self::assertSame('0|0|token', $response->getContent());
    }

    private static function record(int $id): DirectMessageLogRecord
    {
        return new DirectMessageLogRecord(
            id: $id,
            type: DirectMessageType::Sent,
            createdAt: '2026-01-05 09:00:00',
            sender: 'sender@direct.example',
            recipient: 'recipient@direct.example',
            status: DirectMessageLogStatus::Sent,
            statusChangedAt: null,
        );
    }
}
