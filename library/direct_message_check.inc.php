<?php

/**
 * Background receive function for phiMail Direct Messaging service.
 *
 * This script is called by the background service manager
 * at /library/ajax/execute_background_services.php
 *
 * Copyright (C) 2013, 2021 EMR Direct <https://www.emrdirect.com/>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  EMR Direct <https://www.emrdirect.com/>
 * @link    http://www.open-emr.org
 */

require_once(dirname(__FILE__) . "/pnotes.inc");
require_once(dirname(__FILE__) . "/documents.php");
require_once(dirname(__FILE__) . "/gprelations.inc.php");

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Events\Core\Sanitize\IsAcceptedFileFilterEvent;
use OpenEMR\Services\VersionService;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Connect to a phiMail Direct Messaging server
 */

function phimail_connect(&$phimail_error)
{

    if ($GLOBALS['phimail_enable'] == false) {
        $phimail_error = 'C1';
        return false; //for safety
    }

    $phimail_server = @parse_url($GLOBALS['phimail_server_address']);
    $phimail_username = $GLOBALS['phimail_username'];
    $cryptoGen = new CryptoGen();
    $phimail_password = $cryptoGen->decryptStandard($GLOBALS['phimail_password']);

    // if test mode is disabled we use the production cert, otherwise we use the test certificate.
    if (isset($GLOBALS['phimail_testmode_disabled']) && $GLOBALS['phimail_testmode_disabled'] == '1') {
        $phimail_cafile = $GLOBALS['fileroot'] . '/public/certs/phimail/phimail_server.pem';
    } else {
        $phimail_cafile = $GLOBALS['fileroot'] . '/public/certs/phimail/EMRDirectTestCA.pem';
        (new SystemLogger())->debug("running phimail_connect in test mode.  This should not be used for production", ['ca' => $phimail_cafile, 'testmode' => $GLOBALS['phimail_testmode_disabled']]);
    }
    if (!file_exists($phimail_cafile)) {
        $phimail_cafile = '';
    }

    $phimail_secure = true;
    switch ($phimail_server['scheme']) {
        case "tcp":
        case "http":
            $server = "tcp://" . $phimail_server['host'];
            $phimail_secure = false;
            break;
        case "https":
            $server = "ssl://" . $phimail_server['host']
                . ':' . $phimail_server['port'];
            break;
        case "ssl":
        case "sslv3":
        case "tls":
            $server = $GLOBALS['phimail_server_address'];
            break;
        default:
            $phimail_error = 'C2';
            (new SystemLogger())->error("phimail_connect failed to connect due to invalid scheme", ['error' => $phimail_error]);
            return false;
    }

    if ($phimail_secure) {
        $context = stream_context_create();
        if (
            $phimail_cafile != '' &&
            (!stream_context_set_option($context, 'ssl', 'verify_peer', true) ||
                !stream_context_set_option($context, 'ssl', 'cafile', $phimail_cafile))
        ) {
            $phimail_error = 'C3';
            (new SystemLogger())->error("phimail_connect failed to connect", ['error' => $phimail_error, 'server' => $server, 'ca' => $phimail_cafile]);
            return false;
        }

        $socket_tries = 0;
        $fp = false;
        while ($socket_tries < 3 && !$fp) {
            $socket_tries++;
            $fp = @stream_socket_client(
                $server,
                $err1,
                $err2,
                10,
                STREAM_CLIENT_CONNECT,
                $context
            );
        }

        if (!$fp) {
            if ($err1 == '111') {
                $err2 = xl('Server may be offline');
            }

            if ($err2 == '') {
                $err2 = xl('Connection error');
            }

            $phimail_error = "C4 $err1 ($err2)";
        } else {
            (new SystemLogger())->debug("phimail_connect was successful");
        }
    } else {
        $fp = @fsockopen($server, $phimail_server['port']);
    }

    if ($fp !== false) {
        $ret = phimail_write_expect_OK($fp, "INFO VER OEMR " . (new VersionService())->asString() . " 1.3.2 "
            . \PHP_VERSION . "\n");
        if ($ret !== true) {
            $fp = false;
            $phimail_error = 'C5';
        }
    }

    if (!empty($phimail_error)) {
        (new SystemLogger())->error("phimail_connect failed to connect", ['error' => $phimail_error, 'server' => $server, 'ca' => $phimail_cafile]);
    } elseif ($fp !== false) {
        (new SystemLogger())->debug("phimail_connect was successful");
    } else {
        (new SystemLogger())->error("phimail_connect failed to connect with unknown error", ['error' => $phimail_error, 'server' => $server, 'port' => $phimail_server['port']]);
    }

    return $fp;
}

