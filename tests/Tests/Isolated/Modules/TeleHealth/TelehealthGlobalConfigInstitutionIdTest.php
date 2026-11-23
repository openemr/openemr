<?php

/**
 * The telehealth module registers with Comlink under the installation's unique id.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\TeleHealth;

use Comlink\OpenEMR\Modules\TeleHealthModule\TelehealthGlobalConfig;
use Composer\Autoload\ClassLoader;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

/**
 * The custom_modules/oe-module-comlink-telehealth module is not in the root
 * composer.json autoload map; the module manager registers its PSR-4 prefix
 * when the module is enabled in the database. The isolated suite has no
 * database, so the prefix is registered here before a module class is named.
 */
final class TelehealthGlobalConfigInstitutionIdTest extends TestCase
{
    private const SETTING = 'unique_installation_id';

    private string $loadedBefore = '';

    /**
     * @codeCoverageIgnore Fixture wiring; runs before coverage attribution.
     */
    public static function setUpBeforeClass(): void
    {
        $loaders = ClassLoader::getRegisteredLoaders();
        $loader = reset($loaders);
        if (!$loader instanceof ClassLoader) {
            self::fail('Composer ClassLoader not available to register module autoload prefix.');
        }
        $loader->addPsr4(
            'Comlink\\OpenEMR\\Modules\\TeleHealthModule\\',
            dirname(__DIR__, 5) . '/interface/modules/custom_modules/oe-module-comlink-telehealth/src/'
        );
    }

    protected function setUp(): void
    {
        $this->loadedBefore = OEGlobalsBag::getInstance()->getString(self::SETTING);
    }

    protected function tearDown(): void
    {
        OEGlobalsBag::getInstance()->set(self::SETTING, $this->loadedBefore);
    }

    public function testTheInstitutionIdIsTheInstallationsUniqueId(): void
    {
        // A loaded setting is returned as is, so no database is read.
        OEGlobalsBag::getInstance()->set(self::SETTING, '0b0a6f0e-5d0c-4c7e-9d57-3f6f0a1b2c3d');
        $config = new TelehealthGlobalConfig('', $this->createMock(Environment::class));

        $this->assertSame('0b0a6f0e-5d0c-4c7e-9d57-3f6f0a1b2c3d', $config->getInstitutionId());
    }
}
