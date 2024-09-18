<?php

use RingCentral\SDK\SDK;
use RingCentral\SDK\Test\TestCase;

class SDKTest extends TestCase
{

    private function connectToLiveServer($server)
    {

        $sdk = new SDK('foo', 'bar', $server);

        $res = $sdk->platform()
                   ->get('', [], [], ['skipAuthCheck' => true])
                   ->json();

        $this->assertEquals('v1.0', $res->uriString);

    }

    public function testProduction()
    {
        $this->connectToLiveServer(SDK::SERVER_PRODUCTION);
    }
    public function testMultipartBuilderGetter()
    {
        $this->assertNotNull($this->getSDK([], false)->createMultipartBuilder());
    }

}