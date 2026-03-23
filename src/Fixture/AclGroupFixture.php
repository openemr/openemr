<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Fixture;

use OpenEMR\Core\Traits\SingletonTrait;
use OpenEMR\Services\Acl\AclGroupService;

/**
 * @phpstan-import-type TAclGroup from AclGroupService
 */
class AclGroupFixture extends AbstractFixture implements AccessibleFixtureInterface, RemovableFixtureInterface
{
    use SingletonTrait;

    /** @use AccessibleFixtureTrait<TAclGroup> */
    use AccessibleFixtureTrait;

    /** @use RemovableFixtureTrait<TAclGroup> */
    use RemovableFixtureTrait;

    protected static function createInstance(): static
    {
        return new self(
            AclGroupService::getInstance(),
            [
                'additional_acl_groups.json',
            ],
        );
    }

    public function __construct(
        private readonly AclGroupService $groupService,
        array $filenames,
    ) {
        parent::__construct($filenames);
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
