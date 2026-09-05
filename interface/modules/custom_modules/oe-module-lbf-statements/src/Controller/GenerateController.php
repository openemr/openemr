<?php

/**
 * Generate screen.
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
            $have = $reader->formdirsForPatient($pid, $ruleFormIds);
            $formChoices = array_values(array_filter(
                $formChoices,
                static fn (array $layout): bool => in_array($layout['form_id'], $have, true)
            ));
            if ($formId !== '' && !in_array($formId, $have, true)) {
                $formId = '';
                $instanceId = 0;
            }
            if (!$invalidFormId && $formId === '' && count($formChoices) === 1) {
                $formId = $formChoices[0]['form_id'];
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
        if ($formId !== '' && $pid > 0) {
            $instances = $reader->instancesForPatient($formId, $pid);
            if ($instanceId === 0 && count($instances) === 1) {
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
                if ($paragraphField === '') {
                    $error = $error !== '' ? $error : xl('No paragraph field is configured for this form.');
                } else {
                    $paragraphTitle = $meta[$paragraphField]['title'] ?? $paragraphField;
                    $paragraphCurrent = $values[$paragraphField] ?? '';
                    $rules = $repo->rulesForForm($formId, true);
                    $actions = $engine->evaluate($formId, $values, $rules);
                }
            }
        }

        if ($pid > 0) {
            $patient = $reader->patientName($pid);
            if ($formId !== '' && $instances === []) {
                $instances = $reader->instancesForPatient($formId, $pid);
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
                } else {
                    $edited = $this->stringParam($request, 'paragraph_text');
                    $newValues = $applier->apply($values, $actions, 'overwrite', $paragraphField, $edited);
                    $writer->write($instanceId, $newValues, $values, $applier->writeActions($paragraphField));
                    $session = SessionWrapperFactory::getInstance()->getActiveSession();
                    $repo->logRun(
                        $formId,
                        $pid,
                        $instanceId,
                        Values::asString($session->get('authUser')),
                        'overwrite'
                    );
                    $values = $reader->readValues($instanceId);
                    $paragraphCurrent = $values[$paragraphField] ?? '';
                    $instances = $reader->instancesForPatient($formId, $pid);
                    $message = xl('Saved.');
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
            $formUrl = $this->bootstrap->getPublicUrl() . 'open_form.php?' . $openQuery . '&dest=form';
            $printUrl = $this->bootstrap->getPublicUrl() . 'open_form.php?' . $openQuery . '&dest=print';
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
            'paragraphProposed' => $paragraphProposed,
            'measurements' => $measurements,
            'traces' => $traces,
            'message' => $message,
            'error' => $error,
            'printUrl' => $printUrl,
            'formUrl' => $formUrl,
            'patientSetUrl' => $patientSetUrl,
            'csrfTokenValue' => $csrfTokenValue,
            'canAdmin' => AclMain::aclCheckCore('admin', 'super'),
            'activeTab' => 'generate',
            'postUrl' => $this->bootstrap->getPublicUrl() . 'index.php',
            'generateUrl' => $this->bootstrap->getPublicUrl() . 'index.php',
            'adminUrl' => $this->bootstrap->getPublicUrl() . 'admin.php',
            'assetBase' => $this->bootstrap->getPublicUrl() . 'assets/',
        ]);
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
