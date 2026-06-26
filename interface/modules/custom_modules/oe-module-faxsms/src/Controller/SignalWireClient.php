<?php

/**
 * SignalWire Fax Client
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    SignalWire Integration
 * @copyright Copyright (c) 2024
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS\Controller;

use Document;
use Exception;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\FaxSMS\Contracts\FaxChannelInterface;
use OpenEMR\Modules\FaxSMS\RestClient\SignalWire\Rest\Client;
use OpenEMR\Modules\FaxSMS\RestClient\SignalWire\Rest\FaxInstance;
use OpenEMR\Modules\FaxSMS\Service\FaxMailer;
use OpenEMR\Modules\FaxSMS\Service\FaxUploadStaging;

class SignalWireClient extends AppDispatch implements FaxChannelInterface
{
    const debugLogging = false; // Set to true to enable detailed debug logging in error_log
    /** Max faxes to pull from the SignalWire API in a single check. */
    private const FAX_LIST_LIMIT = 100;

    /** Seconds an outbound media handout token (and its staged PHI file) stays valid. */
    private const OUTBOUND_MEDIA_TTL = 900;
    /** Fax statuses that will not change again; used to skip redundant API/media work. */
    private const TERMINAL_FAX_STATUSES = ['delivered', 'received', 'no-answer', 'busy', 'failed', 'canceled'];
    /** Terminal statuses that never carry media, so no fetch/download is ever warranted. */
    private const FAILED_FAX_STATUSES = ['failed', 'no-answer', 'busy', 'canceled'];
    public static $timeZone;
    protected string $baseDir = '';
    protected $uriDir;
    protected $serverUrl;
    protected $credentials;
    public string $portalUrl;
    protected CryptoInterface $crypto;
    private readonly FaxUploadStaging $uploadStaging;
    private ?Client $client = null;
    private $spaceUrl;
    private $projectId;
    private $apiToken;
    private $faxNumber;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        // Initialize properties before calling parent (like other controllers)
        $globals = OEGlobalsBag::getInstance();
        $this->crypto = ServiceContainer::getCrypto();
        $this->uploadStaging = FaxUploadStaging::create();
        $this->baseDir = $globals->getString('temporary_files_dir');
        $this->uriDir = $globals->getString('OE_SITE_WEBROOT');

        try {
            $this->credentials = $this->getCredentials();

            // Initialize SignalWire client only if credentials are complete
            if (!empty($this->credentials['space_url']) &&
                !empty($this->credentials['project_id']) &&
                !empty($this->credentials['api_token'])) {
                $this->client = new Client(
                    $this->credentials['project_id'],
                    $this->credentials['api_token'],
                    ['signalwireSpaceUrl' => $this->credentials['space_url']]
                );
            }

            $this->portalUrl = "https://" . ($this->credentials['space_url'] ?? 'example.signalwire.com');
        } catch (\Throwable $e) {
            error_log('SignalWire initialization error: ' . $e->getMessage());
            // Continue anyway to allow setup
        }

        // Call parent constructor last - it handles routing/dispatch and may exit
        parent::__construct();
    }

    /**
     * Get and decrypt credentials
     *
     * @return mixed
     */
    public function getCredentials(): mixed
    {
        $credentials = AppDispatch::getSetup();

        $this->spaceUrl = $credentials['space_url'] ?? '';
        $this->projectId = $credentials['project_id'] ?? '';
        $this->apiToken = $credentials['api_token'] ?? '';
        // Ensure 'from' fax number is always in E.164 format
        $this->faxNumber = $this->formatPhone($credentials['fax_number'] ?? '');
        $this->serverUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
        $this->uriDir = $this->serverUrl . $this->uriDir;

        //if ($this::debugLogging) error_log("SignalWireClient.getCredentials(): DEBUG - faxNumber after E.164 formatting: " . ($this->faxNumber ?: 'EMPTY'));

        return $credentials;
    }

    /**
     * Authenticate and verify ACL
     *
     * @param array $acl
     * @return int|bool
     */
    public function authenticate($acl = ['patients', 'docs']): int|bool
    {
        if (empty($this->credentials)) {
            $this->credentials = $this->getCredentials();
        }

        if (empty($this->credentials['project_id']) || empty($this->credentials['api_token'])) {
            return 0;
        }

        return $this->verifyAcl($acl[0], $acl[1]);
    }

    /**
     * Send a fax via SignalWire
     *
     * @return string
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function sendFax(): string
    {
        if (!$this->authenticate()) {
            return $this->authErrorDefault;
        }

        if ($this->client === null) {
            return json_encode([
                'success' => false,
                'error' => xlt('SignalWire client not initialized. Please configure credentials.')
            ]);
        }

        // Get request parameters - initialize all to avoid PHPStan warnings
        $isContent = $this->getRequest('isContent');
        $fileParam = $this->getRequest('file');
        $file = !empty($fileParam) ? $fileParam : '';
        $docId = $this->getRequest('docid');
        $phoneParam = $this->getRequest('phone');
        $phone = !empty($phoneParam) ? $this->formatPhone($phoneParam) : '';
        $isDocumentsParam = $this->getRequest('isDocuments');
        $isDocuments = !empty($isDocumentsParam) ? (int)$isDocumentsParam : 0;
        $email = $this->getRequest('email');
        $hasEmail = $this->validEmail($email);
        $globals = OEGlobalsBag::getInstance();
        $smtpEnabled = $globals->getString('SMTP_HOST') !== '';
        $user = $this::getLoggedInUser();

        // DEBUG: Log parameters received in sendFax
        if ($this::debugLogging) {
            error_log("SignalWireClient.sendFax(): DEBUG - Received file path: " . $file);
            error_log("SignalWireClient.sendFax(): DEBUG - isContent: " . ($isContent ?? 'EMPTY'));
            error_log("SignalWireClient.sendFax(): DEBUG - isDocuments: " . $isDocuments);
            error_log("SignalWireClient.sendFax(): DEBUG - Phone: " . $phone);
            error_log("SignalWireClient.sendFax(): DEBUG - File exists: " . (!empty($file) && file_exists($file) ? 'YES' : 'NO'));
        }
        if (!empty($file) && file_exists($file)) {
            if ($this::debugLogging) error_log("SignalWireClient.sendFax(): DEBUG - File size: " . filesize($file) . " bytes");
        }

        // Handle file path
        if (empty($isContent) && !empty($file)) {
            if (str_starts_with((string)$file, 'file://')) {
                $file = substr((string)$file, 7);
            }
            $realPath = realpath($file);
            if ($realPath !== false) {
                $file = str_replace("\\", "/", $realPath);
            } else {
                return xlt('Error: No content SignalWireClient');
            }
        }

        // Outbound file-mode sends must reference an upload this controller
        // staged (see FaxUploadStaging::isStagedUploadPath); reject any other
        // resolved server path so an authenticated caller cannot fax out
        // arbitrary local files. Document sends ($isDocuments) and inline
        // content ($isContent) take their own paths below and are exempt.
        if (
            empty($isContent)
            && !$isDocuments
            && !empty($file)
            && !$this->uploadStaging->isStagedUploadPath((string)$file)
        ) {
            error_log('SignalWireClient.sendFax(): rejected non-staged file path');
            return xlt('Error: Invalid file location');
        }

        // Decrypt the staged upload to a per-request plaintext tempnam
        // and continue with that as $file. Pattern guard scopes the
        // cleanup we'll do below to files this controller staged via
        // FaxUploadStaging, leaving caller-managed temp files alone.
        $stagedPath = null;
        $plainStagePath = null;
        if (
            empty($isContent)
            && !$isDocuments
            && is_string($file)
            && is_file($file)
            && $this->uploadStaging->isStagedUploadPath($file)
        ) {
            $plainStagePath = $this->uploadStaging->decryptStagedToTemp($file);
            if ($plainStagePath === null) {
                return xlt('Error: Failed to read fax content');
            }
            $stagedPath = $file;
            $file = $plainStagePath;
        }

        // Handle document retrieval
        if ($isDocuments) {
            $file = (new Document($docId))->get_data();
        }

        // Send email if requested. $file is raw bytes when either the
        // patient-document branch above set it from Document::get_data
        // ($isDocuments) or the caller indicated the payload is already
        // content ($isContent). The staged-upload branch left $file
        // pointing at a plaintext path, so the else branch in
        // mailUploadedDocument sends it directly.
        $emailPath = null;
        if ($hasEmail && $smtpEnabled) {
            $payloadIsContent = (bool)$isDocuments || !empty($isContent);
            $emailPath = FaxMailer::mailUploadedDocument(
                $email,
                '',
                $file,
                $user,
                $payloadIsContent,
            );
        }

        // Validate phone number
        if (empty($phone)) {
            return xlt('Error: Invalid phone number');
        }

        // Upload file to accessible URL
        $mediaUrl = $this->uploadFileForFax($file, $isDocuments);
        if (empty($mediaUrl)) {
            return xlt('Error: Could not prepare the document for faxing - SignalWire requires a valid PDF.');
        }

        try {
            // Send fax via SignalWire REST API
            // The SDK expects: create($options) where options is an array with 'to', 'from', 'mediaUrl'
            if ($this::debugLogging) {
                error_log("SignalWireClient.sendFax(): DEBUG - About to call SignalWire fax create");
                error_log("SignalWireClient.sendFax(): DEBUG - to={$phone}, from={$this->faxNumber}");
                error_log("SignalWireClient.sendFax(): DEBUG - mediaUrl={$mediaUrl}");
            }

            $fax = $this->client->fax->v1->faxes->create([
                'to' => $phone,
                'from' => $this->faxNumber,
                'mediaUrl' => $mediaUrl
            ]);

            // Stateless: SignalWire is the system of record. The sent fax is NOT
            // persisted to oe_faxsms_queue (only etherFAX uses that table); it
            // shows in the live outbound list on the next poll.
            if ($this::debugLogging) {
                error_log("SignalWireClient.sendFax(): DEBUG - SignalWire accepted fax sid=" . ($fax->sid ?? '') . " status=" . ($fax->status ?? ''));
            }

            return json_encode([
                'success' => true,
                'message' => xlt('Fax queued successfully'),
                'fax_sid' => $fax->sid,
                'status' => $fax->status
            ]);
        } catch (\Throwable $e) {
            error_log('SignalWire Fax Error: ' . $e->getMessage());
            return json_encode([
                'success' => false,
                'error' => xlt('Error sending fax')
            ]);
        } finally {
            $this->uploadStaging->removeStagedArtifacts(
                $stagedPath,
                $plainStagePath,
                $emailPath
            );
        }
    }

    /**
     * Stage outbound fax media for SignalWire to fetch.
     *
     * SignalWire's fax-send API pulls the document from a URL we hand it. That
     * document is PHI, so rather than drop a world-readable file in the web root
     * we stage it OUTSIDE the web root and return a short-lived, encrypted,
     * tamper-proof token URL served by faxMedia.php. The token carries the file
     * name, site, and an expiry; faxMedia.php validates it, streams the PDF, and
     * deletes it. Abandoned stagings are swept here on each send.
     *
     * @param string $file Plaintext file path, or raw content when $isDocuments
     * @param bool   $isDocuments
     * @return string|null Public token URL, or null on failure
     */
    private function uploadFileForFax(string $file, bool $isDocuments = false): ?string
    {
        try {
            $globals = OEGlobalsBag::getInstance();
            $session = SessionWrapperFactory::getInstance()->getActiveSession();
            $siteId = $session->get('site_id') ?? $globals->get('OE_SITE_NAME') ?? 'default';
            $siteDir = $globals->get('OE_SITE_DIR') ?? (dirname(__DIR__, 5) . '/sites/' . $siteId);

            // Non-public staging area (under documents/, not the web root).
            $stageDir = $siteDir . '/documents/logs_and_misc/fax_outbound';
            if (!is_dir($stageDir) && !mkdir($stageDir, 0700, true) && !is_dir($stageDir)) {
                error_log("SignalWireClient.uploadFileForFax(): ERROR - Could not create stage dir: {$stageDir}");
                return null;
            }

            // Opportunistic sweep of anything past its TTL (un-fetched/abandoned).
            $ttl = self::OUTBOUND_MEDIA_TTL;
            foreach ((glob($stageDir . '/fax_out_*.pdf') ?: []) as $old) {
                if (is_file($old) && (time() - (int)filemtime($old)) > $ttl) {
                    @unlink($old);
                }
            }

            // Write the outgoing bytes to a random, non-guessable file name.
            $name = 'fax_out_' . bin2hex(random_bytes(16)) . '.pdf';
            $path = $stageDir . '/' . $name;
            if ($isDocuments) {
                if (file_put_contents($path, $file) === false) {
                    error_log("SignalWireClient.uploadFileForFax(): ERROR - Failed to write document content");
                    return null;
                }
            } else {
                if (!is_file($file) || !copy($file, $path)) {
                    error_log("SignalWireClient.uploadFileForFax(): ERROR - Source not available: {$file}");
                    return null;
                }
            }
            @chmod($path, 0600);

            // SignalWire only sends PDF media. Verify before we hand it a URL so a
            // non-PDF fails clearly on our side instead of as a cryptic "not a PDF
            // file" from the provider after it fetches the media.
            $head = (string)file_get_contents($path, false, null, 0, 5);
            if (!str_starts_with($head, '%PDF-')) {
                @unlink($path);
                error_log("SignalWireClient.uploadFileForFax(): ERROR - Outbound media is not a PDF (first bytes: " . bin2hex($head) . ")");
                return null;
            }

            // Encrypted, expiring token. Authenticated encryption (CryptoGen)
            // gives both confidentiality and tamper-detection, so faxMedia.php
            // can trust it without a session.
            $payload = json_encode(['f' => $name, 'site' => $siteId, 'exp' => time() + $ttl]);
            $token = $this->crypto->encryptStandard((string)$payload);
            if (!is_string($token) || $token === '') {
                @unlink($path);
                error_log("SignalWireClient.uploadFileForFax(): ERROR - Token encryption failed");
                return null;
            }

            $base = rtrim((string)$this->serverUrl, '/');
            $mediaUrl = $base . '/interface/modules/custom_modules/oe-module-faxsms/library/faxMedia.php'
                . '?site=' . urlencode((string)$siteId)
                . '&t=' . urlencode($token);

            if ($this::debugLogging) {
                error_log("SignalWireClient.uploadFileForFax(): DEBUG - staged {$path}");
                error_log("SignalWireClient.uploadFileForFax(): DEBUG - mediaUrl {$mediaUrl}");
            }
            return $mediaUrl;
        } catch (\Throwable $e) {
            error_log('SignalWireClient.uploadFileForFax(): ERROR - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Live count of inbound faxes awaiting handling, read straight from
     * SignalWire (mirrors the RingCentral approach - no queue table). Handled
     * faxes are deleted upstream, so the live count is the unhandled count.
     *
     * @return string
     */
    public function fetchReminderCount(): string
    {
        if ($this->client === null) {
            return json_encode(['count' => 0]);
        }
        try {
            $since = gmdate('Y-m-d\TH:i:s\Z', (int)(strtotime('-30 days') ?: time()));
            $faxes = $this->client->fax->v1->faxes->read(['dateCreatedAfter' => $since], self::FAX_LIST_LIMIT);
            $count = 0;
            foreach ($faxes as $fax) {
                if ((($fax->direction ?? '') === 'inbound')
                    && in_array($fax->status ?? '', self::TERMINAL_FAX_STATUSES, true)) {
                    $count++;
                }
            }
            return json_encode(['count' => $count]);
        } catch (\Throwable $e) {
            error_log('SignalWireClient.fetchReminderCount(): ERROR - ' . $e->getMessage());
            return json_encode(['count' => 0]);
        }
    }

    /**
     * Validate phone number format
     *
     * @param string $n
     * @return bool
     */
    public function validatePhone(string $n): bool
    {
        return preg_match("/^\+[1-9]\d{10,14}$/", $n);
    }

    /**
     * Document action endpoint used by the shared getDocument() UI handler -
     * the same contract every other vendor implements, so SignalWire needs no
     * bespoke per-action method.
     *
     * Request: docid (SID), download ('true'|...), delete ('true'|...).
     *   delete   -> remove the fax from SignalWire; returns 'success'.
     *   download -> stage an encrypted temp copy for disposeDocument to stream,
     *               free the upstream copy ("downloaded -> no longer available"),
     *               return {base64, mime, filename, path}.
     *   view     -> return {base64, mime, filename} for the in-modal viewer.
     *
     * @return string JSON
     */
    public function viewFax(): string
    {
        if (!$this->authenticate()) {
            return text(js_escape(xlt('Not authorized')));
        }

        $sid = (string)$this->getRequest('docid');
        $isDownload = $this->getRequest('download') == 'true';
        $isDelete = $this->getRequest('delete') == 'true';

        if ($sid === '') {
            return text(json_encode(['error' => xlt('Missing fax ID')]));
        }

        try {
            // Delete: drop it from SignalWire (handled = removed upstream).
            if ($isDelete) {
                $this->deleteUpstreamFax($sid);
                return json_encode('success');
            }

            $fax = $this->fetchUpstreamFax($sid);
            $mediaUrl = $fax !== null ? ($fax->mediaUrl ?? '') : '';
            if ($fax === null || $mediaUrl === '') {
                return text(json_encode(['error' => xlt('Fax media not available from provider')]));
            }

            $rawData = $this->downloadFaxMediaContent($mediaUrl);
            if ($rawData === null || $rawData === '') {
                return text(json_encode(['error' => xlt('Failed to retrieve fax from provider')]));
            }

            $mime = 'application/pdf';

            if ($isDownload) {
                // The user is taking it: stage an encrypted temp copy for
                // disposeDocument to stream, then free the upstream copy to honor
                // the "downloaded -> no longer available here" contract.
                $filePath = $this->saveFaxToFile($rawData, $sid);
                $this->setSession('where', $filePath);
                $this->deleteUpstreamFax($sid);
                return text(json_encode([
                    'base64' => base64_encode($rawData),
                    'mime' => $mime,
                    'filename' => 'Fax_' . $sid . '.pdf',
                    'path' => $filePath,
                ]));
            }

            // View: base64 for the in-modal viewer; nothing is disposed.
            return text(json_encode([
                'base64' => base64_encode($rawData),
                'mime' => $mime,
                'filename' => 'Fax_' . $sid . '.pdf',
            ]));
        } catch (\Throwable $e) {
            error_log('SignalWireClient.viewFax(): ERROR - ' . $e->getMessage());
            return text(json_encode(['error' => xlt('Error retrieving fax')]));
        }
    }

    /**
     * Stage fax bytes to an encrypted-at-rest temp file for the download flow.
     * The path is read back by disposeDocument()/sendFile().
     *
     * @return string Absolute file path
     */
    private function saveFaxToFile(string $data, string $jobId): string
    {
        $dir = $this->baseDir;
        if ($dir !== '' && !is_dir($dir)) {
            mkdir($dir, 0700, true);
        }
        $filePath = $dir . DIRECTORY_SEPARATOR . 'Fax_' . $jobId . '.pdf';
        file_put_contents($filePath, $this->crypto->encryptForFilesystem($data));
        return $filePath;
    }

    /**
     * Download handoff for the shared getDocument() download branch. Mirrors the
     * vendor contract: action=setup writes/echoes the temp path; action=download
     * streams the staged file and removes it.
     *
     * @return string JSON (setup) or streams the file (download)
     */
    public function disposeDocument(): string
    {
        $response = ['success' => false, 'message' => '', 'url' => ''];
        $where = $this->getRequest('file_path') ?: $this->getSession('where');

        if (empty($where)) {
            die(xlt('Problem with download. Use browser back button'));
        }

        $content = $this->getRequest('content', '');
        $action = $this->getRequest('action');

        if ($action == 'download') {
            // sendFile() streams the file and exits, removing the staged temp
            // after a successful stream, so no cleanup is reachable here.
            $this->sendFile((string)$where);
            exit;
        }

        if (!empty($content) && $action == 'setup') {
            $decodedContent = base64_decode((string)$content);
            if (file_put_contents($where, $this->crypto->encryptForFilesystem($decodedContent)) !== false) {
                $response['success'] = true;
                $response['url'] = $where;
            } else {
                $response['message'] = 'Failed to write file';
            }
        } elseif ($action == 'setup') {
            // PDF path: viewFax already staged the encrypted file; hand it back.
            $response['success'] = true;
            $response['url'] = $where;
        }

        return json_encode($response);
    }

    /**
     * Decrypt the at-rest temp file and stream it to the browser as a download.
     */
    private function sendFile(string $filePath): void
    {
        $payload = $this->uploadStaging->decryptFileBytes($filePath);
        if ($payload === null) {
            http_response_code(500);
            echo xlt('Failed to read fax file');
            exit;
        }
        ob_end_clean();
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=" . basename($filePath));
        header("Content-Type: application/pdf");
        header("Content-Transfer-Encoding: binary");
        header('Content-Length: ' . strlen($payload));
        echo $payload;
        @unlink($filePath);
        exit;
    }

    /**
     * File a received fax to a patient chart: download it from the provider,
     * store it as the patient's document, then delete it from SignalWire so the
     * upstream copy is freed. The fax_id request value is the SignalWire SID.
     *
     * @return string
     */
    public function assignFax(): string
    {
        if (!$this->authenticate()) {
            return json_encode(['error' => xlt('Not authorized')]);
        }

        $sid = (string)$this->getRequest('fax_id');   // SignalWire SID
        $patientId = (int)$this->getRequest('patient_id');

        if ($sid === '' || $patientId <= 0) {
            return json_encode(['error' => xlt('Missing fax ID or patient ID')]);
        }

        try {
            $fax = $this->fetchUpstreamFax($sid);
            $mediaUrl = $fax !== null ? ($fax->mediaUrl ?? '') : '';
            $from = $fax !== null ? ($fax->from ?? '') : '';
            if ($fax === null || $mediaUrl === '') {
                return json_encode(['error' => xlt('Fax media not available from provider')]);
            }

            $faxService = new \OpenEMR\Modules\FaxSMS\Controller\FaxDocumentService();
            $result = $faxService->downloadAndStoreFromUrl($sid, $mediaUrl, $from, $this->projectId, $this->apiToken, $patientId);

            if (empty($result['success'])) {
                return json_encode(['error' => xlt('Failed to store fax document')]);
            }

            // Filed to the chart - free the upstream copy on SignalWire.
            $this->deleteUpstreamFax($sid);

            return json_encode(['success' => true, 'document_id' => $result['document_id'] ?? null]);
        } catch (\Throwable $e) {
            error_log("SignalWireClient.assignFax(): ERROR - " . $e->getMessage());
            return json_encode(['error' => xlt('Failed to assign fax')]);
        }
    }

    /**
     * Render the inbound fax inbox directly from the live SignalWire list.
     *
     * Stateless by design (the RingCentral model): SignalWire is the system of
     * record, so this neither persists to nor reads from oe_faxsms_queue. Each
     * row renders icon actions routed through the shared getDocument() handler
     * (view / download / delete) plus file-to-chart, exactly like the other
     * vendors. Failed faxes are not shown; in-progress ('receiving') faxes show
     * as such with no actions.
     *
     * @return string
     */
    public function getPending(): string
    {
        if (!$this->authenticate()) {
            return $this->authErrorDefault;
        }

        $fromTs = strtotime((string)$this->getRequest('datefrom'));
        $toTs = strtotime((string)$this->getRequest('dateto'));
        if ($fromTs === false) {
            $fromTs = strtotime('-30 days');
        }
        if ($toTs === false) {
            $toTs = time();
        }
        $dateFrom = date('Y-m-d', $fromTs);
        $dateTo = date('Y-m-d', $toTs);

        // Index 0 = received (inbound), 1 = sent (outbound), 2 = reserved.
        $responseMsg = [0 => '', 1 => '', 2 => xlt('Not Implemented')];

        if ($this->client !== null) {
            $session = SessionWrapperFactory::getInstance()->getActiveSession();
            $site_id = $session->get('site_id') ?? 'default';

            try {
                // LaML date filters are UTC (RFC/ISO-8601 Z).
                $dateFromISO = gmdate('Y-m-d\TH:i:s\Z', (int)strtotime($dateFrom . ' 00:00:01 UTC'));
                $dateToISO = gmdate('Y-m-d\TH:i:s\Z', (int)strtotime($dateTo . ' 23:59:59 UTC'));

                $faxes = $this->client->fax->v1->faxes->read(
                    ['dateCreatedAfter' => $dateFromISO, 'dateCreatedOnOrBefore' => $dateToISO],
                    self::FAX_LIST_LIMIT
                );

                if ($this::debugLogging) error_log("SignalWireClient.getPending(): DEBUG - Found " . count($faxes) . " faxes upstream");

                foreach ($faxes as $fax) {
                    $sid = (string)($fax->sid ?? '');
                    if ($sid === '') {
                        continue;
                    }
                    $direction = $fax->direction ?? 'inbound';
                    $status = $fax->status ?? 'unknown';
                    $from = $fax->from ?? '';
                    $to = $fax->to ?? '';
                    $numPages = (int)($fax->numPages ?? 0);
                    $dateLocal = $fax->dateCreated
                        ? $fax->dateCreated->setTimezone(new \DateTimeZone(date_default_timezone_get()))->format('M j, Y g:i:sa T')
                        : '';

                    if ($this::debugLogging) {
                        error_log("SignalWireClient.getPending(): DEBUG - sid={$sid} dir={$direction} status={$status} pages={$numPages} from={$from} to={$to} mediaUrl=" . ($fax->mediaUrl ?? ''));
                    }

                    // Column mapping matches the shared non-RC fax table headers
                    // (Date | Status | From | To | Result | Message | Actions),
                    // populated from the SignalWire fax JSON:
                    //   Status  = status      (received | receiving | ...)
                    //   Result  = num_pages   ("N pages")
                    //   Message = inline div  (reserved, parity with shared UI)
                    $statusCol = text($status);
                    $resultCol = $numPages > 0 ? (text($numPages) . " " . xlt('pages')) : '';
                    $messageCol = "<div class='" . attr($sid) . "'></div>";

                    if ($direction === 'outbound') {
                        // Sent faxes: view/download icons only.
                        $actions = '';
                        if (in_array($status, self::TERMINAL_FAX_STATUSES, true)) {
                            $actions .= "<a role='button' href='#' onclick=\"getDocument(event, null, " . attr_js($sid) . ", 'false')\"><i class='fa fa-file-pdf mr-2' title='" . xla('View fax document') . "'></i></a>";
                            $actions .= "<a role='button' href='#' onclick=\"getDocument(event, null, " . attr_js($sid) . ", 'true')\"><i class='fa fa-file-download mr-2' title='" . xla('Download fax document') . "'></i></a>";
                        }
                        $responseMsg[1] .= "<tr><td>" . text($dateLocal) . "</td><td>" . text($from) . "</td><td>" . text($to) . "</td><td>" . $resultCol . "</td><td>" . $statusCol . "</td><td class='text-left'>" . $actions . "</td></tr>";
                        continue;
                    }

                    // Inbound. Hide failures; show in-progress; act on received.
                    if (in_array($status, self::FAILED_FAX_STATUSES, true)) {
                        continue;
                    }

                    $actions = '';
                    if (in_array($status, self::TERMINAL_FAX_STATUSES, true)) {
                        // Icon actions routed through the shared getDocument() handler
                        // (same contract as every other vendor): chart, view, download, delete.
                        $actions .= "<a role='button' href='#' onclick=\"assignFaxToPatient(" . attr_js($sid) . ")\"><i class='fa fa-chart-simple mr-2' title='" . xla('File fax to a patient chart') . "'></i></a>";
                        $actions .= "<a role='button' href='#' onclick=\"getDocument(event, null, " . attr_js($sid) . ", 'false')\"><i class='fa fa-file-pdf mr-2' title='" . xla('View fax document') . "'></i></a>";
                        $actions .= "<a role='button' href='#' onclick=\"getDocument(event, null, " . attr_js($sid) . ", 'true')\"><i class='fa fa-file-download mr-2' title='" . xla('Download fax document') . "'></i></a>";
                        $actions .= "<a role='button' href='#' onclick=\"getDocument(event, null, " . attr_js($sid) . ", 'false', 'true')\"><i class='text-danger fa fa-trash mr-2' title='" . xla('Delete fax') . "'></i></a>";
                    } else {
                        // In-progress: shown for visibility, no actions yet.
                        $statusCol = "<span class='badge badge-secondary'>" . text($status) . "</span>";
                    }

                    $responseMsg[0] .= "<tr><td>" . text($dateLocal) . "</td><td>" . text($from) . "</td><td>" . text($to) . "</td><td>" . text($resultCol) . "</td><td>" . text($statusCol) . "</td><td class='text-left'>" . $actions . "</td></tr>";
                }
            } catch (\Throwable $e) {
                error_log("SignalWireClient.getPending(): ERROR - " . $e->getMessage());
            }
        }

        if (empty($responseMsg[0])) {
            $responseMsg[0] = xlt("Currently inbox is empty.");
        }
        if (empty($responseMsg[1])) {
            $responseMsg[1] = xlt("No sent faxes found.");
        }

        echo json_encode($responseMsg);
        exit();
    }

    /**
     * Fetch a single fax resource from SignalWire by SID.
     *
     * @return FaxInstance|null
     */
    private function fetchUpstreamFax(string $sid): ?FaxInstance
    {
        if ($this->client === null || $sid === '') {
            return null;
        }
        try {
            return $this->client->fax->v1->faxes->getContext($sid)->fetch();
        } catch (\Throwable $e) {
            error_log("SignalWireClient.fetchUpstreamFax(): ERROR - " . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete a fax (and its media) from SignalWire. Used when a fax is filed to
     * a chart or dismissed, so the provider stops holding it.
     */
    private function deleteUpstreamFax(string $sid): bool
    {
        if ($this->client === null || $sid === '') {
            return false;
        }
        try {
            return $this->client->fax->v1->faxes->getContext($sid)->delete();
        } catch (\Throwable $e) {
            error_log("SignalWireClient.deleteUpstreamFax(): ERROR - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Download fax media content from SignalWire
     *
     * Uses file_get_contents for HTTP requests. Validates URL to prevent SSRF attacks.
     *
     * @param string $mediaUrl URL to download fax media from
     * @return string|null Binary content if successful, null otherwise
     */
    private function downloadFaxMediaContent(string $mediaUrl): ?string
    {
        try {
            if (empty($mediaUrl)) {
                error_log("SignalWireClient.downloadFaxMediaContent(): Empty media URL");
                return null;
            }

            if (!$this->isValidSignalWireUrl($mediaUrl)) {
                error_log("SignalWireClient.downloadFaxMediaContent(): Invalid SignalWire URL: {$mediaUrl}");
                return null;
            }

            // files.signalwire.com media downloads use Bearer auth; other
            // SignalWire hosts use Basic project/token auth. Bounded timeout so a
            // slow provider can't hang the request thread.
            $options = ['http_errors' => true];
            if (str_contains($mediaUrl, 'files.signalwire.com')) {
                $options['headers'] = ['Authorization' => 'Bearer ' . $this->apiToken];
            } else {
                $options['auth'] = [$this->projectId, $this->apiToken];
            }

            $response = (new \GuzzleHttp\Client(['timeout' => 30]))->request('GET', $mediaUrl, $options);
            return (string)$response->getBody();
        } catch (\Throwable $e) {
            error_log("SignalWireClient.downloadFaxMediaContent(): ERROR - " . $e->getMessage());
            return null;
        }
    }

    /**
     * Validate URL is from SignalWire to prevent SSRF attacks
     *
     * @param string $url URL to validate
     * @return bool True if URL is valid SignalWire URL
     */
    private function isValidSignalWireUrl(string $url): bool
    {
        $parsedUrl = parse_url($url);

        if ($parsedUrl === false || !isset($parsedUrl['scheme']) || !isset($parsedUrl['host'])) {
            return false;
        }

        // Only allow HTTPS
        if ($parsedUrl['scheme'] !== 'https') {
            return false;
        }

        // Whitelist SignalWire domains
        $allowedDomains = ['files.signalwire.com', 'api.signalwire.com'];
        $host = strtolower($parsedUrl['host']);

        foreach ($allowedDomains as $domain) {
            if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Forward a fax
     *
     * @return string
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function forwardFax(): string
    {
        return json_encode(['error' => xlt('Forward fax not yet implemented')]);
    }

    /**
     * Process uploaded fax files
     *
     * @return string
     */
    public function faxProcessUploads(): string
    {
        $upload = $_FILES['fax'] ?? null;
        return is_array($upload)
            ? $this->uploadStaging->processUpload($this->baseDir, $upload)
            : '';
    }

    /**
     * @return string
     */
    public function getCallLogs()
    {
        return xlt('Not Supported');
    }
}
