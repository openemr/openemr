<?php

/**
 * Isolated tests for the questionnaire availability manager's controller.
 *
 * The service and pick lists are in-memory fakes, so these cover what the controller itself
 * decides: what the grid and panels show, and which writes a submitted form turns into.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Controllers\Interface\Super;

use OpenEMR\Controllers\Interface\Super\QuestionnaireAvailability\GridRow;
use OpenEMR\Controllers\Interface\Super\QuestionnaireAvailability\PostOutcome;
use OpenEMR\Controllers\Interface\Super\QuestionnaireAvailability\RuleForm;
use OpenEMR\Controllers\Interface\Super\QuestionnaireAvailability\RuleRow;
use OpenEMR\Controllers\Interface\Super\QuestionnaireAvailabilityController;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Questionnaire\QuestionnaireAvailabilityService;
use OpenEMR\Services\Questionnaire\QuestionnaireSurface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

#[Group('isolated')]
class QuestionnaireAvailabilityControllerTest extends TestCase
{
    private FakeAvailabilityService $service;

    private QuestionnaireAvailabilityController $controller;

    /** @var array{present: bool, value: bool} */
    private array $translationSetting;

    protected function setUp(): void
    {
        // xl() returns the source string with translation off, so no translation tables
        // are needed and assertions can name the English text.
        $globals = OEGlobalsBag::getInstance();
        $this->translationSetting = [
            'present' => $globals->has('disable_translation'),
            'value' => $globals->getBoolean('disable_translation'),
        ];
        $globals->set('disable_translation', true);

        $session = new Session(new MockArraySessionStorage());
        $session->set('csrf_private_key', 'controller-test-key');

        $this->service = FakeAvailabilityService::sample();
        $this->controller = new QuestionnaireAvailabilityController(
            $this->service,
            new FakeAvailabilityLookups(),
            new Environment(new ArrayLoader()),
            $session,
            new NullLogger(),
        );
    }

    protected function tearDown(): void
    {
        $globals = OEGlobalsBag::getInstance();
        if ($this->translationSetting['present']) {
            $globals->set('disable_translation', $this->translationSetting['value']);
        } else {
            $globals->remove('disable_translation');
        }
    }

    #[Test]
    public function gridShowsTicksAndWhyAnUntickedSurfaceIsStillOffered(): void
    {
        $phq = $this->gridRow(50);
        $cells = $this->cellsBySurface($phq);

        $this->assertTrue($cells['dashboard']->checked, 'an active unscoped rule is a tick');
        $this->assertFalse($cells['encounter']->checked, 'a switched-off unscoped rule is not');
        $this->assertTrue($cells['encounter']->limited, 'its visit-category limits still offer it');
        $this->assertTrue($cells['smart']->limited, 'its SMART app limit still offers it');
        $this->assertFalse($cells['encounter']->notRegistered, 'PHQ-9 is a registered encounter form');
        $this->assertSame(3, $phq->extraRules, 'three limited rules, one of them also carrying a permission');

        $audit = $this->cellsBySurface($this->gridRow(56));
        $this->assertTrue($audit['dashboard']->viaCategory, 'the SDOH category rule reaches it');
        $this->assertTrue($audit['encounter']->notRegistered);
        $this->assertSame(2, $this->gridRow(56)->extraRules, 'one permission override, one SMART limit');
    }

    #[Test]
    public function limitsPanelDescribesEachRuleInWords(): void
    {
        $view = $this->controller->buildView(50, null, new PostOutcome());

        $this->assertSame(50, $view['selectedId']);
        $this->assertSame('PHQ-9', $view['selectedName']);
        $rules = $this->ruleRows($view['selectedRules']);
        $this->assertCount(5, $rules);

        $byId = [];
        foreach ($rules as $rule) {
            $byId[$rule->id] = $rule;
        }
        $this->assertSame('Everywhere', $byId[1]->where);
        $this->assertSame('Default (Patients: Medical/History)', $byId[1]->permission);
        $this->assertSame('Visit category: Well Child', $byId[3]->where);
        $this->assertSame(
            'Facility: Main Clinic; Provider: #77; Visit category: Established Patient',
            $byId[4]->where,
            'a provider missing from the list is shown by id, not dropped'
        );
        $this->assertSame('Encounters: Notes', $byId[4]->permission);
        $this->assertSame('SMART app: Growth Chart', $byId[7]->where);
    }

    #[Test]
    public function editingALimitOpensItsQuestionnaireAndKeepsRemovedValuesSelectable(): void
    {
        $view = $this->controller->buildView(null, 4, new PostOutcome());

        $this->assertSame(50, $view['selectedId'], 'editing a rule opens its questionnaire');
        $form = $view['limitForm'];
        $this->assertInstanceOf(RuleForm::class, $form);
        $this->assertTrue($form->isEdit());
        $this->assertSame('encounter', $form->surface);
        $this->assertSame('encounters|notes', $form->acl);
        $this->assertSame(77, $form->provider);

        $providers = $view['providers'];
        $this->assertIsArray($providers);
        $this->assertSame(
            '#77 (inactive or removed)',
            $providers[77] ?? null,
            'otherwise the select falls back to Any and saving widens the rule'
        );
    }

    #[Test]
    public function editingAnUnlistedPermissionKeepsItLabelled(): void
    {
        $form = $this->controller->buildView(null, 6, new PostOutcome())['limitForm'];

        $this->assertInstanceOf(RuleForm::class, $form);
        $this->assertSame('patients|bogus', $form->acl);
        $this->assertSame('patients/bogus (not in the permission list)', $form->aclUnknownLabel);
    }

    #[Test]
    public function editingACategoryRuleFillsTheCategoryFormOnly(): void
    {
        $view = $this->controller->buildView(null, 5, new PostOutcome());

        $this->assertNull($view['selectedId']);
        $categoryForm = $view['categoryForm'];
        $limitForm = $view['limitForm'];
        $this->assertInstanceOf(RuleForm::class, $categoryForm);
        $this->assertInstanceOf(RuleForm::class, $limitForm);
        $this->assertSame('sdoh', $categoryForm->category);
        $this->assertFalse($limitForm->isEdit());
    }

    #[Test]
    public function anUnknownQuestionnaireOpensNoPanel(): void
    {
        $this->assertNull($this->controller->buildView(999, null, new PostOutcome())['selectedId']);
    }

    /**
     * The add form always posts empty fields: assignment_id, and every "Any" select. In
     * Symfony 7 InputBag::getInt('') throws, which is what made "Add rule" a 500.
     */
    #[Test]
    public function emptyFieldsMeanUnscopedRatherThanAnError(): void
    {
        $outcome = $this->post([
            'action' => 'save_rule',
            'target' => 'questionnaire',
            'questionnaire_id' => '57',
            'surface' => 'dashboard',
            'assignment_id' => '',
            'facility' => '',
            'provider' => '',
            'visit_category_id' => '',
            'client_id' => '',
            'acl' => '',
            'seq' => '',
        ]);

        $this->assertSame([], $outcome->errors);
        $this->assertTrue($outcome->ruleSaved);
        $this->assertCount(1, $this->service->assigned);
        $record = $this->service->assigned[0];
        $this->assertSame(0, $record->id);
        $this->assertSame(57, $record->resourceId);
        $this->assertFalse($record->isScoped());
    }

    #[Test]
    public function addingALimitCanUntickEverywhereInTheSameStep(): void
    {
        $outcome = $this->post([
            'action' => 'save_rule',
            'target' => 'questionnaire',
            'questionnaire_id' => '50',
            'surface' => 'dashboard',
            'facility' => '3',
            'only_here' => '1',
        ]);

        $this->assertSame([], $outcome->errors);
        $this->assertCount(1, $this->service->assigned);
        $this->assertSame(3, $this->service->assigned[0]->facility);
        $this->assertSame(
            [[1, false]],
            $this->service->activeChanges,
            'the dashboard "everywhere" rule is switched off, so the limit actually limits'
        );
    }

    #[Test]
    public function aLimitWithoutOnlyHereLeavesEverywhereAlone(): void
    {
        $this->post([
            'action' => 'save_rule',
            'target' => 'questionnaire',
            'questionnaire_id' => '50',
            'surface' => 'dashboard',
            'facility' => '3',
        ]);

        $this->assertSame([], $this->service->activeChanges);
    }

    #[Test]
    public function theCategoryFormNeverTargetsAQuestionnaire(): void
    {
        $this->post([
            'action' => 'save_rule',
            'target' => 'category',
            'questionnaire_id' => '50',
            'category' => 'sdoh',
            'surface' => 'portal',
        ]);

        $this->assertCount(1, $this->service->assigned);
        $this->assertNull($this->service->assigned[0]->resourceId);
        $this->assertSame('sdoh', $this->service->assigned[0]->category);
    }

    #[Test]
    public function aCategoryRuleNeedsACategory(): void
    {
        $outcome = $this->post(['action' => 'save_rule', 'target' => 'category', 'surface' => 'portal']);

        $this->assertSame(['Choose a category'], $outcome->errors);
        $this->assertSame([], $this->service->assigned);
    }

    #[Test]
    public function aMalformedPermissionIsRefused(): void
    {
        $outcome = $this->post([
            'action' => 'save_rule',
            'target' => 'questionnaire',
            'questionnaire_id' => '50',
            'surface' => 'dashboard',
            'acl' => 'patients',
        ]);

        $this->assertSame(['Choose a permission from the list'], $outcome->errors);
        $this->assertSame([], $this->service->assigned);
    }

    #[Test]
    public function serviceRefusalsAreShownInWordsNotExceptionText(): void
    {
        $this->service->refuseWith = QuestionnaireAvailabilityService::ERROR_DUPLICATE;

        $outcome = $this->post([
            'action' => 'save_rule',
            'target' => 'questionnaire',
            'questionnaire_id' => '50',
            'surface' => 'dashboard',
        ]);

        $this->assertSame(['Another rule already has exactly these limits'], $outcome->errors);
        $this->assertFalse($outcome->ruleSaved, 'a refused save stays in edit mode');
    }

    #[Test]
    public function editingARuleUpdatesItInPlace(): void
    {
        $outcome = $this->post([
            'action' => 'save_rule',
            'target' => 'questionnaire',
            'questionnaire_id' => '50',
            'assignment_id' => '3',
            'seq' => '4',
            'surface' => 'encounter',
            'visit_category_id' => '5',
        ]);

        $this->assertSame(['Rule updated'], $outcome->notices);
        $this->assertSame([], $this->service->assigned);
        $this->assertCount(1, $this->service->updated);
        $this->assertSame(3, $this->service->updated[0]->id);
        $this->assertSame(4, $this->service->updated[0]->seq);
        $this->assertSame(5, $this->service->updated[0]->visitCategoryId);
    }

    #[Test]
    public function gridSaveWritesOnlyBoxesThatChanged(): void
    {
        $outcome = $this->post([
            'action' => 'save_grid',
            'rendered' => ['50|dashboard', '50|portal', '50|encounter', '56|portal', '57|dashboard'],
            'offer' => [
                '50' => ['dashboard' => '1', 'portal' => '1'],
            ],
        ]);

        $this->assertCount(1, $this->service->assigned, 'only 50 on the portal is new');
        $this->assertSame(50, $this->service->assigned[0]->resourceId);
        $this->assertSame(QuestionnaireSurface::Portal, $this->service->assigned[0]->surface);
        $this->assertSame([[6, false]], $this->service->activeChanges, 'AUDIT-C unticked on the portal');
        $this->assertSame(['Saved 2 change(s)'], $outcome->notices);
    }

    #[Test]
    public function switchingAndRemovingARuleAddressItById(): void
    {
        $this->post(['action' => 'toggle_rule', 'assignment_id' => '3', 'to' => 'off']);
        $this->post(['action' => 'delete_rule', 'assignment_id' => '8']);
        $this->post(['action' => 'delete_rule', 'assignment_id' => '']);

        $this->assertSame([[3, false]], $this->service->activeChanges);
        $this->assertSame([8], $this->service->deleted, 'an empty id deletes nothing');
    }

    /**
     * @param array<string, mixed> $fields
     */
    private function post(array $fields): PostOutcome
    {
        return $this->controller->handlePost(Request::create('/', 'POST', $fields));
    }

    private function gridRow(int $questionnaireId): GridRow
    {
        $rows = $this->controller->buildView(null, null, new PostOutcome())['gridRows'];
        $this->assertIsArray($rows);
        foreach ($rows as $row) {
            if ($row instanceof GridRow && $row->id === $questionnaireId) {
                return $row;
            }
        }
        $this->fail('no grid row for questionnaire ' . $questionnaireId);
    }

    /**
     * @return array<string, \OpenEMR\Controllers\Interface\Super\QuestionnaireAvailability\GridCell>
     */
    private function cellsBySurface(GridRow $row): array
    {
        $cells = [];
        foreach ($row->cells as $index => $cell) {
            $cells[QuestionnaireSurface::cases()[$index]->value] = $cell;
        }

        return $cells;
    }

    /**
     * @return list<RuleRow>
     */
    private function ruleRows(mixed $rows): array
    {
        $this->assertIsArray($rows);
        $typed = [];
        foreach ($rows as $row) {
            $this->assertInstanceOf(RuleRow::class, $row);
            $typed[] = $row;
        }

        return $typed;
    }
}
