<?php

/**
 * ErrorConstants holds a number of string constants and error codes
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\DirectMessaging;

class ErrorConstants
{
    /**
     * Note for translation purposes we duplicate the constant inside an xl() command
     * otherwise our translation engine won't pick it up.
     */

    // Message that is sent back to the user (translated) if messaging is unavailable
    // xl("Direct messaging is currently unavailable.")
    const MESSAGING_DISABLED = "Direct messaging is currently unavailable.";

    // used in the message sent back to the user when something fails
    const ERROR_CODE_ABBREVIATION = "EC";

    // translated message sent back to the user when a Direct Address is invalid or message delivery not allowed
    // Details of the reason is attached to this message (which is not translated).
    // xl("Delivery is not allowed to the specified Direct Address:")
    const RECIPIENT_NOT_ALLOWED = "Delivery is not allowed to the specified Direct Address:";

    // Direct Messaging is disabled in the globals
    const ERROR_CODE_MESSAGING_DISABLED = 1;

    // The login AUTH failed, password is likely wrong, but could be something else
    const ERROR_CODE_AUTH_FAILED = 4;

    // Failed to get back from the server that we can begin sending a message
    const ERROR_CODE_MESSAGE_BEGIN_FAILED = 5;

    // failed to get a response when sending OK after the message BEGIN command
    const ERROR_CODE_MESSAGE_BEGIN_OK_FAILED = 6;

    // The ADD <file-type> <file-length> <file-name>\n command failed
    const ERROR_CODE_ADD_FILE_FAILED = 7;

    // Failed to get an OK response from the server to begin sending the file
    const ERROR_CODE_ADD_FILE_CONFIRM_FAILED = 8;

    // attempted to send an unsupported format type
    const ERROR_CODE_INVALID_FORMAT_TYPE = 9;

    // we sent the file to Direct but they failed it on their end.  Details are in the event audit log
    // xl("The message could not be sent at this time.")
    const ERROR_MESSAGE_FILE_SEND_FAILED = "The message could not be sent at this time.";

    // We sent the file to Direct and we got back an unexpected response other than ERROR or QUEUED
    // xl("There was a problem sending the message.")
    const ERROR_MESSAGE_UNEXPECTED_RESPONSE = "There was a problem sending the message.";

    const ERROR_MESSAGE_SET_DISPOSITION_NOTIFICATION_FAILED = "There was a problem in setting the server flag for receiving a message recieved disposition notification";
}
