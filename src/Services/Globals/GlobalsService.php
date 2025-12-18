<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2019 Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Globals;

use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;
use Generator;

/**
 * Service for manipulating data structure of "globals" table
 * which affects the Admin > Globals screen
 *
 * @phpstan-import-type TSettingMetadata from GlobalSetting
 * @phpstan-type TMetadata = array<string, array<string, TSettingMetadata>>
 */
class GlobalsService
{
    /** @phpstan-var TMetadata */
    private array $globalsMetadata = [];

    /**
     * @phpstan-param TMetadata $globalsMetadata The entire globals structure
     * @param string[] $userSpecificGlobals User-specific globals
     * @param string[] $userSpecificTabs User specific tabs
     */
    public function __construct(
        array $globalsMetadata = [],
        private array $userSpecificGlobals = [],
        private array $userSpecificTabs = [],
    ) {
        // Migrating arrays to enum data type
        foreach ($globalsMetadata as $sectionName => $sectionMetadatas) {
            if (!$this->isSectionExists($sectionName)) {
                $this->createSection($sectionName);
            }

            foreach ($sectionMetadatas as $settingKey => $settingMetadata) {
                if (is_array($settingMetadata[GlobalSetting::INDEX_DATA_TYPE])) {
                    $settingMetadata[GlobalSetting::INDEX_FIELD_OPTIONS][GlobalSetting::DATA_TYPE_OPTION_ENUM_VALUES] = $settingMetadata[GlobalSetting::INDEX_DATA_TYPE];
                    $settingMetadata[GlobalSetting::INDEX_DATA_TYPE] = GlobalSetting::DATA_TYPE_ENUM;
                }

                $this->globalsMetadata[$sectionName][$settingKey] = $settingMetadata;
            }
        }
    }

    /**
     * Save the globals data structure, does not save globals data values
     */
    public function save(): void
    {
        global $GLOBALS_METADATA, $USER_SPECIFIC_GLOBALS, $USER_SPECIFIC_TABS;
        $GLOBALS_METADATA = $this->globalsMetadata;
        $USER_SPECIFIC_GLOBALS = $this->userSpecificGlobals;
        $USER_SPECIFIC_TABS = $this->userSpecificTabs;
    }

    public function isSectionExists(string $sectionName): bool
    {
        return array_key_exists($sectionName, $this->globalsMetadata);
    }

    /**
     * Create a section, or TAB in the Admin > Globals screen
     *
     * @param string $sectionName Section name
     * @param null|string $beforeSectionName Section name we want to insert this section before (null for at the end)
     *
     * @throws InvalidArgumentException
     */
    public function createSection(string $sectionName, ?string $beforeSectionName = null): void
    {
        Assert::keyNotExists($this->globalsMetadata, $sectionName, sprintf(
            'Section %s already exists',
            $sectionName,
        ));

        Assert::true(null === $beforeSectionName || isset($this->globalsMetadata[$beforeSectionName]), sprintf(
            'Section %s does not exist',
            $beforeSectionName,
        ));

        if (null === $beforeSectionName) {
            $this->globalsMetadata[$sectionName] = [];
            return;
        }

        $beforeSectionIndex = array_search($beforeSectionName, array_keys($this->globalsMetadata));
        $this->globalsMetadata =
            array_slice($this->globalsMetadata, 0, $beforeSectionIndex + 1, true)
            + [$sectionName => []]
            + array_slice($this->globalsMetadata, $beforeSectionIndex + 1, count($this->globalsMetadata) - 1, true)
        ;
    }

    /**
     * Creates a section in the User Settings global pane.
     * If this section doesn't exist in the global panes it will
     * add it at the end of the list.
     *
     * @param string $sectionName The Section name (name of the tab)
     */
    public function addUserSpecificTab(string $sectionName): void
    {
        if (!isset($this->globalsMetadata[$sectionName])) {
            $this->createSection($sectionName);
        }

        $this->userSpecificTabs[] = $sectionName;
    }

    /**
     * Append a global setting to the end of a section
     *
     * @param string $sectionName Section name
     * @param string $settingKey Global metadata key, must be unique in structure
     */
    public function appendToSection(string $sectionName, string $settingKey, GlobalSetting $setting): void
    {
        $this->globalsMetadata[$sectionName][$settingKey] = $setting->format();

        if ($setting->isUserSetting()) {
            $this->userSpecificGlobals[] = $settingKey;
        }
    }

    public function getGlobalsMetadata(): array
    {
        return $this->globalsMetadata;
    }

    /** @deprecated Use getUserSpecificSettings */
    public function getUserSpecificGlobals(): array
    {
        return $this->userSpecificGlobals;
    }

    public function getUserSpecificSettings(): array
    {
        return $this->userSpecificGlobals;
    }

    /** @deprecated Use getUserSpecificSections instead */
    public function getUserSpecificTabs(): array
    {
        return $this->userSpecificTabs;
    }

