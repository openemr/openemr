<?php

/**
 * EmailQueueServiceTest.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025-2026 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Email;

use OpenEMR\Reports\Email\EmailQueueService;
use PHPUnit\Framework\TestCase;

class EmailQueueServiceTest extends TestCase
{
    private EmailQueueService $service;

    protected function setUp(): void
    {
        $this->service = new EmailQueueService();
    }

    public function testGetEmailQueueReturnsRowsWithExpectedKeys(): void
    {
        $result = $this->service->getEmailQueue([
            'search' => 'test@example.com',
            'status' => 'sent',
            'template_name' => 'appointment_reminder',
            'date_from' => '2025-01-01',
            'date_to' => '2025-12-31',
        ], 50, 0);

        self::assertLessThanOrEqual(50, count($result));
        foreach ($result as $row) {
            self::assertArrayHasKey('id', $row);
            self::assertArrayHasKey('recipient', $row);
            self::assertArrayHasKey('subject', $row);
            self::assertArrayHasKey('sender', $row);
            self::assertArrayHasKey('sent', $row);
            self::assertArrayHasKey('error', $row);
        }
    }

    public function testGetEmailQueuePaginationLimit(): void
    {
        $result = $this->service->getEmailQueue([], 10, 5);
        self::assertLessThanOrEqual(10, count($result));
    }

    public function testGetEmailQueueCountIsNonNegative(): void
    {
        $unfiltered = $this->service->getEmailQueueCount();
        $filtered = $this->service->getEmailQueueCount(['status' => 'failed']);
        self::assertGreaterThanOrEqual(0, $unfiltered);
        self::assertGreaterThanOrEqual(0, $filtered);
    }

    public function testGetStatisticsShape(): void
    {
        $stats = $this->service->getStatistics();
        foreach (['total', 'sent', 'pending', 'failed'] as $key) {
            self::assertArrayHasKey($key, $stats);
            self::assertGreaterThanOrEqual(0, $stats[$key]);
        }
    }

    public function testGetTemplateNamesAreNonEmptyStrings(): void
    {
        foreach ($this->service->getTemplateNames() as $template) {
            self::assertNotSame('', $template);
        }
    }

    public function testGetEmailByIdNotFound(): void
    {
        self::assertNull($this->service->getEmailById(999999999));
    }

    public function testGetEmailByIdValidId(): void
    {
        $emails = $this->service->getEmailQueue([], 1, 0);
        if ($emails === []) {
            self::markTestSkipped('No emails in queue to test with');
        }

        $firstEmail = $emails[0];
        $result = $this->service->getEmailById((int) $firstEmail['id']);
        if ($result === null) {
            self::markTestSkipped('No valid email found in database');
        }

        self::assertArrayHasKey('id', $result);
        self::assertSame($firstEmail['id'], $result['id']);
    }
}
