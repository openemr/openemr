<?php

/**
 * SmartLaunchController handles the display and launching of SMART apps from the user interface.
 *
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\FHIR\SMART;

use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\PatientDemographics\RenderEvent;
use OpenEMR\FHIR\Config\ServerConfig;
use OpenEMR\Services\AppointmentService;
use OpenEMR\Services\EncounterService;
use OpenEMR\Services\PatientService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

// not sure I really like this here... it seems like some of this
// should be encapsulated in a class that autoloading can reach.

/**
 * Class SmartLaunchController handles the display and launching of SMART apps from the user interface.
 * @package OpenEMR\FHIR\SMART
 */
class SmartLaunchController
{
    const CLIENT_APP_REQUIRED_LAUNCH_SCOPE = 'launch';
    const CLIENT_APP_STANDALONE_LAUNCH_SCOPE = 'launch/patient';

    public function __construct(private readonly ?EventDispatcherInterface $dispatcher = null)
    {
    }

    public function registerContextEvents()
    {
        $this->dispatcher->addListener(RenderEvent::EVENT_SECTION_LIST_RENDER_AFTER, $this->renderPatientSmartLaunchSection(...));
    }

    public function renderPatientSmartLaunchSection(RenderEvent $event)
    {
        if (empty(OEGlobalsBag::getInstance()->get('rest_fhir_api'))) {
            // do not show patient summary widget if fhir portal is off
            return;
        }

        $smartClients = $this->getSMARTClients();
        if (empty($smartClients)) {
            // do not show patient summary widget if no available smart clients
            return;
        }
        // TODO: adunsulag we would filter the clients based on their smart capability & scopes they could send...
        $pid = $event->getPid();
        $patientService = new PatientService();
        // make sure we've created all of our missing UUIDs
        UuidRegistry::createMissingUuidsForTables(['patient_data']);
        // going to work with string uuids
        $puuid = UuidRegistry::uuidToString($patientService->getUuid($pid));
        // TODO: @adunsulag could this all be moved to twig?
        ?>
        <section>
            <?php
            $launchCode = $this->getLaunchCodeContext($puuid);

        // issuer and audience are the same in a EHR SMART Launch
            $issuer = (new ServerConfig())->getFhirUrl();
            $viewArgs = [
                        'title' => xl('SMART Enabled Apps'),
                        'card_container_class_list' => ['flex-fill', 'mx-1', 'card'],
                        'id' => 'smart',
                        'forceAlwaysOpen' => false,
                        'initiallyCollapsed' => (getUserSetting('smart') == 0) ? true : false,
                        'linkMethod' => "javascript",
                        'auth' => false,
                        'issuer' => $issuer,
                        'launchCode' => $launchCode,
                        'smartClients' => $smartClients,
                        'intent' => SMARTLaunchToken::INTENT_PATIENT_DEMOGRAPHICS_DIALOG
            ];

            $twig = (new TwigContainer(null, OEGlobalsBag::getInstance()->getKernel()))->getTwig();
            echo $twig->render("patient/card/smart_launch.html.twig", $viewArgs);
            $this->renderLaunchScript();
    }

    /**
     * @param literal-string $launchText
     */
    public function renderLaunchButton(ClientEntity $client, string $issuer, SMARTLaunchToken $launchToken, string $launchText = "Launch")
    {
        $launchCode = $launchToken->serialize();
        $launchParams = "?launch=" . urlencode((string) $launchCode) . "&iss=" . urlencode($issuer) . "&aud=" . urlencode($issuer);
        ?>
        <button class='btn btn-primary btn-sm smart-launch-btn' data-smart-name="<?php echo attr($client->getName()); ?>"
                            data-intent="<?php echo attr(SMARTLaunchToken::INTENT_PATIENT_DEMOGRAPHICS_DIALOG); ?>"
                            data-client-id="<?php echo attr($client->getIdentifier()); ?>">
                                    <?php echo xlt($launchText); ?>
        </button>
        <?php
    }

