<?php

/** @package    verysimple::Email */

/**
 * import supporting libraries
 */
require_once("phpmailer/class.phpmailer.php");
require_once("EmailMessage.php");
require_once("Recipient.php");

use PHPMailer\PHPMailer\PHPMailer;

define("MAILER_RESULT_FAIL", 0);
define("MAILER_RESULT_OK", 1);

define("MAILER_METHOD_SENDMAIL", "SENDMAIL");
define("MAILER_METHOD_SMTP", "SMTP");
define("MAILER_METHOD_MAIL", "MAIL");

/**
 * Generic interface for sending Email
 *
 * @package verysimple::Email
 * @author VerySimple Inc.
 * @copyright 1997-2007 VerySimple, Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 2.1
 */
class Mailer
{
    var $_log;
    var $_errors;
    var $Method;
    var $Path;
    var $AuthUsername;
    var $AuthPassword;
    var $Host;
    var $LangPath;

    /**
     * Constructor initializes the mailer object and prepares it for mailing
     *
     * If path is a SMTP connection string it may be entered in the format:
     * host.domain.com -or- username:password@host.domain.com
     *
     * @param
     *          $method
     * @param string $path
     *          (either file path to sendmail or SMTP connection string)
     */
    function __construct($method = MAILER_METHOD_SENDMAIL, $path = "/usr/sbin/sendmail")
    {
        $pair = explode("@", $path);

        if (count($pair) > 1) {
            $this->Path = $pair [1];
            $userpass = explode(":", $pair [0], 2);
            $this->AuthUsername = $userpass [0];
            $this->AuthPassword = count($userpass) > 1 ? $userpass [1] : '';
        } else {
            $this->Path = $path;
        }

        $this->Method = $method;

        $this->Reset();
        $this->LangPath = $this->_GetLangPath();
    }

    /**
     * Bare line feeds do not play nicely with email.
     * this strips them
     *
     * @param string $str
     */
    function FixBareLB($str)
    {
        $str = str_replace("\r\n", "\n", $str);
        return str_replace("\r", "\n", $str);
    }

    /**
     * This function attempts to locate the language file path for
     * PHPMailer because it's a whiney-ass bitch about finding it's
     * language file during unit testing
     *
     * @return string
     */
    private function _GetLangPath()
    {
        $lang_path = "";
        $paths = explode(PATH_SEPARATOR, get_include_path());

        foreach ($paths as $path) {
            if (file_exists($path . '/language/phpmailer.lang-en.php')) {
                $lang_path = $path . '/language/';
            }
        }

        return $lang_path;
    }

