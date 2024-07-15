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
use OpenEMR\Services\ImageUtilities\HandleImageService;
use RingCentral\SDK\Http\ApiException;
use RingCentral\SDK\SDK;

class RCFaxClient extends AppDispatch
{
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
    protected $crypto;

    public function __construct()
    {
        $this->crypto = new CryptoGen();
        $this->baseDir = $GLOBALS['temporary_files_dir'];
        $this->uriDir = $GLOBALS['OE_SITE_WEBROOT'];
        $this->cacheDir = $GLOBALS['OE_SITE_DIR'] . '/documents/logs_and_misc/_cache';
        $this->credentials = $this->getCredentials();
        $this->portalUrl = $this->credentials['production'] ? "https://service.ringcentral.com/" : "https://service.devtest.ringcentral.com/";
        $this->serverUrl = $this->credentials['production'] ? "https://platform.ringcentral.com" : "https://platform.devtest.ringcentral.com";
        $this->redirectUrl = $this->credentials['redirect_url'];
        $this->initializeSDK();
        parent::__construct();
    }

    /**
     * @return int|string
     */
    public function authenticateRingCentral(): int|string
    {
        try {
            $authback = $this->cacheDir . DIRECTORY_SEPARATOR . 'platform.json';
            $cachedAuth = $this->getCachedAuth($authback);
            if (!empty($cachedAuth['refresh_token'])) {
                $this->platform->auth()->setData($cachedAuth);
            }

            if ($this->platform->loggedIn()) {
                return $this->refreshToken();
            } else {
                return $this->loginWithJWT();
            }
        } catch (Exception $e) {
            return text($e->getMessage());
        }
    }

    /**
     * @param string $authback
     * @return array
     */
    private function getCachedAuth(string $authback): array
    {
        if (file_exists($authback)) {
            $cachedAuth = file_get_contents($authback);
            $cachedAuth = json_decode($this->crypto->decryptStandard($cachedAuth), true);
            unlink($authback);
// Remove cached file after reading
            return $cachedAuth;
        }
        return [];
    }

    /**
     * @return int|string
     */
    private function refreshToken(): int|string
    {
        try {
            $this->platform->refresh();
        } catch (Exception $e) {
            return $this->loginWithJWT();
        }
        $this->setSession('sessionAccessToken', $this->platform->auth()->data());
        $this->cacheAuthData($this->platform);
        return 1;
    }

    /**
     * @return int|string
     */
    private function loginWithJWT(): int|string
    {
        $jwt = trim($this->credentials['jwt'] ?? '');
        try {
            $this->platform->login(['jwt' => $jwt]);
            if ($this->platform->loggedIn()) {
                $this->setSession('sessionAccessToken', $this->platform->auth()->data());
                $this->cacheAuthData($this->platform);
                return 1;
            }
        } catch (ApiException $e) {
            return "API Error: " . text($e->getMessage()) . " - " . text($e->getCode());
        } catch (Exception $e) {
            return "Error: " . text($e->getMessage());
        }
        return "Login with JWT failed.";
    }

    /**
     * @param $platform
     * @return void
     */
    private function cacheAuthData($platform): void
    {
        $data = $platform->auth()->data();
        $encryptedData = $this->crypto->encryptStandard(json_encode($data));
        file_put_contents($this->cacheDir . DIRECTORY_SEPARATOR . 'platform.json', $encryptedData);
    }

    /**
     * @return void
     * @throws Exception
     */
    private function initializeSDK(): void
    {
        if (isset($this->credentials['appKey'], $this->credentials['appSecret'])) {
            $this->rcsdk = new SDK($this->credentials['appKey'], $this->credentials['appSecret'], $this->serverUrl, 'OpenEMR', '1.0.0');
            $this->platform = $this->rcsdk->platform();
        } else {
            throw new Exception("App Key and App Secret are required to initialize SDK.");
        }
    }

