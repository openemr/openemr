<?php

/**
 * Twilio Fax SMS Controller
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019-2023 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS\Controller;

use DateTime;
use Exception;
use OpenEMR\Common\Crypto\CryptoGen;
use Twilio\Rest\Client;

class TwilioSMSClient extends AppDispatch
{
    public $baseDir;
    public $uriDir;
    public $serverUrl;
    public $credentials;
    protected $crypto;
    private $sid;
    private $appKey;
    private $appSecret;

    public function __construct()
    {
        if (empty($GLOBALS['oefax_enable_sms'] ?? null)) {
            throw new \RuntimeException(xlt("Access denied! Module not enabled"));
        }
        $this->crypto = new CryptoGen();
        $this->baseDir = $GLOBALS['temporary_files_dir'];
        $this->uriDir = $GLOBALS['OE_SITE_WEBROOT'];
        $this->credentials = $this->getCredentials();
        parent::__construct();
    }

    /**
     * @param $dateFrom
     * @param $dateTo
     * @return void
     */
    public function fetchSMSFilteredList($dateFrom, $dateTo)
    {
    }

    /**
     * @param $uiDateRangeFlag
     * @return false|string|null
     */
    public function fetchSMSList($uiDateRangeFlag = true)
    {
        return $this->_getPending($uiDateRangeFlag);
    }

    /**
     * @return array|mixed
     */
    public function getCredentials()
    {
        $credentials = appDispatch::getSetup();

        $this->sid = $credentials['username'];
        $this->appKey = $credentials['appKey'];
        $this->appSecret = $credentials['appSecret'];
        $this->serverUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ?
                "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
        $this->uriDir = $this->serverUrl . $this->uriDir;

        return $credentials;
    }

    /**
     * @param $tophone
     * @param $subject
     * @param $message
     * @param $from
     * @return mixed
     */
    public function sendSMS($tophone = '', $subject = '', $message = '', $from = ''): mixed
    {
        $tophone = $tophone ?: $this->getRequest('phone');
        $from = $from ?: $this->getRequest('from');
        $message = $message ?: $this->getRequest('comments');

        if (empty($from)) {
            $from = $this->formatPhone($this->credentials['smsNumber']);
        } else {
            $from = $this->formatPhone($from);
        }
        $tophone = $this->formatPhone($tophone);
        try {
            $twilio = new Client($this->appKey, $this->appSecret, $this->sid);
            $message = $twilio->messages->create(
                $tophone,
                array(
                    "body" => text($message),
                    "from" => attr($from)
                )
            );
        } catch (Exception $e) {
            $message = $e->getMessage();
            return text('Error: ' . $message);
        }
        return text($message->sid);
    }

    /**
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
     * @param $acl
     * @return int
     */
    public function authenticate($acl = ['admin', 'doc']): int
    {
        // did construct happen...
        if (empty($this->credentials)) {
            $this->credentials = $this->getCredentials();
        }
        if (!$this->sid || !$this->appKey || !$this->appSecret) {
            return 0;
        }
        list($s, $v) = $acl;
        return $this->verifyAcl($s, $v);
    }

    /**
     * @return false|string|void
     */
    public function _getPending()
    {
        $dateFrom = $this->getRequest('datefrom');
        $dateTo = $this->getRequest('dateto');

        if (!$this->authenticate()) {
            return $this->authErrorDefault;
        }
        try {
            // dateFrom and dateTo
            $timeFrom = 'T00:00:01Z';
            $timeTo = 'T23:59:59Z';
            $dateFrom = trim($dateFrom) . $timeFrom;
            $dateTo = trim($dateTo) . $timeTo;

            try {
                $twilio = new Client($this->appKey, $this->appSecret, $this->sid);
                $messages = $twilio->messages->read([
                    "dateSentAfter" => $dateFrom,
                    "dateSentBefore" => $dateTo
                ], 100);
            } catch (Exception $e) {
                $message = $e->getMessage();
                $emsg = xlt('Ensure account credentials are correct.');
                return json_encode(array('error' => $message . " : " . $emsg));
            }

            $responseMsgs = [];
            $responseMsgs[2] = xlt('Not Implemented');
            foreach ($messages as $messageStore) {
                $id = $messageStore->sid;
                $uri = $messageStore->uri;
                $to = $messageStore->to;
                $from = $messageStore->from;
                $status = $messageStore->status;
                // purge failed. a day is enough time to report.
                if ($status) {
                    $d1 = new DateTime($messageStore->dateCreated->format('Ymd Hi'));
                    $d2 = new DateTime(gmdate('Ymd Hi', time()));
                    $dif = $d1->diff($d2);
                    $interval = ($dif->d * 24) + $dif->h;
                    /* interval for future */
                }
                $vreply = '';
                if ($status != 'failed' && $this->formatPhone($this->credentials['smsNumber']) != $messageStore->from) {
                    $vreply = "<a href='javaScript:' onclick=messageReply(" . attr_js($messageStore->from) . ")>
                            <span class='mx-1 fa fa-reply'></span></a>";
                } else {
                    $vreply = "<a href='#' title='SMS failure'> <span class='fa fa-file-pdf text-danger'></span></a></br>";
                }
                $utc_time = strtotime($messageStore->dateUpdated->format('Ymd His') . ' UTC');
                $updateDate = date('M j Y g:i:sa T', $utc_time);
                if (strtolower($messageStore->direction) != "outbound-api") {
                    $responseMsgs[0] .= "<tr><td>" . text($updateDate) . "</td><td>" . text($messageStore->direction) . "</td><td>" . text($messageStore->body) . "</td><td>" . ($from) . "</td><td>" . text($to) . "</td><td>" . text($status) . "</td><<td>" . $vreply . "</td></tr>";
                } else {
                    $responseMsgs[1] .= "<tr><td>" . text($updateDate) . "</td><td>" . text($messageStore->direction) . "</td><td>" . text($messageStore->body) . "</td><td>" . text($from) . "</td><td>" . text($to) . "</td><td>" . ($status) . "</td><<td>" . $vreply . "</td></tr>";
                }
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            $responseMsgs = "<tr><td>" . text($message) . " : " . xlt('Ensure account credentials are correct.') . "</td></tr>";
            echo json_encode(array('error' => $responseMsgs));
            exit();
        }
        if (empty($responseMsgs)) {
            $responseMsgs = "empty";
        }
        echo json_encode($responseMsgs);
        exit();
    }

    /**
     * @return false|string
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
        $r = array($u['fname'], $u['lname'], $u['fax'], $u['facility']);

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
                $responseMsgs .= "<tr><td>" . text($value["pc_eid"]) . "</td><td>" . text($value["dSentDateTime"]) .
                    "</td><td>" . text($adate) . "</td><td>" . text($pinfo) . "</td><td>" . text($value["message"]) . "</td></tr>";
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            return 'Error: ' . text($message) . PHP_EOL;
        }

        return $responseMsgs;
    }

    /**
     * @return string
     */
    public function getCallLogs()
    {
        return xlt('Not Implemented');
    }

    /**
     * @return null
     */
    protected function index()
    {
        global $pid;
        if (!$this->getSession('pid', '')) {
            $pid_s = $this->getRequest('patient_id');
            $this->setSession('pid', $pid ?: $pid_s);
        }
        if (empty($pid)) {
            $pid = $this->getSession('pid', '');
        }
        return null;
    }

    /**
     * @return string|bool
     */
    function sendFax(): string|bool
    {
        // TODO: Implement sendFax() method.
    }

    /**
     * @return string|bool
     */
    function fetchReminderCount(): string|bool
    {
        return 0;
    }
}
