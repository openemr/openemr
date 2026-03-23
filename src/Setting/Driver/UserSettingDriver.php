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
use OpenEMR\Common\Database\Repository\User\UserRepository;
use OpenEMR\Core\Traits\KeyAwareSingletonTrait;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

/**
 * Usage:
 *   $userSettings = UserSettingDriver::getInstanceById($userId); // get instance by User ID
 *   $userSettings = UserSettingDriver::getInstanceByUuid($uuid); // get instance by UUID
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
    /** @use KeyAwareSingletonTrait<int> */
    use KeyAwareSingletonTrait;

    public static function getInstanceById(int $userId): self
    {
        return self::getInstanceByKey($userId);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function getInstanceByUuid(string $uuid): UserSettingDriver
    {
        Assert::true(Uuid::isValid($uuid), sprintf(
            'UUID %s is not valid',
            $uuid,
        ));

        return self::getInstanceById(
            UserRepository::getInstance()->findOneByUuid($uuid)['id'],
        );
    }

    protected static function createInstance($key): static
    {
        Assert::numeric($key, sprintf(
            'Can not instantiate %s - User ID should be numeric',
            self::class,
        ));

        $userId = (int) $key;

        Assert::greaterThan($userId, 0, sprintf(
            'Can not instantiate %s - User ID should be positive, got: %d',
            self::class,
            $userId,
        ));

        return new self(
            GlobalSettingDriver::getInstance(),
            DatabaseManager::getInstance(),
            $userId,
        );
    }

    public function __construct(
        private readonly GlobalSettingDriver $globalSettingDriver,
        private readonly DatabaseManager $database,
        private readonly int $userId,
    ) {
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
        ]) ?? $this->globalSettingDriver->getSettingDefaultValue($settingKey);
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
            $this->updateSettingValue(
                sprintf('global:%s', $settingKey),
                $settingValue,
            );
        }

        //$this->createDefaultSetting($settingKey, $settingValue);
    }

    public function getSettingValue(string $settingKey): string|null
    {
        return $this->database->getSingleScalarResult(
            "SELECT `setting_value` FROM `user_settings` WHERE `setting_user` = ? AND `setting_label` IN (?, ?) ORDER BY `setting_user` DESC LIMIT 1",
            [$this->userId, $settingKey, sprintf('global:%s', $settingKey)],
        );
    }

    /**
     * Return user setting value for current user.
     * Fallbacks to default setting value if not present for specific user.
     */
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
