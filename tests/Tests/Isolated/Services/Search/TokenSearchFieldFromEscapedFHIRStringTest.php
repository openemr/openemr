<?php

/**
 * Isolated tests for TokenSearchField::fromEscapedFHIRString(), which the FHIR category routers use
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    oldrobotdev <hi@oldrobot.us>
 * @copyright Copyright (c) 2026 oldrobotdev
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Search;

use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TokenSearchFieldFromEscapedFHIRStringTest extends TestCase
{
    /**
     * @return array<string, array{string, list<array{?string, string}>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function categoryProvider(): array
    {
        return [
            'plain category' => ['vital-signs', [[null, 'vital-signs']]],
            'two categories' => ['vital-signs,laboratory', [[null, 'vital-signs'], [null, 'laboratory']]],
            'escaped comma is one category' => ['foo\,laboratory', [[null, 'foo,laboratory']]],
            'escaped pipe is part of the code' => ['x\|vital-signs', [[null, 'x|vital-signs']]],
            'escaped pipe is part of the system' => ['urn:x\|y|vital-signs', [['urn:x|y', 'vital-signs']]],
            'escaped backslash' => ['a\\\\b', [[null, 'a\\b']]],
        ];
    }

    /**
     * @param list<array{?string, string}> $expected
     */
    #[DataProvider('categoryProvider')]
    public function testValueIsReadLikeTheSearchFieldFactory(string $category, array $expected): void
    {
        $field = TokenSearchField::fromEscapedFHIRString('category', $category);

        $actual = [];
        foreach ($field->getValues() as $value) {
            $this->assertInstanceOf(TokenSearchValue::class, $value);
            $actual[] = [$value->getSystem(), $value->getCode()];
        }
        $this->assertSame($expected, $actual);
    }

    public function testRepeatedParameterIsRejected(): void
    {
        $this->expectException(SearchFieldException::class);
        TokenSearchField::fromEscapedFHIRString('category', ['vital-signs', 'laboratory']);
    }
}
