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

use OpenEMR\Common\Database\Repository\Acl\AclGroupSettingRepository;
use OpenEMR\Common\Database\Repository\RepositoryFactory;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

/**
 * @phpstan-import-type TAclGroupSetting from AclGroupSettingRepository
 */
class AclGroupSettingService
{
    private readonly AclSectionService $aclSectionService;

    private readonly AclGroupSettingRepository $aclGroupSettingRepository;

    public function __construct()
    {
        $this->aclSectionService = new AclSectionService();
        $this->aclGroupSettingRepository = RepositoryFactory::createRepository(AclGroupSettingRepository::class);
    }

    /**
     * Reset specific Section Settings to their default values and return them
     *
     * @phpstan-return array<TAclGroupSetting>
     * @throws InvalidArgumentException
     */
    public function resetBySectionId(int $sectionId): array
    {
        Assert::true(
            $this->aclSectionService->isIdValid($sectionId),
            sprintf(
                'Unknown Section ID %d',
                $sectionId
            )
        );

        // @todo Implement

        return $this->aclGroupSettingRepository->findBySectionId($sectionId);
    }
}
