<?php

/**
 * Services-tier integration coverage for PortalSmartAppLauncher::listPortalApps(),
 * the DB-backed filter that ships with openemr/openemr#15272.
 *
 * Complements the unit coverage in
 * tests/Tests/Unit/FHIR/SMART/PortalSmartAppLauncherTest.php — those tests mock
 * ClientRepository and exercise the isPortalApp() predicate directly. This file
 * exercises the full pipeline: rows in oauth_clients → ClientRepository::listClientEntities()
 * → PortalSmartAppLauncher::listPortalApps() filter, so a regression in either the
 * hydration (scope column split, is_enabled coercion, initiate_login_uri rename)
 * or the filter is caught end-to-end.
 *
 * Why a services-tier test and not an api-tier flow: the launcher is a pure read
 * on oauth_clients; mounting the full OAuth2 authorize/token dance to probe
 * list filtering would be out of proportion to the question being asked.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR\SMART;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\FHIR\SMART\PortalSmartAppLauncher;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class PortalSmartAppLauncherIntegrationTest extends TestCase
{
    /**
     * Unique per-test prefix so tearDown can sweep the oauth_clients rows this
     * test family planted without touching DCR'd clients that other tests
     * or fixture data left behind.
     */
    private const CLIENT_ID_PREFIX = 'portal-smart-app-launcher-integration-';

    private const FHIR_URL = 'https://example.test/apis/default/fhir';

    /** @var list<string> */
    private array $trackedClientIds = [];

    protected function tearDown(): void
    {
        foreach ($this->trackedClientIds as $clientId) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM oauth_clients WHERE client_id = ?',
                [$clientId]
            );
        }
        $this->trackedClientIds = [];
        parent::tearDown();
    }

    #[Test]
    public function testPortalEligibleClientAppearsInListPortalApps(): void
    {
        $portalClientId = $this->insertOAuthClient(
            name: 'Portal-eligible test app',
            scope: 'openid launch patient/Patient.rs patient/QuestionnaireResponse.cu',
            launchUri: 'https://app.test/launch.php?mode=portal',
            isEnabled: true,
        );

        self::assertContains(
            $portalClientId,
            $this->listedPortalAppIds(),
            'A DCR-shaped client with patient-only scopes + https launch URI must appear in listPortalApps()'
        );
    }

    #[Test]
    public function testStaffScopedClientDoesNotAppearInListPortalApps(): void
    {
        // A client that mixes patient/ + user/ scopes belongs on the staff
        // launch card, not the portal — the SMART App Launch portal flow is
        // scoped to patient-tier bearer tokens.
        $staffClientId = $this->insertOAuthClient(
            name: 'Staff-scoped test app',
            scope: 'openid launch patient/Patient.rs user/Patient.rs',
            launchUri: 'https://app.test/launch.php',
            isEnabled: true,
        );

        self::assertNotContains(
            $staffClientId,
            $this->listedPortalAppIds(),
            'A client whose scope set contains user/ must never surface in listPortalApps()'
        );
    }

    #[Test]
    public function testSystemScopedClientDoesNotAppearInListPortalApps(): void
    {
        // Mirror case for backend/B2B apps — system/ scoped clients run
        // server-to-server and never come through the portal launch flow.
        $systemClientId = $this->insertOAuthClient(
            name: 'System-scoped test app',
            scope: 'openid launch patient/Patient.rs system/Patient.rs',
            launchUri: 'https://app.test/launch.php',
            isEnabled: true,
        );

        self::assertNotContains(
            $systemClientId,
            $this->listedPortalAppIds(),
            'A client whose scope set contains system/ must never surface in listPortalApps()'
        );
    }

    #[Test]
    public function testDisabledClientDoesNotAppearInListPortalApps(): void
    {
        // An admin disabling a client via the client-management UI
        // (oauth_clients.is_enabled=0) must immediately take it off the
        // portal dashboard — this is the server-side off switch.
        $disabledClientId = $this->insertOAuthClient(
            name: 'Disabled test app',
            scope: 'openid launch patient/Patient.rs',
            launchUri: 'https://app.test/launch.php',
            isEnabled: false,
        );

        self::assertNotContains(
            $disabledClientId,
            $this->listedPortalAppIds(),
            'A client with is_enabled=0 must never surface in listPortalApps()'
        );
    }

    #[Test]
    public function testJavascriptLaunchUriDoesNotAppearInListPortalApps(): void
    {
        // A javascript: launch URI would render the Launch link as an
        // in-page script invocation on the portal dashboard — belt-and-
        // suspenders rejection on top of the twig autoescape layer.
        $jsClientId = $this->insertOAuthClient(
            name: 'Javascript-URI test app',
            scope: 'openid launch patient/Patient.rs',
            launchUri: 'javascript:alert(1)',
            isEnabled: true,
        );

        self::assertNotContains(
            $jsClientId,
            $this->listedPortalAppIds(),
            'A client whose launch URI is not an absolute https URL must never surface in listPortalApps()'
        );
    }

    #[Test]
    public function testClientWithoutPatientScopeDoesNotAppearInListPortalApps(): void
    {
        // launch + openid alone is not enough — the portal only offers apps
        // that will actually get a patient-tier token, which requires at
        // least one patient/ scope.
        $noPatientClientId = $this->insertOAuthClient(
            name: 'No-patient-scope test app',
            scope: 'openid launch fhirUser',
            launchUri: 'https://app.test/launch.php',
            isEnabled: true,
        );

        self::assertNotContains(
            $noPatientClientId,
            $this->listedPortalAppIds(),
            'A client without any patient/ scope must never surface in listPortalApps()'
        );
    }

    /**
     * @return list<ClientEntity>
     */
    private function listPortalApps(): array
    {
        return (new PortalSmartAppLauncher(new ClientRepository(), self::FHIR_URL))->listPortalApps();
    }

    /**
     * @return list<string>
     */
    private function listedPortalAppIds(): array
    {
        $ids = [];
        foreach ($this->listPortalApps() as $client) {
            $id = $client->getIdentifier();
            // ClientEntity::getIdentifier() inherits League\OAuth2\Server's
            // mixed return type, but the backing column (oauth_clients.client_id)
            // is varchar NOT NULL — assert to narrow at the test seam.
            self::assertIsString($id);
            $ids[] = $id;
        }
        return $ids;
    }

    /**
     * Inserts a minimal oauth_clients row matching what DCR would plant for a
     * SMART app. Returns the client_id so the test can both track it for
     * cleanup and assert against it. Defaults match the smallest DCR row that
     * ClientRepository::hydrateClientEntityFromArray() can round-trip without
     * tripping a column-is-null error.
     */
    private function insertOAuthClient(
        string $name,
        string $scope,
        string $launchUri,
        bool $isEnabled,
    ): string {
        $clientId = self::CLIENT_ID_PREFIX . Uuid::uuid4()->toString();
        // redirect_uri is non-null-safe through the hydrator: ClientRepository
        // calls ClientEntity::setRedirectUri() which rejects null. The DCR
        // path always sets it; set a dummy here so the hydrator does not
        // choke when listClientEntities() iterates this row.
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO oauth_clients "
                . "(client_id, client_role, client_name, client_secret, scope, redirect_uri, initiate_login_uri, is_enabled, is_confidential) "
                . "VALUES (?, 'patient', ?, '', ?, ?, ?, ?, 0)",
            [$clientId, $name, $scope, 'https://app.test/callback', $launchUri, $isEnabled ? 1 : 0]
        );
        $this->trackedClientIds[] = $clientId;
        return $clientId;
    }
}
