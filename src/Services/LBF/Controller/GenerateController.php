<?php

/**
 * Generate screen.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\LBF\Controller;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfInvalidException;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\ESign\SignatureWriteLock;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\LBF\Bootstrap;
use OpenEMR\Services\LBF\FormLockedException;
use OpenEMR\Services\LBF\Identifiers;
use OpenEMR\Services\LBF\LayoutCatalog;
use OpenEMR\Services\LBF\LbfReader;
use OpenEMR\Services\LBF\LbfWriter;
use OpenEMR\Services\LBF\MissingFormException;
use OpenEMR\Services\LBF\Queries;
use OpenEMR\Services\LBF\StaleParagraphException;
use OpenEMR\Services\LBF\StatementApplier;
use OpenEMR\Services\LBF\StatementEngine;
use OpenEMR\Services\LBF\StatementParagraph;
use OpenEMR\Services\LBF\StatementRepository;
use OpenEMR\Services\LBF\Values;
use Symfony\Component\HttpFoundation\Request;

class GenerateController
{
    /**
     * @param Bootstrap $bootstrap Module Twig and public URL helper.
     */
    public function __construct(
        private readonly Bootstrap $bootstrap
    ) {
    }

    /**
     * Render the generate screen and persist an edited paragraph on POST.
     */
    public function run(Request $request): void
    {
        $twig = $this->bootstrap->getTwig();
        if (!AclMain::aclCheckCore('encounters', 'notes')) {
            http_response_code(403);
            echo $twig->render('error/400.html.twig', [
                'statusCode' => 403,
                'errorMessage' => xl('Access Denied'),
            ]);
            return;
        }

        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $authUser = Values::asString($session->get('authUser'));
        $notesAny = AclMain::aclCheckCore('encounters', 'notes_a');
        $notesOwn = AclMain::aclCheckCore('encounters', 'notes');

        $catalog = new LayoutCatalog();
        $repo = new StatementRepository();
        $reader = new LbfReader();
        $writer = new LbfWriter();
        $engine = new StatementEngine();
        $applier = new StatementApplier();

        $layouts = $catalog->listLbfForms();
        $ruleFormIds = $repo->formIdsWithRules();
        $formChoices = [];
        foreach ($layouts as $layout) {
            if (in_array($layout['form_id'], $ruleFormIds, true)) {
                $formChoices[] = $layout;
            }
        }

        $message = '';
        $error = '';
        $formId = $this->stringParam($request, 'form_id');
        $invalidFormId = false;
        if ($formId !== '' && !Identifiers::isFieldId($formId)) {
            $error = xl('Invalid form.');
            $formId = '';
            $invalidFormId = true;
        }

        $pid = $this->intParam($request, 'pid');
        $instanceId = $this->intParam($request, 'instance_id');
        if ($formId !== '' && !in_array($formId, $ruleFormIds, true)) {
            $error = xl('This form has no statement rules.');
            $formId = '';
            $instanceId = 0;
            $invalidFormId = true;
        }
        if ($pid > 0) {
            $have = $this->visibleFormdirs($reader, $pid, $ruleFormIds, $notesAny, $notesOwn, $authUser);
            $limited = self::limitFormsToPatient($formChoices, $have, $formId, $instanceId);
            $formChoices = $limited[0];
            $formId = Values::asString($limited[1]);
            $instanceId = Values::asInt($limited[2]);
            if (!$invalidFormId && !$request->isMethod('POST') && $formId === '' && count($formChoices) === 1) {
                $formId = Values::asString($formChoices[0]['form_id'] ?? '');
            }
        }

        $actions = [];
        $rules = [];
        $patient = '';
        $instances = [];
        $values = [];
        $meta = [];
        $paragraphField = '';
        $paragraphTitle = '';
        $paragraphCurrent = '';
        $encounter = 0;

        $selectedInstance = null;
        $layoutWrite = $this->layoutWriteAllowed($catalog, $formId);
        if (!$layoutWrite && $error === '') {
            $error = xl('Access Denied');
        }
        if ($layoutWrite && $formId !== '' && $pid > 0) {
            $instances = $this->visibleInstances(
                $reader->instancesForPatient($formId, $pid),
                $notesAny,
                $notesOwn,
                $authUser
            );
            if (!$request->isMethod('POST') && $instanceId === 0 && count($instances) === 1) {
                $instanceId = Values::asInt($instances[0]['instance_id']);
            }
        }

        if ($layoutWrite && $instanceId > 0 && $formId !== '') {
            $row = $reader->instanceRow($instanceId, $formId);
            if ($row === null) {
                $error = xl('Form instance not found.');
                $instanceId = 0;
            } elseif ($pid > 0 && Values::rowInt($row, 'pid') !== $pid) {
                $error = xl('That form does not belong to this patient.');
                if (!$request->request->has('generate')) {
                    $instanceId = 0;
                }
            } else {
                $pid = Values::rowInt($row, 'pid');
                $encounter = Values::rowInt($row, 'encounter');
                if (!$this->mayReadEncounter($reader, $pid, $encounter, $notesAny, $notesOwn, $authUser)) {
                    $error = xl('Access Denied');
                    $instanceId = 0;
                } else {
                    $values = $reader->readValues($instanceId);
                    $meta = $catalog->fieldMeta($formId);
                    $paragraphField = $catalog->paragraphField($formId);
                    if ($paragraphField === '') {
                        $error = $error !== '' ? $error : xl('No paragraph field is configured for this form.');
                    } else {
                        $paragraphTitle = $meta[$paragraphField]['title'] ?? $paragraphField;
                        $paragraphCurrent = $values[$paragraphField] ?? '';
                        $rules = $repo->rulesForForm($formId, true);
                        $actions = $engine->evaluate($formId, $values, $rules, $reader->patientSex($pid));
                    }
                }
            }
        } elseif (!$layoutWrite) {
            $instanceId = 0;
            $instances = [];
        }

        if ($pid > 0) {
            $have = $this->visibleFormdirs($reader, $pid, $ruleFormIds, $notesAny, $notesOwn, $authUser);
            $previousFormId = $formId;
            $limited = self::limitFormsToPatient($formChoices, $have, $formId, $instanceId);
            $formChoices = $limited[0];
            $formId = Values::asString($limited[1]);
            $instanceId = Values::asInt($limited[2]);
            if ($previousFormId !== '' && $formId === '') {
                $values = [];
                $rules = [];
                $actions = [];
                $paragraphField = '';
                $paragraphTitle = '';
                $paragraphCurrent = '';
                $encounter = 0;
                $selectedInstance = null;
                $instances = [];
                if ($error === '') {
                    $error = xl('This form is not on this patient.');
                }
            } elseif (!$invalidFormId && !$request->isMethod('POST') && $formId === '' && count($formChoices) === 1) {
                $formId = Values::asString($formChoices[0]['form_id'] ?? '');
            }
            $patient = $reader->patientName($pid);
            $layoutWrite = $this->layoutWriteAllowed($catalog, $formId);
            if (!$layoutWrite) {
                $instances = [];
                $instanceId = 0;
                if ($error === '') {
                    $error = xl('Access Denied');
                }
            } elseif ($formId !== '' && $instances === []) {
                $instances = $this->visibleInstances(
                $reader->instancesForPatient($formId, $pid),
                $notesAny,
                $notesOwn,
                $authUser
                );
            }
        }

        if ($request->isMethod('POST') && $request->request->has('generate')) {
            try {
                CsrfUtils::checkCsrfInput(INPUT_POST);
                $csrfOk = true;
            } catch (CsrfInvalidException) {
                $csrfOk = false;
                $error = xl('Invalid CSRF token');
            }
            if ($csrfOk && !self::maySaveParagraph(
                AclMain::aclCheckCore('encounters', 'notes', '', 'write'),
                $this->layoutWriteAllowed($catalog, $formId)
            )) {
                $error = xl('Access Denied');
            } elseif ($csrfOk && ($instanceId <= 0 || $pid <= 0 || $formId === '')) {
                $error = xl('Select a patient and form instance.');
            } elseif ($csrfOk && !in_array($formId, $ruleFormIds, true)) {
                $error = xl('This form has no statement rules.');
            } elseif ($csrfOk && $paragraphField === '') {
                $error = xl('No paragraph field is configured for this form.');
            } elseif ($csrfOk) {
                $row = $reader->instanceRow($instanceId, $formId);
                if ($row === null || Values::rowInt($row, 'pid') !== $pid) {
                    $error = xl('That form does not belong to this patient.');
                } elseif (!$reader->encounterOwnedBy(Values::rowInt($row, 'pid'), Values::rowInt($row, 'encounter'))) {
                    $error = xl('Encounter does not belong to this patient.');
                } elseif (!$this->mayReadEncounter(
                    $reader,
                    Values::rowInt($row, 'pid'),
                    Values::rowInt($row, 'encounter'),
                    $notesAny,
                    $notesOwn,
                    $authUser
                )) {
                    $error = xl('Access Denied');
                } elseif (!$this->categoryAllows($this->categoryId($reader, Values::rowInt($row, 'pid'), Values::rowInt($row, 'encounter')), true)) {
                    $error = xl('Access Denied');
                } elseif ($this->writeIsLocked($reader, Values::rowInt($row, 'id'), Values::rowInt($row, 'encounter'))) {
                    $error = xl('This form is locked.');
                } elseif ($this->stringParam($request, 'paragraph_field') !== $paragraphField) {
                    $error = xl('The paragraph field changed after this page was opened. Reload it and try again.');
                } elseif (!is_string($request->request->get('paragraph_text'))) {
                    $error = xl('The paragraph was not submitted. Reload the page and try again.');
                } else {
                    $edited = $request->request->getString('paragraph_text');
                    $baseline = $this->stringParam($request, 'paragraph_baseline');
                    $newValues = $applier->apply($values, $actions, 'overwrite', $paragraphField, $edited);
                    $actionsToWrite = $applier->writeActions($paragraphField);
                    $formsId = Values::rowInt($row, 'id');
                    $encounterId = Values::rowInt($row, 'encounter');
                    $lockedForm = SignatureWriteLock::acquire('forms', $formsId);
                    $lockedEncounter = $lockedForm && SignatureWriteLock::acquire('form_encounter', $encounterId);
                    if (!$lockedEncounter) {
                        if ($lockedForm) {
                            SignatureWriteLock::release('forms', $formsId);
                        }
                        $error = xl('The form could not be saved. Try again.');
                    } else {
                        try {
                            $sql = new Queries();
                            $sql->inTransaction(function () use (
                            $reader,
                            $writer,
                            $repo,
                            $instanceId,
                            $paragraphField,
                            $baseline,
                            $newValues,
                            $values,
                            $actionsToWrite,
                            $formId,
                            $pid,
                            $authUser,
                            $formsId,
                            $encounterId
                        ): void {
                                $reader->lockFormRow($instanceId, $formId);
                                if ($this->writeIsLocked($reader, $formsId, $encounterId)) {
                                    throw new FormLockedException('This form is locked.');
                                }
                                $storedNow = $reader->paragraphValueForUpdate($instanceId, $paragraphField);
                                if (!self::paragraphIsCurrent($baseline, $instanceId, $paragraphField, $storedNow)) {
                                    throw new StaleParagraphException('The paragraph changed after this page was opened.');
                                }
                                $oldValues = $values;
                                $oldValues[$paragraphField] = $storedNow;
                                $writer->apply($instanceId, $newValues, $oldValues, $actionsToWrite);
                                $repo->logRun($formId, $pid, $instanceId, $authUser, 'overwrite');
                            });
                            $message = xl('Saved.');
                            try {
                                $values = $reader->readValues($instanceId);
                                $paragraphCurrent = $values[$paragraphField] ?? '';
                                $instances = $this->visibleInstances(
                                    $reader->instancesForPatient($formId, $pid),
                                    $notesAny,
                                    $notesOwn,
                                    $authUser
                                );
                            } catch (\Throwable $refreshFailure) {
                                (new SystemLogger())->error('Saved paragraph could not be reloaded', [
                                    'exception' => $refreshFailure,
                                ]);
                                $paragraphCurrent = $newValues[$paragraphField] ?? $edited;
                                $message = xl('Saved. Reload the page to see the form.');
                            }
                            $after = $this->stringParam($request, 'after');
                            if ($after === 'print' || $after === 'form') {
                                $this->redirectAfterSave($formId, $pid, $instanceId, $after);
                            }
                        } catch (FormLockedException) {
                            $error = xl('This form is locked.');
                        } catch (MissingFormException) {
                            $error = xl('That form is no longer on file.');
                        } catch (StaleParagraphException) {
                            $error = xl('The paragraph changed after this page was opened. Reload it and try again.');
                            $values = $reader->readValues($instanceId);
                            $paragraphCurrent = $values[$paragraphField] ?? '';
                            $actions = $engine->evaluate($formId, $values, $rules, $reader->patientSex($pid));
                        } finally {
                            SignatureWriteLock::release('form_encounter', $encounterId);
                            SignatureWriteLock::release('forms', $formsId);
                        }
                    }
                }
            }
        }

        if ($instanceId > 0) {
            foreach ($instances as $inst) {
                if ($inst['instance_id'] === $instanceId) {
                    $selectedInstance = $inst;
                    break;
                }
            }
        }

        $paragraphProposed = StatementParagraph::fromActions($actions);
        $paragraphEditor = ($message !== '' && $error === '')
            ? $paragraphCurrent
            : self::editorParagraph($paragraphCurrent, $paragraphProposed);
        $measurements = [];
        $traces = [];
        if ($values !== [] && $rules !== []) {
            $seen = [];
            foreach ($rules as $rule) {
                foreach (['source_field_id', 'source_field_id_2'] as $key) {
                    $fid = Values::rowString($rule, $key);
                    if ($fid === '' || isset($seen[$fid])) {
                        continue;
                    }
                    $val = trim($values[$fid] ?? '');
                    if ($val === '') {
                        continue;
                    }
                    $seen[$fid] = true;
                    $measurements[] = [
                        'title' => $meta[$fid]['title'] ?? $fid,
                        'value' => $val,
                    ];
                }
            }
            foreach ($actions as $action) {
                $from = [];
                $src = $action['source_field_id'];
                if ($src !== '') {
                    $from[] = trim(($meta[$src]['title'] ?? $src) . ' ' . $action['source_value']);
                }
                $src2 = $action['source_field_id_2'];
                if ($src2 !== null && $src2 !== '') {
                    $from[] = trim(($meta[$src2]['title'] ?? $src2) . ' ' . ($action['source_value_2'] ?? ''));
                }
                $traces[] = [
                    'from' => implode(', ', $from),
                    'sentence' => $action['sentence'],
                ];
            }
        }

        $printUrl = '';
        $formUrl = '';
        $patientSetUrl = '';
        $csrfTokenValue = '';
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        try {
            $csrfTokenValue = CsrfUtils::collectCsrfToken($session);
        } catch (\RuntimeException) {
            $csrfTokenValue = '';
        } catch (\Throwable $e) {
            throw $e;
        }
        if ($instanceId > 0 && $formId !== '' && $pid > 0 && $csrfTokenValue !== '') {
            $openQuery = 'form_id=' . rawurlencode($formId)
                . '&pid=' . $pid
                . '&instance_id=' . $instanceId
                . '&csrf_token_form=' . rawurlencode($csrfTokenValue);
            $formUrl = $this->bootstrap->pageUrl('open') . '&' . $openQuery . '&dest=form';
            $printUrl = $this->bootstrap->pageUrl('open') . '&' . $openQuery . '&dest=print';
            if ($encounter > 0) {
                $patientSetUrl = OEGlobalsBag::getInstance()->getWebRoot()
                    . '/interface/patient_file/summary/demographics.php?set_pid=' . $pid
                    . '&set_encounterid=' . $encounter;
            }
        }

        echo $twig->render('generate.html.twig', [
            'formChoices' => $formChoices,
            'form_id' => $formId,
            'pid' => $pid,
            'instance_id' => $instanceId,
            'encounter' => $encounter,
            'patient' => $patient,
            'finderUrl' => OEGlobalsBag::getInstance()->getWebRoot()
                . '/interface/main/calendar/find_patient_popup.php?pflag=0',
            'instances' => $instances,
            'selectedInstance' => $selectedInstance,
            'paragraphField' => $paragraphField,
            'paragraphTitle' => $paragraphTitle,
            'paragraphCurrent' => $paragraphCurrent,
            'paragraphBaseline' => self::paragraphBaseline($instanceId, $paragraphField, $paragraphCurrent),
            'paragraphProposed' => $paragraphProposed,
            'paragraphEditor' => $paragraphEditor,
            'measurements' => $measurements,
            'traces' => $traces,
            'message' => $message,
            'error' => $error,
            'printUrl' => $printUrl,
            'formUrl' => $formUrl,
            'patientSetUrl' => $patientSetUrl,
            'csrfTokenValue' => $csrfTokenValue,
            'canSave' => self::maySaveParagraph(
                AclMain::aclCheckCore('encounters', 'notes', '', 'write'),
                $this->layoutWriteAllowed($catalog, $formId)
            ),
            'canAdmin' => AclMain::aclCheckCore('admin', 'super'),
            'activeTab' => 'generate',
            'postUrl' => $this->bootstrap->pageUrl('generate'),
            'generateUrl' => $this->bootstrap->pageUrl('generate'),
            'adminUrl' => $this->bootstrap->pageUrl('admin'),
            'assetBase' => $this->bootstrap->getPublicUrl() . 'assets/',
        ]);
    }

    /**
     * Text stored on the form: the note already there, plus generated
     * sentences that are not already in it.
     */
    public static function editorParagraph(string $current, string $proposed): string
    {
        if ($proposed === '') {
            return $current;
        }
        $merged = (new StatementApplier())->apply(
            ['summary' => $current],
            [],
            'append',
            'summary',
            $proposed
        );
        return $merged['summary'] ?? $current;
    }

    /**
     * After a save, open the printed form or the encounter that shows it.
     */
    private function redirectAfterSave(string $formId, int $pid, int $instanceId, string $dest): never
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $token = '';
        try {
            $token = CsrfUtils::collectCsrfToken($session);
        } catch (\RuntimeException) {
            $token = '';
        }
        $url = $this->bootstrap->pageUrl('open') . '&form_id=' . rawurlencode($formId)
            . '&pid=' . $pid
            . '&instance_id=' . $instanceId
            . '&dest=' . rawurlencode($dest)
            . '&csrf_token_form=' . rawurlencode($token);
        header('Location: ' . $url, true, 302);
        exit;
    }

    /**
     * Keep layouts this patient has. Clear form_id and instance_id when the
     * selected form is not in that list.
     *
     * @param list<array<string, mixed>> $formChoices
     * @param list<string> $have
     * @return array{0: list<array<string, mixed>>, 1: string, 2: int}
     */
    public static function limitFormsToPatient(
        array $formChoices,
        array $have,
        string $formId,
        int $instanceId
    ): array {
        $formChoices = array_values(array_filter(
            $formChoices,
            static fn (array $layout): bool => in_array($layout['form_id'] ?? '', $have, true)
        ));
        if ($formId !== '' && !in_array($formId, $have, true)) {
            $formId = '';
            $instanceId = 0;
        }
        return [$formChoices, $formId, $instanceId];
    }

    /**
     * True when this user may read or overwrite the layout.
     *
     * Encounter notes are checked earlier. A missing or inactive layout
     * is refused. An empty rule on an active layout adds nothing. A super
     * user is allowed, matching the layout form.
     */
    private function layoutWriteAllowed(LayoutCatalog $catalog, string $formId): bool
    {
        if ($formId === '') {
            return true;
        }
        if (!$catalog->isActiveLbfForm($formId)) {
            return false;
        }
        $acoSpec = $catalog->acoSpec($formId);

        return self::layoutWriteDecision(
            $acoSpec,
            AclMain::aclCheckCore('admin', 'super'),
            AclMain::aclCheckAcoSpec($acoSpec, '', 'write')
        );
    }

    /**
     * A view of encounter notes is not enough to store a paragraph.
     *
     * The layout rule is checked as well. An empty layout rule does not
     * replace the notes write.
     */
    public static function maySaveParagraph(bool $hasNotesWrite, bool $layoutAllowsWrite): bool
    {
        return $hasNotesWrite && $layoutAllowsWrite;
    }

    /**
     * Decide write access from an already loaded layout access rule.
     *
     * An inactive layout is refused before the access rule is considered.
     */
    public static function layoutWriteDecision(
        string $acoSpec,
        bool $isSuperUser,
        bool $hasLayoutWrite,
        bool $layoutIsActive = true,
    ): bool {
        if (!$layoutIsActive) {
            return false;
        }
        if ($isSuperUser || $acoSpec === '') {
            return true;
        }

        return $hasLayoutWrite;
    }

    /**
     * Form layouts that have at least one encounter this user may open.
     *
     * @param list<string> $formIds
     * @return list<string>
     */
    private function visibleFormdirs(
        LbfReader $reader,
        int $pid,
        array $formIds,
        bool $notesAny,
        bool $notesOwn,
        string $authUser
    ): array {
        $have = [];
        foreach ($reader->encountersForPatient($pid, $formIds) as $row) {
            if (!$this->rowVisible($row, $notesAny, $notesOwn, $authUser)) {
                continue;
            }
            if (!$this->categoryAllows(Values::asInt($row['pc_catid'] ?? 0), false)) {
                continue;
            }
            $dir = $row['formdir'];
            if (!in_array($dir, $have, true)) {
                $have[] = $dir;
            }
        }
        return $have;
    }

    /**
     * Drop instances the user cannot open.
     *
     * @param list<array<string, mixed>> $instances
     * @return list<array<string, mixed>>
     */
    private function visibleInstances(
        array $instances,
        bool $notesAny,
        bool $notesOwn,
        string $authUser
    ): array {
        $kept = [];
        foreach ($instances as $instance) {
            if (!$this->rowVisible($instance, $notesAny, $notesOwn, $authUser)) {
                continue;
            }
            if (!$this->categoryAllows(Values::asInt($instance['pc_catid'] ?? 0), false)) {
                continue;
            }
            $kept[] = $instance;
        }
        return $kept;
    }

    /**
     * True when this user may read or change the encounter.
     */
    private function mayReadEncounter(
        LbfReader $reader,
        int $pid,
        int $encounter,
        bool $notesAny,
        bool $notesOwn,
        string $authUser
    ): bool {
        $policy = $reader->encounterPolicy($pid, $encounter);
        if ($policy === null) {
            return false;
        }
        if (!$this->categoryAllows(Values::asInt($policy['pc_catid'] ?? 0), false)) {
            return false;
        }

        return $this->rowVisible($policy, $notesAny, $notesOwn, $authUser);
    }

    /**
     * Encounter-list rule: any-encounter notes, or this user's own encounter,
     * and a sensitivity the user is allowed to see.
     *
     * @param array<string, mixed> $row
     */
    private function rowVisible(array $row, bool $notesAny, bool $notesOwn, string $authUser): bool
    {
        $sensitivity = Values::asString($row['sensitivity'] ?? '');
        $sensitivityAllowed = $sensitivity === '' || AclMain::aclCheckCore('sensitivities', $sensitivity);

        return self::encounterVisible(
            $notesAny,
            $notesOwn,
            Values::asString($row['encounter_user'] ?? ''),
            $authUser,
            $sensitivityAllowed
        );
    }

    /**
     * Own-encounter notes do not open someone else's encounter.
     */
    public static function encounterVisible(
        bool $notesAny,
        bool $notesOwn,
        string $encounterUser,
        string $authUser,
        bool $sensitivityAllowed
    ): bool {
        if (!$sensitivityAllowed) {
            return false;
        }
        if ($notesAny) {
            return true;
        }

        return $notesOwn && $encounterUser !== '' && $encounterUser === $authUser;
    }

    /**
     * Hash of the instance, the paragraph field, and the text the editor loaded.
     */
    public static function paragraphBaseline(int $instanceId, string $fieldId, string $stored): string
    {
        return hash('sha256', $instanceId . "\n" . $fieldId . "\n" . $stored);
    }

    /**
     * True when the stored paragraph is still the field and text the editor loaded.
     */
    public static function paragraphIsCurrent(string $baseline, int $instanceId, string $fieldId, string $stored): bool
    {
        if (strlen($baseline) !== 64) {
            return false;
        }

        return hash_equals($baseline, self::paragraphBaseline($instanceId, $fieldId, $stored));
    }

    /**
     * An empty category rule does not restrict the encounter.
     */
    public static function categorySpecAllows(string $acoSpec, bool $permitted): bool
    {
        if ($acoSpec === '') {
            return true;
        }

        return $permitted;
    }

    /**
     * Calendar-category read for a list or preview, and write for a save.
     */
    private function categoryAllows(int $pcCatId, bool $write): bool
    {
        if ($pcCatId <= 0) {
            return true;
        }
        $spec = AclMain::fetchPostCalendarCategoryACO($pcCatId);
        if (!is_string($spec) || !str_contains($spec, '|')) {
            return self::categorySpecAllows('', false);
        }
        [$section, $value] = explode('|', $spec, 2);
        if ($section === '' || $value === '') {
            return self::categorySpecAllows('', false);
        }
        $permitted = $write
            ? AclMain::aclCheckCore($section, $value, '', 'write')
            : AclMain::aclCheckCore($section, $value);

        return self::categorySpecAllows($spec, $permitted === true);
    }

    private function categoryId(LbfReader $reader, int $pid, int $encounter): int
    {
        $policy = $reader->encounterPolicy($pid, $encounter);
        if ($policy === null) {
            return 0;
        }

        return Values::asInt($policy['pc_catid'] ?? 0);
    }

    /**
     * Same lock decision as the encounter form: the form's own lock, then
     * the encounter lock when that setting is on.
     */
    private function writeIsLocked(LbfReader $reader, int $formsId, int $encounter): bool
    {
        $globals = OEGlobalsBag::getInstance();
        if ($globals->getBoolean('lock_esign_individual') && $reader->signatureIsLock($formsId, 'forms')) {
            return true;
        }

        return $globals->getBoolean('lock_esign_all') && $reader->signatureIsLock($encounter, 'form_encounter');
    }

    /**
     * First matching POST or GET string for $key.
     */
    private function stringParam(Request $request, string $key): string
    {
        $posted = $request->request->get($key);
        if (is_string($posted)) {
            return $posted;
        }
        $query = $request->query->get($key);
        if (is_string($query)) {
            return $query;
        }
        return '';
    }

    /**
     * Integer form of stringParam(), or 0.
     */
    private function intParam(Request $request, string $key): int
    {
        return Values::asInt($this->stringParam($request, $key));
    }
}
