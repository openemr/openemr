<?php

/**
 * Functions to transmit a CCD as a Direct Protocol Message
 *
 * Copyright (C) 2013, 2021 EMR Direct <https://www.emrdirect.com/>
 * Copyright (C) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 *
 * Use of these functions requires an active phiMail Direct messaging
 * account with EMR Direct.  For information regarding this service,
 * please visit http://www.emrdirect.com or email support@emrdirect.com
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
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
 * @author  Michael A. Smith <michael@opencoreemr.com>
 * @link    https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\DirectMessaging\ErrorConstants;
use OpenEMR\Common\DirectMessaging\PhiMailAttachmentType;
use OpenEMR\Common\DirectMessaging\PhiMailOutgoingAttachment;
use OpenEMR\Common\DirectMessaging\PhiMailOutgoingMessage;
use OpenEMR\Common\DirectMessaging\PhiMailRequester;
use OpenEMR\Common\DirectMessaging\PhiMailSender;
use OpenEMR\Common\DirectMessaging\PhiMailUserRepository;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

/**
 * Connect to a phiMail Direct Messaging server and transmit
 * a message to the specified recipient. If the message is accepted by the
 * server, the script will return "SUCCESS", otherwise it will return an error msg.
 * @param string $message The message to send via Direct
 * @param string $recipient the Direct Address of the recipient
 * @param bool $verifyFinalDelivery Whether to force receipt confirmation that the message was delivered.  Can cause message delivery failures if recipient system does not support the option.
 * @return string result of operation
 */
function transmitMessage($message, $recipient, $verifyFinalDelivery = false): string
{
    $sender = PhiMailSender::fromGlobals(
        OEGlobalsBag::getInstance(),
        ServiceContainer::getCrypto(),
        ServiceContainer::getLogger(),
        EventAuditLogger::getInstance(),
    );
    return $sender->send(
        new PhiMailOutgoingMessage(recipient: $recipient, text: $message, verifyFinalDelivery: $verifyFinalDelivery),
        PhiMailRequester::fromSession(SessionWrapperFactory::getInstance()->getActiveSession()),
        failureEvent: 'transmit-message',
        successEvent: 'transmit-message',
    );
}

/**
 * Connect to a phiMail Direct Messaging server and transmit
 * a CCD document to the specified recipient. If the message is accepted by the
 * server, the script will return "SUCCESS", otherwise it will return an error msg.
 * @param int $pid The patient pid that we are sending a CCDA doc for
 * @param string $ccd_out the data to transmit, a CCDA document is assumed
 * @param string $recipient the Direct Address of the recipient
 * @param string $requested_by user | patient
 * @param string $xml_type
 * @param string $format_type The format that the document ns in (pdf, xml, html)
 * @param string $message The message body the clinician wants to send
 * @param string $filename The filename to use as the name of the attachment (must included file extension as part of filename)
 * @param bool $verifyFinalDelivery Whether to force receipt confirmation that the message was delivered.  Can cause message delivery failures if recipient system does not support the option.
 * @param ?int $referralId the referral (transactions.id) the document goes with, logged with the message so a reply can be filed with it
 * @return string result of operation
 */
function transmitCCD($pid, $ccd_out, $recipient, $requested_by, $xml_type = "CCD", $format_type = 'xml', $message = '', $filename = '', $verifyFinalDelivery = false, ?int $referralId = null): string
{
    //get patient name in Last_First format (used for CCDA filename) and
    //First Last for the message text.
    $patientData = getPatientPID(["pid" => $pid]);
    if (empty($patientData)) { // shouldn't ever happen but we need to check anyways
        return( xl(ErrorConstants::ERROR_MESSAGE_UNEXPECTED_RESPONSE));
    }
    $patientName2 = "";
    $att_filename = "";
    $extension = "";

    $session = SessionWrapperFactory::getInstance()->getActiveSession();

    // TODO: do we want to throw an error if we can't get patient data?  Probably.

    if (!empty($patientData[0]['lname'])) {
        $patientName2 = trim($patientData[0]['fname'] . " " . $patientData[0]['lname']);
    }

    if (!empty($filename)) {
        // if we have a filename from our database, we want to send that
        $att_filename = $filename;
        // extension already initialized to ""
    } elseif (!empty($patientName2)) {
        //spaces are the argument delimiter for the phiMail API calls and must be removed
        // CCDA format requires patient name in last, first format
        $att_filename = str_replace(" ", "_", $xml_type . "_" . $patientData[0]['lname']
            . "_" . $patientData[0]['fname']);
        $extension = "." . $format_type;
    }

    // add whatever the clinician added as a message to be sent.
    $text_out = "";
    if (is_string($message) && trim($message) != "") {
        $text_out = $message . "\n";
    }

    if ($requested_by == "patient") {
        $text_out .= xl("Delivery of the attached clinical document was requested by the patient") .
            ($patientName2 == "" ? "." : ", " . $patientName2 . ".");
    } else {
        $text_out .= xl("A clinical document is attached") .
            ($patientName2 == "" ? "." : " " . xl("for patient") . " " . $patientName2 . ".");
    }

    // MU2 CareCoordination added the need to send CCDAs formatted as html,pdf, or xml
    $add_type = PhiMailAttachmentType::forDocument($format_type, $xml_type);
    if ($add_type === null) {
        return xl(ErrorConstants::MESSAGING_DISABLED) . " " . ErrorConstants::ERROR_CODE_ABBREVIATION . ": "
            . ErrorConstants::ERROR_CODE_INVALID_FORMAT_TYPE;
    }

    $requester = PhiMailRequester::fromSession($session);
    if ($requested_by == "patient") {
        $requester = $requester->onBehalfOf(PhiMailUserRepository::PORTAL_USERNAME, (new PhiMailUserRepository())->portalUserId());
    }

    $sender = PhiMailSender::fromGlobals(
        OEGlobalsBag::getInstance(),
        ServiceContainer::getCrypto(),
        ServiceContainer::getLogger(),
        EventAuditLogger::getInstance(),
    );
    return $sender->send(
        new PhiMailOutgoingMessage(
            recipient: $recipient,
            text: $text_out,
            attachment: new PhiMailOutgoingAttachment($add_type, $att_filename . $extension, $ccd_out),
            verifyFinalDelivery: $verifyFinalDelivery,
            patientId: $pid,
            referralId: $referralId,
        ),
        $requester,
        failureEvent: 'transmit-ccd',
        successEvent: 'transmit-' . $xml_type,
    );
}
