<?php

/**
 * Provider-side telehealth requests run only with the session's request token.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\TeleHealth;

use Comlink\OpenEMR\Modules\TeleHealthModule\Util\ProviderRequestTokenCheck;
use OpenEMR\Common\Csrf\CsrfUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * The module's classes are not in the root composer.json autoload map (the
 * module manager registers them when the module is enabled), so the test
 * registers its own autoloader for them, as TelehealthGlobalConfigInstitutionIdTest does.
 */
final class ProviderRequestTokenCheckTest extends TestCase
{
    private const MODULE_PREFIX = 'Comlink\\OpenEMR\\Modules\\TeleHealthModule\\';

    private Session $session;

    /**
     * @codeCoverageIgnore Fixture wiring; runs before coverage attribution.
     */
    public static function setUpBeforeClass(): void
    {
        $moduleSrc = dirname(__DIR__, 5) . '/interface/modules/custom_modules/oe-module-comlink-telehealth/src/';
        if (!is_dir($moduleSrc)) {
            self::fail('Telehealth module source not found at ' . $moduleSrc);
        }
        spl_autoload_register(static function (string $class) use ($moduleSrc): void {
            if (!str_starts_with($class, self::MODULE_PREFIX)) {
                return;
            }
            $file = $moduleSrc . str_replace('\\', '/', substr($class, strlen(self::MODULE_PREFIX))) . '.php';
            if (is_file($file)) {
                require_once $file;
            }
        });
    }

    protected function setUp(): void
    {
        $this->session = new Session(new MockArraySessionStorage());
        CsrfUtils::setupCsrfKey($this->session);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function providerActionProvider(): array
    {
        return [
            'set appointment status' => ['set_appointment_status'],
            'set current appointment encounter' => ['set_current_appt_encounter'],
            'conference session update' => ['conference_session_update'],
            'save session participant' => ['save_session_participant'],
            'generate participant link' => ['generate_participant_link'],
            'check registration' => ['check_registration'],
            'verify installation settings' => ['verify_installation_settings'],
            'get telehealth launch data' => ['get_telehealth_launch_data'],
            'unknown action' => ['not_an_action'],
        ];
    }

    #[DataProvider('providerActionProvider')]
    public function testActionRunsWithSessionToken(string $action): void
    {
        $token = CsrfUtils::collectCsrfToken($this->session, 'api');

        $this->assertTrue((new ProviderRequestTokenCheck($this->session))->allows($action, $token));
    }

    #[DataProvider('providerActionProvider')]
    public function testActionDoesNotRunWithoutToken(string $action): void
    {
        $check = new ProviderRequestTokenCheck($this->session);

        $this->assertFalse($check->allows($action, null));
        $this->assertFalse($check->allows($action, ''));
        $this->assertFalse($check->allows($action, ['value']));
    }

    #[DataProvider('providerActionProvider')]
    public function testActionDoesNotRunWithAnotherToken(string $action): void
    {
        $check = new ProviderRequestTokenCheck($this->session);

        $this->assertFalse($check->allows($action, CsrfUtils::collectCsrfToken($this->session, 'default')));
        $this->assertFalse($check->allows($action, str_repeat('0', 40)));
    }

    public function testSettingsScriptRunsWithoutToken(): void
    {
        $this->assertTrue((new ProviderRequestTokenCheck($this->session))->allows('get_telehealth_settings', null));
    }
}
