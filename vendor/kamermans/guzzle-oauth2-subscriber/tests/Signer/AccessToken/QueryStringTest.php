<?php

namespace kamermans\OAuth2\Tests\Signer\AccessToken;

use kamermans\OAuth2\Tests\BaseTestCase;
use kamermans\OAuth2\Signer\AccessToken\QueryString;

class QueryStringTest extends BaseTestCase
{
    public function testSign()
    {
        $fieldName = 'access_token';

        $request = $this->createRequest('GET', '/');

        $signer = new QueryString();
        $request = $signer->sign($request, 'foobar');

        $this->assertEquals('foobar', $this->getQueryStringValue($request, $fieldName));
    }

    public function testSignCustomField()
    {
        $fieldName = 'someotherfieldname';

        $request = $this->createRequest('GET', '/');

        $signer = new QueryString($fieldName);
        $request = $signer->sign($request, 'foobar');

        $this->assertEquals('foobar', $this->getQueryStringValue($request, $fieldName));
    }
}
