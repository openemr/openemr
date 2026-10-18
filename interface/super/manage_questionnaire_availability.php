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
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
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
 * A posted id, or null for "not supplied". InputBag::getInt() already returns an int, so
 * this only has to reject the zero that an absent or non-numeric field produces.
 */
$postedId = static function (string $field) use ($request): ?int {
    $value = $request->request->getInt($field);

    return $value > 0 ? $value : null;
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
            if ($surface === null) {
                throw new \InvalidArgumentException(xl('Choose a surface'));
            }
            $questionnaireId = $postedId('resource_id');
            $category = $postedText('category');
            if ($questionnaireId !== null) {
                // A row names one questionnaire or a category, never both.
                $category = null;
            }

            $record = new AssignmentRecord(
                id: $postedId('assignment_id') ?? 0,
                surface: $surface,
                resourceId: $questionnaireId,
                category: $category,
                facility: $postedId('facility'),
                provider: $postedId('provider'),
                visitCategoryId: $postedId('visit_category_id'),
                clientId: $postedText('client_id'),
                aclSection: $postedText('acl_section'),
                aclLevel: $postedText('acl_level'),
                seq: max(0, $request->request->getInt('seq')),
            );

            if ($record->id > 0) {
                $service->updateAssignment($record);
                $notices[] = xl('Rule updated');
            } else {
                $service->assign($record);
                $notices[] = xl('Rule added');
            }
            // Saved, so leave edit mode; a failed save stays in it so the error is shown
            // next to the rule being edited.
            $ruleSaved = true;
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
        // Validation refusals carry text written for an administrator, so they are shown.
        $errors[] = $e->getMessage();
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
$editId = $ruleSaved ? 0 : $request->query->getInt('edit');
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

$surfaceLabels = [
    QuestionnaireSurface::Dashboard->value => xl('Patient dashboard'),
    QuestionnaireSurface::Encounter->value => xl('Encounter forms'),
    QuestionnaireSurface::Portal->value => xl('Patient portal'),
    QuestionnaireSurface::Smart->value => xl('SMART launch'),
];
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
        <?php echo xlt('Choose where each questionnaire is offered. Rules are additive: a questionnaire is offered while any active rule reaches it, so a badge below marks boxes whose questionnaire is also covered by a category or scoped rule.'); ?>
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
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken(session: $session)); ?>" />
        <input type="hidden" name="action" value="save_grid" />
        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead>
                <tr>
                    <th><?php echo xlt('Questionnaire'); ?></th>
                    <th><?php echo xlt('Category'); ?></th>
                    <?php foreach ($surfaceLabels as $label) { ?>
                        <th class="text-center"><?php echo text($label); ?></th>
                    <?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($questionnaires as $questionnaire) { ?>
                    <tr>
                        <td><?php echo text($questionnaire['name']); ?></td>
                        <td class="text-muted"><?php echo text($questionnaire['category']); ?></td>
                        <?php foreach (array_keys($surfaceLabels) as $surfaceValue) {
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
                            ?>
                            <td class="text-center">
                                <input type="hidden" name="rendered[]" value="<?php echo attr($key); ?>" />
                                <input
                                    type="checkbox"
                                    name="offer[<?php echo attr((string)$questionnaire['id']); ?>][<?php echo attr($surfaceValue); ?>]"
                                    value="1"
                                    <?php echo $isOn ? 'checked' : ''; ?>
                                />
                                <?php foreach ($reasons as $reason) { ?>
                                    <div>
                                        <?php if ($reason === 'category') { ?>
                                            <span class="badge badge-info" title="<?php echo attr(xl('A category rule also offers this here, so it stays available even with this box unchecked. See All rules below.')); ?>">
                                                <?php echo xlt('category rule'); ?>
                                            </span>
                                        <?php } else { ?>
                                            <span class="badge badge-warning" title="<?php echo attr(xl('A scoped rule also offers this here, so it stays available in that scope even with this box unchecked. See All rules below.')); ?>">
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
    </form>
    <?php } ?>

    <hr />
    <h4><?php echo xlt('All rules'); ?></h4>
    <p class="text-muted">
        <?php echo xlt('Every stored rule, including category-wide and scoped rules the grid above cannot show. Switching a rule off keeps it for the record; removing it deletes it.'); ?>
    </p>
    <div class="table-responsive">
        <table class="table table-sm">
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
                    $scopeParts[] = xl('facility') . ' ' . $assignment->facility;
                }
                if ($assignment->provider !== null) {
                    $scopeParts[] = xl('provider') . ' ' . $assignment->provider;
                }
                if ($assignment->visitCategoryId !== null) {
                    $scopeParts[] = xl('visit category') . ' ' . $assignment->visitCategoryId;
                }
                if (($assignment->clientId ?? '') !== '') {
                    $scopeParts[] = xl('client') . ' ' . $assignment->clientId;
                }
                ?>
                <tr class="<?php echo $assignment->active ? '' : 'text-muted'; ?><?php echo ($editing !== null && $editing->id === $assignment->id) ? ' table-warning' : ''; ?>">
                    <td class="text-nowrap">
                        <a
                            class="btn btn-sm btn-primary"
                            href="manage_questionnaire_availability.php?edit=<?php echo attr((string)$assignment->id); ?>#rule-form"
                            onclick="top.restoreSession()"
                        ><?php echo xlt('Edit'); ?></a>
                        <form method="post" action="manage_questionnaire_availability.php" class="d-inline"
                              onsubmit="return top.restoreSession()">
                            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken(session: $session)); ?>" />
                            <input type="hidden" name="action" value="toggle_rule" />
                            <input type="hidden" name="assignment_id" value="<?php echo attr((string)$assignment->id); ?>" />
                            <input type="hidden" name="to" value="<?php echo $assignment->active ? 'off' : 'on'; ?>" />
                            <button type="submit" class="btn btn-sm btn-secondary">
                                <?php echo $assignment->active ? xlt('Switch off') : xlt('Switch on'); ?>
                            </button>
                        </form>
                        <form method="post" action="manage_questionnaire_availability.php" class="d-inline"
                              onsubmit="return top.restoreSession()">
                            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken(session: $session)); ?>" />
                            <input type="hidden" name="action" value="delete_rule" />
                            <input type="hidden" name="assignment_id" value="<?php echo attr((string)$assignment->id); ?>" />
                            <button type="submit" class="btn btn-sm btn-link text-danger"><?php echo xlt('Remove'); ?></button>
                        </form>
                    </td>
                    <td>
                        <a
                            href="manage_questionnaire_availability.php?edit=<?php echo attr((string)$assignment->id); ?>#rule-form"
                            onclick="top.restoreSession()"
                            title="<?php echo attr(xl('Edit this rule')); ?>"
                        >
                        <?php if ($assignment->isCategoryWide()) { ?>
                            <em><?php echo xlt('every questionnaire in'); ?> <?php echo text((string)$assignment->category); ?></em>
                        <?php } else { ?>
                            <?php echo text($assignment->resourceName ?? ('#' . $assignment->resourceId)); ?>
                        <?php } ?>
                        </a>
                    </td>
                    <td><?php echo text($surfaceLabels[$assignment->surface->value]); ?></td>
                    <td><?php echo $scopeParts === [] ? text(xl('any')) : text(implode(', ', $scopeParts)); ?></td>
                    <td>
                        <?php if ($assignment->aclSection !== null && $assignment->aclLevel !== null) { ?>
                            <code><?php echo text($assignment->aclSection . '/' . $assignment->aclLevel); ?></code>
                        <?php } else { ?>
                            <span class="text-muted"><?php echo xlt('surface default'); ?></span>
                        <?php } ?>
                    </td>
                    <td><?php echo text((string)$assignment->seq); ?></td>
                    <td><?php echo $assignment->active ? xlt('Active') : xlt('Off'); ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <hr />
    <h4 id="rule-form">
        <?php echo $editing !== null ? xlt('Edit rule') : xlt('Add a rule'); ?>
    </h4>
    <?php if ($editing !== null) { ?>
        <p class="text-muted">
            <?php echo xlt('Changing the target or scope edits this rule in place, keeping its history. Switching it on or off is done from the list above.'); ?>
        </p>
    <?php } ?>
    <form method="post" action="<?php echo attr($formAction); ?>" onsubmit="return top.restoreSession()">
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken(session: $session)); ?>" />
        <input type="hidden" name="action" value="save_rule" />
        <input type="hidden" name="assignment_id" value="<?php echo attr($editing !== null ? (string)$editing->id : ''); ?>" />
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="surface"><?php echo xlt('Surface'); ?></label>
                <select class="form-control" id="surface" name="surface">
                    <?php foreach ($surfaceLabels as $value => $label) { ?>
                        <option value="<?php echo attr($value); ?>" <?php echo $selectedIf($value, $editing?->surface->value); ?>><?php echo text($label); ?></option>
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
                <input type="text" class="form-control" id="category" name="category"
                       value="<?php echo attr($editing !== null && $editing->isCategoryWide() ? (string)$editing->category : ''); ?>"
                       placeholder="<?php echo attr(xl('only when no questionnaire is chosen')); ?>" />
            </div>
            <div class="form-group col-md-2">
                <label for="seq"><?php echo xlt('Order'); ?></label>
                <input type="number" class="form-control" id="seq" name="seq" value="<?php echo attr($editing !== null ? (string)$editing->seq : '0'); ?>" min="0" />
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="facility"><?php echo xlt('Facility'); ?></label>
                <select class="form-control" id="facility" name="facility">
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
                <select class="form-control" id="provider" name="provider">
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
                <select class="form-control" id="visit_category_id" name="visit_category_id">
                    <option value=""><?php echo xlt('Any'); ?></option>
                    <?php foreach ($visitCategories as $visitCategory) { ?>
                        <option value="<?php echo attr($visitCategory['id']); ?>" <?php echo $selectedIf($visitCategory['id'], $editing?->visitCategoryId !== null ? (string)$editing->visitCategoryId : null); ?>>
                            <?php echo text($visitCategory['label']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label for="client_id"><?php echo xlt('SMART client'); ?></label>
                <input type="text" class="form-control" id="client_id" name="client_id"
                       value="<?php echo attr($editing->clientId ?? ''); ?>"
                       placeholder="<?php echo attr(xl('Any')); ?>" />
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="acl_section"><?php echo xlt('ACL section'); ?></label>
                <input type="text" class="form-control" id="acl_section" name="acl_section"
                       value="<?php echo attr($editing->aclSection ?? ''); ?>"
                       placeholder="<?php echo attr(xl('surface default')); ?>" />
            </div>
            <div class="form-group col-md-3">
                <label for="acl_level"><?php echo xlt('ACL level'); ?></label>
                <input type="text" class="form-control" id="acl_level" name="acl_level"
                       value="<?php echo attr($editing->aclLevel ?? ''); ?>"
                       placeholder="<?php echo attr(xl('surface default')); ?>" />
            </div>
        </div>
        <p class="text-muted">
            <?php echo xlt('Set the ACL section and level together or leave both blank. They are only meaningful as a pair, so half of one is refused.'); ?>
        </p>
        <?php if ($editing !== null) { ?>
            <button type="submit" class="btn btn-primary"><?php echo xlt('Save changes'); ?></button>
            <a class="btn btn-link" href="manage_questionnaire_availability.php" onclick="top.restoreSession()"><?php echo xlt('Cancel'); ?></a>
        <?php } else { ?>
            <button type="submit" class="btn btn-primary"><?php echo xlt('Add rule'); ?></button>
        <?php } ?>
    </form>
    <div class="mb-5"></div>
</div>
</body>
</html>
