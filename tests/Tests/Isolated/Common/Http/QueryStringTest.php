<?php

/**
 * Isolated QueryString Test
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Http;

use OpenEMR\Common\Http\QueryString;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class QueryStringTest extends TestCase
{
    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function urlencodeEquivalenceProvider(): array
    {
        return [
            'empty' => [''],
            'zero' => ['0'],
            'space' => ['a b'],
            'reserved' => ['a&b=c?d#e/f:g;h@i+j'],
            'tilde' => ['~user'],
            'percent' => ['100%'],
            'quotes and angle brackets' => ['"\'<script>'],
            'multibyte' => ['Zoë Ñúñez 日本'],
            'newline' => ["line1\nline2"],
            'nul byte' => ["a\0b"],
        ];
    }

    /**
     * The Rector migration depends on this: build() must match urlencode() byte for byte.
     */
    #[DataProvider('urlencodeEquivalenceProvider')]
    public function testBuildEncodesValuesLikeUrlencode(string $value): void
    {
        $this->assertSame('k=' . urlencode($value), QueryString::build(['k' => $value]));
    }

    #[DataProvider('urlencodeEquivalenceProvider')]
    public function testBuildEncodesKeysLikeUrlencode(string $key): void
    {
        $this->assertSame(urlencode($key) . '=v', QueryString::build([$key => 'v']));
    }

    public function testBuildEmptyIsEmptyString(): void
    {
        $this->assertSame('', QueryString::build([]));
    }

    public function testBuildPreservesOrderAndRendersInts(): void
    {
        $this->assertSame(
            'z=1&a=-2&m=x',
            QueryString::build(['z' => 1, 'a' => -2, 'm' => 'x']),
        );
    }

    public function testBuildIgnoresArgSeparatorIniSetting(): void
    {
        $previous = ini_set('arg_separator.output', '&amp;');
        try {
            $this->assertSame('a=1&b=2', QueryString::build(['a' => '1', 'b' => '2']));
        } finally {
            ini_set('arg_separator.output', $previous === false ? '&' : $previous);
        }
    }

    /**
     * @return array<string, array{string, array<string, string|int>, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function appendProvider(): array
    {
        return [
            'no query' => ['x.php', ['a' => '1'], 'x.php?a=1'],
            'existing query' => ['x.php?mode=edit', ['a' => '1'], 'x.php?mode=edit&a=1'],
            'trailing question mark' => ['x.php?', ['a' => '1'], 'x.php?a=1'],
            'trailing ampersand' => ['x.php?mode=edit&', ['a' => '1'], 'x.php?mode=edit&a=1'],
            'fragment' => ['x.php#top', ['a' => '1'], 'x.php?a=1#top'],
            'query and fragment' => ['x.php?m=1#top', ['a' => '1'], 'x.php?m=1&a=1#top'],
            'question mark only in fragment' => ['x.php#a?b', ['a' => '1'], 'x.php?a=1#a?b'],
            'empty url' => ['', ['a' => '1'], '?a=1'],
            'absolute url' => ['https://example.com/p/x.php', ['q' => 'a b'], 'https://example.com/p/x.php?q=a+b'],
            'empty params' => ['x.php#top', [], 'x.php#top'],
        ];
    }

    /**
     * @param array<string, string|int> $params
     */
    #[DataProvider('appendProvider')]
    public function testAppend(string $url, array $params, string $expected): void
    {
        $this->assertSame($expected, QueryString::append($url, $params));
    }

    /**
     * Expected values are what urlencode() and attr_url() produce for the same input.
     *
     * @return array<string, array{mixed, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function untypedProvider(): array
    {
        return [
            'null' => [null, 'k='],
            'false' => [false, 'k='],
            'true' => [true, 'k=1'],
            'int' => [42, 'k=42'],
            'negative int' => [-7, 'k=-7'],
            'float' => [1.5, 'k=1.5'],
            'string' => ['a b&c', 'k=a+b%26c'],
            'numeric string zero' => ['0', 'k=0'],
            'stringable' => [new class implements \Stringable {
                public function __toString(): string
                {
                    return 'x?y';
                }
            }, 'k=x%3Fy'],
        ];
    }

    #[DataProvider('untypedProvider')]
    public function testBuildUntypedCoercesLikeUrlencode(mixed $value, string $expected): void
    {
        $this->assertSame($expected, QueryString::buildUntyped(['k' => $value]));
    }

    #[DataProvider('untypedProvider')]
    public function testAppendUntypedCoercesLikeUrlencode(mixed $value, string $expected): void
    {
        $this->assertSame('x.php?m=1&' . $expected, QueryString::appendUntyped('x.php?m=1', ['k' => $value]));
    }

    /**
     * @return array<string, array{mixed}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unencodableProvider(): array
    {
        return [
            'array' => [['a']],
            'plain object' => [new \stdClass()],
        ];
    }

    #[DataProvider('unencodableProvider')]
    public function testBuildUntypedRejectsWhatUrlencodeCannotTake(mixed $value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Query parameter "k" must be a scalar or Stringable');
        QueryString::buildUntyped(['k' => $value]);
    }
}
