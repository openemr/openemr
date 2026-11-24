<?php

/**
 * Isolated tests for BillingCodeType
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Forms\Types;

use OpenEMR\Common\Forms\Types\BillingCodeEditOption;
use OpenEMR\Common\Forms\Types\BillingCodeType;
use OpenEMR\Services\CodeTypesService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BillingCodeTypeTest extends TestCase
{
    #[DataProvider('editOptionEProvider')]
    public function testEditOptionEClassFollowsTheEOption(?string $editOptions, bool $expectMultiple): void
    {
        $html = (new BillingCodeType())->buildFormView(self::frow($editOptions), '');

        self::assertSame($expectMultiple, str_contains($html, 'EditOptionE'));
    }

    /**
     * @return array<string, array{?string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function editOptionEProvider(): array
    {
        return [
            'JSON E'                  => ['["E"]', true],
            'JSON E with option 2'    => ['["2","E"]', true],
            'JSON EP only'            => ['["EP"]', false],
            'JSON EP with option 2'   => ['["2","EP"]', false],
            'legacy E'                => ['E', true],
            'legacy 2E'               => ['2E', true],
            'legacy 2 without E'      => ['2', false],
            // A JSON number, so isOption() reads no modifiers, as for every other option.
            'legacy 2E0'              => ['2E0', false],
            'no options'              => [null, false],
        ];
    }

    /**
     * @param list<BillingCodeEditOption> $expected
     */
    #[DataProvider('enabledOptionsProvider')]
    public function testEnabledInReadsEveryCase(?string $editOptions, array $expected): void
    {
        self::assertSame($expected, BillingCodeEditOption::enabledIn($editOptions));
    }

    /**
     * @return array<string, array{?string, list<BillingCodeEditOption>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function enabledOptionsProvider(): array
    {
        return [
            'all four, JSON'   => [
                '["0","2","E","T","EP"]',
                [
                    BillingCodeEditOption::MultipleCodes,
                    BillingCodeEditOption::ShowDescriptions,
                    BillingCodeEditOption::DescriptionIsDefault,
                    BillingCodeEditOption::ReadOnly,
                ],
            ],
            'legacy string'    => ['T0', [BillingCodeEditOption::DescriptionIsDefault, BillingCodeEditOption::ReadOnly]],
            'other types only' => ['["EP","DAP"]', []],
            'no options'       => [null, []],
        ];
    }

    public function testOptionTwoAddsTheDescriptionField(): void
    {
        $html = (new BillingCodeType())->buildFormView(self::frow('["2"]'), '');

        self::assertStringContainsString("name='form_codes__desc'", $html);
    }

    public function testReadOnlyDisablesThePicker(): void
    {
        $html = (new BillingCodeType())->buildFormView(self::frow('["0"]'), '');

        self::assertStringContainsString('disabled', $html);
        self::assertStringNotContainsString('sel_related', $html);
    }

    #[DataProvider('codeTypeProvider')]
    public function testPickerUsesTheDescriptionWhenItIsAnInstalledCodeType(bool $installed, string $expectedType): void
    {
        $codeTypes = $this->createMock(CodeTypesService::class);
        $codeTypes->method('isInstalledCodeType')->with('CPT4')->willReturn($installed);
        $frow = self::frow(null) + ['description' => 'CPT4'];

        $html = (new BillingCodeType($codeTypes))->buildFormView($frow, '');

        self::assertStringContainsString('sel_related(this,' . attr_js($expectedType) . ')', $html);
    }

    /**
     * @return array<string, array{bool, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function codeTypeProvider(): array
    {
        return [
            'installed'     => [true, 'CPT4'],
            'not installed' => [false, ''],
        ];
    }

    #[DataProvider('blankValueProvider')]
    public function testBlankValuesSkipTheCodeLookups(?string $value): void
    {
        $type = new BillingCodeType();

        self::assertSame('', $type->buildDisplayView([], $value));
        self::assertSame('', $type->getAccumActionConditions([], '', 'field=' . ($value ?? '')));
    }

    /**
     * @return array<string, array{?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function blankValueProvider(): array
    {
        return [
            'null'         => [null],
            'empty string' => [''],
            'zero string'  => ['0'],
        ];
    }

    /**
     * @return array{field_id: string, max_length: int, fld_length: int, edit_options: ?string}
     */
    private static function frow(?string $editOptions): array
    {
        return [
            'field_id' => 'codes',
            'max_length' => 0,
            'fld_length' => 20,
            'edit_options' => $editOptions,
        ];
    }
}
