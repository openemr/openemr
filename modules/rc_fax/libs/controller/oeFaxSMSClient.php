<?php
/**
 * Fax SMS Module Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

//$ignoreAuth = 1; //todo for debug comment production.
require_once(__DIR__ . "/../../../../interface/globals.php");
require_once(__DIR__ . '/../../vendor/autoload.php');
require_once('oeActionsDispatch.php');

use OpenEMR\Common\Crypto\CryptoGen;
use RingCentral\SDK\Http\ApiException;
use RingCentral\SDK\SDK;

class oeFaxSMSClient extends oeActionsDispatch
{
    public $baseDir;
    public $uriDir;
    public $serverUrl;
    public $redirectUrl;
    public $portalUrl;
    public $credentials;
    public $cacheDir;
    public $apiBase;
    protected $platform;
    protected $rcsdk;
    protected $crypto;

    public function __construct()
    {
        $this->crypto = new CryptoGen();
        $this->baseDir = $GLOBALS['OE_SITE_DIR'] . DIRECTORY_SEPARATOR . "Message-Store";
        $this->uriDir = $GLOBALS['OE_SITE_WEBROOT'] . "/Message-Store";
        $this->cacheDir = $this->baseDir . DIRECTORY_SEPARATOR . '_cache';
        $this->credentials = $this->getCredentials();
        $this->portalUrl = !$this->credentials['production'] ? "https://service.devtest.ringcentral.com/" : "https://service.ringcentral.com/";
        $this->serverUrl = !$this->credentials['production'] ? "https://platform.devtest.ringcentral.com" : "https://platform.ringcentral.com";
        $this->redirectUrl = $this->credentials['redirect_url'];
        parent::__construct();
    }

    public function getCredentials()
    {
        // is this new user or credentials aren't setup?
        if (!file_exists($this->cacheDir . '/_credentials.php')) {
            // create setup json credentials from defaults
            mkdir($this->cacheDir, 0777, true);
            $credentials = require(__DIR__ . '/../../_credentials.php');

            $content = $this->crypto->encryptStandard(json_encode($credentials));
            file_put_contents($this->cacheDir . '/_credentials.php', $content);
        }
        $credentials = file_get_contents($this->cacheDir . '/_credentials.php');

        $credentials = json_decode($this->crypto->decryptStandard($credentials), true);

        return $credentials;
    }

    public function faxProcessUploads()
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

    public function processTokenCode()
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

    public function sendSMS($tophone = '', $subject = '', $message = '', $from = '')
    {
        if (!$this->authenticate()) {
            return xlj('Error Authentication Service Denies Access.');
        };

        $smsNumber = $this->credentials['smsNumber'];
        if ($smsNumber) {
            $response = $this->platform
                ->post('/account/~/extension/~/sms', array(
                    'from' => array('phoneNumber' => $smsNumber),
                    'to' => array(
                        array('phoneNumber' => $tophone),
                    ),
                    'text' => $message,
                ));
        } else {
            return false;
        }
        sleep(1); // RC May only allow 1/second.

        return true;
    }

    public function authenticate($action_flg = '')
    {
        // did construct happen...
        if (empty($this->credentials)) {
            $this->credentials = $this->getCredentials();
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
        $session_token = $this->getSession('sessionAccessToken');
        if (!empty($cachedAuth["refresh_token"])) {
            // probally new openemr session or user!
            $this->platform->auth()->setData($cachedAuth);
        } elseif (isset($session_token)) {
            $this->platform->auth()->setData((array)json_decode($session_token));
        }
        // verified logged status
        if ($this->platform->loggedIn()) {
            $logged_in = 1;
            try {
                $this->platform->refresh();
            } catch (Exception $e) {
                // Give up! Clear old auth stuff afterwards a user
                // most likely will get a OAuth login dialog
                // on return, one would hope!
                unset($_SESSION['sessionAccessToken']);
                $logged_in = 0;
                return $logged_in;
            }
        } else {
            // This means we're not logged in so will have to prompt user.
            // Using the authUrl to setup a OAuth log in.
            // OAuth redirects to rcauth.php(default) or
            // redirect url from setup. Look there.
            $request_url = $this->platform->authUrl(['redirectUri' => $this->redirectUrl, 'state' => 'login']);
            $this->setSession('url', $request_url);
            $this->setSession('redirect_uri', $this->redirectUrl);

            // Init RC's OAuth dialog. Three legs.
            require("rcauth.php");
        }
        // Save updated authentication data
        // sometimes may be empty structure but, we don't care.
        $this->setSession('sessionAccessToken', $this->platform->auth()->data());
        $content = json_encode($this->platform->auth()->data(), JSON_PRETTY_PRINT);
        $content = $this->crypto->encryptStandard($content);
        file_put_contents($authback, $content);

        return $logged_in;
    }

    // Callback rcauth.php has this embedded, below is available in case.

    public function getStoredDoc()
    {
        $docuri = $this->getRequest(docuri);
        $uri = $docuri;
        if (!$this->authenticate()) {
            return xlj('Error Authentication Service Denies Access.');
        };
        try {
            $apiResponse = $this->platform->get($uri);
        } catch (ApiException $e) {
            $message = $e->getMessage() . ' (from backend) at URL ' . $e->apiResponse()->request()->getUri()->__toString();
            $r = "Error Retrieving Fax:\n" . $message;
            return $r;
        }
        if ($apiResponse->response()->getHeader('Content-Type')[0] == 'application/pdf') {
            $ext = 'pdf';
            $type = 'Fax';
            $doc = 'data:application/pdf;base64, ' . rawurlencode(base64_encode((string)$apiResponse->raw()));
        } elseif ($apiResponse->response()->getHeader('Content-Type')[0] == 'image/tiff') {
            $ext = 'tif';
            $type = 'Fax';
            $doc = 'data:image/tiff;base64, ' . rawurlencode(base64_encode((string)$apiResponse->raw()));
        } else {
            $ext = 'txt';
            $type = 'text/html';
            $doc = (string)$apiResponse->raw();
        }
        $r = $apiResponse->raw() ? $apiResponse->raw() : "error";

        return $doc ? $doc : $r;
    }

    /**
     * @return string
     */
    public function viewFax()
    {
        $pid = $this->getRequest('pid');
        $docid = $this->getRequest('docid');
        $docuri = $this->getRequest('docuri');
        $isDownload = $this->getRequest('download');
        $uri = $docuri;
        $isDownload = $isDownload == 'true' ? 1 : 0;

        if (!$this->authenticate()) {
            return xlj('Error Authentication Service Denies Access.');
        };

        $messageStoreDir = $this->baseDir;

        if (!file_exists($messageStoreDir)) {
            mkdir($messageStoreDir, 0777, true);
        }

        try {
            $apiResponse = $this->platform->get($uri);
        } catch (ApiException $e) {
            $message = $e->getMessage() . ' (from backend) at URL ' . $e->apiResponse()->request()->getUri()->__toString();
            $r = "Error Retrieving Fax:\n" . $message;
            return $r;
        }

        if ($apiResponse->response()->getHeader('Content-Type')[0] == 'application/pdf') {
            $ext = 'pdf';
            $type = 'Fax';
            $doc = 'data:application/pdf;base64, ' . rawurlencode(base64_encode((string)$apiResponse->raw()));
        } elseif ($apiResponse->response()->getHeader('Content-Type')[0] == 'image/tiff') {
            $ext = 'tiff';
            $type = 'Fax';
            $doc = 'data:image/tiff;base64, ' . rawurlencode(base64_encode((string)$apiResponse->raw()));
        } elseif ($apiResponse->response()->getHeader('Content-Type')[0] == 'audio/wav' || $apiResponse->response()->getHeader('Content-Type')[0] == 'audio/x-wav') {
            $ext = 'wav';
            $type = 'Audio';
            //$doc = 'data:image/tiff;base64, ' . rawurlencode(base64_encode((string)$apiResponse->raw()));
        } else {
            $ext = 'txt';
            $type = 'Text';
            $doc = (string)$apiResponse->raw();
        }

        $fname = "${messageStoreDir}/${type}_${docid}.${ext}";
        file_put_contents($fname, $apiResponse->raw());
        if ($isDownload) {
            $this->setSession('where', $fname);
            return $fname;
        }
        $furi = "$this->uriDir/${type}_${docid}.${ext}";

        return $furi;
    }

    // this returns encoded uri of document.

    public function disposeDoc()
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

        die('Problem with download. Use browser back button');
    }

    public function sendFax()
    {
        if (!$this->authenticate()) {
            return xlt('Error Authentication Service Denies Access.');
        }
        $isContent = $this->getRequest('isContent');
        $file = $this->getRequest('file');
        $phone = $this->getRequest('phone');
        $name = $this->getRequest('name');
        $lastname = $this->getRequest('surname');
        $isDocuments = $this->getRequest('isDocuments');
        $comments = $this->getRequest('comments');
        $name = $name . ' ' . $lastname;
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
            $type = \GuzzleHttp\Psr7\mimetype_from_filename(basename($file));
            $request = $this->rcsdk->createMultipartBuilder()
                ->setBody(array(
                    'to' => array(
                        array(
                            'phoneNumber' => $phone,
                            'name' => $name)
                    ),
                    'faxResolution' => 'High',
                    'coverPageText' => $comments
                ))
                ->add($content, $file, array('Content-Type' => "$type"))
                ->request('/account/~/extension/~/fax');

            //$debug = $request->getBody() . PHP_EOL;
            $response = $this->platform->sendRequest($request);
        } catch (ApiException $e) {
            $message = $e->getMessage();
            return 'Error: ' . $message . PHP_EOL;
        }

        return xlt('Fax Successfully Sent');
    }

    public function getUser()
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

    public function getNotificationLog()
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
        } catch (ApiException $e) {
            $message = $e->getMessage();
            return 'Error: ' . $message . PHP_EOL;
        }

        return $responseMsgs;
    }

    public function getCallLogs()
    {
        $fromDate = $this->getRequest('datefrom');
        $toDate = $this->getRequest('dateto');

        if (!$this->authenticate()) {
            return xlt('Error Authentication Service Denies Access.');
        };
        try {
            // constants
            $pageCount = 1;
            $recordCountPerPage = 100;
            $timePerCallLogRequest = 10;
            $flag = true;

            // Export call-log response to json file
            $dir = $fromDate;

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

                //print 'Page ' . $pageCount . 'retreived with ' . $recordCountPerPage . 'records' . PHP_EOL;

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

    public function getPending()
    {
        $dateFrom = $this->getRequest(datefrom);
        $dateTo = $this->getRequest(dateto);

        if (!$this->authenticate()) {
            $e = xlt('Error Authentication Service Denies Access.');
            $ee = array('error' => $e);
            return json_encode($ee);
        };
        try {
            // Writing the call-log response to json file
            $dir = 'fax';
            $messageStoreDir = $this->baseDir; // . DIRECTORY_SEPARATOR . $dir;

            //Create the Directory
            if (!file_exists($messageStoreDir)) {
                mkdir($messageStoreDir, 0777, true);
            }
            // dateFrom and dateTo paramteres
            $timeFrom = 'T00:00:01.000Z';
            $timeTo = 'T23:59:59.000Z';
            $dateFrom = trim($dateFrom) . $timeFrom;
            $dateTo = trim($dateTo) . $timeTo;

            $messageStoreList = $this->platform->get('/account/~/extension/~/message-store', array(
                //'messageType' => "",
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo
            ))->json()->records;

            $timePerMessageStore = 6;
            $responseMsgs = [];
            foreach ($messageStoreList as $i => $messageStore) {
                if (property_exists($messageStore, 'attachments')) {
                    foreach ($messageStore->attachments as $i => $attachment) {
                        $id = $attachment->id;
                        $uri = $attachment->uri;
                        $to = $messageStore->to[0]->name . " " . $messageStore->to[0]->phoneNumber;
                        $from = $messageStore->from->name . " " . $messageStore->from->phoneNumber;
                        $errors = $messageStore->to[0]->faxErrorCode ? "why: " . $messageStore->to[0]->faxErrorCode : $messageStore->from->faxErrorCode;
                        $status = $messageStore->messageStatus . " " . $errors;
                        $aUrl = "<a href='#' onclick=getDocument(" . "event,'$uri','${id}','true')>" . ${id} . " <span class='glyphicon glyphicon-open'></span></a></br>";
                        $vUrl = "<a href='#' onclick=getDocument(" . "event,'$uri','${id}','false')> <span class='glyphicon glyphicon-open'></span></a></br>";

                        if (strtolower($messageStore->type) === "sms") {
                            $responseMsgs[2] .= "<tr><td>" . str_replace(array("T", "Z"), " ", $messageStore->lastModifiedTime) . "</td><td>" . $messageStore->type . "</td><td>" . $from . "</td><td>" . $to . "</td><td>" . $status . "</td><td>" . $aUrl . "</td><td>" . $vUrl . "</td></tr>";
                        } elseif (strtolower($messageStore->direction) === "inbound") {
                            $responseMsgs[0] .= "<tr><td>" . str_replace(array("T", "Z"), " ", $messageStore->lastModifiedTime) . "</td><td>" . $messageStore->type . "</td><td>" . $messageStore->faxPageCount . "</td><td>" . $from . "</td><td>" . $to . "</td><td>" . $status . "</td><td>" . $aUrl . "</td><td>" . $vUrl . "</td></tr>";
                        } else {
                            $responseMsgs[1] .= "<tr><td>" . str_replace(array("T", "Z"), " ", $messageStore->lastModifiedTime) . "</td><td>" . $messageStore->type . "</td><td>" . $messageStore->faxPageCount . "</td><td>" . $from . "</td><td>" . $to . "</td><td>" . $status . "</td><td>" . $aUrl . "</td><td>" . $vUrl . "</td></tr>";
                        }
                    }
                } else {
                    echo json_encode("does not have message" . PHP_EOL);
                    exit();
                }
            }
        } catch (ApiException $e) {
            $message = $e->getMessage();
            $responseMsgs[] = "<tr><td>Error: " . $message . " Ensure credentials are correct.</td></tr>";
            echo json_encode($responseMsgs);
            exit();
        }
        if (empty($responseMsgs)) {
            $responseMsgs = "empty";
        }
        echo json_encode($responseMsgs);
        exit();
    }

    public function getMessage()
    {
        $pid = $this->getRequest(pid);
        $dateFrom = $this->getRequest(datefrom);
        $dateTo = $this->getRequest(dateto);
        $type = $this->getRequest(type);

        if (!$this->authenticate()) {
            return xlj('Error Authentication Service Denies Access.');
        };
        try {
            // Writing the call-log response to json file
            $messageStoreDir = $this->baseDir; // . DIRECTORY_SEPARATOR . $dir;

            //Create the Directory
            if (!file_exists($messageStoreDir)) {
                mkdir($messageStoreDir, 0777, true);
            }

            $messageStoreList = $this->platform->get('/account/~/extension/~/message-store', array(
                'messageType' => "",
                'dateFrom' => '2018-05-01'
            ))->json()->records;

            $timePerMessageStore = 6;
            $responseMsgs = "";
            foreach ($messageStoreList as $i => $messageStore) {
                if (property_exists($messageStore, 'attachments')) {
                    foreach ($messageStore->attachments as $i => $attachment) {
                        $id = $attachment->id;
                        $uri = $attachment->uri;
                        //print "Retrieving ${uri}" . PHP_EOL;
                        try {
                            $apiResponse = $this->platform->get($uri);
                        } catch (ApiException $e) {
                            $message = $e->getMessage() . ' (from backend) at URL ' . $e->apiResponse()->request()->getUri()->__toString();
                            $responseMsgs .= "<tr><td>Error: " . $message . "</td></tr>";
                            continue;
                        }
                        // Retreive the appropriate extension type of the message
                        if ($apiResponse->response()->getHeader('Content-Type')[0] == 'application/pdf') {
                            $ext = 'pdf';
                            $type = 'Fax';
                        } elseif ($apiResponse->response()->getHeader('Content-Type')[0] == 'audio/mpeg') {
                            $ext = 'mp3';
                            $type = 'VoiceMail';
                        } else {
                            $ext = 'txt';
                            $type = 'SMS';
                        }

                        $start = microtime(true);
                        file_put_contents("${messageStoreDir}/${type}_${id}.${ext}", $apiResponse->raw());
                        //print "Wrote File for Call Log Record ${id}" . PHP_EOL;
                        $responseMsgs .= "<tr><td>" . $messageStore->creationTime . "</td><td>" . $messageStore->type . "</td><td>" . $messageStore->from->name . "</td><td>" . $messageStore->to->name . "</td><td>" . $messageStore->availability . "</td><td>" . $messageStore->messageStatus . "</td><td>" . $messageStore->message->id . "</td></tr>";
                        $end = microtime(true);
                        $time = ($end * 1000 - $start * 1000);
                        if ($time < $timePerMessageStore) {
                            sleep($timePerMessageStore - $time);
                        }
                    }
                } else {
                    echo xlt("Does not have messages") . PHP_EOL;
                }
            }
        } catch (ApiException $e) {
            $message = $e->getMessage() . ' (from backend) at URL ' . $e->apiResponse()->request()->getUri()->__toString();
            echo "<tr><td>Error: " . $message . "</td></tr>";
            exit();
        }

        echo $responseMsgs;
        exit();
    }

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

    protected function saveSetup()
    {
        $username = $this->getRequest('username');
        $ext = $this->getRequest('extension');
        $password = $this->getRequest('password');
        $appkey = $this->getRequest('key');
        $appsecret = $this->getRequest('secret');
        $production = $this->getRequest('production');
        $smsNumber = $this->getRequest('smsnumber');
        $smsMessage = $this->getRequest('smsmessage');
        $smsHours = $this->getRequest('smshours');
        $setup = array(
            'username' => "$username",
            'extension' => "$ext",
            'password' => "$password",
            'appKey' => "$appkey",
            'appSecret' => "$appsecret",
            'server' => !$production ? 'https://platform.devtest.ringcentral.com' : "https://platform.ringcentral.com",
            'portal' => !$production ? "https://service.devtest.ringcentral.com/" : "https://service.ringcentral.com/",
            'smsNumber' => "$smsNumber",
            'production' => $production,
            'redirect_url' => $this->getRequest('redirect_url'),
            'smsHours' => $smsHours,
            'smsMessage' => $smsMessage
        );
        $baseDir = $GLOBALS['OE_SITE_DIR'] . DIRECTORY_SEPARATOR . "Message-Store";
        $this->baseDir = $baseDir;

        $cacheDir = $baseDir . DIRECTORY_SEPARATOR . '_cache';
        if (!file_exists($cacheDir . '/_credentials.php')) {
            mkdir($cacheDir, 0777, true);
        }

        $content = $this->crypto->encryptStandard(json_encode($setup));
        file_put_contents($cacheDir . '/_credentials.php', $content);

        return xlj('Save Success');
    }
}
