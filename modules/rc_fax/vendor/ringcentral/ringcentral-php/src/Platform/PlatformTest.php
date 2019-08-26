<?php

use GuzzleHttp\Psr7\Request;
use RingCentral\SDK\Mocks\Mock;
use RingCentral\SDK\SDK;
use RingCentral\SDK\Test\TestCase;

class PlatformTest extends TestCase
{

    public function testLogin()
    {

        $sdk = $this->getSDK();
        $authData = $sdk->platform()->auth()->data();
        $this->assertTrue(!empty($authData['access_token']));

    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Refresh token has expired
     */
    public function testRefreshWithOutdatedToken()
    {

        $sdk = $this->getSDK();

        $sdk->platform()->auth()->setData(array(
            'refresh_token_expires_in'  => 1,
            'refresh_token_expire_time' => 1
        ));

        $sdk->platform()->refresh();

    }

    public function testAutomaticRefresh()
    {

        $sdk = $this->getSDK(array(
            $this->refreshMock(),
            $this->createResponse('GET', '/foo', array('foo' => 'bar'))
        ));

        $sdk->platform()->auth()->setData(array(
            'expires_in'  => 1,
            'expire_time' => 1
        ));

        $this->assertEquals('bar', $sdk->platform()->get('/foo')->json()->foo);

        $this->assertEquals('ACCESS_TOKEN_FROM_REFRESH', $sdk->platform()->auth()->accessToken());
        $this->assertTrue($sdk->platform()->loggedIn());

    }

    public function testLogout()
    {

        $sdk = $this->getSDK(array(
            $this->logoutMock()
        ));

        $sdk->platform()->logout();

        $authData = $sdk->platform()->auth()->data();

        $this->assertEquals('', $authData['access_token']);
        $this->assertEquals('', $authData['refresh_token']);

    }

    public function testApiUrl()
    {

        $sdk = $this->getSDK();

        $this->assertEquals(
            'https://whatever/restapi/v1.0/account/~/extension/~?_method=POST&access_token=ACCESS_TOKEN',
            $sdk->platform()->createUrl('/account/~/extension/~', array(
                'addServer' => true,
                'addMethod' => 'POST',
                'addToken'  => true
            ))
        );

        $this->assertEquals(
            'https://foo/account/~/extension/~?_method=POST&access_token=ACCESS_TOKEN',
            $sdk->platform()->createUrl('https://foo/account/~/extension/~', array(
                'addServer' => true,
                'addMethod' => 'POST',
                'addToken'  => true
            ))
        );

    }

    public function testProcessRequest()
    {

        $sdk = $this->getSDK(array(
            $this->createResponse('GET', '/foo', array('foo' => 'bar'))
        ));

        $request = $sdk->platform()->inflateRequest(new Request('GET', '/foo'));

        $this->assertEquals('https://whatever/restapi/v1.0/foo', (string)$request->getUri());

        $this->assertEquals($request->getHeaderLine('User-Agent'), $request->getHeaderLine('RC-User-Agent'));

        $this->assertTrue(!!$request->getHeaderLine('User-Agent'));
        $this->assertContains('RCPHPSDK/' . SDK::VERSION, $request->getHeaderLine('User-Agent'));
        $this->assertContains('SDKTests/' . SDK::VERSION, $request->getHeaderLine('User-Agent'));

    }

}