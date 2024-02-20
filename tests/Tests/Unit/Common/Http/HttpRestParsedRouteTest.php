<?php

/**
 * HttpRestParsedRouteTest verifies all of our route parsing logic works correctly for regular routes as well as FHIR
 * operations.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Common\Http;

use OpenEMR\Common\Http\HttpRestParsedRoute;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class HttpRestParsedRouteTest extends TestCase
{
    public function testGetResource()
    {
        $request = "/fhir/Patient/" . Uuid::uuid4();
        $definition = "GET /fhir/Patient/:id";

        $parsedRoute = new HttpRestParsedRoute("GET", $request, $definition);
        $this->assertEquals("Patient", $parsedRoute->getResource());
    }

    public function testGetResourceWithOperation()
    {
        $request = '/fhir/Patient/$export';
        $definition = 'POST /fhir/Patient/$export';

        $parsedRoute = new HttpRestParsedRoute("POST", $request, $definition);
        $this->assertEquals("Patient", $parsedRoute->getResource());
    }

    public function testGetResourceWithRootOperation()
    {
        $request = '/fhir/$export';
        $definition = 'POST /fhir/$export';

        $parsedRoute = new HttpRestParsedRoute("POST", $request, $definition);
        $this->assertNull($parsedRoute->getResource()); // there is no resource here for the root operation
        $this->assertEquals(null, $parsedRoute->getResource());

        // try a different bulk-status
        $statusRequest = '/fhir/$bulkdata-status';
        $statusDefinition = 'GET /fhir/$bulkdata-status';
        $parsedStatusRoute = new HttpRestParsedRoute("POST", $statusRequest, $statusDefinition);
        $this->assertNull($parsedStatusRoute->getResource()); // there is no resource here for the root operation
        $this->assertEquals(null, $parsedStatusRoute->getResource());
    }

    public function testGetResourceWithDocumentBinaryFormat()
    {
        $request = '/fhir/Binary/15';
        $definition = 'GET /fhir/Binary/:id';

        $parsedRoute = new HttpRestParsedRoute("GET", $request, $definition);
        $this->assertEquals("Binary", $parsedRoute->getResource());
    }

    public function testIsOperation()
    {
        $request = '/fhir/$export';
        $definition = 'POST /fhir/$export';

        $parsedRoute = new HttpRestParsedRoute("POST", $request, $definition);
        $this->assertTrue($parsedRoute->isOperation());

        $request = '/fhir/Patient';
        $definition = 'POST /fhir/Patient';

        $parsedRoute = new HttpRestParsedRoute("POST", $request, $definition);
        $this->assertFalse($parsedRoute->isOperation());
    }

    public function testIsOperationWithResource()
    {
        $request = '/fhir/Patient/$export';
        $definition = 'POST /fhir/Patient/$export';

        $parsedRoute = new HttpRestParsedRoute("POST", $request, $definition);
        $this->assertTrue($parsedRoute->isOperation());
    }

    public function testMetadataRoute()
    {
        $request = '/fhir/metadata';
        $definition = 'GET /fhir/metadata';

        $parsedRoute = new HttpRestParsedRoute("GET", $request, $definition);
        $this->assertTrue($parsedRoute->isValid());

        $this->assertEquals("metadata", $parsedRoute->getResource(), "resource should be metadata");
        $this->assertFalse($parsedRoute->isOperation(), "metadata is not an operation");

        $this->assertNull($parsedRoute->getOperation(), "metadata operation should return null");
    }

    public function testGetOperationWithRootOperation()
    {
        $request = '/fhir/$export';
        $definition = 'POST /fhir/$export';

        $parsedRoute = new HttpRestParsedRoute("POST", $request, $definition);
        $this->assertTrue($parsedRoute->isValid(), "route should match definition");
        $this->assertEquals('$export', $parsedRoute->getOperation());

        // try a different bulk-status
        $statusRequest = '/fhir/$bulkdata-status';
        $statusDefinition = 'GET /fhir/$bulkdata-status';
        $parsedStatusRoute = new HttpRestParsedRoute("GET", $statusRequest, $statusDefinition);
        $this->assertTrue($parsedStatusRoute->isValid(), "route should match definition");
        $this->assertNull($parsedStatusRoute->getResource()); // there is no resource here for the root operation
        $this->assertEquals('$bulkdata-status', $parsedStatusRoute->getOperation());
    }

    public function testGetOperationWithPatientExportOperation()
    {
        $request = '/fhir/Patient/$export';
        $definition = 'GET /fhir/Patient/$export';

        $parsedRoute = new HttpRestParsedRoute("GET", $request, $definition);
        $this->assertTrue($parsedRoute->isValid(), "route should match definition");
        $this->assertEquals('$export', $parsedRoute->getOperation());
        $this->assertEquals('Patient', $parsedRoute->getResource());
    }

    public function testGetRouteWithRouteParamSpecialCharacter()
    {
        $request = '/fhir/Patient/unique-id:with:colons';
        $definition = 'GET /fhir/Patient/:uid';

        $parsedRoute = new HttpRestParsedRoute("GET", $request, $definition);
        $this->assertTrue($parsedRoute->isValid(), "route should match definition");

        $params = $parsedRoute->getRouteParams();
        $this->assertNotEmpty($params, "Params should be populated");
        $this->assertEquals('unique-id:with:colons', $params[0]);
    }
}
