<?php

/**
 * Isolated tests for UtilsService::createFhirMeta(), which builds meta with a typed
 * versionId (FHIRId) and lastUpdated (FHIRInstant) that serialize to the same strings.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@open-emr.org>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@open-emr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR;

use OpenEMR\Services\FHIR\UtilsService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class UtilsServiceCreateFhirMetaIsolatedTest extends TestCase
{
    public function testMetaElementsAreTyped(): void
    {
        $meta = UtilsService::createFhirMeta('3', '2024-03-05T10:00:00+00:00');

        // getValue() exists only on the typed elements; a raw string here would throw.
        $this->assertSame('3', $meta->getVersionId()->getValue());
        $this->assertSame('2024-03-05T10:00:00+00:00', $meta->getLastUpdated()->getValue());
    }

    public function testMetaSerializesToTheSameStrings(): void
    {
        $meta = UtilsService::createFhirMeta('1', '2024-03-05T10:00:00+00:00');

        $json = json_decode((string) json_encode($meta), true);
        $this->assertSame(
            ['versionId' => '1', 'lastUpdated' => '2024-03-05T10:00:00+00:00'],
            $json
        );
    }
}
