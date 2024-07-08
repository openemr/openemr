<?php

/**
 * Fax SMS Module Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS\Controller;

use Exception;
use OpenEMR\Common\Crypto\CryptoGen;
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
    private SDK $sdk;
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
            return $e->getMessage();
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
            return "API Error: " . $e->getMessage() . " - " . $e->getCode();
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
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
        if (empty($this->credentials['username'])) {
            $this->credentials = $this->getCredentials();
            if (empty($this->credentials['username'])) {
                return 2;
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
            return js_escape($authErrorMsg);
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
                return "API Error: " . $e->getMessage() . " - " . $e->getCode();
            }
        }

        return false;
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
     * @return bool|string
     */
    public function sendFax(): bool|string
    {
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return js_escape($authErrorMsg);
        // goes to alert
        }

        $isContent = $this->getRequest('isContent');
        $file = $this->getRequest('file');
        $phone = $this->getRequest('phone');
        $name = $this->getRequest('name') . ' ' . $this->getRequest('surname');
        $isDocuments = $this->getRequest('isDocuments');
        $comments = $this->getRequest('comments');
        $content = '';
        if ($isDocuments === 'true') {
            $file = str_replace("file://", '', $file);
            $file = str_replace("\\", "/", realpath($file));
        }

        if ($isContent) {
            $content = $file;
            $file = 'report-' . $GLOBALS['pid'] . '.pdf';
        } else {
            $content = file_get_contents($file);
            if (!$isDocuments) {
                unlink($file);
            }
        }

        if (empty($content)) {
            return xlt('Error: No content to send.');
        }

        if ($this->crypto->cryptCheckStandard($content)) {
            $content = $this->crypto->decryptStandard($content, null, 'database');
        }

        try {
            $type = $this->getMimeType(basename($file));
            $request = $this->rcsdk->createMultipartBuilder()
                ->setBody([
                    'to' => [['phoneNumber' => $phone, 'name' => $name]],
                    'faxResolution' => 'High',
                    'coverPageText' => $comments
                ])
                ->add($content, $file, ['Content-Type' => $type])
                ->request('/account/~/extension/~/fax');
            $this->platform->sendRequest($request);
            return xlt('Fax Successfully Sent');
        } catch (ApiException $e) {
            return $this->handleApiException($e);
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * @param ApiException $e
     * @return string
     */
    private function handleApiException(ApiException $e): string
    {
        if ($e->response()->error() === 'invalid_grant') {
            try {
                $this->platform->login(['refreshToken' => $this->credentials['refreshToken']]);
                if ($this->platform->loggedIn()) {
                    $this->saveTokens($this->platform->auth()->data());
                    return 'Fax Successfully Sent';
                }
            } catch (Exception $ex) {
                return "Re-authentication Error: " . $ex->getMessage();
            }
        }
        return "API Error: " . $e->getMessage() . " - " . $e->getCode() . "\n" . json_encode($e->response()->json(), JSON_PRETTY_PRINT);
    }

    /**
     * @return string
     */
    public function getStoredDoc(): string
    {
        $docuri = $this->getRequest('docuri');
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return js_escape($authErrorMsg);
        // goes to alert
        }

        try {
            $apiResponse = $this->platform->get($docuri);
        } catch (ApiException $e) {
            return "Error: Retrieving Fax: " . $e->getMessage() . $e->apiResponse()->request()->getUri()->__toString();
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
     * @return string
     */
    public function viewFax(): string
    {
        $docid = $this->getRequest('docid');
        $docuri = $this->getRequest('docuri');
        $isDownload = $this->getRequest('download') === 'true';
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return js_escape($authErrorMsg);
        // goes to alert
        }

        $messageStoreDir = $this->baseDir;
        if (!file_exists($messageStoreDir)) {
            mkdir($messageStoreDir, 0777, true);
        }

        try {
            $apiResponse = $this->platform->get($docuri);
        } catch (ApiException $e) {
            return "Error: Retrieving Fax:\n" . $e->getMessage() . $e->apiResponse()->request()->getUri()->__toString();
        }

        $contentType = $apiResponse->response()->getHeader('Content-Type')[0];
        $rawData = (string)$apiResponse->raw();
        $ext = $this->getExtensionFromContentType($contentType);
        $type = $this->getTypeFromContentType($contentType);
        $fname = "{$messageStoreDir}/{$type}_{$docid}.{$ext}";
        file_put_contents($fname, $rawData);
        if ($isDownload) {
            $this->setSession('where', $fname);
            return $fname;
        }
        return 'data:' . $contentType . ';base64,' . rawurlencode(base64_encode($rawData));
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
            case 'image/tiff':
                return 'tiff';
            case 'audio/wav':
            case 'audio/x-wav':
                return 'wav';
            default:
                return 'txt';
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
     * @param $content
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
     * @param $messageId
     * @return string
     */
    public function check_fax_message_status($messageId): string
    {
        try {
            $endpoint = "/restapi/v1.0/account/~/extension/~/message-store/" . $messageId;
            $resp = $this->platform->get($endpoint);
            $jsonObj = $resp->json();
            $status = "Message status: " . $jsonObj->messageStatus . PHP_EOL;
            if ($jsonObj->messageStatus == "Queued") {
                sleep(10);
                return $this->check_fax_message_status($jsonObj->id);
            }
            return $status;
        } catch (ApiException $e) {
            return "Message: " . $e->getMessage();
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
            $responseMsgs = '';
            while ($nrow = sqlFetchArray($res)) {
                $adate = ($nrow['pc_eventDate'] . '::' . $nrow['pc_startTime']);
                $pinfo = str_replace("|||", " ", $nrow['patient_info']);
                $msg = htmlspecialchars($nrow["message"], ENT_QUOTES);
                $responseMsgs .= "<tr><td>" . $nrow["pc_eid"] . "</td><td>" . $nrow["dSentDateTime"] . "</td><td>" . $adate . "</td><td>" . $pinfo . "</td><td>" . $msg . "</td></tr>";
            }
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage() . PHP_EOL;
        }

        return $responseMsgs;
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
            return js_escape($authErrorMsg);
        // goes to alert
        }

        try {
            $pageCount = 1;
            $recordCountPerPage = 100;
            $timePerCallLogRequest = 10;
            $flag = true;
            $timeFrom = '00:00:00.000Z';
            $timeTo = '23:59:59.000Z';
            $responseMsgs = "";
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
                    $responseMsgs .= "<tr><td>" . str_replace(["T", "Z"], " ", $value->startTime) . "</td><td>" . $value->type . "</td><td>" . $value->from->name . "</td><td>" . $value->to->name . "</td><td>" . $value->action . "</td><td>" . $value->result . "</td><td>" . $value->message->id . "</td></tr>";
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
            return xlt('HTTP Error') . ': ' . $e->getMessage() . PHP_EOL;
        }

        return $responseMsgs;
    }

    /**
     * @return string
     */
    public function getPending(): string
    {
        $dateFrom = $this->getRequest('datefrom');
        $dateTo = $this->getRequest('dateto');
        $authErrorMsg = $this->authenticate();
        if ($authErrorMsg !== 1) {
            return json_encode(['error' => js_escape($authErrorMsg)]);
        }

        try {
            $timeFrom = 'T00:00:01.000Z';
            $timeTo = 'T23:59:59.000Z';
            $dateFrom = trim($dateFrom) . $timeFrom;
            $dateTo = trim($dateTo) . $timeTo;
            $messageStoreList = $this->platform->get('/account/~/extension/~/message-store', [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo
            ])->json()->records;
            $responseMsg = [
                0 => '',
                1 => '',
                2 => ''
            ];
            foreach ($messageStoreList as $messageStore) {
                if (property_exists($messageStore, 'attachments')) {
                    foreach ($messageStore->attachments as $attachment) {
                            $this->formatMessageStore($responseMsg, $messageStore, $attachment);
                    }
                }
            }
        } catch (ApiException $e) {
            return json_encode(['error' => "Ensure account credentials are correct."]);
        }

        return json_encode($responseMsg);
    }

    private function formatMessageStore(array &$responseMsg, $messageStore, $attachment): void
    {
        $id = $attachment->id;
        $uri = $attachment->uri;
        $to = $messageStore->to[0]->name . " " . $messageStore->to[0]->phoneNumber;
        $from = $messageStore->from->name . " " . $messageStore->from->phoneNumber;
        $errors = $messageStore->to[0]->faxErrorCode ? "why: " . $messageStore->to[0]->faxErrorCode : $messageStore->from->faxErrorCode;
        $status = $messageStore->messageStatus . " " . $errors;
        $aUrl = "<a href='#' onclick=getDocument(event,'" . attr_js($uri) . "'," . attr_js($id) . ",'true')>" . text($id) . " <span class='fa fa-download'></span></a></br>";
        $vUrl = "<a href='#' onclick=getDocument(event,'" . attr_js($uri) . "'," . attr_js($id) . ",'false')> <span class='fa fa-file-pdf-o'></span></a></br>";
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
            return js_escape($authErrorMsg);
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
                            $responseMsgs .= "<tr><td>Errors: " . $e->getMessage() . $e->apiResponse()->request()->getUri()->__toString() . "</td></tr>";
                            continue;
                        }

                        $ext = $this->getExtensionFromContentType($apiResponse->response()->getHeader('Content-Type')[0]);
                        $type = $this->getTypeFromContentType($apiResponse->response()->getHeader('Content-Type')[0]);
                        $start = microtime(true);
                        file_put_contents("${messageStoreDir}/${type}_${id}.${ext}", $apiResponse->raw());
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
            echo "<tr><td>Error: " . $e->getMessage() . $e->apiResponse()->request()->getUri()->__toString() . "</td></tr>";
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
        return '0';
    }
}