/**
 * Connect to a phiMail Direct Messaging server and check for any incoming status
 * messages related to previously transmitted messages or any new messages received.
 */

function phimail_check()
{
    $fp = phimail_connect($err);
    if ($fp === false) {
        phimail_logit(0, xl('could not connect to server') . ' ' . $err);
        return;
    }

    $phimail_username = $GLOBALS['phimail_username'];
    $cryptoGen = new CryptoGen();
    $phimail_password = $cryptoGen->decryptStandard($GLOBALS['phimail_password']);

    $ret = phimail_write_expect_OK($fp, "AUTH $phimail_username $phimail_password\n");
    if ($ret !== true) {
        phimail_logit(0, "authentication error " . $ret);
        return;
    }

    if (!($notifyUsername = $GLOBALS['phimail_notify'])) {
        $notifyUsername = 'admin'; //fallback
    }

    while (1) {
        phimail_write($fp, "CHECK\n");
        $ret = fgets($fp, 512);

        if ($ret == "NONE\n") { //nothing to process
            phimail_close($fp);
            phimail_logit(1, "message check completed");
            return;
        } elseif (substr($ret, 0, 6) == "STATUS") {
            //Format STATUS message-id status-code [additional-information]
            $val = explode(" ", trim($ret), 4);
            $sql = 'SELECT * from direct_message_log WHERE msg_id = ?';
            $res = sqlStatementNoLog($sql, array($val[1]));
            if ($res === false) { //database problem
                phimail_close($fp);
                phimail_logit(0, "database problem");
                return;
            }

            if (($msg = sqlFetchArray($res)) === false) {
                //no match, so log it and move on (should never happen)
                phimail_logit(0, "NO MATCH: " . $ret);
                $ret = phimail_write_expect_OK($fp, "OK\n");
                if ($ret !== true) {
                    phimail_logit(0, "M1 status acknowledgment failed: " . $ret);
                    return;
                } else {
                    continue;
                }
            }

            //if we get here, $msg contains the matching outgoing message record
            if ($val[2] == 'failed') {
                $success = 0;
                $status = 'F';
            } elseif ($val[2] == 'dispatched') {
                $success = 1;
                $status = 'D';
            } else {
                //unrecognized status, log it and move on (should never happen)
                $ret = "UNKNOWN STATUS: " . $ret;
                $success = 0;
                $status = 'U';
            }

            phimail_logit($success, $ret, $msg['patient_id']);

            if (!isset($val[3])) {
                $val[3] = "";
            }

            $sql = "UPDATE direct_message_log SET status=?, status_ts=NOW(), status_info=? WHERE msg_type='S' AND msg_id=?";
            $res = sqlStatementNoLog($sql, array($status, $val[3], $val[1]));
            if ($res === false) { //database problem
                phimail_close($fp);
                phimail_logit(0, "database problem updating: " . $val[1]);
                return;
            }

            if (!$success) {
                //notify local user of failure
                $sql = "SELECT username FROM users WHERE id = ?";
                $res2 = sqlStatementNoLog($sql, array($msg['user_id']));
                $fail_user = ($res2 === false || ($user_row = sqlFetchArray($res2)) === false) ?
                    xl('unknown (see log)') : $user_row['username'];
                $fail_notice = xl('Sent by:') . ' ' . $fail_user . '(' . $msg['user_id'] . ') ' . xl('on') . ' ' . $msg['create_ts']
                    . "\n" . xl('Sent to:') . ' ' . $msg['recipient'] . "\n" . xl('Server message:') . ' ' . $ret;
                phimail_notify(xl('Direct Messaging Send Failure.'), $fail_notice);
                $pnote_id = addPnote(
                    $msg['patient_id'],
                    xl("FAILURE NOTICE: Direct Message Send Failed.") . "\n\n$fail_notice\n",
                    0,
                    1,
                    "Unassigned",
                    $notifyUsername,
                    "",
                    "New",
                    "phimail-service"
                );
            }

            //done with this status message
            $ret = phimail_write_expect_OK($fp, "OK\n");
            if ($ret !== true) {
                phimail_logit(0, "M2 status acknowledgment failed: " . $ret);
                phimail_close($fp);
                return;
            }
        } elseif (substr($ret, 0, 4) == "MAIL") {
            $val = explode(" ", trim($ret), 5); // MAIL recipient sender #attachments msg-id
            $recipient = $val[1];
            $sender = $val[2];
            $att = (int)$val[3];
            $msg_id = $val[4];

            //request main message
            $ret2 = phimail_write_expect_OK($fp, "SHOW 0\n");
            if ($ret2 !== true) {
                phimail_logit(0, "M3 SHOW 0 failed: " . $ret2);
                phimail_close($fp);
                return;
            }

            //get message headers
            $hdrs = "";
            while (($next_hdr = fgets($fp, 1024)) != "\n") {
                $hdrs .= $next_hdr;
            }

            $mime_type = fgets($fp, 512);
            $mime_info = explode(";", $mime_type);
            $mime_type_main = trim(strtolower($mime_info[0]));

            //get main message body
            $body_len = fgets($fp, 256);
            $body = phimail_read_blob($fp, $body_len);
            if ($body === false) {
                phimail_logit(0, "M4 read body failed");
                phimail_close($fp);
                return;
            }

            $att2 = fgets($fp, 256);
            if ($att2 != $att) { //safety for mismatch on attachments
                phimail_logit(0, "M5 attachment mismatch");
                phimail_close($fp);
                return;
            }

            //get attachment info
            if ($att > 0) {
                for ($attnum = 0; $attnum < $att; $attnum++) {
                    if (
                        ($attinfo[$attnum]['name'] = fgets($fp, 1024)) === false
                        || ($attinfo[$attnum]['mime'] = fgets($fp, 1024)) === false
                        || ($attinfo[$attnum]['desc'] = fgets($fp, 1024)) === false
                    ) {
                        phimail_logit(0, "M6 read attachment " . ($attnum + 1) . " metadata failed");
                        phimail_close($fp);
                        return;
                    }
                }
            }

            //main part gets stored as document if not plain text content
            //(if plain text it will be the body of the final pnote)
            $all_doc_ids = array();
            $doc_id = 0;
            $att_detail = "";
            if ($mime_type_main != "text/plain" && $mime_type_main != "text/html") {
                if ($body_len == 0) {
                    $att_detail = $att_detail . "\n" . xl("Zero length attachment") . " ($mime_type_main; " .
                        "0 bytes - " . xl("empty file received") . ") Main message body";
                    unlink($body);
                } else {
                    $name = uniqid("dm-message-") . phimail_extension($mime_type_main);
                    $doc_id = phimail_store($name, $mime_type_main, $body);
                    if (!$doc_id) {
                        phimail_logit(0, "M7 store non-text body failed");
                        phimail_close($fp);
                        return;
                    }

                    $idnum = $doc_id['doc_id'];
                    $all_doc_ids[] = $idnum;
                    $url = $doc_id['url'];
                    $url = substr($url, strrpos($url, "/") + 1);
                    $att_detail = "\n" . xl("Document") . " $idnum (\"$url\"; $mime_type_main; " .
                        filesize($body) . " bytes) Main message body";
                }
            }

            //download and store attachments
            for ($attnum = 0; $attnum < $att; $attnum++) {
                $ret2 = phimail_write_expect_OK($fp, "SHOW " . ($attnum + 1) . "\n");
                if ($ret2 !== true) {
                    phimail_logit(0, "M8 SHOW " . ($attnum + 1) . " failed: " . $ret2);
                    phimail_close($fp);
                    return;
                }

                //we can ignore next two lines (repeat of name and mime-type)
                if (($a1 = fgets($fp, 512)) === false || ($a2 = fgets($fp, 512)) === false) {
                    phimail_logit(0, "M9 skip attachment " . ($attnum + 1) . " duplicate header lines failed");
                    phimail_close($fp);
                    return;
                }

                $att_len = fgets($fp, 256); //length of file
                $attdata = phimail_read_blob($fp, $att_len);
                if ($attdata === false) {
                    phimail_logit(0, "M10 read attachment " . ($attnum + 1) . " failed");
                    phimail_close($fp);
                    return;
                }

                $attinfo[$attnum]['file'] = $attdata;

                $req_name = trim($attinfo[$attnum]['name']);
                $req_name = (empty($req_name) ? $attdata : "dm-") . $req_name;
                $attinfo[$attnum]['mime'] = explode(";", trim($attinfo[$attnum]['mime']));
                $attmime = strtolower($attinfo[$attnum]['mime'][0]);

                if ($att_len == 0) {
                    $att_detail = $att_detail . "\n" . xl("Zero length attachment") . " ($attmime; " .
                        "0 bytes - " . xl("empty file received") . " " . trim($attinfo[$attnum]['desc']);
                    unlink($attdata);
                } else {
                    $att_doc_id = phimail_store($req_name, $attmime, $attdata);
                    if (!$att_doc_id) {
                        phimail_logit(0, "M11 store attachment " . ($attnum + 1) . " failed");
                        phimail_close($fp);
                        return;
                    }

                    $attinfo[$attnum]['doc_id'] = $att_doc_id;
                    $idnum = $att_doc_id['doc_id'];
                    $all_doc_ids[] = $idnum;
                    $url = $att_doc_id['url'];
                    $url = substr($url, strrpos($url, "/") + 1);
                    $att_detail = $att_detail . "\n" . xl("Document") . " $idnum (\"$url\"; $attmime; " .
                        $att_doc_id['filesize'] . " bytes) " . trim($attinfo[$attnum]['desc']);
                }
            }

            if ($att_detail != "") {
                $att_detail = "\n\n" . xl("The following documents were attached to this Direct message:") . $att_detail;
            }

            $ret2 = phimail_write_expect_OK($fp, "DONE\n"); //we'll check for failure after logging.

            //logging only after succesful download, storage, and acknowledgement of message
            $sql = "INSERT INTO direct_message_log (msg_type,msg_id,sender,recipient,status,status_ts,user_id) " .
                "VALUES ('R', ?, ?, ?, 'R', NOW(), ?)";
            $res = sqlStatementNoLog($sql, array($msg_id, $sender, $recipient, phimail_service_userID()));

            phimail_logit(1, $ret);

            //alert appointed user about new message
            switch ($mime_type_main) {
                case "text/plain":
                    $body_text = @file_get_contents($body); //this was not uploaded as a document
                    unlink($body);
                    if (empty($body_text ?? '')) {
                        $body_text = xl("Please note, this message was received empty and is not an error.");
                    }
                    $pnote_id = addPnote(
                        0,
                        xl("Direct Message Received.") . "\n$hdrs\n$body_text$att_detail",
                        0,
                        1,
                        "Unassigned",
                        $notifyUsername,
                        "",
                        "New",
                        "phimail-service"
                    );
                    break;
                case "text/html":
                    $body_text = @file_get_contents($body);
                    unlink($body);
                    if (empty($body_text ?? '')) {
                        $body_text = xl("Please note, this message was received empty and is not an error.");
                    } else {
                        // meager attempt to covert to text. @TODO convert our Messages message body from textarea to div so can display html.
                        $body_text = trim(html_entity_decode(strip_tags(str_ireplace(["<br />", "<br>", "<br/>"], PHP_EOL, $body_text))));
                    }
                    $pnote_id = addPnote(
                        0,
                        xl("Direct Message Received.") . "\n$hdrs\n$body_text$att_detail",
                        0,
                        1,
                        "Unassigned",
                        $notifyUsername,
                        "",
                        "New",
                        "phimail-service"
                    );
                    break;

                default:
                    $note = xl("Direct Message Received.") . "\n$hdrs\n"
                        . xl("Message content is not plain text so it has been stored as a document.") . $att_detail;
                    $pnote_id = addPnote(0, $note, 0, 1, "Unassigned", $notifyUsername, "", "New", "phimail-service");
                    break;
            }

            foreach ($all_doc_ids as $doc_id) {
                setGpRelation(1, $doc_id, 6, $pnote_id);
            }

            if ($ret2 !== true) {
                phimail_logit(0, "M12 DONE failed: " . $ret2);
                phimail_close();
                return;
            }
        } else { //unrecognized or FAIL response
            phimail_logit(0, "M16 unexpected problem checking messages " . $ret);
            phimail_close($fp);
            return;
        }
    }
}

