<?php

/**
 * Isolated regression tests for QDM laboratory result conversion.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @copyright Copyright (c) 2026 OpenEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Qdm;

use OpenEMR\Cqm\Qdm\BaseTypes\Code;
use OpenEMR\Cqm\Qdm\BaseTypes\Quantity;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\Qdm\Interfaces\QdmRequestInterface;
use OpenEMR\Services\Qdm\QdmRecord;
use OpenEMR\Services\Qdm\Services\LaboratoryTestService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class LaboratoryTestServiceTest extends TestCase
{
    public function testNumericResultPreservesFractionalValue(): void
    {
        $model = $this->service()->makeQdmModel(new QdmRecord($this->record('9.4'), 1, 1));

        self::assertInstanceOf(Quantity::class, $model->result);
        self::assertSame(9.4, $model->result->value);
    }

    public function testPercentFormattedResultBecomesAQuantity(): void
    {
        $model = $this->service()->makeQdmModel(new QdmRecord($this->record('9.4%'), 1, 1));

        self::assertInstanceOf(Quantity::class, $model->result);
        self::assertSame(9.4, $model->result->value);
        self::assertSame('%', $model->result->unit);
    }

    public function testPercentUnitAliasIsNormalizedToUcum(): void
    {
        $model = $this->service()->makeQdmModel(new QdmRecord($this->record('9.4', 'percent'), 1, 1));

        self::assertInstanceOf(Quantity::class, $model->result);
        self::assertSame(9.4, $model->result->value);
        self::assertSame('%', $model->result->unit);
    }

    public function testNonNumericResultIsNotCoercedToZero(): void
    {
        $model = $this->service()->makeQdmModel(new QdmRecord($this->record('Positive'), 1, 1));

        self::assertSame('Positive', $model->result);
    }

    public function testEmptyResultIsNull(): void
    {
        foreach (['', '    '] as $emptyResult) {
            $model = $this->service()->makeQdmModel(new QdmRecord($this->record($emptyResult), 1, 1));

            self::assertNull($model->result);
        }
    }

    public function testNegativeResultIsRecognizedRegardlessOfCase(): void
    {
        $code = new Code(['code' => '260385009', 'system' => '2.16.840.1.113883.6.96']);
        $service = $this->getMockBuilder(LaboratoryTestService::class)
            ->setConstructorArgs([
                $this->createMock(QdmRequestInterface::class),
                new CodeTypesService()
            ])
            ->onlyMethods(['makeQdmCode'])
            ->getMock();
        $service->expects(self::exactly(3))
            ->method('makeQdmCode')
            ->with('SNOMED-CT:260385009')
            ->willReturn($code);

        foreach (['Negative', 'negative', 'NEGATIVE'] as $negativeResult) {
            $model = $service->makeQdmModel(new QdmRecord($this->record($negativeResult), 1, 1));

            self::assertSame($code, $model->result);
        }
    }

    private function service(): LaboratoryTestService
    {
        return new LaboratoryTestService(
            $this->createMock(QdmRequestInterface::class),
            new CodeTypesService()
        );
    }

    /**
     * @return array<string, string|null>
     */
    private function record(?string $result, string $units = '%'): array
    {
        return [
            'result' => $result,
            'units' => $units,
            'date' => '2026-06-01',
            'date_end' => null,
            'procedure_code' => '',
        ];
    }
}
