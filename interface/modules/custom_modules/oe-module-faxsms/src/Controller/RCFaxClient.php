<?php

/**
 * Fax SMS Module Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS\Controller;

use Document;
use Exception;
use MyMailer;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Utils\FileUtils;
use OpenEMR\Modules\FaxSMS\RCVoice\VoiceFunctionsTrait;
use OpenEMR\Services\ImageUtilities\HandleImageService;
use RingCentral\SDK\Http\ApiException;
use RingCentral\SDK\SDK;

class RCFaxClient extends AppDispatch
{
    use AuthenticateTrait;
    use VoiceFunctionsTrait;

    public $baseDir;
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
    protected CryptoGen $crypto;

    private static $lastAuthAttempt = 0;
    private static $authAttemptCount = 0;
    private const AUTH_RATE_LIMIT = 5; // Max attempts per minute

    public function __construct()
    {
        $this->crypto = new CryptoGen();
        $this->baseDir = $GLOBALS['temporary_files_dir'];
        $this->uriDir = $GLOBALS['OE_SITE_WEBROOT'];
        $this->cacheDir = $GLOBALS['OE_SITE_DIR'] . '/documents/logs_and_misc/_cache';
        $this->credentials = $this->getCredentials();
        $this->portalUrl = $this->credentials['production'] ?? null ? "https://service.ringcentral.com/" : "https://service.devtest.ringcentral.com/";
        $this->serverUrl = $this->credentials['production'] ?? null ? "https://platform.ringcentral.com" : "https://platform.devtest.ringcentral.com";
        $this->redirectUrl = $this->credentials['redirect_url'] ?? null;
        $this->initializeSDK();
        // TODO: initVoice() is not used in this class, move to new voice client.
        //$this->initVoice($this->platform);
        parent::__construct();
    }

    /**
     * Used by fax file drag and drop
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
        $tmpName = $_FILES['fax']['tmp_name'];
        $targetDir = $this->baseDir . '/send';
        if (!file_exists($targetDir) && !mkdir($targetDir, 0777, true)) {
            error_log('Error: Failed to create directory.');
            return '';
        }

        $filepath = $targetDir . "/" . $name;
        if (!move_uploaded_file($tmpName, $filepath)) {
            error_log('Error: Failed to move uploaded file.');
            return '';
        }

        return $filepath;
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
        $message = $message ?: $this->getRequest('comments');

        $smsNumber = $this->formatPhone($this->credentials['smsNumber']);
        $from = $this->formatPhone($from);
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
        } catch (Exception $e) {
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
        $smtpEnabled = !empty($GLOBALS['SMTP_PASS'] ?? null) && !empty($GLOBALS["SMTP_USER"] ?? null);
        $user = $this::getLoggedInUser();
        $facility = substr((string) $user['facility'], 0, 20);
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
            $contentType = $apiResponse->response()->getHeader('Content-Type')[0];
            $rawData = (string)$apiResponse->raw();

            $ext = $this->getExtensionFromContentType($contentType);
            $type = $this->getTypeFromContentType($contentType);
            $filePath = $this->baseDir . "/send/" . ($jobId . $ext);

            if (!file_exists($this->baseDir . '/send')) {
                mkdir($this->baseDir . '/send', 0777, true);
            }
            file_put_contents($filePath, $rawData);

            if ($hasEmail && $smtpEnabled) {
                $statusMsg .= self::emailDocument($email, $this->getRequest('comments'), $filePath, $user) . "<br />";
            }
            if ($faxNumber) {
                try {
                    $this->sendFax(
                        $faxNumber,
                        $filePath,
                        $user['username'],
                        $jobId,
                        $contentType
                    );
                    $statusMsg .= xlt("Successfully forwarded fax to") . ' ' . text($faxNumber) . "<br />";
                } catch (Exception $e) {
                    return js_escape('Error: ' . text($e->getMessage()));
                }
            }
            unlink($filePath);
            return js_escape($statusMsg);
        } catch (ApiException | Exception $e) {
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
        $comments = trim((string) $this->getRequest('comments', $comments));
        $email = $this->getRequest('email');
        $hasEmail = $this->validEmail($email);
        $smtpEnabled = !empty($GLOBALS['SMTP_PASS'] ?? null) && !empty($GLOBALS["SMTP_USER"] ?? null);
        $user = $this::getLoggedInUser();
        $name = $this->getRequest('name', $name) . ' ' . $this->getRequest('surname', '');
        $fileName ??= pathinfo((string) $file, PATHINFO_BASENAME);
        // validate/format file path
        if (is_file($file)) {
            if (str_starts_with((string) $file, 'file://')) {
                $file = substr((string) $file, 7);
            }
            $realPath = realpath($file);
            if ($realPath !== false) {
                $file = str_replace("\\", "/", $realPath);
            } else {
                return xlt('Error: No content');
            }
        }
        // Check if the content is from patient report
        if ($isContent) {
            $content = $file;
            $file = 'report-' . attr($GLOBALS['pid']) . '.pdf';
        } else {
            // Is it from patient documents
            if ($isDocuments) {
                $content = (new Document($docId))->get_data();
            } else {
                // Get the content of the file or the file path
                $content = (is_file($file) && empty($content)) ? file_get_contents($file) : $file;
            }
            if (empty($content)) {
                return xlt('Error: No content to send.');
            }
        }

        // Decrypt content if needed
        if ($this->crypto->cryptCheckStandard($content)) {
            $content = $this->crypto->decryptStandard($content, null, 'database');
        }

        // Email the document if email is provided and SMTP is enabled.
        // TODO: need check to ensure not from forward fax
        $error = false;
        if ($hasEmail && $smtpEnabled) {
            try {
                self::emailDocument($email, $comments, $file, $user);
                $error = false;
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
        } catch (Exception $e) {
            return 'Error: ' . text(js_escape($e->getMessage()));
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

        if (stripos((string) $error, 'invalid_grant') !== false) {
            try {
                $this->platform->login(['jwt' => $this->credentials['jwt']]);
                if ($this->platform->loggedIn()) {
                    $this->cacheAuthData($this->platform);
                    return 'Fax Successfully Sent';
                }
            } catch (Exception $ex) {
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
     * @param string $contentType
     * @return string
     */
    private function getTypeFromContentType(string $contentType): string
    {
        return match ($contentType) {
            'application/pdf', 'image/tiff' => 'Fax',
            'audio/wav', 'audio/x-wav' => 'Audio',
            default => 'Text',
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
            header("Content-Disposition: attachment; filename=" . basename((string) $where));
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
        } catch (Exception $e) {
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

        file_put_contents($filePath, $data);

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
            $decodedContent = base64_decode((string) $content);
            if (file_put_contents($where, $decodedContent) !== false) {
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
     * @param string $filePath
     * @return void
     */
    private function sendFile(string $filePath): void
    {
        ob_end_clean();
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=" . basename($filePath));
        header("Content-Type: application/pdf");
        header("Content-Transfer-Encoding: binary");
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);
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

            // Save the file locally
            $filePath = $this->cacheDir . DIRECTORY_SEPARATOR . $fileName;
            file_put_contents($filePath, $response->raw());

            // Prepare the file for download
            header('Content-Description: File Transfer');
            header('Content-Type: ' . $contentType);
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);

            // Optionally, you can delete the file after download
            unlink($filePath);

            exit; // Stop further script execution
        } catch (ApiException $e) {
            return text(json_encode(['error' => "API Error: " . $e->getMessage()]));
        } catch (Exception $e) {
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
        $normalizedPhone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen((string) $normalizedPhone) === 11 && str_starts_with((string) $normalizedPhone, '1')) {
            $normalizedPhone = substr((string) $normalizedPhone, 1);
        }

        $likePhone = "%" . $normalizedPhone;

        $sql = "
        SELECT CONCAT(fname, ' ', lname) AS fullname
        FROM patient_data
        WHERE REPLACE(REPLACE(REPLACE(REPLACE(phone_cell, '-', ''), '(', ''), ')', ''), ' ', '') LIKE ?
        LIMIT 1
    ";

        $result = sqlQuery($sql, [$likePhone]);
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
        } catch (\Exception $e) {
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
                    $responseMsg .= "<tr><td>" . text(str_replace(["T", "Z"], " ", $value->startTime)) . "</td><td>" . text($value->type) . "</td><td>" . text($value->from->name) . "</td><td>" . text($value->to->name) . "</td><td>" . text($value->action) . "</td><td>" . text($value->result) . "</td><td>" . text($value->message->id) . "</td></tr>";
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
        $dateFrom    = $this->getRequest('datefrom') . 'T00:00:01.000Z';
        $dateTo      = $this->getRequest('dateto')   . 'T23:59:59.000Z';
        $serviceType = strtolower((string) $this->getRequest('type', ''));

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
            $page       = 1;
            do {
                $resp = $this->platform->get(
                    '/restapi/v1.0/account/~/extension/~/message-store',
                    [
                        'messageType' => $messageType,
                        'dateFrom'    => $dateFrom,
                        'dateTo'      => $dateTo,
                        'perPage'     => 100,
                        'page'        => $page
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
        } catch (\Exception $e) {
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
        $responseMsg = [];
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
                    $to = $messageStore->to[0]->name . " " . $messageStore->to[0]->phoneNumber;
                    $from = $messageStore->from->name . " " . $messageStore->from->phoneNumber;
                    $status = $messageStore->messageStatus . $messageStore->from->faxErrorCode;
                    $faxFormattedDate = date('M j, Y g:i:sa T', strtotime((string) $messageStore->creationTime));
                    $updateDate = date('M j Y g:i:sa T', strtotime((string) $messageStore->lastModifiedTime));

                    $links = $this->generateActionLinks($id, $uri);
                    $checkbox = "<input type='checkbox' class='delete-fax-checkbox' value='" . attr($id) . "'>";
                    $type = strtolower((string) $messageStore->type);
                    $direction = strtolower((string) $messageStore->direction);
                    $messageText = '';
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
                        $status = $messageStore->to[0]->faxErrorCode ?: $messageStore->messageStatus;
                        $responseMsg[0] .= "<tr><td>" . text($faxFormattedDate) . "</td><td>" . text($updateDate) . "</td><td>" . text($messageStore->faxPageCount) . "</td><td>" . text($from) . "</td><td>" . text($messageStore->subject) . "</td><td>" . text($status) . "</td><td class='text-left'>" . $links['inbound'] . "</td><td class='text-center'>" . $checkbox . "</td></tr>";
                    } elseif ($direction === "outbound" && $type === $serviceType && $serviceType === "fax") {
                        $status = $messageStore->to[0]->faxErrorCode ?: $messageStore->messageStatus;
                        $responseMsg[1] .= "<tr><td>" . text($faxFormattedDate) . "</td><td>" . text($updateDate) . "</td><td>" . text($messageStore->faxPageCount) .
                            "</td><td>" . text($from) . "</td><td>" . text($to) . "</td><td>" . text($status) . "</td><td>" . $links['outbound'] . "</td><td class='text-center'>" . $checkbox . "</td></tr>";
                    }
                }
            }
        }

        return $responseMsg;
    }

    private function generateActionLinks($id, $uri): array
    {
        $patientLink = "<a role='button' href='javascript:void(0)' onclick=\"createPatient(event, " . attr_js($id) . ", " . attr_js($id) . ", " . attr_js(json_encode([])) . ")\"> <i class='fa fa-chart-simple mr-2' title='" . xla("Chart fax or Create patient and chart fax to documents.") . "'></i></a>";
        $messageLink = "<a role='button' href='javascript:void(0)' onclick=\"notifyUser(event, " . attr_js($id) . ", " . attr_js($id) . ", " . attr_js(0) . ")\"> <i class='fa fa-paper-plane mr-2' title='" . xla("Notify a user and attach this fax to message.") . "'></i></a>";
        $downloadLink = "<a role='button' href='javascript:void(0)' onclick=\"getDocument(event, null, " . attr_js($id) . ", 'true')\"> <i class='fa fa-file-download mr-2' title='" . xla("Download and delete fax") . "'></i></a>";
        $viewLink = "<a role='button' href='javascript:void(0)' onclick=\"getDocument(event, null, " . attr_js($id) . ", 'false')\"> <i class='fa fa-file-pdf mr-2' title='" . xla("View fax document") . "'></i></a>";
        $deleteLink = "<a role='button' href='javascript:void(0)' onclick=\"getDocument(event, null, " . attr_js($id) . ", 'false', 'true')\"> <i class='text-danger fa fa-trash mr-2' title='" . xla("Delete this fax document") . "'></i></a>";
        $forwardLink = "<a role='button' href='javascript:void(0)' onclick=\"forwardFax(event, " . attr_js($id) . ")\"> <i class='fa fa-forward mr-2' title='" . xla("Forward fax to new fax recipient or email attachment.") . "'></i></a>";
        return [
            'inbound' => $patientLink . $messageLink . $forwardLink . $viewLink . $downloadLink . $deleteLink,
            'outbound' => $viewLink . $downloadLink . $deleteLink
        ];
    }

    private function generateSmsActionLinks($id, $uri, $phone): array
    {
        $vtoggle = "<a href='javascript:' onclick=messageShow(" . attr_js($id) . "," . attr_js($uri) . ")><span class='mx-1 fas fa-comment fa-1x'></span></a>";
        $vreply = "<a href='javascript:' onclick=messageReply(" . attr_js($phone) . ")><span class='mx-1 fa fa-reply'></span></a>";

        return [
            'sms' => $vtoggle . $vreply,
            'smsoutbound' => $vreply
        ];
    }

    /**
     * @param $number
     * @return string
     */
    public function formatPhone($number): string
    {
        // this is u.s only. need E-164
        $n = preg_replace('/[^0-9]/', '', (string) $number);
        $n = stripos((string) $n, '1') === 0 ? '+' . $n : '+1' . $n;
        return $n;
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
     * @return mixed
     */
    public function sendEmail(): mixed
    {
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
            return (string)text(count($json->records));
        } catch (Exception $e) {
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
        } catch (Exception $e) {
            return json_encode(['error' => "Error: " . text($e->getMessage())]);
        }
    }

    /**
     * @param       $email
     * @param       $body
     * @param       $file
     * @param array $user
     * @return string
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public static function emailDocument($email, $body, $file, array $user = []): string
    {
        $from_name = ($user['fname'] ?? '') . ' ' . ($user['lname'] ?? '');
        $desc = xlt("Comment") . ":\n" . text($body) . "\n" . xlt("This email has an attached fax document.");
        $mail = new MyMailer();
        $from_name = text($from_name);
        $from = $GLOBALS["practice_return_email_path"];
        $mail->AddReplyTo($from, $from_name);
        $mail->SetFrom($from, $from);
        $mail->AddAddress($email, $email);
        $mail->Subject = xlt("Forwarded Fax Document");
        $mail->Body = $desc;
        $mail->AddAttachment($file);

        return $mail->Send() ? xlt("Email successfully sent.") : xlt("Error: Email failed") . text($mail->ErrorInfo);
    }
}
