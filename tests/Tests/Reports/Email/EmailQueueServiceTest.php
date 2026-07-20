<?php

/**
 * EmailQueueServiceTest.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Generated for PoppyBilling
 * @copyright Copyright (c) 2025
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Reports\Email;

use OpenEMR\Reports\Email\EmailQueueService;
use PHPUnit\Framework\TestCase;

/**
 * EmailQueueService Tests
 * @coversDefaultClass OpenEMR\Reports\Email\EmailQueueService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Generated for PoppyBilling
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class EmailQueueServiceTest extends TestCase
{
    /**
     * @var EmailQueueService
     */
    private $service;

    protected function setUp(): void
    {
        $this->service = new EmailQueueService();
    }

    public function testGetEmailQueueWithNoFilters(): void
    {
        $result = $this->service->getEmailQueue();
        self::assertIsArray($result);
    }

    public function testGetEmailQueueWithSearchFilter(): void
    {
        $filters = ['search' => 'test@example.com'];
        $result = $this->service->getEmailQueue($filters, 50, 0);
        self::assertIsArray($result);
    }

    public function testGetEmailQueueWithStatusFilterSent(): void
    {
        $filters = ['status' => 'sent'];
        $result = $this->service->getEmailQueue($filters);
        self::assertIsArray($result);
    }

    public function testGetEmailQueueWithStatusFilterPending(): void
    {
        $filters = ['status' => 'pending'];
        $result = $this->service->getEmailQueue($filters);
        self::assertIsArray($result);
    }

    public function testGetEmailQueueWithStatusFilterFailed(): void
    {
        $filters = ['status' => 'failed'];
        $result = $this->service->getEmailQueue($filters);
        self::assertIsArray($result);
    }

    public function testGetEmailQueueWithTemplateFilter(): void
    {
        $filters = ['template_name' => 'appointment_reminder'];
        $result = $this->service->getEmailQueue($filters);
        self::assertIsArray($result);
    }

    public function testGetEmailQueueWithDateFilters(): void
    {
        $filters = [
            'date_from' => '2025-01-01',
            'date_to' => '2025-12-31'
        ];
        $result = $this->service->getEmailQueue($filters);
        self::assertIsArray($result);
    }

    public function testGetEmailQueueWithMultipleFilters(): void
    {
        $filters = [
            'search' => 'test',
            'status' => 'sent',
            'template_name' => 'test_template',
            'date_from' => '2025-01-01',
            'date_to' => '2025-12-31'
        ];
        $result = $this->service->getEmailQueue($filters);
        self::assertIsArray($result);
    }

    public function testGetEmailQueueWithPagination(): void
    {
        $result = $this->service->getEmailQueue([], 10, 5);
        self::assertIsArray($result);
        self::assertLessThanOrEqual(10, count($result));
    }

    public function testGetEmailQueueCountWithNoFilters(): void
    {
        $count = $this->service->getEmailQueueCount();
        self::assertIsInt($count);
        self::assertGreaterThanOrEqual(0, $count);
    }

    public function testGetEmailQueueCountWithFilters(): void
    {
        $filters = ['status' => 'sent'];
        $count = $this->service->getEmailQueueCount($filters);
        self::assertIsInt($count);
        self::assertGreaterThanOrEqual(0, $count);
    }

    public function testGetStatistics(): void
    {
        $stats = $this->service->getStatistics();
        self::assertIsArray($stats);
        self::assertArrayHasKey('total', $stats);
        self::assertArrayHasKey('sent', $stats);
        self::assertArrayHasKey('pending', $stats);
        self::assertArrayHasKey('failed', $stats);

        self::assertIsInt($stats['total']);
        self::assertIsInt($stats['sent']);
        self::assertIsInt($stats['pending']);
        self::assertIsInt($stats['failed']);

        self::assertGreaterThanOrEqual(0, $stats['total']);
        self::assertGreaterThanOrEqual(0, $stats['sent']);
        self::assertGreaterThanOrEqual(0, $stats['pending']);
        self::assertGreaterThanOrEqual(0, $stats['failed']);
    }

    public function testGetTemplateNames(): void
    {
        $templates = $this->service->getTemplateNames();
        self::assertIsArray($templates);

        foreach ($templates as $template) {
            self::assertIsString($template);
            self::assertNotEmpty($template);
        }
    }

    public function testGetEmailByIdNotFound(): void
    {
        $result = $this->service->getEmailById(999999999);
        self::assertNull($result);
    }

    public function testGetEmailByIdValidId(): void
    {
        // This test requires a valid ID from the database
        // We can't hardcode an ID, so we'll get one from the queue first
        $emails = $this->service->getEmailQueue([], 1, 0);

        if ($emails !== []) {
            $firstEmail = $emails[0];
            $result = $this->service->getEmailById((int)$firstEmail['id']);

            if ($result !== null) {
                self::assertIsArray($result);
                self::assertArrayHasKey('id', $result);
                self::assertSame($firstEmail['id'], $result['id']);
            } else {
                self::markTestSkipped('No valid email found in database');
            }
        } else {
            self::markTestSkipped('No emails in queue to test with');
        }
    }
}
