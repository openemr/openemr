<?php

namespace kamermans\OAuth2\Tests\Signer\ClientCredentials;

use kamermans\OAuth2\Tests\BaseTestCase;
use kamermans\OAuth2\Signer\ClientCredentials\PostFormData;

class PostFormDataTest extends BaseTestCase
{
    public function testSign()
    {
        $clientId = 'foo';
        $clientSecret = 'bar';

        $clientIdFieldName = 'client_id';
        $clientSecretFieldName = 'client_secret';

        $request = $this->createRequest('GET', '/', [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]);
        $request = $this->setPostBody($request, []);

        $signer = new PostFormData();
        $request = $signer->sign($request, $clientId, $clientSecret);

        $this->assertEquals($clientId, $this->getFormPostBodyValue($request, $clientIdFieldName));
        $this->assertEquals($clientSecret, $this->getFormPostBodyValue($request, $clientSecretFieldName));
    }

    public function testSignCustomFields()
    {
        $clientId = 'foo';
        $clientSecret = 'bar';

        $clientIdFieldName = 'foo_id';
        $clientSecretFieldName = 'foo_secret';

        $request = $this->createRequest('GET', '/', [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]);
        $request = $this->setPostBody($request, []);

        $signer = new PostFormData($clientIdFieldName, $clientSecretFieldName);
        $request = $signer->sign($request, $clientId, $clientSecret);

        $this->assertEquals($clientId, $this->getFormPostBodyValue($request, $clientIdFieldName));
        $this->assertEquals($clientSecret, $this->getFormPostBodyValue($request, $clientSecretFieldName));
    }
}
