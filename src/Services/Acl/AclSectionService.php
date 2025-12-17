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

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\Repository\Acl\AclSectionRepository;
use OpenEMR\Common\Database\Repository\RepositoryFactory;
use OpenEMR\Gacl\GaclApi;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

class AclSectionService
{
    private readonly AclSectionRepository $aclSectionRepository;

    public function __construct()
    {
        $this->aclSectionRepository = RepositoryFactory::createRepository(AclSectionRepository::class);
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
