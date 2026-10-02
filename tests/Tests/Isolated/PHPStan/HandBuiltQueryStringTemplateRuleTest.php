<?php

/**
 * Tests for HandBuiltQueryStringTemplateRule.
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
use OpenEMR\PHPStan\Rules\HandBuiltQueryStringTemplateRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<HandBuiltQueryStringTemplateRule>
 */
final class HandBuiltQueryStringTemplateRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new HandBuiltQueryStringTemplateRule();
    }

    public function testFlagsParametersEchoedIntoInlineHtml(): void
    {
        $message = static fn (string $key, string $encoding): string => sprintf(
            'Query parameter "%s" is concatenated by hand in a template (%s). Build the query with QueryString::build().',
            $key,
            $encoding,
        );
        $this->analyse(
            [__DIR__ . '/data/hand_built_query_string_template.php'],
            [
                [$message('id', 'attr_url()'), 10, HandBuiltQueryString::TIP],
                [$message('row', 'unencoded'), 12, HandBuiltQueryString::TIP],
                [$message('(dynamic)', 'attr()'), 14, HandBuiltQueryString::TIP],
                [$message('document_id', 'attr_url()'), 17, HandBuiltQueryString::TIP],
                [$message('order', 'js_url()'), 19, HandBuiltQueryString::TIP],
            ],
        );
    }
}
