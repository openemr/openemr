<?php

/**
 * EmailTestingTrait trait
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\Email;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

trait EmailTestingTrait
{
    private string $mailpitHost;
    private int $mailpitPort;
    private Client $httpClient;

    private function initializeMailpit(): void
    {
        $this->mailpitHost = getenv("MAILPIT_HOST") ?: "mailpit";
        $this->mailpitPort = (int)(getenv("MAILPIT_API_PORT") ?: 8025);

        $this->httpClient = new Client([
            'base_uri' => "http://{$this->mailpitHost}:{$this->mailpitPort}",
            'timeout' => 10.0,
        ]);
    }

    /**
     * Get all messages from Mailpit
     *
     * @param int $limit Maximum number of messages to retrieve
     * @return array
     * @throws GuzzleException
     */
    private function getMailpitMessages(int $limit = 50): array
    {
        if (!isset($this->httpClient)) {
            $this->initializeMailpit();
        }

        $response = $this->httpClient->get("/api/v1/messages", [
            'query' => ['limit' => $limit]
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        return $data['messages'] ?? [];
    }

    /**
     * Get a specific message by ID from Mailpit
     *
     * @param string $id Message ID
     * @return array|null
     * @throws GuzzleException
     */
    private function getMailpitMessage(string $id): ?array
    {
        if (!isset($this->httpClient)) {
            $this->initializeMailpit();
        }

        try {
            $response = $this->httpClient->get("/api/v1/message/{$id}");
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Search for messages in Mailpit
     *
     * @param string $query Search query
     * @return array
     * @throws GuzzleException
     */
    private function searchMailpitMessages(string $query): array
    {
        if (!isset($this->httpClient)) {
            $this->initializeMailpit();
        }

        $response = $this->httpClient->get("/api/v1/search", [
            'query' => ['query' => $query]
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        return $data['messages'] ?? [];
    }

    /**
     * Delete all messages from Mailpit
     *
     * @return bool
     * @throws GuzzleException
     */
    private function deleteAllMailpitMessages(): bool
    {
        if (!isset($this->httpClient)) {
            $this->initializeMailpit();
        }

        try {
            $this->httpClient->delete("/api/v1/messages");
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Wait for an email to arrive in Mailpit
     *
     * @param string $recipient Email recipient to wait for
     * @param string|null $subject Optional subject to match
     * @param int $timeout Timeout in seconds
     * @return array|null The matching message or null if timeout
     * @throws GuzzleException
     */
    private function waitForEmail(string $recipient, ?string $subject = null, int $timeout = 30): ?array
    {
        $startTime = time();

        while (time() - $startTime < $timeout) {
            $messages = $this->getMailpitMessages();

            foreach ($messages as $message) {
                $toAddresses = $message['To'] ?? [];
                $messageSubject = $message['Subject'] ?? '';

                foreach ($toAddresses as $to) {
                    if (strcasecmp((string) $to['Address'], $recipient) === 0) {
                        if ($subject === null || stripos($messageSubject, $subject) !== false) {
                            return $message;
                        }
                    }
                }
            }

            sleep(1);
        }

        return null;
    }

    /**
     * Assert that an email was received
     *
     * @param string $recipient Email recipient
     * @param string|null $subject Optional subject to match
     * @param string $message Assertion failure message
     * @return void
     * @throws GuzzleException
     */
    private function assertEmailReceived(string $recipient, ?string $subject = null, string $message = ''): void
    {
        $email = $this->waitForEmail($recipient, $subject, 30);

        if ($message === '') {
            $message = "Failed to receive email to {$recipient}";
            if ($subject !== null) {
                $message .= " with subject containing '{$subject}'";
            }
        }

        $this->assertNotNull($email, $message);
    }

    /**
     * Assert email content contains expected text
     *
     * @param string $messageId Message ID
     * @param string $expectedContent Expected content
     * @param string $message Assertion failure message
     * @return void
     * @throws GuzzleException
     */
    private function assertEmailContent(string $messageId, string $expectedContent, string $message = ''): void
    {
        $email = $this->getMailpitMessage($messageId);

        $this->assertNotNull($email, 'Email message not found');

        $body = $email['Text'] ?? $email['HTML'] ?? '';

        if ($message === '') {
            $message = "Email content does not contain expected text: {$expectedContent}";
        }

        $this->assertStringContainsString($expectedContent, $body, $message);
    }

    /**
     * Get the latest email sent to a recipient
     *
     * @param string $recipient Email recipient
     * @return array|null
     * @throws GuzzleException
     */
    private function getLatestEmailForRecipient(string $recipient): ?array
    {
        $messages = $this->getMailpitMessages();

        foreach ($messages as $message) {
            $toAddresses = $message['To'] ?? [];

            foreach ($toAddresses as $to) {
                if (strcasecmp((string) $to['Address'], $recipient) === 0) {
                    return $message;
                }
            }
        }

        return null;
    }

    /**
     * Count messages in Mailpit
     *
     * @return int
     * @throws GuzzleException
     */
    private function getMailpitMessageCount(): int
    {
        if (!isset($this->httpClient)) {
            $this->initializeMailpit();
        }

        $response = $this->httpClient->get("/api/v1/messages");
        $data = json_decode($response->getBody()->getContents(), true);

        return $data['total'] ?? 0;
    }
}
