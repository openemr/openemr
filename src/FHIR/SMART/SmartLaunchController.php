<?php

namespace OpenEMR\FHIR\SMART;

use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
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
        $smartClients = $this->getSMARTClients();
        // TODO: adunsulag we would filter the clients based on their smart capability & scopes they could send...
        $pid = $event->getPid();
        $patientService = new PatientService();
        $puuid = $patientService->getUuid($pid);
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
            // TODO: adunsulag what is wrong with these URL's?  I'm having to hard code the issuer as I can't
            // seem to get these URLs right.  for some reason the $SITE is set to interface, we don't get 'apis' in there
            // ROOT_URL appears to be empty.. just strange
            // $issuer = $GLOBALS['site_addr_oath'] . $gbl::$SITE . $gbl::$ROOT_URL . "/fhir";
            $issuer = $GLOBALS['site_addr_oath'] . "/apis/default/fhir";
            $launchParams = "launch.html?launch=" . $launchCode . "&iss=" . $issuer;

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
                    <li>
                        <?php if (empty($smartClients)) : ?>
                        <p><?php echo xlt("No registered SMART apps in the system"); ?></p>
                        <?php endif; ?>
                        <?php foreach ($smartClients as $client) : ?>
                            <?php echo $client->getName(); ?>
                            <a href="<?php echo $client->getRedirectUri() . $launchParams; ?>">Launch</a>
                        <?php endforeach; ?>
                    </li>
                </ul>
            </div>
        </section>
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
            if ($client->hasScope("launch")) {
                $smartList[] = $client;
            }
        }
        return $smartList;
    }

    // TODO: adunsulag look at moving this to AuthorizationController or a better spot where we can serialize /
    // deserialize the launch code
    private function getLaunchCodeContext($patientUUID, $encounterId = null)
    {
        // no security is really needed here... just need to be able to wrap
        // the current context into some kind of opaque id that the app will pass to the server and we can then
        // return to system
        // TODO: adunsulag do we want a nonce here? don't think it will matter as user has to pass through oauth2 grant
        // in order to get back the launch code.
        $launchCode = ['p' => UuidRegistry::uuidToString($patientUUID), 'e' => $encounterId];
        $launchCode = base64_encode(json_encode($launchCode));
        return $launchCode;
    }
}