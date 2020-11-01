<?php

/** @package    verysimple::Email */

/**
 * Generic interface for connecting to a POP3 or IMAP Server
 * GetPart, GetMimeType & GetAttachments based on code by Kevin Steffer
 * <http://www.linuxscope.net/articles/mailAttachmentsPHP.html>
 *
 * @package verysimple::Email
 * @author VerySimple Inc.
 * @copyright 1997-2007 VerySimple, Inc.
 * @license LGPL
 * @version 1.1
 */
class Pop3Client
{
    private $mbox;
    private $do_delete = false;
    public static $VERSION = "1.1";
    function __construct()
    {
        if (! function_exists("imap_open")) {
            require_once('PEAR.php');
            if (! pear::loadExtension('imap')) {
                throw new Exception("Pop3Client: Unable to load imap extension");
            }
        }
    }

    /**
     * Opens a connection to the mail server
     *
     * @param string $user
     *          username
     * @param string $pass
     *          password
     * @param string $host
     *          host (ex mail.server.com)
     * @param int $port
     *          port (default = 110)
     * @param string $mbtype
     *          type of mailbox (default = pop3) (refer to www.php.net/imap_open)
     * @param string $mbfolder
     *          name of folder to read (default = INBOX)
     * @param int $options
     *          Flags for opening connect (refer to www.php.net/imap_open)
     * @param int $retries
     *          Number of times to retry the connection (default = 1)
     *
     */
    function Open($user, $pass, $host, $port = "110", $mbtype = "pop3", $mbfolder = "INBOX", $options = null, $retries = 1)
    {
        $this->mbox = imap_open("{" . $host . ":" . $port . "/" . $mbtype . "/notls}" . $mbfolder . "", $user, $pass, $options, $retries);
    }

    /**
     * Delete a message from the mailbox
     *
     * @param int $msgnum
     *          The id of the message within the mailbox
     *
     */
    function DeleteMessage($msgnum)
    {
        imap_delete($this->mbox, $msgnum);
        $this->do_delete = true;
    }

    /**
     * Close the connection to the mail server
     *
     * @param bool $empty_trash
     *          (default true) whether to empty the trash upon exit
     *
     */
    function Close($empty_trash = true)
    {
        if ($this->do_delete && $empty_trash) {
            imap_expunge($this->mbox);
        }

        imap_close($this->mbox);
    }

    /**
     * Returns the number of messages in the mail account folder
     *
     * @return int Number of messages
     *
     */
    function GetMessageCount()
    {
        $summary = $this->GetSummary();
        return $summary->Nmsgs;
    }

    /**
     * Returns an object with the following properties:
     * Date, Driver, Mailbox, Nmsgs, Recent
     *
     * @return stdClass
     */
    function GetSummary()
    {
        return imap_check($this->mbox);
    }

    /**
     * Returns an array containing summary information about the messages.
     * Use this function to list messages without downloading the entire
     * contents of each one
     *
     * @return Array
     */
    function GetQuickHeaders()
    {
        return imap_headers($this->mbox);
    }

    /**
     * Returns an object containing message header information
     *
     * @param int $msgno
     *          message number to retrieve
     * @return stdClass
     * @link http://www.php.net/imap_headerinfo
     */
    function GetHeader($msgno)
    {
        return imap_headerinfo($this->mbox, $msgno);
    }

    /**
     * Returns an array containing all files attached to message
     *
     * @param int $msgno
     *          The index of the message to retrieve
     * @param bool $include_raw_data
     *          The raw data is the actual contents of the attachment file. Setting this to FALSE will allow you to display the name, size, type, etc of all attachments without actually downloading the contents of the files. Use GetAttachmentRawData to retrieve the raw data for an individual attachment.
     * @return array An array of attachments
     *
     */
    function GetAttachments($msgno, $include_raw_data = true)
    {
        $struct = imap_fetchstructure($this->mbox, $msgno);
        $contentParts = count($struct->parts);
        $attachments = array ();

        if ($contentParts >= 2) {
            for ($i = 2; $i <= $contentParts; $i++) {
                $att [$i - 2] = imap_bodystruct($this->mbox, $msgno, $i);
                // these extra bits help us later...
                $att [$i - 2]->x_msg_id = $msgno;
                $att [$i - 2]->x_part_id = $i;
            }

            for ($k = 0; $k < sizeof($att); $k++) {
                if (strtolower($att [$k]->parameters [0]->value) == "us-ascii" && $att [$k]->parameters [1]->value != "") {
                    $attachments [$k] = $this->_getPartFromStruct($att [$k], $include_raw_data);
                } elseif (strtolower($att [$k]->parameters [0]->value) != "iso-8859-1") {
                    $attachments [$k] = $this->_getPartFromStruct($att [$k], $include_raw_data);
                }
            }
        }

        return $attachments;
    }
    private function _getPartFromStruct($struct, $include_raw_data)
    {
        // print_r($struct);
        $part = null;
        $part->msgnum = $struct->x_msg_id;
        $part->partnum = $struct->x_part_id;
        $part->filename = $struct->parameters [0]->value;
        $part->type = $this->GetPrimaryType($struct);
        $part->subtype = $struct->subtype;
        $part->mimetype = $this->GetMimeType($struct);
        $part->rawdata = (! $include_raw_data) ? null : $this->GetAttachmentRawData($struct->x_msg_id, $struct->x_part_id, $struct->encoding);
        return $part;
    }

