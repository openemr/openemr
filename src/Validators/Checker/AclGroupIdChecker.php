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

namespace OpenEMR\Validators\Checker;

use OpenEMR\Core\Traits\SingletonTrait;
use OpenEMR\Gacl\GaclApi;

class AclGroupIdChecker
{
    use SingletonTrait;

    protected static function createInstance(): static
    {
        return new self(
            new GaclApi(),
        );
    }

    public function __construct(
        private readonly GaclApi $acl,
    ) {
    }

    public function isAclGroupIdExists(int $groupId): bool
    {
        return false !== $this->acl->get_group_data($groupId);
    }
}
