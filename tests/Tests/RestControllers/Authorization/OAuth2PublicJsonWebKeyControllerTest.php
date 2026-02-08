<?php

namespace OpenEMR\Tests\RestControllers\Authorization;

use League\OAuth2\Server\Exception\OAuthServerException;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\RestControllers\Authorization\OAuth2PublicJsonWebKeyController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorageFactory;

class OAuth2PublicJsonWebKeyControllerTest extends TestCase
{
    /**
     * @return void
     * @throws OAuthServerException
     */
    public function testGetJsonWebKeyResponse(): void
    {
        $publicKeyPath = __DIR__ . '/../../data/Unit/Common/Auth/Grant/openemr-rsa384-public.pem';
        $controller = new OAuth2PublicJsonWebKeyController($publicKeyPath);

        $request = HttpRestRequest::create('/oauth2/jwk');
        $session = new Session((new MockFileSessionStorageFactory())->createStorage($request));
        $session->set("somevalue", "testvalue");
        $request->setSession($session);
        $sessionId = $session->getId();
        $response = $controller->getJsonWebKeyResponse($request);

        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotEmpty($content, 'Response content should not be empty');

        $jsonData = json_decode($content, true);
        $this->assertArrayHasKey('keys', $jsonData, 'Response should contain "keys" key');
        $this->assertCount(1, $jsonData['keys'], 'There should be one key in the response');
        $this->assertEmpty($session->all(), 'Session should be empty after processing the request');
        $this->assertNotEquals($sessionId, $session->getId(), 'Session ID should change after processing the request');
    }
}
