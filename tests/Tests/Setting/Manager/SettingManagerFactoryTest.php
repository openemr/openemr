<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Setting\Manager;

use OpenEMR\Setting\Driver\SettingDriverInterface;
use OpenEMR\Setting\Manager\BooleanSettingManager;
use OpenEMR\Setting\Manager\CodeTypeSettingManager;
use OpenEMR\Setting\Manager\CompositeSettingManager;
use OpenEMR\Setting\Manager\EncryptedHashSettingManager;
use OpenEMR\Setting\Manager\EncryptedSettingManager;
use OpenEMR\Setting\Manager\EnumSettingManager;
use OpenEMR\Setting\Manager\LanguageSettingManager;
use OpenEMR\Setting\Manager\MultiLanguageSettingManager;
use OpenEMR\Setting\Manager\MultiListSettingManager;
use OpenEMR\Setting\Manager\NumberSettingManager;
use OpenEMR\Setting\Manager\ScalarSettingManager;
use OpenEMR\Setting\Manager\SettingManagerFactory;
use OpenEMR\Setting\Manager\SettingManagerInterface;
use OpenEMR\Setting\Manager\VisitCategorySettingManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('setting')]
#[CoversClass(SettingManagerFactory::class)]
#[CoversMethod(SettingManagerFactory::class, 'createNewWithDriver')]
class SettingManagerFactoryTest extends TestCase
{
    #[Test]
    public function createNewWithDriverTest(): void
    {
        $driver = $this->createMock(SettingDriverInterface::class);
        $compositeManager = SettingManagerFactory::createNewWithDriver($driver);

        $reflection = new \ReflectionClass(CompositeSettingManager::class);
        $property = $reflection->getProperty('settingManagers');

        $this->assertEquals(
            [
                BooleanSettingManager::class,
                CodeTypeSettingManager::class,
                EncryptedHashSettingManager::class,
                EncryptedSettingManager::class,
                EnumSettingManager::class,
                LanguageSettingManager::class,
                MultiLanguageSettingManager::class,
                MultiListSettingManager::class,
                NumberSettingManager::class,
                VisitCategorySettingManager::class,
                ScalarSettingManager::class,
            ],
            array_map(
                static fn (SettingManagerInterface $settingManager): string => $settingManager::class,
                $property->getValue($compositeManager),
            )
        );
    }
}
