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

use OpenEMR\Common\Database\Repository\Acl\AclUserSettingRepository;
use OpenEMR\Common\Database\Repository\RepositoryFactory;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

/**
 * @phpstan-import-type TAclUserSetting from AclUserSettingRepository
 */
class AclUserSettingService
{
    private readonly AclSectionService $aclSectionService;

    private readonly AclUserSettingRepository $aclUserSettingRepository;

    public function __construct()
    {
        $this->aclSectionService = new AclSectionService();
        $this->aclUserSettingRepository = RepositoryFactory::createRepository(AclUserSettingRepository::class);
    }

    /**
     * Reset specific Section Settings to their default values and return them
     *
     * @phpstan-return array<TAclUserSetting>
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

        return $this->aclUserSettingRepository->findBySectionId($sectionId);
    }
}
