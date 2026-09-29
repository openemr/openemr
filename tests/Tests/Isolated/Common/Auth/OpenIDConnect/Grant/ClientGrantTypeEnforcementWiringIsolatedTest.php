<?php

/**
 * Pins where OAuth2 grant-type enforcement is wired in. The grant classes and the
 * registration endpoint need a database and key material to run, which the isolated tier
 * does not have; these invariants stop a silent removal of the check from shipping.
 * Behaviour is covered by ClientGrantTypePolicyIsolatedTest, ClientGrantTypeGuardTraitIsolatedTest
 * and the API grant tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\OpenIDConnect\Grant;

use OpenEMR\Common\Auth\OpenIDConnect\Grant\ClientGrantTypeGuardTrait;
use OpenEMR\Common\Auth\OpenIDConnect\Grant\CustomAuthCodeGrant;
use OpenEMR\Common\Auth\OpenIDConnect\Grant\CustomClientCredentialsGrant;
use OpenEMR\Common\Auth\OpenIDConnect\Grant\CustomPasswordGrant;
use OpenEMR\Common\Auth\OpenIDConnect\Grant\CustomRefreshTokenGrant;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('security')]
class ClientGrantTypeEnforcementWiringIsolatedTest extends TestCase
{
    private static function source(string $relativePath): string
    {
        $path = __DIR__ . '/../../../../../../../' . $relativePath;
        $content = file_get_contents($path);
        self::assertIsString($content, 'could not read ' . $relativePath);
        return $content;
    }

    /**
     * @return array<string, array{class-string, string, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function grantProvider(): array
    {
        $grantDir = 'src/Common/Auth/OpenIDConnect/Grant/';
        return [
            // the auth code grant checks at the authorize endpoint and at the token endpoint
            'authorization_code' => [CustomAuthCodeGrant::class, $grantDir . 'CustomAuthCodeGrant.php', 2],
            'refresh_token' => [CustomRefreshTokenGrant::class, $grantDir . 'CustomRefreshTokenGrant.php', 1],
            'password' => [CustomPasswordGrant::class, $grantDir . 'CustomPasswordGrant.php', 1],
            'client_credentials' => [CustomClientCredentialsGrant::class, $grantDir . 'CustomClientCredentialsGrant.php', 1],
        ];
    }

    /**
     * @param class-string $grantClass
     */
    #[DataProvider('grantProvider')]
    public function testGrantEnforcesRegisteredGrantTypes(string $grantClass, string $file, int $expectedCalls): void
    {
        $this->assertContains(ClientGrantTypeGuardTrait::class, class_uses($grantClass), $grantClass . ' must use ClientGrantTypeGuardTrait');
        $this->assertSame(
            $expectedCalls,
            preg_match_all('/\$this->assertClientMayUseGrant\(\s*\$client\s*,\s*\$this->getIdentifier\(\)/', self::source($file)),
            $grantClass . ' must check the client against its own grant identifier'
        );
    }

    public function testAuthCodeJwtBranchDoesNotReturnBeforeTheEnabledAndGrantChecks(): void
    {
        $source = self::source('src/Common/Auth/OpenIDConnect/Grant/CustomAuthCodeGrant.php');
        $start = strpos($source, 'protected function validateClient(');
        $this->assertIsInt($start);
        $end = strpos($source, "\n    }\n", $start);
        $this->assertIsInt($end);
        $body = substr($source, $start, $end - $start);
        // exactly one return, at the end, after isEnabled() and the grant-type check
        $this->assertSame(1, substr_count($body, 'return $client;'));
        $this->assertLessThan(strrpos($body, 'return $client;'), strpos($body, '->isEnabled()'));
        $this->assertLessThan(strrpos($body, 'return $client;'), strpos($body, 'assertClientMayUseGrant'));
    }

    public function testRegistrationResolvesGrantTypesBeforePersisting(): void
    {
        $source = self::source('src/RestControllers/AuthorizationController.php');
        $resolve = strpos($source, 'resolveRegistrationGrantTypes(');
        $insert = strpos($source, 'insertNewClient(');
        $this->assertIsInt($resolve, 'clientRegistration must resolve grant_types through ClientGrantTypePolicy');
        $this->assertIsInt($insert);
        $this->assertLessThan($insert, $resolve, 'grant_types must be validated before the client is saved');
    }
}
