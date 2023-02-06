<?php


namespace Comlink\OpenEMR\Modules\TeleHealthModule\Services;


use Comlink\OpenEMR\Modules\TeleHealthModule\TelehealthGlobalConfig;
use http\Env;
use MyMailer;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\LogoService;
use Twig\Environment;

class TeleHealthParticipantInvitationMailerService
{
    private $publicPathFQDN;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var TelehealthGlobalConfig
     */
    private $config;

    public function __construct(Environment $twig, $publicPathFQDN, TelehealthGlobalConfig $config)
    {
        $this->twig = $twig;
        $this->publicPathFQDN = $publicPathFQDN;
        $this->config = $config;
    }

    public function sendInvitationToExistingPatient($patient) {
        $logoService = new LogoService();
        $logoPath = $GLOBALS['qualified_site_addr'] . $logoService->getLogo('core/login/primary');
        $data = [
            'url' => $this->getJoinLink()
            ,'salutation' => ($patient['fname'] ?? '') . ' ' . ($patient['lname'] ?? '')
            ,'logoPath' => $logoPath
            ,'logoAlt' => $GLOBALS['openemr_name'] ?? 'OpenEMR'
            ,'title' => $GLOBALS['openemr_name'] ?? 'OpenEMR'
        ];
        $htmlMsg = $this->twig->render('comlink/emails/telehealth-invitation-existing.html.twig', $data);
        $plainMsg = $this->twig->render('comlink/emails/telehealth-invitation-existing.text.twig', $data);
        $this->sendMessageToPatient($htmlMsg, $plainMsg, $patient);
    }

    public function sendInvitationToNewPatient($patient) {
        $data = [
            'url' => $this->getJoinLink()
            ,'salutation' => ''
            ,'https://www.discoverandchange.com/wp-content/uploads/2020/08/LogoTransparent.png'
            ,'logoAlt' => 'discoverandchange'
            ,'title' => 'Discover and Change'
        ];
        $htmlMsg = $this->twig->render('comlink/emails/telehealth-invitation-new.html.twig', $data);
        $plainMsg = $this->twig->render('comlink/emails/telehealth-invitation-new.text.twig', $data);
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