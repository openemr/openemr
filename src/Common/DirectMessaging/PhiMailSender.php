<?php

/**
 * Sends a Direct message, with an optional attachment, through a phiMail
 * Direct Messaging server.
 *
 * A send returns "SUCCESS" or a translated message for the user, because the
 * UI callers display whatever it returns. The server's final reply goes to the
 * audit log, and an accepted message gets an 'S' row in direct_message_log so
 * the background check can match its delivery status later.
 *
 * Copyright (C) 2013, 2021 EMR Direct <https://www.emrdirect.com/>
 * Copyright (C) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  EMR Direct <https://www.emrdirect.com/>
 * @author  Michael A. Smith <michael@opencoreemr.com>
 * @link    https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\SoftwareVersion;
use Psr\Log\LoggerInterface;

final readonly class PhiMailSender
{
    public const SUCCESS = 'SUCCESS';

    // The protocol ends lines with LF on every platform, so PHP_EOL would be wrong.
    private const LF = "\n";
    private const BEGIN = 'BEGIN' . self::LF;

    public function __construct(
        private PhiMailConfig $config,
        private PhiMailConnectionFactory $connections,
        private DirectMessageLogRepository $messageLog,
        private EventAuditLogger $auditLogger,
        private LoggerInterface $logger,
    ) {
    }

    public static function fromGlobals(
        OEGlobalsBag $globals,
        CryptoInterface $crypto,
        LoggerInterface $logger,
        EventAuditLogger $auditLogger,
    ): self {
        $config = PhiMailConfig::fromGlobals($globals, $crypto);
        return new self(
            config: $config,
            connections: new PhiMailServerConnectionFactory($config->server, SoftwareVersion::fromGlobals($globals), $logger),
            messageLog: new DirectMessageLogRepository(),
            auditLogger: $auditLogger,
            logger: $logger,
        );
    }

    /**
     * @param string $failureEvent the audit event for a refused or unexpected final reply
     * @param string $successEvent the audit event for a queued message
     * @return string "SUCCESS", or a translated message for the user
     * @throws SqlQueryException when a queued message cannot be logged
     */
    public function send(
        PhiMailOutgoingMessage $message,
        PhiMailRequester $requester,
        string $failureEvent,
        string $successEvent,
    ): string {
        $configError = xl(ErrorConstants::MESSAGING_DISABLED) . ' ' . ErrorConstants::ERROR_CODE_ABBREVIATION . ':';
        if (!$this->config->server->enabled) {
            return $configError . ' ' . ErrorConstants::ERROR_CODE_MESSAGING_DISABLED;
        }
        $refusal = $this->lineBreakRefusal($message, $configError);
        if ($refusal !== null) {
            return $refusal;
        }

        try {
            $connection = $this->connections->open();
        } catch (PhiMailConnectionException $e) {
            $this->logger->error('phiMail send could not connect', ['error' => $e->auditCode(), 'exception' => $e]);
            return $configError . ' ' . $e->auditCode();
        }

        try {
            $reply = $this->transmit($connection, $message, $configError);
        } catch (PhiMailSendException $e) {
            return $e->userMessage;
        } finally {
            $connection->close();
        }

        if (str_starts_with($reply, 'ERROR')) {
            $this->audit($failureEvent, $requester, false, $reply, $message->patientId);
            return xl(ErrorConstants::ERROR_MESSAGE_FILE_SEND_FAILED);
        }

        // The server accepts a message with "QUEUED recipient message-id".
        $fields = explode(' ', trim($reply), 4);
        if ($fields[0] !== 'QUEUED' || !isset($fields[2])) {
            $this->audit($failureEvent, $requester, false, 'UNEXPECTED RESPONSE: ' . $reply, $message->patientId);
            return xl(ErrorConstants::ERROR_MESSAGE_UNEXPECTED_RESPONSE);
        }

        $this->audit($successEvent, $requester, true, $reply, $message->patientId);
        $this->messageLog->recordSent($fields[2], $this->config->username, $message->recipient, $message->patientId, $requester->userId);
        return self::SUCCESS;
    }

    /**
     * Run the exchange up to the server's reply to SEND.
     *
     * @return string the reply to SEND, or '' when the server closed the connection first
     * @throws PhiMailSendException when the server refuses an earlier step
     */
    private function transmit(PhiMailConnection $connection, PhiMailOutgoingMessage $message, string $configError): string
    {
        if ($this->sendExpectingOk($connection, 'AUTH ' . $this->config->username . ' ' . $this->config->password . self::LF) !== null) {
            throw new PhiMailSendException($configError . ' ' . ErrorConstants::ERROR_CODE_AUTH_FAILED);
        }

        $reply = $this->sendExpectingOk($connection, 'TO ' . $message->recipient . self::LF);
        if ($reply !== null) {
            throw new PhiMailSendException(xl(ErrorConstants::RECIPIENT_NOT_ALLOWED) . ' ' . $reply);
        }
        // The server follows its OK with one more line, which the send does not use.
        try {
            $connection->readLine();
        } catch (PhiMailStreamException $e) {
            $this->connectionLost($e);
            throw new PhiMailSendException($configError . ' ' . ErrorConstants::ERROR_CODE_MESSAGE_BEGIN_FAILED);
        }

        if (!$this->sendExpectingBegin($connection, 'TEXT ' . strlen($message->text) . self::LF)) {
            throw new PhiMailSendException($configError . ' ' . ErrorConstants::ERROR_CODE_MESSAGE_BEGIN_FAILED);
        }
        if ($this->sendExpectingOk($connection, $message->text) !== null) {
            throw new PhiMailSendException($configError . ' ' . ErrorConstants::ERROR_CODE_MESSAGE_BEGIN_OK_FAILED);
        }

        $attachment = $message->attachment;
        if ($attachment instanceof PhiMailOutgoingAttachment) {
            $add = 'ADD ' . $attachment->type->value . ' ' . strlen($attachment->content) . ' ' . $attachment->fileName . self::LF;
            if (!$this->sendExpectingBegin($connection, $add)) {
                throw new PhiMailSendException($configError . ' ' . ErrorConstants::ERROR_CODE_ADD_FILE_FAILED);
            }
            if ($this->sendExpectingOk($connection, $attachment->content) !== null) {
                throw new PhiMailSendException($configError . ' ' . ErrorConstants::ERROR_CODE_ADD_FILE_CONFIRM_FAILED);
            }
        }

        $reply = $this->sendExpectingOk($connection, 'SET FINAL ' . ($message->verifyFinalDelivery ? '1' : '0') . self::LF);
        if ($reply !== null) {
            throw new PhiMailSendException(xl(ErrorConstants::ERROR_MESSAGE_SET_DISPOSITION_NOTIFICATION_FAILED) . ' ' . $reply);
        }

        try {
            $connection->write('SEND' . self::LF);
            $reply = $connection->readLine();
        } catch (PhiMailStreamException $e) {
            $this->connectionLost($e);
            return '';
        }
        try {
            $connection->write('OK' . self::LF);
        } catch (PhiMailStreamException $e) {
            // The reply is already in hand, so a lost acknowledgment changes nothing.
            $this->connectionLost($e);
        }
        return $reply;
    }

    /**
     * A line break in a command argument would end the command early and send
     * the rest as another command, so refuse the send before connecting.
     *
     * @return ?string the message for the user, or null when every argument is safe
     */
    private function lineBreakRefusal(PhiMailOutgoingMessage $message, string $configError): ?string
    {
        $attachment = $message->attachment;
        $refusal = match (true) {
            self::hasLineBreak($this->config->username), self::hasLineBreak($this->config->password)
                => $configError . ' ' . ErrorConstants::ERROR_CODE_AUTH_FAILED,
            self::hasLineBreak($message->recipient) => xl(ErrorConstants::RECIPIENT_NOT_ALLOWED),
            $attachment instanceof PhiMailOutgoingAttachment && self::hasLineBreak($attachment->fileName)
                => $configError . ' ' . ErrorConstants::ERROR_CODE_ADD_FILE_FAILED,
            default => null,
        };
        if ($refusal !== null) {
            $this->logger->warning('phiMail send refused a command argument with a line break');
        }
        return $refusal;
    }

    private function connectionLost(PhiMailStreamException $e): void
    {
        $this->logger->warning('phiMail connection failed during a send', ['exception' => $e]);
    }

    private function audit(string $event, PhiMailRequester $requester, bool $success, string $text, ?int $patientId): void
    {
        $this->auditLogger->newEvent($event, $requester->username, $requester->group, $success ? 1 : 0, $text, $patientId);
    }

    /**
     * @return ?string null on OK, otherwise the server's reply, or '' when there was none
     */
    private function sendExpectingOk(PhiMailConnection $connection, string $data): ?string
    {
        try {
            $connection->expectOk($data);
        } catch (PhiMailUnexpectedResponseException $e) {
            return $e->response;
        } catch (PhiMailStreamException $e) {
            $this->connectionLost($e);
            return '';
        }
        return null;
    }

    /**
     * Send a command that announces upload content, and report whether the server is ready for it.
     */
    private function sendExpectingBegin(PhiMailConnection $connection, string $command): bool
    {
        try {
            $connection->write($command);
            return $connection->readLine() === self::BEGIN;
        } catch (PhiMailStreamException $e) {
            $this->connectionLost($e);
            return false;
        }
    }

    private static function hasLineBreak(string $value): bool
    {
        return strpbrk($value, "\r\n") !== false;
    }
}
