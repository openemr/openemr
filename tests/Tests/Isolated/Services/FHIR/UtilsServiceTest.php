<?php

/**
 * UtilsService Isolated Tests
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Joshua Baiad <jbaiad@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Joshua Baiad <jbaiad@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\Services\FHIR\UtilsService;
use PHPUnit\Framework\TestCase;

class UtilsServiceTest extends TestCase
{
    public function testGetUuidFromReferenceReturnsNullForNullInput(): void
    {
        $result = UtilsService::getUuidFromReference(null);
        $this->assertNull($result);
    }

    public function testGetUuidFromReferenceExtractsUuid(): void
    {
        $reference = new FHIRReference();
        $reference->setReference(new FHIRString('Patient/test-uuid-123'));
        $result = UtilsService::getUuidFromReference($reference);
        $this->assertSame('test-uuid-123', $result);
    }

    public function testGetUuidFromReferenceReturnsNullForEmptyReference(): void
    {
        $reference = new FHIRReference();
        $result = UtilsService::getUuidFromReference($reference);
        $this->assertNull($result);
    }
}
