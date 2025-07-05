<?php

namespace OpenEMR\Tests\Api;

use Mi2\Framework\App;
use Nyholm\Psr7\Uri;
use OpenEMR\RestControllers\ApiApplication;
use OpenEMR\Common\Http\HttpRestRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ApiApplicationTest extends TestCase
{
    public function testRun() {
        // simple test for running the application for now just to do a simple smoke test
        $httpRestRequest = new HttpRestRequest();
        // note that mod_rewrite will change the request of http://localhost/apis/default/fhir/metadata to http://localhost/dispatch.php/default/fhir/metadata
        // resulting in symfony getting the request path to /default/fhir/metadata
        $restRequest = $httpRestRequest->withUri(new Uri("http://localhost/default/fhir/metadata"))
            ->withMethod('GET');
        $application = new ApiApplication();
        $application->setResponseMode(ApiApplication::RESPONSE_MODE_RETURN);
        $response = $application->run($restRequest);
        $this->assertNotNull($response, "Expected response to be not null");
        $this->assertEquals(200, $response->getStatusCode(), "Expected response status code to be 200 Response: " . $response->getContent());
    }
}
