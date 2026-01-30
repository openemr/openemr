# Email Testing with Mailpit

This directory contains automated tests for email functionality in OpenEMR using [Mailpit](https://github.com/axllent/mailpit) as a mail testing service.

## Overview

The email testing infrastructure allows you to:
- Test email sending functionality in OpenEMR core
- Test email sending in the oe-module-faxsms module
- Verify email delivery, content, and metadata
- Run tests both locally and in CI/CD pipelines

## Architecture

### Components

1. **Mailpit Service**: A lightweight SMTP server and web UI for testing emails
   - SMTP Server: Port 1025 (receives emails)
   - Web UI/API: Port 8025 (view and query emails)

2. **EmailTestingTrait**: Provides helper methods for interacting with Mailpit API
   - `getMailpitMessages()` - Retrieve all messages
   - `getMailpitMessage($id)` - Get specific message
   - `waitForEmail($recipient, $subject)` - Wait for email arrival
   - `assertEmailReceived()` - Assert email was received
   - `deleteAllMailpitMessages()` - Clean up before tests

3. **EmailTestData**: Contains test data constants
   - Test email addresses
   - Test subjects and bodies
   - Mailpit configuration

4. **Test Suites**:
   - `EmailSendTest.php` - Integration tests for OpenEMR core email functionality using direct PHP calls
   - `FaxSmsEmailTest.php` - Placeholder tests for oe-module-faxsms (currently skipped, pending proper setup)

**Note**: These are integration tests, not E2E tests. They do **not** require Selenium or browser automation. They test email functionality by:
- Sending emails directly using `MyMailer` class (instantiating and calling `send()`)
- Verifying emails arrive in Mailpit via API calls

**Important**: Tests use direct `MyMailer->send()` calls rather than the queue system to avoid Twig template dependencies in the test environment.

## Running Tests Locally

### Prerequisites

1. Docker and Docker Compose installed
2. OpenEMR development environment set up

### Start the Environment

```bash
cd docker/development-easy
docker compose up -d
```

This will start:
- MySQL database
- OpenEMR application
- Mailpit service
- Other supporting services (Selenium, CouchDB, etc.)

### Access Mailpit Web UI

Open your browser to: `http://localhost:8025`

You can view all emails sent during testing in the web interface.

### Run Email Tests

From the OpenEMR root directory:

```bash
# Run all email tests
./vendor/bin/phpunit --testsuite email

# Run specific test file
./vendor/bin/phpunit tests/Tests/E2e/EmailSendTest.php

# Run specific test method
./vendor/bin/phpunit --filter testEmailQueueViaDatabase tests/Tests/E2e/EmailSendTest.php
```

### View Test Results

- Test output will show in the terminal
- Emails can be viewed in the Mailpit UI at `http://localhost:8025`
- JUnit XML results are saved to `junit.xml`

## Running Tests in CI

The email tests are integrated into the GitHub Actions CI pipeline:

1. Mailpit is automatically started as part of the Docker Compose stack
2. Email tests run after other test suites
3. Results are uploaded to Codecov

The tests run on all configured PHP/database combinations defined in `.github/workflows/test.yml`.

## Configuration

### Development Environment

Email settings are configured in `docker/development-easy/docker-compose.yml`:

```yaml
OPENEMR_SETTING_EMAIL_METHOD: SMTP
OPENEMR_SETTING_SMTP_HOST: mailpit
OPENEMR_SETTING_SMTP_PORT: 1025
OPENEMR_SETTING_SMTP_USER: openemr
OPENEMR_SETTING_SMTP_PASS: openemr
OPENEMR_SETTING_SMTP_SECURE: ''
OPENEMR_SETTING_SMTP_Auth: 'TRUE'
```

### CI Environment

Email settings are configured in `ci/compose-shared-mailpit.yml` and merged with other compose files during CI runs.

### Environment Variables

You can override Mailpit configuration using environment variables:

- `MAILPIT_HOST` - Mailpit hostname (default: `mailpit`)
- `MAILPIT_API_PORT` - Mailpit API port (default: `8025`)

## Writing New Email Tests

### Basic Structure

```php
<?php

namespace OpenEMR\Tests\E2e;

use MyMailer;
use OpenEMR\Tests\E2e\Email\EmailTestingTrait;
use OpenEMR\Tests\E2e\Email\EmailTestData;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MyEmailTest extends TestCase
{
    use EmailTestingTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initializeMailpit();
        $this->deleteAllMailpitMessages(); // Clean slate for each test
    }

    #[Test]
    public function testMyEmailFeature(): void
    {
        // Send email directly using MyMailer
        $mailer = new MyMailer();
        $mailer->setFrom(EmailTestData::TEST_SENDER, 'Test Sender');
        $mailer->addAddress(EmailTestData::TEST_RECIPIENT);
        $mailer->Subject = 'Test Subject';
        $mailer->Body = 'Test body content';
        $mailer->isHTML(false);

        $sent = $mailer->send();
        $this->assertTrue($sent, 'Email should be sent successfully');

        // Wait for email delivery
        sleep(2);

        // Assert email was received
        $this->assertEmailReceived(
            EmailTestData::TEST_RECIPIENT,
            'Test Subject'
        );

        // Verify content
        $email = $this->getLatestEmailForRecipient(EmailTestData::TEST_RECIPIENT);
        $this->assertNotNull($email);

        if (isset($email['ID'])) {
            $this->assertEmailContent($email['ID'], 'Test body content');
        }
    }
}
```

### Best Practices

1. **Clean up before tests**: Always call `deleteAllMailpitMessages()` in `setUp()`
2. **Use unique subjects**: For tests that search, use unique subjects to avoid conflicts
3. **Wait for emails**: Use `waitForEmail()` or `assertEmailReceived()` which includes waiting
4. **Use test constants**: Use `EmailTestData` constants for consistency
5. **Send directly**: Use `new MyMailer()` and call `send()` directly to avoid queue/template dependencies
6. **Set sender properly**: Use `setFrom()` instead of deprecated `From` property
7. **No Selenium needed**: These are integration tests - no browser required

## Mailpit API Reference

### Common Endpoints

- `GET /api/v1/messages` - List all messages
- `GET /api/v1/message/{id}` - Get specific message
- `GET /api/v1/search?query={query}` - Search messages
- `DELETE /api/v1/messages` - Delete all messages

### Response Structure

Message list response:
```json
{
  "total": 5,
  "messages": [
    {
      "ID": "abc123",
      "From": {"Name": "Sender", "Address": "sender@example.com"},
      "To": [{"Name": "Recipient", "Address": "recipient@example.com"}],
      "Subject": "Test Email",
      "Created": "2025-01-01T12:00:00Z",
      "Size": 1234
    }
  ]
}
```

Full message response includes:
- `Text` - Plain text body
- `HTML` - HTML body
- `Attachments` - Array of attachments
- `Headers` - Email headers

## Troubleshooting

### Emails Not Appearing

1. Check Mailpit is running: `docker compose ps mailpit`
2. Check logs: `docker compose logs mailpit`
3. Verify SMTP settings in OpenEMR
4. Check network connectivity between containers

### Tests Timing Out

1. Increase timeout in `waitForEmail()` calls
2. Check if email sending is actually triggered
3. Review OpenEMR error logs: `docker compose logs openemr`

### Mailpit API Connection Errors

1. Verify Mailpit host and port configuration
2. Check if Mailpit healthcheck is passing
3. Ensure GuzzleHTTP client is properly initialized

### Common Issues

**Issue**: Tests fail with "Email not received"
- **Solution**: Check that email sending code is actually executing. Add debugging to verify the email is being queued/sent.

**Issue**: Mailpit returns 404 for messages
- **Solution**: Ensure messages were actually sent. Check Mailpit UI to verify.

**Issue**: Connection refused to Mailpit
- **Solution**: Make sure Mailpit container is running and healthy: `docker compose ps mailpit`

## Additional Resources

- [Mailpit Documentation](https://mailpit.axllent.org/)
- [Mailpit GitHub Repository](https://github.com/axllent/mailpit)
- [OpenEMR Email Queue System](../../../library/classes/postmaster.php)
- [PHPUnit Documentation](https://phpunit.de/)
- [Symfony Panther Documentation](https://github.com/symfony/panther)

## Contributing

When adding new email tests:

1. Follow the existing test structure
2. Use descriptive test names
3. Add appropriate assertions
4. Update this README if adding new patterns or helpers
5. Ensure tests pass locally before submitting PR

## License

Copyright (c) 2025 OpenCoreEMR Inc.
Licensed under GPL v3 - see LICENSE file for details
