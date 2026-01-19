<?php

/**
 * SignalWire Fax Client
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    SignalWire Integration
 * @copyright Copyright (c) 2024
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS\Controller;

use Document;
use Exception;
use MyMailer;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use SignalWire\Rest\Client;

class SignalWireClient extends AppDispatch
{
    public static $timeZone;
    protected $baseDir;
    protected $uriDir;
    protected $serverUrl;
    protected $credentials;
    public string $portalUrl;
    protected CryptoGen $crypto;
    private $client;
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
        $this->crypto = new CryptoGen();
        $this->baseDir = $globals->get('temporary_files_dir');
        $this->uriDir = $globals->get('OE_SITE_WEBROOT');

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
        } catch (Exception $e) {
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

        error_log("SignalWireClient.getCredentials(): DEBUG - faxNumber after E.164 formatting: " . ($this->faxNumber ?: 'EMPTY'));

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

        if (empty($this->client)) {
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
        $recipientName = $this->getRequest('name') . ' ' . $this->getRequest('surname');
        $recipientName = trim($recipientName) ?: 'Unknown'; // Default if empty
        $isDocumentsParam = $this->getRequest('isDocuments');
        $isDocuments = !empty($isDocumentsParam) ? (int)$isDocumentsParam : 0;
        $email = $this->getRequest('email');
        $hasEmail = $this->validEmail($email);
        $globals = OEGlobalsBag::getInstance();
        $smtpEnabled = !empty($globals->get('SMTP_PASS') ?? null) && !empty($globals->get('SMTP_USER') ?? null);
        $user = $this::getLoggedInUser();

        // DEBUG: Log parameters received in sendFax
        error_log("SignalWireClient.sendFax(): DEBUG - Received file path: " . $file);
        error_log("SignalWireClient.sendFax(): DEBUG - isContent: " . ($isContent ?? 'EMPTY'));
        error_log("SignalWireClient.sendFax(): DEBUG - isDocuments: " . $isDocuments);
        error_log("SignalWireClient.sendFax(): DEBUG - Phone: " . $phone);
        error_log("SignalWireClient.sendFax(): DEBUG - File exists: " . (!empty($file) && file_exists($file) ? 'YES' : 'NO'));
        if (!empty($file) && file_exists($file)) {
            error_log("SignalWireClient.sendFax(): DEBUG - File size: " . filesize($file) . " bytes");
        }

        // Handle file path
        if (empty($isContent) && !empty($file)) {
            if (str_starts_with((string) $file, 'file://')) {
                $file = substr((string) $file, 7);
            }
            $realPath = realpath($file);
            if ($realPath !== false) {
                $file = str_replace("\\", "/", $realPath);
            } else {
                return xlt('Error: No content SignalWireClient');
            }
        }

        // Handle document retrieval
        if ($isDocuments) {
            $file = (new Document($docId))->get_data();
        }

        // Send email if requested
        if ($hasEmail && $smtpEnabled) {
            self::emailDocument($email, '', $file, $user);
        }

        // Validate phone number
        if (empty($phone)) {
            return xlt('Error: Invalid phone number');
        }

        // Upload file to accessible URL
        $mediaUrl = $this->uploadFileForFax($file, $isDocuments);
        if (empty($mediaUrl)) {
            return xlt('Error: Failed to prepare document for faxing');
        }

        try {
            // Send fax via SignalWire REST API
            // The SDK expects: create($options) where options is an array with 'to', 'from', 'mediaUrl'
            error_log("SignalWireClient.sendFax(): DEBUG - About to call SignalWire fax create");
            error_log("SignalWireClient.sendFax(): DEBUG - to={$phone}, from={$this->faxNumber}");
            error_log("SignalWireClient.sendFax(): DEBUG - mediaUrl={$mediaUrl}");

            $fax = $this->client->fax->v1->faxes->create([
                'to' => $phone,
                'from' => $this->faxNumber,
                'mediaUrl' => $mediaUrl
            ]);

            // Get logged-in user's username
            $username = $user['username'] ?? $_SESSION['authUser'] ?? 'System';

            // Insert into queue
            $this->insertFaxQueue([
                'job_id' => $fax->sid,
                'calling_number' => $username,      // Store username who sent the fax
                'called_number' => $recipientName,  // Store recipient name
                'phone' => $phone,                   // Store phone separately for reference
                'status' => $fax->status,
                'direction' => 'outbound'
            ]);

            return json_encode([
                'success' => true,
                'message' => xlt('Fax queued successfully'),
                'fax_sid' => $fax->sid,
                'status' => $fax->status
            ]);
        } catch (Exception $e) {
            error_log('SignalWire Fax Error: ' . $e->getMessage());
            return json_encode([
                'success' => false,
                'error' => xlt('Error sending fax') . ': ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Upload file to make it accessible for SignalWire
     *
     * @param string $file
     * @param bool $isDocuments
     * @return string|null
     */
    private function uploadFileForFax(string $file, bool $isDocuments = false): ?string
    {
        try {
            // DEBUG: Log before upload
            error_log("SignalWireClient.uploadFileForFax(): DEBUG - baseDir: " . ($this->baseDir ?? 'EMPTY'));
            error_log("SignalWireClient.uploadFileForFax(): DEBUG - File parameter: " . $file);
            error_log("SignalWireClient.uploadFileForFax(): DEBUG - isDocuments: " . ($isDocuments ? 'YES' : 'NO'));

            // Use public web root for uploads so SignalWire can access via HTTP
            // Store in web root's public area accessible to external IPs
            // Use GLOBALS['fileroot'] which is properly set by globals.php
            $globals = OEGlobalsBag::getInstance();
            $webRoot = $globals->get('fileroot') ?? dirname(__DIR__, 5);

            // Get site_id with fallback to 'default'
            $siteId = $_SESSION['site_id'] ?? $globals->get('OE_SITE_NAME') ?? 'default';
            error_log("SignalWireClient.uploadFileForFax(): DEBUG - Using siteId: " . $siteId);
            error_log("SignalWireClient.uploadFileForFax(): DEBUG - Using fileroot: " . $webRoot);

            $relativeUploadDir = 'sites/' . $siteId . '/fax';
            $uploadDir = $webRoot . '/' . $relativeUploadDir;
            error_log("SignalWireClient.uploadFileForFax(): DEBUG - Web root: " . $webRoot);
            error_log("SignalWireClient.uploadFileForFax(): DEBUG - Upload dir: " . $uploadDir);

            if (!file_exists($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                    error_log("SignalWireClient.uploadFileForFax(): ERROR - Failed to create directory: " . $uploadDir);
                    return null;
                }
                error_log("SignalWireClient.uploadFileForFax(): DEBUG - Created upload directory");
            }

            $filename = uniqid('fax_') . '_' . basename($file);
            $uploadPath = $uploadDir . '/' . $filename;
            error_log("SignalWireClient.uploadFileForFax(): DEBUG - Upload path: " . $uploadPath);
            error_log("SignalWireClient.uploadFileForFax(): DEBUG - Filename: " . $filename);

            if ($isDocuments) {
                file_put_contents($uploadPath, $file);
                error_log("SignalWireClient.uploadFileForFax(): DEBUG - Wrote document content to file");
            } else {
                if (!file_exists($file)) {
                    error_log("SignalWireClient.uploadFileForFax(): ERROR - Source file does not exist: " . $file);
                    return null;
                }
                copy($file, $uploadPath);
                error_log("SignalWireClient.uploadFileForFax(): DEBUG - Copied file from: " . $file);
            }

            // Return publicly accessible URL
            // SignalWire accesses this from external IP, so it must be web-accessible
            $mediaUrl = $this->serverUrl . '/' . $relativeUploadDir . '/' . $filename;
            error_log("SignalWireClient.uploadFileForFax(): DEBUG - Generated media URL: " . $mediaUrl);
            return $mediaUrl;
        } catch (Exception $e) {
            error_log('SignalWireClient.uploadFileForFax(): ERROR - ' . $e->getMessage());
            error_log('SignalWireClient.uploadFileForFax(): TRACE - ' . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Insert fax into queue with all details
     *
     * @param array $faxData
     * @return void
     */
    private function insertFaxQueue(array $faxData): void
    {
        $uid = $_SESSION['authUserID'] ?? 0;
        $site_id = $_SESSION['site_id'] ?? 'default';
        $direction = $faxData['direction'] ?? 'outbound';
        $status = $faxData['status'] ?? 'queued';

        $sql = "INSERT INTO oe_faxsms_queue
                (uid, job_id, calling_number, called_number, details_json, date, direction, status, site_id)
                VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?)";

        QueryUtils::sqlStatementThrowException($sql, [
            $uid,
            $faxData['job_id'] ?? '',
            $faxData['calling_number'] ?? '',
            $faxData['called_number'] ?? '',
            json_encode($faxData),
            $direction,
            $status,
            $site_id
        ]);
    }

    /**
     * Fetch fax queue
     *
     * @param string $dateFrom
     * @param string $dateTo
     * @param bool $received
     * @return array
     */
    private function fetchFaxQueue(string $dateFrom, string $dateTo, bool $received = true): array
    {
        $uid = $_SESSION['authUserID'] ?? 0;
        $site_id = $_SESSION['site_id'] ?? 'default';

        // For inbound faxes, show to all users in the site
        // For outbound faxes, show only to the user who sent them
        $sql = "SELECT * FROM oe_faxsms_queue
                WHERE site_id = ?
                  AND date BETWEEN ? AND ?
                  AND (direction = 'inbound' OR uid = ?)
                ORDER BY date DESC";

        $rows = QueryUtils::fetchRecords($sql, [$site_id, $dateFrom, $dateTo, $uid]);
        $faxes = [];

        foreach ($rows as $row) {
            $faxes[] = (object)$row;
        }

        return $faxes;
    }

    /**
     * Fetch reminder count
     *
     * @return string
     */
    public function fetchReminderCount(): string
    {
        return json_encode(['count' => $this->fetchQueueCount()]);
    }

    /**
     * Get fax queue count
     *
     * @return int
     */
    private function fetchQueueCount(): int
    {
        $uid = $_SESSION['authUserID'] ?? 0;
        $sql = "SELECT COUNT(*) as count FROM oe_faxsms_queue WHERE uid = ? AND deleted = 0";
        $result = QueryUtils::querySingleRow($sql, [$uid]);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Send SMS (not implemented for fax-only service)
     *
     * @return string
     */
    public function sendSMS(): string
    {
        return json_encode(['error' => xlt('SMS not implemented for SignalWire Fax')]);
    }

    /**
     * Send Email (not implemented)
     *
     * @return mixed
     */
    public function sendEmail(): mixed
    {
        return xlt('Email not implemented for SignalWire Fax');
    }

    /**
     * Email a document
     *
     * @param string $email
     * @param string $body
     * @param string $file
     * @param array $user
     * @return string
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public static function emailDocument(string $email, string $body, string $file, array $user = []): string
    {
        $globals = OEGlobalsBag::getInstance();
        $from_name = ($user['fname'] ?? '') . ' ' . ($user['lname'] ?? '');
        $desc = xlt("Comment") . ":\n" . text($body) . "\n" . xlt("This email has an attached fax document.");
        $mail = new MyMailer();
        $from_name = text($from_name);
        $from = $globals->get("practice_return_email_path");
        $mail->AddReplyTo($from, $from_name);
        $mail->SetFrom($from, $from);
        $mail->AddAddress($email, $email);
        $mail->Subject = xlt("Forwarded Fax Document");
        $mail->Body = $desc;
        $mail->AddAttachment($file);

        return $mail->Send() ? xlt("Email successfully sent.") : xlt("Error: Email failed") . text($mail->ErrorInfo);
    }

    /**
     * Format phone number to E.164
     *
     * @param string $number
     * @return string
     */
    public function formatPhone(string $number): string
    {
        $n = preg_replace('/[^0-9]/', '', $number);
        if (stripos((string) $n, '1') === 0) {
            $n = '+' . $n;
        } elseif (!empty($n)) {
            $n = '+1' . $n;
        }

        return $this->validatePhone($n) ? $n : '';
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
     * View fax PDF file inline in browser
     *
     * @return void
     */
    public function viewFaxPdf(): void
    {
        if (!$this->authenticate()) {
            http_response_code(403);
            die(xlt('Not authorized'));
        }

        $queueId = $this->getRequest('id');
        if (empty($queueId)) {
            http_response_code(400);
            die(xlt('Missing fax ID'));
        }

        // Fetch fax from queue
        $site_id = $_SESSION['site_id'] ?? 'default';
        $fax = QueryUtils::querySingleRow(
            "SELECT * FROM oe_faxsms_queue WHERE id = ? AND site_id = ?",
            [$queueId, $site_id]
        );

        if (empty($fax)) {
            http_response_code(404);
            die(xlt('Fax not found'));
        }

        $mediaPath = $fax['media_path'] ?? '';
        if (empty($mediaPath) || !file_exists($mediaPath)) {
            http_response_code(404);
            die(xlt('Fax file not found'));
        }

        // Send file for inline viewing
        $filename = basename((string) $mediaPath);
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($mediaPath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: public');
        readfile($mediaPath);
        exit;
    }

    /**
     * Download fax PDF file
     *
     * @return void
     */
    public function download(): void
    {
        if (!$this->authenticate()) {
            http_response_code(403);
            die(xlt('Not authorized'));
        }

        $queueId = $this->getRequest('id');
        if (empty($queueId)) {
            http_response_code(400);
            die(xlt('Missing fax ID'));
        }

        // Fetch fax from queue
        $site_id = $_SESSION['site_id'] ?? 'default';
        $fax = QueryUtils::querySingleRow(
            "SELECT * FROM oe_faxsms_queue WHERE id = ? AND site_id = ?",
            [$queueId, $site_id]
        );

        if (empty($fax)) {
            http_response_code(404);
            die(xlt('Fax not found'));
        }

        $mediaPath = $fax['media_path'] ?? '';
        if (empty($mediaPath) || !file_exists($mediaPath)) {
            http_response_code(404);
            die(xlt('Fax file not found'));
        }

        // Send file as download
        $filename = basename((string) $mediaPath);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($mediaPath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: public');
        readfile($mediaPath);
        exit;
    }

    /**
     * Assign fax to patient
     *
     * @return string
     */
    public function assignFax(): string
    {
        if (!$this->authenticate()) {
            return json_encode(['error' => xlt('Not authorized')]);
        }

        $queueId = $this->getRequest('fax_id');  // This is the queue table ID, not job_id
        $patientId = $this->getRequest('patient_id');

        if (empty($queueId) || empty($patientId)) {
            return json_encode(['error' => xlt('Missing fax ID or patient ID')]);
        }

        try {
            // Look up the fax from queue to get job_id (SID)
            $site_id = $_SESSION['site_id'] ?? 'default';
            $fax = QueryUtils::querySingleRow(
                "SELECT job_id, patient_id FROM oe_faxsms_queue WHERE id = ? AND site_id = ?",
                [$queueId, $site_id]
            );

            if (empty($fax)) {
                return json_encode(['error' => xlt('Fax not found')]);
            }

            if (!empty($fax['patient_id'])) {
                return json_encode(['error' => xlt('Fax already assigned to patient ') . $fax['patient_id']]);
            }

            $jobId = $fax['job_id'];  // This is the SignalWire SID

            // Load the FaxDocumentService
            require_once(__DIR__ . '/FaxDocumentService.php');
            $faxService = new \OpenEMR\Modules\FaxSMS\Controller\FaxDocumentService();

            // Use the service to assign the fax (pass job_id, not queue id)
            $result = $faxService->assignFaxToPatient($jobId, $patientId);

            if ($result['success']) {
                return json_encode(['success' => true, 'document_id' => $result['document_id']]);
            } else {
                return json_encode(['error' => $result['message']]);
            }
        } catch (Exception $e) {
            error_log("SignalWireClient.assignFax(): ERROR - " . $e->getMessage());
            return json_encode(['error' => xlt('Failed to assign fax: ') . $e->getMessage()]);
        }
    }

    /**
     * Retrieve faxes from SignalWire API and populate local queue
     *
     * @return string
     */
    public function getPending(): string
    {
        if (!$this->authenticate()) {
            return $this->authErrorDefault;
        }

        $dateFrom = $this->getRequest('datefrom');
        $dateTo = $this->getRequest('dateto');

        try {
            // Fetch faxes from SignalWire API
            $dateFromISO = date('c', strtotime($dateFrom . 'T00:00:01'));
            $dateToISO = date('c', strtotime($dateTo . 'T23:59:59'));

            error_log("SignalWireClient.getPending(): DEBUG - Fetching faxes from SignalWire");
            error_log("SignalWireClient.getPending(): DEBUG - dateFrom={$dateFromISO}, dateTo={$dateToISO}");

            // Fetch faxes from SignalWire API with date filtering
            $faxes = $this->client->fax->v1->faxes->read(
                [
                    'dateCreatedAfter' => $dateFromISO,
                    'dateCreatedOnOrBefore' => $dateToISO
                ],
                100  // limit
            );

            error_log("SignalWireClient.getPending(): DEBUG - Found " . count($faxes) . " faxes from SignalWire");

            // Insert/update faxes in local queue
            foreach ($faxes as $fax) {
                $this->upsertFaxFromSignalWire($fax);
            }
        } catch (Exception $e) {
            error_log("SignalWireClient.getPending(): ERROR - " . $e->getMessage());
        }

        // Fetch from local queue for display
        $dateFromDB = date("Y-m-d H:i:s", strtotime($dateFrom . 'T00:00:01'));
        $dateToDB = date("Y-m-d H:i:s", strtotime($dateTo . 'T23:59:59'));
        $faxStore = $this->fetchFaxQueue($dateFromDB, $dateToDB, false);

        // Initialize response array with keys 0, 1, 2 like other controllers
        $responseMsg = [0 => '', 1 => '', 2 => xlt('Not Implemented')];

        foreach ($faxStore as $faxDetails) {
            $details = json_decode($faxDetails->details_json ?? '{}', true);
            $formattedDate = date('M j, Y g:i:sa T', strtotime((string) $faxDetails->date));
            $direction = $faxDetails->direction ?? ($details['direction'] ?? 'inbound');
            $status = $faxDetails->status ?? ($details['status'] ?? 'unknown');
            $jobId = $faxDetails->job_id;  // FAX SID from SignalWire
            $recipientName = $faxDetails->called_number ?? '';  // Recipient name or phone
            $senderUsername = $faxDetails->calling_number ?? '';  // Sender phone
            $numPages = $details['numPages'] ?? 0;
            $patientId = $faxDetails->patient_id ?? 0;
            $documentId = $faxDetails->document_id ?? 0;
            $mediaPath = $faxDetails->media_path ?? '';
            $queueId = $faxDetails->id ?? 0;

            // Build message column with view, download links and patient info
            $messageCol = '';

            // Only show View/Download for inbound (received) faxes
            if ($direction === 'inbound') {
                // If assigned to patient, link to patient document
                if ($patientId > 0 && $documentId > 0) {
                    $globals = OEGlobalsBag::getInstance();
                    $viewLink = $globals->get('webroot') . "/controller.php?document&retrieve&patient_id=" .
                                urlencode((string) $patientId) . "&document_id=" . urlencode((string) $documentId) .
                                "&as_file=false&original_file=true";
                    $messageCol .= "<a href='" . attr($viewLink) . "' target='_blank' class='btn btn-sm btn-success'>" .
                                   "<i class='fa fa-eye'></i> " . xlt('View') . "</a> ";
                    if ($numPages > 0) {
                        $messageCol .= "(" . text($numPages) . " " . xlt('pages') . ") ";
                    }
                } elseif (!empty($mediaPath) && file_exists($mediaPath)) {
                    // Unassigned fax - use queue view/download links
                    $filename = basename((string) $mediaPath);
                    $viewLink = "./viewFaxPdf?type=fax&site=" . urlencode($_SESSION['site_id'] ?? 'default') . "&id=" . urlencode($queueId);
                    $downloadLink = "./download?type=fax&site=" . urlencode($_SESSION['site_id'] ?? 'default') . "&id=" . urlencode($queueId);

                    $messageCol .= "<a href='" . attr($viewLink) . "' target='_blank' class='btn btn-sm btn-success'>" .
                                   "<i class='fa fa-eye'></i> " . xlt('View') . "</a> ";
                    $messageCol .= "<a href='" . attr($downloadLink) . "' target='_blank' class='btn btn-sm btn-primary'>" .
                                   "<i class='fa fa-download'></i> " . xlt('Download') . "</a> ";
                    if ($numPages > 0) {
                        $messageCol .= "(" . text($numPages) . " " . xlt('pages') . ") ";
                    }
                }
            } else {
                // Outbound faxes - just show page count if available
                if ($numPages > 0) {
                    $messageCol .= "(" . text($numPages) . " " . xlt('pages') . ")";
                }
            }

            if ($direction === 'inbound') {
                // Show patient assignment status
                if ($patientId > 0) {
                    // Get patient name
                    $patientRow = QueryUtils::querySingleRow("SELECT fname, lname FROM patient_data WHERE pid = ?", [$patientId]);
                    if ($patientRow) {
                        $patientName = text($patientRow['fname'] . ' ' . $patientRow['lname']);
                        $messageCol .= "<span class='badge badge-success'>" . xlt('Assigned to') . ": {$patientName}</span>";
                    }
                } else {
                    $messageCol .= "<span class='badge badge-warning'>" . xlt('Unassigned') . "</span> ";
                    // Add assign button
                    $messageCol .= "<button class='btn btn-sm btn-info' onclick='assignFaxToPatient(" . attr($queueId) . ")'>" .
                                   "<i class='fa fa-user-plus'></i> " . xlt('Assign') . "</button>";
                }
            }

            // Build row with correct column mapping:
            // Start Time | Message (download + patient) | From | To | Result | Reply
            $faxRow = "<tr>
                <td>" . text($formattedDate) . "</td>
                <td>" . $messageCol . "</td>
                <td>" . text($senderUsername) . "</td>
                <td>" . text($recipientName) . "</td>
                <td>" . text($status) . "</td>
            </tr>";

            // Index 0 = received (inbound), Index 1 = sent (outbound)
            $responseMsg[$direction === 'outbound' ? 1 : 0] .= $faxRow;
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
     * Insert or update a fax from SignalWire into local queue
     * Fetches current status from SignalWire API for accurate disposition
     *
     * @param mixed $fax Fax object from SignalWire API
     * @return void
     */
    private function upsertFaxFromSignalWire($fax): void
    {
        try {
            $uid = $_SESSION['authUserID'] ?? 0;
            $jobId = $fax->sid;
            $direction = $fax->direction ?? 'unknown';
            $status = $fax->status ?? 'unknown';
            $from = $fax->from ?? '';
            $to = $fax->to ?? '';
            $numPages = $fax->numPages ?? 0;
            $duration = $fax->duration ?? 0;
            $dateCreated = $fax->dateCreated ? $fax->dateCreated->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');

            // Fetch fresh status from SignalWire API for current fax details
            try {
                $freshFax = $this->client->fax->v1->faxes->getContext($jobId)->fetch();
                $status = $freshFax->status ?? $status;
                $numPages = $freshFax->numPages ?? $numPages;
                $duration = $freshFax->duration ?? $duration;
                error_log("SignalWireClient.upsertFaxFromSignalWire(): DEBUG - Fetched fresh status from API: {$status}");
            } catch (Exception $e) {
                error_log("SignalWireClient.upsertFaxFromSignalWire(): WARNING - Could not fetch fresh status: " . $e->getMessage());
            }

            $faxData = [
                'sid' => $jobId,
                'from' => $from,
                'to' => $to,
                'phone' => $to,  // Store phone number separately
                'status' => $status,
                'direction' => $direction,
                'numPages' => $numPages,
                'duration' => $duration,
                'dateCreated' => $dateCreated
            ];

            error_log("SignalWireClient.upsertFaxFromSignalWire(): DEBUG - Upserting fax sid={$jobId}, from={$from}, to={$to}, status={$status}, direction={$direction}");

            // Download fax media if available
            $mediaPath = $this->downloadFaxMedia($fax);
            if ($mediaPath) {
                $faxData['media_path'] = $mediaPath;
            }

            // Check if fax already exists in queue
            $existing = QueryUtils::querySingleRow("SELECT id, called_number, media_path FROM oe_faxsms_queue WHERE job_id = ?", [$jobId]);

            if (!empty($existing)) {
                // Update existing fax with fresh status and media path
                $sql = "UPDATE oe_faxsms_queue
                        SET details_json = ?,
                            direction = ?,
                            status = ?,
                            media_path = ?
                        WHERE job_id = ?";
                QueryUtils::sqlStatementThrowException($sql, [json_encode($faxData), $direction, $status, $mediaPath ?? null, $jobId]);
                error_log("SignalWireClient.upsertFaxFromSignalWire(): DEBUG - Updated fax {$jobId} with fresh status");
            } else {
                // Insert new fax from API fetch (these are received/already-sent faxes)
                $sql = "INSERT INTO oe_faxsms_queue
                        (uid, job_id, calling_number, called_number, details_json, date, direction, status, site_id, media_path)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $site_id = $_SESSION['site_id'] ?? 'default';
                QueryUtils::sqlStatementThrowException($sql, [$uid, $jobId, $from, $to, json_encode($faxData), $dateCreated, $direction, $status, $site_id, $mediaPath ?? null]);
                error_log("SignalWireClient.upsertFaxFromSignalWire(): DEBUG - Inserted fax {$jobId}");
            }
        } catch (Exception $e) {
            error_log("SignalWireClient.upsertFaxFromSignalWire(): ERROR - " . $e->getMessage());
        }
    }

    /**
     * Download fax media from SignalWire and save locally
     *
     * @param mixed $fax Fax object from SignalWire API
     * @return string|null Local file path if successful, null otherwise
     */
    private function downloadFaxMedia($fax): ?string
    {
        try {
            // Get media URL from fax object
            $mediaUrl = $fax->mediaUrl ?? null;
            if (empty($mediaUrl)) {
                error_log("SignalWireClient.downloadFaxMedia(): No media URL available for fax {$fax->sid}");
                return null;
            }

            // Create directory for fax media if it doesn't exist
            $faxDir = $this->baseDir . '/received_faxes';
            if (!file_exists($faxDir)) {
                mkdir($faxDir, 0777, true);
            }

            // Generate filename
            $filename = $fax->sid . '.pdf';
            $filepath = $faxDir . '/' . $filename;

            // Skip if file already exists
            if (file_exists($filepath)) {
                error_log("SignalWireClient.downloadFaxMedia(): File already exists: {$filepath}");
                return $filepath;
            }

            // Download the file from SignalWire
            $fileContent = file_get_contents($mediaUrl);
            if ($fileContent === false) {
                error_log("SignalWireClient.downloadFaxMedia(): Failed to download media from {$mediaUrl}");
                return null;
            }

            // Save the file
            if (file_put_contents($filepath, $fileContent) === false) {
                error_log("SignalWireClient.downloadFaxMedia(): Failed to save file to {$filepath}");
                return null;
            }

            error_log("SignalWireClient.downloadFaxMedia(): Successfully downloaded fax to {$filepath}");
            return $filepath;
        } catch (Exception $e) {
            error_log("SignalWireClient.downloadFaxMedia(): ERROR - " . $e->getMessage());
            return null;
        }
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
        if (empty($_FILES['fax']) || $_FILES['fax']['error'] !== UPLOAD_ERR_OK) {
            error_log('Error: No file uploaded or upload error.');
            return '';
        }

        $name = basename((string) $_FILES['fax']['name']);
        $tmp_name = $_FILES['fax']['tmp_name'];
        $targetDir = $this->baseDir . '/send';

        if (!file_exists($targetDir) && !mkdir($targetDir, 0777, true)) {
            error_log('Error: Failed to create directory.');
            return '';
        }

        $filepath = $targetDir . "/" . $name;

        if (!move_uploaded_file($tmp_name, $filepath)) {
            error_log('Error: Failed to move uploaded file.');
            return '';
        }

        return $filepath;
    }

    /**
     * Format phone number for saving
     *
     * @param string $number
     * @return string
     */
    public function formatPhoneForSave($number): string
    {
        return preg_replace('/[^0-9+]/', '', $number);
    }
}
