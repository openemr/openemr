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

    /**
     * @covers ::getEmailQueue
     */
    public function testGetEmailQueueWithNoFilters()
    {
        $result = $this->service->getEmailQueue();
        $this->assertIsArray($result);
    }

    /**
     * @covers ::getEmailQueue
     */
    public function testGetEmailQueueWithSearchFilter()
    {
        $filters = ['search' => 'test@example.com'];
        $result = $this->service->getEmailQueue($filters, 50, 0);
        $this->assertIsArray($result);
    }

    /**
     * @covers ::getEmailQueue
     */
    public function testGetEmailQueueWithStatusFilterSent()
    {
        $filters = ['status' => 'sent'];
        $result = $this->service->getEmailQueue($filters);
        $this->assertIsArray($result);
    }

    /**
     * @covers ::getEmailQueue
     */
    public function testGetEmailQueueWithStatusFilterPending()
    {
        $filters = ['status' => 'pending'];
        $result = $this->service->getEmailQueue($filters);
        $this->assertIsArray($result);
    }

    /**
     * @covers ::getEmailQueue
     */
    public function testGetEmailQueueWithStatusFilterFailed()
    {
        $filters = ['status' => 'failed'];
        $result = $this->service->getEmailQueue($filters);
        $this->assertIsArray($result);
    }

    /**
     * @covers ::getEmailQueue
     */
    public function testGetEmailQueueWithTemplateFilter()
    {
        $filters = ['template_name' => 'appointment_reminder'];
        $result = $this->service->getEmailQueue($filters);
        $this->assertIsArray($result);
    }

    /**
     * @covers ::getEmailQueue
     */
    public function testGetEmailQueueWithDateFilters()
    {
        $filters = [
            'date_from' => '2025-01-01',
            'date_to' => '2025-12-31'
        ];
        $result = $this->service->getEmailQueue($filters);
        $this->assertIsArray($result);
    }

    /**
     * @covers ::getEmailQueue
     */
    public function testGetEmailQueueWithMultipleFilters()
    {
        $filters = [
            'search' => 'test',
            'status' => 'sent',
            'template_name' => 'test_template',
            'date_from' => '2025-01-01',
            'date_to' => '2025-12-31'
        ];
        $result = $this->service->getEmailQueue($filters);
        $this->assertIsArray($result);
    }

    /**
     * @covers ::getEmailQueue
     */
    public function testGetEmailQueueWithPagination()
    {
        $result = $this->service->getEmailQueue([], 10, 5);
        $this->assertIsArray($result);
        $this->assertLessThanOrEqual(10, count($result));
    }

    /**
     * @covers ::getEmailQueueCount
     */
    public function testGetEmailQueueCountWithNoFilters()
    {
        $count = $this->service->getEmailQueueCount();
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    /**
     * @covers ::getEmailQueueCount
     */
    public function testGetEmailQueueCountWithFilters()
    {
        $filters = ['status' => 'sent'];
        $count = $this->service->getEmailQueueCount($filters);
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    /**
     * @covers ::getStatistics
     */
    public function testGetStatistics()
    {
        $stats = $this->service->getStatistics();
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('sent', $stats);
        $this->assertArrayHasKey('pending', $stats);
        $this->assertArrayHasKey('failed', $stats);
        
        $this->assertIsInt($stats['total']);
        $this->assertIsInt($stats['sent']);
        $this->assertIsInt($stats['pending']);
        $this->assertIsInt($stats['failed']);
        
        $this->assertGreaterThanOrEqual(0, $stats['total']);
        $this->assertGreaterThanOrEqual(0, $stats['sent']);
        $this->assertGreaterThanOrEqual(0, $stats['pending']);
        $this->assertGreaterThanOrEqual(0, $stats['failed']);
    }

    /**
     * @covers ::getTemplateNames
     */
    public function testGetTemplateNames()
    {
        $templates = $this->service->getTemplateNames();
        $this->assertIsArray($templates);
        
        foreach ($templates as $template) {
            $this->assertIsString($template);
            $this->assertNotEmpty($template);
        }
    }

    /**
     * @covers ::getEmailById
     */
    public function testGetEmailByIdNotFound()
    {
        $result = $this->service->getEmailById(999999999);
        $this->assertNull($result);
    }

    /**
     * @covers ::getEmailById
     */
    public function testGetEmailByIdValidId()
    {
        // This test requires a valid ID from the database
        // We can't hardcode an ID, so we'll get one from the queue first
        $emails = $this->service->getEmailQueue([], 1, 0);
        
        if (!empty($emails)) {
            $firstEmail = $emails[0];
            $result = $this->service->getEmailById((int)$firstEmail['id']);
            
            if ($result !== null) {
                $this->assertIsArray($result);
                $this->assertArrayHasKey('id', $result);
                $this->assertEquals($firstEmail['id'], $result['id']);
            } else {
                $this->markTestSkipped('No valid email found in database');
            }
        } else {
            $this->markTestSkipped('No emails in queue to test with');
        }
    }
}
