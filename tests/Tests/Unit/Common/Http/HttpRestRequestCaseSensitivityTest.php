<?php

/**
 * HttpRestRequestCaseSensitivityTest tests case-sensitive endpoint checking
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    GitHub Copilot AI-generated code
 * @copyright Copyright (c) 2025 OpenEMR Foundation
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Common\Http;

use OpenEMR\Common\Http\HttpRestRequest;
use PHPUnit\Framework\TestCase;

class HttpRestRequestCaseSensitivityTest extends TestCase
{
    /**
     * Test that /fhir/ endpoint check is case-sensitive
     */
    public function testIsFhirRequestIsCaseSensitive(): void
    {
        // Lowercase should match
        $request = HttpRestRequest::create('http://localhost/default/fhir/Patient');
        $this->assertTrue($request->isFhirRequest(), 'Lowercase /fhir/ should match');

        // Uppercase should NOT match (case-sensitive per RFC 3986)
        $request = HttpRestRequest::create('http://localhost/default/FHIR/Patient');
        $this->assertFalse($request->isFhirRequest(), 'Uppercase /FHIR/ should NOT match');

        // Mixed case should NOT match
        $request = HttpRestRequest::create('http://localhost/default/Fhir/Patient');
        $this->assertFalse($request->isFhirRequest(), 'Mixed case /Fhir/ should NOT match');
    }

    /**
     * Test that /portal/ endpoint check is case-sensitive
     */
    public function testIsPortalRequestIsCaseSensitive(): void
    {
        // Lowercase should match
        $request = HttpRestRequest::create('http://localhost/default/portal/patient');
        $this->assertTrue($request->isPortalRequest(), 'Lowercase /portal/ should match');

        // Uppercase should NOT match
        $request = HttpRestRequest::create('http://localhost/default/PORTAL/patient');
        $this->assertFalse($request->isPortalRequest(), 'Uppercase /PORTAL/ should NOT match');

        // Mixed case should NOT match
        $request = HttpRestRequest::create('http://localhost/default/Portal/patient');
        $this->assertFalse($request->isPortalRequest(), 'Mixed case /Portal/ should NOT match');
    }

    /**
     * Test that /api/ endpoint check is case-sensitive
     */
    public function testIsStandardApiRequestIsCaseSensitive(): void
    {
        // Lowercase should match
        $request = HttpRestRequest::create('http://localhost/default/api/facility');
        $this->assertTrue($request->isStandardApiRequest(), 'Lowercase /api/ should match');

        // Uppercase should NOT match
        $request = HttpRestRequest::create('http://localhost/default/API/facility');
        $this->assertFalse($request->isStandardApiRequest(), 'Uppercase /API/ should NOT match');

        // Mixed case should NOT match
        $request = HttpRestRequest::create('http://localhost/default/Api/facility');
        $this->assertFalse($request->isStandardApiRequest(), 'Mixed case /Api/ should NOT match');
    }
}
