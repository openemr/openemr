<?php

/**
 * EmailTestingTrait trait
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc <https://opencoreemr.com/>
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
     * Extract the messages array from a decoded Mailpit API response.
     *
     * @param array<string, mixed> $data
     * @return list<array<string, mixed>>
     */
    private function extractMessages(array $data): array
    {
        if (!array_key_exists('messages', $data)) {
            return [];
        }
        $messages = $data['messages'];
        if (!is_array($messages)) {
            throw new \RuntimeException(
                'Unexpected Mailpit JSON response: "messages" field must be an array, got ' . gettype($messages),
            );
        }
        /** @var list<array<string, mixed>> */
        return array_values($messages);
    }

    /**
     * Decode a JSON response body into an associative array.
     *
     * @return array<string, mixed>
     */
    private function decodeJsonBody(string $body): array
    {
        $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            throw new \RuntimeException(
                'Unexpected Mailpit JSON response: expected object, got ' . gettype($decoded),
            );
        }
        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    /**
     * Get all messages from Mailpit
     *
     * @param int $limit Maximum number of messages to retrieve
     * @return list<array<string, mixed>>
     */
    private function getMailpitMessages(int $limit = 50): array
    {
        if (!isset($this->httpClient)) {
            $this->initializeMailpit();
        }

        $response = $this->httpClient->get("/api/v1/messages", [
            'query' => ['limit' => $limit]
        ]);

        $data = $this->decodeJsonBody($response->getBody()->getContents());
        return $this->extractMessages($data);
    }

    /**
     * Get a specific message by ID from Mailpit
     *
     * @return array<string, mixed>|null
     */
    private function getMailpitMessage(string $id): ?array
    {
        if (!isset($this->httpClient)) {
            $this->initializeMailpit();
        }

        try {
            $response = $this->httpClient->get("/api/v1/message/{$id}");
        } catch (GuzzleException) {
            return null;
        }
        $decoded = $this->decodeJsonBody($response->getBody()->getContents());
        return $decoded !== [] ? $decoded : null;
    }

    /**
     * Search for messages in Mailpit
     *
     * @return list<array<string, mixed>>
     */
    private function searchMailpitMessages(string $query): array
    {
        if (!isset($this->httpClient)) {
            $this->initializeMailpit();
        }

        $response = $this->httpClient->get("/api/v1/search", [
            'query' => ['query' => $query]
        ]);

        $data = $this->decodeJsonBody($response->getBody()->getContents());
        return $this->extractMessages($data);
    }

    /**
     * Delete all messages from Mailpit
     *
     */
    private function deleteAllMailpitMessages(): bool
    {
        if (!isset($this->httpClient)) {
            $this->initializeMailpit();
        }

        try {
            $this->httpClient->delete("/api/v1/messages");
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Wait for an email to arrive in Mailpit
     *
     * @return array<string, mixed>|null The matching message or null if timeout
     */
    private function waitForEmail(string $recipient, ?string $subject = null, int $timeout = 30): ?array
    {
        $startTime = time();

        while (time() - $startTime < $timeout) {
            $messages = $this->getMailpitMessages();

            foreach ($messages as $message) {
                /** @var list<array{Address: string}> $toAddresses */
                $toAddresses = $message['To'] ?? [];
                $messageSubject = is_string($message['Subject'] ?? null) ? $message['Subject'] : '';

                foreach ($toAddresses as $to) {
                    if (strcasecmp($to['Address'], $recipient) === 0) {
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
     */
    private function assertEmailContent(string $messageId, string $expectedContent, string $message = ''): void
    {
        $email = $this->getMailpitMessage($messageId);

        $this->assertNotNull($email, 'Email message not found');

        $text = is_string($email['Text'] ?? null) ? $email['Text'] : '';
        $html = is_string($email['HTML'] ?? null) ? $email['HTML'] : '';
        $body = $text !== '' ? $text : $html;

        if ($message === '') {
            $message = "Email content does not contain expected text: {$expectedContent}";
        }

        $this->assertStringContainsString($expectedContent, $body, $message);
    }

    /**
     * Get the latest email sent to a recipient
     *
     * @return array<string, mixed>|null
     */
    private function getLatestEmailForRecipient(string $recipient): ?array
    {
        $messages = $this->getMailpitMessages();

        foreach ($messages as $message) {
            /** @var list<array{Address: string}> $toAddresses */
            $toAddresses = $message['To'] ?? [];

            foreach ($toAddresses as $to) {
                if (strcasecmp($to['Address'], $recipient) === 0) {
                    return $message;
                }
            }
        }

        return null;
    }

    /**
     * Count messages in Mailpit
     *
     */
    private function getMailpitMessageCount(): int
    {
        if (!isset($this->httpClient)) {
            $this->initializeMailpit();
        }

        $response = $this->httpClient->get("/api/v1/messages");
        $data = $this->decodeJsonBody($response->getBody()->getContents());

        $total = $data['total'] ?? 0;
        return is_int($total) ? $total : 0;
    }
}
