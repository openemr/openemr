<?php
/**
 * Twilio Fax SMS Controller
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Modules\oeFax\Controller;

require_once(__DIR__ . "/../../../vendor/autoload.php");

use DateTime;
use Exception;
use OpenEMR\Common\Crypto\CryptoGen;
use Twilio\Rest\Client;

class TwilioFaxClient extends AppDispatch
{
    public $baseDir;
    public $uriDir;
    public $serverUrl;
    public $credentials;
    public $cacheDir;
    protected $crypto;
    private $sid;
    private $appKey;
    private $appSecret;

    public function __construct()
    {
        $this->crypto = new CryptoGen();
        $this->baseDir = $GLOBALS['OE_SITE_DIR'] . "/messageStore";
        $this->uriDir = $GLOBALS['OE_SITE_WEBROOT'] . "/messageStore";
        $this->cacheDir = $GLOBALS['OE_SITE_DIR'] . '/documents/logs_and_misc/_cache';
        $this->credentials = $this->getCredentials();
        parent::__construct();
    }

    public function getCredentials()
    {
        // is this new user or credentials aren't setup?
        if (!file_exists($this->cacheDir . '/_credentials_twilio.php')) {
            // create setup json credentials from default template
            mkdir($this->cacheDir, 0777, true);
            $credentials = require(__DIR__ . '/../_credentials_twilio.php');
            $content = $this->crypto->encryptStandard(json_encode($credentials));
            file_put_contents($this->cacheDir . '/_credentials_twilio.php', $content);
        }
        $credentials = file_get_contents($this->cacheDir . '/_credentials_twilio.php');
        $credentials = json_decode($this->crypto->decryptStandard($credentials), true);

        $this->sid = $credentials['username'];
        $this->appKey = $credentials['appKey'];
        $this->appSecret = $credentials['appSecret'];
        $this->serverUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ?
                "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
        $this->uriDir = $this->serverUrl . $this->uriDir;

        return $credentials;
    }

    public function sendSMS($tophone = '', $subject = '', $message = '', $from = '')
    {
        if (empty($from)) {
            $from = $this->formatPhone($this->credentials['smsNumber']);
        } else {
            $from = $this->formatPhone($from);
        }
        $tophone = $this->formatPhone($tophone);
        try {
            $twilio = new Client($this->appKey, $this->appSecret, $this->sid);
            $message = $twilio->messages
                ->create(
                    $tophone,
                    array(
                        "body" => $message,
                        "from" => $from
                    )
                );
        } catch (Exception $e) {
            $message = $e->getMessage();
            return 'Error: ' . $message;
        }
        return $message->sid;
    }

    /**
     * @return string
     */

    public function formatPhone($number)
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

    public function faxProcessUploads()
    {
        if (!empty($_FILES)) {
            $name = $_FILES['fax']['name'];
            $ext = $_FILES['fax']['ext'];
            $tmp_name = $_FILES['fax']['tmp_name'];
        } else {
            return 'Error';
        }
        if (!file_exists($this->baseDir . '/send')) {
            // create setup json credentials from default template
            mkdir($this->baseDir . '/send', 0777, true);
        }
        // add to fax queue
        ['basename' => $basename, 'dirname' => $dirname] = pathinfo($tmp_name);
        $filepath = $this->baseDir . "/send/" . $name;

        move_uploaded_file($tmp_name, $filepath);

        return $filepath;
    }

    public function sendFax()
    {
        if (!$this->authenticate()) {
            return xlt('Error: Authentication Service Denies Access.');
        }
        $isContent = $this->getRequest('isContent');
        $file = $this->getRequest('file');
        $phone = $this->getRequest('phone');
        $isDocuments = $this->getRequest('isDocuments');
        $isQueue = $this->getRequest('isQueue');
        $comments = $this->getRequest('comments');
        $content = '';
        $phone = $this->formatPhone($phone);
        $from = $this->formatPhone($this->credentials['smsNumber']);

        $file = str_replace("file://", '', $file);
        $file = str_replace("\\", "/", realpath($file)); // normalize requested path
        if (!$file) {
            return xlt('Error: no content');
        }
        if ($isContent) {
            $content = $file;
            $file = 'report_' . $GLOBALS['pid'] . '.pdf';
        } elseif (!$isQueue) {
            $content = file_get_contents($file);
            if (!$isDocuments) {
                unlink($file);
            }
        }

        ['basename' => $basename, 'dirname' => $dirname] = pathinfo($file);
        if ($this->crypto->cryptCheckStandard($content)) {
            $content = $this->crypto->decryptStandard($content, null, 'database');
        }
        if ($content) {
            $tmpPath = $this->baseDir . '/send/' . $basename;
            file_put_contents($tmpPath, $content);
        }

        $faxfile = $this->uriDir . '/send/' . $basename;
        $callbackUrl = $this->serverUrl . $GLOBALS['webroot'] .
            '/modules/oeFax/faxserver/faxCallback?site=' . $this->getSession('site_id');

        try {
            $twilio = new Client($this->appKey, $this->appSecret, $this->sid);
            $fax = $twilio->fax->v1->faxes->create(
                $phone,
                $faxfile,
                array("from" => $from, 'statusCallback' => $callbackUrl)
            );
        } catch (Exception $e) {
            $message = $e->getMessage();
            return 'Error: ' . $message;
        }

        return xlt('Send Successful');
    }

    public function authenticate($action_flg = null)
    {
        // did construct happen...
        if (empty($this->credentials)) {
            $this->credentials = $this->getCredentials();
        }

        return 1;
    }

    public function getPending()
    {
        $dateFrom = $this->getRequest('datefrom');
        $dateTo = $this->getRequest('dateto');

        if (!$this->authenticate()) {
            $e = xlt('Error: Authentication Service Denies Access.');
            $ee = array('error' => $e);
            return json_encode($ee);
        };
        try {
            $messageStoreDir = $this->baseDir;

            //Create the Directory
            if (!file_exists($messageStoreDir)) {
                mkdir($messageStoreDir, 0777, true);
            }
            // dateFrom and dateTo paramteres
            $timeFrom = 'T00:00:01Z';
            $timeTo = 'T23:59:59Z';
            $dateFrom = trim($dateFrom) . $timeFrom;
            $dateTo = trim($dateTo) . $timeTo;

            try {
                $twilio = new Client($this->appKey, $this->appSecret, $this->sid);
                $faxes = $twilio->fax->v1->faxes->read(array(
                    'dateCreatedAfter' => $dateFrom,
                    'dateCreatedOnOrBefore' => $dateTo
                ), 100);
            } catch (Exception $e) {
                $message = $e->getMessage();
                return json_encode('Error: ' . $message);
            }

            $responseMsgs = [];
            $responseMsgs[2] = xlt('Not Implemented');
            foreach ($faxes as $messageStore) {
                $id = $messageStore->sid;
                $uri = $messageStore->mediaUrl;
                $to = $messageStore->to;
                $from = $messageStore->from;
                $status = $messageStore->status;
                // purge failed. a day is enough time to report.
                if ($status) {
                    $d1 = new DateTime($messageStore->dateCreated->format('Ymd Hm'));
                    $d2 = new DateTime();
                    $dif = $d1->diff($d2);
                    $interval = ($dif->d * 24) + $dif->h;
                    if ($interval >= 12 && ($status != 'delivered' || $status != 'received')) {
                        $f = $twilio->fax->v1->faxes($id)->delete();
                    } elseif ($interval >= 48) {
                        $f = $twilio->fax->v1->faxes($id)->delete();
                    }
                }
                $vUrl = "<a href='#' onclick=viewDocument(" . "event,'$uri','${id}','false')> <span class='glyphicon glyphicon-open'></span></a></br>";
                $lastDate = $messageStore->dateCreated->format("M j Y h:m:s");
                if (strtolower($messageStore->direction) == "inbound") {
                    $responseMsgs[0] .= "<tr><td>" . $lastDate . "</td><td>" . $messageStore->numPages . "</td><td>" . $from . "</td><td>" . $to . "</td><td>" . $status . "</td><<td>" . $vUrl . "</td></tr>";
                } else {
                    $responseMsgs[1] .= "<tr><td>" . $lastDate . "</td><td>" . $messageStore->numPages . "</td><td>" . $from . "</td><td>" . $to . "</td><td>" . $status . "</td><<td>" . $vUrl . "</td></tr>";
                }
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            $responseMsgs[] = "<tr><td>Error: " . $message . " " . xlt('Ensure credentials are correct.') . "</td></tr>";
            echo json_encode($responseMsgs);
            exit();
        }
        if (empty($responseMsgs)) {
            $responseMsgs = "empty";
        }
        echo json_encode($responseMsgs);
        exit();
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

                $responseMsgs .= "<tr><td>" . $value["pc_eid"] . "</td><td>" . $value["dSentDateTime"] .
                    "</td><td>" . $adate . "</td><td>" . $pinfo . "</td><td>" . $msg . "</td></tr>";
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            return 'Error: ' . $message . PHP_EOL;
        }

        return $responseMsgs;
    }

    public function getCallLogs()
    {
        return xlt('Not Implemented');
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
}
