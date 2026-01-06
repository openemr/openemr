<?php

namespace OpenEMR\Tests\RestControllers\Authorization;

use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClaimRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ScopeRepository;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\RestControllers\Authorization\OAuth2DiscoveryController;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorageFactory;

class OAuth2DiscoveryControllerTest extends TestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function testGetDiscoveryResponseWithPasswordGrant(): void
    {
        $globals = new OEGlobalsBag([
            'oauth_password_grant' => 1
        ]);
        $baseUrl = 'https://example.com';

        $claimsRepository = $this->createMock(ClaimRepository::class);
        $claimsRepository->expects($this->once())
            ->method('getSupportedClaims')
            ->willReturn(['email', 'profile', 'openid']);
        $scopeRepository = $this->createMock(ScopeRepository::class);
        $scopeRepository->expects($this->once())
            ->method('getCurrentSmartScopes')
            ->willReturn(['patient/Patient.read', 'user/Practitioner.read']);
        $oauth2DiscoveryController = new OAuth2DiscoveryController(
            $claimsRepository,
            $scopeRepository,
            $globals,
            $baseUrl
        );
        $request = HttpRestRequest::create('/oauth2/.well-known/discovery');
        $session = new Session((new MockFileSessionStorageFactory())->createStorage($request));
        $sessionId = $session->getId();
        $request->setSession($session);
        $session->set('randomvalue', true);
        $response = $oauth2DiscoveryController->getDiscoveryResponse($request);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotEmpty($content);
        $json = json_decode($content, true);
        $this->assertEquals($json['issuer'], $baseUrl, 'Issuer should match the base URL');
        $this->assertStringContainsString('"password",', $content, 'Password grant should be included in the response');
        $this->assertEquals($json['authorization_endpoint'], $baseUrl . '/authorize', 'Authorization endpoint should match base URL');


        $this->assertNotEmpty($json['scopes_supported'], 'Scopes should not be empty');
        $this->assertContains('patient/Patient.read', $json['scopes_supported'], 'Patient read scope should be supported');
        $this->assertContains('user/Practitioner.read', $json['scopes_supported'], 'Practitioner read scope should be supported');

        $this->assertNotEmpty($json['claims_supported'], 'Claims should not be empty');
        $this->assertContains('email', $json['claims_supported'], 'Email claim should be supported');
        $this->assertContains('profile', $json['claims_supported'], 'Profile claim should be supported');
        $this->assertContains('openid', $json['claims_supported'], 'OpenID claim should be supported');

        // now verify the session was invalidated
        $this->assertFalse($session->has('randomvalue'), 'Session variable should be removed after discovery response');
        // old session was invalidated and a new session was created
        $this->assertNotEquals($sessionId, $session->getId(), 'Session ID should change after discovery response');
    }
}
