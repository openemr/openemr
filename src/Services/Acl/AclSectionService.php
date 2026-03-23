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

namespace OpenEMR\Services\Acl;

use OpenEMR\Common\Database\Repository\Acl\AclSectionRepository;
use OpenEMR\Core\Traits\SingletonTrait;

class AclSectionService
{
    use SingletonTrait;

    protected static function createInstance(): static
    {
        return new self(
            AclSectionRepository::getInstance(),
        );
    }

    public function __construct(
        private readonly AclSectionRepository $aclSectionRepository,
    ) {
    }

    public function getAll(): array
    {
        return $this->aclSectionRepository->findAll();
    }

    public function isIdValid(int $id): bool
    {
        return null !== $this->aclSectionRepository->find($id);
    }
}
