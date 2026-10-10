<?php

declare(strict_types=1);

/*
 * UtilsServiceDataAbsentReasonTest.php
 *
 * The data-absent-reason helpers in UtilsService. createDataMissingExtension() returns an
 * element holding the extension, for use as a missing element's value; adding it to another
 * element's extensions produced an extension without a url (Observation.performer failed
 * US Core validation in the Inferno certification run).
 *
 * @package   openemr
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRObservation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\UtilsService;
use PHPUnit\Framework\TestCase;

class UtilsServiceDataAbsentReasonTest extends TestCase
{
    private const DATA_ABSENT_REASON = [
        'valueCode' => 'unknown',
        'url' => FhirCodeSystemConstants::DATA_ABSENT_REASON_EXTENSION,
    ];

    public function testDataAbsentReasonExtensionHasUrlAndCode(): void
    {
        $this->assertSame(
            self::DATA_ABSENT_REASON,
            $this->toArray(UtilsService::createDataAbsentReasonExtension())
        );
    }

    public function testDataAbsentReasonAddedToReferenceHasUrl(): void
    {
        $performer = new FHIRReference();
        $performer->addExtension(UtilsService::createDataAbsentReasonExtension());
        $observation = new FHIRObservation();
        $observation->addPerformer($performer);

        $this->assertSame(
            [['extension' => [self::DATA_ABSENT_REASON]]],
            $this->toArray($observation)['performer']
        );
    }

    public function testDataMissingExtensionIsAnElementHoldingDataAbsentReason(): void
    {
        $this->assertSame(
            ['extension' => [self::DATA_ABSENT_REASON]],
            $this->toArray(UtilsService::createDataMissingExtension())
        );
    }

    /**
     * @return array<mixed>
     */
    private function toArray(\JsonSerializable $element): array
    {
        $decoded = json_decode(json_encode($element, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($decoded);
        return $decoded;
    }
}
