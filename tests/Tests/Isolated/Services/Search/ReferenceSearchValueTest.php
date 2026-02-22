<?php

/**
 * ReferenceSearchValue Isolated Test
 *
 * Tests non-UUID reference parsing and formatting. UUID-specific behavior
 * requires UuidRegistry and is tested in integration tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Search;

use OpenEMR\Services\Search\ReferenceSearchValue;
use PHPUnit\Framework\TestCase;

class ReferenceSearchValueTest extends TestCase
{
    // =========================================================================
    // Constructor
    // =========================================================================

    public function testConstructorWithIdOnly(): void
    {
        $ref = new ReferenceSearchValue('23');
        $this->assertSame('23', $ref->getId());
        $this->assertNull($ref->getResource());
    }

    public function testConstructorWithIdAndResource(): void
    {
        $ref = new ReferenceSearchValue('23', 'Patient');
        $this->assertSame('23', $ref->getId());
        $this->assertSame('Patient', $ref->getResource());
    }

    // =========================================================================
    // createFromRelativeUri
    // =========================================================================

    public function testCreateFromRelativeUriWithResourceAndId(): void
    {
        $ref = ReferenceSearchValue::createFromRelativeUri('Patient/23');
        $this->assertSame('Patient', $ref->getResource());
        $this->assertSame('23', $ref->getId());
    }

    public function testCreateFromRelativeUriWithIdOnly(): void
    {
        $ref = ReferenceSearchValue::createFromRelativeUri('23');
        $this->assertNull($ref->getResource());
        $this->assertSame('23', $ref->getId());
    }

    public function testCreateFromRelativeUriWithDeepPath(): void
    {
        // For paths with multiple slashes, resource is first segment, id is last
        $ref = ReferenceSearchValue::createFromRelativeUri('Organization/dept/42');
        $this->assertSame('Organization', $ref->getResource());
        $this->assertSame('42', $ref->getId());
    }

    public function testCreateFromRelativeUriWithCommonFhirResources(): void
    {
        $resources = [
            'Patient/100' => ['Patient', '100'],
            'Encounter/200' => ['Encounter', '200'],
            'Practitioner/300' => ['Practitioner', '300'],
            'Organization/400' => ['Organization', '400'],
            'Location/500' => ['Location', '500'],
        ];

        foreach ($resources as $uri => [$expectedResource, $expectedId]) {
            $ref = ReferenceSearchValue::createFromRelativeUri($uri);
            $this->assertSame($expectedResource, $ref->getResource(), "Resource mismatch for URI: {$uri}");
            $this->assertSame($expectedId, $ref->getId(), "ID mismatch for URI: {$uri}");
        }
    }

    // =========================================================================
    // __toString
    // =========================================================================

    public function testToStringWithResourceAndId(): void
    {
        $ref = new ReferenceSearchValue('23', 'Patient');
        $this->assertSame('Patient/23', (string) $ref);
    }

    public function testToStringWithIdOnly(): void
    {
        $ref = new ReferenceSearchValue('23');
        $this->assertSame('23', (string) $ref);
    }

    // =========================================================================
    // getHumanReadableId (non-UUID)
    // =========================================================================

    public function testGetHumanReadableIdReturnsIdDirectlyForNonUuid(): void
    {
        $ref = new ReferenceSearchValue('42', 'Patient');
        $this->assertSame('42', $ref->getHumanReadableId());
    }

    // =========================================================================
    // Round-trip: createFromRelativeUri -> __toString
    // =========================================================================

    public function testRoundTripFromRelativeUri(): void
    {
        $uri = 'Patient/23';
        $ref = ReferenceSearchValue::createFromRelativeUri($uri);
        $this->assertSame($uri, (string) $ref);
    }
}
