<?php

/**
 * Questionnaire availability management.
 *
 * Which questionnaires are offered on which surface -- patient dashboard, encounter
 * forms, patient portal, SMART launch -- is stored in `content_assignments` and answered
 * by QuestionnaireAvailabilityService. This screen edits those rows in three places, each
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
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once('../globals.php');

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\FHIR\SMART\SmartLaunchController;
use OpenEMR\Services\Questionnaire\AssignmentRecord;
use OpenEMR\Services\Questionnaire\QuestionnaireAvailabilityService;
use OpenEMR\Services\Questionnaire\QuestionnaireSurface;

if (!AclMain::aclCheckCore('admin', 'super')) {
    AccessDeniedHelper::denyWithTemplate(
        'ACL check failed for admin/super: Questionnaire Availability',
        xl('Questionnaire Availability')
    );
}

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$service = new QuestionnaireAvailabilityService();
// Read input through the Request object rather than the superglobals: the bag getters
// return honest scalar types, so nothing downstream has to re-narrow them.
$request = CurrentRequest::get();

/**
 * A posted positive integer, or null for "not supplied".
 *
 * Not InputBag::getInt(): in Symfony 7 it throws BadRequestException for a field that is
 * posted but empty, and this page always posts empty fields -- assignment_id on an add,
 * and every "Any" select. FILTER_NULL_ON_FAILURE turns "", non-numeric input and an
 * absent field alike into null.
 */
$postedId = static function (string $field) use ($request): ?int {
    $value = $request->request->filter($field, null, FILTER_VALIDATE_INT, ['flags' => FILTER_NULL_ON_FAILURE]);

    return is_int($value) && $value > 0 ? $value : null;
};
// The same, for the query string: ?q= or ?edit=abc must not take the page down.
$queryId = static function (string $field) use ($request): ?int {
    $value = $request->query->filter($field, null, FILTER_VALIDATE_INT, ['flags' => FILTER_NULL_ON_FAILURE]);

    return is_int($value) && $value > 0 ? $value : null;
};

/**
 * A posted string, trimmed, or null when it is empty -- which is how every optional
 * column on content_assignments spells "unscoped on this axis".
 */
$postedText = static function (string $field) use ($request): ?string {
    $trimmed = trim($request->request->getString($field));

    return $trimmed === '' ? null : $trimmed;
};

$notices = [];
$errors = [];
$ruleSaved = false;
// The questionnaire whose limits are open. Its forms post back to the same ?q=, so the
// panel stays open after a save.
$selectedId = $queryId('q');

