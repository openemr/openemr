<?php

namespace OpenEMR\Tests\RestControllers\FHIR\Trait;

use Symfony\Component\HttpFoundation\Response;

trait JsonResponseHandlerTrait
{
    protected function getJsonContents(Response $response): array
    {
        $contents = $response->getContent();
        $this->assertNotEmpty($contents, "Response body should not be empty");
        $decodedContents = json_decode($contents, true);
        $this->assertIsArray($decodedContents, "Response body should be a valid JSON array");
        return $decodedContents;
    }
}
