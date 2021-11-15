<?php

namespace kamermans\OAuth2\Tests\Signer\ClientCredentials;

use kamermans\OAuth2\Tests\BaseTestCase;
use kamermans\OAuth2\Signer\ClientCredentials\BasicAuth;

class BasicAuthTest extends BaseTestCase
{
    public function testSign()
    {
        $clientId = 'foo';
        $clientSecret = 'bar';

        $request = $this->createRequest('GET', '/');
        $signer = new BasicAuth();
        $request = $signer->sign($request, $clientId, $clientSecret);

        $this->assertEquals('Basic '.base64_encode($clientId.':'.$clientSecret), $this->getHeader($request, 'Authorization'));
    }
}
