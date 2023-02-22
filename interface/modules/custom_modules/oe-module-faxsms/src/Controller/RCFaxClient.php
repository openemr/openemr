<?php

/**
 * Fax SMS Module Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS\Controller;

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

    public function __construct()
    {
        $this->crypto = new CryptoGen();
        $this->baseDir = $GLOBALS['temporary_files_dir'];
        $this->uriDir = $GLOBALS['OE_SITE_WEBROOT'];
        $this->cacheDir = $GLOBALS['OE_SITE_DIR'] . '/documents/logs_and_misc/_cache';
        $this->credentials = $this->getCredentials();
        $this->portalUrl = !$this->credentials['production'] ? "https://service.devtest.ringcentral.com/" : "https://service.ringcentral.com/";
        $this->serverUrl = !$this->credentials['production'] ? "https://platform.devtest.ringcentral.com" : "https://platform.ringcentral.com";
        $this->redirectUrl = $this->credentials['redirect_url'];
        parent::__construct();
    }

    /**
     * @return array|mixed
     */
    public function getCredentials(): mixed
    {
        // is this new user or credentials aren't setup?
        if (!file_exists($this->cacheDir)) {
            // must have this path for platform persist. @todo Move to table!
            if (!mkdir($concurrentDirectory = $this->cacheDir, 0777, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }

        $credentials = appDispatch::getSetup();
        return $credentials;
    }

    /**
     * @return string
     */
    public function faxProcessUploads(): string
    {
        if (!empty($_FILES)) {
            $name = $_FILES['fax']['name'];
            $tmp_name = $_FILES['fax']['tmp_name'];
        }
        // I'm not interested in managing fax files. Fax's are
        // maintained on RC's servers so, not keeping uploads.
        ['basename' => $basename, 'dirname' => $dirname] = pathinfo($tmp_name);
        $filepath = $dirname . DIRECTORY_SEPARATOR . $name;
        move_uploaded_file($tmp_name, $filepath);

        return $filepath;
    }

    /**
     * @return bool
     */
    public function processTokenCode(): bool
    {
        $code = $this->getRequest('code');
        if (!isset($code)) {
            return false;
        }
        // Create SDK instance
        $rcsdk = new SDK($this->credentials['appKey'], $this->credentials['appSecret'], $this->serverUrl, 'OpenEMR', '1.0.0');
        $platform = $rcsdk->platform();

        $qs = $platform->parseAuthRedirectUrl($_SERVER['QUERY_STRING']);
        $qs["redirectUri"] = $this->redirectUrl;

        $apiResponse = $platform->login($qs);
        $this->setSession('sessionAccessToken', $apiResponse->text());

        $file = $this->cacheDir . DIRECTORY_SEPARATOR . 'platform.json';
        // Save authentication data

        $content = $this->crypto->encryptStandard(json_encode($platform->auth()->data(), JSON_PRETTY_PRINT));
        file_put_contents($file, $content);

        return true;
    }

    /**
     * @param $tophone
     * @param $subject
     * @param $message
     * @param $from
     * @return string|bool
     */
    public function sendSMS($tophone = null, $subject = null, $message = null, $from = null): string|bool
    {
        if ($this->authenticate() !== 1) {
            $e = xlt('Error: Authentication Service Denies Access. Not logged in.');
            if ($this->authenticate() === 2) {
                $e = xlt('Error: Application account credentials is not setup. Setup in Actions->Account Credentials.');
            }
            $ee = array('error' => $e);
            return json_encode($ee);
        }
        $tophone = $tophone ?: $this->getRequest('phone');
        $message = $message ?: $this->getRequest('comments');
        $smsNumber = $from ?: $this->credentials['smsNumber'];
        if ($smsNumber) {
            try {
                $response = $this->platform
                    ->post('/account/~/extension/~/sms', array(
                        'from' => array('phoneNumber' => attr($smsNumber)),
                        'to' => array(
                            array('phoneNumber' => attr($tophone)),
                        ),
                        'text' => text($message),
                    ));
            } catch (ApiException $e) {
                $message = $e->getMessage();
                return 'Error: ' . $message . PHP_EOL;
            }
        } else {
            return xlt("Error: Missing From Phone");
        }

        return xlt("Message Sent");
    }

    /**
     * @return mixed
     */
    public function getLogIn()
    {
        $request_url = $this->platform->authUrl(['redirectUri' => $this->redirectUrl, 'state' => 'login']);
        $this->setSession('url', $request_url);
        $this->setSession('redirect_uri', $this->redirectUrl);
        require('rcauth.php');
        return $request_url;
    }

    /**
     * @param $action_flg
     * @return int
     */
    public function authenticate($action_flg = ''): int
    {
        // did construct happen or setup...
        if (empty($this->credentials['username'])) {
            $this->credentials = $this->getCredentials();
            if (empty($this->credentials['username'])) {
                return 2;
            }
        }
        $this->rcsdk = new SDK($this->credentials['appKey'], $this->credentials['appSecret'], $this->serverUrl, 'OpenEMR', '1.0.0');
        $this->platform = $this->rcsdk->platform();
        $authback = $this->cacheDir . DIRECTORY_SEPARATOR . 'platform.json';
        $cachedAuth = array();
        // May use stored auth tokens that may not have expired
        // from last api use. Refresh tokens life is 7 days.
        // Access tokens are 1 hour. sic.. give me a break.
        if (file_exists($authback)) {
            // stored tokens may be expired but, will try em anyway.
            // anything but a ridiculous log in!
            $cachedAuth = file_get_contents($authback);
            $cachedAuth = json_decode($this->crypto->decryptStandard($cachedAuth), true);
            // delete will update with current auth.
            unlink($authback);
        }

        $logged_in = 0;
        // set token data(for eventual request)i.e an action request
        // sendFax, will do an auth check first using this
        // token data array. Session storage token is very
        // unreliable and hardly ever accepted. Still, we try...
        $session_token = $_SESSION['sessionAccessToken'];
        if (!empty($cachedAuth["refresh_token"])) {
            // probably new openemr session or user!
            $this->platform->auth()->setData($cachedAuth);
        } elseif (isset($session_token)) {
            $this->platform->auth()->setData((array)json_decode($session_token));
        }
        // verified logged status
        if ($this->platform->loggedIn()) {
            $logged_in = 1;
            try {
                $this->platform->refresh();
            } catch (\Exception $e) {
                // Give up! Clear old auth stuff afterwards a user
                // most likely will get a OAuth login dialog
                // on return, one would hope!
                unset($_SESSION['sessionAccessToken']);
                $logged_in = 0;
                return $logged_in;
            }
        } else {
            // This means we're not logged in so will have to prompt user.
            // Using the authUrl to set up a OAuth log in.
            // OAuth redirects to rcauth.php(default) or
            // redirect url from setup. Look there.
            // Init RC's OAuth dialog. Three legs.
            // if auth check from UI then login dialog will be presented on return.
            // if fail on API call the UI login button will enable.
            return 0;
        }
        // Save updated authentication data
        $this->setSession('sessionAccessToken', $this->platform->auth()->data());
        $content = json_encode($this->platform->auth()->data(), JSON_PRETTY_PRINT);
        $content = $this->crypto->encryptStandard($content);
        file_put_contents($authback, $content);

        return $logged_in;
    }

    /**
     * @return false|string
     */
    public function getStoredDoc(): bool|string
    {
        $docuri = $this->getRequest('docuri');
        $uri = $docuri;
        if ($this->authenticate() !== 1) {
            $e = xlt('Error: Authentication Service Denies Access. Not logged in.');
            if ($this->authenticate() === 2) {
                $e = xlt('Error: Application account credentials is not setup. Setup in Actions->Account Credentials.');
            }
            $ee = array('error' => $e);
            return json_encode($ee);
        }
        try {
            $apiResponse = $this->platform->get($uri);
        } catch (ApiException $e) {
            $message = $e->getMessage() . $e->apiResponse()->request()->getUri()->__toString();
            $r = "Error: Retrieving Fax:" . $message;
            return $r;
        }
        if ($apiResponse->response()->getHeader('Content-Type')[0] == 'application/pdf') {
            $doc = 'data:application/pdf;base64, ' . rawurlencode(base64_encode((string)$apiResponse->raw()));
        } elseif ($apiResponse->response()->getHeader('Content-Type')[0] == 'image/tiff') {
            $doc = 'data:image/tiff;base64, ' . rawurlencode(base64_encode((string)$apiResponse->raw()));
        } else {
            $doc = (string)$apiResponse->raw();
        }
        $r = !empty($apiResponse->raw()) ? $apiResponse->raw() : "error";

        return !empty($doc) ? $doc : $r;
    }

    /**
     * @return string
     */
    public function viewFax(): string
    {
        $docid = $this->getRequest('docid');
        $docuri = $this->getRequest('docuri');
        $doc = '';
        $isDownload = $this->getRequest('download');
        $uri = $docuri;
        $isDownload = $isDownload == 'true' ? 1 : 0;

        if ($this->authenticate() !== 1) {
            $e = xlt('Error: Authentication Service Denies Access. Not logged in.');
            if ($this->authenticate() === 2) {
                $e = xlt('Error: Application account credentials is not setup. Setup in Actions->Account Credentials.');
            }
            $ee = array('error' => $e);
            return json_encode($ee);
        }

        $messageStoreDir = $this->baseDir;

        if (!file_exists($messageStoreDir) && !mkdir($messageStoreDir, 0777, true) && !is_dir($messageStoreDir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $messageStoreDir));
        }

        try {
            $apiResponse = $this->platform->get($uri);
        } catch (ApiException $e) {
            $message = $e->getMessage() . $e->apiResponse()->request()->getUri()->__toString();
            $r = "Error: Retrieving Fax:\n" . $message;
            return $r;
        }
        $c_header = $apiResponse->response()->getHeader('Content-Type');
        if ($c_header[0] == 'application/pdf') {
            $ext = 'pdf';
            $type = 'Fax';
            $doc = 'data:application/pdf;base64, ' . rawurlencode(base64_encode((string)$apiResponse->raw()));
        } elseif ($c_header[0] == 'image/tiff') {
            $ext = 'tiff';
            $type = 'Fax';
            $doc = 'data:image/tiff;base64, ' . rawurlencode(base64_encode((string)$apiResponse->raw()));
        } elseif ($c_header[0] == 'audio/wav' || $c_header[0] == 'audio/x-wav') {
            $ext = 'wav';
            $type = 'Audio';
        } else {
            $ext = 'txt';
            $type = 'Text';
            $doc = "data:text/plain, " . text((string)$apiResponse->raw());
        }

        $fname = "${messageStoreDir}/${type}_${docid}.${ext}";
        file_put_contents($fname, $apiResponse->raw());
        if ($isDownload) {
            $this->setSession('where', $fname);
            return $fname;
        }

        return $doc;
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
     * @return string|bool
     */
    public function sendFax(): string|bool
    {
        if ($this->authenticate() !== 1) {
            $e = xlt('Error: Authentication Service Denies Access. Not logged in.');
            if ($this->authenticate() === 2) {
                $e = xlt('Error: Application account credentials is not setup. Setup in Actions->Account Credentials.');
            }
            $ee = array('error' => $e);
            return json_encode($ee);
        }
        $isContent = $this->getRequest('isContent');
        $file = $this->getRequest('file');
        $mime = $this->getRequest('mime');
        $phone = $this->getRequest('phone');
        $name = $this->getRequest('name');
        $lastname = $this->getRequest('surname');
        $isDocuments = $this->getRequest('isDocuments');
        $comments = $this->getRequest('comments');
        $name .= ' ' . $lastname;
        $content = '';

        if ($isDocuments == 'true') {
            $file = str_replace("file://", '', $file);
            $file = str_replace("\\", "/", realpath($file)); // normalize requested path
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
        // is it decrypted
        if ($this->crypto->cryptCheckStandard($content)) {
            $content = $this->crypto->decryptStandard($content, null, 'database');
        }
        try {
            // do the request
            $type = \GuzzleHttp\Psr7\MimeType::fromFilename(basename($file));
            if (empty($type)) {
                $type = $mime;
            }
            $request = $this->rcsdk->createMultipartBuilder()
                ->setBody(array(
                    'to' => array(
                        array(
                            'phoneNumber' => $phone,
                            'name' => $name)
                    ),
                    'faxResolution' => 'High',
                    'coverPageText' => text($comments)
                ))
                ->add($content, $file, array('Content-Type' => (string)$type))
                ->request('/account/~/extension/~/fax');

            $response = $this->platform->sendRequest($request);
        } catch (ApiException $e) {
            $message = $e->getMessage();
            return text('Error: ' . $message) . PHP_EOL;
        }

        return xlt('Fax Successfully Sent');
    }

    /**
     * @return bool|string
     */
    public function getUser(): bool|string
    {
        $id = $this->getRequest('uid');
        $query = "SELECT * FROM users WHERE id = ?";
        $result = sqlStatement($query, array($id));
        $u = array();
        foreach ($result as $row) {
            $u[] = $row;
        }
        $u = $u[0];
        $r = array($u['fname'], $u['lname'], $u['fax']);

        return json_encode($r);
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
            $res = sqlStatement($query, array($fromDate, $toDate));
            $row = array();
            $cnt = 0;
            while ($nrow = sqlFetchArray($res)) {
                $row[] = $nrow;
                $cnt++;
            }
            $responseMsgs = '';
            foreach ($row as $value) {
                $adate = ($value['pc_eventDate'] . '::' . $value['pc_startTime']);
                $pinfo = str_replace("|||", " ", $value['patient_info']);
                $msg = htmlspecialchars($value["message"], ENT_QUOTES);

                $responseMsgs .= "<tr><td>" . $value["pc_eid"] . "</td><td>" . $value["dSentDateTime"] . "</td><td>" . $adate . "</td><td>" . $pinfo . "</td><td>" . $msg . "</td></tr>";
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return 'Error: ' . $message . PHP_EOL;
        }

        return $responseMsgs;
    }

    /**
     * @return bool|string
     */
    public function getCallLogs(): bool|string
    {
        $fromDate = $this->getRequest('datefrom');
        $toDate = $this->getRequest('dateto');

        if ($this->authenticate() !== 1) {
            $e = xlt('Error: Authentication Service Denies Access. Not logged in.');
            if ($this->authenticate() === 2) {
                $e = xlt('Error: Application account credentials is not setup. Setup in Actions->Account Credentials.');
            }
            $ee = array('error' => $e);
            return json_encode($ee);
        }
        try {
            // constants
            $pageCount = 1;
            $recordCountPerPage = 100;
            $timePerCallLogRequest = 10;
            $flag = true;
            // dateFrom and dateTo paramteres
            $timeFrom = '00:00:00.000Z';
            $timeTo = '23:59:59.000Z';
            // Array to push the call-logs to a file
            $callLogs = array();
            $responseMsgs = "";

            while ($flag) {
                // Start Time
                $start = microtime(true);
                $dateFrom = $fromDate . 'T' . $timeFrom;
                $dateTo = $toDate . 'T' . $timeTo;

                $apiResponse = $this->platform->get('/account/~/extension/~/call-log', array(
                    'dateFrom' => $dateFrom,
                    'dateTo' => $dateTo,
                    //'type' => 'SMS',
                    'perPage' => 500,
                    'page' => $pageCount
                ));

                $apiResponseArray = $apiResponse->json()->records;
                $responseMsgs = '';
                foreach ($apiResponseArray as $value) {
                    $responseMsgs .= "<tr><td>" . str_replace(array("T", "Z"), " ", $value->startTime) . "</td><td>" . $value->type . "</td><td>" . $value->from->name . "</td><td>" . $value->to->name . "</td><td>" . $value->action . "</td><td>" . $value->result . "</td><td>" . $value->message->id . "</td></tr>";
                    array_push($callLogs, $value);
                }
                $end = microtime(true);
                // Check if the recording completed wihtin 10 seconds.
                $time = ($end * 1000 - $start * 1000) / 1000;
                // Check if 'nextPage' exists
                if (isset($apiResponseJSONArray["navigation"]["nextPage"])) {
                    if ($time < $timePerCallLogRequest) {
                        sleep($timePerCallLogRequest - $time);
                        sleep(5);
                        $pageCount = $pageCount + 1;
                    }
                } else {
                    $flag = false;
                    unset($callLogs);
                }
            }
        } catch (ApiException $e) {
            $message = $e->getMessage();
            return xlt('HTTP Error') . ': ' . $message . PHP_EOL;
        }

        return $responseMsgs;
    }

    /**
     * @return false|string|void
     */
    public function getPending()
    {
        $dateFrom = $this->getRequest('datefrom');
        $dateTo = $this->getRequest('dateto');

        if ($this->authenticate() !== 1) {
            $e = xlt('Error: Authentication Service Denies Access. Not logged in.');
            if ($this->authenticate() === 2) {
                $e = xlt('Error: Application account credentials is not setup. Setup in Actions->Account Credentials.');
            }
            $ee = array('error' => $e);
            return json_encode($ee);
        }
        try {
            // Writing the call-log response to json file
            $dir = 'fax';
            $messageStoreDir = $this->baseDir;

            //Create the Directory
            if (!file_exists($messageStoreDir)) {
                if (!mkdir($messageStoreDir, 0777, true) && !is_dir($messageStoreDir)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $messageStoreDir));
                }
            }
            // dateFrom and dateTo parameters
            $timeFrom = 'T00:00:01.000Z';
            $timeTo = 'T23:59:59.000Z';
            $dateFrom = trim($dateFrom) . $timeFrom;
            $dateTo = trim($dateTo) . $timeTo;

            $messageStoreList = $this->platform->get('/account/~/extension/~/message-store', array(
                //'messageType' => "",
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo
            ))->json()->records;

            $responseMsgs = [];
            foreach ($messageStoreList as $ilist => $messageStore) {
                if (property_exists($messageStore, 'attachments')) {
                    foreach ($messageStore->attachments as $i => $attachment) {
                        $id = attr($attachment->id);
                        $uri = $attachment->uri;
                        $to = $messageStore->to[0]->name . " " . $messageStore->to[0]->phoneNumber;
                        $from = $messageStore->from->name . " " . $messageStore->from->phoneNumber;
                        $errors = $messageStore->to[0]->faxErrorCode ? "Why: " . $messageStore->to[0]->faxErrorCode : $messageStore->from->faxErrorCode;
                        $status = $messageStore->messageStatus . " " . $errors;
                        $aUrl = "<a href='#' onclick=getDocument(" . "event,'$uri','$id','true')>" . $id . " <span class='fa fa-download'></span></a></br>";
                        $vUrl = "<a href='#' onclick=getDocument(" . "event,'$uri','$id','false')> <span class='fa fa-file-pdf'></span></a></br>";

                        $utc_time = strtotime($messageStore->lastModifiedTime . ' UTC');
                        $updateDate =  date('M j Y g:i:sa T', $utc_time);

                        if (strtolower($messageStore->type) === "sms") {
                            $messageText = $this->getMessageContent($uri);
                            $vtoggle = "<a href='javascript:' onclick=messageShow('" . $id . "')><span class='mx-1 fa fa-eye-slash fa-1x'></span></a>";
                            $vreply = "<a href='javascript:' onclick=messageReply(" . attr_js($messageStore->from->phoneNumber) . ")>
                            <span class='mx-1 fa fa-reply'></span>
                            </a>";
                            $responseMsgs[2] .= "<tr><td>" . str_replace(array("T", "Z"), " ", $updateDate) .
                                "</td><td>" . $messageStore->type . "</td><td>" . $from . "</td><td>" . $to . "</td><td>" . $status .
                                "</td><td><span class='$id'>" . substr($messageText, 0, 30) . "</span>" .
                                "<div class='d-none $id'>" . $messageText . "</div></td>" .
                                "<td class='btn-group'>" . $vtoggle . $vreply . "</td></tr>";
                        } elseif (strtolower($messageStore->direction) === "inbound") {
                            $responseMsgs[0] .= "<tr><td>" . str_replace(array("T", "Z"), " ", $updateDate) . "</td><td>" . $messageStore->type . "</td><td>" . $messageStore->faxPageCount . "</td><td>" . $from . "</td><td>" . $to . "</td><td>" . $status . "</td><td>" . $aUrl . "</td><td>" . $vUrl . "</td></tr>";
                        } else {
                            $responseMsgs[1] .= "<tr><td>" . str_replace(array("T", "Z"), " ", $updateDate) . "</td><td>" . $messageStore->type . "</td><td>" . $messageStore->faxPageCount . "</td><td>" . $from . "</td><td>" . $to . "</td><td>" . $status . "</td><td>" . $aUrl . "</td><td>" . $vUrl . "</td></tr>";
                        }
                    }
                } else {
                    echo json_encode("does not have message" . PHP_EOL);
                    exit();
                }
            }
        } catch (ApiException $e) {
            $message = $e->getMessage();
            $responseMsgs = "<tr><td>" . $message . " : " . xlt('Ensure account credentials are correct.') . "</td></tr>";
            echo json_encode(array('error' => $responseMsgs));
            exit();
        }
        if (empty($responseMsgs)) {
            $empty_msg = xlt("Nothing to report");
            $responseMsgs = [$empty_msg, $empty_msg, $empty_msg];
        }
        echo json_encode($responseMsgs);
        exit();
    }

    /**
     * @param $uri
     * @return string
     */
    public function getMessageContent($uri): string
    {
        try {
            $apiResponse = $this->platform->get($uri);
        } catch (ApiException $e) {
            return '';
        }
        $msgText = text((string)$apiResponse->raw());

        return $msgText;
    }

    /**
     * @return null
     */
    protected function index()
    {
        if (!$this->getSession('pid', '')) {
            $pid = $this->getRequest('patient_id');
            $this->setSession('pid', $pid);
        } else {
            $pid = $this->getSession('pid', '');
        }

        return null;
    }
}
