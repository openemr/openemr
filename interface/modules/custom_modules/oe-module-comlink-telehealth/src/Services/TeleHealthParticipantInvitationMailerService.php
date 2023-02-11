<?php

/**
 * Handles participant invitation emails sent out for inviting third party patients to a telehealth session.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Comlink\OpenEMR\Modules\TeleHealthModule\Services;

use Comlink\OpenEMR\Modules\TeleHealthModule\TelehealthGlobalConfig;
use MyMailer;
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

    public function sendInvitationToExistingPatient($patient, $session, $thirdPartyLaunchAction)
    {
        $data = $this->getInvitationData($patient, $session, $thirdPartyLaunchAction);
        $htmlMsg = $this->twig->render('comlink/emails/telehealth-invitation-existing.html.twig', $data);
        $plainMsg = $this->twig->render('comlink/emails/telehealth-invitation-existing.text.twig', $data);
        $this->sendMessageToPatient($htmlMsg, $plainMsg, $patient);
    }

    public function sendInvitationToNewPatient($patient, $session, $thirdPartyLaunchAction)
    {
        $data = $this->getInvitationData($patient, $session, $thirdPartyLaunchAction);
        $htmlMsg = $this->twig->render('comlink/emails/telehealth-invitation-new.html.twig', $data);
        $plainMsg = $this->twig->render('comlink/emails/telehealth-invitation-new.text.twig', $data);
        $this->sendMessageToPatient($htmlMsg, $plainMsg, $patient);
    }

    private function getInvitationData($patient, $session, $thirdPartyLaunchAction)
    {
        $logoService = new LogoService();
        $logoPath = $this->config->getQualifiedSiteAddress() . $logoService->getLogo('core/login/primary');
        $name = $this->config->getOpenEMRName();
        $data = [
            'url' => $this->getJoinLink($session, $thirdPartyLaunchAction)
            ,'salutation' => ($patient['fname'] ?? '') . ' ' . ($patient['lname'] ?? '')
            ,'logoPath' => $logoPath
            ,'logoAlt' => $name ?? 'OpenEMR'
            ,'title' => $name ?? 'OpenEMR'
        ];
        return $data;
    }

    private function getJoinLink($session, $thirdPartyLaunchAction)
    {
        // the index-portal will redirect the person to login before completing the action
        return $this->publicPathFQDN . $this->publicPathFQDN . "index-portal.php?action=" . $thirdPartyLaunchAction
            . "&pc_eid=" . intval($session['pc_eid']);
    }

    private function sendMessageToPatient($htmlMsg, $plainMsg, $patient)
    {

        $throwExceptions = true;
        $mail = new MyMailer($throwExceptions);
        $pt_name = $patient['fname'] . ' ' . $patient['lname'];
        $pt_email = $patient['email'];
        $email_subject = xl('Join Telehealth Session');
        $email_sender = $this->config->getPatientReminderName();
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
