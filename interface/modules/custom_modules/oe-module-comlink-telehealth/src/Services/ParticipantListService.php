<?php

namespace Comlink\OpenEMR\Modules\TeleHealthModule\Services;

use Exception;
use Twig\Environment;

class ParticipantListService
{
    /**
     * @var TeleHealthProvisioningService
     */
    private $provisioningService;

    private $publicPathFQDN;

    /**
     * @var Environment
     */
    private $twig;

    public function __construct(Environment $twig, TeleHealthProvisioningService $provisioningService, $publicPathFQDN)
    {
        $this->provisioningService = $provisioningService;
        $this->publicPathFQDN = $publicPathFQDN;
        $this->twig = $twig;
    }

    public function getParticipantListWithInvitationsForAppointment($user, $session)
    {
        $participants = $this->getParticipantListForAppointment($user, $session);
        $link = $this->getJoinLink($session);
        $updatedParticipants = [];
        foreach ($participants as $participant) {
            if ($participant['role'] == 'patient') {
                $participant['invitation'] = [
                    'link' => $link
                    ,'text' => $this->twig->render('comlink/emails/telehealth-invitation-existing.text.twig', ['joinLink' => $link])
                ];
            }
            $updatedParticipants[] = $participant;
        }
        return $updatedParticipants;
    }

    /**
     * TODO: @adunsulag need to refactor this function and the one in the TeleHealthParticipantInvitationMailerService to its own class
     * @param $session
     * @param $thirdPartyLaunchAction
     * @return string
     */
    private function getJoinLink($session)
    {
        // the index-portal will redirect the person to login before completing the action
        return $this->publicPathFQDN . "index-portal.php?action=launch_patient_session&pc_eid="
            . intval($session['pc_eid']);
    }

    public function getSparseParticipantListFromSession($session)
    {
        $participantList = [
            [
                'role' => "provider"
                , 'inRoom' => $this->sessionUserInRoom($session, 'provider')
                , 'id' => $session['user_id']
            ]
        ];
        if (!empty($session['pid'])) {
            $participantList[] = [
                'role' => "patient"
                , 'inRoom' => $this->sessionUserInRoom($session, 'patient')
                , 'id' => $session['pid']
            ];
        }
        if (!empty($session['pid_related'])) {
            $participantList[] = [
                'role' => "patient"
                , 'inRoom' => $this->sessionUserInRoom($session, 'patient_related')
                , 'id' => $session['pid_related']
            ];
        }
        return $participantList;
    }


    public function getParticipantListForAppointment($user, $session)
    {
        $userTelehealthSettings = $this->provisioningService->getOrCreateTelehealthProvider($user);

        $participantList = [
            [
                'callerName' => $user['fname'] . ' ' . $user['lname']
                , 'uuid' => $userTelehealthSettings->getUsername()
                , 'role' => "provider"
                , 'email' => '' // we leave it blank as we don't want patients to have the direct email of the provider
                , 'inRoom' => $this->sessionUserInRoom($session, 'provider')
                , 'id' => $user['id']
            ]
        ];
        if (!empty($session['pid'])) {
            $patient = $this->getPatientForPid($session['pid']);
            $patientTelehealthSettings = $this->provisioningService->getOrCreateTelehealthPatient($patient);
            $participantList[] = [
                'callerName' => $patient['fname'] . ' ' . $patient['lname']
                , 'email' => $patient['email'] ?? ''
                , 'uuid' => $patientTelehealthSettings->getUsername()
                , 'role' => "patient"
                , 'inRoom' => $this->sessionUserInRoom($session, 'patient')
                , 'id' => $patient['pid']
            ];
        }
        if (!empty($session['pid_related'])) {
            $patient = $this->getPatientForPid($session['pid_related']);
            $patientTelehealthSettings = $this->provisioningService->getOrCreateTelehealthPatient($patient);
            $participantList[] = [
                'callerName' => $patient['fname'] . ' ' . $patient['lname']
                , 'email' => $patient['email'] ?? ''
                , 'uuid' => $patientTelehealthSettings->getUsername()
                , 'role' => "patient"
                , 'inRoom' => $this->sessionUserInRoom($session, 'patient_related')
                , 'id' => $patient['pid']
            ];
        }
        return $participantList;
    }


    /**
     * Note because of the way we do escaping for injection into the DOM we return a string value of "Y" or "N"
     * to represent the boolean choices here.
     *
     * @param $session
     * @param $userKey
     * @return string
     * @throws Exception
     */
    private function sessionUserInRoom($session, $userKey)
    {
        if (
            !empty($session[$userKey . '_start_time']) &&
            !empty($session[$userKey . '_last_update'])
        ) {
            $dateTime = \DateTime::createFromFormat("Y-m-d H:i:s", $session[$userKey . '_last_update']);
            $currentDateTime = new \DateTime();
            // odd that this statement returns an empty string instead of false
            if ($currentDateTime < $dateTime->add(new \DateInterval("PT15S"))) {
                return "Y";
            }
        }
        return "N";
    }

    private function getPatientForPid($pid)
    {
        $formattedPatientService = new FormattedPatientService();
        return $formattedPatientService->getPatientForPid($pid);
    }
}
