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
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Modules\FaxSMS\EtherFax\EtherFaxClient;
use OpenEMR\Modules\FaxSMS\EtherFax\FaxResult;
use Symfony\Component\HttpClient\HttpClient;

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
        if (empty($GLOBALS['oefax_enable_fax'] ?? null)) {
            throw new \RuntimeException(xlt("Access denied! Module not enabled"));
        }
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

    /**
     * @return bool|string
     */
    public function fetchReminderCount(): bool|string
    {
        $c = 0;
        while (1) {
            $fax = $this->client->getNextUnreadFax(true);
            if (empty($fax)) {
                break;
            }
            $c++;
            // insert to queue
            if (!empty($fax->JobId)) {
                $this->insertFaxQueue($fax);
                // mark fax received. as good as delete.
                $this->client->setFaxReceived($fax->JobId);
            }
        }

        $cnt = $this->fetchQueueCount();

        return json_encode($cnt);
    }

    /**
     * @return string
     */
    public function faxProcessUploads(): string
    {
        if (!empty($_FILES)) {
            $name = $_FILES['fax']['name'];
            $tmp_name = $_FILES['fax']['tmp_name'];
        } else {
            return 'Error:';
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
        $n = preg_replace('/[^0-9]/', '', $number);
        if (stripos($n, '1') === 0) {
            $n = '+' . $n;
        } elseif (!empty($n)) {
            $n = '+1' . $n;
        }
        if (!$this->validatePhone($n)) {
            $n = '';
        }
        return $n;
    }

    /**
     * @param $n
     * @return bool|int
     */
    public function validatePhone($n): bool|int
    {
        $regEx = "/^\+[1-9]\d{10,14}$/";
        return preg_match($regEx, $n);
    }

    /**
     * Credit to Stephen Neilson
     *
     * @param $email
     * @return bool
     */
    private function validEmail($email): bool
    {
        if (preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-\+]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i", $email)) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function forwardFax(): string
    {
        $fax = $content = $filepath = null;
        $statusMsg = xlt("Forwarding Requests") . "<br />";
        $comment = $this->getRequest('comments');
        $jobId = $this->getRequest('docid');
        $email = $this->getRequest('email');
        $faxNumber = $this->formatPhone($this->getRequest('phone'));
        $hasEmail = $this->validEmail($email);
        $smtpEnabled = !empty($GLOBALS['SMTP_PASS'] ?? null) && !empty($GLOBALS["SMTP_USER"] ?? null);
        $from = $this->formatPhone($this->credentials['phone']);
        $user = $this::getLoggedInUser();
        $csid = $user['facility'];
        $tag = xlt("Forwarded");
        try {
            if (!$hasEmail && empty($faxNumber)) {
                return js_escape(xlt("Error: Nothing to forward. Try again."));
            }
            if ($hasEmail || !empty($faxNumber)) {
                $fax = $this->fetchFaxFromQueue($jobId);
                if (empty($fax)) {
                    $statusMsg .= 'Error: ' . xlt("Fax fetch failed.");
                    return js_escape($statusMsg);
                }
                $content = $fax->FaxImage;
                if (!file_exists($this->baseDir . '/send')) {
                    mkdir($this->baseDir . '/send', 0777, true);
                }
                $filepath = $this->baseDir . "/send/" . ($jobId . '.pdf');
                file_put_contents($filepath, base64_decode($content));
            }
            if ($hasEmail) {
                if ($smtpEnabled) {
                    $statusMsg .= $this->emailDocument($email, $comment, $filepath, $user) . "<br />";
                } else {
                    $statusMsg .= 'Error: ' . xlt("Fax was not forwarded. A SMTP client is not set up in Config Notifications!.");
                    return js_escape($statusMsg);
                }
            }
            // forward to new fax number.
            if ($faxNumber) {
                $fax = $this->client->sendFax($faxNumber, $filepath, null, $from, $csid, $tag, false);
                if (!$fax->FaxResult) {
                    $statusMsg .= 'Error: ' . $fax->Message . ' ' . FaxResult::getFaxResult($fax->Result);
                    // give up
                    return js_escape($statusMsg);
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
                $error = FaxResult::getFaxResult($status->FaxResult);
                if ($status->FaxResult ?? null) {
                    $statusMsg .= 'Error: ' . $error;
                    return js_escape($statusMsg);
                } else {
                    $statusMsg .= xlt("Successfully forwarded fax to") . ' ' . text($faxNumber) . "<br />";
                }
            }
            if ($filepath) {
                unlink($filepath);
            }
            // TODO TBD Should fax be deleted after being forwarded? For now no.
            /*$this->setFaxDeleted($jobId);
            $statusMsg .= xlt("Fax Deleted.");*/
            $statusMsg .= xlt("Fax was not deleted for further processing.");
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $statusMsg = 'Error: ' . $message;
        }

        return js_escape($statusMsg);
    }

    /**
     * @return string|void
     */
    public function getPending()
    {
        $dateFrom = trim($this->getRequest('datefrom'));
        $dateTo = trim($this->getRequest('dateto'));

        if (!$this->authenticate()) {
            return $this->authErrorDefault;
        };
        $pull = $this->fetchReminderCount();
        try {
            // dateFrom and dateTo
            $timeFrom = 'T00:00:01';
            $timeTo = 'T23:59:59';
            $dateFrom = date("Y-m-d H:i:s", strtotime(($dateFrom . $timeFrom)));
            $dateTo = date("Y-m-d H:i:s", strtotime(($dateTo . $timeTo)));
            ;
            $faxStore = $this->fetchFaxQueue($dateFrom, $dateTo);
            $responseMsgs = [];
            $responseMsgs[2] = xlt('Not Implemented');
            $direction = 'inbound';
            foreach ($faxStore as $faxDetails) {
                $id = $faxDetails->JobId;
                $record_id = $faxDetails->RecordId;
                $ReceivedOn = $faxDetails->ReceivedOn;
                $faxDate = strtotime($ReceivedOn . ' UTC');
                $to = $faxDetails->CalledNumber;
                $from = $faxDetails->CallingNumber;
                $params = $faxDetails->DocumentParams;
                $form = '';
                $docType = null;
                $id_esc = text($id);
                $showFlag = 0;
                $recog = $faxDetails->AnalyzeFormResult->AnalyzeResult->DocumentResults;
                foreach ($recog as $r) {
                    $details = null;
                    $form = "<tr id='$id_esc' class='d-none collapse-all'><td colspan='12'>\n" .
                        "<div class='container table-responsive'>\n" .
                        "<table class='table table-sm table-bordered table-dark'>\n";
                    $form .= "<thead><tr><th>\n" .
                        xlt("Parameter") . "\n</th><th>\n" .
                        xlt("Value") . "</th><th>\n" .
                        xlt("Confidence") . " : " . text($r->DocTypeConfidence * 100) .
                        "\n</th></tr></thead>\n";
                    $form .= "<tbody>\n";
                    $parse = $this->parseValidators($r->Fields);
                    $pid_assumed = '';
                    if (!empty($parse['DOB']) && !empty($parse['fname']) && !empty($parse['lname'])) {
                        $pid_assumed = sqlQuery(
                            "Select pid From patient_data Where fname = ? And lname = ? And DOB = ?",
                            array($parse['fname'], $parse['lname'], date("Y-m-d", strtotime(($parse['DOB']))))
                        )['pid'];
                    }
                    $pid_assumed = $pid_assumed ?: 'No';
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
                        $table[$field->Name] = ['name' => $field->Name, 'value' => $field->Text];
                        $details['dob'] = $field->Name == "Patient Date of Birth" ? $field->Text : $details['dob'];
                        if (preg_match('/(Patient First Name)(Patient Last Name)/', $field->Name)) {
                            $details['name'][$field->Name] = $field->Text;
                        }
                        $details['gender'] = $field->Text == "selected" && (stripos($field->Name, 'Male') !== false) ? 'Male' : $details['gender'];
                        $details['gender'] = $field->Text == "selected" && (stripos($field->Name, 'Female') !== false) ? 'Female' : $details['gender'];
                    }
                    $form .= "</tbody>\n</table>\n</div>\n</td>\n</tr>\n";
                }
                $parse = json_encode($parse);
                $patientLink = "<a role='button' href='javascript:void(0)' onclick=\"createPatient(event, " . attr_js($id) . ", " . attr_js($record_id) . ", " . attr_js($parse) . ")\"> <i class='fa fa-chart-simple mr-2' title='" . xla("Chart fax or Create patient and chart fax to documents.") . "'></i></a>";
                $messageLink = "<a role='button' href='javascript:void(0)' onclick=\"notifyUser(event, " . attr_js($id) . ", " . attr_js($record_id) . ", " . attr_js(($pid ?? 0)) . ")\"> <i class='fa fa-paper-plane mr-2' title='" . xla("Notify a user and attach this fax to message.") . "'></i></a>";
                $downloadLink = "<a role='button' href='javascript:void(0)' onclick=\"getDocument(event, null, " . attr_js($id) . ", 'true')\"> <i class='fa fa-file-download mr-2' title='" . xla("Download and delete fax") . "'></i></a>";
                $viewLink = "<a role='button' href='javascript:void(0)' onclick=\"getDocument(event, null, " . attr_js($id) . ", 'false')\"> <i class='fa fa-file-pdf mr-2' title='" . xla("View fax document") . "'></i></a>";
                $deleteLink = "<a role='button' href='javascript:void(0)' onclick=\"getDocument(event, null, " . attr_js($id) . ", 'false', 'true')\"> <i class='text-danger fa fa-trash mr-2' title='" . xla("Delete this fax document") . "'></i></a>";
                $forwardLink = "<a role='button' href='javascript:void(0)' onclick=\"forwardFax(event, " . attr_js($id) . ")\"> <i class='fa fa-forward mr-2' title='" . xla("Forward fax to new fax recipient or email attachment.") . "'></i></a>";
                $detailLink = '';
                if ($showFlag) {
                    $showFlag = text($showFlag) . ' ' . xlt("Items");
                    $detailLink = "<a role='button' href='javascript:void(0)' class='btn btn-link fa fa-eye' onclick='toggleDetail(\"#$id_esc\")' " . xla("") . "'></a>$showFlag";
                }
                $faxFormattedDate = date('M j, Y g:i:sa T', $faxDate);
                $docLen = text(round($params->Length / 1000, 2)) . "KB";
                if (strtolower($direction) == "inbound") {
                    $responseMsgs[0] .= "<tr><td>" . text($faxFormattedDate) .
                        "</td><td>" . text($from) . "</td><td>" . text($to) .
                        "</td><td>" . text($faxDetails->PagesReceived) .
                        "</td><td>" . text($docLen) .
                        "</td><td class='text-left'>" . $detailLink .
                        "</td><td class='text-center'>" . text($pid_assumed) .
                        "</td><td class='text-left'>" . $patientLink . $messageLink . $forwardLink . $viewLink . $downloadLink . $deleteLink .
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
     * Endpoint for fax view setup.
     * @return string
     */
    public function viewFax(): string
    {
        $formatted_document = null;
        $docid = $this->getRequest('docid');
        $isDownload = $this->getRequest('download');
        $isDownload = $isDownload == 'true' ? 1 : 0;
        $isDelete = $this->getRequest('delete');

        if ($this->authenticate() !== 1) {
            return $this->authErrorDefault;
        }

        $faxStoreDir = $this->baseDir;

        try {
            if (is_numeric($docid)) {
                $apiResponse = $this->fetchFaxFromQueue(null, $docid);
            } else {
                $apiResponse = $this->fetchFaxFromQueue($docid);
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $r = "Error: Retrieving Fax:\n" . $message;
            return $r;
        }
        if (!empty($isDelete && !empty($apiResponse->JobId ?? null))) {
            $this->setFaxDeleted($apiResponse->JobId);
            return json_encode('success');
        }
        $faxImage = $apiResponse->FaxImage;
        $raw = base64_decode($faxImage);
        $c_header = $apiResponse->DocumentParams->Type;
        if ($c_header == 'application/pdf') {
            $ext = 'pdf';
            $type = 'Fax';
            $formatted_document = 'data:application/pdf;base64, ' . $faxImage;
        } elseif ($c_header == 'image/tiff' || $c_header == 'image/tif') {
            $ext = 'tiff';
            $type = 'Fax';
            $formatted_document = 'data:image/tiff;base64, ' . $faxImage;
        } else {
            $ext = 'txt';
            $type = 'Text';
            $formatted_document = "data:text/plain, " . $faxImage;
        }
        // Set up to download file.
        if ($isDownload) {
            if (!file_exists($faxStoreDir) && !mkdir($faxStoreDir, 0777, true) && !is_dir($faxStoreDir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $faxStoreDir));
            }
            $file_name = "${faxStoreDir}/${type}_${docid}.${ext}";
            file_put_contents($file_name, $raw);
            $this->setSession('where', $file_name);
            $this->setFaxDeleted($apiResponse->JobId);
            return json_encode($file_name);
        }
        // base64 formatted for view in iframe.
        return json_encode(['base64' => $faxImage, 'mime' => $c_header]);
    }

    /**
     * @param $content
     * @return void
     */
    public function disposeDoc($content = ''): void
    {
        $where = $this->getRequest('file_path', null);
        if (empty($where)) {
            $where = $this->getSession('where');
        }
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
        $r = array($u['fname'], $u['lname'], $u['fax'], $u['facility'], $u['email']);

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
     * @param $faxDetails
     * @return int
     */
    public function insertFaxQueue($faxDetails): int
    {
        $account = $this->credentials['account'];
        $uid = $_SESSION['authUserID'];
        $jobId = $faxDetails->JobId;
        $to = $faxDetails->CalledNumber;
        $from = $faxDetails->CallingNumber;
        $received = date('Y-m-d H:i:s', strtotime($faxDetails->ReceivedOn . ' UTC'));
        $docType = $faxDetails->DocumentParams->Type;
        $details_encoded = json_encode($faxDetails); // is object
        $binds = array($uid, $account, $jobId, $received, $from, $to, $docType, $details_encoded);

        $sql = "INSERT INTO `oe_faxsms_queue` (`id`, `uid`, `account`, `job_id`, `date`, `receive_date`, `calling_number`, `called_number`, `mime`, `details_json`) VALUES (NULL, ?, ?, ?, current_timestamp(), ?, ?, ?, ?, ?)";

        return sqlInsert($sql, $binds);
    }

    /**
     * @param $start
     * @param $end
     * @return array
     */
    public function fetchFaxQueue($start, $end): array
    {
        $rows = [];
        $res = sqlStatement("SELECT `id`, `details_json` FROM `oe_faxsms_queue` WHERE `deleted` = '0' AND (`receive_date` > ? AND `receive_date` < ?)", [$start, $end]);
        while ($row = sqlFetchArray($res)) {
            $detail = json_decode($row['details_json']);
            $detail->RecordId = $row['id'];
            $rows[] = $detail;
        }

        return $rows;
    }

    /**
     * @param $jobId
     * @param $id
     * @return mixed
     */
    public function fetchFaxFromQueue($jobId, $id = null)
    {
        if (!empty($jobId)) {
            $row = sqlQuery("SELECT `id`, `details_json` FROM `oe_faxsms_queue` WHERE `job_id` = ? LIMIT 1", [$jobId]);
        } else {
            $row = sqlQuery("SELECT `id`, `details_json` FROM `oe_faxsms_queue` WHERE `id` = ? LIMIT 1", [$id]);
        }
        $detail = json_decode($row['details_json']);
        $detail->RecordId = $row['id'];

        return $detail;
    }

    /**
     * @return int
     */
    public function fetchQueueCount(): int
    {
        return  (int)sqlQuery("SELECT COUNT(id) as count FROM `oe_faxsms_queue` WHERE deleted = 0")['count'] ?? 0;
    }

    /**
     * @param $jobId
     * @return bool|array|null
     */
    public function setFaxDeleted($jobId): bool|array|null
    {
        return sqlQuery("UPDATE `oe_faxsms_queue` SET `deleted` = '1' WHERE `job_id` = ?", [$jobId]);
    }

    /**
     * @return string
     */
    public function chartDocument(): string
    {
        $pid = $this->getRequest('pid');
        $docid = $this->getRequest('docid');
        $fileName = $this->getRequest('file_name');
        $mime = $this->getRequest('mime');

        $result = $this->chartFaxDocument($pid, $docid, $fileName);

        return $result;
    }

    /**
     * @param $pid
     * @param $docid
     * @param $fileName
     * @return string
     */
    public function chartFaxDocument($pid, $docid, $fileName = null): string
    {
        $catid = sqlQuery("SELECT id FROM `categories` WHERE `name` = 'FAX'")['id'];
        if (empty($catid)) {
            $catid = sqlQuery("SELECT id FROM `categories` WHERE `name` = 'Medical Record'")['id'];
        }
        $fax = $this->fetchFaxFromQueue($docid);
        $mime = $fax->DocumentParams->Type;
        if ($mime == 'application/pdf') {
            $ext = '.pdf';
        } elseif ($mime == 'image/tiff' || $mime == 'image/tif') {
            $ext = '.tiff';
        } else {
            $ext = '.txt';
        }
        if (empty($fileName)) {
            $fileName = xlt("fax") . '_' . text($docid) . $ext;
        }
        $content = base64_decode($fax->FaxImage);
        $document = new Document();
        $result = $document->createDocument(
            $pid,
            $catid,
            $fileName,
            $mime,
            $content
        );
        if (!empty($result)) {
            $err = xlt("Error: Failed to save document. Category Fax");
            error_log($err  . ' ' . text($result));
            return $err;
        } else {
            $result = xlt("Chart Success");
        }
        return $result;
    }

    /**
     * @param $source
     * @return array
     */
    public function parseValidators($source): array
    {
        $rtn = null;
        $rtn['fname'] = '';
        $rtn['lname'] = '';
        $rtn['DOB'] = '';
        $rtn['sex'] = '';
        $val['fname'] = array("First Name", "first", "Patient.name", "Patient.given");
        $val['lname'] = array("Last Name", "last", "Patient.name", "Patient.family");
        $val['DOB'] = array("dob", "Date of Birth", "Birth", "Birthdate", "Patient.birthDate");
        $val['sex'] = array("gender", "sex", "male", "female", "Sexual Orientation", "Patient.gender");
        foreach ($source as $src) {
            foreach ($val as $k => $v) {
                foreach ($v as $s) {
                    if (stripos($src->Name, $s) !== false) {
                        if ($k == "sex") {
                            $src->Text = ucfirst($src->Text);
                            if (stripos($src->Name, 'Male') !== false) {
                                $src->Text = 'Male';
                            }
                            if (stripos($src->Name, 'Female') !== false) {
                                $src->Text = 'Female';
                            }
                            switch ($src->Text) {
                                case 'M':
                                    $src->Text = 'Male';
                                    break;
                                case 'F':
                                    $src->Text = 'Female';
                                    break;
                            }
                        }
                        $rtn[$k] = $src->Text;
                    }
                }
            }
        }

        return $rtn;
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
