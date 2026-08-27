<?php

/**
 * Rules editor.
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
use OpenEMR\Modules\LbfStatements\Bootstrap;
use OpenEMR\Modules\LbfStatements\Identifiers;
use OpenEMR\Modules\LbfStatements\LayoutCatalog;
use OpenEMR\Modules\LbfStatements\StatementRepository;
use OpenEMR\Modules\LbfStatements\Values;
use Symfony\Component\HttpFoundation\Request;

class AdminController
{
    public function __construct(
        private readonly Bootstrap $bootstrap
    ) {
    }

    public function run(Request $request): void
    {
        $twig = $this->bootstrap->getTwig();
        if (!AclMain::aclCheckCore('admin', 'super')) {
            echo $twig->render('error/400.html.twig', [
                'statusCode' => 401,
                'errorMessage' => xl('Access Denied'),
            ]);
            return;
        }

        $catalog = new LayoutCatalog();
        $repo = new StatementRepository();
        $layouts = $catalog->listLbfForms();

        $formId = $this->stringParam($request, 'form_id');
        if ($formId === '') {
            $withRules = $repo->formIdsWithRules();
            if ($withRules !== []) {
                $formId = $withRules[0];
            } elseif ($layouts !== []) {
                $formId = $layouts[0]['form_id'];
            }
        }
        if ($formId !== '') {
            Identifiers::assertFieldId($formId);
        }

        $message = '';
        $error = '';
        $edit = null;
        $fields = $formId !== '' ? $catalog->fieldMeta($formId) : [];

        if ($request->isMethod('POST')) {
            try {
                CsrfUtils::checkCsrfInput(INPUT_POST);
                $csrfOk = true;
            } catch (CsrfInvalidException) {
                $csrfOk = false;
                $error = xl('Invalid CSRF token');
            }
            if ($csrfOk) {
                $action = $this->stringParam($request, 'action');
                if ($action === 'paragraph' && $formId !== '') {
                    $catalog->saveParagraphField($formId, $this->stringParam($request, 'paragraph_field_id'));
                    $message = xl('Paragraph field saved.');
                } elseif ($action === 'toggle') {
                    $rid = Values::asInt($this->stringParam($request, 'rule_id'));
                    $enabled = Values::asInt($this->stringParam($request, 'enabled')) === 1;
                    try {
                        $repo->setEnabled($rid, $enabled);
                        $message = $enabled ? xl('Rule enabled.') : xl('Rule disabled.');
                    } catch (\InvalidArgumentException) {
                        $error = xl('This numeric range overlaps another band on the same field.');
                    }
                } elseif ($action === 'save') {
                    $data = [
                        'form_id' => $this->stringParam($request, 'form_id') !== ''
                            ? $this->stringParam($request, 'form_id')
                            : $formId,
                        'source_field_id' => $this->stringParam($request, 'source_field_id'),
                        'source_field_id_2' => $this->stringParam($request, 'source_field_id_2'),
                        'op' => $this->stringParam($request, 'op') !== ''
                            ? $this->stringParam($request, 'op')
                            : 'band',
                        'min_value' => $this->stringParam($request, 'min_value'),
                        'max_value' => $this->stringParam($request, 'max_value'),
                        'min_inclusive' => $request->request->has('min_inclusive') ? 1 : 0,
                        'max_inclusive' => $request->request->has('max_inclusive') ? 1 : 0,
                        'match_token' => $this->stringParam($request, 'match_token'),
                        'statement_text' => $this->stringParam($request, 'statement_text'),
                        'seq' => Values::asInt($this->stringParam($request, 'seq')),
                        'enabled' => $request->request->has('enabled') ? 1 : 0,
                    ];
                    try {
                        $id = Values::asInt($this->stringParam($request, 'rule_id'));
                        $repo->saveRule($data, $id > 0 ? $id : null);
                        $message = xl('Rule saved.');
                    } catch (\InvalidArgumentException $e) {
                        $error = match ($e->getMessage()) {
                            'Minimum must be less than or equal to maximum.' => xl('Minimum must be less than or equal to maximum.'),
                            'This numeric range overlaps another band on the same field.' => xl('This numeric range overlaps another band on the same field.'),
                            default => xl('Could not save the rule.'),
                        };
                        $edit = $data;
                        $edit['id'] = Values::asInt($this->stringParam($request, 'rule_id'));
                    }
                }
            }
        }

        if ($request->query->has('edit')) {
            $edit = $repo->getRule(Values::asInt($this->stringParam($request, 'edit')));
        }

        $rules = $formId !== '' ? $repo->rulesForForm($formId, false) : [];
        $nextSeq = 10;
        foreach ($rules as $rule) {
            $nextSeq = max($nextSeq, Values::rowInt($rule, 'seq') + 10);
        }

        $paragraphField = $formId !== '' ? $catalog->paragraphField($formId) : '';
        $bandRules = [];
        foreach ($rules as $rule) {
            if (Values::rowString($rule, 'op') !== 'band') {
                continue;
            }
            $bandRules[] = [
                'id' => Values::rowInt($rule, 'id'),
                'source_field_id' => Values::rowString($rule, 'source_field_id'),
                'source_field_id_2' => Values::rowString($rule, 'source_field_id_2'),
                'min_value' => $rule['min_value'] ?? null,
                'max_value' => $rule['max_value'] ?? null,
                'min_inclusive' => Values::rowInt($rule, 'min_inclusive'),
                'max_inclusive' => Values::rowInt($rule, 'max_inclusive'),
                'enabled' => Values::rowInt($rule, 'enabled'),
            ];
        }

        echo $twig->render('admin.html.twig', [
            'layouts' => $layouts,
            'form_id' => $formId,
            'fields' => $fields,
            'rules' => $rules,
            'edit' => $edit,
            'paragraphField' => $paragraphField,
            'nextSeq' => $nextSeq,
            'bandRules' => $bandRules,
            'message' => $message,
            'error' => $error,
            'canAdmin' => true,
            'activeTab' => 'rules',
            'postUrl' => $this->bootstrap->getPublicUrl() . 'admin.php',
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
}
