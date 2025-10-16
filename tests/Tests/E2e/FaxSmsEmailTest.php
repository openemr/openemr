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
use OpenEMR\Modules\FaxSMS\Exception\InvalidEmailAddressException;
use OpenEMR\Modules\FaxSMS\Exception\SmtpNotConfiguredException;
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

        // Set up SMTP configuration in $GLOBALS for MyMailer
        $GLOBALS['EMAIL_METHOD'] = 'SMTP';
        $GLOBALS['SMTP_HOST'] = getenv('OPENEMR_SETTING_SMTP_HOST') ?: 'mailpit';
        $GLOBALS['SMTP_PORT'] = getenv('OPENEMR_SETTING_SMTP_PORT') ?: '1025';
        $GLOBALS['SMTP_USER'] = getenv('OPENEMR_SETTING_SMTP_USER') ?: 'openemr';
        $GLOBALS['SMTP_PASS'] = getenv('OPENEMR_SETTING_SMTP_PASS') ?: 'openemr';
        $GLOBALS['SMTP_SECURE'] = getenv('OPENEMR_SETTING_SMTP_SECURE') ?: 'none';
        $GLOBALS['SMTP_Auth'] = getenv('OPENEMR_SETTING_SMTP_Auth') ?: 'TRUE';
        $GLOBALS['practice_return_email_path'] = EmailTestData::TEST_SENDER;
        $GLOBALS['Patient Reminder Sender Name'] = 'OpenEMR Test';
        $GLOBALS['oe_enable_email'] = true;

        // Manually load the FaxSMS module classes since they're not in the main autoloader
        $faxSmsModulePath = $GLOBALS['fileroot'] . '/interface/modules/custom_modules/oe-module-faxsms';

        // Require the necessary module files
        require_once $faxSmsModulePath . '/src/Controller/AppDispatch.php';
        require_once $faxSmsModulePath . '/src/Controller/EmailClient.php';
        require_once $faxSmsModulePath . '/src/BootstrapService.php';
        require_once $faxSmsModulePath . '/src/Exception/EmailException.php';
        require_once $faxSmsModulePath . '/src/Exception/SmtpNotConfiguredException.php';
        require_once $faxSmsModulePath . '/src/Exception/InvalidEmailAddressException.php';
        require_once $faxSmsModulePath . '/src/Exception/EmailSendFailedException.php';

        $this->initializeMailpit();
        // Clear all existing emails before each test
        $this->deleteAllMailpitMessages();
    }

    #[Test]
    public function testFaxSmsModuleEmailReminderFunction(): void
    {
        // Test the emailReminder method from EmailClient
        $emailClient = new EmailClient();

        $testEmail = EmailTestData::TEST_RECIPIENT;
        $testBody = "Dear Patient, this is a reminder for your upcoming appointment.";

        // Send the reminder
        $result = $emailClient->emailReminder($testEmail, $testBody);

        // The emailReminder method returns false|string (true on success, error message on failure)
        $this->assertTrue($result !== false, 'Email reminder should send successfully');

        // Wait for email to be delivered
        sleep(2);

        // Verify email was received in Mailpit
        $email = $this->waitForEmail($testEmail, 'A Reminder for You', 30);
        $this->assertNotNull($email, 'Appointment reminder email should be received');

        // Verify the subject
        $this->assertEquals('A Reminder for You', $email['Subject'], 'Email subject should match');

        // Get full message to verify body content
        if (isset($email['ID'])) {
            $fullMessage = $this->getMailpitMessage($email['ID']);
            $this->assertNotNull($fullMessage, 'Should retrieve full message');

            $bodyContent = $fullMessage['Text'] ?? '';
            $this->assertStringContainsString('upcoming appointment', $bodyContent, 'Email body should contain appointment text');
        }
    }

    #[Test]
    public function testFaxSmsModuleEmailSendFunction(): void
    {
        // Test the sendEmail method from EmailClient
        // This method uses $_REQUEST parameters, so we need to simulate them
        $_REQUEST['email'] = EmailTestData::TEST_RECIPIENT;
        $_REQUEST['subject'] = 'Test Subject from FaxSMS Module';
        $_REQUEST['comments'] = 'This is a test message body.';
        $_REQUEST['html_content'] = '';

        // Mock the logged-in user
        $_SESSION['authUser'] = 'testuser';
        $_SESSION['authUserID'] = 1;

        $emailClient = new EmailClient();

        $result = $emailClient->sendEmail();

        // The sendEmail method returns a js_escape'd status message
        $this->assertNotEmpty($result, 'sendEmail should return a result');
        $this->assertStringNotContainsString('Error', $result, 'sendEmail should not contain error message');

        // Wait for email to be delivered
        sleep(2);

        // Verify email was received
        $email = $this->waitForEmail(EmailTestData::TEST_RECIPIENT, 'Test Subject from FaxSMS Module', 30);
        $this->assertNotNull($email, 'Email from sendEmail should be received');

        // Clean up
        unset($_REQUEST['email'], $_REQUEST['subject'], $_REQUEST['comments'], $_REQUEST['html_content']);
    }

    #[Test]
    public function testFaxSmsModuleEmailWithoutSmtpConfigured(): void
    {
        // Save original SMTP settings
        $originalSmtpPass = $GLOBALS['SMTP_PASS'];
        $originalSmtpUser = $GLOBALS['SMTP_USER'];

        // Temporarily disable SMTP by clearing credentials
        $GLOBALS['SMTP_PASS'] = '';
        $GLOBALS['SMTP_USER'] = '';

        $emailClient = new EmailClient();

        $testEmail = EmailTestData::TEST_RECIPIENT;
        $testBody = "Test message";

        // Attempt to send email without SMTP configured - should throw exception
        $this->expectException(SmtpNotConfiguredException::class);
        $this->expectExceptionMessage('SMTP not configured');

        try {
            $emailClient->emailReminder($testEmail, $testBody);
        } finally {
            // Restore original SMTP settings
            $GLOBALS['SMTP_PASS'] = $originalSmtpPass;
            $GLOBALS['SMTP_USER'] = $originalSmtpUser;
        }
    }

    #[Test]
    public function testFaxSmsModuleEmailDocumentWithAttachment(): void
    {
        // Create a temporary test file to attach
        $tempFile = tempnam(sys_get_temp_dir(), 'test_document_');
        file_put_contents($tempFile, 'This is a test document for email attachment.');

        $emailClient = new EmailClient();

        $testEmail = EmailTestData::TEST_RECIPIENT;
        $testBody = "Please find the attached document.";
        $testUser = [
            'fname' => 'Test',
            'lname' => 'Provider'
        ];

        // Send email with attachment
        $result = $emailClient->emailDocument($testEmail, $testBody, $tempFile, $testUser);

        // Should return success message
        $this->assertStringContainsString('successfully sent', $result, 'Email with document should send successfully');

        // Wait for email to be delivered
        sleep(2);

        // Verify email was received
        $email = $this->waitForEmail($testEmail, 'Forwarded Fax Document', 30);
        $this->assertNotNull($email, 'Email with attachment should be received');

        // Get full message to check for attachment
        if (isset($email['ID'])) {
            $fullMessage = $this->getMailpitMessage($email['ID']);
            $this->assertNotNull($fullMessage, 'Should retrieve full message');

            // Check that message has attachments
            $attachments = $fullMessage['Attachments'] ?? [];
            $this->assertNotEmpty($attachments, 'Email should have at least one attachment');
        }

        // Clean up temporary file
        unlink($tempFile);
    }

    #[Test]
    public function testFaxSmsModuleEmailValidationBadEmail(): void
    {
        $emailClient = new EmailClient();

        // Test with invalid email address
        $invalidEmail = 'not-a-valid-email';
        $testBody = "Test message";

        $expectedException = false;
        try {
            $emailClient->emailReminder($invalidEmail, $testBody);
        } catch (InvalidEmailAddressException) {
            $expectedException = true;
        }
        $this->assertTrue($expectedException, "Expected an InvalidEmailAddressException");

        // Verify no email was sent
        sleep(1);
        $count = $this->getMailpitMessageCount();
        $this->assertEquals(0, $count, 'No email should be sent for invalid email address');

        // Test with empty email
        $emptyEmail = '';
        $this->expectException(InvalidEmailAddressException::class);
        $emailClient->emailReminder($emptyEmail, $testBody);
    }

    #[Test]
    public function testFaxSmsModuleEmailValidationBlankEmail(): void
    {
        $emailClient = new EmailClient();

        // Test with invalid email address
        $invalidEmail = 'not-a-valid-email';
        $testBody = "Test message";

        $this->expectException(InvalidEmailAddressException::class);
        $emailClient->emailReminder($invalidEmail, $testBody);

        // Verify no email was sent
        sleep(1);
        $count = $this->getMailpitMessageCount();
        $this->assertEquals(0, $count, 'No email should be sent for invalid email address');

        // Test with empty email
        $emptyEmail = '';
        $this->expectException(InvalidEmailAddressException::class);
        $emailClient->emailReminder($emptyEmail, $testBody);
    }

    #[Test]
    public function testMultipleRecipientsFromFaxSmsModule(): void
    {
        $emailClient = new EmailClient();

        $recipients = [
            EmailTestData::TEST_RECIPIENT,
            EmailTestData::TEST_RECIPIENT_2,
        ];

        $testBody = "This is a test appointment reminder.";

        // Send email to multiple recipients
        foreach ($recipients as $recipient) {
            $result = $emailClient->emailReminder($recipient, $testBody);
            $this->assertTrue($result !== false, "Email to {$recipient} should send successfully");
        }

        // Wait for emails to be delivered
        sleep(2);

        // Verify all emails were received
        foreach ($recipients as $recipient) {
            $email = $this->getLatestEmailForRecipient($recipient);
            $this->assertNotNull($email, "Email should be received for {$recipient}");
            $this->assertEquals('A Reminder for You', $email['Subject'], 'Subject should match for all recipients');
        }

        // Verify total count
        $count = $this->getMailpitMessageCount();
        $this->assertGreaterThanOrEqual(
            count($recipients),
            $count,
            'Should have at least ' . count($recipients) . ' messages in Mailpit'
        );
    }
}
