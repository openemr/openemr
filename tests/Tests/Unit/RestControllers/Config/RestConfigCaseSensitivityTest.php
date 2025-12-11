<?php

/**
 * RestConfigCaseSensitivityTest tests case-sensitive endpoint checking
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    GitHub Copilot AI-generated code
 * @copyright Copyright (c) 2025 OpenEMR Foundation
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\RestControllers\Config;

use OpenEMR\RestControllers\Config\RestConfig;
use PHPUnit\Framework\TestCase;

class RestConfigCaseSensitivityTest extends TestCase
{
    /**
     * Test that is_fhir_request is case-sensitive
     */
    public function testIsFhirRequestIsCaseSensitive(): void
    {
        // Lowercase should match
        $this->assertTrue(RestConfig::is_fhir_request('/default/fhir/Patient'), 'Lowercase /fhir/ should match');

        // Uppercase should NOT match (case-sensitive per RFC 3986)
        $this->assertFalse(RestConfig::is_fhir_request('/default/FHIR/Patient'), 'Uppercase /FHIR/ should NOT match');

        // Mixed case should NOT match
        $this->assertFalse(RestConfig::is_fhir_request('/default/Fhir/Patient'), 'Mixed case /Fhir/ should NOT match');
    }

    /**
     * Test that is_portal_request is case-sensitive
     */
    public function testIsPortalRequestIsCaseSensitive(): void
    {
        // Lowercase should match
        $this->assertTrue(RestConfig::is_portal_request('/default/portal/patient'), 'Lowercase /portal/ should match');

        // Uppercase should NOT match
        $this->assertFalse(RestConfig::is_portal_request('/default/PORTAL/patient'), 'Uppercase /PORTAL/ should NOT match');

        // Mixed case should NOT match
        $this->assertFalse(RestConfig::is_portal_request('/default/Portal/patient'), 'Mixed case /Portal/ should NOT match');
    }

    /**
     * Test that is_api_request is case-sensitive
     */
    public function testIsApiRequestIsCaseSensitive(): void
    {
        // Lowercase should match
        $this->assertTrue(RestConfig::is_api_request('/default/api/facility'), 'Lowercase /api/ should match');

        // Uppercase should NOT match
        $this->assertFalse(RestConfig::is_api_request('/default/API/facility'), 'Uppercase /API/ should NOT match');

        // Mixed case should NOT match
        $this->assertFalse(RestConfig::is_api_request('/default/Api/facility'), 'Mixed case /Api/ should NOT match');
    }
}
