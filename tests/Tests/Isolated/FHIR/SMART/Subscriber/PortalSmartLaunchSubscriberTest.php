<?php

/**
 * Isolated-tier coverage for PortalSmartLaunchSubscriber, the subscriber
 * openemr/openemr#15272 added to the portal dashboard-render events.
 *
 * Complements the browser-tier positive (A2, openemr/openemr#15296) and
 * cross-patient replay (A3, openemr/openemr#15297) tests. Those exercise
 * the subscriber wired end-to-end against a running stack. This file
 * exercises the subscriber's skip branches with cheap pure-PHP mocks,
 * so a regression in the no-render guards surfaces without the
 * Panther + compose cost of a full smoke run.
 *
 * Three skip branches are documented in the subscriber (and the
 * §8.2 A4 register entry): FHIR API off, no portal-patient session,
 * and no portal apps resolved. The third surfaces only after the
 * first two pass, so the two renderTile/renderCard happy-path tests
 * below use reflection to set the private `$apps` memo cache rather
 * than driving through portalApps() (which would reach into
 * PatientService::getUuid — a DB-backed call that is out of scope for
 * the isolated tier). The services-tier integration test for
 * PortalSmartAppLauncher (A5, future) covers the DB-backed filter
 * directly.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FHIR\SMART\Subscriber;

use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\FHIR\SMART\Subscriber\PortalSmartLaunchSubscriber;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Environment;

class PortalSmartLaunchSubscriberTest extends TestCase
{
    /**
     * @var array<mixed>
     */
    private array $originalServer;

    /**
     * @var array<mixed>
     */
    private array $originalCookie;

    /**
     * @var array<mixed>|null null when $_SESSION was unset on entry to the test.
     */
    private ?array $originalSession = null;

    private bool $originalSessionWasSet = false;

    /**
     * @var array<class-string<SessionWrapperFactory>, SessionWrapperFactory>
     */
    private array $originalSessionFactoryInstances = [];

    /**
     * @var array<class-string<OEGlobalsBag>, OEGlobalsBag>
     */
    private array $originalGlobalsBagInstances = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalServer = $_SERVER;
        $this->originalCookie = $_COOKIE;
        $this->originalSessionWasSet = isset($_SESSION);
        $this->originalSession = $this->originalSessionWasSet ? $_SESSION : null;
        $_SESSION = [];

        // Save the full singleton maps so teardown can restore whatever state
        // a prior fixture left — clearing without saving would drop any
        // caller-installed activeSession/activeStorage on SessionWrapperFactory
        // and any configured globals on OEGlobalsBag.
        $this->originalSessionFactoryInstances = $this->readSingletonInstances(SessionWrapperFactory::class);
        $this->originalGlobalsBagInstances = $this->readSingletonInstances(OEGlobalsBag::class);
        $this->writeSingletonInstances(SessionWrapperFactory::class, []);

        // Stub OEGlobalsBag with disable_translation=true so xl() called from
        // renderTile() (for `navText`) short-circuits before reaching the
        // SessionWrapperFactory + sqlStatementNoLog() translation path — that
        // path needs a database the isolated tier doesn't have. The subscriber
        // itself reads globals through the constructor-injected mock, so this
        // stub only affects xl().
        $this->installTranslationStubGlobalsBag();
    }

    protected function tearDown(): void
    {
        $this->writeSingletonInstances(SessionWrapperFactory::class, $this->originalSessionFactoryInstances);
        $this->writeSingletonInstances(OEGlobalsBag::class, $this->originalGlobalsBagInstances);
        $_SERVER = $this->originalServer;
        $_COOKIE = $this->originalCookie;
        if ($this->originalSessionWasSet) {
            $_SESSION = $this->originalSession ?? [];
        } else {
            unset($_SESSION);
        }
        parent::tearDown();
    }

    #[Test]
    public function testRenderTileEchoesNothingWhenFhirApiIsOff(): void
    {
        $globals = $this->mockGlobalsWithFhirApi(false);
        // No session wiring — the subscriber returns before touching the session.
        $twig = $this->createMock(Environment::class);
        $twig->expects(self::never())->method('render');
        // Launcher is `final readonly` and can't be mocked; passing
        // null is safe here because the subscriber short-circuits
        // before reaching its launcher() getter. If the short-circuit
        // regresses and the getter runs, it instantiates a real
        // PortalSmartAppLauncher backed by `new ClientRepository()`
        // — the isolated run has no DB, so the TypeError or
        // DB-connection failure that results is a loud regression
        // signal, not a silent pass.
        $launcher = null;

        $subscriber = new PortalSmartLaunchSubscriber($launcher, $twig, $globals);
        self::assertSame('', $this->captureOutput(fn () => $subscriber->renderTile()));
    }

    #[Test]
    public function testRenderCardEchoesNothingWhenFhirApiIsOff(): void
    {
        $globals = $this->mockGlobalsWithFhirApi(false);
        $twig = $this->createMock(Environment::class);
        $twig->expects(self::never())->method('render');
        // Launcher is `final readonly` and can't be mocked; passing
        // null is safe here because the subscriber short-circuits
        // before reaching its launcher() getter. If the short-circuit
        // regresses and the getter runs, it instantiates a real
        // PortalSmartAppLauncher backed by `new ClientRepository()`
        // — the isolated run has no DB, so the TypeError or
        // DB-connection failure that results is a loud regression
        // signal, not a silent pass.
        $launcher = null;

        $subscriber = new PortalSmartLaunchSubscriber($launcher, $twig, $globals);
        self::assertSame('', $this->captureOutput(fn () => $subscriber->renderCard()));
    }

    #[Test]
    public function testRenderTileEchoesNothingWhenNoSignedInPortalPatient(): void
    {
        // FHIR API on, but no portal patient signed in — the
        // portalPatientUuid() guard returns null before reaching
        // PatientService::getUuid (which would need a DB). patient_portal_onsite_two
        // being null is the first short-circuit in that method.
        $globals = $this->mockGlobalsWithFhirApi(true);
        $this->installPortalSessionMock(patientPortalOnsiteTwo: null, pid: null);
        $twig = $this->createMock(Environment::class);
        $twig->expects(self::never())->method('render');
        // Launcher is `final readonly` and can't be mocked; passing
        // null is safe here because the subscriber short-circuits
        // before reaching its launcher() getter. If the short-circuit
        // regresses and the getter runs, it instantiates a real
        // PortalSmartAppLauncher backed by `new ClientRepository()`
        // — the isolated run has no DB, so the TypeError or
        // DB-connection failure that results is a loud regression
        // signal, not a silent pass.
        $launcher = null;

        $subscriber = new PortalSmartLaunchSubscriber($launcher, $twig, $globals);
        self::assertSame('', $this->captureOutput(fn () => $subscriber->renderTile()));
    }

    #[Test]
    public function testRenderCardEchoesNothingWhenNoSignedInPortalPatient(): void
    {
        $globals = $this->mockGlobalsWithFhirApi(true);
        $this->installPortalSessionMock(patientPortalOnsiteTwo: null, pid: null);
        $twig = $this->createMock(Environment::class);
        $twig->expects(self::never())->method('render');
        // Launcher is `final readonly` and can't be mocked; passing
        // null is safe here because the subscriber short-circuits
        // before reaching its launcher() getter. If the short-circuit
        // regresses and the getter runs, it instantiates a real
        // PortalSmartAppLauncher backed by `new ClientRepository()`
        // — the isolated run has no DB, so the TypeError or
        // DB-connection failure that results is a loud regression
        // signal, not a silent pass.
        $launcher = null;

        $subscriber = new PortalSmartLaunchSubscriber($launcher, $twig, $globals);
        self::assertSame('', $this->captureOutput(fn () => $subscriber->renderCard()));
    }

    #[Test]
    public function testRenderTileEmitsHealthAppsTileWhenAppsArePresent(): void
    {
        // Short-circuit the apps-resolution path by pre-populating the
        // subscriber's private $apps memo. The point of this test is
        // the renderTile template invocation, not the (DB-backed)
        // apps-listing logic that A5 (services tier) covers.
        $preloadedApps = [['name' => 'Visit Prep', 'url' => 'https://app.example/launch.php?mode=portal&launch=xyz']];
        $twig = $this->createMock(Environment::class);
        $twig->expects(self::once())
            ->method('render')
            ->with(
                'portal/partial/_nav_icon.html.twig',
                self::callback(static fn(array $vars): bool => ($vars['url'] ?? null) === PortalSmartLaunchSubscriber::CARD_ID
                    && ($vars['icon'] ?? null) === 'mobile-screen'
                    && ($vars['id'] ?? null) === 'smart-apps-go'
                    // navText is translation output — xl() with disable_translation off would
                    // fetch; with it on it returns the source string. Either way the key is
                    // present, which is what matters structurally.
                    && array_key_exists('navText', $vars))
            )
            ->willReturn('<nav-icon html>');
        // Launcher is `final readonly` and can't be mocked; passing
        // null is safe here because the subscriber short-circuits
        // before reaching its launcher() getter. If the short-circuit
        // regresses and the getter runs, it instantiates a real
        // PortalSmartAppLauncher backed by `new ClientRepository()`
        // — the isolated run has no DB, so the TypeError or
        // DB-connection failure that results is a loud regression
        // signal, not a silent pass.
        $launcher = null;
        $globals = $this->mockGlobalsWithFhirApi(true);

        $subscriber = new PortalSmartLaunchSubscriber($launcher, $twig, $globals);
        $this->setPrivateAppsCache($subscriber, $preloadedApps);

        self::assertSame('<nav-icon html>', $this->captureOutput(fn () => $subscriber->renderTile()));
    }

    #[Test]
    public function testRenderCardEmitsSmartAppsCardWhenAppsArePresent(): void
    {
        $preloadedApps = [['name' => 'Visit Prep', 'url' => 'https://app.example/launch.php?mode=portal&launch=xyz']];
        $twig = $this->createMock(Environment::class);
        $twig->expects(self::once())
            ->method('render')
            ->with(
                'portal/partial/cards/_smart_apps_card.html.twig',
                ['id' => PortalSmartLaunchSubscriber::CARD_ID, 'apps' => $preloadedApps]
            )
            ->willReturn('<smart-apps-card html>');
        // Launcher is `final readonly` and can't be mocked; passing
        // null is safe here because the subscriber short-circuits
        // before reaching its launcher() getter. If the short-circuit
        // regresses and the getter runs, it instantiates a real
        // PortalSmartAppLauncher backed by `new ClientRepository()`
        // — the isolated run has no DB, so the TypeError or
        // DB-connection failure that results is a loud regression
        // signal, not a silent pass.
        $launcher = null;
        $globals = $this->mockGlobalsWithFhirApi(true);

        $subscriber = new PortalSmartLaunchSubscriber($launcher, $twig, $globals);
        $this->setPrivateAppsCache($subscriber, $preloadedApps);

        self::assertSame(
            '<smart-apps-card html>',
            $this->captureOutput(fn () => $subscriber->renderCard())
        );
    }

    #[Test]
    public function testGetSubscribedEventsBindsBothPortalRenderEvents(): void
    {
        // Low-ceremony contract test — if either event-key goes missing,
        // the subscriber silently stops firing and the Health Apps UI
        // disappears from the portal dashboard without any assertion
        // tripping at the render-method tier.
        $events = PortalSmartLaunchSubscriber::getSubscribedEvents();
        self::assertArrayHasKey('home.dashboard.inject.card', $events);
        self::assertSame('renderTile', $events['home.dashboard.inject.card']);
        self::assertArrayHasKey('home.section.render.post', $events);
        self::assertSame('renderCard', $events['home.section.render.post']);
    }

    private function mockGlobalsWithFhirApi(bool $value): OEGlobalsBag
    {
        $globals = $this->createMock(OEGlobalsBag::class);
        $globals->method('getBoolean')
            ->with('rest_fhir_api')
            ->willReturn($value);
        return $globals;
    }

    /**
     * Install a mock SessionInterface into the SessionWrapperFactory
     * singleton so the subscriber's portalPatientUuid() reads from a
     * controlled session rather than PHP's real $_SESSION.
     *
     * SessionWrapperFactory::getPortalSession() returns $this->activeSession
     * when it's set, so setActiveSession() is enough to redirect reads.
     */
    private function installPortalSessionMock(mixed $patientPortalOnsiteTwo, mixed $pid): void
    {
        // Avoid SessionWrapperFactory::getInstance()'s createInstance()
        // path (which reads OEGlobalsBag::getInstance()->getString('web_root')
        // and brings in the whole globals chain) by instantiating a fresh
        // factory with an empty web root and planting it as the singleton.
        $factory = new SessionWrapperFactory('');
        $this->writeSingletonInstances(SessionWrapperFactory::class, [SessionWrapperFactory::class => $factory]);

        $session = $this->createMock(SessionInterface::class);
        $session->method('get')
            ->willReturnMap([
                ['patient_portal_onsite_two', null, $patientPortalOnsiteTwo],
                ['pid', null, $pid],
            ]);
        $factory->setActiveSession($session);
    }

    private function installTranslationStubGlobalsBag(): void
    {
        $globals = new OEGlobalsBag(['disable_translation' => true]);
        $this->writeSingletonInstances(OEGlobalsBag::class, [OEGlobalsBag::class => $globals]);
    }

    /**
     * @template T of SessionWrapperFactory|OEGlobalsBag
     * @param class-string<T> $singleton
     * @return array<class-string<T>, T>
     */
    private function readSingletonInstances(string $singleton): array
    {
        $property = (new ReflectionClass($singleton))->getProperty('instances');
        /** @var array<class-string<T>, T> $value */
        $value = $property->getValue();
        return $value;
    }

    /**
     * @template T of SessionWrapperFactory|OEGlobalsBag
     * @param class-string<T> $singleton
     * @param array<class-string<T>, T> $instances
     */
    private function writeSingletonInstances(string $singleton, array $instances): void
    {
        $property = (new ReflectionClass($singleton))->getProperty('instances');
        $property->setValue(null, $instances);
    }

    /**
     * @param list<array{name: string, url: string}> $apps
     */
    private function setPrivateAppsCache(PortalSmartLaunchSubscriber $subscriber, array $apps): void
    {
        $property = (new ReflectionClass(PortalSmartLaunchSubscriber::class))->getProperty('apps');
        $property->setValue($subscriber, $apps);
    }

    private function captureOutput(callable $callback): string
    {
        ob_start();
        try {
            $callback();
            return (string) ob_get_contents();
        } finally {
            ob_end_clean();
        }
    }
}
