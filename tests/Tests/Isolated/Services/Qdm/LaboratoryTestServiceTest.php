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

    public function testNonNumericResultIsNotCoercedToZero(): void
    {
        $model = $this->service()->makeQdmModel(new QdmRecord($this->record('Positive'), 1, 1));

        self::assertSame('Positive', $model->result);
    }

    public function testWhitespaceOnlyResultUsesTheEmptyResultFallback(): void
    {
        $model = $this->service()->makeQdmModel(new QdmRecord($this->record('    '), 1, 1));

        self::assertSame('Negative', $model->result);
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
    private function record(string $result): array
    {
        return [
            'result' => $result,
            'units' => '%',
            'date' => '2026-06-01',
            'date_end' => null,
            'procedure_code' => '',
        ];
    }
}
