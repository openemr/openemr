<?php

/**
 * SearchFieldStatementResolver Token Resolution Test
 *
 * Verify that resolveTokenField() generates CAST-wrapped SQL for the
 * MISSING modifier so DATETIME columns are never compared directly to
 * empty strings (which MySQL strict mode rejects).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Search;

use OpenEMR\Services\Search\SearchFieldStatementResolver;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use PHPUnit\Framework\TestCase;

class SearchFieldStatementResolverTokenTest extends TestCase
{
    /**
     * When missing=true (code === true), the generated SQL must use
     * CAST(field AS CHAR) = '' instead of field = ''.
     */
    public function testMissingTrueUsesCastForEmptyStringComparison(): void
    {
        $field = new TokenSearchField('enddate', [new TokenSearchValue(true)]);
        $field->setModifier(SearchModifier::MISSING);

        $fragment = SearchFieldStatementResolver::resolveTokenField($field);
        $sql = $fragment->getFragment();

        $this->assertStringContainsString('IS NULL', $sql);
        $this->assertStringContainsString("CAST(enddate AS CHAR) = ''", $sql);
        $this->assertStringNotContainsString("enddate = ''", $sql);
    }

    /**
     * When missing=false (code === false), the generated SQL must use
     * CAST(field AS CHAR) != '' instead of field != ''.
     */
    public function testMissingFalseUsesCastForEmptyStringComparison(): void
    {
        $field = new TokenSearchField('enddate', [new TokenSearchValue(false)]);
        $field->setModifier(SearchModifier::MISSING);

        $fragment = SearchFieldStatementResolver::resolveTokenField($field);
        $sql = $fragment->getFragment();

        $this->assertStringContainsString('IS NOT NULL', $sql);
        $this->assertStringContainsString("CAST(enddate AS CHAR) != ''", $sql);
        $this->assertStringNotContainsString("enddate != ''", $sql);
    }
}
