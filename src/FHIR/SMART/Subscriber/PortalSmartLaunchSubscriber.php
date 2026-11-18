<?php

/**
 * PortalSmartLaunchSubscriber puts the patient's SMART apps on the patient portal dashboard (openemr/openemr#7115).
 *
 * It adds a "Health Apps" tile to the dashboard and a card listing each portal SMART app with a Launch button. The
 * launch URL is built when the page renders, for the signed-in portal patient only. Nothing is shown while the FHIR
 * API is off or no portal app is registered.
 *
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FHIR\SMART\Subscriber;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\PatientPortal\RenderEvent;
use OpenEMR\FHIR\Config\ServerConfig;
use OpenEMR\FHIR\SMART\PortalSmartAppLauncher;
use OpenEMR\Services\PatientService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Environment;

class PortalSmartLaunchSubscriber implements EventSubscriberInterface
{
    /** The id of the dashboard card the tile opens. */
    public const CARD_ID = 'smart-apps-card';

    /**
     * The apps and launch URLs for this request, once built: both portal events render from the same list.
     *
     * @var list<array{name: string, url: string}>|null
     */
    private ?array $apps = null;

    /**
     * Dependencies are built on first use: this subscriber is registered on every request, and the portal render
     * events fire only on the portal dashboard.
     */
    public function __construct(
        private ?PortalSmartAppLauncher $launcher = null,
        private ?Environment $twig = null,
        private ?OEGlobalsBag $globals = null,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RenderEvent::EVENT_DASHBOARD_INJECT_CARD => 'renderTile',
            RenderEvent::EVENT_SECTION_RENDER_POST => 'renderCard',
        ];
    }

    public function renderTile(): void
    {
        if ($this->portalApps() === []) {
            return;
        }
        echo $this->twig()->render('portal/partial/_nav_icon.html.twig', [
            'url' => self::CARD_ID,
            'icon' => 'mobile-screen',
            'navText' => xl('Health Apps'),
            'id' => 'smart-apps-go',
        ]);
    }

    public function renderCard(): void
    {
        $apps = $this->portalApps();
        if ($apps === []) {
            return;
        }
        echo $this->twig()->render('portal/partial/cards/_smart_apps_card.html.twig', [
            'id' => self::CARD_ID,
            'apps' => $apps,
        ]);
    }

    /**
     * @return list<array{name: string, url: string}>
     */
    private function portalApps(): array
    {
        if ($this->apps !== null) {
            return $this->apps;
        }
        $this->apps = [];
        if (!$this->globals()->getBoolean('rest_fhir_api')) {
            return $this->apps;
        }
        $patientUuid = $this->portalPatientUuid();
        if ($patientUuid === null) {
            return $this->apps;
        }
        $launcher = $this->launcher();
        foreach ($launcher->listPortalApps() as $client) {
            $this->apps[] = [
                'name' => (string) $client->getName(),
                'url' => $launcher->launchUrl($client, $patientUuid),
            ];
        }
        return $this->apps;
    }

    /**
     * The signed-in portal patient's uuid, or null outside an authenticated portal session.
     */
    private function portalPatientUuid(): ?string
    {
        $session = SessionWrapperFactory::getInstance()->getPortalSession();
        $pid = $session->get('pid');
        $signedIn = $session->get('patient_portal_onsite_two');
        if ($signedIn === null || $signedIn === false || $signedIn === '' || $signedIn === 0) {
            return null;
        }
        if (is_int($pid)) {
            $pid = (string) $pid;
        }
        if (!is_string($pid) || $pid === '') {
            return null;
        }
        $uuid = (new PatientService())->getUuid($pid);
        if (!is_string($uuid) || $uuid === '') {
            return null;
        }
        return UuidRegistry::uuidToString($uuid);
    }

    private function launcher(): PortalSmartAppLauncher
    {
        return $this->launcher ??= new PortalSmartAppLauncher(new ClientRepository(), (new ServerConfig())->getFhirUrl());
    }

    private function twig(): Environment
    {
        return $this->twig ??= ServiceContainer::getTwig();
    }

    private function globals(): OEGlobalsBag
    {
        return $this->globals ??= OEGlobalsBag::getInstance();
    }
}
