<?php

/**
 * QDM medication dosage is the per-dose count with the form unit, as a decimal
 * (#14703). Pairing dosage with drug_unit sent "1 tablet" as "1 mg"; (int)0.5
 * sent 0.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Subramani Nagaraj <yuvamani.mani@gmail.com>
 * @copyright Copyright (c) 2026 Subramani Nagaraj <yuvamani.mani@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Qdm;

use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\Qdm\QdmRecord;
use OpenEMR\Services\Qdm\QdmRequestSome;
use OpenEMR\Services\Qdm\Services\MedicationActiveService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class MedicationDosageIsolatedTest extends TestCase
{
    /**
     * @return array<string, array{mixed, ?string, ?string, float, ?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function dosages(): array
    {
        return [
            'half a tablet' => ['0.5', 'tablet', 'mg', 0.5, 'tablet'],
            'one tablet' => [1, 'tablet', 'mg', 1.0, 'tablet'],
            'two and a half' => [2.5, 'capsule', 'mg', 2.5, 'capsule'],
        ];
    }

    /**
     * @param mixed $dosage
     */
    #[DataProvider('dosages')]
    public function testDosageUsesTheFormUnitAsADecimal(
        mixed $dosage,
        ?string $form,
        ?string $strengthUnit,
        float $expectedValue,
        ?string $expectedUnit
    ): void {
        $json = $this->dosageJson($dosage, $form, $strengthUnit);
        $this->assertEquals($expectedValue, $json['value'] ?? null);
        $this->assertSame($expectedUnit, $json['unit'] ?? null);
        $this->assertNotSame($strengthUnit, $json['unit'] ?? null, 'the strength unit does not describe the per-dose count');
    }

    public function testATruncatingCastWouldHaveSentZero(): void
    {
        $json = $this->dosageJson('0.5', 'tablet', 'mg');
        $this->assertEquals(0.5, $json['value'] ?? null);
        $this->assertNotEquals(0, $json['value'] ?? null);
        $this->assertSame('tablet', $json['unit'] ?? null);
    }

    public function testASigIsNotAQuantity(): void
    {
        $this->assertNull($this->dosageJson('Take 1 tablet daily', 'tablet', 'mg'));
    }

    public function testAMissingFormOmitsTheUnitRatherThanUsingTheStrengthUnit(): void
    {
        $json = $this->dosageJson('1', null, 'mg');
        $this->assertIsArray($json);
        $this->assertEquals(1.0, $json['value'] ?? null);
        $this->assertNull($json['unit']);
    }

    /**
     * @return array<mixed>|null
     */
    private function dosageJson(mixed $dosage, ?string $form, ?string $strengthUnit): ?array
    {
        $service = $this->service();
        $model = $service->makeQdmModel(new QdmRecord([
            'pid' => 1,
            'start_date' => '2024-01-01',
            'end_date' => null,
            'date_added' => '2024-01-01 00:00:00',
            'dosage' => $dosage,
            'drug_form' => $form,
            'drug_unit' => $strengthUnit,
            'drug_interval' => '',
            'rxnorm_drugcode' => '197361',
        ], 1, 1));
        $json = json_decode(json_encode($model, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($json);
        $dosageJson = $json['dosage'] ?? null;
        if ($dosageJson === null) {
            return null;
        }
        $this->assertIsArray($dosageJson);
        return $dosageJson;
    }

    private function service(): MedicationActiveService
    {
        $codeTypes = $this->createMock(CodeTypesService::class);
        $codeTypes->method('getSystemForCodeType')->willReturn('2.16.840.1.113883.6.88');
        return new MedicationActiveService(new QdmRequestSome([1]), $codeTypes);
    }
}
