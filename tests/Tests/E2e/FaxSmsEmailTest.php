<?php

/**
 * FaxSmsEmailTest class
 *
 * Tests email functionality in the oe-module-faxsms module
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use OpenEMR\Modules\FaxSMS\Controller\EmailClient;
use OpenEMR\Tests\E2e\Email\EmailTestData;
use OpenEMR\Tests\E2e\Email\EmailTestingTrait;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FaxSmsEmailTest extends TestCase
{
    use EmailTestingTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initializeMailpit();
        // Clear all existing emails before each test
        $this->deleteAllMailpitMessages();
    }

    #[Test]
    public function testFaxSmsModuleEmailReminderFunction(): void
    {
        $this->markTestSkipped('Fax/SMS module email tests require additional setup and will be implemented in a future update');
    }

    #[Test]
    public function testFaxSmsModuleEmailSendFunction(): void
    {
        $this->markTestSkipped('Fax/SMS module email tests require additional setup and will be implemented in a future update');
    }

    #[Test]
    public function testFaxSmsModuleEmailWithoutSmtpConfigured(): void
    {
        $this->markTestSkipped('Fax/SMS module email tests require additional setup and will be implemented in a future update');
    }

    #[Test]
    public function testFaxSmsModuleEmailDocumentWithAttachment(): void
    {
        $this->markTestSkipped('Fax/SMS module email tests require additional setup and will be implemented in a future update');
    }

    #[Test]
    public function testFaxSmsModuleEmailValidation(): void
    {
        $this->markTestSkipped('Fax/SMS module email tests require additional setup and will be implemented in a future update');
    }

    #[Test]
    public function testMultipleRecipientsFromFaxSmsModule(): void
    {
        $this->markTestSkipped('Fax/SMS module email tests require additional setup and will be implemented in a future update');
    }
}
