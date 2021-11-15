<?php

namespace kamermans\OAuth2\Tests\Signer\AccessToken;

use kamermans\OAuth2\Tests\BaseTestCase;
use kamermans\OAuth2\Signer\AccessToken\BasicAuth;

class BasicAuthTest extends BaseTestCase
{
    public function testSign()
    {
        $request = $this->createRequest('GET', '/');

        $signer = new BasicAuth();
        $request = $signer->sign($request, 'foobar');

        $this->assertEquals('Bearer foobar', $this->getHeader($request, 'Authorization'));
    }
}
