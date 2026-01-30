<?php

/**
 * EmailTestData class
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\Email;

class EmailTestData
{
    // Test email addresses
    public const TEST_RECIPIENT = 'patient@example.com';
    public const TEST_RECIPIENT_2 = 'doctor@example.com';
    public const TEST_SENDER = 'noreply@openemr.local';

    // Test subjects
    public const TEST_SUBJECT_BASIC = 'Test Email Subject';
    public const TEST_SUBJECT_REMINDER = 'A Reminder for You';
    public const TEST_SUBJECT_DOCUMENT = 'Forwarded Fax Document';
    public const TEST_SUBJECT_PRIVATE = 'Private confidential message';

    // Test body content
    public const TEST_BODY_BASIC = 'This is a test email body.';
    public const TEST_BODY_HTML = '<html><body><h1>Test Email</h1><p>This is a test.</p></body></html>';

    // Mailpit configuration
    public const MAILPIT_HOST = 'mailpit';
    public const MAILPIT_API_PORT = 8025;
    public const MAILPIT_SMTP_PORT = 1025;

    // Timeouts
    public const EMAIL_ARRIVAL_TIMEOUT = 30; // seconds
    public const EMAIL_POLL_INTERVAL = 1; // seconds
}
