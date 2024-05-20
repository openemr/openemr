<?php

/**
 * Fax SMS Module Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023-24 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General public License 3
 */

namespace OpenEMR\Modules\FaxSMS\Controller;

use Document;
use Exception;
use MyMailer;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Modules\FaxSMS\EtherFax\EtherFaxClient;
use OpenEMR\Modules\FaxSMS\EtherFax\FaxResult;
use OpenEMR\Services\ImageUtilities\HandleImageService;

class EtherFaxActions extends AppDispatch
{
    public static $timeZone;
    protected $baseDir;
    protected $uriDir;
    protected $serverUrl;
    protected $credentials;
    public string $portalUrl;
    protected $crypto;
    private EtherFaxClient $client;
    private mixed $appSecret;
    private mixed $sid;
    private mixed $appKey;

    public function __construct()
    {
        if (empty($GLOBALS['oefax_enable_fax'] ?? null)) {
            throw new \Exception(xlt("Access denied! Module not enabled"));
        }

        $this->crypto = new CryptoGen();
        $this->baseDir = $GLOBALS['temporary_files_dir'];
        $this->uriDir = $GLOBALS['OE_SITE_WEBROOT'];
        $this->credentials = $this->getCredentials();
        $this->client = new EtherFaxClient();
        $this->client->setCredentials(
            $this->credentials['account'] ?? '',
            $this->credentials['username'] ?? '',
            $this->credentials['password'] ?? '',
            $this->credentials['appKey'] ?? ''
        );
        $this->portalUrl = "https://clients.connect.etherfax.net/Account/Login";
        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function getCredentials(): mixed
    {
        $credentials = appDispatch::getSetup();

        $this->sid = $credentials['username'] ?? '';
        $this->appKey = $credentials['appKey'] ?? '';
        $this->appSecret = $credentials['appSecret'] ?? '';
        $this->serverUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
        $this->uriDir = $this->serverUrl . $this->uriDir;

        return $credentials;
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

    /**
     * @return string
     */
    public function fetchReminderCount(): string
    {
        $c = 0;
        while ($fax = $this->client->getNextUnreadFax(true)) {
            $c++;
            if (!empty($fax->JobId)) {
                $this->insertFaxQueue($fax);
                $this->client->setFaxReceived($fax->JobId);
            }
        }

        return json_encode($this->fetchQueueCount());
    }

    /**
     * @return string
     */
    public function faxProcessUploads(): string
    {
        if (empty($_FILES['fax']) || $_FILES['fax']['error'] !== UPLOAD_ERR_OK) {
            error_log('Error: No file uploaded or upload error.');
            return '';
        }

        $name = basename($_FILES['fax']['name']);
        $tmp_name = $_FILES['fax']['tmp_name'];
        $targetDir = $this->baseDir . '/send';

        if (!file_exists($targetDir) && !mkdir($targetDir, 0777, true)) {
            error_log('Error: Failed to create directory.');
            return '';
        }

        $filepath = $targetDir . "/" . $name;

        if (!move_uploaded_file($tmp_name, $filepath)) {
            error_log('Error: Failed to move uploaded file.');
            return '';
        }

        return $filepath;
    }

    /**
     * @return string
     */
    public function sendSMS(): string
    {
        return text("Not implemented");
    }

    /**
     * @return string
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function sendFax(): string
    {
        if (!$this->authenticate()) {
            return $this->authErrorDefault;
        }
        // needed args
        $isContent = $this->getRequest('isContent');
        $file = $this->getRequest('file');
        $docId = $this->getRequest('docId');
        $phone = $this->formatPhone($this->getRequest('phone'));
        $isDocuments = (int)$this->getRequest('isDocuments');
        $email = $this->getRequest('email');
        $hasEmail = $this->validEmail($email);
        $smtpEnabled = !empty($GLOBALS['SMTP_PASS'] ?? null) && !empty($GLOBALS["SMTP_USER"] ?? null);
        $user = $this::getLoggedInUser();
        $facility = substr($user['facility'], 0, 20);
        $csid = $this->formatPhone($this->credentials['phone']);
        $tag = $user['username'];

        if (empty($isContent)) {
            $file = str_replace(["file://", "\\"], ['', "/"], realpath($file));
            if (!$file) {
                return xlt('Error: No content');
            }
        }

        if ($isDocuments) {
            $file = (new Document($docId))->get_data();
        }

        if ($hasEmail && $smtpEnabled) {
            self::emailDocument($email, '', $file, $user);
        }

        try {
            $fax = $this->client->sendFax($phone, $file, null, $facility, $csid, $tag, $isDocuments, pathinfo($file, PATHINFO_BASENAME));
            if (!$fax->FaxResult) {
                return 'Error: ' . json_encode($fax->Message);
            }
            if ($fax->FaxResult == FaxResult::InProgress) {
                while (true) {
                    $status = $this->client->getFaxStatus($fax->JobId);
                    if (!$status || $status->FaxResult != FaxResult::InProgress) {
                        break;
                    }
                    sleep(5);
                }
            }
        } catch (Exception $e) {
            return 'Error: ' . json_encode($e->getMessage());
        }

        return $status->FaxResult ? 'Error: ' . json_encode(FaxResult::getFaxResult($status->FaxResult)) : json_encode(FaxResult::getFaxResult($status->FaxResult));
    }

    /**
     * @param $acl
     * @return int
     */
    public function authenticate($acl = ['admin', 'doc']): int
    {
        if (empty($this->credentials)) {
            $this->credentials = $this->getCredentials();
        }

        if (!$this->client->getFaxAccount()) {
            return 0;
        }

        self::$timeZone = $this->client->getFaxAccount()->TimeZone ?? null;

        return $this->verifyAcl($acl[0], $acl[1]);
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

        return $this->validatePhone($n) ? $n : '';
    }

    /**
     * @param $n
     * @return bool
     */
    public function validatePhone($n): bool
    {
        return preg_match("/^\+[1-9]\d{10,14}$/", $n);
    }

    /**
     * @return string
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function forwardFax(): string
    {
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

        $fax = $this->fetchFaxFromQueue($jobId);
        if (empty($fax)) {
            return js_escape('Error: ' . xlt("Fax fetch failed."));
        }

        $content = $fax->FaxImage;
        $c_header = $fax->DocumentParams->Type;
        $ext = $c_header == 'application/pdf' ? '.pdf' : ($c_header == 'image/tiff' || $c_header == 'image/tif' ? '.tiff' : '.txt');
        $filepath = $this->baseDir . "/send/" . ($jobId . $ext);

        if (!file_exists($this->baseDir . '/send')) {
            mkdir($this->baseDir . '/send', 0777, true);
        }

        file_put_contents($filepath, base64_decode($content));

        if ($hasEmail && $smtpEnabled) {
            $statusMsg .= self::emailDocument($email, $this->getRequest('comments'), $filepath, $user) . "<br />";
        }

        if ($faxNumber) {
            try {
                $fax = $this->client->sendFax($faxNumber, $filepath, null, $facility, $csid, $tag, false);
                if (!$fax->FaxResult) {
                    return js_escape('Error: ' . $fax->Message . ' ' . FaxResult::getFaxResult($fax->Result));
                }
                if ($fax->FaxResult == FaxResult::InProgress) {
                    while (true) {
                        $status = $this->client->getFaxStatus($fax->JobId);
                        if (!$status || $status->FaxResult != FaxResult::InProgress) {
                            break;
                        }
                        sleep(5);
                    }
                }
                $statusMsg .= xlt("Successfully forwarded fax to") . ' ' . text($faxNumber) . "<br />";
            } catch (Exception $e) {
                return js_escape('Error: ' . $e->getMessage());
            }
        }

        unlink($filepath);

        return js_escape($statusMsg);
    }

    /**
     * @return string|void
     */
    public function getPending()
    {
        if (!$this->authenticate()) {
            return $this->authErrorDefault;
        }

        $pull = $this->fetchReminderCount();
        $dateFrom = date("Y-m-d H:i:s", strtotime(($this->getRequest('datefrom') . 'T00:00:01')));
        $dateTo = date("Y-m-d H:i:s", strtotime(($this->getRequest('dateto') . 'T23:59:59')));
        $faxStore = $this->fetchFaxQueue($dateFrom, $dateTo);
        $responseMsgs = [0 => '', 2 => xlt('Not Implemented')];

        foreach ($faxStore as $faxDetails) {
            $id = $faxDetails->JobId;
            $record_id = $faxDetails->RecordId;
            $faxDate = strtotime($faxDetails->ReceivedOn . ' UTC');
            $to = $faxDetails->CalledNumber;
            $from = $faxDetails->CallingNumber;
            $params = $faxDetails->DocumentParams;
            $showFlag = 0;
            $recogized = $faxDetails->AnalyzeFormResult->AnalyzeResult->DocumentResults ?? [];

            $form = '';
            foreach ($recogized as $r) {
                $details = null;
                $form = "<tr id='" . text($id) . "' class='d-none collapse-all'><td colspan='12'><div class='container table-responsive'><table class='table table-sm table-bordered table-dark'>";
                $form .= "<thead><tr><th>" . xlt("Parameter") . "</th><th>" . xlt("Value") . "</th><th>" . xlt("Confidence") . " : " . text($r->DocTypeConfidence * 100) . "</th></tr></thead><tbody>";
                $parse = $this->parseValidators($r->Fields) ?? [];
                $pid_assumed = sqlQuery(
                    "Select pid From patient_data Where fname = ? And lname = ? And DOB = ?",
                    [$parse['fname'] ?? '', $parse['lname'] ?? '', date("Y-m-d", strtotime(($parse['DOB'] ?? '')))]
                )['pid'] ?? 'No';

                foreach ($r->Fields as $field) {
                    if ($field->Text == 'unselected' || empty($field->Text)) {
                        continue;
                    }
                    $showFlag++;
                    $form .= "<tr><td>" . text(str_replace(" - ", "-", $field->Name)) . "</td><td>" . text($field->Text) . "</td><td>" . text($field->Confidence * 100) . "</td></tr>";
                }
                $form .= "</tbody></table></div></td></tr>";
            }

            $patientLink = "<a role='button' href='javascript:void(0)' onclick=\"createPatient(event, " . attr_js($id) . ", " . attr_js($record_id) . ", " . attr_js(json_encode($parse ?? [])) . ")\"> <i class='fa fa-chart-simple mr-2' title='" . xla("Chart fax or Create patient and chart fax to documents.") . "'></i></a>";
            $messageLink = "<a role='button' href='javascript:void(0)' onclick=\"notifyUser(event, " . attr_js($id) . ", " . attr_js($record_id) . ", " . attr_js(($pid_assumed ?? 0)) . ")\"> <i class='fa fa-paper-plane mr-2' title='" . xla("Notify a user and attach this fax to message.") . "'></i></a>";
            $downloadLink = "<a role='button' href='javascript:void(0)' onclick=\"getDocument(event, null, " . attr_js($id) . ", 'true')\"> <i class='fa fa-file-download mr-2' title='" . xla("Download and delete fax") . "'></i></a>";
            $viewLink = "<a role='button' href='javascript:void(0)' onclick=\"getDocument(event, null, " . attr_js($id) . ", 'false')\"> <i class='fa fa-file-pdf mr-2' title='" . xla("View fax document") . "'></i></a>";
            $deleteLink = "<a role='button' href='javascript:void(0)' onclick=\"getDocument(event, null, " . attr_js($id) . ", 'false', 'true')\"> <i class='text-danger fa fa-trash mr-2' title='" . xla("Delete this fax document") . "'></i></a>";
            $forwardLink = "<a role='button' href='javascript:void(0)' onclick=\"forwardFax(event, " . attr_js($id) . ")\"> <i class='fa fa-forward mr-2' title='" . xla("Forward fax to new fax recipient or email attachment.") . "'></i></a>";
            $detailLink = $showFlag ? "<a role='button' href='javascript:void(0)' class='btn btn-link fa fa-eye' onclick='toggleDetail(\"#" . text($id) . "\")'></a>" . text($showFlag) . ' ' . xlt("Items") : '';

            $faxFormattedDate = date('M j, Y g:i:sa T', $faxDate);
            $docLen = text(round($params->Length / 1000, 2)) . "KB";
            $responseMsgs[0] .= "<tr><td>" . text($faxFormattedDate) . "</td><td>" . text($from) . "</td><td>" . text($faxDetails->RemoteId) . "</td><td>" . text($to) . "</td><td>" . text($faxDetails->PagesReceived) . "</td><td>" . text($docLen) . "</td><td class='text-left'>" . $detailLink . "</td><td class='text-center'>" . text($pid_assumed ?? '') . "</td><td class='text-left'>" . $patientLink . $messageLink . $forwardLink . $viewLink . $downloadLink . $deleteLink . "</td></tr>";
            $responseMsgs[0] .= $form;
        }

        if (empty($responseMsgs[0])) {
            $responseMsgs[0] = xlt("Currently inbox is empty.");
        }

        echo json_encode($responseMsgs);
        exit();
    }

    /**
     * @return string
     * @throws Exception
     */
    public function viewFax(): string
    {
        if ($this->authenticate() !== 1) {
            return $this->authErrorDefault;
        }

        $docId = $this->getRequest('docid');
        $isDownload = $this->getRequest('download') == 'true';
        $isDelete = $this->getRequest('delete');

        try {
            $apiResponse = is_numeric($docId) ? $this->fetchFaxFromQueue(null, $docId) : $this->fetchFaxFromQueue($docId);
        } catch (Exception $e) {
            return "Error: Retrieving Fax:\n" . $e->getMessage();
        }

        if ($isDelete && !empty($apiResponse->JobId)) {
            $this->setFaxDeleted($apiResponse->JobId);
            return json_encode('success');
        }

        $faxImage = $apiResponse->FaxImage;
        $c_header = $apiResponse->DocumentParams->Type;

        if ($c_header == 'image/tiff' || $c_header == 'image/tif') {
            $formattedImage = $this->formatFax($faxImage);
            $c_header = $formattedImage ? 'application/pdf' : 'image/tiff';
            $faxImage = $formattedImage ?: $faxImage;
        }

        $dataUrl = $c_header == 'application/pdf' ? 'data:application/pdf;base64,' . $faxImage : ($c_header == 'image/tiff' ? 'data:image/tiff;base64,' . $faxImage : 'data:text/plain,' . $faxImage);

        if ($isDownload) {
            $faxStoreDir = $this->baseDir;
            if (!file_exists($faxStoreDir) && !mkdir($faxStoreDir, 0777, true)) {
                throw new Exception(sprintf('Directory "%s" was not created', $faxStoreDir));
            }

            $file_name = "{$faxStoreDir}/Fax_{$docId}" . ($c_header == 'application/pdf' ? '.pdf' : ($c_header == 'image/tiff' ? '.tiff' : '.txt'));
            file_put_contents($file_name, base64_decode($faxImage));
            $this->setSession('where', $file_name);
            $this->setFaxDeleted($apiResponse->JobId);

            return json_encode(['base64' => $faxImage, 'mime' => $c_header, 'path' => $file_name]);
        }

        return json_encode(['base64' => $faxImage, 'mime' => $c_header]);
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
     * @return string
     */
    public function getUser(): string
    {
        $id = $this->getRequest('uid');
        $result = sqlStatement("SELECT * FROM users WHERE id = ?", [$id]);
        $user = sqlFetchArray($result);

        return json_encode([$user['fname'], $user['lname'], $user['fax'], $user['facility'], $user['email']]);
    }

    /**
     * @return string
     */
    public function getNotificationLog(): string
    {
        $fromDate = $this->getRequest('datefrom');
        $toDate = $this->getRequest('dateto');

        try {
            $query = "SELECT * FROM notification_log WHERE dSentDateTime > ? AND dSentDateTime < ?";
            $result = sqlStatement($query, [$fromDate, $toDate]);
            $rows = sqlFetchArray($result);
            $responseMsgs = '';

            foreach ($rows as $row) {
                $adate = $row['pc_eventDate'] . '::' . $row['pc_startTime'];
                $pinfo = str_replace("|||", " ", $row['patient_info']);
                $responseMsgs .= "<tr><td>" . text($row["pc_eid"]) . "</td><td>" . text($row["dSentDateTime"]) . "</td><td>" . text($adate) . "</td><td>" . text($pinfo) . "</td><td>" . text($row["message"]) . "</td></tr>";
            }
        } catch (Exception $e) {
            return 'Error: ' . text($e->getMessage()) . PHP_EOL;
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
        $details_encoded = json_encode($faxDetails);

        $sql = "INSERT INTO `oe_faxsms_queue` (`id`, `uid`, `account`, `job_id`, `date`, `receive_date`, `calling_number`, `called_number`, `mime`, `details_json`) VALUES (NULL, ?, ?, ?, current_timestamp(), ?, ?, ?, ?, ?)";

        return sqlInsert($sql, [$uid, $account, $jobId, $received, $from, $to, $docType, $details_encoded]);
    }

    /**
     * @param $start
     * @param $end
     * @return array
     */
    public function fetchFaxQueue($start, $end): array
    {
        $rows = [];
        $result = sqlStatement("SELECT `id`, `details_json`, `receive_date` FROM `oe_faxsms_queue` WHERE `deleted` = '0' AND (`receive_date` > ? AND `receive_date` < ?)", [$start, $end]);

        while ($row = sqlFetchArray($result)) {
            $detail = json_decode($row['details_json']);
            if (json_last_error()) {
                continue;
            }
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
    public function fetchFaxFromQueue($jobId, $id = null): mixed
    {
        $row = $jobId ? sqlQuery("SELECT `id`, `details_json` FROM `oe_faxsms_queue` WHERE `job_id` = ? LIMIT 1", [$jobId]) : sqlQuery("SELECT `id`, `details_json` FROM `oe_faxsms_queue` WHERE `id` = ? LIMIT 1", [$id]);
        $detail = json_decode($row['details_json']);
        $detail->RecordId = $row['id'];

        return $detail;
    }

    /**
     * @return int
     */
    public function fetchQueueCount(): int
    {
        return (int)sqlQuery("SELECT COUNT(id) as count FROM `oe_faxsms_queue` WHERE deleted = 0")['count'] ?? 0;
    }

    /**
     * @param $jobId
     * @return bool
     */
    public function setFaxDeleted($jobId): bool
    {
        return sqlQuery("UPDATE `oe_faxsms_queue` SET `deleted` = '1' WHERE `job_id` = ?", [$jobId]);
    }

    /**
     * @return string
     */
    public function chartDocument(): string
    {
        $pid = $this->getRequest('pid');
        $docId = $this->getRequest('docId');
        $fileName = $this->getRequest('file_name');
        $result = $this->chartFaxDocument($pid, $docId, $fileName);

        return $result;
    }

    /**
     * @param $pid
     * @param $docId
     * @param $fileName
     * @return string
     */
    public function chartFaxDocument($pid, $docId, $fileName = null): string
    {
        $catid = sqlQuery("SELECT id FROM `categories` WHERE `name` = 'FAX'")['id'] ?? sqlQuery("SELECT id FROM `categories` WHERE `name` = 'Medical Record'")['id'];
        $fax = $this->fetchFaxFromQueue($docId);
        $mime = $fax->DocumentParams->Type;
        $ext = $mime == 'application/pdf' ? '.pdf' : ($mime == 'image/tiff' || $mime == 'image/tif' ? '.tiff' : '.txt');
        $fileName = $fileName ?? xlt("fax") . '_' . text($docId) . $ext;
        $content = base64_decode($fax->FaxImage);
        $document = new Document();

        $result = $document->createDocument($pid, $catid, $fileName, $mime, $content);

        return $result ? xlt("Error: Failed to save document. Category Fax") : xlt("Chart Success");
    }

    /**
     * @param $source
     * @return string[]
     */
    public function parseValidators($source): array
    {
        $rtn = ['fname' => '', 'lname' => '', 'DOB' => '', 'sex' => ''];
        $val = [
            'fname' => ["First Name", "first", "Patient.name", "Patient.given"],
            'lname' => ["Last Name", "last", "Patient.name", "Patient.family"],
            'DOB' => ["dob", "Date of Birth", "Birth", "Birthdate", "Patient.birthDate"],
            'sex' => ["gender", "sex", "male", "female", "Sexual Orientation", "Patient.gender"]
        ];

        foreach ($source as $src) {
            foreach ($val as $k => $v) {
                foreach ($v as $s) {
                    if (stripos($src->Name, $s) !== false) {
                        if ($k == "sex") {
                            $src->Text = ucfirst($src->Text);
                            $src->Text = stripos($src->Name, 'Male') !== false ? 'Male' : (stripos($src->Name, 'Female') !== false ? 'Female' : ($src->Text == 'M' ? 'Male' : ($src->Text == 'F' ? 'Female' : $src->Text)));
                        }
                        $rtn[$k] = $src->Text;
                    }
                }
            }
        }

        return $rtn;
    }

    /**
     * @return void
     */
    protected function index()
    {
        if (!$this->getSession('pid', '')) {
            $this->setSession('pid', $this->getRequest('patient_id'));
        }
    }

    /**
     * @return mixed
     */
    public function sendEmail(): mixed
    {
        return null;
    }
}
