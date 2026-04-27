<?php

/**
 * Configuration service for the GCIP Auth module.
 *
 * Reads and writes GCIP-specific settings from the module_gcip_config table.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Modules\GcipAuth\Config;

use OpenEMR\Common\Database\QueryUtils;

class GcipConfigService
{
    public const TABLE_NAME = 'module_gcip_config';

    public function getIssuer(): string
    {
        return $this->get('gcip_issuer');
    }

    public function getClientId(): string
    {
        return $this->get('gcip_client_id');
    }

    public function getFirebaseProjectId(): string
    {
        return $this->get('gcip_firebase_project_id');
    }

    public function getFirebaseApiKey(): string
    {
        return $this->get('gcip_firebase_api_key');
    }

    public function getFirebaseAuthDomain(): string
    {
        return $this->get('gcip_firebase_auth_domain');
    }

    /**
     * @return list<string>
     */
    public function getAllowedTenantIds(): array
    {
        $value = $this->get('gcip_allowed_tenant_ids');
        if ($value === '') {
            return [];
        }

        return array_values(array_filter(
            array_map(trim(...), explode(',', $value)),
            static fn(string $id): bool => $id !== '',
        ));
    }

    public function get(string $key): string
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT `config_value` FROM `' . self::TABLE_NAME . '` WHERE `config_key` = ?',
            [$key],
        );

        if ($rows === []) {
            return '';
        }

        $value = $rows[0]['config_value'] ?? '';
        return is_string($value) ? $value : '';
    }

    public function set(string $key, string $value): void
    {
        $existing = QueryUtils::fetchRecords(
            'SELECT 1 FROM `' . self::TABLE_NAME . '` WHERE `config_key` = ?',
            [$key],
        );

        if ($existing !== []) {
            QueryUtils::sqlStatementThrowException(
                'UPDATE `' . self::TABLE_NAME . '` SET `config_value` = ? WHERE `config_key` = ?',
                [$value, $key],
            );
        } else {
            QueryUtils::sqlStatementThrowException(
                'INSERT INTO `' . self::TABLE_NAME . '` (`config_key`, `config_value`) VALUES (?, ?)',
                [$key, $value],
            );
        }
    }

    /**
     * @return array<string, string>
     */
    public function getAll(): array
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT `config_key`, `config_value` FROM `' . self::TABLE_NAME . '`',
        );

        $config = [];
        foreach ($rows as $row) {
            if (is_string($row['config_key'] ?? null)) {
                $config[$row['config_key']] = is_string($row['config_value'] ?? null) ? $row['config_value'] : '';
            }
        }

        return $config;
    }
}
