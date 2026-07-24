<?php

/**
 * Fax SMS Module Member
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS\Controller;

use Document;
use Exception;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Common\Session\PatientSessionUtil;
use OpenEMR\Common\Utils\FileUtils;
use OpenEMR\Common\ValueObjects\PhoneNumber;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\FaxSMS\Contracts\FaxChannelInterface;
use OpenEMR\Modules\FaxSMS\Contracts\SmsChannelInterface;
use OpenEMR\Modules\FaxSMS\Service\FaxMailer;
use OpenEMR\Modules\FaxSMS\Service\FaxUploadStaging;
use OpenEMR\Services\ImageUtilities\HandleImageService;
use RingCentral\SDK\Http\ApiException;

class RCFaxClient extends AppDispatch implements FaxChannelInterface, SmsChannelInterface
{
    use AuthenticateTrait;

    public string $baseDir = '';
    public $uriDir;
    public $serverUrl;
    public $redirectUrl;
    public $portalUrl;
    public $credentials;
    public $cacheDir;
    public $apiBase;
    public $apiService;
    protected $platform;
    protected $rcsdk;
    protected CryptoInterface $crypto;
    private readonly FaxUploadStaging $uploadStaging;

    private const AUTH_RATE_LIMIT = 5; // Max attempts per minute

    public function __construct()
    {
        $this->crypto = ServiceContainer::getCrypto();
        $this->uploadStaging = FaxUploadStaging::create();
        $this->baseDir = OEGlobalsBag::getInstance()->getString('temporary_files_dir');
        $this->uriDir = OEGlobalsBag::getInstance()->get('OE_SITE_WEBROOT');
        $this->cacheDir = OEGlobalsBag::getInstance()->get('OE_SITE_DIR') . '/documents/logs_and_misc/_cache';
        $this->credentials = $this->getCredentials();
        // RingCentral retired the developer sandbox (platform.devtest.ringcentral.com)
        // at the end of 2024; only production remains. Hardcode it so the dead
        // sandbox host can never be selected again.
        $this->portalUrl = "https://service.ringcentral.com/";
        $this->serverUrl = "https://platform.ringcentral.com";
        $this->redirectUrl = $this->credentials['redirect_url'] ?? null;
        $this->initializeSDK();
        parent::__construct();
    }

    /**
     * Used by fax file drag and drop
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
     * @param string $toPhone
     * @param string $subject
     * @param string $message
     * @param string $from
     * @return string|bool
     */
    public function sendSMS($toPhone = '', $subject = '', $message = '', $from = ''): string|bool
    {
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return text(js_escape($authErrorMsg));
            // goes to alert
        }
        $toPhone = $toPhone ?: $this->getRequest('phone');
        $from = $from ?: $this->getRequest('from');
        $from = $from ?: (is_array($this->credentials) ? ($this->credentials['smsNumber'] ?? '') : '');
        $message = $message ?: $this->getRequest('comments');

        $smsNumber = $this->formatPhone($from);
        $toPhone = $this->formatPhone($toPhone);
        if ($smsNumber) {
            try {
                $this->platform->post('/account/~/extension/~/sms', [
                    'from' => ['phoneNumber' => $smsNumber],
                    'to' => [['phoneNumber' => $toPhone]],
                    'text' => $message,
                ]);
                sleep(1); // Sleep to avoid rate limit 10 per minute
                return true;
            } catch (ApiException $e) {
                return text("API Error: " . $e->getMessage() . " - " . $e->getCode());
            }
        }

        return true;
    }

    /**
     * @return string
     */
    public function fetchTextMessage(): string
    {
        $id = $_REQUEST['id'] ?? null;
        $uri = $_REQUEST['uri'] ?? null;

        if (empty($id) || empty($uri)) {
            return "Missing id or uri parameters.";
        }

        $authResult = $this->authenticateRingCentral();
        if ($authResult !== 1) {
            return $authResult;
        }

        try {
            $response = $this->platform->get($uri);
            return js_escape((string)$response->text());
        } catch (\Throwable $e) {
            $responseMsg = "<tr><td>" . text($e->getMessage()) . "</td></tr>";
            return json_encode(['error' => $responseMsg]);
        }
    }

    /**
     * @return array
     */
    public function getCredentials(): array
    {
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
        return AppDispatch::getSetup();
    }

    /**
     * API Endpoint for sending
     *
     * @return string
     */
    public function forwardFax(): string
    {
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return text(js_escape($authErrorMsg)); // goes to alert
        }

        $jobId = $this->getRequest('docid');
        $email = $this->getRequest('email');
        $faxNumber = $this->formatPhone($this->getRequest('phone'));
        $hasEmail = $this->validEmail($email);
        $smtpEnabled = !empty(OEGlobalsBag::getInstance()->getString('SMTP_HOST') ?? null);
        $user = $this::getLoggedInUser();
        $facility = substr((string)$user['facility'], 0, 20);
        $csid = $this->formatPhone($this->credentials['phone']);
        $tag = xlt("Forwarded");
        $statusMsg = xlt("Forwarding Requests") . "<br />";

        if (!$hasEmail && empty($faxNumber)) {
            return js_escape(xlt("Error: Nothing to forward. Try again."));
        }

        try {
            // Fetch the fax message details
            $messageDetailsResponse = $this->platform->get("/account/~/extension/~/message-store/{$jobId}");
            $messageDetails = $messageDetailsResponse->json();

            // Fetch the fax content
            $contentUri = $messageDetails->attachments[0]->uri;
            $apiResponse = $this->platform->get($contentUri);
            $contentType = (string)($apiResponse->response()->getHeader('Content-Type')[0] ?? '');
            $rawData = (string)$apiResponse->raw();

            $stagedPath = $this->uploadStaging->stageInternalPayload(
                $this->baseDir,
                $rawData,
                (string)$jobId,
                $contentType
            );
            if ($stagedPath === '') {
                return js_escape('Error: ' . xlt('Failed to stage fax payload for forwarding'));
            }

            $plainPath = null;
            try {
                $plainPath = $this->uploadStaging->decryptStagedToTemp($stagedPath);
                if ($plainPath === null) {
                    return js_escape('Error: ' . xlt('Failed to prepare fax payload for forwarding'));
                }

                if ($hasEmail && $smtpEnabled && is_string($email)) {
                    $statusMsg .= FaxMailer::send($email, (string)$this->getRequest('comments'), $plainPath, $user) . "<br />";
                }
                if ($faxNumber) {
                    try {
                        $this->sendFax(
                            $faxNumber,
                            $plainPath,
                            $user['username'],
                            $jobId,
                            $contentType
                        );
                        $statusMsg .= xlt("Successfully forwarded fax to") . ' ' . text($faxNumber) . "<br />";
                    } catch (\Throwable $e) {
                        return js_escape('Error: ' . text($e->getMessage()));
                    }
                }
            } finally {
                $this->uploadStaging->removeStagedArtifacts($stagedPath, $plainPath);
            }
            return js_escape($statusMsg);
        } catch (ApiException|\Throwable $e) {
            return js_escape('Error: ' . text($e->getMessage()));
        }
    }

    /**
     * @param $phone
     * @param $file
     * @param $name
     * @param $comments
     * @param $fileName
     * @return bool|string
     */
    public function sendFax($phone = '', $file = '', $name = '', $comments = '', $fileName = null): bool|string
    {
        // Authenticate and refresh token if needed
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return text(js_escape($authErrorMsg)); // goes to alert
        }

        // Ensure some needed args if not past in or from API abstracted endpoint sendFax().
        $isContent = $this->getRequest('isContent'); // remember this flag is set in patient report and not just it has content.
        $file = $this->getRequest('file', $file); // could be content or file path.
        $isFilePath = is_file($file);
        $isDocuments = (int)$this->getRequest('isDocuments', 0); //from patient documents
        $docId = $this->getRequest('docid');
        $phone = $this->formatPhone($this->getRequest('phone', $phone));
        $comments = trim((string)$this->getRequest('comments', $comments));
        $email = $this->getRequest('email');
        $hasEmail = $this->validEmail($email);
        $smtpEnabled = !empty(OEGlobalsBag::getInstance()->getString('SMTP_HOST') ?? null);
        $user = $this::getLoggedInUser();
        $name = $this->getRequest('name', $name) . ' ' . $this->getRequest('surname', '');
        $fileName ??= pathinfo((string)$file, PATHINFO_BASENAME);

        $allowedTempDir = realpath($this->baseDir . '/send/');
        // Validate file path to prevent path traversal
        if (is_file($file)) {
            if (str_starts_with((string)$file, 'file://')) {
                $file = substr((string)$file, 7);
            }
            $realPath = realpath($file);
            if ($realPath !== false) {
                $allowedRoot = $allowedTempDir !== false
                    ? rtrim($allowedTempDir, DIRECTORY_SEPARATOR)
                    : false;
                // Require an exact match or a true child path; a bare prefix
                // check would let a sibling like ".../send_evil" slip through.
                $withinAllowed = $allowedRoot !== false
                    && ($realPath === $allowedRoot
                        || str_starts_with($realPath, $allowedRoot . DIRECTORY_SEPARATOR));
                if (!$withinAllowed) {
                    error_log("Path traversal blocked: " . $realPath);
                    return xlt('Error: Invalid file location');
                }
                $file = str_replace("\\", "/", $realPath);
            } else {
                return xlt('Error: No Fax content');
            }
        }
        // Decrypt a staged upload to a per-request plaintext tempnam and
        // continue with that as $file. Pattern guard scopes the cleanup
        // below to files this controller staged via FaxUploadStaging,
        // leaving caller-managed temp files alone (e.g. forwardFax).
        $stagedPath = null;
        $plainStagePath = null;
        $emailPath = null;
        try {
            if (
                empty($isContent)
                && !$isDocuments
                && is_string($file)
                && is_file($file)
                && $this->uploadStaging->isStagedUploadPath($file)
            ) {
                $plainStagePath = $this->uploadStaging->decryptStagedToTemp($file);
                if ($plainStagePath === null) {
                    return xlt('Error: No content to send.');
                }
                $stagedPath = $file;
                $file = $plainStagePath;
                // Name the attachment from the staged file (which carries the
                // real .pdf/.tiff extension), not the decrypt tempnam, whose
                // Windows ".tmp" suffix makes RingCentral reject the attachment.
                $fileName = pathinfo($stagedPath, PATHINFO_BASENAME);
            }

            // Build $content (plaintext bytes for the vendor).
            if ($isContent) {
                $content = $file;
                $file = 'report-' . PatientSessionUtil::getPid() . '.pdf';
            } else {
                if ($isDocuments) {
                    // Enforce patients/docs ACL and patient ownership before
                    // reading a request-supplied document id (see A3).
                    $content = $this->readAuthorizedFaxDocument(is_scalar($docId) ? (int) $docId : 0);
                } elseif (is_file($file)) {
                    $content = file_get_contents($file);
                    if ($content === false) {
                        return xlt('Error: No content to send.');
                    }
                } else {
                    $content = $file;
                }
                if (empty($content)) {
                    return xlt('Error: No content to send.');
                }
            }

            // Defensive decrypt: a no-op on plaintext via cryptCheckStandard,
            // covers any caller that supplied already-ciphertext content.
            $content = $this->crypto->decryptFromFilesystem($content);

            // Email: hand the payload to FaxMailer. When we have a real
            // plaintext path (the staged-upload branch), it's emailed as
            // is; for the isContent / isDocuments branches we pass the
            // decrypted bytes and FaxMailer writes a per-request scratch
            // file, returning that path for finally cleanup.
            $error = false;
            if ($hasEmail && $smtpEnabled) {
                try {
                    $payloadIsContent = !(is_string($file) && is_file($file));
                    $emailPath = FaxMailer::mailUploadedDocument(
                        $email,
                        $comments,
                        $payloadIsContent ? $content : $file,
                        $user,
                        $payloadIsContent,
                    );
                } catch (\PHPMailer\PHPMailer\Exception) {
                    $error = true;
                }
            }

            // Request to send the fax
            try {
                $this->sendFaxRequest($phone, $content, $fileName, $comments, $name);
                // debug error log
                error_log($phone . ' ' . $fileName . ' ' . $comments . ' ' . $name);
                return xlt('Fax Successfully Sent') . ($error === true ? ("<br />" . xlt("Email Failed")) : '');
            } catch (\Throwable $e) {
                return 'Error: ' . text(js_escape($e->getMessage()));
            }
        } finally {
            $this->uploadStaging->removeStagedArtifacts(
                $stagedPath,
                $plainStagePath,
                $emailPath
            );
        }
    }

    /**
     * @param $phone
     * @param $content
     * @param $fileName
     * @param $comments
     * @param $name
     * @return void
     * @throws Exception
     */
    private function sendFaxRequest($phone, $content, $fileName = '', $comments = 'No Comment', $name = ''): void
    {
        // Almost always $content is file content but lets check in case it is a file path
        if (is_file($content)) {
            $content = file_get_contents($content);
        }
        try {
            $phone = $this->formatPhone($phone);
            $mime = FileUtils::fileGetMimeType($fileName, $content);
            $type = $mime['type'];
            $fileName = $mime['filePath'];
            if (empty($type)) {
                $type = mime_content_type($content);
            }
            // RingCentral 400s when an attachment's filename extension does not
            // match its Content-Type. Tempnam-derived names (e.g. a Windows
            // "....tmp") and content-mode names can drift, so force the
            // extension to match the resolved type before sending.
            $extByType = [
                'application/pdf' => 'pdf',
                'image/tiff' => 'tiff', 'image/tif' => 'tiff',
                'image/jpeg' => 'jpg', 'image/jpg' => 'jpg',
                'image/png' => 'png', 'text/plain' => 'txt',
            ];
            $wantExt = $extByType[strtolower((string)$type)] ?? null;
            if ($wantExt !== null && strtolower(pathinfo((string)$fileName, PATHINFO_EXTENSION)) !== $wantExt) {
                $stem = pathinfo((string)$fileName, PATHINFO_FILENAME);
                $fileName = ($stem !== '' ? $stem : 'fax') . '.' . $wantExt;
            }
            //error_log($phone . ' ' . $fileName . ' ' . $type . ' ' . $name);
            $request = $this->rcsdk->createMultipartBuilder()
                ->setBody([
                    'to' => [['phoneNumber' => $phone, 'name' => $name]],
                    'faxResolution' => 'High',
                    'coverPageText' => text($comments)
                ])
                ->add($content, $fileName, ['Content-Type' => (string)$type])
                ->request('/account/~/extension/~/fax');
            $this->platform->sendRequest($request);
        } catch (ApiException $e) {
            throw new Exception($this->handleApiException($e));
        }
    }

    /**
     * @param ApiException $e
     * @return string
     */
    private function handleApiException(ApiException $e): string
    {
        $error = $e->apiResponse ? $e->apiResponse->text() : $e->getMessage();

        if (stripos((string)$error, 'invalid_grant') !== false) {
            try {
                $this->platform->login(['jwt' => $this->credentials['jwt']]);
                if ($this->platform->loggedIn()) {
                    $this->cacheAuthData($this->platform);
                    return 'Fax Successfully Sent';
                }
            } catch (\Throwable $ex) {
                return "Re-authentication Error: " . text($ex->getMessage());
            }
        }
        return "API Error: " . text($e->getMessage()) . " - " . text($e->getCode()) . "\n" . text(json_encode($e->apiResponse ? $e->apiResponse->json() : [], JSON_PRETTY_PRINT));
    }

    /**
     * @return string
     */
    public function getStoredDoc(): string
    {
        $docuri = $this->getRequest('docuri');
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return text(js_escape($authErrorMsg));
            // goes to alert
        }

        try {
            $apiResponse = $this->platform->get($docuri);
        } catch (ApiException $e) {
            return "Error: Retrieving Fax: " . text($e->getMessage() . $e->apiResponse()->request()->getUri()->__toString());
        }

        $contentType = $apiResponse->response()->getHeader('Content-Type')[0];
        $rawData = (string)$apiResponse->raw();
        if ($contentType == 'application/pdf') {
            return 'data:application/pdf;base64,' . rawurlencode(base64_encode($rawData));
        } elseif ($contentType == 'image/tiff') {
            return 'data:image/tiff;base64,' . rawurlencode(base64_encode($rawData));
        } else {
            return $rawData;
        }
    }

    /**
     * @param string $contentType
     * @return string
     */
    public function getExtensionFromContentType(string $contentType): string
    {
        return match ($contentType) {
            'application/pdf' => 'pdf',
            'text/plain' => 'txt',
            'image/tiff' => 'tiff',
            'image/jpeg' => 'jpeg',
            'image/jpg' => 'jpg',
            'image/gif' => 'gif',
            'image/png' => 'png',
            'application/xml' => 'xml',
            'audio/wav', 'audio/x-wav' => 'wav',
            default => 'application/pdf',
        };
    }

    /**
     * @param string $content
     * @return void
     */
    public function disposeDoc($content = ''): void
    {
        $where = $this->getSession('where');
        if (file_exists($where)) {
            ob_clean();
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=" . basename((string)$where));
            header("Content-Type: application/download");
            header("Content-Transfer-Encoding: binary");
            header('Content-Length: ' . filesize($where));
            readfile($where);
            unlink($where);
            exit;
        }
        die(xlt('Problem with download. Use browser back button'));
    }

    /**
     * @return string
     */
    public function viewFax(): string
    {
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return text(js_escape($authErrorMsg)); // goes to alert
        }

        $jobId = $this->getRequest('docid');
        $isDownload = $this->getRequest('download') == 'true';
        $isDelete = $this->getRequest('delete') == 'true';

        $messageStoreDir = $this->baseDir;
        if (!file_exists($messageStoreDir)) {
            mkdir($messageStoreDir, 0777, true);
        }

        try {
            // Fetch the message details
            $messageDetailsResponse = $this->platform->get("/account/~/extension/~/message-store/{$jobId}");
            if ($messageDetailsResponse->response()->getStatusCode() !== 200) {
                return json_encode(['error' => "Error: Retrieving Fax: " . $messageDetailsResponse->response()->getReasonPhrase()]);
            }
            $messageDetails = $messageDetailsResponse->json();

            if ($isDelete) {
                // Delete the message
                $this->platform->delete("/account/~/extension/~/message-store/{$jobId}");
                return json_encode('success');
            }

            $contentUri = $messageDetails->attachments[0]->uri;
            $apiResponse = $this->platform->get($contentUri);
            $contentType = $apiResponse->response()->getHeader('Content-Type')[0];
            $rawData = (string)$apiResponse->raw();

            if ($isDownload) {
                $filePath = $this->saveFaxToFile($rawData, $jobId, $contentType);
                $this->setSession('where', $filePath);
                return text(json_encode(['base64' => base64_encode($rawData), 'mime' => $contentType, 'path' => $filePath]));
            }
            return text(json_encode(['base64' => base64_encode($rawData), 'mime' => $contentType]));
        } catch (ApiException $e) {
            return text(json_encode(['error' => "Error: Retrieving Fax: " . $e->getMessage()]));
        }
    }

    /**
     * @param string $jobId
     * @return mixed
     */
    public function fetchFaxFromQueue(string $jobId): mixed
    {
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return text(js_escape($authErrorMsg)); // goes to alert
        }

        try {
            $apiResponse = $this->platform->get("/account/~/extension/~/message-store/{$jobId}/content");
            $contentType = $apiResponse->response()->getHeader('Content-Type')[0];
            $rawData = (string)$apiResponse->raw();

            return [
                'contentType' => $contentType,
                'data' => base64_encode($rawData)
            ];
        } catch (ApiException $e) {
            return text(json_encode(['error' => "API Error: " . $e->getMessage()]));
        } catch (\Throwable $e) {
            return text(json_encode(['error' => "Error: " . $e->getMessage()]));
        }
    }

    /**
     * @param string $data
     * @param string $contentType
     * @return string
     */
    private function formatFaxDataUrl(string $data, string $contentType): string
    {
        return match ($contentType) {
            'application/pdf' => 'data:application/pdf;base64,' . base64_encode($data),
            'image/tiff', 'image/tif' => 'data:image/tiff;base64,' . base64_encode($data),
            default => 'data:text/plain;base64,' . base64_encode($data),
        };
    }

    /**
     * @param string $data
     * @param string $jobId
     * @param string $contentType
     * @return string
     */
    private function saveFaxToFile(string $data, string $jobId, string $contentType): string
    {
        $fileExtension = $this->getFileExtension($contentType);
        $fileName = "Fax_{$jobId}." . $fileExtension;
        $filePath = $this->baseDir . DIRECTORY_SEPARATOR . $fileName;

        // Write encrypted-at-rest. The session-stored path is read back in
        // disposeDocument's download branch (sendFile), which decrypts via
        // FaxUploadStaging::decryptFileBytes.
        file_put_contents($filePath, $this->crypto->encryptForFilesystem($data));

        return $filePath;
    }

    /**
     * @param string $contentType
     * @return string
     */
    private function getFileExtension(string $contentType): string
    {
        return match ($contentType) {
            'application/pdf' => 'pdf',
            'image/tiff', 'image/tif' => 'tiff',
            default => 'txt',
        };
    }

    /**
     * @param $encodedFax
     * @return string
     * @throws Exception
     */
    public function formatFax($encodedFax): string
    {
        $control = new HandleImageService();
        $formatted_document = $control->convertImageToPdf($encodedFax, '');

        return $formatted_document ? base64_encode($formatted_document) : false;
    }

    /**
     * @return string
     */
    public function disposeDocument(): string
    {
        $response = ['success' => false, 'message' => '', 'url' => ''];
        $where = $this->getRequest('file_path') ?? $this->getSession('where');

        if (empty($where)) {
            die(xlt('Problem with download. Use browser back button'));
        }

        $content = $this->getRequest('content', '');
        $action = $this->getRequest('action');

        if ($action == 'download') {
            $this->sendFile($where);
            sleep(2);
            unlink($where);
            exit;
        }

        if (!empty($content) && $action == 'setup') {
            $decodedContent = base64_decode((string)$content);
            // Write encrypted-at-rest; the download branch's sendFile
            // call decrypts via FaxUploadStaging::decryptFileBytes.
            if (file_put_contents($where, $this->crypto->encryptForFilesystem($decodedContent)) !== false) {
                $response['success'] = true;
                $response['url'] = $where;
            } else {
                $response['message'] = 'Failed to write file';
            }
        } elseif ($action == 'setup') {
            $response['success'] = true;
            $response['url'] = $where;
        }

        return json_encode($response);
    }

    /**
     * Decrypt the at-rest fax file and stream it to the browser. Legacy
     * plaintext files flow through unchanged via decryptFromFilesystem's
     * version-prefix check.
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
        exit;
    }

    /**
     * @param string $messageId
     * @return string
     */
    public function downloadFax(string $messageId): string
    {
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return text(js_escape($authErrorMsg)); // goes to alert
        }

        try {
            $response = $this->platform->get("/account/~/extension/~/message-store/{$messageId}/content");
            $contentType = $response->response()->getHeader('Content-Type')[0];
            $fileExtension = $this->getFileExtension($contentType);
            $fileName = "fax_{$messageId}." . $fileExtension;
            $content = (string)$response->raw();

            // Stream straight from memory. The earlier write-to-cacheDir-
            // then-readfile-then-unlink dance left plaintext PHI on disk
            // for the request duration with no functional benefit over
            // serving the bytes directly.
            ob_end_clean();
            header('Content-Description: File Transfer');
            header('Content-Type: ' . $contentType);
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . strlen($content));
            echo $content;
            exit;
        } catch (ApiException $e) {
            return text(json_encode(['error' => "API Error: " . $e->getMessage()]));
        } catch (\Throwable $e) {
            return text(json_encode(['error' => "Error: " . $e->getMessage()]));
        }
    }

    /**
     * @param string $phone
     * @return string|bool
     */
    public function findPatientByPhone(string $phone): bool|string
    {
        if (empty($phone)) {
            return '';
        }

        // Region-aware: a "+CC" number self-describes; a bare national number
        // is read against the site default region (US fallback). Match on the
        // national digits against a separator-stripped column so stored values
        // like "(239) 555-0123" still compare cleanly.
        $parsed = PhoneNumber::tryParse($phone, $this->defaultPhoneRegion());
        $national = $parsed?->isPossible() ? $parsed->getNationalDigits() : null;
        $digits = (string) preg_replace('/\D/', '', $national ?? $phone);
        if ($digits === '') {
            return '';
        }

        $sql = "
        SELECT CONCAT(fname, ' ', lname) AS fullname
        FROM patient_data
        WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone_cell, '-', ''), '(', ''), ')', ''), ' ', ''), '+', '') LIKE ?
        LIMIT 1
    ";

        $result = sqlQuery($sql, ["%" . $digits]);
        $rtn = $result['fullname'] ?? '';
        if (!empty($rtn)) {
            $rtn .= ' ';
        } else {
            $rtn = '';
        }

        return $rtn;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        $id = $this->getRequest('uid');
        $query = "SELECT * FROM users WHERE id = ?";
        $result = sqlStatement($query, [$id]);
        $u = sqlFetchArray($result);
        return json_encode([$u['fname'], $u['lname'], $u['fax']]);
    }

    /**
     * @return string
     */
    public function getNotificationLog(): string
    {
        $type = $this->getRequest('type');
        $fromDate = $this->getRequest('datefrom');
        $toDate = $this->getRequest('dateto');
        try {
            $query = "SELECT notification_log.* FROM notification_log WHERE notification_log.dSentDateTime > ? AND notification_log.dSentDateTime < ?";
            $res = sqlStatement($query, [$fromDate, $toDate]);
            $responseMsg = '';
            while ($nrow = sqlFetchArray($res)) {
                $adate = ($nrow['pc_eventDate'] . '::' . $nrow['pc_startTime']);
                $pinfo = str_replace("|||", " ", $nrow['patient_info']);
                $msg = text($nrow["message"]);
                $responseMsg .= "<tr><td>" . text($nrow["pc_eid"]) . "</td><td>" . text($nrow["dSentDateTime"]) . "</td><td>" . text($adate) . "</td><td>" . text($pinfo) . "</td><td>" . text($msg) . "</td></tr>";
            }
        } catch (\Throwable $e) {
            return 'Error: ' . text($e->getMessage()) . PHP_EOL;
        }

        return $responseMsg;
    }

    /**
     * @return string
     */
    public function getCallLogs(): string
    {
        $fromDate = $this->getRequest('datefrom');
        $toDate = $this->getRequest('dateto');
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return text(js_escape($authErrorMsg));
            // goes to alert
        }

        try {
            $pageCount = 1;
            $recordCountPerPage = 100;
            $timePerCallLogRequest = 10;
            $flag = true;
            $timeFrom = '00:00:00.000Z';
            $timeTo = '23:59:59.000Z';
            $responseMsg = "";
            while ($flag) {
                $start = microtime(true);
                $dateFrom = $fromDate . 'T' . $timeFrom;
                $dateTo = $toDate . 'T' . $timeTo;
                $apiResponse = $this->platform->get('/account/~/extension/~/call-log', [
                    'dateFrom' => $dateFrom,
                    'dateTo' => $dateTo,
                    'perPage' => 500,
                    'page' => $pageCount
                ]);
                foreach ($apiResponse->json()->records as $value) {
                    $responseMsg .= "<tr><td>" . text(str_replace(["T", "Z"], " ", self::asText($value->startTime ?? null))) . "</td><td>" . text(self::asText($value->type ?? null)) . "</td><td>" . text(self::asText($value->from->name ?? null)) . "</td><td>" . text(self::asText($value->to->phoneNumber ?? null)) . "</td><td>" . text(self::asText($value->action ?? null)) . "</td><td>" . text(self::asText($value->result ?? null)) . "</td><td>" . text(self::asText($value->message->id ?? null)) . "</td></tr>";
                }

                $end = microtime(true);
                $time = ($end - $start);
                if (isset($apiResponse->json()->navigation->nextPage)) {
                    if ($time < $timePerCallLogRequest) {
                        sleep($timePerCallLogRequest - $time);
                        sleep(5);
                        $pageCount++;
                    }
                } else {
                    $flag = false;
                }
            }
        } catch (ApiException $e) {
            return xlt('HTTP Error') . ': ' . text($e->getMessage()) . PHP_EOL;
        }

        return $responseMsg;
    }

    /**
     * Fetch all pending SMS or Fax message‑store records in the date range
     * and return the HTML rows (or error) as JSON.
     *
     * @return false|string  JSON‑encoded string of table rows or error
     */
    public function getPending(): false|string
    {
        // 1) Authenticate
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return json_encode(['error' => js_escape($authErrorMsg)]);
        }

        // 2) Build date range
        $dateFrom = $this->getRequest('datefrom') . 'T00:00:01.000Z';
        $dateTo = $this->getRequest('dateto') . 'T23:59:59.000Z';
        $serviceType = strtolower((string)$this->getRequest('type', ''));

        // Decide messageType param
        if ($serviceType === 'sms') {
            $messageType = 'SMS';
        } elseif ($serviceType === 'fax') {
            $messageType = 'Fax';
        } else {
            return json_encode(['error' => xlt('Invalid service type. Please use "sms" or "fax".')]);
        }

        try {
            // 3) Paginate through all pages
            $allRecords = [];
            $page = 1;
            do {
                $resp = $this->platform->get(
                    '/restapi/v1.0/account/~/extension/~/message-store',
                    [
                        'messageType' => $messageType,
                        'dateFrom' => $dateFrom,
                        'dateTo' => $dateTo,
                        'perPage' => 100,
                        'page' => $page
                    ]
                );
                $data = $resp->json();
                if (!empty($data->records)) {
                    $allRecords = array_merge($allRecords, $data->records);
                }
                $hasNext = !empty($data->navigation->nextPage);
                if ($hasNext) {
                    usleep(200000); // 0.2s throttle to respect rate limits
                    $page++;
                }
            } while ($hasNext);

            // 4) Process into table rows
            $responseMsg = $this->processMessageStoreList($allRecords, $serviceType);
        } catch (ApiException $e) {
            $msg = "<tr><td>"
                . text($e->getMessage())
                . " : "
                . xlt('Report to Administration.')
                . "</td></tr>";
            return json_encode(['error' => $msg]);
        } catch (\Throwable $e) {
            return json_encode(['error' => text($e->getMessage())]);
        }

        // 5) Return JSON‑encoded rows (or fallback “nothing to report”)
        $rows = $responseMsg ?: [
            xlt("Nothing to report"),
            xlt("Nothing to report"),
            xlt("Nothing to report")
        ];
        return json_encode($rows);
    }

    /**
     * @return false|string
     */
    /*public function getPending(): false|string
    {
        // Authenticate and refresh token if needed
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return json_encode(['error' => js_escape($authErrorMsg)]);
        }

        // Get the date range and service type from the request
        $dateFrom = $this->getRequest('datefrom');
        $dateTo = $this->getRequest('dateto');
        $serviceType = $this->getRequest('type', '');

        try {
            $dateFrom .= 'T00:00:01.000Z';
            $dateTo .= 'T23:59:59.000Z';
            $serviceType = strtolower($serviceType);
            // Fetch the message store list based on the service type
            if ($serviceType == 'sms') {
                $messageStoreList = $this->platform->get('/account/~/extension/~/message-store', [
                    'dateFrom' => $dateFrom,
                    'dateTo' => $dateTo,
                    'messageType' => 'SMS',
                ])->json()->records;
            } elseif ($serviceType == 'fax') {
                $messageStoreList = $this->platform->get('/account/~/extension/~/message-store', [
                    'dateFrom' => $dateFrom,
                    'dateTo' => $dateTo,
                    'messageType' => 'Fax',
                ])->json()->records;
            } else {
                throw new Exception(
                    xlt('Invalid service type. Please use "sms" or "fax".')
                );
            }

            $responseMsg = $this->processMessageStoreList($messageStoreList, $serviceType);
        } catch (ApiException $e) {
            $responseMsg = "<tr><td>" . text($e->getMessage()) . " : " . xlt('Report to Administration.') . "</td></tr>";
            return json_encode(['error' => $responseMsg]);
        }

        return json_encode($responseMsg ?: [xlt("Nothing to report"), xlt("Nothing to report"), xlt("Nothing to report")]);
    }*/

    private function processMessageStoreList($messageStoreList, $serviceType): false|array|string
    {
        $responseMsg = ['', '', ''];
        $count = count($messageStoreList ?? []);
        $timePerMessageStore = 1; // seconds
        $start = microtime();
        $useLink = false;
        $cnt = 0;
        foreach ($messageStoreList as $messageStore) {
            if (property_exists($messageStore, 'attachments')) {
                foreach ($messageStore->attachments as $attachment) {
                    $id = attr($attachment->id);
                    $uri = $attachment->uri;
                    // Inbound messages carry no ->to, faxErrorCode only
                    // appears on failures, and a fax "from"/"to" entry may
                    // omit name; coalesce every read so polling a mixed
                    // inbox does not spray undefined-property/null-offset
                    // warnings into the error log.
                    $toEntry = $messageStore->to[0] ?? null;
                    $to = trim(($toEntry->name ?? '') . " " . ($toEntry->phoneNumber ?? ''));
                    $from = trim(($messageStore->from->name ?? '') . " " . ($messageStore->from->phoneNumber ?? ''));
                    $status = ($messageStore->messageStatus ?? '') . ($messageStore->from->faxErrorCode ?? '');
                    $faxFormattedDate = date('M j, Y g:i:sa T', strtotime((string)$messageStore->creationTime));
                    $updateDate = date('M j Y g:i:sa T', strtotime((string)$messageStore->lastModifiedTime));

                    $links = $this->generateActionLinks($id, $uri);
                    $checkbox = "<input type='checkbox' class='delete-fax-checkbox' value='" . attr($id) . "'>";
                    $type = strtolower((string)$messageStore->type);
                    $direction = strtolower((string)$messageStore->direction);
                    $messageText = '';
                    $pname = '';
                    if ($type === "sms" && $type === $serviceType) {
                        if ($direction === "inbound") {
                            $links = $this->generateSmsActionLinks($id, $uri, $messageStore->from->phoneNumber ?? '');
                            $pname = $this->findPatientByPhone($messageStore->from->phoneNumber ?? '');
                            try {
                                if (!$useLink) {
                                    $response = $this->platform->get($uri);
                                    $messageText = (string)$response->text();
                                    $messageText = str_replace("\n", "<br />", $messageText);
                                    sleep(0.8); // Sleep to avoid rate limit
                                } else {
                                    $messageText = xlt("Text retrieval error. Click show message");
                                }
                            } catch (ApiException $e) {
                                $messageText = "Error: " . text($e->getMessage());
                                if ($e->getCode() == 429) {
                                    $messageText = xlt("Rate limit exceeded. Please try again after 30 seconds.");
                                    $messageText .= "<br>" . xlt("If this error persists, narrow the date range.");
                                    $useLink = true; // Use link to show message
                                } elseif ($e->getCode() == 403) {
                                    $messageText = xlt("Access denied. Please check your permissions.");
                                    $useLink = true; // Use link to show message
                                } elseif ($e->getCode() == 404) {
                                    $messageText = xlt("Message not found. It may have been deleted or does not exist.");
                                }
                                if ($e->getCode() == 401) {
                                    $useLink = true;
                                }
                            }
                            $responseMsg[0] .= "<tr><td>" . text($faxFormattedDate) . "</td><td>" . text($messageStore->readStatus) . "</td><td>" . text($pname . $from) . "</td><td>" . text($to) . "</td><td>" . text($status) . "</td><td><div class='$id'>" . ($messageText) . "</div></td><td class='btn-group'>" . $links['sms'] . "</td></tr>";
                        } elseif ($direction === "outbound") {
                            $links = $this->generateSmsActionLinks($id, $uri, $messageStore->to[0]->phoneNumber ?? '');
                            $pname = $this->findPatientByPhone($messageStore->to->phoneNumber ?? '');
                            $responseMsg[1] .= "<tr><td>" . text($faxFormattedDate) . "</td><td>" . text($messageStore->readStatus) . "</td><td>" . text($from) . "</td><td>" . text($pname . $to) . "</td><td>" . text($status) . "</td><td><div class='$id'>" . text($messageText) . "</div></td><td class='btn-group'>" . $links['sms'] . "</td></tr>";
                        }
                        $toName = $to;
                        $fromName = $pname . $from;
                        if ($direction === "outbound") {
                            $toName = $pname . $to;
                            $fromName = $from;
                        }
                        $responseMsg[2] .= "<tr><td>" . text($faxFormattedDate) . "</td><td>" . text($messageStore->readStatus) . "</td><td>" . text($fromName) . "</td><td>" . text($toName) . "</td><td>" . text($status) . "</td><td><div class='$id'>" . text($messageText) . "</div></td><td class='btn-group'>" . $links['sms'] . "</td></tr>";
                    } elseif ($direction === "inbound" && $type === $serviceType && $serviceType === "fax") {
                        $status = ($messageStore->to[0]->faxErrorCode ?? '') ?: ($messageStore->messageStatus ?? '');
                        $responseMsg[0] .= "<tr><td>" . text($faxFormattedDate) . "</td><td>" . text($updateDate) . "</td><td>" . text($messageStore->faxPageCount ?? '') . "</td><td>" . text($from) . "</td><td>" . text($messageStore->subject ?? '') . "</td><td>" . text($status) . "</td><td class='text-left'>" . $links['inbound'] . "</td><td class='text-center'>" . $checkbox . "</td></tr>";
                    } elseif ($direction === "outbound" && $type === $serviceType && $serviceType === "fax") {
                        $status = ($messageStore->to[0]->faxErrorCode ?? '') ?: ($messageStore->messageStatus ?? '');
                        $responseMsg[1] .= "<tr><td>" . text($faxFormattedDate) . "</td><td>" . text($updateDate) . "</td><td>" . text($messageStore->faxPageCount ?? '') .
                            "</td><td>" . text($from) . "</td><td>" . text($to) . "</td><td>" . text($status) . "</td><td>" . $links['outbound'] . "</td><td class='text-center'>" . $checkbox . "</td></tr>";
                    }
                }
            }
        }

        return $responseMsg;
    }

    private function generateActionLinks($id, $uri): array
    {
        $patientLink = "<a role='button' href='#' onclick=\"createPatient(event, " . attr_js($id) . ", " . attr_js($id) . ", " . attr_js(json_encode([])) . ")\"> <i class='fa fa-chart-simple mr-2' title='" . xla("Chart fax or Create patient and chart fax to documents.") . "'></i></a>";
        $messageLink = "<a role='button' href='#' onclick=\"notifyUser(event, " . attr_js($id) . ", " . attr_js($id) . ", " . attr_js(0) . ")\"> <i class='fa fa-paper-plane mr-2' title='" . xla("Notify a user and attach this fax to message.") . "'></i></a>";
        $downloadLink = "<a role='button' href='#' onclick=\"getDocument(event, null, " . attr_js($id) . ", 'true')\"> <i class='fa fa-file-download mr-2' title='" . xla("Download and delete fax") . "'></i></a>";
        $viewLink = "<a role='button' href='#' onclick=\"getDocument(event, null, " . attr_js($id) . ", 'false')\"> <i class='fa fa-file-pdf mr-2' title='" . xla("View fax document") . "'></i></a>";
        $deleteLink = "<a role='button' href='#' onclick=\"getDocument(event, null, " . attr_js($id) . ", 'false', 'true')\"> <i class='text-danger fa fa-trash mr-2' title='" . xla("Delete this fax document") . "'></i></a>";
        $forwardLink = "<a role='button' href='#' onclick=\"forwardFax(event, " . attr_js($id) . ")\"> <i class='fa fa-forward mr-2' title='" . xla("Forward fax to new fax recipient or email attachment.") . "'></i></a>";
        return [
            'inbound' => $patientLink . $messageLink . $forwardLink . $viewLink . $downloadLink . $deleteLink,
            'outbound' => $viewLink . $downloadLink . $deleteLink
        ];
    }

    private function generateSmsActionLinks($id, $uri, $phone): array
    {
        $vtoggle = "<a role='button' href='#' onclick=messageShow(" . attr_js($id) . "," . attr_js($uri) . ")><span class='mx-1 fas fa-comment fa-1x'></span></a>";
        $vreply = "<a role='button' href='#' onclick=messageReply(" . attr_js($phone) . ")><span class='mx-1 fa fa-reply'></span></a>";

        return [
            'sms' => $vtoggle . $vreply,
            'smsoutbound' => $vreply
        ];
    }

    /**
     * @return string|null
     */
    protected function index(): ?string
    {
        if (!$this->getSession('pid', '')) {
            $pid = $this->getRequest('patient_id');
            $this->setSession('pid', $pid);
        }

        return null;
    }

    /**
     * @return string|bool
     */
    public function fetchReminderCount(): string|bool
    {
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return text(js_escape($authErrorMsg)); // goes to alert
        }

        if (self::$_apiModule == 'sms') {
            return '0';
        }
        try {
            $platform = $this->rcsdk->platform();
            $response = $platform->get('/restapi/v1.0/account/~/extension/~/message-store', [
                'messageType' => 'Fax',
                'direction' => 'Inbound',
                'availability' => 'Alive'
            ]);
            $json = $response->json();
            return text(count($json->records));
        } catch (\Throwable $e) {
            error_log('Error fetching incoming faxes in Reminder tasking: ' . text($e->getMessage()));
            return false;
        }
    }

    /**
     * @param $pid
     * @param $jobId
     * @param $fileName
     * @return string
     */
    public function chartFaxDocument($pid, $jobId, $fileName = null): string
    {
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return text(js_escape($authErrorMsg)); // goes to alert
        }

        // Determine the category ID
        $catid = sqlQuery("SELECT id FROM `categories` WHERE `name` = 'FAX'")['id'] ?? sqlQuery("SELECT id FROM `categories` WHERE `name` = 'Medical Record'")['id'];

        try {
            // Fetch the fax message details
            $messageDetailsResponse = $this->platform->get("/account/~/extension/~/message-store/{$jobId}");
            $messageDetails = $messageDetailsResponse->json();

            // Fetch the fax content
            $contentUri = $messageDetails->attachments[0]->uri;
            $apiResponse = $this->platform->get($contentUri);
            $contentType = $apiResponse->response()->getHeader('Content-Type')[0];
            $rawData = (string)$apiResponse->raw();

            // Determine file extension and file name
            $ext = $this->getExtensionFromContentType($contentType);
            $fileName ??= xlt("fax") . '_' . text($jobId) . $ext;
            $content = $rawData;

            // Create a new document and save it
            $document = new Document();
            $result = $document->createDocument($pid, $catid, $fileName, $contentType, $content);

            return $result ? xlt("Error: Failed to save document. Category Fax") : xlt("Chart Success");
        } catch (ApiException $e) {
            return json_encode(['error' => "Error: Retrieving Fax: " . text($e->getMessage())]);
        } catch (\Throwable $e) {
            return json_encode(['error' => "Error: " . text($e->getMessage())]);
        }
    }

    /**
     * Coerce a mixed value (e.g. an untyped SDK-response field) to a display
     * string, treating non-scalars as empty. Keeps explicit (string) casts off
     * mixed values, which strict PHPStan flags as cast.string.
     */
    private static function asText(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
