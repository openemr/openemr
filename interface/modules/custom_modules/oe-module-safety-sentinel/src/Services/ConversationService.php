<?php

/**
 * Safety Sentinel Conversation Service
 *
 * Persists and retrieves LangChain message history for multi-turn
 * safety check conversations, one row per message.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ryo Iwata <ryo@example.com>
 * @copyright Copyright (c) 2026
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\SafetySentinel\Services;

use OpenEMR\Validators\ProcessingResult;

class ConversationService
{
    private const TABLE = 'safety_conversations';

    private const VALID_ROLES = ['human', 'ai', 'tool'];

    /**
     * List distinct conversations for a patient, most recent first.
     *
     * Returns one row per conversation_id with message count and latest timestamp.
     */
    public function listByPatient(string $puuid, int $limit): ProcessingResult
    {
        $result = new ProcessingResult();
        $rows = sqlStatement(
            "SELECT conversation_id,
                    COUNT(*) AS message_count,
                    MAX(created_at) AS last_message_at
             FROM `" . self::TABLE . "`
             WHERE patient_uuid = ?
             GROUP BY conversation_id
             ORDER BY last_message_at DESC
             LIMIT ?",
            [$puuid, $limit]
        );
        $data = [];
        while ($row = sqlFetchArray($rows)) {
            $data[] = [
                'conversation_id' => $row['conversation_id'],
                'message_count'   => (int)$row['message_count'],
                'last_message_at' => $row['last_message_at'],
            ];
        }
        $result->setData($data);
        return $result;
    }

    /**
     * Return all messages for a conversation, ordered by insertion order.
     */
    public function getByConversation(string $conv_id, string $puuid, int $limit): ProcessingResult
    {
        $result = new ProcessingResult();
        $rows = sqlStatement(
            "SELECT role, content, tool_name, tool_call_id
             FROM `" . self::TABLE . "`
             WHERE conversation_id = ? AND patient_uuid = ?
             ORDER BY id ASC
             LIMIT ?",
            [$conv_id, $puuid, $limit]
        );
        $data = [];
        while ($row = sqlFetchArray($rows)) {
            $data[] = $row;
        }
        $result->setData($data);
        return $result;
    }

    /**
     * Replace all stored messages for a conversation (delete then insert).
     *
     * Each message in $messages must have: role, content (JSON string), tool_name, tool_call_id.
     */
    public function save(string $conv_id, string $puuid, array $messages): ProcessingResult
    {
        $result = new ProcessingResult();

        // Validate roles before touching the DB
        foreach ($messages as $i => $msg) {
            if (!isset($msg['role']) || !in_array($msg['role'], self::VALID_ROLES, true)) {
                $result->addValidationError("messages[$i].role", "role must be one of: human, ai, tool");
            }
            if (!isset($msg['content'])) {
                $result->addValidationError("messages[$i].content", "content is required");
            }
        }
        if ($result->hasErrors()) {
            return $result;
        }

        // Delete existing messages for this conversation
        sqlStatement(
            "DELETE FROM `" . self::TABLE . "` WHERE conversation_id = ? AND patient_uuid = ?",
            [$conv_id, $puuid]
        );

        $saved = 0;
        foreach ($messages as $msg) {
            sqlInsert(
                "INSERT INTO `" . self::TABLE . "`
                 (conversation_id, patient_uuid, role, content, tool_name, tool_call_id)
                 VALUES (?, ?, ?, ?, ?, ?)",
                [
                    substr($conv_id, 0, 36),
                    substr($puuid, 0, 255),
                    $msg['role'],
                    $msg['content'],
                    isset($msg['tool_name']) ? substr($msg['tool_name'], 0, 255) : null,
                    isset($msg['tool_call_id']) ? substr($msg['tool_call_id'], 0, 255) : null,
                ]
            );
            $saved++;
        }

        $result->setData([['saved' => $saved]]);
        return $result;
    }

    /**
     * Delete all messages for a conversation.
     */
    public function delete(string $conv_id, string $puuid): ProcessingResult
    {
        $result = new ProcessingResult();
        sqlStatement(
            "DELETE FROM `" . self::TABLE . "` WHERE conversation_id = ? AND patient_uuid = ?",
            [$conv_id, $puuid]
        );
        $result->setData([['deleted' => true]]);
        return $result;
    }
}
