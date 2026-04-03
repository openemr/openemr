<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\library;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class GlobalFunctionsTest extends TestCase
{
    public static function dateProvider(): array
    {
        return [
            'null uses default' => [null, 'default', 'default'],
            'empty string uses default' => ['', 'default', 'default'],
            'invalid uses default' => ['notadate', 'default', 'default'],
            'default can be null' => ['notadate', null, null],
            '01/02/2023' => ['01/02/2023', null, '2023-02-01'],
            '1/2/87' => ['1/2/87', null, '1987-02-01'],
            // Plenty more to test here, just coverign a few basics
        ];
    }

    #[DataProvider('dateProvider')]
    public function testFixDate(?string $input, mixed $default, mixed $expected): void
    {
        $result = fixDate($input, $default);
        self::assertSame($expected, $result);
    }
}
