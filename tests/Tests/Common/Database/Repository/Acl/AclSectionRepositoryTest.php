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

use OpenEMR\Common\Database\Repository\Acl\AclSectionRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('db')]
#[Group('repository')]
#[CoversClass(AclSectionRepository::class)]
#[CoversMethod(AclSectionRepository::class, 'find')]
final class AclSectionRepositoryTest extends TestCase
{
    #[Test]
    public function findTest(): void
    {
        $repository = AclSectionRepository::getInstance();
        $section = $repository->find(5);

        $this->assertArrayHasKey('section_id', $section);
        $this->assertEquals(5, $section['section_id']);
    }
}
