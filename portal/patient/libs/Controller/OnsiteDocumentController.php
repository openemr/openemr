<?php

/**
 * OnsiteDocumentController.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2023 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Session\PortalPatientAccessGuard;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\DocumentTemplates\DocumentTemplateRender;
use OpenEMR\Services\PortalDocuments\OnsiteDocumentListQuery;
use OpenEMR\Services\PortalDocuments\OnsiteDocumentRepository;
use OpenEMR\Services\PortalDocuments\OnsiteDocumentSort;
use OpenEMR\Services\PortalDocuments\PortalDocumentNotFoundException;
use OpenEMR\Services\Utils\TranslationService;

/**
 * OnsiteDocumentController is the controller class for the OnsiteDocument object.  The
 * controller is responsible for processing input from the user, reading/updating
 * the model as necessary and displaying the appropriate view.
 *
 * @package Patient Portal::Controller
 * @author  ClassBuilder
 * @version 1.0
 */
class OnsiteDocumentController extends AppBasePortalController
{
    /**
     * Override here for any controller-specific functionality
     *
     * @inheritdocs
     */
    protected function Init()
    {
        parent::Init();
    }

    /**
     * Displays a list view of OnsiteDocument objects
     */
    public function ListView()
    {
        $recid = $pid = $user = $encounter = 0;
        $is_module = $catid = 0;
        $is_portal = GlobalConfig::$PORTAL;
        $docid = $new_filename = "";
        // get latest help template id
        $help_id = sqlQuery('SELECT * FROM `document_templates` WHERE `template_name` = ? Order By modified_date DESC Limit 1', ['Help'])['id'] ?? 0;

        if (isset($_GET['pid'])) {
            $pid = (int)$_GET['pid'];
        }
        // only allow patient to see themselves

        $bootstrapPid = OEGlobalsBag::getInstance()->get('bootstrap_pid');
            // only allow patient to delete themselves
        if (!empty($bootstrapPid)) {
            $pid = (int)$bootstrapPid;
        }
        PortalPatientAccessGuard::assertCanReadDocuments($pid);

        $user = $_GET['user'] ?? 0;
        $docid = $_GET['docid'] ?? '';
        $encounter = $_GET['enc'] ?? 0;
        $recid = $_GET['recid'] ?? 0;
        $is_module = $_GET['is_module'] ?? 0;
        $catid = $_GET['catid'] ?? '';
        $new_filename = $_GET['new'] ?? '';
        $doc_edit = $_GET['edit'] ?? 0;
        $referer_flag = $_GET['referer_flag'] ?? 0; // return to New Documents or Documents

        $auto_render = $_GET['auto_render_id'] ?? 0;
        $auto_render_name = $_GET['auto_render_name'] ?? 0;
        $audit_render = $_GET['audit_render_id'] ?? 0;

        unset($_GET['auto_render_id']);
        unset($_GET['auto_render_name']);
        unset($_GET['audit_render_id']);

        $language_defs = TranslationService::getLanguageDefinitionsForSession();

        $this->Assign("language_defs", $language_defs);
        $this->Assign('doc_edit', $doc_edit);
        $this->Assign('recid', $recid);
        $this->Assign('help_id', $help_id);
        $this->Assign('cpid', $pid);
        $this->Assign('cuser', $user);
        $this->Assign('encounter', $encounter);
        $this->Assign('docid', $docid);
        $this->Assign('is_module', $is_module);
        $this->Assign('is_portal', $is_portal);
        $this->Assign('save_catid', $catid);
        $this->Assign('new_filename', $new_filename);
        $this->Assign('auto_render', $auto_render);
        $this->Assign('audit_render', $audit_render);
        $this->Assign('referer_flag', $referer_flag);
        $this->Assign('auto_render_name', $auto_render_name);
        $this->Render();
    }

