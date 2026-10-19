<?php

/**
 * Questionnaire availability management.
 *
 * Which questionnaires are offered on which surface -- patient dashboard, encounter
 * forms, patient portal, SMART launch -- is stored in `content_assignments` and answered
 * by QuestionnaireAvailabilityService. Until this screen existed those rows arrived only
 * from the upgrade backfill and from import, so the only way to change them was SQL.
 *
 * Two sections, because there are two genuinely different jobs:
 *
 *  - the grid turns one questionnaire on or off for a surface, which is the common case;
 *  - the rules list shows every stored row including category-wide and scoped ones, which
 *    is how an administrator sees why a questionnaire is still offered after its own row
 *    was switched off.
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
// The same, for the query string: ?edit= or ?edit=abc must not take the page down.
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
                // Index the unscoped rows once; a scoped or category row is never touched
                // by the grid, because the grid cannot express what it is scoped to.
                $unscopedByKey = [];
                foreach ($service->listAssignments() as $existing) {
                    if ($existing->isCategoryWide() || $existing->isScoped()) {
                        continue;
                    }
                    if ($existing->resourceId === null) {
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
            $surface = QuestionnaireSurface::tryFrom($request->request->getString('surface'));
            $questionnaireId = $postedId('resource_id');
            $category = $postedText('category');
            if ($questionnaireId !== null) {
                // A row names one questionnaire or a category, never both.
                $category = null;
            }

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
                $errors[] = xl('Choose a surface');
            } elseif (!$aclValid) {
                $errors[] = xl('Choose a permission from the list');
            } else {
                // A scope field the chosen surface cannot apply is disabled in the form
                // and so not posted. If one arrives anyway, the service refuses it.
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
                    $notices[] = xl('Rule added');
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
                => xl('This surface cannot be narrowed by one of the chosen fields. Clear the fields the form disables for it.'),
            QuestionnaireAvailabilityService::ERROR_DUPLICATE
                => xl('Another rule already covers that questionnaire or category with the same scope'),
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

// The rule being edited, if any. Addressed by id in the query string so a failed save
// returns to the same rule; cleared once a save succeeds.
$editing = null;
$editId = $ruleSaved ? 0 : ($queryId('edit') ?? 0);
if ($editId > 0) {
    foreach ($assignments as $candidate) {
        if ($candidate->id === $editId) {
            $editing = $candidate;
            break;
        }
    }
}
$formAction = 'manage_questionnaire_availability.php'
    . ($editing !== null ? '?edit=' . $editing->id : '');

/**
 * `selected` when an option matches the value being edited.
 */
$selectedIf = static fn(string $optionValue, ?string $current): string
    => $current !== null && $optionValue === $current ? 'selected' : '';

// Unscoped, questionnaire-specific rows drive the grid checkboxes. Those are the only
// rows the grid can express, so they are the only ones it writes.
/** @var array<string, bool> $gridState */
$gridState = [];

// Rules the grid cannot show, indexed so the grid can still warn about them. Availability
// is additive: a questionnaire stays offered while ANY active rule reaches it. Without
// this, an administrator unticks a box, sees no change, and concludes the screen is
// broken -- when in fact a category-wide or scoped rule is still offering it.
/** @var array<string, array<string, true>> $otherRuleReasons */
$otherRuleReasons = [];
$noteOther = static function (string $key, string $reason) use (&$otherRuleReasons): void {
    $otherRuleReasons[$key][$reason] = true;
};

