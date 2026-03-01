<?php

/**
 * ConditionServiceSearchFieldTest
 *
 * Validates that ConditionService::getAll() correctly converts UUID string
 * search parameters to TokenSearchField objects and unsets them before the
 * foreach loop to prevent overwrite.
 *
 * Regression test for GitHub issue #10827: the foreach loop in getAll()
 * was overwriting the correct TokenSearchField('puuid') with a
 * StringSearchField because the original $search['puuid'] was not unset.
 *
 * This test inspects the source code to verify the fix is in place,
 * since the service requires a database connection for full integration testing.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 * AI-Generated Code Notice: This file contains code generated with
 * assistance from Claude Code (Anthropic). The code has been reviewed
 * and tested by the contributor.
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services;

use PHPUnit\Framework\TestCase;

class ConditionServiceSearchFieldTest extends TestCase
{
    private string $conditionServiceFile;
    private string $allergyServiceFile;

    protected function setUp(): void
    {
        $this->conditionServiceFile = realpath(__DIR__ . '/../../../../src/Services/ConditionService.php');
        $this->allergyServiceFile = realpath(__DIR__ . '/../../../../src/Services/AllergyIntoleranceService.php');

        if ($this->conditionServiceFile === false) {
            $this->markTestSkipped('ConditionService.php not found');
        }
        if ($this->allergyServiceFile === false) {
            $this->markTestSkipped('AllergyIntoleranceService.php not found');
        }
    }

    /**
     * ConditionService must unset $search['puuid'] after creating TokenSearchField
     * to prevent the foreach loop from overwriting it with a StringSearchField.
     */
    public function testConditionServiceUnsetsPuuidBeforeForeach(): void
    {
        $content = file_get_contents($this->conditionServiceFile);

        // The TokenSearchField creation for puuid must be followed by unset
        // before the foreach loop
        $pattern = '/TokenSearchField\s*\(\s*[\'"]puuid[\'"]\s*,.*?\);\s*\n\s*unset\s*\(\s*\$search\s*\[\s*[\'"]puuid[\'"]\s*\]\s*\)/s';
        $this->assertMatchesRegularExpression(
            $pattern,
            $content,
            'ConditionService must unset $search[\'puuid\'] after creating TokenSearchField to prevent foreach overwrite'
        );
    }

    /**
     * ConditionService must convert condition_uuid to TokenSearchField
     * for proper binary UUID comparison.
     */
    public function testConditionServiceConvertsConditionUuidToTokenSearchField(): void
    {
        $content = file_get_contents($this->conditionServiceFile);

        $this->assertStringContainsString(
            "TokenSearchField('condition_uuid'",
            $content,
            'ConditionService::getAll() must convert condition_uuid to TokenSearchField for binary comparison'
        );

        // Must also unset to prevent foreach overwrite
        $pattern = '/TokenSearchField\s*\(\s*[\'"]condition_uuid[\'"]\s*,.*?\);\s*\n\s*unset\s*\(\s*\$search\s*\[\s*[\'"]condition_uuid[\'"]\s*\]\s*\)/s';
        $this->assertMatchesRegularExpression(
            $pattern,
            $content,
            'ConditionService must unset $search[\'condition_uuid\'] after creating TokenSearchField'
        );
    }

    /**
     * AllergyIntoleranceService must convert allergy_uuid to TokenSearchField
     * for proper binary UUID comparison.
     */
    public function testAllergyServiceConvertsAllergyUuidToTokenSearchField(): void
    {
        $content = file_get_contents($this->allergyServiceFile);

        $this->assertStringContainsString(
            "TokenSearchField('allergy_uuid'",
            $content,
            'AllergyIntoleranceService::getAll() must convert allergy_uuid to TokenSearchField for binary comparison'
        );

        // Must also unset to prevent foreach overwrite
        $pattern = '/TokenSearchField\s*\(\s*[\'"]allergy_uuid[\'"]\s*,.*?\);\s*\n\s*unset\s*\(\s*\$search\s*\[\s*[\'"]allergy_uuid[\'"]\s*\]\s*\)/s';
        $this->assertMatchesRegularExpression(
            $pattern,
            $content,
            'AllergyIntoleranceService must unset $search[\'allergy_uuid\'] after creating TokenSearchField'
        );
    }

    /**
     * AllergyIntoleranceService must unset $search['puuid'] after creating
     * TokenSearchField (verify the existing fix is not regressed).
     */
    public function testAllergyServiceUnsetsPuuidBeforeForeach(): void
    {
        $content = file_get_contents($this->allergyServiceFile);

        $pattern = '/TokenSearchField\s*\(\s*[\'"]puuid[\'"]\s*,\s*\$search\s*\[\s*[\'"]puuid[\'"]\s*\]\s*,\s*true\s*\);\s*\n\s*unset\s*\(\s*\$search\s*\[\s*[\'"]puuid[\'"]\s*\]\s*\)/s';
        $this->assertMatchesRegularExpression(
            $pattern,
            $content,
            'AllergyIntoleranceService must unset $search[\'puuid\'] after creating TokenSearchField'
        );
    }
}
