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

namespace OpenEMR\Modules\FaxSMS\Controller;

use DateTime;
use Document;
use Exception;
use http\Exception\RuntimeException;
use OpenEMR\Common\Crypto\CryptoGen;
use Symfony\Component\HttpClient\HttpClient;
use OpenEMR\Modules\FaxSMS\EtherFax\EtherFaxClient;
use OpenEMR\Modules\FaxSMS\EtherFax\FaxResult;

class EtherFaxActions extends AppDispatch
{
    public static $timeZone;
    public $baseDir;
    public $uriDir;
    public $serverUrl;
    public $credentials;
    public string $portalUrl;
    protected $crypto;
    private EtherFaxClient $client;

    public function __construct()
    {
        $this->crypto = new CryptoGen();
        $this->baseDir = $GLOBALS['temporary_files_dir'];
        $this->uriDir = $GLOBALS['OE_SITE_WEBROOT'];
        $this->credentials = $this->getCredentials();
        $this->client = new EtherFaxClient();
        $this->client->setCredentials(
            $this->credentials['account'],
            $this->credentials['username'],
            $this->credentials['password'],
            $this->credentials['appKey']
        );
        $this->portalUrl = "https://clients.connect.etherfax.net/Account/Login";
        parent::__construct();
    }

    /**
     * @return array|mixed
     */
    public function getCredentials(): mixed
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

    public function fetchReminderCount(): bool|string
    {
        $cnt = $this->client->getUnreadFaxCount();

        return json_encode($cnt);
    }

        /**
     * @return string
     */
    public function faxProcessUploads(): string
    {
        if (!empty($_FILES)) {
            $name = $_FILES['fax']['name'];
            $ext = $_FILES['fax']['ext'];
            $tmp_name = $_FILES['fax']['tmp_name'];
        } else {
            return 'Error';
        }
        if (!file_exists($this->baseDir . '/send')) {
            mkdir($this->baseDir . '/send', 0777, true);
        }
        // add to fax queue
        ['basename' => $basename, 'dirname' => $dirname] = pathinfo($tmp_name);
        $filepath = $this->baseDir . "/send/" . $name;

        move_uploaded_file($tmp_name, $filepath);

        return $filepath;
    }

    /**
     * @return string
     */
    public function sendSMS(): string
    {
        // dummy function
        return text("Not implemented");
    }

