<?php

/**
 * Tests for FHIR API write audit request eligibility.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @copyright Copyright (c) 2026 OpenEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Services\FHIR\ApiWriteAuditService;
use PHPUnit\Framework\TestCase;

class ApiWriteAuditServiceTest extends TestCase
{
    public function testOnlyOAuthRequestsWithClientTokenAndRequiredScopeAreAudited(): void
    {
        $service = new ApiWriteAuditService();
        $request = HttpRestRequest::create('/apis/default/fhir/Condition', 'POST');
        self::assertFalse($service->shouldAudit($request));

        $request->setClientId('test-client');
        $request->setAccessTokenId('test-token');
        $request->setRequestRequiredScope(ScopeEntity::createFromString('user/Condition.write'));
        self::assertTrue($service->shouldAudit($request));

        $request->setIsLocalApi(true);
        self::assertFalse($service->shouldAudit($request));
    }
}
