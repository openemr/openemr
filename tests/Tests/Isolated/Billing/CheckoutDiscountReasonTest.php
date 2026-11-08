<?php

/**
 * Isolated tests for CheckoutDiscountReason.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\CheckoutDiscountReason;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class CheckoutDiscountReasonTest extends TestCase
{
    /** What the page passes as the default: xl('Discount'), here untranslated. */
    private const DEFAULT = 'Discount';

    /**
     * @return list<array{id: string, title: string}>
     */
    private static function offered(): array
    {
        return CheckoutDiscountReason::choices([
            ['option_id' => 'Discount', 'title' => 'Discount'],
            ['option_id' => 'Hardship w/o', 'title' => 'Hardship w/o'],
            ['option_id' => 'Sliding fee', 'title' => 'Sliding fee discount'],
            ['option_id' => '7', 'title' => 'Numeric id'],
        ]);
    }

    public function testChoicesLeaveOutTheDefault(): void
    {
        $this->assertSame([
            ['id' => 'Hardship w/o', 'title' => 'Hardship w/o'],
            ['id' => 'Sliding fee', 'title' => 'Sliding fee discount'],
            ['id' => '7', 'title' => 'Numeric id'],
        ], self::offered());
    }

    public function testChoicesKeepAnEntryTitledDiscountUnderAnotherId(): void
    {
        $this->assertSame(
            [['id' => 'Disc2', 'title' => 'Discount']],
            CheckoutDiscountReason::choices([['option_id' => 'Disc2', 'title' => 'Discount']])
        );
    }

    public function testChoicesSkipMalformedRows(): void
    {
        $this->assertSame([['id' => 'Ok', 'title' => 'Ok']], CheckoutDiscountReason::choices([
            ['option_id' => '', 'title' => 'Empty id'],
            ['option_id' => null, 'title' => 'Null id'],
            ['title' => 'No id'],
            ['option_id' => 'No title'],
            ['option_id' => 'Ok', 'title' => 'Ok'],
        ]));
    }

    public function testChoicesFromNoRows(): void
    {
        $this->assertSame([], CheckoutDiscountReason::choices([]));
    }

    #[DataProvider('memoProvider')]
    public function testMemo(mixed $posted, string $expected): void
    {
        $this->assertSame($expected, CheckoutDiscountReason::memo($posted, self::offered(), self::DEFAULT));
    }

    /**
     * @return array<string, array{mixed, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function memoProvider(): array
    {
        return [
            'offered reason' => ['Sliding fee', 'Sliding fee'],
            'another offered reason' => ['Hardship w/o', 'Hardship w/o'],
            'numeric option id kept as given' => ['7', '7'],
            // Anything else keeps the memo checkout always wrote.
            'nothing chosen' => ['', self::DEFAULT],
            'field absent' => [null, self::DEFAULT],
            'not offered' => ['Bad debt', self::DEFAULT],
            'the default itself' => ['Discount', self::DEFAULT],
            'case differs' => ['sliding fee', self::DEFAULT],
            'trailing space' => ['Sliding fee ', self::DEFAULT],
            'integer' => [7, self::DEFAULT],
            'array' => [['Sliding fee'], self::DEFAULT],
        ];
    }

    public function testMemoWithNothingOffered(): void
    {
        $this->assertSame(self::DEFAULT, CheckoutDiscountReason::memo('Sliding fee', [], self::DEFAULT));
    }
}
