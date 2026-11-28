<?php

/**
 * Isolated test: the EHR launch patient binding checks access with the user's UUID.
 *
 * checkUserHasAccessToPatient() accepts only a UUID string for the user. Passing the
 * numeric users.id (getRequestUserId()) made the check fail for every practitioner,
 * so the launched patient was never bound and patient/ scoped requests returned 401.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestControllers\Authorization;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\RestControllers\Authorization\BearerTokenAuthorizationStrategy;
use PHPUnit\Framework\TestCase;

final class PopulateTokenContextUserUuidIsolatedTest extends TestCase
{
    private const USER_UUID = '9a4b5c6d-1e2f-4a3b-8c7d-0e1f2a3b4c5d';
    private const PATIENT_UUID = '1f2e3d4c-5b6a-4978-8a9b-0c1d2e3f4a5b';

    public function testPatientAccessIsCheckedWithTheUserUuidNotTheNumericId(): void
    {
        $strategy = new class (new OEGlobalsBag(), $this->createMock(EventAuditLogger::class)) extends BearerTokenAuthorizationStrategy {
            public mixed $checkedUserId = null;

            public function getTokenContextForRequest(HttpRestRequest $restRequest)
            {
                return ['patient' => '1f2e3d4c-5b6a-4978-8a9b-0c1d2e3f4a5b'];
            }

            protected function checkUserHasAccessToPatient($userId, $patientUuid): bool
            {
                $this->checkedUserId = $userId;
                return true;
            }
        };

        $request = $this->createMock(HttpRestRequest::class);
        $request->method('getRequestUserId')->willReturn(42);
        $request->method('getRequestUserUUIDString')->willReturn(self::USER_UUID);
        $request->expects($this->once())->method('setPatientUuidString')->with(self::PATIENT_UUID);

        $strategy->populateTokenContextForRequest($request);

        $this->assertSame(self::USER_UUID, $strategy->checkedUserId);
    }
}
