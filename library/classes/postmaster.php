<?php

/**
 * MyMailer class
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010 Open Support LLC
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Crypto\CryptoGen;
use PHPMailer\PHPMailer\PHPMailer;

class MyMailer extends PHPMailer
{
    var $Mailer;
    var $SMTPAuth;
    var $Host;
    var $Username;
    var $Password;
    var $Port;
    var $CharSet;

    function __construct($throwExceptions = false)
    {
        // make sure we initiate our constructor here...
        parent::__construct($throwExceptions);

        $this->emailMethod();
    }

    /**
     * Checks if the MyMailer service is configured for mail with all of the host parameters defined
     * @return bool
     */
    public static function isConfigured()
    {
        switch ($GLOBALS['EMAIL_METHOD']) {
            case "SMTP":
                $requiredKeys = ['SMTP_HOST', 'SMTP_PORT', 'SMTP_SECURE'];
                if ($GLOBALS['SMTP_Auth']) {
                    $requiredKeys[] = 'SMTP_USER';
                    $requiredKeys[] = 'SMTP_PASS';
                }
                break;
            default:
                $requiredKeys = [];
                break;
        }

        foreach ($requiredKeys as $key) {
            if (empty($GLOBALS[$key])) {
                return false;
            }
        }
        return true;
    }

    public static function emailServiceQueue(string $sender, string $recipient, string $subject, string $body): bool
    {
        if (empty($sender) || empty($recipient) || empty($subject) || empty($body)) {
            return false;
        }

        sqlInsert("INSERT into `email_queue` (`sender`, `recipient`, `subject`, `body`, `datetime_queued`) VALUES (?, ?, ?, ?, NOW())", [$sender, $recipient, $subject, $body]);
        return true;
    }

    public static function emailServiceRun(): void
    {
        // collect the queue
        $res = sqlStatement("SELECT `id`, `sender`, `recipient`, `subject`, `body` FROM `email_queue` WHERE `sent` = 0");

        // send emails in the queue (to avoid race conditions, sent flag is rechecked before sending the email and then quickly set before proceeding to send the email)
        //  (first ensure the email method is properly configured)
        $emailMethodConfigured = self::isConfigured();
        while ($ret = sqlFetchArray($res)) {
            $sql = sqlQuery("SELECT `sent` FROM `email_queue` WHERE `id` = ?", [$ret['id']]);
            if ($sql['sent'] == 1) {
                // Sent, so skip
            } else {
                // Not sent, so set the sent flag, and then send the email
                sqlStatement("UPDATE `email_queue` SET `sent` = 1, `datetime_sent` = NOW() WHERE `id` = ?", [$ret['id']]);

                if ($emailMethodConfigured) {
                    $mail = new MyMailer();
                    $email_subject = $ret['subject'];
                    $email_sender = $ret['sender'];
                    $email_address = $ret['recipient'];
                    $message = $ret['body'];
                    $mail->AddReplyTo($email_sender, $email_sender);
                    $mail->SetFrom($email_sender, $email_sender);
                    $mail->AddAddress($email_address);
                    $mail->Subject = $email_subject;
                    $mail->MsgHTML("<html><body><div class='wrapper'>" . text($message) . "</div></body></html>");
                    $mail->AltBody = $message;
                    $mail->IsHTML(true);
                    if (!$mail->Send()) {
                        sqlStatement("UPDATE `email_queue` SET `error` = 1, `error_message`= ?, , `datetime_error` = NOW() WHERE `id` = ?", [$mail->ErrorInfo, $ret['id']]);
                        error_log("Failed to send email notification through Mymailer emailServiceRun with error " . errorLogEscape($mail->ErrorInfo));
                    }
                } else {
                    sqlStatement("UPDATE `email_queue` SET `error` = 1, `error_message`= 'email method is not configured correctly', `datetime_error` = NOW() WHERE `id` = ?", [$ret['id']]);
                    error_log("Failed to send email notification through Mymailer since email method is not configured correctly");
                }
            }
        }
    }

    function emailMethod()
    {
        global $HTML_CHARSET;
        $this->CharSet = $HTML_CHARSET;
        switch ($GLOBALS['EMAIL_METHOD']) {
            case "PHPMAIL":
                $this->Mailer = "mail";
                break;
            case "SMTP":
                $this->Mailer = "smtp";
                $this->SMTPAuth = $GLOBALS['SMTP_Auth'];
                $this->Host = $GLOBALS['SMTP_HOST'];
                $this->Username = $GLOBALS['SMTP_USER'];
                $cryptoGen = new CryptoGen();
                $this->Password = $cryptoGen->decryptStandard($GLOBALS['SMTP_PASS']);
                $this->Port = $GLOBALS['SMTP_PORT'];
                $this->SMTPSecure = $GLOBALS['SMTP_SECURE'];
                break;
            case "SENDMAIL":
                $this->Mailer = "sendmail";
                break;
        }
    }
}
