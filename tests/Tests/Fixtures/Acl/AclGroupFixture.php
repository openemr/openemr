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

namespace OpenEMR\Tests\Fixtures\Acl;

use OpenEMR\Services\Acl\AclGroupService;
use OpenEMR\Tests\Fixtures\AbstractFixture;

class AclGroupFixture extends AbstractFixture
{
    private readonly AclGroupService $groupService;

    public function __construct() {
        $this->groupService = new AclGroupService();
    }

    public function load(): void
    {
        $this->loadFromFile(sprintf('%s/../data/acl_groups.json', __DIR__));
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
