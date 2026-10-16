<?php

/**
 * Physical exam results keep their decimals.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Qdm;

use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\Qdm\QdmRequestSome;
use OpenEMR\Services\Qdm\Services\PhysicalExamService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PhysicalExamServiceTest extends TestCase
{
    /**
     * @return array<string, array{mixed, ?float}>
     */
    public static function values(): array
    {
        return [
            'a BMI with a decimal' => ['18.7', 18.7],
            'a whole number' => ['120', 120.0],
            'a number stored as a number' => [98.6, 98.6],
            'nothing recorded' => ['', null],
            'not a number' => ['high', null],
            'no value at all' => [null, null],
        ];
    }

    #[DataProvider('values')]
    public function testTheResultIsTheRecordedNumber(mixed $stored, ?float $expected): void
    {
        $service = new PhysicalExamService(new QdmRequestSome([1]), $this->createMock(CodeTypesService::class));
        $result = $service->makeResult(['ob_value' => $stored, 'ob_unit' => 'kg/m2']);
        $this->assertSame($expected, $result->value);
        $this->assertSame('kg/m2', $result->unit);
    }
}
