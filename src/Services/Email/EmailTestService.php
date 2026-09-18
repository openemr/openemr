<?php

/**
 * Send test emails via each of OpenEMR's email code paths
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Email;

use MyMailer;
use Psr\Log\LoggerInterface;

class EmailTestService
{
    private const QUEUE_TEMPLATE = 'emails/system/system-notification';

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Run one or more email send tests.
     *
     * @param non-empty-list<EmailSendMethod> $methods
     * @return list<EmailTestResult>
     */
    public function test(string $sender, string $recipient, array $methods): array
    {
        $results = [];
        foreach ($methods as $method) {
            $results[] = match ($method) {
                EmailSendMethod::Direct => $this->testDirect($sender, $recipient),
                EmailSendMethod::Queue => $this->testQueue($sender, $recipient),
                EmailSendMethod::QueueTemplated => $this->testQueueTemplated($sender, $recipient),
            };
        }
        return $results;
    }

    private function testDirect(string $sender, string $recipient): EmailTestResult
    {
        $method = EmailSendMethod::Direct;

        $mailer = null;
        try {
            $mailer = new MyMailer(true);
            $mailer->setFrom($sender);
            $mailer->addReplyTo($sender);
            $mailer->addAddress($recipient);
            $mailer->Subject = 'OpenEMR Email Test — Direct Send';
            $mailer->Body = 'This is a test email sent directly via MyMailer::send().';
            $mailer->isHTML(false);

            $sent = $mailer->send();
        } catch (\Throwable $e) {
            $this->logger->error('Direct email send failed', ['exception' => $e]);
            $errorInfo = ($mailer !== null && $mailer->ErrorInfo !== '') ? $mailer->ErrorInfo : $e->getMessage();
            return new EmailTestResult($method, false, 'Send failed: ' . $errorInfo);
        } finally {
            $mailer?->smtpClose();
        }

        if (!$sent) {
            return new EmailTestResult($method, false, 'Send returned false: ' . $mailer->ErrorInfo);
        }
        return new EmailTestResult($method, true, 'Email sent successfully.');
    }

    private function testQueue(string $sender, string $recipient): EmailTestResult
    {
        $method = EmailSendMethod::Queue;

        try {
            $queued = MyMailer::emailServiceQueue(
                $sender,
                $recipient,
                'OpenEMR Email Test — Queue',
                'This is a test email queued via MyMailer::emailServiceQueue().',
            );
        } catch (\Throwable $e) {
            $this->logger->error('Queue email failed', ['exception' => $e]);
            return new EmailTestResult($method, false, 'Failed to queue email. Check the application logs for details.');
        }

        if (!$queued) {
            return new EmailTestResult($method, false, 'emailServiceQueue() returned false. Check the application logs for details.');
        }
        return new EmailTestResult($method, true, 'Email queued successfully. It will be sent when the email background service runs.');
    }

    private function testQueueTemplated(string $sender, string $recipient): EmailTestResult
    {
        $method = EmailSendMethod::QueueTemplated;

        try {
            $queued = MyMailer::emailServiceQueueTemplatedEmail(
                $sender,
                $recipient,
                'OpenEMR Email Test — Queue Templated',
                self::QUEUE_TEMPLATE,
                ['message' => 'This is a test email queued via MyMailer::emailServiceQueueTemplatedEmail().'],
            );
        } catch (\Throwable $e) {
            $this->logger->error('Queue templated email failed', ['exception' => $e]);
            return new EmailTestResult($method, false, 'Failed to queue templated email. Check the application logs for details.');
        }

        if (!$queued) {
            return new EmailTestResult($method, false, 'emailServiceQueueTemplatedEmail() returned false. Check the application logs for details.');
        }
        return new EmailTestResult($method, true, 'Templated email queued successfully. It will be sent when the email background service runs.');
    }
}
