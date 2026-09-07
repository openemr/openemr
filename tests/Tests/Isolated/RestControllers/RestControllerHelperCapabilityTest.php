<?php

/**
 * Isolated RestControllerHelper CapabilityStatement Test
 *
 * The CapabilityStatement is derived from the FHIR route map, so any registered
 * POST/PUT becomes an advertised create/update interaction. Writes registered
 * only to return a 405 OperationOutcome must therefore be excluded, or
 * /fhir/metadata advertises capabilities the server cannot honour.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestControllers;

use OpenEMR\FHIR\R4\FHIRResource\FHIRCapabilityStatement\FHIRCapabilityStatementResource;
use OpenEMR\RestControllers\RestControllerHelper;
use PHPUnit\Framework\TestCase;

class RestControllerHelperCapabilityTest extends TestCase
{
    private RestControllerHelper $helper;

    protected function setUp(): void
    {
        $this->helper = new RestControllerHelper();
    }

    public function testFhirWriteNotImplementedRegistersTheInteraction(): void
    {
        RestControllerHelper::fhirWriteNotImplemented('POST', 'Provenance', 'no provenance table');

        $this->assertTrue(RestControllerHelper::isUnimplementedFhirWrite('Provenance', 'POST'));
        $this->assertTrue(
            RestControllerHelper::isUnimplementedFhirWrite('Provenance', 'post'),
            'method matching should be case-insensitive'
        );
        $this->assertFalse(RestControllerHelper::isUnimplementedFhirWrite('Provenance', 'GET'));
        $this->assertArrayHasKey('Provenance', RestControllerHelper::getUnimplementedFhirWrites());
    }

    public function testUnimplementedWriteIsNotAdvertisedAsCreate(): void
    {
        RestControllerHelper::fhirWriteNotImplemented('POST', 'Group', 'computed aggregation');
        RestControllerHelper::fhirWriteNotImplemented('PUT', 'Group', 'computed aggregation');

        $capResource = new FHIRCapabilityStatementResource();
        $this->helper->addRequestMethods(explode('/', 'POST /fhir/Group'), $capResource);
        $this->helper->addRequestMethods(explode('/', 'PUT /fhir/Group/:uuid'), $capResource);

        $this->assertSame([], $this->interactionCodes($capResource));
    }

    public function testReadInteractionsStillAdvertisedForTheSameResource(): void
    {
        RestControllerHelper::fhirWriteNotImplemented('POST', 'Location', 'virtual projection');

        $capResource = new FHIRCapabilityStatementResource();
        $this->helper->addRequestMethods(explode('/', 'GET /fhir/Location'), $capResource);
        $this->helper->addRequestMethods(explode('/', 'GET /fhir/Location/:uuid'), $capResource);
        $this->helper->addRequestMethods(explode('/', 'POST /fhir/Location'), $capResource);

        $codes = $this->interactionCodes($capResource);
        $this->assertContains('search-type', $codes);
        $this->assertContains('read', $codes);
        $this->assertNotContains('create', $codes);
    }

    public function testImplementedWritesAreStillAdvertised(): void
    {
        $capResource = new FHIRCapabilityStatementResource();
        $this->helper->addRequestMethods(explode('/', 'POST /fhir/Condition'), $capResource);
        $this->helper->addRequestMethods(explode('/', 'PUT /fhir/Condition/:uuid'), $capResource);

        $codes = $this->interactionCodes($capResource);
        $this->assertContains('create', $codes);
        $this->assertContains('update', $codes);
    }

    public function testHandlerReturnsMethodNotAllowedWithOperationOutcome(): void
    {
        $handler = RestControllerHelper::fhirWriteNotImplemented(
            'POST',
            'MedicationDispense',
            'pharmacy persistence varies by deployment'
        );

        $response = $handler(null);
        $this->assertSame(405, $response->getStatusCode());
        $body = (string) $response->getContent();
        $this->assertStringContainsString('not-supported', $body);
        $this->assertStringContainsString('pharmacy persistence varies by deployment', $body);
    }

    public function testInstanceRouteHandlerAcceptsTheUuidArgument(): void
    {
        $handler = RestControllerHelper::fhirWriteNotImplemented('PUT', 'Procedure', 'federated resource');

        // Instance routes are invoked as ($uuid, $request); the handler must
        // tolerate the extra argument.
        $response = $handler('9c1f2a44-0000-4000-8000-000000000000', null);
        $this->assertSame(405, $response->getStatusCode());
    }

    /**
     * @return string[]
     */
    private function interactionCodes(FHIRCapabilityStatementResource $capResource): array
    {
        $codes = [];
        foreach ($capResource->getInteraction() as $interaction) {
            $codes[] = (string) $interaction->getCode()->getValue();
        }
        return $codes;
    }
}
