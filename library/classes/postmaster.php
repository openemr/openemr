<?php

/**
 * MyMailer class
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2010 Open Support LLC
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\OEGlobalsBag;
use PHPMailer\PHPMailer\PHPMailer;

class MyMailer extends PHPMailer
{
    public $Mailer;
    public $SMTPAuth;
    public $Host;
    public $Username;
    public $Password;
    public $Port;
    public $CharSet;

    function __construct($throwExceptions = false)
    {
        // make sure we initiate our constructor here...
        parent::__construct($throwExceptions);

        $this->emailMethod();
    }

    /**
     * Checks if the MyMailer service is configured for mail with all of the host parameters defined
     *
     * @return bool
     */
    public static function isConfigured(): bool
    {
        $requiredKeys = [];
        if (OEGlobalsBag::getInstance()->get('EMAIL_METHOD') === "SMTP") {
            $requiredKeys = ['SMTP_HOST', 'SMTP_PORT'];
            if (!empty(OEGlobalsBag::getInstance()->get('SMTP_Auth'))) {
                $requiredKeys[] = 'SMTP_USER';
                $requiredKeys[] = 'SMTP_PASS';
            }
        }

        foreach ($requiredKeys as $key) {
            if (empty(OEGlobalsBag::getInstance()->get($key))) {
                return false;
            }
        }
        return true;
    }

    /**
     * Adds an email to the email queue
     *
     * @param string $sender
     * @param string $recipient
     * @param string $template
     * @param array  $templateData
     * @return bool
     */
    public static function emailServiceQueueTemplatedEmail(string $sender, string $recipient, string $subject, string $template, array $templateData): bool
    {
        if (empty($sender) || empty($recipient) || empty($subject) || empty($template) || empty($templateData)) {
            return false;
        }
        try {
            $body = json_encode($templateData);
            QueryUtils::sqlInsert("INSERT into `email_queue` (`sender`, `recipient`, `subject`, `body`,  `template_name`, `datetime_queued`) VALUES (?, ?, ?, ?, ?, NOW())", [$sender, $recipient, $subject, $body, $template]);
            return true;
        } catch (\Throwable $e) {
            ServiceContainer::getLogger()->error("Failed to add email to queue notification error " . $e->getMessage(), ['exception' => $e]);
        }
        return false;
    }


    /**
     * @param string $sender
     * @param string $recipient
     * @param string $subject
     * @param string $body
     * @return bool
     */
    public static function emailServiceQueue(string $sender, string $recipient, string $subject, string $body): bool
    {
        if (empty($sender) || empty($recipient) || empty($subject) || empty($body)) {
            return false;
        }

        sqlInsert("INSERT into `email_queue` (`sender`, `recipient`, `subject`, `body`, `datetime_queued`) VALUES (?, ?, ?, ?, NOW())", [$sender, $recipient, $subject, $body]);
        return true;
    }

    /**
     * @return void
     */
    public static function emailServiceRun(): void
    {
        QueryUtils::startTransaction();
        try {
            $res = sqlStatement("SELECT `id`, `sender`, `recipient`, `subject`, `body`, `template_name` FROM `email_queue` WHERE `sent` = 0");

            // send emails in the queue (to avoid race conditions, sent flag is rechecked before sending the email and then quickly set before proceeding to send the email)
            //  (first ensure the email method is properly configured)
            $emailMethodConfigured = self::isConfigured();
            while ($ret = sqlFetchArray($res)) {
                $sql = sqlQuery("SELECT `sent` FROM `email_queue` WHERE `id` = ?", [$ret['id']]);
                if ($sql['sent'] == 1) {
                    continue;
                }
                // Not sent, so set the sent flag, and then send the email
                sqlStatement("UPDATE `email_queue` SET `sent` = 1, `datetime_sent` = NOW() WHERE `id` = ?", [$ret['id']]);

                if ($emailMethodConfigured) {
                    try {
                        $twigContainer = new TwigContainer(null, OEGlobalsBag::getInstance()->getKernel());
                        $twig = $twigContainer->getTwig();
                        if (!empty($ret['template_name'])) {
                            $templateData = json_decode((string) $ret['body'], true);
                            // we make sure to prefix this so that people have to work inside the openemr namespace for email templates
                            $htmlBody = $twig->render($ret['template_name'] . ".html.twig", $templateData);
                            $textBody = $twig->render($ret['template_name'] . ".text.twig", $templateData);
                        } else {
                            $htmlBody = $twig->render("emails/system/system-notification.html.twig", ['message' => $ret['body']]);
                            $textBody = $twig->render("emails/system/system-notification.text.twig", ['message' => $ret['body']]);
                        }

                        // send the email. sjp fixed deprecated usage 03/05/2025
                        $mail = new MyMailer();
                        $mail->addReplyTo($ret['sender']);
                        $mail->setFrom($ret['sender']);
                        $mail->addAddress($ret['recipient']);
                        $mail->Subject = $ret['subject'];
                        $mail->msgHTML($htmlBody);
                        $mail->AltBody = $textBody;
                        $mail->isHTML(true);
                        if (!$mail->send()) {
                            $mail->smtpClose();
                            error_log("Failed to send email" . ': ' . errorLogEscape($mail->ErrorInfo));
                            throw new \Exception("Email sending failed" . ': ' . errorLogEscape($mail->ErrorInfo));
                        } else {
                            $mail->smtpClose();
                        }
                    } catch (\Throwable $e) {
                        ServiceContainer::getLogger()->error("Failed to generate email contents: " . $e->getMessage(), ['exception' => $e, 'id' => $ret['id']]);
                        throw $e; // Ensure rollback in case of failure
                    }
                } else {
                    error_log("Email method not configured");
                    throw new \Exception("Email method not configured");
                }
            }
            // Success so Commit transaction.
            QueryUtils::commitTransaction();
        } catch (\Throwable $e) {
            // Failed so Rollback transaction.
            QueryUtils::rollbackTransaction();
            ServiceContainer::getLogger()->error("Failed to send email" . ': ' . $e->getMessage(), ['exception' => $e]);
            // So we can still send, reset previously set sent flag since failed to send email
            sqlStatement("UPDATE `email_queue` SET `sent` = 0, `datetime_sent` = null WHERE `id` = ?", [$ret['id']]);
            // set the error flag and message
            sqlStatement("UPDATE `email_queue` SET `error` = 1, `error_message`= ?, `datetime_error` = NOW() WHERE `id` = ?", [$e->getMessage(), $ret['id']]);
        }
    }

    /**
     * @return void
     */
    function emailMethod(): void
    {
        // OpenEMR is hardcoded to UTF-8 (see interface/globals.php). Set it
        // explicitly rather than reading the legacy $HTML_CHARSET global,
        // which is not reliably defined in every entry point. PHPUnit, for
        // example, loads its bootstrap via `include_once` inside a method
        // scope, so the `$HTML_CHARSET = "UTF-8"` assignment in globals.php
        // never escapes to global scope there. A null CharSet makes PHPMailer
        // emit encoded-word subject headers with an empty charset declaration
        // (=??Q?...?=) and triggers strlen(null) / strtolower(null)
        // deprecations from PHPMailer on PHP 8.2+.
        $this->CharSet = PHPMailer::CHARSET_UTF8;
        switch (OEGlobalsBag::getInstance()->get('EMAIL_METHOD')) {
            case "PHPMAIL":
                $this->Mailer = "mail";
                break;
            case "SMTP":
                $this->Mailer = "smtp";
                $this->SMTPAuth = OEGlobalsBag::getInstance()->get('SMTP_Auth');
                $this->Host = OEGlobalsBag::getInstance()->getString('SMTP_HOST');
                $this->Username = OEGlobalsBag::getInstance()->getString('SMTP_USER');
                $cryptoGen = ServiceContainer::getCrypto();
                $this->Password = $cryptoGen->decryptStandard(OEGlobalsBag::getInstance()->getString('SMTP_PASS'));
                $this->Port = OEGlobalsBag::getInstance()->getInt('SMTP_PORT');
                $this->SMTPSecure = OEGlobalsBag::getInstance()->get('SMTP_SECURE');
                break;
            case "SENDMAIL":
                $this->Mailer = "sendmail";
                break;
        }
    }
}
