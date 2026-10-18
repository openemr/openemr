<?php

/**
 * Physical exam results: a number keeps its decimals; anything else is the exam's
 * coded result, or no result, never a quantity without a value.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Qdm;

use OpenEMR\Cqm\Qdm\BaseTypes\Code;
use OpenEMR\Cqm\Qdm\BaseTypes\Quantity;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\Qdm\QdmRequestSome;
use OpenEMR\Services\Qdm\Services\PhysicalExamService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PhysicalExamServiceTest extends TestCase
{
    /**
     * @return array<string, array{mixed, float}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function numbers(): array
    {
        return [
            'a BMI with a decimal' => ['18.7', 18.7],
            'a whole number' => ['120', 120.0],
            'a number stored as a number' => [98.6, 98.6],
        ];
    }

    #[DataProvider('numbers')]
    public function testANumberIsAQuantityWithItsDecimals(mixed $stored, float $expected): void
    {
        $result = $this->service()->makeResult(['ob_value' => $stored, 'ob_unit' => 'kg/m2', 'ob_code' => '']);
        $this->assertInstanceOf(Quantity::class, $result);
        $this->assertSame($expected, $result->value);
        $this->assertSame('kg/m2', $result->unit);
    }

    /**
     * @return array<string, array{mixed}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function notNumbers(): array
    {
        return [
            'nothing recorded' => [''],
            'not a number' => ['high'],
            'no value at all' => [null],
        ];
    }

    #[DataProvider('notNumbers')]
    public function testAValueThatIsNotANumberIsNoResult(mixed $stored): void
    {
        // A Quantity with a null value makes the CQL engine throw, failing the measure for every patient.
        $this->assertNull($this->service()->makeResult(['ob_value' => $stored, 'ob_unit' => 'mm[Hg]', 'ob_code' => '']));
    }

    public function testAnExamWithACodedResultKeepsTheCode(): void
    {
        $code = new Code(['code' => '263654008', 'system' => '2.16.840.1.113883.6.96']);
        $service = $this->getMockBuilder(PhysicalExamService::class)
            ->setConstructorArgs([new QdmRequestSome([1]), $this->createMock(CodeTypesService::class)])
            ->onlyMethods(['makeQdmCode'])
            ->getMock();
        $service->expects($this->once())->method('makeQdmCode')->with('SNOMED-CT:263654008')->willReturn($code);
        $this->assertSame($code, $service->makeResult(['ob_value' => 'abnormal', 'ob_unit' => '', 'ob_code' => 'SNOMED-CT:263654008']));
    }

    private function service(): PhysicalExamService
    {
        return new PhysicalExamService(new QdmRequestSome([1]), $this->createMock(CodeTypesService::class));
    }
}
