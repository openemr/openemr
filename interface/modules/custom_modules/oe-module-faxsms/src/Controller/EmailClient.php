<?php

/**
 * Email Controller
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2023 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS\Controller;

use MyMailer;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\FaxSMS\Exception\EmailSendFailedException;
use OpenEMR\Modules\FaxSMS\Exception\InvalidEmailAddressException;
use OpenEMR\Modules\FaxSMS\Exception\SmtpNotConfiguredException;
use PHPMailer\PHPMailer\Exception;

class EmailClient extends AppDispatch
{
    public static $timeZone;
    public $baseDir;
    public $uriDir;
    public $serverUrl;
    public $credentials;
    public string $portalUrl;
    protected CryptoInterface $crypto;
    private readonly bool $smtpEnabled;

    public function __construct()
    {
        if (empty(OEGlobalsBag::getInstance()->get('oe_enable_email') ?? null)) {
            throw new \RuntimeException(xlt("Access denied! Module not enabled"));
        }
        $this->crypto = ServiceContainer::getCrypto();
        $this->baseDir = OEGlobalsBag::getInstance()->get('temporary_files_dir');
        $this->uriDir = OEGlobalsBag::getInstance()->get('OE_SITE_WEBROOT');
        $this->smtpEnabled = !empty(OEGlobalsBag::getInstance()->get('SMTP_HOST') ?? null);
        parent::__construct();
    }

    /**
     * @return array|mixed
     */
    public function getCredentials(): mixed
    {
        $credentials = AppDispatch::getSetup();

        $this->sid = $credentials['username'];
        $this->appKey = $credentials['appKey'];
        $this->appSecret = $credentials['appSecret'];
        $this->serverUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
        $this->uriDir = $this->serverUrl . $this->uriDir;

        return $credentials;
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
        // dummy function
        return text("Not implemented");
    }

    /**
     * @param $acl
     * @return int
     */
    public function authenticate($acl = ['patients', 'appt']): int
    {
        [$s, $v] = $acl;
        return $this->verifyAcl($s, $v);
    }

    /**
     * @return string
     */
    public function sendEmail(): string
    {
        $statusMsg = xlt("Email Requests") . "<br />";
        $body = $this->getRequest('comments', '');
        $email = $this->getRequest('email');
        $hasEmail = $this->validEmail($email);
        $subject = $this->getRequest('subject', xl("Private confidential message"));
        $user = $this::getLoggedInUser();
        $htmlContent = $this->getRequest('html_content', '');
        $from_name = ($user['fname'] ?? '') . ' ' . ($user['lname'] ?? '');
        if (!$hasEmail) {
            return js_escape(xlt("Error: Missing email address. Try again."));
        }
        $statusMsg .= $this->mailEmail($email, $from_name, $body, $subject, $htmlContent);
        return js_escape($statusMsg);
    }

    /**
     * @throws Exception
     */
    public function emailDocument($email, $body, $file, array $user = []): string
    {
        $from_name = ($user['fname'] ?? '') . ' ' . ($user['lname'] ?? '');
        $desc = xlt("Comment") . ":\n" . text($body) . "\n" . xlt("This email has an attached document.");
        $mail = new MyMailer();
        $from_name = text($from_name);
        $from = OEGlobalsBag::getInstance()->get("practice_return_email_path");
        $mail->AddReplyTo($from, $from_name);
        $mail->SetFrom($from, $from);
        $mail->AddAddress($email, $email);
        $mail->Subject = xlt("Forwarded Fax Document");
        $mail->Body = $desc;
        $mail->AddAttachment($file);

        return $mail->Send() ? xlt("Email successfully sent.") : xlt("Error: Email failed") . text($mail->ErrorInfo);
    }

    /**
     * @throws InvalidEmailAddressException
     * @throws SmtpNotConfiguredException
     * @throws EmailSendFailedException
     * @throws Exception
     */
    public function emailReminder($email, $body): void
    {
        if (!$this->validEmail($email)) {
            throw new InvalidEmailAddressException("Missing valid email address");
        }
        if (!$this->smtpEnabled) {
            throw new SmtpNotConfiguredException(sprintf(
                "SMTP not configured (SMTP_HOST=%s, SMTP_PORT=%s, SMTP_USER=%s)",
                OEGlobalsBag::getInstance()->get('SMTP_HOST') ?? 'NOT_SET',
                OEGlobalsBag::getInstance()->get('SMTP_PORT') ?? 'NOT_SET',
                !empty(OEGlobalsBag::getInstance()->get('SMTP_USER')) ? 'SET' : 'NOT_SET'
            ));
        }
        $from_name = text(OEGlobalsBag::getInstance()->get("Patient Reminder Sender Name") ?? 'UNK');
        $desc = text($body);
        $mail = new MyMailer();
        $from = text(OEGlobalsBag::getInstance()->get("practice_return_email_path"));
        $mail->AddReplyTo($from, $from_name);
        $mail->SetFrom($from, $from);
        $mail->AddAddress($email, $email);
        $mail->Subject = xlt("A Reminder for You");
        $mail->Body = $desc;

        if (!$mail->Send()) {
            throw new EmailSendFailedException($mail->ErrorInfo);
        }
    }
    /**
     * @return false|string
     */
    public function getUser(): bool|string
    {
        $id = $this->getRequest('uid');
        $query = "SELECT * FROM users WHERE id = ?";
        $result = sqlStatement($query, [$id]);
        $u = [];
        foreach ($result as $row) {
            $u[] = $row;
        }
        $u = $u[0];
        $r = [$u['fname'], $u['lname'], $u['fax'], $u['facility'], $u['email']];

        return json_encode($r);
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

    /**
     * @return string|bool
     */
    function fetchReminderCount(): string|bool
    {
        return "0"; // Caller expects a string result, not HTML;
        // TODO: Implement fetchReminderCount() method.
    }

    /**
     * @param $uiDateRangeFlag
     * @return false|string|null
     */
    public function fetchEmailList($uiDateRangeFlag = true): false|string|null
    {
        return "[]"; // Caller expects JSON result, not HTML;
    }
}
