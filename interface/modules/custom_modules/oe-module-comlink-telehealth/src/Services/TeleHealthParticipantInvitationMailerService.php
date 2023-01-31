<?php


namespace Comlink\OpenEMR\Modules\TeleHealthModule\Services;


use MyMailer;
use OpenEMR\Common\Logging\SystemLogger;

class TeleHealthParticipantInvitationMailerService
{
    private $publicPathFQDN;

    public function __construct($publicPathFQDN)
    {
        $this->publicPathFQDN = $publicPathFQDN;
    }

    public function sendInvitationToExistingPatient($patient) {
        $joinLink = $this->getJoinLink();
        $htmlMsg = $plainMsg = "You've been invited to join a telehealth session";
        $htmlMsg .= "<p>Click the link <a href='$joinLink'>$joinLink</a> to join";
        $plainMsg .= "Paste the link $joinLink into your browser to join the session";
        $this->sendMessageToPatient($htmlMsg, $plainMsg, $patient);
    }

    public function sendInvitationToNewPatient($patient) {
        $joinLink = $this->getJoinLink();
        // TODO: @adunsulag it would be best to consolidate the messages here... but this works for now.
        $htmlMsg = $plainMsg = "You've had a new account created for you in order to join join a telehealth session. You have been sent a temporary password in a separate email.  Click the following link to join the session.";
        $htmlMsg .= "<p>Click the link <a href='$joinLink'>$joinLink</a> to join";
        $plainMsg .= "Paste the link $joinLink into your browser to join the session";
        $this->sendMessageToPatient($htmlMsg, $plainMsg, $patient);
    }

    private function getJoinLink() {
        return $this->publicPathFQDN . "index-thirdparty2.php";
    }

    private function sendMessageToPatient($htmlMsg, $plainMsg, $patient) {

        $throwExceptions = true;
        $mail = new MyMailer($throwExceptions);
        $pt_name = $patient['fname'] . ' ' . $patient['lname'];
        $pt_email = $patient['email'];
        $email_subject = xl('Join Telehealth Session');
        $email_sender = $GLOBALS['patient_reminder_sender_email'];
        $mail->AddReplyTo($email_sender, $email_sender);
        $mail->SetFrom($email_sender, $email_sender);
        $mail->AddAddress($pt_email, $pt_name);
        $mail->Subject = $email_subject;
        $mail->MsgHTML($htmlMsg);
        $mail->AltBody = $plainMsg;
        $mail->IsHTML(true);

        // the invitation is critical and participants can't join w/o it.  We will send any failure exceptions
        // up the chain to fail everything
        // if the email does not go out
        $mail->Send();
    }
}