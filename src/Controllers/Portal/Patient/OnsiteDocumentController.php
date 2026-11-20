<?php

/**
 * Patient portal documents: the documents page and the `onsitedocument(s)` JSON API.
 *
 * Every action checks access to the document's patient with PortalPatientAccessGuard: a portal
 * patient reaches only their own documents; staff need the portal ACL plus patients/docs.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Portal\Patient;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Forms\CoreFormToPortalUtility;
use OpenEMR\Common\Session\PortalPatientAccessGuard;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Portal\Routing\PortalPrincipal;
use OpenEMR\Portal\Routing\PrincipalKind;
use OpenEMR\Portal\Routing\RouteMatch;
use OpenEMR\Services\DocumentTemplates\DocumentTemplateRender;
use OpenEMR\Services\DocumentTemplates\DocumentTemplateService;
use OpenEMR\Services\PortalDocuments\OnsiteDocumentListQuery;
use OpenEMR\Services\PortalDocuments\OnsiteDocumentRepository;
use OpenEMR\Services\PortalDocuments\OnsiteDocumentSort;
use OpenEMR\Services\PortalDocuments\PortalDocumentNotFoundException;
use OpenEMR\Services\Utils\TranslationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class OnsiteDocumentController
{
    public function __construct(
        private PortalPageRenderer $pages,
        private OnsiteDocumentRepository $documents = new OnsiteDocumentRepository(),
    ) {
    }

    /**
     * The documents page: GET onsitedocuments.
     */
    public function ListView(Request $request, RouteMatch $route, PortalPrincipal $caller): Response
    {
        $query = $request->query;
        // The newest Help template, which the page opens from its help button.
        $help_id = QueryUtils::fetchSingleValue(
            'SELECT `id` FROM `document_templates` WHERE `template_name` = ? ORDER BY `modified_date` DESC LIMIT 1',
            'id',
            ['Help'],
        ) ?? 0;

        // A portal patient sees only their own documents.
        $pid = $caller->patientPid() ?? $query->getInt('pid');
        PortalPatientAccessGuard::assertCanReadDocuments($pid);

        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $globals = OEGlobalsBag::getInstance();
        // The page acts as the session's user: `-patient-` for a portal patient, else the staff id.
        $cuser = $session->get('sessionUser') ?? $session->get('authUserID');
        $isModule = $query->get('is_module', 0);
        $isPortal = $caller->kind === PrincipalKind::Staff ? 0 : 1;
        $catid = $query->get('catid', '');
        $siteId = $session->get('site_id');
        $siteId = is_string($siteId) ? $siteId : '';

        return new Response($this->pages->render('OnsiteDocumentListView', [
            'language_defs' => TranslationService::getLanguageDefinitionsForSession(),
            'doc_edit' => $query->get('edit', 0),
            'recid' => $query->get('recid', 0),
            'help_id' => $help_id,
            'cpid' => $pid,
            'cuser' => $cuser,
            'pt_name' => $session->get('ptName', $pid),
            'encounter' => $query->get('enc', 0),
            'docid' => $query->get('docid', ''),
            'is_module' => $isModule,
            'is_portal' => $isPortal,
            'save_catid' => $catid,
            'category_name' => self::categoryName($catid),
            'new_filename' => $query->get('new', ''),
            'auto_render' => $query->get('auto_render_id', 0),
            'audit_render' => $query->get('audit_render_id', 0),
            'referer_flag' => $query->get('referer_flag', 0),
            'auto_render_name' => $query->get('auto_render_name', 0),
            'allow_portal_uploads' => $globals->getBoolean('allow_portal_uploads'),
            // The encounter forms the page may load, by directory: a whitelist, for security.
            'form_names_whitelist' => CoreFormToPortalUtility::getListPortalCompliantEncounterForms(),
            'template_menu' => PortalValues::isFilled($isModule) || $isPortal === 1
                ? (new DocumentTemplateService())->renderPortalTemplateMenu($pid, $cuser, true)
                : '',
            'datetimepicker_js' => PortalValues::scriptOutput($globals->getString('srcdir') . '/js/xl/jquery-datetimepicker-2-5-4-alternate.js.php'),
            'portal_home_url' => '../home.php?site=' . urlencode($siteId === '' ? 'default' : $siteId),
            // The patient's Exit link when the page opened a document for them has always dropped
            // the 'default' fallback.
            'exit_url' => '../home.php?site=' . urlencode($siteId),
        ]));
    }

    /**
     * The name of the documents category the page charts to, if it was given one that has a name.
     */
    private static function categoryName(mixed $catid): ?string
    {
        if (!PortalValues::isFilled($catid)) {
            return null;
        }
        $name = QueryUtils::fetchSingleValue('SELECT `name` FROM `categories` WHERE `id` = ?', 'name', [$catid]);

        return is_string($name) && PortalValues::isFilled($name) ? $name : null;
    }

    /**
     * One patient's documents: GET api/onsitedocuments.
     */
    public function Query(Request $request, RouteMatch $route, PortalPrincipal $caller): Response
    {
        try {
            $query = $request->query;
            // A portal patient lists only their own documents.
            $pid = $caller->patientPid() ?? filter_var($query->getString('patientId'), FILTER_VALIDATE_INT);
            if (!is_int($pid) || $pid <= 0) {
                throw new \InvalidArgumentException('A patient is required');
            }
            PortalPatientAccessGuard::assertCanReadDocuments($pid);

            $recid = filter_var($query->getString('recid'), FILTER_VALIDATE_INT);
            $page = $query->getString('page');
            $page = $page === '' || $page === '0' ? null : filter_var($page, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($page === false) {
                throw new \InvalidArgumentException('The page must be a positive number');
            }
            $orderDesc = $query->getString('orderDesc') !== '';

            $result = $this->documents->list(new OnsiteDocumentListQuery(
                pid: $pid,
                recordId: is_int($recid) && $recid > 0 ? $recid : null,
                includeLocked: $query->getString('showActive') === 'true',
                filter: $query->getString('filter'),
                orderBy: OnsiteDocumentSort::fromRequest($query->getString('orderBy')),
                orderDesc: $orderDesc,
                page: $page,
                pageSize: OnsiteDocumentListQuery::DEFAULT_PAGE_SIZE,
            ));

            return PortalJson::ok([
                // The list is always sorted by review date last, and has always reported that.
                'orderBy' => 'ReviewDate',
                'orderDesc' => $orderDesc,
                'rows' => $result->rows,
                'totalResults' => $result->totalResults,
                'totalPages' => $result->totalPages,
                'pageSize' => $result->pageSize,
                'currentPage' => $result->currentPage,
            ]);
        } catch (\RuntimeException | \LogicException $ex) {
            return PortalJson::exception($ex);
        }
    }

    /**
     * One document, filled with its saved answers: GET api/onsitedocument/{id}.
     */
    public function Read(Request $request, RouteMatch $route, PortalPrincipal $caller): Response
    {
        try {
            $document = $this->documents->find(self::documentId($route->param('id')));
            if ($document === null) {
                throw new PortalDocumentNotFoundException();
            }
            PortalPatientAccessGuard::assertCanReadDocuments($document['pid']);

            $fullDocument = $document['fullDocument'];
            $isLegacy = !is_string($fullDocument) || stripos($fullDocument, 'portal_version') === false;
            $templateData = $document['templateData'];
            if (PortalValues::isFilled($templateData) && !$isLegacy) {
                // Fill the template saved at create time with the saved answers, so a document
                // keeps the template version it was started on.
                $templateRender = new DocumentTemplateRender($document['pid'], $document['provider'], $document['encounter']);
                $document['fullDocument'] = $templateRender->doRender(null, $document['fullDocument'], $document['templateData']);
            }

            return PortalJson::ok($document);
        } catch (\RuntimeException | \LogicException $ex) {
            return PortalJson::exception($ex);
        }
    }

    /**
     * Start a document from a template: POST api/onsitedocument.
     */
    public function Create(Request $request, RouteMatch $route, PortalPrincipal $caller): Response
    {
        try {
            $json = PortalJson::body($request->getContent());

            // A portal patient adds documents only for themselves.
            $document = [
                'pid' => $caller->patientPid() ?? PortalJson::field($json, 'pid'),
            ];
            PortalPatientAccessGuard::assertCanAddDocument($document['pid']);

            $document += [
                'facility' => PortalJson::field($json, 'facility'),
                'provider' => PortalJson::field($json, 'provider'),
                'encounter' => PortalJson::field($json, 'encounter'),
                'createDate' => self::dateTime(PortalJson::field($json, 'createDate')),
                'docType' => PortalJson::field($json, 'docType'),
                'patientSignedStatus' => PortalJson::field($json, 'patientSignedStatus'),
                'patientSignedTime' => self::dateTime(PortalJson::field($json, 'patientSignedTime')),
                'authorizeSignedTime' => self::dateTime(PortalJson::field($json, 'authorizeSignedTime')),
                'acceptSignedStatus' => PortalJson::field($json, 'acceptSignedStatus'),
                'authorizingSignator' => PortalJson::field($json, 'authorizingSignator'),
                'reviewDate' => self::dateTime(PortalJson::field($json, 'reviewDate')),
                'denialReason' => PortalJson::field($json, 'denialReason'),
                'authorizedSignature' => PortalJson::field($json, 'authorizedSignature'),
                'patientSignature' => PortalJson::field($json, 'patientSignature'),
                'fullDocument' => null, // The template comes from the server, never from the request.
                'fileName' => PortalJson::field($json, 'fileName', ''),
                'filePath' => PortalJson::field($json, 'filePath', ''),
                'templateData' => PortalJson::field($json, 'templateData', null),
            ];

            $errors = OnsiteDocumentRepository::validate($document);
            if ($errors !== []) {
                return PortalJson::error('Please check the form for errors', $errors);
            }

            // Save the template as it is now, so the document keeps this version through its edits.
            $templateRender = new DocumentTemplateRender($document['pid'], $document['provider'], $document['encounter']);
            $template = $templateRender->fetchTemplateDocument($document['filePath'])['template_content'] ?? '';
            if (!is_string($template)) {
                throw new \RuntimeException('The document template could not be read');
            }
            if (PortalJson::field($json, 'version') == 'New') {
                $template .= "<input id='portal_version' name='portal_version' type='hidden' value='New' />";
            }
            $document['fullDocument'] = $template;

            $id = $this->documents->insert($document);

            return PortalJson::ok(self::inColumnOrder(['id' => $id] + $document));
        } catch (\RuntimeException | \LogicException $ex) {
            return PortalJson::exception($ex);
        }
    }

    /**
     * Save a document's answers, or its flattened copy: PUT api/onsitedocument/{id}.
     */
    public function Update(Request $request, RouteMatch $route, PortalPrincipal $caller): Response
    {
        try {
            $json = PortalJson::body($request->getContent());
            $globalsBag = OEGlobalsBag::getInstance();

            $pk = self::documentId($route->param('id'));
            $document = $this->documents->find($pk) ?? throw new PortalDocumentNotFoundException();
            $owner = $document['pid'];
            PortalPatientAccessGuard::assertCanWriteDocuments($owner);

            $existing_template = $document['fullDocument'];

            $hasVersion = is_string($existing_template) && stripos($existing_template, 'portal_version') !== false;
            if (PortalJson::field($json, 'type') == 'flattened') {
                $existing = PortalJson::field($json, 'fullDocument');
                if (is_string($existing) && PortalValues::isFilled($existing)) {
                    $config = \HTMLPurifier_Config::createDefault();
                    $config->set('Core.Encoding', 'UTF-8');
                    // purifier will only allow base64 data urls in img tag.
                    // all other element will be removed. Flatten document have already been sanitized
                    // by replacing all inputs, checks and radios tags to their answers.
                    // Thus Enter Comment: <input name="element" value="This is my comment I don't like purifier" />
                    // renders to Enter Comment: 'This is my comment I don't like purifier in document.'
                    $config->set('URI.AllowedSchemes', ['data' => true]);
                    $purifyTempDir = $globalsBag->getString('temporary_files_dir') . DIRECTORY_SEPARATOR . 'htmlpurifier';
                    if (!is_dir($purifyTempDir) && !mkdir($purifyTempDir, 0700, true)) {
                        ServiceContainer::getLogger()->error("Could not create directory ", [$purifyTempDir]);
                    }
                    $config->set('Cache.SerializerPath', $purifyTempDir);
                    $purify = new \HTMLPurifier($config);
                    $existing_template = $purify->purify($existing);
                    // since this is a flatten document won't need to track legacy or not.
                    if (!$hasVersion) {
                        $existing_template .= "<input id='portal_version' name='portal_version' type='hidden' value='New' />";
                    }
                }
            } elseif (PortalValues::isFilled(PortalJson::field($json, 'fullDocument'))) { // test if an unexpected document is sent.
                // the only time a document is allow from interface is fo flattened documents
                // which should be flagged and even still if flagged HTMLPurifier will blow it up.
                ServiceContainer::getLogger()->warning('Invalid portal document save attempt: an unflattened document body was sent');
                throw new \RuntimeException(xlt("Invalid save attempt"));
            }

            // A portal patient's document stays theirs. Staff may move it only to a patient
            // whose documents they may also change.
            $document['pid'] = $caller->patientPid() ?? PortalJson::field($json, 'pid', $owner);
            PortalPatientAccessGuard::assertCanWriteDocuments($document['pid']);
            // Set values from API interface.
            foreach (['facility', 'provider', 'encounter', 'docType', 'patientSignedStatus', 'acceptSignedStatus', 'authorizingSignator', 'denialReason', 'authorizedSignature', 'patientSignature', 'fileName', 'filePath', 'templateData'] as $field) {
                $document[$field] = PortalJson::field($json, $field, $document[$field]);
            }
            foreach (['createDate', 'patientSignedTime', 'authorizeSignedTime', 'reviewDate'] as $field) {
                $document[$field] = self::dateTime(PortalJson::field($json, $field, $document[$field]));
            }
            $document['fullDocument'] = $existing_template; // retain original template that was populated during create save.

            $errors = OnsiteDocumentRepository::validate($document);
            if ($errors !== []) {
                return PortalJson::error('Please check the form for errors', $errors);
            }
            $this->documents->update($pk, $document);

            return PortalJson::ok($document);
        } catch (\RuntimeException | \LogicException $ex) {
            return PortalJson::exception($ex);
        }
    }

    /**
     * Delete a document: DELETE api/onsitedocument/{id}.
     */
    public function Delete(Request $request, RouteMatch $route, PortalPrincipal $caller): Response
    {
        try {
            $pk = self::documentId($route->param('id'));
            $document = $this->documents->find($pk);
            if ($document === null) {
                throw new PortalDocumentNotFoundException();
            }
            PortalPatientAccessGuard::assertCanWriteDocuments($document['pid']);

            $this->documents->delete($pk);

            return PortalJson::ok(new \stdClass());
        } catch (\RuntimeException | \LogicException $ex) {
            return PortalJson::exception($ex);
        }
    }

    /**
     * The document id from the route, as a positive int; a document that cannot exist is not found.
     */
    private static function documentId(?string $id): int
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!is_int($id) || $id <= 0) {
            throw new PortalDocumentNotFoundException();
        }

        return $id;
    }

    /**
     * A date as the old controller saved it: Y-m-d H:i:s, with anything unparsable as the epoch.
     */
    private static function dateTime(mixed $value): string
    {
        $time = is_string($value) ? strtotime($value) : false;

        return date('Y-m-d H:i:s', $time === false ? 0 : $time);
    }

    /**
     * @param array<string, mixed> $document
     * @return array<string, mixed>
     */
    private static function inColumnOrder(array $document): array
    {
        return array_merge(array_intersect_key(OnsiteDocumentRepository::COLUMNS, $document), $document);
    }
}
