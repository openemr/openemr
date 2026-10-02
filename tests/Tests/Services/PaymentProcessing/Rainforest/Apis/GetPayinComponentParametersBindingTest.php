<?php

/**
 * Database-backed tests for the GetPayinComponentParameters binding check.
 *
 * Verifies that encounter rows in the request body must reference real,
 * active billing entries for the session-bound patient. Paired with the
 * webhook side, which re-derives payment state from the gateway-signed
 * identifier (see `RainforestWebhookAuthorityIsolatedTest`); this half
 * stops a mismatched payin_config from being created in the first place.
 * The isolated unit tests for `parseRawRequest` cannot reach this check
 * because the lookup goes through `QueryUtils::fetchRecords`, so coverage
 * lives here in the Services suite where a real database is available.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\PaymentProcessing\Rainforest\Apis;

use Nyholm\Psr7\Factory\Psr17Factory;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\PaymentProcessing\Rainforest\Apis\GetPayinComponentParameters;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use UnexpectedValueException;

final class GetPayinComponentParametersBindingTest extends TestCase
{
    /**
     * PIDs chosen high enough to avoid collision with seeded fixtures.
     */
    private const TEST_PID = 999999041;
    private const OTHER_PID = 999999042;

    private const TEST_ENCOUNTER = 999999041;

    private const TEST_CODE = '99213';
    private const TEST_CODE_TYPE = 'CPT4';

    private Psr17Factory $factory;

    protected function setUp(): void
    {
        $this->factory = new Psr17Factory();
        $this->cleanUpTestData();
    }

    protected function tearDown(): void
    {
        $this->cleanUpTestData();
    }

    public function testThrowsWhenEncounterDoesNotExistForAnyPatient(): void
    {
        // No billing rows seeded — request references an encounter that
        // does not exist.
        $request = $this->buildRequest('10.00', [
            $this->encounter(self::TEST_ENCOUNTER, self::TEST_CODE, self::TEST_CODE_TYPE, '10.00'),
        ]);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Encounter line does not belong to patient');
        GetPayinComponentParameters::parseRawRequest(
            $request,
            $this->createMock(OEGlobalsBag::class),
            (string) self::TEST_PID,
        );
    }

    public function testThrowsWhenEncounterBelongsToAnotherPatient(): void
    {
        // A real billing row exists, but it belongs to another patient.
        // parseRawRequest is bound to TEST_PID, so OTHER_PID's encounter
        // must not be reachable.
        $this->insertBilling(
            self::OTHER_PID,
            self::TEST_ENCOUNTER,
            self::TEST_CODE,
            self::TEST_CODE_TYPE,
            activity: 1,
        );

        $request = $this->buildRequest('10.00', [
            $this->encounter(self::TEST_ENCOUNTER, self::TEST_CODE, self::TEST_CODE_TYPE, '10.00'),
        ]);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Encounter line does not belong to patient');
        GetPayinComponentParameters::parseRawRequest(
            $request,
            $this->createMock(OEGlobalsBag::class),
            (string) self::TEST_PID,
        );
    }

    public function testThrowsWhenBillingRowIsInactive(): void
    {
        // Reversed/voided billing rows (activity=0) should not be payable
        // even when they belong to the bound patient.
        $this->insertBilling(
            self::TEST_PID,
            self::TEST_ENCOUNTER,
            self::TEST_CODE,
            self::TEST_CODE_TYPE,
            activity: 0,
        );

        $request = $this->buildRequest('10.00', [
            $this->encounter(self::TEST_ENCOUNTER, self::TEST_CODE, self::TEST_CODE_TYPE, '10.00'),
        ]);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Encounter line does not belong to patient');
        GetPayinComponentParameters::parseRawRequest(
            $request,
            $this->createMock(OEGlobalsBag::class),
            (string) self::TEST_PID,
        );
    }

    public function testThrowsWhenCodeTypeDoesNotMatchBilling(): void
    {
        // The composite key includes (encounter, code_type, code), so a
        // request with a code_type that doesn't match the billing row
        // must not coast through on the encounter+code pair alone.
        $this->insertBilling(
            self::TEST_PID,
            self::TEST_ENCOUNTER,
            self::TEST_CODE,
            self::TEST_CODE_TYPE,
            activity: 1,
        );

        $request = $this->buildRequest('10.00', [
            $this->encounter(self::TEST_ENCOUNTER, self::TEST_CODE, 'HCPCS', '10.00'),
        ]);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Encounter line does not belong to patient');
        GetPayinComponentParameters::parseRawRequest(
            $request,
            $this->createMock(OEGlobalsBag::class),
            (string) self::TEST_PID,
        );
    }

    /**
     * @return array{id: string, code: string, codeType: string, value: string}
     */
    private function encounter(int $encounter, string $code, string $codeType, string $value): array
    {
        return [
            'id' => (string) $encounter,
            'code' => $code,
            'codeType' => $codeType,
            'value' => $value,
        ];
    }

    /**
     * @param array<int, array{id: string, code: string, codeType: string, value: string}> $encounters
     */
    private function buildRequest(string $dollars, array $encounters): ServerRequestInterface
    {
        // patientId is present in the body but the parseRawRequest calls
        // below all pass a trustedPatientId, which takes precedence — the
        // body value here just satisfies the array-shape docblock.
        $body = json_encode([
            'dollars' => $dollars,
            'patientId' => (string) self::TEST_PID,
            'encounters' => $encounters,
        ], JSON_THROW_ON_ERROR);

        $request = $this->factory->createServerRequest('POST', '/payment');
        $request = $request->withHeader('Content-Type', 'application/json');
        return $request->withBody($this->factory->createStream($body));
    }

    private function insertBilling(
        int $pid,
        int $encounter,
        string $code,
        string $codeType,
        int $activity,
    ): void {
        QueryUtils::sqlInsert(
            'INSERT INTO billing (pid, encounter, code, code_type, units, fee, provider_id, activity)'
            . ' VALUES (?, ?, ?, ?, 1, 10.00, 1, ?)',
            [$pid, $encounter, $code, $codeType, $activity],
        );
    }

    private function cleanUpTestData(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM billing WHERE pid IN (?, ?)',
            [self::TEST_PID, self::OTHER_PID],
        );
    }
}
