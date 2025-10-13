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

namespace OpenEMR\Services\Acl;

use OpenEMR\Gacl\GaclApi;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

class AclGroupService
{
    private GaclApi $acl;

    public function __construct()
    {
        $this->acl = new GaclApi();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function addUserToGroupById(int $userId, string $username, int $groupId): void
    {
        $this->acl->add_object('users', $userId, $username, 0, 0, 'ARO');

        Assert::true(
            $this->acl->add_group_object(
                $groupId,
                'users',
                $username,
            ),
        );
    }
}
