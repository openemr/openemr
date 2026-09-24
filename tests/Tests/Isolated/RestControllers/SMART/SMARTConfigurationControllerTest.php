<?php

/**
 * Isolated tests for the SMART on FHIR configuration document
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestControllers\SMART;

use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ScopeRepository;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\FHIR\Config\ServerConfig;
use OpenEMR\RestControllers\SMART\SMARTConfigurationController;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SMARTConfigurationControllerTest extends TestCase
{
    private const SCOPES = ['openid', 'launch/patient', 'patient/Patient.rs'];

    /**
     * With oauth_password_grant at 0 the password grant is not advertised.
     */
    public function testGrantTypesLeaveOutPasswordWhenThePasswordGrantIsOff(): void
    {
        $config = $this->buildConfig('0');

        $this->assertSame(['client_credentials', 'authorization_code'], $config['grant_types_supported']);
    }

    /**
     * With oauth_password_grant for users, patients or both the password grant is advertised.
     */
    #[DataProvider('passwordGrantEnabledProvider')]
    public function testGrantTypesIncludePasswordWhenThePasswordGrantIsOn(string $setting): void
    {
        $config = $this->buildConfig($setting);

        $this->assertSame(
            ['client_credentials', 'authorization_code', 'password'],
            $config['grant_types_supported']
        );
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function passwordGrantEnabledProvider(): array
    {
        return [
            'users' => ['1'],
            'patients' => ['2'],
            'users and patients' => ['3'],
        ];
    }

    /**
     * scopes_supported is the scope list itself, not an array wrapping it.
     */
    public function testScopesSupportedIsTheScopeListItself(): void
    {
        $config = $this->buildConfig('0');

        $this->assertSame(self::SCOPES, $config['scopes_supported']);
    }

    /**
     * The token endpoint auth methods include client_secret_post next to the two already listed.
     */
    public function testTokenEndpointAuthMethodsIncludeClientSecretPost(): void
    {
        $config = $this->buildConfig('0');

        $this->assertSame(
            ['client_secret_basic', 'client_secret_post', 'private_key_jwt'],
            $config['token_endpoint_auth_methods_supported']
        );
    }

    /**
     * Builds the configuration with the given oauth_password_grant value, as the globals table stores it.
     *
     * @return array<string, mixed>
     */
    private function buildConfig(string $passwordGrant): array
    {
        $scopeRepository = $this->createStub(ScopeRepository::class);
        $scopeRepository->method('getCurrentSmartScopes')->willReturn(self::SCOPES);

        $controller = new SMARTConfigurationController(
            new OEGlobalsBag(['oauth_password_grant' => $passwordGrant]),
            $scopeRepository,
            $this->createStub(ServerConfig::class)
        );

        return $controller->getConfig();
    }
}