    /**
     * @return int|string
     */
    public function authenticate(): int|string
    {
        if (empty($this->credentials['appKey'])) {
            $this->credentials = $this->getCredentials();
            if (empty($this->credentials['appKey'])) {
                return 'Missing or Invalid RingCentral Credentials. Please contact your administrator.';
                // No credentials set
            }
        }
        $error = $this->authenticateRingCentral();
        if (is_numeric($error)) {
            return $error;
        }
        return $error;
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

        $name = basename($_FILES['fax']['name']);
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
    public function sendSMS(string $toPhone = '', string $subject = '', string $message = '', string $from = ''): string|bool
    {
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return text(js_escape($authErrorMsg));
            // goes to alert
        }

        $smsNumber = $this->credentials['smsNumber'];
        if ($smsNumber) {
            try {
                $this->platform->post('/account/~/extension/~/sms', [
                    'from' => ['phoneNumber' => $smsNumber],
                    'to' => [['phoneNumber' => $toPhone]],
                    'text' => $message,
                ]);
                sleep(1);
                // RC may only allow 1/second.
                return true;
            } catch (ApiException $e) {
                return text("API Error: " . $e->getMessage() . " - " . $e->getCode());
            }
        }

        return true;
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
        $facility = substr($user['facility'], 0, 20);
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
        $comments = trim($this->getRequest('comments', $comments));
        $email = $this->getRequest('email');
        $hasEmail = $this->validEmail($email);
        $smtpEnabled = !empty($GLOBALS['SMTP_PASS'] ?? null) && !empty($GLOBALS["SMTP_USER"] ?? null);
        $user = $this::getLoggedInUser();
        $name = $this->getRequest('name', $name) . ' ' . $this->getRequest('surname', '');
        $fileName = $fileName ?? pathinfo($file, PATHINFO_BASENAME);
        // validate/format file path
        if (is_file($file)) {
            if (str_starts_with($file, 'file://')) {
                $file = substr($file, 7);
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
            } catch (\PHPMailer\PHPMailer\Exception $e) {
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

        if (stripos($error, 'invalid_grant') !== false) {
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
        switch ($contentType) {
            case 'application/pdf':
                return 'pdf';
            case 'text/plain':
                return 'txt';
            case 'image/tiff':
                return 'tiff';
            case 'image/jpeg':
                return 'jpeg';
            case 'image/jpg':
                return 'jpg';
            case 'image/gif':
                return 'gif';
            case 'image/png':
                return 'png';
            case 'application/xml':
                return 'xml';
            case 'audio/wav':
            case 'audio/x-wav':
                return 'wav';
            default:
                return 'application/pdf';
        }
    }

    /**
     * @param string $contentType
     * @return string
     */
    private function getTypeFromContentType(string $contentType): string
    {
        switch ($contentType) {
            case 'application/pdf':
            case 'image/tiff':
                return 'Fax';
            case 'audio/wav':
            case 'audio/x-wav':
                return 'Audio';
            default:
                return 'Text';
        }
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
            header("Content-Disposition: attachment; filename=" . basename($where));
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
        switch ($contentType) {
            case 'application/pdf':
                return 'data:application/pdf;base64,' . base64_encode($data);
            case 'image/tiff':
            case 'image/tif':
                return 'data:image/tiff;base64,' . base64_encode($data);
            default:
                return 'data:text/plain;base64,' . base64_encode($data);
        }
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
        switch ($contentType) {
            case 'application/pdf':
                return 'pdf';
            case 'image/tiff':
            case 'image/tif':
                return 'tiff';
            default:
                return 'txt';
        }
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
            $decodedContent = base64_decode($content);
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
     * @return false|string
     */
    public function getPending(): false|string
    {
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return json_encode(['error' => js_escape($authErrorMsg)]);
        }

        $dateFrom = $this->getRequest('datefrom');
        $dateTo = $this->getRequest('dateto');
        $serviceType = $this->getRequest('type', '');

        try {
            $messageStoreDir = $this->baseDir;

            if (!file_exists($messageStoreDir) && !mkdir($messageStoreDir, 0777, true) && !is_dir($messageStoreDir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $messageStoreDir));
            }

            $dateFrom .= 'T00:00:01.000Z';
            $dateTo .= 'T23:59:59.000Z';

            $messageStoreList = $this->platform->get('/account/~/extension/~/message-store', [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
            ])->json()->records;

            $responseMsg = $this->processMessageStoreList($messageStoreList, $serviceType);
        } catch (ApiException $e) {
            $responseMsg = "<tr><td>" . text($e->getMessage()) . " : " . xlt('Ensure account credentials are correct.') . "</td></tr>";
            return json_encode(['error' => $responseMsg]);
        }

        return json_encode($responseMsg ?: [xlt("Nothing to report"), xlt("Nothing to report"), xlt("Nothing to report")]);
    }

    private function processMessageStoreList($messageStoreList, $serviceType): array
    {
        $responseMsg = [];
        foreach ($messageStoreList as $messageStore) {
            if (property_exists($messageStore, 'attachments')) {
                foreach ($messageStore->attachments as $attachment) {
                    $id = attr($attachment->id);
                    $uri = $attachment->uri;
                    $to = $messageStore->to[0]->name . " " . $messageStore->to[0]->phoneNumber;
                    $from = $messageStore->from->name . " " . $messageStore->from->phoneNumber;
                    $status = $messageStore->messageStatus . $messageStore->from->faxErrorCode;
                    $faxFormattedDate = date('M j, Y g:i:sa T', strtotime($messageStore->creationTime));
                    $updateDate = date('M j Y g:i:sa T', strtotime($messageStore->lastModifiedTime));

                    $links = $this->generateActionLinks($id, $uri);
                    $checkbox = "<input type='checkbox' class='delete-fax-checkbox' value='" . attr($id) . "'>";
                    $type = strtolower($messageStore->type);
                    $direction = strtolower($messageStore->direction);
                    $readStatus = $messageStore->readStatus;
                    if ($type === "sms") {
                        $messageText = $this->getMessageContent($uri);
                        $responseMsg[2] .= "<tr><td>" . text($faxFormattedDate) . "</td><td>" . text($messageStore->type) . "</td><td>" . text($from) . "</td><td>" . text($to) . "</td><td>" . text($status) . "</td><td><span class='$id'>" . text(substr($messageText, 0, 30)) . "</span><div class='d-none $id'>" . text($messageText) . "</div></td><td class='btn-group'>" . attr($links['sms']) . "</td><td class='text-center'>" . $checkbox . "</td></tr>";
                    } elseif ($direction === "inbound" && $type === $serviceType) {
                        $status = $messageStore->to[0]->faxErrorCode ?: $messageStore->messageStatus;
                        $responseMsg[0] .= "<tr><td>" . text($faxFormattedDate) . "</td><td>" . text($updateDate) . "</td><td>" . text($messageStore->faxPageCount) . "</td><td>" . text($from) . "</td><td>" . text($messageStore->subject) . "</td><td>" . text($status) . "</td><td class='text-left'>" . $links['inbound'] . "</td><td class='text-center'>" . $checkbox . "</td></tr>";
                    } elseif ($direction === "outbound" && $type === $serviceType) {
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

        $vtoggle = "<a href='javascript:' onclick=messageShow(" . attr_js($id) . ")><span class='mx-1 fa fa-eye-slash fa-1x'></span></a>";
        $vreply = "<a href='javascript:' onclick=messageReply(" . attr_js($id) . ")><span class='mx-1 fa fa-reply'></span></a>";

        return [
            'sms' => $vtoggle . $vreply,
            'inbound' => $patientLink . $messageLink . $forwardLink . $viewLink . $downloadLink . $deleteLink,
            'outbound' => $viewLink . $downloadLink . $deleteLink
        ];
    }

    /**
     * @param array $responseMsg
     * @param       $messageStore
     * @param       $attachment
     * @return void
     */
    private function formatMessageStore(array &$responseMsg, $messageStore, $attachment): void
    {
        $id = $attachment->id;
        $uri = $attachment->uri;
        $to = $messageStore->to[0]->name . " " . $messageStore->to[0]->phoneNumber;
        $from = $messageStore->from->name . " " . $messageStore->from->phoneNumber;
        $errors = $messageStore->to[0]->faxErrorCode ? "why: " . $messageStore->to[0]->faxErrorCode : $messageStore->from->faxErrorCode;
        $status = $messageStore->messageStatus . " " . $errors;
        $patientLink = "<a role='button' href='javascript:void(0)' onclick=\"createPatient(event, " . attr_js($id) . ", " . attr_js($id) . ", " . attr_js(json_encode($parse ?? [])) . ")\"> <i class='fa fa-chart-simple mr-2' title='" . xla("Chart fax or Create patient and chart fax to documents.") . "'></i></a>";
        $messageLink = "<a role='button' href='javascript:void(0)' onclick=\"notifyUser(event, " . attr_js($id) . ", " . attr_js($id) . ", " . attr_js(($pid_assumed ?? 0)) . ")\"> <i class='fa fa-paper-plane mr-2' title='" . xla("Notify a user and attach this fax to message.") . "'></i></a>";
        $downloadLink = "<a role='button' href='javascript:void(0)' onclick=\"getDocument(event, null, " . attr_js($id) . ", 'true')\"> <i class='fa fa-file-download mr-2' title='" . xla("Download and delete fax") . "'></i></a>";
        $viewLink = "<a role='button' href='javascript:void(0)' onclick=\"getDocument(event, null, " . attr_js($id) . ", 'false')\"> <i class='fa fa-file-pdf mr-2' title='" . xla("View fax document") . "'></i></a>";
        $deleteLink = "<a role='button' href='javascript:void(0)' onclick=\"getDocument(event, null, " . attr_js($id) . ", 'false', 'true')\"> <i class='text-danger fa fa-trash mr-2' title='" . xla("Delete this fax document") . "'></i></a>";
        $forwardLink = "<a role='button' href='javascript:void(0)' onclick=\"forwardFax(event, " . attr_js($id) . ")\"> <i class='fa fa-forward mr-2' title='" . xla("Forward fax to new fax recipient or email attachment.") . "'></i></a>";

        $faxFormattedDate = date('M j, Y g:i:sa T', strtotime($messageStore->lastModifiedTime));
        $docLen = text(round(1024 / 1024, 2)) . "KB"; // todo add length
        $responseMsg[0] .= "<tr><td>" . text($faxFormattedDate) . "</td><td>" . text($from) . "</td><td>" . text('todo: add caller id') . "</td><td>" . text($to) . "</td><td>" . text($messageStore->PagesReceived) . "</td><td>" . text($docLen) . "</td><td class='text-left'>" . /*$detailLink .*/
            "</td><td class='text-center'>" . text($pid_assumed ?? '') . "</td><td class='text-left'>" . $patientLink . $messageLink . $forwardLink . $viewLink . $downloadLink . $deleteLink . "</td></tr>";
        //$responseMsg[0] .= $form;
        $aUrl = "<a href='#' onclick=getDocument(event," . attr_js($uri) . "," . attr_js($id) . ",'true')>" . text($id) . " <span class='fa fa-download'></span></a></br>";
        $vUrl = "<a href='#' onclick=getDocument(event," . attr_js($uri) . "," . attr_js($id) . ",'false')> <span class='fa fa-file-pdf-o'></span></a></br>";
        if ($status != 'failed' && $this->formatPhone($this->credentials['smsNumber']) != $messageStore->from) {
            $vreply = "<a href='javaScript:' onclick=messageReply(" . attr_js($messageStore->from) . ")><span class='mx-1 fa fa-reply'></span></a>";
        } else {
            $vreply = "<a href='#' title='SMS failure'> <span class='fa fa-file-pdf text-danger'></span></a></br>";
        }
        $row = "<tr>
                <td>" . text(str_replace(["T", "Z"], " ", $messageStore->lastModifiedTime)) . "</td>
                <td>" . text($messageStore->type) . "</td>
                <td>" . text($from) . "</td>
                <td>" . text($to) . "</td>
                <td>" . text($status) . "</td>
                <td>" . ($aUrl) . "</td>
                <td>" . ($vUrl) . "</td>";
        if (strtolower($messageStore->type) === "sms") {
            $row .= "<td>" . ($vreply) . "</td>";
        }

        $row .= "</tr>";
        if (strtolower($messageStore->type) === "sms") {
            $responseMsg[2] .= $row;
            // sms
        } elseif (strtolower($messageStore->direction) === "inbound") {
            $responseMsg[0] .= $row;
// in fax
        } else {
            $responseMsg[1] .= $row;
// out fax
        }
    }

    /**
     * @param $number
     * @return string
     */
    public function formatPhone($number): string
    {
        // this is u.s only. need E-164
        $n = preg_replace('/[^0-9]/', '', $number);
        if (stripos($n, '1') === 0) {
            $n = '+' . $n;
        } else {
            $n = '+1' . $n;
        }
        return $n;
    }

    /**
     * @return string|void
     */
    public function getMessage()
    {
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return text(js_escape($authErrorMsg));
            // goes to alert
        }

        try {
            $messageStoreDir = $this->baseDir;
            if (!file_exists($messageStoreDir)) {
                mkdir($messageStoreDir, 0777, true);
            }

            $messageStoreList = $this->platform->get('/account/~/extension/~/message-store', [
                'messageType' => "",
                'dateFrom' => '2018-05-01'
            ])->json()->records;
            $timePerMessageStore = 6;
            $responseMsgs = "";
            foreach ($messageStoreList as $messageStore) {
                if (property_exists($messageStore, 'attachments')) {
                    foreach ($messageStore->attachments as $attachment) {
                        $id = $attachment->id;
                        $uri = $attachment->uri;
                        try {
                            $apiResponse = $this->platform->get($uri);
                        } catch (ApiException $e) {
                            $responseMsgs .= "<tr><td>Errors: " . text($e->getMessage()) . $e->apiResponse()->request()->getUri()->__toString() . "</td></tr>";
                            continue;
                        }

                        $ext = $this->getExtensionFromContentType($apiResponse->response()->getHeader('Content-Type')[0]);
                        $type = $this->getTypeFromContentType($apiResponse->response()->getHeader('Content-Type')[0]);
                        $start = microtime(true);
                        file_put_contents("{$messageStoreDir}/{$type}_{$id}.{$ext}", $apiResponse->raw());
                        $responseMsgs .= "<tr><td>" . $messageStore->creationTime . "</td><td>" . $messageStore->type . "</td><td>" . $messageStore->from->name . "</td><td>" . $messageStore->to->name . "</td><td>" . $messageStore->availability . "</td><td>" . $messageStore->messageStatus . "</td><td>" . $messageStore->message->id . "</td></tr>";
                        $end = microtime(true);
                        $time = ($end - $start);
                        if ($time < $timePerMessageStore) {
                            sleep($timePerMessageStore - $time);
                        }
                    }
                } else {
                    echo xlt("Does not have messages") . PHP_EOL;
                }
            }
        } catch (ApiException $e) {
            echo "<tr><td>Error: " . text($e->getMessage() . $e->apiResponse()->request()->getUri()->__toString()) . "</td></tr>";
        }

        exit;
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
            return (string) text(count($json->records));
        } catch (Exception $e) {
            error_log('Error fetching incoming faxes: ' . text($e->getMessage()));
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
            $fileName = $fileName ?? xlt("fax") . '_' . text($jobId) . $ext;
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
