<?php

/**
 * E2E tests for EmailTestService
 *
 * Exercises all three email code paths (direct, queue, queue templated) via
 * EmailTestService, then verifies delivery through Mailpit.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use MyMailer;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\Email\EmailSendMethod;
use OpenEMR\Services\Email\EmailTestService;
use OpenEMR\Tests\E2e\Email\EmailTestData;
use OpenEMR\Tests\E2e\Email\EmailTestingTrait;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EmailTestServiceTest extends TestCase
{
    use EmailTestingTrait;

    private EmailTestService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['EMAIL_METHOD'] = 'SMTP';
        $GLOBALS['SMTP_HOST'] = getenv('OPENEMR_SETTING_SMTP_HOST') ?: 'mailpit';
        $GLOBALS['SMTP_PORT'] = getenv('OPENEMR_SETTING_SMTP_PORT') ?: '1025';
        $GLOBALS['SMTP_USER'] = getenv('OPENEMR_SETTING_SMTP_USER') ?: 'openemr';
        $GLOBALS['SMTP_PASS'] = getenv('OPENEMR_SETTING_SMTP_PASS') ?: 'openemr';
        $GLOBALS['SMTP_SECURE'] = getenv('OPENEMR_SETTING_SMTP_SECURE') ?: 'none';
        $GLOBALS['SMTP_Auth'] = getenv('OPENEMR_SETTING_SMTP_Auth') ?: 'TRUE';

        $this->initializeMailpit();
        $this->deleteAllMailpitMessages();

        // Clear any pre-existing queued emails so emailServiceRun() only sends ours
        QueryUtils::sqlStatementThrowException('DELETE FROM email_queue', []);

        $this->service = new EmailTestService(ServiceContainer::getLogger());
    }

    #[Test]
    public function directSendDeliversEmail(): void
    {
        $results = $this->service->test(
            EmailTestData::TEST_SENDER,
            EmailTestData::TEST_RECIPIENT,
            [EmailSendMethod::Direct],
        );

        $this->assertCount(1, $results);
        $this->assertTrue($results[0]->success, $results[0]->message);
        $this->assertSame(EmailSendMethod::Direct, $results[0]->method);

        $email = $this->waitForEmail(EmailTestData::TEST_RECIPIENT, 'Direct Send');
        $this->assertNotNull($email, 'Direct send email should arrive in Mailpit');
    }

    #[Test]
    public function queueInsertsAndDelivers(): void
    {
        $results = $this->service->test(
            EmailTestData::TEST_SENDER,
            EmailTestData::TEST_RECIPIENT,
            [EmailSendMethod::Queue],
        );

        $this->assertCount(1, $results);
        $this->assertTrue($results[0]->success, $results[0]->message);
        $this->assertSame(EmailSendMethod::Queue, $results[0]->method);

        // Flush the queue so the email is actually sent
        MyMailer::emailServiceRun();

        $email = $this->waitForEmail(EmailTestData::TEST_RECIPIENT, 'Queue');
        $this->assertNotNull($email, 'Queued email should arrive in Mailpit after emailServiceRun()');
    }

    #[Test]
    public function queueTemplatedInsertsAndDelivers(): void
    {
        $results = $this->service->test(
            EmailTestData::TEST_SENDER,
            EmailTestData::TEST_RECIPIENT,
            [EmailSendMethod::QueueTemplated],
        );

        $this->assertCount(1, $results);
        $this->assertTrue($results[0]->success, $results[0]->message);
        $this->assertSame(EmailSendMethod::QueueTemplated, $results[0]->method);

        // Flush the queue so the email is actually sent
        MyMailer::emailServiceRun();

        $email = $this->waitForEmail(EmailTestData::TEST_RECIPIENT, 'Queue Templated');
        $this->assertNotNull($email, 'Queue-templated email should arrive in Mailpit after emailServiceRun()');
    }

    #[Test]
    public function allMethodsTogetherProduceThreeResults(): void
    {
        $results = $this->service->test(
            EmailTestData::TEST_SENDER,
            EmailTestData::TEST_RECIPIENT,
            [EmailSendMethod::Direct, EmailSendMethod::Queue, EmailSendMethod::QueueTemplated],
        );

        $this->assertCount(3, $results);
        foreach ($results as $result) {
            $this->assertTrue($result->success, "[{$result->method->value}] {$result->message}");
        }

        // Flush queue for the two queued emails
        MyMailer::emailServiceRun();

        // Poll until all three emails arrive
        $deadline = time() + 10;
        do {
            $count = $this->getMailpitMessageCount();
            if ($count >= 3) {
                break;
            }
            usleep(250_000);
        } while (time() < $deadline);

        $this->assertSame(3, $count, 'All three test emails should arrive in Mailpit');
    }

    #[Test]
    public function directSendFailsWithBadHost(): void
    {
        $GLOBALS['EMAIL_METHOD'] = 'SMTP';
        $GLOBALS['SMTP_HOST'] = '127.0.0.1';
        $GLOBALS['SMTP_PORT'] = '1';

        $results = $this->service->test(
            EmailTestData::TEST_SENDER,
            EmailTestData::TEST_RECIPIENT,
            [EmailSendMethod::Direct],
        );

        $this->assertCount(1, $results);
        $this->assertFalse($results[0]->success);
    }
}
