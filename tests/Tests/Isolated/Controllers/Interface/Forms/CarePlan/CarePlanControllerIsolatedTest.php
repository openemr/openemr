<?php

/**
 * Isolated tests for CarePlanController.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (C) 2025 Open Plan IT Ltd. <support@openplanit.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Controllers\Interface\Forms\CarePlan;

use OpenEMR\Controllers\Interface\Forms\CarePlan\CarePlanController;
use OpenEMR\Services\Forms\CarePlanFormService;
use OpenEMR\Services\FormService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Twig\Environment;

#[Group('isolated')]
class CarePlanControllerIsolatedTest extends TestCase
{
    private CarePlanFormService&MockObject $carePlanFormService;

    private FormService&MockObject $formService;

    private Environment&MockObject $twig;

    private Session $session;

    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->carePlanFormService = $this->createMock(CarePlanFormService::class);
        $this->formService = $this->createMock(FormService::class);
        $this->twig = $this->createMock(Environment::class);
        $this->session = new Session(new MockArraySessionStorage());
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->session->set('pid', 42);
        $this->session->set('encounter', 7);
        $this->session->set('authUser', 'testuser');
        $this->session->set('authProvider', 'Default');
    }

    private function controller(): CarePlanController
    {
        return new CarePlanController(
            $this->carePlanFormService,
            $this->formService,
            $this->twig,
            $this->session,
            $this->logger,
            '/openemr',
            'http://localhost',
        );
    }

    // The permission-denied paths are not covered here: denyAccess() calls xlt(), which
    // resolves translations through sqlStatementNoLog() and therefore needs a database.
    // Those cases live in tests/Tests/Controllers/Interface/Forms/CarePlan/.

    #[Test]
    public function testReportActionReturnsEmptyBodyWhenNoIdSupplied(): void
    {
        $this->formService->method('hasFormPermission')->willReturn(true);
        $this->carePlanFormService->expects($this->never())->method('getCarePlanRows');
        $this->twig->expects($this->never())->method('render');

        $response = $this->controller()->reportAction(42, 7, 3, null);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('', $response->getContent());
    }

    #[Test]
    public function testReportActionReturnsEmptyBodyWhenFormHasNoRows(): void
    {
        $this->formService->method('hasFormPermission')->willReturn(true);
        $this->carePlanFormService->method('getCarePlanRows')->willReturn([]);
        $this->twig->expects($this->never())->method('render');

        $response = $this->controller()->reportAction(42, 7, 3, 5);

        $this->assertSame('', $response->getContent());
    }

    #[Test]
    public function testReportActionRendersRows(): void
    {
        $rows = [['code' => 'SNOMED-CT:123', 'description' => 'a plan']];

        $this->formService->method('hasFormPermission')->willReturn(true);
        $this->carePlanFormService->method('getCarePlanRows')->willReturn($rows);
        $this->twig->expects($this->once())
            ->method('render')
            ->with('/forms/care_plan/templates/care_plan_report.html.twig', ['rows' => $rows])
            ->willReturn('<table></table>');

        $response = $this->controller()->reportAction(42, 7, 3, 5);

        $this->assertSame('<table></table>', $response->getContent());
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    #[Test]
    public function testReportActionFallsBackToSessionPidAndEncounter(): void
    {
        $this->formService->method('hasFormPermission')->willReturn(true);
        $this->carePlanFormService->expects($this->once())
            ->method('getCarePlanRows')
            ->with(5, 42, 7)
            ->willReturn([]);

        $this->controller()->reportAction(0, 0, 3, 5);
    }

    /**
     * A positive id belonging to another encounter would delete nothing and then write
     * rows under a foreign, unregistered form id, so the save has to be refused.
     */
    #[Test]
    public function testSubmittedFormIdMustBelongToTheEncounter(): void
    {
        $this->carePlanFormService->method('formBelongsToEncounter')
            ->willReturnCallback(static function (int $formId, int $pid, int $encounter): bool {
                // Assert the ownership context is forwarded intact -- checking only the
                // form id would still pass if the controller looked it up against the
                // wrong patient or encounter.
                self::assertSame(42, $pid);
                self::assertSame(7, $encounter);

                return in_array($formId, [5, 6], true);
            });

        // Zero means "create a new form", which is always allowed and must not query.
        self::assertTrue($this->controller()->isSubmittedFormIdValid(0, 42, 7));
        // Any care plan form registered against this encounter is accepted -- an encounter
        // can carry more than one, so this must not be equality against a single id.
        self::assertTrue($this->controller()->isSubmittedFormIdValid(5, 42, 7));
        self::assertTrue($this->controller()->isSubmittedFormIdValid(6, 42, 7));
        // A form id from some other encounter is refused.
        self::assertFalse($this->controller()->isSubmittedFormIdValid(99, 42, 7));
    }

    #[Test]
    public function testSubmittedFormIdOfZeroDoesNotHitTheDatabase(): void
    {
        $this->carePlanFormService->expects($this->never())->method('formBelongsToEncounter');

        self::assertTrue($this->controller()->isSubmittedFormIdValid(0, 42, 7));
    }

    #[Test]
    public function testMapPostToRowsReturnsNothingWhenNoCountsSubmitted(): void
    {
        self::assertSame([], $this->controller()->mapPostToRows([], 42, 7, 1));
        self::assertSame([], $this->controller()->mapPostToRows(['count' => ['0', '']], 42, 7, 1));
    }

    /**
     * The pre-refactor code tested the whole POST array rather than the per-row value, so
     * the failsafe only fired when no row at all carried a reason code. Row 1 here has a
     * reason code and row 2 does not; only row 2 may be blanked.
     */
    #[Test]
    public function testReasonFieldsAreBlankedPerRowWhenThatRowHasNoReasonCode(): void
    {
        $this->carePlanFormService->method('normalizeNullableString')
            ->willReturnCallback(static fn(string $v): ?string => trim($v) !== '' ? trim($v) : null);
        $this->carePlanFormService->method('parseNote')->willReturn('[]');

        $rows = $this->controller()->mapPostToRows([
            'count' => ['1', '2'],
            'reasonCode' => ['SNOMED-CT:111', ''],
            'reasonCodeStatus' => ['resolved', 'resolved'],
            'reasonCodeText' => ['has a code', 'orphaned text'],
            'reasonDateLow' => ['2026-01-01 00:00', '2026-02-02 00:00'],
            'reasonDateHigh' => ['2026-03-03 00:00', '2026-04-04 00:00'],
        ], 42, 7, 1);

        self::assertCount(2, $rows);

        self::assertSame('SNOMED-CT:111', $rows[0]['reason_code']);
        self::assertSame('resolved', $rows[0]['reason_status']);
        self::assertSame('has a code', $rows[0]['reason_description']);
        self::assertSame('2026-01-01 00:00', $rows[0]['reason_date_low']);
        self::assertSame('2026-03-03 00:00', $rows[0]['reason_date_high']);

        self::assertSame('', $rows[1]['reason_code']);
        self::assertSame('', $rows[1]['reason_status']);
        self::assertSame('', $rows[1]['reason_description']);
        self::assertSame('', $rows[1]['reason_date_low']);
        self::assertSame('', $rows[1]['reason_date_high']);
    }

    /**
     * The engagement category select is client-controlled, so a submitted value that the
     * list does not define must not reach the database -- it would have no localized title
     * and would export as an unresolvable EHI value.
     */
    #[Test]
    public function testEngagementCategoryIsCheckedAgainstTheList(): void
    {
        $this->carePlanFormService->method('normalizeNullableString')
            ->willReturnCallback(static fn(string $v): ?string => trim($v) !== '' ? trim($v) : null);
        $this->carePlanFormService->method('parseNote')->willReturn('[]');
        // Includes a deployment-added option at seq 70+ and a retired one, both of which
        // must remain acceptable.
        $this->carePlanFormService->method('getEngagementCategoryOptionIds')
            ->willReturn(['active', 'site_specific_option', 'retired_option']);

        $rows = $this->controller()->mapPostToRows([
            'count' => ['1', '2', '3', '4'],
            'plan_engagement_category' => ['active', 'site_specific_option', 'not-a-real-option', ''],
        ], 42, 7, 1);

        self::assertSame('active', $rows[0]['plan_engagement_category']);
        self::assertSame('site_specific_option', $rows[1]['plan_engagement_category']);
        self::assertNull($rows[2]['plan_engagement_category'], 'unknown option must be discarded');
        self::assertNull($rows[3]['plan_engagement_category'], 'empty stays unset');
    }

    #[Test]
    public function testEngagementCategoryListIsFetchedOncePerSaveNotPerRow(): void
    {
        $this->carePlanFormService->method('normalizeNullableString')->willReturn(null);
        $this->carePlanFormService->method('parseNote')->willReturn('[]');
        $this->carePlanFormService->expects($this->once())
            ->method('getEngagementCategoryOptionIds')
            ->willReturn(['active']);

        $this->controller()->mapPostToRows(['count' => ['1', '2', '3']], 42, 7, 1);
    }

    #[Test]
    public function testMapPostToRowsFallsBackToSessionUserWhenRowHasNone(): void
    {
        $this->carePlanFormService->method('normalizeNullableString')->willReturn(null);
        $this->carePlanFormService->method('parseNote')->willReturn('[]');

        $rows = $this->controller()->mapPostToRows([
            'count' => ['1', '2'],
            'user' => ['someone', ''],
        ], 42, 7, 1);

        self::assertSame('someone', $rows[0]['user']);
        self::assertSame('testuser', $rows[1]['user']);
        self::assertSame('Default', $rows[1]['groupname']);
        self::assertSame(42, $rows[1]['pid']);
        self::assertSame(7, $rows[1]['encounter']);
    }
}
