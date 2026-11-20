<?php

/**
 * Isolated tests for the Email Queue report controller.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025-2026 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Controllers\Interface\Reports;

use OpenEMR\Controllers\Interface\Reports\EmailQueueReportController;
use OpenEMR\Reports\Email\EmailQueueService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

#[Group('isolated')]
final class EmailQueueReportControllerTest extends TestCase
{
    private EmailQueueService&MockObject $service;

    private EmailQueueReportController $controller;

    protected function setUp(): void
    {
        $this->service = $this->createMock(EmailQueueService::class);

        $twig = new Environment(new ArrayLoader([
            EmailQueueReportController::TEMPLATE
                => '{{ currentPage }}|{{ totalPages }}|{{ totalCount }}|{{ queryString }}|{{ webroot }}|{{ emails|length }}',
        ]));

        $this->controller = new EmailQueueReportController($this->service, $twig, '/openemr');
    }

    #[Test]
    public function filtersFromRequestReadsQueryBag(): void
    {
        $request = Request::create('/report', 'GET', [
            'search' => 'alice',
            'status' => 'sent',
            'template_name' => 'welcome',
            'date_from' => '2026-01-01',
            'date_to' => '2026-01-31',
            'page' => '2',
        ]);

        self::assertSame(
            [
                'search' => 'alice',
                'status' => 'sent',
                'template_name' => 'welcome',
                'date_from' => '2026-01-01',
                'date_to' => '2026-01-31',
            ],
            $this->controller->filtersFromRequest($request)
        );
        self::assertSame(2, $this->controller->pageFromRequest($request));
    }

    /**
     * @return array<string, array{string|int|null, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function pageProvider(): array
    {
        return [
            'missing' => [null, 1],
            'zero becomes one' => [0, 1],
            'negative becomes one' => [-3, 1],
            'valid' => [4, 4],
        ];
    }

    #[Test]
    #[DataProvider('pageProvider')]
    public function pageFromRequestNormalizes(int|string|null $page, int $expected): void
    {
        $query = $page === null ? [] : ['page' => $page];
        $request = Request::create('/report', 'GET', $query);
        self::assertSame($expected, $this->controller->pageFromRequest($request));
    }

    #[Test]
    public function buildQueryStringOmitsEmptyFilters(): void
    {
        self::assertSame(
            'search=bob&status=failed',
            $this->controller->buildQueryString([
                'search' => 'bob',
                'status' => 'failed',
                'template_name' => '',
                'date_from' => '',
                'date_to' => '',
            ])
        );
        self::assertSame('', $this->controller->buildQueryString([
            'search' => '',
            'status' => '',
            'template_name' => '',
            'date_from' => '',
            'date_to' => '',
        ]));
    }

    #[Test]
    public function renderClampsPageAndPassesViewData(): void
    {
        $this->service->expects(self::once())->method('getEmailQueueCount')->willReturn(120);
        $this->service->expects(self::once())->method('getStatistics')->willReturn([
            'total' => 120,
            'sent' => 100,
            'pending' => 15,
            'failed' => 5,
        ]);
        $this->service->expects(self::once())->method('getTemplateNames')->willReturn(['welcome']);
        $this->service->expects(self::once())
            ->method('getEmailQueue')
            // page 99 clamps to totalPages=3 so offset is (3-1)*50 = 100
            ->with(
                [
                    'search' => 'x',
                    'status' => '',
                    'template_name' => '',
                    'date_from' => '',
                    'date_to' => '',
                ],
                EmailQueueReportController::PAGE_SIZE,
                100
            )
            ->willReturn([
                ['id' => 1],
                ['id' => 2],
            ]);

        $response = $this->controller->render(Request::create('/report', 'GET', [
            'search' => 'x',
            'page' => '99',
        ]));

        self::assertSame('3|3|120|search=x|/openemr|2', $response->getContent());
        self::assertSame('text/html; charset=utf-8', $response->headers->get('Content-Type'));
    }

    #[Test]
    public function renderUsesFirstPageWhenEmpty(): void
    {
        $this->service->method('getEmailQueueCount')->willReturn(0);
        $this->service->method('getStatistics')->willReturn([
            'total' => 0,
            'sent' => 0,
            'pending' => 0,
            'failed' => 0,
        ]);
        $this->service->method('getTemplateNames')->willReturn([]);
        $this->service->expects(self::once())
            ->method('getEmailQueue')
            ->with(self::anything(), EmailQueueReportController::PAGE_SIZE, 0)
            ->willReturn([]);

        $response = $this->controller->render(Request::create('/report'));
        self::assertSame('1|1|0||/openemr|0', $response->getContent());
    }
}