    /**
     * Returns the raw data for an attachment.
     * The raw data is the actual file contents of an attachment.
     *
     * @param int $msgno
     *          message number
     * @param int $partnum
     *          which attachment to retrieve
     * @param int $encoding_id
     *          0 = no encoding, 3 = base64, 4 = qprint
     * @return mixed file contents of the attachment
     *
     */
    function GetAttachmentRawData($msgno, $partnum, $encoding_id = 0)
    {
        $content = imap_fetchbody($this->mbox, $msgno, $partnum);

        if ($encoding_id == 3) {
            return imap_base64($content);
        } elseif ($encoding_id == 4) {
            return imap_qprint($content);
        }

        return $content;
    }

    /**
     * Returns a text representation of the MIME type of the primary part of a message
     *
     * @param object $structure
     *          message structure
     * @return string
     *
     */
    function GetPrimaryType(&$structure)
    {
        $primary_mime_type = array (
                "TEXT",
                "MULTIPART",
                "MESSAGE",
                "APPLICATION",
                "AUDIO",
                "IMAGE",
                "VIDEO",
                "OTHER"
        );
        return $primary_mime_type [(int) $structure->type];
    }

    /**
     * Returns the MimeType of the primary part of the given message
     *
     * @param object $structure
     *          The message to inspect
     * @return string Mime Type
     *
     */
    function GetMimeType(&$structure)
    {
        if ($structure->subtype) {
            return $this->GetPrimaryType($structure) . '/' . $structure->subtype;
        }

        return "TEXT/PLAIN";
    }

    /**
     * Returns what is best determined to be the body text of the messages
     *
     * @param int $msgnum
     *          Message number in the mailbox
     * @param bool $prefer_html
     *          If an html version is provided, return that
     * @return string The contents of the message body
     *
     */
    function GetMessageBody($msgnum, $prefer_html = true)
    {
        if ($prefer_html) {
            $body = $this->GetPart($msgnum, "TEXT/HTML");
            $body = $body ? $body : $this->GetPart($msgnum, "TEXT/PLAIN");
        } else {
            $body = $this->GetPart($msgnum, "TEXT/PLAIN");
            $body = $body ? $body : $this->GetPart($msgnum, "TEXT/HTML");
        }

        return $body;
    }

    /**
     * Returns a single part of a message
     *
     * @param int $msg_number
     *          The message number in the mailbox
     * @param string $mime_type
     *          The mime type of the part to retrieve
     * @param object $structure
     *          The message structure
     * @param int $part_number
     *          The id of the part number within the message
     * @return object The message part, or false if no match exists
     *
     */
    function GetPart($msg_number, $mime_type, $structure = false, $part_number = false)
    {
        $stream = $this->mbox;
        $prefix = "";

        $structure = $structure ? $structure : imap_fetchstructure($stream, $msg_number);

        if ($structure) {
            if ($mime_type == $this->GetMimeType($structure)) {
                $part_number = $part_number ? $part_number : "1";
                $text = imap_fetchbody($stream, $msg_number, $part_number);

                if ($structure->encoding == 3) {
                    return imap_base64($text);
                } elseif ($structure->encoding == 4) {
                    return imap_qprint($text);
                } else {
                    return $text;
                }
            }

            if ($structure->type == 1) { /* multipart */
                while (list ( $index, $sub_structure ) = each($structure->parts)) {
                    if ($part_number) {
                        $prefix = $part_number . '.';
                    }

                    $data = $this->GetPart($msg_number, $mime_type, $sub_structure, $prefix . ($index + 1));

                    if ($data) {
                        return $data;
                    }
                }
            }
        }

        // no structure returned
        return false;
    }
}
