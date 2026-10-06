<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR\Traits;

use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FhirServiceBaseEmptyTraitTest extends TestCase
{
    /**
     * @return array<string, array{callable(object): mixed}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function writeMethodProvider(): array
    {
        return [
            'parseFhirResource' => [fn(object $service) => $service->parseFhirResource([])],
            'insertOpenEMRRecord' => [fn(object $service) => $service->insertOpenEMRRecord([])],
            'updateOpenEMRRecord' => [fn(object $service) => $service->updateOpenEMRRecord('id', [])],
        ];
    }

    /**
     * @param callable(object): mixed $call
     */
    #[DataProvider('writeMethodProvider')]
    public function testWriteMethodsThrow(callable $call): void
    {
        $this->expectException(\BadMethodCallException::class);
        $call($this->makeService());
    }

    public function testCreateProvenanceResourceReturnsNull(): void
    {
        self::assertNull(
            $this->makeService()->createProvenanceResource(),
            'FhirServiceBase::getAll() treats null as "no provenance available" and keeps going',
        );
    }

    private function makeService(): object
    {
        return new class {
            use FhirServiceBaseEmptyTrait;
        };
    }
}