    /**
     * Send the message.
     * If MAILER_RESULT_FAIL is returned, use GetErrors() to
     * determine the problem.
     *
     * @param EmailMessage $message
     * @return int results of operation (MAILER_RESULT_OK | MAILER_RESULT_FAIL)
     */
    function Send($message)
    {
        $mailer = new PHPMailer();

        // this prevents problems with phpmailer not being able to locate the language path
        $mailer->SetLanguage("en", $this->LangPath);

        $mailer->From = $message->From->Email;
        $mailer->FromName = $message->From->RealName;
        if ($message->ReplyTo) {
            $mailer->AddReplyTo($message->ReplyTo->Email, $message->ReplyTo->RealName);
        }

        $mailer->Subject = $message->GetSubject();
        $mailer->Body = $this->FixBareLB($message->GetBody());
        $mailer->ContentType = ($message->Format == MESSAGE_FORMAT_TEXT) ? "text/plain" : "text/html";
        $mailer->Mailer = strtolower($this->Method);
        $mailer->Host = $this->Path;
        $mailer->Sendmail = $this->Path;

        // use authentication if necessary
        if ($this->AuthUsername) {
            $mailer->SMTPAuth = true;
            $mailer->Username = $this->AuthUsername;
            $mailer->Password = $this->AuthPassword;
        }

        // if custom headers are to be provided, include them in the message
        foreach ($message->Headers as $header_key => $header_val) {
            $mailer->AddCustomHeader($header_key . ': ' . $header_val);
        }

        if ($message->Sender) {
            $this->_log [] = "Adding Sender " . $message->Sender;

            // phpmailer accepts this but it seems to not work consistently..?
            // $mailer->Sender = $message->Sender;

            // instead add the dang headers ourselves
            $mailer->AddCustomHeader("Sender: " . $message->Sender);
            $mailer->AddCustomHeader("Return-Path: " . $message->Sender);
        }

        if (! $this->IsValid($mailer->From)) {
            $this->_errors [] = "Sender '" . $mailer->From . "' is not a valid email address.";
            return MAILER_RESULT_FAIL;
        }

        // add the recipients
        foreach ($message->Recipients as $recipient) {
            $this->_log [] = "Adding Recipient " . $recipient->RealName . " [" . $recipient->Email . "]";

            if (! $this->IsValid($recipient->Email)) {
                $this->_errors [] = "Recipient '" . $recipient->Email . "' is not a valid email address.";
                return MAILER_RESULT_FAIL;
            }

            $mailer->AddAddress($recipient->Email, $recipient->RealName);
        }

        foreach ($message->CCRecipients as $recipient) {
            $this->_log [] = "Adding CC Recipient " . $recipient->RealName . " [" . $recipient->Email . "]";

            if (! $this->IsValid($recipient->Email)) {
                $this->_errors [] = "CC Recipient '" . $recipient->Email . "' is not a valid email address.";
                return MAILER_RESULT_FAIL;
            }

            $mailer->AddCC($recipient->Email, $recipient->RealName);
        }

        foreach ($message->BCCRecipients as $recipient) {
            $this->_log [] = "Adding BCC Recipient " . $recipient->RealName . " [" . $recipient->Email . "]";

            if (! $this->IsValid($recipient->Email)) {
                $this->_errors [] = "BCC Recipient '" . $recipient->Email . "' is not a valid email address.";
                return MAILER_RESULT_FAIL;
            }

            $mailer->AddBCC($recipient->Email, $recipient->RealName);
        }

        $result = MAILER_RESULT_OK;

        $this->_log [] = "Sending message using " . $mailer->Mailer;

        ob_start(); // buffer output because class.phpmailer.php Send() is chatty and writes to stdout

        $fail = ! $mailer->Send();

        ob_end_clean(); // clear the buffer

        if ($fail || $mailer->ErrorInfo) {
            $result = MAILER_RESULT_FAIL;
            $this->_errors [] = trim(str_replace(array (
                    '<p>',
                    '</p>'
            ), array (
                    ', ',
                    ''
            ), $mailer->ErrorInfo));
        }

        return $result;
    }

    /**
     * returns true if the provided email appears to be valid
     *
     * @return bool
     */
    function IsValid($email)
    {
        return Recipient::IsEmailInValidFormat($email);
    }

    /**
     * Clears log and error
     */
    function Reset()
    {
        $this->_errors = array ();
        $this->_log = array ();
    }

    /**
     * Utility method to send a simple text email message
     *
     * @param string $to
     * @param string $from
     * @param string $subject
     * @param string $body
     */
    function QuickSend($to, $from, $subject, $body, $format = MESSAGE_FORMAT_TEXT)
    {
        $message = new EmailMessage();
        $message->SetFrom($from);
        $message->AddRecipient($to);
        $message->Subject = $subject;
        $message->Body = $body;
        $message->Format = $format;

        return $this->Send($message);
    }

    /**
     * Returns an array of errors that occured during the last attempt
     * to send a message
     *
     * @return Array
     */
    function GetErrors()
    {
        return $this->_errors;
    }

    /**
     * Returns a log of the last email transaction in array format
     *
     * @return Array
     */
    function GetLog()
    {
        return $this->_log;
    }
}
