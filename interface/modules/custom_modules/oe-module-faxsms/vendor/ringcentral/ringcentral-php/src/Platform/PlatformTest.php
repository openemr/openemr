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

    public function testLoginWithPassword()
    {
        $sdk = $this->getSDK(
            [
                $this->authenticationMock(),
            ],
            false
        );
        $sdk->platform()->login('username', 'extension', 'password');
        $authData = $sdk->platform()->auth()->data();
        $this->assertTrue(!empty($authData['access_token']));
    }

    public function testRefreshWithOutdatedToken()
    {

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Refresh token has expired');

        $sdk = $this->getSDK();

        $sdk->platform()->auth()->setData([
            'refresh_token_expires_in' => 1,
            'refresh_token_expire_time' => 1
        ]);

        $sdk->platform()->refresh();

    }

    public function testAutomaticRefresh()
    {

        $sdk = $this->getSDK([
            $this->refreshMock(),
            $this->createResponse('GET', '/foo', ['foo' => 'bar'])
        ]);

        $sdk->platform()->auth()->setData([
            'expires_in' => 1,
            'expire_time' => 1
        ]);

        $this->assertEquals('bar', $sdk->platform()->get('/foo')->json()->foo);

        $this->assertEquals('ACCESS_TOKEN_FROM_REFRESH', $sdk->platform()->auth()->accessToken());
        $this->assertTrue($sdk->platform()->loggedIn());

    }

    public function testLogout()
    {

        $sdk = $this->getSDK([
            $this->logoutMock()
        ]);

        $sdk->platform()->logout();

        $authData = $sdk->platform()->auth()->data();

        $this->assertEquals('', $authData['access_token']);
        $this->assertEquals('', $authData['refresh_token']);

    }

    public function testAuthUrl()
    {
        $sdk = $this->getSDK();
        $url = $sdk->platform()->authUrl(
            array(
                'redirectUri' => 'foo',
                'state' => 'bar',
                'client_id' => 'baz'
            )
        );
        $this->assertEquals($url, "https://whatever/restapi/oauth/authorize?response_type=code&redirect_uri=foo&client_id=whatever&state=bar");
    }

    public function testApiUrl()
    {
        $sdk = $this->getSDK();

        $this->assertEquals(
            'https://whatever/restapi/v1.0/account/~/extension/~?_method=POST&access_token=ACCESS_TOKEN',
            $sdk->platform()->createUrl('/account/~/extension/~', [
                'addServer' => true,
                'addMethod' => 'POST',
                'addToken' => true
            ])
        );

        $this->assertEquals(
            'https://whatever/restapi/v1.0/account/~/extension/~',
            $sdk->platform()->createUrl('/account/~/extension/~', [
                'addServer' => true
            ])
        );

        $this->assertEquals(
            'https://whatever/rcvideo/v2/account/~/extension/~/bridges',
            $sdk->platform()->createUrl('/rcvideo/v2/account/~/extension/~/bridges', [
                'addServer' => true
            ])
        );

        $this->assertEquals(
            'https://whatever/scim/v2/ServiceProviderConfig',
            $sdk->platform()->createUrl('/scim/v2/ServiceProviderConfig', [
                'addServer' => true
            ])
        );

        $this->assertEquals(
            'https://whatever/cx/some-api',
            $sdk->platform()->createUrl('/cx/some-api', [
                'addServer' => true
            ])
        );

        $this->assertEquals(
            'https://whatever/analytics/phone/performance/v1/accounts/accountId/calls/aggregate',
            $sdk->platform()->createUrl('/analytics/phone/performance/v1/accounts/accountId/calls/aggregate', [
                'addServer' => true
            ])
        );

        $this->assertEquals(
            'https://foo/account/~/extension/~?_method=POST&access_token=ACCESS_TOKEN',
            $sdk->platform()->createUrl('https://foo/account/~/extension/~', [
                'addServer' => true,
                'addMethod' => 'POST',
                'addToken' => true
            ])
        );

    }

    public function testProcessRequest()
    {

        $sdk = $this->getSDK([
            $this->createResponse('GET', '/foo', ['foo' => 'bar'])
        ]);

        $request = $sdk->platform()->inflateRequest(new Request('GET', '/foo'));

        $this->assertEquals('https://whatever/restapi/v1.0/foo', (string) $request->getUri());

        $this->assertEquals($request->getHeaderLine('User-Agent'), $request->getHeaderLine('RC-User-Agent'));

        $this->assertTrue(!!$request->getHeaderLine('User-Agent'));
        $this->assertStringContainsString('RCPHPSDK/' . SDK::VERSION, $request->getHeaderLine('User-Agent'));
        $this->assertStringContainsString('SDKTests/' . SDK::VERSION, $request->getHeaderLine('User-Agent'));

    }

    public function testDeleteWithBody()
    {
        $body = ["keepAssetsInInventory" => true, "records" => [["id" => "123"]]];
        $sdk = $this->getSDK([
            $this->createResponse('DELETE', '/restapi/v2/accounts/~/extensions', ["keepAssetsInInventory" => true, "records" => [["id" => "123"]]])
        ]);
        $response = $sdk->platform()->delete("/restapi/v2/accounts/~/extensions", $body);
        $this->assertEquals($response->body(), json_encode($body));
    }
    public function testDeleteWithoutBody()
    {
        $sdk = $this->getSDK([
            $this->createResponse('DELETE', '/restapi/v2/accounts/~/extensions', ["Success"])
        ]);
        $response = $sdk->platform()->delete("/restapi/v2/accounts/~/extensions");
        $this->assertEquals($response->body(), json_encode(["Success"]));
    }

    public function testDeleteWithParamsBody()
    {
        $body = [
            'param1' => 'value1',
            'param2' => 'value2'
        ];
        $sdk = $this->getSDK([
            $this->createResponse('DELETE', '/restapi/v2/accounts/~/extensions', [
                'param1' => 'value1',
                'param2' => 'value2'
            ])
        ]);
        $response = $sdk->platform()->delete("/restapi/v2/accounts/~/extensions", [
            'param1' => 'value1',
            'param2' => 'value2'
        ]);
        $this->assertEquals($response->body(), json_encode($body));
    }
    public function testDeleteWithBodyWithParams()
    {
        $body = ["keepAssetsInInventory" => true, "records" => [["id" => "123"]]];
        $sdk = $this->getSDK([
            $this->createResponse('DELETE', '/restapi/v2/accounts/~/extensions', ["Success"])
        ]);
        $response = $sdk->platform()->delete("/restapi/v2/accounts/~/extensions", $body, [
            'param1' => 'value1',
            'param2' => 'value2'
        ]);
        $this->assertEquals($response->body(), json_encode(["Success"]));
    }


}