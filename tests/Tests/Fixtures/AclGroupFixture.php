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

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Common\Auth\AuthHash;
use OpenEMR\Common\Auth\Password\RandomPasswordGenerator;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\Acl\AclGroupService;

class AclGroupFixture extends AbstractFixture
{
    private AclGroupService $groupService;

    public function __construct() {
        $this->groupService = new AclGroupService();
    }

    public function load(): void
    {
        $this->loadFromFile(sprintf('%s/acl_groups.json', __DIR__));
    }

    protected function loadRecord(array $record): array
    {
        return $this->groupService->insert($record);
    }

    protected function removeRecord(array $record): void
    {
        $this->groupService->deleteById($record['id']);
    }
}
