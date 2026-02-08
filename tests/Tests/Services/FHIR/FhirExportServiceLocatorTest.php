<?php

/**
 * FhirExportServiceLocatorTest tests that the export services locator actually finds the correct classes.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\Services\FHIR\FhirExportServiceLocator;
use OpenEMR\Services\FHIR\IFhirExportableResourceService;
use OpenEMR\Services\FHIR\Utils\FhirServiceLocator;
use PHPUnit\Framework\TestCase;

class FhirExportServiceLocatorTest extends TestCase
{
    /**
     * @var FhirExportServiceLocator
     */
    private $object;

    public function setUp(): void
    {
    }

    public function tearDown(): void
    {
    }

    public function testFindExportServices(): void
    {
        $noop = function (): void {};
        $routes = [
            'GET /fhir/Patient' => $noop
            ,'GET /fhir/SomeRandomResourceWithNoServiceDefinition' => $noop
        ];
        $this->object = new FhirExportServiceLocator(
            new FhirServiceLocator($routes)
        );
        $resources = $this->object->findExportServices();
        $this->assertArrayHasKey('Patient', $resources, "Service Locator should have found Patient class");
        $this->assertArrayNotHasKey('SomeRandomResourceWithNoServiceDefinition', $resources, "Should skip resources with no export interface");
        $service = $resources['Patient'];
        $this->assertInstanceOf(IFhirExportableResourceService::class, $service, "Service found should implement the interface");
    }
}