/**
 * Helper functions
 */
function phimail_write($fp, $text)
{
    fwrite($fp, $text);
    fflush($fp);
}

function phimail_write_expect_OK($fp, $text)
{
    phimail_write($fp, $text);
    $ret = fgets($fp, 256);
    if ($ret != "OK\n") { //unexpected error
        phimail_close($fp);
        return $ret;
    }

    return true;
}

function phimail_close($fp)
{
    fclose($fp);
}

function phimail_logit($success, $text, $pid = 0, $event = "direct-message-check")
{
    if (!$success) {
        (new SystemLogger())->errorLogCaller($event, ['success' => $success, 'text' => $text, 'pid' => $pid]);
    }
    EventAuditLogger::instance()->newEvent($event, "phimail-service", 0, $success, $text, $pid);
}

/**
 * Read a blob of data into a local temporary file
 *
 * @param $len number of bytes to read
 * @return the temp filename, or FALSE if failure
 */
function phimail_read_blob($fp, $len)
{

    $fpath = $GLOBALS['temporary_files_dir'];
    if (!@file_exists($fpath)) {
        phimail_logit(0, "M13 temp dir does not exist: " . $fpath);
        return false;
    }

    $name = uniqid("direct-");
    $fn = $fpath . "/" . $name . ".dat";
    $dup = 1;
    while (file_exists($fn)) {
        $fn = $fpath . "/" . $name . "." . $dup++ . ".dat";
    }

    $ff = @fopen($fn, "w");
    if (!$ff) {
        phimail_logit(0, "M14 failed opening temp file: " . $fn);
        return false;
    }

    $bytes_left = $len;
    $chunk_size = 1024;
    while (!feof($fp) && $bytes_left > 0) {
        if ($bytes_left < $chunk_size) {
            $chunk_size = $bytes_left;
        }

        $chunk = fread($fp, $chunk_size);
        if ($chunk === false || @fwrite($ff, $chunk) === false) {
            @fclose($ff);
            @unlink($fn);
            phimail_logit(0, "M15 failure " . ($chunk === false ? "reading" : "writing")
                . " blob chunk after " . ($len - $bytes_left) . " bytes");
            return false;
        }

        $bytes_left -= strlen($chunk);
    }

    @fclose($ff);
    return ($fn);
}

