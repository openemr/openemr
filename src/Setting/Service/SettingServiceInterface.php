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

interface SettingServiceInterface
{
    public function getAll(): array;

    public function getBySectionSlug(string $sectionSlug): array;

    public function getOneBySettingKey(string $sectionSlug, string $settingKey): null|bool|int|string|array;

    public function setOneBySettingKey(string $sectionSlug, string $settingKey, null|bool|int|string|array $settingValue): array;

    public function resetBySectionSlug(string $sectionSlug): array;

    public function resetOneBySettingKey(string $sectionSlug, string $settingKey): array;
}
