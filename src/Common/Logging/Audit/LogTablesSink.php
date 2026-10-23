<?php

/**
 * Audit event sink that writes to log tables:
 * - log
 * - log_comment_encrypt
 * - api_log
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

use Doctrine\DBAL\Connection;

readonly class LogTablesSink implements SinkInterface
{
    /**
     * CRITICAL: The connection must be separate from the one used for other
     * database operations. If it's not, autoincrement IDs will start
     * cross-talking during SQL events being logged, since it's stateful across
     * the connection rather than the query.
     *
     * If $crypto is set, the api body data will be stored encrypted and
     * marked as such. If null, they will be blanked out.
     */
    public function __construct(
        private Connection $conn,
        private readonly string $chainKey,
        private AuditEventBuffer $buffer = new AuditEventBuffer(),
    ) {
    }

    public function record(Event $event): void
    {
        $this->buffer->add($event);
    }

    /**
     * Writes every event buffered for this request in one transaction.
     */
    public function flush(): void
    {
        $events = $this->buffer->drain();
        if ($events === []) {
            return;
        }

        $this->conn->beginTransaction();
        try {
            $this->conn->executeStatement(
                'INSERT INTO log_chain_anchor (id, log_id, chain) VALUES (1, 0, ?) ON DUPLICATE KEY UPDATE id = id',
                [AuditChain::genesis()],
            );
            $anchor = $this->conn->fetchAssociative(
                'SELECT chain FROM log_chain_anchor WHERE id = 1 FOR UPDATE',
            );
            $previous = AuditChain::genesis();
            if (is_array($anchor)) {
                $stored = $anchor['chain'] ?? null;
                if (is_string($stored) && $stored !== '') {
                    $previous = $stored;
                }
            }
            foreach ($events as $event) {
                $previous = $this->write($event, $previous);
            }
            $this->conn->executeStatement(
                'UPDATE log_chain_anchor SET log_id = ?, chain = ? WHERE id = 1',
                [$this->buffer->lastLogId, $previous],
            );
            $this->conn->commit();
        } catch (\Throwable $failure) {
            if ($this->conn->isTransactionActive()) {
                $this->conn->rollBack();
            }
            throw $failure;
        }
    }

    public function __destruct()
    {
        try {
            $this->flush();
        } catch (\RuntimeException) {
            // The request is already ending.
        }
    }

    private function write(Event $event, string $previous): string
    {
        $api = $event->api;

        // 1. insert entry into log table
        $logData = [
            'date' => $event->current_datetime,
            'event' => $event->event,
            'category' => $event->category,
            'user' => $event->user,
            'groupname' => $event->group,
            'comments' => $event->comments,
            'user_notes' => $event->user_notes,
            'patient_id' => $event->patientId,
            'success' => $event->success,
            // crt_user stored SSL client certificate CN for ATNA compliance (2010).
            // Client cert auth was never integrated with login; column is vestigial.
            'crt_user' => '',
            'log_from' => $event->logFrom,
            'menu_item_id' => $event->menuItemId,
            'ccda_doc_id' => $event->ccdaDocId,
        ];
        $this->conn->insert('log', $logData);
        $lastLogId = $this->conn->lastInsertId();
        $this->buffer->lastLogId = (string) $lastLogId;

        $checksum = hash('sha3-512', implode('', array_values($logData)));

        if ($api === null) {
            $checksumGenerateApi = '';
        } else {
            //...
            // api log
            $ipAddress = collectIpAddresses()['ip_string'];
            $apiLogData = [
                'log_id' => $lastLogId,
                'user_id' => $api['user_id'],
                // Empty-string default keeps checksums of rows logged before the
                // client_id column existed verifiable by the tamper report.
                'client_id' => $api['client_id'] ?? '',
                'patient_id' => $api['patient_id'],
                'ip_address' => $ipAddress,
                'method' => $api['method'],
                'request' => $api['request'],
                'request_url' => $api['request_url'],
                'request_body' => $api['request_body'],
                'response' => $api['response'],
                'created_time' => $event->current_datetime,
            ];
            $checksumGenerateApi = hash('sha3-512', implode('', array_values($apiLogData)));
        }

        $chain = AuditChain::link($previous, $this->buffer->lastLogId, $checksum, $this->chainKey);

        // 2. insert associated entry (in addition to calculating and storing applicable checksums) into log_comment_encrypt
        $logCommentData = [
            'log_id' => $lastLogId,
            'encrypt' => 'No', // DB is a Yes/No enum instead of bool :( (see #12118)
            'checksum' => $checksum,
            'checksum_api' => $checksumGenerateApi,
            'version' => '5',
            'chain' => $chain,
        ];
        $this->conn->insert('log_comment_encrypt', $logCommentData);

        // 3. if api log entry, then insert insert associated entry into api_log
        if ($api !== null) {
            $this->conn->insert('api_log', $apiLogData);
        }

        return $chain;
    }
}