    /**
     * API Method lists one patient's portal documents and renders them as JSON.
     */
    public function Query()
    {
        try {
            $pid = RequestUtil::Get('patientId');
            // A portal patient lists only their own documents.
            $bootstrapPid = OEGlobalsBag::getInstance()->get('bootstrap_pid');
            if (!empty($bootstrapPid)) {
                $pid = $bootstrapPid;
            }
            $pid = filter_var($pid, FILTER_VALIDATE_INT);
            if (!is_int($pid) || $pid <= 0) {
                throw new InvalidArgumentException('A patient is required');
            }
            PortalPatientAccessGuard::assertCanReadDocuments($pid);

            $recid = filter_var(RequestUtil::Get('recid'), FILTER_VALIDATE_INT);
            $page = RequestUtil::Get('page');
            $page = empty($page) ? null : filter_var($page, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($page === false) {
                throw new InvalidArgumentException('The page must be a positive number');
            }
            $orderBy = RequestUtil::Get('orderBy');
            $filter = RequestUtil::Get('filter');

            $result = self::documents()->list(new OnsiteDocumentListQuery(
                pid: $pid,
                recordId: is_int($recid) && $recid > 0 ? $recid : null,
                includeLocked: RequestUtil::Get('showActive') === 'true',
                filter: is_string($filter) ? $filter : '',
                orderBy: OnsiteDocumentSort::fromRequest(is_string($orderBy) ? $orderBy : ''),
                orderDesc: RequestUtil::Get('orderDesc') != '',
                page: $page,
                pageSize: OnsiteDocumentListQuery::DEFAULT_PAGE_SIZE,
            ));

            $this->RenderJSON([
                // The list is always sorted by review date last, and has always reported that.
                'orderBy' => 'ReviewDate',
                'orderDesc' => RequestUtil::Get('orderDesc') != '',
                'rows' => $result->rows,
                'totalResults' => $result->totalResults,
                'totalPages' => $result->totalPages,
                'pageSize' => $result->pageSize,
                'currentPage' => $result->currentPage,
            ], $this->JSONPCallback());
        } catch (\Throwable $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }

    /**
     * API Method retrieves a single OnsiteDocument record and render as JSON
     */
    public function Read()
    {
        try {
            $document = self::documents()->find(self::documentId($this->GetRouter()->GetUrlParam('id')));
            if ($document === null) {
                throw new PortalDocumentNotFoundException();
            }
            PortalPatientAccessGuard::assertCanReadDocuments($document['pid']);

            $fullDocument = $document['fullDocument'];
            $isLegacy = !is_string($fullDocument) || stripos($fullDocument, 'portal_version') === false;
            if (!empty($document['templateData']) && !$isLegacy) {
                // Fill the template saved at create time with the saved answers, so a document
                // keeps the template version it was started on.
                $templateRender = new DocumentTemplateRender($document['pid'], $document['provider'], $document['encounter']);
                $document['fullDocument'] = $templateRender->doRender(null, $document['fullDocument'], $document['templateData']);
            }
            $this->RenderJSON($document, $this->JSONPCallback());
        } catch (\Throwable $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }

    /**
     * API Method inserts a new OnsiteDocument record and render response as JSON
     */
    public function Create()
    {
        try {
            $json = json_decode(RequestUtil::GetBody());

            if (!$json) {
                throw new Exception('The request body does not contain valid JSON');
            }

            // A portal patient adds documents only for themselves.
            $bootstrapPid = OEGlobalsBag::getInstance()->get('bootstrap_pid');
            $document = [
                'pid' => !empty($bootstrapPid) ? $bootstrapPid : $this->SafeGetVal($json, 'pid'),
            ];
            PortalPatientAccessGuard::assertCanAddDocument($document['pid']);

            $document += [
                'facility' => $this->SafeGetVal($json, 'facility'),
                'provider' => $this->SafeGetVal($json, 'provider'),
                'encounter' => $this->SafeGetVal($json, 'encounter'),
                'createDate' => date('Y-m-d H:i:s', strtotime((string) $this->SafeGetVal($json, 'createDate'))),
                'docType' => $this->SafeGetVal($json, 'docType'),
                'patientSignedStatus' => $this->SafeGetVal($json, 'patientSignedStatus'),
                'patientSignedTime' => date('Y-m-d H:i:s', strtotime((string) $this->SafeGetVal($json, 'patientSignedTime'))),
                'authorizeSignedTime' => date('Y-m-d H:i:s', strtotime((string) $this->SafeGetVal($json, 'authorizeSignedTime'))),
                'acceptSignedStatus' => $this->SafeGetVal($json, 'acceptSignedStatus'),
                'authorizingSignator' => $this->SafeGetVal($json, 'authorizingSignator'),
                'reviewDate' => date('Y-m-d H:i:s', strtotime((string) $this->SafeGetVal($json, 'reviewDate'))),
                'denialReason' => $this->SafeGetVal($json, 'denialReason'),
                'authorizedSignature' => $this->SafeGetVal($json, 'authorizedSignature'),
                'patientSignature' => $this->SafeGetVal($json, 'patientSignature'),
                'fullDocument' => null, // The template comes from the server, never from the request.
                'fileName' => $this->SafeGetVal($json, 'fileName', ''),
                'filePath' => $this->SafeGetVal($json, 'filePath', ''),
                'templateData' => $this->SafeGetVal($json, 'templateData', null),
            ];
            $version = $this->SafeGetVal($json, 'version');

            $errors = OnsiteDocumentRepository::validate($document);
            if ($errors !== []) {
                $this->RenderErrorJSON('Please check the form for errors', $errors);
                return;
            }

            // Save the template as it is now, so the document keeps this version through its edits.
            $templateRender = new DocumentTemplateRender($document['pid'], $document['provider'], $document['encounter']);
            $template = $templateRender->fetchTemplateDocument($document['filePath'])['template_content'];
            if ($version == 'New') {
                $template .= "<input id='portal_version' name='portal_version' type='hidden' value='New' />";
            }
            $document['fullDocument'] = $template;

            $id = self::documents()->insert($document);
            $this->RenderJSON(self::inColumnOrder(['id' => $id] + $document), $this->JSONPCallback());
        } catch (\Throwable $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }

    /**
     * API Method updates an existing OnsiteDocument record and render response as JSON
     */
    public function Update()
    {
        try {
            $json = json_decode(RequestUtil::GetBody());

            $globalsBag = OEGlobalsBag::getInstance();

            if (!$json) {
                throw new Exception('The request body does not contain valid JSON');
            }
            $pk = self::documentId($this->GetRouter()->GetUrlParam('id'));
            PortalPatientAccessGuard::assertCanWriteDocuments(self::documentOwner($pk));
            $document = self::documents()->find($pk) ?? throw new PortalDocumentNotFoundException();
            // Check the loaded document too, in case it moved to another patient after the lookup.
            $owner = $document['pid'];
            PortalPatientAccessGuard::assertCanWriteDocuments($owner);

            $existing_template = $document['fullDocument'];

            $hasVersion = stripos((string) $existing_template, 'portal_version') !== false;
            if ($this->SafeGetVal($json, 'type') == 'flattened') {
                $existing = $this->SafeGetVal($json, 'fullDocument');
                if (!empty($existing)) {
                    $config = HTMLPurifier_Config::createDefault();
                    $config->set('Core.Encoding', 'UTF-8');
                    // purifier will only allow base64 data urls in img tag.
                    // all other element will be removed. Flatten document have already been sanitized
                    // by replacing all inputs, checks and radios tags to their answers.
                    // Thus Enter Comment: <input name="element" value="This is my comment I don't like purifier" />
                    // renders to Enter Comment: 'This is my comment I don't like purifier in document.'
                    $config->set('URI.AllowedSchemes', ['data' => true]);
                    $purifyTempDir = $globalsBag->getString('temporary_files_dir') . DIRECTORY_SEPARATOR . 'htmlpurifier';
                    if (
                        !is_dir($purifyTempDir)
                    ) {
                        if (!mkdir($purifyTempDir, 0700, true)) {
                            ServiceContainer::getLogger()->error("Could not create directory ", [$purifyTempDir]);
                        }
                    }
                    $config->set('Cache.SerializerPath', $purifyTempDir);
                    $purify = new HTMLPurifier($config);
                    $existing_template = $purify->purify($existing);
                    // since this is a flatten document won't need to track legacy or not.
                    if (!$hasVersion) {
                        $existing_template .= "<input id='portal_version' name='portal_version' type='hidden' value='New' />";
                    }
                }
            } elseif (!empty($this->SafeGetVal($json, 'fullDocument'))) { // test if an unexpected document is sent.
                // the only time a document is allow from interface is fo flattened documents
                // which should be flagged and even still if flagged HTMLPurifier will blow it up.
                error_log(xlt("Invalid save attempt. Suspected portal document attack!"));
                throw new Exception(xlt("Invalid save attempt"));
            }

            // A portal patient's document stays theirs. Staff may move it only to a patient
            // whose documents they may also change.
            $bootstrapPid = $globalsBag->get('bootstrap_pid');
            $document['pid'] = !empty($bootstrapPid) ? $bootstrapPid : $this->SafeGetVal($json, 'pid', $owner);
            PortalPatientAccessGuard::assertCanWriteDocuments($document['pid']);
            // Set values from API interface.
            $document['facility'] = $this->SafeGetVal($json, 'facility', $document['facility']);
            $document['provider'] = $this->SafeGetVal($json, 'provider', $document['provider']);
            $document['encounter'] = $this->SafeGetVal($json, 'encounter', $document['encounter']);
            $document['createDate'] = date('Y-m-d H:i:s', strtotime((string) $this->SafeGetVal($json, 'createDate', $document['createDate'])));
            $document['docType'] = $this->SafeGetVal($json, 'docType', $document['docType']);
            $document['patientSignedStatus'] = $this->SafeGetVal($json, 'patientSignedStatus', $document['patientSignedStatus']);
            $document['patientSignedTime'] = date('Y-m-d H:i:s', strtotime((string) $this->SafeGetVal($json, 'patientSignedTime', $document['patientSignedTime'])));
            $document['authorizeSignedTime'] = date('Y-m-d H:i:s', strtotime((string) $this->SafeGetVal($json, 'authorizeSignedTime', $document['authorizeSignedTime'])));
            $document['acceptSignedStatus'] = $this->SafeGetVal($json, 'acceptSignedStatus', $document['acceptSignedStatus']);
            $document['authorizingSignator'] = $this->SafeGetVal($json, 'authorizingSignator', $document['authorizingSignator']);
            $document['reviewDate'] = date('Y-m-d H:i:s', strtotime((string) $this->SafeGetVal($json, 'reviewDate', $document['reviewDate'])));
            $document['denialReason'] = $this->SafeGetVal($json, 'denialReason', $document['denialReason']);
            $document['authorizedSignature'] = $this->SafeGetVal($json, 'authorizedSignature', $document['authorizedSignature']);
            $document['patientSignature'] = $this->SafeGetVal($json, 'patientSignature', $document['patientSignature']);
            $document['fullDocument'] = $existing_template; // retain original template that was populated during create save.
            $document['fileName'] = $this->SafeGetVal($json, 'fileName', $document['fileName']);
            $document['filePath'] = $this->SafeGetVal($json, 'filePath', $document['filePath']);
            $document['templateData'] = $this->SafeGetVal($json, 'templateData', $document['templateData']);

            $errors = OnsiteDocumentRepository::validate($document);
            if ($errors !== []) {
                $this->RenderErrorJSON('Please check the form for errors', $errors);
                return;
            }
            self::documents()->update($pk, $document);
            $this->RenderJSON($document, $this->JSONPCallback());
        } catch (\Throwable $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }

    /**
     * API Method deletes an existing OnsiteDocument record and render response as JSON
     */
    public function Delete()
    {
        try {
            $pk = self::documentId($this->GetRouter()->GetUrlParam('id'));
            $document = self::documents()->find($pk);
            if ($document === null) {
                throw new PortalDocumentNotFoundException();
            }
            PortalPatientAccessGuard::assertCanWriteDocuments($document['pid']);

            self::documents()->delete($pk);

            $this->RenderJSON(new stdClass(), $this->JSONPCallback());
        } catch (\Throwable $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }

    /**
     * The pid that owns a portal document.
     *
     * @throws PortalDocumentNotFoundException when no document has that id
     */
    private static function documentOwner(int $documentId): int
    {
        return self::documents()->owner($documentId) ?? throw new PortalDocumentNotFoundException();
    }

    private static function documentId(mixed $id): int
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!is_int($id) || $id <= 0) {
            throw new PortalDocumentNotFoundException();
        }

        return $id;
    }

    /**
     * @param array<string, mixed> $document
     * @return array<string, mixed>
     */
    private static function inColumnOrder(array $document): array
    {
        return array_merge(array_intersect_key(OnsiteDocumentRepository::COLUMNS, $document), $document);
    }

    private static function documents(): OnsiteDocumentRepository
    {
        return new OnsiteDocumentRepository();
    }
}