if ($request->getMethod() === 'POST') {
    CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);

    $action = $request->request->getString('action');

    try {
        if ($action === 'save_grid') {
            // The grid posts a checkbox per questionnaire per surface. An unchecked box
            // sends nothing, so the authority is the full set of questionnaires and
            // surfaces rendered, not the keys that came back.
            $checked = [];
            foreach ($request->request->all('offer') as $questionnaireKey => $surfaces) {
                if (!is_array($surfaces)) {
                    continue;
                }
                $questionnaireId = is_numeric($questionnaireKey) ? (int)$questionnaireKey : 0;
                if ($questionnaireId <= 0) {
                    continue;
                }
                foreach (array_keys($surfaces) as $surfaceKey) {
                    if (is_string($surfaceKey)) {
                        $checked[$questionnaireId . '|' . $surfaceKey] = true;
                    }
                }
            }

            $rendered = $request->request->all('rendered');
            $changes = 0;
            if ($rendered !== []) {
                // Index the unscoped rows once; a limited or category row is never
                // touched by the grid, because a tick cannot express what it is limited to.
                $unscopedByKey = [];
                foreach ($service->listAssignments() as $existing) {
                    if ($existing->isCategoryWide() || $existing->isScoped() || $existing->resourceId === null) {
                        continue;
                    }
                    $unscopedByKey[$existing->resourceId . '|' . $existing->surface->value] = $existing;
                }

                foreach ($rendered as $key) {
                    if (!is_string($key) || !str_contains($key, '|')) {
                        continue;
                    }
                    [$rawId, $rawSurface] = explode('|', $key, 2);
                    $questionnaireId = is_numeric($rawId) ? (int)$rawId : 0;
                    $surface = QuestionnaireSurface::tryFrom($rawSurface);
                    if ($questionnaireId <= 0 || $surface === null) {
                        continue;
                    }

                    $wanted = isset($checked[$questionnaireId . '|' . $surface->value]);
                    $existing = $unscopedByKey[$questionnaireId . '|' . $surface->value] ?? null;

                    if ($existing === null) {
                        if ($wanted) {
                            $service->assign(new AssignmentRecord(
                                id: 0,
                                surface: $surface,
                                resourceId: $questionnaireId,
                            ));
                            $changes++;
                        }
                        continue;
                    }
                    if ($existing->active !== $wanted) {
                        $service->setAssignmentActive($existing->id, $wanted);
                        $changes++;
                    }
                }
            }

            $notices[] = $changes === 0
                ? xl('No changes to save')
                : sprintf(xl('Saved %d change(s)'), $changes);
        } elseif ($action === 'save_rule') {
            // One handler for both rule forms. The limits form names a questionnaire; the
            // category form names a category. Neither form offers the other's target, so a
            // rule can no longer be saved with both or with the wrong one.
            $surface = QuestionnaireSurface::tryFrom($request->request->getString('surface'));
            $target = $request->request->getString('target');
            $questionnaireId = $target === 'questionnaire' ? $postedId('questionnaire_id') : null;
            $category = $target === 'category' ? $postedText('category') : null;

            // The permission is picked as one "section|level" value, so the form cannot
            // post half a pair. A malformed value is refused rather than guessed at.
            $aclSection = null;
            $aclLevel = null;
            $aclValid = true;
            $aclChoice = $postedText('acl');
            if ($aclChoice !== null) {
                $separator = strpos($aclChoice, '|');
                if ($separator === false || $separator === 0 || $separator === strlen($aclChoice) - 1) {
                    $aclValid = false;
                } else {
                    $aclSection = substr($aclChoice, 0, $separator);
                    $aclLevel = substr($aclChoice, $separator + 1);
                }
            }

            if ($surface === null) {
                $errors[] = xl('Choose where it is offered');
            } elseif ($target === 'category' && $category === null) {
                $errors[] = xl('Choose a category');
            } elseif (!$aclValid) {
                $errors[] = xl('Choose a permission from the list');
            } else {
                // A limit the chosen surface cannot apply is disabled in the form and so
                // not posted. If one arrives anyway, the service refuses it.
                $record = new AssignmentRecord(
                    id: $postedId('assignment_id') ?? 0,
                    surface: $surface,
                    resourceId: $questionnaireId,
                    category: $category,
                    facility: $postedId('facility'),
                    provider: $postedId('provider'),
                    visitCategoryId: $postedId('visit_category_id'),
                    clientId: $postedText('client_id'),
                    aclSection: $aclSection,
                    aclLevel: $aclLevel,
                    seq: $postedId('seq') ?? 0,
                );

                if ($record->id > 0) {
                    $service->updateAssignment($record);
                    $notices[] = xl('Rule updated');
                } else {
                    $service->assign($record);
                    $notices[] = $record->isScoped() ? xl('Limit added') : xl('Rule saved');

                    // Rules add up, so a limit on its own changes nothing while the
                    // questionnaire is still ticked for the whole surface. "Only where this
                    // limit applies" switches that tick off in the same step.
                    if (
                        $record->isScoped()
                        && $record->resourceId !== null
                        && $request->request->getString('only_here') === '1'
                    ) {
                        foreach ($service->listAssignments($surface) as $existing) {
                            if (
                                $existing->resourceId === $record->resourceId
                                && !$existing->isScoped()
                                && $existing->active
                            ) {
                                $service->setAssignmentActive($existing->id, false);
                                $notices[] = xl('No longer offered everywhere on that surface, only where its limits apply');
                            }
                        }
                    }
                }
                // Saved, so leave edit mode; a failed save stays in it so the error is
                // shown next to the rule being edited.
                $ruleSaved = true;
            }
        } elseif ($action === 'toggle_rule') {
            $id = $postedId('assignment_id');
            if ($id !== null) {
                $service->setAssignmentActive($id, $request->request->getString('to') === 'on');
                $notices[] = xl('Rule updated');
            }
        } elseif ($action === 'delete_rule') {
            $id = $postedId('assignment_id');
            if ($id !== null) {
                $service->deleteAssignment($id);
                $notices[] = xl('Rule removed');
            }
        }
    } catch (\InvalidArgumentException $e) {
        // The service's refusals are identified by code and shown in translated words,
        // never by echoing the exception message.
        $errors[] = match ($e->getCode()) {
            QuestionnaireAvailabilityService::ERROR_NO_TARGET => xl('Choose a questionnaire or a category'),
            QuestionnaireAvailabilityService::ERROR_HALF_ACL => xl('Choose a permission from the list'),
            QuestionnaireAvailabilityService::ERROR_UNSUPPORTED_SCOPE
                => xl('That surface cannot be limited by one of the chosen fields. Clear the fields the form disables for it.'),
            QuestionnaireAvailabilityService::ERROR_DUPLICATE
                => xl('Another rule already has exactly these limits'),
            QuestionnaireAvailabilityService::ERROR_MISSING => xl('That rule no longer exists'),
            default => xl('The rule could not be saved'),
        };
        ServiceContainer::getLogger()->info(
            'Questionnaire availability change refused',
            ['action' => $action, 'exception' => $e]
        );
    } catch (SqlQueryException $e) {
        // A failed write is the one thing this page can recover from: report it and keep
        // the screen usable. Narrowed to SqlQueryException because ForbiddenCatchTypeRule
        // bars suppressing Error and ErrorException, and rightly -- a TypeError here is a
        // bug to surface, not a message to show an administrator.
        ServiceContainer::getLogger()->error(
            'Questionnaire availability update failed',
            ['action' => $action, 'exception' => $e]
        );
        $errors[] = xl('The change could not be saved. See the error log for details.');
    }
}

$questionnaires = $service->listManageableQuestionnaires();
$assignments = $service->listAssignments();

$questionnaireNames = [];
foreach ($questionnaires as $questionnaire) {
    $questionnaireNames[$questionnaire['id']] = $questionnaire['name'];
}

// The rule being edited, if any, addressed by id so a failed save returns to it. Editing
// a questionnaire's rule opens that questionnaire's panel.
$editing = null;
$editId = $ruleSaved ? null : $queryId('edit');
if ($editId !== null) {
    foreach ($assignments as $candidate) {
        if ($candidate->id === $editId) {
            $editing = $candidate;
            break;
        }
    }
}
if ($editing !== null && $editing->resourceId !== null) {
    $selectedId = $editing->resourceId;
}
if ($selectedId !== null && !isset($questionnaireNames[$selectedId])) {
    $selectedId = null;
}
$editingCategoryRule = $editing !== null && $editing->isCategoryWide() ? $editing : null;
$editingLimit = $editing !== null && !$editing->isCategoryWide() ? $editing : null;

$pageUrl = 'manage_questionnaire_availability.php';
$selectedUrl = $selectedId !== null ? $pageUrl . '?q=' . $selectedId : $pageUrl;

/**
 * `selected` when an option matches the value being edited.
 */
$selectedIf = static fn(string $optionValue, ?string $current): string
    => $current !== null && $optionValue === $current ? 'selected' : '';