/**
 * Return a suitable filename extension based on MIME-type
 * (very limited, default is .dat)
 */
function phimail_extension($mime)
{
    $m = explode("/", $mime);
    switch ($mime) {
        case 'text/plain':
            return (".txt");
        default:
    }

    switch ($m[1]) {
        case 'html':
        case 'xml':
        case 'pdf':
            return ("." . $m[1]);
        default:
            return (".dat");
    }
}

function phimail_service_userID($name = 'phimail-service')
{
    $sql = "SELECT id FROM users WHERE username=?";
    if (
        ($r = sqlStatementNoLog($sql, array($name))) === false ||
        ($u = sqlFetchArray($r)) === false
    ) {
        $user = 1; //default if we don't have a service user
    } else {
        $user = $u['id'];
    }

    return ($user);
}

/**
 * Given an IsAcceptedFileFilterEvent from our isWhitelist function we check to make sure that we allow
 * the mime type of the Direct message attachment that was sent by the server.
 *
 * @param IsAcceptedFileFilterEvent $event The event with the mimetype to check if we allow it or not
 * @return IsAcceptedFileFilterEvent
 */
function phimail_allow_document_mimetype(IsAcceptedFileFilterEvent $event)
{
    global $phimail_direct_message_check_allowed_mimetype;
    $isAllowedFile = $event->isAllowedFile();
    if (!$isAllowedFile) {
        // we used to only bypass if the Direct mime type matched with what comes through in the event.
        // This fails though if there are multiple possible mime types such as application/xml vs text/xml and the Direct
        // mime type differs from the local OS detection. We will just bypass the mime check alltogether.
        $event->setAllowedFile(true);
    }
    return $event;
}