    /**
     * @return mixed|string
     */
    public function sendFax(): string|bool
    {
        if (!$this->authenticate()) {
            return $this->authErrorDefault;
        }
        $isContent = $this->getRequest('isContent');
        $file = $this->getRequest('file');
        $docid = $this->getRequest('docid');
        $mime = $this->getRequest('mime');
        $phone = $this->getRequest('phone');
        $isDocuments = (int)$this->getRequest('isDocuments');
        $isQueue = $this->getRequest('isQueue');
        $comments = $this->getRequest('comments');
        $phone = $this->formatPhone($phone);
        $from = $this->formatPhone($this->credentials['phone']);
        $user = $this::getLoggedInUser();
        $csid = $user['facility'];
        $tag = $user['username'];
        $status = [];
        if (empty($isContent)) {
            $file = str_replace("file://", '', $file);
            $file = str_replace("\\", "/", realpath($file)); // normalize requested path
            if (!$file) {
                return xlt('Error: No content');
            }
        }
        ['basename' => $basename, 'dirname' => $dirname] = pathinfo($file);
        if ($isContent) {
            $basename = 'content.pdf';
        }
        if ($isDocuments) {
            $file = (new Document($docid))->get_data();
        }
        try {
            $fax = $this->client->sendFax($phone, $file, null, $from, $csid, $tag, $isDocuments, $basename);
            if (!$fax->FaxResult) {
                return 'Error: ' . json_encode($fax->Message);
            }
            if ($fax->FaxResult == FaxResult::InProgress) {
                while (true) {
                    $status = $this->client->getFaxStatus($fax->JobId);
                    if ($status == null || $status->FaxResult != FaxResult::InProgress) {
                        break;
                    }
                    sleep(5);
                }
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            return 'Error: ' . json_encode($message);
        }
        $error = FaxResult::getFaxResult($status->FaxResult);
        if ($status->FaxResult ?? null) {
            return 'Error: ' . json_encode($error);
        }

        return json_encode($error);
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
        $check = $this->client->getFaxAccount();
        if (!$check) {
            return 0;
        }
        self::$timeZone = $check->TimeZone ?? null;

        list($s, $v) = $acl;
        return $this->verifyAcl($s, $v);
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
        $validate = preg_match('^\+[1-9]\d{1,14}$', $n);
        return $n;
    }

    /**
     * I do not like Sympony http client.
     * Very similar to the one I wrote except mine can handle relative url.
     * on hold for now!
     * @param $to
     * @param $message
     * @param $from
     * @return string
     */
    private function sendMessage($to, $message, $from = null): string
    {
        $client = HttpClient::create(['verify_peer' => false, 'verify_host' => false, 'proxy' => "localhost:8888"]);
        $url = "/interface/modules/custom_modules/oe-module-faxsms/sendSMS?type=sms";

        try {
            $response = $client->request('POST', $url, [
                'body' => [
                    'phone' => $to,
                    'comment' => $message
                ]
            ]);
        } catch (\RuntimeException $e) {
            return $e->getMessage();
        }
        $statusCode = $response->getStatusCode();

        return $statusCode;
    }

    /**
     * @return false|string|void
     */
    public function getPending()
    {
        $dateFrom = $this->getRequest('datefrom');
        $dateTo = $this->getRequest('dateto');

        if (!$this->authenticate()) {
            return $this->authErrorDefault;
        };
        try {
            // dateFrom and dateTo
            $timeFrom = 'T00:00:01';
            $timeTo = 'T23:59:59';
            $dateFrom = trim($dateFrom) . $timeFrom;
            $dateTo = trim($dateTo) . $timeTo;
            $faxStore = $this->client->getUnreadFaxList();
            $responseMsgs = [];
            //$responseMsgs[2] = xlt('Not Implemented');
            $direction = 'inbound';
            foreach ($faxStore as $fax) {
                $id = $fax->JobId;
                $ReceivedOn = $fax->ReceivedOn;
                // purge failed. a day is enough time to report.
                if ($ReceivedOn) {
                    $fromDate = strtotime($dateFrom . ' UTC');
                    $toDate = strtotime($dateTo . ' UTC');
                    $faxDate = strtotime($ReceivedOn . ' UTC');
                    // only process faxes in date range
                    if ($faxDate < $fromDate || $faxDate > $toDate) {
                        continue;
                    }
                    $date_received = new DateTime(date('Ymd Hi', $faxDate));
                    $date_today = new DateTime(date('Ymd Hi', time()));
                    $dif = $date_received->diff($date_today);
                    $interval = ($dif->d * 24) + $dif->h;
                    // todo mark received if out of date
                    if ($interval >= 24 && $interval <= 48) {
                        // send message
                    } elseif ($interval >= 48) {
                        $this->client->setFaxReceived($id);
                        continue;
                    }
                } else {
                    throw new RuntimeException(xlt("Missing fax receive date!"));
                }
                // get the raw and any fields
                $faxDetails = $this->client->getFax($fax->JobId);
                $to = $faxDetails->CalledNumber;
                $from = $faxDetails->CallingNumber;
                $params = $faxDetails->DocumentParams;
                $form = '';
                $docType = null;
                $id_esc = text($id);
                $showFlag = 0;
                foreach ($faxDetails->AnalyzeFormResult->AnalyzeResult->DocumentResults as $r) {
                    $details = null;
                    $docType = $params->Type;
                    $form = "<tr id='$id_esc' class='d-none collapse-all'><td colspan='12'>\n" .
                        "<div class='container table-responsive'>\n" .
                        "<table class='table table-sm table-bordered table-dark'>\n";
                    $form .= "<thead><tr><th>\n" .
                        xlt("Parameter") . "\n</th><th>\n" .
                        xlt("Value") . "</th><th>\n" .
                        xlt("Confidence") . " : " . text($r->DocTypeConfidence * 100) .
                        "\n</th></tr></thead>\n";
                    $form .= "<tbody>\n";
                    foreach ($r->Fields as $field) {
                        if ($field->Text == 'unselected' || empty($field->Text)) {
                            continue;
                        }
                        $showFlag++;
                        $form .= "<tr>\n";
                        $form .= '<td>' . text(str_replace(" - ", "-", $field->Name)) . "</td>\n";
                        $form .= '<td>' . text($field->Text) . "</td>\n";
                        $form .= '<td>' . text($field->Confidence * 100) . "</td>\n";
                        $form .= "</tr>\n";
                        $details['dob'] = $field->Name == "Patient Date of Birth" ? $field->Text : $details['dob'];
                        if (preg_match('/(Patient First Name)(Patient Last Name)/', $field->Name)) {
                            $details['name'][$field->Name] = $field->Text;
                        }
                        $details['gender'] = $field->Text == "selected" && ($field->Name == "Patient Gender - Male") ? 'Male' : $details['gender'];
                        $details['gender'] = $field->Text == "selected" && ($field->Name == "Patient Gender - Female") ? 'Female' : $details['gender'];
                    }
                    $form .= "</tbody></table></div></td></tr>\n";
                }
                $downloadLink = "<a role='button' href='javaScript:' onclick=\"getDocument(event, null, " . attr_js($id) . ", 'true')\"> <span class='fa fa-file-pdf'></span></a>";
                $viewLink = "<a role='button' href='javaScript:' onclick=\"getDocument(event, null, " . attr_js($id) . ", 'false')\"> <span class='fa fa-file-pdf'></span></a>";
                $details_esc = json_encode($details);
                $forwardLink = "<a role='button' href='javaScript:' onclick=\"forwardFax(event, " . attr_js($id) . ")\"> <span class='fa fa-forward'></span>";
                $detailLink = '';
                if ($showFlag) {
                    $showFlag = text($showFlag) . ' ' . xlt("Items");
                    $detailLink = "$showFlag<a role='button' href='javaScript:' class='btn btn-link fa fa-eye' onclick='toggleDetail(\"#$id_esc\")'></a>";
                }

                $faxFormattedDate = date('M j, Y g:i:sa T', $faxDate);
                $docLen = text(round($params->Length / 1000, 2)) . "KB";
                if (strtolower($direction) == "inbound") {
                    $responseMsgs[0] .= "<tr><td>" . text($faxFormattedDate) .
                        "</td><td>" . text($from) . "</td><td>" . text($to) .
                        "</td><td>" . text($faxDetails->PagesReceived) .
                        "</td><td>" . text($docLen) .
                        "</td><td class='text-center'>" . $detailLink .
                        "</td><td class='text-center'>" . $forwardLink .
                        "</td><td class='text-center'>" . $downloadLink .
                        "</td><td class='text-center'>" . $viewLink .
                        "</td></tr>";
                    $responseMsgs[0] .= $form;
                }
            }
        } catch (\RuntimeException $e) {
            $message = $e->getMessage();
            $responseMsgs = "<tr><td>" . $message . " : " . xlt('Ensure account credentials are correct.') . "</td></tr>";
            echo json_encode(array('error' => $responseMsgs));
            exit();
        }
        if (empty($responseMsgs)) {
            $responseMsgs = [xlt("Currently inbox is empty.")];
        }

        echo json_encode($responseMsgs);
        exit();
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
            return $this->authErrorDefault;
        }

        $faxStoreDir = $this->baseDir;

        if (!file_exists($faxStoreDir) && !mkdir($faxStoreDir, 0777, true) && !is_dir($faxStoreDir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $faxStoreDir));
        }

