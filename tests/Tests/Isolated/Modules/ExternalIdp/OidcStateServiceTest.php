<?php

/**
 * Isolated tests for the External IdP OIDC state helpers.
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\ExternalIdp;

require_once __DIR__ . '/bootstrap.php';

use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Modules\ExternalIdp\Service\OidcStateService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

final class OidcStateServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['web_root'] = '';
        $this->resetSessionFactory();
    }

    protected function tearDown(): void
    {
        $this->resetSessionFactory();
        parent::tearDown();
    }

    public function testPkceChallengeMatchesKnownExample(): void
    {
        $verifier = 'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk';

        self::assertSame(
            'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM',
            OidcStateService::createPkceChallenge($verifier)
        );
    }

    public function testNormalizeSiteIdFallsBackToDefaultForInvalidInput(): void
    {
        self::assertSame('default', OidcStateService::normalizeSiteId('../../evil'));
        self::assertSame('site-1', OidcStateService::normalizeSiteId('site-1'));
    }

    public function testBuildReturnTargetIncludesNormalizedSiteId(): void
    {
        $target = OidcStateService::buildReturnTarget('site-1');

        self::assertStringContainsString('/interface/main/main_screen.php?auth=external&site=site-1', $target);
    }

    public function testStoreAndConsumeRoundTripOneTimeState(): void
    {
        $session = new Session(new MockArraySessionStorage());
        SessionWrapperFactory::getInstance()->setActiveSession($session);

        $service = new OidcStateService();
        $state = $service->store('provider-1', 'site-1', [
            'languageChoice' => '3',
            'facility' => 17,
            'appChoice' => 'billing',
            'ignored' => 'nope',
        ]);

        self::assertSame('provider-1', $state['provider_id']);
        self::assertSame('site-1', $state['site_id']);
        self::assertSame(['languageChoice' => '3', 'facility' => 17, 'appChoice' => 'billing'], $state['login_options']);
        self::assertSame($state, $session->get('external_idp_oidc_pending'));

        $consumed = $service->consume((string) $state['state']);

        self::assertSame($state['state'], $consumed['state']);
        self::assertNull($session->get('external_idp_oidc_pending'));
    }

    public function testConsumeRejectsWrongStateAndClearsSession(): void
    {
        $session = new Session(new MockArraySessionStorage());
        SessionWrapperFactory::getInstance()->setActiveSession($session);

        $service = new OidcStateService();
        $service->store('provider-1', 'site-1', []);

        try {
            $service->consume('wrong-state');
            self::fail('Expected consume() to reject the wrong state value.');
        } catch (\RuntimeException $exception) {
            self::assertSame('OIDC authentication state did not match.', $exception->getMessage());
        }

        self::assertNull($session->get('external_idp_oidc_pending'));
    }

    private function resetSessionFactory(): void
    {
        $reflection = new ReflectionClass(SessionWrapperFactory::class);
        $instancesProperty = $reflection->getProperty('instances');
        $instancesProperty->setValue(null, []);
    }
}
