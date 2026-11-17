<?php

/**
 * The portal record validator keeps the Phreeze model's rules and messages.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Validators\Portal;

use OpenEMR\Validators\Portal\FieldRule;
use OpenEMR\Validators\Portal\FieldRuleValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FieldRuleValidatorTest extends TestCase
{
    /**
     * @return array<string, array{mixed, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function numberProvider(): array
    {
        return [
            'int' => [12, true],
            'numeric string' => ['12', true],
            'zero' => [0, true],
            'zero string' => ['0', true],
            'decimal string' => ['1.5', true],
            'word' => ['abc', false],
            'empty string' => ['', false],
            'null' => [null, false],
            'false' => [false, false],
        ];
    }

    /**
     * A number field accepts numbers and numeric strings, and names the field otherwise.
     */
    #[DataProvider('numberProvider')]
    public function testNumberFieldsMustBeNumeric(mixed $value, bool $valid): void
    {
        $errors = FieldRuleValidator::validate(['facility' => FieldRule::number(10)], ['facility' => $value]);

        $this->assertSame($valid ? [] : ['facility' => 'Facility is not a valid number'], $errors);
    }

    /**
     * @return array<string, array{mixed}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function emptyProvider(): array
    {
        return [
            'missing' => [null],
            'empty string' => [''],
            'false' => [false],
        ];
    }

    #[DataProvider('emptyProvider')]
    public function testOptionalNumberFieldsMayBeEmpty(mixed $value): void
    {
        $this->assertSame([], FieldRuleValidator::validate(['id' => FieldRule::number(10, optional: true)], ['id' => $value]));
        $this->assertSame(
            ['id' => 'Id is not a valid number'],
            FieldRuleValidator::validate(['id' => FieldRule::number(10, optional: true)], ['id' => 'abc']),
        );
    }

    public function testLengthAllowsOneCharacterOverTheSizeAsPhreezeDid(): void
    {
        $rules = ['authorizingSignator' => FieldRule::text(50)];

        $this->assertSame([], FieldRuleValidator::validate($rules, ['authorizingSignator' => str_repeat('a', 51)]));
        $this->assertSame(
            ['authorizingSignator' => 'AuthorizingSignator exceeds the maximum length of 50'],
            FieldRuleValidator::validate($rules, ['authorizingSignator' => str_repeat('a', 52)]),
        );
    }

    public function testLengthCountsCharactersNotBytes(): void
    {
        $this->assertSame([], FieldRuleValidator::validate(['name' => FieldRule::text(3)], ['name' => 'éééé']));
    }

    public function testUnboundedTextAndUnlistedFieldsAreNotChecked(): void
    {
        $this->assertSame(
            [],
            FieldRuleValidator::validate(['note' => FieldRule::text()], ['note' => str_repeat('a', 100000), 'other' => 'abc']),
        );
    }

    public function testANonScalarValueIsRefused(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        FieldRuleValidator::validate(['facility' => FieldRule::number(10)], ['facility' => ['1']]);
    }
}
