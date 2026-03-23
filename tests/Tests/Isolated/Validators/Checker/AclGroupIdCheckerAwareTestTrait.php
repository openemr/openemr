<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Validators\Checker;

use OpenEMR\Validators\Checker\AclGroupIdChecker;
use PHPUnit\Framework\MockObject\MockObject;

trait AclGroupIdCheckerAwareTestTrait
{
    private const GROUP_ID_EXISTING = 999;

    /**
     * @return MockObject&AclGroupIdChecker
     */
    private function getAclGroupIdCheckerMock(): MockObject
    {
        $checker = $this->createMock(AclGroupIdChecker::class);
        $checker->method('isAclGroupIdExists')->willReturnCallback(
            fn (string|int $id): bool => self::GROUP_ID_EXISTING === $id,
        );

        return $checker;
    }
}