    public function getUserSpecificSections(): array
    {
        return $this->userSpecificTabs;
    }

    public function getAllSections(): array
    {
        return array_keys($this->globalsMetadata);
    }

    public function getSettingKeysBySectionName(string $sectionName): array
    {
        return array_keys($this->globalsMetadata[$sectionName]);
    }

//    public function isSectionUserSpecific(string $sectionName): bool
//    {
//        return in_array($sectionName, $this->getUserSpecificSections(), true);
//    }

    public function isSettingExists(string $sectionName, string $settingKey): bool
    {
        return $this->isSectionExists($sectionName)
            && array_key_exists($settingKey, $this->globalsMetadata[$sectionName])
            ;
    }

    public function isSettingUserSpecific(string $settingKey): bool
    {
        return in_array($settingKey, $this->getUserSpecificGlobals(), true);
    }

    public function getSectionNameBySettingKey(string $settingKey): ?string
    {
        foreach ($this->globalsMetadata as $sectionName => $sectionMetadata) {
            if (isset($sectionMetadata[$settingKey])) {
                return $sectionName;
            }
        }

        return null;
    }

    /**
     * Returns metadata of all Section's Settings by Section Name
     *
     * @phpstan-return array<string, TSettingMetadata>
     * @throws InvalidArgumentException
     */
    public function getMetadataBySectionName(string $sectionName): array
    {
        Assert::true($this->isSectionExists($sectionName), sprintf(
            'Section %s does not exist',
            $sectionName,
        ));

        return $this->globalsMetadata[$sectionName];
    }

    /**
     * Returns single Setting Metadata by Setting Key
     *
     * @phpstan-return TSettingMetadata
     * @throws InvalidArgumentException
     */
    public function getMetadataBySettingKey(string $settingKey): array
    {
        foreach ($this->globalsMetadata as $sectionMetadata) {
            if (isset($sectionMetadata[$settingKey])) {
                return $sectionMetadata[$settingKey];
            }
        }

        throw new InvalidArgumentException(sprintf(
            'Setting "%s" does not exist',
            $settingKey,
        ));
    }

//    /**
//     * @phpstan-return TSettingMetadata|null
//     * @throws InvalidArgumentException
//     */
//    public function getSettingMetadataBySectionAndKey(string $sectionName, string $settingKey): ?array
//    {
//        Assert::true($this->isSectionExists($sectionName), sprintf(
//            'Section %s does not exists',
//            $sectionName,
//        ));
//
//        // @todo Add Did you mean ... (guess correct section or key name)
//        Assert::true($this->isSettingExists($sectionName, $settingKey), sprintf(
//            'Setting %s does not exists at section %s. Possible section settings are: %s',
//            $settingKey,
//            $sectionName,
//            implode(', ', $this->getSettingKeysBySectionName($sectionName))
//        ));
//
//        return $this->globalsMetadata[$sectionName][$settingKey];
//    }

    public function getSettingName(string $settingKey): string
    {
        return $this->getMetadataBySettingKey($settingKey)[GlobalSetting::INDEX_NAME];
    }

    public function getSettingDataType(string $settingKey): string
    {
        return $this->getMetadataBySettingKey($settingKey)[GlobalSetting::INDEX_DATA_TYPE];
    }

    public function getSettingDefaultValue(string $settingKey): null|bool|int|string
    {
        return $this->getMetadataBySettingKey($settingKey)[GlobalSetting::INDEX_DEFAULT];
    }

    public function getSettingDescription(string $settingKey): ?string
    {
        return $this->getMetadataBySettingKey($settingKey)[GlobalSetting::INDEX_DESCRIPTION];
    }

    public function getSettingFieldOption(string $settingKey, string $fieldOption): string|array|callable
    {
        $metadata = $this->getMetadataBySettingKey($settingKey);

        Assert::oneOf($fieldOption, GlobalSetting::ALL_DATA_TYPE_OPTIONS, sprintf(
            'Unknown field option "%s". Expected one of: %s',
            $fieldOption,
            implode(', ', GlobalSetting::ALL_DATA_TYPE_OPTIONS)
        ));

        Assert::true(
            isset($metadata[GlobalSetting::INDEX_FIELD_OPTIONS][$fieldOption]),
            sprintf(
                'Option "%s" is not set for setting "%s"',
                $fieldOption,
                $settingKey,
            ),
        );

        return $metadata[GlobalSetting::INDEX_FIELD_OPTIONS][$fieldOption];
    }

    public function getSettingsByDataType(string $dataType): Generator
    {
        Assert::oneOf($dataType, GlobalSetting::ALL_DATA_TYPES);

        foreach ($this->globalsMetadata as $sectionMetadata) {
            foreach ($sectionMetadata as $settingKey => $settingMetadata) {
                if ($settingMetadata[GlobalSetting::INDEX_DATA_TYPE] === $dataType) {
                    yield $settingKey;
                }
            }
        }
    }
}
