<?php

/**
 * Questionnaire availability management.
 *
 * Which questionnaires are offered on which surface -- patient dashboard, encounter
 * forms, patient portal, SMART launch -- is stored in `content_assignments` and answered
 * by QuestionnaireAvailabilityService. The screen edits those rows in three places, each
 * answering one question an administrator actually asks:
 *
 *  - Questionnaires: "where is this offered?" One tick per surface, meaning offered
 *    everywhere on that surface. This is the common case and needs nothing else.
 *  - Limits for one questionnaire: "only at this facility / for this kind of visit / for
 *    this SMART app", and who may see it. Opened from the questionnaire's row.
 *  - Category rules: "every questionnaire in this category", including ones imported
 *    later. A separate list, because it targets a category rather than a questionnaire.
 *
 * Rules add up: a questionnaire is offered on a surface while any active rule for it, or
 * for its category, matches. The grid marks a surface that is also reached by a limit or
 * a category rule, so unticking a box that changes nothing is explained, not a mystery.
 *
 * This class parses the request, applies changes through the service, and builds the view;
 * the markup is templates/super/questionnaire_availability.html.twig.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\Super;

use InvalidArgumentException;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Controllers\Interface\Super\QuestionnaireAvailability\GridCell;
use OpenEMR\Controllers\Interface\Super\QuestionnaireAvailability\GridRow;
use OpenEMR\Controllers\Interface\Super\QuestionnaireAvailability\PostOutcome;
use OpenEMR\Controllers\Interface\Super\QuestionnaireAvailability\RuleDescriber;
use OpenEMR\Controllers\Interface\Super\QuestionnaireAvailability\RuleForm;
use OpenEMR\Controllers\Interface\Super\QuestionnaireAvailability\SurfaceOption;
use OpenEMR\Services\Questionnaire\AssignmentRecord;
use OpenEMR\Services\Questionnaire\QuestionnaireAvailabilityLookups;
use OpenEMR\Services\Questionnaire\QuestionnaireAvailabilityService;
use OpenEMR\Services\Questionnaire\QuestionnaireSurface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Environment;

class QuestionnaireAvailabilityController
{
    public const TEMPLATE = 'super/questionnaire_availability.html.twig';

    public function __construct(
        private readonly QuestionnaireAvailabilityService $service,
        private readonly QuestionnaireAvailabilityLookups $lookups,
        private readonly Environment $twig,
        private readonly SessionInterface $session,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function dispatchAction(Request $request): Response
    {
        if (!AclMain::aclCheckCore('admin', 'super')) {
            AccessDeniedHelper::denyWithTemplate(
                'ACL check failed for admin/super: Questionnaire Availability',
                xl('Questionnaire Availability')
            );
        }

        $outcome = new PostOutcome();
        if ($request->getMethod() === 'POST') {
            if (!CsrfUtils::verifyCsrfToken($request->request->getString('csrf_token_form'), $this->session)) {
                CsrfUtils::csrfNotVerified();
            }
            $outcome = $this->handlePost($request);
        }

        // A form posts back to its own ?q=, so the open panel stays open after a save. A save
        // that succeeded leaves edit mode; a refused one stays in it so the error sits next to
        // the rule being edited.
        $view = $this->buildView(
            self::positiveId($request->query, 'q'),
            $outcome->ruleSaved ? null : self::positiveId($request->query, 'edit'),
            $outcome
        );

        return new Response(
            $this->twig->render(self::TEMPLATE, $view),
            Response::HTTP_OK,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }

    /**
     * Apply one submitted change. The CSRF token has already been checked.
     */
    public function handlePost(Request $request): PostOutcome
    {
        $action = $request->request->getString('action');

        try {
            return match ($action) {
                'save_grid' => $this->saveGrid($request->request),
                'save_rule' => $this->saveRule($request->request),
                'toggle_rule' => $this->toggleRule($request->request),
                'delete_rule' => $this->deleteRule($request->request),
                default => new PostOutcome(),
            };
        } catch (InvalidArgumentException $e) {
            // The service's refusals are identified by code and shown in translated words,
            // never by echoing the exception message.
            $this->logger->info('Questionnaire availability change refused', ['action' => $action, 'exception' => $e]);

            return new PostOutcome(errors: [self::refusalMessage($e->getCode())]);
        } catch (SqlQueryException $e) {
            // A failed write is the one thing this page can recover from: report it and keep
            // the screen usable. Narrowed to SqlQueryException because ForbiddenCatchTypeRule
            // bars suppressing Error and ErrorException, and rightly -- a TypeError here is a
            // bug to surface, not a message to show an administrator.
            $this->logger->error('Questionnaire availability update failed', ['action' => $action, 'exception' => $e]);

            return new PostOutcome(errors: [xl('The change could not be saved. See the error log for details.')]);
        }
    }

    /**
     * Everything the template needs, as plain values and view objects.
     *
     * @param ?int $selectedId questionnaire whose limits panel is open
     * @param ?int $editId rule open in a form
     * @return array<string, mixed>
     */
    public function buildView(?int $selectedId, ?int $editId, PostOutcome $outcome): array
    {
        $questionnaires = $this->service->listManageableQuestionnaires();
        $assignments = $this->service->listAssignments();

        $questionnaireNames = [];
        foreach ($questionnaires as $questionnaire) {
            $questionnaireNames[$questionnaire['id']] = $questionnaire['name'];
        }

        $editing = null;
        if ($editId !== null) {
            foreach ($assignments as $candidate) {
                if ($candidate->id === $editId) {
                    $editing = $candidate;
                    break;
                }
            }
        }
        // Editing a questionnaire's rule opens that questionnaire's panel.
        if ($editing !== null && $editing->resourceId !== null) {
            $selectedId = $editing->resourceId;
        }
        if ($selectedId !== null && !isset($questionnaireNames[$selectedId])) {
            $selectedId = null;
        }

        $facilities = $this->lookups->facilities();
        $providers = $this->lookups->providers();
        $visitCategories = $this->lookups->visitCategories();
        $smartApps = $this->lookups->smartApps();
        $categoryTitles = $this->lookups->categoryTitles();
        $permissionChoices = $this->lookups->permissionChoices();
        $encounterFormIds = $this->lookups->encounterFormIds();

        $permissionLabels = [];
        foreach ($permissionChoices as $sectionLabel => $levels) {
            foreach ($levels as $pair => $levelLabel) {
                $permissionLabels[$pair] = $sectionLabel . ': ' . $levelLabel;
            }
        }

        // Categories a questionnaire or a rule already carries but the list lacks stay
        // selectable, so existing data does not silently disappear from the form.
        foreach ($questionnaires as $questionnaire) {
            if ($questionnaire['category'] !== '') {
                $categoryTitles[$questionnaire['category']] ??= $questionnaire['category'];
            }
        }

        // What the grid shows. A tick is an active unscoped rule; the rest of a
        // questionnaire's rules are counted so the grid can say a surface is also reached
        // another way.
        $gridState = [];
        $limitCounts = [];
        $ruleCounts = [];
        $categoryReach = [];
        $selectedRecords = [];
        $categoryRecords = [];
        foreach ($assignments as $assignment) {
            if ($assignment->isCategoryWide()) {
                $categoryRecords[] = $assignment;
                $ruleCategory = (string)$assignment->category;
                $categoryTitles[$ruleCategory] ??= $ruleCategory;
                if ($assignment->active) {
                    $categoryReach[$ruleCategory . '|' . $assignment->surface->value] = true;
                }
                continue;
            }
            if ($assignment->resourceId === null) {
                continue;
            }
            $resourceId = $assignment->resourceId;
            $key = $resourceId . '|' . $assignment->surface->value;
            if ($resourceId === $selectedId) {
                $selectedRecords[] = $assignment;
            }
            if ($assignment->isScoped()) {
                $ruleCounts[$resourceId] = ($ruleCounts[$resourceId] ?? 0) + 1;
                if ($assignment->active) {
                    $limitCounts[$key] = ($limitCounts[$key] ?? 0) + 1;
                }
                continue;
            }
            if ($assignment->aclSection !== null) {
                $ruleCounts[$resourceId] = ($ruleCounts[$resourceId] ?? 0) + 1;
            }
            $gridState[$key] = $assignment->active;
        }

        // A rule can point at a provider who has since been deactivated, or a facility,
        // visit category or SMART app that was removed. Keep the edited value selectable;
        // otherwise its select falls back to "Any" and saving the edit widens the rule.
        $inactive = ' ' . xl('(inactive or removed)');
        if ($editing !== null) {
            if ($editing->facility !== null && !isset($facilities[$editing->facility])) {
                $facilities[$editing->facility] = '#' . $editing->facility . $inactive;
            }
            if ($editing->provider !== null && !isset($providers[$editing->provider])) {
                $providers[$editing->provider] = '#' . $editing->provider . $inactive;
            }
            if ($editing->visitCategoryId !== null && !isset($visitCategories[$editing->visitCategoryId])) {
                $visitCategories[$editing->visitCategoryId] = '#' . $editing->visitCategoryId . $inactive;
            }
        }

        $surfaceLabels = self::surfaceLabels();
        $surfaces = [];
        foreach (QuestionnaireSurface::cases() as $surface) {
            $surfaces[] = new SurfaceOption(
                value: $surface->value,
                label: $surfaceLabels[$surface->value],
                scopesJson: json_encode($surface->supportedScopes(), JSON_THROW_ON_ERROR),
                hint: self::scopeHint($surface),
                isSmart: $surface === QuestionnaireSurface::Smart,
            );
        }

        $gridRows = [];
        foreach ($questionnaires as $questionnaire) {
            $questionnaireId = $questionnaire['id'];
            $categoryLabel = $questionnaire['category'] !== ''
                ? ($categoryTitles[$questionnaire['category']] ?? $questionnaire['category'])
                : '';
            $cells = [];
            foreach (QuestionnaireSurface::cases() as $surface) {
                $key = $questionnaireId . '|' . $surface->value;
                $cells[] = new GridCell(
                    key: $key,
                    fieldName: 'offer[' . $questionnaireId . '][' . $surface->value . ']',
                    ariaLabel: $questionnaire['name'] . ': ' . $surfaceLabels[$surface->value],
                    checked: $gridState[$key] ?? false,
                    limited: ($limitCounts[$key] ?? 0) > 0,
                    viaCategory: $questionnaire['category'] !== ''
                        && isset($categoryReach[$questionnaire['category'] . '|' . $surface->value]),
                    notRegistered: $surface === QuestionnaireSurface::Encounter
                        && !isset($encounterFormIds[$questionnaireId]),
                );
            }
            $gridRows[] = new GridRow(
                id: $questionnaireId,
                name: $questionnaire['name'],
                categoryLabel: $categoryLabel,
                filterText: mb_strtolower($questionnaire['name'] . ' ' . $categoryLabel),
                selected: $questionnaireId === $selectedId,
                extraRules: $ruleCounts[$questionnaireId] ?? 0,
                cells: $cells,
            );
        }

        $describe = new RuleDescriber(
            $surfaceLabels,
            $facilities,
            $providers,
            $visitCategories,
            $smartApps,
            $permissionLabels,
        );

        $selectedRules = [];
        foreach ($selectedRecords as $record) {
            $selectedRules[] = $describe->row($record, $editing?->id, null);
        }
        $categoryRules = [];
        foreach ($categoryRecords as $record) {
            $ruleCategory = (string)$record->category;
            $categoryRules[] = $describe->row($record, $editing?->id, $categoryTitles[$ruleCategory] ?? $ruleCategory);
        }

        $editingLimit = $editing !== null && !$editing->isCategoryWide() ? $editing : null;
        $editingCategoryRule = $editing !== null && $editing->isCategoryWide() ? $editing : null;

        return [
            'notices' => $outcome->notices,
            'errors' => $outcome->errors,
            'csrfToken' => CsrfUtils::collectCsrfToken($this->session),
            'surfaces' => $surfaces,
            'gridRows' => $gridRows,
            'selectedId' => $selectedId,
            'selectedName' => $selectedId !== null ? $questionnaireNames[$selectedId] : '',
            'selectedRules' => $selectedRules,
            'categoryRules' => $categoryRules,
            'limitForm' => self::ruleForm('limit', $editingLimit, $permissionLabels),
            'categoryForm' => self::ruleForm('category', $editingCategoryRule, $permissionLabels),
            'facilities' => $facilities,
            'providers' => $providers,
            'visitCategories' => $visitCategories,
            'smartApps' => $smartApps,
            'categories' => $categoryTitles,
            'permissionChoices' => $permissionChoices,
        ];
    }

    /**
     * The grid posts a checkbox per questionnaire per surface. An unchecked box sends
     * nothing, so the authority is the full set of boxes rendered, not the keys that came
     * back. Only unscoped questionnaire rules are touched: a tick cannot express a limit.
     *
     * @param InputBag<string> $post
     */
    private function saveGrid(InputBag $post): PostOutcome
    {
        $checked = [];
        foreach ($post->all('offer') as $questionnaireKey => $surfaces) {
            if (!is_array($surfaces) || !is_numeric($questionnaireKey)) {
                continue;
            }
            foreach (array_keys($surfaces) as $surfaceKey) {
                if (is_string($surfaceKey)) {
                    $checked[$questionnaireKey . '|' . $surfaceKey] = true;
                }
            }
        }

        $unscopedByKey = [];
        foreach ($this->service->listAssignments() as $existing) {
            if ($existing->isCategoryWide() || $existing->isScoped() || $existing->resourceId === null) {
                continue;
            }
            $unscopedByKey[$existing->resourceId . '|' . $existing->surface->value] = $existing;
        }

        $changes = 0;
        foreach ($post->all('rendered') as $key) {
            if (!is_string($key) || !str_contains($key, '|')) {
                continue;
            }
            [$rawId, $rawSurface] = explode('|', $key, 2);
            $questionnaireId = preg_match('/^\d+$/', $rawId) === 1 ? (int)$rawId : 0;
            $surface = QuestionnaireSurface::tryFrom($rawSurface);
            if ($questionnaireId <= 0 || $surface === null) {
                continue;
            }

            $canonicalKey = $questionnaireId . '|' . $surface->value;
            $wanted = isset($checked[$canonicalKey]);
            $existing = $unscopedByKey[$canonicalKey] ?? null;

            if ($existing === null) {
                if ($wanted) {
                    $this->service->assign(new AssignmentRecord(id: 0, surface: $surface, resourceId: $questionnaireId));
                    $changes++;
                }
                continue;
            }
            if ($existing->active !== $wanted) {
                $this->service->setAssignmentActive($existing->id, $wanted);
                $changes++;
            }
        }

        return new PostOutcome(notices: [
            $changes === 0 ? xl('No changes to save') : sprintf(xl('Saved %d change(s)'), $changes),
        ]);
    }

    /**
     * One handler for both rule forms. The limits form names a questionnaire; the category
     * form names a category. Neither offers the other's target, so a rule cannot be saved
     * with both or with the wrong one.
     *
     * @param InputBag<string> $post
     */
    private function saveRule(InputBag $post): PostOutcome
    {
        $surface = QuestionnaireSurface::tryFrom($post->getString('surface'));
        $target = $post->getString('target');
        $category = $target === 'category' ? self::text($post, 'category') : null;

        if ($surface === null) {
            return new PostOutcome(errors: [xl('Choose where it is offered')]);
        }
        if ($target === 'category' && $category === null) {
            return new PostOutcome(errors: [xl('Choose a category')]);
        }

        // The permission is picked as one "section|level" value, so the form cannot post half
        // a pair. A malformed value is refused rather than guessed at.
        $aclSection = null;
        $aclLevel = null;
        $aclChoice = self::text($post, 'acl');
        if ($aclChoice !== null) {
            $separator = strpos($aclChoice, '|');
            if ($separator === false || $separator === 0 || $separator === strlen($aclChoice) - 1) {
                return new PostOutcome(errors: [xl('Choose a permission from the list')]);
            }
            $aclSection = substr($aclChoice, 0, $separator);
            $aclLevel = substr($aclChoice, $separator + 1);
        }

        // A limit the chosen surface cannot apply is disabled in the form and so not posted.
        // If one arrives anyway, the service refuses it.
        $record = new AssignmentRecord(
            id: self::positiveId($post, 'assignment_id') ?? 0,
            surface: $surface,
            resourceId: $target === 'questionnaire' ? self::positiveId($post, 'questionnaire_id') : null,
            category: $category,
            facility: self::positiveId($post, 'facility'),
            provider: self::positiveId($post, 'provider'),
            visitCategoryId: self::positiveId($post, 'visit_category_id'),
            clientId: self::text($post, 'client_id'),
            aclSection: $aclSection,
            aclLevel: $aclLevel,
            seq: self::positiveId($post, 'seq') ?? 0,
        );

        if ($record->id > 0) {
            $this->service->updateAssignment($record);

            return new PostOutcome(notices: [xl('Rule updated')], ruleSaved: true);
        }

        $this->service->assign($record);
        $notices = [$record->isScoped() ? xl('Limit added') : xl('Rule saved')];

        // Rules add up, so a limit on its own changes nothing while the questionnaire is
        // still ticked for the whole surface. "Only where this limit applies" switches that
        // tick off in the same step.
        if ($record->isScoped() && $record->resourceId !== null && $post->getString('only_here') === '1') {
            foreach ($this->service->listAssignments($surface) as $existing) {
                if ($existing->resourceId === $record->resourceId && !$existing->isScoped() && $existing->active) {
                    $this->service->setAssignmentActive($existing->id, false);
                    $notices[] = xl('No longer offered everywhere on that surface, only where its limits apply');
                }
            }
        }

        return new PostOutcome(notices: $notices, ruleSaved: true);
    }

    /**
     * @param InputBag<string> $post
     */
    private function toggleRule(InputBag $post): PostOutcome
    {
        $id = self::positiveId($post, 'assignment_id');
        if ($id === null) {
            return new PostOutcome();
        }
        $this->service->setAssignmentActive($id, $post->getString('to') === 'on');

        return new PostOutcome(notices: [xl('Rule updated')]);
    }

    /**
     * @param InputBag<string> $post
     */
    private function deleteRule(InputBag $post): PostOutcome
    {
        $id = self::positiveId($post, 'assignment_id');
        if ($id === null) {
            return new PostOutcome();
        }
        $this->service->deleteAssignment($id);

        return new PostOutcome(notices: [xl('Rule removed')]);
    }

    /**
     * The form's starting values: the stored rule when editing, empty when adding.
     *
     * @param array<string, string> $permissionLabels
     */
    private static function ruleForm(string $prefix, ?AssignmentRecord $rule, array $permissionLabels): RuleForm
    {
        if ($rule === null) {
            return new RuleForm(prefix: $prefix);
        }
        $acl = $rule->aclSection !== null && $rule->aclLevel !== null
            ? $rule->aclSection . '|' . $rule->aclLevel
            : null;

        return new RuleForm(
            prefix: $prefix,
            assignmentId: $rule->id,
            seq: $rule->seq,
            surface: $rule->surface->value,
            acl: $acl,
            aclUnknownLabel: $acl !== null && !isset($permissionLabels[$acl])
                ? sprintf(xl('%s (not in the permission list)'), str_replace('|', '/', $acl))
                : null,
            facility: $rule->facility,
            provider: $rule->provider,
            visitCategory: $rule->visitCategoryId,
            clientId: $rule->clientId !== null && $rule->clientId !== '' ? $rule->clientId : null,
            category: $rule->isCategoryWide() ? $rule->category : null,
        );
    }

    /**
     * @return array<string, string> surface value => label
     */
    private static function surfaceLabels(): array
    {
        $labels = [];
        foreach (QuestionnaireSurface::cases() as $surface) {
            $labels[$surface->value] = match ($surface) {
                QuestionnaireSurface::Dashboard => xl('Patient dashboard'),
                QuestionnaireSurface::Encounter => xl('Encounter forms'),
                QuestionnaireSurface::Portal => xl('Patient portal'),
                QuestionnaireSurface::Smart => xl('SMART launch'),
            };
        }

        return $labels;
    }

    /**
     * One line telling an administrator what a surface can be limited by.
     */
    private static function scopeHint(QuestionnaireSurface $surface): string
    {
        $names = [];
        foreach ($surface->supportedScopes() as $column) {
            $names[] = RuleDescriber::scopeLabel($column);
        }
        $label = self::surfaceLabels()[$surface->value];

        return $names === []
            ? sprintf(xl('%s cannot be limited yet.'), $label)
            : sprintf(xl('%s can be limited by %s.'), $label, implode(', ', $names));
    }

    private static function refusalMessage(mixed $code): string
    {
        return match ($code) {
            QuestionnaireAvailabilityService::ERROR_NO_TARGET => xl('Choose a questionnaire or a category'),
            QuestionnaireAvailabilityService::ERROR_HALF_ACL => xl('Choose a permission from the list'),
            QuestionnaireAvailabilityService::ERROR_UNSUPPORTED_SCOPE
                => xl('That surface cannot be limited by one of the chosen fields. Clear the fields the form disables for it.'),
            QuestionnaireAvailabilityService::ERROR_DUPLICATE => xl('Another rule already has exactly these limits'),
            QuestionnaireAvailabilityService::ERROR_MISSING => xl('That rule no longer exists'),
            default => xl('The rule could not be saved'),
        };
    }

    /**
     * A positive integer field, or null for "not supplied".
     *
     * Not InputBag::getInt(): in Symfony 7 it throws BadRequestException for a field that is
     * present but empty, and these forms always post empty fields -- assignment_id on an add,
     * and every "Any" select. FILTER_NULL_ON_FAILURE turns "", non-numeric input and an
     * absent field alike into null.
     *
     * @param InputBag<string> $bag
     */
    private static function positiveId(InputBag $bag, string $field): ?int
    {
        $value = $bag->filter($field, null, FILTER_VALIDATE_INT, ['flags' => FILTER_NULL_ON_FAILURE]);

        return is_int($value) && $value > 0 ? $value : null;
    }

    /**
     * A trimmed text field, or null when empty -- which is how every optional column on
     * content_assignments spells "unscoped on this axis".
     *
     * @param InputBag<string> $bag
     */
    private static function text(InputBag $bag, string $field): ?string
    {
        $trimmed = trim($bag->getString($field));

        return $trimmed === '' ? null : $trimmed;
    }
}
