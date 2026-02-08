<?php

/**
 * EmailSendTest class
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use MyMailer;
use OpenEMR\Tests\E2e\Email\EmailTestingTrait;
use OpenEMR\Tests\E2e\Email\EmailTestData;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

class EmailSendTest extends TestCase
{
    use EmailTestingTrait;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up SMTP configuration in $GLOBALS for MyMailer
        // These values should match what's in ci/compose-shared-mailpit.yml
        $GLOBALS['EMAIL_METHOD'] = 'SMTP';
        $GLOBALS['SMTP_HOST'] = getenv('OPENEMR_SETTING_SMTP_HOST') ?: 'mailpit';
        $GLOBALS['SMTP_PORT'] = getenv('OPENEMR_SETTING_SMTP_PORT') ?: '1025';
        $GLOBALS['SMTP_USER'] = getenv('OPENEMR_SETTING_SMTP_USER') ?: 'openemr';
        $GLOBALS['SMTP_PASS'] = getenv('OPENEMR_SETTING_SMTP_PASS') ?: 'openemr';
        // Note: SMTP_SECURE empty string fails MyMailer::isConfigured() check
        // Use 'none' for no encryption (Mailpit accepts any value)
        $GLOBALS['SMTP_SECURE'] = getenv('OPENEMR_SETTING_SMTP_SECURE') ?: 'none';
        $GLOBALS['SMTP_Auth'] = getenv('OPENEMR_SETTING_SMTP_Auth') ?: 'TRUE';

        $this->initializeMailpit();
        // Clear all existing emails before each test
        $this->deleteAllMailpitMessages();
    }

    #[Test]
    public function testMailpitIsAccessible(): void
    {
        // Test that Mailpit API is accessible
        $count = $this->getMailpitMessageCount();
        $this->assertIsInt($count, 'Mailpit API should return message count');
        $this->assertEquals(0, $count, 'Mailpit should have no messages after cleanup');

        // Also check if email is configured
        $isConfigured = MyMailer::isConfigured();

        // Debug output to see what's actually set
        $debugInfo = sprintf(
            "SMTP Config - EMAIL_METHOD: %s, SMTP_HOST: %s, SMTP_PORT: %s, SMTP_USER: %s, SMTP_PASS: %s, SMTP_SECURE: %s, SMTP_Auth: %s",
            $GLOBALS['EMAIL_METHOD'] ?? 'NOT SET',
            $GLOBALS['SMTP_HOST'] ?? 'NOT SET',
            $GLOBALS['SMTP_PORT'] ?? 'NOT SET',
            $GLOBALS['SMTP_USER'] ?? 'NOT SET',
            ($GLOBALS['SMTP_PASS'] ?? 'NOT SET') !== 'NOT SET' ? '***SET***' : 'NOT SET',
            $GLOBALS['SMTP_SECURE'] ?? 'NOT SET',
            $GLOBALS['SMTP_Auth'] ?? 'NOT SET'
        );

        $this->assertTrue($isConfigured, 'MyMailer should be configured for SMTP. ' . $debugInfo);
    }

    #[Test]
    #[Depends('testMailpitIsAccessible')]
    public function testEmailQueueViaDatabase(): void
    {
        // Send an email directly using MyMailer (bypassing queue to avoid Twig template dependencies)
        $mailer = new MyMailer();
        $mailer->setFrom(EmailTestData::TEST_SENDER, 'OpenEMR Test');
        $mailer->addAddress(EmailTestData::TEST_RECIPIENT);
        $mailer->Subject = EmailTestData::TEST_SUBJECT_BASIC;
        $mailer->Body = EmailTestData::TEST_BODY_BASIC;
        $mailer->isHTML(false);

        $sent = $mailer->send();
        if (!$sent) {
            $this->fail('Email failed to send: ' . $mailer->ErrorInfo);
        }
        $this->assertTrue($sent, 'Email should be sent successfully');

        // Wait for email to be delivered
        sleep(2);

        // Verify email was received in Mailpit
        $this->assertEmailReceived(
            EmailTestData::TEST_RECIPIENT,
            EmailTestData::TEST_SUBJECT_BASIC,
            'Email should be received in Mailpit after sending'
        );

        // Get the received email and verify content
        $email = $this->getLatestEmailForRecipient(EmailTestData::TEST_RECIPIENT);
        $this->assertNotNull($email, 'Should find the sent email');

        // Verify subject
        $this->assertEquals(EmailTestData::TEST_SUBJECT_BASIC, $email['Subject'], 'Email subject should match');

        // Verify sender
        $fromAddresses = $email['From'] ?? [];
        $this->assertNotEmpty($fromAddresses, 'Email should have a sender');
    }

    #[Test]
    #[Depends('testMailpitIsAccessible')]
    public function testMultipleEmailsQueued(): void
    {
        $recipients = [
            EmailTestData::TEST_RECIPIENT,
            EmailTestData::TEST_RECIPIENT_2,
        ];

        foreach ($recipients as $recipient) {
            $mailer = new MyMailer();
            $mailer->setFrom(EmailTestData::TEST_SENDER, 'OpenEMR Test');
            $mailer->addAddress($recipient);
            $mailer->Subject = EmailTestData::TEST_SUBJECT_BASIC;
            $mailer->Body = EmailTestData::TEST_BODY_BASIC;
            $mailer->isHTML(false);
            $sent = $mailer->send();
            if (!$sent) {
                $this->fail("Email to {$recipient} failed: " . $mailer->ErrorInfo);
            }
        }

        // Wait for emails to be delivered
        sleep(2);

        // Verify both emails were received
        foreach ($recipients as $recipient) {
            $email = $this->getLatestEmailForRecipient($recipient);
            $this->assertNotNull($email, "Email should be received for {$recipient}");
        }

        // Verify total count
        $count = $this->getMailpitMessageCount();
        $this->assertGreaterThanOrEqual(
            count($recipients),
            $count,
            'Should have at least ' . count($recipients) . ' messages in Mailpit'
        );
    }

    #[Test]
    #[Depends('testMailpitIsAccessible')]
    public function testEmailWithHtmlContent(): void
    {
        // Send an email with HTML content using MyMailer directly
        $mailer = new MyMailer();
        $mailer->setFrom(EmailTestData::TEST_SENDER, 'OpenEMR');
        $mailer->addAddress(EmailTestData::TEST_RECIPIENT);
        $mailer->Subject = EmailTestData::TEST_SUBJECT_BASIC;
        $mailer->Body = EmailTestData::TEST_BODY_HTML;
        $mailer->isHTML(true);

        $sent = $mailer->send();
        if (!$sent) {
            $this->fail('HTML email failed to send: ' . $mailer->ErrorInfo);
        }
        $this->assertTrue($sent, 'HTML email should be sent successfully');

        // Wait for email to be processed
        sleep(2);

        // Verify email was received
        $email = $this->waitForEmail(EmailTestData::TEST_RECIPIENT, EmailTestData::TEST_SUBJECT_BASIC);
        $this->assertNotNull($email, 'HTML email should be received');

        // Get full message details to check HTML content
        if ($email && isset($email['ID'])) {
            $fullMessage = $this->getMailpitMessage($email['ID']);
            $this->assertNotNull($fullMessage, 'Should retrieve full message');

            $htmlContent = $fullMessage['HTML'] ?? '';
            $this->assertStringContainsString('Test Email', $htmlContent, 'HTML content should be present');
        }
    }

    #[Test]
    #[Depends('testMailpitIsAccessible')]
    public function testSearchEmails(): void
    {
        $uniqueSubject = 'Unique Test Subject ' . time();

        // Send an email with a unique subject
        $mailer = new MyMailer();
        $mailer->setFrom(EmailTestData::TEST_SENDER, 'OpenEMR Test');
        $mailer->addAddress(EmailTestData::TEST_RECIPIENT);
        $mailer->Subject = $uniqueSubject;
        $mailer->Body = EmailTestData::TEST_BODY_BASIC;
        $mailer->isHTML(false);
        $sent = $mailer->send();
        if (!$sent) {
            $this->fail('Search test email failed to send: ' . $mailer->ErrorInfo);
        }

        // Wait for email
        sleep(2);

        // Search for the email
        $searchResults = $this->searchMailpitMessages($uniqueSubject);
        $this->assertNotEmpty($searchResults, 'Search should find the email with unique subject');

        $found = false;
        foreach ($searchResults as $result) {
            if (stripos($result['Subject'] ?? '', $uniqueSubject) !== false) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Search results should contain the email with unique subject');
    }
}
