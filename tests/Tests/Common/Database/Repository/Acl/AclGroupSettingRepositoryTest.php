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

namespace OpenEMR\Tests\Common\Database\Repository\Acl;

use OpenEMR\Common\Database\Repository\Acl\AclGroupSettingRepository;
use OpenEMR\Common\Database\Repository\RepositoryFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('db')]
#[Group('repository')]
#[CoversClass(AclGroupSettingRepository::class)]
#[CoversMethod(AclGroupSettingRepository::class, 'findBySectionId')]
final class AclGroupSettingRepositoryTest extends TestCase
{
    #[Test]
    public function findByTest(): void
    {
        $repository = RepositoryFactory::createRepository(AclGroupSettingRepository::class);
        $settings = $repository->findBySectionId(5);

        $this->assertCount(1, $settings);
        foreach ($settings as $setting) {
            $this->assertArrayHasKey('section_id', $setting);
            $this->assertEquals(5, $setting['section_id']);
        }
    }
}