/**
 * Registers an attachment or non-text message file using the existing Document structure
 *
 * @return Array(doc_id,URL) of the file as stored in documents table, false = failure
 */
function phimail_store($name, $mime_type, $fn)
{
    global $phimail_direct_message_check_allowed_mimetype;

    $allowMimeTypeFunction = 'phimail_allow_document_mimetype';
    // we bypass the whitelisting JUST for phimail documents
    if (isset($GLOBALS['kernel'])) {
        $GLOBALS['kernel']->getEventDispatcher()
            ->addListener(IsAcceptedFileFilterEvent::EVENT_FILTER_IS_ACCEPTED_FILE, $allowMimeTypeFunction);
    }
    // Collect phimail user id
    $user = phimail_service_userID();
    try {
        // Import the document
        $phimail_direct_message_check_allowed_mimetype = $mime_type;
        $filesize = filesize($fn);
        $return = addNewDocument($name, $mime_type, $fn, 0, $filesize, $user, 'direct', 1, '', 1, true);
        if (is_array($return)) {
            $return['filesize'] = $filesize;
        }
    } catch (\Exception $exception) {
        (new SystemLogger())->errorLogCaller($exception->getMessage(), ['name' => $name, 'mime_type' => $mime_type, 'fn' => $fn]);
        phimail_logit(0, "problem storing attachment in OpenEMR");
        $return = false;
    } finally {
        $phimail_direct_message_check_allowed_mimetype = null;
        // There shouldn't be another request in the system to add a document, but for security sake we will prevent code
        // after this from bypassing the whitelist filter
        if (isset($GLOBALS['kernel'])) {
            $GLOBALS['kernel']->getEventDispatcher()
                ->removeListener(IsAcceptedFileFilterEvent::EVENT_FILTER_IS_ACCEPTED_FILE, $allowMimeTypeFunction);
        }
        // Remove the temporary file
        @unlink($fn);
    }

    // Return the result
    return $return;
}

/**
 * Send an error notification or other alert to the notification address specified in globals.
 * (notification email function modified from interface/drugs/dispense_drug.php)
 *
 * @return true if notification successfully sent, false otherwise
 */
function phimail_notify($subj, $body)
{
    $recipient = $GLOBALS['practice_return_email_path'];
    if (empty($recipient)) {
        return false;
    }

    $mail = new PHPMailer();
    $mail->From = $recipient;
    $mail->FromName = 'phiMail Gateway';
    $mail->isMail();
    $mail->Host = "localhost";
    $mail->Mailer = "mail";
    $mail->Body = $body;
    $mail->Subject = $subj;
    $mail->AddAddress($recipient);
    return ($mail->Send());
}
