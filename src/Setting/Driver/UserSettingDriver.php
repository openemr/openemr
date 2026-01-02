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

namespace OpenEMR\Setting\Driver;

use OpenEMR\Common\Database\DatabaseManager;
use OpenEMR\Validators\UserValidator;
use Webmozart\Assert\Assert;

/**
 * Usage:
 *   $userSettings = UserSettingManagerFactory::createForAuthorizedUser();
 *   $userSettings = UserSettingManagerFactory::createForUserById($userId);
 *
 *   $gaclProtectDefault = $userSettings->getSettingDefaultValue('gacl_protect');
 *   $gaclProtect = $userSettings->getSettingValue('gacl_protect');
 *
 *   $userSettings->resetSettingValue('gacl_protect');
 *   $userSettings->removeSetting('gacl_protect');
 *
 * @todo Rename user_setting.setting_label > user_setting.setting_key at DB
 */
class UserSettingDriver implements SettingDriverInterface
{
    private readonly DatabaseManager $database;

    public function __construct(
        private readonly int $userId
    ) {
        $this->database = DatabaseManager::getInstance();

        Assert::true(
            UserValidator::getInstance()->isUserIdExists($this->userId),
            sprintf(
                'User with ID %s does not exists',
                $this->userId
            )
        );
    }

    private function createDefaultSetting(string $settingKey, string $settingValue): void
    {
        if (null !== $this->getSettingDefaultValue($settingKey)) {
            return;
        }

        $this->database->insert('user_settings', [
            'setting_user' => 0,
            'setting_label' => $settingKey,
            'setting_value' => $settingValue,
        ]);
    }

    /**
     * Return default setting value.
     */
    public function getSettingDefaultValue(string $settingKey)
    {
        return $this->database->getSingleScalarResultBy('user_settings', 'setting_value', [
            'setting_user' => 0,
            'setting_label' => $settingKey,
        ]);
    }

    public function setSettingValue(string $settingKey, $settingValue): void
    {
        $existingSettingValue = $this->getSettingValue($settingKey);
        if (null === $existingSettingValue) {
            $this->database->insert('user_settings', [
                'setting_user' => $this->userId,
                'setting_label' => sprintf('global:%s', $settingKey),
                'setting_value' => $settingValue,
            ]);
        } elseif ($existingSettingValue !== $settingValue) {
            $this->updateSettingValue($settingKey, $settingValue);
        }

        //$this->createDefaultSetting($settingKey, $settingValue);
    }

    /**
     * Return user setting value for current user.
     * Fallbacks to default setting value if not present for specific user.
     */
    public function getSettingValue(string $settingKey): string|null
    {
        return $this->database->getSingleScalarResult(
            "SELECT `setting_value` FROM `user_settings` WHERE `setting_user` = ? AND `setting_label` IN (?, ?) ORDER BY `setting_user` DESC LIMIT 1",
            [$this->userId, $settingKey, sprintf('global:%s', $settingKey)],
        );
    }

    // @todo Do we need it?
    public function getSettingValueFallback(string $settingKey): array|string|null
    {
        return $this->database->getSingleScalarResult(
            "SELECT `setting_value` FROM `user_settings` WHERE (`setting_user` = ? OR `setting_user` = 0) AND `setting_label` IN(?, ?)  ORDER BY `setting_user` DESC LIMIT 1",
            [$this->userId, $settingKey, sprintf('global:%s', $settingKey)],
        );
    }

    public function setMultiSettingValues(string $settingKey, array $settingValues): void
    {
        $this->database->removeBy('user_settings', [
            'setting_user' => $this->userId,
            'setting_label' => sprintf('global:%s', $settingKey),
        ]);

        foreach ($settingValues as $settingValue) {
            $this->database->insert('user_settings', [
                'setting_user' => $this->userId,
                'setting_label' => sprintf('global:%s', $settingKey),
                'setting_value' => $settingValue,
            ]);
        }
    }

    public function getMultiSettingValue(string $settingKey): array
    {
        return $this->database->getSingleColumnResultBy('user_settings', 'setting_value', [
            'setting_label' => sprintf('global:%s', $settingKey),
        ], [
            'setting_value' => 'ASC',
        ]);
    }

    public function resetSetting(string $settingKey): void
    {
        $this->database->getAffectedRows(
            'DELETE FROM `user_settings` WHERE `setting_user` = ? AND `setting_label` IN (?, ?)',
            [$this->userId, $settingKey, sprintf('global:%s', $settingKey)]
        );
    }

    private function updateSettingValue(string $settingKey, string $settingValue): void
    {
        $this->database->update('user_settings', [
            'setting_value' => $settingValue,
        ], [
            'setting_user' => $this->userId,
            'setting_label' => $settingKey,
        ]);
    }
}
