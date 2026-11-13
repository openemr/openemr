<?php

/**
 * PortalSmartAppLauncherTest verifies which clients the patient portal offers and the launch URL it builds.
 *
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Unit\FHIR\SMART;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\FHIR\SMART\PortalSmartAppLauncher;
use OpenEMR\FHIR\SMART\SMARTLaunchToken;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PortalSmartAppLauncherTest extends TestCase
{
    private const FHIR_URL = 'https://example.test/apis/default/fhir';

    /**
     * @return array<string, array{list<string>, bool, string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function clients(): array
    {
        $launchUri = 'https://app.test/launch.php?mode=portal';
        return [
            'patient app' => [['openid', 'launch', 'patient/QuestionnaireResponse.cu'], true, $launchUri, true],
            'no launch scope' => [['openid', 'launch/patient', 'patient/QuestionnaireResponse.cu'], true, $launchUri, false],
            'staff scopes too' => [['openid', 'launch', 'patient/Patient.rs', 'user/Patient.rs'], true, $launchUri, false],
            'system scopes too' => [['openid', 'launch', 'patient/Patient.rs', 'system/Patient.rs'], true, $launchUri, false],
            'no patient scope' => [['openid', 'launch', 'fhirUser'], true, $launchUri, false],
            'disabled' => [['openid', 'launch', 'patient/Patient.rs'], false, $launchUri, false],
            'no launch uri' => [['openid', 'launch', 'patient/Patient.rs'], true, '', false],
            'http launch uri' => [['openid', 'launch', 'patient/Patient.rs'], true, 'http://app.test/launch.php', false],
            'javascript launch uri' => [['openid', 'launch', 'patient/Patient.rs'], true, 'javascript:alert(1)', false],
            'relative launch uri' => [['openid', 'launch', 'patient/Patient.rs'], true, '/launch.php', false],
            'https without host' => [['openid', 'launch', 'patient/Patient.rs'], true, 'https:///launch.php', false],
            'uppercase https scheme' => [['openid', 'launch', 'patient/Patient.rs'], true, 'HTTPS://app.test/launch.php', true],
        ];
    }

    /**
     * @param list<string> $scopes
     */
    #[DataProvider('clients')]
    public function testIsPortalApp(array $scopes, bool $enabled, string $launchUri, bool $expected): void
    {
        $this->assertSame($expected, PortalSmartAppLauncher::isPortalApp($this->client($scopes, $enabled, $launchUri)));
    }

    public function testListPortalAppsKeepsOnlyPortalApps(): void
    {
        $portal = $this->client(['launch', 'patient/Patient.rs'], true, 'https://app.test/launch.php');
        $staff = $this->client(['launch', 'user/Patient.rs'], true, 'https://app.test/launch.php');
        $repository = $this->createMock(ClientRepository::class);
        $repository->method('listClientEntities')->willReturn([$staff, $portal]);

        $this->assertSame([$portal], (new PortalSmartAppLauncher($repository, self::FHIR_URL))->listPortalApps());
    }

    public function testLaunchUrlCarriesThePortalPatientAndKeepsTheLaunchUriQuery(): void
    {
        $client = $this->client(['launch', 'patient/Patient.rs'], true, 'https://app.test/launch.php?mode=portal');
        $launcher = new PortalSmartAppLauncher($this->createMock(ClientRepository::class), self::FHIR_URL);

        $url = $launcher->launchUrl($client, 'patient-uuid');

        $this->assertStringStartsWith('https://app.test/launch.php?mode=portal&launch=', $url);
        $query = [];
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $this->assertSame('portal', $query['mode']);
        $this->assertSame(self::FHIR_URL, $query['iss']);
        $this->assertSame(self::FHIR_URL, $query['aud']);
        $this->assertIsString($query['launch']);
        $token = SMARTLaunchToken::deserializeToken($query['launch']);
        $this->assertSame('patient-uuid', $token->getPatient());
        $this->assertSame(SMARTLaunchToken::INTENT_PATIENT_PORTAL, $token->getIntent());
    }

    public function testLaunchUrlPutsTheLaunchQueryBeforeAFragment(): void
    {
        $client = $this->client(['launch', 'patient/Patient.rs'], true, 'https://app.test/#/launch');
        $launcher = new PortalSmartAppLauncher($this->createMock(ClientRepository::class), self::FHIR_URL);

        $url = $launcher->launchUrl($client, 'patient-uuid');

        $this->assertStringStartsWith('https://app.test/?launch=', $url);
        $this->assertSame('/launch', parse_url($url, PHP_URL_FRAGMENT), 'The fragment stays after the query');
        $query = [];
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $this->assertSame(self::FHIR_URL, $query['iss']);
        $this->assertIsString($query['launch']);
    }

    /**
     * @param list<string> $scopes
     */
    private function client(array $scopes, bool $enabled, string $launchUri): ClientEntity
    {
        $client = new ClientEntity();
        $client->setScopes($scopes);
        $client->setIsEnabled($enabled);
        $client->setLaunchUri($launchUri);
        $client->setName('Test app');
        return $client;
    }
}
