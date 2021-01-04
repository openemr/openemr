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

use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Events\PatientDemographics\RenderEvent;
use OpenEMR\RestControllers\AuthorizationController;
use OpenEMR\Services\PatientService;
use Symfony\Component\EventDispatcher\EventDispatcher;

// not sure I really like this here... it seems like some of this
// should be encapsulated in a class that autoloading can reach.
require_once(__DIR__ . '/../../../_rest_config.php');

/**
 * Class SmartLaunchController handles the display and launching of SMART apps from the user interface.
 * @package OpenEMR\FHIR\SMART
 */
class SmartLaunchController
{
    const CLIENT_APP_REQUIRED_LAUNCH_SCOPE = 'launch';
    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    public function __construct(EventDispatcher $dispatcher = null)
    {
        $this->dispatcher = $dispatcher;
    }

    public function registerContextEvents()
    {
        $this->dispatcher->addListener(RenderEvent::EVENT_SECTION_LIST_RENDER_AFTER, [$this, 'renderPatientSmartLaunchSection']);
    }

    public function renderPatientSmartLaunchSection(RenderEvent $event)
    {
        if (empty($GLOBALS['rest_fhir_api']) && empty($GLOBALS['rest_portal_fhir_api'])) {
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
        (new UuidRegistry(['table_name' => 'patient_data']))->createMissingUuids();
        // going to work with string uuids
        $puuid = UuidRegistry::uuidToString($patientService->getUuid($pid));
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

            $gbl = \RestConfig::GetInstance();
            // TODO: adunsulag surely we can centralize where this fhir API url is set?
            // seem to get these URLs right.  for some reason the $SITE is set to interface, we don't get 'apis' in there
            // ROOT_URL appears to be empty.. just strange
            // $issuer = $GLOBALS['site_addr_oath'] . $gbl::$SITE . $gbl::$ROOT_URL . "/fhir";
//            $issuer = $GLOBALS['site_addr_oath'] . "/apis/default/fhir";
            $issuer = $GLOBALS['site_addr_oath'] . $GLOBALS['web_root'] . '/apis/' . $_SESSION['site_id'] . "/fhir";
            $launchParams = "?launch=" . urlencode($launchCode) . "&iss=" . urlencode($issuer);

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
                                <button class='btn btn-primary btn-sm smart-launch-btn' data-smart-name="<?php echo attr($client->getName()); ?>"
                                        data-smart-redirect-url="<?php echo attr($client->getLaunchUri($launchParams)); ?>">
                                    <?php echo xlt("Launch"); ?>
                                </button>
                                <?php echo text($client->getName()); ?>
                            </li>
                        <?php endforeach; ?>
                </ul>
            </div>
        </section>
        <?php
        // it's too bad we don't have a centralized page renderer we could tie this into and render javascript at the
        // end of our footer pages on everything...
        ?>
        <script>
            (function(window) {
                let smartLaunchers = document.querySelectorAll('.smart-launch-btn');
                for (let launch of smartLaunchers) {
                    let url =
                        launch.addEventListener('click', function(evt) {
                            let node = evt.target;
                            let url = node.dataset.smartRedirectUrl;
                            if (!url) {
                                return;
                            }
                            let title = node.dataset.smartName || "<?php echo xlt("Smart App"); ?>";
                            // we allow external dialog's  here because that is what a SMART app is
                            dlgopen(url, '_blank', 950, 650, '', title, {allowExternal: true});
                        });
                }
            })(window);

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
            } else {
                SystemLogger::instance()->debug(
                    "Skipping over client ",
                    [
                        "clientId" => $client->getIdentifier()
                        , "enabled" => $client->isEnabled()
                        , "hasLaunchScope" => $client->hasScope(self::CLIENT_APP_REQUIRED_LAUNCH_SCOPE)
                    ]
                );
            }
        }
        return $smartList;
    }

    private function getLaunchCodeContext($patientUUID, $encounterId = null)
    {
        $token = new SMARTLaunchToken($patientUUID, $encounterId);
        $token->setIntent(SMARTLaunchToken::INTENT_PATIENT_DEMOGRAPHICS_DIALOG);
        return $token->serialize();
    }
}
