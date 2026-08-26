<?php

/**
 * Generate a paragraph onto an LBF textarea for one form instance.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\LbfStatements\Controller;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfInvalidException;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\LbfStatements\Bootstrap;
use OpenEMR\Modules\LbfStatements\Identifiers;
use OpenEMR\Modules\LbfStatements\LayoutCatalog;
use OpenEMR\Modules\LbfStatements\LbfReader;
use OpenEMR\Modules\LbfStatements\LbfWriter;
use OpenEMR\Modules\LbfStatements\StatementApplier;
use OpenEMR\Modules\LbfStatements\StatementEngine;
use OpenEMR\Modules\LbfStatements\StatementParagraph;
use OpenEMR\Modules\LbfStatements\StatementRepository;
use OpenEMR\Modules\LbfStatements\Values;
use Symfony\Component\HttpFoundation\Request;

class GenerateController
{
    public function __construct(
        private readonly Bootstrap $bootstrap
    ) {
    }

    public function run(Request $request): void
    {
        $twig = $this->bootstrap->getTwig();
        if (!AclMain::aclCheckCore('encounters', 'notes')) {
            echo $twig->render('error/400.html.twig', [
                'statusCode' => 401,
                'errorMessage' => xl('Access Denied'),
            ]);
            return;
        }

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

        $formId = $this->stringParam($request, 'form_id');
        if ($formId === '' && $formChoices !== []) {
            $formId = $formChoices[0]['form_id'];
        }
        if ($formId !== '') {
            Identifiers::assertFieldId($formId);
        }

        if ($request->query->get('ajax') === 'patients') {
            header('Content-Type: application/json; charset=utf-8');
            $q = $this->stringParam($request, 'q');
            echo json_encode(
                $formId !== '' ? $reader->searchPatientsWithForm($formId, $q) : [],
                JSON_THROW_ON_ERROR
            );
            return;
        }

        $pid = $this->intParam($request, 'pid');
        $instanceId = $this->intParam($request, 'instance_id');
        if ($pid > 0) {
            $have = $reader->formdirsForPatient($pid, $ruleFormIds);
            if ($have !== []) {
                $formChoices = array_values(array_filter(
                    $formChoices,
                    static function (array $layout) use ($have): bool {
                        return in_array($layout['form_id'], $have, true);
                    }
                ));
                if ($formId === '' || !in_array($formId, $have, true)) {
                    $formId = $have[0];
                }
            }
        }

        $message = '';
        $error = '';
        $needMode = false;
        $actions = [];
        $rules = [];
        $patient = $reader->patientName($pid);
        $instances = [];
        $values = [];
        $meta = [];
        $paragraphField = '';
        $paragraphTitle = '';
        $paragraphCurrent = '';
        $encounter = 0;

        if ($formId !== '' && $pid > 0) {
            $instances = $reader->instancesForPatient($formId, $pid);
            if ($instanceId === 0 && $instances !== []) {
                $instanceId = $instances[0]['instance_id'];
            }
        }

        if ($instanceId > 0 && $formId !== '') {
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
                $values = $reader->readValues($instanceId);
                $meta = $catalog->fieldMeta($formId);
                $paragraphField = $catalog->paragraphField($formId);
                $paragraphTitle = $meta[$paragraphField]['title'] ?? $paragraphField;
                $paragraphCurrent = $values[$paragraphField] ?? '';
                $rules = $repo->rulesForForm($formId, true);
                $actions = $engine->evaluate($formId, $values, $rules);
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
            if ($csrfOk && ($instanceId <= 0 || $pid <= 0 || $formId === '')) {
                $error = xl('Select a patient and form instance.');
            } elseif ($csrfOk) {
                $row = $reader->instanceRow($instanceId, $formId);
                if ($row === null || Values::rowInt($row, 'pid') !== $pid) {
                    $error = xl('That form does not belong to this patient.');
                } elseif (!$reader->encounterOwnedBy(Values::rowInt($row, 'pid'), Values::rowInt($row, 'encounter'))) {
                    $error = xl('Encounter does not belong to this patient.');
                } else {
                    $mode = $this->stringParam($request, 'write_mode');
                    $nonempty = $applier->targetsNonempty($values, $actions, $paragraphField);
                    if ($nonempty && !in_array($mode, ['append', 'overwrite'], true)) {
                        $needMode = true;
                    } else {
                        if ($mode === '') {
                            $mode = 'overwrite';
                        }
                        $edited = $this->stringParam($request, 'paragraph_text');
                        $newValues = $applier->apply($values, $actions, $mode, $paragraphField, $edited);
                        $writer->write($instanceId, $newValues, $values, $applier->writeActions($paragraphField));
                        $session = SessionWrapperFactory::getInstance()->getActiveSession();
                        $repo->logRun(
                            $formId,
                            $pid,
                            $instanceId,
                            Values::asString($session->get('authUser')),
                            $mode
                        );
                        $values = $reader->readValues($instanceId);
                        $paragraphCurrent = $values[$paragraphField] ?? '';
                        $message = xl('Saved.');
                    }
                }
            }
        }

        $paragraphProposed = StatementParagraph::fromActions($actions);
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
        $webroot = OEGlobalsBag::getInstance()->getWebRoot();
        if ($instanceId > 0 && $formId !== '' && $pid > 0) {
            $printUrl = $webroot . '/interface/forms/LBF/printable.php?formname='
                . urlencode($formId) . '&formid=' . $instanceId
                . '&patientid=' . $pid . '&visitid=' . $encounter;
            $formUrl = $webroot . '/interface/forms/LBF/new.php?formname='
                . urlencode($formId) . '&id=' . $instanceId;
        }

        echo $twig->render('generate.html.twig', [
            'formChoices' => $formChoices,
            'form_id' => $formId,
            'pid' => $pid,
            'instance_id' => $instanceId,
            'encounter' => $encounter,
            'patient' => $patient,
            'searchUrl' => $this->bootstrap->getPublicUrl() . 'index.php',
            'instances' => $instances,
            'paragraphField' => $paragraphField,
            'paragraphTitle' => $paragraphTitle,
            'paragraphCurrent' => $paragraphCurrent,
            'paragraphProposed' => $paragraphProposed,
            'measurements' => $measurements,
            'traces' => $traces,
            'needMode' => $needMode,
            'message' => $message,
            'error' => $error,
            'printUrl' => $printUrl,
            'formUrl' => $formUrl,
            'canAdmin' => AclMain::aclCheckCore('admin', 'super'),
            'activeTab' => 'generate',
            'postUrl' => $this->bootstrap->getPublicUrl() . 'index.php',
            'generateUrl' => $this->bootstrap->getPublicUrl() . 'index.php',
            'adminUrl' => $this->bootstrap->getPublicUrl() . 'admin.php',
            'assetBase' => $this->bootstrap->getPublicUrl() . 'assets/',
        ]);
    }

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

    private function intParam(Request $request, string $key): int
    {
        return Values::asInt($this->stringParam($request, $key));
    }
}
