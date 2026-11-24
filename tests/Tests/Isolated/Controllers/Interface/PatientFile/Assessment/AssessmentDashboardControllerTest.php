<?php

/**
 * Isolated tests for the Questionnaire Assessments page's controller.
 *
 * The store is an in-memory fake, so these cover what the controller decides: which cards
 * and history rows show, what each opens, and which SMART apps each offers.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Controllers\Interface\PatientFile\Assessment;

use OpenEMR\Controllers\Interface\PatientFile\Assessment\AssessmentCard;
use OpenEMR\Controllers\Interface\PatientFile\Assessment\AssessmentCategory;
use OpenEMR\Controllers\Interface\PatientFile\Assessment\AssessmentDashboardContext;
use OpenEMR\Controllers\Interface\PatientFile\Assessment\AssessmentDashboardController;
use OpenEMR\Controllers\Interface\PatientFile\Assessment\AssessmentPageChrome;
use OpenEMR\Controllers\Interface\PatientFile\Assessment\SmartApp;
use OpenEMR\Services\Globals\CollapsedSections;
use OpenEMR\Services\Questionnaire\AssessmentPageLayout;
use OpenEMR\Services\Questionnaire\AvailabilityContext;
use OpenEMR\Services\Questionnaire\QuestionnaireSurface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

#[Group('isolated')]
class AssessmentDashboardControllerTest extends TestCase
{
    use TranslationOff;

    protected function setUp(): void
    {
        $this->turnTranslationOff();
    }

    protected function tearDown(): void
    {
        $this->restoreTranslation();
    }

    /**
     * A controller for patient 7 at encounter 5, facility 3, with two SMART apps.
     *
     * @param list<string> $collapsed sections the user folded
     */
    public static function controller(
        FakeAssessmentStore $store,
        bool $authorized = true,
        int $pid = 7,
        array $collapsed = [],
        bool $showTests = true,
    ): AssessmentDashboardController {
        return new AssessmentDashboardController(
            new Environment(new ArrayLoader()),
            $store,
            new AssessmentDashboardContext(
                authorized: $authorized,
                pid: $pid,
                encounter: 5,
                facility: 3,
                smartApps: [new SmartApp('app-1', 'Growth Chart'), new SmartApp('app-2', 'Risk Calc')],
                showSmartContextTestLaunches: $showTests,
                csrfToken: 'token',
                adminTabs: [
                    'canUseTemplates' => false,
                    'canManageAvailability' => true,
                    'canImport' => true,
                    'templatesUrl' => '/portal/patient/onsitedocuments?pid=7&is_module=1',
                    'availabilityUrl' => '/interface/super/manage_questionnaire_availability.php',
                    'importUrl' => '/interface/super/manage_questionnaire_availability.php?view=import',
                ],
                webRoot: '',
            ),
            new AssessmentPageChrome('container-fluid', '<!-- expand -->', '<nav>menu</nav>'),
            static fn(array $sections): CollapsedSections => new CollapsedSections(array_values(array_intersect($collapsed, $sections))),
        );
    }

    #[Test]
    public function withoutPatientsMedNothingIsRead(): void
    {
        $store = FakeAssessmentStore::sample();
        $view = self::controller($store, authorized: false)->buildView();

        $this->assertFalse($view['authorized']);
        $this->assertSame([], $view['categories']);
        $this->assertSame([], $view['history']);
        $this->assertSame([], $store->asked);
    }

    #[Test]
    public function withoutAPatientNothingIsRead(): void
    {
        $store = FakeAssessmentStore::sample();
        $view = self::controller($store, pid: 0)->buildView();

        $this->assertFalse($view['hasPatient']);
        $this->assertSame([], $store->asked);
    }

    #[Test]
    public function cardsAreGroupedByCategoryAndRowsWithoutAnIdLeftOut(): void
    {
        $view = self::controller(FakeAssessmentStore::sample(), collapsed: ['questionnaire_category_unassigned_expand'])->buildView();
        $categories = $view['categories'];

        $this->assertSame(['Mental Health', 'Unassigned'], array_map(static fn(AssessmentCategory $category): string => $category->title, $categories));
        $this->assertSame([1, 2], array_map(static fn(AssessmentCard $card): int => $card->questionnaireId, $categories[0]->cards));
        $this->assertFalse($categories[0]->collapsed);
        $this->assertTrue($categories[1]->collapsed, 'the user folded the unassigned group');
        $this->assertSame('Unnamed Questionnaire', $categories[1]->cards[0]->name);
    }

    #[Test]
    public function anInProgressResponseIsContinuedAndACompletedOneStartsAgain(): void
    {
        [$phq, $gad] = self::controller(FakeAssessmentStore::sample())->buildView()['categories'][0]->cards;

        $this->assertTrue($phq->continuable);
        $this->assertSame('/interface/forms/questionnaire_assessments/native_questionnaire.php?response_id=qr-11', $phq->launchUrl);
        $this->assertSame(11, $phq->smartResponseId, 'a SMART launch continues it too');
        $this->assertSame('in-progress', $phq->latestStatus);
        $this->assertSame('badge-warning', $phq->statusClass());
        $this->assertSame('Depression screen', $phq->summary);
        $this->assertTrue($phq->sdc);

        $this->assertFalse($gad->continuable);
        $this->assertSame('/interface/forms/questionnaire_assessments/native_questionnaire.php?questionnaire_id=2', $gad->launchUrl);
        $this->assertNull($gad->smartResponseId);
        $this->assertSame('Anxiety screen', $gad->summary, 'the code display stands in for a missing description');
        $this->assertSame('2026-09-01 09:00:00', $gad->lastAssessed, 'create_time stands in for an empty last_updated');
        $this->assertFalse($gad->sdc);
    }

    #[Test]
    public function smartAppsAreOfferedOnlyWhereARuleAllowsThem(): void
    {
        $store = FakeAssessmentStore::sample();
        $view = self::controller($store)->buildView();
        [$phq, $gad] = $view['categories'][0]->cards;

        $this->assertSame(['app-1'], array_map(static fn(SmartApp $app): string => $app->clientId, $phq->smartApps));
        $this->assertSame([], $gad->smartApps);

        $smartQuestions = array_values(array_filter($store->asked, static fn(AvailabilityContext $context): bool => $context->surface === QuestionnaireSurface::Smart));
        $this->assertSame(['app-1', 'app-2'], array_map(static fn(AvailabilityContext $context): ?string => $context->clientId, $smartQuestions), 'one question per app');
        $this->assertSame(3, $smartQuestions[0]->facility);
        $this->assertSame(5, $store->asked[0]->encounter, 'the dashboard question carries the encounter');
    }

    #[Test]
    public function historyRowsOpenTheirResponseAndOfferSmartOnlyWithBothIds(): void
    {
        $rows = self::controller(FakeAssessmentStore::sample())->buildView()['history'];

        $this->assertCount(4, $rows);
        $this->assertSame('/interface/forms/questionnaire_assessments/native_questionnaire.php?response_id=qr-11', $rows[0]->editUrl);
        $this->assertTrue($rows[0]->canLaunchSmart());
        $this->assertFalse($rows[2]->canLaunchSmart(), 'GAD-7 has no SMART rule');
        $this->assertSame('', $rows[3]->editUrl, 'a row without a response id cannot be opened');
        $this->assertSame('badge-info', $rows[3]->statusClass());
        $this->assertFalse($rows[3]->canLaunchSmart());
    }

    #[Test]
    public function testLaunchesShowOnlyWithTheGlobalOnAndEncounterOnlyWhenPositive(): void
    {
        $shown = self::controller(FakeAssessmentStore::sample(), collapsed: [AssessmentPageLayout::SMART_LAUNCHES_SECTION])->buildView();
        $this->assertCount(2, $shown['smartTestApps']);
        $this->assertTrue($shown['smartLaunchesCollapsed']);
        $this->assertSame(5, $shown['currentEncounterId']);

        $hidden = self::controller(FakeAssessmentStore::sample(), showTests: false)->buildView();
        $this->assertSame([], $hidden['smartTestApps']);
    }

    #[Test]
    public function dispatchRendersTheTemplateWithTheView(): void
    {
        $controller = new AssessmentDashboardController(
            new Environment(new ArrayLoader([AssessmentDashboardController::TEMPLATE => '{{ categories|length }}/{{ history|length }}'])),
            FakeAssessmentStore::sample(),
            new AssessmentDashboardContext(true, 7, null, null, [], false, 't', [
                'canUseTemplates' => false, 'canManageAvailability' => false, 'canImport' => false,
                'templatesUrl' => '', 'availabilityUrl' => '', 'importUrl' => '',
            ], ''),
            new AssessmentPageChrome('', '', ''),
            static fn(array $sections): CollapsedSections => new CollapsedSections(),
        );

        $response = $controller->dispatchAction();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('2/4', $response->getContent());
    }
}
