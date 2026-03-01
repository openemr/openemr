<?php

/**
 * AllergyConditionRouteParameterTest
 *
 * Validates that per-patient allergy and condition REST API routes
 * pass UUID-based search keys (puuid, allergy_uuid, condition_uuid)
 * instead of legacy table-column references (lists.pid, lists.id).
 *
 * Regression test for GitHub issue #10827: per-patient scoped allergy
 * and condition endpoints returning empty results because UUID strings
 * were compared against numeric PID columns or binary UUID columns
 * without proper TokenSearchField conversion.
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

namespace OpenEMR\Tests\Isolated\RestRoutes;

use PHPUnit\Framework\TestCase;

class AllergyConditionRouteParameterTest extends TestCase
{
    private string $routeFile;

    protected function setUp(): void
    {
        $this->routeFile = realpath(__DIR__ . '/../../../../apis/routes/_rest_routes_standard.inc.php');
        if ($this->routeFile === false) {
            $this->markTestSkipped('Route file not found');
        }
    }

    /**
     * Verify that per-patient allergy list route uses 'puuid' key
     * instead of the legacy 'lists.pid' which caused UUID-to-PID mismatch.
     */
    public function testPatientAllergyListRouteUsesPuuid(): void
    {
        $content = file_get_contents($this->routeFile);

        // Find the per-patient allergy list route
        $pattern = '/GET \/api\/patient\/:puuid\/allergy".*?getAll\(\[([^\]]+)\]\)/s';
        $this->assertMatchesRegularExpression($pattern, $content, 'Per-patient allergy list route not found');

        preg_match($pattern, $content, $matches);
        $params = $matches[1];

        $this->assertStringContainsString("'puuid'", $params, 'Route should pass puuid key for patient filtering');
        $this->assertStringNotContainsString("'lists.pid'", $params, 'Route should not use legacy lists.pid key');
    }

    /**
     * Verify that per-patient single allergy route uses 'puuid' and
     * 'allergy_uuid' keys instead of legacy 'lists.pid' and 'lists.id'.
     */
    public function testPatientSingleAllergyRouteUsesUuidKeys(): void
    {
        $content = file_get_contents($this->routeFile);

        // Find the per-patient single allergy route
        $pattern = '/GET \/api\/patient\/:puuid\/allergy\/:auuid".*?getAll\(\[([^\]]+)\]\)/s';
        $this->assertMatchesRegularExpression($pattern, $content, 'Per-patient single allergy route not found');

        preg_match($pattern, $content, $matches);
        $params = $matches[1];

        $this->assertStringContainsString("'puuid'", $params, 'Route should pass puuid key for patient filtering');
        $this->assertStringContainsString("'allergy_uuid'", $params, 'Route should pass allergy_uuid key for allergy filtering');
        $this->assertStringNotContainsString("'lists.pid'", $params, 'Route should not use legacy lists.pid key');
        $this->assertStringNotContainsString("'lists.id'", $params, 'Route should not use legacy lists.id key');
    }

    /**
     * Verify that per-patient condition list route uses 'puuid' key.
     */
    public function testPatientConditionListRouteUsesPuuid(): void
    {
        $content = file_get_contents($this->routeFile);

        // Find the per-patient condition list route (medical_problem)
        $pattern = '/GET \/api\/patient\/:puuid\/medical_problem".*?ConditionRestController.*?getAll\(\[([^\]]+)\]\)/s';
        $this->assertMatchesRegularExpression($pattern, $content, 'Per-patient condition list route not found');

        preg_match($pattern, $content, $matches);
        $params = $matches[1];

        $this->assertStringContainsString("'puuid'", $params, 'Route should pass puuid key for patient filtering');
    }

    /**
     * Verify that per-patient single condition route uses 'puuid' and
     * 'condition_uuid' keys.
     */
    public function testPatientSingleConditionRouteUsesUuidKeys(): void
    {
        $content = file_get_contents($this->routeFile);

        // Find the per-patient single condition route
        $pattern = '/GET \/api\/patient\/:puuid\/medical_problem\/:muuid".*?ConditionRestController.*?getAll\(\[([^\]]+)\]\)/s';
        $this->assertMatchesRegularExpression($pattern, $content, 'Per-patient single condition route not found');

        preg_match($pattern, $content, $matches);
        $params = $matches[1];

        $this->assertStringContainsString("'puuid'", $params, 'Route should pass puuid key for patient filtering');
        $this->assertStringContainsString("'condition_uuid'", $params, 'Route should pass condition_uuid key');
    }
}
