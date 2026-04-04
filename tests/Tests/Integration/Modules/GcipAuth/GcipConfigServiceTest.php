<?php

/**
 * Integration tests for GcipConfigService against a real database.
 *
 * Creates the module_gcip_config table in setUp and drops it in tearDown,
 * since the module may not be installed in the test environment.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Integration\Modules\GcipAuth;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Modules\GcipAuth\Config\GcipConfigService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GcipConfigService::class)]
final class GcipConfigServiceTest extends TestCase
{
    private GcipConfigService $service;

    protected function setUp(): void
    {
        if (getenv('DISABLE_DATABASE') === '1') {
            self::markTestSkipped('Integration test requires database');
        }

        $this->ensureTableExists();
        $this->service = new GcipConfigService();
        $this->cleanTable();
    }

    protected function tearDown(): void
    {
        if (getenv('DISABLE_DATABASE') !== '1') {
            $this->cleanTable();
        }
    }

    private function ensureTableExists(): void
    {
        $rows = QueryUtils::fetchRecords(
            "SELECT 1 FROM `information_schema`.`tables`"
            . " WHERE `table_schema` = DATABASE() AND `table_name` = ?",
            [GcipConfigService::TABLE_NAME],
        );

        if ($rows === []) {
            QueryUtils::sqlStatementThrowException(
                'CREATE TABLE `' . GcipConfigService::TABLE_NAME . '` ('
                . ' `config_key` VARCHAR(100) NOT NULL,'
                . ' `config_value` TEXT,'
                . ' PRIMARY KEY (`config_key`)'
                . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
            );
        }
    }

    private function cleanTable(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `' . GcipConfigService::TABLE_NAME . '`',
        );
    }

    // -----------------------------------------------------------------------
    // get() and set()
    // -----------------------------------------------------------------------

    public function testGetReturnsEmptyStringForMissingKey(): void
    {
        self::assertSame('', $this->service->get('nonexistent_key'));
    }

    public function testSetAndGet(): void
    {
        $this->service->set('test_key', 'test_value');

        self::assertSame('test_value', $this->service->get('test_key'));
    }

    public function testSetUpdatesExistingKey(): void
    {
        $this->service->set('test_key', 'original');
        $this->service->set('test_key', 'updated');

        self::assertSame('updated', $this->service->get('test_key'));
    }

    public function testSetHandlesEmptyValue(): void
    {
        $this->service->set('empty_key', '');

        self::assertSame('', $this->service->get('empty_key'));
    }

    // -----------------------------------------------------------------------
    // getAll()
    // -----------------------------------------------------------------------

    public function testGetAllReturnsEmptyArrayWhenNoConfig(): void
    {
        self::assertSame([], $this->service->getAll());
    }

    public function testGetAllReturnsAllEntries(): void
    {
        $this->service->set('key_a', 'value_a');
        $this->service->set('key_b', 'value_b');
        $this->service->set('key_c', 'value_c');

        $all = $this->service->getAll();

        self::assertCount(3, $all);
        self::assertSame('value_a', $all['key_a']);
        self::assertSame('value_b', $all['key_b']);
        self::assertSame('value_c', $all['key_c']);
    }

    // -----------------------------------------------------------------------
    // Typed getters
    // -----------------------------------------------------------------------

    public function testGetIssuerReturnsConfiguredValue(): void
    {
        $this->service->set('gcip_issuer', 'https://securetoken.google.com/my-project');

        self::assertSame('https://securetoken.google.com/my-project', $this->service->getIssuer());
    }

    public function testGetIssuerReturnsEmptyWhenNotConfigured(): void
    {
        self::assertSame('', $this->service->getIssuer());
    }

    public function testGetClientId(): void
    {
        $this->service->set('gcip_client_id', 'my-client-id');

        self::assertSame('my-client-id', $this->service->getClientId());
    }

    public function testGetFirebaseProjectId(): void
    {
        $this->service->set('gcip_firebase_project_id', 'my-firebase-project');

        self::assertSame('my-firebase-project', $this->service->getFirebaseProjectId());
    }

    public function testGetFirebaseApiKey(): void
    {
        $this->service->set('gcip_firebase_api_key', 'AIzaSy...');

        self::assertSame('AIzaSy...', $this->service->getFirebaseApiKey());
    }

    public function testGetFirebaseAuthDomain(): void
    {
        $this->service->set('gcip_firebase_auth_domain', 'my-project.firebaseapp.com');

        self::assertSame('my-project.firebaseapp.com', $this->service->getFirebaseAuthDomain());
    }

    // -----------------------------------------------------------------------
    // getAllowedTenantIds()
    // -----------------------------------------------------------------------

    public function testGetAllowedTenantIdsReturnsEmptyWhenNotConfigured(): void
    {
        self::assertSame([], $this->service->getAllowedTenantIds());
    }

    public function testGetAllowedTenantIdsReturnsEmptyForEmptyString(): void
    {
        $this->service->set('gcip_allowed_tenant_ids', '');

        self::assertSame([], $this->service->getAllowedTenantIds());
    }

    public function testGetAllowedTenantIdsParsesCommaSeparatedList(): void
    {
        $this->service->set('gcip_allowed_tenant_ids', 'tenant-a,tenant-b,tenant-c');

        self::assertSame(['tenant-a', 'tenant-b', 'tenant-c'], $this->service->getAllowedTenantIds());
    }

    public function testGetAllowedTenantIdsTrimsWhitespace(): void
    {
        $this->service->set('gcip_allowed_tenant_ids', ' tenant-a , tenant-b , tenant-c ');

        self::assertSame(['tenant-a', 'tenant-b', 'tenant-c'], $this->service->getAllowedTenantIds());
    }

    public function testGetAllowedTenantIdsFiltersEmptyEntries(): void
    {
        $this->service->set('gcip_allowed_tenant_ids', 'tenant-a,,tenant-b,,,tenant-c');

        self::assertSame(['tenant-a', 'tenant-b', 'tenant-c'], $this->service->getAllowedTenantIds());
    }

    public function testGetAllowedTenantIdsSingleTenant(): void
    {
        $this->service->set('gcip_allowed_tenant_ids', 'only-tenant');

        self::assertSame(['only-tenant'], $this->service->getAllowedTenantIds());
    }
}
