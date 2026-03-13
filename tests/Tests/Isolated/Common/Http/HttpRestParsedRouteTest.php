<?php

/**
 * Isolated HttpRestParsedRoute Test
 *
 * Tests REST API route parsing logic.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Http;

use OpenEMR\Common\Http\HttpRestParsedRoute;
use PHPUnit\Framework\TestCase;

class HttpRestParsedRouteTest extends TestCase
{
    public function testMatchingRouteIsValid(): void
    {
        $route = new HttpRestParsedRoute('GET', '/api/patient', 'GET /api/patient');
        $this->assertTrue($route->isValid());
    }

    public function testNonMatchingRouteIsInvalid(): void
    {
        $route = new HttpRestParsedRoute('GET', '/api/patient', 'POST /api/patient');
        $this->assertFalse($route->isValid());
    }

    public function testMethodMismatchIsInvalid(): void
    {
        $route = new HttpRestParsedRoute('POST', '/api/patient', 'GET /api/patient');
        $this->assertFalse($route->isValid());
    }

    public function testPathMismatchIsInvalid(): void
    {
        $route = new HttpRestParsedRoute('GET', '/api/encounter', 'GET /api/patient');
        $this->assertFalse($route->isValid());
    }

    public function testRouteWithParameterExtractsParam(): void
    {
        $route = new HttpRestParsedRoute('GET', '/api/patient/123', 'GET /api/patient/:id');
        $this->assertTrue($route->isValid());
        $params = $route->getRouteParams();
        $this->assertContains('123', $params);
    }

    public function testRouteWithMultipleParametersExtractsAll(): void
    {
        $route = new HttpRestParsedRoute(
            'GET',
            '/api/patient/123/encounter/456',
            'GET /api/patient/:pid/encounter/:eid'
        );
        $this->assertTrue($route->isValid());
        $params = $route->getRouteParams();
        $this->assertContains('123', $params);
        $this->assertContains('456', $params);
    }

    public function testGetResourceForApiRoute(): void
    {
        $route = new HttpRestParsedRoute('GET', '/api/patient', 'GET /api/patient');
        $this->assertTrue($route->isValid());
        $this->assertSame('patient', $route->getResource());
    }

    public function testGetResourceForFhirRoute(): void
    {
        $route = new HttpRestParsedRoute('GET', '/fhir/Patient', 'GET /fhir/Patient');
        $this->assertTrue($route->isValid());
        $this->assertSame('Patient', $route->getResource());
    }

    public function testGetResourceForFhirRouteWithId(): void
    {
        $route = new HttpRestParsedRoute('GET', '/fhir/Patient/123', 'GET /fhir/Patient/:id');
        $this->assertTrue($route->isValid());
        $this->assertSame('Patient', $route->getResource());
    }

    public function testGetInstanceIdentifier(): void
    {
        $route = new HttpRestParsedRoute('GET', '/api/patient/abc-123', 'GET /api/patient/:id');
        $this->assertTrue($route->isValid());
        $this->assertSame('abc-123', $route->getInstanceIdentifier());
    }

    public function testGetInstanceIdentifierForNestedRoute(): void
    {
        $route = new HttpRestParsedRoute(
            'GET',
            '/api/patient/123/encounter/456',
            'GET /api/patient/:pid/encounter/:eid'
        );
        $this->assertTrue($route->isValid());
        $this->assertSame('456', $route->getInstanceIdentifier());
    }

    public function testOperationRoute(): void
    {
        $route = new HttpRestParsedRoute(
            'POST',
            '/fhir/Patient/$export',
            'POST /fhir/Patient/$export'
        );
        $this->assertTrue($route->isValid());
        $this->assertTrue($route->isOperation());
        $this->assertSame('$export', $route->getOperation());
    }

    public function testOperationRouteWithId(): void
    {
        $route = new HttpRestParsedRoute(
            'GET',
            '/fhir/Patient/123/$everything',
            'GET /fhir/Patient/:id/$everything'
        );
        $this->assertTrue($route->isValid());
        $this->assertTrue($route->isOperation());
        $this->assertSame('$everything', $route->getOperation());
        $this->assertSame('123', $route->getInstanceIdentifier());
    }

    public function testNonOperationRouteIsNotOperation(): void
    {
        $route = new HttpRestParsedRoute('GET', '/api/patient', 'GET /api/patient');
        $this->assertTrue($route->isValid());
        $this->assertFalse($route->isOperation());
        $this->assertNull($route->getOperation());
    }

    public function testGetRouteDefinition(): void
    {
        $definition = 'GET /api/patient/:id';
        $route = new HttpRestParsedRoute('GET', '/api/patient/123', $definition);
        $this->assertSame($definition, $route->getRouteDefinition());
    }

    public function testGetRequestRoute(): void
    {
        $requestRoute = '/api/patient/123';
        $route = new HttpRestParsedRoute('GET', $requestRoute, 'GET /api/patient/:id');
        $this->assertSame($requestRoute, $route->getRequestRoute());
    }

    public function testPortalRouteDoesNotExtractPortalAsResource(): void
    {
        $route = new HttpRestParsedRoute('GET', '/portal/patient', 'GET /portal/patient');
        $this->assertTrue($route->isValid());
        $this->assertSame('patient', $route->getResource());
    }

    public function testApiRootRouteDoesNotExtractApiAsResource(): void
    {
        // When route is just /api, resource should be null (api is excluded)
        $route = new HttpRestParsedRoute('GET', '/api', 'GET /api');
        $this->assertTrue($route->isValid());
        // 'api' should not be the resource
        $this->assertNotSame('api', $route->getResource());
    }

    public function testParameterWithHyphenAndUnderscore(): void
    {
        $route = new HttpRestParsedRoute(
            'GET',
            '/api/patient/abc-123_def',
            'GET /api/patient/:id'
        );
        $this->assertTrue($route->isValid());
        $params = $route->getRouteParams();
        $this->assertContains('abc-123_def', $params);
    }

    public function testParameterWithDollarSign(): void
    {
        // Route parameters can include $ (used in FHIR operations)
        $route = new HttpRestParsedRoute(
            'GET',
            '/api/resource/$operation',
            'GET /api/resource/:id'
        );
        $this->assertTrue($route->isValid());
        $params = $route->getRouteParams();
        $this->assertContains('$operation', $params);
    }

    public function testNoInstanceIdentifierForCollectionRoute(): void
    {
        $route = new HttpRestParsedRoute('GET', '/api/patient', 'GET /api/patient');
        $this->assertTrue($route->isValid());
        $this->assertNull($route->getInstanceIdentifier());
    }
}