    public function redirectAndLaunchSmartApp($intent, $client_id, $csrf_token, array $intentData)
    {
        $clientRepository = new ClientRepository();
        $client = $clientRepository->getClientEntity($client_id);
        if (empty($client)) {
            throw new \Exception("Invalid client id");
        }
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        CsrfUtils::verifyCsrfToken($csrf_token, session: $session);
        $puuid = null;
        $euuid = null;
        $pid = $session->get('pid');
        if (!empty($pid)) {
            // grab the patient puuid
            $patientService = new PatientService();
            $puuid = UuidRegistry::uuidToString($patientService->getUuid($pid));
        }
        $encounter = $session->get('encounter');
        if (!empty($encounter)) {
            // grab the encounter euuid
            $euuid = UuidRegistry::uuidToString(EncounterService::getUuidById($encounter, 'form_encounter', 'encounter'));
        }
        $appointmentUuid = null;
        if (!empty($intentData)) {
            // let's grab specific data
            if (!empty($intentData['appointment_id'])) {
                if (!AclMain::aclCheckCore('patients', 'appt')) {
                    throw new AccessDeniedException("patients", "appt", "You do not have permission to access appointments");
                }
                $appointmentService = new AppointmentService();
                $appointment = $appointmentService->getAppointment($intentData['appointment_id']);
                if (!empty($appointment)) {
                    $patientService = new PatientService();
                    $appointmentUuidValue = $appointment[0]['pc_uuid'] ?? null;
                    $appointmentUuid = is_string($appointmentUuidValue) ? $appointmentUuidValue : null;
                    $pid = $appointment[0]['pid'];
                    $puuid = UuidRegistry::uuidToString($patientService->getUuid($pid));
                    // at some point if the appointment has a link to encounters we could grab that here.
//                    $euuid = UuidRegistry::uuidToString(EncounterService::getUuidById($appointment['encounter'], 'form_encounter', 'encounter'));
                }
            }
        }
        if (!empty($encounter)) {
            // grab the encounter euuid
            $euuid = UuidRegistry::uuidToString(EncounterService::getUuidById($encounter, 'form_encounter', 'encounter'));
        }

        $issuer = (new ServerConfig())->getFhirUrl();
        $launchCode = $this->getLaunchCodeContext($puuid, $euuid, $intent);

        if (!empty($appointmentUuid)) {
            $launchCode->setAppointmentUuid($appointmentUuid);
        }

        // Questionnaire context is resolved from OpenEMR database ids so the browser cannot inject FHIR references.
        /** @var array<string, mixed> $intentData */
        $this->addQuestionnaireLaunchContext($launchCode, $intentData, $pid);

        $serializedCode = $launchCode->serialize();
        $launchParams = "?launch=" . urlencode((string) $serializedCode) . "&iss=" . urlencode($issuer) . "&aud=" . urlencode($issuer);
        $redirectUrl = $client->getLaunchUri($launchParams);
        header("Location: " . $redirectUrl);
        exit;
    }

    public function renderLaunchScript()
    {
        ?>

        <?php
    }
    /**
     * Retrieves the registered ClientEntities that are SMART only clients.
     * @return \OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity[]
     */
    private function getSMARTClients()
    {
        $clientRepository = new ClientRepository();
        $clientEntities = $clientRepository->listClientEntities();
        $smartList = [];
        foreach ($clientEntities as $client) {
            // only clients with a registered 'launch' scope will show up as
            // launchable inside EHR launch scope.
            // TODO: adunsulag should these scopes be against a class constant? if we pull them from a db that won't
            // work...
            if ($client->isEnabled() && $client->hasScope(self::CLIENT_APP_REQUIRED_LAUNCH_SCOPE)) {
                $smartList[] = $client;
            }
        }
        return $smartList;
    }

