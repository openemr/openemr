<?php

/**
 * Checks a phiMail Direct Messaging server for status updates on sent messages
 * and for new messages, and delivers each to OpenEMR.
 *
 * Every failure writes an audit row and throws PhiMailCheckException, so the
 * background service runner reports the run as an error. Operators search the
 * audit log for the M-codes, so their text must not change.
 *
 * Copyright (C) 2013, 2021 EMR Direct <https://www.emrdirect.com/>
 * Copyright (C) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
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

use Doctrine\DBAL\Exception as DbalException;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\SoftwareVersion;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

final class PhiMailReceiver
{
    // The protocol ends lines with LF on every platform, so PHP_EOL would be wrong.
    private const LF = "\n";
    private const TEXT_TYPES = ['text/plain', 'text/html'];

    /**
     * Downloaded bodies and attachments hold message content (PHI). Track
     * them so a failed check cannot leave them in the temp directory.
     *
     * @var list<string>
     */
    private array $tempFiles = [];

    /**
     * @param string $tempDir where message bodies and attachments are downloaded
     */
    public function __construct(
        private readonly PhiMailConfig $config,
        private readonly PhiMailConnectionFactory $connections,
        private readonly DirectMessageLogRepository $messageLog,
        private readonly PhiMailUserRepository $users,
        private readonly PhiMailDocumentStore $documents,
        private readonly PhiMailNoteWriter $notes,
        private readonly PhiMailNotifier $notifier,
        private readonly PhiMailAuditLog $audit,
        private readonly LoggerInterface $logger,
        private readonly string $tempDir,
        private readonly Filesystem $filesystem = new Filesystem(),
    ) {
    }

    /**
     * The phimail background_services row names this method. The runner
     * calls it with no arguments, so it builds the receiver from globals.
     *
     * @throws PhiMailCheckException when the check fails, after writing its audit row
     * @throws DbalException when an audit row cannot be written on a path that is not already failing
     */
    public static function runBackgroundService(): void
    {
        self::fromGlobals(
            OEGlobalsBag::getInstance(),
            ServiceContainer::getCrypto(),
            ServiceContainer::getLogger(),
            EventAuditLogger::getInstance(),
        )->check();
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
            users: new PhiMailUserRepository(),
            documents: new LegacyPhiMailDocumentStore($globals->hasKernel() ? $globals->getKernel()->getEventDispatcher() : null),
            notes: new LegacyPhiMailNoteWriter(),
            notifier: new PhiMailEmailNotifier($globals->getString('practice_return_email_path')),
            audit: new PhiMailAuditLog($auditLogger, $logger),
            logger: $logger,
            tempDir: $globals->getString('temporary_files_dir'),
        );
    }

    /**
     * Process every status update and message the server has waiting.
     *
     * @throws PhiMailCheckException when the check fails, after writing its audit row
     * @throws DbalException when an audit row cannot be written on a path that is not already failing
     */
    public function check(): void
    {
        try {
            $connection = $this->connections->open();
        } catch (PhiMailConnectionException $e) {
            $this->logger->error('phiMail check could not connect', ['exception' => $e]);
            $this->fail(xl('could not connect to server') . ' ' . $e->auditCode(), $e);
        }

        $this->tempFiles = [];
        try {
            $this->authenticate($connection);
            while ($this->processNextReply($connection)) {
                // Each pass handles one reply to CHECK.
            }
        } finally {
            $connection->close();
            // A no-op for files already stored or deleted.
            $this->filesystem->remove($this->tempFiles);
            $this->tempFiles = [];
        }
    }

    private function authenticate(PhiMailConnection $connection): void
    {
        $command = 'AUTH ' . $this->config->username . ' ' . $this->config->password . self::LF;
        $this->expectOk($connection, $command, 'authentication error ');
    }

    /**
     * @return bool false once the server has nothing more to deliver
     */
    private function processNextReply(PhiMailConnection $connection): bool
    {
        try {
            $connection->write('CHECK' . self::LF);
            $line = $connection->readLine();
        } catch (PhiMailStreamException $e) {
            $this->fail('M16 unexpected problem checking messages ', $e);
        }

        try {
            $reply = PhiMailCheckReplyParser::parse($line);
        } catch (PhiMailUnexpectedResponseException $e) {
            $this->fail('M16 unexpected problem checking messages ' . $line, $e);
        }

        if ($reply instanceof PhiMailNoneReply) {
            $this->audit->record(true, 'message check completed');
            return false;
        }
        if ($reply instanceof PhiMailStatusReply) {
            $this->processStatus($connection, $reply);
        } else {
            $this->processMail($connection, $reply);
        }
        return true;
    }

    private function processStatus(PhiMailConnection $connection, PhiMailStatusReply $reply): void
    {
        try {
            $sent = $this->messageLog->findByMessageId($reply->messageId);
        } catch (SqlQueryException $e) {
            $this->fail('database problem', $e);
        }

        if ($sent === null) {
            // Should never happen, so log it and move on.
            $this->audit->record(false, 'NO MATCH: ' . $reply->line);
            $this->expectOk($connection, 'OK' . self::LF, 'M1 status acknowledgment failed: ');
            return;
        }

        $delivered = $reply->status === PhiMailDeliveryStatus::Dispatched;
        $serverMessage = $reply->status === PhiMailDeliveryStatus::Unknown
            ? 'UNKNOWN STATUS: ' . $reply->line
            : $reply->line;
        $this->audit->record($delivered, $serverMessage, $sent->patientId);

        try {
            $this->messageLog->updateStatus($reply->messageId, $reply->status, $reply->info);
        } catch (SqlQueryException $e) {
            $this->fail('database problem updating: ' . $reply->messageId, $e);
        }

        if (!$delivered) {
            $this->reportSendFailure($reply, $sent, $serverMessage);
        }

        $this->expectOk($connection, 'OK' . self::LF, 'M2 status acknowledgment failed: ');
    }

    /**
     * Tell the practice and the notify user that a sent message failed.
     */
    private function reportSendFailure(PhiMailStatusReply $reply, DirectMessageLogEntry $sent, string $serverMessage): void
    {
        try {
            $sender = $this->users->findUsername($sent->userId);
        } catch (SqlQueryException $e) {
            $this->fail('database problem looking up sender of: ' . $reply->messageId, $e);
        }

        $notice = xl('Sent by:') . ' ' . ($sender ?? xl('unknown (see log)')) . '(' . $sent->userId . ') '
            . xl('on') . ' ' . $sent->createdAt . self::LF
            . xl('Sent to:') . ' ' . $sent->recipient . self::LF
            . xl('Server message:') . ' ' . $serverMessage;
        $this->notifier->notify(xl('Direct Messaging Send Failure.'), $notice);
        $note = xl('FAILURE NOTICE: Direct Message Send Failed.') . self::LF . self::LF . $notice . self::LF;
        try {
            $this->notes->addNote($sent->patientId ?? 0, $note, $this->config->notifyUsername);
        } catch (SqlQueryException $e) {
            $this->fail('database problem adding failure notice for: ' . $reply->messageId, $e);
        }
    }

    /**
     * Reads one received message, files its note and documents (to the patient of the sent message it answers, if any), and logs it.
     */
    private function processMail(PhiMailConnection $connection, PhiMailMailReply $reply): void
    {
        $this->expectOk($connection, 'SHOW 0' . self::LF, 'M3 SHOW 0 failed: ');
        $headers = $this->readHeaders($connection);
        // A reply to a message sent from here about a patient is filed to that patient, and to the
        // referral the sent message carried; anything else stays unassigned.
        try {
            $target = $this->messageLog->findReplyTarget(DirectReplyHeaders::referencedMessageIds($headers), $reply->sender);
        } catch (SqlQueryException $e) {
            $this->fail('database problem matching reply: ' . $reply->messageId, $e);
        }
        $patientId = $target?->patientId;
        try {
            $mimeType = self::mainMimeType($connection->readLine());
        } catch (PhiMailStreamException $e) {
            $this->fail('M4 read headers failed', $e);
        }
        $bodyLength = self::readLength($connection);
        $body = $this->download($connection, $bodyLength, 'M4 read body failed');

        try {
            $announced = trim($connection->readLine());
        } catch (PhiMailStreamException $e) {
            $this->fail('M5 attachment mismatch', $e);
        }
        if ($announced !== (string) $reply->attachmentCount) {
            $this->fail('M5 attachment mismatch');
        }
        $attachments = $this->readAttachmentInfo($connection, $reply->attachmentCount);

        // A text body becomes the note itself; anything else is stored as a document.
        $documentIds = [];
        $details = '';
        $isText = in_array($mimeType, self::TEXT_TYPES, true);
        if (!$isText) {
            if ($bodyLength === 0) {
                $details .= self::LF . sprintf(
                    '%s (%s; 0 bytes - %s) Main message body',
                    xl('Zero length attachment'),
                    $mimeType,
                    xl('empty file received'),
                );
                $this->filesystem->remove($body);
            } else {
                $name = uniqid('dm-message-') . self::extension($mimeType);
                $document = $this->store($name, $mimeType, $body, 'M7 store non-text body failed', $patientId);
                if ($document->url === null) {
                    $this->fail('M7 store non-text body returned non-string url');
                }
                $documentIds[] = $document->id;
                $details .= self::LF . sprintf(
                    '%s %d ("%s"; %s; %d bytes) Main message body',
                    xl('Document'),
                    $document->id,
                    $document->fileName(),
                    $mimeType,
                    $document->size,
                );
            }
        }

        foreach ($attachments as $index => $attachment) {
            $number = $index + 1;
            $this->expectOk($connection, 'SHOW ' . $number . self::LF, 'M8 SHOW ' . $number . ' failed: ');
            // The next two lines repeat the attachment's name and MIME type.
            try {
                $connection->readLine();
                $connection->readLine();
            } catch (PhiMailStreamException $e) {
                $this->fail('M9 skip attachment ' . $number . ' duplicate header lines failed', $e);
            }
            $length = self::readLength($connection);
            $file = $this->download($connection, $length, 'M10 read attachment ' . $number . ' failed');

            if ($length === 0) {
                $details .= self::LF . sprintf(
                    '%s (%s; 0 bytes - %s) %s',
                    xl('Zero length attachment'),
                    $attachment->mimeType,
                    xl('empty file received'),
                    $attachment->description,
                );
                $this->filesystem->remove($file);
                continue;
            }

            $name = $attachment->name === ''
                ? uniqid('dm-attachment-') . self::extension($attachment->mimeType)
                : 'dm-' . $attachment->name;
            $document = $this->store($name, $attachment->mimeType, $file, 'M11 store attachment ' . $number . ' failed', $patientId);
            $documentIds[] = $document->id;
            $details .= self::LF . sprintf(
                '%s %d ("%s"; %s; %d bytes) %s',
                xl('Document'),
                $document->id,
                $document->fileName(),
                $attachment->mimeType,
                $document->size,
                $attachment->description,
            );
        }

        if ($details !== '') {
            $details = self::LF . self::LF . xl('The following documents were attached to this Direct message:') . $details;
        }

        // Check for a DONE failure only after the message is delivered and logged.
        $doneFailure = $this->sendExpectingOk($connection, 'DONE' . self::LF);
        // DONE acknowledges the message, so the server will not send it
        // again. Keep the body out of the failure cleanup from here on, so
        // a failure before the note exists leaves it on disk for recovery.
        $this->tempFiles = array_values(array_diff($this->tempFiles, [$body]));

        try {
            $content = $isText ? $this->bodyText($body, $mimeType) : xl('Message content is not plain text so it has been stored as a document.');
        } catch (IOException $e) {
            $this->fail('problem reading received message body: ' . $reply->messageId, $e);
        }
        $note = xl('Direct Message Received.') . self::replyLine($target) . self::LF . $headers . self::LF . $content . $details;
        try {
            $noteId = $this->notes->addNote($patientId ?? 0, $note, $this->config->notifyUsername);
            foreach ($documentIds as $documentId) {
                $this->notes->linkDocument($documentId, $noteId);
            }
        } catch (SqlQueryException $e) {
            $this->fail('database problem delivering received message: ' . $reply->messageId, $e);
        }

        // The note now holds a text body.
        if ($isText) {
            $this->filesystem->remove($body);
        }

        try {
            $this->messageLog->recordReceived(
                $reply->messageId,
                $reply->sender,
                $reply->recipient,
                $this->users->serviceUserId(),
                $patientId,
                $target?->referralId,
                $noteId,
            );
        } catch (SqlQueryException $e) {
            $this->fail('database problem logging received message: ' . $reply->messageId, $e);
        }

        $this->audit->record(true, $reply->line, $patientId ?? 0);

        if ($doneFailure !== null) {
            $this->fail('M12 DONE failed: ' . $doneFailure);
        }
    }

    /**
     * Read the message headers, up to the blank line that ends them.
     *
     * @return string the header lines, each with its line feed
     */
    private function readHeaders(PhiMailConnection $connection): string
    {
        $headers = '';
        while (true) {
            try {
                $line = $connection->readLine();
            } catch (PhiMailStreamException $e) {
                $this->fail('M4 read headers failed', $e);
            }
            if ($line === self::LF) {
                return $headers;
            }
            $headers .= $line;
        }
    }

    /**
     * Read the name, MIME type, and description lines that follow the body for each attachment.
     *
     * @return list<PhiMailAttachmentInfo>
     */
    private function readAttachmentInfo(PhiMailConnection $connection, int $count): array
    {
        $attachments = [];
        for ($index = 0; $index < $count; $index++) {
            try {
                $name = $connection->readLine();
                $mimeType = $connection->readLine();
                $description = $connection->readLine();
            } catch (PhiMailStreamException $e) {
                $this->fail('M6 read attachment ' . ($index + 1) . ' metadata failed', $e);
            }
            $attachments[] = new PhiMailAttachmentInfo(trim($name), self::mainMimeType($mimeType), trim($description));
        }
        return $attachments;
    }

    /**
     * Download a message part into the temp directory.
     *
     * @param ?int $length the byte count the server announced, or null when it sent no valid count
     * @return string path of the downloaded file
     */
    private function download(PhiMailConnection $connection, ?int $length, string $failure): string
    {
        if ($length === null) {
            $this->audit->record(false, 'M17 invalid blob length', failing: true);
            $this->fail($failure);
        }
        try {
            $path = $connection->readBlob($length, $this->tempDir);
        } catch (PhiMailBlobException $e) {
            $this->audit->record(false, $e->getMessage(), failing: true);
            $this->fail($failure, $e);
        }
        $this->tempFiles[] = $path;
        return $path;
    }

    /**
     * Store a downloaded part as a document. The downloaded file is removed either way.
     */
    private function store(string $name, string $mimeType, string $path, string $failure, ?int $patientId): PhiMailStoredDocument
    {
        try {
            $document = $this->documents->store($name, $mimeType, $path, $this->users->serviceUserId(), $patientId);
        } catch (SqlQueryException | PhiMailDocumentException $e) {
            $this->logger->error('phiMail could not store a received document', ['exception' => $e, 'mimeType' => $mimeType]);
            $this->audit->record(false, 'problem storing attachment in OpenEMR', failing: true);
            $this->fail($failure, $e);
        } finally {
            $this->filesystem->remove($path);
        }
        if ($document === null) {
            $this->fail($failure);
        }
        return $document;
    }

    /**
     * The note text for a text/plain or text/html body.
     *
     * @throws IOException when the downloaded body cannot be read
     */
    private function bodyText(string $body, string $mimeType): string
    {
        $text = $this->filesystem->readFile($body);
        if ($text === '') {
            return xl('Please note, this message was received empty and is not an error.');
        }
        if ($mimeType === 'text/plain') {
            return $text;
        }
        // A meager conversion to text, since messages cannot display HTML.
        return trim(html_entity_decode(strip_tags(str_ireplace(['<br />', '<br>', '<br/>'], PHP_EOL, $text))));
    }

    /**
     * Send a command that the server should answer with OK, and fail the check with $failure plus its reply otherwise.
     */
    private function expectOk(PhiMailConnection $connection, string $command, string $failure): void
    {
        $reply = $this->sendExpectingOk($connection, $command);
        if ($reply !== null) {
            $this->fail($failure . $reply);
        }
    }

    /**
     * @return ?string null on OK, otherwise the server's reply, or '' when there was none
     */
    private function sendExpectingOk(PhiMailConnection $connection, string $command): ?string
    {
        try {
            $connection->expectOk($command);
        } catch (PhiMailUnexpectedResponseException $e) {
            return $e->response;
        } catch (PhiMailStreamException) {
            return '';
        }
        return null;
    }

    /**
     * Record the failure in the audit log, then throw so the background
     * service runner reports the run as an error.
     */
    private function fail(string $text, ?\Throwable $previous = null): never
    {
        $this->audit->record(false, $text, failing: true);
        throw new PhiMailCheckException($text, 0, $previous);
    }

    /**
     * What the note says about the sent message a reply answers, if any.
     */
    private static function replyLine(?DirectReplyTarget $target): string
    {
        if ($target === null) {
            return '';
        }
        return ' ' . ($target->referralId === null
            ? xl('It answers a message sent about this patient.')
            : xl('It answers a message sent with referral') . ' #' . $target->referralId . ' ' . xl('for this patient') . '.');
    }

    /**
     * @return ?int the byte count on the next line, or null when the line is missing or not a count
     */
    private static function readLength(PhiMailConnection $connection): ?int
    {
        try {
            $digits = trim($connection->readLine());
        } catch (PhiMailStreamException) {
            return null;
        }
        return ctype_digit($digits) ? (int) $digits : null;
    }

    /**
     * The lowercase type and subtype of a Content-Type line, without its parameters.
     */
    private static function mainMimeType(string $line): string
    {
        return strtolower(trim(explode(';', $line)[0]));
    }

    /**
     * A file name extension for a MIME type: .txt, .html, .xml, or .pdf, and .dat for anything else.
     */
    private static function extension(string $mimeType): string
    {
        if ($mimeType === 'text/plain') {
            return '.txt';
        }
        $subtype = explode('/', $mimeType)[1] ?? '';
        return match ($subtype) {
            'html', 'xml', 'pdf' => '.' . $subtype,
            default => '.dat',
        };
    }
}