// What the grid shows. A tick is an active unscoped rule; the rest of a questionnaire's
// rules are counted so the grid can say the surface is also reached another way.
/** @var array<string, bool> $gridState */
$gridState = [];
/** @var array<string, int> $limitCounts "questionnaire|surface" => active limited rules */
$limitCounts = [];
/** @var array<int, int> $ruleCounts questionnaire => rules beyond a plain tick */
$ruleCounts = [];
/** @var array<string, true> $categoryReach "category|surface" => an active category rule */
$categoryReach = [];
$selectedRules = [];
$categoryRules = [];

foreach ($assignments as $assignment) {
    if ($assignment->isCategoryWide()) {
        $categoryRules[] = $assignment;
        if ($assignment->active) {
            $categoryReach[$assignment->category . '|' . $assignment->surface->value] = true;
        }
        continue;
    }
    if ($assignment->resourceId === null) {
        continue;
    }
    $resourceId = $assignment->resourceId;
    $key = $resourceId . '|' . $assignment->surface->value;
    if ($resourceId === $selectedId) {
        $selectedRules[] = $assignment;
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

/**
 * Database rows come back as mixed; narrow them here so the markup renders declared
 * strings instead of casting mixed at every option.
 *
 * Each row is narrowed with is_array() rather than by the @param alone: a docblock on a
 * closure assignment is not load-bearing, so the guard is what actually makes $row an
 * array to the analyser and to PHP. A row whose id is not integer-shaped is dropped
 * rather than coerced -- a lookup table with a malformed key should lose that one option,
 * not silently offer option "0".
 *
 * @param list<array<mixed>> $rows
 * @return list<array{id: string, label: string}>
 */
$asOptions = static function (array $rows, string $idColumn, string $labelColumn): array {
    $options = [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $id = $row[$idColumn] ?? null;
        if (is_string($id) && preg_match('/^\d+$/', $id) === 1) {
            $id = (int)$id;
        }
        if (!is_int($id) || $id <= 0) {
            continue;
        }
        $label = $row[$labelColumn] ?? null;
        $options[] = [
            'id' => (string)$id,
            'label' => is_string($label) ? $label : '',
        ];
    }

    return $options;
};

$facilities = $asOptions(
    QueryUtils::fetchRecordsNoLog('SELECT `id`, `name` FROM `facility` ORDER BY `name`', []),
    'id',
    'name'
);
$visitCategories = $asOptions(
    QueryUtils::fetchRecordsNoLog(
        'SELECT `pc_catid`, `pc_catname` FROM `openemr_postcalendar_categories`
          WHERE `pc_active` = 1 ORDER BY `pc_seq`, `pc_catname`',
        []
    ),
    'pc_catid',
    'pc_catname'
);
$providers = $asOptions(
    QueryUtils::fetchRecordsNoLog(
        "SELECT `id`, CONCAT_WS(', ', `lname`, `fname`) AS `name` FROM `users`
          WHERE `active` = 1 AND `authorized` = 1 AND `username` <> '' ORDER BY `lname`, `fname`",
        []
    ),
    'id',
    'name'
);
// id => label maps, so rules show names instead of ids. Built with plain loops rather
// than a helper closure: a closure's array docblock types are not honoured, so its result
// would come back as mixed.
$facilityNames = [];
foreach ($facilities as $option) {
    $facilityNames[$option['id']] = $option['label'];
}
$providerNames = [];
foreach ($providers as $option) {
    $providerNames[$option['id']] = $option['label'];
}
$visitCategoryNames = [];
foreach ($visitCategories as $option) {
    $visitCategoryNames[$option['id']] = $option['label'];
}

/**
 * "Name" for a scope id, or "#id" when the facility, provider or visit category has
 * since been removed or deactivated, so a rule pointing at it is still identifiable.
 */
$nameOrId = static fn(?string $name, int $id): string
    => $name !== null && $name !== '' ? $name : '#' . $id;

// A rule can point at a provider who has since been deactivated, or a facility or visit
// category that was removed. Keep the edited value selectable; otherwise the select falls
// back to "Any" and saving the edit silently widens the rule.
$inactiveSuffix = ' ' . xl('(inactive or removed)');
if ($editing !== null && $editing->facility !== null && !isset($facilityNames[(string)$editing->facility])) {
    $facilities[] = ['id' => (string)$editing->facility, 'label' => '#' . $editing->facility . $inactiveSuffix];
}
if ($editing !== null && $editing->provider !== null && !isset($providerNames[(string)$editing->provider])) {
    $providers[] = ['id' => (string)$editing->provider, 'label' => '#' . $editing->provider . $inactiveSuffix];
}
if (
    $editing !== null
    && $editing->visitCategoryId !== null
    && !isset($visitCategoryNames[(string)$editing->visitCategoryId])
) {
    $visitCategories[] = [
        'id' => (string)$editing->visitCategoryId,
        'label' => '#' . $editing->visitCategoryId . $inactiveSuffix,
    ];
}

// Questionnaire categories are Observation_Types option ids. Offer the list so a
// category rule cannot be stored with a typo that matches nothing, and show titles
// rather than ids wherever a category is displayed. Categories a questionnaire or a rule
// already carries but the list lacks are kept, so existing data stays selectable.
/** @var array<string, string> $categoryTitles */
$categoryTitles = [];
foreach (
    QueryUtils::fetchRecordsNoLog(
        "SELECT `option_id`, `title` FROM `list_options`
          WHERE `list_id` = 'Observation_Types' AND `activity` = 1 ORDER BY `seq`, `title`",
        []
    ) as $row
) {
    $optionId = $row['option_id'] ?? null;
    $title = $row['title'] ?? null;
    if (!is_string($optionId) || $optionId === '') {
        continue;
    }
    // xl() takes literal strings only; list titles go through xl_list_label(), which
    // honours the translate_lists global and returns an untyped value, hence the check.
    $translated = is_string($title) && $title !== '' ? xl_list_label($title) : null;
    $categoryTitles[$optionId] = is_string($translated) && $translated !== '' ? $translated : $optionId;
}
foreach ($questionnaires as $questionnaire) {
    if ($questionnaire['category'] !== '' && !isset($categoryTitles[$questionnaire['category']])) {
        $categoryTitles[$questionnaire['category']] = $questionnaire['category'];
    }
}
foreach ($categoryRules as $categoryRule) {
    $ruleCategory = (string)$categoryRule->category;
    $categoryTitles[$ruleCategory] ??= $ruleCategory;
}
$categoryLabel = static fn(string $category): string => $categoryTitles[$category] ?? $category;

// Permissions, picked as one section/level pair. Free-text section and level boxes made
// it easy to store a pair the installer never created, which aclCheckCore() denies for
// everyone, so the questionnaire silently vanished.
/** @var array<string, string> $aclSectionNames */
$aclSectionNames = [];
/** @var array<string, array<string, string>> $aclChoices section => ["section|level" => level name] */
$aclChoices = [];
/** @var array<string, string> $aclLabels "section|level" => "Section: Level" */
$aclLabels = [];
foreach (
    QueryUtils::fetchRecordsNoLog(
        'SELECT s.`value` AS `section`, s.`name` AS `section_name`,
                a.`value` AS `level`, a.`name` AS `level_name`
           FROM `gacl_aco` a
          INNER JOIN `gacl_aco_sections` s ON s.`value` = a.`section_value`
          WHERE a.`hidden` = 0 AND s.`hidden` = 0
          ORDER BY s.`order_value`, s.`name`, a.`order_value`, a.`name`',
        []
    ) as $row
) {
    $section = $row['section'] ?? null;
    $level = $row['level'] ?? null;
    if (!is_string($section) || $section === '' || !is_string($level) || $level === '') {
        continue;
    }
    $sectionName = $row['section_name'] ?? null;
    $levelName = $row['level_name'] ?? null;
    // Shown as stored, the way the access control admin shows them.
    $sectionLabel = is_string($sectionName) && $sectionName !== '' ? $sectionName : $section;
    $levelLabel = is_string($levelName) && $levelName !== '' ? $levelName : $level;
    $pair = $section . '|' . $level;
    $aclSectionNames[$section] = $sectionLabel;
    $aclChoices[$section][$pair] = $levelLabel;
    $aclLabels[$pair] = $sectionLabel . ': ' . $levelLabel;
}
$aclLabel = static fn(string $section, string $level): string
    => $aclLabels[$section . '|' . $level] ?? ($section . '/' . $level);

// Questionnaires registered as encounter forms. The encounter surface only filters the
// registered list, so an encounter rule for an unregistered questionnaire has no effect
// until it is registered; the grid says so instead of leaving a tick that does nothing.
/** @var array<int, true> $registeredForEncounter */
$registeredForEncounter = [];
foreach (
    QueryUtils::fetchRecordsNoLog(
        'SELECT DISTINCT `form_foreign_id` FROM `registry`
          WHERE `directory` = ? AND `form_foreign_id` > 0 AND `state` = 1',
        [QuestionnaireAvailabilityService::ENCOUNTER_FORM_DIRECTORY]
    ) as $row
) {
    $foreignId = $row['form_foreign_id'] ?? null;
    if (is_string($foreignId) && preg_match('/^\d+$/', $foreignId) === 1) {
        $foreignId = (int)$foreignId;
    }
    if (is_int($foreignId) && $foreignId > 0) {
        $registeredForEncounter[$foreignId] = true;
    }
}

// SMART apps a rule can name: enabled clients registered with the EHR launch scope,
// the same set the dashboard offers in its launch menu.
/** @var array<string, string> $smartAppNames client id => app name */
$smartAppNames = [];
// The library's getters are loosely typed; taking them through a mixed parameter narrows
// them the same way whatever the installed version declares.
$clientText = static fn(mixed $value): string => is_string($value) ? $value : '';
foreach ((new ClientRepository())->listClientEntities() as $client) {
    if (!$client->isEnabled() || !$client->hasScope(SmartLaunchController::CLIENT_APP_REQUIRED_LAUNCH_SCOPE)) {
        continue;
    }
    $clientIdentifier = $clientText($client->getIdentifier());
    if ($clientIdentifier === '') {
        continue;
    }
    $clientName = $clientText($client->getName());
    $smartAppNames[$clientIdentifier] = $clientName !== '' ? $clientName : $clientIdentifier;
}

$surfaceLabels = [
    QuestionnaireSurface::Dashboard->value => xl('Patient dashboard'),
    QuestionnaireSurface::Encounter->value => xl('Encounter forms'),
    QuestionnaireSurface::Portal->value => xl('Patient portal'),
    QuestionnaireSurface::Smart->value => xl('SMART launch'),
];
$scopeLabels = [
    'facility' => xl('Facility'),
    'provider' => xl('Provider'),
    'visit_category_id' => xl('Visit category'),
    'client_id' => xl('SMART app'),
];
$surfaces = QuestionnaireSurface::cases();

/**
 * One line telling an administrator what a surface can be limited by.
 */
$scopeHint = static function (QuestionnaireSurface $surface) use ($scopeLabels, $surfaceLabels): string {
    $names = [];
    foreach ($surface->supportedScopes() as $column) {
        $names[] = $scopeLabels[$column];
    }
    if ($names === []) {
        return sprintf(xl('%s cannot be limited yet.'), $surfaceLabels[$surface->value]);
    }

    return sprintf(xl('%s can be limited by %s.'), $surfaceLabels[$surface->value], implode(', ', $names));
};

/**
 * Where a rule applies, in words: "Everywhere" or "Facility: Main; Visit category: …".
 */
$limitText = static function (AssignmentRecord $rule) use (
    $scopeLabels,
    $nameOrId,
    $facilityNames,
    $providerNames,
    $visitCategoryNames,
    $smartAppNames
): string {
    $parts = [];
    if ($rule->facility !== null) {
        $parts[] = $scopeLabels['facility'] . ': '
            . $nameOrId($facilityNames[(string)$rule->facility] ?? null, $rule->facility);
    }
    if ($rule->provider !== null) {
        $parts[] = $scopeLabels['provider'] . ': '
            . $nameOrId($providerNames[(string)$rule->provider] ?? null, $rule->provider);
    }
    if ($rule->visitCategoryId !== null) {
        $parts[] = $scopeLabels['visit_category_id'] . ': '
            . $nameOrId($visitCategoryNames[(string)$rule->visitCategoryId] ?? null, $rule->visitCategoryId);
    }
    if ($rule->clientId !== null && $rule->clientId !== '') {
        $parts[] = $scopeLabels['client_id'] . ': '
            . ($smartAppNames[$rule->clientId] ?? $rule->clientId . ' ' . xl('(inactive or removed)'));
    }

    return $parts === [] ? xl('Everywhere') : implode('; ', $parts);
};

/**
 * Who may see what a rule offers, in words.
 */
$permissionText = static function (AssignmentRecord $rule) use ($aclLabel): string {
    if ($rule->aclSection !== null && $rule->aclLevel !== null) {
        return $aclLabel($rule->aclSection, $rule->aclLevel);
    }
    [$defaultSection, $defaultLevel] = $rule->surface->defaultAcl();

    return sprintf(xl('Default (%s)'), $aclLabel($defaultSection, $defaultLevel));
};

$csrfToken = CsrfUtils::collectCsrfToken(session: $session);

/**
 * One row of a rule table, shared by the questionnaire panel and the category list.
 *
 * $leadingCell is the category title for a category rule, or null for the
 * questionnaire panel, which already names its questionnaire in the heading.
 */
$renderRuleRow = static function (string $returnUrl, AssignmentRecord $rule, ?string $leadingCell) use (
    $surfaceLabels,
    $limitText,
    $permissionText,
    $csrfToken,
    $editing,
    $pageUrl
): void {
    $rowClass = $editing !== null && $editing->id === $rule->id ? 'table-active' : '';
    $editUrl = $rule->resourceId !== null
        ? $pageUrl . '?q=' . $rule->resourceId . '&edit=' . $rule->id . '#limits'
        : $pageUrl . '?edit=' . $rule->id . '#category-rules';
    ?>
    <tr class="<?php echo attr($rowClass); ?>">
        <?php if ($leadingCell !== null) { ?>
            <td><?php echo text($leadingCell); ?></td>
        <?php } ?>
        <td><?php echo text($surfaceLabels[$rule->surface->value]); ?></td>
        <td><?php echo text($limitText($rule)); ?></td>
        <td class="text-muted"><?php echo text($permissionText($rule)); ?></td>
        <td>
            <?php if ($rule->active) { ?>
                <span class="badge badge-success"><?php echo xlt('On'); ?></span>
            <?php } else { ?>
                <span class="badge badge-info"><?php echo xlt('Off'); ?></span>
            <?php } ?>
        </td>
        <td class="text-nowrap text-right">
            <a class="btn btn-sm btn-outline-primary" href="<?php echo attr($editUrl); ?>" onclick="top.restoreSession()"><?php echo xlt('Edit'); ?></a>
            <form method="post" action="<?php echo attr($returnUrl); ?>" class="d-inline" onsubmit="return top.restoreSession()">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr($csrfToken); ?>" />
                <input type="hidden" name="action" value="toggle_rule" />
                <input type="hidden" name="assignment_id" value="<?php echo attr((string)$rule->id); ?>" />
                <input type="hidden" name="to" value="<?php echo $rule->active ? 'off' : 'on'; ?>" />
                <button type="submit" class="btn btn-sm btn-outline-secondary">
                    <?php echo $rule->active ? xlt('Switch off') : xlt('Switch on'); ?>
                </button>
            </form>
            <form method="post" action="<?php echo attr($returnUrl); ?>" class="d-inline"
                  data-confirm="<?php echo xla('Remove this rule? Switch it off instead to keep a record of it.'); ?>"
                  onsubmit="return top.restoreSession()">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr($csrfToken); ?>" />
                <input type="hidden" name="action" value="delete_rule" />
                <input type="hidden" name="assignment_id" value="<?php echo attr((string)$rule->id); ?>" />
                <button type="submit" class="btn btn-sm btn-link text-danger"><?php echo xlt('Remove'); ?></button>
            </form>
        </td>
    </tr>
    <?php
};

/**
 * Surface, permission and limit fields, shared by both rule forms.
 *
 * Ids are prefixed so the two forms on the page do not collide. Limit fields carry
 * data-scope and are disabled for a surface that cannot apply them, so they are not
 * posted; the service refuses them too.
 */
$renderRuleFields = static function (string $prefix, ?AssignmentRecord $rule) use (
    $surfaces,
    $surfaceLabels,
    $scopeHint,
    $selectedIf,
    $facilities,
    $providers,
    $visitCategories,
    $smartAppNames,
    $aclChoices,
    $aclSectionNames,
    $aclLabels
): void {
    $ruleAcl = $rule !== null && $rule->aclSection !== null && $rule->aclLevel !== null
        ? $rule->aclSection . '|' . $rule->aclLevel
        : null;
    $ruleClientId = $rule !== null && $rule->clientId !== null && $rule->clientId !== '' ? $rule->clientId : null;
    ?>
    <div class="form-row">
        <div class="form-group col-md-4">
            <label for="<?php echo attr($prefix); ?>-surface"><?php echo xlt('Offered on'); ?></label>
            <select class="form-control" id="<?php echo attr($prefix); ?>-surface" name="surface" data-surface-select>
                <?php foreach ($surfaces as $surface) { ?>
                    <option
                        value="<?php echo attr($surface->value); ?>"
                        data-scopes="<?php echo attr(json_encode($surface->supportedScopes(), JSON_THROW_ON_ERROR)); ?>"
                        data-hint="<?php echo attr($scopeHint($surface)); ?>"
                        <?php echo $selectedIf($surface->value, $rule?->surface->value); ?>
                    ><?php echo text($surfaceLabels[$surface->value]); ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-md-8">
            <label for="<?php echo attr($prefix); ?>-acl"><?php echo xlt('Who may see it'); ?></label>
            <select class="form-control" id="<?php echo attr($prefix); ?>-acl" name="acl">
                <option value=""><?php echo xlt('Default for that surface'); ?></option>
                <?php if ($ruleAcl !== null && !isset($aclLabels[$ruleAcl])) { ?>
                    <option value="<?php echo attr($ruleAcl); ?>" selected>
                        <?php echo text(sprintf(xl('%s (not in the permission list)'), str_replace('|', '/', $ruleAcl))); ?>
                    </option>
                <?php } ?>
                <?php foreach ($aclChoices as $section => $levels) { ?>
                    <optgroup label="<?php echo attr($aclSectionNames[$section] ?? $section); ?>">
                        <?php foreach ($levels as $pair => $levelLabel) { ?>
                            <option value="<?php echo attr($pair); ?>" <?php echo $selectedIf($pair, $ruleAcl); ?>>
                                <?php echo text($levelLabel); ?>
                            </option>
                        <?php } ?>
                    </optgroup>
                <?php } ?>
            </select>
        </div>
    </div>
    <p class="small text-muted mb-2" data-scope-hint></p>
    <div class="form-row">
        <div class="form-group col-md-3">
            <label for="<?php echo attr($prefix); ?>-facility"><?php echo xlt('Only at facility'); ?></label>
            <select class="form-control" id="<?php echo attr($prefix); ?>-facility" name="facility" data-scope="facility">
                <option value=""><?php echo xlt('Any'); ?></option>
                <?php foreach ($facilities as $facility) { ?>
                    <option value="<?php echo attr($facility['id']); ?>" <?php echo $selectedIf($facility['id'], $rule?->facility !== null ? (string)$rule->facility : null); ?>>
                        <?php echo text($facility['label']); ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-md-3">
            <label for="<?php echo attr($prefix); ?>-provider"><?php echo xlt('Only for provider'); ?></label>
            <select class="form-control" id="<?php echo attr($prefix); ?>-provider" name="provider" data-scope="provider">
                <option value=""><?php echo xlt('Any'); ?></option>
                <?php foreach ($providers as $provider) { ?>
                    <option value="<?php echo attr($provider['id']); ?>" <?php echo $selectedIf($provider['id'], $rule?->provider !== null ? (string)$rule->provider : null); ?>>
                        <?php echo text($provider['label']); ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-md-3">
            <label for="<?php echo attr($prefix); ?>-visit"><?php echo xlt('Only for visit category'); ?></label>
            <select class="form-control" id="<?php echo attr($prefix); ?>-visit" name="visit_category_id" data-scope="visit_category_id">
                <option value=""><?php echo xlt('Any'); ?></option>
                <?php foreach ($visitCategories as $visitCategory) { ?>
                    <option value="<?php echo attr($visitCategory['id']); ?>" <?php echo $selectedIf($visitCategory['id'], $rule?->visitCategoryId !== null ? (string)$rule->visitCategoryId : null); ?>>
                        <?php echo text($visitCategory['label']); ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group col-md-3">
            <label for="<?php echo attr($prefix); ?>-app"><?php echo xlt('Only for SMART app'); ?></label>
            <select class="form-control" id="<?php echo attr($prefix); ?>-app" name="client_id" data-scope="client_id">
                <option value=""><?php echo xlt('Any'); ?></option>
                <?php if ($ruleClientId !== null && !isset($smartAppNames[$ruleClientId])) { ?>
                    <option value="<?php echo attr($ruleClientId); ?>" selected>
                        <?php echo text($ruleClientId . ' ' . xl('(inactive or removed)')); ?>
                    </option>
                <?php } ?>
                <?php foreach ($smartAppNames as $appId => $appName) { ?>
                    <option value="<?php echo attr((string)$appId); ?>" <?php echo $selectedIf((string)$appId, $ruleClientId); ?>>
                        <?php echo text($appName); ?>
                    </option>
                <?php } ?>
            </select>
        </div>
    </div>
    <?php if ($rule !== null) { ?>
        <input type="hidden" name="assignment_id" value="<?php echo attr((string)$rule->id); ?>" />
        <input type="hidden" name="seq" value="<?php echo attr((string)$rule->seq); ?>" />
    <?php } ?>
    <?php
};
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Questionnaire Availability'); ?></title>
    <?php Header::setupHeader(); ?>
</head>
<body class="body_top">
<div class="container-fluid">
    <h2 class="mt-3"><?php echo xlt('Questionnaire Availability'); ?></h2>
    <p class="text-muted mb-3">
        <?php echo xlt('Tick where each questionnaire is offered. Open Limits to offer one only at a facility, for a provider, for a kind of visit or for one SMART app, or to change who may see it.'); ?>
    </p>

    <?php foreach ($notices as $notice) { ?>
        <div class="alert alert-success"><?php echo text($notice); ?></div>
    <?php } ?>
    <?php foreach ($errors as $error) { ?>
        <div class="alert alert-danger"><?php echo text($error); ?></div>
    <?php } ?>

    <?php if ($questionnaires === []) { ?>
        <div class="alert alert-info">
            <?php echo xlt('No active questionnaires are in the repository yet. Import one from the patient dashboard first.'); ?>
        </div>
    <?php } else { ?>
    <form method="post" action="<?php echo attr($selectedUrl); ?>" onsubmit="return top.restoreSession()">
        <input type="hidden" name="csrf_token_form" value="<?php echo attr($csrfToken); ?>" />
        <input type="hidden" name="action" value="save_grid" />
        <div class="d-flex flex-wrap align-items-center mb-2">
            <label class="sr-only" for="grid-filter"><?php echo xlt('Filter questionnaires'); ?></label>
            <input type="search" class="form-control form-control-sm mr-3 mb-1" id="grid-filter" style="max-width: 18rem;"
                   placeholder="<?php echo xla('Filter by name or category'); ?>" />
            <span class="small text-muted mb-1">
                <span class="badge badge-info"><?php echo xlt('limited'); ?></span>
                <?php echo xlt('also offered where a limit applies'); ?>
                &nbsp;
                <span class="badge badge-info"><?php echo xlt('category'); ?></span>
                <?php echo xlt('also offered by a category rule'); ?>
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-striped" id="availability-grid">
                <thead>
                <tr>
                    <th><?php echo xlt('Questionnaire'); ?></th>
                    <?php foreach ($surfaces as $surface) { ?>
                        <th class="text-center">
                            <?php echo text($surfaceLabels[$surface->value]); ?>
                            <?php if ($surface === QuestionnaireSurface::Smart) { ?>
                                <i class="fa fa-info-circle text-muted" title="<?php echo xla('Only questionnaires offered here can be launched into a SMART app from Available Assessments.'); ?>"></i>
                            <?php } ?>
                        </th>
                    <?php } ?>
                    <th class="text-right"><?php echo xlt('Limits'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($questionnaires as $questionnaire) {
                    $questionnaireId = $questionnaire['id'];
                    $questionnaireCategory = $questionnaire['category'] !== ''
                        ? $categoryLabel($questionnaire['category'])
                        : '';
                    $extraRules = $ruleCounts[$questionnaireId] ?? 0;
                    ?>
                    <tr class="<?php echo $questionnaireId === $selectedId ? 'table-active' : ''; ?>"
                        data-filter="<?php echo attr(mb_strtolower($questionnaire['name'] . ' ' . $questionnaireCategory)); ?>">
                        <td>
                            <?php echo text($questionnaire['name']); ?>
                            <?php if ($questionnaireCategory !== '') { ?>
                                <div class="small text-muted"><?php echo text($questionnaireCategory); ?></div>
                            <?php } ?>
                        </td>
                        <?php foreach ($surfaces as $surface) {
                            $key = $questionnaireId . '|' . $surface->value;
                            $isOn = $gridState[$key] ?? false;
                            $limited = $limitCounts[$key] ?? 0;
                            $viaCategory = $questionnaire['category'] !== ''
                                && isset($categoryReach[$questionnaire['category'] . '|' . $surface->value]);
                            $notRegistered = $surface === QuestionnaireSurface::Encounter
                                && !isset($registeredForEncounter[$questionnaireId]);
                            ?>
                            <td class="text-center">
                                <input type="hidden" name="rendered[]" value="<?php echo attr($key); ?>" />
                                <input
                                    type="checkbox"
                                    class="js-grid-box"
                                    name="offer[<?php echo attr((string)$questionnaireId); ?>][<?php echo attr($surface->value); ?>]"
                                    value="1"
                                    title="<?php echo xla('Offered everywhere on this surface'); ?>"
                                    aria-label="<?php echo attr($questionnaire['name'] . ': ' . $surfaceLabels[$surface->value]); ?>"
                                    <?php echo $isOn ? 'checked' : ''; ?>
                                />
                                <?php if ($limited > 0) { ?>
                                    <div><span class="badge badge-info" title="<?php echo xla('Also offered where its limits apply, even with this box unticked. Open Limits to see them.'); ?>"><?php echo xlt('limited'); ?></span></div>
                                <?php } ?>
                                <?php if ($viaCategory) { ?>
                                    <div><span class="badge badge-info" title="<?php echo xla('A category rule also offers it here, even with this box unticked.'); ?>"><?php echo xlt('category'); ?></span></div>
                                <?php } ?>
                                <?php if ($notRegistered) { ?>
                                    <div><span class="badge badge-warning" title="<?php echo xla('Not registered as an encounter form, so it cannot appear in encounters yet. Register it from the Questionnaire form in an encounter; this setting then applies.'); ?>"><?php echo xlt('not registered'); ?></span></div>
                                <?php } ?>
                            </td>
                        <?php } ?>
                        <td class="text-right text-nowrap">
                            <a class="btn btn-sm btn-outline-primary"
                               href="<?php echo attr($pageUrl . '?q=' . $questionnaireId . '#limits'); ?>"
                               onclick="top.restoreSession()">
                                <?php echo xlt('Limits'); ?>
                                <?php if ($extraRules > 0) { ?>
                                    <span class="badge badge-info ml-1"><?php echo text((string)$extraRules); ?></span>
                                <?php } ?>
                            </a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <button type="submit" class="btn btn-primary"><?php echo xlt('Save'); ?></button>
        <span class="badge badge-warning ml-2" id="grid-dirty" hidden><?php echo xlt('Unsaved changes'); ?></span>
    </form>
    <?php } ?>

    <?php if ($selectedId !== null) {
        // $selectedId was cleared above unless it names a listed questionnaire.
        $selectedName = $questionnaireNames[$selectedId];
        ?>
        <div class="card mt-4" id="limits">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><?php echo text(sprintf(xl('Limits for %s'), $selectedName)); ?></strong>
                <a class="btn btn-sm btn-link" href="<?php echo attr($pageUrl); ?>" onclick="top.restoreSession()"><?php echo xlt('Close'); ?></a>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    <?php echo xlt('Each line is one rule. "Everywhere" is the tick in the grid. A limited rule offers the questionnaire only where its limits match. Rules add up: it is offered if any rule that is on matches.'); ?>
                </p>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th><?php echo xlt('Offered on'); ?></th>
                            <th><?php echo xlt('Where'); ?></th>
                            <th><?php echo xlt('Who may see it'); ?></th>
                            <th><?php echo xlt('Status'); ?></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($selectedRules === []) { ?>
                            <tr><td colspan="5" class="text-muted"><?php echo xlt('Not offered anywhere yet. Tick a surface in the grid, or add a limit below.'); ?></td></tr>
                        <?php } ?>
                        <?php foreach ($selectedRules as $selectedRule) {
                            $renderRuleRow($selectedUrl . '#limits', $selectedRule, null);
                        } ?>
                        </tbody>
                    </table>
                </div>

                <h5 class="mt-3">
                    <?php echo $editingLimit !== null ? xlt('Edit rule') : xlt('Add a limit'); ?>
                </h5>
                <form method="post" action="<?php echo attr($selectedUrl . '#limits'); ?>" onsubmit="return top.restoreSession()" data-rule-form>
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr($csrfToken); ?>" />
                    <input type="hidden" name="action" value="save_rule" />
                    <input type="hidden" name="target" value="questionnaire" />
                    <input type="hidden" name="questionnaire_id" value="<?php echo attr((string)$selectedId); ?>" />
                    <?php $renderRuleFields('limit', $editingLimit); ?>
                    <?php if ($editingLimit === null) { ?>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="only_here" value="1" id="limit-only-here" checked />
                            <label class="form-check-label" for="limit-only-here">
                                <?php echo xlt('Offer it only where this limit applies (untick "everywhere" on that surface)'); ?>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary"><?php echo xlt('Add limit'); ?></button>
                    <?php } else { ?>
                        <button type="submit" class="btn btn-primary"><?php echo xlt('Save changes'); ?></button>
                        <a class="btn btn-link" href="<?php echo attr($selectedUrl . '#limits'); ?>" onclick="top.restoreSession()"><?php echo xlt('Cancel'); ?></a>
                    <?php } ?>
                </form>
            </div>
        </div>
    <?php } ?>

    <div class="card mt-4 mb-5" id="category-rules">
        <div class="card-header"><strong><?php echo xlt('Category rules'); ?></strong></div>
        <div class="card-body">
            <p class="text-muted">
                <?php echo xlt('Offer every questionnaire in a category, including ones imported later, without ticking each one.'); ?>
            </p>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                    <tr>
                        <th><?php echo xlt('Category'); ?></th>
                        <th><?php echo xlt('Offered on'); ?></th>
                        <th><?php echo xlt('Where'); ?></th>
                        <th><?php echo xlt('Who may see it'); ?></th>
                        <th><?php echo xlt('Status'); ?></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($categoryRules === []) { ?>
                        <tr><td colspan="6" class="text-muted"><?php echo xlt('No category rules'); ?></td></tr>
                    <?php } ?>
                    <?php foreach ($categoryRules as $categoryRule) {
                        $renderRuleRow(
                            $selectedUrl . '#category-rules',
                            $categoryRule,
                            $categoryLabel((string)$categoryRule->category)
                        );
                    } ?>
                    </tbody>
                </table>
            </div>

            <h5 class="mt-3">
                <?php echo $editingCategoryRule !== null ? xlt('Edit category rule') : xlt('Add a category rule'); ?>
            </h5>
            <form method="post" action="<?php echo attr($selectedUrl . '#category-rules'); ?>" onsubmit="return top.restoreSession()" data-rule-form>
                <input type="hidden" name="csrf_token_form" value="<?php echo attr($csrfToken); ?>" />
                <input type="hidden" name="action" value="save_rule" />
                <input type="hidden" name="target" value="category" />
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="category-category"><?php echo xlt('Category'); ?></label>
                        <select class="form-control" id="category-category" name="category" required>
                            <option value=""><?php echo xlt('Choose a category'); ?></option>
                            <?php foreach ($categoryTitles as $categoryValue => $categoryTitle) { ?>
                                <option value="<?php echo attr((string)$categoryValue); ?>" <?php echo $selectedIf((string)$categoryValue, $editingCategoryRule?->category); ?>>
                                    <?php echo text($categoryTitle); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <?php $renderRuleFields('category', $editingCategoryRule); ?>
                <?php if ($editingCategoryRule === null) { ?>
                    <button type="submit" class="btn btn-primary"><?php echo xlt('Add category rule'); ?></button>
                <?php } else { ?>
                    <button type="submit" class="btn btn-primary"><?php echo xlt('Save changes'); ?></button>
                    <a class="btn btn-link" href="<?php echo attr($selectedUrl . '#category-rules'); ?>" onclick="top.restoreSession()"><?php echo xlt('Cancel'); ?></a>
                <?php } ?>
            </form>
        </div>
    </div>
</div>
<script>
(function () {
    'use strict';

    // In each rule form, limit fields the chosen surface never fills in are disabled, so
    // they are not posted. The service refuses such a rule too; this keeps the form from
    // offering it.
    document.querySelectorAll('form[data-rule-form]').forEach(function (form) {
        const surface = form.querySelector('[data-surface-select]');
        const hint = form.querySelector('[data-scope-hint]');
        const scopedFields = form.querySelectorAll('[data-scope]');
        if (!surface) {
            return;
        }
        const applySurface = function () {
            const option = surface.options[surface.selectedIndex];
            let allowed = [];
            try {
                allowed = JSON.parse(option?.dataset.scopes || '[]');
            } catch (e) {
                allowed = [];
            }
            scopedFields.forEach(function (field) {
                field.disabled = !allowed.includes(field.dataset.scope);
            });
            if (hint) {
                hint.textContent = option?.dataset.hint || '';
            }
        };
        surface.addEventListener('change', applySurface);
        applySurface();
    });

    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    const dirty = document.getElementById('grid-dirty');
    document.querySelectorAll('.js-grid-box').forEach(function (box) {
        box.addEventListener('change', function () {
            if (dirty) {
                dirty.hidden = false;
            }
        });
    });

    const gridFilter = document.getElementById('grid-filter');
    if (gridFilter) {
        const gridRows = document.querySelectorAll('#availability-grid tbody tr');
        gridFilter.addEventListener('input', function () {
            const needle = gridFilter.value.trim().toLowerCase();
            gridRows.forEach(function (row) {
                row.hidden = needle !== '' && !(row.dataset.filter || '').includes(needle);
            });
        });
    }
})();
</script>
</body>
</html>
