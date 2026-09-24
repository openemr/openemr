<?php

/**
 * Tests for HandBuiltQueryStringRule.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PHPStan;

use OpenEMR\PHPStan\Rules\HandBuiltQueryString;
use OpenEMR\PHPStan\Rules\HandBuiltQueryStringRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<HandBuiltQueryStringRule>
 */
final class HandBuiltQueryStringRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new HandBuiltQueryStringRule();
    }

    public function testFlagsEachHandBuiltParameterOnce(): void
    {
        $message = static fn (string $key, string $encoding): string => sprintf(
            'Query parameter "%s" is concatenated by hand (%s). Build the query with QueryString::build().',
            $key,
            $encoding,
        );
        $this->analyse(
            [__DIR__ . '/data/hand_built_query_string.php'],
            [
                [$message('id', 'urlencode(), typed'), 12, HandBuiltQueryString::TIP],
                [$message('id', 'urlencode(), typed'), 13, HandBuiltQueryString::TIP],
                [$message('pid', 'unencoded int'), 13, HandBuiltQueryString::TIP],
                [$message('raw', 'unencoded, untyped'), 14, HandBuiltQueryString::TIP],
                [$message('(dynamic)', 'dynamic key'), 15, HandBuiltQueryString::TIP],
                [$message('(dynamic)', 'dynamic key'), 16, HandBuiltQueryString::TIP],
                [$message('document_id', 'urlencode(), typed'), 27, HandBuiltQueryString::TIP],
            ],
        );
    }
}
