<?php

/** @package verysimple::Email */

/**
 * import supporting libraries
 */
require_once("Recipient.php");
require_once("verysimple/String/VerySimpleStringUtil.php");

define("MESSAGE_FORMAT_TEXT", 0);
define("MESSAGE_FORMAT_HTML", 1);

/**
 * Generic interface for sending Email
 *
 * @package verysimple::Email
 * @author VerySimple Inc.
 * @copyright 1997-2007 VerySimple, Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 2.2
 */
class EmailMessage
{
    public $Recipients;
    public $CCRecipients;
    public $BCCRecipients;
    public $From;
    public $ReplyTo;
    public $Subject;
    public $Body;
    public $Format;
    public $Attachments;
    public $Sender;
    public $Headers = array ();

    /** @var bool set to true to decode html entities from the sender, recipients and subject.  you must still decode the body manually if necessary, though */
    public $DecodeEntities;
    static $RECIPIENT_TYPE_TO = "";
    static $RECIPIENT_TYPE_BCC = "BCC";
    static $RECIPIENT_TYPE_CC = "CC";

    /** @var bool this controls the default value for DecodeEntities */
    static $DECODE_HTML_ENTITIES = true;
    function __construct()
    {
        $this->Recipients = array ();
        $this->CCRecipients = array ();
        $this->BCCRecipients = array ();
        $this->Attachments = array ();
        $this->Format = MESSAGE_FORMAT_TEXT;
        $this->DecodeEntities = self::$DECODE_HTML_ENTITIES;
    }

    /**
     * Set the sender of the message.
     * This will appear in some email clients
     * as "on behalf of"
     *
     * @param
     *          string
     */
    function SetSender($email)
    {
        $this->Sender = $email;
    }

    /**
     * Set the from address of the message
     *
     * @param
     *          string
     */
    function SetFrom($email, $name = "")
    {
        if ($this->DecodeEntities) {
            $email = $this->DecodeEntities($email);
            $name = $this->DecodeEntities($name);
        }

        $this->From = new Recipient($email, $name);
    }

    /**
     * Set the from address of the message
     *
     * @param
     *          string
     */
    function SetReplyTo($email, $name = "")
    {
        if ($this->DecodeEntities) {
            $email = $this->DecodeEntities($email);
            $name = $this->DecodeEntities($name);
        }

        $this->ReplyTo = new Recipient($email, $name);
    }

    /**
     * Strips any HTML entities from the value using VerySimpleStringUtil
     *
     * @param string $val
     * @return string
     */
    public function DecodeEntities($val)
    {
        return VerySimpleStringUtil::DecodeFromHTML($val, 'ISO-8859-1');
        // return utf8_decode( VerySimpleStringUtil::DecodeFromHTML($val,) );
    }

    /**
     * This is used by mailer to obtain the subject line, giving
     * EmailMessage a chance to do any necessary encoding
     */
    public function GetSubject()
    {
        return ($this->DecodeEntities) ? $this->DecodeEntities($this->Subject) : $this->Subject;
    }

    /**
     * This is used by mailer to obtain the body line, giving
     * EmailMessage a chance to do any necessary encoding
     */
    public function GetBody()
    {
        return $this->Body;
    }

    /**
     * Adds a custom MIME header that will be included in the outgoing email
     *
     * @param string $key
     * @param string $value
     */
    function AddHeader($key, $value)
    {
        $this->Headers [$key] = $value;
    }

    /**
     * Adds a recipient to the email message
     *
     * @param $email a
     *          single email or semi-colon/comma delimited list
     * @param $string a
     *          single email or semi-colon/comma delimited list
     * @param $string $RECIPIENT_TYPE_TO,
     *          $RECIPIENT_TYPE_CC or $RECIPIENT_TYPE_BCC (default = $RECIPIENT_TYPE_TO)
     */
    function AddRecipient($email, $name = "", $recipientType = "")
    {
        if ($this->DecodeEntities) {
            $email = $this->DecodeEntities($email);
            $name = $this->DecodeEntities($name);
        }

        $email = str_replace(",", ";", $email);
        $emails = explode(";", $email);

        $name = str_replace(",", ";", $name);
        $names = explode(";", $name);

        for ($i = 0; $i < count($emails); $i++) {
            $addr = trim($emails [$i]);
            $realname = isset($names [$i]) ? $names [$i] : $addr;

            if ($recipientType == self::$RECIPIENT_TYPE_CC) {
                $this->CCRecipients [] = new Recipient($addr, $realname);
            } elseif ($recipientType == self::$RECIPIENT_TYPE_BCC) {
                $this->BCCRecipients [] = new Recipient($addr, $realname);
            } else {
                $this->Recipients [] = new Recipient($addr, $realname);
            }
        }
    }

    /**
     * Attach a message to the email.
     *
     * @param
     *          the full path to the file to be attached
     */
    function AddAttachment($path)
    {
        $this->Attachments [] = $path;
    }
}
