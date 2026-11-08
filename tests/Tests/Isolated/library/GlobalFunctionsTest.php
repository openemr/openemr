<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\library;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class GlobalFunctionsTest extends TestCase
{
    /**
     * @return array{?string, ?string, ?string}[]
     */
    public static function dateProvider(): array
    {
        return [
            'null uses default' => [null, 'default', 'default'],
            'empty string uses default' => ['', 'default', 'default'],
            'invalid uses default' => ['notadate', 'default', 'default'],
            'default can be null' => ['notadate', null, null],
            // DMY format with various separators (test env uses DMY, not MDY)
            '01/02/2023 (slash)' => ['01/02/2023', null, '2023-02-01'],
            '01-02-2023 (dash)' => ['01-02-2023', null, '2023-02-01'],
            '01.02.2023 (dot)' => ['01.02.2023', null, '2023-02-01'],

            // Two-digit year handling: 00-09 → 2000-2009
            '1/2/00' => ['1/2/00', null, '2000-02-01'],
            '1/2/05' => ['1/2/05', null, '2005-02-01'],
            '1/2/09' => ['1/2/09', null, '2009-02-01'],

            // Two-digit year handling: 10-99 → 1910-1999
            '1/2/10' => ['1/2/10', null, '1910-02-01'],
            '1/2/87' => ['1/2/87', null, '1987-02-01'],
            '1/2/99' => ['1/2/99', null, '1999-02-01'],

            // Without leading zeros
            '1/2/2023' => ['1/2/2023', null, '2023-02-01'],
            '31/12/2023' => ['31/12/2023', null, '2023-12-31'],

            // YYYY/MM/DD format (first part > 99)
            '2023/01/15' => ['2023/01/15', null, '2023-01-15'],
            '2023-06-30' => ['2023-06-30', null, '2023-06-30'],
            '100/01/01' => ['100/01/01', null, '0100-01-01'],

            // All zeros produces 0000-00-00 (skips year adjustment)
            '0/0/0' => ['0/0/0', null, '0000-00-00'],
            '00/00/00' => ['00/00/00', null, '0000-00-00'],

            // Whitespace handling
            '  01/02/2023  ' => ['  01/02/2023  ', null, '2023-02-01'],

            // Three-digit years (< 1000 → +1900, then < 1910 → +100)
            '1/2/100' => ['1/2/100', null, '2000-02-01'],
            '1/2/900' => ['1/2/900', null, '2800-02-01'],
        ];
    }

    #[DataProvider('dateProvider')]
    public function testFixDate(?string $input, mixed $default, mixed $expected): void
    {
        $result = fixDate($input, $default);
        self::assertSame($expected, $result);
    }
}
