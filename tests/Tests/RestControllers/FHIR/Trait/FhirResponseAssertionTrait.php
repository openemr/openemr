<?php

namespace OpenEMR\Tests\RestControllers\FHIR\Trait;

use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle;
use Symfony\Component\HttpFoundation\Response;

trait FhirResponseAssertionTrait
{
    public function assertFhirBundleResponse(Response $response, int $expectedStatusCode, string $expectedResourceType): void
    {
        $this->assertEquals($expectedStatusCode, $response->getStatusCode(), "Unexpected HTTP status code");

        $contents = $this->getJsonContents($response);
        $this->assertArrayHasKey('resourceType', $contents, "Response should contain 'resourceType'");
        $fhirBundle = new FHIRBundle();
        $this->assertEquals($fhirBundle->get_fhirElementName(), $contents['resourceType'], "Unexpected resource type in response");

        if (isset($contents['entry'])) {
            foreach ($contents['entry'] as $entry) {
                $this->assertArrayHasKey('resource', $entry, "Each entry should contain a 'resource'");
                $this->assertArrayHasKey('id', $entry['resource'], "Each resource should have an 'id'");
                $this->assertEquals($expectedResourceType, $entry['resource']['resourceType'], "Resource type in entry does not match expected type");
            }
        }
    }
}
