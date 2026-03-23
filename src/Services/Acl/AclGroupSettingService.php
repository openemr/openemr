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

use OpenEMR\Common\Database\Repository\Acl\AclGroupSettingRepository;
use OpenEMR\Core\Traits\SingletonTrait;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

/**
 * @phpstan-import-type TAclGroupSetting from AclGroupSettingRepository
 */
class AclGroupSettingService
{
    use SingletonTrait;

    protected static function createInstance(): static
    {
        return new self(
            AclSectionService::getInstance(),
            AclGroupSettingRepository::getInstance(),
        );
    }

    public function __construct(
        private readonly AclSectionService $aclSectionService,
        private readonly AclGroupSettingRepository $aclGroupSettingRepository,
    ) {
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
