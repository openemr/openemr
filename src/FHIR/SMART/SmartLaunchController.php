<?php

/**
 * SmartLaunchController handles the display and launching of SMART apps from the user interface.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2020 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\FHIR\SMART;

use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Events\PatientDemographics\RenderEvent;
use OpenEMR\Services\AppointmentService;
use OpenEMR\Services\EncounterService;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\UserService;
use Symfony\Component\EventDispatcher\EventDispatcher;
use OpenEMR\FHIR\Config\ServerConfig;

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

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    public function __construct(?EventDispatcher $dispatcher = null)
    {
        $this->dispatcher = $dispatcher;
    }

    public function registerContextEvents()
    {
        $this->dispatcher->addListener(RenderEvent::EVENT_SECTION_LIST_RENDER_AFTER, [$this, 'renderPatientSmartLaunchSection']);
    }

    public function renderPatientSmartLaunchSection(RenderEvent $event)
    {
        if (empty($GLOBALS['rest_fhir_api'])) {
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
            // Billing expand collapse widget
            $widgetTitle = xl("SMART Enabled Apps");
            $widgetLabel = "smart";
            $widgetButtonLabel = xl("Edit");
            $widgetButtonLink = ""; // "return newEvt();";
            $widgetButtonClass = "";
            $linkMethod = "javascript";
            $bodyClass = "notab";
            $widgetAuth = false;
            $fixedWidth = false;
            $forceExpandAlways = false;
            $launchCode = $this->getLaunchCodeContext($puuid);
            // TODO: adunsulag is there an redirect_uri that we can specify for the launch path?? The spec feels vague
            // here...  all the SMART apps we've seen appear to follow a 'launch.html' nomenclature but that doesn't
            // appear to be required in the spec.

            $issuer = (new ServerConfig())->getFhirUrl();
            // issuer and audience are the same in a EHR SMART Launch


            expand_collapse_widget(
                $widgetTitle,
                $widgetLabel,
                $widgetButtonLabel,
                $widgetButtonLink,
                $widgetButtonClass,
                $linkMethod,
                $bodyClass,
                $widgetAuth,
                $fixedWidth,
                $forceExpandAlways
            );
            ?>
            <div>
                <ul>
                        <?php if (empty($smartClients)) : ?>
                            <li><p><?php echo xlt("No registered SMART apps in the system"); ?></p></li>
                        <?php endif; ?>
                        <?php foreach ($smartClients as $client) : ?>
                            <li class="summary_item">
                                <?php $this->renderLaunchButton($client, $issuer, $launchCode); ?>
                                <?php echo text($client->getName()); ?>
                            </li>
                        <?php endforeach; ?>
                </ul>
            </div>
        </section>
        <?php
        // it's too bad we don't have a centralized page renderer we could tie this into and render javascript at the
        // end of our footer pages on everything...
        $this->renderLaunchScript();
    }

    public function renderLaunchButton(ClientEntity $client, string $issuer, SMARTLaunchToken $launchToken, $launchText = "Launch")
    {
        $launchCode = $launchToken->serialize();
        $launchParams = "?launch=" . urlencode($launchCode) . "&iss=" . urlencode($issuer) . "&aud=" . urlencode($issuer);
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
        CsrfUtils::verifyCsrfToken($csrf_token);
        $puuid = null;
        $euuid = null;
        if (isset($_SESSION['pid'])) {
            // grab the patient puuid
            $patientService = new PatientService();
            $puuid = UuidRegistry::uuidToString($patientService->getUuid($_SESSION['pid']));
        }
        if (!empty($_SESSION['encounter'])) {
            // grab the encounter euuid
            $euuid = UuidRegistry::uuidToString(EncounterService::getUuidById($_SESSION['encounter'], 'form_encounter', 'encounter'));
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
                    $appointmentUuid = $appointment[0]['pc_uuid'];
                    $pid = $appointment[0]['pid'];
                    $puuid = UuidRegistry::uuidToString($patientService->getUuid($pid));
                    // at some point if the appointment has a link to encounters we could grab that here.
//                    $euuid = UuidRegistry::uuidToString(EncounterService::getUuidById($appointment['encounter'], 'form_encounter', 'encounter'));
                }
            }
        }
        if (!empty($_SESSION['encounter'])) {
            // grab the encounter euuid
            $euuid = UuidRegistry::uuidToString(EncounterService::getUuidById($_SESSION['encounter'], 'form_encounter', 'encounter'));
        }

        $issuer = (new ServerConfig())->getFhirUrl();
        $launchCode = $this->getLaunchCodeContext($puuid, $euuid, $intent);

        if (!empty($appointmentUuid)) {
            $launchCode->setAppointmentUuid($appointmentUuid);
        }
        $serializedCode = $launchCode->serialize();
        $launchParams = "?launch=" . urlencode($serializedCode) . "&iss=" . urlencode($issuer) . "&aud=" . urlencode($issuer);
        $redirectUrl = $client->getLaunchUri($launchParams);
        header("Location: " . $redirectUrl);
        exit;
    }

    public function renderLaunchScript()
    {
        ?>
        <script>
            (function(oeSMART) {
                if (oeSMART && oeSMART.initLaunch) {
                    oeSMART.initLaunch(<?php echo js_escape($GLOBALS['webroot']); ?>, <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>);
                }
            })(window.oeSMART || {});

        </script>
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

    private function getLaunchCodeContext($patientUUID, $encounterId = null, $intent = null)
    {
        $token = new SMARTLaunchToken($patientUUID, $encounterId);
        $token->setIntent($intent);
        if (empty($intent)) {
            $intent = SMARTLaunchToken::INTENT_PATIENT_DEMOGRAPHICS_DIALOG;
        }
        $token->setIntent($intent);
        return $token;
    }
}