        try {
            $apiResponse = $this->client->getFax($docid);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $r = "Error: Retrieving Fax:\n" . $message;
            return $r;
        }

        $faxImage = $apiResponse->FaxImage;
        $raw = base64_decode($faxImage);
        $c_header = $apiResponse->DocumentParams->Type;
        if ($c_header == 'application/pdf') {
            $ext = 'pdf';
            $type = 'Fax';
            $doc = 'data:application/pdf;base64, ' . rawurlencode(((string)$faxImage));
        } elseif ($c_header == 'image/tiff' || $c_header == 'image/tif') {
            $ext = 'tiff';
            $type = 'Fax';
            $doc = 'data:image/tiff;base64, ' . rawurlencode((string)$faxImage);
        } else {
            $ext = 'txt';
            $type = 'Text';
            $doc = "data:text/plain, " . text((string)$faxImage);
        }

        $fname = "${faxStoreDir}/${type}_${docid}.${ext}";
        file_put_contents($fname, $raw);
        if ($isDownload) {
            $this->setSession('where', $fname);
            $this->client->setFaxReceived($apiResponse->JobId);
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
        if (!$this->getSession('pid', '')) {
            $pid = $this->getRequest('patient_id');
            $this->setSession('pid', $pid);
        } else {
            $pid = $this->getSession('pid', '');
        }

        return null;
    }
}