    /**
     * @param array<string, mixed> $intentData
     */
    private function addQuestionnaireLaunchContext(
        SMARTLaunchToken $launchCode,
        array $intentData,
        mixed $pid
    ): void {
        $questionnaireValue = $intentData['questionnaire_id'] ?? null;
        $questionnaireResponseValue = $intentData['questionnaire_response_id'] ?? null;
        if ($questionnaireValue === null && $questionnaireResponseValue === null) {
            return;
        }

        $questionnaireId = $this->getPositiveInteger($questionnaireValue);
        $questionnaireResponseId = $this->getPositiveInteger($questionnaireResponseValue);
        $patientId = $this->getPositiveInteger($pid);
        if (
            $launchCode->getIntent() !== SMARTLaunchToken::INTENT_QUESTIONNAIRE_ASSESSMENT
            || $questionnaireId === null
            || $patientId === null
            || ($questionnaireResponseValue !== null && $questionnaireResponseId === null)
        ) {
            throw new \InvalidArgumentException("Questionnaire SMART launch context is invalid");
        }
        if (!AclMain::aclCheckCore('patients', 'med')) {
            throw new AccessDeniedException("patients", "med", "You do not have permission to access patient assessments");
        }

        $questionnaireRecords = QueryUtils::fetchRecordsNoLog(
            "SELECT id FROM questionnaire_repository WHERE id = ? AND active = 1",
            [$questionnaireId]
        );
        $questionnaireRecord = $questionnaireRecords[0] ?? null;
        $resolvedQuestionnaireId = is_array($questionnaireRecord)
            ? $this->getPositiveInteger($questionnaireRecord['id'] ?? null)
            : null;
        if ($resolvedQuestionnaireId !== $questionnaireId) {
            throw new \InvalidArgumentException("Questionnaire SMART launch context was not found");
        }

        $launchCode->addFhirContextReference('Questionnaire', (string)$questionnaireId);

        $responseStatus = '';
        if ($questionnaireResponseId !== null) {
            $responseRecords = QueryUtils::fetchRecordsNoLog(
                "SELECT uuid, patient_id, questionnaire_foreign_id, status
                 FROM questionnaire_response
                 WHERE id = ? AND COALESCE(encounter, 0) = 0",
                [$questionnaireResponseId]
            );
            $responseRecord = $responseRecords[0] ?? null;
            $responseUuidBinary = is_array($responseRecord) ? ($responseRecord['uuid'] ?? null) : null;
            $responsePatientId = is_array($responseRecord)
                ? $this->getPositiveInteger($responseRecord['patient_id'] ?? null)
                : null;
            $responseQuestionnaireId = is_array($responseRecord)
                ? $this->getPositiveInteger($responseRecord['questionnaire_foreign_id'] ?? null)
                : null;
            $responseStatus = is_array($responseRecord) && is_string($responseRecord['status'] ?? null)
                ? $responseRecord['status']
                : '';
            if (
                !is_string($responseUuidBinary)
                || $responseUuidBinary === ''
                || $responsePatientId !== $patientId
                || $responseQuestionnaireId !== $questionnaireId
            ) {
                throw new \InvalidArgumentException("QuestionnaireResponse SMART launch context is invalid");
            }

            $questionnaireResponseUuid = UuidRegistry::uuidToString($responseUuidBinary);
            $launchCode->addFhirContextReference('QuestionnaireResponse', $questionnaireResponseUuid);
        }

        $action = 'start';
        if ($questionnaireResponseId !== null) {
            $action = $responseStatus === 'in-progress' ? 'continue' : 'review';
        }
        $appContext = json_encode(
            [
                'workflow' => 'questionnaire-assessment',
                'action' => $action,
                'returnContext' => 'patient-fhir-assessments',
            ],
            JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );
        $launchCode->setAppContext($appContext);
    }

    private function getPositiveInteger(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }
        if (!is_string($value) || $value === '' || !ctype_digit($value)) {
            return null;
        }

        $validated = filter_var($value, FILTER_VALIDATE_INT);
        return is_int($validated) && $validated > 0 ? $validated : null;
    }

    private function getLaunchCodeContext(
        $patientUUID,
        $encounterId = null,
        $intent = null
    ): SMARTLaunchToken {
        $token = new SMARTLaunchToken($patientUUID, $encounterId);
        $token->setIntent($intent);
        if ($intent === null) {
            $intent = SMARTLaunchToken::INTENT_PATIENT_DEMOGRAPHICS_DIALOG;
        }
        $token->setIntent($intent);
        return $token;
    }
}
