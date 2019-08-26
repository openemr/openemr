<?php

use RingCentral\SDK\SDK;
use RingCentral\SDK\Test\TestCase;

class SDKTest extends TestCase
{

    private function connectToLiveServer($server)
    {

        $sdk = new SDK('foo', 'bar', $server);

        $res = $sdk->platform()
                   ->get('', array(), array(), array('skipAuthCheck' => true))
                   ->json();

        $this->assertEquals('v1.0', $res->uriString);

    }

    public function testProduction()
    {
        $this->connectToLiveServer(SDK::SERVER_PRODUCTION);
    }

    public function testSandbox()
    {
        $this->connectToLiveServer(SDK::SERVER_SANDBOX);
    }

    public function testMultipartBuilderGetter()
    {
        $this->assertNotNull($this->getSDK(array(), false)->createMultipartBuilder());
    }

}