foreach ($assignments as $assignment) {
    if ($assignment->isCategoryWide()) {
        if ($assignment->active && ($assignment->category ?? '') !== '') {
            // Keyed by category; resolved per questionnaire when the grid renders.
            $noteOther('category:' . $assignment->category . '|' . $assignment->surface->value, 'category');
        }
        continue;
    }
    if ($assignment->resourceId === null) {
        continue;
    }
    if ($assignment->isScoped()) {
        if ($assignment->active) {
            $noteOther($assignment->resourceId . '|' . $assignment->surface->value, 'scoped');
        }
        continue;
    }
    $gridState[$assignment->resourceId . '|' . $assignment->surface->value] = $assignment->active;
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
// id => label maps, so the rules list can show names instead of ids. Built with plain
// loops rather than a helper closure: a closure's array docblock types are not honoured,
// so its result would come back as mixed.
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
// rather than ids wherever a category is displayed. Categories a questionnaire already
// carries but the list lacks are kept, so existing data stays selectable.
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
if ($editing !== null && $editing->isCategoryWide() && !isset($categoryTitles[(string)$editing->category])) {
    $categoryTitles[(string)$editing->category] = (string)$editing->category;
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
$editingAcl = ($editing !== null && $editing->aclSection !== null && $editing->aclLevel !== null)
    ? $editing->aclSection . '|' . $editing->aclLevel
    : null;
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
 * One line telling an administrator what a surface can be narrowed by.
 */
$scopeHint = static function (QuestionnaireSurface $surface) use ($scopeLabels, $surfaceLabels): string {
    $names = [];
    foreach ($surface->supportedScopes() as $column) {
        $names[] = $scopeLabels[$column];
    }
    if ($names === []) {
        return sprintf(xl('%s: rules cannot be narrowed.'), $surfaceLabels[$surface->value]);
    }

    return sprintf(xl('%s: rules can be narrowed by %s.'), $surfaceLabels[$surface->value], implode(', ', $names));
};

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
$editingClientId = $editing !== null && ($editing->clientId ?? '') !== '' ? $editing->clientId : null;

$csrfToken = CsrfUtils::collectCsrfToken(session: $session);
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
    <p class="text-muted">
        <?php echo xlt('Choose where each questionnaire is offered. Rules are additive: a questionnaire is offered while any active rule reaches it, so a badge marks boxes whose questionnaire is also covered by a category or scoped rule.'); ?>
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
    <form method="post" action="manage_questionnaire_availability.php" onsubmit="return top.restoreSession()">
        <input type="hidden" name="csrf_token_form" value="<?php echo attr($csrfToken); ?>" />
        <input type="hidden" name="action" value="save_grid" />
        <div class="form-inline mb-2">
            <label class="sr-only" for="grid-filter"><?php echo xlt('Filter questionnaires'); ?></label>
            <input type="search" class="form-control form-control-sm" id="grid-filter"
                   placeholder="<?php echo xla('Filter by name or category'); ?>" />
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-striped" id="availability-grid">
                <thead>
                <tr>
                    <th><?php echo xlt('Questionnaire'); ?></th>
                    <th><?php echo xlt('Category'); ?></th>
                    <?php foreach ($surfaces as $surface) { ?>
                        <th class="text-center">
                            <?php echo text($surfaceLabels[$surface->value]); ?>
                            <?php if ($surface === QuestionnaireSurface::Smart) { ?>
                                <i class="fa fa-info-circle text-muted" title="<?php echo xla('Only ticked questionnaires can be launched into a SMART app from Available Assessments. Add a rule to allow one app only.'); ?>"></i>
                            <?php } ?>
                        </th>
                    <?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($questionnaires as $questionnaire) {
                    $questionnaireCategory = $questionnaire['category'] !== ''
                        ? $categoryLabel($questionnaire['category'])
                        : '';
                    ?>
                    <tr data-filter="<?php echo attr(mb_strtolower($questionnaire['name'] . ' ' . $questionnaireCategory)); ?>">
                        <td><?php echo text($questionnaire['name']); ?></td>
                        <td class="text-muted"><?php echo text($questionnaireCategory); ?></td>
                        <?php foreach ($surfaces as $surface) {
                            $surfaceValue = $surface->value;
                            $key = $questionnaire['id'] . '|' . $surfaceValue;
                            $isOn = $gridState[$key] ?? false;

                            // Reasons this questionnaire may be offered here regardless of
                            // the checkbox, so an unticked-but-still-offered state is
                            // explained rather than looking like a bug.
                            $reasons = array_keys($otherRuleReasons[$key] ?? []);
                            if ($questionnaire['category'] !== '') {
                                $categoryKey = 'category:' . $questionnaire['category'] . '|' . $surfaceValue;
                                if (isset($otherRuleReasons[$categoryKey])) {
                                    $reasons[] = 'category';
                                }
                            }
                            $reasons = array_values(array_unique($reasons));
                            $notRegistered = $surface === QuestionnaireSurface::Encounter
                                && !isset($registeredForEncounter[$questionnaire['id']]);
                            ?>
                            <td class="text-center">
                                <input type="hidden" name="rendered[]" value="<?php echo attr($key); ?>" />
                                <input
                                    type="checkbox"
                                    class="js-grid-box"
                                    name="offer[<?php echo attr((string)$questionnaire['id']); ?>][<?php echo attr($surfaceValue); ?>]"
                                    value="1"
                                    aria-label="<?php echo attr($questionnaire['name'] . ': ' . $surfaceLabels[$surfaceValue]); ?>"
                                    <?php echo $isOn ? 'checked' : ''; ?>
                                />
                                <?php if ($notRegistered) { ?>
                                    <div>
                                        <span class="badge badge-info" title="<?php echo xla('Not registered as an encounter form, so it cannot appear in encounters yet. Register it from the Questionnaire form in an encounter; this setting then applies.'); ?>">
                                            <?php echo xlt('not registered'); ?>
                                        </span>
                                    </div>
                                <?php } ?>
                                <?php foreach ($reasons as $reason) { ?>
                                    <div>
                                        <?php if ($reason === 'category') { ?>
                                            <span class="badge badge-info" title="<?php echo xla('A category rule also offers this here, so it stays available even with this box unchecked. See All rules below.'); ?>">
                                                <?php echo xlt('category rule'); ?>
                                            </span>
                                        <?php } else { ?>
                                            <span class="badge badge-warning" title="<?php echo xla('A scoped rule also offers this here, so it stays available in that scope even with this box unchecked. See All rules below.'); ?>">
                                                <?php echo xlt('scoped rule'); ?>
                                            </span>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </td>
                        <?php } ?>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <button type="submit" class="btn btn-primary"><?php echo xlt('Save'); ?></button>
        <span class="badge badge-warning ml-2" id="grid-dirty" hidden><?php echo xlt('Unsaved changes'); ?></span>
    </form>
    <?php } ?>

    <hr />
    <h4><?php echo xlt('All rules'); ?></h4>
    <p class="text-muted">
        <?php echo xlt('Every stored rule, including category-wide and scoped rules the grid above cannot show. Switching a rule off keeps it for the record; removing it deletes it.'); ?>
    </p>
    <?php if ($assignments !== []) { ?>
        <div class="form-inline mb-2">
            <label class="sr-only" for="rules-filter"><?php echo xlt('Filter rules'); ?></label>
            <input type="search" class="form-control form-control-sm mr-2" id="rules-filter"
                   placeholder="<?php echo xla('Filter by target or scope'); ?>" />
            <label class="sr-only" for="rules-surface"><?php echo xlt('Surface'); ?></label>
            <select class="form-control form-control-sm" id="rules-surface">
                <option value=""><?php echo xlt('All surfaces'); ?></option>
                <?php foreach ($surfaceLabels as $value => $label) { ?>
                    <option value="<?php echo attr($value); ?>"><?php echo text($label); ?></option>
                <?php } ?>
            </select>
        </div>
    <?php } ?>
    <div class="table-responsive">
        <table class="table table-sm" id="rules-table">
            <thead>
            <tr>
                <th><?php echo xlt('Actions'); ?></th>
                <th><?php echo xlt('Target'); ?></th>
                <th><?php echo xlt('Surface'); ?></th>
                <th><?php echo xlt('Scope'); ?></th>
                <th><?php echo xlt('Requires'); ?></th>
                <th><?php echo xlt('Order'); ?></th>
                <th><?php echo xlt('Status'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php if ($assignments === []) { ?>
                <tr><td colspan="7" class="text-muted"><?php echo xlt('No rules yet'); ?></td></tr>
            <?php } ?>
            <?php foreach ($assignments as $assignment) {
                $scopeParts = [];
                if ($assignment->facility !== null) {
                    $scopeParts[] = $scopeLabels['facility'] . ': ' . $nameOrId($facilityNames[(string)$assignment->facility] ?? null, $assignment->facility);
                }
                if ($assignment->provider !== null) {
                    $scopeParts[] = $scopeLabels['provider'] . ': ' . $nameOrId($providerNames[(string)$assignment->provider] ?? null, $assignment->provider);
                }
                if ($assignment->visitCategoryId !== null) {
                    $scopeParts[] = $scopeLabels['visit_category_id'] . ': '
                        . $nameOrId($visitCategoryNames[(string)$assignment->visitCategoryId] ?? null, $assignment->visitCategoryId);
                }
                if (($assignment->clientId ?? '') !== '') {
                    $scopeParts[] = $scopeLabels['client_id'] . ': '
                        . ($smartAppNames[(string)$assignment->clientId]
                            ?? $assignment->clientId . ' ' . xl('(inactive or removed)'));
                }
                $targetText = $assignment->isCategoryWide()
                    ? sprintf(xl('every questionnaire in %s'), $categoryLabel((string)$assignment->category))
                    : ($assignment->resourceName ?? ('#' . $assignment->resourceId));
                $scopeText = $scopeParts === [] ? xl('any') : implode('; ', $scopeParts);
                $rowClasses = [];
                if (!$assignment->active) {
                    $rowClasses[] = 'text-muted';
                }
                if ($editing !== null && $editing->id === $assignment->id) {
                    // table-active shades with the theme; table-warning keeps a light
                    // background under dark-theme text and becomes unreadable.
                    $rowClasses[] = 'table-active';
                }
                ?>
                <tr class="<?php echo attr(implode(' ', $rowClasses)); ?>"
                    data-surface="<?php echo attr($assignment->surface->value); ?>"
                    data-filter="<?php echo attr(mb_strtolower($targetText . ' ' . $scopeText)); ?>">
                    <td class="text-nowrap">
                        <a
                            class="btn btn-sm btn-primary"
                            href="manage_questionnaire_availability.php?edit=<?php echo attr((string)$assignment->id); ?>#rule-form"
                            onclick="top.restoreSession()"
                        ><?php echo xlt('Edit'); ?></a>
                        <form method="post" action="manage_questionnaire_availability.php" class="d-inline"
                              onsubmit="return top.restoreSession()">
                            <input type="hidden" name="csrf_token_form" value="<?php echo attr($csrfToken); ?>" />
                            <input type="hidden" name="action" value="toggle_rule" />
                            <input type="hidden" name="assignment_id" value="<?php echo attr((string)$assignment->id); ?>" />
                            <input type="hidden" name="to" value="<?php echo $assignment->active ? 'off' : 'on'; ?>" />
                            <button type="submit" class="btn btn-sm btn-secondary">
                                <?php echo $assignment->active ? xlt('Switch off') : xlt('Switch on'); ?>
                            </button>
                        </form>
                        <form method="post" action="manage_questionnaire_availability.php" class="d-inline"
                              data-confirm="<?php echo xla('Remove this rule? This cannot be undone. Switch it off instead to keep a record of it.'); ?>"
                              onsubmit="return top.restoreSession()">
                            <input type="hidden" name="csrf_token_form" value="<?php echo attr($csrfToken); ?>" />
                            <input type="hidden" name="action" value="delete_rule" />
                            <input type="hidden" name="assignment_id" value="<?php echo attr((string)$assignment->id); ?>" />
                            <button type="submit" class="btn btn-sm btn-link text-danger"><?php echo xlt('Remove'); ?></button>
                        </form>
                    </td>
                    <td>
                        <a
                            href="manage_questionnaire_availability.php?edit=<?php echo attr((string)$assignment->id); ?>#rule-form"
                            onclick="top.restoreSession()"
                            title="<?php echo xla('Edit this rule'); ?>"
                        >
                        <?php if ($assignment->isCategoryWide()) { ?>
                            <em><?php echo text($targetText); ?></em>
                        <?php } else { ?>
                            <?php echo text($targetText); ?>
                        <?php } ?>
                        </a>
                    </td>
                    <td>
                        <?php echo text($surfaceLabels[$assignment->surface->value]); ?>

                    </td>
                    <td><?php echo text($scopeText); ?></td>
                    <td>
                        <?php if ($assignment->aclSection !== null && $assignment->aclLevel !== null) { ?>
                            <span title="<?php echo attr($assignment->aclSection . '/' . $assignment->aclLevel); ?>">
                                <?php echo text($aclLabel($assignment->aclSection, $assignment->aclLevel)); ?>
                            </span>
                        <?php } else {
                            [$defaultSection, $defaultLevel] = $assignment->surface->defaultAcl();
                            ?>
                            <span class="text-muted" title="<?php echo attr($defaultSection . '/' . $defaultLevel); ?>">
                                <?php echo text(sprintf(xl('surface default (%s)'), $aclLabel($defaultSection, $defaultLevel))); ?>
                            </span>
                        <?php } ?>
                    </td>
                    <td><?php echo text((string)$assignment->seq); ?></td>
                    <td>
                        <?php if ($assignment->active) { ?>
                            <span class="badge badge-success"><?php echo xlt('Active'); ?></span>
                        <?php } else { ?>
                            <span class="badge badge-info"><?php echo xlt('Off'); ?></span>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <hr />
    <h4 id="rule-form">
        <?php echo $editing !== null ? xlt('Edit rule') : xlt('Add a rule'); ?>
    </h4>
    <p class="text-muted">
        <?php if ($editing !== null) { ?>
            <?php echo xlt('Changing the target or scope edits this rule in place, keeping its history. Switching it on or off is done from the list above.'); ?>
        <?php } else { ?>
            <?php echo xlt('Use a rule for what the grid cannot express: every questionnaire in a category, or a questionnaire offered only at one facility, for one provider, for one kind of visit, or to one SMART app. Rules are additive, so to limit a questionnaire to certain visits, also untick its box in the grid.'); ?>
        <?php } ?>
    </p>
    <form method="post" action="<?php echo attr($formAction); ?>" onsubmit="return top.restoreSession()">
        <input type="hidden" name="csrf_token_form" value="<?php echo attr($csrfToken); ?>" />
        <input type="hidden" name="action" value="save_rule" />
        <input type="hidden" name="assignment_id" value="<?php echo attr($editing !== null ? (string)$editing->id : ''); ?>" />
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="surface"><?php echo xlt('Surface'); ?></label>
                <select class="form-control" id="surface" name="surface" aria-describedby="scope-hint">
                    <?php foreach ($surfaces as $surface) { ?>
                        <option
                            value="<?php echo attr($surface->value); ?>"
                            data-scopes="<?php echo attr(json_encode($surface->supportedScopes(), JSON_THROW_ON_ERROR)); ?>"
                            data-hint="<?php echo attr($scopeHint($surface)); ?>"
                            <?php echo $selectedIf($surface->value, $editing?->surface->value); ?>
                        ><?php echo text($surfaceLabels[$surface->value]); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label for="resource_id"><?php echo xlt('Questionnaire'); ?></label>
                <select class="form-control" id="resource_id" name="resource_id">
                    <option value=""><?php echo xlt('-- use a category instead --'); ?></option>
                    <?php foreach ($questionnaires as $questionnaire) { ?>
                        <option value="<?php echo attr((string)$questionnaire['id']); ?>" <?php echo $selectedIf((string)$questionnaire['id'], $editing?->resourceId !== null ? (string)$editing->resourceId : null); ?>>
                            <?php echo text($questionnaire['name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label for="category"><?php echo xlt('Category'); ?></label>
                <select class="form-control" id="category" name="category">
                    <option value=""><?php echo xlt('-- choose a questionnaire instead --'); ?></option>
                    <?php foreach ($categoryTitles as $categoryValue => $categoryTitle) { ?>
                        <option value="<?php echo attr((string)$categoryValue); ?>" <?php echo $selectedIf((string)$categoryValue, $editing !== null && $editing->isCategoryWide() ? (string)$editing->category : null); ?>>
                            <?php echo text($categoryTitle); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-md-2">
                <label for="seq"><?php echo xlt('Order'); ?></label>
                <input type="number" class="form-control" id="seq" name="seq" value="<?php echo attr($editing !== null ? (string)$editing->seq : '0'); ?>" min="0" />
            </div>
        </div>
        <p class="small text-muted mb-2" id="scope-hint"></p>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="facility"><?php echo xlt('Facility'); ?></label>
                <select class="form-control" id="facility" name="facility" data-scope="facility">
                    <option value=""><?php echo xlt('Any'); ?></option>
                    <?php foreach ($facilities as $facility) { ?>
                        <option value="<?php echo attr($facility['id']); ?>" <?php echo $selectedIf($facility['id'], $editing?->facility !== null ? (string)$editing->facility : null); ?>>
                            <?php echo text($facility['label']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label for="provider"><?php echo xlt('Provider'); ?></label>
                <select class="form-control" id="provider" name="provider" data-scope="provider">
                    <option value=""><?php echo xlt('Any'); ?></option>
                    <?php foreach ($providers as $provider) { ?>
                        <option value="<?php echo attr($provider['id']); ?>" <?php echo $selectedIf($provider['id'], $editing?->provider !== null ? (string)$editing->provider : null); ?>>
                            <?php echo text($provider['label']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label for="visit_category_id"><?php echo xlt('Visit category'); ?></label>
                <select class="form-control" id="visit_category_id" name="visit_category_id" data-scope="visit_category_id">
                    <option value=""><?php echo xlt('Any'); ?></option>
                    <?php foreach ($visitCategories as $visitCategory) { ?>
                        <option value="<?php echo attr($visitCategory['id']); ?>" <?php echo $selectedIf($visitCategory['id'], $editing?->visitCategoryId !== null ? (string)$editing->visitCategoryId : null); ?>>
                            <?php echo text($visitCategory['label']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label for="client_id"><?php echo xlt('SMART app'); ?></label>
                <select class="form-control" id="client_id" name="client_id" data-scope="client_id">
                    <option value=""><?php echo xlt('Any'); ?></option>
                    <?php if ($editingClientId !== null && !isset($smartAppNames[$editingClientId])) { ?>
                        <option value="<?php echo attr($editingClientId); ?>" selected>
                            <?php echo text($editingClientId . ' ' . xl('(inactive or removed)')); ?>
                        </option>
                    <?php } ?>
                    <?php foreach ($smartAppNames as $appId => $appName) { ?>
                        <option value="<?php echo attr((string)$appId); ?>" <?php echo $selectedIf((string)$appId, $editingClientId); ?>>
                            <?php echo text($appName); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="acl"><?php echo xlt('Required permission'); ?></label>
                <select class="form-control" id="acl" name="acl">
                    <option value=""><?php echo xlt('Surface default'); ?></option>
                    <?php if ($editingAcl !== null && !isset($aclLabels[$editingAcl])) { ?>
                        <option value="<?php echo attr($editingAcl); ?>" selected>
                            <?php echo text(sprintf(xl('%s (not in the permission list)'), str_replace('|', '/', $editingAcl))); ?>
                        </option>
                    <?php } ?>
                    <?php foreach ($aclChoices as $section => $levels) { ?>
                        <optgroup label="<?php echo attr($aclSectionNames[$section] ?? $section); ?>">
                            <?php foreach ($levels as $pair => $levelLabel) { ?>
                                <option value="<?php echo attr($pair); ?>" <?php echo $selectedIf($pair, $editingAcl); ?>>
                                    <?php echo text($levelLabel); ?>
                                </option>
                            <?php } ?>
                        </optgroup>
                    <?php } ?>
                </select>
                <small class="form-text text-muted">
                    <?php echo xlt('Who may see this questionnaire. Surface default keeps the check that page already makes.'); ?>
                </small>
            </div>
        </div>
        <?php if ($editing !== null) { ?>
            <button type="submit" class="btn btn-primary"><?php echo xlt('Save changes'); ?></button>
            <a class="btn btn-link" href="manage_questionnaire_availability.php" onclick="top.restoreSession()"><?php echo xlt('Cancel'); ?></a>
        <?php } else { ?>
            <button type="submit" class="btn btn-primary"><?php echo xlt('Add rule'); ?></button>
        <?php } ?>
    </form>
    <div class="mb-5"></div>
</div>
<script>
(function () {
    'use strict';

    // Scope fields the chosen surface never fills in are disabled, so they are not posted.
    // The service refuses such a rule too; this keeps the form from offering it.
    const surface = document.getElementById('surface');
    const scopeHint = document.getElementById('scope-hint');
    const scopedFields = document.querySelectorAll('[data-scope]');
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
        scopeHint.textContent = option?.dataset.hint || '';
    };
    surface.addEventListener('change', applySurface);
    applySurface();

    // A rule names one questionnaire or a category, never both.
    const resource = document.getElementById('resource_id');
    const category = document.getElementById('category');
    const applyTarget = function () {
        category.disabled = resource.value !== '';
    };
    resource.addEventListener('change', applyTarget);
    applyTarget();

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

    const filterRows = function (rows, text, surfaceValue) {
        const needle = text.trim().toLowerCase();
        rows.forEach(function (row) {
            const matchesText = needle === '' || (row.dataset.filter || '').includes(needle);
            const matchesSurface = !surfaceValue || row.dataset.surface === surfaceValue;
            row.hidden = !(matchesText && matchesSurface);
        });
    };

    const gridFilter = document.getElementById('grid-filter');
    if (gridFilter) {
        const gridRows = document.querySelectorAll('#availability-grid tbody tr');
        gridFilter.addEventListener('input', function () {
            filterRows(gridRows, gridFilter.value, '');
        });
    }

    const rulesFilter = document.getElementById('rules-filter');
    const rulesSurface = document.getElementById('rules-surface');
    if (rulesFilter && rulesSurface) {
        const ruleRows = document.querySelectorAll('#rules-table tbody tr[data-surface]');
        const applyRulesFilter = function () {
            filterRows(ruleRows, rulesFilter.value, rulesSurface.value);
        };
        rulesFilter.addEventListener('input', applyRulesFilter);
        rulesSurface.addEventListener('change', applyRulesFilter);
    }
})();
</script>
</body>
</html>
