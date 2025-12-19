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

namespace OpenEMR\Setting\Service;

use OpenEMR\Services\Globals\GlobalsService;
use OpenEMR\Setting\Manager\CompositeSettingManager;
use Webmozart\Assert\InvalidArgumentException;

abstract class AbstractSettingService implements SettingServiceInterface
{
    public function __construct(
        protected readonly GlobalsService $globalsService,
        protected readonly SettingSectionServiceInterface $settingSectionService,
        protected readonly CompositeSettingManager $settingManager
    ) {
    }

    public function getAll(): array
    {
        return array_merge(...array_map(
            fn (string $sectionSlug): array => $this->getBySectionSlug(
                $sectionSlug
            ),
            $this->settingSectionService->getSectionSlugs()
        ));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getBySectionSlug(string $sectionSlug): array
    {
        $sectionName = $this->settingSectionService->deslugify($sectionSlug);

        return array_map(
            fn (string $settingKey): array => $this->getOneBySettingKey($sectionSlug, $settingKey),
            $this->getSettingKeysBySectionName($sectionName),
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getOneBySettingKey(string $sectionSlug, string $settingKey): null|bool|int|string|array
    {
        $this->checkSectionHasSetting($sectionSlug, $settingKey);

        return iterator_to_array(
            $this->settingManager->normalizeSetting($settingKey)
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setOneBySettingKey(string $sectionSlug, string $settingKey, null|bool|int|string|array $settingValue): array
    {
        $this->checkSectionHasSetting($sectionSlug, $settingKey);

        $this->settingManager->setSettingValue($settingKey, $settingValue);

        return iterator_to_array(
            $this->settingManager->normalizeSetting($settingKey)
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function resetBySectionSlug(string $sectionSlug): array
    {
        $sectionName = $this->settingSectionService->deslugify($sectionSlug);

        return array_map(
            fn (string $settingKey): array => $this->resetOneBySettingKey($sectionSlug, $settingKey),
            $this->getSettingKeysBySectionName($sectionName),
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function resetOneBySettingKey(string $sectionSlug, string $settingKey): array
    {
        $this->checkSectionHasSetting($sectionSlug, $settingKey);

        $this->settingManager->resetSetting($settingKey);

        return iterator_to_array(
            $this->settingManager->normalizeSetting($settingKey)
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function checkSectionHasSetting(string $sectionSlug, string $settingKey): void
    {
        $sectionName = $this->settingSectionService->deslugify($sectionSlug);

        // @todo User specific service should throw exception when global passed

        if ($this->globalsService->isSettingExists($sectionName, $settingKey)) {
            return;
        }

        $guessedSectionName = $this->globalsService->getSectionNameBySettingKey($settingKey);
        if (null !== $guessedSectionName) {
            throw new InvalidArgumentException(sprintf(
                'Setting "%s" does not exist under "%s" section. Did you mean "%s" section?',
                $settingKey,
                $sectionSlug,
                $this->settingSectionService->slugify($guessedSectionName),
            ));
        }

        throw new InvalidArgumentException(sprintf(
            'Setting "%s" does not exists under "%s" section. Possible section settings are: %s.',
            $settingKey,
            $sectionSlug,
            implode(', ', array_map(
                static fn (string $sectionSlug): string => sprintf('"%s"', $sectionSlug),
                $this->getSettingKeysBySectionName($sectionName),
            ))
        ));
    }

    abstract protected function getSettingKeysBySectionName(string $sectionName): array;
}
